-- Migration: Add "Accessories & Hardware" parent category
-- Run once against the production / local DB.
-- Idempotent: safe to re-run; INSERT IGNORE skips rows that already exist (slug is UNIQUE).

INSERT IGNORE INTO `categories`
    (`name`, `slug`, `description`, `image_path`, `parent_id`, `display_order`, `is_active`)
VALUES
    ('Accessories & Hardware', 'accessories-and-hardware',
     'Furniture accessories and hardware.',
     NULL, NULL, 7, 1);
