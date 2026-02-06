-- ============================================================
-- INNOVATIVE HOMESI - SAMPLE PRODUCTS
-- ============================================================
-- This file contains 10 sample furniture products
-- Import this AFTER importing database.sql
-- ============================================================
-- Usage: mysql -u root -p innovative_homesi < sample-products.sql
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- SAMPLE FURNITURE PRODUCTS
-- ============================================================

-- 1. Modern L-Shape Sofa
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Modern L-Shape Sofa',
    'modern-l-shape-sofa',
    'IH-LSF-001',
    'Spacious and comfortable L-shaped sofa perfect for modern living rooms',
    'This elegant L-shaped sofa features premium fabric upholstery, solid wood frame, and plush cushioning. Perfect for entertaining guests or relaxing with family. The neutral beige color complements any decor style.',
    'Free delivery within 7-10 business days. Easy returns within 30 days. Assembly required.',
    45999.00,
    38999.00,
    (SELECT id FROM categories WHERE slug = 'l-shape-sofa' LIMIT 1),
    'images/category/cate-18.jpg',
    15,
    1,
    1,
    NOW(),
    NOW()
);

SET @product1_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product1_id, 'Dimensions (L x W x H)', '260cm x 160cm x 85cm', 1),
(@product1_id, 'Material', 'Premium Fabric & Solid Wood Frame', 2),
(@product1_id, 'Color', 'Beige', 3),
(@product1_id, 'Seating Capacity', '5-6 People', 4),
(@product1_id, 'Weight', '85 kg', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product1_id, 'images/category/cate-18.jpg', 1, 1);

-- 2. Recliner Sofa with Footrest
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Premium Recliner Sofa',
    'premium-recliner-sofa',
    'IH-REC-002',
    'Luxurious recliner sofa with adjustable footrest for ultimate comfort',
    'Indulge in ultimate relaxation with this premium recliner sofa. Features adjustable backrest, extendable footrest, and genuine leather upholstery. Perfect for your home theater or reading corner.',
    'Free delivery within 7-10 business days. Easy returns within 30 days. Assembly required.',
    32999.00,
    27999.00,
    (SELECT id FROM categories WHERE slug = 'recliner-sofa' LIMIT 1),
    'images/category/cate-19.jpg',
    20,
    1,
    1,
    NOW(),
    NOW()
);

SET @product2_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product2_id, 'Dimensions (L x W x H)', '190cm x 95cm x 100cm', 1),
(@product2_id, 'Material', 'Genuine Leather & Steel Frame', 2),
(@product2_id, 'Color', 'Dark Brown', 3),
(@product2_id, 'Recline Angle', '90° to 160°', 4),
(@product2_id, 'Weight Capacity', '150 kg', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product2_id, 'images/category/cate-19.jpg', 1, 1);

-- 3. Dining Table Set
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    '6-Seater Wooden Dining Table',
    '6-seater-wooden-dining-table',
    'IH-DT-003',
    'Elegant 6-seater dining table made from premium sheesham wood',
    'This stunning dining table set includes a solid sheesham wood table with 6 comfortable chairs. Perfect for family dinners and entertaining guests. Features a rich walnut finish and contemporary design.',
    'Free delivery within 10-15 business days. Easy returns within 30 days. Professional assembly available.',
    54999.00,
    NULL,
    (SELECT id FROM categories WHERE slug = 'dining-table' LIMIT 1),
    'images/category/cate-20.jpg',
    8,
    1,
    1,
    NOW(),
    NOW()
);

SET @product3_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product3_id, 'Dimensions (L x W x H)', '180cm x 90cm x 76cm', 1),
(@product3_id, 'Material', 'Solid Sheesham Wood', 2),
(@product3_id, 'Color', 'Walnut Brown', 3),
(@product3_id, 'Seating Capacity', '6 People', 4),
(@product3_id, 'Finish', 'Lacquer Finish', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product3_id, 'images/category/cate-20.jpg', 1, 1);

-- 4. King Size Bed with Storage
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'King Size Bed with Storage',
    'king-size-bed-storage',
    'IH-BED-004',
    'Spacious king size bed with hydraulic storage and upholstered headboard',
    'Maximize your bedroom storage with this elegant king size bed. Features hydraulic lift storage, padded headboard, and durable engineered wood construction. Available in multiple colors.',
    'Free delivery within 7-10 business days. Easy returns within 30 days. Assembly required.',
    38999.00,
    34999.00,
    (SELECT id FROM categories WHERE slug = 'beds-and-frames' LIMIT 1),
    'images/category/cate-21.jpg',
    12,
    1,
    1,
    NOW(),
    NOW()
);

