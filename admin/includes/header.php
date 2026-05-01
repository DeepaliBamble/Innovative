<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get current page for active menu state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Innovative Admin - Professional Furniture Management</title>

    <!-- Favicon -->
    <link rel="icon" href="../images/logo/logo.png" type="image/png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap CSS -->
    <link href="assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Admin CSS -->
    <link href="assets/css/admin-custom.css" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <img src="../images/logo/logo.png" alt="Innovative Furniture">
                <h2>Innovative</h2>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="products.php" class="<?= $current_page === 'products.php' ? 'active' : '' ?>">
                            <i class="bi bi-box-seam"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="add-product.php" class="<?= $current_page === 'add-product.php' ? 'active' : '' ?>">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add Product</span>
                        </a>
                    </li>
                    <li>
                        <a href="import-products.php" class="<?= $current_page === 'import-products.php' ? 'active' : '' ?>">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>Import Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="export-products-page.php" class="<?= $current_page === 'export-products-page.php' ? 'active' : '' ?>">
                            <i class="bi bi-file-earmark-arrow-down"></i>
                            <span>Export Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" class="<?= $current_page === 'orders.php' ? 'active' : '' ?>">
                            <i class="bi bi-cart-check"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="<?= in_array($current_page, ['users.php', 'edit-user.php']) ? 'active' : '' ?>">
                            <i class="bi bi-people"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="reviews.php" class="<?= $current_page === 'reviews.php' ? 'active' : '' ?>">
                            <i class="bi bi-star"></i>
                            <span>Reviews</span>
                        </a>
                    </li>
                    <li>
                        <a href="contact-messages.php" class="<?= $current_page === 'contact-messages.php' ? 'active' : '' ?>">
                            <i class="bi bi-envelope"></i>
                            <span>Contact Messages</span>
                        </a>
                    </li>
                    <li>
                        <a href="customise-enquiries.php" class="<?= $current_page === 'customise-enquiries.php' ? 'active' : '' ?>">
                            <i class="bi bi-pencil-square"></i>
                            <span>Customise Enquiries</span>
                        </a>
                    </li>
                    <li>
                        <a href="blogs.php" class="<?= $current_page === 'blogs.php' || $current_page === 'add-blog.php' || $current_page === 'edit-blog.php' ? 'active' : '' ?>">
                            <i class="bi bi-newspaper"></i>
                            <span>Blogs</span>
                        </a>
                    </li>
                    <li>
                        <a href="blog-comments.php" class="<?= $current_page === 'blog-comments.php' ? 'active' : '' ?>">
                            <i class="bi bi-chat-square-text"></i>
                            <span>Blog Comments</span>
                        </a>
                    </li>
                    <li>
                        <a href="gallery.php" class="<?= $current_page === 'gallery.php' ? 'active' : '' ?>">
                            <i class="bi bi-images"></i>
                            <span>Gallery</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="admin-main">
            <!-- Top Navigation Bar -->
            <header class="admin-topbar">
                <div class="topbar-title">
                    <h1><?= ucfirst(str_replace(['.php', '-'], ['', ' '], $current_page)) ?></h1>
                </div>
                <div class="topbar-actions">
                    <div class="topbar-user">
                        <i class="bi bi-person-circle" style="font-size: 1.5rem;"></i>
                        <span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
                    </div>
                </div>
            </header>

            <?php include __DIR__ . '/admin-tabs.php'; ?>

            <!-- Page Content -->
            <main class="admin-content">
