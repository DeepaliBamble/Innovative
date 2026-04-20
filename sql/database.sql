-- ============================================================
-- INNOVATIVE HOMESI - COMPLETE DATABASE SCHEMA
-- ============================================================
-- This file contains all necessary tables and initial data
-- for the Innovative Homesi furniture e-commerce platform
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- USER MANAGEMENT TABLES
-- ============================================================

--
-- Table: users
-- Purpose: Store customer and admin accounts
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_is_admin` (`is_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Default Users: Admin and Test Customer
--
-- IMPORTANT: Change these passwords immediately after first login.
-- Admin password: Admin@Homesi2026 | Test user password: Customer@Homesi2026
-- To generate your own hash: php -r "echo password_hash('YourPassword', PASSWORD_DEFAULT);"
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `is_admin`, `is_active`, `email_verified`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@innovative.com', '$2y$10$Jk3NBNE9v0bznY6iJIx.0ez0d2xjQVMnleByXcF4VLQ2lO8CQPztm', NULL, 1, 1, 1, '2025-12-02 10:05:36', '2025-12-02 10:06:08'),
(2, 'Test User', 'customer@test.com', '$2y$10$pZdHJvft/i7xhNT7lK8zWOo6rD4cdSZbb1E7yiVV4DOHs7fA1muNi', NULL, 0, 1, 1, '2025-12-06 11:56:23', '2025-12-06 12:19:53');

