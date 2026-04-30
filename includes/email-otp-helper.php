<?php
/**
 * Email OTP helper
 * Generates, stores (hashed), sends, and verifies a 6-digit OTP for login-by-email.
 */

require_once __DIR__ . '/mail-helper.php';

const EMAIL_OTP_TTL_SECONDS = 600;   // 10 minutes
const EMAIL_OTP_MAX_ATTEMPTS = 5;

/**
 * Generate, store, and email a 6-digit OTP to the given address.
 * Wipes any existing OTPs for that email first (only the latest is valid).
 *
 * $purpose: 'login' (default) or 'registration' — only changes subject + intro copy.
 */
function sendEmailLoginOtp(PDO $pdo, string $email, string $userName = '', string $purpose = 'login'): array {
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }

    $otp     = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expires = date('Y-m-d H:i:s', time() + EMAIL_OTP_TTL_SECONDS);

    try {
        $pdo->prepare('DELETE FROM email_otps WHERE email = ?')->execute([$email]);
        $pdo->prepare('INSERT INTO email_otps (email, otp_hash, expires_at) VALUES (?, ?, ?)')
            ->execute([$email, $otpHash, $expires]);
    } catch (PDOException $e) {
        error_log('Email OTP store error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Could not generate OTP. Please try again.'];
    }

    if ($purpose === 'registration') {
        $subject  = 'Verify your email — Innovative Homesi';
        $intro    = 'Use the code below to verify your email and finish creating your Innovative Homesi account. It expires in 10 minutes.';
    } else {
        $subject  = 'Your Innovative Homesi login code';
        $intro    = 'Use the code below to log in to your account. It expires in 10 minutes.';
    }
    $greeting = $userName !== '' ? 'Hi ' . htmlspecialchars($userName) . ',' : 'Hello,';
    $body = '
        <div style="font-family:Arial,sans-serif;max-width:520px;margin:0 auto;padding:24px;color:#222;">
            <h2 style="color:#d4a574;margin:0 0 16px;">Innovative Homesi</h2>
            <p>' . $greeting . '</p>
            <p>' . $intro . '</p>
            <p style="font-size:32px;font-weight:bold;letter-spacing:8px;background:#faf1e5;padding:16px 24px;text-align:center;border-radius:8px;margin:24px 0;">
                ' . $otp . '
            </p>
            <p style="color:#666;font-size:13px;">If you did not request this code, you can safely ignore this email.</p>
            <p style="color:#999;font-size:12px;margin-top:32px;">— Innovative Homesi</p>
        </div>';

    $mail = sendEmail($email, $subject, $body, $userName);
    if (!$mail['success']) {
        // Roll back the stored OTP since we couldn't deliver it
        try { $pdo->prepare('DELETE FROM email_otps WHERE email = ?')->execute([$email]); } catch (PDOException $e) {}
        error_log('Email OTP send failed for ' . $email . ': ' . $mail['message']);
        return ['success' => false, 'message' => 'Could not send OTP email. Please try again later.'];
    }

    return ['success' => true, 'message' => 'OTP sent to your email'];
}

/**
 * Verify a 6-digit OTP against the latest record for that email.
 * Consumes the record on success; increments attempts and discards after max attempts.
 */
function verifyEmailLoginOtp(PDO $pdo, string $email, string $otp): array {
    $email = strtolower(trim($email));
    if (!preg_match('/^\d{6}$/', $otp)) {
        return ['success' => false, 'message' => 'OTP must be 6 digits.'];
    }

    try {
        $stmt = $pdo->prepare('SELECT id, otp_hash, expires_at, attempts FROM email_otps WHERE email = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Email OTP lookup error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Could not verify OTP. Please try again.'];
    }

    if (!$row) {
        return ['success' => false, 'message' => 'No OTP found. Please request a new one.'];
    }

    if (strtotime($row['expires_at']) < time()) {
        $pdo->prepare('DELETE FROM email_otps WHERE id = ?')->execute([$row['id']]);
        return ['success' => false, 'message' => 'OTP has expired. Please request a new one.'];
    }

    if ((int)$row['attempts'] >= EMAIL_OTP_MAX_ATTEMPTS) {
        $pdo->prepare('DELETE FROM email_otps WHERE id = ?')->execute([$row['id']]);
        return ['success' => false, 'message' => 'Too many incorrect attempts. Please request a new OTP.'];
    }

    if (!password_verify($otp, $row['otp_hash'])) {
        $pdo->prepare('UPDATE email_otps SET attempts = attempts + 1 WHERE id = ?')->execute([$row['id']]);
        return ['success' => false, 'message' => 'Invalid OTP. Please try again.'];
    }

    // Success — consume the OTP
    $pdo->prepare('DELETE FROM email_otps WHERE email = ?')->execute([$email]);
    return ['success' => true, 'message' => 'OTP verified.'];
}
