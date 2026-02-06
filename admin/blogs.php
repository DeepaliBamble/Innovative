<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

// Handle delete action
if (isset($_POST['delete_blog'])) {
    $blog_id = (int)$_POST['blog_id'];
    try {
        // Delete comments first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE blog_id = ?");
        $stmt->execute([$blog_id]);

        // Delete blog
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->execute([$blog_id]);

        $_SESSION['success_message'] = 'Blog deleted successfully!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error deleting blog: ' . $e->getMessage();
    }
    header('Location: blogs.php');
    exit;
}

// Fetch all blogs
try {
    $stmt = $pdo->query("SELECT id, title, slug, category, author_name, is_published, is_featured, views, published_at, created_at FROM blogs ORDER BY created_at DESC");
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $blogs = [];
    $error_message = 'Error fetching blogs: ' . $e->getMessage();
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
                <i class="bi bi-newspaper"></i> Blog Management
            </h2>
            <p class="text-muted" style="margin: 0;">Manage your blog posts and articles</p>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 16px;">
            <a href="blog-comments.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-chat-square-text"></i> Manage Comments
            </a>
            <a href="add-blog.php" class="btn-custom btn-primary-custom">
                <i class="bi bi-plus-circle"></i> Add New Blog Post
            </a>
        </div>
    </div>
</div>

<!-- Blogs List -->
<div class="content-card">
    <?php if (empty($blogs)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--admin-text-light);">
            <i class="bi bi-newspaper" style="font-size: 4rem; opacity: 0.3;"></i>
            <h4 style="margin-top: 20px; margin-bottom: 10px;">No blog posts yet</h4>
            <p>Create your first blog post to get started</p>
            <a href="add-blog.php" class="btn-custom btn-primary-custom" style="margin-top: 20px;">
                <i class="bi bi-plus-circle"></i> Create Blog Post
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 30%;">Title</th>
                        <th style="width: 12%;">Category</th>
                        <th style="width: 12%;">Author</th>
                        <th style="width: 8%;">Views</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 11%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogs as $blog): ?>
                        <tr>
                            <td><strong>#<?= $blog['id'] ?></strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <?= htmlspecialchars($blog['title']) ?>
                                    <?php if ($blog['is_featured']): ?>
                                        <span class="badge-custom badge-warning" style="font-size: 0.7rem;">
                                            <i class="bi bi-star-fill"></i> Featured
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($blog['category']) ?></td>
                            <td><?= htmlspecialchars($blog['author_name']) ?></td>
                            <td><i class="bi bi-eye"></i> <?= number_format($blog['views']) ?></td>
                            <td>
                                <?php if ($blog['is_published']): ?>
                                    <span class="badge-custom badge-success">Published</span>
                                <?php else: ?>
                                    <span class="badge-custom badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($blog['created_at'])) ?></td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <a href="../blog-detail.php?slug=<?= urlencode($blog['slug']) ?>"
                                       target="_blank"
                                       class="btn-custom btn-outline-custom"
                                       style="padding: 6px 10px; font-size: 0.8rem;"
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit-blog.php?id=<?= $blog['id'] ?>"
                                       class="btn-custom btn-outline-custom"
                                       style="padding: 6px 10px; font-size: 0.8rem;"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                                        <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                                        <button type="submit"
                                                name="delete_blog"
                                                class="btn-custom btn-danger-custom"
                                                style="padding: 6px 10px; font-size: 0.8rem;"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .btn-danger-custom {
        background-color: #dc3545;
        color: white;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-danger-custom:hover {
        background-color: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .badge-custom.badge-secondary {
        background-color: #6c757d;
    }

    .badge-custom.badge-warning {
        background-color: #ffc107;
        color: #000;
    }
</style>

<?php include 'includes/footer.php'; ?>
