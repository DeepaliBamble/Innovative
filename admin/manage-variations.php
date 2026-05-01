<?php
/**
 * Admin - Manage Product Variations
 * Interface for adding and managing color, size, material, and pattern variations
 */

require_once __DIR__ . '/../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Manage Product Variations';

// Get product ID
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id <= 0) {
    $_SESSION['error_message'] = 'Invalid product ID';
    header('Location: products.php');
    exit;
}

// Fetch product details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_message'] = 'Product not found';
        header('Location: products.php');
        exit;
    }

    // Check if variation tables exist
    $tables_check = $pdo->query("SHOW TABLES LIKE 'product_variations'")->rowCount();

    if ($tables_check === 0) {
        $_SESSION['error_message'] = 'Variation tables not found. Please run the product_variations.sql file first.';
        header('Location: products.php');
        exit;
    }

    // Fetch available variation types for this category
    $stmt = $pdo->prepare("
        SELECT vt.id, vt.name, vt.display_name, vt.input_type, cvt.is_required
        FROM variation_types vt
        LEFT JOIN category_variation_types cvt ON vt.id = cvt.variation_type_id AND cvt.category_id = ?
        WHERE vt.is_active = 1
        ORDER BY vt.display_order ASC
    ");
    $stmt->execute([$product['category_id']]);
    $variation_types = $stmt->fetchAll();

    // Fetch existing variations for this product
    $stmt = $pdo->prepare("
        SELECT
            pv.*,
            vt.name as type_name,
            vt.display_name as type_display_name,
            vt.input_type
        FROM product_variations pv
        JOIN variation_types vt ON pv.variation_type_id = vt.id
        WHERE pv.product_id = ?
        ORDER BY vt.display_order ASC, pv.display_order ASC
    ");
    $stmt->execute([$product_id]);
    $existing_variations = $stmt->fetchAll();

    // Group by type
    $variations_by_type = [];
    foreach ($existing_variations as $variation) {
        $type_id = $variation['variation_type_id'];
        if (!isset($variations_by_type[$type_id])) {
            $variations_by_type[$type_id] = [
                'type_info' => [
                    'id' => $type_id,
                    'name' => $variation['type_name'],
                    'display_name' => $variation['type_display_name'],
                    'input_type' => $variation['input_type']
                ],
                'variations' => []
            ];
        }
        $variations_by_type[$type_id]['variations'][] = $variation;
    }

} catch (PDOException $e) {
    error_log("Database error in manage-variations.php: " . $e->getMessage());
    $_SESSION['error_message'] = 'Database error occurred';
    header('Location: products.php');
    exit;
}

include 'includes/header.php';
?>

<style>
/* Admin Theme Color Scheme */
:root {
    --primary-color: #9e6747;
    --primary-hover: #7d5238;
    --primary-light: #b8835f;
    --secondary-color: #d89d43;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --light-bg: #F5F5F5;
    --border-color: #EDEDED;
    --shadow-sm: 0 2px 12px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.15);
}

/* Product Info Card Enhancement */
.wg-box.mb-20 {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
    color: white;
    border: none;
    box-shadow: var(--shadow-lg);
    padding: 25px !important;
}

.wg-box.mb-20 h5 {
    color: white !important;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 1.5rem;
}

.wg-box.mb-20 .text-muted {
    color: rgba(255, 255, 255, 0.95) !important;
    font-size: 0.95rem;
}

/* Variation Card Styling */
.variation-card {
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 25px;
    background: white;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.variation-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.variation-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.variation-card h5 {
    color: #1e293b;
    font-weight: 600;
    font-size: 1.25rem;
    margin-bottom: 0;
}

/* Add Variation Button */
.btn-primary.btn-sm {
    background: var(--primary-color) !important;
    border: none !important;
    padding: 10px 20px !important;
    border-radius: 8px !important;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
}

.btn-primary.btn-sm:hover {
    background: var(--primary-hover) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(99, 102, 241, 0.4);
}

/* Variation Item Cards */
.variation-item {
    display: flex;
    align-items: center;
    padding: 18px;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 12px;
    border: 2px solid var(--border-color);
    transition: all 0.3s ease;
}

.variation-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    transform: translateX(4px);
}

