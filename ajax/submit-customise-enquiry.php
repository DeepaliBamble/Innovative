<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/mail-helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
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

try {
    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO customise_enquiries
        (name, email, phone, furniture_type, requirements, timeline, budget, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([$name, $email, $phone, $furniture_type, $requirements, $timeline, $budget]);
    $enquiry_id = $pdo->lastInsertId();

    // Prepare data for email functions
    $enquiryData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'furniture_type' => $furniture_type,
        'requirements' => $requirements,
        'timeline' => $timeline,
        'budget' => $budget
    ];

    // Send email notification to admin using PHPMailer
    $adminEmailResult = sendCustomiseEnquiryToAdmin($enquiryData, $enquiry_id);
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
