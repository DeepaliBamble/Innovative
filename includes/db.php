<?php
/**
 * Database Connection File
 * This file establishes PDO connection to MySQL database
 */

// Load configuration
require_once __DIR__ . '/config.php';

try {
    // Create PDO instance
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5, // 5 seconds timeout
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Also create MySQLi connection for compatibility with some modules
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("MySQLi Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset(DB_CHARSET);

} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log('Database Connection Error: ' . $e->getMessage());

    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Connection Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 50px; text-align: center; }
            .error-box { max-width: 600px; margin: 0 auto; padding: 30px; border: 2px solid #dc3545; border-radius: 5px; background: #f8d7da; }
            h1 { color: #721c24; }
            p { color: #721c24; margin: 20px 0; }
            .steps { text-align: left; background: white; padding: 20px; border-radius: 5px; margin-top: 20px; }
            .steps li { margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>⚠️ Database Connection Failed</h1>
            <p>Unable to connect to the database. Please check your configuration.</p>
            <div class="steps">
                <strong>Setup Steps:</strong>
                <ol>
                    <li>Make sure XAMPP MySQL service is running</li>
                    <li>Open phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></li>
                    <li>Create a database named: <code>innovative</code></li>
                    <li>Import the SQL schema from: <code>sql/schema.sql</code></li>
                    <li>Check database credentials in: <code>includes/config.php</code></li>
                </ol>
            </div>
        </div>
    </body>
    </html>
    ');
}
