<?php
/**
 * Razorpay Payment Gateway Configuration
 *
 * Setup Instructions:
 * 1. Sign up at https://razorpay.com/
 * 2. Get your API keys from Dashboard > Settings > API Keys
 * 3. Replace RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET with your actual keys
 * 4. Set RAZORPAY_MODE to 'live' for production
 *
 * IMPORTANT SECURITY NOTES:
 * - NEVER commit real API keys to version control
 * - Use environment variables in production
 * - Keep RAZORPAY_KEY_SECRET absolutely private
 */

// =============================================
// Load credentials from environment or .env file
// =============================================
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Razorpay API Credentials
if (!defined('RAZORPAY_KEY_ID')) {
    define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_YOUR_KEY_ID');
}
if (!defined('RAZORPAY_KEY_SECRET')) {
    define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'YOUR_KEY_SECRET');
}

// Mode: 'test' or 'live'
if (!defined('RAZORPAY_MODE')) {
    define('RAZORPAY_MODE', getenv('RAZORPAY_MODE') ?: 'test');
}

// Business Information
define('RAZORPAY_BUSINESS_NAME', 'Innovative Homesi');
define('RAZORPAY_BUSINESS_LOGO', SITE_URL . '/images/logo/logo.png');
define('RAZORPAY_BUSINESS_COLOR', '#d4a574');

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
if (!defined('RAZORPAY_WEBHOOK_SECRET')) {
    define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'YOUR_WEBHOOK_SECRET');
}

// Order token secret for secure URLs
if (!defined('ORDER_TOKEN_SECRET')) {
    define('ORDER_TOKEN_SECRET', getenv('ORDER_TOKEN_SECRET') ?: 'innovative_homesi_order_secret_2026_change_me');
}

/**
 * Check if Razorpay is properly configured
 * @return bool
 */
function isRazorpayConfigured() {
    return RAZORPAY_KEY_ID !== 'rzp_test_YOUR_KEY_ID' && 
           RAZORPAY_KEY_SECRET !== 'YOUR_KEY_SECRET';
}

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
 * CRITICAL: This prevents payment tampering attacks
 */
function verifyRazorpaySignature($orderId, $razorpayPaymentId, $razorpaySignature) {
    $secret = RAZORPAY_KEY_SECRET;
    $generatedSignature = hash_hmac('sha256', $orderId . '|' . $razorpayPaymentId, $secret);
    return hash_equals($generatedSignature, $razorpaySignature);
}

/**
 * Verify Razorpay webhook signature
 */
function verifyWebhookSignature($payload, $signature) {
    $expectedSignature = hash_hmac('sha256', $payload, RAZORPAY_WEBHOOK_SECRET);
    return hash_equals($expectedSignature, $signature);
}

/**
 * Create Razorpay order via API
 * @param float $amount Amount in INR (will be converted to paise)
 * @param int $orderId Local order ID
 * @param array $customerDetails Customer information
 * @return array|false Razorpay order data or false on failure
 */
function createRazorpayOrder($amount, $orderId, $customerDetails = []) {
    $config = getRazorpayConfig();

    // Convert amount to paise (Razorpay uses smallest currency unit)
    $amountInPaise = (int) round($amount * 100);

    // Validate minimum amount (Razorpay minimum is ₹1 = 100 paise)
    if ($amountInPaise < 100) {
        error_log("Razorpay: Amount too small - {$amountInPaise} paise");
        return false;
    }

    $orderData = [
        'amount' => $amountInPaise,
        'currency' => $config['currency'],
        'receipt' => 'order_' . $orderId,
        'notes' => [
            'order_id' => (string) $orderId,
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Razorpay cURL error: {$curlError}");
        return false;
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['id'])) {
            return $result;
        }
    }

    error_log("Razorpay order creation failed. HTTP: {$httpCode}, Response: {$response}");
    return false;
}

/**
 * Fetch payment details from Razorpay API
 * @param string $paymentId Razorpay payment ID
 * @return array|false Payment details or false on failure
 */
function fetchRazorpayPayment($paymentId) {
    $config = getRazorpayConfig();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/payments/' . $paymentId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $config['key_id'] . ':' . $config['key_secret']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    error_log("Razorpay fetch payment failed. HTTP: {$httpCode}, Response: {$response}");
    return false;
}
