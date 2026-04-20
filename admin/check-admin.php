<?php
/**
 * Diagnostic / reset script for admin login issues.
 *
 * USAGE:
 *   1. Upload to /admin/check-admin.php on the server.
 *   2. Visit: https://innovativehomesi.com/admin/check-admin.php
 *      - Shows whether the admin user exists and whether the stored
 *        password hash matches the known default.
 *   3. To reset the password back to the default, visit:
 *      https://innovativehomesi.com/admin/check-admin.php?reset=1
 *   4. DELETE THIS FILE FROM THE SERVER AFTER USE.
 */

require __DIR__ . '/../includes/init.php';

header('Content-Type: text/plain; charset=utf-8');

$defaultEmail = 'admin@innovative.com';
$defaultPassword = 'Admin@Homesi2026';

echo "=== Admin Login Diagnostic ===\n\n";
echo "DB Host: " . DB_HOST . "\n";
echo "DB Name: " . DB_NAME . "\n\n";

try {
    $stmt = $pdo->prepare('SELECT id, name, email, password, is_admin, is_active FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$defaultEmail]);
    $admin = $stmt->fetch();

    if (!$admin) {
        echo "[FAIL] No user found with email: $defaultEmail\n";
        echo "\nCreating admin account...\n";
        $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (name, email, password, is_admin, is_active, email_verified) VALUES (?, ?, ?, 1, 1, 1)')
            ->execute(['Admin', $defaultEmail, $hash]);
        echo "[OK] Admin created. Login with $defaultEmail / $defaultPassword\n";
    } else {
        echo "[OK] Admin record found.\n";
        echo "  id        : " . $admin['id'] . "\n";
        echo "  name      : " . $admin['name'] . "\n";
        echo "  email     : " . $admin['email'] . "\n";
        echo "  is_admin  : " . $admin['is_admin'] . "\n";
        echo "  is_active : " . $admin['is_active'] . "\n";
        echo "  hash head : " . substr($admin['password'], 0, 7) . "...\n\n";

        $verifyDefault = password_verify($defaultPassword, $admin['password']);
        echo "Default password ('$defaultPassword') verifies: " . ($verifyDefault ? 'YES' : 'NO') . "\n";

        if ((int) $admin['is_admin'] !== 1) {
            echo "\n[FAIL] User exists but is_admin = 0. Login query filters on is_admin=1.\n";
        }
    }

    // Optional reset
    if (isset($_GET['reset']) && $_GET['reset'] === '1') {
        $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $pdo->prepare('UPDATE users SET password = ?, is_admin = 1, is_active = 1 WHERE email = ?')
            ->execute([$hash, $defaultEmail]);
        echo "\n[OK] Password reset to: $defaultPassword\n";
        echo "You can now log in at /admin/login.php with $defaultEmail / $defaultPassword\n";
    } else {
        echo "\nTo reset the admin password to the default, add ?reset=1 to this URL.\n";
    }
} catch (PDOException $e) {
    echo "[FAIL] Database error: " . $e->getMessage() . "\n";
}

echo "\n*** DELETE THIS FILE FROM THE SERVER AFTER USE ***\n";
