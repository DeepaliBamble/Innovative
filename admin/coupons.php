<?php
require_once __DIR__ . '/../includes/init.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success_message = '';
$editing = null;

// Helper: read/normalize coupon input
function readCouponInput(array $src): array {
    $code = strtoupper(trim($src['code'] ?? ''));
    $description = trim($src['description'] ?? '');
    $discountType = ($src['discount_type'] ?? 'percentage') === 'fixed' ? 'fixed' : 'percentage';
    $discountValue = (float)($src['discount_value'] ?? 0);
    $minPurchase = (float)($src['min_purchase_amount'] ?? 0);
    $maxDiscount = isset($src['max_discount_amount']) && $src['max_discount_amount'] !== '' ? (float)$src['max_discount_amount'] : null;
    $usageLimit = isset($src['usage_limit']) && $src['usage_limit'] !== '' ? (int)$src['usage_limit'] : null;
    $perUserLimit = isset($src['per_user_limit']) && $src['per_user_limit'] !== '' ? (int)$src['per_user_limit'] : null;
    $validFrom = trim($src['valid_from'] ?? '');
    $validUntil = trim($src['valid_until'] ?? '');
    $isActive = !empty($src['is_active']) ? 1 : 0;
    $newUserOnly = !empty($src['new_user_only']) ? 1 : 0;
    $exclusive = !empty($src['exclusive']) ? 1 : 0;
    $showOnSite = !empty($src['show_on_site']) ? 1 : 0;

    return compact(
        'code', 'description', 'discountType', 'discountValue',
        'minPurchase', 'maxDiscount', 'usageLimit', 'perUserLimit',
        'validFrom', 'validUntil', 'isActive', 'newUserOnly', 'exclusive', 'showOnSite'
    );
}

function validateCouponInput(array $d): array {
    $errs = [];
    if ($d['code'] === '' || strlen($d['code']) > 50 || !preg_match('/^[A-Z0-9_\-]+$/', $d['code'])) {
        $errs[] = 'Coupon code is required (letters, digits, underscore or hyphen only, max 50 chars).';
    }
    if ($d['discountValue'] <= 0) {
        $errs[] = 'Discount value must be greater than zero.';
    }
    if ($d['discountType'] === 'percentage' && $d['discountValue'] > 100) {
        $errs[] = 'Percentage discount cannot exceed 100%.';
    }
    if ($d['minPurchase'] < 0) {
        $errs[] = 'Minimum purchase amount cannot be negative.';
    }
    if ($d['maxDiscount'] !== null && $d['maxDiscount'] < 0) {
        $errs[] = 'Maximum discount cap cannot be negative.';
    }
    if ($d['usageLimit'] !== null && $d['usageLimit'] < 0) {
        $errs[] = 'Total usage limit cannot be negative.';
    }
    if ($d['perUserLimit'] !== null && $d['perUserLimit'] < 0) {
        $errs[] = 'Per-user limit cannot be negative.';
    }
    if ($d['validFrom'] !== '' && !strtotime($d['validFrom'])) {
        $errs[] = '"Valid from" is not a valid date/time.';
    }
    if ($d['validUntil'] !== '' && !strtotime($d['validUntil'])) {
        $errs[] = '"Valid until" is not a valid date/time.';
    }
    if ($d['validFrom'] !== '' && $d['validUntil'] !== '' && strtotime($d['validFrom']) > strtotime($d['validUntil'])) {
        $errs[] = '"Valid from" must be on or before "Valid until".';
    }
    return $errs;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $d = readCouponInput($_POST);
        $errors = validateCouponInput($d);

        // Uniqueness check on code
        if (!$errors) {
            $checkSql = "SELECT id FROM coupons WHERE code = ?";
            $params = [$d['code']];
            if ($action === 'update') {
                $checkSql .= " AND id <> ?";
                $params[] = (int)$_POST['coupon_id'];
            }
            $chk = $pdo->prepare($checkSql);
            $chk->execute($params);
            if ($chk->fetch()) {
                $errors[] = 'Another coupon with this code already exists.';
            }
        }

        if (!$errors) {
            $validFromSql = $d['validFrom'] !== '' ? date('Y-m-d H:i:s', strtotime($d['validFrom'])) : null;
            $validUntilSql = $d['validUntil'] !== '' ? date('Y-m-d H:i:s', strtotime($d['validUntil'])) : null;

            if ($action === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO coupons (code, description, discount_type, discount_value,
                        min_purchase_amount, max_discount_amount, usage_limit, per_user_limit,
                        valid_from, valid_until, is_active, new_user_only, exclusive, show_on_site)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $d['code'], $d['description'], $d['discountType'], $d['discountValue'],
                    $d['minPurchase'], $d['maxDiscount'], $d['usageLimit'], $d['perUserLimit'],
                    $validFromSql, $validUntilSql, $d['isActive'], $d['newUserOnly'], $d['exclusive'], $d['showOnSite']
                ]);
                $success_message = 'Coupon created successfully.';
            } else {
                $couponId = (int)$_POST['coupon_id'];
                $stmt = $pdo->prepare("
                    UPDATE coupons SET
                        code = ?, description = ?, discount_type = ?, discount_value = ?,
                        min_purchase_amount = ?, max_discount_amount = ?, usage_limit = ?,
                        per_user_limit = ?, valid_from = ?, valid_until = ?, is_active = ?,
                        new_user_only = ?, exclusive = ?, show_on_site = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $d['code'], $d['description'], $d['discountType'], $d['discountValue'],
                    $d['minPurchase'], $d['maxDiscount'], $d['usageLimit'], $d['perUserLimit'],
                    $validFromSql, $validUntilSql, $d['isActive'], $d['newUserOnly'], $d['exclusive'], $d['showOnSite'], $couponId
                ]);
                $success_message = 'Coupon updated successfully.';
            }
        } else {
            // Preserve user input so the form re-renders with the values they entered
            $editing = [
                'id' => (int)($_POST['coupon_id'] ?? 0),
                'code' => $d['code'],
                'description' => $d['description'],
                'discount_type' => $d['discountType'],
                'discount_value' => $d['discountValue'],
                'min_purchase_amount' => $d['minPurchase'],
                'max_discount_amount' => $d['maxDiscount'],
                'usage_limit' => $d['usageLimit'],
                'per_user_limit' => $d['perUserLimit'],
                'valid_from' => $d['validFrom'],
                'valid_until' => $d['validUntil'],
                'is_active' => $d['isActive'],
                'new_user_only' => $d['newUserOnly'],
                'exclusive' => $d['exclusive'],
                'show_on_site' => $d['showOnSite'],
                'used_count' => 0,
            ];
        }
    } elseif ($action === 'toggle' && !empty($_POST['coupon_id'])) {
        $stmt = $pdo->prepare("UPDATE coupons SET is_active = 1 - is_active WHERE id = ?");
        $stmt->execute([(int)$_POST['coupon_id']]);
        $success_message = 'Coupon status updated.';
    } elseif ($action === 'delete' && !empty($_POST['coupon_id'])) {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([(int)$_POST['coupon_id']]);
        $success_message = 'Coupon deleted.';
    }
}

