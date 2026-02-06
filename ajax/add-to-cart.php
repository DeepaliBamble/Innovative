<?php
/**
 * AJAX Handler - Add Item to Cart
 * Adds a product to user's shopping cart
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get product ID and quantity
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($productId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product.'
    ]);
    exit;
}

if ($quantity <= 0) {
    $quantity = 1;
}

try {
    // Check if product exists and is in stock
    $stmt = $pdo->prepare('SELECT id, name, stock_quantity FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found.'
        ]);
        exit;
    }

    if ($product['stock_quantity'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock available.'
        ]);
        exit;
    }

    // Determine if user is logged in or using session
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $sessionId = null;

        // Check if item already exists in cart
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $cartItem = $stmt->fetch();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['quantity'] + $quantity;

            // Check stock again
            if ($product['stock_quantity'] < $newQuantity) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot add more items. Only ' . $product['stock_quantity'] . ' available.'
                ]);
                exit;
            }

            $stmt = $pdo->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            // Insert new cart item
            $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $productId, $quantity]);
        }
    } else {
        // Guest user - use session ID
        $sessionId = session_id();
        $userId = null;

        // Check if item already exists in cart
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?');
        $stmt->execute([$sessionId, $productId]);
        $cartItem = $stmt->fetch();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['quantity'] + $quantity;

            // Check stock again
            if ($product['stock_quantity'] < $newQuantity) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot add more items. Only ' . $product['stock_quantity'] . ' available.'
                ]);
                exit;
            }

            $stmt = $pdo->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$newQuantity, $cartItem['id']]);
        } else {
            // Insert new cart item
            $stmt = $pdo->prepare('INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)');
            $stmt->execute([$sessionId, $productId, $quantity]);
        }
    }

    // Get updated cart count
    $cartCount = getCartCount($pdo);

    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully.',
        'cart_count' => $cartCount
    ]);

} catch (PDOException $e) {
    error_log('Add to cart error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
