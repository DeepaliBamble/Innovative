<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Gallery - Innovative Homesi | Furniture Design & Inspiration</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Explore our gallery of premium furniture designs, home décor, and interior inspiration. See how Innovative Homesi transforms spaces with beautiful, sustainable craftsmanship.">
    <meta name="keywords" content="furniture gallery, home décor gallery, interior design inspiration, furniture photos, designer furniture, innovative homesi gallery">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://innovativehomesi.com/gallery">

    <!-- Open Graph Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Gallery - Innovative Homesi | Furniture Design & Inspiration">
    <meta property="og:description" content="Explore our beautiful collection of premium furniture designs and home décor inspiration.">
    <meta property="og:url" content="https://innovativehomesi.com/gallery">
    <meta property="og:site_name" content="Innovative Homesi">
    <meta property="og:image" content="https://innovativehomesi.com/images/logo/logo.png">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Gallery - Innovative Homesi | Furniture Design & Inspiration">
    <meta name="twitter:description" content="Explore our beautiful collection of premium furniture designs and home décor inspiration.">
    <meta name="twitter:image" content="https://innovativehomesi.com/images/logo/logo.png">

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

    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 50px;
        }

        @media (max-width: 768px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 16px;
            }
        }

        @media (max-width: 480px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 12px;
            }
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            aspect-ratio: 1;
            cursor: pointer;
            group: 'gallery';
            background: #f0f0f0;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
            border-radius: 12px;
        }

        .gallery-item:hover .gallery-overlay {
            background: rgba(0, 0, 0, 0.6);
        }

        .gallery-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s ease;
        }

        .gallery-item:hover .gallery-icon {
            opacity: 1;
            transform: scale(1);
        }

        .gallery-icon i {
            font-size: 24px;
            color: #d4a574;
        }

        .gallery-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            color: white;
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover .gallery-info {
            transform: translateY(0);
        }

        .gallery-title {
            font-weight: bold;
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .gallery-stats {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 0.9rem;
        }

        /* Lightbox customization */
        .lightbox {
            border-radius: 12px;
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
        <section class="page-title-image">
            <div class="page_image overflow-hidden">
                <img class="lazyload ani-zoom" src="images/section/about-us.jpg" data-src="images/section/about-us.jpg" alt="Gallery">
            </div>
            <div class="page_content">
                <div class="container">
                    <div class="content">
                        <h1 class="heading fw-bold">
                            OUR GALLERY <br class="d-none d-sm-block">
                            DESIGN INSPIRATION
                        </h1>
                        <p class="sub-heading">Explore our beautiful collection of furniture and home décor designs</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Page Title -->

        <!-- Gallery Section -->
        <section class="flat-spacing">
            <div class="container">
                <!-- Gallery Grid -->
                <div class="gallery-grid" id="galleryGrid">
                    <?php
                    // Fetch all active gallery images with category names
                    $galleryQuery = "SELECT g.*, c.name as category_name FROM gallery g LEFT JOIN categories c ON g.category_id = c.id WHERE g.is_active = 1 ORDER BY g.display_order, g.created_at DESC";
                    $galleryStmt = $pdo->prepare($galleryQuery);
                    $galleryStmt->execute();
                    $galleryImages = $galleryStmt->fetchAll();

                    if (empty($galleryImages)) {
                        echo '<div style="text-align:center; color:#999; padding:40px;">No images found in gallery.</div>';
                    } else {
                        foreach ($galleryImages as $img) {
                            $imgPath = $img['image_path'] ? htmlspecialchars($img['image_path'], ENT_QUOTES, 'UTF-8') : '';
                            $imgTitle = $img['title'] ? htmlspecialchars($img['title'], ENT_QUOTES, 'UTF-8') : '';
                            $imgDesc = $img['description'] ? htmlspecialchars($img['description'], ENT_QUOTES, 'UTF-8') : '';
                            $imgCatName = isset($img['category_name']) && $img['category_name'] ? ucfirst(htmlspecialchars($img['category_name'], ENT_QUOTES, 'UTF-8')) : 'All';
                            echo '<div class="gallery-item" data-category="' . $imgCatName . '">';
                            echo '<a href="' . $imgPath . '" data-lightbox="gallery" data-title="' . $imgTitle . '">';
                            echo '<img src="' . $imgPath . '" alt="' . $imgTitle . '" />';
                            echo '<div class="gallery-overlay"><span class="gallery-icon"><i class="fa fa-search-plus"></i></span></div>';
                            echo '</a>';
                            echo '<div class="gallery-info">';
                            echo '<div class="gallery-title">' . $imgTitle . '</div>';
                            if ($imgDesc) echo '<div class="gallery-desc">' . $imgDesc . '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>

                <!-- Load More Button -->
                <div class="gallery-load-more">
                    <button class="load-btn" id="loadMoreBtn" style="display: none;">Load More Images</button>
                </div>

                <!-- Stats -->
                <div class="gallery-stats">
                    <p><span id="imageCount"><?= count($galleryImages) ?></span> images displayed</p>
                </div>
            </div>
        </section>
        <!-- /Gallery Section -->

        <!-- CTA Section -->
        <section class="flat-spacing" style="background-color: #f8f8f8;">
            <div class="container">
                <div class="text-center">
                    <h2 class="fw-bold mb-4" style="font-size: 2.5rem;">Ready to Transform Your Space?</h2>
                    <p style="font-size: 1.1rem; color: #666; line-height: 1.8; margin-bottom: 30px;">
                        Browse our full collection of premium furniture and find the perfect pieces for your home. Every item is crafted with care and designed to inspire.
                    </p>
                    <a href="shop.php" class="tf-btn animate-btn">
                        Shop Collection
                        <i class="icon icon-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>
        <!-- /CTA Section -->

        <?php include 'includes/footer.php'; ?>
    
    <!-- Toolbar -->
    <div class="tf-toolbar-bottom">
        <div class="toolbar-item">
            <a href="shop-default.php">
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

    <!-- Javascript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/swiper-bundle.min.js"></script>
    <script src="js/carousel.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/parallaxie.js"></script>
    <script src="js/main.js"></script>

    <!-- Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

    <script src="js/sibforms.js" defer></script>
    <script>
        // Reinitialize lightbox after page load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lightbox !== 'undefined') {
                lightbox.refresh();
            }

            // Update image count
            const galleryItems = document.querySelectorAll('.gallery-item');
            const imageCount = document.getElementById('imageCount');
            if (imageCount) {
                imageCount.textContent = galleryItems.length;
            }
        });

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
