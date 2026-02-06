<?php
require_once __DIR__ . '/includes/init.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Shop Furniture - Innovative Homesi | Premium Furniture Collection</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Browse our complete furniture collection. Explore sofas, chairs, tables, beds, and home decor items.">
    <meta name="keywords" content="furniture shop, sofa collection, chairs, tables, beds, home decor, furnishing">

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
    <link rel="stylesheet" type="text/css" href="css/shop-custom.css">
    <link rel="stylesheet" type="text/css" href="css/shop-custom-additions.css">

    <!-- Favicon and Touch Icons  -->
    <link rel="shortcut icon" href="images/logo/favicon.svg">
    <link rel="apple-touch-icon-precomposed" href="images/logo/favicon.svg">

    <style>
        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .category-parent {
            margin-bottom: 15px;
        }

        .category-parent > .category-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid #e5e5e5;
        }

        .category-parent > .category-title:hover {
            color: #8b7355;
        }

        .category-parent.active > .category-title {
            background-color: rgba(139, 115, 85, 0.1);
            border-left: 4px solid #8b7355;
            padding-left: 12px;
            color: #8b7355;
        }

        .category-parent > .category-title h5 {
            margin: 0;
            font-weight: 600;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .category-parent.active > .category-title h5 {
            color: #8b7355;
        }

        .category-icon {
            transition: transform 0.3s ease;
            font-size: 14px;
        }

        .category-icon.rotated {
            transform: rotate(180deg);
        }

        .subcategory-list {
            list-style: none;
            padding: 0;
            margin: 12px 0 0 0;
            display: none;
        }

        .subcategory-list.show {
            display: block;
        }

        .subcategory-item {
            margin-bottom: 8px;
        }

        .subcategory-item a {
            display: block;
            padding: 8px 0 8px 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }

        .subcategory-item a:before {
            content: "";
            position: absolute;
            left: 0;
            color: #8b7355;
            font-weight: bold;
        }

        .subcategory-item a:hover {
            color: #8b7355;
            padding-left: 25px;
        }

        .subcategory-item.active a {
            color: #8b7355;
            font-weight: 600;
            background-color: rgba(139, 115, 85, 0.08);
            padding: 10px 20px;
            border-radius: 6px;
            position: relative;
        }

        .subcategory-item.active a:before {
            content: "▶";
            position: absolute;
            left: 6px;
            color: #8b7355;
            font-weight: bold;
            font-size: 10px;
        }

        .subcategory-item.active a:hover {
            background-color: rgba(139, 115, 85, 0.15);
        }

        .widget-facet {
            margin-bottom: 30px;
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
                    <h1 class="title-page">Shop</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Shop</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->

        <!-- Section Product -->
        <div class="flat-spacing">
            <div class="container">
                <div class="row flex-row-reverse">
                    <!-- Sidebar (now right side) -->
                    <div class="col-xl-3 order-xl-2">
                        <div class="canvas-sidebar sidebar-filter canvas-filter right">
                            <div class="canvas-wrapper">
                                <div class="canvas-header d-xl-none">
                                    <span class="title h3 fw-medium">Categories</span>
                                    <span class="icon-close link icon-close-popup fs-24 close-filter"></span>
                                </div>
                                <div class="canvas-body">
                                    <!-- Categories Widget -->
                                    <div class="widget-facet">
                                        <div class="facet-title mb-3">
                                            <span class="h4 fw-semibold">Browse Categories</span>
                                        </div>

                                        <ul class="category-list">
                                            <?php
                                            // Get selected category slug from URL
                                            $selectedCategorySlug = isset($_GET['category']) ? sanitize($_GET['category']) : null;

                                            // Fetch all parent categories (no parent_id)
                                            $stmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY display_order, name");
                                            $stmt->execute();
                                            $categories = $stmt->fetchAll();
                                            foreach ($categories as $cat) {
                                                $catId = $cat['id'];
                                                $catSlug = htmlspecialchars($cat['slug']);
                                                $catName = htmlspecialchars($cat['name']);

                                                // Fetch subcategories
                                                $subStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY display_order, name");
                                                $subStmt->execute([$catId]);
                                                $subcategories = $subStmt->fetchAll();
                                                $catHtmlId = 'cat-' . $catId;

                                                // Check if this parent category is active (either directly selected or has active child)
                                                $isParentActive = false;
                                                $hasActiveChild = false;

                                                if ($selectedCategorySlug === $catSlug) {
                                                    $isParentActive = true;
                                                }

                                                // Check if any subcategory is active
                                                foreach ($subcategories as $sub) {
                                                    if ($selectedCategorySlug === $sub['slug']) {
                                                        $hasActiveChild = true;
                                                        break;
                                                    }
                                                }

                                                $parentActiveClass = ($isParentActive || $hasActiveChild) ? 'active' : '';
                                                $showSubcategories = ($isParentActive || $hasActiveChild) ? 'show' : '';
                                                $rotateIcon = ($isParentActive || $hasActiveChild) ? 'rotated' : '';
                                            ?>
                                            <li class="category-parent <?php echo $parentActiveClass; ?>">
                                                <div class="category-title" onclick="toggleCategory('<?php echo $catHtmlId; ?>')">
                                                    <h5><?php echo $catName; ?></h5>
                                                    <i class="fas fa-chevron-down category-icon <?php echo $rotateIcon; ?>" id="<?php echo $catHtmlId; ?>-icon"></i>
                                                </div>
                                                <?php if (count($subcategories) > 0): ?>
                                                <ul class="subcategory-list <?php echo $showSubcategories; ?>" id="<?php echo $catHtmlId; ?>-subcategory">
                                                    <?php foreach ($subcategories as $sub) {
                                                        $subSlug = htmlspecialchars($sub['slug']);
                                                        $subActiveClass = ($selectedCategorySlug === $subSlug) ? 'active' : '';
                                                    ?>
                                                        <li class="subcategory-item <?php echo $subActiveClass; ?>">
                                                            <a href="?category=<?php echo $subSlug; ?>">
                                                                <?php echo htmlspecialchars($sub['name']); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                                <?php endif; ?>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Grid (now left side) -->
                    <div class="col-xl-9 order-xl-1">
                        <?php
                        // Get pagination and sorting parameters early
                        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                        $sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'default';
                        ?>
                        <div class="tf-shop-control">
                            <div class="tf-control-filter d-xl-none">
                                <button type="button" id="filterShop" class="tf-btn-filter">
                                    <span class="icon icon-filter"></span><span class="text">Categories</span>
                                </button>
                            </div>
                            <ul class="tf-control-layout">
                                <li class="tf-view-layout-switch sw-layout-2" data-value-layout="tf-col-2">
                                    <i class="icon-grid-2"></i>
                                </li>
                                <li class="tf-view-layout-switch sw-layout-3 active d-none d-md-flex" data-value-layout="tf-col-3">
                                    <i class="icon-grid-3"></i>
                                </li>
                                <li class="br-line type-vertical"></li>
                                <li class="tf-view-layout-switch sw-layout-list list-layout" data-value-layout="list">
                                    <i class="icon-list"></i>
                                </li>
                            </ul>
                            <div class="tf-control-sorting">
                                <p class="h6 d-none d-lg-block">Sort by:</p>
                                <div class="tf-dropdown-sort" data-bs-toggle="dropdown">
                                    <div class="btn-select">
                                        <span class="text-sort-value">
                                            <?php
                                            $sortLabels = [
                                                'default' => 'Latest',
                                                'best-selling' => 'Best Selling',
                                                'a-z' => 'Alphabetically, A-Z',
                                                'z-a' => 'Alphabetically, Z-A',
                                                'price-low-high' => 'Price, low to high',
                                                'price-high-low' => 'Price, high to low'
                                            ];
                                            echo isset($sortLabels[$sort]) ? $sortLabels[$sort] : 'Latest';
                                            ?>
                                        </span>
                                        <span class="icon icon-caret-down"></span>
                                    </div>
                                    <div class="dropdown-menu">
                                        <div class="select-item <?php echo $sort == 'default' ? 'active' : ''; ?>" data-sort-value="default">
                                            <span class="text-value-item">Latest</span>
                                        </div>
                                        <div class="select-item <?php echo $sort == 'best-selling' ? 'active' : ''; ?>" data-sort-value="best-selling">
                                            <span class="text-value-item">Best Selling</span>
                                        </div>
                                        <div class="select-item <?php echo $sort == 'a-z' ? 'active' : ''; ?>" data-sort-value="a-z">
                                            <span class="text-value-item">Alphabetically, A-Z</span>
                                        </div>
                                        <div class="select-item <?php echo $sort == 'z-a' ? 'active' : ''; ?>" data-sort-value="z-a">
                                            <span class="text-value-item">Alphabetically, Z-A</span>
                                        </div>
                                        <div class="select-item <?php echo $sort == 'price-low-high' ? 'active' : ''; ?>" data-sort-value="price-low-high">
                                            <span class="text-value-item">Price, low to high</span>
                                        </div>
                                        <div class="select-item <?php echo $sort == 'price-high-low' ? 'active' : ''; ?>" data-sort-value="price-high-low">
                                            <span class="text-value-item">Price, high to low</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="wrapper-control-shop gridLayout-wrapper">
                            <?php
                            // Calculate pagination parameters
                            $perPage = PRODUCTS_PER_PAGE;
                            $offset = ($page - 1) * $perPage;

                            // Determine selected category or subcategory
                            $selectedCategorySlug = isset($_GET['category']) ? sanitize($_GET['category']) : null;
                            $categoryIds = [];
                            if ($selectedCategorySlug) {
                                // Find the category by slug
                                $catStmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND is_active = 1");
                                $catStmt->execute([$selectedCategorySlug]);
                                $catRow = $catStmt->fetch();
                                if ($catRow) {
                                    $categoryIds[] = $catRow['id'];
                                    // Also include subcategories if this is a parent
                                    $subStmt = $pdo->prepare("SELECT id FROM categories WHERE parent_id = ? AND is_active = 1");
                                    $subStmt->execute([$catRow['id']]);
                                    $subs = $subStmt->fetchAll();
                                    foreach ($subs as $sub) {
                                        $categoryIds[] = $sub['id'];
                                    }
                                }
                            }

                            // Count total products
                            $countQuery = "SELECT COUNT(*) FROM products WHERE is_active = 1";
                            $countParams = [];
                            if (!empty($categoryIds)) {
                                $in = str_repeat('?,', count($categoryIds) - 1) . '?';
                                $countQuery .= " AND category_id IN ($in)";
                                $countParams = $categoryIds;
                            }
                            $countStmt = $pdo->prepare($countQuery);
                            $countStmt->execute($countParams);
                            $totalProducts = $countStmt->fetchColumn();

                            // Calculate pagination
                            $pagination = paginate($totalProducts, $perPage, $page);

                            // Build product query with sorting
                            $query = "SELECT * FROM products WHERE is_active = 1";
                            $params = [];
                            if (!empty($categoryIds)) {
                                $in = str_repeat('?,', count($categoryIds) - 1) . '?';
                                $query .= " AND category_id IN ($in)";
                                $params = $categoryIds;
                            }

                            // Apply sorting
                            switch ($sort) {
                                case 'price-low-high':
                                    $query .= " ORDER BY COALESCE(sale_price, price) ASC";
                                    break;
                                case 'price-high-low':
                                    $query .= " ORDER BY COALESCE(sale_price, price) DESC";
                                    break;
                                case 'a-z':
                                    $query .= " ORDER BY name ASC";
                                    break;
                                case 'z-a':
                                    $query .= " ORDER BY name DESC";
                                    break;
                                case 'best-selling':
                                    $query .= " ORDER BY views_count DESC, id DESC";
                                    break;
                                default:
                                    $query .= " ORDER BY id DESC";
                            }

                            $query .= " LIMIT ? OFFSET ?";
                            $params[] = $perPage;
                            $params[] = $offset;

                            $stmt = $pdo->prepare($query);
                            $stmt->execute($params);
                            $products = $stmt->fetchAll();

                            // Calculate display range
                            $startRange = $totalProducts > 0 ? $offset + 1 : 0;
                            $endRange = min($offset + $perPage, $totalProducts);
                            ?>

                            <div class="meta-filter-shop">
                                <div class="count-text">
                                    <p>Showing <span class="fw-semibold"><?php echo $startRange; ?>-<?php echo $endRange; ?></span> of <span class="fw-semibold"><?php echo $totalProducts; ?></span> products</p>
                                </div>
                            </div>

                            <!-- Products List View (hidden by default) -->
                            <div class="tf-list-layout wrapper-shop" id="listLayout" style="display: none;"></div>

                            <!-- Products Grid -->
                            <div class="tf-grid-layout tf-col-3 wrapper-shop" id="gridLayout">
                                <?php
                                if (count($products) === 0) {
                                    echo '<div class="col-12"><div class="alert alert-warning">No products found for this category.</div></div>';
                                }
                                foreach ($products as $product) {
                                    $productId = $product['id'];
                                    $productName = htmlspecialchars($product['name']);
                                    $productSlug = htmlspecialchars($product['slug']);
                                    $productPrice = formatPrice($product['price']);
                                    $productSale = $product['sale_price'] ? formatPrice($product['sale_price']) : null;
                                    $img = $product['image_path'] ? $product['image_path'] : 'images/products/default.jpg';
                                ?>
                                <div class="card-product">
                                    <div class="card-product_wrapper">
                                        <a href="product-detail.php?id=<?php echo $productId; ?>" class="product-img">
                                            <img class="lazyload img-product" src="<?php echo $img; ?>" data-src="<?php echo $img; ?>" alt="<?php echo $productName; ?>">
                                            <img class="lazyload img-hover" src="<?php echo $img; ?>" data-src="<?php echo $img; ?>" alt="<?php echo $productName; ?>">
                                        </a>
                                        <div class="list-product-btn">
                                            <a href="product-detail.php?id=<?php echo $productId; ?>" class="box-icon quickview tooltip-left">
                                                <span class="icon icon-view"></span>
                                                <span class="tooltip">Quick view</span>
                                            </a>
                                            <a href="javascript:void(0);" class="box-icon wishlist tooltip-left add-to-wishlist" data-product-id="<?php echo $productId; ?>">
                                                <span class="icon icon-heart"></span>
                                                <span class="tooltip">Add to Wishlist</span>
                                            </a>
                                            <a href="javascript:void(0);" class="box-icon add-to-cart tooltip-left" data-product-id="<?php echo $productId; ?>">
                                                <span class="icon icon-shopping-cart-simple"></span>
                                                <span class="tooltip">Add to Cart</span>
                                            </a>
                                        </div>
                                        <!-- Notification style moved to bottom -->
                                        <?php if ($productSale) { ?>
                                        <div class="on-sale-wrap">
                                            <span class="on-sale-item">Sale</span>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <div class="card-product_info">
                                        <a href="product-detail.php?id=<?php echo $productId; ?>" class="h5 name-product title-product link"><?php echo $productName; ?></a>
                                        <span class="price h6">
                                            <?php if ($productSale) { ?>
                                                <span class="price-new"><?php echo $productSale; ?></span>
                                                <span class="price-old"><?php echo $productPrice; ?></span>
                                            <?php } else { ?>
                                                <span class="price-new"><?php echo $productPrice; ?></span>
                                            <?php } ?>
                                        </span>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>

                            <!-- Pagination -->
                            <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="tf-pagination">
                                <ul class="pagination">
                                    <?php
                                    // Build query string for pagination links
                                    $queryParams = $_GET;
                                    unset($queryParams['page']);
                                    $queryString = http_build_query($queryParams);
                                    $baseUrl = 'shop.php' . ($queryString ? '?' . $queryString . '&' : '?');
                                    ?>

                                    <!-- Previous Button -->
                                    <li class="page-item <?php echo !$pagination['has_previous'] ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo $pagination['has_previous'] ? $baseUrl . 'page=' . ($page - 1) : '#'; ?>" aria-label="Previous">
                                            <span class="icon icon-caret-left"></span>
                                        </a>
                                    </li>

                                    <?php
                                    // Show page numbers with ellipsis
                                    $maxVisible = 5;
                                    $startPage = max(1, $page - floor($maxVisible / 2));
                                    $endPage = min($pagination['total_pages'], $startPage + $maxVisible - 1);

                                    if ($endPage - $startPage < $maxVisible - 1) {
                                        $startPage = max(1, $endPage - $maxVisible + 1);
                                    }

                                    // First page
                                    if ($startPage > 1) {
                                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=1">1</a></li>';
                                        if ($startPage > 2) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                    }

                                    // Page numbers
                                    for ($i = $startPage; $i <= $endPage; $i++) {
                                        $activeClass = $i == $page ? 'active' : '';
                                        echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a></li>';
                                    }

                                    // Last page
                                    if ($endPage < $pagination['total_pages']) {
                                        if ($endPage < $pagination['total_pages'] - 1) {
                                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                        }
                                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . 'page=' . $pagination['total_pages'] . '">' . $pagination['total_pages'] . '</a></li>';
                                    }
                                    ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo $pagination['has_next'] ? $baseUrl . 'page=' . ($page + 1) : '#'; ?>" aria-label="Next">
                                            <span class="icon icon-caret-right"></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Section Product -->

        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Javascript -->
    <?php include 'includes/scripts.php'; ?>

    <script src="js/shop.js"></script>
    <script>
    // Attach Add to Cart handler after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Add to Cart functionality
        document.querySelectorAll('.add-to-cart').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var productId = this.getAttribute('data-product-id');
                // Disable button to prevent double clicks
                btn.classList.add('disabled');
                fetch('ajax/add-to-cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'product_id=' + encodeURIComponent(productId) + '&quantity=1'
                })
                .then(response => response.json())
                .then(data => {
                    btn.classList.remove('disabled');
                    if (data.success) {
                        showShopNotification('Success', 'Added to cart!', 'success');
                        // Update cart count in header
                        if (data.cart_count !== undefined && window.updateCartCount) {
                            window.updateCartCount(data.cart_count);
                        }
                    } else {
                        showShopNotification('Error', data.message, 'error');
                    }
                })
                .catch(() => {
                    btn.classList.remove('disabled');
                    showShopNotification('Error', 'Could not add to cart.', 'error');
                });
            });
        });

        // Add to Wishlist functionality
        document.querySelectorAll('.add-to-wishlist').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var productId = this.getAttribute('data-product-id');
                var icon = this.querySelector('.icon');

                // Disable button to prevent double clicks
                btn.classList.add('disabled');

                fetch('ajax/wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=toggle&product_id=' + encodeURIComponent(productId)
                })
                .then(response => response.json())
                .then(data => {
                    btn.classList.remove('disabled');

                    if (data.success) {
                        if (data.action === 'added') {
                            icon.classList.remove('icon-heart');
                            icon.classList.add('icon-heart-fill');
                            showShopNotification('Success', 'Added to wishlist!', 'success', false);
                        } else {
                            icon.classList.remove('icon-heart-fill');
                            icon.classList.add('icon-heart');
                            showShopNotification('Success', 'Removed from wishlist', 'success', false);
                        }

                        // Update wishlist count in header
                        if (data.wishlist_count !== undefined && window.updateWishlistCount) {
                            window.updateWishlistCount(data.wishlist_count);
                        }
                    } else if (data.redirect) {
                        // User not logged in, redirect to login
                        showShopNotification('Login Required', 'Please login to add items to wishlist', 'error', false);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    } else {
                        showShopNotification('Error', data.error || data.message || 'Could not update wishlist', 'error', false);
                    }
                })
                .catch(() => {
                    btn.classList.remove('disabled');
                    showShopNotification('Error', 'Could not update wishlist.', 'error', false);
                });
            });
        });
    });

    function showShopNotification(title, message, type, updateCart = true) {
        // Only update cart count if updateCart is true
        if (updateCart) {
            // Update cart count in header globally
            if (window.updateMenuCartCount) window.updateMenuCartCount();
            // Update cart total in menu or summary if present
            fetch('ajax/get-cart-total.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && typeof data.total !== 'undefined') {
                        document.querySelectorAll('.cart-total').forEach(function(el) {
                            el.textContent = '₹' + data.total.toLocaleString('en-IN');
                        });
                    }
                });
        }
        var notification = document.createElement('div');
        notification.className = 'shop-notification ' + type;
        notification.innerHTML = '<strong>' + title + '</strong><div>' + message + '</div>';
        document.body.appendChild(notification);
        setTimeout(function() {
            notification.classList.add('show');
        }, 50);
        setTimeout(function() {
            notification.classList.remove('show');
            setTimeout(function() { notification.remove(); }, 300);
        }, 2000);
    }
    </script>
    <style>
    .shop-notification {
        position: fixed;
        top: 30px;
        right: 30px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 16px 24px;
        z-index: 9999;
        opacity: 0;
        transform: translateY(-30px);
        transition: all 0.3s;
        border-left: 4px solid #28a745;
    }
    .shop-notification.show { opacity: 1; transform: translateY(0); }
    .shop-notification.error { border-left-color: #dc3545; }
    .shop-notification.success { border-left-color: #28a745; }
    .shop-notification strong { font-size: 15px; display: block; margin-bottom: 4px; }

    /* Wishlist icon styling */
    .add-to-wishlist .icon-heart-fill {
        color: #dc3545 !important;
    }
    .wishlist-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        min-width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        padding: 0 4px;
    }
    </style>

    <script>
        function toggleCategory(categoryId) {
            const subcategoryList = document.getElementById(categoryId + '-subcategory');
            const icon = document.getElementById(categoryId + '-icon');

            if (subcategoryList.classList.contains('show')) {
                subcategoryList.classList.remove('show');
                icon.classList.remove('rotated');
            } else {
                subcategoryList.classList.add('show');
                icon.classList.add('rotated');
            }
        }

        // Mobile filter toggle
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtn = document.getElementById('filterShop');
            const sidebar = document.querySelector('.canvas-sidebar');
            const closeBtn = document.querySelector('.close-filter');

            if (filterBtn) {
                filterBtn.addEventListener('click', function() {
                    sidebar.classList.add('show');
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                });
            }

            // Handle sort dropdown clicks
            const sortItems = document.querySelectorAll('.select-item[data-sort-value]');
            sortItems.forEach(item => {
                item.addEventListener('click', function() {
                    const sortValue = this.getAttribute('data-sort-value');
                    const url = new URL(window.location.href);
                    url.searchParams.set('sort', sortValue);
                    url.searchParams.delete('page'); // Reset to page 1 when sorting
                    window.location.href = url.toString();
                });
            });
        });
    </script>

    <style>
        /* Wishlist button active state */
        .add-to-wishlist .icon-heart-fill {
            color: var(--primary);
        }

        .add-to-wishlist.active {
            background-color: rgba(158, 103, 71, 0.1);
            border-color: var(--primary);
        }
    </style>

</body>
</html>
