<?php
/**
 * Product Detail Page
 * Displays detailed information about a specific product
 */

// Include initialization (database, session, functions)
require_once __DIR__ . '/includes/init.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: shop.php');
    exit;
}

try {
    // Fetch product details
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: shop.php');
        exit;
    }

    // Fetch product images
    $stmt = $pdo->prepare("
        SELECT image_path, is_primary, display_order
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_primary DESC, display_order ASC
    ");
    $stmt->execute([$product_id]);
    $product_images = $stmt->fetchAll();

    // If no images in database, use the main image_path from products table
    if (empty($product_images) && $product['image_path']) {
        $product_images = [['image_path' => $product['image_path'], 'is_primary' => 1]];
    }

    // Fetch product attributes
    $stmt = $pdo->prepare("
        SELECT attribute_name, attribute_value, display_order
        FROM product_attributes
        WHERE product_id = ?
        ORDER BY display_order ASC
    ");
    $stmt->execute([$product_id]);
    $product_attributes = $stmt->fetchAll();

    // Fetch product variations
    $product_variations = [];
    $variation_types = [];

    // Check if variation tables exist
    $tables_check = $pdo->query("SHOW TABLES LIKE 'product_variations'")->rowCount();

    if ($tables_check > 0) {
        // Fetch variation types for this product's category
        $stmt = $pdo->prepare("
            SELECT DISTINCT
                vt.id,
                vt.name,
                vt.display_name,
                vt.input_type,
                cvt.is_required,
                cvt.display_order
            FROM variation_types vt
            JOIN category_variation_types cvt ON vt.id = cvt.variation_type_id
            WHERE cvt.category_id = ? AND vt.is_active = 1
            ORDER BY cvt.display_order ASC
        ");
        $stmt->execute([$product['category_id']]);
        $variation_types = $stmt->fetchAll();

        // Fetch variations for this product, grouped by type
        $stmt = $pdo->prepare("
            SELECT
                pv.id,
                pv.variation_type_id,
                pv.variation_name,
                pv.variation_value,
                pv.color_code,
                pv.image_path as variation_image,
                pv.price_adjustment,
                pv.stock_quantity,
                pv.sku_suffix,
                pv.display_order,
                vt.name as type_name,
                vt.display_name as type_display,
                vt.input_type
            FROM product_variations pv
            JOIN variation_types vt ON pv.variation_type_id = vt.id
            WHERE pv.product_id = ? AND pv.is_active = 1
            ORDER BY vt.display_order ASC, pv.display_order ASC
        ");
        $stmt->execute([$product_id]);
        $all_variations = $stmt->fetchAll();

        // Group variations by type
        foreach ($all_variations as $variation) {
            $type_id = $variation['variation_type_id'];
            if (!isset($product_variations[$type_id])) {
                $product_variations[$type_id] = [
                    'type_name' => $variation['type_name'],
                    'type_display' => $variation['type_display'],
                    'input_type' => $variation['input_type'],
                    'variations' => []
                ];
            }
            $product_variations[$type_id]['variations'][] = $variation;
        }
    }

    // Calculate discount percentage if on sale
    $discount_percentage = 0;
    if ($product['sale_price'] && $product['sale_price'] < $product['price']) {
        $discount_percentage = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
    }

    // Fetch related products (same category)
    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.name,
            p.slug,
            p.short_desc,
            p.price,
            p.sale_price,
            p.is_featured,
            COALESCE(pi.image_path, p.image_path) as image_path
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $product_id]);
    $related_products = $stmt->fetchAll();

    // Update view count
    $stmt = $pdo->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = ?");
    $stmt->execute([$product_id]);

    // Check if product is in user's wishlist
    $isInWishlist = false;
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $product_id]);
        $isInWishlist = $stmt->rowCount() > 0;
    }

    // Fetch review statistics for this product
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as review_count,
            COALESCE(AVG(rating), 0) as avg_rating
        FROM reviews
        WHERE product_id = ? AND is_approved = 1
    ");
    $stmt->execute([$product_id]);
    $review_stats = $stmt->fetch();
    $avg_rating = round($review_stats['avg_rating'], 1);
    $review_count = (int)$review_stats['review_count'];

} catch (PDOException $e) {
    error_log('Error fetching product: ' . $e->getMessage());
    header('Location: shop.php');
    exit;
}

$page_title = htmlspecialchars($product['name']) . ' - Innovative Homesi';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title><?php echo $page_title; ?></title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($product['short_desc'] ?? $product['name']); ?>">
    <meta name="keywords" content="furniture, <?php echo htmlspecialchars($product['category_name'] ?? ''); ?>, <?php echo htmlspecialchars($product['name']); ?>">

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
    <link rel="stylesheet" href="css/drift-basic.min.css">
    <link rel="stylesheet" href="css/photoswipe.css">
    <link rel="stylesheet" href="css/swiper-bundle.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/modern-typography.css">
    <link rel="stylesheet" href="css/product-variations.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

<link rel="stylesheet" href="css/product-detail-layout.css">
</head>

