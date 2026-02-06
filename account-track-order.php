<?php
require_once __DIR__ . '/includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/account-track-order.php';
    setFlashMessage('error', 'Please login to track your orders');
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

$orderDetails = null;
$trackingError = '';
$searchedOrderNumber = '';

// Handle order tracking search
if (isset($_GET['order_number']) && !empty($_GET['order_number'])) {
    $searchedOrderNumber = trim($_GET['order_number']);

    // Remove # if present
    $searchedOrderNumber = ltrim($searchedOrderNumber, '#');

    // Fetch order details
    $orderStmt = $pdo->prepare('
        SELECT o.*,
               a.full_name as shipping_name, a.phone as shipping_phone,
               a.address_line1, a.address_line2, a.city, a.state, a.postal_code, a.country
        FROM orders o
        LEFT JOIN addresses a ON o.shipping_address_id = a.id
        WHERE o.order_number = ? AND o.user_id = ?
    ');
    $orderStmt->execute([$searchedOrderNumber, $userId]);
    $orderDetails = $orderStmt->fetch();

    if ($orderDetails) {
        // Fetch order items
        $itemsStmt = $pdo->prepare('
            SELECT oi.*, p.image_path, p.slug as product_slug
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ');
        $itemsStmt->execute([$orderDetails['id']]);
        $orderDetails['items'] = $itemsStmt->fetchAll();
    } else {
        $trackingError = 'Order not found. Please check the order number and try again.';
    }
}

// Get recent orders for quick access
$recentOrdersStmt = $pdo->prepare('
    SELECT order_number, order_status, created_at, total_amount
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
');
$recentOrdersStmt->execute([$userId]);
$recentOrders = $recentOrdersStmt->fetchAll();

// Function to get status step number
function getStatusStep($status) {
    $steps = [
        'pending' => 1,
        'processing' => 2,
        'shipped' => 3,
        'delivered' => 4,
        'cancelled' => 0
    ];
    return isset($steps[$status]) ? $steps[$status] : 1;
}
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Track Order - Innovative Homesi | Order Tracking</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Track your Innovative Homesi furniture orders and see delivery status updates.">
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

        /* Track Order Content */
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

        /* Track Order Form */
        .track-order-form {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .track-order-form .form-control {
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 16px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .track-order-form .form-control:focus {
            border-color: #9e6747;
            box-shadow: 0 0 0 3px rgba(158, 103, 71, 0.1);
        }

        .track-order-form .btn-track {
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            border: none;
            padding: 14px 30px;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .track-order-form .btn-track:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(158, 103, 71, 0.3);
        }

        /* Order Progress Timeline */
        .order-progress {
            padding: 40px 0;
            position: relative;
        }

        .progress-track {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 30px;
        }

        .progress-track::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e9ecef;
            z-index: 1;
        }

        .progress-track .progress-line {
            position: absolute;
            top: 25px;
            left: 0;
            height: 4px;
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            z-index: 2;
            transition: width 0.5s ease;
        }

        .progress-step {
            position: relative;
            z-index: 3;
            text-align: center;
            flex: 1;
        }

        .progress-step .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 20px;
            color: #6c757d;
            transition: all 0.3s ease;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .progress-step.active .step-icon,
        .progress-step.completed .step-icon {
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            color: #fff;
        }

        .progress-step.completed .step-icon {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }

        .progress-step .step-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }

        .progress-step.active .step-label,
        .progress-step.completed .step-label {
            color: #333;
            font-weight: 600;
        }

        .progress-step .step-date {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        /* Order Details Card */
        .order-details-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e9ecef;
        }

        .order-details-card .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 16px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 16px;
        }

        .order-details-card .order-number {
            font-size: 18px;
            font-weight: 600;
            color: #9e6747;
        }

        .order-details-card .order-date {
            color: #6c757d;
            font-size: 14px;
        }

        .order-status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending { background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); color: #f39c12; }
        .status-processing { background: linear-gradient(135deg, #d6e9f8 0%, #b3d7f2 100%); color: #3498db; }
        .status-shipped { background: linear-gradient(135deg, #e8daef 0%, #d7bde2 100%); color: #9b59b6; }
        .status-delivered { background: linear-gradient(135deg, #d4edda 0%, #a8e6cf 100%); color: #27ae60; }
        .status-cancelled { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #e74c3c; }

        /* Order Items */
        .order-items-list {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 16px;
            background: #fff;
            border-radius: 12px;
            margin-bottom: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .order-item .item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            margin-right: 16px;
            flex-shrink: 0;
        }

        .order-item .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .order-item .item-details {
            flex: 1;
        }

        .order-item .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .order-item .item-meta {
            font-size: 14px;
            color: #6c757d;
        }

        .order-item .item-price {
            font-weight: 600;
            color: #9e6747;
            font-size: 16px;
        }

        /* Shipping Info */
        .shipping-info {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e9ecef;
        }

        .shipping-info h5 {
            color: #9e6747;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .shipping-info p {
            color: #6c757d;
            margin-bottom: 4px;
            font-size: 14px;
        }

        /* Recent Orders Quick Access */
        .recent-orders-sidebar {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e9ecef;
        }

        .recent-orders-sidebar h5 {
            color: #9e6747;
            margin-bottom: 16px;
            font-size: 16px;
        }

        .recent-order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .recent-order-item:hover {
            background: linear-gradient(135deg, #9e6747 0%, #b07d5e 100%);
            color: #fff;
        }

        .recent-order-item:hover .order-status-mini {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .recent-order-item .order-num {
            font-weight: 600;
            font-size: 14px;
        }

        .order-status-mini {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 12px;
            background: #e9ecef;
            text-transform: capitalize;
        }

        /* Error Message */
        .track-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .track-error i {
            font-size: 20px;
        }

        /* Cancelled Order */
        .cancelled-notice {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }

        .cancelled-notice i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
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

        @media (max-width: 991px) {
            .progress-step .step-label {
                font-size: 12px;
            }

            .progress-step .step-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }

        @media (max-width: 767px) {
            .my-account-content {
                padding: 20px;
            }

            .track-order-form {
                padding: 20px;
            }

            .order-item {
                flex-direction: column;
                text-align: center;
            }

            .order-item .item-image {
                margin-right: 0;
                margin-bottom: 12px;
            }

            .progress-track {
                flex-direction: column;
                gap: 20px;
            }

            .progress-track::before {
                top: 0;
                bottom: 0;
                left: 25px;
                width: 4px;
                height: auto;
            }

            .progress-track .progress-line {
                top: 0;
                left: 25px;
                width: 4px !important;
                height: var(--progress-height, 0%) !important;
            }

            .progress-step {
                display: flex;
                align-items: center;
                text-align: left;
                gap: 16px;
            }

            .progress-step .step-icon {
                margin: 0;
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
                    <h1 class="title-page">Track Order</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li><a href="account-page.php" class="h6 link">My account</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Track Order</h6>
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
                                    <p class="my-account-nav_item h5 active">
                                        <i class="fas fa-shipping-fast"></i>
                                        Track Order
                                    </p>
                                </li>
                                <li>
                                    <a href="account-wishlist.php" class="my-account-nav_item h5">
                                        <i class="fas fa-heart"></i>
                                        Wishlist
                                    </a>
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
                            <h2 class="account-title type-semibold">Track Your Order</h2>

                            <!-- Track Order Form -->
                            <div class="track-order-form">
                                <form action="" method="GET">
                                    <div class="row align-items-end">
                                        <div class="col-md-8 mb-3 mb-md-0">
                                            <label class="form-label h6">Enter Order Number</label>
                                            <input type="text" name="order_number" class="form-control"
                                                   placeholder="e.g., ORD-20251215-ABC123"
                                                   value="<?php echo htmlspecialchars($searchedOrderNumber); ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-track w-100">
                                                <i class="fas fa-search me-2"></i> Track Order
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <?php if ($trackingError): ?>
                            <div class="track-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?php echo htmlspecialchars($trackingError); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($orderDetails): ?>
                            <!-- Order Found - Show Details -->
                            <div class="order-details-card">
                                <div class="order-header">
                                    <div>
                                        <span class="order-number">#<?php echo htmlspecialchars($orderDetails['order_number']); ?></span>
                                        <div class="order-date">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Ordered on <?php echo date('M d, Y \a\t h:i A', strtotime($orderDetails['created_at'])); ?>
                                        </div>
                                    </div>
                                    <span class="order-status-badge status-<?php echo $orderDetails['order_status']; ?>">
                                        <?php echo ucfirst($orderDetails['order_status']); ?>
                                    </span>
                                </div>

                                <?php if ($orderDetails['order_status'] === 'cancelled'): ?>
                                <div class="cancelled-notice">
                                    <i class="fas fa-times-circle"></i>
                                    <h5>This order has been cancelled</h5>
                                    <p class="mb-0">If you have any questions, please contact our support team.</p>
                                </div>
                                <?php else: ?>
                                <!-- Order Progress Timeline -->
                                <div class="order-progress">
                                    <?php
                                    $currentStep = getStatusStep($orderDetails['order_status']);
                                    $progressWidth = ($currentStep / 4) * 100;
                                    ?>
                                    <div class="progress-track">
                                        <div class="progress-line" style="width: <?php echo $progressWidth; ?>%"></div>

                                        <div class="progress-step <?php echo $currentStep >= 1 ? ($currentStep > 1 ? 'completed' : 'active') : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-clipboard-check"></i>
                                            </div>
                                            <div class="step-label">Order Placed</div>
                                            <div class="step-date"><?php echo date('M d', strtotime($orderDetails['created_at'])); ?></div>
                                        </div>

                                        <div class="progress-step <?php echo $currentStep >= 2 ? ($currentStep > 2 ? 'completed' : 'active') : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <div class="step-label">Processing</div>
                                            <div class="step-date"><?php echo $currentStep >= 2 ? 'In Progress' : 'Pending'; ?></div>
                                        </div>

                                        <div class="progress-step <?php echo $currentStep >= 3 ? ($currentStep > 3 ? 'completed' : 'active') : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-truck"></i>
                                            </div>
                                            <div class="step-label">Shipped</div>
                                            <div class="step-date"><?php echo $currentStep >= 3 ? 'On the way' : 'Pending'; ?></div>
                                        </div>

                                        <div class="progress-step <?php echo $currentStep >= 4 ? 'completed' : ''; ?>">
                                            <div class="step-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="step-label">Delivered</div>
                                            <div class="step-date"><?php echo $currentStep >= 4 ? 'Completed' : 'Pending'; ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Order Items -->
                                <div class="order-items-list">
                                    <h5 class="mb-3"><i class="fas fa-box me-2"></i>Order Items</h5>
                                    <?php foreach ($orderDetails['items'] as $item):
                                        $itemImage = !empty($item['image_path']) ? $item['image_path'] : 'images/products/placeholder.jpg';
                                    ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <img src="<?php echo htmlspecialchars($itemImage); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                        </div>
                                        <div class="item-details">
                                            <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                            <div class="item-meta">
                                                SKU: <?php echo htmlspecialchars($item['product_sku'] ?? 'N/A'); ?> |
                                                Qty: <?php echo (int)$item['quantity']; ?>
                                            </div>
                                        </div>
                                        <div class="item-price"><?php echo formatPrice($item['subtotal']); ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="row mt-4">
                                    <!-- Shipping Address -->
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="shipping-info">
                                            <h5><i class="fas fa-map-marker-alt"></i> Shipping Address</h5>
                                            <?php if ($orderDetails['shipping_name']): ?>
                                            <p><strong><?php echo htmlspecialchars($orderDetails['shipping_name']); ?></strong></p>
                                            <p><?php echo htmlspecialchars($orderDetails['address_line1']); ?></p>
                                            <?php if ($orderDetails['address_line2']): ?>
                                            <p><?php echo htmlspecialchars($orderDetails['address_line2']); ?></p>
                                            <?php endif; ?>
                                            <p><?php echo htmlspecialchars($orderDetails['city'] . ', ' . $orderDetails['state'] . ' ' . $orderDetails['postal_code']); ?></p>
                                            <p><?php echo htmlspecialchars($orderDetails['country']); ?></p>
                                            <p><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($orderDetails['shipping_phone']); ?></p>
                                            <?php else: ?>
                                            <p>No shipping address on file</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Order Summary -->
                                    <div class="col-md-6">
                                        <div class="shipping-info">
                                            <h5><i class="fas fa-receipt"></i> Order Summary</h5>
                                            <p><span class="text-dark">Subtotal:</span> <strong><?php echo formatPrice($orderDetails['subtotal']); ?></strong></p>
                                            <p><span class="text-dark">Tax:</span> <strong><?php echo formatPrice($orderDetails['tax_amount']); ?></strong></p>
                                            <p><span class="text-dark">Shipping:</span> <strong><?php echo formatPrice($orderDetails['shipping_amount']); ?></strong></p>
                                            <?php if ($orderDetails['discount_amount'] > 0): ?>
                                            <p><span class="text-dark">Discount:</span> <strong class="text-success">-<?php echo formatPrice($orderDetails['discount_amount']); ?></strong></p>
                                            <?php endif; ?>
                                            <hr>
                                            <p class="mb-0"><span class="text-dark h6">Total:</span> <strong class="h5" style="color: #9e6747;"><?php echo formatPrice($orderDetails['total_amount']); ?></strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Recent Orders Quick Access -->
                            <?php if (!empty($recentOrders) && !$orderDetails): ?>
                            <div class="recent-orders-sidebar mt-4">
                                <h5><i class="fas fa-history me-2"></i>Your Recent Orders</h5>
                                <?php foreach ($recentOrders as $order): ?>
                                <a href="?order_number=<?php echo urlencode($order['order_number']); ?>" class="recent-order-item">
                                    <div>
                                        <div class="order-num">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small>
                                    </div>
                                    <span class="order-status-mini"><?php echo ucfirst($order['order_status']); ?></span>
                                </a>
                                <?php endforeach; ?>
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
