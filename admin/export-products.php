<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$format = $_GET['format'] ?? 'csv';
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? intval($_GET['category']) : null;
$status = $_GET['status'] ?? 'all'; // all, active, inactive
$featured = $_GET['featured'] ?? 'all'; // all, yes, no

// Build query based on filters
$sql = "SELECT
    p.name,
    p.sku,
    p.short_desc,
    p.description,
    p.shipping_returns,
    p.price,
    p.sale_price,
    p.cost_price,
    c.name as category,
    p.stock_quantity,
    p.is_featured,
    p.is_active,
    p.image_path as image_url
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
WHERE 1=1";

$params = [];

if ($category_id !== null) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if ($status === 'active') {
    $sql .= " AND p.is_active = 1";
} elseif ($status === 'inactive') {
    $sql .= " AND p.is_active = 0";
}

if ($featured === 'yes') {
    $sql .= " AND p.is_featured = 1";
} elseif ($featured === 'no') {
    $sql .= " AND p.is_featured = 0";
}

$sql .= " ORDER BY p.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Failed to export products: ' . $e->getMessage();
    redirect('export-products-page.php');
}

if (empty($products)) {
    $_SESSION['error_message'] = 'No products found to export with the selected filters.';
    redirect('export-products-page.php');
}

// Generate filename with timestamp
$timestamp = date('Y-m-d_H-i-s');
$filename = "products_export_{$timestamp}";

if ($format === 'csv') {
    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Header row
    fputcsv($output, array_keys($products[0]));

    // Data rows
    foreach ($products as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} elseif ($format === 'excel') {
    // Generate Excel-compatible HTML table
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
    echo 'th { background-color: #4CAF50; color: white; font-weight: bold; }';
    echo 'tr:nth-child(even) { background-color: #f2f2f2; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<table>';

    // Header row
    echo '<tr>';
    foreach (array_keys($products[0]) as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';

    // Data rows
    foreach ($products as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell ?? '') . '</td>';
        }
        echo '</tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;

} elseif ($format === 'json') {
    // Generate JSON
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Invalid format
$_SESSION['error_message'] = 'Invalid export format specified.';
redirect('export-products-page.php');
