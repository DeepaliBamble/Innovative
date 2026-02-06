<?php
require __DIR__ . '/../includes/init.php';
unset($_SESSION['admin_id']);
session_destroy();
redirect('login.php');
