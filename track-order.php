<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Track Order - Innovative Homesi | Check Furniture Delivery Status</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Track your Innovative Homesi furniture order in real-time. Check delivery status and estimated arrival time.">
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
    <link rel="stylesheet" type="text/css" href="css/modern-typography.css">

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
                    <h1 class="title-page">Order Tracking</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Order Tracking</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Login -->
        <section class="flat-spacing">
            <div class="container">
                <div class="s-log">
                    <div class="col-left">
                        <div class="heading">
                            <h1 class="mb-8">Track Your Order</h1>
                            <p class="text-subhead">
                                To track your order, please enter your order ID in the box below and press the "Track" button. The ID has been sent to
                                you on your receipt and in the confirmation email you received.
                            </p>
                        </div>
                        <div id="alert-container"></div>
                        <form id="track-order-form" class="form-login">
                            <div class="list-ver">
                                <fieldset>
                                    <input type="text" id="order_id" name="order_id" placeholder="Order ID or Order Number*" required>
                                </fieldset>
                                <fieldset>
                                    <input type="email" id="email" name="email" placeholder="Email address*" required>
                                </fieldset>
                            </div>
                            <button type="submit" class="tf-btn animate-btn w-100">
                                Track Order
                            </button>
                        </form>

                        <!-- Order Details Container (Initially Hidden) -->
                        <div id="order-details" style="display: none; margin-top: 30px;">
                            <!-- Order details will be loaded here via JavaScript -->
                        </div>
                    </div>
                    <div class="col-right">
                        <h1 class="heading">Have An Account</h1>
                        <p class="h6 text-sub">
                            Welcome back, log in to your account to enhance your shopping experience, receive coupons, and the best discount codes.
                        </p>
                        <a href="login.php" class="btn_log tf-btn animate-btn">
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Login -->
        <?php include 'includes/footer.php'; ?>
    <!-- Javascript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <!-- <script src="js/swiper-bundle.min.js"></script> -->
    <!-- <script src="js/carousel.js"></script> -->
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>



    <!-- <script src="js/parallaxie.js"></script> -->
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

        // Track Order Form Handler
        document.addEventListener('DOMContentLoaded', function() {
            const trackForm = document.getElementById('track-order-form');
            const alertContainer = document.getElementById('alert-container');
            const orderDetailsContainer = document.getElementById('order-details');

            trackForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(trackForm);
                const submitBtn = trackForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;

                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Tracking...';

                // Clear previous alerts and order details
                alertContainer.innerHTML = '';
                orderDetailsContainer.style.display = 'none';

                fetch('ajax/track-order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showOrderDetails(data.order);
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'An error occurred while tracking your order. Please try again.');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                });
            });

            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

                alertContainer.innerHTML = `
                    <div class="alert ${alertClass}" role="alert" style="margin-bottom: 20px; padding: 15px; border-radius: 5px; background-color: ${type === 'success' ? '#d4edda' : '#f8d7da'}; border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'}; color: ${type === 'success' ? '#155724' : '#721c24'};">
                        <i class="fa ${icon}"></i> ${message}
                    </div>
                `;

                // Scroll to alert
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function showOrderDetails(order) {
                // Build shipping address string
                let shippingAddress = '';
                if (order.shipping_address.address1) {
                    shippingAddress = `
                        <p><strong>${order.shipping_address.name || 'N/A'}</strong><br>
                        ${order.shipping_address.address1}<br>
                        ${order.shipping_address.address2 ? order.shipping_address.address2 + '<br>' : ''}
                        ${order.shipping_address.city}, ${order.shipping_address.state} ${order.shipping_address.postal_code}<br>
                        ${order.shipping_address.country}<br>
                        Phone: ${order.shipping_address.phone || 'N/A'}</p>
                    `;
                } else {
                    shippingAddress = '<p>No shipping address on file</p>';
                }

                // Build order items HTML
                let itemsHTML = '';
                order.items.forEach(item => {
                    const imgSrc = item.image1 || 'images/products/placeholder.jpg';
                    itemsHTML += `
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="${imgSrc}" alt="${item.product_name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <div>
                                        <strong>${item.product_name}</strong><br>
                                        ${item.product_sku ? '<small>SKU: ' + item.product_sku + '</small>' : ''}
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">${item.quantity}</td>
                            <td style="text-align: right;">₹${item.price}</td>
                            <td style="text-align: right;"><strong>₹${item.subtotal}</strong></td>
                        </tr>
                    `;
                });

                orderDetailsContainer.innerHTML = `
                    <div style="background: #f9f9f9; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <!-- Order Status Header -->
                        <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: white; border-radius: 10px;">
                            <i class="fa ${order.status_icon}" style="font-size: 48px; color: ${order.status_color}; margin-bottom: 10px;"></i>
                            <h2 style="color: ${order.status_color}; margin: 10px 0;">${order.status_label}</h2>
                            <p style="color: #666; margin: 5px 0;">${order.status_description}</p>
                            <p style="color: #999; font-size: 14px; margin-top: 10px;">Last Updated: ${order.last_updated}</p>
                        </div>

                        <!-- Order Information Grid -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                            <div style="background: white; padding: 20px; border-radius: 8px;">
                                <h4 style="margin-top: 0; color: #333; border-bottom: 2px solid #6366f1; padding-bottom: 10px;">Order Details</h4>
                                <p><strong>Order Number:</strong> ${order.order_number}</p>
                                <p><strong>Order Date:</strong> ${order.order_date}</p>
                                <p><strong>Order Time:</strong> ${order.order_time}</p>
                            </div>

                            <div style="background: white; padding: 20px; border-radius: 8px;">
                                <h4 style="margin-top: 0; color: #333; border-bottom: 2px solid #6366f1; padding-bottom: 10px;">Customer Information</h4>
                                <p><strong>Name:</strong> ${order.customer_name}</p>
                                <p><strong>Email:</strong> ${order.customer_email}</p>
                            </div>

                            <div style="background: white; padding: 20px; border-radius: 8px;">
                                <h4 style="margin-top: 0; color: #333; border-bottom: 2px solid #6366f1; padding-bottom: 10px;">Payment Information</h4>
                                <p><strong>Payment Status:</strong> <span style="color: ${order.payment_status === 'Paid' ? '#28a745' : '#ffc107'};">${order.payment_status}</span></p>
                                <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                            <h4 style="margin-top: 0; color: #333; border-bottom: 2px solid #6366f1; padding-bottom: 10px;">Shipping Address</h4>
                            ${shippingAddress}
                        </div>

                        <!-- Order Items -->
                        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h4 style="margin-top: 0; color: #333; border-bottom: 2px solid #6366f1; padding-bottom: 10px;">Order Items</h4>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #ddd;">
                                            <th style="text-align: left; padding: 10px;">Product</th>
                                            <th style="text-align: center; padding: 10px;">Quantity</th>
                                            <th style="text-align: right; padding: 10px;">Price</th>
                                            <th style="text-align: right; padding: 10px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsHTML}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div style="background: white; padding: 20px; border-radius: 8px;">
                            <h4 style="margin-top: 0; color: #333; border-bottom: 2px solid #6366f1; padding-bottom: 10px;">Order Summary</h4>
                            <div style="max-width: 400px; margin-left: auto;">
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>Subtotal:</span>
                                    <span>₹${order.subtotal}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>Tax:</span>
                                    <span>₹${order.tax}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>Shipping:</span>
                                    <span>₹${order.shipping}</span>
                                </div>
                                ${order.discount > 0 ? `
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; color: #28a745;">
                                    <span>Discount:</span>
                                    <span>-₹${order.discount}</span>
                                </div>
                                ` : ''}
                                <div style="display: flex; justify-content: space-between; padding: 15px 0; font-size: 18px; font-weight: bold; color: #6366f1;">
                                    <span>Total:</span>
                                    <span>₹${order.total}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="text-align: center; margin-top: 30px;">
                            <button onclick="window.print()" class="tf-btn animate-btn" style="margin-right: 10px;">
                                <i class="fa fa-print"></i> Print Order
                            </button>
                            <a href="contact-us.php" class="tf-btn animate-btn" style="background: #6c757d;">
                                <i class="fa fa-envelope"></i> Contact Support
                            </a>
                        </div>
                    </div>
                `;

                orderDetailsContainer.style.display = 'block';
                orderDetailsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
</body>
</html>

