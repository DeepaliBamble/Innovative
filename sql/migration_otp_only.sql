-- ============================================================
-- Migration: switch customer auth to OTP-only
-- Run each statement separately in phpMyAdmin > SQL.
-- Any statement that errors with "Duplicate column" / "Duplicate key"
-- just means it was already applied — skip and move on.
-- ============================================================

-- 1. password becomes optional (customers no longer have one)
ALTER TABLE `users` MODIFY `password` VARCHAR(255) NULL;

-- 2. email becomes optional (still UNIQUE — multiple NULLs are allowed)
ALTER TABLE `users` MODIFY `email` VARCHAR(255) NULL;

-- 3. Add `location` column. Skip if already present.
ALTER TABLE `users` ADD COLUMN `location` VARCHAR(255) NULL AFTER `phone`;

-- 4. Enforce unique phone numbers at DB level.
-- If this errors with "Duplicate entry", find dupes first with:
--   SELECT phone, COUNT(*) c FROM users WHERE phone IS NOT NULL AND phone <> '' GROUP BY phone HAVING c > 1;
ALTER TABLE `users` ADD UNIQUE KEY `uniq_phone` (`phone`);
