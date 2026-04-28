<?php
/**
 * Send Registration OTP via MSG91 SMS
 * Collects name, mobile, email, location — stores in session until verify step.
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

$name     = sanitize($_POST['name']     ?? '');
$mobile   = sanitize($_POST['mobile']   ?? '');
$email    = sanitize($_POST['email']    ?? '');
$location = sanitize($_POST['location'] ?? '');

$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Please enter your full name (at least 2 characters)';
}

if (empty($mobile)) {
    $errors[] = 'Mobile number is required';
} elseif (!isValidMobile($mobile)) {
    $errors[] = 'Please enter a valid 10-digit mobile number';
}

if (empty($email)) {
    $errors[] = 'Email address is required';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($location) || strlen($location) < 2) {
    $errors[] = 'Please enter your location (city / area)';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

$plain10 = substr(preg_replace('/\D/', '', $mobile), -10);

try {
    // Phone already registered?
    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
    $stmt->execute([$plain10]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this mobile number already exists. Please login instead.']);
        exit;
    }

    // Email already registered?
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists. Please login with your registered mobile number.']);
        exit;
    }

    // Send OTP via MSG91
    $smsResult = sendSmsOTP($mobile);

    if (!$smsResult['success']) {
        echo json_encode(['success' => false, 'message' => $smsResult['message']]);
        exit;
    }

    // Stash registration data for the verify step
    $_SESSION['registration_data'] = [
        'name'     => $name,
        'mobile'   => $plain10,
        'email'    => $email,
        'location' => $location,
    ];
    $_SESSION['otp_mobile']  = $plain10;
    $_SESSION['otp_type']    = 'registration';
    $_SESSION['otp_sent_at'] = time();

    $maskedMobile = substr($plain10, 0, 2) . 'XXXX' . substr($plain10, -4);

    echo json_encode([
        'success'      => true,
        'message'      => 'OTP sent to your mobile number',
        'maskedMobile' => $maskedMobile,
    ]);

} catch (PDOException $e) {
    error_log('Send Registration OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
