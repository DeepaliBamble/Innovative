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

    <style>
        /* Product Detail Enhancements */
        .product-info-name {
            color: #333;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 15px;
        }

        .product-info-meta {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 6px;
            display: inline-block;
        }

        .product-price-section {
            padding: 25px 20px;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .product-info-price {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .product-trust-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px 18px;
            margin-top: 10px;
            color: #6c757d;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .product-trust-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .product-trust-item i {
            color: #d4a574;
            font-size: 0.95rem;
        }

        .product-short-description {
            background: #fff;
            padding: 20px;
            border-left: 4px solid #d4a574;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .product-stock-status .badge {
            font-size: 14px;
            padding: 8px 16px;
            font-weight: 500;
        }

        .wg-quantity {
            display: flex;
            align-items: center;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            overflow: hidden;
        }

        .btn-quantity {
            background: #f8f9fa;
            border: none;
            padding: 12px 18px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-quantity:hover {
            background: #d4a574;
            color: white;
        }

        .quantity-product {
            border: none;
            width: 60px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
        }

        .tf-btn.btn-add-to-cart {
            background: #d4a574;
            color: white;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .tf-btn.btn-add-to-cart:hover {
            background: #c49464;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 165, 116, 0.3);
        }

        .tf-btn.btn-primary {
            background: #333;
            color: white;
            padding: 14px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .tf-btn.btn-primary:hover {
            background: #d4a574;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(51, 51, 51, 0.3);
        }

        .tf-product-delivery-return {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .product-delivery {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 0;
        }

        .product-delivery .icon, .product-delivery i {
            color: #d4a574;
            font-size: 20px;
        }

        .tf-product-trust-seal {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e5e5;
        }

        .list-card {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .card-item img {
            height: 35px;
            object-fit: contain;
        }

        /* Tabs Styling */
        .nav-tabs {
            border-bottom: 2px solid #e5e5e5;
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #666;
            padding: 15px 25px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link.active {
            color: #d4a574;
            border-bottom-color: #d4a574;
            background: transparent;
        }

        .nav-tabs .nav-link:hover {
            color: #d4a574;
        }

        .tab-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        /* Product Attributes Table */
        .product-attribute-table {
            width: 100%;
            margin-top: 10px;
        }

        .product-attribute-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .product-attribute-table tbody tr:last-child {
            border-bottom: none;
        }

        .product-attribute-table td {
            padding: 16px 12px;
        }

        .product-attribute-table td:first-child {
            font-weight: 600;
            width: 40%;
            color: #333;
            background: #f8f9fa;
        }

        .product-attribute-table td:last-child {
            color: #666;
            background: #fff;
        }

        .product-description {
            line-height: 1.9;
            color: #666;
            font-size: 15px;
        }

        .product-description h4 {
            color: #333;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .product-description ul {
            list-style: none;
            padding-left: 0;
        }

        .product-description ul li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }

        .product-description ul li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #d4a574;
            font-weight: bold;
        }

        /* Related Products Section */
        .related-products-section {
            padding: 80px 0;
            background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
        }

        .section-header {
            margin-bottom: 40px;
        }

        .flat-title {
            text-align: left;
        }

        .flat-title h3 {
            color: #333;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .flat-title h3::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: #d4a574;
            border-radius: 2px;
        }

        .flat-title p {
            color: #666;
            font-size: 0.95rem;
            margin-top: 12px;
            margin-bottom: 0;
        }

        /* Swiper Navigation Buttons */
        .swiper-nav-buttons {
            display: flex;
            gap: 10px;
        }

        .swiper-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #d4a574;
            background: white;
            color: #d4a574;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .swiper-btn:hover {
            background: #d4a574;
            color: white;
            transform: scale(1.1);
        }

        .swiper-btn.swiper-button-disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .card-product {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-product:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.15);
        }

        .card-product_wrapper {
            position: relative;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
            background: #f8f9fa;
            aspect-ratio: 1 / 1;
        }

        .card-product_wrapper .product-img {
            display: block;
            width: 100%;
            height: 100%;
        }

        .card-product_wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-product:hover .card-product_wrapper img {
            transform: scale(1.15);
        }

        .card-product_info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .card-product_info .name-product {
            color: #333;
            font-weight: 600;
            font-size: 1rem;
            line-height: 1.4;
            transition: color 0.3s;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-decoration: none;
            margin: 0;
        }

        .card-product:hover .card-product_info .name-product {
            color: #d4a574;
        }

        .card-product_info .price {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .price-new {
            color: #d4a574;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .price-old {
            text-decoration: line-through;
            color: #999;
            font-size: 1rem;
            font-weight: 500;
        }

        .related-product-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #d4a574 0%, #c49464 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            z-index: 5;
            box-shadow: 0 2px 8px rgba(212, 165, 116, 0.4);
        }

        .related-sale-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, #ff4757 0%, #ff6348 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            z-index: 5;
            box-shadow: 0 2px 8px rgba(255, 71, 87, 0.4);
        }

        .quick-view-btn {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            color: #333;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 5;
            text-decoration: none;
            white-space: nowrap;
        }

        .card-product:hover .quick-view-btn {
            bottom: 15px;
        }

        .quick-view-btn:hover {
            background: #d4a574;
            color: white;
            transform: translateX(-50%) scale(1.05);
        }

        .related-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        /* Image Gallery Styling */
        .tf-product-media-wrap {
            position: sticky;
            top: 100px;
        }

        .product-thumbs-slider {
            display: flex;
            gap: 15px;
        }

        .tf-product-media-thumbs {
            width: 100px;
            height: auto;
        }

        .tf-product-media-thumbs .swiper-slide {
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            opacity: 0.6;
        }

        .tf-product-media-thumbs .swiper-slide:hover {
            opacity: 1;
            border-color: #d4a574;
            transform: scale(1.05);
        }

        .tf-product-media-thumbs .swiper-slide-thumb-active {
            opacity: 1;
            border-color: #d4a574;
            box-shadow: 0 4px 12px rgba(212, 165, 116, 0.3);
        }

        .tf-product-media-thumbs .item {
            width: 100%;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
        }

        .tf-product-media-thumbs .item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .tf-product-media-thumbs .swiper-slide:hover .item img {
            transform: scale(1.1);
        }

        .flat-wrap-media-product {
            flex: 1;
            position: relative;
        }

        .tf-product-media-main {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            background: #fff;
        }

        .tf-product-media-main .swiper-slide {
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 500px;
        }

        .tf-product-media-main .item {
            display: block;
            width: 100%;
            height: 100%;
        }

        .tf-product-media-main img {
            width: 100%;
            height: auto;
            object-fit: contain;
            transition: transform 0.4s ease;
        }

        .tf-product-media-main .swiper-slide:hover img {
            transform: scale(1.05);
        }

        /* Navigation Arrows */
        .thumbs-next,
        .thumbs-prev {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            cursor: pointer;
            z-index: 20;
        }

        .thumbs-next:hover,
        .thumbs-prev:hover {
            background: #d4a574;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(212, 165, 116, 0.4);
        }

        .thumbs-next::after,
        .thumbs-prev::after {
            font-size: 16px;
            font-weight: bold;
        }

        .thumbs-next {
            right: 15px;
        }

        .thumbs-prev {
            left: 15px;
        }

        .zoom-indicator,
        .image-counter {
            pointer-events: none;
        }

        /* Loading skeleton for images */
        .tf-image-zoom {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .tf-image-zoom.lazyloaded {
            animation: none;
            background: transparent;
        }

        /* Product Badges */
        .sale-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #ff4757 0%, #ff6348 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(255, 71, 87, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 11px;
            z-index: 10;
            box-shadow: 0 3px 12px rgba(251, 191, 36, 0.5);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            letter-spacing: 0.5px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            width: auto;
            max-width: fit-content;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .featured-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 16px rgba(251, 191, 36, 0.6);
        }

        .featured-badge i {
            color: white;
            font-size: 11px;
            animation: starPulse 2s ease-in-out infinite;
        }

        @keyframes starPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        .zoom-indicator {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 16px;
            border-radius: 25px;
            font-size: 13px;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }

        .tf-product-media-main:hover .zoom-indicator {
            opacity: 1;
        }

        .zoom-indicator i {
            animation: zoomPulse 1.5s infinite;
        }

        @keyframes zoomPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

      

        /* Fix header overlap issue */
        .s-page-title.style-2 {
            margin-top: 100px !important;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }

        /* Breadcrumb styling */
        .breadcrumbs-page {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .breadcrumbs-page .link {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumbs-page .link:hover {
            color: #d4a574;
        }

        .breadcrumbs-page .current-page {
            color: #d4a574;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .s-page-title.style-2 {
                margin-top: 70px !important;
                padding-top: 10px !important;
            }

            .product-info-price {
                flex-direction: column;
                align-items: flex-start;
            }

            .group-btn {
                flex-direction: column !important;
                width: 100%;
            }

            .group-btn > * {
                width: 100%;
            }

            .nav-tabs .nav-link {
                padding: 12px 15px;
                font-size: 14px;
            }

            .tab-content {
                padding: 20px 15px;
            }

            .product-thumbs-slider {
                flex-direction: column-reverse;
                gap: 10px;
            }

            .tf-product-media-thumbs {
                width: 100%;
                height: auto;
            }

            .tf-product-media-thumbs .item {
                height: 80px;
            }

            .tf-product-media-main .swiper-slide {
                min-height: 350px;
            }

            .sale-badge,
            .featured-badge {
                padding: 6px 12px;
                font-size: 12px;
            }

            .zoom-indicator {
                padding: 8px 12px;
                font-size: 11px;
            }

            .image-counter {
                padding: 6px 10px;
                font-size: 11px;
            }

            .thumbs-next,
            .thumbs-prev {
                width: 35px;
                height: 35px;
            }

            .thumbs-next::after,
            .thumbs-prev::after {
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            .s-page-title.style-2 {
                margin-top: 70px !important;
                padding-top: 15px !important;
            }

            .tf-product-media-main .swiper-slide {
                min-height: 280px;
            }

            .sale-badge {
                top: 10px;
                left: 10px;
            }

            .featured-badge {
                top: 10px;
                right: 10px;
            }

            .zoom-indicator {
                display: none;
            }

            /* Related Products Responsive */
            .related-products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .card-product_info {
                padding: 15px;
            }

            .card-product_info .name-product {
                font-size: 0.9rem;
            }

            .price-new {
                font-size: 1.1rem;
            }

            .price-old {
                font-size: 0.9rem;
            }

            .flat-title h3 {
                font-size: 1.5rem;
            }

            .flat-title p {
                font-size: 0.9rem;
            }
        }

        @media (min-width: 577px) and (max-width: 768px) {
            .related-products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (min-width: 769px) and (max-width: 992px) {
            .related-products-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 25px;
            }
        }

        @media (min-width: 993px) {
            .related-products-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
    </style>
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
                                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                            style="width: 100%; height: auto; object-fit: contain; max-height: 500px;">
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
                                                    <i class="fas fa-star" style="color: #f5a623;"></i>
                                                <?php endfor;

                                                if ($half_star): ?>
                                                    <i class="fas fa-star-half-alt" style="color: #f5a623;"></i>
                                                <?php endif;

                                                for ($i = 0; $i < $empty_stars; $i++): ?>
                                                    <i class="far fa-star" style="color: #f5a623;"></i>
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
                                                <span class="price-new h2 fw-bold" style="color: #d4a574;">₹<?php echo number_format($product['sale_price'], 0); ?></span>
                                                <span class="price-old h4 text-muted text-decoration-line-through ms-2">₹<?php echo number_format($product['price'], 0); ?></span>
                                                <?php if ($discount_percentage > 0): ?>
                                                <span class="badge bg-danger ms-2" style="font-size: 14px; padding: 6px 12px;">Save <?php echo $discount_percentage; ?>%</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="price-new h2 fw-bold" style="color: #d4a574;">₹<?php echo number_format($product['price'], 0); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-trust-row">
                                            <span class="product-trust-item"><i class="fa-solid fa-check-circle"></i>In-House manufacturing</span>
                                            <span class="product-trust-item"><i class="fa-solid fa-check-circle"></i>Strict Quality Checked</span>
                                            <span class="product-trust-item"><i class="fa-solid fa-check-circle"></i>Secure Payments</span>
                                        </div>
                                    </div>

                                    <!-- Short Description -->
                                    <?php if ($product['short_desc']): ?>
                                    <div class="product-short-description mb-4">
                                        <p class="text-muted" style="font-size: 1rem; font-weight: 500;"><?php echo nl2br(htmlspecialchars($product['short_desc'])); ?></p>
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
                                        <a href="shippingpolicy.php" class="product-delivery mb-1 text-decoration-none" style="cursor: pointer;">
                                            <div class="icon icon-clock-cd"></div>
                                            <p class="mb-0" style="font-size: 0.95rem;">Estimated delivery: <span class="fw-bold text-black">10-15 business days</span> <i class="fas fa-external-link-alt ms-1 small"></i></p>
                                        </a>
                                        <a href="refund&returnspolicy.php" class="product-delivery return mb-1 text-decoration-none" style="cursor: pointer;">
                                            <div class="icon icon-compare"></div>
                                            <p class="mb-0" style="font-size: 0.95rem;">Return within <span class="fw-bold text-black">10 days</span> of purchase <i class="fas fa-external-link-alt ms-1 small"></i></p>
                                        </a>
                                        <div class="product-delivery">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            <p class="mb-0" style="font-size: 0.95rem;"><span class="fw-bold text-black">Quality Guarantee</span> Handcrafted with patience and precision, reflecting the care, skill, and passion of our master craftsmen.</p>
                                        </div>
                                    </div>

                                    <!-- Payment Methods -->
                                    <div class="tf-product-trust-seal">
                                        <p class="text-seal mb-2" style="font-size: 0.95rem; font-weight: 600;">Secure Payment:</p>
                                        <ul class="list-card d-flex gap-2">
                                            <li class="card-item">
                                                <img src="images/payment/visa.png" alt="Visa" style="height: 30px;">
                                            </li>
                                            <li class="card-item">
                                                <img src="images/payment/master-card.png" alt="Mastercard" style="height: 30px;">
                                            </li>
                                            <li class="card-item">
                                                <img src="images/payment/g pay.png" alt="g pay" style="height: 30px;">
                                            </li>
                                             <li class="card-item">
                                                <img src="images/payment/PhonePe_Logo.png" alt="PhonePe" style="height: 30px;">
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
                                <button class="nav-link active h5 fw-semibold" data-bs-toggle="tab" data-bs-target="#description"
                                    type="button" role="tab">Description</button>
                            </li>
                            <?php if (!empty($product_attributes)): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link h5 fw-semibold" data-bs-toggle="tab" data-bs-target="#specifications"
                                    type="button" role="tab">Specifications</button>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link h5 fw-semibold" href="shippingpolicy.php" target="_blank">Shipping Policy</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link h5 fw-semibold" href="refund&returnspolicy.php" target="_blank">Refund & Returns Policy</a>
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
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <section class="related-products-section">
            <div class="container">
                <div class="section-header mb-4">
                    <div class="flat-title mb-0">
                        <h3 class="fw-semibold">You May Also Like</h3>
                        <p class="mb-0">Discover more furniture from our collection</p>
                    </div>
                </div>

                <div class="related-products-grid">
                    <?php foreach ($related_products as $related):
                        // Calculate discount for related product
                        $related_discount = 0;
                        if ($related['sale_price'] && $related['sale_price'] < $related['price']) {
                            $related_discount = round((($related['price'] - $related['sale_price']) / $related['price']) * 100);
                        }
                    ?>
                    <div class="related-product-item">
                        <div class="card-product">
                                <div class="card-product_wrapper">
                                    <?php if ($related_discount > 0): ?>
                                    <div class="related-sale-badge">
                                        -<?php echo $related_discount; ?>%
                                    </div>
                                    <?php endif; ?>
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="product-img">
                                        <img class="lazyload img-product"
                                             data-src="<?php echo htmlspecialchars($related['image_path'] ?? 'images/products/placeholder.jpg'); ?>"
                                             src="<?php echo htmlspecialchars($related['image_path'] ?? 'images/products/placeholder.jpg'); ?>"
                                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                                    </a>
                                    <div class="product-actions">
                                        <button class="action-btn btn-quick-add" data-product-id="<?php echo $related['id']; ?>" title="Add to Cart">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        <button class="action-btn btn-wishlist-add" data-product-id="<?php echo $related['id']; ?>" title="Add to Wishlist">
                                            <i class="far fa-heart"></i>
                                        </button>
                                        <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-product_info">
                                    <a href="product-detail.php?id=<?php echo $related['id']; ?>"
                                       class="name-product"><?php echo htmlspecialchars($related['name']); ?></a>
                                    <?php if (!empty($related['short_desc'])): ?>
                                    <p class="product-desc">
                                        <?php echo htmlspecialchars(substr($related['short_desc'], 0, 60)); ?><?php echo strlen($related['short_desc']) > 60 ? '...' : ''; ?>
                                    </p>
                                    <?php endif; ?>
                                    <div class="price-row">
                                        <div class="price">
                                            <?php if ($related['sale_price'] && $related['sale_price'] < $related['price']): ?>
                                                <span class="price-new">₹<?php echo number_format($related['sale_price'], 0); ?></span>
                                                <span class="price-old">₹<?php echo number_format($related['price'], 0); ?></span>
                                            <?php else: ?>
                                                <span class="price-new">₹<?php echo number_format($related['price'], 0); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <button class="btn-add-cart-full btn-quick-add" data-product-id="<?php echo $related['id']; ?>">
                                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                    </button>
                                </div>
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

            const galleryMainEl = document.querySelector('.tf-product-media-main');
            const galleryNextBtn = galleryMainEl ? galleryMainEl.querySelector('.thumbs-next') : null;
            const galleryPrevBtn = galleryMainEl ? galleryMainEl.querySelector('.thumbs-prev') : null;

            const galleryMain = new Swiper('.tf-product-media-main', {
                spaceBetween: 10,
                navigation: {
                    nextEl: galleryNextBtn,
                    prevEl: galleryPrevBtn,
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

        /* Wishlist button active state */
        .btn-add-wishlist.added .icon {
            color: var(--primary);
        }

        .btn-add-wishlist.added {
            background-color: rgba(158, 103, 71, 0.1);
            border-color: var(--primary);
        }

        .btn-add-wishlist.added:hover {
            background-color: rgba(158, 103, 71, 0.15);
        }

        /* Product Gallery - Horizontal Thumbnails Below */
        .product-gallery-vertical {
            display: flex;
            flex-direction: column;
        }

        .main-image-container {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
        }

        .main-image-container .tf-product-media-main {
            width: 100%;
        }

        .main-image-container .swiper-slide .item {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            background: #f8f9fa;
        }

        .main-image-container .swiper-slide .item img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
        }

        /* Thumbnail styles */
        .tf-product-media-thumbs.thumbs-horizontal {
            width: 100%;
            margin-top: 15px;
        }

        .tf-product-media-thumbs.thumbs-horizontal .swiper-slide {
            width: auto;
            height: auto;
        }

        .tf-product-media-thumbs.thumbs-horizontal .item {
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .tf-product-media-thumbs.thumbs-horizontal .item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            display: block;
        }

        .tf-product-media-thumbs.thumbs-horizontal .swiper-slide-thumb-active .item {
            border-color: #d4a574;
            box-shadow: 0 2px 8px rgba(212, 165, 116, 0.3);
        }

        .tf-product-media-thumbs.thumbs-horizontal .item:hover {
            border-color: #d4a574;
        }

        /* Featured badge and zoom indicator positioning */
        .main-image-container .featured-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 10;
            background: linear-gradient(135deg, #d4a574 0%, #b8956a 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .main-image-container .zoom-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        /* Navigation arrows */
        .main-image-container .swiper-button-next,
        .main-image-container .swiper-button-prev {
            color: #333;
            background: rgba(255, 255, 255, 0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .main-image-container .swiper-button-next:after,
        .main-image-container .swiper-button-prev:after {
            font-size: 16px;
            font-weight: bold;
        }

        .main-image-container .swiper-button-next:hover,
        .main-image-container .swiper-button-prev:hover {
            background: #d4a574;
            color: white;
        }

        /* Image counter */
        .image-counter {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 10;
        }

        @media (max-width: 767px) {
            .main-image-container .swiper-slide .item {
                min-height: 300px;
            }

            .main-image-container .swiper-slide .item img {
                max-height: 350px;
            }

            .tf-product-media-thumbs.thumbs-horizontal .item img {
                height: 60px;
            }
        }

        /* Related Products Section - Enhanced */
        .related-products-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            padding: 70px 0;
        }

        .related-products-section .flat-title h3 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 8px;
        }

        .related-products-section .flat-title p {
            color: #666;
            font-size: 1rem;
        }

        .related-products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            padding: 10px 0;
        }

        .related-product-item .card-product {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            height: 100%;
        }

        .related-product-item .card-product:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .related-product-item .card-product_wrapper {
            position: relative;
            overflow: hidden;
        }

        .related-product-item .product-img {
            display: block;
            position: relative;
            padding-top: 100%;
            background: #f8f9fa;
        }

        .related-product-item .product-img img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .related-product-item .card-product:hover .product-img img {
            transform: scale(1.08);
        }

        .related-product-item .related-sale-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #e74c3c;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .related-product-item .related-product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(251, 191, 36, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.3px;
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            width: auto;
            max-width: fit-content;
            white-space: nowrap;
        }

        .related-product-item .related-product-badge i {
            font-size: 9px;
            animation: starPulse 2s ease-in-out infinite;
        }

        @keyframes starPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
            }
        }

        .related-product-item .product-actions {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            display: flex;
            gap: 8px;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 10;
            pointer-events: none;
        }

        .related-product-item .card-product:hover .product-actions {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
            pointer-events: auto;
        }

        .related-product-item .action-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: white;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            pointer-events: auto;
            position: relative;
            z-index: 11;
        }

        .related-product-item .action-btn:hover {
            background: #d4a574;
            color: white;
            transform: scale(1.1);
        }

        .related-product-item .action-btn.active {
            background: #e74c3c;
            color: white;
        }

        .related-product-item .card-product_info {
            padding: 20px;
        }

        .related-product-item .name-product {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            text-decoration: none;
            margin-bottom: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: color 0.3s ease;
        }

        .related-product-item .name-product:hover {
            color: #d4a574;
        }

        .related-product-item .product-desc {
            font-size: 0.85rem;
            color: #777;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .related-product-item .price-row {
            margin-bottom: 15px;
        }

        .related-product-item .price {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .related-product-item .price-new {
            font-size: 1.25rem;
            font-weight: 700;
            color: #d4a574;
        }

        .related-product-item .price-old {
            font-size: 0.95rem;
            color: #999;
            text-decoration: line-through;
        }

        .related-product-item .btn-add-cart-full {
            width: 100%;
            padding: 12px 20px;
            background: transparent;
            border: 2px solid #d4a574;
            color: #d4a574;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .related-product-item .btn-add-cart-full:hover {
            background: #d4a574;
            color: white;
        }

        .related-product-item .btn-add-cart-full:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        @media (max-width: 1199px) {
            .related-products-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 991px) {
            .related-products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 767px) {
            .related-products-section {
                padding: 50px 0;
            }

            .related-products-section .flat-title h3 {
                font-size: 1.4rem;
            }

            .related-products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .related-product-item .card-product_info {
                padding: 15px;
            }

            .related-product-item .name-product {
                font-size: 0.9rem;
            }

            .related-product-item .price-new {
                font-size: 1.1rem;
            }

            .related-product-item .btn-add-cart-full {
                padding: 10px 15px;
                font-size: 0.85rem;
            }

            .related-product-item .product-actions {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        @media (max-width: 480px) {
            .related-products-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>

</body>
</html>
