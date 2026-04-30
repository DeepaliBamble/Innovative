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
    ini_set('session.cookie_samesite', 'Lax');

    // Set session name
    session_name(SESSION_NAME);

    // Persist the session cookie across browser restarts (was browser-session
    // only by default). Combined with SESSION_LIFETIME below this keeps users
    // signed in for 30 days of idle time.
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

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
