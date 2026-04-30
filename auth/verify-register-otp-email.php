<?php
/**
 * Verify Registration OTP delivered by email and create the user account.
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

$email            = $_SESSION['otp_email']        ?? '';
$otpType          = $_SESSION['otp_type']         ?? '';
$registrationData = $_SESSION['registration_data'] ?? null;

if (empty($email) || $otpType !== 'registration_email' || !$registrationData) {
    echo json_encode(['success' => false, 'message' => 'Invalid session. Please start registration again.']);
    exit;
}

$otp = sanitize($_POST['otp'] ?? '');

if (empty($otp) || !preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['success' => false, 'message' => 'Please enter the 6-digit OTP']);
    exit;
}

$verify = verifyEmailLoginOtp($pdo, $email, $otp);
if (!$verify['success']) {
    echo json_encode($verify);
    exit;
}

try {
    $name     = $registrationData['name'];
    $mobile   = $registrationData['mobile'];
    $regEmail = $registrationData['email']    ?? null;
    $location = $registrationData['location'] ?? null;

    // Insert user — no password, phone is the unique identifier; email is verified by definition here.
    $stmt = $pdo->prepare('
        INSERT INTO users (name, phone, email, location, password, is_admin, is_active, email_verified, created_at)
        VALUES (?, ?, ?, ?, NULL, 0, 1, 1, NOW())
    ');
    $stmt->execute([$name, $mobile, $regEmail, $location]);
    $userId = $pdo->lastInsertId();

    $_SESSION['user_id']       = $userId;
    $_SESSION['user_name']     = $name;
    $_SESSION['user_email']    = $regEmail ?? '';
    $_SESSION['user_is_admin'] = 0;
    $_SESSION['logged_in']     = true;
    $_SESSION['login_time']    = time();

    unset($_SESSION['otp_email'], $_SESSION['otp_type'], $_SESSION['otp_sent_at'], $_SESSION['registration_data']);

    mergeCartItems($pdo, $userId);

    echo json_encode([
        'success'  => true,
        'message'  => 'Welcome to Innovative Homesi, ' . htmlspecialchars($name) . '!',
        'redirect' => SITE_URL . '/account-page.php',
    ]);

} catch (PDOException $e) {
    error_log('Verify Registration Email OTP Error: ' . $e->getMessage());

    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'This mobile number or email is already registered. Please login instead.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred during registration. Please try again.']);
    }
}

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
