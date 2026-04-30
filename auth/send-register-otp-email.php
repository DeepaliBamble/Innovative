<?php
/**
 * Send Registration OTP via email.
 * Collects name, mobile, email, location — stashes them in session until verify step.
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/email-otp-helper.php';
require_once __DIR__ . '/../includes/msg91-helper.php'; // isValidMobile() lives here

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

if (!checkRateLimit($pdo, 'send_register_email_otp', 5, 600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many OTP requests. Please try again in a few minutes.']);
    exit;
}

$name     = sanitize($_POST['name']     ?? '');
$mobile   = sanitize($_POST['mobile']   ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
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
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
    $stmt->execute([$plain10]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this mobile number already exists. Please login instead.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists. Please login instead.']);
        exit;
    }

    $send = sendEmailLoginOtp($pdo, $email, $name, 'registration');
    if (!$send['success']) {
        echo json_encode($send);
        exit;
    }

    $_SESSION['registration_data'] = [
        'name'     => $name,
        'mobile'   => $plain10,
        'email'    => $email,
        'location' => $location,
    ];
    $_SESSION['otp_email']   = $email;
    $_SESSION['otp_type']    = 'registration_email';
    $_SESSION['otp_sent_at'] = time();

    $atPos = strpos($email, '@');
    $local = substr($email, 0, $atPos);
    $maskedEmail = (strlen($local) <= 2 ? $local : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local) - 2))) . substr($email, $atPos);

    echo json_encode([
        'success'     => true,
        'message'     => 'OTP sent to your email',
        'maskedEmail' => $maskedEmail,
    ]);

} catch (PDOException $e) {
    error_log('Send Registration Email OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
