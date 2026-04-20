<?php
/**
 * Email Configuration for Hostinger SMTP
 * Credentials are loaded from the .env file — never hardcode them here.
 */

// Load .env if SMTP_PASSWORD not already in environment
if (!getenv('SMTP_PASSWORD')) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key   = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if (!array_key_exists($key, $_ENV)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
}

// SMTP Configuration for Hostinger
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587); // Use 587 for TLS or 465 for SSL
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_USERNAME', 'contactform@innovativehomesi.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');

// Sender Information
define('MAIL_FROM_EMAIL', 'contactform@innovativehomesi.com');
define('MAIL_FROM_NAME', 'Innovative Homesi');

// Admin Email (where enquiries are sent)
define('ADMIN_EMAIL', 'contactform@innovativehomesi.com');
define('ADMIN_NAME', 'Innovative Homesi Admin');

// Email Settings
define('SMTP_DEBUG', 0); // 0 = off, 1 = client messages, 2 = client and server messages
define('SMTP_AUTH', true);
