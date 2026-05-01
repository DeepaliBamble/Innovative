<?php
require_once __DIR__ . '/includes/init.php';

// Resolve checkout source: either the staged "Buy Now" product or the cart.
$isBuyNow  = isBuyNowFlow();
$cartItems = getCheckoutItems($pdo);

if (empty($cartItems)) {
    if ($isBuyNow) {
        // Stale buy-now reference (e.g. product deactivated). Drop it and bounce.
        unset($_SESSION['buy_now']);
        setFlashMessage('warning', 'That product is no longer available.');
        redirect('shop.php');
    }
    setFlashMessage('warning', 'Your cart is empty. Please add items before checkout.');
    redirect('view-cart.php');
}

// Calculate cart totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $itemPrice = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
    $subtotal += $itemPrice * $item['quantity'];
}

$shippingCost = 0; // Free shipping by default
$expressShippingCost = 5.00;
$taxAmount = 0; // No tax for now
$total = $subtotal + $shippingCost + $taxAmount;

// Get user details if logged in
$userData = null;
if (isLoggedIn()) {
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([getCurrentUserId()]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
}

// Get Razorpay configuration
require_once __DIR__ . '/includes/razorpay-config.php';
$razorpayConfig = getRazorpayConfig();
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Secure Checkout - Innovative Homesi | Complete Your Order</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Complete your furniture purchase securely at Innovative Homesi. Safe and encrypted checkout process.">
    <meta name="robots" content="noindex, nofollow">

    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="stylesheet" href="icon/icomoon/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- css -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/modern-typography.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        .form-field.error input,
        .form-field.error select,
        .form-field.error textarea {
            border-color: #dc3545;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }

        /* Order success popup */
        .order-success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 20px;
        }
        .order-success-overlay.show {
            display: flex;
            animation: fadeIn 0.25s ease-out;
        }
        .order-success-modal {
            background: #fff;
            border-radius: 12px;
            max-width: 480px;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            animation: popIn 0.35s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }
        .order-success-icon {
            width: 84px;
            height: 84px;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: #28a745;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            animation: scaleIn 0.5s 0.1s both;
        }
        .order-success-modal h2 {
            margin: 0 0 0.5rem;
            color: #1f2d3d;
            font-size: 1.6rem;
        }
        .order-success-modal p {
            color: #555;
            margin: 0.25rem 0;
        }
        .order-success-meta {
            background: #f6f8fa;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            margin: 1.25rem 0 1.5rem;
            font-size: 0.95rem;
        }
        .order-success-meta strong {
            color: #d4a574;
        }
        .order-success-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .order-success-actions a,
        .order-success-actions button {
            padding: 0.65rem 1.4rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            border: 0;
            cursor: pointer;
        }
        .order-success-actions .btn-primary {
            background: #d4a574;
            color: #fff;
        }
        .order-success-actions .btn-secondary {
            background: #f1f3f5;
            color: #333;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes popIn {
            0%   { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1);   opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0); }
            to   { transform: scale(1); }
        }
    </style>
</head>

