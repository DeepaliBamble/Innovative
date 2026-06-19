-- =====================================================================
-- Multiple coupon (stacking) support.
-- Run once on each environment (local + production). Safe to re-run.
-- =====================================================================

-- 1) Allow an admin to mark a coupon as "exclusive" — it cannot be combined
--    with any other coupon (must be used on its own).
ALTER TABLE `coupons`
    ADD COLUMN IF NOT EXISTS `exclusive` tinyint(1) NOT NULL DEFAULT 0 AFTER `new_user_only`;

-- 2) Orders can now record more than one coupon code (comma-separated),
--    so widen the column from varchar(50).
ALTER TABLE `orders`
    MODIFY `coupon_code` varchar(255) DEFAULT NULL;
