<?php
/**
 * Wishlist API Endpoint
 * Handles add, remove, toggle, and check operations for user wishlists
 */

ob_start();
require_once __DIR__ . '/../includes/init.php';
ob_end_clean();

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please login to manage your wishlist',
        'require_login' => true
    ]);
    exit;
}

$userId = getCurrentUserId();

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        // Try form data
        $input = $_POST;
    }

    $action = isset($input['action']) ? $input['action'] : '';
    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;

    if (!$productId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }

    // Verify product exists
    $productStmt = $pdo->prepare('SELECT id, name FROM products WHERE id = ? AND is_active = 1');
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }

    switch ($action) {
        case 'add':
            // Check if already in wishlist
            $checkStmt = $pdo->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
            $checkStmt->execute([$userId, $productId]);

            if ($checkStmt->fetch()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Product is already in your wishlist',
                    'in_wishlist' => true
                ]);
                exit;
            }

            // Add to wishlist
            $insertStmt = $pdo->prepare('INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())');
            $insertStmt->execute([$userId, $productId]);

            // Get updated count
            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
            $countStmt->execute([$userId]);
            $wishlistCount = $countStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'message' => 'Product added to wishlist',
                'in_wishlist' => true,
                'wishlist_count' => (int)$wishlistCount
            ]);
            break;

        case 'remove':
            // Remove from wishlist
            $deleteStmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
            $deleteStmt->execute([$userId, $productId]);

            // Get updated count
            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
            $countStmt->execute([$userId]);
            $wishlistCount = $countStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'message' => 'Product removed from wishlist',
                'in_wishlist' => false,
                'wishlist_count' => (int)$wishlistCount
            ]);
            break;

        case 'toggle':
            // Check if already in wishlist
            $checkStmt = $pdo->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
            $checkStmt->execute([$userId, $productId]);
            $exists = $checkStmt->fetch();

            if ($exists) {
                // Remove from wishlist
                $deleteStmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
                $deleteStmt->execute([$userId, $productId]);
                $inWishlist = false;
                $message = 'Product removed from wishlist';
            } else {
                // Add to wishlist
                $insertStmt = $pdo->prepare('INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())');
                $insertStmt->execute([$userId, $productId]);
                $inWishlist = true;
                $message = 'Product added to wishlist';
            }

            // Get updated count
            $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
            $countStmt->execute([$userId]);
            $wishlistCount = $countStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist,
                'wishlist_count' => (int)$wishlistCount
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action. Use: add, remove, or toggle'
            ]);
            break;
    }

} elseif ($method === 'GET') {
    // Check if product is in wishlist or get full wishlist
    $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

    if ($productId) {
        // Check single product
        $checkStmt = $pdo->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
        $checkStmt->execute([$userId, $productId]);
        $inWishlist = (bool)$checkStmt->fetch();

        echo json_encode([
            'success' => true,
            'in_wishlist' => $inWishlist
        ]);
    } else {
        // Get all wishlist items
        $wishlistStmt = $pdo->prepare('
            SELECT w.product_id, w.created_at,
                   p.name, p.slug, p.price, p.sale_price, p.image_path
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ');
        $wishlistStmt->execute([$userId]);
        $items = $wishlistStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get count
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
        $countStmt->execute([$userId]);
        $wishlistCount = $countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'items' => $items,
            'count' => (int)$wishlistCount
        ]);
    }

} elseif ($method === 'DELETE') {
    // Remove product from wishlist
    $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

    if (!$productId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Product ID is required'
        ]);
        exit;
    }

    $deleteStmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
    $deleteStmt->execute([$userId, $productId]);

    // Get updated count
    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
    $countStmt->execute([$userId]);
    $wishlistCount = $countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Product removed from wishlist',
        'in_wishlist' => false,
        'wishlist_count' => (int)$wishlistCount
    ]);

} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
