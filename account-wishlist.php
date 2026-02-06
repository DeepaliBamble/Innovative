<?php
require_once __DIR__ . '/includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/account-wishlist.php';
    setFlashMessage('error', 'Please login to view your wishlist');
    redirect('/login.php');
    exit;
}

// Get current user data
$userId = getCurrentUserId();
$stmt = $pdo->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('/login.php');
    exit;
}

// Handle wishlist removal
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $productId = (int)$_GET['remove'];
    $deleteStmt = $pdo->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?');
    $deleteStmt->execute([$userId, $productId]);
    setFlashMessage('success', 'Item removed from wishlist');
    redirect('/account-wishlist.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$itemsPerPage = 12;

// Get total wishlist count
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
$countStmt->execute([$userId]);
$totalItems = $countStmt->fetchColumn();
$pagination = paginate($totalItems, $itemsPerPage, $page);

// Get wishlist items with product details
$wishlistStmt = $pdo->prepare('
    SELECT w.id as wishlist_id, w.created_at as added_at,
           p.id as product_id, p.name, p.slug, p.price, p.sale_price, p.image_path,
           p.stock_quantity, p.is_active,
           c.name as category_name
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
    LIMIT ? OFFSET ?
');
$wishlistStmt->execute([$userId, $itemsPerPage, $pagination['offset']]);
$wishlistItems = $wishlistStmt->fetchAll();
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>My Wishlist - Innovative Homesi</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="View and manage your saved items in your Innovative Homesi wishlist.">
    <meta name="robots" content="noindex, nofollow">

    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=GFS+Neohellenic:ital,wght@0,400;0,700;1,400;1,700&family=Luxurious+Roman&family=Maven+Pro:wght@400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="stylesheet" href="icon/icomoon/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- css -->
    <link rel="stylesheet" href="../sibforms.com/forms/end-form/build/sib-styles.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/shop-custom-additions.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

    <style>
        /* Account Page Enhancements */
        .user-avatar-modern {
            position: relative;
            overflow: visible !important;
        }

        .user-avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 48px;
            box-shadow: 0 8px 24px rgba(158, 103, 71, 0.3);
            transition: all 0.3s ease;
        }

        .user-avatar-placeholder:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 32px rgba(158, 103, 71, 0.4);
        }

        .author_avatar {
            position: relative;
            margin-bottom: 20px;
        }

        .author_avatar .image {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #f8f9fa;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .btn-change_img {
            position: absolute;
            bottom: 10px;
            right: calc(50% - 60px);
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            color: #fff !important;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(158, 103, 71, 0.3);
            transition: all 0.3s ease;
            border: 3px solid #fff;
        }

        .btn-change_img:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(158, 103, 71, 0.4);
        }

        .member-since {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }

        .member-since small {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
        }

        .member-since i {
            color: #9e6747;
        }

        /* Sidebar Enhancement */
        .sidebar-account {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 20px;
            padding: 30px 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }

        .my-account-nav_item {
            padding: 14px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #495057;
            text-decoration: none;
            margin-bottom: 8px;
        }

        .my-account-nav_item:hover,
        .my-account-nav_item.active {
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            color: #fff !important;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(158, 103, 71, 0.2);
        }

        .my-account-nav_item i {
            font-size: 18px;
            width: 24px;
        }

        /* Wishlist Content */
        .my-account-content {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
        }

        .account-title {
            color: #9e6747;
            margin-bottom: 24px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .account-title::before {
            content: '';
            width: 4px;
            height: 28px;
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            border-radius: 2px;
        }

        /* Wishlist Stats */
        .wishlist-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .wishlist-stat {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            padding: 16px 24px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .wishlist-stat i {
            font-size: 24px;
            color: #9e6747;
        }

        .wishlist-stat .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }

        .wishlist-stat .stat-label {
            font-size: 14px;
            color: #6c757d;
        }

        /* Wishlist Grid */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        /* Wishlist Card */
        .wishlist-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
        }

        .wishlist-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .wishlist-card .card-image {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .wishlist-card .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .wishlist-card:hover .card-image img {
            transform: scale(1.05);
        }

        .wishlist-card .card-badges {
            position: absolute;
            top: 12px;
            left: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .wishlist-card .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-sale {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: #fff;
        }

        .badge-out-of-stock {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: #fff;
        }

        .wishlist-card .remove-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #e74c3c;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .wishlist-card .remove-btn:hover {
            background: #e74c3c;
            color: #fff;
            transform: scale(1.1);
        }

        .wishlist-card .card-body {
            padding: 20px;
        }

        .wishlist-card .card-category {
            font-size: 12px;
            color: #9e6747;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .wishlist-card .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .wishlist-card .card-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .wishlist-card .card-title a:hover {
            color: #9e6747;
        }

        .wishlist-card .card-price {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .wishlist-card .current-price {
            font-size: 20px;
            font-weight: 700;
            color: #9e6747;
        }

        .wishlist-card .original-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
        }

        .wishlist-card .card-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 16px;
        }

        .wishlist-card .card-actions {
            display: flex;
            gap: 10px;
        }

        .wishlist-card .btn-add-cart {
            flex: 1;
            padding: 12px 16px;
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .wishlist-card .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(158, 103, 71, 0.3);
        }

        .wishlist-card .btn-add-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .wishlist-card .btn-view {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .wishlist-card .btn-view:hover {
            background: #9e6747;
            border-color: #9e6747;
            color: #fff;
        }

        /* Empty Wishlist */
        .empty-wishlist {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-wishlist i {
            font-size: 80px;
            color: #e9ecef;
            margin-bottom: 24px;
        }

        .empty-wishlist h4 {
            color: #333;
            margin-bottom: 12px;
        }

        .empty-wishlist p {
            color: #6c757d;
            margin-bottom: 24px;
        }

        .empty-wishlist .btn-shop {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 30px;
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-wishlist .btn-shop:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(158, 103, 71, 0.3);
            color: #fff;
        }

        /* Flash Messages */
        .flash-message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .flash-success {
            background: linear-gradient(135deg, #d4edda 0%, #a8e6cf 100%);
            color: #155724;
        }

        .flash-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        /* Responsive */
        @media (max-width: 1199px) {
            .author_avatar .image {
                width: 100px;
                height: 100px;
            }

            .user-avatar-placeholder {
                font-size: 38px;
            }

            .btn-change_img {
                right: calc(50% - 50px);
            }
        }

        @media (max-width: 767px) {
            .my-account-content {
                padding: 20px;
            }

            .wishlist-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 16px;
            }

            .wishlist-card .card-image {
                height: 180px;
            }
        }

        @media (max-width: 575px) {
            .wishlist-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation for page load */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar-account,
        .my-account-content {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .my-account-content {
            animation-delay: 0.2s;
        }

        .wishlist-card {
            animation: fadeInUp 0.5s ease-out forwards;
        }
    </style>

</head>

<body>

    <!-- Scroll Top -->
    <button id="goTop">
        <span class="border-progress"></span>
        <span class="icon icon-caret-up"></span>
    </button>

    <div id="wrapper">
        <?php include 'includes/topbar.php'; ?>
        <?php include 'includes/header.php'; ?>
        <!-- Page Title -->
        <section class="s-page-title">
            <div class="container">
                <div class="content">
                    <h1 class="title-page">My Wishlist</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li><a href="account-page.php" class="h6 link">My account</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Wishlist</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Account -->
        <section class="flat-spacing">
            <input class="fileInputDash" type="file" accept="image/*" style="display: none;">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 d-none d-xl-block">
                        <div class="sidebar-account sidebar-content-wrap sticky-top">
                            <div class="account-author">
                                <div class="author_avatar">
                                    <div class="image user-avatar-modern">
                                        <div class="user-avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="btn-change_img box-icon changeImgDash">
                                        <i class="icon icon-camera"></i>
                                    </div>
                                </div>
                                <h4 class="author_name"><?php echo htmlspecialchars($user['name']); ?></h4>
                                <p class="author_email h6"><?php echo htmlspecialchars($user['email']); ?></p>
                                <div class="member-since">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i> Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <ul class="my-account-nav">
                                <li>
                                    <a href="account-page.php" class="my-account-nav_item h5">
                                        <i class="icon icon-circle-four"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="account-orders.php" class="my-account-nav_item h5">
                                        <i class="icon icon-box-arrow-down"></i>
                                        Orders
                                    </a>
                                </li>
                                <li>
                                    <a href="account-track-order.php" class="my-account-nav_item h5">
                                        <i class="fas fa-shipping-fast"></i>
                                        Track Order
                                    </a>
                                </li>
                                <li>
                                    <p class="my-account-nav_item h5 active">
                                        <i class="fas fa-heart"></i>
                                        Wishlist
                                    </p>
                                </li>
                                <li>
                                    <a href="account-addresses.php" class="my-account-nav_item h5">
                                        <i class="icon icon-address-book"></i>
                                        My address
                                    </a>
                                </li>
                                <li>
                                    <a href="account-setting.php" class="my-account-nav_item h5">
                                        <i class="icon icon-setting"></i>
                                        Setting
                                    </a>
                                </li>
                                <li>
                                    <a href="auth/logout.php" class="my-account-nav_item h5">
                                        <i class="icon icon-sign-out"></i>
                                        Log out
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-9">
                        <div class="my-account-content">
                            <h2 class="account-title type-semibold">My Wishlist</h2>

                            <?php
                            // Display flash messages
                            $flashMessage = getFlashMessage();
                            if ($flashMessage):
                            ?>
                            <div class="flash-message flash-<?php echo $flashMessage['type']; ?>">
                                <i class="fas fa-<?php echo $flashMessage['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                <span><?php echo htmlspecialchars($flashMessage['message']); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($totalItems > 0): ?>
                            <!-- Wishlist Stats -->
                            <div class="wishlist-stats">
                                <div class="wishlist-stat">
                                    <i class="fas fa-heart"></i>
                                    <div>
                                        <div class="stat-value"><?php echo $totalItems; ?></div>
                                        <div class="stat-label">Saved Items</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Wishlist Grid -->
                            <div class="wishlist-grid">
                                <?php foreach ($wishlistItems as $item):
                                    $imagePath = !empty($item['image_path']) ? $item['image_path'] : 'images/products/placeholder.jpg';
                                    $hasDiscount = $item['sale_price'] && $item['sale_price'] < $item['price'];
                                    $displayPrice = $hasDiscount ? $item['sale_price'] : $item['price'];
                                    $inStock = $item['stock_quantity'] > 0 && $item['is_active'];
                                ?>
                                <div class="wishlist-card" style="animation-delay: <?php echo $loop * 0.1; ?>s">
                                    <div class="card-image">
                                        <a href="product-detail.php?slug=<?php echo htmlspecialchars($item['slug']); ?>">
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </a>
                                        <div class="card-badges">
                                            <?php if ($hasDiscount): ?>
                                            <span class="badge badge-sale">
                                                -<?php echo round((($item['price'] - $item['sale_price']) / $item['price']) * 100); ?>%
                                            </span>
                                            <?php endif; ?>
                                            <?php if (!$inStock): ?>
                                            <span class="badge badge-out-of-stock">Out of Stock</span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="?remove=<?php echo $item['product_id']; ?>" class="remove-btn" title="Remove from wishlist" onclick="return confirm('Remove this item from your wishlist?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <div class="card-category"><?php echo htmlspecialchars($item['category_name'] ?? 'Furniture'); ?></div>
                                        <h5 class="card-title">
                                            <a href="product-detail.php?slug=<?php echo htmlspecialchars($item['slug']); ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </a>
                                        </h5>
                                        <div class="card-price">
                                            <span class="current-price"><?php echo formatPrice($displayPrice); ?></span>
                                            <?php if ($hasDiscount): ?>
                                            <span class="original-price"><?php echo formatPrice($item['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-meta">
                                            <i class="fas fa-calendar-plus me-1"></i>
                                            Added <?php echo date('M d, Y', strtotime($item['added_at'])); ?>
                                        </div>
                                        <div class="card-actions">
                                            <button class="btn-add-cart" <?php echo !$inStock ? 'disabled' : ''; ?>
                                                    onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                                <i class="fas fa-shopping-cart"></i>
                                                <?php echo $inStock ? 'Add to Cart' : 'Out of Stock'; ?>
                                            </button>
                                            <a href="product-detail.php?slug=<?php echo htmlspecialchars($item['slug']); ?>" class="btn-view" title="View Product">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="wd-full wg-pagination mt-4">
                                <?php if ($pagination['has_previous']): ?>
                                <a href="?page=<?php echo $pagination['current_page'] - 1; ?>" class="pagination-item h6 direct"><i class="icon icon-caret-left"></i></a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <?php if ($i == $pagination['current_page']): ?>
                                    <span class="pagination-item h6 active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>" class="pagination-item h6"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                <a href="?page=<?php echo $pagination['current_page'] + 1; ?>" class="pagination-item h6 direct"><i class="icon icon-caret-right"></i></a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <?php else: ?>
                            <!-- Empty Wishlist -->
                            <div class="empty-wishlist">
                                <i class="fas fa-heart"></i>
                                <h4>Your wishlist is empty</h4>
                                <p>Save your favorite items to your wishlist and find them here anytime.</p>
                                <a href="shop.php" class="btn-shop">
                                    <i class="fas fa-shopping-bag"></i>
                                    Start Shopping
                                </a>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Account -->
        <?php include 'includes/footer.php'; ?>
    <!-- Javascript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/main.js"></script>

    <script>
        // Add to cart function
        function addToCart(productId) {
            fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Product added to cart!');
                    // Optionally update cart count in header
                    if (typeof updateCartCount === 'function') {
                        updateCartCount();
                    }
                } else {
                    alert(data.message || 'Failed to add product to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>

    <script src="js/sibforms.js" defer></script>
    <script>
        window.REQUIRED_CODE_ERROR_MESSAGE = 'Please choose a country code';
        window.LOCALE = 'en';
        window.EMAIL_INVALID_MESSAGE = window.SMS_INVALID_MESSAGE = "The information provided is invalid. Please review the field format and try again.";

        window.REQUIRED_ERROR_MESSAGE = "This field cannot be left blank. ";

        window.GENERIC_INVALID_MESSAGE = "The information provided is invalid. Please review the field format and try again.";

        window.translation = {
            common: {
                selectedList: '{quantity} list selected',
                selectedLists: '{quantity} lists selected'
            }
        };

        var AUTOHIDE = Boolean(0);
    </script>
</body>

</html>
