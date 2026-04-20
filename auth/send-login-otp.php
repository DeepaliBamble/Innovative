<?php
/**
 * Send Login OTP
 * Handles OTP generation and sending for login
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

// Get and sanitize email
$email = sanitize($_POST['email'] ?? '');

// Validation
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

if (!isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

try {
    // Check if user exists with this email
    $stmt = $pdo->prepare('SELECT id, name, email, is_active FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Email not found - prompt user to register
        echo json_encode(['success' => false, 'message' => 'Email not found. Please register first to create an account.']);
        exit;
    }

    // Check if account is active
    if ($user['is_active'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.']);
        exit;
    }

    // Generate and store OTP
    $otpResult = createOTP($pdo, $email, 'login', 10);

    if (!$otpResult['success']) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate OTP. Please try again.']);
        exit;
    }

    // Send OTP via email
    $emailResult = sendLoginOTP($email, $otpResult['otp'], $user['name']);

    if (!$emailResult['success']) {
        error_log('Failed to send login OTP to: ' . $email . ' - ' . $emailResult['message']);
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again later.']);
        exit;
    }

    // Store email in session for OTP verification
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_type'] = 'login';
    $_SESSION['otp_sent_at'] = time();

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully to your email',
        'email' => $email
    ]);

} catch (PDOException $e) {
    error_log('Send Login OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
