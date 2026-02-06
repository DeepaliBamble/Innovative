<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

try {
    // Get product identifier (slug or id)
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$slug && !$id) {
        throw new Exception('Product slug or id is required');
    }

    // Build query based on identifier
    $sql = "SELECT
                p.id,
                p.name,
                p.slug,
                p.sku,
                p.short_desc,
                p.description,
                p.price,
                p.sale_price,
                p.cost_price,
                p.image_path,
                p.stock_quantity,
                p.is_featured,
                p.is_active,
                p.views_count,
                p.created_at,
                p.updated_at,
                c.name as category_name,
                c.slug as category_slug,
                c.id as category_id,
                pc.name as parent_category_name,
                pc.slug as parent_category_slug,
                pc.id as parent_category_id
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN categories pc ON c.parent_id = pc.id
            WHERE p.is_active = 1 AND ";

    if ($slug) {
        $sql .= "p.slug = ?";
        $param = $slug;
        $type = "s";
    } else {
        $sql .= "p.id = ?";
        $param = $id;
        $type = "i";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($type, $param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Product not found'
        ]);
        exit;
    }

    $product = $result->fetch_assoc();
    $product_id = $product['id'];

    // Increment view count
    $updateViewsSql = "UPDATE products SET views_count = views_count + 1 WHERE id = ?";
    $updateStmt = $conn->prepare($updateViewsSql);
    $updateStmt->bind_param("i", $product_id);
    $updateStmt->execute();

    // Get additional images
    $imagesSql = "SELECT id, image_path, is_primary, display_order
                  FROM product_images
                  WHERE product_id = ?
                  ORDER BY display_order ASC, is_primary DESC";
    $imagesStmt = $conn->prepare($imagesSql);
    $imagesStmt->bind_param("i", $product_id);
    $imagesStmt->execute();
    $imagesResult = $imagesStmt->get_result();

    $images = [];
    while ($imgRow = $imagesResult->fetch_assoc()) {
        $images[] = [
            'id' => (int)$imgRow['id'],
            'image_path' => $imgRow['image_path'],
            'is_primary' => (bool)$imgRow['is_primary'],
            'display_order' => (int)$imgRow['display_order']
        ];
    }

    // Get product attributes/specifications
    $attrSql = "SELECT id, attribute_name, attribute_value, display_order
                FROM product_attributes
                WHERE product_id = ?
                ORDER BY display_order ASC";
    $attrStmt = $conn->prepare($attrSql);
    $attrStmt->bind_param("i", $product_id);
    $attrStmt->execute();
    $attrResult = $attrStmt->get_result();

    $attributes = [];
    while ($attrRow = $attrResult->fetch_assoc()) {
        $attributes[] = [
            'id' => (int)$attrRow['id'],
            'name' => $attrRow['attribute_name'],
            'value' => $attrRow['attribute_value'],
            'display_order' => (int)$attrRow['display_order']
        ];
    }

    // Get reviews with user info
    $reviewsSql = "SELECT
                      r.id,
                      r.rating,
                      r.title,
                      r.comment,
                      r.is_approved,
                      r.created_at,
                      u.name as username,
                      u.email
                   FROM reviews r
                   LEFT JOIN users u ON r.user_id = u.id
                   WHERE r.product_id = ? AND r.is_approved = 1
                   ORDER BY r.created_at DESC
                   LIMIT 20";
    $reviewsStmt = $conn->prepare($reviewsSql);
    $reviewsStmt->bind_param("i", $product_id);
    $reviewsStmt->execute();
    $reviewsResult = $reviewsStmt->get_result();

    $reviews = [];
    $totalRating = 0;
    $ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

    while ($reviewRow = $reviewsResult->fetch_assoc()) {
        $rating = (int)$reviewRow['rating'];
        $totalRating += $rating;
        $ratingDistribution[$rating]++;

        $reviews[] = [
            'id' => (int)$reviewRow['id'],
            'rating' => $rating,
            'title' => $reviewRow['title'],
            'comment' => $reviewRow['comment'],
            'username' => $reviewRow['username'] ?? 'Anonymous',
            'created_at' => $reviewRow['created_at']
        ];
    }

    $reviewCount = count($reviews);
    $avgRating = $reviewCount > 0 ? round($totalRating / $reviewCount, 1) : 0;

    // Calculate rating percentages
    foreach ($ratingDistribution as $star => $count) {
        $ratingDistribution[$star] = [
            'count' => $count,
            'percentage' => $reviewCount > 0 ? round(($count / $reviewCount) * 100, 1) : 0
        ];
    }

    // Get related products (same category, excluding current product)
    $relatedSql = "SELECT
                      p.id,
                      p.name,
                      p.slug,
                      p.price,
                      p.sale_price,
                      p.image_path,
                      p.stock_quantity,
                      (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND is_approved = 1) as avg_rating,
                      (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND is_approved = 1) as review_count
                   FROM products p
                   WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
                   ORDER BY p.is_featured DESC, RAND()
                   LIMIT 4";
    $relatedStmt = $conn->prepare($relatedSql);
    $relatedStmt->bind_param("ii", $product['category_id'], $product_id);
    $relatedStmt->execute();
    $relatedResult = $relatedStmt->get_result();

    $relatedProducts = [];
    while ($relatedRow = $relatedResult->fetch_assoc()) {
        $discount_percentage = 0;
        if ($relatedRow['sale_price'] && $relatedRow['sale_price'] < $relatedRow['price']) {
            $discount_percentage = round((($relatedRow['price'] - $relatedRow['sale_price']) / $relatedRow['price']) * 100);
        }

        $relatedProducts[] = [
            'id' => (int)$relatedRow['id'],
            'name' => $relatedRow['name'],
            'slug' => $relatedRow['slug'],
            'price' => (float)$relatedRow['price'],
            'sale_price' => $relatedRow['sale_price'] ? (float)$relatedRow['sale_price'] : null,
            'final_price' => $relatedRow['sale_price'] ? (float)$relatedRow['sale_price'] : (float)$relatedRow['price'],
            'discount_percentage' => $discount_percentage,
            'image_path' => $relatedRow['image_path'],
            'stock_quantity' => (int)$relatedRow['stock_quantity'],
            'avg_rating' => $relatedRow['avg_rating'] ? round((float)$relatedRow['avg_rating'], 1) : 0,
            'review_count' => (int)$relatedRow['review_count']
        ];
    }

    // Calculate discount percentage
    $discount_percentage = 0;
    if ($product['sale_price'] && $product['sale_price'] < $product['price']) {
        $discount_percentage = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
    }

    // Check stock status
    $stock_status = $product['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';
    if ($product['stock_quantity'] > 0 && $product['stock_quantity'] <= 5) {
        $stock_status = 'low_stock';
    }

    // Build response
    $response = [
        'success' => true,
        'data' => [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'slug' => $product['slug'],
            'sku' => $product['sku'],
            'short_desc' => $product['short_desc'],
            'description' => $product['description'],
            'price' => (float)$product['price'],
            'sale_price' => $product['sale_price'] ? (float)$product['sale_price'] : null,
            'final_price' => $product['sale_price'] ? (float)$product['sale_price'] : (float)$product['price'],
            'discount_percentage' => $discount_percentage,
            'image_path' => $product['image_path'],
            'images' => $images,
            'stock_quantity' => (int)$product['stock_quantity'],
            'stock_status' => $stock_status,
            'is_featured' => (bool)$product['is_featured'],
            'views_count' => (int)$product['views_count'] + 1, // Include the incremented count
            'category' => [
                'id' => (int)$product['category_id'],
                'name' => $product['category_name'],
                'slug' => $product['category_slug']
            ],
            'parent_category' => $product['parent_category_id'] ? [
                'id' => (int)$product['parent_category_id'],
                'name' => $product['parent_category_name'],
                'slug' => $product['parent_category_slug']
            ] : null,
            'attributes' => $attributes,
            'reviews' => [
                'items' => $reviews,
                'count' => $reviewCount,
                'avg_rating' => $avgRating,
                'rating_distribution' => $ratingDistribution
            ],
            'related_products' => $relatedProducts,
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at']
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching product details',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
