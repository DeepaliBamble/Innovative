<?php
/**
 * Send Registration OTP via MSG91 SMS
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

$name   = sanitize($_POST['name']   ?? '');
$mobile = sanitize($_POST['mobile'] ?? '');

$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Please enter your full name (at least 2 characters)';
}

if (empty($mobile)) {
    $errors[] = 'Mobile number is required';
} elseif (!isValidMobile($mobile)) {
    $errors[] = 'Please enter a valid 10-digit mobile number';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

$plain10 = substr(preg_replace('/\D/', '', $mobile), -10);

try {
    // Check if mobile already registered
    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
    $stmt->execute([$plain10]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this mobile number already exists. Please login instead.']);
        exit;
    }

    // Send OTP via MSG91
    $smsResult = sendSmsOTP($mobile);

    if (!$smsResult['success']) {
        echo json_encode(['success' => false, 'message' => $smsResult['message']]);
        exit;
    }

    // Store registration data in session
    $_SESSION['registration_data'] = [
        'name'   => $name,
        'mobile' => $plain10
    ];
    $_SESSION['otp_mobile']  = $plain10;
    $_SESSION['otp_type']    = 'registration';
    $_SESSION['otp_sent_at'] = time();

    $maskedMobile = substr($plain10, 0, 2) . 'XXXX' . substr($plain10, -4);

    echo json_encode([
        'success'       => true,
        'message'       => 'OTP sent to your mobile number',
        'maskedMobile'  => $maskedMobile
    ]);

} catch (PDOException $e) {
    error_log('Send Registration OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
