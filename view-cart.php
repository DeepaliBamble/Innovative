
<?php
require_once __DIR__ . '/includes/init.php';

// Require login before accessing cart page
if (!isLoggedIn()) {
    // Set absolute path for redirect after login
    $_SESSION['redirect_after_login'] = SITE_URL . '/view-cart.php';
    header('Location: login.php');
    exit;
}

// Fetch cart items from database
try {
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare("
            SELECT
                c.*,
                p.name,
                p.slug,
                p.price,
                p.sale_price,
                p.image_path,
                p.stock_quantity,
                p.is_active,
                cat.name as category_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            LEFT JOIN categories cat ON p.category_id = cat.id
            WHERE c.user_id = ? AND p.is_active = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$userId]);
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare("
            SELECT
                c.*,
                p.name,
                p.slug,
                p.price,
                p.sale_price,
                p.image_path,
                p.stock_quantity,
                p.is_active,
                cat.name as category_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            LEFT JOIN categories cat ON p.category_id = cat.id
            WHERE c.session_id = ? AND p.is_active = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$sessionId]);
    }

    $cartItems = $stmt->fetchAll();

    // Calculate totals
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
        $subtotal += $price * $item['quantity'];
    }

    $shipping = 0; // Always free shipping
    $tax = 0; // Add tax calculation if needed
    $total = $subtotal; // Total is just subtotal since shipping and tax are 0

} catch (Exception $e) {
    error_log('Cart page error: ' . $e->getMessage());
    $cartItems = [];
    $subtotal = 0;
    $shipping = 0;
    $tax = 0;
    $total = 0;
}
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Shopping Cart - Innovative Homesi | Review Your Items</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Review your selected furniture items and proceed to secure checkout at Innovative Homesi.">
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
                    <h1 class="title-page">Shopping Cart</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Shopping Cart</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- View Cart -->
        <div class="flat-spacing each-list-prd">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-9 col-xl-8">
                        <?php if (!empty($cartItems)): ?>
                        <div class="tf-cart-sold mb-4">
                            <!-- Free Shipping Badge -->
                            <div class="alert alert-success d-flex align-items-center mb-0" style="border-left: 4px solid #28a745;">
                                <i class="fas fa-truck me-3" style="font-size: 24px;"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold">FREE SHIPPING on All Orders!</h6>
                                    <p class="mb-0 text-muted small">Enjoy complimentary shipping on every purchase</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <form>
                            <table class="tf-table-page-cart">
                                <thead>
                                    <tr>
                                        <th class="h6">Product</th>
                                        <th class="h6">Price</th>
                                        <th class="h6">Quantity</th>
                                        <th class="h6">Total price</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($cartItems)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <i class="icon icon-shopping-cart-simple" style="font-size: 48px; color: #ccc;"></i>
                                                <h5 class="mt-3">Your cart is empty</h5>
                                                <p class="text-muted">Add some products to get started!</p>
                                                <a href="shop.php" class="tf-btn animate-btn btn-primary mt-3">
                                                    <i class="icon icon-shopping-bag me-2"></i> Start Shopping
                                                </a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($cartItems as $item):
                                            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
                                            $itemTotal = $price * $item['quantity'];
                                            $isOutOfStock = $item['stock_quantity'] <= 0;
                                            $quantityExceeded = $item['quantity'] > $item['stock_quantity'];
                                        ?>
                                        <tr class="tf-cart_item each-prd file-delete" data-cart-item-id="<?php echo $item['id']; ?>">
                                            <td>
                                                <div class="cart_product">
                                                    <a href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>" class="img-prd">
                                                        <img class="lazyload" src="<?php echo htmlspecialchars($item['image_path']); ?>" data-src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                                            alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                    </a>
                                                    <div class="infor-prd">
                                                        <h6 class="prd_name">
                                                            <a href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>" class="link">
                                                                <?php echo htmlspecialchars($item['name']); ?>
                                                            </a>
                                                        </h6>
                                                        <?php if ($item['category_name']): ?>
                                                        <div class="prd_select text-small">
                                                            Category: <span class="text-muted"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                                        </div>
                                                        <?php endif; ?>
                                                        <?php if ($isOutOfStock): ?>
                                                        <div class="text-danger text-small mt-1">
                                                            <i class="icon icon-alert-triangle"></i> Out of Stock
                                                        </div>
                                                        <?php elseif ($quantityExceeded): ?>
                                                        <div class="text-warning text-small mt-1">
                                                            <i class="icon icon-alert-circle"></i> Only <?php echo $item['stock_quantity']; ?> available
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="cart_price h6 each-price" data-cart-title="Price">
                                                <?php if ($item['sale_price']): ?>
                                                    <span class="text-decoration-line-through text-muted me-2">₹<?php echo number_format($item['price'], 0); ?></span>
                                                    <span class="text-primary price-value" data-price="<?php echo $item['sale_price']; ?>">₹<?php echo number_format($item['sale_price'], 0); ?></span>
                                                <?php else: ?>
                                                    <span class="price-value" data-price="<?php echo $item['price']; ?>">₹<?php echo number_format($item['price'], 0); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="cart_quantity" data-cart-title="Quantity">
                                                <div class="wg-quantity">
                                                    <button class="btn-quantity minus-quantity" type="button" data-cart-item-id="<?php echo $item['id']; ?>">
                                                        <i class="icon-minus fs-14"></i>
                                                    </button>
                                                    <input class="quantity-product" type="text" name="number" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" data-cart-item-id="<?php echo $item['id']; ?>">
                                                    <button class="btn-quantity plus-quantity" type="button" data-cart-item-id="<?php echo $item['id']; ?>" <?php echo $quantityExceeded ? 'disabled' : ''; ?>>
                                                        <i class="icon-plus fs-14"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="cart_total h6 each-subtotal-price" data-cart-title="Total">₹<?php echo number_format($itemTotal, 0); ?></td>
                                            <td class="cart_remove remove link" data-cart-title="Remove" data-cart-item-id="<?php echo $item['id']; ?>">
                                                <i class="icon icon-close"></i>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <!-- Static demo product rows removed -->
                                </tbody>
                            </table>
                            <!-- Coupon and discount sections removed -->
                        </form>
                    </div>
                    <div class="col-xxl-3 col-xl-4">
                        <div class="fl-sidebar-cart bg-white-smoke sticky-top">
                            <div class="box-order-summary">
                                <h4 class="title fw-semibold">Order Summary</h4>
                                <div class="subtotal h6 text-button d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold">Subtotal</h6>
                                    <span class="total cart-subtotal">₹<?php echo number_format($subtotal, 0); ?></span>
                                </div>
                                <?php if ($subtotal > 0): ?>
                                <div class="ship">
                                    <h6 class="fw-bold">Shipping</h6>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-light rounded">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-truck me-2 text-success"></i>
                                                <span class="h6 mb-0">FREE Shipping</span>
                                            </div>
                                            <span class="h6 mb-0 text-success fw-bold">₹0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="total-order d-flex justify-content-between align-items-center">
                                    <span>Total</span>
                                    <span class="total each-total-price cart-total" id="cart-total-amount"><?php echo '₹' . number_format($total, 0); ?></span>
                                </h5>
                                <?php endif; ?>
                                <div class="list-ver">
                                    <?php if ($subtotal > 0): ?>
                                    <a href="checkout.php" class="tf-btn w-100 animate-btn">
                                        Process to checkout
                                        <i class="icon icon-arrow-right"></i>
                                    </a>
                                    <?php else: ?>
                                    <button class="tf-btn w-100 animate-btn" disabled style="opacity:0.7;cursor:not-allowed;">
                                        Your cart is empty. Add products to cart for checkout
                                    </button>
                                    <?php endif; ?>
                                    <a href="shop.php" class="tf-btn btn-white animate-btn animate-dark w-100">
                                        Continue shopping
                                        <i class="icon icon-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /View Cart -->
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
    <!-- <script src="js/count-down.js"></script> -->
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

    <!-- Cart Management Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                                    // On page load, fetch and update cart total to ensure accuracy
                                    fetch('ajax/get-cart-total.php')
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success && typeof data.total !== 'undefined') {
                                                document.querySelectorAll('.cart-total').forEach(function(el) {
                                                    el.textContent = '₹' + Number(data.total).toLocaleString('en-IN');
                                                });
                                            } else {
                                                document.querySelectorAll('.cart-total').forEach(function(el) {
                                                    el.textContent = '₹0';
                                                });
                                            }
                                        });
                        // On page load, ensure all cart-total elements show correct INR value
                        // Always set the total to the correct INR value on page load
                        var phpTotal = <?php echo (int)$total; ?>;
                        document.querySelectorAll('.cart-total').forEach(function(el) {
                            el.textContent = '₹' + phpTotal.toLocaleString('en-IN');
                        });
            // Update quantity buttons
            const plusButtons = document.querySelectorAll('.plus-quantity');
            const minusButtons = document.querySelectorAll('.minus-quantity');
            const removeButtons = document.querySelectorAll('.cart_remove');

            // Increase quantity
            plusButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const cartItemId = this.getAttribute('data-cart-item-id');
                    const input = document.querySelector(`input[data-cart-item-id="${cartItemId}"]`);
                    const currentQty = parseInt(input.value);
                    const maxQty = parseInt(input.getAttribute('max'));

                    if (currentQty < maxQty) {
                        updateCartQuantity(cartItemId, currentQty + 1, input);
                    } else {
                        showNotification('Error', `Maximum ${maxQty} items available`, 'warning');
                    }
                });
            });

            // Decrease quantity
            minusButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const cartItemId = this.getAttribute('data-cart-item-id');
                    const input = document.querySelector(`input[data-cart-item-id="${cartItemId}"]`);
                    const currentQty = parseInt(input.value);

                    // Allow decreasing to 0, which will remove the item
                    updateCartQuantity(cartItemId, currentQty - 1, input);
                });
            });

            // Remove from cart
            removeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const cartItemId = this.getAttribute('data-cart-item-id');
                    if (confirm('Remove this item from cart?')) {
                        removeFromCart(cartItemId);
                    }
                });
            });

            // Update cart quantity via AJAX
            function updateCartQuantity(cartItemId, quantity, inputElement) {
                fetch('ajax/update-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_item_id=${cartItemId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (quantity <= 0) {
                            // Remove row from table
                            const row = document.querySelector(`tr[data-cart-item-id="${cartItemId}"]`);
                            if (row) row.remove();
                            // If cart is now empty, reload page to show empty cart message
                            const remainingItems = document.querySelectorAll('tr[data-cart-item-id]').length;
                            if (remainingItems === 0) {
                                window.location.reload();
                                return;
                            }
                        } else {
                            inputElement.value = quantity;
                        }
                        recalculateCart();
                        if (window.updateMenuCartCount) window.updateMenuCartCount();
                        showNotification('Success', 'Cart updated', 'success');
                    } else {
                        showNotification('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error', 'Failed to update cart', 'error');
                });
            }

            // Remove from cart via AJAX
            function removeFromCart(cartItemId) {
                fetch('ajax/remove-from-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_item_id=${cartItemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row from table
                        const row = document.querySelector(`tr[data-cart-item-id="${cartItemId}"]`);
                        if (row) {
                            row.remove();
                        }

                        // Reload page if cart is empty
                        const remainingItems = document.querySelectorAll('.tf-cart_item:not([style*="display: none"])').length - 1;
                        if (remainingItems === 0) {
                            window.location.reload();
                        } else {
                            recalculateCart();
                        }
                        if (window.updateMenuCartCount) window.updateMenuCartCount();
                        showNotification('Success', 'Item removed from cart', 'success');
                    } else {
                        showNotification('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error', 'Failed to remove item', 'error');
                });
            }

            // Recalculate cart totals
            function recalculateCart() {
                let subtotal = 0;
                const rows = document.querySelectorAll('tr[data-cart-item-id]');

                rows.forEach(row => {
                    // Get price from data attribute for accuracy
                    const priceElem = row.querySelector('.price-value');
                    const price = priceElem ? parseFloat(priceElem.getAttribute('data-price')) : 0;
                    const quantity = parseInt(row.querySelector('.quantity-product').value);
                    const itemTotal = price * quantity;

                    // Update row total
                    row.querySelector('.each-subtotal-price').textContent = '₹' + itemTotal.toLocaleString('en-IN');
                    subtotal += itemTotal;
                });

                // Update subtotal
                document.querySelector('.cart-subtotal').textContent = '₹' + subtotal.toLocaleString('en-IN');
                // Update total (since shipping and tax are always 0)
                document.querySelectorAll('.cart-total').forEach(function(el) {
                    el.textContent = '₹' + subtotal.toLocaleString('en-IN');
                });
            }

            // Notification function
            function showNotification(title, message, type) {
                const notification = document.createElement('div');
                notification.className = `notification-toast ${type}`;
                notification.innerHTML = `
                    <div class="notification-header">
                        <strong>${title}</strong>
                        <button class="notification-close">&times;</button>
                    </div>
                    <div class="notification-body">${message}</div>
                `;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);

                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);

                const closeBtn = notification.querySelector('.notification-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        notification.classList.remove('show');
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    });
                }
            }
        });
    </script>

    <style>
        /* Notification Toast Styles */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            max-width: 400px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(450px);
            transition: transform 0.3s ease;
            z-index: 9999;
            overflow: hidden;
        }

        .notification-toast.show {
            transform: translateX(0);
        }

        .notification-toast.success {
            border-left: 4px solid #28a745;
        }

        .notification-toast.error {
            border-left: 4px solid #dc3545;
        }

        .notification-toast.warning {
            border-left: 4px solid #ffc107;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .notification-header strong {
            color: #333;
            font-size: 14px;
        }

        .notification-close {
            background: none;
            border: none;
            font-size: 20px;
            color: #666;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            line-height: 1;
        }

        .notification-close:hover {
            color: #333;
        }

        .notification-body {
            padding: 12px 15px;
            color: #666;
            font-size: 14px;
        }
    </style>
</body>

</html>

