<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/mail-helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate required fields
$required_fields = ['name', 'email', 'message'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
}

// Sanitize input
$name = htmlspecialchars(trim($_POST['name']));
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : null;
$subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : null;
$message = htmlspecialchars(trim($_POST['message']));

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

try {
    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages
        (name, email, phone, subject, message, is_read, created_at)
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");

    $stmt->execute([$name, $email, $phone, $subject, $message]);
    $message_id = $pdo->lastInsertId();

    // Prepare data for email functions
    $contactData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message
    ];

    // Send email notification to admin using PHPMailer
    $adminEmailResult = sendContactFormToAdmin($contactData, $message_id);
    if (!$adminEmailResult['success']) {
        error_log("Failed to send admin notification: " . $adminEmailResult['message']);
    }

    // Send confirmation email to customer using PHPMailer
    $customerEmailResult = sendContactFormConfirmation($contactData);
    if (!$customerEmailResult['success']) {
        error_log("Failed to send customer confirmation: " . $customerEmailResult['message']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for contacting us! We will get back to you shortly.'
    ]);

} catch (PDOException $e) {
    error_log("Contact Form Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // In development, show detailed error; in production, show generic message
    $errorMessage = (defined('SITE_URL') && strpos(SITE_URL, 'localhost') !== false)
        ? 'Database Error: ' . $e->getMessage()
        : 'An error occurred while sending your message. Please try again or contact us directly.';

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
