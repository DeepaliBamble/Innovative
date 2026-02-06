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

try {
    // Get and validate POST data
    $razorpayPaymentId = sanitize($_POST['razorpay_payment_id'] ?? '');
    $razorpayOrderId = sanitize($_POST['razorpay_order_id'] ?? '');
    $razorpaySignature = sanitize($_POST['razorpay_signature'] ?? '');
    $orderId = (int) ($_POST['order_id'] ?? 0);

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

    // Check if order is already paid
    if ($order['payment_status'] === 'paid') {
        throw new Exception('This order has already been paid.');
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
            // Update stock with safety check
            $stockStmt = $pdo->prepare("
                UPDATE products
                SET stock_quantity = stock_quantity - ?
                WHERE id = ? AND stock_quantity >= ?
            ");
            $stockStmt->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']
            ]);

            // Verify stock was updated
            if ($stockStmt->rowCount() === 0) {
                throw new Exception("Failed to update stock for product ID {$item['product_id']}");
            }
        }

        // Clear cart for this user/session
        if ($order['user_id']) {
            $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $clearCartStmt->execute([$order['user_id']]);
        } elseif ($order['session_id']) {
            $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
            $clearCartStmt->execute([$order['session_id']]);
        }

        // Commit all database changes
        $pdo->commit();

        // Log successful payment
        error_log("Payment successful for order {$orderId}: {$razorpayPaymentId}");

        // TODO: Send order confirmation email
        // sendOrderConfirmationEmail($order, $orderItems);

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Payment successful! Your order has been confirmed.',
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'payment_id' => $razorpayPaymentId,
            'redirect' => 'order-success.php?order_id=' . $orderId
        ]);

    } catch (Exception $e) {
        // Rollback all changes on error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    // Log error for debugging
    error_log('Payment processing error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    // Update payment record as failed if we have an order ID
    if (isset($orderId) && $orderId > 0) {
        try {
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
                WHERE id = ?
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
