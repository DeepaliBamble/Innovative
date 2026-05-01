<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$galleryHasCategoryId = false;
try {
    $columnStmt = $pdo->query("SHOW COLUMNS FROM gallery LIKE 'category_id'");
    $galleryHasCategoryId = (bool) $columnStmt->fetch();
} catch (PDOException $e) {
    error_log('Gallery schema check failed: ' . $e->getMessage());
}

$galleryCategoryOptions = [
    'all' => 'All',
    'sofas' => 'Sofas',
    'seating' => 'Seating',
    'dining' => 'Dining',
    'bedroom' => 'Bedroom',
    'decor' => 'Decor',
    'workspace' => 'Workspace',
];

if ($galleryHasCategoryId) {
    $categoryStmt = $pdo->query("SELECT id, name FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY display_order, name");
    $galleryCategoryOptions = [];
    foreach ($categoryStmt->fetchAll() as $cat) {
        $galleryCategoryOptions[(int) $cat['id']] = $cat['name'];
    }
}

function galleryCategoryLabel($value, array $options) {
    return $options[$value] ?? $options[(string) $value] ?? 'Uncategorized';
}

// Handle add new gallery image
if (isset($_POST['add_image'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_path = $_POST['image_path'] ?? '';
    $category = $_POST['category'] ?? ($galleryHasCategoryId ? 0 : 'all');
    $display_order = (int)($_POST['display_order'] ?? 0);

    try {
        if ($galleryHasCategoryId) {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path, category_id, display_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $image_path, (int) $category, $display_order]);
        } else {
            $category = array_key_exists($category, $galleryCategoryOptions) ? $category : 'all';
            $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path, category, display_order, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $description, $image_path, $category, $display_order]);
        }
        $_SESSION['success_message'] = 'Gallery image added successfully!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error adding image: ' . $e->getMessage();
    }
    header('Location: gallery.php');
    exit;
}

// Handle update gallery image
if (isset($_POST['update_image'])) {
    $id = (int)$_POST['image_id'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_path = $_POST['image_path'] ?? '';
    $category = $_POST['category'] ?? ($galleryHasCategoryId ? 0 : 'all');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        if ($galleryHasCategoryId) {
            $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, image_path = ?, category_id = ?, display_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $description, $image_path, (int) $category, $display_order, $is_active, $id]);
        } else {
            $category = array_key_exists($category, $galleryCategoryOptions) ? $category : 'all';
            $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, image_path = ?, category = ?, display_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $description, $image_path, $category, $display_order, $is_active, $id]);
        }
        $_SESSION['success_message'] = 'Gallery image updated successfully!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error updating image: ' . $e->getMessage();
    }
    header('Location: gallery.php');
    exit;
}

// Handle delete
if (isset($_POST['delete_image'])) {
    $id = (int)$_POST['image_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = 'Gallery image deleted successfully!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error deleting image: ' . $e->getMessage();
    }
    header('Location: gallery.php');
    exit;
}

