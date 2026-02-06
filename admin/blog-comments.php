<?php
require_once __DIR__ . '/../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle approval/rejection actions
if (isset($_POST['action']) && isset($_POST['comment_id'])) {
    $commentId = (int)$_POST['comment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE blog_comments SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$commentId]);
        $message = "Comment approved successfully!";
        $messageType = "success";
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE blog_comments SET is_approved = 0 WHERE id = ?");
        $stmt->execute([$commentId]);
        $message = "Comment rejected successfully!";
        $messageType = "warning";
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE id = ?");
        $stmt->execute([$commentId]);
        $message = "Comment deleted successfully!";
        $messageType = "success";
    }
}

// Get all comments with blog info
$query = "SELECT c.*, b.title as blog_title, b.slug as blog_slug
          FROM blog_comments c
          LEFT JOIN blogs b ON c.blog_id = b.id
          ORDER BY c.created_at DESC";
$stmt = $pdo->query($query);
$comments = $stmt->fetchAll();

// Get counts
$total_comments = count($comments);
$pending_count = count(array_filter($comments, fn($c) => $c['is_approved'] == 0));
$approved_count = count(array_filter($comments, fn($c) => $c['is_approved'] == 1));

include 'includes/header.php';
?>

<?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <div>
            <h2 style="font-size: 1.75rem; margin-bottom: 8px; color: var(--admin-text);">
                <i class="bi bi-chat-square-text"></i> Blog Comments
            </h2>
            <p class="text-muted" style="margin: 0;">Manage comments on your blog posts</p>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 16px;">
            <a href="blogs.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-arrow-left"></i> Back to Blogs
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="content-card" style="text-align: center; padding: 20px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: rgba(158, 103, 71, 0.1); border-radius: 12px; margin-bottom: 12px;">
                <i class="bi bi-chat-square-text" style="font-size: 1.8rem; color: var(--admin-primary);"></i>
            </div>
            <h3 style="margin: 0; font-size: 2rem; color: var(--admin-text);"><?php echo $total_comments; ?></h3>
            <p class="text-muted" style="margin: 0;">Total Comments</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card" style="text-align: center; padding: 20px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: rgba(255, 193, 7, 0.1); border-radius: 12px; margin-bottom: 12px;">
                <i class="bi bi-clock" style="font-size: 1.8rem; color: #ffc107;"></i>
            </div>
            <h3 style="margin: 0; font-size: 2rem; color: var(--admin-text);"><?php echo $pending_count; ?></h3>
            <p class="text-muted" style="margin: 0;">Pending Approval</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card" style="text-align: center; padding: 20px;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 60px; height: 60px; background: rgba(40, 167, 69, 0.1); border-radius: 12px; margin-bottom: 12px;">
                <i class="bi bi-check-circle" style="font-size: 1.8rem; color: #28a745;"></i>
            </div>
            <h3 style="margin: 0; font-size: 2rem; color: var(--admin-text);"><?php echo $approved_count; ?></h3>
            <p class="text-muted" style="margin: 0;">Approved</p>
        </div>
    </div>
</div>

<!-- Comments Table -->
<div class="content-card">
    <?php if (empty($comments)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--admin-text-light);">
            <i class="bi bi-chat-square-text" style="font-size: 4rem; opacity: 0.3;"></i>
            <h4 style="margin-top: 20px; margin-bottom: 10px;">No comments yet</h4>
            <p>Comments from your blog readers will appear here</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 20%;">Blog Post</th>
                        <th style="width: 18%;">Author</th>
                        <th style="width: 7%;">Rating</th>
                        <th style="width: 25%;">Comment</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 7%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr class="<?php echo $comment['is_approved'] == 0 ? 'pending-row' : ''; ?>">
                            <td><strong>#<?php echo $comment['id']; ?></strong></td>
                            <td>
                                <a href="../blog-detail.php?slug=<?php echo urlencode($comment['blog_slug']); ?>"
                                   target="_blank" style="color: var(--admin-primary); text-decoration: none;">
                                    <?php echo htmlspecialchars($comment['blog_title'] ?? 'Unknown Blog'); ?>
                                    <i class="bi bi-box-arrow-up-right" style="font-size: 0.75rem; margin-left: 4px;"></i>
                                </a>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($comment['author_email']); ?></small>
                                <?php if ($comment['author_phone']): ?>
                                    <br><small class="text-muted"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($comment['author_phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($comment['rating']): ?>
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $comment['rating'] ? '-fill' : ''; ?>"
                                               style="color: <?php echo $i <= $comment['rating'] ? '#ffc107' : '#ddd'; ?>; font-size: 0.8rem;"></i>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 300px;">
                                <div class="comment-text" style="max-height: 80px; overflow-y: auto;">
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($comment['created_at'])); ?><br>
                                <small class="text-muted"><?php echo date('h:i A', strtotime($comment['created_at'])); ?></small>
                            </td>
                            <td>
                                <?php if ($comment['is_approved']): ?>
                                    <span class="badge-custom badge-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge-custom badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <?php if ($comment['is_approved'] == 0): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-custom btn-success-custom"
                                                    style="padding: 6px 10px; font-size: 0.8rem;"
                                                    title="Approve"
                                                    onclick="return confirm('Approve this comment?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn-custom btn-warning-custom"
                                                    style="padding: 6px 10px; font-size: 0.8rem;"
                                                    title="Unapprove"
                                                    onclick="return confirm('Unapprove this comment?')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn-custom btn-danger-custom"
                                                style="padding: 6px 10px; font-size: 0.8rem;"
                                                title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this comment? This action cannot be undone.')">
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
    .pending-row {
        background-color: rgba(255, 193, 7, 0.08) !important;
    }

    .btn-success-custom {
        background-color: #28a745;
        color: white;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-success-custom:hover {
        background-color: #218838;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-warning-custom {
        background-color: #ffc107;
        color: #000;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-warning-custom:hover {
        background-color: #e0a800;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

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

    .badge-custom.badge-warning {
        background-color: #ffc107;
        color: #000;
    }

    .comment-text {
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .comment-text::-webkit-scrollbar {
        width: 4px;
    }

    .comment-text::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;
    }
</style>

<?php include 'includes/footer.php'; ?>
