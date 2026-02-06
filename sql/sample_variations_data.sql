-- Sample Product Variations Data
-- This file provides example variations for testing the system
-- Run this AFTER importing product_variations.sql

-- ============================================================================
-- STEP 1: Assign Variation Types to Categories
-- ============================================================================

-- Find your category IDs first with: SELECT id, name FROM categories;
-- Replace the category IDs below with your actual category IDs

-- Assuming category structure:
-- 1 = Sofas & Couches
-- 2 = Chairs
-- 3 = Tables
-- 4 = Beds
-- 5 = Storage
-- 6 = Decor

-- Sofas: Color + Size
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 1, vt.id, 1, 1 FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM categories WHERE id = 1);

INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 1, vt.id, 0, 2 FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM categories WHERE id = 1);

INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 1, vt.id, 0, 3 FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM categories WHERE id = 1);

-- Chairs: Color + Material
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 2, vt.id, 1, 1 FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM categories WHERE id = 2);

INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 2, vt.id, 0, 2 FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM categories WHERE id = 2);

-- Tables: Material + Size
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 3, vt.id, 1, 1 FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM categories WHERE id = 3);

INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 3, vt.id, 0, 2 FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM categories WHERE id = 3);

-- Beds: Size + Color
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 4, vt.id, 1, 1 FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM categories WHERE id = 4);

INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 4, vt.id, 0, 2 FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM categories WHERE id = 4);

-- Decor: Color + Pattern
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 6, vt.id, 0, 1 FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM categories WHERE id = 6);

INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 6, vt.id, 0, 2 FROM variation_types vt WHERE vt.name = 'pattern' AND EXISTS (SELECT 1 FROM categories WHERE id = 6);

-- ============================================================================
-- STEP 2: Sample Product Variations
-- ============================================================================

-- Find your product IDs with: SELECT id, name, category_id FROM products LIMIT 10;
-- Replace product_id values below with your actual product IDs

-- Example Product 1: Modern L-Shaped Sofa (assuming product_id = 1)
-- Colors for Sofa
INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Navy Blue', 'Navy Blue', '#000080', 0.00, 10, '-NAVY', 1
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Light Gray', 'Light Gray', '#D3D3D3', 0.00, 15, '-GRAY', 2
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Burgundy', 'Burgundy', '#800020', 2000.00, 5, '-BURG', 3
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Beige', 'Beige', '#F5F5DC', 0.00, 12, '-BEIGE', 4
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Charcoal', 'Charcoal', '#36454F', 1000.00, 8, '-CHAR', 5
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

-- Sizes for Sofa
INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, '2-Seater', '2-Seater', NULL, -5000.00, 20, '-2S', 1
FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, '3-Seater', '3-Seater', NULL, 0.00, 15, '-3S', 2
FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, '4-Seater', '4-Seater', NULL, 8000.00, 8, '-4S', 3
FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

-- Materials for Sofa
INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Fabric', 'Fabric', NULL, 0.00, 30, '-FAB', 1
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Velvet', 'Velvet', NULL, 5000.00, 15, '-VEL', 2
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 1, vt.id, 'Leather', 'Genuine Leather', NULL, 12000.00, 10, '-LEATH', 3
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 1);

-- ============================================================================
-- Example Product 2: Dining Chair (assuming product_id = 2)
-- ============================================================================

-- Colors for Chair
INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 2, vt.id, 'White', 'White', '#FFFFFF', 0.00, 25, '-WHT', 1
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 2);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 2, vt.id, 'Black', 'Black', '#000000', 0.00, 30, '-BLK', 2
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 2);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 2, vt.id, 'Natural Wood', 'Natural Wood', '#DEB887', 500.00, 20, '-NAT', 3
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 2);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 2, vt.id, 'Walnut', 'Walnut', '#5C4033', 1000.00, 15, '-WAL', 4
FROM variation_types vt WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM products WHERE id = 2);

