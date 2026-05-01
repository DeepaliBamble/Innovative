<?php
/**
 * Process Razorpay Payment
 * Handles secure payment verification and order completion
 *
 * Security Features:
 * - Signature verification (prevents payment tampering)
 * - Input validation and sanitization
 * - SQL injection prevention (prepared statements)
 * - Transaction management
 * - Stock deduction
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/razorpay-config.php';
require_once __DIR__ . '/../includes/mail-helper.php';

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

$orderId = (int) ($_POST['order_id'] ?? 0);

try {
    // Get and validate POST data
    $razorpayPaymentId = sanitize($_POST['razorpay_payment_id'] ?? '');
    $razorpayOrderId = sanitize($_POST['razorpay_order_id'] ?? '');
    $razorpaySignature = sanitize($_POST['razorpay_signature'] ?? '');

    // Validate required fields
    if (empty($razorpayPaymentId) || empty($razorpayOrderId) || empty($razorpaySignature)) {
        throw new Exception('Missing payment details. Payment may not have been completed.');
    }

    if ($orderId <= 0) {
        throw new Exception('Invalid order ID.');
    }

    // CRITICAL SECURITY: Verify payment signature
    // This prevents attackers from faking successful payments
    if (!verifyRazorpaySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
        // Log suspicious activity
        error_log("SECURITY ALERT: Payment signature verification failed for order {$orderId}");
        error_log("Payment ID: {$razorpayPaymentId}, Order ID: {$razorpayOrderId}");

        throw new Exception('Payment signature verification failed. This transaction may be fraudulent.');
    }

    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, u.email as user_email, u.name as user_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found.');
    }

    // Webhook race: if Razorpay's server-to-server webhook reached us first
    // and already marked this order paid, just return success — don't throw,
    // because the catch block would otherwise wrongly cancel a paid order.
    if ($order['payment_status'] === 'paid') {
        $orderToken = generateOrderAccessToken($orderId, $order['order_number']);
        echo json_encode([
            'success' => true,
            'message' => 'Payment confirmed.',
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'payment_id' => $order['razorpay_payment_id'] ?: $razorpayPaymentId,
            'redirect' => 'order-success.php?order_id=' . $orderId . '&token=' . urlencode($orderToken)
        ]);
        exit;
    }

    // Start transaction for atomic operations
    $pdo->beginTransaction();

    try {
        // Update order status
        $updateOrderStmt = $pdo->prepare("
            UPDATE orders
            SET payment_status = 'paid',
                payment_method = 'razorpay',
                razorpay_payment_id = ?,
                razorpay_order_id = ?,
                order_status = 'processing',
                paid_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateOrderStmt->execute([$razorpayPaymentId, $razorpayOrderId, $orderId]);

        // Update payment record
        $updatePaymentStmt = $pdo->prepare("
            UPDATE payments
            SET razorpay_payment_id = ?,
                razorpay_signature = ?,
                payment_status = 'captured',
                verified_at = NOW(),
                updated_at = NOW()
            WHERE order_id = ? AND razorpay_order_id = ?
        ");
        $updatePaymentStmt->execute([
            $razorpayPaymentId,
            $razorpaySignature,
            $orderId,
            $razorpayOrderId
        ]);

        // Add order tracking entry
        $trackingStmt = $pdo->prepare("
            INSERT INTO order_tracking (
                order_id,
                status,
                message,
                created_by
            ) VALUES (?, 'processing', 'Payment verified and order is being processed', 'system')
        ");
        $trackingStmt->execute([$orderId]);

        // Reduce stock quantity for ordered products
        $itemsStmt = $pdo->prepare("
            SELECT product_id, quantity
            FROM order_items
            WHERE order_id = ?
        ");
        $itemsStmt->execute([$orderId]);
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orderItems as $item) {
            // Update stock — if it goes negative we still allow it rather than
            // cancelling a paid order; admin can reconcile manually.
            $stockStmt = $pdo->prepare("
                UPDATE products
                SET stock_quantity = GREATEST(stock_quantity - ?, 0)
                WHERE id = ?
            ");
            $stockStmt->execute([
                $item['quantity'],
                $item['product_id']
            ]);
        }

        // Clear cart — but only for normal cart orders. Buy Now orders never
        // touched the cart, so leave the user's cart contents alone.
        $isBuyNowOrder = !empty($_SESSION['buy_now_order_ids'])
            && is_array($_SESSION['buy_now_order_ids'])
            && in_array((int) $orderId, $_SESSION['buy_now_order_ids'], true);

        if (!$isBuyNowOrder) {
            if ($order['user_id']) {
                $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $clearCartStmt->execute([$order['user_id']]);
            } elseif ($order['session_id']) {
                $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
                $clearCartStmt->execute([$order['session_id']]);
            }
        }

        // Done with this order's Buy Now marker.
        if ($isBuyNowOrder) {
            $_SESSION['buy_now_order_ids'] = array_values(array_diff(
                $_SESSION['buy_now_order_ids'],
                [(int) $orderId]
            ));
        }

        // Commit all database changes
        $pdo->commit();

    } catch (Exception $e) {
        // Rollback all changes on error during the transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    // === Post-commit: payment is durably recorded as paid. ===
    // Anything that throws below MUST NOT cancel the order.

    error_log("Payment successful for order {$orderId}: {$razorpayPaymentId}");

    // Send order confirmation email — never let this affect order status
    try {
        $order['razorpay_payment_id'] = $razorpayPaymentId; // freshen for email body
        $emailResult = sendOrderConfirmationEmail($order, $orderItems);
        if (!$emailResult['success']) {
            error_log("Order confirmation email failed for order {$orderId}: " . $emailResult['message']);
        }
    } catch (Throwable $emailErr) {
        error_log("Order confirmation email exception for order {$orderId}: " . $emailErr->getMessage());
    }

    // Generate secure access token
    $orderToken = generateOrderAccessToken($orderId, $order['order_number']);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Payment successful! Your order has been confirmed.',
        'order_id' => $orderId,
        'order_number' => $order['order_number'],
        'payment_id' => $razorpayPaymentId,
        'redirect' => 'order-success.php?order_id=' . $orderId . '&token=' . urlencode($orderToken)
    ]);

} catch (Exception $e) {
    // Log error for debugging
    error_log('Payment processing error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    // Only mark the order as failed if it has NOT already been paid.
    // (Webhook may have paid it; or our own commit may have succeeded
    // and a later post-commit step threw — in both cases we must not
    // overwrite a paid order with cancelled.)
    if ($orderId > 0) {
        try {
            $checkStmt = $pdo->prepare("SELECT payment_status FROM orders WHERE id = ?");
            $checkStmt->execute([$orderId]);
            $currentStatus = $checkStmt->fetchColumn();

            if ($currentStatus !== 'paid') {
                $failStmt = $pdo->prepare("
                    UPDATE payments
                    SET payment_status = 'failed',
                        error_description = ?,
                        updated_at = NOW()
                    WHERE order_id = ?
                ");
                $failStmt->execute([$e->getMessage(), $orderId]);

                $failOrderStmt = $pdo->prepare("
                    UPDATE orders
                    SET payment_status = 'failed',
                        order_status = 'cancelled',
                        updated_at = NOW()
                    WHERE id = ? AND payment_status <> 'paid'
                ");
                $failOrderStmt->execute([$orderId]);

                $trackingStmt = $pdo->prepare("
                    INSERT INTO order_tracking (
                        order_id,
                        status,
                        message,
                        created_by
                    ) VALUES (?, 'cancelled', ?, 'system')
                ");
                $trackingStmt->execute([$orderId, 'Payment failed: ' . $e->getMessage()]);
            }
        } catch (Exception $logError) {
            error_log('Failed to update payment status: ' . $logError->getMessage());
        }
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
