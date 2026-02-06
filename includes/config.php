<?php
/**
 * Configuration file for Innovative E-commerce
 */

// ======================
// Environment Detection
// ======================
// Detect if running on localhost or live server
$whitelist = array(
    '127.0.0.1',
    '::1',
    'localhost'
);

if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], $whitelist)) {
    define('IS_LOCAL', true);
} else {
    define('IS_LOCAL', false);
}

// ======================
// Database Configuration
// ======================
if (IS_LOCAL) {
    // Localhost Credentials (XAMPP Default)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u312948055_innovative'); // Consider changing this to just 'innovative' for local if that is your local DB name
    define('DB_USER', 'u312948055_innovative'); // Usually 'root' for local XAMPP
    define('DB_PASS', 'Innovative@321');        // Usually empty '' for local XAMPP
} else {
    // Live Server Credentials - PLEASE VERIFY THESE
    define('DB_HOST', 'localhost');             // Often 'localhost' even on live shared hosting
    define('DB_NAME', 'u312948055_innovative');
    define('DB_USER', 'u312948055_innovative');
    define('DB_PASS', 'Innovative@321'); 
}

define('DB_CHARSET', 'utf8mb4');

// ======================
// Site Configuration
// ======================
define('SITE_NAME', 'Innovative Homesi');

if (IS_LOCAL) {
    define('SITE_URL', 'http://localhost/Innovative%20Homesi'); // Update folder name if different
} else {
    define('SITE_URL', 'https://innovativehomesi.com');
}

define('ADMIN_URL', SITE_URL . '/admin');
define('PUBLIC_URL', SITE_URL . '/public');

// ======================
// Path Configuration
// ======================
define('BASE_PATH', dirname(__DIR__)); // points to /public_html
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('ADMIN_PATH', BASE_PATH . '/admin');

// ======================
// Session Configuration
// ======================
define('SESSION_LIFETIME', 3600);
define('SESSION_NAME', 'innovative_session');
define('PASSWORD_MIN_LENGTH', 6);

// ======================
// Pagination
// ======================
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// ======================
// File Upload
// ======================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// ======================
// Error Reporting
// ======================
error_reporting(E_ALL);
ini_set('display_errors', 0); // TURNED OFF for live site

// ======================
// Timezone
// ======================
date_default_timezone_set('Asia/Kolkata');
