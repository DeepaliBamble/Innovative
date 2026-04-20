<?php
/**
 * Create Order and Razorpay Order ID
 * Handles secure order creation with proper validation
 *
 * Security Features:
 * - Input validation and sanitization
 * - CSRF token validation
 * - SQL injection prevention (prepared statements)
 * - Stock verification
 * - Amount validation
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/razorpay-config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Verify CSRF Token
if (!isAjaxRequest()) {
    verifyCsrfOrDie();
} else {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCsrfToken($token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page and try again.']);
        exit;
    }
}

// Enforce Rate Limiting (max 10 order attempts per 15 minutes)
if (!checkRateLimit($pdo, 'create_order', 10, 900)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Too many order attempts. Please try again later.'
    ]);
    exit;
}

try {
    // Validate and sanitize input
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = validateEmail($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? ''); // Ideally use validatePhone($phone)
    $country = sanitize($_POST['country'] ?? 'India');
    $city = sanitize($_POST['city'] ?? '');
    $street = sanitize($_POST['street'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $postalCode = sanitize($_POST['postal_code'] ?? ''); // Ideally validatePostalCode($postalCode)
    $notes = sanitize($_POST['notes'] ?? '');
    $shippingMethod = sanitize($_POST['shipping_method'] ?? 'standard');
    $couponCode = sanitize($_POST['coupon_code'] ?? '');

    // Validate required fields
    if (empty($firstName) || empty($lastName)) {
        throw new Exception('First name and last name are required.');
    }

    if (!$email) {
        throw new Exception('Valid email address is required.');
    }

    if (empty($phone)) {
        throw new Exception('Phone number is required.');
    }

    if (empty($country) || empty($city) || empty($state) || empty($postalCode)) {
        throw new Exception('Complete shipping address is required.');
    }

    $customerName = $firstName . ' ' . $lastName;

    // Get cart items with security check
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $sessionId = null;
        $cartStmt = $pdo->prepare("
            SELECT c.*, p.name, p.sku, p.price, p.sale_price, p.image_path, p.stock_quantity
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.is_active = 1
        ");
        $cartStmt->execute([$userId]);
    } else {
        $sessionId = session_id();
        $userId = null;
        $cartStmt = $pdo->prepare("
            SELECT c.*, p.name, p.sku, p.price, p.sale_price, p.image_path, p.stock_quantity
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ? AND p.is_active = 1
        ");
        $cartStmt->execute([$sessionId]);
    }

    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        throw new Exception('Your cart is empty. Please add items to proceed.');
    }

    // Calculate totals and verify stock
    $subtotal = 0;
    foreach ($cartItems as $item) {
        // Check stock availability
        if ($item['stock_quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for '{$item['name']}'. Only {$item['stock_quantity']} available.");
        }

        // Calculate subtotal
        $itemPrice = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
        $subtotal += $itemPrice * $item['quantity'];
    }

    // Calculate shipping cost (in INR)
    $shippingCost = 0;
    if ($shippingMethod === 'express') {
        $shippingCost = 500.00; // ₹500 for express shipping
    }

    // Apply coupon if provided
    $discountAmount = 0;
    if (!empty($couponCode)) {
        $couponStmt = $pdo->prepare("
            SELECT * FROM coupons
            WHERE code = ?
            AND is_active = 1
            AND (usage_limit IS NULL OR used_count < usage_limit)
            AND (valid_from IS NULL OR valid_from <= NOW())
            AND (valid_until IS NULL OR valid_until >= NOW())
        ");
        $couponStmt->execute([$couponCode]);
        $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);

        if ($coupon) {
            if ($subtotal >= $coupon['min_purchase_amount']) {
                if ($coupon['discount_type'] === 'percentage') {
                    $discountAmount = ($subtotal * $coupon['discount_value']) / 100;
                    if ($coupon['max_discount_amount'] && $discountAmount > $coupon['max_discount_amount']) {
                        $discountAmount = $coupon['max_discount_amount'];
                    }
                } else {
                    $discountAmount = $coupon['discount_value'];
                }
            }
        }
    }

    // Calculate tax (if applicable - adjust as needed)
    $taxRate = 0; // Set to 0.18 for 18% GST
    $taxAmount = ($subtotal - $discountAmount) * $taxRate;

    // Calculate final total
    $totalAmount = $subtotal + $shippingCost + $taxAmount - $discountAmount;

    // Validate total amount
    if ($totalAmount <= 0) {
        throw new Exception('Invalid order total.');
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Generate unique order number
        $orderNumber = generateOrderNumber();

        // Create order in database
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (
                order_number,
                user_id,
                session_id,
                customer_name,
                customer_email,
                customer_phone,
                shipping_full_name,
                shipping_phone,
                shipping_address_line1,
                shipping_address_line2,
                shipping_city,
                shipping_state,
                shipping_postal_code,
                shipping_country,
                shipping_method,
                subtotal,
                tax_amount,
                shipping_amount,
                discount_amount,
                total_amount,
                coupon_code,
                payment_method,
                payment_status,
                order_status,
                notes,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'razorpay', 'pending', 'pending', ?, NOW())
        ");

        $orderStmt->execute([
            $orderNumber,
            $userId,
            $sessionId,
            $customerName,
            $email,
            $phone,
            $customerName,
            $phone,
            $street,
            '', // address line 2
            $city,
            $state,
            $postalCode,
            $country,
            $shippingMethod,
            $subtotal,
            $taxAmount,
            $shippingCost,
            $discountAmount,
            $totalAmount,
            $couponCode,
            $notes
        ]);

        $orderId = $pdo->lastInsertId();

        // Insert order items
        $orderItemStmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id,
                product_id,
                product_name,
                product_sku,
                quantity,
                price,
                subtotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($cartItems as $item) {
            $itemPrice = !empty($item['sale_price']) ? $item['sale_price'] : $item['price'];
            $itemSubtotal = $itemPrice * $item['quantity'];

            $orderItemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['sku'],
                $item['quantity'],
                $itemPrice,
                $itemSubtotal
            ]);
        }

        // Update coupon usage if applicable
        if ($discountAmount > 0 && !empty($couponCode)) {
            $updateCouponStmt = $pdo->prepare("
                UPDATE coupons
                SET used_count = used_count + 1
                WHERE code = ?
            ");
            $updateCouponStmt->execute([$couponCode]);
        }

        // Create Razorpay order
        $customerDetails = [
            'name' => $customerName,
            'email' => $email,
            'phone' => $phone
        ];

        $razorpayOrder = createRazorpayOrder($totalAmount, $orderId, $customerDetails);

        if (!$razorpayOrder || !isset($razorpayOrder['id'])) {
            throw new Exception('Failed to create payment gateway order. Please check your configuration.');
        }

        // Update order with Razorpay order ID
        $updateStmt = $pdo->prepare("
            UPDATE orders
            SET razorpay_order_id = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$razorpayOrder['id'], $orderId]);

        // Create payment record
        $paymentStmt = $pdo->prepare("
            INSERT INTO payments (
                order_id,
                razorpay_order_id,
                payment_status,
                amount,
                currency,
                created_at
            ) VALUES (?, ?, 'pending', ?, 'INR', NOW())
        ");
        $paymentStmt->execute([$orderId, $razorpayOrder['id'], $totalAmount]);

        // Add order tracking entry
        $trackingStmt = $pdo->prepare("
            INSERT INTO order_tracking (
                order_id,
                status,
                message,
                created_by
            ) VALUES (?, 'pending', 'Order created and awaiting payment', 'system')
        ");
        $trackingStmt->execute([$orderId]);

        // Commit transaction
        $pdo->commit();

        // Return success response
        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $totalAmount,
            'currency' => 'INR',
            'razorpay_key_id' => RAZORPAY_KEY_ID,
            'customer' => [
                'name' => $customerName,
                'email' => $email,
                'phone' => $phone
            ],
            'message' => 'Order created successfully.'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    // Log error for debugging
    error_log('Create order error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
