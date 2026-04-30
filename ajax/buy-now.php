<?php
/**
 * Buy Now handler — adds a single product to the cart and forwards
 * the user to checkout.php in one round trip. Synchronous form POST
 * (not AJAX) so the browser keeps the same session/cookie context.
 */

require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('shop.php');
}

// CSRF guard
$token = $_POST['csrf_token'] ?? '';
if (!validateCsrfToken($token)) {
    setFlashMessage('error', 'Security token expired. Please try again.');
    redirect('shop.php');
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity  = isset($_POST['quantity'])   ? (int) $_POST['quantity']   : 1;
if ($quantity < 1) $quantity = 1;

if ($productId <= 0) {
    setFlashMessage('error', 'Invalid product.');
    redirect('shop.php');
}

try {
    $stmt = $pdo->prepare('SELECT id, name, slug, stock_quantity, is_active FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product || (int) $product['is_active'] !== 1) {
        setFlashMessage('error', 'Product not available.');
        redirect('shop.php');
    }

    if ((int) $product['stock_quantity'] < $quantity) {
        setFlashMessage('error', 'Insufficient stock for this product.');
        redirect('product-detail.php?id=' . $productId);
    }

    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQty = min((int) $existing['quantity'] + $quantity, (int) $product['stock_quantity']);
            $pdo->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$newQty, $existing['id']]);
        } else {
            $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)')
                ->execute([$userId, $productId, $quantity]);
        }
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?');
        $stmt->execute([$sessionId, $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQty = min((int) $existing['quantity'] + $quantity, (int) $product['stock_quantity']);
            $pdo->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$newQty, $existing['id']]);
        } else {
            $pdo->prepare('INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)')
                ->execute([$sessionId, $productId, $quantity]);
        }
    }

    redirect('checkout.php');

} catch (Throwable $e) {
    error_log('Buy Now error: ' . $e->getMessage());
    setFlashMessage('error', 'Could not start checkout. Please try again.');
    redirect('product-detail.php?id=' . $productId);
}
