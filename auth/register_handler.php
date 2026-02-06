<?php
/**
 * Registration Handler
 * Processes user registration
 */

require_once __DIR__ . '/../includes/init.php';

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../register.php');
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

// If validation fails, redirect back with error
if (!empty($errors)) {
    setFlashMessage('error', implode('. ', $errors));
    redirect('../register.php');
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        setFlashMessage('error', 'An account with this email already exists. Please login instead.');
        redirect('../register.php');
        exit;
    }

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

    // Merge cart items if any
    mergeCartItems($pdo, $userId);

    // Set success message
    setFlashMessage('success', 'Registration successful! Welcome to Innovative Homesi, ' . htmlspecialchars($name) . '!');

    // Redirect to account page
    redirect(SITE_URL . '/account-page.php');

} catch (PDOException $e) {
    // Log error
    error_log('Registration Error: ' . $e->getMessage());

    setFlashMessage('error', 'An error occurred during registration. Please try again.');
    redirect('../register.php');
    exit;
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
