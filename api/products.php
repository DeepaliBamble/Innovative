<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

try {
    // Get parameters
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $category_slug = isset($_GET['category']) ? trim($_GET['category']) : null;
    $featured = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : false;
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'latest';
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
    $in_stock = isset($_GET['in_stock']) ? filter_var($_GET['in_stock'], FILTER_VALIDATE_BOOLEAN) : false;

    // Build WHERE clause
    $where = ["p.is_active = 1"];
    $params = [];
    $types = "";

    // Category filter (by ID or slug)
    if ($category_id) {
        $where[] = "p.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    } elseif ($category_slug) {
        $where[] = "c.slug = ?";
        $params[] = $category_slug;
        $types .= "s";
    }

    // Featured filter
    if ($featured) {
        $where[] = "p.is_featured = 1";
    }

    // Search filter
    if ($search) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.short_desc LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    // Price range filter
    if ($min_price !== null) {
        $where[] = "COALESCE(p.sale_price, p.price) >= ?";
        $params[] = $min_price;
        $types .= "d";
    }
    if ($max_price !== null) {
        $where[] = "COALESCE(p.sale_price, p.price) <= ?";
        $params[] = $max_price;
        $types .= "d";
    }

    // Stock filter
    if ($in_stock) {
        $where[] = "p.stock_quantity > 0";
    }

    $whereClause = implode(" AND ", $where);

    // Determine sorting
    $orderBy = "";
    switch ($sort) {
        case 'price_low':
            $orderBy = "COALESCE(p.sale_price, p.price) ASC";
            break;
        case 'price_high':
            $orderBy = "COALESCE(p.sale_price, p.price) DESC";
            break;
        case 'name_asc':
            $orderBy = "p.name ASC";
            break;
        case 'name_desc':
            $orderBy = "p.name DESC";
            break;
        case 'popular':
            $orderBy = "p.views_count DESC";
            break;
        case 'latest':
        default:
            $orderBy = "p.created_at DESC";
            break;
    }

    // Get total count
    $countSql = "SELECT COUNT(DISTINCT p.id) as total
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE {$whereClause}";

    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalResult = $countStmt->get_result();
    $total = $totalResult->fetch_assoc()['total'];

    // Get products with category info
    $sql = "SELECT
                p.id,
                p.name,
                p.slug,
                p.sku,
                p.short_desc,
                p.description,
                p.price,
                p.sale_price,
                p.image_path,
                p.stock_quantity,
                p.is_featured,
                p.views_count,
                p.created_at,
                p.updated_at,
                c.name as category_name,
                c.slug as category_slug,
                c.id as category_id,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND is_approved = 1) as review_count,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND is_approved = 1) as avg_rating
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    // Add limit and offset to params
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Calculate discount percentage
        $discount_percentage = 0;
        if ($row['sale_price'] && $row['sale_price'] < $row['price']) {
            $discount_percentage = round((($row['price'] - $row['sale_price']) / $row['price']) * 100);
        }

        // Check stock status
        $stock_status = $row['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock';
        if ($row['stock_quantity'] > 0 && $row['stock_quantity'] <= 5) {
            $stock_status = 'low_stock';
        }

        // Get additional images
        $imagesSql = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY display_order ASC";
        $imagesStmt = $conn->prepare($imagesSql);
        $imagesStmt->bind_param("i", $row['id']);
        $imagesStmt->execute();
        $imagesResult = $imagesStmt->get_result();

        $additional_images = [];
        while ($imgRow = $imagesResult->fetch_assoc()) {
            $additional_images[] = $imgRow['image_path'];
        }

        $products[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'sku' => $row['sku'],
            'short_desc' => $row['short_desc'],
            'description' => $row['description'],
            'price' => (float)$row['price'],
            'sale_price' => $row['sale_price'] ? (float)$row['sale_price'] : null,
            'final_price' => $row['sale_price'] ? (float)$row['sale_price'] : (float)$row['price'],
            'discount_percentage' => $discount_percentage,
            'image_path' => $row['image_path'],
            'additional_images' => $additional_images,
            'stock_quantity' => (int)$row['stock_quantity'],
            'stock_status' => $stock_status,
            'is_featured' => (bool)$row['is_featured'],
            'views_count' => (int)$row['views_count'],
            'category' => [
                'id' => (int)$row['category_id'],
                'name' => $row['category_name'],
                'slug' => $row['category_slug']
            ],
            'review_count' => (int)$row['review_count'],
            'avg_rating' => $row['avg_rating'] ? round((float)$row['avg_rating'], 1) : 0,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    // Success response
    echo json_encode([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'current_page' => floor($offset / $limit) + 1,
            'total_pages' => ceil($total / $limit),
            'has_more' => ($offset + $limit) < $total
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching products',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
