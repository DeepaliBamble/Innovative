<?php
require_once __DIR__ . '/../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle approval/rejection actions
if (isset($_POST['action']) && isset($_POST['review_id'])) {
    $reviewId = (int)$_POST['review_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$reviewId]);
        $message = "Review approved successfully!";
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 0 WHERE id = ?");
        $stmt->execute([$reviewId]);
        $message = "Review rejected successfully!";
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $message = "Review deleted successfully!";
    }
}

// Get all reviews with product and user info
$query = "SELECT r.*, p.name as product_name, p.slug as product_slug,
          u.name as user_name, u.email as user_email
          FROM reviews r
          LEFT JOIN products p ON r.product_id = p.id
          LEFT JOIN users u ON r.user_id = u.id
          ORDER BY r.created_at DESC";
$stmt = $pdo->query($query);
$reviews = $stmt->fetchAll();

// Get counts
$total_reviews = count($reviews);
$pending_count = count(array_filter($reviews, fn($r) => $r['is_approved'] == 0));
$approved_count = count(array_filter($reviews, fn($r) => $r['is_approved'] == 1));

include 'includes/header.php';
?>

<div class="container-fluid">
    <?php if (isset($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="bi bi-star-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $total_reviews; ?></h3>
                            <p class="text-muted mb-0">Total Reviews</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $pending_count; ?></h3>
                            <p class="text-muted mb-0">Pending Approval</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $approved_count; ?></h3>
                            <p class="text-muted mb-0">Approved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-star me-2"></i>Product Reviews</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($reviews)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-star" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No reviews yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr class="<?php echo $review['is_approved'] == 0 ? 'table-warning bg-opacity-10' : ''; ?>">
                                    <td>
                                        <a href="../product-detail.php?slug=<?php echo urlencode($review['product_slug']); ?>"
                                           target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars($review['product_name']); ?>
                                            <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($review['user_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($review['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill text-warning' : ' text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="ms-2 fw-bold"><?php echo $review['rating']; ?>/5</span>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <?php if ($review['title']): ?>
                                            <strong><?php echo htmlspecialchars($review['title']); ?></strong><br>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($review['comment'], 0, 100)); ?>
                                            <?php echo strlen($review['comment']) > 100 ? '...' : ''; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($review['is_approved']): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if ($review['is_approved'] == 0): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success"
                                                            onclick="return confirm('Approve this review?')">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-warning"
                                                            onclick="return confirm('Unapprove this review?')">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this review? This action cannot be undone.')">
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
    </div>
</div>

<?php include 'includes/footer.php'; ?>
