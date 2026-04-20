<?php
/**
 * Initialization File
 * This file should be included at the beginning of every page
 * It loads all necessary configuration, database connection, and functions
 */

// Load configuration
require_once __DIR__ . '/config.php';

// Initialize session
require_once __DIR__ . '/session.php';

// Load database connection
require_once __DIR__ . '/db.php';

// Load helper functions
require_once __DIR__ . '/functions.php';

// Load security functions (CSRF, rate limiting, headers)
require_once __DIR__ . '/security.php';

// Set security headers on every page load
setSecurityHeaders();

// Generate CSRF token on every page load (for forms)
generateCsrfToken();

// Auto-login from remember-me cookie if not already in session
autoLoginFromCookie($pdo);
