<?php
/**
 * Get Wishlist Count AJAX Handler
 * Returns the current wishlist item count
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

try {
    if (!function_exists('getWishlistCount')) {
        require_once __DIR__ . '/../includes/functions.php';
    }

    $count = getWishlistCount($pdo);

    echo json_encode([
        'success' => true,
        'count' => (int)$count
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get wishlist count'
    ]);
}
