<?php
/**
 * AJAX Handler - Remove Item from Wishlist
 * Removes a product from user's wishlist
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to manage your wishlist.'
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get wishlist ID
$wishlistId = isset($_POST['wishlist_id']) ? intval($_POST['wishlist_id']) : 0;

if ($wishlistId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid wishlist item.'
    ]);
    exit;
}

$userId = getCurrentUserId();

try {
    // Verify that this wishlist item belongs to the current user
    $stmt = $pdo->prepare('SELECT id FROM wishlist WHERE id = ? AND user_id = ?');
    $stmt->execute([$wishlistId, $userId]);
    $wishlistItem = $stmt->fetch();

    if (!$wishlistItem) {
        echo json_encode([
            'success' => false,
            'message' => 'Wishlist item not found or access denied.'
        ]);
        exit;
    }

    // Delete the wishlist item
    $stmt = $pdo->prepare('DELETE FROM wishlist WHERE id = ? AND user_id = ?');
    $stmt->execute([$wishlistId, $userId]);

    // Get updated wishlist count
    $wishlistCount = getWishlistCount($pdo);

    echo json_encode([
        'success' => true,
        'message' => 'Item removed from wishlist successfully.',
        'wishlist_count' => $wishlistCount
    ]);

} catch (PDOException $e) {
    error_log('Wishlist removal error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
