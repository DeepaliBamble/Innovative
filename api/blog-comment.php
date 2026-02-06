<?php
/**
 * Blog Comment API
 * Handles comment submission for blog posts
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // If not JSON, try regular POST
    if (!$data) {
        $data = $_POST;
    }

    // Validate required fields
    $blog_id = isset($data['blog_id']) ? (int)$data['blog_id'] : 0;
    $author_name = isset($data['author_name']) ? trim($data['author_name']) : '';
    $author_email = isset($data['author_email']) ? trim($data['author_email']) : '';
    $author_phone = isset($data['author_phone']) ? trim($data['author_phone']) : '';
    $comment = isset($data['comment']) ? trim($data['comment']) : '';
    $rating = isset($data['rating']) ? (int)$data['rating'] : null;

    // Validation
    if (!$blog_id) {
        throw new Exception('Blog ID is required');
    }

    if (empty($author_name)) {
        throw new Exception('Name is required');
    }

    if (empty($author_email)) {
        throw new Exception('Email is required');
    }

    if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    if (empty($comment)) {
        throw new Exception('Comment is required');
    }

    if (strlen($comment) > 2000) {
        throw new Exception('Comment is too long (max 2000 characters)');
    }

    if ($rating !== null && ($rating < 1 || $rating > 5)) {
        $rating = null;
    }

    // Verify blog exists and is published
    $blogStmt = $pdo->prepare("SELECT id FROM blogs WHERE id = ? AND is_published = 1");
    $blogStmt->execute([$blog_id]);
    if (!$blogStmt->fetch()) {
        throw new Exception('Blog post not found');
    }

    // Insert comment (pending approval by default)
    $stmt = $pdo->prepare("INSERT INTO blog_comments (blog_id, author_name, author_email, author_phone, comment, rating, is_approved, created_at)
                           VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
    $stmt->execute([
        $blog_id,
        htmlspecialchars($author_name, ENT_QUOTES, 'UTF-8'),
        $author_email,
        $author_phone,
        htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'),
        $rating
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Your comment has been submitted and is pending approval. Thank you!'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
