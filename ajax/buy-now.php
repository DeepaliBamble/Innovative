<?php
/**
 * Buy Now handler — stages a single product for checkout WITHOUT touching
 * the user's existing cart, then forwards to checkout.php. The staged item
 * lives in $_SESSION['buy_now'] and is consumed by checkout.php and
 * ajax/create-order.php. The user's cart is preserved end-to-end.
 */

require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/shop.php');
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Security token expired. Please try again.');
    redirect('/shop.php');
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity  = isset($_POST['quantity'])   ? (int) $_POST['quantity']   : 1;
if ($quantity < 1) $quantity = 1;

if ($productId <= 0) {
    setFlashMessage('error', 'Invalid product.');
    redirect('/shop.php');
}

try {
    $stmt = $pdo->prepare('SELECT id, stock_quantity, is_active FROM products WHERE id = ?');
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product || (int) $product['is_active'] !== 1) {
        setFlashMessage('error', 'Product not available.');
        redirect('/shop.php');
    }

    if ((int) $product['stock_quantity'] < $quantity) {
        setFlashMessage('error', 'Insufficient stock for this product.');
        redirect('/product-detail.php?id=' . $productId);
    }

    // Stage the single-product checkout. Cart table is intentionally untouched.
    $_SESSION['buy_now'] = [
        'product_id' => $productId,
        'quantity'   => $quantity,
        'created_at' => time(),
    ];

    redirect('/checkout.php');

} catch (Throwable $e) {
    error_log('Buy Now error: ' . $e->getMessage());
    setFlashMessage('error', 'Could not start checkout. Please try again.');
    redirect('/product-detail.php?id=' . $productId);
}
