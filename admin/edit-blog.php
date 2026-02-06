<?php
// This file redirects to add-blog.php with the ID parameter
// The add-blog.php file handles both creating and editing

if (!isset($_GET['id'])) {
    header('Location: blogs.php');
    exit;
}

// Include the add-blog.php file which handles both add and edit
include 'add-blog.php';