// Fetch all gallery images with category names
try {
    if ($galleryHasCategoryId) {
        $stmt = $pdo->query("SELECT g.*, c.name as category_name FROM gallery g LEFT JOIN categories c ON g.category_id = c.id ORDER BY g.display_order ASC, g.created_at DESC");
    } else {
        $stmt = $pdo->query("SELECT g.*, g.category as category_name FROM gallery g ORDER BY g.display_order ASC, g.created_at DESC");
    }
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $images = [];
    $error_message = 'Error fetching gallery images: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <div>
            <h2 style="font-size: 1.75rem; margin-bottom: 8px; color: var(--admin-text);">
                <i class="bi bi-images"></i> Gallery Management
            </h2>
            <p class="text-muted" style="margin: 0;">Manage your gallery images and categories</p>
        </div>
        <div style="margin-top: 16px;">
            <button type="button" class="btn-custom btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addImageModal">
                <i class="bi bi-plus-circle"></i> Add New Image
            </button>
        </div>
    </div>
</div>

<!-- Gallery Grid -->
<div class="content-card">
    <?php if (empty($images)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--admin-text-light);">
            <i class="bi bi-images" style="font-size: 4rem; opacity: 0.3;"></i>
            <h4 style="margin-top: 20px; margin-bottom: 10px;">No gallery images yet</h4>
            <p>Add your first image to get started</p>
            <button type="button" class="btn-custom btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addImageModal" style="margin-top: 20px;">
                <i class="bi bi-plus-circle"></i> Add Image
            </button>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($images as $image): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="gallery-card">
                        <div class="gallery-image">
                            <img src="../<?= htmlspecialchars($image['image_path']) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                            <div class="gallery-overlay">
                                <button type="button" class="btn-icon" data-bs-toggle="modal" data-bs-target="#editImageModal<?= $image['id'] ?>" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                    <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                                    <button type="submit" name="delete_image" class="btn-icon btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="gallery-info">
                            <h5><?= htmlspecialchars($image['title']) ?></h5>
                            <div class="gallery-meta">
                                <span class="badge-custom <?= $image['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $image['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                                    <span class="badge-custom badge-info"><?= isset($image['category_name']) && $image['category_name'] !== null ? htmlspecialchars($image['category_name']) : 'Uncategorized' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal for each image -->
                <div class="modal fade" id="editImageModal<?= $image['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Gallery Image</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="image_id" value="<?= $image['id'] ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($image['title']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($image['description']) ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Current Image</label>
                                        <img src="../<?= htmlspecialchars($image['image_path']) ?>" alt="Current" class="current-image-preview" id="editPreview<?= $image['id'] ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Change Image (optional)</label>
                                        <div class="edit-upload-area" onclick="document.getElementById('editUpload<?= $image['id'] ?>').click()">
                                            <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--admin-primary);"></i>
                                            <p class="mt-2 mb-0"><strong>Click to upload new image</strong></p>
                                            <p class="text-muted" style="font-size: 0.875rem;">PNG, JPG, GIF, WebP up to 5MB</p>
                                        </div>
                                        <input type="file" id="editUpload<?= $image['id'] ?>" accept="image/*" style="display: none;">
                                        <input type="hidden" name="image_path" id="editImagePath<?= $image['id'] ?>" value="<?= htmlspecialchars($image['image_path']) ?>">
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            setupEditUpload(<?= $image['id'] ?>);
                                        });
                                    </script>

                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="form-control" name="category">
                                            <?php foreach ($galleryCategoryOptions as $categoryValue => $categoryLabel): ?>
                                                <?php
                                                $currentCategory = $galleryHasCategoryId ? ($image['category_id'] ?? 0) : ($image['category'] ?? 'all');
                                                $selected = (string) $currentCategory === (string) $categoryValue ? ' selected' : '';
                                                ?>
                                                <option value="<?= htmlspecialchars((string) $categoryValue) ?>"<?= $selected ?>>
                                                    <?= htmlspecialchars($categoryLabel) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Display Order</label>
                                        <input type="number" class="form-control" name="display_order" value="<?= $image['display_order'] ?>">
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_active" <?= $image['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn-custom btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_image" class="btn-custom btn-primary-custom">Update Image</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Image Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="addImageForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Image Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" name="title" id="add_title" placeholder="Enter image title" required>
                            <small class="text-muted">Give your image a descriptive name</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Upload Image <span class="text-danger">*</span></label>
                            <div class="upload-area-enhanced" id="uploadArea">
                                <input type="file" id="imageUpload" accept="image/*" style="display: none;">
                                <div class="upload-placeholder">
                                    <i class="bi bi-cloud-upload" style="font-size: 3.5rem; color: var(--admin-primary); margin-bottom: 10px;"></i>
                                    <p class="mt-3 mb-2"><strong>Click to upload</strong> or drag and drop</p>
                                    <p class="text-muted" style="font-size: 0.875rem; margin: 0;">PNG, JPG, GIF, WebP up to 5MB</p>
                                </div>
                                <div class="upload-preview" id="uploadPreview" style="display: none;">
                                    <img id="previewImage" src="" alt="Preview">
                                    <button type="button" class="btn-remove-image" id="removeImage">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="upload-progress" id="uploadProgress" style="display: none;">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                                    </div>
                                    <p class="text-muted mt-2 mb-0">Uploading image...</p>
                                </div>
                            </div>
                            <input type="hidden" name="image_path" id="add_image_path" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Optional short caption"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Category</label>
                            <select class="form-control" name="category">
                                <?php foreach ($galleryCategoryOptions as $categoryValue => $categoryLabel): ?>
                                    <option value="<?= htmlspecialchars((string) $categoryValue) ?>"><?= htmlspecialchars($categoryLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-custom btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_image" class="btn-custom btn-primary-custom" id="addImageBtn">Add Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .gallery-card {
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .gallery-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .gallery-image {
        position: relative;
        padding-top: 100%;
        overflow: hidden;
        background: #f5f5f5;
    }

    .gallery-image img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-card:hover .gallery-overlay {
        opacity: 1;
    }

    .btn-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: white;
        color: var(--admin-primary);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .btn-icon.btn-danger {
        background: #dc3545;
        color: white;
    }

    .gallery-info {
        padding: 15px;
    }

    .gallery-info h5 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--admin-text);
    }

    .gallery-meta {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge-custom.badge-secondary {
        background-color: #6c757d;
    }

    .badge-custom.badge-info {
        background-color: #17a2b8;
    }

    /* Upload Area Styles */
    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .upload-area:hover {
        border-color: var(--admin-primary);
        background-color: rgba(158, 103, 71, 0.05);
    }

    .upload-area-enhanced {
        border: 2px dashed #ddd;
        border-radius: 16px;
        padding: 50px 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        background: linear-gradient(135deg, rgba(158, 103, 71, 0.02) 0%, transparent 100%);
    }

    .upload-area-enhanced:hover {
        border-color: var(--admin-primary);
        background-color: rgba(158, 103, 71, 0.08);
        box-shadow: 0 4px 12px rgba(158, 103, 71, 0.1);
    }

    .upload-placeholder {
        color: var(--admin-text);
    }

    .upload-preview {
        position: relative;
    }

    .upload-preview img {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .btn-remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #dc3545;
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-remove-image:hover {
        background: #c82333;
        transform: scale(1.1);
    }

    .upload-progress .progress {
        height: 8px;
        border-radius: 4px;
        background-color: #e9ecef;
    }

    .upload-progress .progress-bar {
        background-color: var(--admin-primary);
        transition: width 0.3s ease;
    }

    /* Edit Modal Upload Styles */
    .edit-upload-area {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .edit-upload-area:hover {
        border-color: var(--admin-primary);
        background-color: rgba(158, 103, 71, 0.05);
    }

    .current-image-preview {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Upload functionality for Add Image Modal
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const imageUpload = document.getElementById('imageUpload');
    const uploadPlaceholder = uploadArea.querySelector('.upload-placeholder');
    const uploadPreview = document.getElementById('uploadPreview');
    const uploadProgress = document.getElementById('uploadProgress');
    const previewImage = document.getElementById('previewImage');
    const removeImageBtn = document.getElementById('removeImage');
    const imagePathInput = document.getElementById('add_image_path');
    const progressBar = document.getElementById('progressBar');

    // Click to upload
    uploadArea.addEventListener('click', function(e) {
        if (!e.target.closest('.btn-remove-image')) {
            imageUpload.click();
        }
    });

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--admin-primary)';
        uploadArea.style.backgroundColor = 'rgba(158, 103, 71, 0.05)';
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = '#ddd';
        uploadArea.style.backgroundColor = 'transparent';
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = '#ddd';
        uploadArea.style.backgroundColor = 'transparent';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });

    // File input change
    imageUpload.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileUpload(e.target.files[0]);
        }
    });

    // Remove image
    removeImageBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        resetUpload();
    });

    // Handle file upload
    function handleFileUpload(file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit.');
            return;
        }

        // Show progress
        uploadPlaceholder.style.display = 'none';
        uploadPreview.style.display = 'none';
        uploadProgress.style.display = 'block';

        // Upload file
        const formData = new FormData();
        formData.append('image', file);

        const xhr = new XMLHttpRequest();

        // Progress handler
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
            }
        });

        // Load handler
        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Show preview
                    previewImage.src = '../' + response.path;
                    imagePathInput.value = response.path;

                    uploadProgress.style.display = 'none';
                    uploadPreview.style.display = 'block';
                } else {
                    alert('Upload failed: ' + response.error);
                    resetUpload();
                }
            } else {
                alert('Upload failed. Please try again.');
                resetUpload();
            }
        });

        // Error handler
        xhr.addEventListener('error', function() {
            alert('Upload failed. Please try again.');
            resetUpload();
        });

        xhr.open('POST', 'upload-image.php');
        xhr.send(formData);
    }

    // Reset upload state
    function resetUpload() {
        uploadPlaceholder.style.display = 'block';
        uploadPreview.style.display = 'none';
        uploadProgress.style.display = 'none';
        progressBar.style.width = '0%';
        imagePathInput.value = '';
        imageUpload.value = '';
    }

    // Reset on modal close
    document.getElementById('addImageModal').addEventListener('hidden.bs.modal', function() {
        resetUpload();
        document.getElementById('addImageForm').reset();
    });
});

// Edit modal upload functionality for each image
function setupEditUpload(imageId) {
    const editUploadInput = document.getElementById('editUpload' + imageId);
    const editImagePath = document.getElementById('editImagePath' + imageId);
    const editPreview = document.getElementById('editPreview' + imageId);

    if (editUploadInput) {
        editUploadInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];

                // Validate
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type.');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB.');
                    return;
                }

                // Upload
                const formData = new FormData();
                formData.append('image', file);

                fetch('upload-image.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        editImagePath.value = data.path;
                        if (editPreview) {
                            editPreview.src = '../' + data.path;
                        }
                    } else {
                        alert('Upload failed: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Upload failed. Please try again.');
                });
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
