<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    redirect('login.php');
}

$errors = [];
$success_count = 0;
$skipped_count = 0;
$updated_count = 0;
$error_rows = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('import-products.php');
}

// Check if file was uploaded
if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error_message'] = 'No file was uploaded or upload failed';
    redirect('import-products.php');
}

$file = $_FILES['import_file'];
$fileName = $file['name'];
$fileTmpPath = $file['tmp_name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file type
$allowedExtensions = ['csv', 'xls', 'xlsx'];
if (!in_array($fileExtension, $allowedExtensions)) {
    $_SESSION['error_message'] = 'Invalid file type. Please upload CSV or Excel file.';
    redirect('import-products.php');
}

// Get import options
$skipDuplicates = isset($_POST['skip_duplicates']) ? true : false;
$updateExisting = isset($_POST['update_existing']) ? true : false;

// Get all categories for mapping
$categoriesStmt = $pdo->query("SELECT id, name FROM categories");
$categories = [];
while ($row = $categoriesStmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[strtolower(trim($row['name']))] = $row['id'];
}

// Parse file based on extension
$data = [];

if ($fileExtension === 'csv') {
    // Parse CSV file
    $handle = fopen($fileTmpPath, 'r');
    if ($handle === false) {
        $_SESSION['error_message'] = 'Failed to read CSV file';
        redirect('import-products.php');
    }

    // Read header row
    $headers = fgetcsv($handle);
    if ($headers === false) {
        fclose($handle);
        $_SESSION['error_message'] = 'CSV file is empty or invalid';
        redirect('import-products.php');
    }

    // Clean headers (remove BOM, trim whitespace)
    $headers = array_map(function($header) {
        $header = trim($header);
        // Remove BOM if present
        $header = str_replace("\xEF\xBB\xBF", '', $header);
        return strtolower($header);
    }, $headers);

    // Read data rows
    $rowNumber = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $rowNumber++;
        if (count($row) === count($headers)) {
            $data[] = array_combine($headers, $row);
        } else {
            $error_rows[] = "Row {$rowNumber}: Column count mismatch";
        }
    }

    fclose($handle);

} elseif (in_array($fileExtension, ['xls', 'xlsx'])) {
    // Parse Excel file using simple XML parsing for .xlsx
    // For .xls, we'll try to read it as CSV if possible, otherwise require a library

    if ($fileExtension === 'xlsx') {
        // Try to parse XLSX using built-in PHP functions
        try {
            $zip = new ZipArchive();
            if ($zip->open($fileTmpPath) !== true) {
                $_SESSION['error_message'] = 'Failed to read Excel file. Please use CSV format or install PhpSpreadsheet library.';
                redirect('import-products.php');
            }

            // Read shared strings
            $sharedStrings = [];
            $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedStringsXml) {
                $xml = simplexml_load_string($sharedStringsXml);
                foreach ($xml->si as $si) {
                    $sharedStrings[] = (string)$si->t;
                }
            }

            // Read worksheet data
            $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if (!$worksheetXml) {
                $zip->close();
                $_SESSION['error_message'] = 'Failed to read worksheet from Excel file';
                redirect('import-products.php');
            }

            $xml = simplexml_load_string($worksheetXml);
            $rows = [];

            // Helper function to convert Excel column letters to index (A=0, B=1, etc.)
            $columnToIndex = function($col) {
                $col = strtoupper($col);
                $result = 0;
                $length = strlen($col);
                for ($i = 0; $i < $length; $i++) {
                    $result = $result * 26 + (ord($col[$i]) - ord('A') + 1);
                }
                return $result - 1;
            };

            // Determine the maximum column count from first row
            $maxColumns = 0;

            foreach ($xml->sheetData->row as $row) {
                // Initialize cells array with empty values
                $cells = array_fill(0, 50, ''); // Pre-fill with empty strings (up to 50 columns)
                $maxColIndex = 0;

                foreach ($row->c as $cell) {
                    $cellRef = (string)$cell['r']; // Cell reference like "A1", "B2"
                    // Extract column letters from cell reference
                    preg_match('/^([A-Z]+)/', $cellRef, $matches);
                    $colIndex = isset($matches[1]) ? $columnToIndex($matches[1]) : 0;

                    $cellValue = '';
                    if (isset($cell->v)) {
                        $value = (string)$cell->v;
                        $type = (string)$cell['t'];

                        if ($type === 's') {
                            // Shared string
                            $cellValue = isset($sharedStrings[(int)$value]) ? $sharedStrings[(int)$value] : '';
                        } else {
                            $cellValue = $value;
                        }
                    }
                    $cells[$colIndex] = $cellValue;
                    $maxColIndex = max($maxColIndex, $colIndex);
                }

                // Track max columns from first row (header)
                if (empty($rows)) {
                    $maxColumns = $maxColIndex + 1;
                }

                // Trim array to actual column count
                $cells = array_slice($cells, 0, $maxColumns);
                $rows[] = $cells;
            }

            $zip->close();

            if (empty($rows)) {
                $_SESSION['error_message'] = 'Excel file is empty';
                redirect('import-products.php');
            }

            // First row is headers
            $headers = array_map('strtolower', array_map('trim', $rows[0]));

            // Rest are data
            for ($i = 1; $i < count($rows); $i++) {
                if (count($rows[$i]) === count($headers)) {
                    $data[] = array_combine($headers, $rows[$i]);
                } else {
                    $error_rows[] = "Row " . ($i + 1) . ": Column count mismatch";
                }
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error parsing Excel file: ' . $e->getMessage() . '. Please use CSV format or install PhpSpreadsheet library.';
            redirect('import-products.php');
        }
    } else {
        // .xls format - suggest using CSV instead
        $_SESSION['error_message'] = 'Legacy Excel format (.xls) is not supported. Please convert to .xlsx or use CSV format.';
        redirect('import-products.php');
    }
}

