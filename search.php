<?php
require_once __DIR__ . '/includes/init.php';

// Get search query
$search_query = sanitize($_GET['q'] ?? '');
$products = [];
$total_results = 0;

if (!empty($search_query)) {
    try {
        // Search in products table (name, description, short_desc)
        // and categories table
        $searchTerm = '%' . $search_query . '%';

        $stmt = $pdo->prepare("
            SELECT DISTINCT p.*,
                   c.name as category_name,
                   c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1
            AND (
                p.name LIKE ? OR
                p.description LIKE ? OR
                p.short_desc LIKE ? OR
                p.sku LIKE ? OR
                c.name LIKE ?
            )
            ORDER BY
                CASE
                    WHEN p.name LIKE ? THEN 1
                    WHEN p.short_desc LIKE ? THEN 2
                    WHEN c.name LIKE ? THEN 3
                    ELSE 4
                END,
                p.name ASC
            LIMIT 50
        ");

        $stmt->execute([
            $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm,
            $searchTerm, $searchTerm, $searchTerm
        ]);

        $products = $stmt->fetchAll();
        $total_results = count($products);

    } catch (PDOException $e) {
        error_log('Search Error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<head>
    <meta charset="utf-8">
    <title>Search Results - <?= htmlspecialchars($search_query) ?> | Innovative Homesi</title>
    <meta name="author" content="Innovative Homesi">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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
                    <h1 class="title-page">Search Results</h1>
                    <ul class="breadcrumbs-page">
                        <li><a href="index.php" class="h6 link">Home</a></li>
                        <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                        <li>
                            <h6 class="current-page fw-normal">Search</h6>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- /Page Title -->

        <!-- Search Results -->
        <section class="flat-spacing">
            <div class="container">
                <!-- Search Info -->
                <div class="search-info" style="margin-bottom: 30px; padding: 20px; background: #faf1e5; border-radius: 12px; border-left: 4px solid #9e6747;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <div>
                            <h5 style="margin: 0 0 8px 0; color: #5F615E;">
                                <?php if (!empty($search_query)): ?>
                                    Search results for: <strong style="color: #9e6747;">"<?= htmlspecialchars($search_query) ?>"</strong>
                                <?php else: ?>
                                    Please enter a search term
                                <?php endif; ?>
                            </h5>
                            <p style="margin: 0; color: #8A8C8A;">
                                <?= $total_results ?> product<?= $total_results !== 1 ? 's' : '' ?> found
                            </p>
                        </div>
                        <div>
                            <a href="#search" data-bs-toggle="modal" class="tf-btn animate-btn">
                                <i class="icon icon-magnifying-glass"></i> New Search
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (empty($search_query)): ?>
                    <!-- Empty Search -->
                    <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                        <i class="icon icon-magnifying-glass" style="font-size: 5rem; color: #EDEDED; margin-bottom: 20px;"></i>
                        <h3 style="color: #5F615E; margin-bottom: 12px;">Start Searching</h3>
                        <p style="color: #8A8C8A; margin-bottom: 30px;">Enter a keyword to find your perfect furniture</p>
                        <a href="#search" data-bs-toggle="modal" class="tf-btn animate-btn">
                            <i class="icon icon-magnifying-glass"></i> Open Search
                        </a>
                    </div>

                <?php elseif ($total_results === 0): ?>
                    <!-- No Results -->
                    <div class="no-results" style="text-align: center; padding: 60px 20px;">
                        <i class="icon icon-info" style="font-size: 5rem; color: #EDEDED; margin-bottom: 20px;"></i>
                        <h3 style="color: #5F615E; margin-bottom: 12px;">No Results Found</h3>
                        <p style="color: #8A8C8A; margin-bottom: 30px;">
                            We couldn't find any products matching "<strong><?= htmlspecialchars($search_query) ?></strong>"
                        </p>

                        <div style="max-width: 600px; margin: 0 auto 30px; padding: 20px; background: #fff; border-radius: 12px; text-align: left;">
                            <h6 style="color: #5F615E; margin-bottom: 12px;">Search Tips:</h6>
                            <ul style="color: #8A8C8A; line-height: 1.8;">
                                <li>Check your spelling</li>
                                <li>Try more general keywords (e.g., "sofa" instead of "3-seater L-shape sofa")</li>
                                <li>Try different keywords</li>
                                <li>Browse our categories instead</li>
                            </ul>
                        </div>

                        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                            <a href="#search" data-bs-toggle="modal" class="tf-btn animate-btn">
                                <i class="icon icon-magnifying-glass"></i> Try Again
                            </a>
                            <a href="shop.php" class="tf-btn animate-btn style-line">
                                <i class="icon icon-grid"></i> Browse All Products
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Products Grid -->
                    <div class="grid-layout wrapper-shop" data-grid="grid-4">
                        <?php foreach ($products as $product):
                            $productId = $product['id'];
                            $productName = htmlspecialchars($product['name']);
                            $productSlug = htmlspecialchars($product['slug']);
                            $productPrice = number_format($product['price'], 2);
                            $productSalePrice = $product['sale_price'] ? number_format($product['sale_price'], 2) : null;
                            $productImage = htmlspecialchars($product['image_path'] ?? 'images/products/default-product.jpg');
                            $categoryName = htmlspecialchars($product['category_name'] ?? 'Uncategorized');
                            $isFeatured = $product['is_featured'] == 1;
                        ?>
                        <div class="card-product">
                            <div class="card-product-wrapper">
                                <?php if ($isFeatured): ?>
                                <div class="card-product-label">
                                    <div class="on-sale-wrap">
                                        <div class="on-sale-item">Featured</div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if ($productSalePrice): ?>
                                <div class="card-product-label-right">
                                    <div class="on-sale-wrap">
                                        <div class="on-sale-item">SALE</div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <a href="product-detail.php?slug=<?= $productSlug ?>" class="card-product-img">
                                    <img class="lazyload img-product" data-src="<?= $productImage ?>" src="<?= $productImage ?>" alt="<?= $productName ?>">
                                </a>
                                <div class="list-product-btn absolute-2">
                                    <a href="product-detail.php?slug=<?= $productSlug ?>" class="box-icon wishlist">
                                        <i class="icon icon-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-product-info">
                                <div class="inner-info">
                                    <a href="product-detail.php?slug=<?= $productSlug ?>" class="title link h6"><?= $productName ?></a>
                                    <p class="h7 text-muted" style="margin: 4px 0 8px 0;"><?= $categoryName ?></p>
                                    <span class="price">
                                        <?php if ($productSalePrice): ?>
                                            <span class="fw-semibold">₹<?= $productSalePrice ?></span>
                                            <del class="text-muted ms-2">₹<?= $productPrice ?></del>
                                        <?php else: ?>
                                            ₹<?= $productPrice ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <!-- /Search Results -->

        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Javascript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/swiper-bundle.min.js"></script>
    <script src="js/carousel.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/lazysize.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/count-down.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
