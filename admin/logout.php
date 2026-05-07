<?php
require __DIR__ . '/../includes/init.php';
clearRememberToken($pdo);
unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_login_at']);
session_destroy();
redirect('login.php');
