<?php
/**
 * Common Helper Functions
 * Reusable functions for the entire application
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a slug from text
 */
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Format price with currency symbol
 */
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current admin ID
 */
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Generate URL with base path
 * Prepends BASE_URL_PATH for root-relative URLs
 * Leaves absolute URLs (starting with http:// or https://) unchanged
 */
function url($path = '') {
    // If it's already an absolute URL, return as-is
    if (preg_match('/^https?:\/\//', $path)) {
        return $path;
    }
    
    // If it's a root-relative URL and BASE_URL_PATH is set, prepend it
    if (strpos($path, '/') === 0 && defined('BASE_URL_PATH') && BASE_URL_PATH !== '') {
        return BASE_URL_PATH . $path;
    }
    
    return $path;
}

/**
 * Redirect to a URL
 * Handles both absolute URLs and relative paths
 */
function redirect($path) {
    $redirectUrl = url($path);
    header("Location: " . $redirectUrl);
    exit;
}

/**
 * Flash message - Set
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Flash message - Get and clear
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        $class = $alertClass[$flash['type']] ?? 'alert-info';
        return '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">'
            . htmlspecialchars($flash['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
            . '</div>';
    }
    return '';
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using bcrypt
 * Uses PHP's password_hash() with default algorithm (currently bcrypt)
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * Uses PHP's password_verify() for secure comparison
 */
function verifyPassword($password, $storedHash) {
    return password_verify($password, $storedHash);
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get user IP address
 */
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Upload file
 */
function uploadFile($file, $targetDir = 'uploads/products') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload failed'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }

    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = BASE_PATH . '/' . $targetDir;

    // Create directory if not exists
    if (!is_dir($targetPath)) {
        mkdir($targetPath, 0755, true);
    }

    $fullPath = $targetPath . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        return ['success' => true, 'path' => $targetDir . '/' . $filename];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Pagination helper
 */
function paginate($totalItems, $itemsPerPage, $currentPage = 1) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;

    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Get cart item count
 */
function getCartCount($pdo) {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart WHERE user_id = ?');
        $stmt->execute([getCurrentUserId()]);
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart WHERE session_id = ?');
        $stmt->execute([$sessionId]);
    }
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Calculate order total
 */
function calculateOrderTotal($subtotal, $tax = 0, $shipping = 0, $discount = 0) {
    return $subtotal + $tax + $shipping - $discount;
}

/**
 * Generate order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Get wishlist item count for current user
 */
function getWishlistCount($pdo) {
    if (!isLoggedIn()) {
        return 0;
    }

    $userId = getCurrentUserId();
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?');
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}
