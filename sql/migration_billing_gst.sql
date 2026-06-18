-- Migration: add billing address, GST number, and business name to orders
-- Safe to run multiple times on MariaDB (uses IF NOT EXISTS). For MySQL without
-- IF NOT EXISTS support, apply each ADD COLUMN once.

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `business_name` varchar(255) DEFAULT NULL AFTER `notes`,
  ADD COLUMN IF NOT EXISTS `gst_number` varchar(20) DEFAULT NULL AFTER `business_name`,
  ADD COLUMN IF NOT EXISTS `billing_same_as_shipping` tinyint(1) DEFAULT 1 AFTER `gst_number`,
  ADD COLUMN IF NOT EXISTS `billing_full_name` varchar(255) DEFAULT NULL AFTER `billing_same_as_shipping`,
  ADD COLUMN IF NOT EXISTS `billing_address_line1` varchar(255) DEFAULT NULL AFTER `billing_full_name`,
  ADD COLUMN IF NOT EXISTS `billing_address_line2` varchar(255) DEFAULT NULL AFTER `billing_address_line1`,
  ADD COLUMN IF NOT EXISTS `billing_city` varchar(100) DEFAULT NULL AFTER `billing_address_line2`,
  ADD COLUMN IF NOT EXISTS `billing_state` varchar(100) DEFAULT NULL AFTER `billing_city`,
  ADD COLUMN IF NOT EXISTS `billing_postal_code` varchar(20) DEFAULT NULL AFTER `billing_state`,
  ADD COLUMN IF NOT EXISTS `billing_country` varchar(100) DEFAULT 'India' AFTER `billing_postal_code`;
