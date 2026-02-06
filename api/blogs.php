<?php
/**
 * Blogs API
 * Returns blog posts with pagination
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

try {
    // Get parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 9;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : null;

    // Build SQL query
    $sql = "SELECT id, title, slug, excerpt, featured_image, author_name, category,
                   tags, views, published_at, created_at
            FROM blogs
            WHERE is_published = 1";

    $params = [];

    if ($category) {
        $sql .= " AND category = :category";
        $params[':category'] = $category;
    }

    if ($featured !== null) {
        $sql .= " AND is_featured = :featured";
        $params[':featured'] = $featured ? 1 : 0;
    }

    $sql .= " ORDER BY published_at DESC, created_at DESC
              LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM blogs WHERE is_published = 1";
    if ($category) {
        $countSql .= " AND category = :category";
    }
    if ($featured !== null) {
        $countSql .= " AND is_featured = :featured";
    }

    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        if ($key !== ':limit' && $key !== ':offset') {
            $countStmt->bindValue($key, $value);
        }
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => $blogs,
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
