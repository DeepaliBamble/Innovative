<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
$total = 0;
try {
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare("SELECT p.price, p.sale_price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? AND p.is_active = 1");
        $stmt->execute([$userId]);
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare("SELECT p.price, p.sale_price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.session_id = ? AND p.is_active = 1");
        $stmt->execute([$sessionId]);
    }
    $items = $stmt->fetchAll();
    foreach ($items as $item) {
        $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
        $total += $price * $item['quantity'];
    }
    echo json_encode(['success' => true, 'total' => $total]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'total' => 0]);
}
