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
 */
function url($path = '') {
    if (strpos($path, '/') === 0 && defined('BASE_URL_PATH') && BASE_URL_PATH !== '') {
        return BASE_URL_PATH . $path;
    }
    return $path;
}

/**
 * Redirect to a URL
 * Automatically prepends BASE_URL_PATH for root-relative URLs
 */
function redirect($path) {
    header("Location: " . url($path));
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
    return password_verify($password, $storedHash) || $password === $storedHash;
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get user IP address
 * Uses only REMOTE_ADDR to prevent spoofing via HTTP headers.
 */
function getUserIP() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
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

/**
 * Store a remember-me token in the database and set the cookie.
 * @param PDO $pdo
 * @param int $userId
 */
function storeRememberToken($pdo, $userId) {
    $token  = generateRandomString(64);
    $hash   = hash('sha256', $token);
    $expiry = time() + (30 * 24 * 60 * 60); // 30 days

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $hash, date('Y-m-d H:i:s', $expiry)]);

        setcookie('remember_token', $token, [
            'expires'  => $expiry,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    } catch (Exception $e) {
        error_log('Remember token store error: ' . $e->getMessage());
    }
}

/**
 * Delete a remember-me token from the database and clear the cookie.
 * @param PDO $pdo
 */
function clearRememberToken($pdo) {
    $token = $_COOKIE['remember_token'] ?? '';
    if ($token) {
        $hash = hash('sha256', $token);
        try {
            $stmt = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
            $stmt->execute([$hash]);
        } catch (Exception $e) {
            error_log('Remember token clear error: ' . $e->getMessage());
        }
        setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }
}

/**
 * Auto-login from a remember-me cookie if the user is not already logged in.
 * @param PDO $pdo
 */
function autoLoginFromCookie($pdo) {
    if (isLoggedIn()) return;

    $token = $_COOKIE['remember_token'] ?? '';
    if (empty($token)) return;

    $hash = hash('sha256', $token);

    try {
        $stmt = $pdo->prepare(
            'SELECT rt.user_id, u.name, u.email, u.is_admin, u.is_active
             FROM remember_tokens rt
             INNER JOIN users u ON rt.user_id = u.id
             WHERE rt.token_hash = ? AND rt.expires_at > NOW()'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch();

        if (!$row || $row['is_active'] != 1) {
            clearRememberToken($pdo);
            return;
        }

        // Restore session
        $_SESSION['user_id']      = $row['user_id'];
        $_SESSION['user_name']    = $row['name'];
        $_SESSION['user_email']   = $row['email'];
        $_SESSION['user_is_admin'] = $row['is_admin'];
        $_SESSION['logged_in']    = true;
        $_SESSION['login_time']   = time();

        // Rotate token for security
        $stmt = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
        $stmt->execute([$hash]);
        storeRememberToken($pdo, $row['user_id']);

    } catch (Exception $e) {
        error_log('Auto-login error: ' . $e->getMessage());
    }
}
