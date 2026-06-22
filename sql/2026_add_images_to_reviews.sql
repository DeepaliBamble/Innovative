-- Adds photo support to product reviews.
-- Stores a JSON array of uploaded image paths (relative to the site root), e.g.
--   ["uploads/reviews/ab12_1737000000.jpg","uploads/reviews/cd34_1737000001.webp"]
-- Run once against each environment (local + production). Safe to re-run on MariaDB.

ALTER TABLE `reviews`
  ADD COLUMN IF NOT EXISTS `images` TEXT DEFAULT NULL AFTER `comment`;
