<?php
/**
 * AJAX Handler - Submit a product review.
 * Only a logged-in customer who has a DELIVERED order containing the product
 * may review it (verified purchase). Reviews are held for admin approval.
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// CSRF (token in POST or X-CSRF-Token header)
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to write a review.']);
    exit;
}

// Rate limit: 5 review submissions per hour per session
if (!checkRateLimit($pdo, 'submit_review', 5, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Please try again later.']);
    exit;
}

$userId    = getCurrentUserId();
$productId = (int) ($_POST['product_id'] ?? 0);
$rating    = (int) ($_POST['rating'] ?? 0);
$title     = sanitize($_POST['title'] ?? '');
$comment   = sanitize($_POST['comment'] ?? '');

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5 stars.']);
    exit;
}
if ($comment === '') {
    echo json_encode(['success' => false, 'message' => 'Please write a few words about the product.']);
    exit;
}
if (mb_strlen($title) > 255) {
    $title = mb_substr($title, 0, 255);
}

try {
    // Verified-purchase gate: must have received (delivered) this product.
    if (!userHasReceivedProduct($pdo, $userId, $productId)) {
        echo json_encode(['success' => false, 'message' => 'You can review this product only after your order for it has been delivered.']);
        exit;
    }

    // One review per user per product.
    if (getUserReview($pdo, $userId, $productId)) {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO reviews (product_id, user_id, rating, title, comment, is_approved, created_at)
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$productId, $userId, $rating, ($title !== '' ? $title : null), $comment]);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your review has been submitted and will appear once approved.'
    ]);
} catch (PDOException $e) {
    // Duplicate review (unique user_id+product_id) or other DB error
    if ($e->getCode() === '23000') {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
        exit;
    }
    error_log('submit-review error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
