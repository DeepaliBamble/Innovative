<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

// Fetch statistics from database
try {
    // Get total products
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    // Get total orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    // Get pending orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    // Get recent orders
    $stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o
                         LEFT JOIN users u ON o.user_id = u.id
                         ORDER BY o.created_at DESC LIMIT 5");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $total_products = 0;
    $total_orders = 0;
    $total_users = 0;
    $pending_orders = 0;
    $recent_orders = [];
}

include 'includes/header.php';
?>

<!-- Welcome Section -->
<div class="content-card">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <div>
            <h2 style="font-size: 1.75rem; margin-bottom: 8px; color: var(--admin-text);">
                Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!
            </h2>
            <p class="text-muted" style="margin: 0;">Here's what's happening with your furniture store today.</p>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 16px;">
            <a href="add-product.php" class="btn-custom btn-primary-custom">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
            <a href="products.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-box-seam"></i> Manage Products
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <!-- Total Products Card -->
    <div class="stat-card primary">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Total Products</div>
                <div class="stat-card-value"><?= number_format($total_products) ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
        <div class="stat-card-trend up">
            <i class="bi bi-arrow-up"></i>
            <span>View all products</span>
        </div>
    </div>

    <!-- Total Orders Card -->
    <div class="stat-card success">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Total Orders</div>
                <div class="stat-card-value"><?= number_format($total_orders) ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-cart-check"></i>
            </div>
        </div>
        <div class="stat-card-trend up">
            <i class="bi bi-arrow-up"></i>
            <span>View all orders</span>
        </div>
    </div>

    <!-- Total Users Card -->
    <div class="stat-card warning">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Total Users</div>
                <div class="stat-card-value"><?= number_format($total_users) ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-people"></i>
            </div>
        </div>
        <div class="stat-card-trend">
            <i class="bi bi-person-plus"></i>
            <span>Registered customers</span>
        </div>
    </div>

    <!-- Pending Orders Card -->
    <div class="stat-card info">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Pending Orders</div>
                <div class="stat-card-value"><?= number_format($pending_orders) ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
        <div class="stat-card-trend">
            <i class="bi bi-hourglass-split"></i>
            <span>Needs attention</span>
        </div>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="content-card">
    <div class="content-card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="content-card-title">
                <i class="bi bi-clock-history"></i> Recent Orders
            </h3>
            <a href="orders.php" class="btn-custom btn-outline-custom" style="padding: 8px 16px; font-size: 0.875rem;">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <?php if (empty($recent_orders)): ?>
        <div style="text-align: center; padding: 40px; color: var(--admin-text-light);">
            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 16px;">No orders yet</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($order['id']) ?></strong></td>
                            <td><?= htmlspecialchars($order['user_name'] ?? 'Guest') ?></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td><strong>₹<?= number_format($order['total_amount'] ?? 0, 2) ?></strong></td>
                            <td>
                                <?php
                                $status = $order['order_status'] ?? 'pending';
                                $badge_class = 'badge-info';
                                if ($status === 'delivered') $badge_class = 'badge-success';
                                elseif ($status === 'cancelled') $badge_class = 'badge-danger';
                                elseif ($status === 'processing') $badge_class = 'badge-warning';
                                elseif ($status === 'shipped') $badge_class = 'badge-primary';
                                ?>
                                <span class="badge-custom <?= $badge_class ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </td>
                            <td>
                                <a href="orders.php?id=<?= $order['id'] ?>" class="btn-custom btn-outline-custom" style="padding: 6px 12px; font-size: 0.8rem;">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-lightning"></i> Quick Actions
        </h3>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <a href="add-product.php" style="display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--admin-bg); border-radius: 8px; text-decoration: none; color: var(--admin-text); transition: all 0.2s ease;">
            <i class="bi bi-plus-circle" style="font-size: 1.5rem; color: var(--admin-primary);"></i>
            <div>
                <div style="font-weight: 600;">Add Product</div>
                <div style="font-size: 0.85rem; color: var(--admin-text-light);">Create new listing</div>
            </div>
        </a>

        <a href="orders.php" style="display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--admin-bg); border-radius: 8px; text-decoration: none; color: var(--admin-text); transition: all 0.2s ease;">
            <i class="bi bi-cart-check" style="font-size: 1.5rem; color: #28a745;"></i>
            <div>
                <div style="font-weight: 600;">Manage Orders</div>
                <div style="font-size: 0.85rem; color: var(--admin-text-light);">Process orders</div>
            </div>
        </a>

        <a href="users.php" style="display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--admin-bg); border-radius: 8px; text-decoration: none; color: var(--admin-text); transition: all 0.2s ease;">
            <i class="bi bi-people" style="font-size: 1.5rem; color: #ffc107;"></i>
            <div>
                <div style="font-weight: 600;">View Users</div>
                <div style="font-size: 0.85rem; color: var(--admin-text-light);">Customer list</div>
            </div>
        </a>

        <a href="settings.php" style="display: flex; align-items: center; gap: 12px; padding: 16px; background: var(--admin-bg); border-radius: 8px; text-decoration: none; color: var(--admin-text); transition: all 0.2s ease;">
            <i class="bi bi-gear" style="font-size: 1.5rem; color: #17a2b8;"></i>
            <div>
                <div style="font-weight: 600;">Settings</div>
                <div style="font-size: 0.85rem; color: var(--admin-text-light);">Configure store</div>
            </div>
        </a>
    </div>
</div>

<style>
    /* Add hover effects for quick actions */
    .content-card a[href]:hover {
        background: var(--admin-primary) !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(158, 103, 71, 0.2);
    }

    .content-card a[href]:hover i {
        color: white !important;
    }

    .content-card a[href]:hover div {
        color: white !important;
    }

    .table-responsive {
        overflow-x: auto;
    }
</style>

<?php include 'includes/footer.php'; ?>
