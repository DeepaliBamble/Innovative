<?php
/**
 * AJAX Handler - Remove an applied coupon (or clear all).
 * Returns the recomputed discount state for the remaining coupons.
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// CSRF check (accepts token in POST or X-CSRF-Token header for AJAX)
$csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
    exit;
}

$code = strtoupper(trim($_POST['coupon_code'] ?? ''));

try {
    $userId = isLoggedIn() ? getCurrentUserId() : null;

    $appliedCodes = isset($_SESSION['applied_coupons']) && is_array($_SESSION['applied_coupons'])
        ? $_SESSION['applied_coupons'] : [];

    if ($code === '' || $code === 'ALL') {
        // Clear all applied coupons
        $appliedCodes = [];
    } else {
        $appliedCodes = array_values(array_filter(
            $appliedCodes,
            fn($c) => strtoupper($c) !== $code
        ));
    }

    $subtotal = getCartSubtotal($pdo, $userId);

    // Re-evaluate the remaining codes against the current cart
    $result = evaluateCoupons($pdo, $appliedCodes, $subtotal, $userId);
    $_SESSION['applied_coupons'] = array_column($result['applied'], 'code');

    echo json_encode([
        'success' => true,
        'message' => 'Coupon removed.',
        'applied' => $result['applied'],
        'discount_amount' => $result['total_discount'],
        'subtotal' => $result['subtotal'],
        'total' => round($result['subtotal'] - $result['total_discount'], 2),
        'max_coupons' => MAX_STACKED_COUPONS,
    ]);

} catch (PDOException $e) {
    error_log('Error removing coupon: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
