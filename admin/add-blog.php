<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$editing = false;
$blog = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'featured_image' => '',
    'author_name' => $_SESSION['admin_name'] ?? 'Admin',
    'category' => '',
    'tags' => '',
    'meta_title' => '',
    'meta_description' => '',
    'is_published' => 0,
    'is_featured' => 0
];

// Check if editing
if (isset($_GET['id'])) {
    $editing = true;
    $blog_id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
        $stmt->execute([$blog_id]);
        $fetched_blog = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fetched_blog) {
            $blog = $fetched_blog;
        } else {
            $_SESSION['error_message'] = 'Blog not found!';
            header('Location: blogs.php');
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error fetching blog: ' . $e->getMessage();
        header('Location: blogs.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $featured_image = trim($_POST['featured_image'] ?? '');
    $author_name = trim($_POST['author_name'] ?? 'Admin');
    $category = trim($_POST['category'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Validation
    $errors = [];
    if (empty($title)) {
        $errors[] = 'Blog title is required.';
    }
    if (empty($content)) {
        $errors[] = 'Blog content is required.';
    }

    if (empty($errors)) {
        // Auto-generate slug if empty, otherwise normalize user-provided slug
        // so URL-unsafe characters (spaces, colons, punctuation) can never reach the DB.
        $slugSource = empty($slug) ? $title : $slug;
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slugSource)));
        $slug = trim($slug, '-');
        if ($slug === '') {
            $slug = 'blog-' . time();
        }

        // Auto-generate meta title if empty
        if (empty($meta_title)) {
            $meta_title = $title;
        }

        try {
            if ($editing) {
                // Check for duplicate slug (excluding current blog)
                $checkStmt = $pdo->prepare("SELECT id FROM blogs WHERE slug = ? AND id != ?");
                $checkStmt->execute([$slug, $blog_id]);
                if ($checkStmt->fetch()) {
                    $slug = $slug . '-' . time();
                }

                // Update existing blog
                $stmt = $pdo->prepare("UPDATE blogs SET
                    title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?,
                    author_name = ?, category = ?, tags = ?, meta_title = ?,
                    meta_description = ?, is_published = ?, is_featured = ?,
                    published_at = ?, updated_at = NOW()
                    WHERE id = ?");

                $published_at = $is_published ? ($blog['published_at'] ?: date('Y-m-d H:i:s')) : null;

                $stmt->execute([
                    $title, $slug, $excerpt, $content, $featured_image,
                    $author_name, $category, $tags, $meta_title,
                    $meta_description, $is_published, $is_featured,
                    $published_at, $blog_id
                ]);

                $_SESSION['success_message'] = 'Blog updated successfully!';
            } else {
                // Check for duplicate slug
                $checkStmt = $pdo->prepare("SELECT id FROM blogs WHERE slug = ?");
                $checkStmt->execute([$slug]);
                if ($checkStmt->fetch()) {
                    $slug = $slug . '-' . time();
                }

                // Insert new blog
                $stmt = $pdo->prepare("INSERT INTO blogs (
                    title, slug, excerpt, content, featured_image, author_name,
                    category, tags, meta_title, meta_description, is_published,
                    is_featured, published_at, views, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())");

                $published_at = $is_published ? date('Y-m-d H:i:s') : null;

                $stmt->execute([
                    $title, $slug, $excerpt, $content, $featured_image, $author_name,
                    $category, $tags, $meta_title, $meta_description, $is_published,
                    $is_featured, $published_at
                ]);

                $_SESSION['success_message'] = 'Blog created successfully!';
            }

            header('Location: blogs.php');
            exit;

        } catch (PDOException $e) {
            $error_message = 'Error saving blog: ' . $e->getMessage();
        }
    } else {
        $error_message = implode(' ', $errors);
    }
}

include 'includes/header.php';
?>

<!-- TinyMCE Rich Text Editor (Free CDN - No API Key Required) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js"></script>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="content-card">
    <h2 style="font-size: 1.75rem; margin-bottom: 8px; color: var(--admin-text);">
        <i class="bi bi-pencil-square"></i> <?= $editing ? 'Edit Blog Post' : 'Add New Blog Post' ?>
    </h2>
    <p class="text-muted" style="margin: 0;">Fill in the details below to create or update a blog post</p>
</div>

<!-- Blog Form -->
<form method="POST" action="" id="blogForm" enctype="multipart/form-data">
    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="content-card">
                <h3 class="content-card-title">
                    <i class="bi bi-file-text"></i> Basic Information
                </h3>

                <div class="mb-3">
                    <label for="title" class="form-label">Blog Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($blog['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">URL Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($blog['slug']) ?>" placeholder="leave-empty-for-auto-generation">
                    <small class="text-muted">Leave empty to auto-generate from title</small>
                </div>

                <div class="mb-3">
                    <label for="excerpt" class="form-label">Excerpt</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Short summary of the blog post"><?= htmlspecialchars($blog['excerpt']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea id="content" name="content"><?= htmlspecialchars($blog['content']) ?></textarea>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="content-card">
                <h3 class="content-card-title">
                    <i class="bi bi-search"></i> SEO Settings
                </h3>

                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?= htmlspecialchars($blog['meta_title']) ?>" placeholder="Leave empty to use blog title">
                </div>

                <div class="mb-3">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea class="form-control" id="meta_description" name="meta_description" rows="2" placeholder="Brief description for search engines"><?= htmlspecialchars($blog['meta_description']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Publish Settings -->
            <div class="content-card">
                <h3 class="content-card-title">
                    <i class="bi bi-gear"></i> Publish Settings
                </h3>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" <?= $blog['is_published'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_published">Published</label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?= $blog['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Featured</label>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn-custom btn-primary-custom" id="submitBtn">
                        <i class="bi bi-save"></i> <?= $editing ? 'Update Blog' : 'Create Blog' ?>
                    </button>
                    <a href="blogs.php" class="btn-custom btn-outline-custom">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Blog Details -->
            <div class="content-card">
                <h3 class="content-card-title">
                    <i class="bi bi-info-circle"></i> Blog Details
                </h3>

                <div class="mb-3">
                    <label for="author_name" class="form-label">Author Name</label>
                    <input type="text" class="form-control" id="author_name" name="author_name" value="<?= htmlspecialchars($blog['author_name']) ?>">
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">Select Category</option>
                        <option value="Furniture Tips" <?= $blog['category'] === 'Furniture Tips' ? 'selected' : '' ?>>Furniture Tips</option>
                        <option value="Interior Design" <?= $blog['category'] === 'Interior Design' ? 'selected' : '' ?>>Interior Design</option>
                        <option value="Home Decor" <?= $blog['category'] === 'Home Decor' ? 'selected' : '' ?>>Home Decor</option>
                        <option value="Maintenance" <?= $blog['category'] === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="Trends" <?= $blog['category'] === 'Trends' ? 'selected' : '' ?>>Trends</option>
                        <option value="DIY" <?= $blog['category'] === 'DIY' ? 'selected' : '' ?>>DIY</option>
                        <option value="News" <?= $blog['category'] === 'News' ? 'selected' : '' ?>>News</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tags" class="form-label">Tags</label>
                    <input type="text" class="form-control" id="tags" name="tags" value="<?= htmlspecialchars($blog['tags']) ?>" placeholder="tag1, tag2, tag3">
                    <small class="text-muted">Comma-separated tags</small>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="content-card">
                <h3 class="content-card-title">
                    <i class="bi bi-image"></i> Featured Image
                </h3>

                <div class="mb-3">
                    <div class="blog-upload-area" id="blogUploadArea">
                        <input type="file" id="blogImageUpload" accept="image/*" style="display: none;">

                        <div class="blog-upload-placeholder" id="blogUploadPlaceholder" style="<?= !empty($blog['featured_image']) ? 'display: none;' : '' ?>">
                            <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--admin-primary);"></i>
                            <p class="mt-2 mb-1"><strong>Click to upload</strong> or drag and drop</p>
                            <p class="text-muted" style="font-size: 0.875rem;">PNG, JPG, GIF, WebP up to 5MB</p>
                        </div>

                        <div class="blog-upload-preview" id="blogUploadPreview" style="<?= empty($blog['featured_image']) ? 'display: none;' : '' ?>">
                            <img id="blogPreviewImage" src="<?= !empty($blog['featured_image']) ? '../' . htmlspecialchars($blog['featured_image']) : '' ?>" alt="Preview" style="width: 100%; border-radius: 8px; margin-bottom: 10px;">
                            <button type="button" class="btn-custom btn-outline-custom w-100" onclick="document.getElementById('blogImageUpload').click(); event.stopPropagation();">
                                <i class="bi bi-cloud-upload"></i> Change Image
                            </button>
                            <button type="button" class="btn-custom btn-danger-custom w-100 mt-2" onclick="removeImage(); event.stopPropagation();">
                                <i class="bi bi-trash"></i> Remove Image
                            </button>
                        </div>

                        <div class="blog-upload-progress" id="blogUploadProgress" style="display: none;">
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar" id="blogProgressBar" style="width: 0%; background-color: var(--admin-primary);"></div>
                            </div>
                            <p class="text-muted mt-2 text-center">Uploading...</p>
                        </div>
                    </div>
                    <input type="hidden" name="featured_image" id="blog_featured_image" value="<?= htmlspecialchars($blog['featured_image']) ?>">
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#content',
                height: 500,
                menubar: true,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | removeformat | code | help',
                content_style: 'body { font-family:Inter,Arial,sans-serif; font-size:16px; line-height:1.6; }',
                branding: false,
                promotion: false,
                images_upload_url: 'upload-blog-image.php',
                images_upload_handler: function(blobInfo, progress) {
                    return new Promise(function(resolve, reject) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'upload-blog-image.php');

                        xhr.upload.onprogress = function(e) {
                            progress(e.loaded / e.total * 100);
                        };

                        xhr.onload = function() {
                            if (xhr.status !== 200) {
                                reject('HTTP Error: ' + xhr.status);
                                return;
                            }

                            try {
                                var json = JSON.parse(xhr.responseText);
                                if (json.success) {
                                    resolve('../' + json.path);
                                } else {
                                    reject(json.error || 'Upload failed');
                                }
                            } catch (e) {
                                reject('Invalid JSON response: ' + xhr.responseText);
                            }
                        };

                        xhr.onerror = function() {
                            reject('Image upload failed due to network error');
                        };

                        var formData = new FormData();
                        formData.append('image', blobInfo.blob(), blobInfo.filename());
                        xhr.send(formData);
                    });
                },
                automatic_uploads: true,
                file_picker_types: 'image',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });
        }

        // Form submission - sync TinyMCE content
        const blogForm = document.getElementById('blogForm');
        if (blogForm) {
            blogForm.addEventListener('submit', function(e) {
                // Sync TinyMCE content to textarea before submit
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    tinymce.get('content').save();
                }

                // Validate content
                const content = document.getElementById('content').value;
                const title = document.getElementById('title').value;

                if (!title.trim()) {
                    e.preventDefault();
                    alert('Please enter a blog title.');
                    return false;
                }

                if (!content.trim()) {
                    e.preventDefault();
                    alert('Please enter blog content.');
                    return false;
                }

                return true;
            });
        }

        // Auto-generate slug from title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');

        if (titleInput && slugInput) {
            titleInput.addEventListener('blur', function() {
                if (!slugInput.value) {
                    const slug = this.value
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                    slugInput.value = slug;
                }
            });
        }

        // Image upload functionality
        const blogUploadArea = document.getElementById('blogUploadArea');
        const blogImageUpload = document.getElementById('blogImageUpload');
        const blogUploadPlaceholder = document.getElementById('blogUploadPlaceholder');
        const blogUploadPreview = document.getElementById('blogUploadPreview');
        const blogUploadProgress = document.getElementById('blogUploadProgress');
        const blogPreviewImage = document.getElementById('blogPreviewImage');
        const blogFeaturedImageInput = document.getElementById('blog_featured_image');
        const blogProgressBar = document.getElementById('blogProgressBar');

        // Click to upload (on placeholder only)
        if (blogUploadPlaceholder) {
            blogUploadPlaceholder.addEventListener('click', function(e) {
                e.stopPropagation();
                blogImageUpload.click();
            });
        }

        // Drag and drop
        if (blogUploadArea) {
            blogUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                blogUploadArea.style.borderColor = 'var(--admin-primary)';
                blogUploadArea.style.backgroundColor = 'rgba(158, 103, 71, 0.05)';
            });

            blogUploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                blogUploadArea.style.borderColor = '#ddd';
                blogUploadArea.style.backgroundColor = '#f8f9fa';
            });

            blogUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                blogUploadArea.style.borderColor = '#ddd';
                blogUploadArea.style.backgroundColor = '#f8f9fa';

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleBlogImageUpload(files[0]);
                }
            });
        }

        // File input change
        if (blogImageUpload) {
            blogImageUpload.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    handleBlogImageUpload(e.target.files[0]);
                }
            });
        }

        // Handle blog image upload
        window.handleBlogImageUpload = function(file) {
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
            blogUploadPlaceholder.style.display = 'none';
            blogUploadPreview.style.display = 'none';
            blogUploadProgress.style.display = 'block';

            // Upload file
            const formData = new FormData();
            formData.append('image', file);

            const xhr = new XMLHttpRequest();

            // Progress handler
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    blogProgressBar.style.width = percentComplete + '%';
                }
            });

            // Load handler
            xhr.addEventListener('load', function() {
                blogUploadProgress.style.display = 'none';

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Show preview
                            blogPreviewImage.src = '../' + response.path;
                            blogFeaturedImageInput.value = response.path;
                            blogUploadPreview.style.display = 'block';
                            blogUploadPlaceholder.style.display = 'none';
                        } else {
                            alert('Upload failed: ' + (response.error || 'Unknown error'));
                            resetBlogUpload();
                        }
                    } catch (e) {
                        console.error('Parse error:', e, xhr.responseText);
                        alert('Upload failed: Invalid response from server');
                        resetBlogUpload();
                    }
                } else {
                    alert('Upload failed. Server returned status: ' + xhr.status);
                    resetBlogUpload();
                }
            });

            // Error handler
            xhr.addEventListener('error', function() {
                blogUploadProgress.style.display = 'none';
                alert('Upload failed. Please check your connection and try again.');
                resetBlogUpload();
            });

            xhr.open('POST', 'upload-blog-image.php');
            xhr.send(formData);
        };

        // Reset blog upload state
        window.resetBlogUpload = function() {
            blogUploadPlaceholder.style.display = 'block';
            blogUploadPreview.style.display = 'none';
            blogUploadProgress.style.display = 'none';
            blogProgressBar.style.width = '0%';
            blogFeaturedImageInput.value = '';
            blogImageUpload.value = '';
        };

        // Remove image
        window.removeImage = function() {
            if (confirm('Are you sure you want to remove the featured image?')) {
                resetBlogUpload();
            }
        };
    });
