-- Migration: 50% partial payment support on orders
-- payment_type: 'full' (pay total now) or 'partial' (pay 50% advance, balance on delivery)
-- amount_paid:  amount actually captured online
-- balance_due:  amount still to be collected (on delivery) for partial orders
-- Also adds 'partial' to the payment_status enum.
-- Safe to run multiple times on MariaDB.

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `payment_type` varchar(10) NOT NULL DEFAULT 'full' AFTER `payment_method`,
  ADD COLUMN IF NOT EXISTS `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `payment_type`,
  ADD COLUMN IF NOT EXISTS `balance_due` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `amount_paid`;

ALTER TABLE `orders`
  MODIFY COLUMN `payment_status` enum('pending','paid','partial','failed','refunded') DEFAULT 'pending';
