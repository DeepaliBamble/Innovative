<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) { redirect('login.php'); }

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($orderId <= 0) {
    $_SESSION['error_message'] = 'Invalid order ID';
    redirect('orders.php');
}

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

$validOrderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
$validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderStatus = $_POST['order_status'] ?? '';
    $paymentStatus = $_POST['payment_status'] ?? '';
    $trackingMessage = trim($_POST['tracking_message'] ?? '');

    if (!in_array($orderStatus, $validOrderStatuses, true) || !in_array($paymentStatus, $validPaymentStatuses, true)) {
        $_SESSION['error_message'] = 'Invalid order or payment status.';
        redirect('view-order.php?id=' . $orderId);
    }

    try {
        $pdo->beginTransaction();

        // Detect whether order_status actually changed so we only log real transitions
        $prevStmt = $pdo->prepare("SELECT order_status FROM orders WHERE id = ?");
        $prevStmt->execute([$orderId]);
        $previousStatus = $prevStmt->fetchColumn();

        $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$orderStatus, $paymentStatus, $orderId]);

        if ($previousStatus !== $orderStatus || $trackingMessage !== '') {
            $trackStmt = $pdo->prepare("INSERT INTO order_tracking (order_id, status, message, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
            $trackStmt->execute([
                $orderId,
                $orderStatus,
                $trackingMessage !== '' ? $trackingMessage : null,
                $_SESSION['admin_name'] ?? 'admin',
            ]);
        }

        $pdo->commit();
        $_SESSION['success_message'] = 'Order updated successfully.';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Order update failed: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Could not update order.';
    }

    redirect('view-order.php?id=' . $orderId);
}

$stmt = $pdo->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
    LIMIT 1
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    redirect('orders.php');
}

$itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

$trackingStmt = $pdo->prepare("SELECT * FROM order_tracking WHERE order_id = ? ORDER BY created_at DESC, id DESC");
$trackingStmt->execute([$orderId]);
$tracking = $trackingStmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-receipt"></i> Order <?= htmlspecialchars($order['order_number'] ?? '#' . $order['id']) ?>
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Placed on <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <a href="orders.php" class="btn-custom btn-outline-custom">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="bi bi-bag"></i> Items</h3>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><code><?= htmlspecialchars($item['product_sku'] ?? 'N/A') ?></code></td>
                                <td><?= (int) $item['quantity'] ?></td>
                                <td>₹<?= number_format((float) $item['price'], 2) ?></td>
                                <td><strong>₹<?= number_format((float) $item['subtotal'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="bi bi-clock-history"></i> Tracking History</h3>
            </div>
            <?php if (empty($tracking)): ?>
                <p class="text-muted mb-0">No tracking notes yet.</p>
            <?php else: ?>
                <div style="display: grid; gap: 12px;">
                    <?php foreach ($tracking as $row): ?>
                        <div style="border-left: 3px solid var(--admin-primary); padding-left: 12px;">
                            <strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $row['status']))) ?></strong>
                            <p class="mb-1"><?= htmlspecialchars($row['message'] ?? '') ?></p>
                            <small class="text-muted"><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?> by <?= htmlspecialchars($row['created_by'] ?? 'system') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="bi bi-person"></i> Customer</h3>
            </div>
            <p><strong><?= htmlspecialchars($order['customer_name'] ?: ($order['user_name'] ?? 'Guest')) ?></strong></p>
            <p class="mb-1"><i class="bi bi-envelope"></i> <?= htmlspecialchars($order['customer_email'] ?: ($order['user_email'] ?? 'N/A')) ?></p>
            <p class="mb-0"><i class="bi bi-telephone"></i> <?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></p>
        </div>

        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="bi bi-truck"></i> Shipping</h3>
            </div>
            <p class="mb-1"><strong><?= htmlspecialchars($order['shipping_full_name'] ?? '') ?></strong></p>
            <p class="mb-1"><?= htmlspecialchars($order['shipping_address_line1'] ?? '') ?></p>
            <?php if (!empty($order['shipping_address_line2'])): ?>
                <p class="mb-1"><?= htmlspecialchars($order['shipping_address_line2']) ?></p>
            <?php endif; ?>
            <p class="mb-0">
                <?= htmlspecialchars(trim(($order['shipping_city'] ?? '') . ', ' . ($order['shipping_state'] ?? '') . ' ' . ($order['shipping_postal_code'] ?? ''))) ?><br>
                <?= htmlspecialchars($order['shipping_country'] ?? 'India') ?>
            </p>
        </div>

        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="bi bi-currency-rupee"></i> Totals</h3>
            </div>
            <div style="display: grid; gap: 8px;">
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong>₹<?= number_format((float) $order['subtotal'], 2) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Shipping</span><strong>₹<?= number_format((float) $order['shipping_amount'], 2) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Tax</span><strong>₹<?= number_format((float) $order['tax_amount'], 2) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Discount</span><strong>-₹<?= number_format((float) $order['discount_amount'], 2) ?></strong></div>
                <hr>
                <div class="d-flex justify-content-between" style="font-size: 1.1rem;"><span>Total</span><strong>₹<?= number_format((float) $order['total_amount'], 2) ?></strong></div>
            </div>
        </div>

        <div class="content-card">
            <div class="content-card-header">
                <h3 class="content-card-title"><i class="bi bi-pencil-square"></i> Update Order</h3>
            </div>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Order Status</label>
                    <select name="order_status" class="form-control">
                        <?php foreach ($validOrderStatuses as $status): ?>
                            <option value="<?= $status ?>" <?= $order['order_status'] === $status ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-control">
                        <?php foreach ($validPaymentStatuses as $status): ?>
                            <option value="<?= $status ?>" <?= $order['payment_status'] === $status ? 'selected' : '' ?>>
                                <?= ucfirst($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tracking Note</label>
                    <textarea name="tracking_message" class="form-control" rows="3" placeholder="Optional note for this update"></textarea>
                </div>
                <button type="submit" class="btn-custom btn-primary-custom w-100">
                    <i class="bi bi-check-circle"></i> Save Update
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