<body>
    <!-- Scroll Top -->
    <button id="goTop" class="pos1">
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
        <section class="s-page-title style-2">
            <div class="container">
                <div class="content">
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li><a href="shop.php" class="h6 link">Shop</a></li>
                        <?php if ($product['category_name']): ?>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li><a href="shop.php?category=<?php echo urlencode($product['category_slug']); ?>" class="h6 link"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                        <?php endif; ?>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal"><?php echo htmlspecialchars($product['name']); ?></h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->

        <!-- Product Main -->
        <section class="flat-single-product flat-spacing-3">
            <div class="tf-main-product section-image-zoom">
                <div class="container">
                    <div class="row">
                        <!-- Product Images -->
                        <div class="col-md-6">
                            <div class="tf-product-media-wrap sticky-top">
                                <?php if (!empty($product_images)): ?>
                                <div class="product-gallery-vertical">
                                    <!-- Main Image -->
                                    <div class="main-image-container position-relative mb-3">
                                        <?php if ($product['is_featured']): ?>
                                        <div class="featured-badge">
                                            <i class="fas fa-star"></i> Featured
                                        </div>
                                        <?php endif; ?>
                                        <div class="zoom-indicator">
                                            <i class="fas fa-search-plus"></i> Click to zoom
                                        </div>
                                        <div dir="ltr" class="swiper tf-product-media-main" id="gallery-swiper-started">
                                            <div class="swiper-wrapper">
                                                <?php foreach ($product_images as $index => $image): ?>
                                                <div class="swiper-slide">
                                                    <a href="<?php echo htmlspecialchars($image['image_path']); ?>" target="_blank" class="item" data-pswp-width="770px"
                                                        data-pswp-height="1075px">
                                                        <img class="tf-image-zoom lazyload" data-zoom="<?php echo htmlspecialchars($image['image_path']); ?>"
                                                            data-src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                                            src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                    </a>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="swiper-button-next button-style-arrow thumbs-next"></div>
                                            <div class="swiper-button-prev button-style-arrow thumbs-prev"></div>
                                        </div>
                                    </div>

                                    <!-- Thumbnails Below -->
                                    <div dir="ltr" class="swiper tf-product-media-thumbs thumbs-horizontal">
                                        <div class="swiper-wrapper">
                                            <?php foreach ($product_images as $index => $image): ?>
                                            <div class="swiper-slide">
                                                <div class="item thumb-item">
                                                    <img class="lazyload" data-src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                                        src="<?php echo htmlspecialchars($image['image_path']); ?>"
                                                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="flat-wrap-media-product">
                                    <img src="images/products/placeholder.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- /Product Images -->

                        <!-- Product Info -->
                        <div class="col-md-6">
                            <div class="tf-product-info-wrap position-relative">
                                <div class="tf-product-info-list">
                                    <h1 class="product-info-name h2"><?php echo htmlspecialchars($product['name']); ?></h1>

                                    <!-- Star Rating -->
                                    <div class="product-rating mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="stars">
                                                <?php
                                                $full_stars = floor($avg_rating);
                                                $half_star = ($avg_rating - $full_stars) >= 0.5;
                                                $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

                                                for ($i = 0; $i < $full_stars; $i++): ?>
                                                    <i class="fas fa-star star-color"></i>
                                                <?php endfor;

                                                if ($half_star): ?>
                                                    <i class="fas fa-star-half-alt star-color"></i>
                                                <?php endif;

                                                for ($i = 0; $i < $empty_stars; $i++): ?>
                                                    <i class="far fa-star star-color"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="rating-value fw-semibold"><?php echo $avg_rating; ?></span>
                                            <span class="text-muted">(<?php echo $review_count; ?> <?php echo $review_count === 1 ? 'review' : 'reviews'; ?>)</span>
                                        </div>
                                    </div>

                                    <?php if ($product['sku']): ?>
                                    <div class="product-info-meta mb-3">
                                        <span class="text-muted">SKU: <?php echo htmlspecialchars($product['sku']); ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Price Section -->
                                    <div class="product-price-section">
                                        <div class="product-info-price">
                                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                                                <span class="price-new">₹<?php echo number_format($product['sale_price'], 0); ?></span>
                                                <span class="price-old">₹<?php echo number_format($product['price'], 0); ?></span>
                                                <?php if ($discount_percentage > 0): ?>
                                                <span class="badge bg-danger">Save <?php echo $discount_percentage; ?>%</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="price-new">₹<?php echo number_format($product['price'], 0); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="product-trust-badges mb-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span class="fw-medium">In-House Manufacturing</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span class="fw-medium">Strict Quality Checked</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span class="fw-medium">Secure Payments</span>
                                        </div>
                                    </div>

                                    <!-- Short Description -->
                                    <?php if ($product['short_desc']): ?>
                                    <div class="product-short-description mb-4">
                                        <p><?php echo nl2br(htmlspecialchars($product['short_desc'])); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Stock Status -->
                                    <div class="product-stock-status mb-4">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> In Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Out of Stock</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Product Variations -->
                                    <?php if (!empty($product_variations)): ?>
                                    <div class="product-variations-wrapper mb-4">
                                        <?php foreach ($product_variations as $type_id => $variation_group): ?>
                                        <div class="variation-group mb-3" data-variation-type="<?php echo htmlspecialchars($variation_group['type_name']); ?>">
                                            <label class="variation-label fw-bold mb-2">
                                                <?php echo htmlspecialchars($variation_group['type_display']); ?>:
                                                <span class="selected-variation-value text-muted" data-type="<?php echo $type_id; ?>"></span>
                                            </label>

                                            <div class="variation-options d-flex flex-wrap gap-2">
                                                <?php foreach ($variation_group['variations'] as $index => $variation):
                                                    $is_out_of_stock = ($variation['stock_quantity'] <= 0);
                                                    $is_first = ($index === 0);
                                                ?>

                                                <?php if ($variation_group['input_type'] === 'color'): ?>
                                                    <!-- Color Swatch -->
                                                    <button type="button"
                                                            class="variation-option color-swatch <?php echo $is_first ? 'active' : ''; ?> <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>"
                                                            data-variation-id="<?php echo $variation['id']; ?>"
                                                            data-variation-type-id="<?php echo $type_id; ?>"
                                                            data-variation-value="<?php echo htmlspecialchars($variation['variation_value']); ?>"
                                                            data-price-adjustment="<?php echo $variation['price_adjustment']; ?>"
                                                            data-stock="<?php echo $variation['stock_quantity']; ?>"
                                                            <?php echo $is_out_of_stock ? 'disabled' : ''; ?>
                                                            title="<?php echo htmlspecialchars($variation['variation_value']); ?>">
                                                        <span class="color-circle" style="background-color: <?php echo htmlspecialchars($variation['color_code'] ?? '#ccc'); ?>;"></span>
                                                        <?php if ($is_out_of_stock): ?>
                                                        <span class="stock-line"></span>
                                                        <?php endif; ?>
                                                    </button>

                                                <?php elseif ($variation_group['input_type'] === 'image'): ?>
                                                    <!-- Image Swatch (for material/pattern) -->
                                                    <button type="button"
                                                            class="variation-option image-swatch <?php echo $is_first ? 'active' : ''; ?> <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>"
                                                            data-variation-id="<?php echo $variation['id']; ?>"
                                                            data-variation-type-id="<?php echo $type_id; ?>"
                                                            data-variation-value="<?php echo htmlspecialchars($variation['variation_value']); ?>"
                                                            data-price-adjustment="<?php echo $variation['price_adjustment']; ?>"
                                                            data-stock="<?php echo $variation['stock_quantity']; ?>"
                                                            data-image="<?php echo htmlspecialchars($variation['variation_image'] ?? ''); ?>"
                                                            <?php echo $is_out_of_stock ? 'disabled' : ''; ?>
                                                            title="<?php echo htmlspecialchars($variation['variation_value']); ?>">
                                                        <?php if ($variation['variation_image']): ?>
                                                            <img src="<?php echo htmlspecialchars($variation['variation_image']); ?>"
                                                                 alt="<?php echo htmlspecialchars($variation['variation_value']); ?>"
                                                                 class="swatch-image">
                                                        <?php else: ?>
                                                            <span class="swatch-text"><?php echo htmlspecialchars($variation['variation_name']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($is_out_of_stock): ?>
                                                        <span class="stock-line"></span>
                                                        <?php endif; ?>
                                                    </button>

                                                <?php else: ?>
                                                    <!-- Button Swatch (for size, etc.) -->
                                                    <button type="button"
                                                            class="variation-option button-swatch <?php echo $is_first ? 'active' : ''; ?> <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>"
                                                            data-variation-id="<?php echo $variation['id']; ?>"
                                                            data-variation-type-id="<?php echo $type_id; ?>"
                                                            data-variation-value="<?php echo htmlspecialchars($variation['variation_value']); ?>"
                                                            data-price-adjustment="<?php echo $variation['price_adjustment']; ?>"
                                                            data-stock="<?php echo $variation['stock_quantity']; ?>"
                                                            <?php echo $is_out_of_stock ? 'disabled' : ''; ?>>
                                                        <?php echo htmlspecialchars($variation['variation_value']); ?>
                                                        <?php if ($is_out_of_stock): ?>
                                                        <span class="stock-indicator">✕</span>
                                                        <?php endif; ?>
                                                    </button>
                                                <?php endif; ?>

                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>

                                        <!-- Hidden inputs to store selected variations -->
                                        <input type="hidden" id="selectedVariations" name="variations" value="">
                                        <input type="hidden" id="basePrice" value="<?php echo $product['sale_price'] ?: $product['price']; ?>">
                                    </div>
                                    <?php endif; ?>

                                    <!-- Quantity and Add to Cart -->
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <div class="tf-product-total-quantity mb-4">
                                        <div class="group-btn d-flex gap-3 flex-wrap">
                                            <div class="wg-quantity">
                                                <button type="button" class="btn-quantity btn-decrease">
                                                    <i class="icon icon-minus"></i>
                                                </button>
                                                <input class="quantity-product" type="text" name="number" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                                <button type="button" class="btn-quantity btn-increase">
                                                    <i class="icon icon-plus"></i>
                                                </button>
                                            </div>
                                            <a href="#shoppingCart" data-bs-toggle="offcanvas" class="tf-btn animate-btn btn-add-to-cart">
                                                <i class="icon icon-shopping-cart-simple me-2"></i> Add to Cart
                                            </a>
                                            <button type="button" class="hover-tooltip box-icon btn-add-wishlist<?php echo $isInWishlist ? ' added' : ''; ?>" data-product-id="<?php echo $product_id; ?>">
                                                <span class="icon <?php echo $isInWishlist ? 'icon-heart-fill' : 'icon-heart'; ?>"></span>
                                                <span class="tooltip"><?php echo $isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?></span>
                                            </button>
                                        </div>

                                        <a href="checkout.php" class="tf-btn btn-primary w-100 mt-3">
                                            <i class="fas fa-bolt me-2"></i> Buy It Now
                                        </a>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Product Features -->
                                    <div class="tf-product-delivery-return mb-4">
                                        <a href="shippingpolicy.php" class="product-delivery text-decoration-none">
                                            <i class="fas fa-truck"></i>
                                            <p>Estimated delivery: <span class="fw-bold">10-15 business days</span></p>
                                        </a>
                                        <a href="refund&returnspolicy.php" class="product-delivery text-decoration-none">
                                            <i class="fas fa-undo-alt"></i>
                                            <p>Return within <span class="fw-bold">10 days</span> of purchase</p>
                                        </a>
                                        <div class="product-delivery">
                                            <i class="fas fa-shield-alt"></i>
                                            <p><span class="fw-bold">Quality Guarantee</span> &mdash; Handcrafted with patience and precision by our master craftsmen.</p>
                                        </div>
                                    </div>

                                    <!-- Payment Methods -->
                                    <div class="tf-product-trust-seal">
                                        <p class="text-seal">Secure Payment</p>
                                        <ul class="list-card">
                                            <li class="card-item">
                                                <img src="images/payment/visa.png" alt="Visa">
                                            </li>
                                            <li class="card-item">
                                                <img src="images/payment/master-card.png" alt="Mastercard">
                                            </li>
                                            <li class="card-item">
                                                <img src="images/payment/g pay.png" alt="Google Pay">
                                            </li>
                                            <li class="card-item">
                                                <img src="images/payment/PhonePe_Logo.png" alt="PhonePe">
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Product Info -->
                    </div>
                </div>
            </div>
        </section>
        <!-- /Product Main -->

        <!-- Product Description & Specifications -->
        <section class="flat-spacing-2">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs widget-tab-1" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#description"
                                    type="button" role="tab">Description</button>
                            </li>
                            <?php if (!empty($product_attributes)): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#specifications"
                                    type="button" role="tab">Specifications</button>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shippingPolicy"
                                    type="button" role="tab">Shipping Policy</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#returnsPolicy"
                                    type="button" role="tab">Refund & Returns</button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <!-- Description Tab -->
                            <div class="tab-pane fade show active" id="description" role="tabpanel">
                                <div class="product-description">
                                    <?php if ($product['description']): ?>
                                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                                    <?php else: ?>
                                        <p>No detailed description available for this product.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Specifications Tab -->
                            <?php if (!empty($product_attributes)): ?>
                            <div class="tab-pane fade" id="specifications" role="tabpanel">
                                <table class="product-attribute-table">
                                    <tbody>
                                        <?php foreach ($product_attributes as $attribute): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attribute['attribute_name']); ?></td>
                                            <td><?php echo htmlspecialchars($attribute['attribute_value']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            <!-- Shipping Policy Tab -->
                            <div class="tab-pane fade" id="shippingPolicy" role="tabpanel">
                                <div class="shipping-policy-summary">
                                    <p><i class="fas fa-truck me-2" style="color:#d4a574;"></i> We deliver across India with an estimated delivery time of <strong>10–15 business days</strong> depending on your location.</p>
                                    <p>Shipping charges are calculated at checkout based on the delivery address and order weight. Orders above a certain value may qualify for <strong>free shipping</strong>.</p>
                                    <p>All orders are carefully packed to ensure your furniture arrives in perfect condition.</p>
                                    <a href="shippingpolicy.php" class="tf-btn animate-btn mt-3" style="display:inline-flex; align-items:center; gap:8px;">
                                        Read More <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Refund & Returns Policy Tab -->
                            <div class="tab-pane fade" id="returnsPolicy" role="tabpanel">
                                <div class="shipping-policy-summary">
                                    <p><i class="fas fa-undo-alt me-2" style="color:#d4a574;"></i> We offer hassle-free returns within <strong>10 days</strong> of delivery if the product is unused and in its original packaging.</p>
                                    <p>Refunds are processed within <strong>7–10 business days</strong> after we receive and inspect the returned item.</p>
                                    <p>Please note that customized or made-to-order furniture may not be eligible for returns unless there is a manufacturing defect.</p>
                                    <a href="refund&returnspolicy.php" class="tf-btn animate-btn mt-3" style="display:inline-flex; align-items:center; gap:8px;">
                                        Read More <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <section class="related-products-section">
            <div class="container">
                <div class="related-section-heading">
                    <span class="related-section-label">Curated For You</span>
                    <h3>You May Also Like</h3>
                    <p>Handpicked pieces that complement your style</p>
                </div>

                <div class="related-products-grid">
                    <?php foreach ($related_products as $related):
                        $related_discount = 0;
                        if ($related['sale_price'] && $related['sale_price'] < $related['price']) {
                            $related_discount = round((($related['price'] - $related['sale_price']) / $related['price']) * 100);
                        }
                    ?>
                    <div class="related-product-card">
                        <div class="rpc-image-wrap">
                            <?php if ($related_discount > 0): ?>
                            <span class="rpc-badge rpc-badge-sale">-<?php echo $related_discount; ?>%</span>
                            <?php endif; ?>
                            <?php if ($related['is_featured']): ?>
                            <span class="rpc-badge rpc-badge-featured"><i class="fas fa-star"></i></span>
                            <?php endif; ?>
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="rpc-image-link">
                                <img class="lazyload"
                                     data-src="<?php echo htmlspecialchars($related['image_path'] ?? 'images/products/placeholder.jpg'); ?>"
                                     src="<?php echo htmlspecialchars($related['image_path'] ?? 'images/products/placeholder.jpg'); ?>"
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </a>
                            <div class="rpc-quick-actions">
                                <button class="rpc-action-btn btn-quick-add" data-product-id="<?php echo $related['id']; ?>" title="Add to Cart">
                                    <i class="fas fa-shopping-bag"></i>
                                </button>
                                <button class="rpc-action-btn btn-wishlist-add" data-product-id="<?php echo $related['id']; ?>" title="Add to Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="rpc-details">
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="rpc-name"><?php echo htmlspecialchars($related['name']); ?></a>
                            <div class="rpc-price">
                                <?php if ($related['sale_price'] && $related['sale_price'] < $related['price']): ?>
                                    <span class="rpc-price-current">₹<?php echo number_format($related['sale_price'], 0); ?></span>
                                    <span class="rpc-price-original">₹<?php echo number_format($related['price'], 0); ?></span>
                                <?php else: ?>
                                    <span class="rpc-price-current">₹<?php echo number_format($related['price'], 0); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="rpc-view-btn">
                                View Product <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Javascript -->
    <?php include 'includes/scripts.php'; ?>

    <script>
        // Quantity controls
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.querySelector('.quantity-product');
            const decreaseBtn = document.querySelector('.btn-decrease');
            const increaseBtn = document.querySelector('.btn-increase');
            const addToCartBtn = document.querySelector('.btn-add-to-cart');
            const addToWishlistBtn = document.querySelector('.btn-add-wishlist');

            // Quantity controls with stopImmediatePropagation to prevent main.js handlers
            if (decreaseBtn && increaseBtn && quantityInput) {
                const maxQuantity = parseInt(quantityInput.getAttribute('max')) || 999;

                // Use capture phase with stopImmediatePropagation to prevent main.js from handling
                decreaseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    let value = parseInt(quantityInput.value) || 1;
                    if (value > 1) {
                        quantityInput.value = value - 1;
                    }
                }, true); // true = capture phase, fires before main.js bubble phase

                increaseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    let value = parseInt(quantityInput.value) || 1;
                    if (value < maxQuantity) {
                        quantityInput.value = value + 1;
                    }
                }, true); // true = capture phase, fires before main.js bubble phase
            }

            // Add to Cart functionality
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const productId = <?php echo $product['id']; ?>;
                    const quantity = parseInt(quantityInput ? quantityInput.value : 1);

                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Adding...';
                    this.style.pointerEvents = 'none';

                    // Send AJAX request
                    fetch('ajax/add-to-cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=${quantity}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            this.innerHTML = '<i class="fas fa-check me-2"></i> Added!';
                            this.style.background = '#28a745';

                            // Update cart count in header if element exists
                            const cartCountElements = document.querySelectorAll('.count-box:not(.wishlist-count)');
                            if (cartCountElements.length > 0 && data.cart_count) {
                                cartCountElements.forEach(el => {
                                    el.textContent = data.cart_count;
                                });
                            }

                            // Show notification
                            showNotification('Success!', data.message, 'success');

                            // Reset button after 2 seconds
                            setTimeout(() => {
                                this.innerHTML = originalText;
                                this.style.background = '#d4a574';
                                this.style.pointerEvents = 'auto';
                            }, 2000);
                        } else {
                            // Show error message
                            showNotification('Error', data.message, 'error');
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error', 'Failed to add item to cart. Please try again.', 'error');
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                    });
                });
            }

            // Add to Wishlist functionality
            if (addToWishlistBtn) {
                addToWishlistBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const productId = <?php echo $product['id']; ?>;

                    // Send AJAX request
                    fetch('ajax/wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=toggle&product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Toggle heart icon state
                            const heartIcon = this.querySelector('.icon');
                            if (data.action === 'added') {
                                heartIcon.classList.remove('icon-heart');
                                heartIcon.classList.add('icon-heart-fill');
                                this.classList.add('added');
                                showNotification('Success!', 'Added to wishlist', 'success');
                            } else {
                                heartIcon.classList.remove('icon-heart-fill');
                                heartIcon.classList.add('icon-heart');
                                this.classList.remove('added');
                                showNotification('Success!', 'Removed from wishlist', 'success');
                            }

                            // Update wishlist count in header
                            if (data.wishlist_count !== undefined && window.updateWishlistCount) {
                                window.updateWishlistCount(data.wishlist_count);
                            }

                            // Update tooltip text
                            const tooltip = this.querySelector('.tooltip');
                            if (tooltip) {
                                tooltip.textContent = data.action === 'added' ? 'Remove from Wishlist' : 'Add to Wishlist';
                            }
                        } else {
                            if (data.redirect) {
                                // User not logged in, redirect to login
                                showNotification('Login Required', 'Please login to add items to wishlist', 'warning');
                                setTimeout(() => {
                                    window.location.href = data.redirect + '?redirect=' + encodeURIComponent(window.location.href);
                                }, 1500);
                            } else {
                                showNotification('Error', data.error || 'Failed to update wishlist', 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error', 'Failed to update wishlist. Please try again.', 'error');
                    });
                });
            }

            // Notification function
            function showNotification(title, message, type) {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `notification-toast ${type}`;
                notification.innerHTML = `
                    <div class="notification-header">
                        <strong>${title}</strong>
                        <button class="notification-close">&times;</button>
                    </div>
                    <div class="notification-body">${message}</div>
                `;

                // Add to page
                document.body.appendChild(notification);

                // Show notification
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);

                // Auto close after 3 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);

                // Close button handler
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

            // Initialize Swiper for product gallery - Horizontal thumbnails below main image
            const galleryThumbs = new Swiper('.tf-product-media-thumbs', {
                spaceBetween: 10,
                slidesPerView: 4,
                freeMode: true,
                watchSlidesProgress: true,
                direction: 'horizontal',
                breakpoints: {
                    0: {
                        slidesPerView: 3,
                        spaceBetween: 8
                    },
                    576: {
                        slidesPerView: 4,
                        spaceBetween: 10
                    },
                    768: {
                        slidesPerView: 5,
                        spaceBetween: 10
                    }
                }
            });

            const galleryMain = new Swiper('.tf-product-media-main', {
                spaceBetween: 10,
                navigation: {
                    nextEl: '.thumbs-next',
                    prevEl: '.thumbs-prev',
                },
                thumbs: {
                    swiper: galleryThumbs,
                },
                on: {
                    init: function() {
                        updateImageCounter(this);
                    },
                    slideChange: function() {
                        updateImageCounter(this);
                    }
                }
            });

            // Add image counter
            function updateImageCounter(swiper) {
                const totalImages = swiper.slides.length;
                if (totalImages > 1) {
                    let counterDiv = document.querySelector('.image-counter');
                    if (!counterDiv) {
                        counterDiv = document.createElement('div');
                        counterDiv.className = 'image-counter';
                        swiper.el.appendChild(counterDiv);
                    }
                    counterDiv.innerHTML = `<i class="fas fa-images"></i> ${swiper.activeIndex + 1} / ${totalImages}`;
                }
            }


            // ============================================================================
            // PRODUCT VARIATIONS FUNCTIONALITY
            // ============================================================================
            const variationOptions = document.querySelectorAll('.variation-option');
            const selectedVariationsInput = document.getElementById('selectedVariations');
            const basePriceInput = document.getElementById('basePrice');
            const priceDisplayNew = document.querySelector('.product-price-section .price-new');
            const priceDisplayOld = document.querySelector('.product-price-section .price-old');
            const stockStatusBadge = document.querySelector('.product-stock-status .badge');

            // Store selected variations by type
            const selectedVariations = {};
            let basePrice = basePriceInput ? parseFloat(basePriceInput.value) : 0;

            // Initialize first variation as selected for each type
            document.querySelectorAll('.variation-group').forEach(group => {
                const firstActive = group.querySelector('.variation-option.active');
                if (firstActive) {
                    const typeId = firstActive.dataset.variationTypeId;
                    const variationId = firstActive.dataset.variationId;
                    const value = firstActive.dataset.variationValue;
                    selectedVariations[typeId] = {
                        id: variationId,
                        value: value,
                        priceAdjustment: parseFloat(firstActive.dataset.priceAdjustment) || 0,
                        stock: parseInt(firstActive.dataset.stock) || 0
                    };

                    // Update label
                    const valueLabel = group.querySelector('.selected-variation-value');
                    if (valueLabel) {
                        valueLabel.textContent = value;
                    }
                }
            });

            // Update initial price and stock
            updatePriceDisplay();
            updateStockStatus();

            // Handle variation selection
            variationOptions.forEach(option => {
                option.addEventListener('click', function() {
                    if (this.disabled) return;

                    const typeId = this.dataset.variationTypeId;
                    const variationId = this.dataset.variationId;
                    const value = this.dataset.variationValue;
                    const priceAdjustment = parseFloat(this.dataset.priceAdjustment) || 0;
                    const stock = parseInt(this.dataset.stock) || 0;
                    const variationImage = this.dataset.image;

                    // Remove active from siblings
                    const group = this.closest('.variation-group');
                    group.querySelectorAll('.variation-option').forEach(opt => {
                        opt.classList.remove('active');
                    });

                    // Add active to this
                    this.classList.add('active');

                    // Update selected variations
                    selectedVariations[typeId] = {
                        id: variationId,
                        value: value,
                        priceAdjustment: priceAdjustment,
                        stock: stock
                    };

                    // Update label
                    const valueLabel = group.querySelector('.selected-variation-value');
                    if (valueLabel) {
                        valueLabel.textContent = value;
                    }

                    // Update hidden input with JSON
                    if (selectedVariationsInput) {
                        selectedVariationsInput.value = JSON.stringify(selectedVariations);
                    }

                    // Change product image if variation has an image
                    if (variationImage && variationImage !== '') {
                        changeProductImage(variationImage);
                    }

                    // Update price and stock
                    updatePriceDisplay();
                    updateStockStatus();
                });
            });

            function updatePriceDisplay() {
                if (!priceDisplayNew || !basePriceInput) return;

                // Calculate total price adjustment
                let totalAdjustment = 0;
                Object.values(selectedVariations).forEach(variation => {
                    totalAdjustment += variation.priceAdjustment;
                });

                const newPrice = basePrice + totalAdjustment;

                // Add animation class
                priceDisplayNew.classList.add('price-updating');

                // Update price
                priceDisplayNew.textContent = '₹' + newPrice.toLocaleString('en-IN', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });

                // Remove animation class after animation completes
                setTimeout(() => {
                    priceDisplayNew.classList.remove('price-updating');
                }, 500);
            }

            function updateStockStatus() {
                if (!stockStatusBadge) return;

                // Check minimum stock across all selected variations
                let minStock = Infinity;
                let hasSelection = false;

                Object.values(selectedVariations).forEach(variation => {
                    hasSelection = true;
                    if (variation.stock < minStock) {
                        minStock = variation.stock;
                    }
                });

                // If no variations selected, use product stock
                if (!hasSelection || minStock === Infinity) {
                    return;
                }

                // Update stock status
                if (minStock > 0) {
                    stockStatusBadge.className = 'badge bg-success';
                    stockStatusBadge.innerHTML = '<i class="fas fa-check-circle"></i> In Stock';

                    // Update quantity input max
                    if (quantityInput) {
                        quantityInput.setAttribute('max', minStock);
                        if (parseInt(quantityInput.value) > minStock) {
                            quantityInput.value = minStock;
                        }
                    }

                    // Enable add to cart
                    if (addToCartBtn) {
                        addToCartBtn.style.pointerEvents = 'auto';
                        addToCartBtn.style.opacity = '1';
                    }
                } else {
                    stockStatusBadge.className = 'badge bg-danger';
                    stockStatusBadge.innerHTML = '<i class="fas fa-times-circle"></i> Out of Stock';

                    // Disable add to cart
                    if (addToCartBtn) {
                        addToCartBtn.style.pointerEvents = 'none';
                        addToCartBtn.style.opacity = '0.5';
                    }
                }
            }

            function changeProductImage(imagePath) {
                const mainImage = document.querySelector('.tf-image-zoom img');
                if (mainImage) {
                    // Add fade effect
                    mainImage.style.opacity = '0.5';

                    setTimeout(() => {
                        mainImage.src = imagePath;
                        mainImage.dataset.src = imagePath;

                        // Restore opacity
                        mainImage.style.opacity = '1';
                    }, 200);
                }
            }

            // Update Add to Cart to include variations
            if (selectedVariationsInput && addToCartBtn) {
                const originalAddToCartHandler = addToCartBtn.onclick;

                addToCartBtn.addEventListener('click', function(e) {
                    // Add variations to the cart request
                    const variations = selectedVariationsInput.value;
                    if (variations) {
                        // Store in session or send with cart data
                        sessionStorage.setItem('pendingVariations', variations);
                    }
                });
            }

            // Quick Add to Cart for Related Products
            document.querySelectorAll('.btn-quick-add').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.dataset.productId;
                    const originalContent = this.innerHTML;

                    // Show loading
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    this.disabled = true;

                    fetch('ajax/add-to-cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=1`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.innerHTML = '<i class="fas fa-check"></i>';
                            showNotification('Success!', data.message, 'success');

                            // Update cart count
                            const cartCountElements = document.querySelectorAll('.count-box:not(.wishlist-count)');
                            if (cartCountElements.length > 0 && data.cart_count) {
                                cartCountElements.forEach(el => el.textContent = data.cart_count);
                            }

                            setTimeout(() => {
                                this.innerHTML = originalContent;
                                this.disabled = false;
                            }, 2000);
                        } else {
                            showNotification('Error', data.message, 'error');
                            this.innerHTML = originalContent;
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error', 'Failed to add item to cart', 'error');
                        this.innerHTML = originalContent;
                        this.disabled = false;
                    });
                });
            });

            // Lazy-load policy tab content on first click
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function() {
                    const target = document.querySelector(this.dataset.bsTarget);
                    if (!target) return;
                    const policyWrap = target.querySelector('.policy-tab-content');
                    if (!policyWrap || policyWrap.dataset.loaded === 'true') return;

                    const url = policyWrap.dataset.policyUrl;
                    policyWrap.dataset.loaded = 'true';

                    fetch(url)
                        .then(r => r.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const content = doc.querySelector('.policy-page-content');
                            if (content) {
                                policyWrap.innerHTML = content.innerHTML;
                            } else {
                                policyWrap.innerHTML = '<p>Could not load policy content. <a href="' + url + '" target="_blank">View full page</a></p>';
                            }
                        })
                        .catch(() => {
                            policyWrap.innerHTML = '<p>Failed to load. <a href="' + url + '" target="_blank">View full page</a></p>';
                        });
                });
            });

            // Add to Wishlist for Related Products
            document.querySelectorAll('.btn-wishlist-add').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.dataset.productId;
                    const icon = this.querySelector('i');

                    fetch('ajax/wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=toggle&product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.action === 'added') {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                                this.classList.add('active');
                                showNotification('Success!', 'Added to wishlist', 'success');
                            } else {
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                                this.classList.remove('active');
                                showNotification('Success!', 'Removed from wishlist', 'success');
                            }

                            // Update wishlist count
                            if (data.wishlist_count !== undefined && window.updateWishlistCount) {
                                window.updateWishlistCount(data.wishlist_count);
                            }
                        } else {
                            if (data.redirect) {
                                showNotification('Login Required', 'Please login to add items to wishlist', 'warning');
                                setTimeout(() => {
                                    window.location.href = data.redirect + '?redirect=' + encodeURIComponent(window.location.href);
                                }, 1500);
                            } else {
                                showNotification('Error', data.error || 'Failed to update wishlist', 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error', 'Failed to update wishlist', 'error');
                    });
                });
            });
        });
    </script>

    <style>
        /* Star rating color */
        .star-color { color: #f5a623; }

        /* Trust badges styling */
        .product-trust-badges {
            background: #f9fafb;
            border-radius: 10px;
            padding: 16px 18px;
            border: 1px solid #f0f0f0;
        }
        .product-trust-badges .fas.fa-check-circle {
            color: #5da271;
            font-size: 14px;
        }
        .product-trust-badges .fw-medium {
            font-size: 13.5px;
            color: #444;
        }

        /* Policy tab content */
        .policy-tab-loader {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            font-size: 14px;
        }
        .policy-tab-loader i {
            margin-right: 8px;
            color: #d4a574;
        }
        .policy-tab-content {
            max-width: 850px;
        }
        .policy-tab-content .policy-section {
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        .policy-tab-content .policy-section:last-child {
            border-bottom: none;
        }
        .policy-tab-content h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 14px;
        }
        .policy-tab-content h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
            margin-top: 16px;
        }
        .policy-tab-content p {
            font-size: 0.9rem;
            line-height: 1.7;
            color: #555;
            margin-bottom: 10px;
        }
        .policy-tab-content ul,
        .policy-tab-content ol {
            padding-left: 20px;
            margin-bottom: 12px;
        }
        .policy-tab-content li {
            font-size: 0.88rem;
            line-height: 1.7;
            color: #555;
            margin-bottom: 4px;
        }
        .policy-tab-content .policy-intro {
            background: #fdf8f3;
            border-left: 3px solid #d4a574;
            padding: 16px 20px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 24px;
        }
        .policy-tab-content .policy-table-wrapper {
            overflow-x: auto;
        }
        .policy-tab-content .policy-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        .policy-tab-content .policy-table th,
        .policy-tab-content .policy-table td {
            padding: 10px 14px;
            border: 1px solid #eee;
            text-align: left;
        }
        .policy-tab-content .policy-table th {
            background: #f8f8f8;
            font-weight: 600;
            color: #333;
        }
        .policy-tab-content .policy-highlight {
            background: #f9fafb;
            border-radius: 10px;
            padding: 20px 24px;
            border: 1px solid #eee;
            margin-top: 20px;
        }
        .policy-tab-content .policy-highlight h3 {
            font-size: 1rem;
            color: #333;
            margin-top: 0;
        }
        .policy-tab-content .policy-highlight i {
            color: #d4a574;
            margin-right: 6px;
        }
        .policy-tab-content .policy-contact-box {
            background: #f9fafb;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 18px 22px;
            margin-top: 12px;
        }
        .policy-tab-content .policy-contact-box h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }
        .policy-tab-content .policy-contact-box p {
            font-size: 0.85rem;
            margin-bottom: 4px;
        }
        .policy-tab-content .effective-date {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f0f0f0;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.82rem;
            color: #666;
            margin-bottom: 20px;
        }
        .policy-tab-content .effective-date i {
            color: #d4a574;
        }
    </style>

</body>
</html>
