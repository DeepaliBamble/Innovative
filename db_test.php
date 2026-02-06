<?php
/**
 * Database Connection Test Script
 * diagnostic tool to check connectivity
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connectivity Test</h1>";
echo "<p>Running diagnostics...</p>";

// 1. Check Configuration
echo "<h2>1. Loading Configuration</h2>";
$configFile = __DIR__ . '/includes/config.php';
if (file_exists($configFile)) {
    echo "<p style='color:green'>Found config.php</p>";
    require_once $configFile;
    
    echo "<ul>";
    echo "<li><strong>Environment:</strong> " . (defined('IS_LOCAL') && IS_LOCAL ? 'Local' : 'Live') . "</li>";
    echo "<li><strong>DB_HOST:</strong> " . DB_HOST . "</li>";
    echo "<li><strong>DB_USER:</strong> " . DB_USER . "</li>";
    echo "<li><strong>DB_NAME:</strong> " . DB_NAME . "</li>";
    echo "</ul>";
} else {
    die("<p style='color:red'>CRITICAL: config.php not found at $configFile</p>");
}

// 2. Test PDO Connection
echo "<h2>2. Testing PDO Connection</h2>";
try {
    $start = microtime(true);
    
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5, // 5 second timeout
    ];

    echo "<p>Attempting connection (timeout: 5s)...</p>";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    $end = microtime(true);
    $time = round($end - $start, 4);
    
    echo "<p style='color:green'><strong>SUCCESS:</strong> Connected via PDO in $time seconds.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'><strong>ERROR:</strong> PDO Connection failed.</p>";
    echo "<pre>Code: " . $e->getCode() . "\nMessage: " . $e->getMessage() . "</pre>";
}

// 3. Test MySQLi Connection
echo "<h2>3. Testing MySQLi Connection</h2>";
$start = microtime(true);
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo "<p style='color:red'><strong>ERROR:</strong> MySQLi Connection failed.</p>";
    echo "<pre>Error: " . $conn->connect_error . "</pre>";
} else {
    $end = microtime(true);
    $time = round($end - $start, 4);
    echo "<p style='color:green'><strong>SUCCESS:</strong> Connected via MySQLi in $time seconds.</p>";
    $conn->close();
}

echo "<hr>";
echo "<p><em>Delete this file after debugging is complete.</em></p>";
?>
