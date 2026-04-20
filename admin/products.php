<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) { redirect('login.php'); }

// Handle success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Get products from database with category information
try {
    $stmt = $pdo->query('
        SELECT
            p.*,
            c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
    ');
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    error_log('Error fetching products: ' . $e->getMessage());
    $error_message = 'Failed to load products: ' . $e->getMessage();
}

// Calculate statistics
$total_products = count($products);
$active_products = count(array_filter($products, fn($p) => $p['is_active'] == 1));
$featured_products = count(array_filter($products, fn($p) => $p['is_featured'] == 1));
$unique_categories = count(array_unique(array_filter(array_column($products, 'category_id'))));

include 'includes/header.php';
?>

<!-- Success/Error Messages -->
<?php if ($success_message): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-box-seam"></i> Product Management
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Manage your furniture products inventory</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="add-product.php" class="btn-custom btn-primary-custom">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
            <a href="import-products.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-file-earmark-arrow-up"></i> Import
            </a>
            <a href="export-products-page.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-file-earmark-arrow-down"></i> Export
            </a>
            <a href="dashboard.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card primary">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Total Products</div>
                <div class="stat-card-value"><?= $total_products ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Active</div>
                <div class="stat-card-value"><?= $active_products ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Featured</div>
                <div class="stat-card-value"><?= $featured_products ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-star"></i>
            </div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Categories</div>
                <div class="stat-card-value"><?= $unique_categories ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-grid"></i>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-list-ul"></i> All Products
        </h3>
    </div>

    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="bi bi-inbox" style="font-size: 4rem; color: var(--admin-text-light); opacity: 0.3;"></i>
            <h3 style="margin-top: 20px; color: var(--admin-text-light);">No Products Yet</h3>
            <p style="color: var(--admin-text-light); margin-bottom: 24px;">Start by adding your first product</p>
            <a href="add-product.php" class="btn-custom btn-primary-custom">
                <i class="bi bi-plus-circle"></i> Add Your First Product
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><strong>#<?= $p['id'] ?></strong></td>
                        <td>
                            <?php if (!empty($p['image_path'])): ?>
                                <img
                                    src="../<?= htmlspecialchars($p['image_path']) ?>"
                                    alt="<?= htmlspecialchars($p['name']) ?>"
                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                    onerror="this.onerror=null;this.outerHTML='<div style=&quot;width:60px;height:60px;background:var(--admin-bg);border-radius:8px;display:flex;align-items:center;justify-content:center;&quot;><i class=&quot;bi bi-image&quot; style=&quot;font-size:1.5rem;color:var(--admin-text-light);&quot;></i></div>';"
                                >
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: var(--admin-bg); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-image" style="font-size: 1.5rem; color: var(--admin-text-light);"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                            <?php if (!empty($p['short_desc'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars(substr($p['short_desc'], 0, 50)) ?><?= strlen($p['short_desc']) > 50 ? '...' : '' ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= htmlspecialchars($p['sku']) ?></code></td>
                        <td>
                            <?php if (!empty($p['category_name'])): ?>
                                <span class="badge-custom badge-info"><?= htmlspecialchars($p['category_name']) ?></span>
                            <?php else: ?>
                                <span class="badge-custom badge-secondary">Uncategorized</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
                                <div>
                                    <strong style="color: #28a745;">₹<?= number_format($p['sale_price'], 2) ?></strong>
                                    <br><small class="text-muted" style="text-decoration: line-through;">₹<?= number_format($p['price'], 2) ?></small>
                                </div>
                            <?php else: ?>
                                <strong>₹<?= number_format($p['price'], 2) ?></strong>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $stock = $p['stock_quantity'] ?? 0;
                            $stock_class = $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger');
                            ?>
                            <span class="badge-custom badge-<?= $stock_class ?>"><?= $stock ?> units</span>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <?php if ($p['is_active']): ?>
                                    <span class="badge-custom badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge-custom badge-secondary">Inactive</span>
                                <?php endif; ?>

                                <?php if ($p['is_featured']): ?>
                                    <span class="badge-custom badge-warning"><i class="bi bi-star-fill"></i> Featured</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="../product-detail.php?id=<?= $p['id'] ?>"
                                   class="btn-custom btn-outline-custom"
                                   style="padding: 6px 12px; font-size: 0.85rem;"
                                   title="View Product"
                                   target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit-product.php?id=<?= $p['id'] ?>"
                                   class="btn-custom btn-outline-custom"
                                   style="padding: 6px 12px; font-size: 0.85rem;"
                                   title="Edit Product">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="manage-variations.php?product_id=<?= $p['id'] ?>"
                                   class="btn-custom"
                                   style="padding: 6px 12px; font-size: 0.85rem; background: #17a2b8; color: white;"
                                   title="Manage Variations">
                                    <i class="bi bi-palette"></i>
                                </a>
                                <a href="delete-product.php?id=<?= $p['id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this product?')"
                                   class="btn-custom"
                                   style="padding: 6px 12px; font-size: 0.85rem; background: #dc3545; color: white;"
                                   title="Delete Product">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