// Check if there's any data to import
if (empty($data)) {
    $_SESSION['error_message'] = 'No data rows found in the file';
    redirect('import-products.php');
}

// Validate required columns by checking the first row's keys
$requiredColumns = ['name', 'price', 'category'];
$availableColumns = array_keys($data[0]);
foreach ($requiredColumns as $col) {
    if (!in_array($col, $availableColumns)) {
        $_SESSION['error_message'] = "Missing required column: {$col}";
        redirect('import-products.php');
    }
}

// Process each row
$pdo->beginTransaction();

try {
    foreach ($data as $index => $row) {
        $rowNum = $index + 2; // +2 because we start from 1 and skip header

        // Validate required fields
        if (empty(trim($row['name'])) || empty(trim($row['price'])) || empty(trim($row['category']))) {
            $error_rows[] = "Row {$rowNum}: Missing required fields (name, price, or category)";
            continue;
        }

        // Get or validate category
        $categoryName = strtolower(trim($row['category']));
        if (!isset($categories[$categoryName])) {
            $error_rows[] = "Row {$rowNum}: Category '{$row['category']}' not found";
            continue;
        }
        $category_id = $categories[$categoryName];

        // Prepare product data
        $name = trim($row['name']);
        $price = floatval(str_replace(',', '', $row['price']));

        if ($price <= 0) {
            $error_rows[] = "Row {$rowNum}: Invalid price";
            continue;
        }

        // Generate slug
        $slug = isset($row['slug']) && !empty(trim($row['slug']))
            ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($row['slug'])), '-'))
            : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        // Generate or use SKU
        $sku = isset($row['sku']) && !empty(trim($row['sku']))
            ? trim($row['sku'])
            : 'IH-' . strtoupper(substr(md5($name . time() . $index), 0, 8));

        // Check if product with this SKU exists
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
        $checkStmt->execute([$sku]);
        $existingProduct = $checkStmt->fetch();

        if ($existingProduct) {
            if ($updateExisting) {
                // Update existing product - keep existing slug unless name changed
                $existingSlugStmt = $pdo->prepare("SELECT slug, name FROM products WHERE id = ?");
                $existingSlugStmt->execute([$existingProduct['id']]);
                $existingData = $existingSlugStmt->fetch();

                // Only generate new slug if name has changed
                $updateSlug = $existingData['slug'];
                if ($existingData['name'] !== $name) {
                    // Check if new slug would conflict with another product
                    $newSlugCheckStmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
                    $newSlugCheckStmt->execute([$slug, $existingProduct['id']]);
                    if ($newSlugCheckStmt->fetch()) {
                        $updateSlug = $slug . '-' . $existingProduct['id'];
                    } else {
                        $updateSlug = $slug;
                    }
                }

                $updateSql = "UPDATE products SET
                    name = ?, slug = ?, short_desc = ?, description = ?, shipping_returns = ?,
                    price = ?, sale_price = ?, cost_price = ?, category_id = ?,
                    stock_quantity = ?, is_featured = ?, is_active = ?,
                    updated_at = NOW()
                    WHERE sku = ?";

                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([
                    $name,
                    $updateSlug,
                    isset($row['short_desc']) ? trim($row['short_desc']) : '',
                    isset($row['description']) ? trim($row['description']) : '',
                    isset($row['shipping_returns']) ? trim($row['shipping_returns']) : '',
                    $price,
                    isset($row['sale_price']) && !empty(trim($row['sale_price'])) ? floatval(str_replace(',', '', $row['sale_price'])) : null,
                    isset($row['cost_price']) && !empty(trim($row['cost_price'])) ? floatval(str_replace(',', '', $row['cost_price'])) : null,
                    $category_id,
                    isset($row['stock_quantity']) ? intval($row['stock_quantity']) : 0,
                    isset($row['is_featured']) ? intval($row['is_featured']) : 0,
                    isset($row['is_active']) ? intval($row['is_active']) : 1,
                    $sku
                ]);

                $updated_count++;
            } else {
                // Skip duplicate
                $skipped_count++;
            }
            continue;
        }

        // Check if slug exists
        $slugCheckStmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
        $slugCheckStmt->execute([$slug]);
        if ($slugCheckStmt->fetch()) {
            // Add timestamp to make it unique
            $slug = $slug . '-' . time();
        }

        // Insert new product
        $insertSql = "INSERT INTO products
            (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, cost_price,
             category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            $name,
            $slug,
            $sku,
            isset($row['short_desc']) ? trim($row['short_desc']) : '',
            isset($row['description']) ? trim($row['description']) : '',
            isset($row['shipping_returns']) ? trim($row['shipping_returns']) : '',
            $price,
            isset($row['sale_price']) && !empty(trim($row['sale_price'])) ? floatval(str_replace(',', '', $row['sale_price'])) : null,
            isset($row['cost_price']) && !empty(trim($row['cost_price'])) ? floatval(str_replace(',', '', $row['cost_price'])) : null,
            $category_id,
            isset($row['image_url']) ? trim($row['image_url']) : null,
            isset($row['stock_quantity']) ? intval($row['stock_quantity']) : 0,
            isset($row['is_featured']) ? intval($row['is_featured']) : 0,
            isset($row['is_active']) ? intval($row['is_active']) : 1
        ]);

        $success_count++;
    }

    $pdo->commit();

    // Prepare success message
    $message = "Import completed successfully! ";
    $message .= "Added: {$success_count} products. ";
    if ($updated_count > 0) {
        $message .= "Updated: {$updated_count} products. ";
    }
    if ($skipped_count > 0) {
        $message .= "Skipped: {$skipped_count} duplicates. ";
    }
    if (!empty($error_rows)) {
        $message .= "Errors: " . count($error_rows) . " rows.";
    }

    $_SESSION['success_message'] = $message;

    if (!empty($error_rows)) {
        $_SESSION['import_errors'] = $error_rows;
    }

    redirect('import-products.php');

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = 'Import failed: ' . $e->getMessage();
    redirect('import-products.php');
}
