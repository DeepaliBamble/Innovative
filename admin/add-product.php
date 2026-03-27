<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$errors = [];
$success = false;

// Fetch categories for dropdown
$categoriesStmt = $pdo->query("SELECT id, name, parent_id FROM categories ORDER BY parent_id, display_order, name");
$categories = $categoriesStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $short_desc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $shipping_returns = trim($_POST['shipping_returns'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $cost_price = !empty($_POST['cost_price']) ? floatval($_POST['cost_price']) : null;
    $category_ids = array_map('intval', $_POST['category_ids'] ?? []);
    $category_id = !empty($category_ids) ? $category_ids[0] : 0; // primary = first selected
    $image_path = trim($_POST['image_path'] ?? '');
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Get additional images (comma-separated)
    $additional_images = !empty($_POST['additional_images']) ? explode(',', $_POST['additional_images']) : [];

    // Get attributes
    $attributes = [];
    if (!empty($_POST['attribute_name'])) {
        foreach ($_POST['attribute_name'] as $index => $attr_name) {
            $attr_value = $_POST['attribute_value'][$index] ?? '';
            if (!empty($attr_name) && !empty($attr_value)) {
                $attributes[] = [
                    'name' => trim($attr_name),
                    'value' => trim($attr_value)
                ];
            }
        }
    }

    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($slug)) {
        // Auto-generate slug from name
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
    if (empty($sku)) {
        // Auto-generate SKU
        $sku = 'IH-' . strtoupper(substr(md5($name . time()), 0, 8));
    }
    if ($price <= 0) $errors[] = 'Price must be greater than 0';
    if ($sale_price !== null && $sale_price >= $price) $errors[] = 'Sale price must be less than regular price';
    if (empty($category_ids)) $errors[] = 'Please select at least one category';
    if (empty($image_path)) $errors[] = 'Main product image is required';

    // Check if slug already exists
    if (empty($errors)) {
        $slugCheckStmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $slugCheckStmt->execute([$slug]);
        if ($slugCheckStmt->fetch()) {
            $errors[] = 'A product with this slug already exists';
        }
    }

    if (empty($errors)) {
        // Start transaction
        $pdo->beginTransaction();

        try {
            // Insert product
            $insertSql = "INSERT INTO products
                         (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, cost_price,
                          category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                $name,
                $slug,
                $sku,
                $short_desc,
                $description,
                $shipping_returns,
                $price,
                $sale_price,
                $cost_price,
                $category_id,
                $image_path,
                $stock_quantity,
                $is_featured,
                $is_active
            ]);

            $product_id = $pdo->lastInsertId();

            // Insert product_categories (multi-category)
            $pcStmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)");
            foreach ($category_ids as $i => $cid) {
                $pcStmt->execute([$product_id, $cid, $i === 0 ? 1 : 0]);
            }

            // Insert additional images
            if (!empty($additional_images)) {
                $imageSql = "INSERT INTO product_images (product_id, image_path, is_primary, display_order) VALUES (?, ?, 0, ?)";
                $imageStmt = $pdo->prepare($imageSql);

                foreach ($additional_images as $index => $img_path) {
                    $img_path = trim($img_path);
                    if (!empty($img_path)) {
                        $display_order = $index + 1;
                        $imageStmt->execute([$product_id, $img_path, $display_order]);
                    }
                }
            }

            // Insert attributes
            if (!empty($attributes)) {
                $attrSql = "INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES (?, ?, ?, ?)";
                $attrStmt = $pdo->prepare($attrSql);

                foreach ($attributes as $index => $attr) {
                    $display_order = $index + 1;
                    $attrStmt->execute([$product_id, $attr['name'], $attr['value'], $display_order]);
                }
            }

            // Commit transaction
            $pdo->commit();
            $success = true;

            // Redirect to products list
            $_SESSION['success_message'] = 'Product created successfully!';
            redirect('products.php');

        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollback();
            $errors[] = 'Failed to create product: ' . $e->getMessage();
        }
    }
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h1 class="mb-0">Add New Product</h1>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Product created successfully!
                </div>
            <?php endif; ?>

            <form method="post" id="productForm">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                           placeholder="e.g., Modern L-Shape Sofa">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="slug" class="form-label">Slug (URL)</label>
                                        <input type="text" class="form-control" id="slug" name="slug"
                                               value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>"
                                               placeholder="auto-generated from name">
                                        <small class="text-muted">Leave blank to auto-generate</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="sku" class="form-label">SKU</label>
                                        <input type="text" class="form-control" id="sku" name="sku"
                                               value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>"
                                               placeholder="auto-generated">
                                        <small class="text-muted">Leave blank to auto-generate</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="short_desc" class="form-label">Short Description</label>
                                    <textarea class="form-control" id="short_desc" name="short_desc" rows="2"
                                              placeholder="Brief product description (1-2 sentences)"><?= htmlspecialchars($_POST['short_desc'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Full Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="6"
                                              placeholder="Detailed product description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Product Images -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Product Images</h5>
                            </div>
                            <div class="card-body">
                                <!-- Main Image -->
                                <div class="mb-3">
                                    <label class="form-label">Main Product Image *</label>
                                    <input type="file" id="mainImageUpload" accept="image/*" style="display: none;">
                                    <input type="hidden" name="image_path" id="image_path" value="<?= htmlspecialchars($_POST['image_path'] ?? '') ?>">

                                    <div id="mainImagePlaceholder" style="<?= !empty($_POST['image_path']) ? 'display: none;' : '' ?>">
                                        <div class="border rounded p-4 text-center" style="cursor: pointer; background: #f8f9fa;"
                                             onclick="document.getElementById('mainImageUpload').click()">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                            <p class="mb-0">Click to upload main product image</p>
                                            <small class="text-muted">JPG, PNG, WebP (Max 5MB)</small>
                                        </div>
                                    </div>

                                    <div id="mainImagePreview" style="<?= empty($_POST['image_path']) ? 'display: none;' : '' ?>">
                                        <img id="mainPreviewImg" src="<?= !empty($_POST['image_path']) ? '../' . htmlspecialchars($_POST['image_path']) : '' ?>"
                                             alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; margin-bottom: 10px;">
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="document.getElementById('mainImageUpload').click()">
                                                Change Image
                                            </button>
                                        </div>
                                    </div>

                                    <div id="mainImageProgress" style="display: none; margin-top: 10px;">
                                        <div class="progress">
                                            <div id="mainProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                                 role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Images -->
                                <div class="mb-3">
                                    <label class="form-label">Additional Images (Gallery)</label>
                                    <input type="file" id="additionalImagesUpload" accept="image/*" multiple style="display: none;">
                                    <input type="hidden" name="additional_images" id="additional_images" value="<?= htmlspecialchars($_POST['additional_images'] ?? '') ?>">

                                    <div class="border rounded p-3 text-center" style="cursor: pointer; background: #f8f9fa;"
                                         onclick="document.getElementById('additionalImagesUpload').click()">
                                        <i class="fas fa-images fa-2x text-muted mb-2"></i>
                                        <p class="mb-0">Click to upload additional images</p>
                                        <small class="text-muted">Select multiple images</small>
                                    </div>

                                    <div id="additionalImagesContainer" class="row g-2 mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Attributes/Specifications -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Product Specifications</h5>
                                <button type="button" class="btn btn-sm btn-primary" id="addAttributeBtn">
                                    <i class="fas fa-plus"></i> Add Specification
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="attributesContainer">
                                    <p class="text-muted">Add specifications like Dimensions, Material, Color, Weight, etc.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Pricing -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Pricing</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Regular Price (₹) *</label>
                                    <input type="number" class="form-control" id="price" name="price" required
                                           step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                                           placeholder="0.00">
                                </div>

                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">Sale Price (₹)</label>
                                    <input type="number" class="form-control" id="sale_price" name="sale_price"
                                           step="0.01" min="0" value="<?= htmlspecialchars($_POST['sale_price'] ?? '') ?>"
                                           placeholder="Leave blank if no sale">
                                    <small class="text-muted">Optional discount price</small>
                                    <div id="discountBadge" class="mt-2" style="display: none;">
                                        <span class="badge bg-danger fs-6">
                                            <i class="fas fa-tag me-1"></i>
                                            <span id="discountPercent">0</span>% OFF
                                        </span>
                                        <small class="text-success ms-2">Customer saves ₹<span id="savingsAmount">0</span></small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cost_price" class="form-label">Cost Price (₹)</label>
                                    <input type="number" class="form-control" id="cost_price" name="cost_price"
                                           step="0.01" min="0" value="<?= htmlspecialchars($_POST['cost_price'] ?? '') ?>"
                                           placeholder="Internal cost">
                                    <small class="text-muted">For profit calculation (not shown to customers)</small>
                                    <div id="profitBadge" class="mt-2" style="display: none;">
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Profit: ₹<span id="profitAmount">0</span>
                                            (<span id="profitPercent">0</span>%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Category</h5>
                            </div>
                            <div class="card-body">
                                <label class="form-label">Product Categories * <small class="text-muted">(select one or more; first selected = primary)</small></label>
                                <?php
                                $parentCategories = [];
                                $childCategories = [];
                                foreach ($categories as $cat) {
                                    if ($cat['parent_id'] === null || $cat['parent_id'] == 0) {
                                        $parentCategories[] = $cat;
                                    } else {
                                        $childCategories[$cat['parent_id']][] = $cat;
                                    }
                                }
                                $selectedCategoryIds = array_map('intval', $_POST['category_ids'] ?? []);
                                foreach ($parentCategories as $parent):
                                ?>
                                <div class="mb-1">
                                    <strong><?= htmlspecialchars($parent['name']) ?></strong>
                                    <?php if (isset($childCategories[$parent['id']])): ?>
                                    <div class="ms-3 mt-1">
                                        <?php foreach ($childCategories[$parent['id']] as $child): ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox"
                                                   name="category_ids[]" value="<?= $child['id'] ?>"
                                                   id="cat_<?= $child['id'] ?>"
                                                   <?= in_array($child['id'], $selectedCategoryIds) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="cat_<?= $child['id'] ?>">
                                                <?= htmlspecialchars($child['name']) ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="ms-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox"
                                                   name="category_ids[]" value="<?= $parent['id'] ?>"
                                                   id="cat_<?= $parent['id'] ?>"
                                                   <?= in_array($parent['id'], $selectedCategoryIds) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="cat_<?= $parent['id'] ?>">
                                                <?= htmlspecialchars($parent['name']) ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <div id="categoryError" class="text-danger small mt-1" style="display:none;">Please select at least one category.</div>
                            </div>
                        </div>

                        <!-- Inventory -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Inventory</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                                           min="0" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '0') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Product Status -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Status</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Determine checkbox states - if form was submitted, use POST values, otherwise use defaults
                                // For new products: is_featured defaults to unchecked, is_active defaults to checked
                                $is_featured_checked = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['is_featured']) : false;
                                $is_active_checked = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['is_active']) : true;
                                ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                        <?= $is_featured_checked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_featured">
                                        Featured Product
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        <?= $is_active_checked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active (Published)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-save"></i> Create Product
                                </button>
                                <a href="products.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validate at least one category is selected before submit