<body>
    <!-- Scroll Top -->
    <button id="goTop">
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
        <?php include 'includes/topbar.php'; ?>
        <?php include 'includes/header.php'; ?>

        <!-- Page Title -->
        <section class="s-page-title">
            <div class="container">
                <div class="content">
                    <h1 class="title-page">Checkout</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li><h6 class="current-page fw-normal">Checkout</h6></li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->

        <!-- Check Out -->
        <section class="flat-spacing">
            <div class="container">
                <div id="checkout-alert-container"></div>

                <?php if ($isBuyNow): ?>
                <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" role="alert">
                    <div>
                        <i class="fas fa-bolt me-2"></i>
                        <strong>Express checkout</strong> &mdash; you're buying just this one item. Your cart is untouched.
                    </div>
                    <a href="view-cart.php" class="text-decoration-underline small">Use my cart instead</a>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="tf-page-checkout mb-lg-0">
                            <!-- Coupon Code (Optional) -->
                            <div class="wrap-coupon mb-4">
                                <h5 class="mb-12">Have a coupon? <span class="text-primary">Enter your code</span></h5>
                                <div class="ip-discount-code mb-0">
                                    <input type="text" id="coupon-code-input" placeholder="Enter your code">
                                    <button class="tf-btn animate-btn" type="button" id="apply-coupon-btn">
                                        Apply Code
                                    </button>
                                </div>
                                <div id="coupon-message" class="mt-2"></div>
                            </div>

                            <!-- Checkout Form -->
                            <form id="checkout-form" class="tf-checkout-cart-main">
                                <input type="hidden" id="csrf_token" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                                
                                <!-- Customer Information -->
                                <div class="box-ip-checkout estimate-shipping">
                                    <h2 class="title type-semibold">Customer Information</h2>
                                    <div class="form_content">
                                        <div class="cols tf-grid-layout sm-col-2">
                                            <fieldset class="form-field">
                                                <input type="text" name="first_name" id="first_name" placeholder="First name *" required value="<?= $userData ? htmlspecialchars(explode(' ', $userData['name'])[0] ?? '') : '' ?>">
                                                <span class="error-message">First name is required</span>
                                            </fieldset>
                                            <fieldset class="form-field">
                                                <input type="text" name="last_name" id="last_name" placeholder="Last name *" required value="<?= $userData ? htmlspecialchars(explode(' ', $userData['name'], 2)[1] ?? '') : '' ?>">
                                                <span class="error-message">Last name is required</span>
                                            </fieldset>
                                        </div>
                                        <div class="cols tf-grid-layout sm-col-2">
                                            <fieldset class="form-field">
                                                <input type="email" name="email" id="email" placeholder="Email address *" required value="<?= $userData ? htmlspecialchars($userData['email']) : '' ?>">
                                                <span class="error-message">Valid email is required</span>
                                            </fieldset>
                                            <fieldset class="form-field">
                                                <input type="tel" name="phone" id="phone" placeholder="Phone number *" required value="<?= $userData ? htmlspecialchars($userData['phone'] ?? '') : '' ?>">
                                                <span class="error-message">Phone number is required</span>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Address -->
                                <div class="box-ip-checkout">
                                    <h2 class="title type-semibold">Shipping Address</h2>
                                    <div class="form_content">
                                        <fieldset class="form-field">
                                            <div class="tf-select">
                                                <select class="w-100" id="country" name="country" required>
                                                    <option value="" disabled selected>Choose country / Region *</option>
                                                    <option value="India">India</option>
                                                    <option value="United States">United States</option>
                                                    <option value="United Kingdom">United Kingdom</option>
                                                    <option value="Canada">Canada</option>
                                                    <option value="Australia">Australia</option>
                                                </select>
                                            </div>
                                            <span class="error-message">Please select a country</span>
                                        </fieldset>
                                        <div class="cols tf-grid-layout sm-col-2">
                                            <fieldset class="form-field">
                                                <input type="text" name="city" id="city" placeholder="Town/City *" required>
                                                <span class="error-message">City is required</span>
                                            </fieldset>
                                            <fieldset class="form-field">
                                                <input type="text" name="state" id="state" placeholder="State *" required>
                                                <span class="error-message">State is required</span>
                                            </fieldset>
                                        </div>
                                        <fieldset class="form-field">
                                            <input type="text" name="street" id="street" placeholder="Street Address *" required>
                                            <span class="error-message">Street address is required</span>
                                        </fieldset>
                                        <fieldset class="form-field">
                                            <input type="text" name="postal_code" id="postal_code" placeholder="Postal code *" required>
                                            <span class="error-message">Postal code is required</span>
                                        </fieldset>
                                        <textarea name="notes" id="notes" placeholder="Order notes (optional)" style="height: 120px;"></textarea>
                                    </div>
                                </div>

                                <!-- Shipping Method -->
                                <div class="box-ip-shipping">
                                    <h2 class="title type-semibold">Shipping Method</h2>
                                    <label for="freeship" class="check-ship mb-12">
                                        <input type="radio" id="freeship" class="tf-check-rounded style-2 line-black" name="shipping_method" value="standard" checked>
                                        <span class="text h6">
                                            <span>Free shipping (Standard Delivery)</span>
                                            <span class="price">₹0.00</span>
                                        </span>
                                    </label>
                                    <label for="express" class="check-ship">
                                        <input type="radio" id="express" class="tf-check-rounded style-2 line-black" name="shipping_method" value="express">
                                        <span class="text h6">
                                            <span>Express shipping (2-3 Business Days)</span>
                                            <span class="price fw-medium">₹<?= number_format($expressShippingCost, 2) ?></span>
                                        </span>
                                    </label>
                                </div>

                                <!-- Payment Method -->
                                <div class="box-ip-payment">
                                    <h2 class="title type-semibold">Payment Method</h2>
                                    <div class="payment-method-box" id="payment-method-box">
                                        <div class="payment_accordion">
                                            <label for="razorpay" class="payment_check checkbox-wrap" data-bs-toggle="collapse" data-bs-target="#razorpay-payment" aria-controls="razorpay-payment">
                                                <input type="radio" name="payment_method" class="tf-check-rounded style-2" id="razorpay" value="razorpay" checked>
                                                <span class="pay-title">Razorpay (UPI, Cards, NetBanking, Wallets)</span>
                                            </label>
                                            <div id="razorpay-payment" class="collapse show" data-bs-parent="#payment-method-box">
                                                <p class="payment_body h6">
                                                    Pay securely using Razorpay. Supports UPI, Credit/Debit Cards, Net Banking, and popular wallets.
                                                    Your payment information is encrypted and secure.
                                                </p>
                                                <div class="card-logo d-flex gap-2 align-items-center flex-wrap mt-3">
                                                    <span class="badge bg-primary">UPI</span>
                                                    <span class="badge bg-success">Cards</span>
                                                    <span class="badge bg-info text-dark">NetBanking</span>
                                                    <span class="badge bg-warning text-dark">Wallets</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="h6 mb-20 mt-3">
                                        Your personal data will be used to process your order and support your experience throughout this website.
                                    </p>
                                    <div class="checkbox-wrap">
                                        <input id="agree" type="checkbox" class="tf-check style-2" required>
                                        <label for="agree" class="h6">I have read and agree to the website <a href="term&conditions.php" class="text-primary" target="_blank">terms and conditions</a> *</label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="button_submit">
                                    <button type="submit" id="checkout-submit-btn" class="tf-btn animate-btn w-100">
                                        <i class="fas fa-lock me-2"></i> Proceed to Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="col-lg-5">
                        <div class="fl-sidebar-cart sticky-top">
                            <div class="box-your-order">
                                <h2 class="title type-semibold">Your Order</h2>
                                <ul class="list-order-product">
                                    <?php foreach ($cartItems as $item): ?>
                                    <?php
                                        $itemPrice = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
                                        $itemTotal = $itemPrice * $item['quantity'];
                                    ?>
                                    <li class="order-item">
                                        <a href="product-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="img-prd">
                                            <img class="lazyload" src="<?= htmlspecialchars($item['image_path']) ?>" data-src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        </a>
                                        <div class="infor-prd">
                                            <h6 class="prd_name">
                                                <a href="product-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="link">
                                                    <?= htmlspecialchars($item['name']) ?>
                                                </a>
                                            </h6>
                                            <div class="prd_select text-small">
                                                Quantity: <?= (int)$item['quantity'] ?>
                                            </div>
                                        </div>
                                        <p class="price-prd h6">
                                            ₹<?= number_format($itemTotal, 2) ?>
                                        </p>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <ul class="list-total">
                                    <li class="total-item h6">
                                        <span class="fw-bold text-black">Subtotal</span>
                                        <span id="order-subtotal">₹<?= number_format($subtotal, 2) ?></span>
                                    </li>
                                    <li class="total-item h6" id="discount-row" style="display: none;">
                                        <span class="fw-bold text-black">Discount</span>
                                        <span id="order-discount" class="text-success">-₹0.00</span>
                                    </li>
                                    <li class="total-item h6">
                                        <span class="fw-bold text-black">Shipping</span>
                                        <span id="order-shipping">Free</span>
                                    </li>
                                </ul>
                                <div class="last-total h5 fw-medium text-black">
                                    <span>Total</span>
                                    <span id="order-total">₹<?= number_format($total, 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Check Out -->

        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Order Success Popup -->
    <div class="order-success-overlay" id="order-success-overlay" role="dialog" aria-modal="true" aria-labelledby="order-success-title">
        <div class="order-success-modal">
            <div class="order-success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 id="order-success-title">Thank you for your order!</h2>
            <p>Your payment was successful and your order is being processed.</p>
            <div class="order-success-meta">
                <div>Order Number: <strong id="success-order-number">—</strong></div>
                <div>Payment ID: <strong id="success-payment-id">—</strong></div>
            </div>
            <p class="text-muted small mb-3">A confirmation email is on its way to your inbox.</p>
            <div class="order-success-actions">
                <a href="#" id="success-view-order" class="btn-primary">View Order</a>
                <a href="index.php" class="btn-secondary">Continue Shopping</a>
            </div>
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

    <!-- Razorpay SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <!-- Checkout Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkoutForm = document.getElementById('checkout-form');
            const submitBtn = document.getElementById('checkout-submit-btn');
            const alertContainer = document.getElementById('checkout-alert-container');

            let subtotal = <?= $subtotal ?>;
            let shippingCost = 0;
            let discountAmount = 0;
            let couponCode = '';

            // Apply coupon handler
            const applyCouponBtn = document.getElementById('apply-coupon-btn');
            if (applyCouponBtn) {
                applyCouponBtn.addEventListener('click', function() {
                    const code = document.getElementById('coupon-code-input').value.trim();
                    const messageEl = document.getElementById('coupon-message');
                    
                    if (!code) {
                        messageEl.innerHTML = '<span class="text-danger">Please enter a coupon code.</span>';
                        return;
                    }
                    
                    const btnOriginalText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    const formData = new FormData();
                    formData.append('coupon_code', code);
                    
                    fetch('ajax/validate-coupon.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.disabled = false;
                        this.innerHTML = btnOriginalText;
                        
                        if (data.success) {
                            couponCode = data.coupon.code;
                            
                            // Calculate discount based on subtotal
                            if (data.coupon.discount_type === 'percentage') {
                                discountAmount = (subtotal * data.coupon.discount_value) / 100;
                                if (data.coupon.max_discount_amount && discountAmount > data.coupon.max_discount_amount) {
                                    discountAmount = data.coupon.max_discount_amount;
                                }
                            } else {
                                discountAmount = parseFloat(data.coupon.discount_value);
                            }
                            
                            messageEl.innerHTML = `<span class="text-success"><i class="fas fa-check-circle me-1"></i> ${data.message}</span>`;
                            
                            // Show discount row
                            const discountRow = document.getElementById('discount-row');
                            if (discountRow) {
                                discountRow.style.display = 'flex';
                                document.getElementById('order-discount').textContent = '-₹' + discountAmount.toFixed(2);
                            }
                            
                            updateTotal();
                        } else {
                            couponCode = '';
                            discountAmount = 0;
                            messageEl.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i> ${data.message}</span>`;
                            
                            const discountRow = document.getElementById('discount-row');
                            if (discountRow) discountRow.style.display = 'none';
                            
                            updateTotal();
                        }
                    })
                    .catch(err => {
                        console.error('Error applying coupon:', err);
                        this.disabled = false;
                        this.innerHTML = btnOriginalText;
                        messageEl.innerHTML = '<span class="text-danger">Failed to validate coupon.</span>';
                    });
                });
            }

            // Shipping method change handler
            document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'express') {
                        shippingCost = <?= $expressShippingCost ?>;
                        document.getElementById('order-shipping').textContent = '₹' + shippingCost.toFixed(2);
                    } else {
                        shippingCost = 0;
                        document.getElementById('order-shipping').textContent = 'Free';
                    }
                    updateTotal();
                });
            });

            // Update total
            function updateTotal() {
                const total = subtotal + shippingCost - discountAmount;
                document.getElementById('order-total').textContent = '₹' + total.toFixed(2);
            }

            // Show alert
            function showAlert(message, type = 'danger') {
                alertContainer.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // Form validation
            function validateForm() {
                let isValid = true;
                const formFields = checkoutForm.querySelectorAll('input[required], select[required]');

                formFields.forEach(field => {
                    const fieldContainer = field.closest('.form-field');
                    if (!fieldContainer) return;

                    if (!field.value.trim()) {
                        fieldContainer.classList.add('error');
                        const errorMsg = fieldContainer.querySelector('.error-message');
                        if (errorMsg) errorMsg.style.display = 'block';
                        isValid = false;
                    } else {
                        fieldContainer.classList.remove('error');
                        const errorMsg = fieldContainer.querySelector('.error-message');
                        if (errorMsg) errorMsg.style.display = 'none';
                    }
                });

                // Validate terms checkbox
                const agreeCheckbox = document.getElementById('agree');
                if (!agreeCheckbox.checked) {
                    showAlert('Please agree to the terms and conditions to proceed.');
                    isValid = false;
                }

                return isValid;
            }

            // Handle form submission
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    return;
                }

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';

                // Get form data
                const formData = new FormData(checkoutForm);
                formData.append('coupon_code', couponCode);
                formData.append('csrf_token', document.getElementById('csrf_token') ? document.getElementById('csrf_token').value : '');

                // Create order
                fetch('ajax/create-order.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Open Razorpay checkout
                        openRazorpayCheckout(data);
                    } else {
                        showAlert(data.message || 'Failed to create order. Please try again.');
                        resetSubmitButton();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.');
                    resetSubmitButton();
                });
            });

            // Open Razorpay checkout
            function openRazorpayCheckout(orderData) {
                const options = {
                    key: orderData.razorpay_key_id,
                    amount: orderData.amount * 100, // Amount in paise
                    currency: orderData.currency,
                    name: 'Innovative Homesi',
                    description: 'Furniture Purchase - Order #' + orderData.order_number,
                    image: 'images/logo/logo.png',
                    order_id: orderData.razorpay_order_id,
                    handler: function(response) {
                        verifyPayment(orderData.order_id, response);
                    },
                    prefill: {
                        name: orderData.customer.name,
                        email: orderData.customer.email,
                        contact: orderData.customer.phone
                    },
                    theme: {
                        color: '#d4a574'
                    },
                    modal: {
                        ondismiss: function() {
                            showAlert('Payment cancelled. Please try again when ready.', 'warning');
                            resetSubmitButton();
                        }
                    }
                };

                const rzp = new Razorpay(options);
                rzp.open();
            }

            // Verify payment
            function verifyPayment(orderId, razorpayResponse) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Verifying Payment...';

                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('razorpay_payment_id', razorpayResponse.razorpay_payment_id);
                formData.append('razorpay_order_id', razorpayResponse.razorpay_order_id);
                formData.append('razorpay_signature', razorpayResponse.razorpay_signature);
                formData.append('csrf_token', document.getElementById('csrf_token') ? document.getElementById('csrf_token').value : '');

                fetch('ajax/process-payment.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        submitBtn.innerHTML = '<i class="fas fa-check me-2"></i> Payment Successful!';
                        submitBtn.style.background = '#28a745';
                        showOrderSuccessPopup(data);
                    } else {
                        showAlert(data.message || 'Payment verification failed.');
                        resetSubmitButton();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Payment verification failed. Please contact support.');
                    resetSubmitButton();
                });
            }

            // Show "Thank you" confirmation popup, then auto-redirect after 5s
            function showOrderSuccessPopup(data) {
                const overlay = document.getElementById('order-success-overlay');
                const orderNumberEl = document.getElementById('success-order-number');
                const paymentIdEl = document.getElementById('success-payment-id');
                const viewOrderLink = document.getElementById('success-view-order');

                if (orderNumberEl) orderNumberEl.textContent = data.order_number || '—';
                if (paymentIdEl)   paymentIdEl.textContent   = data.payment_id   || '—';
                if (viewOrderLink && data.redirect) viewOrderLink.href = data.redirect;

                if (overlay) {
                    overlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }

                // Auto-redirect to the full order confirmation page after a short delay
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                }, 5000);
            }

            // Reset submit button
            function resetSubmitButton() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i> Proceed to Payment';
                submitBtn.style.background = '';
            }
        });
    </script>
</body>
</html>
