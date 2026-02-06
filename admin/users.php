<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) { redirect('login.php'); }

// Get users from database
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user statistics
    $total_users = count($users);
    $admin_users = count(array_filter($users, fn($u) => $u['is_admin'] == 1));
    $regular_users = $total_users - $admin_users;

} catch (PDOException $e) {
    $users = [];
    $total_users = 0;
    $admin_users = 0;
    $regular_users = 0;
    error_log('Error fetching users: ' . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-people"></i> User Management
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Manage customer accounts and permissions</p>
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
                <div class="stat-card-title">Total Users</div>
                <div class="stat-card-value"><?= $total_users ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Customers</div>
                <div class="stat-card-value"><?= $regular_users ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-person"></i>
            </div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-title">Administrators</div>
                <div class="stat-card-value"><?= $admin_users ?></div>
            </div>
            <div class="stat-card-icon">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">
            <i class="bi bi-list-ul"></i> All Users
        </h3>
    </div>

    <?php if (empty($users)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="bi bi-inbox" style="font-size: 4rem; color: var(--admin-text-light); opacity: 0.3;"></i>
            <h3 style="margin-top: 20px; color: var(--admin-text-light);">No Users Yet</h3>
            <p style="color: var(--admin-text-light);">User accounts will appear here when customers register</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong>#<?= $user['id'] ?></strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--admin-primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <strong><?= htmlspecialchars($user['name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                        <td>
                            <?php if ($user['is_admin'] == 1): ?>
                                <span class="badge-custom badge-danger">
                                    <i class="bi bi-shield-fill-check"></i> Admin
                                </span>
                            <?php else: ?>
                                <span class="badge-custom badge-info">
                                    <i class="bi bi-person"></i> Customer
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('M d, Y', strtotime($user['created_at'])) ?><br>
                            <small class="text-muted"><?= date('h:i A', strtotime($user['created_at'])) ?></small>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="edit-user.php?id=<?= $user['id'] ?>"
                                   class="btn-custom btn-outline-custom"
                                   style="padding: 6px 12px; font-size: 0.85rem;"
                                   title="Edit User">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($user['is_admin'] != 1): ?>
                                <a href="delete-user.php?id=<?= $user['id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this user?')"
                                   class="btn-custom"
                                   style="padding: 6px 12px; font-size: 0.85rem; background: #dc3545; color: white;"
                                   title="Delete User">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
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
