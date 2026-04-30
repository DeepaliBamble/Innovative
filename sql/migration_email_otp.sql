-- ============================================================
-- Migration: email OTP login
-- Run ONCE on the production database (phpMyAdmin > SQL tab).
-- ============================================================

CREATE TABLE IF NOT EXISTS `email_otps` (
    `id`         INT(11) NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(255) NOT NULL,
    `otp_hash`   VARCHAR(255) NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `attempts`   INT(11) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email`   (`email`),
    KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
