<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) { redirect('login.php'); }

// Get orders from database
try {
    $stmt = $pdo->query("SELECT o.*, o.order_status as status, u.name as user_name, u.email as user_email
                         FROM orders o
                         LEFT JOIN users u ON o.user_id = u.id
                         ORDER BY o.created_at DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get order statistics
    $total_orders = count($orders);
    $pending_orders = count(array_filter($orders, fn($o) => ($o['status'] ?? '') === 'pending'));
    $completed_orders = count(array_filter($orders, fn($o) => ($o['status'] ?? '') === 'delivered'));
    $total_revenue = array_sum(array_column($orders, 'total_amount'));

} catch (PDOException $e) {
    $orders = [];
    $total_orders = 0;
    $pending_orders = 0;
    $completed_orders = 0;
    $total_revenue = 0;
    error_log('Error fetching orders: ' . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-cart-check"></i> Order Management
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Track and manage customer orders</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="dashboard.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card primary">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Total Orders</div>
                <div class="stat-card-value"><?= $total_orders ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-cart"></i>
            </div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Pending Orders</div>
                <div class="stat-card-value"><?= $pending_orders ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Completed</div>
                <div class="stat-card-value"><?= $completed_orders ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Total Revenue</div>
                <div class="stat-card-value" style="font-size: 1.5rem;">₹<?= number_format($total_revenue, 0) ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-currency-rupee"></i>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-list-ul"></i> All Orders
        </h3>
    </div>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="bi bi-inbox" style="font-size: 4rem; color: var(--admin-text-light); opacity: 0.3;"></i>
            <h3 style="margin-top: 20px; color: var(--admin-text-light);">No Orders Yet</h3>
            <p style="color: var(--admin-text-light);">Orders will appear here when customers make purchases</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></td>
                        <td><?= htmlspecialchars($order['user_email'] ?? 'N/A') ?></td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?><br>
                            <small class="text-muted"><?= date('h:i A', strtotime($order['created_at'])) ?></small>
                        </td>
                        <td><strong>₹<?= number_format($order['total_amount'], 2) ?></strong></td>
                        <td>
                            <?php
                            $payment_status = $order['payment_status'] ?? 'pending';
                            $payment_class = $payment_status === 'paid' ? 'success' : 'warning';
                            ?>
                            <span class="badge-custom badge-<?= $payment_class ?>">
                                <?= ucfirst($payment_status) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $status = $order['status'] ?? 'pending';
                            $status_class = 'info';
                            if ($status === 'delivered') $status_class = 'success';
                            elseif ($status === 'cancelled') $status_class = 'danger';
                            elseif ($status === 'processing' || $status === 'shipped') $status_class = 'warning';
                            ?>
                            <span class="badge-custom badge-<?= $status_class ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="view-order.php?id=<?= $order['id'] ?>"
                                   class="btn-custom btn-outline-custom"
                                   style="padding: 6px 12px; font-size: 0.85rem;"
                                   title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
