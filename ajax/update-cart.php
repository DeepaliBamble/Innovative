<?php
/**
 * Update Cart Item Quantity
 * Updates quantity for a specific cart item
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {

    $cartItemId = isset($_POST['cart_item_id']) ? intval($_POST['cart_item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($cartItemId <= 0) {
        throw new Exception('Invalid cart item.');
    }

    // If quantity is 0 or less, remove the item from cart
    if ($quantity <= 0) {
        $deleteStmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $deleteStmt->execute([$cartItemId]);
        $cartCount = getCartCount($pdo);
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart.',
            'quantity' => 0,
            'cart_count' => $cartCount
        ]);
        exit;
    }

    // Get cart item
    $stmt = $pdo->prepare("
        SELECT c.*, p.stock_quantity, p.name
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id = ?
    ");
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

    // Check stock
    if ($quantity > $cartItem['stock_quantity']) {
        throw new Exception("Only {$cartItem['stock_quantity']} items available in stock.");
    }

    // Update quantity
    $updateStmt = $pdo->prepare("
        UPDATE cart
        SET quantity = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$quantity, $cartItemId]);

    // Get updated cart count
    $cartCount = getCartCount($pdo);

    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully.',
        'quantity' => $quantity,
        'cart_count' => $cartCount
    ]);

} catch (Exception $e) {
    error_log('Update cart error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
