<?php
/**
 * AJAX Handler - Validate Coupon Code
 * Validates a coupon code and returns discount information
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get coupon code
$couponCode = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';

if (empty($couponCode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a coupon code.'
    ]);
    exit;
}

try {
    // Check if coupon exists and is valid
    $stmt = $pdo->prepare("
        SELECT *
        FROM coupons
        WHERE code = ?
        AND is_active = 1
        AND (valid_from IS NULL OR valid_from <= NOW())
        AND (valid_until IS NULL OR valid_until >= NOW())
        AND (usage_limit IS NULL OR used_count < usage_limit)
    ");
    $stmt->execute([$couponCode]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired coupon code.'
        ]);
        exit;
    }

    // Store coupon in session
    $_SESSION['applied_coupon'] = [
        'id' => $coupon['id'],
        'code' => $coupon['code'],
        'discount_type' => $coupon['discount_type'],
        'discount_value' => $coupon['discount_value'],
        'min_purchase_amount' => $coupon['min_purchase_amount'],
        'max_discount_amount' => $coupon['max_discount_amount']
    ];

    // Format success message
    if ($coupon['discount_type'] === 'percentage') {
        $discountText = $coupon['discount_value'] . '% off';
    } else {
        $discountText = '₹' . number_format($coupon['discount_value'], 0) . ' off';
    }

    $message = "Coupon applied! You get {$discountText}";
    if ($coupon['min_purchase_amount'] > 0) {
        $message .= " on orders above ₹" . number_format($coupon['min_purchase_amount'], 0);
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'coupon' => [
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value'],
            'min_purchase_amount' => $coupon['min_purchase_amount'],
            'max_discount_amount' => $coupon['max_discount_amount']
        ]
    ]);

} catch (PDOException $e) {
    error_log('Error validating coupon: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
