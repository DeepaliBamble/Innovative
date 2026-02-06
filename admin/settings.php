<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) { redirect('login.php'); }

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $site_name = sanitize($_POST['site_name'] ?? '');
        $admin_email = sanitize($_POST['admin_email'] ?? '');
        $contact_phone = sanitize($_POST['contact_phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');

        // Here you would typically save settings to database
        // For now, just show success message
        $success_message = 'Settings updated successfully!';

    } catch (Exception $e) {
        $error_message = 'Error updating settings: ' . $e->getMessage();
    }
}

// Default values
$site_name = 'Innovative Furniture';
$admin_email = 'admin@innovative.com';
$contact_phone = '+91 1234567890';
$address = 'Your furniture store address';

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="content-card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 1.5rem; margin: 0; color: var(--admin-text);">
                <i class="bi bi-gear"></i> System Settings
            </h2>
            <p class="text-muted" style="margin: 8px 0 0 0;">Configure your store settings and preferences</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="dashboard.php" class="btn-custom btn-outline-custom">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success_message): ?>
    <div class="content-card" style="background: #d4edda; border: 1px solid #c3e6cb;">
        <div style="display: flex; align-items: center; gap: 12px; color: #155724;">
            <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
            <span><?= htmlspecialchars($success_message) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="content-card" style="background: #f8d7da; border: 1px solid #f5c6cb;">
        <div style="display: flex; align-items: center; gap: 12px; color: #721c24;">
            <i class="bi bi-exclamation-circle" style="font-size: 1.5rem;"></i>
            <span><?= htmlspecialchars($error_message) ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Settings Form -->
<form method="post" action="settings.php">
    <div class="content-card">
        <div class="content-card-header">
            <h3 class="content-card-title">
                <i class="bi bi-building"></i> General Settings
            </h3>
        </div>

        <div style="display: grid; gap: 20px;">
            <div>
                <label class="form-label" for="site_name">
                    <strong>Site Name</strong>
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="site_name"
                    name="site_name"
                    value="<?= htmlspecialchars($site_name) ?>"
                    placeholder="Enter your site name"
                    style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem;"
                >
            </div>

            <div>
                <label class="form-label" for="admin_email">
                    <strong>Admin Email</strong>
                </label>
                <input
                    type="email"
                    class="form-control"
                    id="admin_email"
                    name="admin_email"
                    value="<?= htmlspecialchars($admin_email) ?>"
                    placeholder="admin@example.com"
                    style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem;"
                >
            </div>

            <div>
                <label class="form-label" for="contact_phone">
                    <strong>Contact Phone</strong>
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="contact_phone"
                    name="contact_phone"
                    value="<?= htmlspecialchars($contact_phone) ?>"
                    placeholder="+91 1234567890"
                    style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem;"
                >
            </div>

            <div>
                <label class="form-label" for="address">
                    <strong>Store Address</strong>
                </label>
                <textarea
                    class="form-control"
                    id="address"
                    name="address"
                    rows="3"
                    placeholder="Enter your store address"
                    style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem; resize: vertical;"
                ><?= htmlspecialchars($address) ?></textarea>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="content-card-header">
            <h3 class="content-card-title">
                <i class="bi bi-palette"></i> Appearance Settings
            </h3>
        </div>

        <div style="display: grid; gap: 20px;">
            <div>
                <label class="form-label">
                    <strong>Primary Color</strong>
                </label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input
                        type="color"
                        name="primary_color"
                        value="#9e6747"
                        style="width: 60px; height: 45px; border: 2px solid var(--admin-border); border-radius: 8px; cursor: pointer;"
                    >
                    <span class="text-muted">Current theme color</span>
                </div>
            </div>

            <div>
                <label class="form-label">
                    <strong>Logo Upload</strong>
                </label>
                <input
                    type="file"
                    class="form-control"
                    name="logo"
                    accept="image/*"
                    style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem;"
                >
                <small class="text-muted">Recommended size: 200x200 pixels</small>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="content-card-header">
            <h3 class="content-card-title">
                <i class="bi bi-envelope"></i> Email Settings
            </h3>
        </div>

        <div style="display: grid; gap: 20px;">
            <div>
                <label class="form-label" for="smtp_host">
                    <strong>SMTP Host</strong>
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="smtp_host"
                    name="smtp_host"
                    placeholder="smtp.gmail.com"
                    style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem;"
                >
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <label class="form-label" for="smtp_port">
                        <strong>SMTP Port</strong>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="smtp_port"
                        name="smtp_port"
                        placeholder="587"
                        style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem;"
                    >
                </div>

                <div>
                    <label class="form-label" for="smtp_encryption">
                        <strong>Encryption</strong>
                    </label>
                    <select
                        class="form-control"
                        id="smtp_encryption"
                        name="smtp_encryption"
                        style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: 8px; width: 100%; font-size: 0.95rem;"
                    >
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="content-card">
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button type="reset" class="btn-custom btn-outline-custom">
                <i class="bi bi-arrow-clockwise"></i> Reset Changes
            </button>
            <button type="submit" class="btn-custom btn-primary-custom">
                <i class="bi bi-check-circle"></i> Save Settings
            </button>
        </div>
    </div>
</form>

<style>
    .form-control:focus {
        border-color: var(--admin-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(158, 103, 71, 0.1);
    }
</style>

<?php include 'includes/footer.php'; ?>
