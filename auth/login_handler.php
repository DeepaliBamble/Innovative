<?php
/**
 * Login Handler
 * Processes user login authentication
 */

require_once __DIR__ . '/../includes/init.php';

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
    exit;
}

// Get and sanitize form data
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) && $_POST['remember'] == '1';

// Validation
$errors = [];

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($password)) {
    $errors[] = 'Password is required';
}

// If validation fails, redirect back with error
if (!empty($errors)) {
    setFlashMessage('error', implode('. ', $errors));
    redirect('../login.php');
    exit;
}

try {
    // Check if user exists with this email
    $stmt = $pdo->prepare('SELECT id, name, email, password, is_admin, is_active, email_verified FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // User not found - prompt to register
        setFlashMessage('error', 'Email not found. Please register first to create an account.');
        redirect('../login.php');
        exit;
    }

    // Verify password using secure hash comparison
    if (!verifyPassword($password, $user['password'])) {
        // Invalid password
        setFlashMessage('error', 'Invalid email or password');
        redirect('../login.php');
        exit;
    }

    // Check if account is active
    if ($user['is_active'] != 1) {
        setFlashMessage('error', 'Your account has been deactivated. Please contact support.');
        redirect('../login.php');
        exit;
    }

    // Login successful - Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_is_admin'] = $user['is_admin'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Set remember me cookie if checked (30 days)
    if ($remember) {
        $token = generateRandomString(64);
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days

        // Store token in database (you may want to create a remember_tokens table)
        // For now, we'll just set a cookie with user ID (in production, use a secure token system)
        setcookie('remember_token', $token, $expiry, '/', '', false, true);

        // Store token in session for future use
        $_SESSION['remember_token'] = $token;
    }

    // Update last login time (optional - you may want to add a last_login column to users table)
    $stmt = $pdo->prepare('UPDATE users SET updated_at = NOW() WHERE id = ?');
    $stmt->execute([$user['id']]);

    // Merge cart items if any (from session to database)
    mergeCartItems($pdo, $user['id']);

    // Set success message
    setFlashMessage('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');

    // Redirect based on user role
    if ($user['is_admin'] == 1) {
        // Redirect admin to admin panel
        redirect(SITE_URL . '/admin/dashboard.php');
    } else {
        // Check if there's a redirect URL in session (from checkout, etc.)
        $redirectUrl = $_SESSION['redirect_after_login'] ?? SITE_URL . '/account-page.php';
        unset($_SESSION['redirect_after_login']);
        redirect($redirectUrl);
    }

} catch (PDOException $e) {
    // Log error
    error_log('Login Error: ' . $e->getMessage());

    setFlashMessage('error', 'An error occurred during login. Please try again.');
    redirect('../login.php');
    exit;
}

/**
 * Merge cart items from session to database after login
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
                // Check if product already in user's cart
                $stmt = $pdo->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?');
                $stmt->execute([$userId, $item['product_id']]);
                $existingItem = $stmt->fetch();

                if ($existingItem) {
                    // Update quantity
                    $newQuantity = $existingItem['quantity'] + $item['quantity'];
                    $stmt = $pdo->prepare('UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?');
                    $stmt->execute([$newQuantity, $existingItem['id']]);
                } else {
                    // Insert new item
                    $stmt = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
                    $stmt->execute([$userId, $item['product_id'], $item['quantity']]);
                }
            }

            // Delete session cart items
            $stmt = $pdo->prepare('DELETE FROM cart WHERE session_id = ?');
            $stmt->execute([$sessionId]);
        }
    } catch (PDOException $e) {
        error_log('Cart Merge Error: ' . $e->getMessage());
    }
}
