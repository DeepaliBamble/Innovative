<?php
require_once __DIR__ . '/includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/account-orders.php';
    setFlashMessage('error', 'Please login to view order details');
    redirect('/login.php');
    exit;
}

// Get current user data
$userId = getCurrentUserId();
$stmt = $pdo->prepare('SELECT id, name, email, phone FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('/login.php');
    exit;
}

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    setFlashMessage('error', 'Invalid order');
    redirect('/account-orders.php');
    exit;
}

// Fetch order details - make sure it belongs to this user
$orderStmt = $pdo->prepare('
    SELECT o.*, a.full_name as shipping_name, a.address_line1, a.address_line2, a.city, a.state, a.postal_code
    FROM orders o
    LEFT JOIN addresses a ON o.shipping_address_id = a.id
    WHERE o.id = ? AND o.user_id = ?
');
$orderStmt->execute([$orderId, $userId]);
$order = $orderStmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Order not found');
    redirect('/account-orders.php');
    exit;
}

// Fetch order items
$itemsStmt = $pdo->prepare('
    SELECT oi.*, p.image_path, p.slug as product_slug, c.name as category_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE oi.order_id = ?
');
$itemsStmt->execute([$orderId]);
$orderItems = $itemsStmt->fetchAll();

// Get first item for display
$firstItem = $orderItems[0] ?? null;

// Determine status class and label (PHP 7.x compatible)
switch($order['order_status']) {
    case 'delivered':
        $statusClass = 'bg-success';
        break;
    case 'pending':
        $statusClass = 'bg-warning';
        break;
    case 'processing':
    case 'shipped':
        $statusClass = 'bg-primary';
        break;
    case 'cancelled':
        $statusClass = 'bg-danger';
        break;
    default:
        $statusClass = 'bg-secondary';
}
$statusLabel = ucfirst($order['order_status']);

// Build shipping address string
$shippingAddress = '';
if ($order['address_line1']) {
    $shippingAddress = $order['address_line1'];
    if ($order['address_line2']) $shippingAddress .= ', ' . $order['address_line2'];
    $shippingAddress .= ', ' . $order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code'];
}
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Order Details - Innovative Homesi | Track Your Order</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="View detailed information about your Innovative Homesi furniture order and delivery status.">
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

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

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
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 d-none d-xl-block">
                        <div class="sidebar-account sidebar-content-wrap sticky-top">
                            <div class="account-author">
                                <div class="author_avatar">
                                    <div class="image">
                                        <img id="imgDash" class="lazyload" src="images/avatar/avatar-4.jpg" data-src="images/avatar/avatar-4.jpg"
                                            alt="Avatar">
                                    </div>
                                    <input type="file" id="fileInputDash" accept="image/*" style="display: none;">
                                    <div class="btn-change_img box-icon" id="changeImgDash">
                                        <i class="icon icon-camera"></i>
                                    </div>
                                </div>
                                <h4 class="author_name"><?php echo htmlspecialchars($user['name']); ?></h4>
                                <p class="author_email h6"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <ul class="my-account-nav">
                                <li>
                                    <a href="account-page.php" class="my-account-nav_item h5">
                                        <i class="icon icon-circle-four"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="account-orders.php" class="my-account-nav_item h5 active">
                                        <i class="icon icon-box-arrow-down"></i>
                                        Orders
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
                        <div class="my-account-content flat-animate-tab">
                            <div class="account-order_detail">
                                <div class="order-detail_image">
                                    <?php $imagePath = $firstItem['image_path'] ?? 'images/products/placeholder.jpg'; ?>
                                    <img class="lazyload" src="<?php echo htmlspecialchars($imagePath); ?>" data-src="<?php echo htmlspecialchars($imagePath); ?>" alt="">
                                </div>
                                <div class="order-detail_content tf-grid-layout">
                                    <div class="detail-content_info">
                                        <div class="detail-info_status <?php echo $statusClass; ?> h6">
                                            <?php echo $statusLabel; ?>
                                        </div>
                                        <div class="detail-info_prd">
                                            <p class="prd_name h4 text-black"><?php echo htmlspecialchars($firstItem['product_name'] ?? 'Product'); ?></p>
                                            <div class="price-wrap">
                                                <span class="price-new h6 text-main fw-semibold"><?php echo formatPrice($order['total_amount']); ?></span>
                                            </div>
                                        </div>
                                        <div class="detail-info_item">
                                            <p class="info-item_label">Order Number</p>
                                            <p class="info-item_value">#<?php echo htmlspecialchars($order['order_number']); ?></p>
                                        </div>
                                        <div class="detail-info_item">
                                            <p class="info-item_label">Order date</p>
                                            <p class="info-item_value"><?php echo formatDate($order['created_at'], 'F j, Y - H:i'); ?></p>
                                        </div>
                                        <div class="detail-info_item">
                                            <p class="info-item_label">Items</p>
                                            <p class="info-item_value"><?php echo count($orderItems); ?> item(s)</p>
                                        </div>
                                        <?php if ($shippingAddress): ?>
                                        <div class="detail-info_item">
                                            <p class="info-item_label">Address</p>
                                            <p class="info-item_value"><?php echo htmlspecialchars($shippingAddress); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="br-line d-flex"></span>
                                    <div>
                                        <a href="account-orders.php" class="tf-btn style-line">
                                            <i class="icon icon-arrow-left"></i>
                                            Back to Orders
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="account-order_tab">
                                <ul class="tab-order_detail" role="tablist">
                                    <li class="nav-tab-item" role="presentation">
                                        <a href="#order-history" data-bs-toggle="tab" class="tf-btn-line tf-btn-tab active">
                                            <span class="h4">
                                                Order history
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-tab-item" role="presentation">
                                        <a href="#item-detail" data-bs-toggle="tab" class="tf-btn-line tf-btn-tab">
                                            <span class="h4">
                                                Item details
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-tab-item" role="presentation">
                                        <a href="#courier" data-bs-toggle="tab" class="tf-btn-line tf-btn-tab">
                                            <span class="h4">
                                                Courier
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-tab-item" role="presentation">
                                        <a href="#receiver" data-bs-toggle="tab" class="tf-btn-line tf-btn-tab">
                                            <span class="h4">
                                                Receiver
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content overflow-hidden">
                                    <div class="tab-pane active show" id="order-history" role="tabpanel">
                                        <div class="order-timeline">
                                            <div class="timeline-step completed">
                                                <div class="timeline_icon">
                                                    <span class="icon">
                                                        <i class="icon-check-1"></i>
                                                    </span>
                                                </div>
                                                <div class="timeline_content">
                                                    <h5 class="step-title fw-semibold">Product shipped</h5>
                                                    <h6 class="step-date fw-normal">April 3, 2025 - 10:52</h6>
                                                    <p class="step-detail h6">
                                                        <span class="fw-semibold text-black">Shipping carrier:</span> DHL Home - Logistics
                                                    </p>
                                                    <p class="step-detail h6">
                                                        <span class="fw-semibold text-black">Estimated delivery date:</span> April 6, 2025 - 06:42
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="timeline-step completed">
                                                <div class="timeline_icon">
                                                    <span class="icon">
                                                        <i class="icon-truck"></i>
                                                    </span>
                                                </div>
                                                <div class="timeline_content">
                                                    <h5 class="step-title fw-semibold">Product returned</h5>
                                                    <h6 class="step-date fw-normal">April 4, 2025 - 14:30</h6>
                                                    <p class="step-detail h6">Return method: UPS Pickup</p>
                                                    <p class="step-detail h6">Return authorization: RA987654</p>
                                                </div>
                                            </div>
                                            <div class="timeline-step">
                                                <div class="timeline_icon">
                                                    <span class="icon">
                                                        <i class="icon-check-1"></i>
                                                    </span>
                                                </div>
                                                <div class="timeline_content">
                                                    <h5 class="step-title fw-semibold">Product delivered</h5>
                                                    <h6 class="step-date fw-normal mb-0">April 6, 2025 - 07:15</h6>
                                                </div>
                                            </div>
                                            <div class="timeline-step">
                                                <div class="timeline_icon">
                                                    <span class="icon">
                                                        <i class="icon-check-1"></i>
                                                    </span>
                                                </div>
                                                <div class="timeline_content">
                                                    <h5 class="step-title fw-semibold">Order placed</h5>
                                                    <h6 class="step-date fw-normal mb-0">April 6, 2025 - 07:15</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="item-detail" role="tabpanel">
                                        <div class="order-item_detail">
                                            <?php foreach ($orderItems as $item): ?>
                                            <div class="prd-info mb-3">
                                                <div class="info_image">
                                                    <?php $itemImage = $item['image_path'] ?? 'images/products/placeholder.jpg'; ?>
                                                    <img class="lazyload" src="<?php echo htmlspecialchars($itemImage); ?>"
                                                        data-src="<?php echo htmlspecialchars($itemImage); ?>" alt="Product">
                                                </div>
                                                <div class="info_detail">
                                                    <a href="product-detail.php" class="link info-name h4"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                                    <p class="info-price">Price: <span class="fw-semibold h6 text-black"><?php echo formatPrice($item['price']); ?></span></p>
                                                    <p class="info-variant">Quantity: <span class="fw-semibold h6 text-black"><?php echo (int)$item['quantity']; ?></span></p>
                                                    <p class="info-variant">Subtotal: <span class="fw-semibold h6 text-black"><?php echo formatPrice($item['subtotal']); ?></span></p>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            <div class="prd-price mt-4">
                                                <div class="price_total">
                                                    <span>Subtotal:</span>
                                                    <span class="fw-semibold h6 text-black"><?php echo formatPrice($order['subtotal']); ?></span>
                                                </div>
                                                <?php if ($order['tax_amount'] > 0): ?>
                                                <p class="price_dis">
                                                    <span>Tax:</span>
                                                    <span class="fw-semibold h6 text-black"><?php echo formatPrice($order['tax_amount']); ?></span>
                                                </p>
                                                <?php endif; ?>
                                                <?php if ($order['shipping_amount'] > 0): ?>
                                                <p class="price_dis">
                                                    <span>Shipping:</span>
                                                    <span class="fw-semibold h6 text-black"><?php echo formatPrice($order['shipping_amount']); ?></span>
                                                </p>
                                                <?php endif; ?>
                                                <?php if ($order['discount_amount'] > 0): ?>
                                                <p class="price_dis">
                                                    <span>Discount:</span>
                                                    <span class="fw-semibold h6 text-black">-<?php echo formatPrice($order['discount_amount']); ?></span>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="prd-order_total">
                                                <span>Order total</span>
                                                <span class="fw-semibold h6 text-black"><?php echo formatPrice($order['total_amount']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="courier" role="tabpanel">
                                        <p class="h6 text-courier h6">
                                            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer convallis velit erat, at bibendum leo
                                            lacinia faucibus. Donec quis eleifend enim. Phasellus hendrerit pellentesque augue ac scelerisque. Nunc
                                            tristique maximus dignissim. Sed porta facilisis augue, iaculis ullamcorper magna fringilla nec. In hac
                                            habitasse platea dictumst. Aenean quam lectus, vulputate non ultrices nec, ornare a velit. Vestibulum
                                            lobortis felis non fringilla cursus. Suspendisse odio est, fermentum quis sodales ut, hendrerit ut velit.
                                            Pellentesque pellentesque ligula et elit placerat, quis finibus quam consequat.
                                        </p>
                                    </div>
                                    <div class="tab-pane" id="receiver" role="tabpanel">
                                        <div class="order-receiver">
                                            <div class="recerver_text h6">
                                                <span class="text">Order Number:</span>
                                                <span class="text_info">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                            </div>
                                            <div class="recerver_text h6">
                                                <span class="text">Date:</span>
                                                <span class="text_info"><?php echo formatDate($order['created_at'], 'F j, Y - H:i'); ?></span>
                                            </div>
                                            <div class="recerver_text h6">
                                                <span class="text">Total:</span>
                                                <span class="text_info"><?php echo formatPrice($order['total_amount']); ?></span>
                                            </div>
                                            <div class="recerver_text h6">
                                                <span class="text">Payment Method:</span>
                                                <span class="text_info"><?php echo htmlspecialchars($order['payment_method'] ?? 'Not specified'); ?></span>
                                            </div>
                                            <div class="recerver_text h6">
                                                <span class="text">Payment Status:</span>
                                                <span class="text_info"><?php echo ucfirst($order['payment_status']); ?></span>
                                            </div>
                                            <?php if ($order['shipping_name']): ?>
                                            <div class="recerver_text h6">
                                                <span class="text">Ship To:</span>
                                                <span class="text_info"><?php echo htmlspecialchars($order['shipping_name']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($shippingAddress): ?>
                                            <div class="recerver_text h6">
                                                <span class="text">Address:</span>
                                                <span class="text_info"><?php echo htmlspecialchars($shippingAddress); ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

