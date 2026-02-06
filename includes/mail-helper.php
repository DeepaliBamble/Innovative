<?php
/**
 * Email Helper Functions using PHPMailer
 */

require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/mail-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using PHPMailer with Hostinger SMTP
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $toName Recipient name (optional)
 * @param string $replyTo Reply-to email (optional)
 * @param string $replyToName Reply-to name (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function sendEmail($to, $subject, $body, $toName = '', $replyTo = null, $replyToName = null) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = SMTP_DEBUG;

        // Character set
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to, $toName);

        // Reply-To address
        if ($replyTo) {
            $mail->addReplyTo($replyTo, $replyToName);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Plain text version

        // Send email
        $mail->send();

        return [
            'success' => true,
            'message' => 'Email sent successfully'
        ];

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return [
            'success' => false,
            'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Send customisation enquiry notification to admin
 *
 * @param array $data Enquiry data
 * @param int $enquiry_id Enquiry ID
 * @return array ['success' => bool, 'message' => string]
 */
function sendCustomiseEnquiryToAdmin($data, $enquiry_id) {
    $subject = 'New Customisation Enquiry - Innovative Homesi';

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .info-row { margin-bottom: 15px; }
            .label { font-weight: bold; color: #6366f1; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Customisation Enquiry</h1>
            </div>
            <div class='content'>
                <h2>Customer Details:</h2>
                <div class='info-row'><span class='label'>Name:</span> {$data['name']}</div>
                <div class='info-row'><span class='label'>Email:</span> {$data['email']}</div>
                <div class='info-row'><span class='label'>Phone:</span> {$data['phone']}</div>

                <h2>Enquiry Details:</h2>
                <div class='info-row'><span class='label'>Furniture Type:</span> {$data['furniture_type']}</div>
                <div class='info-row'><span class='label'>Timeline:</span> " . ($data['timeline'] ?: 'Not specified') . "</div>
                <div class='info-row'><span class='label'>Budget:</span> " . ($data['budget'] ?: 'Not specified') . "</div>

                <h2>Requirements:</h2>
                <div style='background: white; padding: 15px; border-left: 4px solid #6366f1;'>
                    " . nl2br($data['requirements']) . "
                </div>

                <p style='margin-top: 20px;'><a href='" . SITE_URL . "/admin/customise-enquiries.php?id={$enquiry_id}' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View in Admin Panel</a></p>
            </div>
            <div class='footer'>
                <p>This is an automated notification from Innovative Homesi</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail(ADMIN_EMAIL, $subject, $message, ADMIN_NAME, $data['email'], $data['name']);
}

/**
 * Send confirmation email to customer
 *
 * @param array $data Customer data
 * @return array ['success' => bool, 'message' => string]
 */
function sendCustomiseEnquiryConfirmation($data) {
    $subject = 'Thank you for your customisation enquiry - Innovative Homesi';

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Thank You for Your Enquiry!</h1>
            </div>
            <div class='content'>
                <p>Dear {$data['name']},</p>

                <p>Thank you for your interest in our custom furniture services. We have received your customisation enquiry and our expert team will review your requirements.</p>

                <p><strong>What happens next?</strong></p>
                <ul>
                    <li>Our design team will review your requirements</li>
                    <li>We'll contact you within 24 hours</li>
                    <li>We'll discuss customisation options and provide a quote</li>
                </ul>

                <p><strong>Your Enquiry Summary:</strong></p>
                <p><strong>Furniture Type:</strong> {$data['furniture_type']}<br>
                <strong>Timeline:</strong> " . ($data['timeline'] ?: 'Not specified') . "<br>
                <strong>Budget:</strong> " . ($data['budget'] ?: 'Not specified') . "</p>

                <p>If you have any immediate questions, please don't hesitate to contact us at " . ADMIN_EMAIL . "</p>

                <p>Best regards,<br>
                The Innovative Homesi Team</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($data['email'], $subject, $message, $data['name']);
}

/**
 * Send contact form notification to admin
 *
 * @param array $data Contact form data
 * @param int $message_id Message ID
 * @return array ['success' => bool, 'message' => string]
 */
function sendContactFormToAdmin($data, $message_id) {
    $subject = 'New Contact Form Submission - Innovative Homesi';

    if ($data['subject']) {
        $subject = 'New Contact: ' . $data['subject'] . ' - Innovative Homesi';
    }

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .info-row { margin-bottom: 15px; }
            .label { font-weight: bold; color: #6366f1; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Contact Form Submission</h1>
            </div>
            <div class='content'>
                <h2>Contact Details:</h2>
                <div class='info-row'><span class='label'>Name:</span> {$data['name']}</div>
                <div class='info-row'><span class='label'>Email:</span> {$data['email']}</div>
                " . ($data['phone'] ? "<div class='info-row'><span class='label'>Phone:</span> {$data['phone']}</div>" : "") . "
                " . ($data['subject'] ? "<div class='info-row'><span class='label'>Subject:</span> {$data['subject']}</div>" : "") . "

                <h2>Message:</h2>
                <div style='background: white; padding: 15px; border-left: 4px solid #6366f1;'>
                    " . nl2br($data['message']) . "
                </div>

                <p style='margin-top: 20px;'><a href='" . SITE_URL . "/admin/contact-messages.php?id={$message_id}' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>View in Admin Panel</a></p>
            </div>
            <div class='footer'>
                <p>This is an automated notification from Innovative Homesi</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail(ADMIN_EMAIL, $subject, $message, ADMIN_NAME, $data['email'], $data['name']);
}

/**
 * Send contact form confirmation to customer
 *
 * @param array $data Customer data
 * @return array ['success' => bool, 'message' => string]
 */
function sendContactFormConfirmation($data) {
    $subject = 'Thank you for contacting Innovative Homesi';

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Thank You for Contacting Us!</h1>
            </div>
            <div class='content'>
                <p>Dear {$data['name']},</p>

                <p>Thank you for reaching out to Innovative Homesi. We have received your message and our team will get back to you as soon as possible.</p>

                <p><strong>Your Message:</strong></p>
                <div style='background: white; padding: 15px; border-left: 4px solid #6366f1;'>
                    " . nl2br($data['message']) . "
                </div>

                <p><strong>What happens next?</strong></p>
                <ul>
                    <li>Our team will review your message</li>
                    <li>We'll respond within 24 hours</li>
                    <li>If urgent, call us at +91 9892827404</li>
                </ul>

                <p>Best regards,<br>
                The Innovative Homesi Team</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($data['email'], $subject, $message, $data['name']);
}
