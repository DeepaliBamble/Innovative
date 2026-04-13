-- ============================================================
-- MIGRATION: Razorpay Payment Gateway & Security Enhancements
-- Run this AFTER the base database.sql has been imported
-- ============================================================

-- --------------------------------------------------------
-- 1. ALTER orders table to add missing columns for Razorpay
-- --------------------------------------------------------

-- Allow guest checkout (user_id nullable)
ALTER TABLE `orders` MODIFY COLUMN `user_id` int(11) DEFAULT NULL;

-- Drop foreign key constraint on user_id if it exists (to allow NULL)
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_NAME = 'orders_ibfk_1' AND TABLE_NAME = 'orders' AND TABLE_SCHEMA = DATABASE());
SET @sql = IF(@fk_exists > 0, 'ALTER TABLE `orders` DROP FOREIGN KEY `orders_ibfk_1`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key back as nullable
ALTER TABLE `orders` ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Add session_id for guest orders
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `session_id` varchar(100) DEFAULT NULL AFTER `user_id`;

-- Add customer info columns
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_name` varchar(255) DEFAULT NULL AFTER `session_id`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_email` varchar(255) DEFAULT NULL AFTER `customer_name`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `customer_phone` varchar(20) DEFAULT NULL AFTER `customer_email`;

-- Add shipping detail columns
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_full_name` varchar(255) DEFAULT NULL AFTER `customer_phone`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_phone` varchar(20) DEFAULT NULL AFTER `shipping_full_name`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_address_line1` varchar(255) DEFAULT NULL AFTER `shipping_phone`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_address_line2` varchar(255) DEFAULT NULL AFTER `shipping_address_line1`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_city` varchar(100) DEFAULT NULL AFTER `shipping_address_line2`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_state` varchar(100) DEFAULT NULL AFTER `shipping_city`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_postal_code` varchar(20) DEFAULT NULL AFTER `shipping_state`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_country` varchar(100) DEFAULT 'India' AFTER `shipping_postal_code`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `shipping_method` varchar(50) DEFAULT 'standard' AFTER `shipping_country`;

-- Add coupon and payment gateway columns
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `coupon_code` varchar(50) DEFAULT NULL AFTER `discount_amount`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `razorpay_order_id` varchar(100) DEFAULT NULL AFTER `payment_method`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `razorpay_payment_id` varchar(100) DEFAULT NULL AFTER `razorpay_order_id`;
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `paid_at` timestamp NULL DEFAULT NULL AFTER `razorpay_payment_id`;

-- Add index for razorpay lookups
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_razorpay_order` (`razorpay_order_id`);
ALTER TABLE `orders` ADD INDEX IF NOT EXISTS `idx_session` (`session_id`);

-- --------------------------------------------------------
-- 2. Create payments table for Razorpay payment tracking
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','authorized','captured','failed','refunded') DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `payment_method` varchar(50) DEFAULT NULL,
  `error_code` varchar(50) DEFAULT NULL,
  `error_description` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_razorpay_order` (`razorpay_order_id`),
  KEY `idx_razorpay_payment` (`razorpay_payment_id`),
  KEY `idx_status` (`payment_status`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Create order_tracking table
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT 'system',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Create CSRF tokens table for security
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Create rate_limiting table for brute force protection
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_limiting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `action` varchar(50) NOT NULL,
  `attempts` int(11) DEFAULT 1,
  `first_attempt_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_attempt_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `blocked_until` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_action` (`ip_address`, `action`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_action` (`action`),
  KEY `idx_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Create webhook_logs table for Razorpay webhook tracking
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `webhook_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(100) NOT NULL,
  `payload` longtext NOT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `is_processed` tinyint(1) DEFAULT 0,
  `processing_result` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_event` (`event_type`),
  KEY `idx_razorpay_order` (`razorpay_order_id`),
  KEY `idx_processed` (`is_processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Update default admin password to bcrypt hash
--    Password: admin123 (CHANGE THIS IN PRODUCTION!)
-- --------------------------------------------------------
UPDATE `users` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE `id` = 1 AND `password` = 'admin123';

-- Update test user password too
UPDATE `users` 
SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE `id` = 2 AND `password` = 'test123';

-- --------------------------------------------------------
-- 8. Clean up expired OTP entries (maintenance query)
-- --------------------------------------------------------
DELETE FROM `otp_verifications` WHERE `expires_at` < NOW();

-- --------------------------------------------------------
-- 9. Clean up expired rate limiting entries
-- --------------------------------------------------------
CREATE EVENT IF NOT EXISTS `cleanup_rate_limiting`
ON SCHEDULE EVERY 1 HOUR
DO DELETE FROM `rate_limiting` WHERE `last_attempt_at` < DATE_SUB(NOW(), INTERVAL 24 HOUR);

CREATE EVENT IF NOT EXISTS `cleanup_csrf_tokens`
ON SCHEDULE EVERY 1 HOUR
DO DELETE FROM `csrf_tokens` WHERE `expires_at` < NOW();

-- ============================================================
-- MIGRATION COMPLETE
-- ============================================================
