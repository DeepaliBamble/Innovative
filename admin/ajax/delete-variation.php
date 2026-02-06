<?php
/**
 * AJAX Handler - Delete Product Variation
 */

require_once '../../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $variation_id = isset($_POST['variation_id']) ? (int)$_POST['variation_id'] : 0;

    if ($variation_id <= 0) {
        throw new Exception('Invalid variation ID');
    }

    // Fetch variation details to delete associated image file
    $stmt = $pdo->prepare("SELECT image_path FROM product_variations WHERE id = ?");
    $stmt->execute([$variation_id]);
    $variation = $stmt->fetch();

    if (!$variation) {
        throw new Exception('Variation not found');
    }

    // Delete the variation
    $stmt = $pdo->prepare("DELETE FROM product_variations WHERE id = ?");
    $stmt->execute([$variation_id]);

    // Delete associated image file if exists
    if ($variation['image_path'] && file_exists('../../' . $variation['image_path'])) {
        unlink('../../' . $variation['image_path']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Variation deleted successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in delete-variation.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
