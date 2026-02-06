<?php
/**
 * Session Management
 * Initialize and configure PHP sessions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0); // Auto-detect HTTPS
    ini_set('session.cookie_samesite', 'Strict');

    // Set session name
    session_name(SESSION_NAME);

    // Set session lifetime
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

    // Start the session
    session_start();

    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
