<?php
/**
 * Remove Item from Cart
 * Deletes a specific cart item
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $cartItemId = isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0;

    if ($cartItemId <= 0) {
        throw new Exception('Invalid cart item.');
    }

    // Get cart item to verify ownership
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE id = ?");
    $stmt->execute([$cartItemId]);
    $cartItem = $stmt->fetch();

    if (!$cartItem) {
        throw new Exception('Cart item not found.');
    }

    // Verify ownership
    if (isLoggedIn()) {
        if ($cartItem['user_id'] != getCurrentUserId()) {
            throw new Exception('Unauthorized.');
        }
    } else {
        if ($cartItem['session_id'] != session_id()) {
            throw new Exception('Unauthorized.');
        }
    }

    // Delete cart item
    $deleteStmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    $deleteStmt->execute([$cartItemId]);

    // Get updated cart count
    $cartCount = getCartCount($pdo);

    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart.',
        'cart_count' => $cartCount
    ]);

} catch (Exception $e) {
    error_log('Remove from cart error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
