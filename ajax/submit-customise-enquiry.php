<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/mail-helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// If the upload exceeds PHP's post_max_size, $_POST and $_FILES arrive empty.
// Detect this so the user gets a clear message instead of "fill all fields".
if (empty($_POST) && empty($_FILES)
    && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Your attachments are too large. Please keep each file under 5 MB (max 3 files) and try again.'
    ]);
    exit;
}

// Validate required fields
$required_fields = ['name', 'email', 'phone', 'furniture_type', 'requirements'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
}

// Sanitize input
$name = htmlspecialchars(trim($_POST['name']));
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$phone = htmlspecialchars(trim($_POST['phone']));
$furniture_type = htmlspecialchars(trim($_POST['furniture_type']));
$requirements = htmlspecialchars(trim($_POST['requirements']));
$timeline = isset($_POST['timeline']) ? htmlspecialchars(trim($_POST['timeline'])) : null;
$budget = isset($_POST['budget']) ? htmlspecialchars(trim($_POST['budget'])) : null;

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

// ---- Handle optional file attachments (PDF / JPG / PNG, up to 3 files, 5 MB each) ----
$MAX_FILES      = 3;
$MAX_FILE_SIZE  = 5 * 1024 * 1024; // 5 MB
$ALLOWED_MIMES  = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
];

$savedRelPaths = [];  // stored in DB (relative to site root)
$savedAbsPaths = [];  // used for email attachments

if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
    // Normalise the PHP $_FILES array into a per-file list, skipping empty slots.
    $files = [];
    foreach ($_FILES['attachments']['name'] as $i => $name) {
        if (($_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue; // no file in this slot
        }
        $files[] = [
            'name'     => $name,
            'tmp_name' => $_FILES['attachments']['tmp_name'][$i] ?? '',
            'error'    => $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $_FILES['attachments']['size'][$i] ?? 0,
        ];
    }

    if (count($files) > 0) {
        if (count($files) > $MAX_FILES) {
            echo json_encode(['success' => false, 'message' => "You can upload a maximum of {$MAX_FILES} files."]);
            exit;
        }

        // Prepare upload directory (created on demand, protected against script execution).
        $uploadDir = __DIR__ . '/../uploads/enquiries';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            error_log('Customise Enquiry: upload directory not writable: ' . $uploadDir);
            echo json_encode(['success' => false, 'message' => 'Unable to store the uploaded files. Please try again or contact us directly.']);
            exit;
        }
        $htaccess = $uploadDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phps\nRemoveType .php .phtml .php3 .php4 .php5 .php7 .phps\n");
        }

        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;

        foreach ($files as $file) {
            // Cleanup helper for the partial set if anything fails mid-loop.
            $abortUpload = function ($msg) use (&$savedAbsPaths, $finfo) {
                foreach ($savedAbsPaths as $p) {
                    @unlink($p);
                }
                if ($finfo) {
                    finfo_close($finfo);
                }
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            };

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $abortUpload('One of the files could not be uploaded. Please try again.');
            }
            if ($file['size'] <= 0 || $file['size'] > $MAX_FILE_SIZE) {
                $abortUpload('Each file must be 5 MB or smaller.');
            }
            if (!is_uploaded_file($file['tmp_name'])) {
                $abortUpload('Invalid file upload.');
            }

            $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : ($file['type'] ?? '');
            if (!isset($ALLOWED_MIMES[$mime])) {
                $abortUpload('Only PDF, JPG and PNG files are allowed.');
            }
            $ext = $ALLOWED_MIMES[$mime];

            $basename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
            $destAbs  = $uploadDir . '/' . $basename;
            $destRel  = 'uploads/enquiries/' . $basename;

            if (!move_uploaded_file($file['tmp_name'], $destAbs)) {
                $abortUpload('Failed to save an uploaded file. Please try again.');
            }
            @chmod($destAbs, 0644);

            $savedAbsPaths[] = $destAbs;
            $savedRelPaths[] = $destRel;
        }

        if ($finfo) {
            finfo_close($finfo);
        }
    }
}

$attachmentsJson = !empty($savedRelPaths) ? json_encode($savedRelPaths) : null;

try {
    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO customise_enquiries
        (name, email, phone, furniture_type, requirements, attachments, timeline, budget, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([$name, $email, $phone, $furniture_type, $requirements, $attachmentsJson, $timeline, $budget]);
    $enquiry_id = $pdo->lastInsertId();

    // Prepare data for email functions
    $enquiryData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'furniture_type' => $furniture_type,
        'requirements' => $requirements,
        'timeline' => $timeline,
        'budget' => $budget,
        'attachments' => $savedRelPaths
    ];

    // Send email notification to admin using PHPMailer (with file attachments)
    $adminEmailResult = sendCustomiseEnquiryToAdmin($enquiryData, $enquiry_id, $savedAbsPaths);
    if (!$adminEmailResult['success']) {
        error_log("Failed to send admin notification: " . $adminEmailResult['message']);
    }

    // Send confirmation email to customer using PHPMailer
    $customerEmailResult = sendCustomiseEnquiryConfirmation($enquiryData);
    if (!$customerEmailResult['success']) {
        error_log("Failed to send customer confirmation: " . $customerEmailResult['message']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your enquiry has been submitted successfully. We will contact you within 24 hours.'
    ]);

} catch (PDOException $e) {
    // Remove any files saved for this failed submission so we don't leave orphans.
    foreach ($savedAbsPaths as $p) {
        @unlink($p);
    }
    error_log("Customise Enquiry Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // In development, show detailed error; in production, show generic message
    $errorMessage = (defined('SITE_URL') && strpos(SITE_URL, 'localhost') !== false)
        ? 'Database Error: ' . $e->getMessage()
        : 'An error occurred while submitting your enquiry. Please try again or contact us directly.';

    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    $errorMessage = (defined('SITE_URL') && strpos(SITE_URL, 'localhost') !== false)
        ? 'Error: ' . $e->getMessage()
        : 'An unexpected error occurred. Please try again or contact us directly.';

    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
}
?>
