-- ============================================================
-- Migration: switch customer auth to OTP-only
-- Run ONCE on the production database (phpMyAdmin > SQL tab).
--
-- Changes:
--   1. password becomes optional (customers no longer have one)
--   2. email becomes optional (still UNIQUE — multiple NULLs are allowed)
--   3. add `location` column for the new register form
--   4. enforce unique phone numbers at the DB level
-- ============================================================

ALTER TABLE `users`
    MODIFY `password` VARCHAR(255) NULL,
    MODIFY `email`    VARCHAR(255) NULL,
    ADD COLUMN `location` VARCHAR(255) NULL AFTER `phone`;

-- Add unique key on phone. If this fails because of existing duplicates,
-- clean them up first with:
--   SELECT phone, COUNT(*) c FROM users WHERE phone IS NOT NULL GROUP BY phone HAVING c > 1;
ALTER TABLE `users` ADD UNIQUE KEY `uniq_phone` (`phone`);
