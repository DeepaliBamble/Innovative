<?php

function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://checkout.razorpay.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.razorpay.com; frame-src https://api.razorpay.com;");

    // Enforce HTTPS in production
    if (defined('APP_ENV') && APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Alias used throughout auth and payment files
function validateCsrfToken($token) {
    return verifyCsrfToken($token);
}

// Output a hidden CSRF input field
function csrfTokenField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// Die with 403 JSON response if CSRF token in POST is invalid
function verifyCsrfOrDie() {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh and try again.']);
        } else {
            echo 'Security token expired. Please go back and try again.';
        }
        exit;
    }
}

// Detect AJAX requests
function isAjaxRequest() {
    return (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    );
}

/**
 * Simple rate limiter using PHP sessions.
 * @param PDO   $pdo      Database connection (unused here, kept for signature compatibility)
 * @param string $action  Unique action name
 * @param int   $maxHits  Maximum allowed attempts
 * @param int   $window   Time window in seconds
 * @return bool True if within limit, false if exceeded
 */
function checkRateLimit($pdo, $action, $maxHits = 10, $window = 900) {
    $key = 'rate_limit_' . $action;
    $now = time();

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'start' => $now];
    }

    // Reset window if expired
    if ($now - $_SESSION[$key]['start'] > $window) {
        $_SESSION[$key] = ['count' => 0, 'start' => $now];
    }

    $_SESSION[$key]['count']++;

    return $_SESSION[$key]['count'] <= $maxHits;
}

/**
 * Validate and return a sanitized email, or false if invalid.
 */
function validateEmail($email) {
    $email = trim($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Generate a signed token for secure order success page access.
 */
function generateOrderAccessToken($orderId, $orderNumber) {
    $secret = defined('ORDER_TOKEN_SECRET') ? ORDER_TOKEN_SECRET : 'fallback_secret';
    $payload = $orderId . '|' . $orderNumber . '|' . date('Y-m-d');
    return hash_hmac('sha256', $payload, $secret);
}

/**
 * Verify a signed order access token.
 */
function verifyOrderAccessToken($orderId, $orderNumber, $token) {
    $expected = generateOrderAccessToken($orderId, $orderNumber);
    return hash_equals($expected, $token);
}
