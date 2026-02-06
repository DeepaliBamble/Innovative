<?php
/**
 * Verify Registration OTP
 * Handles OTP verification and completes user registration
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/otp-helper.php';

header('Content-Type: application/json');

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get session data
$email = $_SESSION['otp_email'] ?? '';
$otpType = $_SESSION['otp_type'] ?? '';
$registrationData = $_SESSION['registration_data'] ?? null;

if (empty($email) || $otpType !== 'registration' || !$registrationData) {
    echo json_encode(['success' => false, 'message' => 'Invalid session. Please start registration again.']);
    exit;
}

// Get and sanitize OTP
$otp = sanitize($_POST['otp'] ?? '');

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
    $verifyResult = verifyOTP($pdo, $email, $otp, 'registration');

    if (!$verifyResult['success']) {
        echo json_encode(['success' => false, 'message' => $verifyResult['message']]);
        exit;
    }

    // OTP verified successfully, now complete registration
    $name = $registrationData['name'];
    $email = $registrationData['email'];
    $phone = $registrationData['phone'];
    $password = $registrationData['password'];

    // Hash the password securely
    $hashedPassword = hashPassword($password);

    // Insert new user with hashed password
    $stmt = $pdo->prepare('
        INSERT INTO users (name, email, phone, password, is_admin, is_active, email_verified, created_at)
        VALUES (?, ?, ?, ?, 0, 1, 1, NOW())
    ');

    $stmt->execute([
        $name,
        $email,
        $phone ?: null,
        $hashedPassword
    ]);

    $userId = $pdo->lastInsertId();

    // Auto-login after registration
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_is_admin'] = 0;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Clear OTP and registration session data
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_type']);
    unset($_SESSION['otp_sent_at']);
    unset($_SESSION['registration_data']);

    // Merge cart items if any
    mergeCartItems($pdo, $userId);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Welcome to Innovative Homesi, ' . htmlspecialchars($name) . '!',
        'redirect' => SITE_URL . '/account-page.php'
    ]);

} catch (PDOException $e) {
    error_log('Verify Registration OTP Error: ' . $e->getMessage());

    // Check if it's a duplicate email error (in case someone registered while user was verifying)
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists. Please login instead.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred during registration. Please try again.']);
    }
}

/**
 * Merge cart items from session to database after registration
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
                // Insert cart items for new user
                $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
                $stmt->execute([$userId, $item['product_id'], $item['quantity']]);
            }

            // Delete session cart items
            $stmt = $pdo->prepare('DELETE FROM cart WHERE session_id = ?');
            $stmt->execute([$sessionId]);
        }
    } catch (PDOException $e) {
        error_log('Cart Merge Error: ' . $e->getMessage());
    }
}
