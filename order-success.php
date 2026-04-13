<?php
require_once __DIR__ . '/includes/init.php';

// Get order ID from query string
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($orderId <= 0 || empty($token)) {
    setFlashMessage('error', 'Invalid order link.');
    redirect('index.php');
}

// Fetch order details
$orderStmt = $pdo->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.payment_status = 'paid'
");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setFlashMessage('error', 'Order not found or payment not confirmed.');
    redirect('index.php');
}

// Security check: Verify order access token
if (!verifyOrderAccessToken($orderId, $order['order_number'], $token)) {
    // Fallback: Verify order belongs to current user or session if token fails
    $isOwner = false;
    if (isLoggedIn() && $order['user_id'] == getCurrentUserId()) {
        $isOwner = true;
    } elseif (!isLoggedIn() && $order['session_id'] == session_id()) {
        $isOwner = true;
    }

    if (!$isOwner) {
        setFlashMessage('error', 'Access denied.');
        redirect('index.php');
    }
}

// Fetch order items
$itemsStmt = $pdo->prepare("
    SELECT oi.*, p.image_path, p.slug
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$orderId]);
$orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta charset="utf-8">
    <title>Order Confirmation - Innovative Homesi</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" type="text/css" href="css/styles.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

    <style>
        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease-out;
        }
        .success-icon i {
            color: white;
            font-size: 40px;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        .order-summary-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .order-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .order-detail-row:last-child {
            border-bottom: none;
        }
        .order-detail-label {
            font-weight: 600;
            color: #495057;
        }
        .print-btn {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #5a6268;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Scroll Top -->
    <button id="goTop" class="no-print">
        <span class="border-progress"></span>
        <span class="icon icon-caret-up"></span>
    </button>

    <!-- preload -->
    <div class="preload preload-container" id="preload">
        <div class="preload-logo">
            <div class="spinner"></div>
        </div>
    </div>
    <!-- /preload -->

    <div id="wrapper">
        <div class="no-print">
            <?php include 'includes/topbar.php'; ?>
            <?php include 'includes/header.php'; ?>
        </div>

        <!-- Order Success Content -->
        <section class="flat-spacing">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Success Message -->
                        <div class="text-center mb-5">
                            <div class="success-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <h1 class="mb-3">Order Confirmed!</h1>
                            <p class="h5 text-muted">Thank you for your purchase. Your order has been successfully placed.</p>
                            <p class="mt-3">
                                <strong>Order Number:</strong>
                                <span class="text-primary"><?= htmlspecialchars($order['order_number']) ?></span>
                            </p>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary-box">
                            <h3 class="mb-4">Order Summary</h3>

                            <div class="order-detail-row">
                                <span class="order-detail-label">Order Number:</span>
                                <span><?= htmlspecialchars($order['order_number']) ?></span>
                            </div>

                            <div class="order-detail-row">
                                <span class="order-detail-label">Order Date:</span>
                                <span><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></span>
                            </div>

                            <div class="order-detail-row">
                                <span class="order-detail-label">Payment Status:</span>
                                <span class="badge bg-success">Paid</span>
                            </div>

                            <div class="order-detail-row">
                                <span class="order-detail-label">Order Status:</span>
                                <span class="badge bg-info">Processing</span>
                            </div>

                            <div class="order-detail-row">
                                <span class="order-detail-label">Payment Method:</span>
                                <span>Razorpay</span>
                            </div>

                            <div class="order-detail-row">
                                <span class="order-detail-label">Payment ID:</span>
                                <span class="text-muted small"><?= htmlspecialchars($order['razorpay_payment_id']) ?></span>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="order-summary-box">
                            <h3 class="mb-4">Shipping Address</h3>
                            <p class="mb-1"><strong><?= htmlspecialchars($order['shipping_full_name']) ?></strong></p>
                            <p class="mb-1"><?= htmlspecialchars($order['shipping_address_line1']) ?></p>
                            <?php if ($order['shipping_address_line2']): ?>
                                <p class="mb-1"><?= htmlspecialchars($order['shipping_address_line2']) ?></p>
                            <?php endif; ?>
                            <p class="mb-1">
                                <?= htmlspecialchars($order['shipping_city']) ?>,
                                <?= htmlspecialchars($order['shipping_state']) ?>
                                <?= htmlspecialchars($order['shipping_postal_code']) ?>
                            </p>
                            <p class="mb-1"><?= htmlspecialchars($order['shipping_country']) ?></p>
                            <p class="mb-0"><strong>Phone:</strong> <?= htmlspecialchars($order['shipping_phone']) ?></p>
                        </div>

                        <!-- Order Items -->
                        <div class="order-summary-box">
                            <h3 class="mb-4">Order Items</h3>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($item['image_path']): ?>
                                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width: 60px; height: 60px; object-fit: cover; margin-right: 1rem; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                                        <?php if ($item['product_sku']): ?>
                                                            <br><small class="text-muted">SKU: <?= htmlspecialchars($item['product_sku']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= (int)$item['quantity'] ?></td>
                                            <td class="text-end">₹<?= number_format($item['price'], 2) ?></td>
                                            <td class="text-end">₹<?= number_format($item['subtotal'], 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                            <td class="text-end">₹<?= number_format($order['subtotal'], 2) ?></td>
                                        </tr>
                                        <?php if ($order['discount_amount'] > 0): ?>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                            <td class="text-end text-success">-₹<?= number_format($order['discount_amount'], 2) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                            <td class="text-end">
                                                <?php if ($order['shipping_amount'] > 0): ?>
                                                    ₹<?= number_format($order['shipping_amount'], 2) ?>
                                                <?php else: ?>
                                                    Free
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if ($order['tax_amount'] > 0): ?>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                            <td class="text-end">₹<?= number_format($order['tax_amount'], 2) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="table-active">
                                            <td colspan="3" class="text-end"><strong class="h5">Total:</strong></td>
                                            <td class="text-end"><strong class="h5">₹<?= number_format($order['total_amount'], 2) ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <?php if (!empty($order['notes'])): ?>
                        <div class="order-summary-box">
                            <h3 class="mb-3">Order Notes</h3>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="text-center mt-5 no-print">
                            <button onclick="window.print()" class="print-btn me-3">
                                <i class="fas fa-print me-2"></i>Print Order
                            </button>
                            <a href="index.php" class="tf-btn animate-btn">
                                <i class="fas fa-home me-2"></i>Continue Shopping
                            </a>
                            <?php if (isLoggedIn()): ?>
                                <a href="account-orders.php" class="tf-btn animate-btn ms-3" style="background: #6c757d;">
                                    <i class="fas fa-list me-2"></i>View Orders
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Important Notice -->
                        <div class="alert alert-info mt-5 no-print">
                            <h5><i class="fas fa-info-circle me-2"></i>What's Next?</h5>
                            <ul class="mb-0">
                                <li>You will receive an order confirmation email shortly at <strong><?= htmlspecialchars($order['customer_email']) ?></strong></li>
                                <li>We will notify you when your order ships</li>
                                <li>Estimated delivery time depends on your location</li>
                                <li>For any questions, please contact our support team</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="no-print">
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Javascript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/count-down.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
