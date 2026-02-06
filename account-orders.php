<?php
require_once __DIR__ . '/includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/account-orders.php';
    setFlashMessage('error', 'Please login to access your orders');
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

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$ordersPerPage = 10;

$totalOrdersStmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$totalOrdersStmt->execute([$userId]);
$totalOrders = $totalOrdersStmt->fetchColumn();
$pagination = paginate($totalOrders, $ordersPerPage, $page);

// Fetch orders with first product info
$ordersStmt = $pdo->prepare('
    SELECT o.id, o.order_number, o.total_amount, o.order_status, o.created_at,
           oi.product_name, oi.quantity,
           p.image_path, c.name as category_name
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
');
$ordersStmt->execute([$userId, $ordersPerPage, $pagination['offset']]);
$orders = $ordersStmt->fetchAll();
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>My Orders - Innovative Homesi | Order History</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="View and track your Innovative Homesi furniture orders and purchase history.">
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

        /* Orders Content Enhancement */
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

        .table-my_order thead {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .table-my_order thead th {
            padding: 16px;
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #9e6747;
        }

        .table-my_order tbody tr {
            transition: all 0.3s ease;
        }

        .table-my_order tbody tr:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .tb-order_status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }

        .stt-pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #f39c12;
        }

        .stt-complete {
            background: linear-gradient(135deg, #d4edda 0%, #a8e6cf 100%);
            color: #27ae60;
        }

        .stt-delivery {
            background: linear-gradient(135deg, #d6e9f8 0%, #b3d7f2 100%);
            color: #3498db;
        }

        .stt-cancel {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #e74c3c;
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
                    <h1 class="title-page">My Account</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">My account</h6>
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
                                    <p class="my-account-nav_item h5 active">
                                        <i class="icon icon-box-arrow-down"></i>
                                        Orders
                                    </p>
                                </li>
                                <li>
                                    <a href="account-track-order.php" class="my-account-nav_item h5">
                                        <i class="fas fa-shipping-fast"></i>
                                        Track Order
                                    </a>
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
                            <h2 class="account-title type-semibold">My Order</h2>
                            <div class="overflow-auto">
                                <table class="table-my_order">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Products</th>
                                            <th>Pricing</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($orders)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <p class="h6">No orders yet. <a href="shop.php" class="link">Start shopping</a></p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($orders as $order):
                                            // Determine status class (PHP 7.x compatible)
                                            switch($order['order_status']) {
                                                case 'delivered':
                                                    $statusClass = 'stt-complete';
                                                    break;
                                                case 'pending':
                                                    $statusClass = 'stt-pending';
                                                    break;
                                                case 'processing':
                                                case 'shipped':
                                                    $statusClass = 'stt-delivery';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'stt-cancel';
                                                    break;
                                                default:
                                                    $statusClass = 'stt-pending';
                                            }
                                            $statusLabel = ucfirst($order['order_status']);
                                            $imagePath = isset($order['image_path']) ? $order['image_path'] : 'images/products/placeholder.jpg';
                                        ?>
                                        <tr class="tb-order-item">
                                            <td class="tb-order_code">#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td>
                                                <div class="tb-order_product">
                                                    <a href="product-detail.php" class="img-prd">
                                                        <img class="lazyload" src="<?php echo htmlspecialchars($imagePath); ?>"
                                                            data-src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product">
                                                    </a>
                                                    <div class="infor-prd">
                                                        <h6>
                                                            <a href="product-detail.php" class="prd_name link">
                                                                <?php echo htmlspecialchars($order['product_name'] ?? 'Product'); ?>
                                                            </a>
                                                        </h6>
                                                        <p class="prd_select text-small">
                                                            <?php echo htmlspecialchars($order['category_name'] ?? 'Furniture'); ?>
                                                            <span>Qty: <?php echo (int)($order['quantity'] ?? 1); ?></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="tb-order_price"><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <div class="tb-order_status <?php echo $statusClass; ?>">
                                                    <?php echo $statusLabel; ?>
                                                </div>
                                            </td>
                                            <td class="tb-order_action">
                                                <a href="account-orders-detail.php?id=<?php echo $order['id']; ?>" class="link fw-semibold">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="wd-full wg-pagination">
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
    <script src="js/count-down.js"></script>
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

