<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) { redirect('login.php'); }

$errors = [];
$success = false;
$user = null;

// Get user ID from URL
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    redirect('users.php');
    exit;
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        setFlashMessage('error', 'User not found');
        redirect('users.php');
        exit;
    }
} catch (PDOException $e) {
    error_log('Error fetching user: ' . $e->getMessage());
    setFlashMessage('error', 'Error loading user data');
    redirect('users.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Invalid email format';
    }

    // Check if email is already used by another user
    if (!$errors) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $errors[] = 'Email is already in use by another user';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error occurred';
        }
    }

    // Password validation (if provided)
    if (!empty($new_password)) {
        if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        }
        if ($new_password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
    }

    // Update user if no errors
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Update with new password
                $hashedPassword = hashPassword($new_password);
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET name = ?, email = ?, phone = ?, is_admin = ?, is_active = ?, password = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone ?: null, $is_admin, $is_active, $hashedPassword, $user_id]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET name = ?, email = ?, phone = ?, is_admin = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $phone ?: null, $is_admin, $is_active, $user_id]);
            }

            setFlashMessage('success', 'User updated successfully');
            redirect('users.php');
            exit;

        } catch (PDOException $e) {
            error_log('Error updating user: ' . $e->getMessage());
            $errors[] = 'Failed to update user';
        }
    }

    // Re-fetch user data to show updated values
    $user['name'] = $name;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['is_admin'] = $is_admin;
    $user['is_active'] = $is_active;
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-pencil"></i> Edit User
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Update user information and permissions</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="users.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
</div>

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
<div class="content-card" style="background: #f8d7da; border-left: 4px solid #dc3545;">
    <h4 style="color: #721c24; margin: 0 0 10px 0;">
        <i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:
    </h4>
    <ul style="margin: 0; padding-left: 20px; color: #721c24;">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Edit User Form -->
<div class="content-card">
    <form method="POST" action="">
        <div class="row g-3">
            <!-- Name -->
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-person"></i> Full Name <span style="color: red;">*</span>
                </label>
                <input type="text"
                       name="name"
                       class="form-control-custom"
                       value="<?= htmlspecialchars($user['name']) ?>"
                       required>
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-envelope"></i> Email Address <span style="color: red;">*</span>
                </label>
                <input type="email"
                       name="email"
                       class="form-control-custom"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       required>
            </div>

            <!-- Phone -->
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-telephone"></i> Phone Number
                </label>
                <input type="tel"
                       name="phone"
                       class="form-control-custom"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                       placeholder="Optional">
            </div>

            <!-- User ID (Read-only) -->
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-hash"></i> User ID
                </label>
                <input type="text"
                       class="form-control-custom"
                       value="#<?= $user['id'] ?>"
                       readonly
                       style="background: #f8f9fa;">
            </div>

            <!-- New Password -->
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-key"></i> New Password
                </label>
                <input type="password"
                       name="new_password"
                       class="form-control-custom"
                       placeholder="Leave blank to keep current password">
                <small class="text-muted">Minimum <?= PASSWORD_MIN_LENGTH ?> characters</small>
            </div>

            <!-- Confirm Password -->
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-key-fill"></i> Confirm New Password
                </label>
                <input type="password"
                       name="confirm_password"
                       class="form-control-custom"
                       placeholder="Confirm new password">
            </div>

            <!-- Permissions -->
            <div class="col-12">
                <hr style="margin: 20px 0;">
                <h4 style="color: var(--admin-text); margin-bottom: 15px;">
                    <i class="bi bi-shield-check"></i> Permissions & Status
                </h4>
            </div>

            <!-- Admin Role -->
            <div class="col-md-6">
                <div class="form-check" style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <input type="checkbox"
                           name="is_admin"
                           id="is_admin"
                           class="form-check-input"
                           <?= $user['is_admin'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_admin" style="font-weight: 500;">
                        <i class="bi bi-shield-fill-check"></i> Administrator Access
                    </label>
                    <small class="d-block text-muted" style="margin-top: 5px;">
                        Grant full admin panel access
                    </small>
                </div>
            </div>

            <!-- Active Status -->
            <div class="col-md-6">
                <div class="form-check" style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <input type="checkbox"
                           name="is_active"
                           id="is_active"
                           class="form-check-input"
                           <?= $user['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active" style="font-weight: 500;">
                        <i class="bi bi-check-circle"></i> Account Active
                    </label>
                    <small class="d-block text-muted" style="margin-top: 5px;">
                        Allow user to login and access the site
                    </small>
                </div>
            </div>

            <!-- Account Info -->
            <div class="col-12">
                <div style="padding: 15px; background: #e7f3ff; border-radius: 8px; border-left: 4px solid #0d6efd;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <small class="text-muted">Account Created</small>
                            <div style="font-weight: 500; color: var(--admin-text);">
                                <?= date('M d, Y \a\t h:i A', strtotime($user['created_at'])) ?>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Last Updated</small>
                            <div style="font-weight: 500; color: var(--admin-text);">
                                <?= date('M d, Y \a\t h:i A', strtotime($user['updated_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="col-12" style="margin-top: 30px;">
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="users.php" class="btn-custom btn-outline-custom">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn-custom btn-primary-custom">
                        <i class="bi bi-check-circle"></i> Update User
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
