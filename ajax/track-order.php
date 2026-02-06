<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
if (empty($_POST['order_id']) || empty($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Please provide both Order ID and Email address']);
    exit;
}

// Sanitize input
$order_id = htmlspecialchars(trim($_POST['order_id']));
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

try {
    // Query to get order details with user information
    $stmt = $pdo->prepare("
        SELECT
            o.id,
            o.order_number,
            o.subtotal,
            o.tax_amount,
            o.shipping_amount,
            o.discount_amount,
            o.total_amount,
            o.order_status,
            o.payment_status,
            o.payment_method,
            o.created_at,
            o.updated_at,
            u.email,
            u.name,
            sa.full_name as shipping_name,
            sa.address_line1 as shipping_address1,
            sa.address_line2 as shipping_address2,
            sa.city as shipping_city,
            sa.state as shipping_state,
            sa.postal_code as shipping_postal,
            sa.country as shipping_country,
            sa.phone as shipping_phone
        FROM orders o
        INNER JOIN users u ON o.user_id = u.id
        LEFT JOIN addresses sa ON o.shipping_address_id = sa.id
        WHERE (o.order_number = ? OR o.id = ?)
        AND u.email = ?
    ");

    $stmt->execute([$order_id, $order_id, $email]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found. Please check your Order ID and Email address.'
        ]);
        exit;
    }

    // Get order items
    $items_stmt = $pdo->prepare("
        SELECT
            oi.product_name,
            oi.product_sku,
            oi.quantity,
            oi.price,
            oi.subtotal,
            p.image1
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");

    $items_stmt->execute([$order['id']]);
    $order_items = $items_stmt->fetchAll();

    // Format the order status for display
    $status_info = [
        'pending' => [
            'label' => 'Order Placed',
            'description' => 'Your order has been received and is being processed',
            'icon' => 'fa-clock',
            'color' => '#ffc107'
        ],
        'processing' => [
            'label' => 'Processing',
            'description' => 'Your order is being prepared for shipment',
            'icon' => 'fa-box',
            'color' => '#17a2b8'
        ],
        'shipped' => [
            'label' => 'Shipped',
            'description' => 'Your order is on its way',
            'icon' => 'fa-truck',
            'color' => '#007bff'
        ],
        'delivered' => [
            'label' => 'Delivered',
            'description' => 'Your order has been delivered',
            'icon' => 'fa-check-circle',
            'color' => '#28a745'
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'description' => 'This order has been cancelled',
            'icon' => 'fa-times-circle',
            'color' => '#dc3545'
        ]
    ];

    $current_status = $status_info[$order['order_status']] ?? $status_info['pending'];

    echo json_encode([
        'success' => true,
        'order' => [
            'order_number' => $order['order_number'],
            'order_date' => date('F j, Y', strtotime($order['created_at'])),
            'order_time' => date('g:i A', strtotime($order['created_at'])),
            'last_updated' => date('F j, Y g:i A', strtotime($order['updated_at'])),
            'status' => $order['order_status'],
            'status_label' => $current_status['label'],
            'status_description' => $current_status['description'],
            'status_icon' => $current_status['icon'],
            'status_color' => $current_status['color'],
            'payment_status' => ucfirst($order['payment_status']),
            'payment_method' => $order['payment_method'] ? ucfirst($order['payment_method']) : 'N/A',
            'customer_name' => $order['name'],
            'customer_email' => $order['email'],
            'shipping_address' => [
                'name' => $order['shipping_name'],
                'address1' => $order['shipping_address1'],
                'address2' => $order['shipping_address2'],
                'city' => $order['shipping_city'],
                'state' => $order['shipping_state'],
                'postal_code' => $order['shipping_postal'],
                'country' => $order['shipping_country'],
                'phone' => $order['shipping_phone']
            ],
            'subtotal' => number_format($order['subtotal'], 2),
            'tax' => number_format($order['tax_amount'], 2),
            'shipping' => number_format($order['shipping_amount'], 2),
            'discount' => number_format($order['discount_amount'], 2),
            'total' => number_format($order['total_amount'], 2),
            'items' => $order_items
        ]
    ]);

} catch (PDOException $e) {
    error_log("Track Order Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while tracking your order. Please try again later.'
    ]);
}
?>
