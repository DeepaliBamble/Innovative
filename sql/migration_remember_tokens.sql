-- ============================================================
-- MIGRATION: Remember Me Token Storage
-- Run this after database.sql and migration_razorpay_security.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL COMMENT 'SHA-256 hash of the cookie token',
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token_hash`),
  KEY `idx_user` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clean up expired tokens automatically
CREATE EVENT IF NOT EXISTS `cleanup_remember_tokens`
ON SCHEDULE EVERY 1 DAY
DO DELETE FROM `remember_tokens` WHERE `expires_at` < NOW();

-- ============================================================
-- MIGRATION COMPLETE
-- ============================================================
