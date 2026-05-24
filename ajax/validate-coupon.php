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
    // Compute the current cart subtotal server-side (never trust client value)
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $cartStmt = $pdo->prepare("
            SELECT c.quantity, p.price, p.sale_price
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.is_active = 1
        ");
        $cartStmt->execute([$userId]);
    } else {
        $userId = null;
        $cartStmt = $pdo->prepare("
            SELECT c.quantity, p.price, p.sale_price
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ? AND p.is_active = 1
        ");
        $cartStmt->execute([session_id()]);
    }

    $cartRows = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
    $subtotal = 0.0;
    foreach ($cartRows as $row) {
        $price = !empty($row['sale_price']) ? (float)$row['sale_price'] : (float)$row['price'];
        $subtotal += $price * (int)$row['quantity'];
    }

    if ($subtotal <= 0) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty. Add items before applying a coupon.']);
        exit;
    }

    // Look up the coupon
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
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code.']);
        exit;
    }

    // Enforce minimum purchase amount
    $minPurchase = (float)($coupon['min_purchase_amount'] ?? 0);
    if ($minPurchase > 0 && $subtotal < $minPurchase) {
        $shortBy = $minPurchase - $subtotal;
        echo json_encode([
            'success' => false,
            'message' => 'This coupon requires a minimum purchase of ' . formatPrice($minPurchase)
                . '. Add ' . formatPrice($shortBy) . ' more to your cart.'
        ]);
        exit;
    }

    // Per-user usage limit (only enforceable for logged-in users)
    $perUserLimit = isset($coupon['per_user_limit']) ? (int)$coupon['per_user_limit'] : 0;
    if ($perUserLimit > 0 && $userId !== null) {
        $usageStmt = $pdo->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND user_id = ?");
        $usageStmt->execute([$coupon['id'], $userId]);
        $timesUsed = (int)$usageStmt->fetchColumn();
        if ($timesUsed >= $perUserLimit) {
            echo json_encode([
                'success' => false,
                'message' => 'You have already used this coupon the maximum number of times.'
            ]);
            exit;
        }
    }

    // Compute the actual discount this coupon will apply right now
    if ($coupon['discount_type'] === 'percentage') {
        $discount = ($subtotal * (float)$coupon['discount_value']) / 100.0;
        if (!empty($coupon['max_discount_amount']) && $discount > (float)$coupon['max_discount_amount']) {
            $discount = (float)$coupon['max_discount_amount'];
        }
    } else {
        $discount = (float)$coupon['discount_value'];
    }
    // Never let discount exceed subtotal
    if ($discount > $subtotal) {
        $discount = $subtotal;
    }

    // Store in session for checkout to use
    $_SESSION['applied_coupon'] = [
        'id' => (int)$coupon['id'],
        'code' => $coupon['code'],
        'discount_type' => $coupon['discount_type'],
        'discount_value' => (float)$coupon['discount_value'],
        'min_purchase_amount' => $minPurchase,
        'max_discount_amount' => $coupon['max_discount_amount'] !== null ? (float)$coupon['max_discount_amount'] : null,
    ];

    if ($coupon['discount_type'] === 'percentage') {
        $discountText = rtrim(rtrim(number_format((float)$coupon['discount_value'], 2), '0'), '.') . '% off';
    } else {
        $discountText = formatPrice((float)$coupon['discount_value']) . ' off';
    }

    echo json_encode([
        'success' => true,
        'message' => "Coupon applied! You get {$discountText}.",
        'coupon' => [
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => (float)$coupon['discount_value'],
            'min_purchase_amount' => $minPurchase,
            'max_discount_amount' => $coupon['max_discount_amount'] !== null ? (float)$coupon['max_discount_amount'] : null,
        ],
        'discount_amount' => round($discount, 2),
        'subtotal' => round($subtotal, 2),
        'total' => round($subtotal - $discount, 2),
    ]);

} catch (PDOException $e) {
    error_log('Error validating coupon: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
