<?php
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json');
$count = 0;
try {
    if (!function_exists('getCartCount')) {
        require_once __DIR__ . '/../includes/functions.php';
    }
    global $pdo;
    $count = (int)getCartCount($pdo);
    echo json_encode(['success' => true, 'count' => $count]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'count' => 0]);
}