--
-- Table: otp_verifications
-- Purpose: Store OTP codes for login and registration
--
CREATE TABLE `otp_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `otp_type` enum('login','registration') NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_type` (`email`, `otp_type`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_otp_lookup` (`email`, `otp_code`, `otp_type`, `is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: addresses
-- Purpose: Store shipping and billing addresses
--
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `address_type` enum('shipping','billing') DEFAULT 'shipping',
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'USA',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PRODUCT CATALOG TABLES
-- ============================================================

--
-- Table: categories
-- Purpose: Product categories with hierarchical structure
--
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Default Categories
--
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image_path`, `parent_id`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sofa', 'sofa', NULL, NULL, NULL, 1, 1, '2025-12-06 10:51:15', '2025-12-06 10:58:55'),
(2, '3 Seater Sofa', '3-seater-sofa', NULL, NULL, 1, 1, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(3, 'L Shape Sofa', 'l-shape-sofa', NULL, NULL, 1, 2, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(4, 'Sofa Cum Bed', 'sofa-cum-bed', NULL, NULL, 1, 3, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(5, 'U Shape Sofa', 'u-shape-sofa', NULL, NULL, 1, 4, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(6, 'Modern Sofa', 'modern-sofa', NULL, NULL, 1, 5, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(7, 'Recliner Sofa', 'recliner-sofa', NULL, NULL, 1, 6, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(8, 'Chairs', 'chairs', NULL, NULL, NULL, 2, 1, '2025-12-06 10:51:15', '2025-12-06 10:59:09'),
(9, 'Dining Chair', 'dining-chair', NULL, NULL, 8, 1, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(10, 'Accent Chair', 'accent-chair', NULL, NULL, 8, 2, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(11, 'Bench & Ottoman', 'bench-ottoman', NULL, NULL, 8, 3, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(13, 'Tables & Storage', 'tables-storage', NULL, NULL, NULL, 3, 1, '2025-12-06 10:51:15', '2025-12-06 10:59:40'),
(14, 'Center Table', 'center-table', NULL, NULL, 13, 1, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(15, 'Dining Table', 'dining-table', NULL, NULL, 13, 2, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(16, 'Console Table', 'console-table', NULL, NULL, 13, 3, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(17, 'Side Table', 'side-table', NULL, NULL, 13, 4, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(18, 'Cabinet', 'cabinet', NULL, NULL, 13, 5, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(19, 'TV Unit', 'tv-unit', NULL, NULL, 13, 6, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(20, 'Beds', 'beds', NULL, NULL, NULL, 4, 1, '2025-12-06 10:51:15', '2025-12-06 10:59:50'),
(21, 'Beds and Frames', 'beds-and-frames', NULL, NULL, 20, 1, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(22, 'Nightstand / Side Table', 'nightstand-side-table', NULL, NULL, 20, 2, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(23, 'Bedroom Benches', 'bedroom-benches', NULL, NULL, 20, 3, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(24, 'Furnishing', 'furnishing', NULL, NULL, NULL, 5, 1, '2025-12-06 10:51:15', '2025-12-06 11:00:13'),
(25, 'Cushion', 'cushion', NULL, NULL, 24, 1, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(26, 'Table Runner', 'table-runner', NULL, NULL, 24, 2, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(27, 'Table Mats', 'table-mats', NULL, NULL, 24, 3, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(28, 'Rugs and Carpets', 'rugs-and-carpets', NULL, NULL, 24, 4, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(29, 'Decor & Vases', 'decor-and-vases', NULL, NULL, NULL, 6, 1, '2025-12-06 10:51:15', '2025-12-06 11:00:38'),
(30, 'Vases', 'vases', NULL, NULL, 29, 1, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(31, 'Lamps', 'lamps', NULL, NULL, 29, 2, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15'),
(32, 'Wall Art', 'wall-art', NULL, NULL, 29, 3, 1, '2025-12-06 10:51:15', '2025-12-06 10:51:15');

--
-- Table: products
-- Purpose: Store product information (includes shipping_returns field)
--
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `short_desc` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `shipping_returns` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_slug` (`slug`),
  KEY `idx_sku` (`sku`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_active` (`is_active`),
  KEY `idx_price` (`price`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: product_images
-- Purpose: Store multiple images per product
--
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: product_attributes
-- Purpose: Store product specifications (dimensions, material, etc.)
--
CREATE TABLE `product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `attribute_name` varchar(100) NOT NULL,
  `attribute_value` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: reviews
-- Purpose: Store customer product reviews
--
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_approved` (`is_approved`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SHOPPING CART & WISHLIST
-- ============================================================

--
-- Table: cart
-- Purpose: Store shopping cart items
--
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: wishlist
-- Purpose: Store customer wishlists
--
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ORDER MANAGEMENT
-- ============================================================

--
-- Table: orders
-- Purpose: Store customer orders
--
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_address_id` int(11) DEFAULT NULL,
  `billing_address_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_status` (`order_status`),
  KEY `idx_payment` (`payment_status`),
  KEY `shipping_address_id` (`shipping_address_id`),
  KEY `billing_address_id` (`billing_address_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`billing_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: order_items
-- Purpose: Store individual items within orders
--
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: coupons
-- Purpose: Store discount coupons
--
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` datetime DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CONTENT MANAGEMENT
-- ============================================================

--
-- Table: blogs
-- Purpose: Store blog posts and articles
--
CREATE TABLE `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `author_name` varchar(100) DEFAULT 'Innovative Homesi',
  `category` varchar(100) DEFAULT 'Furniture Tips',
  `tags` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_published` (`is_published`),
  KEY `is_featured` (`is_featured`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Sample Blog Posts
--
INSERT INTO `blogs` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_name`, `category`, `tags`, `meta_title`, `meta_description`, `is_published`, `is_featured`, `views`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'How to Choose the Perfect Sofa for Your Living Room', 'how-to-choose-perfect-sofa', 'Discover expert tips on selecting the ideal sofa that combines style, comfort, and functionality for your home.', '<p class=\"h4 text-black\">Choosing the right sofa is one of the most important decisions you\'ll make for your living room. It\'s not just about aestheticsÔÇöit\'s about finding a piece that fits your lifestyle, space, and comfort needs.</p><p class=\"h6\">When shopping for a sofa, consider the size of your room first. Measure your space carefully and leave enough room for movement. A sofa that\'s too large can overwhelm a small room, while one that\'s too small can look lost in a spacious area.</p><h3>Key Factors to Consider</h3><p class=\"h6\">Fabric choice is crucial for durability and maintenance. If you have children or pets, opt for stain-resistant fabrics like microfiber or treated leather. For a luxurious feel, velvet or linen can add elegance to your space.</p><p class=\"h6\">Don\'t forget about frame qualityÔÇölook for hardwood frames that will last for years. The suspension system (springs) should be sturdy and comfortable. Test the sofa in person if possibleÔÇösit on it, bounce a little, and make sure it feels right.</p><h3>Style Considerations</h3><p class=\"h6\">Your sofa should complement your existing d├®cor. Modern homes pair well with clean-lined contemporary sofas, while traditional spaces shine with classic tufted designs. L-shaped or sectional sofas work great for large families or those who love to entertain.</p>', 'images/blog/article-1.jpg', 'Sarah Johnson', 'Furniture Tips', '#SofaTips, #HomeDecor, #FurnitureGuide', 'How to Choose the Perfect Sofa - Furniture Guide', 'Expert tips on selecting the ideal sofa for your living room. Learn about sizing, fabrics, and styles.', 1, 1, 0, '2025-12-15 13:35:28', '2025-12-15 13:35:28', '2025-12-15 13:35:28'),
(2, 'The Ultimate Guide to Furniture Maintenance', 'ultimate-guide-furniture-maintenance', 'Keep your furniture looking new for years with these professional maintenance and care tips.', '<p class=\"h4 text-black\">Proper furniture maintenance not only preserves the beauty of your pieces but also extends their lifespan significantly. Here\'s your comprehensive guide to caring for different types of furniture.</p><p class=\"h6\">Regular cleaning is the foundation of furniture care. Dust your furniture weekly using a soft, lint-free cloth. For upholstered pieces, vacuum weekly using the upholstery attachment to remove dust and prevent it from settling into the fabric.</p><h3>Wood Furniture Care</h3><p class=\"h6\">Wood furniture requires special attention. Use coasters and placemats to protect surfaces from heat and moisture. Polish wood furniture every few months with appropriate productsÔÇöavoid silicone-based polishes that can damage the finish over time.</p><p class=\"h6\">Keep wood furniture away from direct sunlight and heating vents to prevent warping and fading. Maintain moderate humidity levels (40-45%) to prevent cracking.</p><h3>Upholstery Care</h3><p class=\"h6\">Act quickly on spillsÔÇöblot (don\'t rub) with a clean cloth. Use fabric-appropriate cleaners and always test in an inconspicuous area first. Professional cleaning is recommended annually for heavily used pieces.</p><p class=\"h6\">Rotate cushions regularly to ensure even wear. Fluff and plump cushions daily to maintain their shape and prevent permanent indentations.</p>', 'images/blog/article-2.jpg', 'Michael Chen', 'Maintenance', '#FurnitureCare, #Maintenance, #HomeTips', 'Furniture Maintenance Guide - Keep Your Furniture Beautiful', 'Professional tips for maintaining and caring for your furniture to ensure it lasts for years.', 1, 1, 0, '2025-12-15 13:35:28', '2025-12-15 13:35:28', '2025-12-15 13:35:28'),
(3, '2025 Interior Design Trends You Need to Know', '2025-interior-design-trends', 'Stay ahead of the curve with the latest interior design trends that are shaping homes in 2025.', '<p class=\"h4 text-black\">The world of interior design is constantly evolving, and 2025 brings exciting new trends that blend sustainability, technology, and timeless elegance. Here are the key trends shaping homes this year.</p><h3>Sustainable and Natural Materials</h3><p class=\"h6\">Eco-consciousness continues to dominate design choices. Expect to see more furniture made from reclaimed wood, bamboo, and recycled materials. Natural fabrics like organic cotton, linen, and wool are replacing synthetic alternatives.</p><p class=\"h6\">Earth tones are back in a big wayÔÇöthink warm terracotta, sage green, and sandy beige. These colors create calming, nature-inspired spaces that promote wellbeing.</p><h3>Multifunctional Spaces</h3><p class=\"h6\">With remote work becoming permanent for many, multifunctional furniture is essential. Sofa beds, extendable dining tables, and storage ottomans maximize space without compromising style.</p><h3>Bold Patterns and Textures</h3><p class=\"h6\">While neutral bases remain popular, people are getting adventurous with accent pieces. Geometric patterns, textured wallpapers, and statement lighting add personality without overwhelming spaces.</p><p class=\"h6\">Mixing materialsÔÇöpairing wood with metal, stone with glassÔÇöcreates visual interest and depth.</p>', 'images/blog/article-3.jpg', 'Emma Williams', 'Interior Design', '#DesignTrends, #InteriorDesign, #2025Trends', '2025 Interior Design Trends - Latest Home D├®cor Ideas', 'Discover the top interior design trends of 2025, from sustainable materials to bold patterns.', 1, 0, 0, '2025-12-15 13:35:28', '2025-12-15 13:35:28', '2025-12-15 13:35:28');

--
-- Table: blog_comments
-- Purpose: Store comments on blog posts
--
CREATE TABLE `blog_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_email` varchar(255) NOT NULL,
  `author_phone` varchar(20) DEFAULT NULL,
  `comment` text NOT NULL,
  `rating` int(1) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`),
  KEY `is_approved` (`is_approved`),
  CONSTRAINT `blog_comments_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: gallery
-- Purpose: Store gallery images for lookbook
--
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `category` enum('sofas','seating','dining','bedroom','decor','workspace','all') DEFAULT 'all',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CUSTOMER COMMUNICATION
-- ============================================================

--
-- Table: contact_messages
-- Purpose: Store contact form submissions
--
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table: customise_enquiries
-- Purpose: Store custom furniture enquiries
--
CREATE TABLE `customise_enquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `furniture_type` varchar(100) NOT NULL,
  `requirements` text NOT NULL,
  `timeline` varchar(100) DEFAULT NULL,
  `budget` varchar(100) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- AUTO INCREMENT SETTINGS
-- ============================================================

ALTER TABLE `addresses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `blogs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `blog_comments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cart` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `categories` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
ALTER TABLE `contact_messages` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `customise_enquiries` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `coupons` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `gallery` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `orders` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `order_items` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `otp_verifications` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `products` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `product_attributes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `product_images` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reviews` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `wishlist` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

COMMIT;

-- ============================================================
-- DATABASE SETUP COMPLETE
-- ============================================================
-- Database: innovative_homesi
-- Tables: 19
-- Default Users: Admin (admin@innovative.com / admin123)
--                Customer (customer@test.com / test123)
-- Categories: 32 (6 parent, 26 child)
-- Blog Posts: 3 sample articles
--
-- IMPORTANT: Change default passwords after setup!
-- ============================================================