// Edit mode via GET
if (isset($_GET['edit']) && !$editing) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $editing = [
            'id' => (int)$row['id'],
            'code' => $row['code'],
            'description' => $row['description'],
            'discount_type' => $row['discount_type'],
            'discount_value' => $row['discount_value'],
            'min_purchase_amount' => $row['min_purchase_amount'],
            'max_discount_amount' => $row['max_discount_amount'],
            'usage_limit' => $row['usage_limit'],
            'per_user_limit' => $row['per_user_limit'] ?? null,
            'valid_from' => $row['valid_from'] ? date('Y-m-d\TH:i', strtotime($row['valid_from'])) : '',
            'valid_until' => $row['valid_until'] ? date('Y-m-d\TH:i', strtotime($row['valid_until'])) : '',
            'is_active' => (int)$row['is_active'],
            'new_user_only' => (int)($row['new_user_only'] ?? 0),
            'show_on_site' => (int)($row['show_on_site'] ?? 0),
            'used_count' => (int)$row['used_count'],
        ];
    }
}

// Fetch all coupons for the list
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY is_active DESC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$total_coupons = count($coupons);
$active_coupons = count(array_filter($coupons, fn($c) => (int)$c['is_active'] === 1));
$total_uses = array_sum(array_column($coupons, 'used_count'));

include 'includes/header.php';
?>

