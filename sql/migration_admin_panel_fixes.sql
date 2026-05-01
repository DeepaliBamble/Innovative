-- ============================================================
-- Migration: admin panel data fixes
-- Run this on an existing database if product add/edit or
-- related products fail because product_categories is missing.
-- ============================================================

CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_product_category` (`product_id`, `category_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `product_categories` (`product_id`, `category_id`, `is_primary`)
SELECT `id`, `category_id`, 1
FROM `products`
WHERE `category_id` IS NOT NULL;
