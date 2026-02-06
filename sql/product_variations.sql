-- Product Variations System
-- This SQL file adds comprehensive variation support to the e-commerce platform

-- ============================================================================
-- 1. VARIATION TYPES TABLE
-- Defines what types of variations are available (Color, Size, Material, etc.)
-- ============================================================================
CREATE TABLE IF NOT EXISTS variation_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    input_type ENUM('color', 'button', 'image', 'dropdown') DEFAULT 'button',
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default variation types
INSERT INTO variation_types (name, display_name, input_type, description, display_order) VALUES
('color', 'Color', 'color', 'Product color variations with color swatches', 1),
('size', 'Size', 'button', 'Product size options (S, M, L, XL, dimensions, etc.)', 2),
('material', 'Material/Fabric', 'image', 'Material or fabric type with optional preview images', 3),
('pattern', 'Pattern/Design', 'image', 'Different patterns or designs with preview images', 4);

-- ============================================================================
-- 2. CATEGORY VARIATION TYPES (Many-to-Many)
-- Links variation types to specific categories
-- ============================================================================
CREATE TABLE IF NOT EXISTS category_variation_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    variation_type_id INT NOT NULL,
    is_required TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_variation (category_id, variation_type_id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (variation_type_id) REFERENCES variation_types(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_variation_type (variation_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. PRODUCT VARIATIONS TABLE
-- Stores individual variation options for each product
-- ============================================================================
CREATE TABLE IF NOT EXISTS product_variations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    variation_type_id INT NOT NULL,
    variation_name VARCHAR(100) NOT NULL,
    variation_value VARCHAR(255) NOT NULL,
    color_code VARCHAR(20) NULL COMMENT 'Hex color code for color swatches',
    image_path VARCHAR(500) NULL COMMENT 'Image for material/pattern swatches',
    price_adjustment DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Price difference from base price',
    stock_quantity INT DEFAULT 0,
    sku_suffix VARCHAR(50) NULL COMMENT 'Suffix to add to product SKU',
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variation_type_id) REFERENCES variation_types(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_type (variation_type_id),
    INDEX idx_active (is_active),
    INDEX idx_stock (stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. PRODUCT VARIATION COMBINATIONS TABLE
-- Stores valid combinations of variations (e.g., Red + Large, Blue + Small)
-- ============================================================================
CREATE TABLE IF NOT EXISTS product_variation_combinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    combination_sku VARCHAR(100) UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    stock_quantity INT DEFAULT 0,
    image_path VARCHAR(500) NULL COMMENT 'Combination-specific image',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_sku (combination_sku),
    INDEX idx_active (is_active),
    INDEX idx_stock (stock_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. VARIATION COMBINATION VALUES (Link Table)
-- Maps which variations belong to which combinations
-- ============================================================================
CREATE TABLE IF NOT EXISTS variation_combination_values (
    id INT PRIMARY KEY AUTO_INCREMENT,
    combination_id INT NOT NULL,
    variation_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (combination_id) REFERENCES product_variation_combinations(id) ON DELETE CASCADE,
    FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_combination_variation (combination_id, variation_id),
    INDEX idx_combination (combination_id),
    INDEX idx_variation (variation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SAMPLE DATA FOR TESTING
-- ============================================================================

-- Example 1: Assign Color and Size variations to Sofas category (ID: 1)
-- First, let's check if category ID 1 exists and is Sofas
-- You may need to adjust category IDs based on your actual data

-- Assign Color to Sofas category
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 1, vt.id, 1, 1
FROM variation_types vt
WHERE vt.name = 'color' AND EXISTS (SELECT 1 FROM categories WHERE id = 1);

-- Assign Size to Sofas category
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 1, vt.id, 0, 2
FROM variation_types vt
WHERE vt.name = 'size' AND EXISTS (SELECT 1 FROM categories WHERE id = 1);

-- Example 2: Assign Material to Tables category (ID: 2)
INSERT IGNORE INTO category_variation_types (category_id, variation_type_id, is_required, display_order)
SELECT 2, vt.id, 1, 1
FROM variation_types vt
WHERE vt.name = 'material' AND EXISTS (SELECT 1 FROM categories WHERE id = 2);

-- ============================================================================
-- VIEWS FOR EASIER QUERYING
-- ============================================================================

-- View to get all variations for a product with type information
CREATE OR REPLACE VIEW v_product_variations AS
SELECT
    pv.id,
    pv.product_id,
    p.name as product_name,
    pv.variation_type_id,
    vt.name as variation_type,
    vt.display_name as variation_type_display,
    vt.input_type,
    pv.variation_name,
    pv.variation_value,
    pv.color_code,
    pv.image_path,
    pv.price_adjustment,
    pv.stock_quantity,
    pv.sku_suffix,
    pv.is_active,
    pv.display_order
FROM product_variations pv
JOIN products p ON pv.product_id = p.id
JOIN variation_types vt ON pv.variation_type_id = vt.id
WHERE pv.is_active = 1 AND p.is_active = 1
ORDER BY pv.product_id, vt.display_order, pv.display_order;

-- View to get available variation types for a category
CREATE OR REPLACE VIEW v_category_variations AS
SELECT
    c.id as category_id,
    c.name as category_name,
    vt.id as variation_type_id,
    vt.name as variation_type,
    vt.display_name,
    vt.input_type,
    cvt.is_required,
    cvt.display_order
FROM categories c
JOIN category_variation_types cvt ON c.id = cvt.category_id
JOIN variation_types vt ON cvt.variation_type_id = vt.id
WHERE c.is_active = 1 AND vt.is_active = 1
ORDER BY c.id, cvt.display_order;

-- ============================================================================
-- HELPFUL QUERIES FOR REFERENCE
-- ============================================================================

-- Get all variations for a specific product (replace ? with product_id)
-- SELECT * FROM v_product_variations WHERE product_id = ?;

-- Get variation types available for a product's category
-- SELECT DISTINCT vt.*
-- FROM products p
-- JOIN category_variation_types cvt ON p.category_id = cvt.category_id
-- JOIN variation_types vt ON cvt.variation_type_id = vt.id
-- WHERE p.id = ? AND vt.is_active = 1;

-- Get all combinations for a product with their variations
-- SELECT
--     pvc.id,
--     pvc.combination_sku,
--     pvc.price,
--     pvc.sale_price,
--     pvc.stock_quantity,
--     GROUP_CONCAT(CONCAT(vt.display_name, ': ', pv.variation_value) SEPARATOR ', ') as variation_details
-- FROM product_variation_combinations pvc
-- JOIN variation_combination_values vcv ON pvc.id = vcv.combination_id
-- JOIN product_variations pv ON vcv.variation_id = pv.id
-- JOIN variation_types vt ON pv.variation_type_id = vt.id
-- WHERE pvc.product_id = ?
-- GROUP BY pvc.id;

-- ============================================================================
-- NOTES FOR IMPLEMENTATION
-- ============================================================================
-- 1. When adding a product, check its category's variation types
-- 2. Admin should be able to add variations based on allowed types
-- 3. Frontend should display variations as swatches/buttons based on input_type
-- 4. When variation is selected, update price and check stock availability
-- 5. Cart should store selected variation IDs to track exact product variant
-- 6. Consider adding variation images that change the main product image
