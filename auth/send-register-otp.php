<?php
/**
 * Send Registration OTP
 * Handles OTP generation and sending for user registration
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/otp-helper.php';

header('Content-Type: application/json');

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and sanitize form data
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
    $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// If validation fails, return error
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists. Please login instead.']);
        exit;
    }

    // Generate and store OTP
    $otpResult = createOTP($pdo, $email, 'registration', 10);

    if (!$otpResult['success']) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate OTP. Please try again.']);
        exit;
    }

    // Send OTP via email
    $emailResult = sendRegistrationOTP($email, $otpResult['otp'], $name);

    if (!$emailResult['success']) {
        error_log('Failed to send registration OTP to: ' . $email . ' - ' . $emailResult['message']);
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again later.']);
        exit;
    }

    // Store registration data in session for OTP verification
    $_SESSION['registration_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password' => $password
    ];
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_type'] = 'registration';
    $_SESSION['otp_sent_at'] = time();

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully to your email',
        'email' => $email
    ]);

} catch (PDOException $e) {
    error_log('Send Registration OTP Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
