<?php
/**
 * Wishlist AJAX Handler
 * Handles add, remove, and get wishlist operations
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'error' => 'Please login to use wishlist',
        'redirect' => 'login.php'
    ]);
    exit;
}

$user_id = getCurrentUserId();
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : (isset($_GET['product_id']) ? intval($_GET['product_id']) : 0);

try {
    switch ($action) {
        case 'add':
            // Add product to wishlist
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID');
            }

            // Check if product exists
            $productCheck = $pdo->prepare("SELECT id FROM products WHERE id = ? AND is_active = 1");
            $productCheck->execute([$product_id]);
            if ($productCheck->rowCount() === 0) {
                throw new Exception('Product not found');
            }

            // Check if already in wishlist
            $checkStmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $checkStmt->execute([$user_id, $product_id]);

            if ($checkStmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Product already in wishlist',
                    'already_exists' => true,
                    'wishlist_count' => getWishlistCount($pdo)
                ]);
            } else {
                // Add to wishlist
                $insertStmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
                $insertStmt->execute([$user_id, $product_id]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Product added to wishlist',
                    'wishlist_count' => getWishlistCount($pdo)
                ]);
            }
            break;

        case 'remove':
            // Remove product from wishlist
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID');
            }

            $deleteStmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $deleteStmt->execute([$user_id, $product_id]);

            echo json_encode([
                'success' => true,
                'message' => 'Product removed from wishlist',
                'wishlist_count' => getWishlistCount($pdo)
            ]);
            break;

        case 'toggle':
            // Toggle product in wishlist
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID');
            }

            $checkStmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $checkStmt->execute([$user_id, $product_id]);

            if ($checkStmt->rowCount() > 0) {
                // Remove from wishlist
                $deleteStmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $deleteStmt->execute([$user_id, $product_id]);

                echo json_encode([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Removed from wishlist',
                    'wishlist_count' => getWishlistCount($pdo)
                ]);
            } else {
                // Add to wishlist
                $insertStmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
                $insertStmt->execute([$user_id, $product_id]);

                echo json_encode([
                    'success' => true,
                    'action' => 'added',
                    'message' => 'Added to wishlist',
                    'wishlist_count' => getWishlistCount($pdo)
                ]);
            }
            break;

        case 'get':
        case 'list':
            // Get all wishlist items
            $stmt = $pdo->prepare("
                SELECT
                    w.id as wishlist_id,
                    w.created_at as added_date,
                    p.id,
                    p.name,
                    p.slug,
                    p.price,
                    p.sale_price,
                    p.image_path,
                    p.stock_quantity,
                    c.name as category_name
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE w.user_id = ? AND p.is_active = 1
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $items_data = $stmt->fetchAll();

            $items = [];
            foreach ($items_data as $row) {
                $discount_percentage = 0;
                if ($row['sale_price'] && $row['sale_price'] < $row['price']) {
                    $discount_percentage = round((($row['price'] - $row['sale_price']) / $row['price']) * 100);
                }

                $items[] = [
                    'wishlist_id' => (int)$row['wishlist_id'],
                    'product_id' => (int)$row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'price' => (float)$row['price'],
                    'sale_price' => $row['sale_price'] ? (float)$row['sale_price'] : null,
                    'final_price' => $row['sale_price'] ? (float)$row['sale_price'] : (float)$row['price'],
                    'discount_percentage' => $discount_percentage,
                    'image_path' => $row['image_path'],
                    'stock_quantity' => (int)$row['stock_quantity'],
                    'category_name' => $row['category_name'],
                    'added_date' => $row['added_date']
                ];
            }

            echo json_encode([
                'success' => true,
                'items' => $items,
                'count' => count($items)
            ]);
            break;

        case 'count':
            // Get wishlist count
            echo json_encode([
                'success' => true,
                'count' => getWishlistCount($pdo)
            ]);
            break;

        case 'clear':
            // Clear all wishlist items
            $deleteStmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ?");
            $deleteStmt->execute([$user_id]);

            echo json_encode([
                'success' => true,
                'message' => 'Wishlist cleared',
                'wishlist_count' => 0
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
