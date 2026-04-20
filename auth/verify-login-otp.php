<?php
/**
 * Verify Login OTP
 * Handles OTP verification and user login
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/otp-helper.php';

header('Content-Type: application/json');

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.']);
    exit;
}

// Get session email
$email = $_SESSION['otp_email'] ?? '';
$otpType = $_SESSION['otp_type'] ?? '';

if (empty($email) || $otpType !== 'login') {
    echo json_encode(['success' => false, 'message' => 'Invalid session. Please request a new OTP.']);
    exit;
}

// Get and sanitize OTP
$otp = sanitize($_POST['otp'] ?? '');
$remember = isset($_POST['remember']) && $_POST['remember'] == '1';

// Validation
if (empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'OTP is required']);
    exit;
}

if (!preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['success' => false, 'message' => 'OTP must be 6 digits']);
    exit;
}

try {
    // Verify OTP
    $verifyResult = verifyOTP($pdo, $email, $otp, 'login');

    if (!$verifyResult['success']) {
        echo json_encode(['success' => false, 'message' => $verifyResult['message']]);
        exit;
    }

    // Get user details
    $stmt = $pdo->prepare('SELECT id, name, email, is_admin, is_active FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Check if account is active
    if ($user['is_active'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.']);
        exit;
    }

    // Login successful - Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_is_admin'] = $user['is_admin'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Clear OTP session data
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_type']);
    unset($_SESSION['otp_sent_at']);

    // Set remember me cookie if checked (30 days)
    if ($remember) {
        storeRememberToken($pdo, $user['id']);
    }

    // Update last login time
    $stmt = $pdo->prepare('UPDATE users SET updated_at = NOW() WHERE id = ?');
    $stmt->execute([$user['id']]);

    // Merge cart items if any (from session to database)
    mergeCartItems($pdo, $user['id']);

    // Determine redirect URL
    if ($user['is_admin'] == 1) {
        $redirectUrl = SITE_URL . '/admin/dashboard.php';
    } else {
        $redirectUrl = $_SESSION['redirect_after_login'] ?? SITE_URL . '/account-page.php';
        unset($_SESSION['redirect_after_login']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Redirecting...',
        'redirect' => $redirectUrl
    ]);

} catch (PDOException $e) {
    error_log('Verify Login OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during login. Please try again.']);
}

/**
 * Merge cart items from session to database after login
 */
function mergeCartItems($pdo, $userId) {
    try {
        $sessionId = session_id();

        // Get cart items from current session
        $stmt = $pdo->prepare('SELECT product_id, quantity FROM cart WHERE session_id = ?');
        $stmt->execute([$sessionId]);
        $sessionCart = $stmt->fetchAll();

        if (!empty($sessionCart)) {
            foreach ($sessionCart as $item) {
                // Check if product already in user's cart
                $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
                $stmt->execute([$userId, $item['product_id']]);
                $existingItem = $stmt->fetch();

                if ($existingItem) {
                    // Update quantity
                    $newQuantity = $existingItem['quantity'] + $item['quantity'];
                    $stmt = $pdo->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?');
                    $stmt->execute([$newQuantity, $existingItem['id']]);
                } else {
                    // Insert new item
                    $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
                    $stmt->execute([$userId, $item['product_id'], $item['quantity']]);
                }
            }

            // Delete session cart items
            $stmt = $pdo->prepare('DELETE FROM cart WHERE session_id = ?');
            $stmt->execute([$sessionId]);
        }
    } catch (PDOException $e) {
        error_log('Cart Merge Error: ' . $e->getMessage());
    }
}
