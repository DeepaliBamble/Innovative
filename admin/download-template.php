<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$format = $_GET['format'] ?? 'csv';

// Sample data for template
$sampleData = [
    [
        'name' => 'Modern L-Shape Sofa',
        'sku' => 'IH-SOFA-001',
        'short_desc' => 'Comfortable L-shaped sofa perfect for modern living rooms',
        'description' => 'This elegant L-shaped sofa features premium upholstery and sturdy wooden frame. Perfect for family gatherings.',
        'shipping_returns' => 'Free shipping within city limits. 7-day return policy.',
        'price' => '45000',
        'sale_price' => '39999',
        'cost_price' => '30000',
        'category' => 'Sofas',
        'stock_quantity' => '10',
        'is_featured' => '1',
        'is_active' => '1',
        'image_url' => 'images/products/sofa-1.jpg'
    ],
    [
        'name' => 'King Size Bed with Storage',
        'sku' => 'IH-BED-001',
        'short_desc' => 'Spacious king size bed with under-bed storage',
        'description' => 'Premium quality king size bed made from solid wood with ample storage space underneath.',
        'shipping_returns' => 'Free delivery. Assembly included. 30-day return policy.',
        'price' => '55000',
        'sale_price' => '',
        'cost_price' => '38000',
        'category' => 'Beds',
        'stock_quantity' => '5',
        'is_featured' => '0',
        'is_active' => '1',
        'image_url' => 'images/products/bed-1.jpg'
    ],
    [
        'name' => '6-Seater Dining Table Set',
        'sku' => 'IH-DINING-001',
        'short_desc' => 'Elegant dining table set with 6 chairs',
        'description' => 'Beautiful dining table set crafted from premium teak wood. Includes cushioned chairs for maximum comfort.',
        'shipping_returns' => 'Free shipping. 14-day return policy applies.',
        'price' => '65000',
        'sale_price' => '59999',
        'cost_price' => '45000',
        'category' => 'Dining Tables',
        'stock_quantity' => '3',
        'is_featured' => '1',
        'is_active' => '1',
        'image_url' => 'images/products/dining-1.jpg'
    ]
];

if ($format === 'csv') {
    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="product_import_template.csv"');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header row
    fputcsv($output, array_keys($sampleData[0]));

    // Data rows
    foreach ($sampleData as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} elseif ($format === 'excel') {
    // For Excel, we'll use a simple approach with HTML table that Excel can open
    // This avoids requiring external libraries

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="product_import_template.xls"');

    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; }';
    echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; }';
    echo 'th { background-color: #4CAF50; color: white; font-weight: bold; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    echo '<table>';

    // Header row
    echo '<tr>';
    foreach (array_keys($sampleData[0]) as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';

    // Data rows
    foreach ($sampleData as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell) . '</td>';
        }
        echo '</tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';
    exit;
}

// Invalid format
redirect('import-products.php');
