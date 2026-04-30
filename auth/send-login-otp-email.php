<?php
/**
 * Send Login OTP via email
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

// Throttle: 5 sends per 10 minutes per session
if (!checkRateLimit($pdo, 'send_email_otp', 5, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many OTP requests. Please try again in a few minutes.']);
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, name, is_active FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No account found with this email. Please register first.']);
        exit;
    }

    if ($user['is_active'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.']);
        exit;
    }

    $send = sendEmailLoginOtp($pdo, $email, $user['name']);
    if (!$send['success']) {
        echo json_encode($send);
        exit;
    }

    $_SESSION['otp_email']     = $email;
    $_SESSION['otp_type']      = 'login_email';
    $_SESSION['otp_sent_at']   = time();

    // Mask the email a bit for the confirmation screen: jo***@example.com
    $atPos = strpos($email, '@');
    $local = substr($email, 0, $atPos);
    $maskedEmail = (strlen($local) <= 2 ? $local : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2))) . substr($email, $atPos);

    echo json_encode([
        'success'      => true,
        'message'      => 'OTP sent to your email',
        'maskedEmail'  => $maskedEmail,
    ]);
} catch (PDOException $e) {
    error_log('Send Email Login OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
