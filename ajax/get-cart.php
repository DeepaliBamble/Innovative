<?php
/**
 * Get Cart Items
 * Returns all items in the cart with product details
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

try {
    // Get cart items based on user or session
    if (isLoggedIn()) {
        $userId = getCurrentUserId();
        $stmt = $pdo->prepare("
            SELECT
                c.*,
                p.name,
                p.slug,
                p.price,
                p.sale_price,
                p.image_path,
                p.stock_quantity,
                p.is_active,
                cat.name as category_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            LEFT JOIN categories cat ON p.category_id = cat.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$userId]);
    } else {
        $sessionId = session_id();
        $stmt = $pdo->prepare("
            SELECT
                c.*,
                p.name,
                p.slug,
                p.price,
                p.sale_price,
                p.image_path,
                p.stock_quantity,
                p.is_active,
                cat.name as category_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            LEFT JOIN categories cat ON p.category_id = cat.id
            WHERE c.session_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$sessionId]);
    }

    $cartItems = $stmt->fetchAll();

    // Calculate totals
    $subtotal = 0;
    $items = [];

    foreach ($cartItems as $item) {
        // Skip inactive products
        if (!$item['is_active']) {
            continue;
        }

        $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
        $itemTotal = $price * $item['quantity'];
        $subtotal += $itemTotal;

        // Check if quantity exceeds stock
        $availableQty = min($item['quantity'], $item['stock_quantity']);
        $isOutOfStock = $item['stock_quantity'] <= 0;

        $items[] = [
            'id' => (int)$item['id'],
            'product_id' => (int)$item['product_id'],
            'name' => $item['name'],
            'slug' => $item['slug'],
            'category' => $item['category_name'],
            'price' => (float)$item['price'],
            'sale_price' => $item['sale_price'] ? (float)$item['sale_price'] : null,
            'final_price' => (float)$price,
            'quantity' => (int)$item['quantity'],
            'available_quantity' => (int)$availableQty,
            'stock_quantity' => (int)$item['stock_quantity'],
            'is_out_of_stock' => $isOutOfStock,
            'quantity_exceeded' => $item['quantity'] > $item['stock_quantity'],
            'image_path' => $item['image_path'],
            'item_total' => (float)$itemTotal
        ];
    }

    // Calculate shipping and tax
    $shipping = $subtotal >= 150 ? 0 : 50; // Free shipping over ₹150
    $tax = 0; // Add tax calculation if needed
    $total = $subtotal + $shipping + $tax;

    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items),
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'free_shipping_threshold' => 150,
        'free_shipping_remaining' => max(0, 150 - $subtotal)
    ]);

} catch (Exception $e) {
    error_log('Get cart error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch cart items.',
        'items' => [],
        'count' => 0
    ]);
}
