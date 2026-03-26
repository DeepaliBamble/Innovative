<?php
/**
 * Configuration file for Innovative E-commerce
 */

// ======================
// Database Configuration
// ======================
define('DB_HOST', 'localhost');
define('DB_NAME', 'innovative');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ======================
// Site Configuration
// ======================
define('SITE_NAME', 'Innovative Homesi');
define('SITE_URL', 'http://localhost/Innovative'); // Local XAMPP URL
define('BASE_URL_PATH', ''); // root deployment - no subdirectory
define('ADMIN_URL', SITE_URL . BASE_URL_PATH . '/admin');
define('PUBLIC_URL', SITE_URL . BASE_URL_PATH . '/public');

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
ini_set('display_errors', 1); // TURNED ON for local development

// ======================
// Timezone
// ======================
date_default_timezone_set('Asia/Kolkata');
