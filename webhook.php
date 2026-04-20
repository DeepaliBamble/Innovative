<?php
/**
 * Razorpay Webhook Handler
 *
 * Register this URL in Razorpay Dashboard:
 *   Dashboard > Settings > Webhooks > Add New Webhook
 *   URL: https://innovativehomesi.com/webhook.php
 *   Events to enable: payment.captured, payment.failed, order.paid
 *
 * After adding the webhook, copy the "Webhook Secret" from Razorpay
 * and paste it as RAZORPAY_WEBHOOK_SECRET in your .env file.
 */

require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/razorpay-config.php';
require_once __DIR__ . '/includes/mail-helper.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Read raw payload
$payload = file_get_contents('php://input');
if (empty($payload)) {
    http_response_code(400);
    exit('Empty payload');
}

// Verify Razorpay webhook signature
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (!empty($signature) && RAZORPAY_WEBHOOK_SECRET !== 'MySuperSecret123!') {
    if (!verifyWebhookSignature($payload, $signature)) {
        error_log('Razorpay webhook: Invalid signature');
        http_response_code(400);
        exit('Invalid signature');
    }
} else {
    // If no webhook secret is configured yet, log a warning but continue
    error_log('Razorpay webhook: Signature verification skipped — set RAZORPAY_WEBHOOK_SECRET in .env');
}

$event = json_decode($payload, true);
if (!$event || !isset($event['event'])) {
    http_response_code(400);
    exit('Invalid JSON');
}

$eventType = $event['event'];
$razorpayOrderId = $event['payload']['payment']['entity']['order_id'] ?? null;
$razorpayPaymentId = $event['payload']['payment']['entity']['id'] ?? null;

// Log the webhook
try {
    $logStmt = $pdo->prepare("
        INSERT INTO webhook_logs (event_type, payload, razorpay_order_id, razorpay_payment_id, is_processed)
        VALUES (?, ?, ?, ?, 0)
    ");
    $logStmt->execute([$eventType, $payload, $razorpayOrderId, $razorpayPaymentId]);
    $webhookLogId = $pdo->lastInsertId();
} catch (Exception $e) {
    error_log('Webhook log insert failed: ' . $e->getMessage());
    // Don't exit — still try to process the event
    $webhookLogId = null;
}

$result = 'skipped';

try {
    switch ($eventType) {

        case 'payment.captured':
        case 'order.paid':
            $result = handlePaymentCaptured($pdo, $razorpayOrderId, $razorpayPaymentId, $event);
            break;

        case 'payment.failed':
            $result = handlePaymentFailed($pdo, $razorpayOrderId, $razorpayPaymentId, $event);
            break;

        default:
            $result = 'unhandled_event';
    }

} catch (Exception $e) {
    error_log("Webhook processing error [{$eventType}]: " . $e->getMessage());
    $result = 'error: ' . $e->getMessage();
}

// Update webhook log with result
if ($webhookLogId) {
    try {
        $pdo->prepare("UPDATE webhook_logs SET is_processed = 1, processing_result = ? WHERE id = ?")
            ->execute([$result, $webhookLogId]);
    } catch (Exception $e) {
        error_log('Webhook log update failed: ' . $e->getMessage());
    }
}

// Always return 200 to Razorpay so it stops retrying
http_response_code(200);
echo json_encode(['status' => 'ok', 'result' => $result]);
exit;


/**
 * Handle payment.captured / order.paid
 * This runs if the browser crashed and process-payment.php never ran.
 */
function handlePaymentCaptured($pdo, $razorpayOrderId, $razorpayPaymentId, $event)
{
    if (!$razorpayOrderId || !$razorpayPaymentId) {
        return 'missing_ids';
    }

    // Find the local order
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE razorpay_order_id = ?");
    $stmt->execute([$razorpayOrderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        error_log("Webhook: Order not found for razorpay_order_id={$razorpayOrderId}");
        return 'order_not_found';
    }

    // Already paid — nothing to do
    if ($order['payment_status'] === 'paid') {
        return 'already_paid';
    }

    $orderId = $order['id'];
    $razorpaySignature = $event['payload']['payment']['entity']['id'] ?? '';

    $pdo->beginTransaction();
    try {
        // Mark order as paid
        $pdo->prepare("
            UPDATE orders
            SET payment_status = 'paid',
                payment_method = 'razorpay',
                razorpay_payment_id = ?,
                order_status = 'processing',
                paid_at = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ")->execute([$razorpayPaymentId, $orderId]);

        // Update payment record
        $pdo->prepare("
            UPDATE payments
            SET razorpay_payment_id = ?,
                payment_status = 'captured',
                verified_at = NOW(),
                updated_at = NOW()
            WHERE order_id = ? AND razorpay_order_id = ?
        ")->execute([$razorpayPaymentId, $orderId, $razorpayOrderId]);

        // Add tracking entry
        $pdo->prepare("
            INSERT INTO order_tracking (order_id, status, message, created_by)
            VALUES (?, 'processing', 'Payment confirmed via Razorpay webhook', 'webhook')
        ")->execute([$orderId]);

        // Deduct stock
        $itemsStmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orderItems as $item) {
            $stockStmt = $pdo->prepare("
                UPDATE products
                SET stock_quantity = stock_quantity - ?
                WHERE id = ? AND stock_quantity >= ?
            ");
            $stockStmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
        }

        // Clear cart
        if ($order['user_id']) {
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$order['user_id']]);
        } elseif ($order['session_id']) {
            $pdo->prepare("DELETE FROM cart WHERE session_id = ?")->execute([$order['session_id']]);
        }

        $pdo->commit();

        // Send confirmation email
        try {
            sendOrderConfirmationEmail($order, $orderItems);
        } catch (Exception $e) {
            error_log("Webhook: Confirmation email failed for order {$orderId}: " . $e->getMessage());
        }

        error_log("Webhook: Payment confirmed for order {$orderId} via {$razorpayPaymentId}");
        return 'payment_confirmed';

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}


/**
 * Handle payment.failed
 * Marks the order as failed so admin can see it.
 */
function handlePaymentFailed($pdo, $razorpayOrderId, $razorpayPaymentId, $event)
{
    if (!$razorpayOrderId) {
        return 'missing_order_id';
    }

    $stmt = $pdo->prepare("SELECT id, payment_status FROM orders WHERE razorpay_order_id = ?");
    $stmt->execute([$razorpayOrderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return 'order_not_found';
    }

    // Don't overwrite a successful payment
    if ($order['payment_status'] === 'paid') {
        return 'already_paid';
    }

    $orderId = $order['id'];
    $errorDesc = $event['payload']['payment']['entity']['error_description'] ?? 'Payment failed';
    $errorCode = $event['payload']['payment']['entity']['error_code'] ?? '';

    $pdo->prepare("
        UPDATE orders SET payment_status = 'failed', order_status = 'cancelled', updated_at = NOW()
        WHERE id = ?
    ")->execute([$orderId]);

    $pdo->prepare("
        UPDATE payments
        SET payment_status = 'failed', error_code = ?, error_description = ?, updated_at = NOW()
        WHERE order_id = ?
    ")->execute([$errorCode, $errorDesc, $orderId]);

    $pdo->prepare("
        INSERT INTO order_tracking (order_id, status, message, created_by)
        VALUES (?, 'cancelled', ?, 'webhook')
    ")->execute([$orderId, 'Payment failed via Razorpay: ' . $errorDesc]);

    error_log("Webhook: Payment failed for order {$orderId}: {$errorDesc}");
    return 'payment_failed_recorded';
}
