<!-- Header -->
<header class="tf-header header-fix header-abs-2">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 col-3 d-xl-none">
                <a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-mobile-menu" aria-label="Open menu">
                    <span></span>
                </a>
            </div>
            <div class="col-xl-2 col-md-4 col-6 text-center text-xl-start">
                <a href="index.php" class="logo-site justify-content-center justify-content-xl-start">
                    <img src="images/logo/logo.png" alt="Innovative Homesi" class="header-logo">
                </a>
            </div>
            <div class="col-xl-8 d-none d-xl-block">
                <nav class="box-navigation" role="navigation" aria-label="Main navigation">
                    <ul class="box-nav-menu" style="justify-content: center;">
                        <li class="menu-item">
                            <a href="index.php" class="item-link">Home</a>
                        </li>
                        <?php
                        // Fetch all parent categories for menu
                        $menuStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY display_order, name");
                        $menuStmt->execute();
                        $menuCategories = $menuStmt->fetchAll();

                        foreach ($menuCategories as $menuCat) {
                            $menuCatId = $menuCat['id'];
                            $menuCatSlug = htmlspecialchars($menuCat['slug']);
                            $menuCatName = htmlspecialchars($menuCat['name']);

                            // Fetch subcategories for this category
                            $menuSubStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY display_order, name");
                            $menuSubStmt->execute([$menuCatId]);
                            $menuSubcategories = $menuSubStmt->fetchAll();
                        ?>
                        <li class="menu-item position-relative <?php echo count($menuSubcategories) > 0 ? 'has-children' : ''; ?>">
                            <a href="shop.php?category=<?php echo $menuCatSlug; ?>" class="item-link">
                                <?php echo $menuCatName; ?>
                                <?php if (count($menuSubcategories) > 0): ?>
                                    <i class="icon icon-caret-down"></i>
                                <?php endif; ?>
                            </a>
                            <?php if (count($menuSubcategories) > 0): ?>
                            <div class="sub-menu">
                                <ul class="sub-menu_list">
                                    <li class="sub-menu-header">
                                        <a href="shop.php?category=<?php echo $menuCatSlug; ?>" class="sub-menu_link sub-menu-view-all">
                                            All <?php echo $menuCatName; ?>
                                        </a>
                                    </li>
                                    <?php foreach ($menuSubcategories as $menuSub): ?>
                                    <li>
                                        <a href="shop.php?category=<?php echo htmlspecialchars($menuSub['slug']); ?>" class="sub-menu_link">
                                            <?php echo htmlspecialchars($menuSub['name']); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>
            <div class="col-xl-2 col-md-4 col-3">
                <ul class="nav-icon-list">
                    <li class="d-none d-lg-flex">
                        <?php if (isLoggedIn()): ?>
                        <a class="nav-icon-item link" href="account-page.php" aria-label="My account"><i class="icon icon-user"></i></a>
                        <?php else: ?>
                        <a class="nav-icon-item link" href="login.php" aria-label="Login"><i class="icon icon-user"></i></a>
                        <?php endif; ?>
                    </li>
                    <li class="d-none d-sm-flex shop-cart">
                        <a class="nav-icon-item link" href="wishlist.php" aria-label="Wishlist">
                            <i class="icon icon-heart"></i>
                        </a>
                        <span class="count count-box wishlist-count">
                            <?php
                            echo (int)(isLoggedIn() ? getWishlistCount($pdo) : 0);
                            ?>
                        </span>
                    </li>
                    <li class="shop-cart">
                        <a class="nav-icon-item link" href="view-cart.php" aria-label="Shopping cart">
                            <i class="icon icon-shopping-cart-simple"></i>
                        </a>
                        <span class="count count-box">
                            <?php
                            if (!function_exists('getCartCount')) {
                                require_once __DIR__ . '/functions.php';
                            }
                            echo (int)getCartCount($pdo);
                            ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
<!-- /Header -->