document.getElementById('productForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.category-checkbox:checked');
    if (checked.length === 0) {
        e.preventDefault();
        document.getElementById('categoryError').style.display = 'block';
        document.getElementById('categoryError').scrollIntoView({behavior: 'smooth'});
    } else {
        document.getElementById('categoryError').style.display = 'none';
    }
});

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
});

// Real-time discount and profit calculator
const priceInput = document.getElementById('price');
const salePriceInput = document.getElementById('sale_price');
const costPriceInput = document.getElementById('cost_price');
const discountBadge = document.getElementById('discountBadge');
const discountPercent = document.getElementById('discountPercent');
const savingsAmount = document.getElementById('savingsAmount');
const profitBadge = document.getElementById('profitBadge');
const profitAmount = document.getElementById('profitAmount');
const profitPercent = document.getElementById('profitPercent');

function calculatePricing() {
    const price = parseFloat(priceInput.value) || 0;
    const salePrice = parseFloat(salePriceInput.value) || 0;
    const costPrice = parseFloat(costPriceInput.value) || 0;

    // Calculate discount
    if (price > 0 && salePrice > 0 && salePrice < price) {
        const discount = Math.round(((price - salePrice) / price) * 100);
        const savings = price - salePrice;
        discountPercent.textContent = discount;
        savingsAmount.textContent = savings.toLocaleString('en-IN');
        discountBadge.style.display = 'block';
    } else {
        discountBadge.style.display = 'none';
    }

    // Calculate profit (based on sale price if available, otherwise regular price)
    const sellingPrice = (salePrice > 0 && salePrice < price) ? salePrice : price;
    if (sellingPrice > 0 && costPrice > 0) {
        const profit = sellingPrice - costPrice;
        const profitPercentVal = Math.round((profit / costPrice) * 100);
        profitAmount.textContent = profit.toLocaleString('en-IN');
        profitPercent.textContent = profitPercentVal;
        profitBadge.style.display = 'block';

        // Change badge color based on profit
        const badge = profitBadge.querySelector('.badge');
        if (profit < 0) {
            badge.className = 'badge bg-danger fs-6';
            badge.innerHTML = `<i class="fas fa-chart-line-down me-1"></i> Loss: ₹${Math.abs(profit).toLocaleString('en-IN')} (${Math.abs(profitPercentVal)}%)`;
        } else {
            badge.className = 'badge bg-success fs-6';
            badge.innerHTML = `<i class="fas fa-chart-line me-1"></i> Profit: ₹${profit.toLocaleString('en-IN')} (${profitPercentVal}%)`;
        }
    } else {
        profitBadge.style.display = 'none';
    }
}

