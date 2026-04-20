<?php
/**
 * Logout Handler
 * Destroys user session and logs out
 */

// Load config first to get SESSION_NAME
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set session name before starting
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Remove remember me token from DB and clear cookie
clearRememberToken($pdo);

// Clear all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        SESSION_NAME,
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Start a fresh session for the flash message
session_name(SESSION_NAME);
session_start();
session_regenerate_id(true);

// Set logout success message
$_SESSION['flash_message'] = [
    'type' => 'success',
    'message' => 'You have been successfully logged out.'
];

// Redirect to login page
header('Location: ' . SITE_URL . '/login.php');
exit;
