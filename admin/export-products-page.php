<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

// Fetch categories for filtering
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

// Get product counts for display
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$featuredProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn();

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-file-earmark-arrow-down"></i> Export Products
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Export your products to Excel, CSV, or JSON files</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="import-products.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-upload"></i> Import Products
            </a>
            <a href="products.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>
</div>

<?php if ($error_message): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Product Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="content-card" style="text-align: center; padding: 20px;">
            <div style="font-size: 2rem; font-weight: bold; color: var(--admin-primary);"><?= number_format($totalProducts) ?></div>
            <div class="text-muted">Total Products</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card" style="text-align: center; padding: 20px;">
            <div style="font-size: 2rem; font-weight: bold; color: #28a745;"><?= number_format($activeProducts) ?></div>
            <div class="text-muted">Active Products</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card" style="text-align: center; padding: 20px;">
            <div style="font-size: 2rem; font-weight: bold; color: #ffc107;"><?= number_format($featuredProducts) ?></div>
            <div class="text-muted">Featured Products</div>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-funnel"></i> Export Options
        </h3>
    </div>
    <div style="padding: 20px;">
        <form id="exportForm" method="get" action="export-products.php">
            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label for="exportCategory" class="form-label">
                        <i class="bi bi-tag"></i> Category
                    </label>
                    <select class="form-select" id="exportCategory" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="exportStatus" class="form-label">
                        <i class="bi bi-toggle-on"></i> Status
                    </label>
                    <select class="form-select" id="exportStatus" name="status">
                        <option value="all">All Products</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="exportFeatured" class="form-label">
                        <i class="bi bi-star"></i> Featured
                    </label>
                    <select class="form-select" id="exportFeatured" name="featured">
                        <option value="all">All Products</option>
                        <option value="yes">Featured Only</option>
                        <option value="no">Non-Featured Only</option>
                    </select>
                </div>
            </div>

            <!-- Format Selection -->
            <div class="mb-4">
                <label class="form-label"><i class="bi bi-file-earmark"></i> Export Format</label>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100" style="cursor: pointer; border: 2px solid var(--admin-primary);" id="csvCard">
                            <div class="card-body text-center">
                                <input class="form-check-input visually-hidden" type="radio" name="format" id="formatCsv" value="csv" checked>
                                <label class="form-check-label d-block" for="formatCsv" style="cursor: pointer;">
                                    <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #28a745;"></i>
                                    <h5 class="mt-2 mb-1">CSV</h5>
                                    <small class="text-muted">Comma-separated values. Best for spreadsheets and data analysis.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100" style="cursor: pointer; border: 2px solid transparent;" id="excelCard">
                            <div class="card-body text-center">
                                <input class="form-check-input visually-hidden" type="radio" name="format" id="formatExcel" value="excel">
                                <label class="form-check-label d-block" for="formatExcel" style="cursor: pointer;">
                                    <i class="bi bi-file-earmark-spreadsheet" style="font-size: 3rem; color: #217346;"></i>
                                    <h5 class="mt-2 mb-1">Excel</h5>
                                    <small class="text-muted">Microsoft Excel format. Opens directly in Excel.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100" style="cursor: pointer; border: 2px solid transparent;" id="jsonCard">
                            <div class="card-body text-center">
                                <input class="form-check-input visually-hidden" type="radio" name="format" id="formatJson" value="json">
                                <label class="form-check-label d-block" for="formatJson" style="cursor: pointer;">
                                    <i class="bi bi-filetype-json" style="font-size: 3rem; color: #f7df1e;"></i>
                                    <h5 class="mt-2 mb-1">JSON</h5>
                                    <small class="text-muted">JavaScript Object Notation. Best for developers and APIs.</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Button -->
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn-custom btn-outline-custom" onclick="resetFilters()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                </button>
                <button type="submit" class="btn-custom btn-primary-custom">
                    <i class="bi bi-download"></i> Export Products
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Export Information -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-info-circle"></i> Export Information
        </h3>
    </div>
    <div style="padding: 20px;">
        <div class="row">
            <div class="col-md-6">
                <h5 style="color: var(--admin-text); margin-bottom: 12px;">Exported Fields</h5>
                <ul style="margin-bottom: 20px;">
                    <li><strong>name</strong> - Product name</li>
                    <li><strong>sku</strong> - Stock Keeping Unit</li>
                    <li><strong>short_desc</strong> - Short description</li>
                    <li><strong>description</strong> - Full description</li>
                    <li><strong>shipping_returns</strong> - Shipping and returns info</li>
                    <li><strong>price</strong> - Regular price</li>
                    <li><strong>sale_price</strong> - Discounted price</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5 style="color: var(--admin-text); margin-bottom: 12px;">&nbsp;</h5>
                <ul style="margin-bottom: 20px;">
                    <li><strong>cost_price</strong> - Cost price</li>
                    <li><strong>category</strong> - Category name</li>
                    <li><strong>stock_quantity</strong> - Available stock</li>
                    <li><strong>is_featured</strong> - Featured status (1 or 0)</li>
                    <li><strong>is_active</strong> - Active status (1 or 0)</li>
                    <li><strong>image_url</strong> - Product image path</li>
                </ul>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-lightbulb"></i>
            <strong>Tip:</strong> The exported file can be edited and re-imported using the Import Products feature. This is useful for bulk updating products.
        </div>
    </div>
</div>

<script>
// Format card selection
const formatCards = {
    csv: document.getElementById('csvCard'),
    excel: document.getElementById('excelCard'),
    json: document.getElementById('jsonCard')
};

const formatRadios = {
    csv: document.getElementById('formatCsv'),
    excel: document.getElementById('formatExcel'),
    json: document.getElementById('formatJson')
};

function updateCardStyles() {
    Object.keys(formatCards).forEach(format => {
        if (formatRadios[format].checked) {
            formatCards[format].style.border = '2px solid var(--admin-primary)';
            formatCards[format].style.backgroundColor = 'var(--admin-bg-light)';
        } else {
            formatCards[format].style.border = '2px solid transparent';
            formatCards[format].style.backgroundColor = '';
        }
    });
}

// Add click handlers to cards
Object.keys(formatCards).forEach(format => {
    formatCards[format].addEventListener('click', function() {
        formatRadios[format].checked = true;
        updateCardStyles();
    });
});

// Add change handlers to radios
Object.keys(formatRadios).forEach(format => {
    formatRadios[format].addEventListener('change', updateCardStyles);
});

// Reset filters function
function resetFilters() {
    document.getElementById('exportCategory').value = '';
    document.getElementById('exportStatus').value = 'all';
    document.getElementById('exportFeatured').value = 'all';
    document.getElementById('formatCsv').checked = true;
    updateCardStyles();
}

// Initialize card styles
updateCardStyles();
</script>

<?php include 'includes/footer.php'; ?>
