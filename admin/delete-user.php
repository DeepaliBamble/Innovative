<?php
/**
 * Delete User Handler
 * Handles user deletion from the admin panel
 */

require __DIR__ . '/../includes/init.php';

// Check admin authentication
if (!isAdmin()) {
    redirect('login.php');
    exit;
}

// Get user ID from URL
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    setFlashMessage('error', 'Invalid user ID');
    redirect('users.php');
    exit;
}

// Prevent admin from deleting themselves
if ($user_id == getCurrentAdminId()) {
    setFlashMessage('error', 'You cannot delete your own account');
    redirect('users.php');
    exit;
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name, email, is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        setFlashMessage('error', 'User not found');
        redirect('users.php');
        exit;
    }

    // Prevent deletion of admin users (additional safety check)
    if ($user['is_admin'] == 1) {
        setFlashMessage('error', 'Cannot delete administrator accounts');
        redirect('users.php');
        exit;
    }

    // Start transaction
    $pdo->beginTransaction();

    // Delete related data first (due to foreign key constraints)

    // Delete user's cart items
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Delete user's wishlist
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Delete user's addresses
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Delete user's reviews
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Note: We keep orders for record-keeping purposes
    // You can optionally anonymize orders instead of keeping user reference
    // For now, orders will remain linked but user data will be gone

    // Finally, delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    // Commit transaction
    $pdo->commit();

    setFlashMessage('success', 'User "' . htmlspecialchars($user['name']) . '" has been deleted successfully');
    redirect('users.php');
    exit;

} catch (PDOException $e) {
    // Rollback on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error deleting user: ' . $e->getMessage());
    setFlashMessage('error', 'Failed to delete user. Please try again.');
    redirect('users.php');
    exit;
}
