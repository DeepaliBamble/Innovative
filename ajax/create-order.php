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

    // Business / GST details (optional)
    $businessName = sanitize($_POST['business_name'] ?? '');
    $gstNumber = strtoupper(trim(sanitize($_POST['gst_number'] ?? '')));

    // Billing address (checkbox present => same as shipping)
    $billingSame = isset($_POST['billing_same']);
    $billingFullName = sanitize($_POST['billing_full_name'] ?? '');
    $billingStreet = sanitize($_POST['billing_street'] ?? '');
    $billingCity = sanitize($_POST['billing_city'] ?? '');
    $billingState = sanitize($_POST['billing_state'] ?? '');
    $billingPostal = sanitize($_POST['billing_postal_code'] ?? '');
    $billingCountry = sanitize($_POST['billing_country'] ?? 'India');

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

    // Validate GSTIN format only when provided (optional)
    if ($gstNumber !== '' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $gstNumber)) {
        throw new Exception('Please enter a valid 15-character GSTIN.');
    }

    $customerName = $firstName . ' ' . $lastName;

    // Resolve billing address: mirror shipping when "same as shipping" is checked.
    if ($billingSame) {
        $billingFullName = $customerName;
        $billingStreet = $street;
        $billingCity = $city;
        $billingState = $state;
        $billingPostal = $postalCode;
        $billingCountry = $country;
    } else {
        if (empty($billingCountry) || empty($billingCity) || empty($billingState) || empty($billingStreet) || empty($billingPostal)) {
            throw new Exception('Complete billing address is required.');
        }
        if (empty($billingFullName)) {
            $billingFullName = $customerName;
        }
    }
    $billingSameFlag = $billingSame ? 1 : 0;

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

    // Shipping is free (standard delivery only)
    $shippingCost = 0;

    // Apply coupons (supports stacking up to MAX_STACKED_COUPONS).
    // The session is the source of truth — it holds the codes the customer applied
    // and saw on the cart/checkout. Fall back to the posted codes only if absent.
    $couponCodes = isset($_SESSION['applied_coupons']) && is_array($_SESSION['applied_coupons'])
        ? $_SESSION['applied_coupons'] : [];
    if (empty($couponCodes) && !empty($couponCode)) {
        $couponCodes = array_filter(array_map('trim', explode(',', (string)$couponCode)));
    }

    $couponResult  = evaluateCoupons($pdo, $couponCodes, $subtotal, $userId);
    $appliedCoupons = $couponResult['applied'];
    $discountAmount = $couponResult['total_discount'];
    // Store the accepted codes (comma-separated) on the order
    $couponCode = implode(', ', array_column($appliedCoupons, 'code'));

    // Calculate tax (if applicable - adjust as needed)
    $taxRate = 0; // Set to 0.18 for 18% GST
    $taxAmount = ($subtotal - $discountAmount) * $taxRate;

    // Calculate final total
    $totalAmount = $subtotal + $shippingCost + $taxAmount - $discountAmount;

    // Validate total amount
    if ($totalAmount <= 0) {
        throw new Exception('Invalid order total.');
    }

    // Payment option: 50% partial advance is only allowed on orders over this total.
    $partialMinTotal = 10000;
    $paymentType = ($_POST['payment_type'] ?? 'full') === 'partial' ? 'partial' : 'full';
    if ($paymentType === 'partial' && $totalAmount <= $partialMinTotal) {
        $paymentType = 'full'; // not eligible — charge full
    }
    // Amount to charge online now (advance for partial, full otherwise).
    $amountToCharge = $paymentType === 'partial' ? round($totalAmount * 0.5, 2) : $totalAmount;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Generate unique order number
        $orderNumber = generateOrderNumber($pdo);

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
                business_name,
                gst_number,
                billing_same_as_shipping,
                billing_full_name,
                billing_address_line1,
                billing_address_line2,
                billing_city,
                billing_state,
                billing_postal_code,
                billing_country,
                payment_type,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'razorpay', 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
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
            $notes,
            $businessName,
            $gstNumber,
            $billingSameFlag,
            $billingFullName,
            $billingStreet,
            '', // billing address line 2
            $billingCity,
            $billingState,
            $billingPostal,
            $billingCountry,
            $paymentType
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

        // Update coupon usage for each applied coupon
        if (!empty($appliedCoupons)) {
            $updateCouponStmt = $pdo->prepare("
                UPDATE coupons
                SET used_count = used_count + 1
                WHERE id = ?
            ");
            $logUsageStmt = $pdo->prepare("
                INSERT INTO coupon_usage (coupon_id, user_id, email, order_id, discount_amount, used_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            foreach ($appliedCoupons as $ac) {
                $updateCouponStmt->execute([$ac['id']]);
                $logUsageStmt->execute([
                    $ac['id'],
                    $userId,
                    $email,
                    $orderId,
                    $ac['discount'],
                ]);
            }
        }

        // Create Razorpay order
        $customerDetails = [
            'name' => $customerName,
            'email' => $email,
            'phone' => $phone
        ];

        $razorpayOrder = createRazorpayOrder($amountToCharge, $orderId, $customerDetails);

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
        $paymentStmt->execute([$orderId, $razorpayOrder['id'], $amountToCharge]);

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
            'amount' => $amountToCharge,
            'total_amount' => $totalAmount,
            'payment_type' => $paymentType,
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
