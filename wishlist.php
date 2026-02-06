<?php
require_once __DIR__ . '/includes/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your wishlist.');
    redirect('login.php');
}

$userId = getCurrentUserId();

// Fetch wishlist items for the logged-in user
$query = "SELECT w.id as wishlist_id, w.created_at as added_on,
          p.id, p.name, p.slug, p.price, p.sale_price, p.image_path,
          p.stock_quantity, p.sku, p.is_featured, p.is_active
          FROM wishlist w
          INNER JOIN products p ON w.product_id = p.id
          WHERE w.user_id = ?
          ORDER BY w.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$wishlistItems = $stmt->fetchAll();
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Your Wishlist - Innovative Homesi | Save Your Favorite Furniture</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="View and manage your wishlist of favorite furniture pieces at Innovative Homesi. Save items for later and never lose track of your dream home decor.">

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
                    <h1 class="title-page">Your Wishlist</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Wishlist</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->

        <!-- Wishlist -->
        <div class="flat-spacing">
            <div class="container">
                <?php
                // Display flash message if exists
                $flash = getFlashMessage();
                if ($flash):
                ?>
                <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (count($wishlistItems) > 0): ?>
                <div class="tf-grid-layout tf-col-2 md-col-3 xl-col-4 wrapper-wishlist">
                    <?php foreach ($wishlistItems as $item):
                        $displayPrice = $item['sale_price'] > 0 ? $item['sale_price'] : $item['price'];
                        $hasDiscount = $item['sale_price'] > 0 && $item['sale_price'] < $item['price'];
                    ?>
                    <!-- Product -->
                    <div class="card-product grid style-2" data-wishlist-id="<?php echo $item['wishlist_id']; ?>">
                        <div class="card-product_wrapper">
                            <a href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>" class="product-img">
                                <img class="lazyload img-product"
                                     src="<?php echo htmlspecialchars($item['image_path'] ?: 'images/products/default.jpg'); ?>"
                                     data-src="<?php echo htmlspecialchars($item['image_path'] ?: 'images/products/default.jpg'); ?>"
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </a>
                            <span class="remove box-icon" onclick="removeFromWishlist(<?php echo $item['wishlist_id']; ?>)">
                                <i class="icon icon-trash"></i>
                            </span>
                            <ul class="product-action_list">
                                <li>
                                    <a href="javascript:void(0);"
                                       onclick="addToCart(<?php echo $item['id']; ?>)"
                                       class="hover-tooltip box-icon">
                                        <span class="icon icon-shopping-cart-simple"></span>
                                        <span class="tooltip">Add to cart</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>"
                                       class="hover-tooltip box-icon">
                                        <span class="icon icon-view"></span>
                                        <span class="tooltip">View details</span>
                                    </a>
                                </li>
                            </ul>

                            <?php if ($item['is_featured'] || $hasDiscount): ?>
                            <ul class="product-badge_list">
                                <?php if ($item['is_featured']): ?>
                                <li class="product-badge_item h6 new">Featured</li>
                                <?php endif; ?>
                                <?php if ($hasDiscount): ?>
                                <li class="product-badge_item h6 sale">Sale</li>
                                <?php endif; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card-product_info">
                            <a href="product-detail.php?slug=<?php echo urlencode($item['slug']); ?>"
                               class="name-product h4 link">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                            <div class="price-wrap">
                                <?php if ($hasDiscount): ?>
                                <span class="price-old h6 fw-normal"><?php echo formatPrice($item['price']); ?></span>
                                <span class="price-new h6"><?php echo formatPrice($item['sale_price']); ?></span>
                                <?php else: ?>
                                <span class="price-new h6"><?php echo formatPrice($item['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($item['stock_quantity'] <= 0): ?>
                            <div class="mt-2">
                                <span class="badge bg-danger">Out of Stock</span>
                            </div>
                            <?php elseif ($item['stock_quantity'] < 5): ?>
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark">Only <?php echo $item['stock_quantity']; ?> left</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <!-- Empty Wishlist State -->
                <div class="empty-wishlist text-center py-5">
                    <div class="mb-4">
                        <i class="icon icon-heart" style="font-size: 80px; color: #ddd;"></i>
                    </div>
                    <h3 class="mb-3">Your Wishlist is Empty</h3>
                    <p class="h6 text-muted mb-4">Looks like you haven't added any items to your wishlist yet.</p>
                    <a href="shop.php" class="tf-btn animate-btn">
                        <span>Start Shopping</span>
                        <i class="icon icon-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- /Wishlist -->

        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Javascript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
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

        // Update wishlist count in header
        function updateWishlistCount(count) {
            $('.wishlist-count').text(count);
        }

        // Remove from wishlist
        function removeFromWishlist(wishlistId) {
            if (!confirm('Are you sure you want to remove this item from your wishlist?')) {
                return;
            }

            $.ajax({
                url: 'ajax/remove-wishlist.php',
                type: 'POST',
                data: { wishlist_id: wishlistId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update wishlist count in header immediately
                        if (response.wishlist_count !== undefined) {
                            $('.wishlist-count').text(response.wishlist_count);
                            if (window.updateWishlistCount) {
                                window.updateWishlistCount(response.wishlist_count);
                            }
                        }

                        // Remove the product card from DOM
                        $('[data-wishlist-id="' + wishlistId + '"]').fadeOut(300, function() {
                            $(this).remove();

                            // Check if wishlist is now empty
                            if ($('.wrapper-wishlist .card-product').length === 0) {
                                location.reload();
                            }
                        });

                        // Show success message
                        showNotification('Item removed from wishlist', 'success');
                    } else {
                        showNotification(response.message || 'Failed to remove item', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
        }

        // Update cart count in header
        function updateCartCount(count) {
            $('.count-box:not(.wishlist-count)').text(count);
        }

        // Add to cart
        function addToCart(productId) {
            $.ajax({
                url: 'ajax/add-to-cart.php',
                type: 'POST',
                data: { product_id: productId, quantity: 1 },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Item added to cart', 'success');
                        // Update cart count in header
                        if (response.cart_count !== undefined) {
                            updateCartCount(response.cart_count);
                        }
                    } else {
                        showNotification(response.message || 'Failed to add to cart', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
        }

        // Simple notification function
        function showNotification(message, type) {
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
            const alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';

            $('body').append(alertHtml);

            // Auto dismiss after 3 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 3000);
        }
    </script>
</body>
</html>
