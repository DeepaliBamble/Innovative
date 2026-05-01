<?php
$adminTabs = [
    'dashboard' => [
        'label' => 'Overview',
        'icon' => 'bi-speedometer2',
        'pages' => ['dashboard.php'],
        'href' => 'dashboard.php',
    ],
    'catalog' => [
        'label' => 'Catalog',
        'icon' => 'bi-box-seam',
        'pages' => ['products.php', 'add-product.php', 'edit-product.php', 'manage-variations.php', 'import-products.php', 'export-products-page.php'],
        'href' => 'products.php',
    ],
    'orders' => [
        'label' => 'Orders',
        'icon' => 'bi-cart-check',
        'pages' => ['orders.php', 'view-order.php'],
        'href' => 'orders.php',
    ],
    'customers' => [
        'label' => 'Customers',
        'icon' => 'bi-people',
        'pages' => ['users.php', 'edit-user.php', 'reviews.php'],
        'href' => 'users.php',
    ],
    'content' => [
        'label' => 'Content',
        'icon' => 'bi-newspaper',
        'pages' => ['blogs.php', 'add-blog.php', 'edit-blog.php', 'blog-comments.php', 'gallery.php'],
        'href' => 'blogs.php',
    ],
    'inbox' => [
        'label' => 'Inbox',
        'icon' => 'bi-envelope',
        'pages' => ['contact-messages.php', 'customise-enquiries.php'],
        'href' => 'contact-messages.php',
    ],
    'settings' => [
        'label' => 'Settings',
        'icon' => 'bi-gear',
        'pages' => ['settings.php'],
        'href' => 'settings.php',
    ],
];
?>

<nav class="admin-tabs" aria-label="Admin sections">
    <?php foreach ($adminTabs as $tab): ?>
        <?php $isActive = in_array($current_page, $tab['pages'], true); ?>
        <a href="<?= htmlspecialchars($tab['href']) ?>" class="admin-tab <?= $isActive ? 'active' : '' ?>">
            <i class="bi <?= htmlspecialchars($tab['icon']) ?>"></i>
            <span><?= htmlspecialchars($tab['label']) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
