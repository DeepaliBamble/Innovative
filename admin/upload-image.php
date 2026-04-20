<?php
require __DIR__ . '/../includes/init.php';
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded', 'debug' => [
        'files_empty' => empty($_FILES),
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize')
    ]]);
    exit;
}

$file = $_FILES['image'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];

    $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
    echo json_encode(['success' => false, 'error' => $errorMsg, 'error_code' => $file['error']]);
    exit;
}

$uploadDir = __DIR__ . '/../images/gallery/';
$uploadUrl = 'images/gallery/';

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
}

// Check if directory is writable
if (!is_writable($uploadDir)) {
    echo json_encode(['success' => false, 'error' => 'Upload directory is not writable', 'dir' => $uploadDir]);
    exit;
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$fileType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type: ' . $fileType . '. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit;
}

// Validate file size (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File size exceeds 5MB limit']);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (empty($extension)) {
    // If no extension, get from mime type
    $mimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $extension = $mimeToExt[$fileType] ?? 'jpg';
}

$filename = uniqid('gallery_', true) . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Verify file was created
    if (file_exists($filepath)) {
        // Return success with file path
        echo json_encode([
            'success' => true,
            'path' => $uploadUrl . $filename,
            'filename' => $filename,
            'size' => filesize($filepath)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'File upload succeeded but file not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file', 'tmp_name' => $file['tmp_name'], 'destination' => $filepath]);
}
