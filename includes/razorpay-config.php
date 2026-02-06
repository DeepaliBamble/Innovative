<?php
/**
 * Razorpay Payment Gateway Configuration
 *
 * Setup Instructions:
 * 1. Sign up at https://razorpay.com/
 * 2. Get your API keys from Dashboard > Settings > API Keys
 * 3. Replace RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET with your actual keys
 * 4. Set RAZORPAY_MODE to 'live' for production
 */

// Razorpay API Credentials
define('RAZORPAY_KEY_ID', 'rzp_test_YOUR_KEY_ID');        // Replace with your Test/Live Key ID
define('RAZORPAY_KEY_SECRET', 'YOUR_KEY_SECRET');          // Replace with your Test/Live Key Secret

// Mode: 'test' or 'live'
define('RAZORPAY_MODE', 'test');  // Change to 'live' for production

// Business Information
define('RAZORPAY_BUSINESS_NAME', 'Innovative Homesi');
define('RAZORPAY_BUSINESS_LOGO', 'https://yourdomain.com/images/logo.png');  // Full URL to your logo
define('RAZORPAY_BUSINESS_COLOR', '#d4a574');  // Your theme color

// Currency (INR for Indian Rupees)
define('RAZORPAY_CURRENCY', 'INR');

// Enable/Disable specific payment methods
define('RAZORPAY_PAYMENT_METHODS', [
    'card' => true,      // Credit/Debit Cards
    'netbanking' => true, // Net Banking
    'wallet' => true,     // Wallets (Paytm, PhonePe, etc.)
    'upi' => true,        // UPI (GPay, PhonePe, etc.)
]);

// Webhook Secret (for payment verification)
define('RAZORPAY_WEBHOOK_SECRET', 'YOUR_WEBHOOK_SECRET');  // Get from Dashboard > Settings > Webhooks

/**
 * Get Razorpay configuration array
 */
function getRazorpayConfig() {
    return [
        'key_id' => RAZORPAY_KEY_ID,
        'key_secret' => RAZORPAY_KEY_SECRET,
        'mode' => RAZORPAY_MODE,
        'currency' => RAZORPAY_CURRENCY,
        'business_name' => RAZORPAY_BUSINESS_NAME,
        'business_logo' => RAZORPAY_BUSINESS_LOGO,
        'theme_color' => RAZORPAY_BUSINESS_COLOR,
        'payment_methods' => RAZORPAY_PAYMENT_METHODS
    ];
}

/**
 * Verify Razorpay payment signature
 */
function verifyRazorpaySignature($orderId, $razorpayPaymentId, $razorpaySignature) {
    $secret = RAZORPAY_KEY_SECRET;

    $generatedSignature = hash_hmac('sha256', $orderId . '|' . $razorpayPaymentId, $secret);

    return hash_equals($generatedSignature, $razorpaySignature);
}

/**
 * Create Razorpay order
 */
function createRazorpayOrder($amount, $orderId, $customerDetails = []) {
    $config = getRazorpayConfig();

    // Convert amount to paise (Razorpay uses smallest currency unit)
    $amountInPaise = $amount * 100;

    $orderData = [
        'amount' => $amountInPaise,
        'currency' => $config['currency'],
        'receipt' => 'order_' . $orderId,
        'notes' => [
            'order_id' => $orderId,
            'customer_name' => $customerDetails['name'] ?? '',
            'customer_email' => $customerDetails['email'] ?? '',
            'customer_phone' => $customerDetails['phone'] ?? ''
        ]
    ];

    // Make API call to Razorpay
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    curl_setopt($ch, CURLOPT_USERPWD, $config['key_id'] . ':' . $config['key_secret']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    return false;
}
