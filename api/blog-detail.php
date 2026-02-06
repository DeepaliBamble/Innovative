<?php
/**
 * Blog Detail API
 * Returns single blog post with comments
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

try {
    $slug = isset($_GET['slug']) ? $_GET['slug'] : null;
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$slug && !$id) {
        throw new Exception('Blog slug or ID is required');
    }

    // Get blog post
    if ($slug) {
        $sql = "SELECT * FROM blogs WHERE slug = :slug AND is_published = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    } else {
        $sql = "SELECT * FROM blogs WHERE id = :id AND is_published = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$blog) {
        throw new Exception('Blog post not found');
    }

    // Increment view count
    $updateViewsSql = "UPDATE blogs SET views = views + 1 WHERE id = :id";
    $updateStmt = $pdo->prepare($updateViewsSql);
    $updateStmt->bindParam(':id', $blog['id'], PDO::PARAM_INT);
    $updateStmt->execute();

    // Get comments
    $commentsSql = "SELECT * FROM blog_comments
                    WHERE blog_id = :blog_id AND is_approved = 1
                    ORDER BY created_at DESC";
    $commentsStmt = $pdo->prepare($commentsSql);
    $commentsStmt->bindParam(':blog_id', $blog['id'], PDO::PARAM_INT);
    $commentsStmt->execute();
    $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get related posts (same category)
    $relatedSql = "SELECT id, title, slug, excerpt, featured_image, author_name, published_at
                   FROM blogs
                   WHERE category = :category AND id != :id AND is_published = 1
                   ORDER BY published_at DESC
                   LIMIT 3";
    $relatedStmt = $pdo->prepare($relatedSql);
    $relatedStmt->bindParam(':category', $blog['category'], PDO::PARAM_STR);
    $relatedStmt->bindParam(':id', $blog['id'], PDO::PARAM_INT);
    $relatedStmt->execute();
    $relatedPosts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    echo json_encode([
        'success' => true,
        'blog' => $blog,
        'comments' => $comments,
        'related_posts' => $relatedPosts,
        'comments_count' => count($comments)
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
