<?php
require_once __DIR__ . '/../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Handle delete action
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['message_id'])) {
    $messageId = (int)$_POST['message_id'];
    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    $success_message = "Message deleted successfully!";
}

// Mark as read if requested
if (isset($_POST['action']) && $_POST['action'] === 'mark_read' && isset($_POST['message_id'])) {
    $messageId = (int)$_POST['message_id'];
    $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$messageId]);
    $success_message = "Message marked as read!";
}

// Get all contact messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$messages = $stmt->fetchAll();

// Get counts
$total_messages = count($messages);
$unread_count = count(array_filter($messages, fn($m) => $m['is_read'] == 0));
$today_count = count(array_filter($messages, fn($m) => date('Y-m-d', strtotime($m['created_at'])) == date('Y-m-d')));

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
                                <i class="bi bi-envelope-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $total_messages; ?></h3>
                            <p class="text-muted mb-0">Total Messages</p>
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
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="bi bi-calendar-check-fill" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0"><?php echo $today_count; ?></h3>
                            <p class="text-muted mb-0">Today</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Contact Messages</h5>
        </div>
        <div class="card-body">
            <?php if (empty($messages)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-envelope" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No contact messages yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="card mb-3 <?php echo $message['is_read'] == 0 ? 'border-primary' : 'border-0'; ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 me-3"><?php echo htmlspecialchars($message['name']); ?></h5>
                                        <?php if ($message['is_read'] == 0): ?>
                                            <span class="badge bg-primary">NEW</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Read</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-2 text-muted">
                                        <i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($message['email']); ?>
                                        <?php if ($message['phone']): ?>
                                            <i class="bi bi-telephone ms-3 me-2"></i><?php echo htmlspecialchars($message['phone']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($message['subject']): ?>
                                        <p class="mb-2">
                                            <strong>Subject:</strong> <?php echo htmlspecialchars($message['subject']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="bg-light p-3 rounded mt-3">
                                        <strong class="d-block mb-2">Message:</strong>
                                        <p class="mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($message['message']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-calendar me-2"></i>
                                        <?php echo date('M d, Y', strtotime($message['created_at'])); ?><br>
                                        <i class="bi bi-clock me-2"></i>
                                        <?php echo date('h:i A', strtotime($message['created_at'])); ?>
                                    </p>
                                    <div class="d-grid gap-2">
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-reply me-1"></i> Reply
                                        </a>
                                        <?php if ($message['is_read'] == 0): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    <i class="bi bi-check-lg me-1"></i> Mark as Read
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Delete this message?');">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
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
