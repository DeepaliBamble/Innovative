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
 * Send order confirmation email to customer after successful payment
 *
 * @param array $order     Order row from the database
 * @param array $orderItems Array of order_items rows
 * @return array ['success' => bool, 'message' => string]
 */
function sendOrderConfirmationEmail($order, $orderItems) {
    $customerName  = htmlspecialchars($order['customer_name'] ?? $order['user_name'] ?? 'Customer');
    $customerEmail = $order['customer_email'] ?? $order['user_email'] ?? '';
    $orderNumber   = htmlspecialchars($order['order_number']);
    $orderDate     = date('d M Y, h:i A', strtotime($order['created_at']));
    $paymentId     = htmlspecialchars($order['razorpay_payment_id'] ?? '');

    if (empty($customerEmail)) {
        error_log("sendOrderConfirmationEmail: no email for order {$order['id']}");
        return ['success' => false, 'message' => 'No customer email'];
    }

    // Build items table rows
    $itemRows = '';
    foreach ($orderItems as $item) {
        $name     = htmlspecialchars($item['product_name']);
        $qty      = (int) $item['quantity'];
        $price    = number_format($item['price'], 2);
        $subtotal = number_format($item['subtotal'], 2);
        $itemRows .= "
            <tr>
                <td style='padding:8px 12px;border-bottom:1px solid #eee;'>{$name}</td>
                <td style='padding:8px 12px;border-bottom:1px solid #eee;text-align:center;'>{$qty}</td>
                <td style='padding:8px 12px;border-bottom:1px solid #eee;text-align:right;'>&#x20B9;{$price}</td>
                <td style='padding:8px 12px;border-bottom:1px solid #eee;text-align:right;'>&#x20B9;{$subtotal}</td>
            </tr>";
    }

    $subtotalFmt  = number_format($order['subtotal'], 2);
    $shippingFmt  = number_format($order['shipping_amount'] ?? 0, 2);
    $discountFmt  = number_format($order['discount_amount'] ?? 0, 2);
    $totalFmt     = number_format($order['total_amount'], 2);
    $shippingAddr = implode(', ', array_filter([
        $order['shipping_address_line1'] ?? '',
        $order['shipping_address_line2'] ?? '',
        $order['shipping_city']          ?? '',
        $order['shipping_state']         ?? '',
        $order['shipping_postal_code']   ?? '',
        $order['shipping_country']       ?? '',
    ]));

    $subject = "Order Confirmed #{$orderNumber} - Innovative Homesi";

    $message = "
    <html>
    <head>
        <style>
            body{font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:0;padding:0;}
            .wrap{max-width:620px;margin:0 auto;padding:20px;}
            .header{background:#d4a574;color:#fff;padding:24px 20px;text-align:center;}
            .header h1{margin:0;font-size:22px;}
            .section{padding:20px;background:#fafafa;margin-top:16px;border-radius:4px;}
            table{width:100%;border-collapse:collapse;}
            th{background:#f5f5f5;padding:8px 12px;text-align:left;font-size:13px;}
            .total-row td{font-weight:bold;padding:10px 12px;border-top:2px solid #d4a574;}
            .btn{display:inline-block;background:#d4a574;color:#fff;padding:12px 24px;
                 text-decoration:none;border-radius:4px;margin-top:16px;font-weight:bold;}
            .footer{text-align:center;color:#999;font-size:12px;margin-top:24px;}
        </style>
    </head>
    <body>
    <div class='wrap'>
        <div class='header'>
            <h1>Order Confirmed!</h1>
            <p style='margin:4px 0 0;font-size:14px;'>Thank you for shopping with Innovative Homesi</p>
        </div>

        <div class='section'>
            <p>Dear {$customerName},</p>
            <p>Your payment was successful and your order is now being processed.</p>
            <table style='width:100%;'>
                <tr><td style='padding:4px 0;color:#666;width:160px;'>Order Number</td><td><strong>#{$orderNumber}</strong></td></tr>
                <tr><td style='padding:4px 0;color:#666;'>Order Date</td><td>{$orderDate}</td></tr>
                " . ($paymentId ? "<tr><td style='padding:4px 0;color:#666;'>Payment ID</td><td>{$paymentId}</td></tr>" : '') . "
                <tr><td style='padding:4px 0;color:#666;'>Status</td><td><span style='color:#28a745;font-weight:bold;'>Paid &amp; Processing</span></td></tr>
            </table>
        </div>

        <div class='section'>
            <h3 style='margin-top:0;'>Items Ordered</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style='text-align:center;'>Qty</th>
                        <th style='text-align:right;'>Price</th>
                        <th style='text-align:right;'>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$itemRows}
                    <tr><td colspan='3' style='padding:8px 12px;text-align:right;color:#666;'>Subtotal</td><td style='padding:8px 12px;text-align:right;'>&#x20B9;{$subtotalFmt}</td></tr>
                    <tr><td colspan='3' style='padding:8px 12px;text-align:right;color:#666;'>Shipping</td><td style='padding:8px 12px;text-align:right;'>&#x20B9;{$shippingFmt}</td></tr>
                    " . ($order['discount_amount'] > 0 ? "<tr><td colspan='3' style='padding:8px 12px;text-align:right;color:#28a745;'>Discount</td><td style='padding:8px 12px;text-align:right;color:#28a745;'>-&#x20B9;{$discountFmt}</td></tr>" : '') . "
                    <tr class='total-row'><td colspan='3' style='text-align:right;'>Total Paid</td><td style='text-align:right;'>&#x20B9;{$totalFmt}</td></tr>
                </tbody>
            </table>
        </div>

        " . ($shippingAddr ? "
        <div class='section'>
            <h3 style='margin-top:0;'>Delivery Address</h3>
            <p style='margin:0;'>{$shippingAddr}</p>
        </div>" : '') . "

        <div style='text-align:center;margin-top:20px;'>
            <a href='" . SITE_URL . "/account-orders.php' class='btn'>View My Orders</a>
        </div>

        <div class='footer'>
            <p>Questions? Email us at " . ADMIN_EMAIL . " or call +91 9892827404</p>
            <p>&copy; " . date('Y') . " Innovative Homesi. All rights reserved.</p>
        </div>
    </div>
    </body>
    </html>
    ";

    return sendEmail($customerEmail, $subject, $message, $customerName);
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
