<?php
/**
 * AJAX Handler - Save Product Variation
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

    // Validate required fields
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $variation_type_id = isset($_POST['variation_type_id']) ? (int)$_POST['variation_type_id'] : 0;
    $variation_name = trim($_POST['variation_name'] ?? '');
    $variation_value = trim($_POST['variation_value'] ?? '');
    $input_type = trim($_POST['input_type'] ?? '');

    if ($product_id <= 0 || $variation_type_id <= 0 || empty($variation_name) || empty($variation_value)) {
        throw new Exception('Missing required fields');
    }

    // Optional fields
    $color_code = null;
    $image_path = null;
    $price_adjustment = isset($_POST['price_adjustment']) ? (float)$_POST['price_adjustment'] : 0.00;
    $stock_quantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
    $sku_suffix = trim($_POST['sku_suffix'] ?? '');
    $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;

    // Handle color code for color swatches
    if ($input_type === 'color') {
        $color_code = isset($_POST['color_code']) ? trim($_POST['color_code']) : null;
        if (empty($color_code)) {
            throw new Exception('Color code is required for color variations');
        }
        // Validate hex color format
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color_code)) {
            throw new Exception('Invalid color code format');
        }
    }

    // Handle image upload for material/pattern swatches
    if ($input_type === 'image' && isset($_FILES['variation_image']) && $_FILES['variation_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/variations/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file = $_FILES['variation_image'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('var_') . '.' . $extension;
        $upload_path = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload image');
        }

        $image_path = 'uploads/variations/' . $filename;
    }

    // Insert variation into database
    $stmt = $pdo->prepare("
        INSERT INTO product_variations (
            product_id,
            variation_type_id,
            variation_name,
            variation_value,
            color_code,
            image_path,
            price_adjustment,
            stock_quantity,
            sku_suffix,
            display_order,
            is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $product_id,
        $variation_type_id,
        $variation_name,
        $variation_value,
        $color_code,
        $image_path,
        $price_adjustment,
        $stock_quantity,
        $sku_suffix,
        $display_order
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Variation added successfully',
        'variation_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    error_log("Error in save-variation.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
