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

    // Optional photo uploads (max 4 images, 5 MB each, JPG/PNG/WEBP)
    $MAX_REVIEW_IMAGES = 4;
    $MAX_IMG_SIZE = 5 * 1024 * 1024;
    $ALLOWED_IMG = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $savedRel = [];
    $savedAbs = [];

    if (!empty($_FILES['review_images']) && is_array($_FILES['review_images']['name'])) {
        $files = [];
        foreach ($_FILES['review_images']['name'] as $i => $name) {
            if (($_FILES['review_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'tmp_name' => $_FILES['review_images']['tmp_name'][$i] ?? '',
                'error'    => $_FILES['review_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $_FILES['review_images']['size'][$i] ?? 0,
            ];
        }

        if (count($files) > $MAX_REVIEW_IMAGES) {
            echo json_encode(['success' => false, 'message' => "You can upload up to {$MAX_REVIEW_IMAGES} photos."]);
            exit;
        }

        if ($files) {
            $uploadDir = __DIR__ . '/../uploads/reviews';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                error_log('Review upload dir not writable: ' . $uploadDir);
                echo json_encode(['success' => false, 'message' => 'Unable to store the uploaded photos. Please try again.']);
                exit;
            }
            $htaccess = $uploadDir . '/.htaccess';
            if (!file_exists($htaccess)) {
                @file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phps\nRemoveType .php .phtml .php3 .php4 .php5 .php7 .phps\n");
            }
            $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;

            foreach ($files as $file) {
                $abort = function ($msg) use (&$savedAbs, $finfo) {
                    foreach ($savedAbs as $p) {
                        @unlink($p);
                    }
                    if ($finfo) {
                        finfo_close($finfo);
                    }
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit;
                };
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $abort('One of the photos could not be uploaded. Please try again.');
                }
                if ($file['size'] <= 0 || $file['size'] > $MAX_IMG_SIZE) {
                    $abort('Each photo must be 5 MB or smaller.');
                }
                if (!is_uploaded_file($file['tmp_name'])) {
                    $abort('Invalid file upload.');
                }
                $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
                if (!isset($ALLOWED_IMG[$mime])) {
                    $abort('Only JPG, PNG and WEBP images are allowed.');
                }
                $ext = $ALLOWED_IMG[$mime];
                $basename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                $destAbs = $uploadDir . '/' . $basename;
                if (!move_uploaded_file($file['tmp_name'], $destAbs)) {
                    $abort('Failed to save a photo. Please try again.');
                }
                @chmod($destAbs, 0644);
                $savedAbs[] = $destAbs;
                $savedRel[] = 'uploads/reviews/' . $basename;
            }
            if ($finfo) {
                finfo_close($finfo);
            }
        }
    }

    $imagesJson = !empty($savedRel) ? json_encode($savedRel) : null;

    $stmt = $pdo->prepare("
        INSERT INTO reviews (product_id, user_id, rating, title, comment, images, is_approved, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    $stmt->execute([$productId, $userId, $rating, ($title !== '' ? $title : null), $comment, $imagesJson]);

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
