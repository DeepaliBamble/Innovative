<?php
/**
 * Verify email login OTP and log the user in
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/email-otp-helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.']);
    exit;
}

$email   = $_SESSION['otp_email'] ?? '';
$otpType = $_SESSION['otp_type']  ?? '';

if (empty($email) || $otpType !== 'login_email') {
    echo json_encode(['success' => false, 'message' => 'Invalid session. Please request a new OTP.']);
    exit;
}

$otp      = sanitize($_POST['otp'] ?? '');
$remember = isset($_POST['remember']) && $_POST['remember'] == '1';

if (empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'OTP is required']);
    exit;
}

$verify = verifyEmailLoginOtp($pdo, $email, $otp);
if (!$verify['success']) {
    echo json_encode($verify);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, name, email, phone, is_admin, is_active FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($user['is_active'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.']);
        exit;
    }

    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_name']     = $user['name'];
    $_SESSION['user_email']    = $user['email'];
    $_SESSION['user_is_admin'] = $user['is_admin'];
    $_SESSION['logged_in']     = true;
    $_SESSION['login_time']    = time();

    unset($_SESSION['otp_email'], $_SESSION['otp_type'], $_SESSION['otp_sent_at']);

    if ($remember) {
        storeRememberToken($pdo, $user['id']);
    }

    // First successful email-OTP login implicitly verifies the email
    $pdo->prepare('UPDATE users SET email_verified = 1, updated_at = NOW() WHERE id = ?')
        ->execute([$user['id']]);

    mergeCartItems($pdo, $user['id']);

    if ($user['is_admin'] == 1) {
        $redirectUrl = SITE_URL . '/admin/dashboard.php';
    } else {
        $redirectUrl = $_SESSION['redirect_after_login'] ?? SITE_URL . '/account-page.php';
        unset($_SESSION['redirect_after_login']);
    }

    echo json_encode([
        'success'  => true,
        'message'  => 'Login successful! Redirecting...',
        'redirect' => $redirectUrl,
    ]);
} catch (PDOException $e) {
    error_log('Verify Email Login OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred during login. Please try again.']);
}

/**
 * Reuse the same cart-merge behaviour the mobile-OTP login uses.
 */
function mergeCartItems($pdo, $userId) {
    try {
        $sessionId = session_id();
        $stmt = $pdo->prepare('SELECT product_id, quantity FROM cart WHERE session_id = ?');
        $stmt->execute([$sessionId]);
        $sessionCart = $stmt->fetchAll();

        if (!empty($sessionCart)) {
            foreach ($sessionCart as $item) {
                $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
                $stmt->execute([$userId, $item['product_id']]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $pdo->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?')
                        ->execute([$existing['quantity'] + $item['quantity'], $existing['id']]);
                } else {
                    $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)')
                        ->execute([$userId, $item['product_id'], $item['quantity']]);
                }
            }
            $pdo->prepare('DELETE FROM cart WHERE session_id = ?')->execute([$sessionId]);
        }
    } catch (PDOException $e) {
        error_log('Cart Merge Error: ' . $e->getMessage());
    }
}
