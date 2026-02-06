<?php
/**
 * OTP Helper Functions
 * Handles OTP generation, validation, and email sending
 */

require_once __DIR__ . '/mail-helper.php';

/**
 * Generate a 6-digit OTP code
 * @return string
 */
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Create and store OTP in database
 *
 * @param PDO $pdo Database connection
 * @param string $email User email
 * @param string $otpType Type: 'login', 'registration'
 * @param int $expiryMinutes OTP validity in minutes (default: 10)
 * @return array ['success' => bool, 'otp' => string, 'message' => string]
 */
function createOTP($pdo, $email, $otpType = 'login', $expiryMinutes = 10) {
    try {
        // Invalidate any existing unused OTPs for this email and type
        $stmt = $pdo->prepare("
            UPDATE otp_verifications
            SET is_used = 1
            WHERE email = ? AND otp_type = ? AND is_used = 0
        ");
        $stmt->execute([$email, $otpType]);

        // Generate new OTP
        $otp = generateOTP();
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryMinutes} minutes"));

        // Store OTP in database
        $stmt = $pdo->prepare("
            INSERT INTO otp_verifications (email, otp_code, otp_type, expires_at)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$email, $otp, $otpType, $expiresAt]);

        return [
            'success' => true,
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'message' => 'OTP generated successfully'
        ];

    } catch (PDOException $e) {
        error_log('Create OTP Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to generate OTP'
        ];
    }
}

/**
 * Verify OTP code
 *
 * @param PDO $pdo Database connection
 * @param string $email User email
 * @param string $otp OTP code to verify
 * @param string $otpType Type: 'login', 'registration'
 * @return array ['success' => bool, 'message' => string]
 */
function verifyOTP($pdo, $email, $otp, $otpType) {
    try {
        // Get current timestamp
        $currentTime = date('Y-m-d H:i:s');

        // Find valid OTP - check expiration in PHP instead of MySQL to debug
        $stmt = $pdo->prepare("
            SELECT id, expires_at, created_at
            FROM otp_verifications
            WHERE email = ?
            AND otp_code = ?
            AND otp_type = ?
            AND is_used = 0
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$email, $otp, $otpType]);
        $otpRecord = $stmt->fetch();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => 'Invalid OTP or no OTP found. Please request a new one.'
            ];
        }

        // Check if expired
        if (strtotime($otpRecord['expires_at']) < strtotime($currentTime)) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ];
        }

        // Mark OTP as used
        $stmt = $pdo->prepare("
            UPDATE otp_verifications
            SET is_used = 1, verified_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$currentTime, $otpRecord['id']]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully'
        ];

    } catch (PDOException $e) {
        error_log('Verify OTP Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'OTP verification failed: ' . $e->getMessage()
        ];
    }
}

/**
 * Send OTP via email for login
 *
 * @param string $email User email
 * @param string $otp OTP code
 * @param string $userName User name (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function sendLoginOTP($email, $otp, $userName = '') {
    $subject = 'Your Login OTP - Innovative Homesi';

    $greeting = $userName ? "Dear $userName," : "Hello,";

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
            .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
            .otp-box { background: #f0f0f0; border: 2px dashed #6366f1; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
            .otp-code { font-size: 32px; font-weight: bold; color: #6366f1; letter-spacing: 8px; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Login Verification</h1>
            </div>
            <div class='content'>
                <p>$greeting</p>

                <p>You requested to log in to your Innovative Homesi account. Please use the OTP code below to complete your login:</p>

                <div class='otp-box'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>Your OTP Code</p>
                    <div class='otp-code'>$otp</div>
                    <p style='margin: 10px 0 0 0; color: #666; font-size: 12px;'>Valid for 10 minutes</p>
                </div>

                <div class='warning'>
                    <strong>⚠️ Security Notice:</strong>
                    <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                        <li>Never share this OTP with anyone</li>
                        <li>Innovative Homesi will never ask for your OTP</li>
                        <li>If you didn't request this, please ignore this email</li>
                    </ul>
                </div>

                <p>If you didn't attempt to log in, please secure your account immediately by changing your password.</p>

                <p>Best regards,<br>
                The Innovative Homesi Team</p>
            </div>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; " . date('Y') . " Innovative Homesi. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($email, $subject, $message, $userName);
}

/**
 * Send OTP via email for registration
 *
 * @param string $email User email
 * @param string $otp OTP code
 * @param string $userName User name (optional)
 * @return array ['success' => bool, 'message' => string]
 */
function sendRegistrationOTP($email, $otp, $userName = '') {
    $subject = 'Verify Your Email - Innovative Homesi';

    $greeting = $userName ? "Dear $userName," : "Hello,";

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
            .header { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
            .otp-box { background: #f0f0f0; border: 2px dashed #6366f1; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
            .otp-code { font-size: 32px; font-weight: bold; color: #6366f1; letter-spacing: 8px; }
            .info { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to Innovative Homesi!</h1>
            </div>
            <div class='content'>
                <p>$greeting</p>

                <p>Thank you for registering with Innovative Homesi! To complete your registration, please verify your email address using the OTP code below:</p>

                <div class='otp-box'>
                    <p style='margin: 0; color: #666; font-size: 14px;'>Your Verification Code</p>
                    <div class='otp-code'>$otp</div>
                    <p style='margin: 10px 0 0 0; color: #666; font-size: 12px;'>Valid for 10 minutes</p>
                </div>

                <div class='info'>
                    <strong>ℹ️ What's Next?</strong>
                    <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                        <li>Enter this code on the registration page</li>
                        <li>Once verified, your account will be activated</li>
                        <li>Start shopping for quality furniture!</li>
                    </ul>
                </div>

                <p>If you didn't create an account with us, you can safely ignore this email.</p>

                <p>Best regards,<br>
                The Innovative Homesi Team</p>
            </div>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; " . date('Y') . " Innovative Homesi. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return sendEmail($email, $subject, $message, $userName);
}

/**
 * Clean up expired OTPs (can be run as a cron job)
 *
 * @param PDO $pdo Database connection
 * @return int Number of deleted records
 */
function cleanupExpiredOTPs($pdo) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM otp_verifications
            WHERE expires_at < NOW() - INTERVAL 24 HOUR
        ");
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Cleanup OTPs Error: ' . $e->getMessage());
        return 0;
    }
}