-- Materials for Chair
INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 2, vt.id, 'Plastic', 'Plastic', NULL, 0.00, 50, '-PLS', 1
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 2);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 2, vt.id, 'Metal', 'Metal Frame', NULL, 800.00, 30, '-MET', 2
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 2);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 2, vt.id, 'Wood', 'Solid Wood', NULL, 1500.00, 20, '-WD', 3
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 2);

-- ============================================================================
-- Example Product 3: Dining Table (assuming product_id = 3)
-- ============================================================================

-- Materials for Table
INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 3, vt.id, 'Sheesham Wood', 'Sheesham', NULL, 0.00, 10, '-SHEE', 1
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 3);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 3, vt.id, 'Teak Wood', 'Teak', NULL, 10000.00, 5, '-TEAK', 2
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 3);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 3, vt.id, 'Oak Wood', 'Oak', NULL, 15000.00, 8, '-OAK', 3
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 3);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 3, vt.id, 'Mango Wood', 'Mango', NULL, 5000.00, 12, '-MANG', 4
FROM variation_types vt WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM products WHERE id = 3);

-- Sizes for Table
INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 3, vt.id, '4-Seater', '4-Seater (120cm)', NULL, -3000.00, 15, '-4S', 1
FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM products WHERE id = 3);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 3, vt.id, '6-Seater', '6-Seater (150cm)', NULL, 0.00, 20, '-6S', 2
FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM products WHERE id = 3);

INSERT IGNORE INTO product_variations (product_id, variation_type_id, variation_name, variation_value, color_code, price_adjustment, stock_quantity, sku_suffix, display_order)
SELECT 3, vt.id, '8-Seater', '8-Seater (180cm)', NULL, 6000.00, 10, '-8S', 3
FROM variation_types vt WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM products WHERE id = 3);

-- ============================================================================
-- ADDITIONAL COLOR OPTIONS (Common Colors for Reference)
-- ============================================================================

-- Popular Furniture Colors with Hex Codes:
-- Red: #FF0000
-- Blue: #0000FF
-- Green: #008000
-- Yellow: #FFFF00
-- Orange: #FFA500
-- Purple: #800080
-- Pink: #FFC0CB
-- Brown: #A52A2A
-- Navy: #000080
-- Maroon: #800000
-- Olive: #808000
-- Teal: #008080
-- Silver: #C0C0C0
-- Gold: #FFD700
-- Cream: #FFFDD0
-- Ivory: #FFFFF0
-- Tan: #D2B48C
-- Khaki: #F0E68C
-- Coral: #FF7F50
-- Salmon: #FA8072
-- Mint: #98FF98
-- Lavender: #E6E6FA
-- Peach: #FFDAB9
-- Rust: #B7410E
-- Mustard: #FFDB58

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Check assigned variation types per category:
-- SELECT c.name as category, vt.display_name as variation_type, cvt.is_required
-- FROM category_variation_types cvt
-- JOIN categories c ON cvt.category_id = c.id
-- JOIN variation_types vt ON cvt.variation_type_id = vt.id
-- ORDER BY c.name, cvt.display_order;

-- Check variations for a specific product:
-- SELECT p.name as product, vt.display_name as type, pv.variation_value, pv.price_adjustment, pv.stock_quantity
-- FROM product_variations pv
-- JOIN products p ON pv.product_id = p.id
-- JOIN variation_types vt ON pv.variation_type_id = vt.id
-- WHERE p.id = 1
-- ORDER BY vt.display_order, pv.display_order;

-- Count variations by product:
-- SELECT p.id, p.name, COUNT(pv.id) as variation_count
-- FROM products p
-- LEFT JOIN product_variations pv ON p.id = pv.product_id
-- GROUP BY p.id
-- ORDER BY variation_count DESC;

-- ============================================================================
-- NOTES
-- ============================================================================
-- 1. Replace product_id values (1, 2, 3) with your actual product IDs
-- 2. Check your category IDs before running category assignments
-- 3. Adjust stock quantities based on your inventory
-- 4. Price adjustments are added to the base product price
-- 5. For image-based variations (material/pattern), upload images via admin panel
