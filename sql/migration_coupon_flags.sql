-- Migration: add new_user_only and show_on_site flags to coupons
-- new_user_only: coupon valid only on a logged-in customer's first (paid) order
-- show_on_site:  advertise this coupon in the cart/checkout coupon box
-- Safe to run multiple times on MariaDB (uses IF NOT EXISTS).

ALTER TABLE `coupons`
  ADD COLUMN IF NOT EXISTS `new_user_only` tinyint(1) DEFAULT 0 AFTER `is_active`,
  ADD COLUMN IF NOT EXISTS `show_on_site` tinyint(1) DEFAULT 0 AFTER `new_user_only`;