priceInput.addEventListener('input', calculatePricing);
salePriceInput.addEventListener('input', calculatePricing);
costPriceInput.addEventListener('input', calculatePricing);

// Calculate on page load if values exist
calculatePricing();

// Main image upload
const mainImageUpload = document.getElementById('mainImageUpload');
const mainImagePath = document.getElementById('image_path');
const mainPreviewImg = document.getElementById('mainPreviewImg');
const mainImagePlaceholder = document.getElementById('mainImagePlaceholder');
const mainImagePreview = document.getElementById('mainImagePreview');
const mainImageProgress = document.getElementById('mainImageProgress');
const mainProgressBar = document.getElementById('mainProgressBar');

mainImageUpload.addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        handleImageUpload(e.target.files[0], true);
    }
});

// Additional images upload
const additionalImagesUpload = document.getElementById('additionalImagesUpload');
const additionalImages = document.getElementById('additional_images');
const additionalImagesContainer = document.getElementById('additionalImagesContainer');
let additionalImagesList = [];

additionalImagesUpload.addEventListener('change', function(e) {
    Array.from(e.target.files).forEach(file => {
        handleImageUpload(file, false);
    });
});

function handleImageUpload(file, isMain) {
    console.log('Starting upload for:', file.name, 'Size:', file.size, 'Type:', file.type);

    // Validate file
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
        console.error('Invalid file type:', file.type);
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('File size exceeds 5MB limit.');
        console.error('File too large:', file.size);
        return;
    }

    // Show progress
    if (isMain) {
        mainImagePlaceholder.style.display = 'none';
        mainImagePreview.style.display = 'none';
        mainImageProgress.style.display = 'block';
    }

    // Upload file
    const formData = new FormData();
    formData.append('image', file);

    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable && isMain) {
            const percentComplete = (e.loaded / e.total) * 100;
            mainProgressBar.style.width = percentComplete + '%';
            console.log('Upload progress:', percentComplete.toFixed(2) + '%');
        }
    });

    xhr.addEventListener('load', function() {
        console.log('Upload response status:', xhr.status);
        console.log('Upload response text:', xhr.responseText);

        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Parsed response:', response);

                if (response.success) {
                    console.log('Upload successful! Path:', response.path);
                    if (isMain) {
                        mainPreviewImg.src = '../' + response.path;
                        mainImagePath.value = response.path;
                        mainImageProgress.style.display = 'none';
                        mainImagePreview.style.display = 'block';
                    } else {
                        // Add to additional images
                        additionalImagesList.push(response.path);
                        additionalImages.value = additionalImagesList.join(',');
                        displayAdditionalImages();
                    }
                } else {
                    console.error('Upload failed:', response.error);
                    const errorMsg = response.error + (response.debug ? '\n\nDebug info:\n' + JSON.stringify(response.debug, null, 2) : '');
                    alert('Upload failed: ' + errorMsg);
                    if (isMain) {
                        mainImageProgress.style.display = 'none';
                        mainImagePlaceholder.style.display = 'block';
                    }
                }
            } catch (e) {
                console.error('Failed to parse response:', e);
                alert('Upload failed: Invalid server response');
                if (isMain) {
                    mainImageProgress.style.display = 'none';
                    mainImagePlaceholder.style.display = 'block';
                }
            }
        } else {
            console.error('HTTP error:', xhr.status);
            alert('Upload failed with HTTP status: ' + xhr.status);
            if (isMain) {
                mainImageProgress.style.display = 'none';
                mainImagePlaceholder.style.display = 'block';
            }
        }
    });

    xhr.addEventListener('error', function() {
        console.error('XHR error occurred');
        alert('Upload failed. Please try again.');
        if (isMain) {
            mainImageProgress.style.display = 'none';
            mainImagePlaceholder.style.display = 'block';
        }
    });

    console.log('Sending request to: upload-image.php');
    xhr.open('POST', 'upload-image.php');
    xhr.send(formData);
}

