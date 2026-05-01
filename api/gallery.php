<?php
/**
 * Gallery API
 * Returns gallery images based on category filter
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

try {
    // Get filter parameter (default: 'all')
    $category = isset($_GET['category']) ? $_GET['category'] : 'all';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    $columnStmt = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'category_id'");
    $galleryHasCategoryId = (bool) $columnStmt->fetch();

    if ($galleryHasCategoryId) {
        if ($category === 'all') {
            $sql = "SELECT g.id, g.title, g.description, g.image_path, g.category_id, g.display_order, c.name as category_name, c.slug as category_slug
                    FROM gallery g
                    LEFT JOIN categories c ON g.category_id = c.id
                    WHERE g.is_active = 1
                    ORDER BY g.display_order ASC, g.created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
        } else {
            $catStmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND parent_id IS NULL LIMIT 1");
            $catStmt->execute([$category]);
            $catRow = $catStmt->fetch();
            $catId = $catRow ? (int)$catRow['id'] : null;
            if ($catId) {
                $sql = "SELECT g.id, g.title, g.description, g.image_path, g.category_id, g.display_order, c.name as category_name, c.slug as category_slug
                        FROM gallery g
                        LEFT JOIN categories c ON g.category_id = c.id
                        WHERE g.is_active = 1 AND g.category_id = :category_id
                        ORDER BY g.display_order ASC, g.created_at DESC
                        LIMIT :limit OFFSET :offset";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':category_id', $catId, PDO::PARAM_INT);
            } else {
                echo json_encode([
                    'success' => true,
                    'data' => [],
                    'total' => 0,
                    'category' => $category,
                    'limit' => $limit,
                    'offset' => $offset
                ], JSON_PRETTY_PRINT);
                exit;
            }
        }
    } else {
        $validCategories = ['all', 'sofas', 'seating', 'dining', 'bedroom', 'decor', 'workspace'];
        if (!in_array($category, $validCategories, true)) {
            $category = 'all';
        }

        if ($category === 'all') {
            $sql = "SELECT g.id, g.title, g.description, g.image_path, g.category, g.display_order,
                           g.category as category_name, g.category as category_slug
                    FROM gallery g
                    WHERE g.is_active = 1
                    ORDER BY g.display_order ASC, g.created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
        } else {
            $sql = "SELECT g.id, g.title, g.description, g.image_path, g.category, g.display_order,
                           g.category as category_name, g.category as category_slug
                    FROM gallery g
                    WHERE g.is_active = 1 AND g.category = :category
                    ORDER BY g.display_order ASC, g.created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        }
    }

    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    if ($galleryHasCategoryId && $category === 'all') {
        $countSql = "SELECT COUNT(*) as total FROM gallery g WHERE g.is_active = 1";
        $countStmt = $pdo->prepare($countSql);
    } elseif ($galleryHasCategoryId) {
        $countSql = "SELECT COUNT(*) as total FROM gallery g LEFT JOIN categories c ON g.category_id = c.id WHERE g.is_active = 1 AND g.category_id = :category_id";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->bindParam(':category_id', $catId, PDO::PARAM_INT);
    } elseif ($category === 'all') {
        $countSql = "SELECT COUNT(*) as total FROM gallery g WHERE g.is_active = 1";
        $countStmt = $pdo->prepare($countSql);
    } else {
        $countSql = "SELECT COUNT(*) as total FROM gallery g WHERE g.is_active = 1 AND g.category = :category";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->bindParam(':category', $category, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => $images,
        'total' => $totalCount,
        'category' => $category,
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
