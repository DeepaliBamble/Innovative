<?php
// Mock DB/config
define('APP_ENV', 'production');
require_once __DIR__ . '/includes/mail-config.php';
define('SITE_URL', 'https://innovativehomesi.com');

require_once __DIR__ . '/includes/mail-helper.php';

$res = sendEmail('test@example.com', 'Test Subject', 'Test Body');
var_dump($res);
