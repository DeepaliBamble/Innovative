<?php
/**
 * AJAX Handler - Validate Coupon Code
 * Validates a coupon code against the current cart and returns discount info.
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

// Rate limit: 10 attempts per 5 minutes per session
if (!checkRateLimit($pdo, 'validate_coupon', 10, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Please try again in a few minutes.']);
    exit;
}

$couponCode = strtoupper(trim($_POST['coupon_code'] ?? ''));

if ($couponCode === '') {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code.']);
    exit;
}

try {
    $userId = isLoggedIn() ? getCurrentUserId() : null;
    $subtotal = getCartSubtotal($pdo, $userId);

    if ($subtotal <= 0) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty. Add items before applying a coupon.']);
        exit;
    }

    // Current set of applied codes (server-side source of truth)
    $appliedCodes = isset($_SESSION['applied_coupons']) && is_array($_SESSION['applied_coupons'])
        ? $_SESSION['applied_coupons'] : [];

    // Already applied?
    if (in_array($couponCode, array_map('strtoupper', $appliedCodes), true)) {
        echo json_encode(['success' => false, 'message' => 'This coupon is already applied.']);
        exit;
    }

    // Trial = existing applied codes + the new candidate (candidate evaluated last)
    $trialCodes = array_merge($appliedCodes, [$couponCode]);
    $result = evaluateCoupons($pdo, $trialCodes, $subtotal, $userId);

    // Was the new candidate accepted?
    $acceptedCodes = array_column($result['applied'], 'code');
    $candidateAccepted = in_array($couponCode, array_map('strtoupper', $acceptedCodes), true);

    if (!$candidateAccepted) {
        // Find the rejection reason for this specific code
        $reason = 'Could not apply this coupon.';
        foreach ($result['rejected'] as $r) {
            if (strtoupper($r['code']) === $couponCode) {
                $reason = $r['reason'];
                break;
            }
        }
        echo json_encode(['success' => false, 'message' => $reason]);
        exit;
    }

    // Persist the accepted set of codes
    $_SESSION['applied_coupons'] = array_column($result['applied'], 'code');

    echo json_encode([
        'success' => true,
        'message' => 'Coupon applied!',
        'applied' => $result['applied'],
        'discount_amount' => $result['total_discount'],
        'subtotal' => $result['subtotal'],
        'total' => round($result['subtotal'] - $result['total_discount'], 2),
        'max_coupons' => MAX_STACKED_COUPONS,
    ]);

} catch (PDOException $e) {
    error_log('Error validating coupon: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
