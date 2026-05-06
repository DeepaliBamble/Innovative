<?php
require_once __DIR__ . '/includes/init.php';

// Fetch published blogs for homepage - limit to 6 for the slider
try {
    $stmt = $pdo->query("SELECT id, title, slug, excerpt, featured_image, published_at, category
                         FROM blogs
                         WHERE is_published = 1
                         ORDER BY published_at DESC
                         LIMIT 6");
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $blogs = [];
    error_log('Error fetching blogs: ' . $e->getMessage());
}

// Fetch latest products for New Product section - limit to 8 products
try {
    $stmt = $pdo->query("SELECT id, name, slug, price, sale_price, image_path, stock_quantity
                         FROM products
                         WHERE is_active = 1
                         ORDER BY created_at DESC
                         LIMIT 8");
    $latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latestProducts = [];
    error_log('Error fetching latest products: ' . $e->getMessage());
}

// Fetch categories for Shop By Category section
try {
    $stmt = $pdo->query("SELECT id, name, slug, image_path, description
                         FROM categories
                         WHERE is_active = 1 AND parent_id IS NULL
                         ORDER BY display_order ASC, name ASC
                         LIMIT 7");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    error_log('Error fetching categories: ' . $e->getMessage());
}

// Fetch featured products for Shop This Look section - limit to 4 products
try {
    $stmt = $pdo->query("
        SELECT
            p.id,
            p.name,
            p.slug,
            p.price,
            p.sale_price,
            p.image_path,
            p.stock_quantity,
            c.name as category_name,
            (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, display_order ASC LIMIT 1 OFFSET 1) as hover_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND p.is_featured = 1
        ORDER BY RAND()
        LIMIT 4
    ");
    $lookbookProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lookbookProducts = [];
    error_log('Error fetching lookbook products: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
    <meta charset="utf-8">
    <title>Innovative Homesi - Premium Sofa & Furniture Manufacturer | Custom Design & Craftsmanship</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description"
        content="Discover Innovative Homesi - where comfort meets craftsmanship. Premium sofas and furniture designed to last, built with sustainable materials and timeless design. Custom pieces tailored to your vision. Your home, your story, our craft.">
    <meta name="keywords"
        content="sofa manufacturer, custom furniture, premium sofas, furniture design, sustainable furniture, luxury sofas, custom sofa design, furniture craftsmanship, home furniture, innovative homesi">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://innovativehomesi.com/">

    <!-- Open Graph Meta Tags for Social Sharing -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Innovative Homesi - Premium Sofa & Furniture Manufacturer">
    <meta property="og:description"
        content="Transform your space with handcrafted sofas and furniture. Blending timeless craftsmanship with modern design. Built to last, designed to inspire.">
    <meta property="og:url" content="https://innovativehomesi.com/">
    <meta property="og:site_name" content="Innovative Homesi">
    <meta property="og:image" content="https://innovativehomesi.com/images/logo/logo.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Innovative Homesi - Premium Sofa & Furniture Manufacturer">
    <meta name="twitter:description"
        content="Transform your space with handcrafted sofas and furniture. Blending timeless craftsmanship with modern design.">
    <meta name="twitter:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- Business Information -->
    <meta name="geo.region" content="IN">
    <meta name="geo.placename" content="India">

    <!-- Structured Data - JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Innovative Homesi",
      "url": "https://innovativehomesi.com",
      "logo": "https://innovativehomesi.com/images/logo/logo.png",
      "description": "Premium sofa and furniture manufacturer specializing in custom designs that blend timeless craftsmanship with modern aesthetics. Creating furniture built to last and designed to inspire.",
            "sameAs": [
                "https://www.facebook.com/innovativehomesi/",
                "https://www.instagram.com/innovative_homesi/",
                "https://in.pinterest.com/innovativehomes3/",
                "https://www.youtube.com/@innovativehomesi",
                "https://www.linkedin.com/company/innovativehomesi"
            ],
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "Customer Service",
        "availableLanguage": ["English", "Hindi"]
      }
    }
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "Innovative Homesi",
      "image": "https://innovativehomesi.com/images/logo/logo.png",
      "description": "Premium furniture manufacturer creating handcrafted sofas and custom furniture pieces. From cozy sofas to elegant furniture that transforms spaces.",
      "url": "https://innovativehomesi.com",
      "telephone": "+91-XXXXXXXXXX",
      "priceRange": "₹₹-₹₹₹",
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "IN"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.8",
        "reviewCount": "150"
      }
    }
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Innovative Homesi",
      "url": "https://innovativehomesi.com",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://innovativehomesi.com/search?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>

    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="fonts/fonts.css">
    <link rel="stylesheet" href="icon/icomoon/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        <!-- Banner Slider -->
        <div class="tf-slideshow type-abs tf-btn-swiper-main hover-sw-nav">
            <div dir="ltr" class="swiper tf-swiper sw-slide-show slider_effect_fade" data-auto="true" data-loop="true"
                data-effect="fade" data-delay="3000">
                <div class="swiper-wrapper">
                    <!-- item 1 -->
                    <div class="swiper-slide">
                        <div class="slider-wrap style-2">
                            <div class="sld_image">
                                <img src="images/slider/slider-19.jpg" data-src="images/slider/slider-19.jpg" alt=""
                                    class="lazyload ani-zoom">
                            </div>
                            <div class="sld_content text-center">
                                <div class="container">
                                    <div class="content-sld_wrap">
                                        <p class="sub-title_sld h6 text-primary fade-item fade-item-1">
                                            UP TO 70% OFF
                                        </p>
                                        <h1 class="title_sld text-display fade-item fade-item-2"
                                            style="color: #9e6747;">
                                            Save Big on Every Order!
                                        </h1>
                                        <p class="sub-text_sld h5 text-black fade-item fade-item-3">
                                            Enjoy Up to 70% OFF on top products, plus get an <br
                                                class="d-none d-sm-block">
                                            Extra 10% Discount for New Users. <br class="d-none d-sm-block">
                                            FREE Delivery on All Orders.
                                        </p>
                                        <div class="fade-item fade-item-4">
                                            <a href="shop.php" class="tf-btn animate-btn fw-normal"
                                                style="background-color: #9e6747;">
                                                Shop now
                                                <i class="icon icon-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- item 2 -->
                    <div class="swiper-slide">
                        <div class="slider-wrap style-2">
                            <div class="sld_image">
                                <img src="images/slider/slider-20.jpg" data-src="images/slider/slider-20.jpg" alt=""
                                    class="lazyload ani-zoom">
                            </div>
                            <div class="sld_content text-center">
                                <div class="container">
                                    <div class="content-sld_wrap">
                                        <p class="sub-title_sld h6 text-primary fade-item fade-item-1">
                                            CUSTOM-MADE FURNITURE
                                        </p>
                                        <h1 class="title_sld text-display fade-item fade-item-2"
                                            style="color: #9e6747;">
                                            Create Furniture That <br> Fits Your Style
                                        </h1>
                                        <p class="sub-text_sld h5 text-black fade-item fade-item-3">
                                            Customize your sofa and furniture <br>exactly the way you want — from design
                                            and br
                                            size to fabric and finish. <br class="d-none d-sm-block">
                                            Crafted for comfort, made to match your space perfectly.
                                        </p>
                                        <div class="fade-item fade-item-4">
                                            <a href="shop.php?category=sofa" class="tf-btn animate-btn fw-normal"
                                                style="background-color: #9e6747;">
                                                Shop now
                                                <i class="icon icon-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- item 3 -->
                    <div class="swiper-slide">
                        <div class="slider-wrap style-2">
                            <div class="sld_image">
                                <img src="images/slider/slider-21.jpg" data-src="images/slider/slider-21.jpg" alt=""
                                    class="lazyload ani-zoom">
                            </div>
                            <div class="sld_content text-center">
                                <div class="container">
                                    <div class="content-sld_wrap">
                                        <p class="sub-title_sld h6 text-primary fade-item fade-item-1">
                                            HOME DÉCOR COLLECTION
                                        </p>
                                        <h1 class="title_sld text-display fade-item fade-item-2"
                                            style="color: #9e6747;">
                                            Elevate Your Space with Beautiful <br> Home Décor
                                        </h1>
                                        <p class="sub-text_sld h5 text-black fade-item fade-item-3">
                                            Explore stylish rugs, cozy cushions, elegant vases, and more. <br
                                                class="d-none d-sm-block">
                                            Add warmth, texture, and personality <br> to every corner of your home.
                                        </p>
                                        <div class="fade-item fade-item-4">
                                            <a href="shop.php?category=decor-and-vases"
                                                class="tf-btn animate-btn fw-normal" style="background-color: #9e6747;">
                                                Shop now
                                                <i class="icon icon-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sw-dot-default tf-sw-pagination"></div>
            </div>
            <div class="tf-sw-nav nav-prev-swiper">
                <i class="icon icon-caret-left"></i>
            </div>
            <div class="tf-sw-nav nav-next-swiper">
                <i class="icon icon-caret-right"></i>
            </div>
        </div>
        <!-- /Banner Slider -->

        <!-- Category -->
        <section class="flat-spacing">
            <div class="container">
                <div class="sect-title text-center wow fadeInUp">
                    <h1 class="title mb-8" style="color: #9e6747;">Shop by Category</h1>
                    <p class="s-subtitle h6" style="text-transform: none;">Explore furniture by category to find the
                        perfect pieces that match your style, space, and comfort needs.</p>
                </div>
                <div dir="ltr" class="swiper tf-swiper wow fadeInUp" data-preview="6" data-tablet="4" data-mobile-sm="3"
                    data-mobile="2" data-space-lg="48" data-space-md="32" data-space="12" data-pagination="2"
                    data-pagination-sm="3" data-pagination-md="4" data-pagination-lg="6">
                    <div class="swiper-wrapper">
                        <?php
                        // Predefined category images mapping
                        $categoryImages = [
                            'cate-18.jpg',
                            'cate-19.jpg',
                            'cate-20.jpg',
                            'cate-21.jpg',
                            'cate-22.jpg',
                            'cate-23.jpg',
                            'cate-24.jpg'
                        ];

                        if (!empty($categories)):
                            $imageIndex = 0;
                            foreach ($categories as $category):
                                $categoryId = htmlspecialchars($category['id']);
                                $categoryName = htmlspecialchars($category['name']);
                                $categorySlug = htmlspecialchars($category['slug']);
                                // Use predefined images in order
                                $categoryImage = 'images/category/' . $categoryImages[$imageIndex % count($categoryImages)];
                                $categoryUrl = 'shop.php?category=' . $categorySlug;
                                $imageIndex++;
                                ?>
                                <div class="swiper-slide">
                                    <a href="<?php echo $categoryUrl; ?>" class="widget-collection style-circle hover-img">
                                        <div class="collection_image img-style">
                                            <img class="lazyload" src="<?php echo $categoryImage; ?>"
                                                data-src="<?php echo $categoryImage; ?>" alt="<?php echo $categoryName; ?>">
                                        </div>
                                        <p class="collection_name h5 link">
                                            <?php echo $categoryName; ?>
                                        </p>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback when no categories available -->
                            <div class="swiper-slide">
                                <div class="widget-collection style-circle">
                                    <p class="h6 text-center p-4">No categories available at the moment.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="sw-dot-default tf-sw-pagination"></div>
                </div>
            </div>
        </section>
        <!-- /Category -->
        <!-- New Product -->
        <section class="flat-spacing pt-0 tf-pag-swiper new-products-section">
            <div class="container">
                <div class="sect-title text-center wow fadeInUp">
                    <h1 class="s-title mb-8" style="color: #9e6747;">New Products</h1>
                    <p class="s-subtitle h6" style="text-transform: none;">Discover our latest furniture designs,
                        crafted to elevate comfort and style.</p>
                </div>
                <div class="tf-btn-swiper-main pst-3">
                    <div dir="ltr" class="swiper tf-swiper wow fadeInUp" data-preview="4" data-tablet="3"
                        data-mobile-sm="2" data-mobile="2" data-space-lg="48" data-space-md="30" data-space="12"
                        data-pagination="2" data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4">
                        <div class="swiper-wrapper">
                            <?php if (!empty($latestProducts)): ?>
                                <?php foreach ($latestProducts as $product):
                                    // Get product details
                                    $productId = htmlspecialchars($product['id']);
                                    $productName = htmlspecialchars($product['name']);
                                    $productSlug = htmlspecialchars($product['slug']);
                                    $productPrice = number_format($product['price'], 2);
                                    $productSalePrice = $product['sale_price'] ? number_format($product['sale_price'], 2) : null;
                                    $productImage = !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : 'images/products/default.jpg';
                                    $stockStatus = $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock';
                                    $productUrl = 'product-detail.php?slug=' . $productSlug;
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="card-product">
                                            <div class="card-product_wrapper d-flex">
                                                <a href="<?php echo $productUrl; ?>" class="product-img">
                                                    <img class="lazyload img-product" src="<?php echo $productImage; ?>"
                                                        data-src="<?php echo $productImage; ?>"
                                                        alt="<?php echo $productName; ?>">
                                                    <img class="lazyload img-hover" src="<?php echo $productImage; ?>"
                                                        data-src="<?php echo $productImage; ?>"
                                                        alt="<?php echo $productName; ?>">
                                                </a>
                                                <ul class="product-action_list">
                                                    <li>
                                                        <a href="javascript:void(0);"
                                                            class="hover-tooltip tooltip-left box-icon add-to-cart-btn"
                                                            data-product-id="<?php echo $productId; ?>">
                                                            <span class="icon icon-shopping-cart-simple"></span>
                                                            <span class="tooltip">Add to cart</span>
                                                        </a>
                                                    </li>
                                                    <li class="wishlist">
                                                        <a href="javascript:void(0);"
                                                            class="hover-tooltip tooltip-left box-icon add-to-wishlist-btn"
                                                            data-product-id="<?php echo $productId; ?>">
                                                            <span class="icon icon-heart"></span>
                                                            <span class="tooltip">Add to Wishlist</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);"
                                                            class="hover-tooltip tooltip-left box-icon quick-view-btn"
                                                            data-product-id="<?php echo $productId; ?>"
                                                            data-product-slug="<?php echo $productSlug; ?>">
                                                            <span class="icon icon-view"></span>
                                                            <span class="tooltip">Quick view</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="card-product_info">
                                                <a href="<?php echo $productUrl; ?>" class="name-product link">
                                                    <?php echo $productName; ?>
                                                </a>
                                                <div class="price-wrap mb-0">
                                                    <?php if ($productSalePrice): ?>
                                                        <span class="price-old">₹<?php echo $productPrice; ?></span>
                                                        <span class="price-new">₹<?php echo $productSalePrice; ?></span>
                                                    <?php else: ?>
                                                        <span class="price-new">₹<?php echo $productPrice; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback message when no products are available -->
                                <div class="swiper-slide">
                                    <div class="card-product">
                                        <div class="card-product_info text-center p-4">
                                            <p class="h5">No products available at the moment. Please check back later!</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="tf-sw-nav style-2 nav-prev-swiper d-xl-flex">
                        <i class="icon icon-caret-left"></i>
                    </div>
                    <div class="tf-sw-nav style-2 nav-next-swiper d-xl-flex">
                        <i class="icon icon-caret-right"></i>
                    </div>
                </div>
                <div class="sw-dot-default tf-sw-pagination d-xl-none"></div>
            </div>
        </section>
        <!-- /New Product -->
        <!-- Box Image -->
        <section class="themesFlat">
            <div class="container-full-2">
                <div class="tf-grid-layout lg-col-2 gap-0">
                    <div class="box-image_V04 hover-img">
                        <a href="shop.php?category=extendable-sofa" class="box-image_image img-style">
                            <img src="images/section/box-image-6.jpg" data-src="images/section/box-image-6.jpg" alt=""
                                class="lazyload">
                        </a>
                        <div class="box-image_content align-items-center text-center">
                            <h2 class="title type-semibold">
                                <a href="shop.php?category=extendable-sofa" class="link">
                                    Modern Furniture Collection
                                </a>
                            </h2>
                            <p class="sub-title h6">Elegant designs crafted to enhance your living space.</p>
                            <a href="shop.php?category=extendable-sofa" class="tf-btn animate-btn"
                                style="background-color: #9e6747;">
                                Shop now
                                <i class="icon icon-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="box-image_V04 hover-img">
                        <a href="shop.php?category=tables-storage" class="box-image_image img-style">
                            <img src="images/section/box-image-7.jpg" data-src="images/section/box-image-7.jpg" alt=""
                                class="lazyload">
                        </a>
                        <div class="box-image_content align-items-center text-center">
                            <h2 class="title type-semibold">
                                <a href="shop.php?category=tables-storage" class="link">
                                    Functional Furniture Solutions
                                </a>
                            </h2>
                            <p class="sub-title h6">Built for comfort, storage, and style.</p>
                            <a href="shop.php?category=tables-storage" class="tf-btn animate-btn"
                                style="background-color: #9e6747;">
                                Shop now
                                <i class="icon icon-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Box Image -->

        <!-- 
        <section class="flat-spacing pt-0 tf-lookbook-simple">
            <div class="container">
                <div class="text-center mb-5 wow fadeInUp">
                    <h1 class="s-title mb-3" style="color: #9e6747;">Shop By Category</h1>
                    <p class="s-subtitle h6" style="text-transform: none;">Explore our premium furniture collections</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="banner-lookbook-simple position-relative">
                            <img class="lazyload img-banner w-100"
                                 src="images/section/about-us.jpg"
                                 data-src="images/section/about-us.jpg"
                                 alt="Shop By Category"
                                 style="border-radius: 16px; box-shadow: 0 10px 40px rgba(158, 103, 71, 0.15);">

                          
                            <div class="lookbook-item position3">
                                <a href="shop.php?category=tables-storage" class="tf-pin-btn category-pin" title="Shop Tables & Storage">
                                    <span></span>
                                </a>
                                <div class="pin-label">
                                    <span class="category-name">Tables</span>
                                </div>
                            </div>

                       
                            <div class="lookbook-item position4">
                                <a href="shop.php?category=sofa" class="tf-pin-btn category-pin" title="Shop Sofas">
                                    <span></span>
                                </a>
                                <div class="pin-label">
                                    <span class="category-name">Sofas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
         -->
        <!-- Testimonial -->
        <section class="flat-spacing pt-0" style="background: linear-gradient(135deg, #faf8f6 0%, #f5f0eb 100%);">
            <div class="container">
                <div class="text-center mb-5 wow fadeInUp">
                    <h1 class="sect-title"
                        style="color: #9e6747; font-size: 2.5rem; margin-bottom: 1rem; padding-top: 2rem;">What our
                        customers say</h1>
                    <p style="color: #666; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Discover why thousands
                        of customers trust us for their home furnishing needs</p>
                </div>

                <div class="testimonial-container" style="max-width: 1200px; margin: 0 auto; position: relative;">
                    <div dir="ltr" class="swiper testimonial-swiper wow fadeInUp">
                        <div class="swiper-wrapper">
                            <!-- Testimonial 1 -->
                            <div class="swiper-slide">
                                <div class="testimonial-card"
                                    style="background: white; border-radius: 20px; padding: 3rem 2.5rem; box-shadow: 0 10px 40px rgba(158, 103, 71, 0.1); position: relative; margin: 20px;">
                                    <div class="quote-icon"
                                        style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: #9e6747; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="icon icon-block-quote" style="color: white; font-size: 24px;"></i>
                                    </div>

                                    <div class="rating-stars" style="margin-bottom: 1.5rem; margin-top: 1rem;">
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                    </div>

                                    <h4
                                        style="color: #9e6747; font-size: 1.4rem; margin-bottom: 1rem; font-weight: 600;">
                                        A sofa that reflects your personality</h4>

                                    <p style="color: #555; font-size: 1.05rem; line-height: 1.8; margin-bottom: 2rem;">
                                        A sofa is more than just a piece of furniture—it's the heart of your home, where
                                        memories are made, conversations flow, and comfort meets style. Invest in a sofa
                                        that reflects your personality and stands the test of time.
                                    </p>

                                    <div class="testimonial-author"
                                        style="display: flex; align-items: center; border-top: 2px solid #f0e6dd; padding-top: 1.5rem;">
                                        <div class="author-avatar"
                                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #9e6747, #c49069); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.3rem; font-weight: 600; margin-right: 1rem;">
                                            AS
                                        </div>
                                        <div>
                                            <p
                                                style="color: #333; font-size: 1.1rem; font-weight: 600; margin: 0; display: flex; align-items: center;">
                                                Ananya Sharma
                                                <i class="icon-check-circle"
                                                    style="color: #4caf50; margin-left: 8px; font-size: 18px;"></i>
                                            </p>
                                            <p style="color: #999; font-size: 0.9rem; margin: 0;">Verified Customer</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Testimonial 2 -->
                            <div class="swiper-slide">
                                <div class="testimonial-card"
                                    style="background: white; border-radius: 20px; padding: 3rem 2.5rem; box-shadow: 0 10px 40px rgba(158, 103, 71, 0.1); position: relative; margin: 20px;">
                                    <div class="quote-icon"
                                        style="position: absolute; top: -15px; left: 30px; width: 50px; height: 50px; background: #9e6747; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <i class="icon icon-block-quote" style="color: white; font-size: 24px;"></i>
                                    </div>

                                    <div class="rating-stars" style="margin-bottom: 1.5rem; margin-top: 1rem;">
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                        <i class="icon-star" style="color: #ffc107; font-size: 20px;"></i>
                                    </div>

                                    <h4
                                        style="color: #9e6747; font-size: 1.4rem; margin-bottom: 1rem; font-weight: 600;">
                                        Exceptional quality and craftsmanship</h4>

                                    <p style="color: #555; font-size: 1.05rem; line-height: 1.8; margin-bottom: 2rem;">
                                        The quality of the sofa we ordered from Innovative Homesi is exceptional. The
                                        craftsmanship and attention to detail are evident in every stitch. It's not just
                                        furniture—it's a work of art that has transformed our living room.
                                    </p>

                                    <div class="testimonial-author"
                                        style="display: flex; align-items: center; border-top: 2px solid #f0e6dd; padding-top: 1.5rem;">
                                        <div class="author-avatar"
                                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #9e6747, #c49069); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.3rem; font-weight: 600; margin-right: 1rem;">
                                            RM
                                        </div>
                                        <div>
                                            <p
                                                style="color: #333; font-size: 1.1rem; font-weight: 600; margin: 0; display: flex; align-items: center;">
                                                Rohan Mehta
                                                <i class="icon-check-circle"
                                                    style="color: #4caf50; margin-left: 8px; font-size: 18px;"></i>
                                            </p>
                                            <p style="color: #999; font-size: 0.9rem; margin: 0;">Verified Customer</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="testimonial-navigation"
                        style="display: flex; justify-content: center; gap: 15px; margin-top: 3rem;">
                        <button class="testimonial-prev"
                            style="width: 50px; height: 50px; border-radius: 50%; background: white; border: 2px solid #9e6747; color: #9e6747; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(158, 103, 71, 0.15);">
                            <i class="icon icon-caret-left" style="font-size: 20px;"></i>
                        </button>
                        <button class="testimonial-next"
                            style="width: 50px; height: 50px; border-radius: 50%; background: #9e6747; border: 2px solid #9e6747; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(158, 103, 71, 0.25);">
                            <i class="icon icon-caret-right" style="font-size: 20px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <style>
            .testimonial-prev:hover {
                background: #9e6747;
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(158, 103, 71, 0.3);
            }

            .testimonial-next:hover {
                background: #7d5138;
                border-color: #7d5138;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(158, 103, 71, 0.4);
            }

            .testimonial-card {
                transition: all 0.3s ease;
            }

            .testimonial-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 50px rgba(158, 103, 71, 0.15);
            }

            @media (max-width: 768px) {
                .testimonial-card {
                    padding: 2rem 1.5rem !important;
                    margin: 10px !important;
                }

                .sect-title {
                    font-size: 2rem !important;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const testimonialSwiper = new Swiper('.testimonial-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 30,
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    breakpoints: {
                        768: {
                            slidesPerView: 2,
                            spaceBetween: 30,
                        },
                        1024: {
                            slidesPerView: 2,
                            spaceBetween: 40,
                        }
                    }
                });

                document.querySelector('.testimonial-prev').addEventListener('click', function () {
                    testimonialSwiper.slidePrev();
                });

                document.querySelector('.testimonial-next').addEventListener('click', function () {
                    testimonialSwiper.slideNext();
                });
            });
        </script>
        <!-- /Testimonial -->
        <!-- Box Icon -->
        <div class="themesFlat">
            <div class="container">
                <div class="flat-spacing pt-0">
                    <span class="br-line d-flex"></span>
                </div>
                <div dir="ltr" class="swiper tf-swiper" data-preview="4" data-tablet="3" data-mobile-sm="2"
                    data-mobile="1" data-space-lg="97" data-space-md="33" data-space="13" data-pagination="1"
                    data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4">
                    <div class="swiper-wrapper">
                        <!-- item 1 -->
                        <div class="swiper-slide">
                            <div class="box-icon_V01 wow fadeInLeft">
                                <span class="icon">
                                    <i class="icon-package"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal">10 days return</h4>
                                    <p class="text">10 day money back guarantee</p>
                                </div>
                            </div>
                        </div>
                        <!-- item 2 -->
                        <div class="swiper-slide">

                            <div class="box-icon_V01 wow fadeInLeft" data-wow-delay="0.1s">
                                <span class="icon">
                                    <i class="icon-calender"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal">5 year warranty</h4>
                                    <p class="text">Manufacturer's defect</p>
                                </div>
                            </div>
                        </div>
                        <!-- item 3 -->
                        <div class="swiper-slide">

                            <div class="box-icon_V01 wow fadeInLeft" data-wow-delay="0.3s">
                                <span class="icon">
                                    <i class="icon-boat"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal">Free shipping</h4>
                                    <p class="text">Free Shipping on all orders</p>
                                </div>
                            </div>
                        </div>
                        <!-- item 4 -->
                        <div class="swiper-slide">
                            <div class="box-icon_V01 wow fadeInLeft" data-wow-delay="0.4s">
                                <span class="icon">
                                    <i class="icon-headset"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal">Online support</h4>
                                    <p class="text">Mon-Sat (7am -10pm)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sw-dot-default tf-sw-pagination"></div>
                </div>
            </div>
        </div>
        <!-- /Box Icon -->
        <!-- Blog -->
        <section class="flat-spacing">
            <div class="container">
                <div class="sect-title text-center wow fadeInUp">
                    <h1 class="s-title mb-8" style="color: #9e6747;">Our Blog</h1>
                    <p class="s-subtitle h6" style="text-transform: none;">Inspiration and insights for creating spaces
                        that truly feel like home</p>
                </div>
                <?php if (!empty($blogs)): ?>
                    <div dir="ltr" class="swiper tf-swiper" data-preview="3" data-tablet="3" data-mobile-sm="2"
                        data-mobile="1" data-space-lg="48" data-space-md="32" data-space="12" data-pagination="1"
                        data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="3">
                        <div class="swiper-wrapper">
                            <?php
                            $delay = 0.1;
                            foreach ($blogs as $blog):
                                // Default image if featured_image is empty
                                $blogImage = !empty($blog['featured_image']) ? htmlspecialchars($blog['featured_image']) : 'images/blog/default.jpg';
                                // Format date
                                $publishedDate = date('F j, Y', strtotime($blog['published_at']));

                                // Truncate excerpt if too long
                                $excerpt = !empty($blog['excerpt']) ? htmlspecialchars($blog['excerpt']) : 'Read more about this fascinating topic...';
                                if (strlen($excerpt) > 120) {
                                    $excerpt = substr($excerpt, 0, 120) . '...';
                                }
                                ?>
                                <div class="swiper-slide">
                                    <div class="article-blog type-space-2 hover-img4 wow fadeInLeft"
                                        data-wow-delay="<?php echo $delay; ?>s">
                                        <a href="blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>"
                                            class="entry_image img-style4">
                                            <img src="<?php echo $blogImage; ?>" data-src="<?php echo $blogImage; ?>"
                                                alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                                class="lazyload aspect-ratio-1">
                                        </a>
                                        <div class="entry_tag">
                                            <a href="blogs.php?category=<?php echo urlencode($blog['category']); ?>"
                                                class="name-tag h6 link"><?php echo $publishedDate; ?></a>
                                        </div>

                                        <div class="blog-content">
                                            <a href="blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>"
                                                class="entry_name link h4">
                                                <?php echo htmlspecialchars($blog['title']); ?>
                                            </a>
                                            <p class="text h6">
                                                <?php echo $excerpt; ?>
                                            </p>
                                            <a href="blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>"
                                                class="tf-btn-line">
                                                Read more
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $delay += 0.1;
                            endforeach;
                            ?>
                        </div>
                        <div class="sw-dot-default tf-sw-pagination"></div>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 60px 20px;">
                        <p class="h6 text-muted">No blog posts available at the moment. Check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <!-- /Blog -->
        <!-- Gallery -->
        <section class="themesFlat">
            <div class="container">
                <div class="sect-title text-center wow fadeInUp">
                    <h1 class="title mb-8" style="color: #9e6747;">Our Instagram Gallery</h1>
                    <p class="s-subtitle h6" style="text-transform: none;">Discover our latest designs & inspiration</p>
                </div>
                <div dir="ltr" class="swiper tf-swiper wow fadeInUp" data-preview="5" data-tablet="4" data-mobile-sm="3"
                    data-mobile="2" data-space="0" data-pagination="2" data-pagination-sm="3" data-pagination-md="4"
                    data-pagination-lg="5">
                    <div class="swiper-wrapper">
                        <!-- item 1 -->
                        <div class="swiper-slide">
                            <div class="gallery-item hover-img hover-overlay">
                                <div class="image img-style">
                                    <img class="lazyload" src="images/gallery/gallery-19.jpg"
                                        data-src="images/gallery/gallery-19.jpg" alt="Instagram Reel">
                                </div>
                                <a href="https://www.instagram.com/reel/C8CF0WIo7AE/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="
                                    target="_blank" rel="noopener noreferrer" class="box-icon hover-tooltip">
                                    <span class="icon icon-instagram-logo" style="color: #9e6747;"></span>
                                    <span class="tooltip">Watch on Instagram</span>
                                </a>
                            </div>
                        </div>
                        <!-- item 2 -->
                        <div class="swiper-slide">
                            <div class="gallery-item hover-img hover-overlay">
                                <div class="image img-style">
                                    <img class="lazyload" src="images/gallery/gallery-20.jpg"
                                        data-src="images/gallery/gallery-20.jpg" alt="Instagram Reel">
                                </div>
                                <a href="https://www.instagram.com/reel/DQ4FnPDAqeb/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="
                                    target="_blank" rel="noopener noreferrer" class="box-icon hover-tooltip">
                                    <span class="icon icon-instagram-logo" style="color: #9e6747;"></span>
                                    <span class="tooltip">Watch on Instagram</span>
                                </a>
                            </div>
                        </div>
                        <!-- item 3 -->
                        <div class="swiper-slide">
                            <div class="gallery-item hover-img hover-overlay">
                                <div class="image img-style">
                                    <img class="lazyload" src="images/gallery/gallery-21.jpg"
                                        data-src="images/gallery/gallery-21.jpg" alt="Instagram Reel">
                                </div>
                                <a href="https://www.instagram.com/reel/DPx7sDHAkQg/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="
                                    target="_blank" rel="noopener noreferrer" class="box-icon hover-tooltip">
                                    <span class="icon icon-instagram-logo" style="color: #9e6747;"></span>
                                    <span class="tooltip">Watch on Instagram</span>
                                </a>
                            </div>
                        </div>
                        <!-- item 4 -->
                        <div class="swiper-slide">
                            <div class="gallery-item hover-img hover-overlay">
                                <div class="image img-style">
                                    <img class="lazyload" src="images/gallery/gallery-22.jpg"
                                        data-src="images/gallery/gallery-22.jpg" alt="Instagram Reel">
                                </div>
                                <a href="https://www.instagram.com/reel/DOX4jfGE5hQ/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="
                                    target="_blank" rel="noopener noreferrer" class="box-icon hover-tooltip">
                                    <span class="icon icon-instagram-logo" style="color: #9e6747;"></span>
                                    <span class="tooltip">Watch on Instagram</span>
                                </a>
                            </div>
                        </div>
                        <!-- item 5 -->
                        <div class="swiper-slide">
                            <div class="gallery-item hover-img hover-overlay">
                                <div class="image img-style">
                                    <img class="lazyload" src="images/gallery/gallery-23.jpg"
                                        data-src="images/gallery/gallery-23.jpg" alt="Instagram Reel">
                                </div>
                                <a href="https://www.instagram.com/reel/DMfRhXHyQNn/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="
                                    target="_blank" rel="noopener noreferrer" class="box-icon hover-tooltip">
                                    <span class="icon icon-instagram-logo" style="color: #9e6747;"></span>
                                    <span class="tooltip">Watch on Instagram</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="sw-dot-default tf-sw-pagination"></div>
                </div>
            </div>
        </section>
        <!-- /Gallery -->
        <?php include 'includes/footer.php'; ?>

        <!-- Toolbar -->
        <div class="tf-toolbar-bottom">
            <div class="toolbar-item">
                <a href="shop.php">
                    <span class="toolbar-icon">
                        <i class="icon icon-storefront"></i>
                    </span>
                    <span class="toolbar-label">Shop</span>
                </a>
            </div>
            <div class="toolbar-item">
                <a href="#search" data-bs-toggle="modal">
                    <span class="toolbar-icon">
                        <i class="icon icon-magnifying-glass"></i>
                    </span>
                    <span class="toolbar-label">Search</span>
                </a>
            </div>
            <div class="toolbar-item">
                <a href="account-page.php">
                    <span class="toolbar-icon">
                        <i class="icon icon-user"></i>
                    </span>
                    <span class="toolbar-label">Account</span>
                </a>
            </div>
            <div class="toolbar-item">
                <a href="wishlist.php">
                    <span class="toolbar-icon">
                        <i class="icon icon-heart"></i>
                        <span class="toolbar-count">7</span>
                    </span>
                    <span class="toolbar-label">Wishlist</span>
                </a>
            </div>
            <div class="toolbar-item">
                <a href="view-cart.php">
                    <span class="toolbar-icon">
                        <i class="icon icon-shopping-cart-simple"></i>
                        <span class="toolbar-count">24</span>
                    </span>
                    <span class="toolbar-label">Cart</span>
                </a>
            </div>
        </div>
        <!-- /Toolbar -->

        <!-- Size Guide -->
        <div class="modal modalCentered fade modal-size-guide" id="size-guide">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content widget-tabs style-2">
                    <div class="header">
                        <ul class="widget-menu-tab">
                            <li class="item-title active">
                                <span class="inner h3">Size </span>
                            </li>
                            <li class="item-title">
                                <span class="inner h3">Size Guide</span>
                            </li>
                        </ul>
                        <span class="icon-close icon-close-popup" data-bs-dismiss="modal"></span>
                    </div>
                    <div class="wrap">
                        <div class="widget-content-tab">
                            <div class="widget-content-inner active">
                                <div class="tab-size">
                                    <div>
                                        <div class="widget-size mb-24">
                                            <div class="box-title-size">
                                                <div class="title-size h6 text-black">Height</div>
                                                <div class="number-size text-small">
                                                    <span class="max-size">100</span>
                                                    <span class="">Cm</span>
                                                </div>
                                            </div>
                                            <div class="range-input">
                                                <div class="tow-bar-block">
                                                    <div class="progress-size" style="width: 50%;"></div>
                                                </div>
                                                <input type="range" min="0" max="200" value="100" class="range-max">
                                            </div>
                                        </div>
                                        <div class="widget-size">
                                            <div class="box-title-size">
                                                <div class="title-size h6 text-black">Weight</div>
                                                <div class="number-size text-small">
                                                    <span class="max-size">50</span>
                                                    <span class="">Kg</span>
                                                </div>
                                            </div>
                                            <div class="range-input">
                                                <div class="tow-bar-block">
                                                    <div class="progress-size" style="width: 50%;"></div>
                                                </div>
                                                <input type="range" min="0" max="100" value="50" class="range-max">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="size-button-wrap choose-option-list">
                                        <div class="size-button-item choose-option-item">
                                            <h6 class="text">Thin</h6>
                                        </div>
                                        <div class="size-button-item choose-option-item select-option">
                                            <h6 class="text">Normal</h6>
                                        </div>
                                        <div class="size-button-item choose-option-item">
                                            <h6 class="text">Plump</h6>
                                        </div>
                                    </div>
                                    <div class="suggests">
                                        <h4 class="">Suggests for you:</h4>
                                        <div class="suggests-list">
                                            <a href="#" class="suggests-item link h6">L - shirt</a>
                                            <a href="#" class="suggests-item link h6">XL - Pant</a>
                                            <a href="#" class="suggests-item link h6">31 - Jeans</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-content-inner overflow-auto text-nowrap">
                                <table class="tab-sizeguide-table">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>US</th>
                                            <th>Bust</th>
                                            <th>Waist</th>
                                            <th>Low Hip</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>XS</td>
                                            <td>2</td>
                                            <td>32</td>
                                            <td>24 - 25</td>
                                            <td>33 - 34</td>
                                        </tr>
                                        <tr>
                                            <td>S</td>
                                            <td>4</td>
                                            <td>26 - 27</td>
                                            <td>34 - 35</td>
                                            <td>35 - 26</td>
                                        </tr>
                                        <tr>
                                            <td>M</td>
                                            <td>6</td>
                                            <td>28 - 29</td>
                                            <td>36 - 37</td>
                                            <td>38 - 40</td>
                                        </tr>
                                        <tr>
                                            <td>L</td>
                                            <td>8</td>
                                            <td>30 - 31</td>
                                            <td>38 - 29</td>
                                            <td>42 - 44</td>
                                        </tr>
                                        <tr>
                                            <td>XL</td>
                                            <td>10</td>
                                            <td>32 - 33</td>
                                            <td>40 - 41</td>
                                            <td>45 - 47</td>
                                        </tr>
                                        <tr>
                                            <td>XXL</td>
                                            <td>12</td>
                                            <td>34 - 35</td>
                                            <td>42 - 43</td>
                                            <td>48 - 50</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Size Guide -->
        <!-- Compare -->
        <div class="offcanvas offcanvas-bottom canvas-compare" id="compare">
            <div class="canvas-wrapper">
                <div class="canvas-body">
                    <div class="container">
                        <div class="tf-compare-list main-list-clear wrap-empty_text">
                            <div class="tf-compare-head">
                                <h4 class="title">Compare products</h4>
                            </div>
                            <div class="tf-compare-offcanvas list-empty">
                                <p class="box-text_empty h6 text-main">Your Compare is curently empty</p>
                                <div class="tf-compare-item file-delete">
                                    <a href="product-countdown-timer.php">
                                        <div class="icon remove">
                                            <i class="icon-close"></i>
                                        </div>
                                        <img class="radius-3 lazyload" data-src="images/products/decor/product-1.jpg"
                                            src="images/products/decor/product-1.jpg" alt="">
                                    </a>
                                </div>
                                <div class="tf-compare-item file-delete">
                                    <a href="product-countdown-timer.php">
                                        <div class="icon remove">
                                            <i class="icon-close"></i>
                                        </div>
                                        <img class="radius-3 lazyload" data-src="images/products/decor/product-3.jpg"
                                            src="images/products/decor/product-3.jpg" alt="">
                                    </a>
                                </div>
                                <div class="tf-compare-item file-delete">
                                    <a href="product-countdown-timer.php">
                                        <div class="icon remove">
                                            <i class="icon-close"></i>
                                        </div>
                                        <img class="radius-3 lazyload" data-src="images/products/decor/product-5.jpg"
                                            src="images/products/decor/product-5.jpg" alt="">
                                    </a>
                                </div>
                            </div>
                            <div class="tf-compare-buttons">
                                <a href="compare.php"
                                    class="tf-btn animate-btn d-inline-flex bg-dark-2 justify-content-center">
                                    Compare
                                </a>
                                <div
                                    class="tf-btn btn-white animate-btn animate-dark line clear-list-empty tf-compare-button-clear-all">
                                    Clear All
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Compare -->
        <!-- Quick View -->
        <div class="modal modalCentered fade modal-quick-view" id="quickView">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content align-items-md-center">
                    <i class="icon icon-close icon-close-popup" data-bs-dismiss="modal"></i>
                    <div class="tf-product-media-wrap tf-btn-swiper-item">
                        <div dir="ltr" class="swiper tf-single-slide">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide" data-size="XS" data-color="beige">
                                    <div class="item">
                                        <img class="lazyload" data-src="images/products/decor/product-1.jpg"
                                            src="images/products/decor/product-1.jpg" alt="">
                                    </div>
                                </div>
                                <div class="swiper-slide" data-size="L" data-color="pink">
                                    <div class="item">
                                        <img class="lazyload" data-src="images/products/decor/product-3.jpg"
                                            src="images/products/decor/product-3.jpg" alt="">
                                    </div>
                                </div>
                                <div class="swiper-slide" data-size="M" data-color="green">
                                    <div class="item">
                                        <img class="lazyload" data-src="images/products/decor/product-4.jpg"
                                            src="images/products/decor/product-4.jpg" alt="">
                                    </div>
                                </div>
                                <div class="swiper-slide" data-size="S" data-color="blue">
                                    <div class="item">
                                        <img class="lazyload" data-src="images/products/decor/product-5.jpg"
                                            src="images/products/decor/product-5.jpg" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tf-product-info-wrap">
                        <div class="tf-product-info-inner tf-product-info-list">
                            <div class="tf-product-info-heading">
                                <a href="product-countdown-timer.php" class="link product-info-name fw-medium h1">
                                    Casual Round Neck T-Shirt
                                </a>
                                <div class="product-info-meta">
                                    <div class="rating">
                                        <div class="d-flex gap-4">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                                                    fill="#EF9122" />
                                            </svg>
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                                                    fill="#EF9122" />
                                            </svg>
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                                                    fill="#EF9122" />
                                            </svg>
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                                                    fill="#EF9122" />
                                            </svg>
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z"
                                                    fill="#EF9122" />
                                            </svg>
                                        </div>
                                        <div class="reviews text-main">(3.671 review)</div>
                                    </div>
                                    <div class="people-add text-primary">
                                        <i class="icon icon-shopping-cart-simple"></i>
                                        <span class="h6">9 people just added this product to their cart</span>
                                    </div>
                                </div>
                                <div class="product-info-price">
                                    <div class="price-wrap">
                                        <span class="price-new price-on-sale h2">$ 14.99</span>
                                        <span class="price-old compare-at-price h6">$ 24.99</span>
                                        <p class="badges-on-sale h6 fw-semibold">
                                            <span class="number-sale" data-person-sale="29">
                                                -29 %
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <p class="product-infor-sub text-main h6">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse justo dolor,
                                    consectetur vel metus vitae,
                                    tincidunt finibus dui fusce tellus enim.
                                </p>
                            </div>
                            <div class="tf-product-total-quantity w-100">
                                <div class="group-btn">
                                    <div class="wg-quantity">
                                        <button class="btn-quantity btn-decrease">
                                            <i class="icon icon-minus"></i>
                                        </button>
                                        <input class="quantity-product" type="text" name="number" value="1">
                                        <button class="btn-quantity btn-increase">
                                            <i class="icon icon-plus"></i>
                                        </button>
                                    </div>
                                    <p class="h6 d-none d-sm-block">
                                        15 products available
                                    </p>
                                    <button type="button"
                                        class="d-sm-none hover-tooltip box-icon btn-add-wishlist flex-sm-shrink-0">
                                        <span class="icon icon-heart"></span>
                                        <span class="tooltip">Add to Wishlist</span>
                                    </button>
                                    <a href="#compare" data-bs-toggle="offcanvas"
                                        class="d-sm-none hover-tooltip tooltip-top box-icon flex-sm-shrink-0">
                                        <span class="icon icon-compare"></span>
                                        <span class="tooltip">Compare</span>
                                    </a>
                                </div>
                                <div class="group-btn flex-sm-nowrap">
                                    <a href="#shoppingCart" data-bs-toggle="offcanvas"
                                        class="tf-btn animate-btn btn-add-to-cart">
                                        ADD TO CART
                                        <i class="icon icon-shopping-cart-simple"></i>
                                    </a>
                                    <button type="button"
                                        class="d-none d-sm-flex hover-tooltip box-icon btn-add-wishlist flex-sm-shrink-0">
                                        <span class="icon icon-heart"></span>
                                        <span class="tooltip">Add to Wishlist</span>
                                    </button>
                                    <a href="#compare" data-bs-toggle="offcanvas"
                                        class="d-none d-sm-flex hover-tooltip tooltip-top box-icon flex-sm-shrink-0">
                                        <span class="icon icon-compare"></span>
                                        <span class="tooltip">Compare</span>
                                    </a>
                                </div>
                                <div class="group-btn">
                                    <a href="checkout.php" class="tf-btn btn-yellow w-100 animate-btn animate-dark">
                                        Pay with
                                        <span class="icon">
                                            <svg width="68" height="18" viewBox="0 0 68 18" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M45.7745 0H40.609C40.3052 0 40.0013 0.30254 39.8494 0.605081L37.7224 13.9169C37.7224 14.2194 37.8743 14.3707 38.1782 14.3707H40.9129C41.2167 14.3707 41.3687 14.2194 41.3687 13.9169L41.9764 10.1351C41.9764 9.83258 42.2802 9.53004 42.736 9.53004H44.4072C47.9015 9.53004 49.8766 7.86606 50.3323 4.53811C50.6362 3.17668 50.3323 1.96652 49.7246 1.21017C48.8131 0.453813 47.4457 0 45.7745 0ZM46.3822 4.99193C46.0784 6.80717 44.711 6.80717 43.3437 6.80717H42.4321L43.0399 3.32795C43.0399 3.17668 43.1918 3.02541 43.4956 3.02541H43.7995C44.7111 3.02541 45.6226 3.02541 46.0784 3.63049C46.3822 3.78176 46.3822 4.23558 46.3822 4.99193Z"
                                                    fill="#139AD6" />
                                                <path
                                                    d="M8.55188 0H3.38637C3.08251 0 2.77866 0.30254 2.62673 0.605081L0.499756 13.9169C0.499756 14.2194 0.651685 14.3707 0.955538 14.3707H3.38637C3.69022 14.3707 3.99407 14.0682 4.146 13.7656L4.75371 10.1351C4.75371 9.83258 5.05757 9.53004 5.51335 9.53004H7.18454C10.6789 9.53004 12.6539 7.86607 13.1097 4.53811C13.4135 3.17668 13.1097 1.96652 12.502 1.21017C11.5904 0.453813 10.375 0 8.55188 0ZM9.15959 4.99193C8.85574 6.80717 7.4884 6.80717 6.12105 6.80717H5.36142L5.96913 3.32795C5.96913 3.17668 6.12105 3.02541 6.42491 3.02541H6.72876C7.64032 3.02541 8.55188 3.02541 9.00766 3.63049C9.15959 3.78176 9.31152 4.23558 9.15959 4.99193ZM24.2004 4.84066H21.7695C21.6176 4.84066 21.3137 4.99193 21.3137 5.1432L21.1618 5.89955L21.0099 5.59701C20.4022 4.84066 19.3387 4.53811 18.1233 4.53811C15.3886 4.53811 12.9578 6.6559 12.502 9.53004C12.1981 11.0427 12.6539 12.4042 13.4135 13.3118C14.1732 14.2194 15.2367 14.522 16.604 14.522C18.8829 14.522 20.0983 13.1605 20.0983 13.1605L19.9464 13.9169C19.9464 14.2194 20.0983 14.3707 20.4022 14.3707H22.6811C22.9849 14.3707 23.2888 14.0682 23.4407 13.7656L24.8081 5.29447C24.6561 5.1432 24.3523 4.84066 24.2004 4.84066ZM20.706 9.68131C20.4022 11.0427 19.3387 12.1016 17.8194 12.1016C17.0598 12.1016 16.4521 11.7991 16.1482 11.4966C15.8444 11.0427 15.6924 10.4377 15.6924 9.68131C15.8444 8.31988 17.0598 7.26098 18.4271 7.26098C19.1868 7.26098 19.6425 7.56352 20.0983 7.86606C20.5541 8.31987 20.706 9.07623 20.706 9.68131Z"
                                                    fill="#263B80" />
                                                <path
                                                    d="M61.2699 4.8416H58.839C58.6871 4.8416 58.3833 4.99287 58.3833 5.14414L58.2313 5.9005L58.0794 5.59796C57.4717 4.8416 56.4082 4.53906 55.1928 4.53906C52.4581 4.53906 50.0273 6.65685 49.5715 9.53099C49.2676 11.0437 49.7234 12.4051 50.4831 13.3128C51.2427 14.2204 52.3062 14.5229 53.6735 14.5229C55.9524 14.5229 57.1678 13.1615 57.1678 13.1615L57.0159 13.9178C57.0159 14.2204 57.1678 14.3716 57.4717 14.3716H59.7506C60.0545 14.3716 60.3583 14.0691 60.5102 13.7666L61.8776 5.29541C61.7256 5.14414 61.5737 4.8416 61.2699 4.8416ZM57.7755 9.68226C57.4717 11.0437 56.4082 12.1026 54.8889 12.1026C54.1293 12.1026 53.5216 11.8 53.2177 11.4975C52.9139 11.0437 52.762 10.4386 52.762 9.68226C52.9139 8.32082 54.1293 7.26193 55.4966 7.26193C56.2563 7.26193 56.7121 7.56447 57.1678 7.86701C57.7755 8.32082 57.9275 9.07718 57.7755 9.68226Z"
                                                    fill="#139AD6" />
                                                <path
                                                    d="M37.4179 4.83984H34.8351C34.5312 4.83984 34.3793 4.99111 34.2274 5.14238L30.885 10.2856L29.3657 5.44493C29.2138 5.14238 29.0619 4.99111 28.6061 4.99111H26.1753C25.8714 4.99111 25.7195 5.29366 25.7195 5.5962L28.4542 13.6135L25.8714 17.244C25.7195 17.5466 25.8714 18.0004 26.1753 18.0004H28.6061C28.9099 18.0004 29.0619 17.8491 29.2138 17.6978L37.5698 5.74747C38.0256 5.29366 37.7217 4.83984 37.4179 4.83984Z"
                                                    fill="#263B80" />
                                                <path
                                                    d="M64.158 0.455636L62.031 14.07C62.031 14.3725 62.1829 14.5238 62.4868 14.5238H64.6138C64.9176 14.5238 65.2215 14.2212 65.3734 13.9187L67.5004 0.606904C67.5004 0.304363 67.3484 0.153094 67.0446 0.153094H64.6138C64.4618 0.00182346 64.3099 0.153095 64.158 0.455636Z"
                                                    fill="#139AD6" />
                                            </svg>
                                        </span>
                                    </a>
                                </div>
                                <div class="group-btn justify-content-center">
                                    <a href="#" class="tf-btn-line text-normal letter-space-0 fw-normal">
                                        More payment options
                                    </a>
                                </div>
                            </div>
                            <a href="product-countdown-timer.php"
                                class="tf-btn-line text-normal letter-space-0 fw-normal">
                                <span class="h5">View full details</span>
                                <i class="icon icon-arrow-top-right fs-24"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Quick View -->

        <!-- Shopping Cart -->
        <div class="offcanvas offcanvas-end popup-shopping-cart" id="shoppingCart">
            <div class="tf-minicart-recommendations">
                <h4 class="title">You may also like</h4>
                <div class="wrap-recommendations">
                    <div class="list-cart">
                        <div class="list-cart-item">
                            <div class="image">
                                <img class="lazyload" data-src="images/products/decor/product-1.jpg"
                                    src="images/products/decor/product-1.jpg" alt="">
                            </div>
                            <div class="content">
                                <h6 class="name">
                                    <a class="link text-line-clamp-1" href="product-countdown-timer.php">Nike Sportswear
                                        Tee Shirts</a>
                                </h6>
                                <div class="cart-item-bot">
                                    <div class="price-wrap price">
                                        <span class="price-old h6 fw-normal">₹99,99</span>
                                        <span class="price-new h6">₹69,99</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="list-cart-item">
                            <div class="image">
                                <img class="lazyload" data-src="images/products/decor/product-3.jpg"
                                    src="images/products/decor/product-3.jpg" alt="">
                            </div>
                            <div class="content">
                                <h6 class="name">
                                    <a class="link text-line-clamp-1" href="product-countdown-timer.php">Puma Essentials
                                        Graphic Tee</a>
                                </h6>
                                <div class="cart-item-bot">
                                    <div class="price-wrap price">
                                        <span class="price-old h6 fw-normal">₹89,99</span>
                                        <span class="price-new h6">₹59,99</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="list-cart-item">
                            <div class="image">
                                <img class="lazyload" data-src="images/products/decor/product-5.jpg"
                                    src="images/products/decor/product-5.jpg" alt="">
                            </div>
                            <div class="content">
                                <h6 class="name">
                                    <a class="link text-line-clamp-1" href="product-countdown-timer.php">Reebok Classic
                                        Crew Sweatshirt</a>
                                </h6>
                                <div class="cart-item-bot">
                                    <div class="price-wrap price">
                                        <span class="price-old h6 fw-normal">₹149.99</span>
                                        <span class="price-new h6">₹109.99</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="list-cart-item">
                            <div class="image">
                                <img class="lazyload" data-src="images/products/decor/product-7.jpg"
                                    src="images/products/decor/product-7.jpg" alt="">
                            </div>
                            <div class="content">
                                <h6 class="name">
                                    <a class="link text-line-clamp-1" href="product-countdown-timer.php">Columbia PFG
                                        Fishing Shirt</a>
                                </h6>
                                <div class="cart-item-bot">
                                    <div class="price-wrap price">
                                        <span class="price-old h6 fw-normal">₹59.99</span>
                                        <span class="price-new h6">₹39.99</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="canvas-wrapper">
                <div class="popup-header">
                    <span class="title fw-semibold h4">Shopping cart</span>
                    <span class="icon-close icon-close-popup" data-bs-dismiss="offcanvas"></span>
                </div>
                <div class="wrap">
                    <div class="tf-mini-cart-wrap list-file-delete wrap-empty_text">
                        <div class="tf-mini-cart-main">
                            <div class="tf-mini-cart-sroll">
                                <div class="tf-mini-cart-items list-empty">
                                    <div class="box-text_empty type-shop_cart">
                                        <div class="shop-empty_top">
                                            <span class="icon">
                                                <i class="icon-shopping-cart-simple"></i>
                                            </span>
                                            <h3 class="text-emp fw-normal">Your cart is empty</h3>
                                            <p class="h6 text-main">
                                                Your cart is currently empty. Let us assist you in finding the right
                                                product
                                            </p>
                                        </div>
                                        <div class="shop-empty_bot">
                                            <a href="shop.php" class="tf-btn animate-btn">
                                                Shopping
                                            </a>
                                            <a href="index.php" class="tf-btn style-line">
                                                Back to home
                                            </a>
                                        </div>
                                    </div>
                                    <div class="tf-mini-cart-item file-delete">
                                        <div class="tf-mini-cart-image">
                                            <img class="lazyload" data-src="images/products/decor/product-9.jpg"
                                                src="images/products/decor/product-9.jpg" alt="img-product">
                                        </div>
                                        <div class="tf-mini-cart-info">
                                            <div class="text-small text-main-2 sub">T-shirt</div>
                                            <h6 class="title">
                                                <a href="product-countdown-timer.php"
                                                    class="link text-line-clamp-1">Queen fashion long sleeve shirt,
                                                    basic
                                                    t-shirt</a>
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="h6 fw-semibold">
                                                    <span class="number">1x</span>
                                                    <span class="price text-primary tf-mini-card-price">₹20.00</span>
                                                </div>
                                                <i class="icon link icon-close remove"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tf-mini-cart-item file-delete">
                                        <div class="tf-mini-cart-image">
                                            <img class="lazyload" data-src="images/products/decor/product-11.jpg"
                                                src="images/products/decor/product-11.jpg" alt="img-product">
                                        </div>
                                        <div class="tf-mini-cart-info">
                                            <div class="text-small text-main-2 sub">T-shirt</div>
                                            <h6 class="title">
                                                <a href="product-countdown-timer.php"
                                                    class="link text-line-clamp-1">Champion Reverse Weave Pullover</a>
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="h6 fw-semibold">
                                                    <span class="number">1x</span>
                                                    <span class="price text-primary tf-mini-card-price">₹24.99</span>
                                                </div>
                                                <i class="icon link icon-close remove"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tf-mini-cart-item file-delete">
                                        <div class="tf-mini-cart-image">
                                            <img class="lazyload" data-src="images/products/decor/product-13.jpg"
                                                src="images/products/decor/product-13.jpg" alt="img-product">
                                        </div>
                                        <div class="tf-mini-cart-info">
                                            <div class="text-small text-main-2 sub">Sweatshirt</div>
                                            <h6 class="title">
                                                <a href="product-countdown-timer.php"
                                                    class="link text-line-clamp-1">ASICS Core Running Tights</a>
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="h6 fw-semibold">
                                                    <span class="number">1x</span>
                                                    <span class="price text-primary tf-mini-card-price">₹18.99</span>
                                                </div>
                                                <i class="icon link icon-close remove"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tf-mini-cart-item file-delete">
                                        <div class="tf-mini-cart-image">
                                            <img class="lazyload" data-src="images/products/decor/product-15.jpg"
                                                src="images/products/decor/product-15.jpg" alt="img-product">
                                        </div>
                                        <div class="tf-mini-cart-info">
                                            <div class="text-small text-main-2 sub">Shorts</div>
                                            <h6 class="title">
                                                <a href="product-countdown-timer.php" class="link text-line-clamp-1">New
                                                    Balance Athletics Shorts</a>
                                            </h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="h6 fw-semibold">
                                                    <span class="number">1x</span>
                                                    <span class="price text-primary tf-mini-card-price">₹22.50</span>
                                                </div>
                                                <i class="icon link icon-close remove"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tf-mini-cart-bottom box-empty_clear">
                            <div class="tf-mini-cart-tool">
                                <div class="tf-mini-cart-tool-btn btn-add-note">
                                    <div class="h6">Note</div>
                                    <i class="icon icon-note-pencil"></i>
                                </div>
                                <div class="tf-mini-cart-tool-btn btn-estimate-shipping">
                                    <div class="h6">Shipping</div>
                                    <i class="icon icon-truck"></i>
                                </div>
                                <div class="tf-mini-cart-tool-btn btn-add-gift">
                                    <div class="h6">Gift</div>
                                    <i class="icon icon-gift"></i>
                                </div>
                            </div>
                            <div class="tf-mini-cart-threshold">
                                <div class="text">
                                    <h6 class="subtotal">Subtotal (<span class="prd-count">3</span> item)</h6>
                                    <h4 class="text-primary total-price tf-totals-total-value">₹60.00</h4>
                                </div>
                                <div class="tf-progress-bar tf-progress-ship">
                                    <div class="value" style="width: 0%;" data-progress="25"></div>
                                </div>
                                <div class="desc text-main">Add <span class="text-primary fw-bold">₹15.40</span> to cart
                                    and get free shipping!</div>
                            </div>
                            <div class="tf-mini-cart-bottom-wrap">
                                <div class="tf-mini-cart-view-checkout">
                                    <a href="view-cart.php" class="tf-btn btn-white animate-btn animate-dark line">View
                                        cart</a>
                                    <a href="checkout.php"
                                        class="tf-btn animate-btn d-inline-flex bg-dark-2 w-100 justify-content-center"><span>Check
                                            out</span></a>
                                </div>
                                <div class="free-shipping">
                                    <i class="icon icon-truck"></i>
                                    Free shipping on all orders over ₹150
                                </div>
                            </div>
                        </div>
                        <div class="tf-mini-cart-tool-openable add-note">
                            <div class="overlay tf-mini-cart-tool-close"></div>
                            <form action="#" class="tf-mini-cart-tool-content">
                                <label for="Cart-note" class="tf-mini-cart-tool-text h5 fw-semibold">
                                    <i class="icon icon-note-pencil"></i>
                                    Note
                                </label>
                                <textarea name="note" id="Cart-note" placeholder="Note about your order"></textarea>
                                <div class="tf-cart-tool-btns">
                                    <button class="subscribe-button tf-btn animate-btn d-inline-flex bg-dark-2 w-100"
                                        type="submit">Save</button>
                                    <div class="tf-btn btn-white animate-btn animate-dark line tf-mini-cart-tool-close">
                                        Cancel</div>
                                </div>
                            </form>
                        </div>
                        <div class="tf-mini-cart-tool-openable estimate-shipping">
                            <div class="overlay tf-mini-cart-tool-close"></div>
                            <form id="shipping-form" class="tf-mini-cart-tool-content">
                                <div class="tf-mini-cart-tool-text h5 fw-semibold">
                                    <i class="icon icon-truck"></i>
                                    Shipping
                                </div>
                                <div class="field">
                                    <div class="tf-select">
                                        <select class="w-100" id="shipping-country-form" name="address[country]"
                                            data-default="">
                                            <option value="Australia"
                                                data-provinces='[["Australian Capital Territory","Australian Capital Territory"],["New South Wales","New South Wales"],["Northern Territory","Northern Territory"],["Queensland","Queensland"],["South Australia","South Australia"],["Tasmania","Tasmania"],["Victoria","Victoria"],["Western Australia","Western Australia"]]'>
                                                Australia</option>
                                            <option value="Austria" data-provinces='[]'>Austria</option>
                                            <option value="Belgium" data-provinces='[]'>Belgium</option>
                                            <option value="Canada"
                                                data-provinces='[["Ontario","Ontario"],["Quebec","Quebec"]]'>Canada
                                            </option>
                                            <option value="Czech Republic" data-provinces='[]'>Czechia</option>
                                            <option value="Denmark" data-provinces='[]'>Denmark</option>
                                            <option value="Finland" data-provinces='[]'>Finland</option>
                                            <option value="France" data-provinces='[]'>France</option>
                                            <option value="Germany" data-provinces='[]'>Germany</option>
                                            <option selected value="United States"
                                                data-provinces='[["Alabama","Alabama"],["California","California"],["Florida","Florida"]]'>
                                                United States</option>
                                            <option value="United Kingdom"
                                                data-provinces='[["England","England"],["Scotland","Scotland"],["Wales","Wales"],["Northern Ireland","Northern Ireland"]]'>
                                                United Kingdom</option>
                                            <option value="India" data-provinces='[]'>India</option>
                                            <option value="Japan" data-provinces='[]'>Japan</option>
                                            <option value="Mexico" data-provinces='[]'>Mexico</option>
                                            <option value="South Korea" data-provinces='[]'>South Korea</option>
                                            <option value="Spain" data-provinces='[]'>Spain</option>
                                            <option value="Italy" data-provinces='[]'>Italy</option>
                                            <option value="Vietnam"
                                                data-provinces='[["Ha Noi","Ha Noi"],["Da Nang","Da Nang"],["Ho Chi Minh","Ho Chi Minh"]]'>
                                                Vietnam</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="tf-select">
                                        <select id="shipping-province-form" name="address[province]"
                                            data-default=""></select>
                                    </div>
                                </div>
                                <div class="field">
                                    <input type="text" placeholder="Postal code" data-opend-focus id="zipcode"
                                        name="address[zip]" value="">
                                </div>
                                <div id="zipcode-message" class="error" style="display: none;">
                                    We found one shipping rate available for undefined.
                                </div>
                                <div id="zipcode-success" class="success" style="display: none;">
                                    <p>We found one shipping rate available for your address:</p>
                                    <p class="standard">Standard at <span>₹0.00</span> USD</p>
                                </div>
                                <div class="tf-cart-tool-btns">
                                    <button class="subscribe-button tf-btn animate-btn d-inline-flex bg-dark-2 w-100"
                                        type="submit">Save</button>
                                    <div class="tf-btn btn-white animate-btn animate-dark line tf-mini-cart-tool-close">
                                        Cancel</div>
                                </div>
                            </form>
                        </div>
                        <div class="tf-mini-cart-tool-openable add-gift">
                            <div class="overlay tf-mini-cart-tool-close"></div>
                            <form action="#" class="tf-mini-cart-tool-content">
                                <div class="tf-mini-cart-tool-text h5 fw-semibold">
                                    <i class="icon icon-gift"></i>
                                    Gift
                                </div>
                                <div class="wrap">
                                    <i class="icon icon-gift-2"></i>
                                    <h3>Only <span class="text-primary">₹2</span> for a gift box</h3>
                                </div>
                                <div class="tf-cart-tool-btns">
                                    <button class="subscribe-button tf-btn animate-btn d-inline-flex bg-dark-2 w-100"
                                        type="submit">Add a
                                        gift</button>
                                    <div class="tf-btn btn-white animate-btn animate-dark line tf-mini-cart-tool-close">
                                        Cancel</div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Shopping Cart -->


        <!-- Javascript -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/swiper-bundle.min.js"></script>
        <script src="js/carousel.js"></script>
        <script src="js/bootstrap-select.min.js"></script>
        <script src="js/lazysize.min.js"></script>
        <script src="js/wow.min.js"></script>
        <script src="js/parallaxie.js"></script>
        <script src="js/main.js"></script>
        <script src="js/lookbook.js"></script>

        <!-- Product Action Handlers -->
        <script>
            jQuery(document).ready(function ($) {
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

                // Add to Cart Handler
                $(document).on('click', '.add-to-cart-btn', function (e) {
                    e.preventDefault();
                    const productId = $(this).data('product-id');
                    const $button = $(this);

                    if (!productId) {
                        showNotification('Error', 'Invalid product', 'error');
                        return;
                    }

                    $button.css('pointer-events', 'none');

                    $.ajax({
                        url: 'ajax/add-to-cart.php',
                        type: 'POST',
                        data: {
                            product_id: productId,
                            quantity: 1
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                if (response.cart_count !== undefined && window.updateMenuCartCount) {
                                    window.updateMenuCartCount();
                                }
                                showNotification('Success!', response.message || 'Product added to cart successfully!', 'success');
                            } else {
                                showNotification('Error', response.message || 'Failed to add product to cart', 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Cart error:', error);
                            showNotification('Error', 'An error occurred while adding to cart. Please try again.', 'error');
                        },
                        complete: function () {
                            $button.css('pointer-events', 'auto');
                        }
                    });
                });

                // Add to Wishlist Handler
                $(document).on('click', '.add-to-wishlist-btn', function (e) {
                    e.preventDefault();
                    const productId = $(this).data('product-id');
                    const $button = $(this);
                    const $icon = $button.find('.icon');

                    if (!productId) {
                        showNotification('Error', 'Invalid product', 'error');
                        return;
                    }

                    $button.css('pointer-events', 'none');

                    $.ajax({
                        url: 'ajax/wishlist.php',
                        type: 'POST',
                        data: {
                            action: 'toggle',
                            product_id: productId
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                if (response.wishlist_count !== undefined && window.updateMenuWishlistCount) {
                                    window.updateMenuWishlistCount();
                                }

                                if (response.action === 'added') {
                                    $icon.removeClass('icon-heart').addClass('icon-heart-fill');
                                    $button.addClass('active');
                                    showNotification('Success!', response.message || 'Added to wishlist!', 'success');
                                } else {
                                    $icon.removeClass('icon-heart-fill').addClass('icon-heart');
                                    $button.removeClass('active');
                                    showNotification('Success!', response.message || 'Removed from wishlist!', 'success');
                                }
                            } else {
                                if (response.redirect) {
                                    showNotification('Login Required', response.error || 'Please login to use wishlist', 'warning');
                                    setTimeout(() => {
                                        window.location.href = response.redirect;
                                    }, 1500);
                                } else {
                                    showNotification('Error', response.error || 'Failed to update wishlist', 'error');
                                }
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Wishlist error:', error);
                            showNotification('Error', 'An error occurred. Please try again.', 'error');
                        },
                        complete: function () {
                            $button.css('pointer-events', 'auto');
                        }
                    });
                });

                // Quick View Handler - Opens product detail page
                $(document).on('click', '.quick-view-btn', function (e) {
                    e.preventDefault();
                    const productId = $(this).data('product-id');

                    if (!productId) {
                        showNotification('Error', 'Invalid product', 'error');
                        return;
                    }

                    window.location.href = 'product-detail.php?id=' + productId;
                });
            });
        </script>

        <!-- Notification Toast Styles -->
        <style>
            /* New Products section - 1:1 square images */
            .new-products-section .card-product_wrapper {
                aspect-ratio: 1 / 1;
            }

            .notification-toast {
                position: fixed;
                top: 20px;
                right: 20px;
                min-width: 300px;
                max-width: 400px;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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