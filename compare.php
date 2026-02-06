<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Compare Furniture - Innovative Homesi | Compare Features & Prices</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Compare furniture specifications, features, and prices side-by-side to make the best choice for your home.">
    <meta name="keywords" content="compare furniture, furniture comparison, compare sofas, furniture features">
    <meta name="robots" content="index, follow">

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
                    <h1 class="title-page">Compare Product</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Compare Product</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->
        <!-- Compare -->
        <div class="flat-spacing">
            <div class="container">
                <div class="tf-table-compare">
                    <table>
                        <thead>
                            <tr class="compare-row">
                                <th class="compare-col "></th>
                                <th class="compare-col compare-head">
                                    <div class="compare-item">
                                        <div class="item_image">
                                            <img src="images/products/product-45.jpg" data-src="images/products/product-45.jpg" alt=""
                                                class="lazyload">
                                            <span class="remove">
                                                <i class="icon icon-trash"></i>
                                            </span>
                                        </div>
                                        <a href="product-detail.php" class="item_name h4 link">
                                            Women's T-shirts Plus Size
                                        </a>
                                        <div class="item_price price-wrap">
                                            <span class="price-old h6">₹99,99</span>
                                            <span class="price-new h6 text-main fw-semibold">₹69,99</span>
                                        </div>
                                    </div>
                                </th>
                                <th class="compare-col compare-head">
                                    <div class="compare-item">
                                        <div class="item_image">
                                            <img src="images/products/product-49.jpg" data-src="images/products/product-49.jpg" alt=""
                                                class="lazyload">
                                            <span class="remove">
                                                <i class="icon icon-trash"></i>
                                            </span>
                                        </div>
                                        <a href="product-detail.php" class="item_name h4 link">
                                            Nike Sportswear Tee Shirts
                                        </a>
                                        <div class="item_price price-wrap">
                                            <span class="price-old h6">₹179,99</span>
                                            <span class="price-new h6 text-main fw-semibold">₹149,99</span>
                                        </div>
                                    </div>
                                </th>
                                <th class="compare-col compare-head">
                                    <div class="compare-item">
                                        <div class="item_image">
                                            <img src="images/products/product-53.jpg" data-src="images/products/product-53.jpg" alt=""
                                                class="lazyload">
                                            <span class="remove">
                                                <i class="icon icon-trash"></i>
                                            </span>
                                        </div>
                                        <a href="product-detail.php" class="item_name h4 link">
                                            Summer Two Piece Set
                                        </a>
                                        <div class="item_price price-wrap">
                                            <span class="price-old h6">₹79,99</span>
                                            <span class="price-new h6 text-main fw-semibold">₹49,99</span>
                                        </div>
                                    </div>
                                </th>
                                <th class="compare-col compare-head">
                                    <div class="compare-item">
                                        <div class="item_image">
                                            <img src="images/products/product-59.jpg" data-src="images/products/product-59.jpg" alt=""
                                                class="lazyload">
                                            <span class="remove">
                                                <i class="icon icon-trash"></i>
                                            </span>
                                        </div>
                                        <a href="product-detail.php" class="item_name h4 link">
                                            Youth Casual Fleece Hoodie
                                        </a>
                                        <div class="item_price price-wrap">
                                            <span class="price-old h6">₹89,99</span>
                                            <span class="price-new h6 text-main fw-semibold">₹139,99</span>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Rating</td>
                                <td class="compare-col">
                                    <div class="compare_rate">
                                        <div class="rate_wrap">
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                        </div>
                                        <span class="rate_count">
                                            (3.671)
                                        </span>
                                    </div>
                                </td>
                                <td class="compare-col">
                                    <div class="compare_rate">
                                        <div class="rate_wrap">
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                        </div>
                                        <span class="rate_count">
                                            (2.480)
                                        </span>
                                    </div>
                                </td>
                                <td class="compare-col">
                                    <div class="compare_rate">
                                        <div class="rate_wrap">
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                        </div>
                                        <span class="rate_count">
                                            (4.925)
                                        </span>
                                    </div>
                                </td>
                                <td class="compare-col">
                                    <div class="compare_rate">
                                        <div class="rate_wrap">
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                            <i class="icon-star text-star"></i>
                                        </div>
                                        <span class="rate_count">
                                            (1.417)
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Type</td>
                                <td class="compare-col compare-value"><span>T-Shirt</span></td>
                                <td class="compare-col compare-value"><span>Hoodie</span></td>
                                <td class="compare-col compare-value"><span>Sweater</span></td>
                                <td class="compare-col compare-value"><span>Bomber</span></td>

                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Price</td>
                                <td class="compare-col compare-value"><span>₹250</span></td>
                                <td class="compare-col compare-value"><span>₹250</span></td>
                                <td class="compare-col compare-value"><span>₹250</span></td>
                                <td class="compare-col compare-value"><span>₹250</span></td>
                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Color</td>
                                <td class="compare-col compare-value">
                                    <div class="list-compare-color justify-content-center">
                                        <span class="item bg-caramel"></span>
                                        <span class="item bg-deep-orange"></span>
                                        <span class="item bg-baby-blue"></span>
                                        <span class="item bg-light-beige"></span>
                                        <span class="item bg-sage-green"></span>
                                    </div>
                                </td>
                                <td class="compare-col compare-value">
                                    <div class="list-compare-color justify-content-center">
                                        <span class="item bg-light-purple"></span>
                                        <span class="item bg-dark-charcoal"></span>
                                        <span class="item bg-dark-jade"></span>
                                        <span class="item bg-tomato"></span>
                                        <span class="item bg-honey-orange"></span>
                                    </div>
                                </td>
                                <td class="compare-col compare-value">
                                    <div class="list-compare-color justify-content-center">
                                        <span class="item bg-dark-olive"></span>
                                        <span class="item bg-hot-pink"></span>
                                        <span class="item bg-muted-violet"></span>
                                        <span class="item bg-light-beige"></span>
                                        <span class="item bg-dusty-olive"></span>
                                    </div>
                                </td>
                                <td class="compare-col compare-value">
                                    <div class="list-compare-color justify-content-center">
                                        <span class="item bg-deep-orange"></span>
                                        <span class="item bg-light-beige"></span>
                                        <span class="item bg-muted-violet"></span>
                                        <span class="item bg-baby-blue"></span>
                                        <span class="item bg-dusty-olive"></span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Brand</td>
                                <td class="compare-col compare-value"><span>Real Essentials</span></td>
                                <td class="compare-col compare-value"><span>Dokotoo</span></td>
                                <td class="compare-col compare-value"><span>Hanes</span></td>
                                <td class="compare-col compare-value"><span>Hades</span></td>
                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Material</td>
                                <td class="compare-col compare-value"><span>Cotton</span></td>
                                <td class="compare-col compare-value"><span>Fleece</span></td>
                                <td class="compare-col compare-value"><span>Silk</span></td>
                                <td class="compare-col compare-value"><span>Nylon</span></td>
                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Size</td>
                                <td class="compare-col compare-value"><span>XS, M, L, XL, XXL</span></td>
                                <td class="compare-col compare-value"><span>XS, M</span></td>
                                <td class="compare-col compare-value"><span>L, XL, XXL</span></td>
                                <td class="compare-col compare-value"><span>XS, M, XL</span></td>
                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Intended Use</td>
                                <td class="compare-col compare-value"><span>Wear Every Day</span></td>
                                <td class="compare-col compare-value"><span>Gym</span></td>
                                <td class="compare-col compare-value"><span>Sports</span></td>
                                <td class="compare-col compare-value"><span>Travel</span></td>
                            </tr>
                            <tr class="compare-row">
                                <td class="compare-col compare-title">Buy</td>
                                <td class="compare-col compare-value p-0">
                                    <a href="#shoppingCart" class="tf-btn style-transparent w-100 rounded-0" data-bs-toggle="offcanvas">
                                        Add to cart
                                        <i class="icon icon-shopping-cart-simple"></i>
                                    </a>
                                </td>
                                <td class="compare-col compare-value p-0">
                                    <a href="#shoppingCart" class="tf-btn style-transparent w-100 rounded-0" data-bs-toggle="offcanvas">
                                        Add to cart
                                        <i class="icon icon-shopping-cart-simple"></i>
                                    </a>
                                </td>
                                <td class="compare-col compare-value p-0">
                                    <a href="#shoppingCart" class="tf-btn style-transparent w-100 rounded-0" data-bs-toggle="offcanvas">
                                        Add to cart
                                        <i class="icon icon-shopping-cart-simple"></i>
                                    </a>
                                </td>
                                <td class="compare-col compare-value p-0">
                                    <a href="#shoppingCart" class="tf-btn style-transparent w-100 rounded-0" data-bs-toggle="offcanvas">
                                        Add to cart
                                        <i class="icon icon-shopping-cart-simple"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /Compare -->
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

