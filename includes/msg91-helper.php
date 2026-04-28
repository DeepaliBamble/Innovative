<?php
/**
 * MSG91 SMS OTP Helper
 * Sends and verifies OTP via MSG91 API v5
 * Docs: https://docs.msg91.com/reference/send-otp
 */

/**
 * Normalize mobile number to 10-digit Indian format.
 * Accepts: 9876543210 / 09876543210 / +919876543210 / 919876543210
 * Returns: '919876543210' (with country code, no +)
 */
function normalizeMobile($mobile) {
    $mobile = preg_replace('/\D/', '', $mobile);
    if (strlen($mobile) === 10) {
        return '91' . $mobile;
    }
    if (strlen($mobile) === 11 && $mobile[0] === '0') {
        return '91' . substr($mobile, 1);
    }
    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        return $mobile;
    }
    return $mobile;
}

/**
 * Validate Indian mobile number (10 digits, starts with 6-9).
 */
function isValidMobile($mobile) {
    $mobile = preg_replace('/\D/', '', $mobile);
    if (strlen($mobile) === 10) {
        return preg_match('/^[6-9]\d{9}$/', $mobile) === 1;
    }
    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        return preg_match('/^[6-9]\d{9}$/', substr($mobile, 2)) === 1;
    }
    return false;
}

/**
 * Send OTP via MSG91.
 * MSG91 generates and stores the OTP internally.
 *
 * @param string $mobile  Mobile number (10-digit or with country code)
 * @return array ['success' => bool, 'message' => string]
 */
function sendSmsOTP($mobile) {
    $authKey    = MSG91_AUTH_KEY;
    $templateId = MSG91_TEMPLATE_ID;

    if (empty($authKey) || empty($templateId)) {
        error_log('MSG91: AUTH_KEY or TEMPLATE_ID not configured');
        return ['success' => false, 'message' => 'SMS service not configured. Please contact support.'];
    }

    $mobile = normalizeMobile($mobile);

    $url = 'https://control.msg91.com/api/v5/otp?template_id=' . urlencode($templateId)
         . '&mobile=' . urlencode($mobile)
         . '&authkey=' . urlencode($authKey);

    $response = _msg91Request($url, 'GET');

    if ($response === false) {
        return ['success' => false, 'message' => 'Failed to connect to SMS service. Please try again.'];
    }

    $data = json_decode($response, true);

    if (isset($data['type']) && $data['type'] === 'success') {
        return ['success' => true, 'message' => 'OTP sent successfully'];
    }

    $errorMsg = $data['message'] ?? 'Failed to send OTP';
    error_log('MSG91 Send OTP Error: ' . $response);
    return ['success' => false, 'message' => $errorMsg];
}

/**
 * Verify OTP via MSG91.
 *
 * @param string $mobile  Mobile number
 * @param string $otp     6-digit OTP entered by user
 * @return array ['success' => bool, 'message' => string]
 */
function verifySmsOTP($mobile, $otp) {
    $authKey = MSG91_AUTH_KEY;

    if (empty($authKey)) {
        return ['success' => false, 'message' => 'SMS service not configured.'];
    }

    $mobile = normalizeMobile($mobile);

    $url = 'https://control.msg91.com/api/v5/otp/verify?otp=' . urlencode($otp)
         . '&mobile=' . urlencode($mobile)
         . '&authkey=' . urlencode($authKey);

    $response = _msg91Request($url, 'GET');

    if ($response === false) {
        return ['success' => false, 'message' => 'Failed to connect to SMS service. Please try again.'];
    }

    $data = json_decode($response, true);

    if (isset($data['type']) && $data['type'] === 'success') {
        return ['success' => true, 'message' => 'OTP verified successfully'];
    }

    $errorMsg = $data['message'] ?? 'Invalid OTP. Please try again.';
    error_log('MSG91 Verify OTP: mobile=' . $mobile . ' response=' . $response);
    return ['success' => false, 'message' => $errorMsg];
}

/**
 * Resend OTP via MSG91 (retries with same mobile).
 *
 * @param string $mobile
 * @param string $retryType  'text' or 'voice'
 * @return array ['success' => bool, 'message' => string]
 */
function resendSmsOTP($mobile, $retryType = 'text') {
    $authKey = MSG91_AUTH_KEY;

    if (empty($authKey)) {
        return ['success' => false, 'message' => 'SMS service not configured.'];
    }

    $mobile = normalizeMobile($mobile);

    $url = 'https://control.msg91.com/api/v5/otp/retry?mobile=' . urlencode($mobile)
         . '&retrytype=' . urlencode($retryType)
         . '&authkey=' . urlencode($authKey);

    $response = _msg91Request($url, 'GET');

    if ($response === false) {
        return ['success' => false, 'message' => 'Failed to resend OTP. Please try again.'];
    }

    $data = json_decode($response, true);

    if (isset($data['type']) && $data['type'] === 'success') {
        return ['success' => true, 'message' => 'OTP resent successfully'];
    }

    error_log('MSG91 Resend OTP: ' . $response);
    return ['success' => false, 'message' => $data['message'] ?? 'Failed to resend OTP.'];
}

/**
 * Internal cURL helper for MSG91 API calls.
 */
function _msg91Request($url, $method = 'GET', $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    if ($method === 'POST' && $body !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log('MSG91 cURL Error: ' . $error);
        return false;
    }

    return $response;
}