/* Variation Preview */
.variation-preview {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    margin-right: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid var(--border-color);
    background: white;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.variation-item:hover .variation-preview {
    transform: scale(1.1);
    border-color: var(--primary-color);
}

.variation-preview.color-preview {
    border-radius: 50%;
}

.variation-preview.color-preview > div {
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
}

.variation-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 7px;
}

.variation-preview .text-muted {
    font-weight: 700;
    font-size: 1.1rem;
    color: #94a3b8 !important;
}

/* Variation Info */
.variation-info strong {
    color: #1e293b;
    font-size: 1.05rem;
    font-weight: 600;
}

.variation-info .small {
    color: #64748b;
    font-size: 0.9rem;
    margin-top: 4px;
}

/* Stock Badges */
.stock-badge {
    font-size: 11px;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.badge.bg-success {
    background: var(--success-color) !important;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
}

.badge.bg-danger {
    background: var(--danger-color) !important;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.badge.bg-primary {
    background: var(--primary-color) !important;
    box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
}

.badge.bg-secondary {
    background: #94a3b8 !important;
}

.badge.bg-warning {
    background: var(--warning-color) !important;
    color: white !important;
}

/* Action Buttons */
.variation-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.variation-actions .btn-sm {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.variation-actions .btn-warning {
    background: #fbbf24 !important;
    border-color: #fbbf24 !important;
    color: white !important;
}

.variation-actions .btn-warning:hover {
    background: #f59e0b !important;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(251, 191, 36, 0.4);
}

.variation-actions .btn-danger {
    background: var(--danger-color) !important;
    border-color: var(--danger-color) !important;
}

.variation-actions .btn-danger:hover {
    background: #dc2626 !important;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.4);
}

/* Add Variation Form */
.add-variation-form {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    padding: 25px;
    border-radius: 12px;
    margin-top: 15px;
    display: none;
    border: 2px solid var(--border-color);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.add-variation-form.show {
    display: block;
}

.add-variation-form .form-label {
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.add-variation-form .form-control {
    border: 2px solid var(--border-color);
    border-radius: 8px;
    padding: 10px 14px;
    transition: all 0.3s ease;
}

.add-variation-form .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    outline: none;
}

/* Color Input Wrapper */
.color-input-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.color-input-wrapper input[type="color"] {
    width: 70px;
    height: 45px;
    border: 3px solid var(--border-color);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.color-input-wrapper input[type="color"]:hover {
    border-color: var(--primary-color);
    transform: scale(1.05);
}

/* Form Buttons */
.add-variation-form .btn-success {
    background: var(--success-color) !important;
    border: none !important;
    padding: 12px 24px !important;
    border-radius: 8px !important;
    font-weight: 600;
    transition: all 0.3s ease;
}

.add-variation-form .btn-success:hover {
    background: #059669 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
}

.add-variation-form .btn-secondary {
    background: #64748b !important;
    border: none !important;
    padding: 12px 24px !important;
    border-radius: 8px !important;
    font-weight: 600;
}

.add-variation-form .btn-secondary:hover {
    background: #475569 !important;
}

/* Empty State */
.variations-list p.text-muted {
    padding: 40px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    color: #94a3b8;
    text-align: center;
    font-size: 0.95rem;
}

/* No Variation Types Message */
.wg-box.text-center.py-5 {
    background: white;
    border: 2px dashed var(--border-color);
    border-radius: 16px;
    padding: 50px !important;
}

.wg-box.text-center.py-5 .icon-alert-circle {
    color: var(--warning-color);
}

/* Back Button */
.btn-secondary {
    background: #64748b !important;
    border: none !important;
    padding: 12px 24px !important;
    border-radius: 8px !important;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #475569 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(100, 116, 139, 0.3);
}

/* Alert Messages */
.alert {
    border-radius: 12px;
    border: none;
    padding: 16px 20px;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.alert-danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
}

/* Small Text */
small.text-muted {
    color: #8A8C8A !important;
    font-size: 0.85rem;
}

/* Required asterisks styling */
.text-danger-required {
    color: var(--danger-color) !important;
    font-weight: 600;
    margin-left: 2px;
}

/* Hide old text-danger class (if any remain) */
.text-danger:not(.text-danger-required) {
    display: none !important;
}

/* Responsive */
@media (max-width: 768px) {
    .variation-card {
        padding: 20px;
    }

    .variation-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .variation-actions {
        width: 100%;
        justify-content: flex-end;
    }

    .variation-preview {
        margin-right: 0;
        margin-bottom: 10px;
    }
}

/* Loading Animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.icon-loader {
    animation: spin 1s linear infinite;
}

/* Breadcrumb Styling */
.breadcrumbs {
    background: white;
    padding: 12px 20px;
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.breadcrumbs li {
    display: inline-flex;
    align-items: center;
}

.breadcrumbs a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 4px 10px;
    border-radius: 6px;
}

.breadcrumbs a:hover {
    background: rgba(158, 103, 71, 0.1);
    color: var(--primary-hover);
}

.breadcrumbs .text-tiny {
    font-size: 0.875rem;
    font-weight: 500;
}

.breadcrumbs li:last-child .text-tiny {
    color: #5F615E;
    font-weight: 600;
}

.breadcrumbs .icon-chevron-right {
    color: #8A8C8A;
    font-size: 12px;
    margin: 0 8px;
}

/* Page Header Section */
.flex.items-center.flex-wrap.justify-between.gap20.mb-27 {
    background: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--primary-color);
    margin-bottom: 25px;
}

.flex.items-center.flex-wrap.justify-between.gap20.mb-27 h3 {
    color: var(--primary-color);
    font-weight: 600;
    margin: 0;
    font-size: 1.75rem;
}
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3><?php echo htmlspecialchars($page_title); ?></h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="dashboard.php"><div class="text-tiny">Dashboard</div></a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li>
                        <a href="products.php"><div class="text-tiny">Products</div></a>
                    </li>
                    <li><i class="icon-chevron-right"></i></li>
                    <li><div class="text-tiny">Manage Variations</div></li>
                </ul>
            </div>

            <!-- Product Info Card -->
            <div class="wg-box mb-20">
                <h5>Product: <?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="text-muted mb-0">
                    Category: <?php echo htmlspecialchars($product['category_name']); ?> |
                    SKU: <?php echo htmlspecialchars($product['sku']); ?> |
                    Base Price: ₹<?php echo number_format($product['price'], 2); ?>
                </p>
            </div>

            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Variation Types -->
            <?php foreach ($variation_types as $type): ?>
                <div class="wg-box variation-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <?php echo htmlspecialchars($type['display_name']); ?>
                            <?php if ($type['is_required']): ?>
                                <span class="badge bg-warning text-dark">Required</span>
                            <?php endif; ?>
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="toggleAddForm(<?php echo $type['id']; ?>)">
                            <i class="icon-plus"></i> Add <?php echo htmlspecialchars($type['display_name']); ?>
                        </button>
                    </div>

                    <!-- Add Variation Form -->
                    <div id="addForm<?php echo $type['id']; ?>" class="add-variation-form">
                        <form action="ajax/save-variation.php" method="POST" enctype="multipart/form-data" class="variation-form">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="variation_type_id" value="<?php echo $type['id']; ?>">
                            <input type="hidden" name="input_type" value="<?php echo $type['input_type']; ?>">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Variation Name <span class="text-danger-required">*</span></label>
                                        <input type="text" name="variation_name" class="form-control" required placeholder="e.g., Red, Large, Oak Wood">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Variation Value <span class="text-danger-required">*</span></label>
                                        <input type="text" name="variation_value" class="form-control" required placeholder="Display value">
                                    </div>
                                </div>

                                <?php if ($type['input_type'] === 'color'): ?>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Color Code </label>
                                        <div class="color-input-wrapper">
                                            <input type="color" name="color_code" class="form-control" value="#000000">
                                            <input type="text" name="color_code_text" class="form-control" placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$">
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($type['input_type'] === 'image'): ?>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Swatch Image</label>
                                        <input type="file" name="variation_image" class="form-control" accept="image/*">
                                        <small class="text-muted">Optional: Upload an image for this variation</small>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Price Adjustment</label>
                                        <input type="number" name="price_adjustment" class="form-control" step="0.01" value="0.00" placeholder="0.00">
                                        <small class="text-muted">Add/subtract from base price</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Stock Quantity <span class="text-danger-required">*</span></label>
                                        <input type="number" name="stock_quantity" class="form-control" required min="0" value="0">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">SKU Suffix</label>
                                        <input type="text" name="sku_suffix" class="form-control" placeholder="e.g., -RED">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Display Order</label>
                                        <input type="number" name="display_order" class="form-control" min="0" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success"><i class="icon-check"></i> Save Variation</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleAddForm(<?php echo $type['id']; ?>)">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Existing Variations -->
                    <div class="variations-list mt-3">
                        <?php if (isset($variations_by_type[$type['id']])): ?>
                            <?php foreach ($variations_by_type[$type['id']]['variations'] as $variation): ?>
                                <div class="variation-item">
                                    <div class="variation-preview <?php echo $type['input_type'] === 'color' ? 'color-preview' : ''; ?>">
                                        <?php if ($type['input_type'] === 'color' && $variation['color_code']): ?>
                                            <div style="width: 100%; height: 100%; background-color: <?php echo htmlspecialchars($variation['color_code']); ?>; border-radius: inherit;"></div>
                                        <?php elseif ($type['input_type'] === 'image' && $variation['image_path']): ?>
                                            <img src="<?php echo htmlspecialchars('../' . $variation['image_path']); ?>" alt="<?php echo htmlspecialchars($variation['variation_value']); ?>">
                                        <?php else: ?>
                                            <span class="text-muted"><?php echo strtoupper(substr($variation['variation_value'], 0, 2)); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="variation-info">
                                        <strong><?php echo htmlspecialchars($variation['variation_value']); ?></strong>
                                        <div class="text-muted small">
                                            <?php if ($variation['price_adjustment'] != 0): ?>
                                                Price: <?php echo $variation['price_adjustment'] > 0 ? '+' : ''; ?>₹<?php echo number_format($variation['price_adjustment'], 2); ?> |
                                            <?php endif; ?>
                                            Stock: <?php echo $variation['stock_quantity']; ?>
                                            <?php if ($variation['sku_suffix']): ?>
                                                | SKU: <?php echo htmlspecialchars($product['sku'] . $variation['sku_suffix']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="variation-actions">
                                        <?php if ($variation['stock_quantity'] > 0): ?>
                                            <span class="badge bg-success stock-badge">In Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger stock-badge">Out of Stock</span>
                                        <?php endif; ?>

                                        <?php if ($variation['is_active']): ?>
                                            <span class="badge bg-primary stock-badge">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary stock-badge">Inactive</span>
                                        <?php endif; ?>

                                        <button type="button" class="btn btn-sm btn-warning" onclick="editVariation(<?php echo $variation['id']; ?>)">
                                            <i class="icon-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariation(<?php echo $variation['id']; ?>, '<?php echo htmlspecialchars($variation['variation_value']); ?>')">
                                            <i class="icon-trash-2"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No variations added yet. Click "Add <?php echo htmlspecialchars($type['display_name']); ?>" to get started.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($variation_types)): ?>
                <div class="wg-box text-center py-5">
                    <i class="icon-alert-circle" style="font-size: 48px; color: #ffc107;"></i>
                    <h5 class="mt-3">No Variation Types Available</h5>
                    <p class="text-muted">
                        This product's category doesn't have any variation types assigned yet.<br>
                        Please assign variation types to the category first.
                    </p>
                </div>
            <?php endif; ?>

            <div class="mt-4">
                <a href="products.php" class="btn btn-secondary"><i class="icon-arrow-left"></i> Back to Products</a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAddForm(typeId) {
    const form = document.getElementById('addForm' + typeId);
    form.classList.toggle('show');

    // Reset form if hiding
    if (!form.classList.contains('show')) {
        form.querySelector('form').reset();
    }
}

function deleteVariation(variationId, variationValue) {
    if (!confirm(`Are you sure you want to delete the variation "${variationValue}"?`)) {
        return;
    }

    fetch('ajax/delete-variation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `variation_id=${variationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the variation');
    });
}

// Handle form submissions via AJAX
document.querySelectorAll('.variation-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="icon-loader"></i> Saving...';
        submitBtn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the variation');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

// Sync color picker with text input
document.querySelectorAll('input[name="color_code"]').forEach(colorPicker => {
    const textInput = colorPicker.nextElementSibling;

    colorPicker.addEventListener('input', function() {
        textInput.value = this.value.toUpperCase();
    });

    textInput.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorPicker.value = this.value;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
