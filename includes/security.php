<?php
/**
 * Security Helper Functions
 * CSRF protection, rate limiting, input validation, and XSS prevention
 */

// =============================================
// CSRF PROTECTION
// =============================================

/**
 * Generate a CSRF token and store it in the session
 * @return string The generated CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > 3600) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token from a form submission
 * @param string $token The token to validate
 * @return bool Whether the token is valid
 */
function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output a hidden CSRF token field for forms
 * @return string HTML hidden input
 */
function csrfTokenField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verify CSRF token from POST request, die if invalid
 */
function verifyCsrfOrDie() {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.']);
        } else {
            setFlashMessage('error', 'Security token expired. Please try again.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        }
        exit;
    }
}

/**
 * Check if current request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// =============================================
// RATE LIMITING
// =============================================

/**
 * Check and enforce rate limiting
 * @param PDO $pdo Database connection
 * @param string $action The action being rate-limited (e.g., 'login', 'register')
 * @param int $maxAttempts Maximum attempts allowed in the window
 * @param int $windowSeconds Time window in seconds
 * @return bool True if the request is allowed, false if rate-limited
 */
function checkRateLimit($pdo, $action, $maxAttempts = 5, $windowSeconds = 900) {
    $ip = getUserIP();
    
    try {
        // Check if IP is currently blocked
        $stmt = $pdo->prepare("
            SELECT attempts, first_attempt_at, blocked_until 
            FROM rate_limiting 
            WHERE ip_address = ? AND action = ?
        ");
        $stmt->execute([$ip, $action]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            // Check if blocked
            if ($record['blocked_until'] && strtotime($record['blocked_until']) > time()) {
                return false;
            }
            
            // Check if window has expired, reset if so
            $windowStart = strtotime($record['first_attempt_at']);
            if ((time() - $windowStart) > $windowSeconds) {
                $stmt = $pdo->prepare("
                    UPDATE rate_limiting 
                    SET attempts = 1, first_attempt_at = NOW(), blocked_until = NULL 
                    WHERE ip_address = ? AND action = ?
                ");
                $stmt->execute([$ip, $action]);
                return true;
            }
            
            // Increment attempts
            if ($record['attempts'] >= $maxAttempts) {
                // Block for the remaining window
                $blockUntil = date('Y-m-d H:i:s', $windowStart + $windowSeconds);
                $stmt = $pdo->prepare("
                    UPDATE rate_limiting 
                    SET attempts = attempts + 1, blocked_until = ? 
                    WHERE ip_address = ? AND action = ?
                ");
                $stmt->execute([$blockUntil, $ip, $action]);
                return false;
            }
            
            $stmt = $pdo->prepare("
                UPDATE rate_limiting 
                SET attempts = attempts + 1 
                WHERE ip_address = ? AND action = ?
            ");
            $stmt->execute([$ip, $action]);
            return true;
        }
        
        // First attempt - create record
        $stmt = $pdo->prepare("
            INSERT INTO rate_limiting (ip_address, action, attempts, first_attempt_at) 
            VALUES (?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE attempts = 1, first_attempt_at = NOW(), blocked_until = NULL
        ");
        $stmt->execute([$ip, $action]);
        return true;
        
    } catch (PDOException $e) {
        // If rate limiting table doesn't exist, allow the request
        error_log('Rate limiting error: ' . $e->getMessage());
        return true;
    }
}

/**
 * Reset rate limit for an IP/action after successful action (e.g., successful login)
 */
function resetRateLimit($pdo, $action) {
    $ip = getUserIP();
    try {
        $stmt = $pdo->prepare("DELETE FROM rate_limiting WHERE ip_address = ? AND action = ?");
        $stmt->execute([$ip, $action]);
    } catch (PDOException $e) {
        error_log('Rate limit reset error: ' . $e->getMessage());
    }
}

// =============================================
// INPUT VALIDATION & SANITIZATION
// =============================================

/**
 * Validate and sanitize an email address
 * @param string $email
 * @return string|false Sanitized email or false if invalid
 */
function validateEmail($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Validate a phone number (Indian format)
 * @param string $phone
 * @return bool
 */
function validatePhone($phone) {
    // Allows +91, 91, 0 prefixes followed by 10 digits
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
    return preg_match('/^(?:\+?91|0)?[6-9]\d{9}$/', $cleaned);
}

/**
 * Validate a postal code (Indian PIN code)
 * @param string $code
 * @return bool
 */
function validatePostalCode($code) {
    $cleaned = preg_replace('/\s/', '', $code);
    return preg_match('/^[1-9][0-9]{5}$/', $cleaned);
}

/**
 * Sanitize output for HTML context
 * @param string $data
 * @return string
 */
function escapeHtml($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Validate that a required string field is not empty and within length limits
 * @param string $value
 * @param int $minLength
 * @param int $maxLength
 * @return bool
 */
function validateStringLength($value, $minLength = 1, $maxLength = 255) {
    $len = mb_strlen(trim($value));
    return $len >= $minLength && $len <= $maxLength;
}

// =============================================
// SECURITY HEADERS
// =============================================

/**
 * Set security-related HTTP headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions policy
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    
    // Content Security Policy (relaxed for inline scripts/styles)
    // Note: Tighten this in production by removing 'unsafe-inline' when possible
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://checkout.razorpay.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https://api.razorpay.com https://lumberjack.razorpay.com; frame-src https://api.razorpay.com https://checkout.razorpay.com;");
}

// =============================================
// SECURE ORDER TOKEN
// =============================================

/**
 * Generate a secure token for order access (instead of using numeric IDs in URLs)
 * @param int $orderId
 * @param string $orderNumber
 * @return string
 */
function generateOrderAccessToken($orderId, $orderNumber) {
    return hash_hmac('sha256', $orderId . '|' . $orderNumber, getOrderTokenSecret());
}

/**
 * Verify an order access token
 * @param int $orderId
 * @param string $orderNumber
 * @param string $token
 * @return bool
 */
function verifyOrderAccessToken($orderId, $orderNumber, $token) {
    $expected = generateOrderAccessToken($orderId, $orderNumber);
    return hash_equals($expected, $token);
}

/**
 * Get or generate the order token secret
 * @return string
 */
function getOrderTokenSecret() {
    // Use a constant secret key - in production, this should be in an env file
    if (defined('ORDER_TOKEN_SECRET')) {
        return ORDER_TOKEN_SECRET;
    }
    return 'innovative_homesi_order_secret_key_2026';
}