SET @product4_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product4_id, 'Dimensions (L x W x H)', '210cm x 195cm x 110cm', 1),
(@product4_id, 'Material', 'Engineered Wood & Fabric', 2),
(@product4_id, 'Color', 'Grey', 3),
(@product4_id, 'Bed Size', 'King (72" x 78")', 4),
(@product4_id, 'Storage Type', 'Hydraulic Lift', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product4_id, 'images/category/cate-21.jpg', 1, 1);

-- 5. Center Table - Glass Top
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Modern Glass Center Table',
    'modern-glass-center-table',
    'IH-CT-005',
    'Contemporary center table with tempered glass top and wooden base',
    'Add a touch of elegance to your living room with this modern center table. Features 10mm tempered glass top, solid mango wood base, and ample storage space underneath.',
    'Free delivery within 5-7 business days. Easy returns within 30 days. No assembly required.',
    12999.00,
    10999.00,
    (SELECT id FROM categories WHERE slug = 'center-table' LIMIT 1),
    'images/category/cate-22.jpg',
    25,
    0,
    1,
    NOW(),
    NOW()
);

SET @product5_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product5_id, 'Dimensions (L x W x H)', '120cm x 60cm x 45cm', 1),
(@product5_id, 'Material', 'Tempered Glass & Mango Wood', 2),
(@product5_id, 'Color', 'Walnut Brown', 3),
(@product5_id, 'Glass Thickness', '10mm Tempered Glass', 4),
(@product5_id, 'Weight Capacity', '50 kg', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product5_id, 'images/category/cate-22.jpg', 1, 1);

-- 6. Accent Chair - Velvet
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Velvet Accent Chair - Emerald Green',
    'velvet-accent-chair-emerald',
    'IH-AC-006',
    'Luxurious velvet accent chair with gold metal legs',
    'Make a statement with this stunning velvet accent chair. Features plush velvet upholstery in emerald green, elegant gold metal legs, and ergonomic design. Perfect for bedroom or living room.',
    'Free delivery within 5-7 business days. Easy returns within 30 days. No assembly required.',
    15999.00,
    NULL,
    (SELECT id FROM categories WHERE slug = 'accent-chair' LIMIT 1),
    'images/category/cate-23.jpg',
    18,
    0,
    1,
    NOW(),
    NOW()
);

SET @product6_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product6_id, 'Dimensions (L x W x H)', '75cm x 80cm x 85cm', 1),
(@product6_id, 'Material', 'Velvet Fabric & Metal Legs', 2),
(@product6_id, 'Color', 'Emerald Green', 3),
(@product6_id, 'Leg Finish', 'Gold Powder Coated', 4),
(@product6_id, 'Weight Capacity', '120 kg', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product6_id, 'images/category/cate-23.jpg', 1, 1);

-- 7. TV Unit with Drawers
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Modern TV Unit with Storage',
    'modern-tv-unit-storage',
    'IH-TV-007',
    'Wall-mounted TV unit with drawers and open shelves',
    'Organize your entertainment area with this modern TV unit. Features 3 drawers, 2 open shelves, and cable management. Supports TVs up to 55 inches. Made from high-quality engineered wood.',
    'Free delivery within 7-10 business days. Easy returns within 30 days. Assembly required.',
    18999.00,
    16499.00,
    (SELECT id FROM categories WHERE slug = 'tv-unit' LIMIT 1),
    'images/gallery/gallery-19.jpg',
    10,
    1,
    1,
    NOW(),
    NOW()
);

SET @product7_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product7_id, 'Dimensions (L x W x H)', '180cm x 40cm x 50cm', 1),
(@product7_id, 'Material', 'Engineered Wood', 2),
(@product7_id, 'Color', 'Wenge Brown', 3),
(@product7_id, 'TV Size Support', 'Up to 55 inches', 4),
(@product7_id, 'Number of Drawers', '3', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product7_id, 'images/gallery/gallery-19.jpg', 1, 1);