<div class="container-fluid">
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="bi bi-ticket-perforated-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= $total_coupons ?></h3>
                            <p class="text-muted mb-0">Total Coupons</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= $active_coupons ?></h3>
                            <p class="text-muted mb-0">Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="bi bi-bar-chart-fill" style="font-size: 2rem;"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0"><?= $total_uses ?></h3>
                            <p class="text-muted mb-0">Total Uses</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-<?= $editing ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
                <?= $editing ? 'Edit Coupon' : 'Add New Coupon' ?>
            </h5>
            <?php if ($editing): ?>
                <a href="coupons.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel edit
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="<?= $editing && !empty($editing['id']) ? 'update' : 'create' ?>">
                <?php if ($editing && !empty($editing['id'])): ?>
                    <input type="hidden" name="coupon_id" value="<?= (int)$editing['id'] ?>">
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control text-uppercase"
                           value="<?= htmlspecialchars($editing['code'] ?? '') ?>"
                           placeholder="e.g. WELCOME10" required maxlength="50" style="text-transform:uppercase;">
                    <small class="text-muted">Letters, digits, underscore or hyphen only.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                    <select name="discount_type" class="form-select" required>
                        <option value="percentage" <?= ($editing['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                        <option value="fixed" <?= ($editing['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed amount (₹)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="discount_value" class="form-control"
                           value="<?= htmlspecialchars($editing['discount_value'] ?? '') ?>" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control"
                           value="<?= htmlspecialchars($editing['description'] ?? '') ?>"
                           placeholder="e.g. 10% off for first-time customers">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Minimum Purchase (₹)</label>
                    <input type="number" step="0.01" min="0" name="min_purchase_amount" class="form-control"
                           value="<?= htmlspecialchars($editing['min_purchase_amount'] ?? '0') ?>">
                    <small class="text-muted">Cart subtotal must be at least this much.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Max Discount Cap (₹)</label>
                    <input type="number" step="0.01" min="0" name="max_discount_amount" class="form-control"
                           value="<?= htmlspecialchars($editing['max_discount_amount'] ?? '') ?>"
                           placeholder="Optional">
                    <small class="text-muted">Only applies to percentage coupons.</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total Usage Limit</label>
                    <input type="number" min="0" name="usage_limit" class="form-control"
                           value="<?= htmlspecialchars($editing['usage_limit'] ?? '') ?>"
                           placeholder="Unlimited">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Per-User Limit</label>
                    <input type="number" min="0" name="per_user_limit" class="form-control"
                           value="<?= htmlspecialchars($editing['per_user_limit'] ?? '') ?>"
                           placeholder="Unlimited">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Valid From</label>
                    <input type="datetime-local" name="valid_from" class="form-control"
                           value="<?= htmlspecialchars($editing['valid_from'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valid Until</label>
                    <input type="datetime-local" name="valid_until" class="form-control"
                           value="<?= htmlspecialchars($editing['valid_until'] ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1"
                               <?= (!isset($editing) || (int)($editing['is_active'] ?? 1) === 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="new_user_only" id="new_user_only" value="1"
                               <?= !empty($editing['new_user_only']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="new_user_only">New customers only (first order)</label>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="show_on_site" id="show_on_site" value="1"
                               <?= !empty($editing['show_on_site']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_on_site">Show on site (advertise in cart/checkout)</label>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="exclusive" id="exclusive" value="1"
                               <?= !empty($editing['exclusive']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="exclusive">Exclusive (cannot be combined with other coupons)</label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        <?= $editing && !empty($editing['id']) ? 'Update Coupon' : 'Create Coupon' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>All Coupons</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($coupons)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-ticket-perforated" style="font-size: 3rem;"></i>
                    <p class="mt-3 mb-0">No coupons yet. Add one above to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Min. Purchase</th>
                                <th>Usage</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $c): ?>
                                <?php
                                $discountLabel = $c['discount_type'] === 'percentage'
                                    ? rtrim(rtrim(number_format((float)$c['discount_value'], 2), '0'), '.') . '%'
                                    : '₹' . number_format((float)$c['discount_value'], 0);
                                $usageLabel = (int)$c['used_count'] . ' / ' . ($c['usage_limit'] !== null ? (int)$c['usage_limit'] : '∞');
                                $validityParts = [];
                                if ($c['valid_from']) $validityParts[] = 'From ' . date('M d, Y', strtotime($c['valid_from']));
                                if ($c['valid_until']) $validityParts[] = 'Until ' . date('M d, Y', strtotime($c['valid_until']));
                                $validityLabel = $validityParts ? implode('<br>', $validityParts) : '<span class="text-muted">Always</span>';
                                $isExpired = $c['valid_until'] && strtotime($c['valid_until']) < time();
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?= htmlspecialchars($c['code']) ?></strong>
                                        <?php if (!empty($c['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($c['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= $discountLabel ?></strong>
                                        <?php if ($c['discount_type'] === 'percentage' && $c['max_discount_amount']): ?>
                                            <br><small class="text-muted">Max ₹<?= number_format((float)$c['max_discount_amount'], 0) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹<?= number_format((float)$c['min_purchase_amount'], 0) ?></td>
                                    <td>
                                        <?= $usageLabel ?>
                                        <?php if (!empty($c['per_user_limit'])): ?>
                                            <br><small class="text-muted"><?= (int)$c['per_user_limit'] ?>/user</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $validityLabel ?></td>
                                    <td>
                                        <?php if ((int)$c['is_active'] === 0): ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php elseif ($isExpired): ?>
                                            <span class="badge bg-danger">Expired</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                        <?php if (!empty($c['new_user_only'])): ?>
                                            <br><span class="badge bg-info mt-1">New customers</span>
                                        <?php endif; ?>
                                        <?php if (!empty($c['show_on_site'])): ?>
                                            <br><span class="badge bg-primary mt-1">On site</span>
                                        <?php endif; ?>
                                        <?php if (!empty($c['exclusive'])): ?>
                                            <br><span class="badge bg-warning text-dark mt-1">Exclusive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit=<?= (int)$c['id'] ?>#top" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="coupon_id" value="<?= (int)$c['id'] ?>">
                                                <button type="submit" class="btn btn-outline-warning" title="Toggle active">
                                                    <i class="bi bi-toggle-<?= (int)$c['is_active'] === 1 ? 'on' : 'off' ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete coupon <?= htmlspecialchars($c['code'], ENT_QUOTES) ?>? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="coupon_id" value="<?= (int)$c['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
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
