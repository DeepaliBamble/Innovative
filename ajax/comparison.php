<?php
/**
 * Product Comparison AJAX Handler
 * Handles add, remove, and get comparison operations
 * Uses session storage (can be upgraded to database for logged-in users)
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : (isset($_GET['product_id']) ? intval($_GET['product_id']) : 0);

// Initialize comparison session if not exists
if (!isset($_SESSION['comparison'])) {
    $_SESSION['comparison'] = [];
}

try {
    switch ($action) {
        case 'add':
            // Add product to comparison
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID');
            }

            // Limit to 4 products for comparison
            if (count($_SESSION['comparison']) >= 4 && !in_array($product_id, $_SESSION['comparison'])) {
                echo json_encode([
                    'success' => false,
                    'error' => 'You can compare maximum 4 products at a time',
                    'limit_reached' => true
                ]);
                exit;
            }

            // Check if product exists
            $productCheck = $conn->prepare("SELECT id, name FROM products WHERE id = ? AND is_active = 1");
            $productCheck->bind_param("i", $product_id);
            $productCheck->execute();
            $productResult = $productCheck->get_result();

            if ($productResult->num_rows === 0) {
                throw new Exception('Product not found');
            }

            $product = $productResult->fetch_assoc();

            // Check if already in comparison
            if (in_array($product_id, $_SESSION['comparison'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Product already in comparison',
                    'already_exists' => true,
                    'comparison_count' => count($_SESSION['comparison'])
                ]);
            } else {
                // Add to comparison
                $_SESSION['comparison'][] = $product_id;

                echo json_encode([
                    'success' => true,
                    'message' => 'Product added to comparison',
                    'product_name' => $product['name'],
                    'comparison_count' => count($_SESSION['comparison'])
                ]);
            }
            break;

        case 'remove':
            // Remove product from comparison
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID');
            }

            $key = array_search($product_id, $_SESSION['comparison']);
            if ($key !== false) {
                unset($_SESSION['comparison'][$key]);
                $_SESSION['comparison'] = array_values($_SESSION['comparison']); // Re-index array
            }

            echo json_encode([
                'success' => true,
                'message' => 'Product removed from comparison',
                'comparison_count' => count($_SESSION['comparison'])
            ]);
            break;

        case 'toggle':
            // Toggle product in comparison
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID');
            }

            $key = array_search($product_id, $_SESSION['comparison']);

            if ($key !== false) {
                // Remove from comparison
                unset($_SESSION['comparison'][$key]);
                $_SESSION['comparison'] = array_values($_SESSION['comparison']);

                echo json_encode([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Removed from comparison',
                    'comparison_count' => count($_SESSION['comparison'])
                ]);
            } else {
                // Check limit
                if (count($_SESSION['comparison']) >= 4) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'You can compare maximum 4 products at a time',
                        'limit_reached' => true
                    ]);
                    exit;
                }

                // Add to comparison
                $_SESSION['comparison'][] = $product_id;

                echo json_encode([
                    'success' => true,
                    'action' => 'added',
                    'message' => 'Added to comparison',
                    'comparison_count' => count($_SESSION['comparison'])
                ]);
            }
            break;

        case 'get':
        case 'list':
            // Get all comparison items
            if (empty($_SESSION['comparison'])) {
                echo json_encode([
                    'success' => true,
                    'items' => [],
                    'count' => 0
                ]);
                exit;
            }

            $ids = implode(',', array_map('intval', $_SESSION['comparison']));
            $query = "
                SELECT
                    p.id,
                    p.name,
                    p.slug,
                    p.sku,
                    p.short_desc,
                    p.description,
                    p.price,
                    p.sale_price,
                    p.image_path,
                    p.stock_quantity,
                    c.name as category_name,
                    c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id IN ($ids) AND p.is_active = 1
                ORDER BY FIELD(p.id, $ids)
            ";

            $result = $conn->query($query);
            $items = [];

            while ($row = $result->fetch_assoc()) {
                // Get product attributes
                $attrStmt = $conn->prepare("
                    SELECT attribute_name, attribute_value
                    FROM product_attributes
                    WHERE product_id = ?
                    ORDER BY display_order ASC
                ");
                $attrStmt->bind_param("i", $row['id']);
                $attrStmt->execute();
                $attrResult = $attrStmt->get_result();

                $attributes = [];
                while ($attr = $attrResult->fetch_assoc()) {
                    $attributes[$attr['attribute_name']] = $attr['attribute_value'];
                }

                $discount_percentage = 0;
                if ($row['sale_price'] && $row['sale_price'] < $row['price']) {
                    $discount_percentage = round((($row['price'] - $row['sale_price']) / $row['price']) * 100);
                }

                $items[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'sku' => $row['sku'],
                    'short_desc' => $row['short_desc'],
                    'description' => $row['description'],
                    'price' => (float)$row['price'],
                    'sale_price' => $row['sale_price'] ? (float)$row['sale_price'] : null,
                    'final_price' => $row['sale_price'] ? (float)$row['sale_price'] : (float)$row['price'],
                    'discount_percentage' => $discount_percentage,
                    'image_path' => $row['image_path'],
                    'stock_quantity' => (int)$row['stock_quantity'],
                    'category_name' => $row['category_name'],
                    'category_slug' => $row['category_slug'],
                    'attributes' => $attributes
                ];
            }

            echo json_encode([
                'success' => true,
                'items' => $items,
                'count' => count($items)
            ]);
            break;

        case 'count':
            // Get comparison count
            echo json_encode([
                'success' => true,
                'count' => count($_SESSION['comparison'])
            ]);
            break;

        case 'clear':
            // Clear all comparison items
            $_SESSION['comparison'] = [];

            echo json_encode([
                'success' => true,
                'message' => 'Comparison cleared',
                'comparison_count' => 0
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