function displayAdditionalImages() {
    additionalImagesContainer.innerHTML = '';
    additionalImagesList.forEach((path, index) => {
        const col = document.createElement('div');
        col.className = 'col-4';
        col.innerHTML = `
            <div class="position-relative">
                <img src="../${path}" class="img-thumbnail" style="width: 100%; height: 120px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                        onclick="removeAdditionalImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        additionalImagesContainer.appendChild(col);
    });
}

function removeAdditionalImage(index) {
    additionalImagesList.splice(index, 1);
    additionalImages.value = additionalImagesList.join(',');
    displayAdditionalImages();
}

// Attributes management
const attributesContainer = document.getElementById('attributesContainer');
const addAttributeBtn = document.getElementById('addAttributeBtn');
let attributeCount = 0;

addAttributeBtn.addEventListener('click', function() {
    addAttributeRow();
});

function addAttributeRow(name = '', value = '') {
    const row = document.createElement('div');
    row.className = 'row mb-2 attribute-row';
    row.innerHTML = `
        <div class="col-5">
            <input type="text" class="form-control" name="attribute_name[]" placeholder="e.g., Dimensions" value="${name}">
        </div>
        <div class="col-5">
            <input type="text" class="form-control" name="attribute_value[]" placeholder="e.g., 200cm x 90cm x 85cm" value="${value}">
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.attribute-row').remove()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    attributesContainer.appendChild(row);
    attributeCount++;

    // Remove the "no attributes" message if it exists
    const noAttrMsg = attributesContainer.querySelector('p.text-muted');
    if (noAttrMsg) {
        noAttrMsg.remove();
    }
}

// Add common furniture attributes on load
if (attributeCount === 0) {
    const commonAttributes = [
        {name: 'Dimensions (L x W x H)', value: ''},
        {name: 'Material', value: ''},
        {name: 'Color', value: ''},
        {name: 'Weight', value: ''}
    ];

    commonAttributes.forEach(attr => addAttributeRow(attr.name, attr.value));
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