-- 8. Ottoman Pouf
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Round Ottoman Pouf - Mustard',
    'round-ottoman-pouf-mustard',
    'IH-OTT-008',
    'Soft cushioned ottoman pouf with storage compartment',
    'Versatile ottoman pouf that serves as extra seating, footrest, or coffee table. Features hidden storage compartment, soft cushioning, and easy-to-clean fabric. Available in vibrant mustard color.',
    'Free delivery within 3-5 business days. Easy returns within 30 days. No assembly required.',
    5999.00,
    4999.00,
    (SELECT id FROM categories WHERE slug = 'ottoman-pouf' LIMIT 1),
    'images/gallery/gallery-20.jpg',
    30,
    0,
    1,
    NOW(),
    NOW()
);

SET @product8_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product8_id, 'Dimensions (Dia x H)', '45cm x 40cm', 1),
(@product8_id, 'Material', 'Fabric & Foam Cushioning', 2),
(@product8_id, 'Color', 'Mustard Yellow', 3),
(@product8_id, 'Storage', 'Hidden Storage Compartment', 4),
(@product8_id, 'Weight Capacity', '100 kg', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product8_id, 'images/gallery/gallery-20.jpg', 1, 1);

-- 9. Decorative Floor Lamp
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Tripod Floor Lamp - Wooden',
    'tripod-floor-lamp-wooden',
    'IH-LMP-009',
    'Contemporary tripod floor lamp with fabric shade',
    'Illuminate your space with this stylish tripod floor lamp. Features natural wood legs, linen fabric shade, and adjustable height. Perfect for reading corners or ambient lighting.',
    'Free delivery within 3-5 business days. Easy returns within 30 days. Bulb not included.',
    7999.00,
    NULL,
    (SELECT id FROM categories WHERE slug = 'lamps' LIMIT 1),
    'images/gallery/gallery-21.jpg',
    22,
    0,
    1,
    NOW(),
    NOW()
);

SET @product9_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product9_id, 'Dimensions (Dia x H)', '50cm x 150cm', 1),
(@product9_id, 'Material', 'Wood & Linen Fabric', 2),
(@product9_id, 'Color', 'Natural Wood & White Shade', 3),
(@product9_id, 'Bulb Type', 'E27 (Not Included)', 4),
(@product9_id, 'Max Wattage', '60W', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product9_id, 'images/gallery/gallery-21.jpg', 1, 1);

-- 10. Wall Art - Abstract Canvas
INSERT INTO products (name, slug, sku, short_desc, description, shipping_returns, price, sale_price, category_id, image_path, stock_quantity, is_featured, is_active, created_at, updated_at)
VALUES (
    'Abstract Canvas Wall Art Set',
    'abstract-canvas-wall-art-set',
    'IH-ART-010',
    'Set of 3 abstract canvas paintings for modern interiors',
    'Transform your walls with this stunning 3-piece abstract canvas art set. Features vibrant colors, high-quality canvas print, and ready-to-hang wooden frames. Perfect for living room or bedroom.',
    'Free delivery within 3-5 business days. Easy returns within 30 days. Ready to hang.',
    8999.00,
    7499.00,
    (SELECT id FROM categories WHERE slug = 'wall-art' LIMIT 1),
    'images/gallery/gallery-22.jpg',
    40,
    1,
    1,
    NOW(),
    NOW()
);

SET @product10_id = LAST_INSERT_ID();

INSERT INTO product_attributes (product_id, attribute_name, attribute_value, display_order) VALUES
(@product10_id, 'Dimensions (Each)', '40cm x 60cm', 1),
(@product10_id, 'Material', 'Canvas & Wooden Frame', 2),
(@product10_id, 'Color', 'Multi-Color Abstract', 3),
(@product10_id, 'Set Includes', '3 Pieces', 4),
(@product10_id, 'Mounting', 'Ready to Hang', 5);

INSERT INTO product_images (product_id, image_path, is_primary, display_order)
VALUES (@product10_id, 'images/gallery/gallery-22.jpg', 1, 1);

COMMIT;

-- ============================================================
-- SAMPLE PRODUCTS IMPORTED SUCCESSFULLY
-- ============================================================
SELECT '10 Sample products created successfully!' as Message;
SELECT COUNT(*) as Total_Products FROM products WHERE is_active = 1;
-- ============================================================
