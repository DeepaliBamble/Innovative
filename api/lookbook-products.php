<?php
/**
 * API: Get Shop This Look products
 * Returns featured/lookbook products for the homepage
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    // Get lookbook/featured products (you can customize this query)
    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.name,
            p.slug,
            p.price,
            p.sale_price,
            p.image_path,
            p.stock_quantity,
            c.name as category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, display_order ASC LIMIT 1 OFFSET 1) as hover_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND p.is_featured = 1
        ORDER BY RAND()
        LIMIT 4
    ");

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format products for response
    $formattedProducts = [];
    foreach ($products as $product) {
        $formattedProducts[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'category' => $product['category_name'] ?? 'Product',
            'price' => floatval($product['price']),
            'sale_price' => $product['sale_price'] ? floatval($product['sale_price']) : null,
            'display_price' => $product['sale_price'] ? floatval($product['sale_price']) : floatval($product['price']),
            'old_price' => $product['sale_price'] ? floatval($product['price']) : null,
            'image_path' => $product['image_path'] ?? 'images/category/cate-18.jpg',
            'hover_image' => $product['hover_image'] ?? $product['image_path'] ?? 'images/category/cate-19.jpg',
            'in_stock' => $product['stock_quantity'] > 0,
            'stock_quantity' => intval($product['stock_quantity'])
        ];
    }

    echo json_encode([
        'success' => true,
        'products' => $formattedProducts
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ]);
}