</script>

<style>
    .form-label {
        font-weight: 600;
        color: var(--admin-text);
        margin-bottom: 8px;
        display: block;
    }

    .form-control {
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 14px;
        width: 100%;
    }

    .form-control:focus {
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 0.2rem rgba(158, 103, 71, 0.25);
        outline: none;
    }

    .form-check-input:checked {
        background-color: var(--admin-primary);
        border-color: var(--admin-primary);
    }

    /* Blog Upload Area Styles */
    .blog-upload-area {
        border: 2px dashed #ddd;
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        background-color: #f8f9fa;
    }

    .blog-upload-area:hover {
        border-color: var(--admin-primary);
        background-color: rgba(158, 103, 71, 0.05);
    }

    .blog-upload-placeholder {
        color: var(--admin-text);
        cursor: pointer;
    }

    .blog-upload-preview {
        text-align: center;
    }

    .blog-upload-preview img {
        max-height: 200px;
        object-fit: cover;
    }

    .btn-danger-custom {
        background-color: #dc3545;
        color: white;
        border: none;
    }

    .btn-danger-custom:hover {
        background-color: #c82333;
    }

    /* TinyMCE container fix */
    .tox-tinymce {
        border-radius: 6px !important;
    }
</style>

<?php include 'includes/footer.php'; ?>
