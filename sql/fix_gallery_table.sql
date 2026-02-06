-- Fix gallery table to use category_id instead of category ENUM

-- Step 1: Add the new category_id column
ALTER TABLE `gallery` ADD COLUMN `category_id` int(11) DEFAULT NULL AFTER `image_path`;

-- Step 2: Add foreign key index
ALTER TABLE `gallery` ADD KEY `idx_category_id` (`category_id`);

-- Step 3: Drop the old category column
ALTER TABLE `gallery` DROP COLUMN `category`;

-- Note: Run this SQL in phpMyAdmin or MySQL command line
