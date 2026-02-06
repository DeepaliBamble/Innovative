<?php
/**
 * Email Configuration for Hostinger SMTP
 * Update these settings with your Hostinger email credentials
 */

// SMTP Configuration for Hostinger
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587); // Use 587 for TLS or 465 for SSL
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
define('SMTP_USERNAME', 'contactform@innovativehomesi.com'); // Your Hostinger email
define('SMTP_PASSWORD', 'Contact@Form321'); // Your email password - UPDATE THIS!

// Sender Information
define('MAIL_FROM_EMAIL', 'contactform@innovativehomesi.com');
define('MAIL_FROM_NAME', 'Innovative Homesi');

// Admin Email (where enquiries are sent)
define('ADMIN_EMAIL', 'contactform@innovativehomesi.com');
define('ADMIN_NAME', 'Innovative Homesi Admin');

// Email Settings
define('SMTP_DEBUG', 0); // 0 = off, 1 = client messages, 2 = client and server messages
define('SMTP_AUTH', true);
