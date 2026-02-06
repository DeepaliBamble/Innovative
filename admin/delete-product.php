<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) { redirect('login.php'); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid product ID';
    redirect('products.php');
}

try {
    // Check if product exists first
    $checkStmt = $pdo->prepare('SELECT name, image_path FROM products WHERE id = ?');
    $checkStmt->execute([$id]);
    $product = $checkStmt->fetch();

    if (!$product) {
        $_SESSION['error_message'] = 'Product not found';
        redirect('products.php');
    }

    // Get additional images for potential cleanup
    $imagesStmt = $pdo->prepare('SELECT image_path FROM product_images WHERE product_id = ?');
    $imagesStmt->execute([$id]);
    $additionalImages = $imagesStmt->fetchAll(PDO::FETCH_COLUMN);

    // Delete the product (cascades will handle related records)
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = 'Product "' . htmlspecialchars($product['name']) . '" has been deleted successfully';
    } else {
        $_SESSION['error_message'] = 'Failed to delete product';
    }
} catch (PDOException $e) {
    error_log('Error deleting product: ' . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred while deleting the product';
}

redirect('products.php');
