-- =====================================================================
-- Coupon usage tracking (per-user limit support)
-- Run this once on the production DB. Safe to re-run (IF NOT EXISTS).
-- =====================================================================

CREATE TABLE IF NOT EXISTS `coupon_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_coupon_user` (`coupon_id`, `user_id`),
  KEY `idx_coupon_email` (`coupon_id`, `email`),
  KEY `idx_order` (`order_id`),
  CONSTRAINT `fk_coupon_usage_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add per-user usage limit column to coupons table (0/NULL = no per-user limit)
ALTER TABLE `coupons`
    ADD COLUMN IF NOT EXISTS `per_user_limit` int(11) DEFAULT NULL AFTER `usage_limit`;
