<?php
/**
 * Send Login OTP via MSG91 SMS
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/msg91-helper.php';

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

$mobile = sanitize($_POST['mobile'] ?? '');

if (empty($mobile)) {
    echo json_encode(['success' => false, 'message' => 'Mobile number is required']);
    exit;
}

if (!isValidMobile($mobile)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number']);
    exit;
}

// Normalize to plain 10-digit for DB lookup
$plain10 = substr(preg_replace('/\D/', '', $mobile), -10);

try {
    // Look up user by phone number (stored as 10-digit in DB)
    $stmt = $pdo->prepare('SELECT id, name, phone, is_active FROM users WHERE phone = ?');
    $stmt->execute([$plain10]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No account found with this mobile number. Please register first.']);
        exit;
    }

    if ($user['is_active'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.']);
        exit;
    }

    // Send OTP via MSG91
    $smsResult = sendSmsOTP($mobile);

    if (!$smsResult['success']) {
        echo json_encode(['success' => false, 'message' => $smsResult['message']]);
        exit;
    }

    // Store mobile in session for verification step
    $_SESSION['otp_mobile']  = $plain10;
    $_SESSION['otp_type']    = 'login';
    $_SESSION['otp_sent_at'] = time();

    // Mask middle digits for display: 98XXXX4321
    $maskedMobile = substr($plain10, 0, 2) . 'XXXX' . substr($plain10, -4);

    echo json_encode([
        'success'       => true,
        'message'       => 'OTP sent to your mobile number',
        'maskedMobile'  => $maskedMobile
    ]);

} catch (PDOException $e) {
    error_log('Send Login OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
