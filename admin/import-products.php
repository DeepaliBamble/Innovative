<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$errors = [];
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
$import_errors = $_SESSION['import_errors'] ?? [];
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['import_errors']);

// Fetch categories for mapping
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
$categories = $categoriesStmt->fetchAll();

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-file-earmark-arrow-up"></i> Import Products
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Import products from Excel or CSV file</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="export-products-page.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-download"></i> Export Products
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

<?php if (!empty($import_errors)): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle"></i>
    <strong>Import Warnings:</strong>
    <ul class="mb-0 mt-2">
        <?php foreach ($import_errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($success_message): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Import Instructions -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-info-circle"></i> Import Instructions
        </h3>
    </div>
    <div style="padding: 20px;">
        <div class="row">
            <div class="col-md-6">
                <h5 style="color: var(--admin-text); margin-bottom: 12px;">Supported File Formats</h5>
                <ul style="margin-bottom: 20px;">
                    <li><strong>CSV</strong> (.csv) - Comma-separated values</li>
                    <li><strong>Excel</strong> (.xlsx, .xls) - Microsoft Excel spreadsheet</li>
                </ul>

                <h5 style="color: var(--admin-text); margin-bottom: 12px;">Required Columns</h5>
                <ul style="margin-bottom: 20px;">
                    <li><strong>name</strong> - Product name (required)</li>
                    <li><strong>price</strong> - Regular price (required)</li>
                    <li><strong>category</strong> - Category name (required)</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5 style="color: var(--admin-text); margin-bottom: 12px;">Optional Columns</h5>
                <ul style="margin-bottom: 20px;">
                    <li><strong>sku</strong> - Stock Keeping Unit (auto-generated if empty)</li>
                    <li><strong>short_desc</strong> - Short description</li>
                    <li><strong>description</strong> - Full description</li>
                    <li><strong>shipping_returns</strong> - Shipping and returns information</li>
                    <li><strong>sale_price</strong> - Discounted price</li>
                    <li><strong>cost_price</strong> - Cost price</li>
                    <li><strong>stock_quantity</strong> - Available stock (default: 0)</li>
                    <li><strong>is_featured</strong> - Featured status (1 or 0)</li>
                    <li><strong>is_active</strong> - Active status (1 or 0, default: 1)</li>
                    <li><strong>image_url</strong> - Product image URL or path</li>
                </ul>
            </div>
        </div>

        <div class="alert alert-info" style="margin-top: 20px;">
            <i class="bi bi-lightbulb"></i>
            <strong>Tip:</strong> Download the sample template below to ensure your file has the correct format and column names.
        </div>
    </div>
</div>

<!-- Download Template -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-download"></i> Download Template
        </h3>
    </div>
    <div style="padding: 20px;">
        <p style="margin-bottom: 16px; color: var(--admin-text-light);">
            Download a sample template file to use as a reference for importing your products.
        </p>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="download-template.php?format=csv" class="btn-custom btn-outline-custom">
                <i class="bi bi-file-earmark-text"></i> Download CSV Template
            </a>
            <a href="download-template.php?format=excel" class="btn-custom btn-outline-custom">
                <i class="bi bi-file-earmark-spreadsheet"></i> Download Excel Template
            </a>
        </div>
    </div>
</div>

<!-- Upload Form -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-upload"></i> Upload Products File
        </h3>
    </div>
    <div style="padding: 20px;">
        <form action="process-import.php" method="post" enctype="multipart/form-data" id="importForm">
            <div class="mb-3">
                <label for="importFile" class="form-label">Select File</label>
                <input type="file" class="form-control" id="importFile" name="import_file"
                       accept=".csv,.xlsx,.xls" required>
                <small class="text-muted">Accepted formats: CSV, Excel (.xlsx, .xls) | Max size: 10MB</small>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="skipDuplicates" name="skip_duplicates" value="1" checked>
                    <label class="form-check-label" for="skipDuplicates">
                        Skip duplicate SKUs (if a product with the same SKU exists, skip it)
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="updateExisting" name="update_existing" value="1">
                    <label class="form-check-label" for="updateExisting">
                        Update existing products (if SKU exists, update the product instead of skipping)
                    </label>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn-custom btn-primary-custom">
                    <i class="bi bi-upload"></i> Import Products
                </button>
                <button type="reset" class="btn-custom btn-outline-custom">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<!-- File Preview Section (Hidden initially) -->
<div class="content-card" id="previewSection" style="display: none;">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-eye"></i> File Preview
        </h3>
    </div>
    <div style="padding: 20px;">
        <div id="previewContent"></div>
    </div>
</div>

<script>
// Form validation
document.getElementById('importForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('importFile');
    const skipDuplicates = document.getElementById('skipDuplicates');
    const updateExisting = document.getElementById('updateExisting');

    if (fileInput.files.length === 0) {
        e.preventDefault();
        alert('Please select a file to import');
        return false;
    }

    // Validate file size (10MB)
    const maxSize = 10 * 1024 * 1024;
    if (fileInput.files[0].size > maxSize) {
        e.preventDefault();
        alert('File size exceeds 10MB limit');
        return false;
    }

    // Prevent both options from being selected
    if (skipDuplicates.checked && updateExisting.checked) {
        updateExisting.checked = false;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Importing...';
});

// Toggle duplicate handling options
document.getElementById('skipDuplicates').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('updateExisting').checked = false;
    }
});

document.getElementById('updateExisting').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('skipDuplicates').checked = false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
