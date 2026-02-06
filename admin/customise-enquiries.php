<?php
require_once __DIR__ . '/../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle status update
if (isset($_POST['action']) && $_POST['action'] === 'update_status' && isset($_POST['enquiry_id']) && isset($_POST['status'])) {
    $enquiryId = (int)$_POST['enquiry_id'];
    $status = $_POST['status'];
    $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

    if (in_array($status, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE customise_enquiries SET status = ?, is_read = 1 WHERE id = ?");
        $stmt->execute([$status, $enquiryId]);
        $success_message = "Status updated successfully!";
    }
}

// Handle delete action
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['enquiry_id'])) {
    $enquiryId = (int)$_POST['enquiry_id'];
    $stmt = $pdo->prepare("DELETE FROM customise_enquiries WHERE id = ?");
    $stmt->execute([$enquiryId]);
    $success_message = "Enquiry deleted successfully!";
}

// Get all enquiries
$query = "SELECT * FROM customise_enquiries ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$enquiries = $stmt->fetchAll();

// Get counts
$total_enquiries = count($enquiries);
$pending_count = count(array_filter($enquiries, fn($e) => $e['status'] == 'pending'));
$unread_count = count(array_filter($enquiries, fn($e) => $e['is_read'] == 0));

include 'includes/header.php';
?>

<div class="container-fluid">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
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
                                <i class="bi bi-pencil-square" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $total_enquiries; ?></h3>
                            <p class="text-muted mb-0">Total Enquiries</p>
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
                            <p class="text-muted mb-0">Pending</p>
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
                            <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                                <i class="bi bi-bell-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $unread_count; ?></h3>
                            <p class="text-muted mb-0">Unread</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enquiries List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Furniture Customisation Enquiries</h5>
        </div>
        <div class="card-body">
            <?php if (empty($enquiries)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-pencil-square" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No customisation enquiries yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($enquiries as $enquiry): ?>
                    <div class="card mb-3 <?php echo $enquiry['is_read'] == 0 ? 'border-primary' : 'border-0'; ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-3">
                                        <h5 class="mb-0 me-3"><?php echo htmlspecialchars($enquiry['name']); ?></h5>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusColor = $statusColors[$enquiry['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $statusColor; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $enquiry['status'])); ?>
                                        </span>
                                        <?php if ($enquiry['is_read'] == 0): ?>
                                            <span class="badge bg-primary ms-2">NEW</span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="mb-2 text-muted">
                                        <i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($enquiry['email']); ?>
                                        <i class="bi bi-telephone ms-3 me-2"></i><?php echo htmlspecialchars($enquiry['phone']); ?>
                                    </p>

                                    <div class="bg-light p-3 rounded mb-2">
                                        <p class="mb-2">
                                            <i class="bi bi-furniture-fill me-2"></i><strong>Furniture Type:</strong>
                                            <?php echo htmlspecialchars($enquiry['furniture_type']); ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Requirements:</strong><br>
                                            <span style="white-space: pre-wrap;"><?php echo htmlspecialchars($enquiry['requirements']); ?></span>
                                        </p>
                                        <?php if ($enquiry['timeline']): ?>
                                            <p class="mb-2">
                                                <i class="bi bi-clock me-2"></i><strong>Timeline:</strong> <?php echo htmlspecialchars($enquiry['timeline']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($enquiry['budget']): ?>
                                            <p class="mb-0">
                                                <i class="bi bi-currency-rupee me-2"></i><strong>Budget:</strong> <?php echo htmlspecialchars($enquiry['budget']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-calendar me-2"></i>
                                        <?php echo date('M d, Y', strtotime($enquiry['created_at'])); ?><br>
                                        <i class="bi bi-clock me-2"></i>
                                        <?php echo date('h:i A', strtotime($enquiry['created_at'])); ?>
                                    </p>

                                    <div class="d-grid gap-2">
                                        <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>"
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-reply me-1"></i> Reply
                                        </a>

                                        <?php if ($enquiry['status'] === 'pending'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="btn btn-info btn-sm w-100">
                                                    <i class="bi bi-play-fill me-1"></i> Start
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($enquiry['status'] !== 'completed'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    <i class="bi bi-check-lg me-1"></i> Mark Complete
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" onsubmit="return confirm('Delete this enquiry?');">
                                            <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
