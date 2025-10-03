-- ============================================
-- E-COMMERCE DATABASE SCHEMA (PRODUCTION READY)
-- Standar Industri + Order System + Reporting
-- ============================================

-- ============================================
-- 1. BRANDS TABLE
-- ============================================
CREATE TABLE brands (
    brand_id INT PRIMARY KEY AUTO_INCREMENT,
    brand_name VARCHAR(255) NOT NULL UNIQUE,
    slug VARCHAR(255) UNIQUE NOT NULL,
    logo_url VARCHAR(500),
    description TEXT,
    website VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. CATEGORIES TABLE
-- ============================================
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    parent_category_id INT NULL,
    description TEXT,
    image_url VARCHAR(500),
    icon VARCHAR(100),
    level INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    
    INDEX idx_parent (parent_category_id),
    INDEX idx_slug (slug),
    INDEX idx_active (is_active),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. PRODUCTS TABLE (Master/Parent)
-- ============================================
CREATE TABLE products (
    product_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    brand_id INT,
    category_id INT,
    
    product_type ENUM('simple', 'variable', 'grouped', 'bundle', 'digital') DEFAULT 'simple',
    
    short_description VARCHAR(500),
    description TEXT,
    specifications JSON,
    
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    
    tags VARCHAR(500),
    featured BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT FALSE,
    is_bestseller BOOLEAN DEFAULT FALSE,
    
    status ENUM('draft', 'active', 'archived', 'out_of_stock') DEFAULT 'draft',
    
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    rating_count INT DEFAULT 0,
    review_count INT DEFAULT 0,
    
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_brand (brand_id),
    INDEX idx_category (category_id),
    INDEX idx_type (product_type),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_created (created_at),
    FULLTEXT idx_search (product_name, description, tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. PRODUCT VARIANTS (SKU Level)
-- ============================================
CREATE TABLE product_variants (
    variant_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL,
    
    sku VARCHAR(100) UNIQUE NOT NULL,
    barcode VARCHAR(100),
    upc VARCHAR(50),
    ean VARCHAR(50),
    isbn VARCHAR(50),
    mpn VARCHAR(100),
    
    variant_name VARCHAR(255),
    variant_description TEXT,
    
    price DECIMAL(12,2) NOT NULL,
    compare_at_price DECIMAL(12,2),
    cost_price DECIMAL(12,2),
    wholesale_price DECIMAL(12,2),
    
    tax_code VARCHAR(50),
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    is_taxable BOOLEAN DEFAULT TRUE,
    
    weight DECIMAL(10,3),
    weight_unit ENUM('g', 'kg', 'lb', 'oz') DEFAULT 'kg',
    length DECIMAL(10,2),
    width DECIMAL(10,2),
    height DECIMAL(10,2),
    dimension_unit ENUM('cm', 'm', 'in', 'ft') DEFAULT 'cm',
    
    track_inventory BOOLEAN DEFAULT TRUE,
    allow_backorder BOOLEAN DEFAULT FALSE,
    stock_status ENUM('in_stock', 'out_of_stock', 'on_backorder', 'discontinued') DEFAULT 'in_stock',
    
    is_default BOOLEAN DEFAULT FALSE,
    position INT DEFAULT 0,
    
    is_digital BOOLEAN DEFAULT FALSE,
    download_url VARCHAR(500),
    download_limit INT,
    download_expiry_days INT,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    
    INDEX idx_product (product_id),
    INDEX idx_sku (sku),
    INDEX idx_barcode (barcode),
    INDEX idx_active (is_active),
    INDEX idx_default (is_default),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. VARIANT OPTIONS (Flexible Attributes)
-- ============================================
CREATE TABLE variant_options (
    option_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id BIGINT NOT NULL,
    option_name VARCHAR(50) NOT NULL,
    option_value VARCHAR(100) NOT NULL,
    option_display_name VARCHAR(100),
    sort_order INT DEFAULT 0,
    
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    
    INDEX idx_variant (variant_id),
    INDEX idx_option_name (option_name),
    INDEX idx_option_value (option_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. LOCATIONS (Stores, Warehouses, etc)
-- ============================================
CREATE TABLE locations (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    location_name VARCHAR(255) NOT NULL,
    location_code VARCHAR(50) UNIQUE NOT NULL,
    location_type ENUM('store', 'warehouse', 'fulfillment_center', 'supplier', 'dropshipper') DEFAULT 'store',
    
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Indonesia',
    
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    
    phone VARCHAR(50),
    email VARCHAR(255),
    manager_name VARCHAR(255),
    
    opening_hours JSON,
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    
    is_active BOOLEAN DEFAULT TRUE,
    can_fulfill_online_orders BOOLEAN DEFAULT TRUE,
    can_pickup BOOLEAN DEFAULT FALSE,
    can_return BOOLEAN DEFAULT TRUE,
    priority INT DEFAULT 0,
    
    max_capacity INT,
    current_utilization DECIMAL(5,2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (location_type),
    INDEX idx_active (is_active),
    INDEX idx_code (location_code),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. INVENTORY (Stock Management)
-- ============================================
CREATE TABLE inventory (
    inventory_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id BIGINT NOT NULL,
    location_id INT NOT NULL,
    
    quantity_available INT NOT NULL DEFAULT 0,
    quantity_reserved INT DEFAULT 0,
    quantity_incoming INT DEFAULT 0,
    quantity_damaged INT DEFAULT 0,
    quantity_on_hold INT DEFAULT 0,
    
    quantity_on_hand INT GENERATED ALWAYS AS (quantity_available + quantity_reserved) STORED,
    quantity_sellable INT GENERATED ALWAYS AS (quantity_available - quantity_reserved) STORED,
    
    reorder_point INT DEFAULT 10,
    reorder_quantity INT DEFAULT 50,
    max_stock_level INT DEFAULT 1000,
    
    bin_location VARCHAR(50),
    aisle VARCHAR(20),
    shelf VARCHAR(20),
    
    last_counted_at TIMESTAMP,
    last_received_at TIMESTAMP,
    last_sold_at TIMESTAMP,
    
    notes TEXT,
    
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_inventory (variant_id, location_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE CASCADE,
    
    CHECK (quantity_available >= 0),
    CHECK (quantity_reserved >= 0),
    CHECK (quantity_incoming >= 0),
    CHECK (quantity_damaged >= 0),
    CHECK (quantity_reserved <= quantity_available + quantity_incoming),
    
    INDEX idx_variant (variant_id),
    INDEX idx_location (location_id),
    INDEX idx_available (quantity_available),
    INDEX idx_low_stock (reorder_point, quantity_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. LOCATION PRICING (Harga Berbeda per Location)
-- ============================================
CREATE TABLE location_pricing (
    pricing_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id BIGINT NOT NULL,
    location_id INT NOT NULL,
    
    price DECIMAL(12,2) NOT NULL,
    compare_at_price DECIMAL(12,2),
    cost_price DECIMAL(12,2),
    wholesale_price DECIMAL(12,2),
    
    margin_amount DECIMAL(12,2) GENERATED ALWAYS AS (price - COALESCE(cost_price, 0)) STORED,
    margin_percentage DECIMAL(5,2) GENERATED ALWAYS AS (
        CASE 
            WHEN cost_price > 0 THEN ((price - cost_price) / cost_price * 100)
            ELSE 0
        END
    ) STORED,
    
    valid_from DATE,
    valid_until DATE,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_location_pricing (variant_id, location_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE CASCADE,
    
    INDEX idx_variant (variant_id),
    INDEX idx_location (location_id),
    INDEX idx_active (is_active),
    INDEX idx_valid_dates (valid_from, valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. INVENTORY MOVEMENTS (Stock History)
-- ============================================
CREATE TABLE inventory_movements (
    movement_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    variant_id BIGINT NOT NULL,
    location_id INT NOT NULL,
    
    movement_type ENUM('in', 'out', 'transfer', 'adjustment', 'return', 'damaged', 'lost', 'found', 'sale', 'purchase') NOT NULL,
    quantity INT NOT NULL,
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    
    reference_type VARCHAR(50),
    reference_id BIGINT,
    reference_number VARCHAR(100),
    
    from_location_id INT,
    to_location_id INT,
    
    reason VARCHAR(255),
    notes TEXT,
    
    created_by INT,
    created_by_name VARCHAR(255),
    
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE CASCADE,
    FOREIGN KEY (from_location_id) REFERENCES locations(location_id) ON DELETE SET NULL,
    FOREIGN KEY (to_location_id) REFERENCES locations(location_id) ON DELETE SET NULL,
    
    INDEX idx_variant (variant_id),
    INDEX idx_location (location_id),
    INDEX idx_type (movement_type),
    INDEX idx_date (movement_date),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. CUSTOMERS
-- ============================================
CREATE TABLE customers (
    customer_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    
    password_hash VARCHAR(255),
    
    customer_type ENUM('retail', 'wholesale', 'vip') DEFAULT 'retail',
    
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(15,2) DEFAULT 0.00,
    
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    
    last_login_at TIMESTAMP,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_type (customer_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. CUSTOMER ADDRESSES
-- ============================================
CREATE TABLE customer_addresses (
    address_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT NOT NULL,
    
    address_type ENUM('billing', 'shipping', 'both') DEFAULT 'both',
    
    recipient_name VARCHAR(255),
    phone VARCHAR(50),
    
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Indonesia',
    
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    
    is_default BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    
    INDEX idx_customer (customer_id),
    INDEX idx_type (address_type),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. ORDERS
-- ============================================
CREATE TABLE orders (
    order_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    
    customer_id BIGINT,
    location_id INT NOT NULL,
    
    order_type ENUM('online', 'in_store', 'phone', 'wholesale') DEFAULT 'online',
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'partial', 'refunded', 'failed') DEFAULT 'pending',
    fulfillment_status ENUM('unfulfilled', 'partial', 'fulfilled') DEFAULT 'unfulfilled',
    
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    shipping_cost DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    discount_code VARCHAR(50),
    discount_type ENUM('percentage', 'fixed', 'free_shipping'),
    
    notes TEXT,
    internal_notes TEXT,
    
    shipping_method VARCHAR(100),
    tracking_number VARCHAR(100),
    
    billing_address_id BIGINT,
    shipping_address_id BIGINT,
    
    ip_address VARCHAR(50),
    user_agent TEXT,
    
    confirmed_at TIMESTAMP,
    shipped_at TIMESTAMP,
    delivered_at TIMESTAMP,
    completed_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE RESTRICT,
    
    INDEX idx_order_number (order_number),
    INDEX idx_customer (customer_id),
    INDEX idx_location (location_id),
    INDEX idx_status (order_status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created (created_at),
    INDEX idx_total (total_amount)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. ORDER ITEMS
-- ============================================
CREATE TABLE order_items (
    order_item_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    
    variant_id BIGINT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    variant_name VARCHAR(255),
    sku VARCHAR(100) NOT NULL,
    
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2),
    
    subtotal DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0.00,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) NOT NULL,
    
    profit_amount DECIMAL(15,2) GENERATED ALWAYS AS (
        total_amount - (COALESCE(unit_cost, 0) * quantity)
    ) STORED,
    
    fulfillment_status ENUM('unfulfilled', 'fulfilled', 'refunded', 'cancelled') DEFAULT 'unfulfilled',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE RESTRICT,
    
    INDEX idx_order (order_id),
    INDEX idx_variant (variant_id),
    INDEX idx_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. PAYMENTS
-- ============================================
CREATE TABLE payments (
    payment_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'bank_transfer', 'e_wallet', 'qris', 'cod') NOT NULL,
    payment_provider VARCHAR(100),
    
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    
    payment_status ENUM('pending', 'processing', 'success', 'failed', 'refunded') DEFAULT 'pending',
    
    transaction_id VARCHAR(255),
    reference_number VARCHAR(255),
    
    payment_date TIMESTAMP,
    
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    
    INDEX idx_order (order_id),
    INDEX idx_status (payment_status),
    INDEX idx_method (payment_method),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 15. PRODUCT IMAGES
-- ============================================
CREATE TABLE product_images (
    image_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL,
    variant_id BIGINT,
    
    image_url VARCHAR(500) NOT NULL,
    image_thumbnail_url VARCHAR(500),
    image_medium_url VARCHAR(500),
    image_large_url VARCHAR(500),
    
    alt_text VARCHAR(255),
    title VARCHAR(255),
    
    position INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    
    width INT,
    height INT,
    file_size INT,
    mime_type VARCHAR(50),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    
    INDEX idx_product (product_id),
    INDEX idx_variant (variant_id),
    INDEX idx_primary (is_primary),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRIGGERS: Auto Update Stock & Create Movements
-- ============================================

DELIMITER $$

-- Trigger: Reserve Stock saat Order Dibuat
CREATE TRIGGER after_order_item_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE v_location_id INT;
    DECLARE v_qty_available INT;
    
    -- Ambil location_id dari order
    SELECT location_id INTO v_location_id
    FROM orders WHERE order_id = NEW.order_id;
    
    -- Cek stock availability
    SELECT quantity_available INTO v_qty_available
    FROM inventory
    WHERE variant_id = NEW.variant_id AND location_id = v_location_id;
    
    IF v_qty_available >= NEW.quantity THEN
        -- Reserve stock (kurangi available, tambah reserved)
        UPDATE inventory
        SET quantity_reserved = quantity_reserved + NEW.quantity,
            quantity_available = quantity_available - NEW.quantity,
            updated_at = NOW()
        WHERE variant_id = NEW.variant_id 
          AND location_id = v_location_id;
        
        -- Log inventory movement (reserved)
        INSERT INTO inventory_movements (
            variant_id, location_id, movement_type, quantity,
            quantity_before, quantity_after,
            reference_type, reference_id, reference_number,
            reason, created_by_name
        )
        SELECT 
            NEW.variant_id,
            v_location_id,
            'out',
            NEW.quantity,
            quantity_available + NEW.quantity,
            quantity_available,
            'order',
            NEW.order_id,
            (SELECT order_number FROM orders WHERE order_id = NEW.order_id),
            'Stock reserved for order',
            'system'
        FROM inventory
        WHERE variant_id = NEW.variant_id AND location_id = v_location_id;
    END IF;
END$$

-- Trigger: Update Stock saat Order Confirmed
CREATE TRIGGER after_order_confirmed
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.order_status != 'confirmed' AND NEW.order_status = 'confirmed' THEN
        -- Stock sudah di-reserve, tinggal update last_sold_at
        UPDATE inventory i
        JOIN order_items oi ON i.variant_id = oi.variant_id
        SET i.last_sold_at = NOW()
        WHERE oi.order_id = NEW.order_id
          AND i.location_id = NEW.location_id;
    END IF;
END$$

-- Trigger: Release Stock saat Order Cancelled
CREATE TRIGGER after_order_cancelled
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.order_status != 'cancelled' AND NEW.order_status = 'cancelled' THEN
        -- Release reserved stock
        UPDATE inventory i
        JOIN order_items oi ON i.variant_id = oi.variant_id
        SET i.quantity_available = i.quantity_available + oi.quantity,
            i.quantity_reserved = i.quantity_reserved - oi.quantity,
            i.updated_at = NOW()
        WHERE oi.order_id = NEW.order_id
          AND i.location_id = NEW.location_id;
        
        -- Log inventory movement (cancelled)
        INSERT INTO inventory_movements (
            variant_id, location_id, movement_type, quantity,
            quantity_before, quantity_after,
            reference_type, reference_id, reference_number,
            reason, created_by_name
        )
        SELECT 
            oi.variant_id,
            NEW.location_id,
            'in',
            oi.quantity,
            i.quantity_available - oi.quantity,
            i.quantity_available,
            'order_cancelled',
            NEW.order_id,
            NEW.order_number,
            'Order cancelled - stock released',
            'system'
        FROM order_items oi
        JOIN inventory i ON oi.variant_id = i.variant_id AND i.location_id = NEW.location_id
        WHERE oi.order_id = NEW.order_id;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- VIEWS untuk REPORTING
-- ============================================

-- View: Product Availability dengan Harga per Location
CREATE VIEW view_product_availability AS
SELECT 
    p.product_id,
    p.product_name,
    p.product_type,
    p.status,
    
    pv.variant_id,
    pv.sku,
    pv.variant_name,
    
    l.location_id,
    l.location_name,
    l.location_code,
    l.location_type,
    l.city,
    
    COALESCE(lp.price, pv.price) AS price,
    COALESCE(lp.compare_at_price, pv.compare_at_price) AS compare_at_price,
    COALESCE(lp.cost_price, pv.cost_price) AS cost_price,
    
    i.quantity_available,
    i.quantity_reserved,
    i.quantity_sellable,
    i.reorder_point,
    
    CASE 
        WHEN i.quantity_sellable > i.reorder_point THEN 'available'
        WHEN i.quantity_sellable > 0 THEN 'low_stock'
        ELSE 'out_of_stock'
    END AS availability,
    
    pv.allow_backorder,
    l.can_pickup,
    l.can_fulfill_online_orders

FROM products p
JOIN product_variants pv ON p.product_id = pv.product_id
LEFT JOIN inventory i ON pv.variant_id = i.variant_id
LEFT JOIN locations l ON i.location_id = l.location_id
LEFT JOIN location_pricing lp ON pv.variant_id = lp.variant_id 
    AND l.location_id = lp.location_id 
    AND lp.is_active = TRUE
    AND (lp.valid_from IS NULL OR lp.valid_from <= CURDATE())
    AND (lp.valid_until IS NULL OR lp.valid_until >= CURDATE())

WHERE p.status = 'active' 
  AND pv.is_active = TRUE
  AND l.is_active = TRUE;

-- View: Sales Report per Location
CREATE VIEW view_sales_by_location AS
SELECT 
    l.location_id,
    l.location_name,
    l.location_code,
    l.location_type,
    l.city,
    
    DATE(o.created_at) AS sales_date,
    
    COUNT(DISTINCT o.order_id) AS total_orders,
    COUNT(DISTINCT o.customer_id) AS unique_customers,
    SUM(o.total_amount) AS total_revenue,
    SUM(o.subtotal) AS total_subtotal,
    SUM(o.discount_amount) AS total_discount,
    SUM(o.tax_amount) AS total_tax,
    SUM(oi.profit_amount) AS total_profit,
    AVG(o.total_amount) AS average_order_value,
    
    SUM(oi.quantity) AS total_items_sold

FROM orders o
JOIN locations l ON o.location_id = l.location_id
JOIN order_items oi ON o.order_id = oi.order_id

WHERE o.order_status NOT IN ('cancelled', 'refunded')
  AND o.payment_status = 'paid'

GROUP BY l.location_id, DATE(o.created_at);

-- View: Product Performance Report
CREATE VIEW view_product_performance AS
SELECT 
    p.product_id,
    p.product_name,
    p.product_type,
    b.brand_name,
    c.category_name,
    
    pv.variant_id,
    pv.sku,
    pv.variant_name,
    
    COUNT(DISTINCT oi.order_id) AS total_orders,
    SUM(oi.quantity) AS total_quantity_sold,
    SUM(oi.total_amount) AS total_revenue,
    SUM(oi.profit_amount) AS total_profit,
    AVG(oi.unit_price) AS average_price,
    
    (SELECT SUM(i.quantity_available) 
     FROM inventory i 
     WHERE i.variant_id = pv.variant_id) AS current_stock,
    
    MIN(o.created_at) AS first_sale_date,
    MAX(o.created_at) AS last_sale_date

FROM products p
JOIN product_variants pv ON p.product_id = pv.product_id
LEFT JOIN brands b ON p.brand_id = b.brand_id
LEFT JOIN categories c ON p.category_id = c.category_id
LEFT JOIN order_items oi ON pv.variant_id = oi.variant_id
LEFT JOIN orders o ON oi.order_id = o.order_id 
    AND o.order_status NOT IN ('cancelled', 'refunded')

GROUP BY p.product_id, pv.variant_id;

-- View: Stock Movement Summary
CREATE VIEW view_stock_movements AS
SELECT 
    l.location_id,
    l.location_name,
    l.city,
    
    p.product_name,
    pv.sku,
    pv.variant_name,
    
    im.movement_type,
    DATE(im.movement_date) AS movement_date,
    
    SUM(CASE WHEN im.movement_type IN ('in', 'transfer', 'return', 'found', 'purchase') THEN im.quantity ELSE 0 END) AS total_in,
    SUM(CASE WHEN im.movement_type IN ('out', 'damaged', 'lost', 'sale') THEN im.quantity ELSE 0 END) AS total_out,
    
    im.reference_type,
    im.reference_number

FROM inventory_movements im
JOIN locations l ON im.location_id = l.location_id
JOIN product_variants pv ON im.variant_id = pv.variant_id
JOIN products p ON pv.product_id = p.product_id

GROUP BY l.location_id, pv.variant_id, DATE(im.movement_date), im.movement_type;

-- View: Low Stock Alert
CREATE VIEW view_low_stock_alert AS
SELECT 
    p.product_name,
    pv.sku,
    pv.variant_name,
    l.location_name,
    l.location_type,
    l.city,
    
    i.quantity_available,
    i.quantity_reserved,
    i.quantity_sellable,
    i.reorder_point,
    i.reorder_quantity,
    
    (i.reorder_point - i.quantity_available) AS units_needed,
    
    CASE 
        WHEN i.quantity_available = 0 THEN 'critical'
        WHEN i.quantity_available <= (i.reorder_point * 0.5) THEN 'urgent'
        ELSE 'warning'
    END AS alert_level

FROM inventory i
JOIN product_variants pv ON i.variant_id = pv.variant_id
JOIN products p ON pv.product_id = p.product_id
JOIN locations l ON i.location_id = l.location_id

WHERE i.quantity_available <= i.reorder_point
  AND pv.is_active = TRUE
  AND p.status = 'active'
  AND l.is_active = TRUE

ORDER BY alert_level, units_needed DESC;

-- View: Customer Purchase History
CREATE VIEW view_customer_purchases AS
SELECT 
    c.customer_id,
    c.email,
    c.first_name,
    c.last_name,
    c.customer_type,
    
    COUNT(DISTINCT o.order_id) AS total_orders,
    SUM(o.total_amount) AS lifetime_value,
    AVG(o.total_amount) AS average_order_value,
    
    MIN(o.created_at) AS first_order_date,
    MAX(o.created_at) AS last_order_date,
    
    DATEDIFF(NOW(), MAX(o.created_at)) AS days_since_last_order,
    
    SUM(oi.quantity) AS total_items_purchased

FROM customers c
LEFT JOIN orders o ON c.customer_id = o.customer_id 
    AND o.order_status NOT IN ('cancelled', 'refunded')
    AND o.payment_status = 'paid'
LEFT JOIN order_items oi ON o.order_id = oi.order_id

GROUP BY c.customer_id;

-- ============================================
-- STORED PROCEDURES
-- ============================================

DELIMITER $

-- Procedure: Create Order dengan Auto Stock Reserve
CREATE PROCEDURE sp_create_order(
    IN p_customer_id BIGINT,
    IN p_location_id INT,
    IN p_order_type VARCHAR(20),
    IN p_items JSON,
    OUT p_order_id BIGINT,
    OUT p_status VARCHAR(50),
    OUT p_message TEXT
)
BEGIN
    DECLARE v_order_number VARCHAR(50);
    DECLARE v_subtotal DECIMAL(15,2) DEFAULT 0;
    DECLARE v_tax_amount DECIMAL(15,2) DEFAULT 0;
    DECLARE v_total_amount DECIMAL(15,2) DEFAULT 0;
    DECLARE v_item_count INT;
    DECLARE v_idx INT DEFAULT 0;
    DECLARE v_variant_id BIGINT;
    DECLARE v_quantity INT;
    DECLARE v_price DECIMAL(12,2);
    DECLARE v_cost_price DECIMAL(12,2);
    DECLARE v_stock_available INT;
    DECLARE v_product_name VARCHAR(255);
    DECLARE v_variant_name VARCHAR(255);
    DECLARE v_sku VARCHAR(100);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'error';
        SET p_message = 'Failed to create order. Transaction rolled back.';
    END;
    
    START TRANSACTION;
    
    -- Generate order number
    SET v_order_number = CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 10000), 4, '0'));
    
    -- Hitung jumlah item
    SET v_item_count = JSON_LENGTH(p_items);
    
    -- Validasi semua items sebelum create order
    WHILE v_idx < v_item_count DO
        SET v_variant_id = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].variant_id')));
        SET v_quantity = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].quantity')));
        
        -- Cek stock availability
        SELECT quantity_sellable INTO v_stock_available
        FROM inventory
        WHERE variant_id = v_variant_id AND location_id = p_location_id;
        
        IF v_stock_available < v_quantity THEN
            SET p_status = 'error';
            SET p_message = CONCAT('Insufficient stock for variant_id: ', v_variant_id);
            ROLLBACK;
            LEAVE;
        END IF;
        
        SET v_idx = v_idx + 1;
    END WHILE;
    
    -- Jika validasi OK, create order
    IF p_status != 'error' THEN
        INSERT INTO orders (
            order_number, customer_id, location_id, order_type,
            order_status, payment_status, fulfillment_status
        ) VALUES (
            v_order_number, p_customer_id, p_location_id, p_order_type,
            'pending', 'pending', 'unfulfilled'
        );
        
        SET p_order_id = LAST_INSERT_ID();
        
        -- Insert order items
        SET v_idx = 0;
        WHILE v_idx < v_item_count DO
            SET v_variant_id = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].variant_id')));
            SET v_quantity = JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].quantity')));
            
            -- Get pricing (prioritas location_pricing)
            SELECT 
                COALESCE(lp.price, pv.price),
                COALESCE(lp.cost_price, pv.cost_price),
                p.product_name,
                pv.variant_name,
                pv.sku
            INTO v_price, v_cost_price, v_product_name, v_variant_name, v_sku
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN location_pricing lp ON pv.variant_id = lp.variant_id 
                AND lp.location_id = p_location_id
                AND lp.is_active = TRUE
            WHERE pv.variant_id = v_variant_id;
            
            -- Insert order item
            INSERT INTO order_items (
                order_id, variant_id, product_name, variant_name, sku,
                quantity, unit_price, unit_cost, subtotal, total_amount
            ) VALUES (
                p_order_id, v_variant_id, v_product_name, v_variant_name, v_sku,
                v_quantity, v_price, v_cost_price, 
                v_price * v_quantity, v_price * v_quantity
            );
            
            -- Accumulate totals
            SET v_subtotal = v_subtotal + (v_price * v_quantity);
            
            SET v_idx = v_idx + 1;
        END WHILE;
        
        -- Calculate tax (PPN 11%)
        SET v_tax_amount = v_subtotal * 0.11;
        SET v_total_amount = v_subtotal + v_tax_amount;
        
        -- Update order totals
        UPDATE orders
        SET subtotal = v_subtotal,
            tax_amount = v_tax_amount,
            total_amount = v_total_amount
        WHERE order_id = p_order_id;
        
        SET p_status = 'success';
        SET p_message = CONCAT('Order created successfully: ', v_order_number);
        
        COMMIT;
    END IF;
END$

-- Procedure: Confirm Payment & Update Order Status
CREATE PROCEDURE sp_confirm_payment(
    IN p_order_id BIGINT,
    IN p_payment_method VARCHAR(50),
    IN p_transaction_id VARCHAR(255),
    OUT p_status VARCHAR(50),
    OUT p_message TEXT
)
BEGIN
    DECLARE v_order_status VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'error';
        SET p_message = 'Payment confirmation failed.';
    END;
    
    START TRANSACTION;
    
    -- Cek order status
    SELECT order_status INTO v_order_status
    FROM orders WHERE order_id = p_order_id;
    
    IF v_order_status = 'pending' THEN
        -- Insert payment record
        INSERT INTO payments (
            order_id, payment_method, amount, payment_status,
            transaction_id, payment_date
        )
        SELECT 
            order_id, p_payment_method, total_amount, 'success',
            p_transaction_id, NOW()
        FROM orders WHERE order_id = p_order_id;
        
        -- Update order status
        UPDATE orders
        SET order_status = 'confirmed',
            payment_status = 'paid',
            confirmed_at = NOW()
        WHERE order_id = p_order_id;
        
        -- Update customer stats
        UPDATE customers c
        JOIN orders o ON c.customer_id = o.customer_id
        SET c.total_orders = c.total_orders + 1,
            c.total_spent = c.total_spent + o.total_amount
        WHERE o.order_id = p_order_id;
        
        SET p_status = 'success';
        SET p_message = 'Payment confirmed successfully';
        
        COMMIT;
    ELSE
        SET p_status = 'error';
        SET p_message = CONCAT('Order status is not pending: ', v_order_status);
        ROLLBACK;
    END IF;
END$

-- Procedure: Cancel Order & Release Stock
CREATE PROCEDURE sp_cancel_order(
    IN p_order_id BIGINT,
    IN p_reason TEXT,
    OUT p_status VARCHAR(50),
    OUT p_message TEXT
)
BEGIN
    DECLARE v_order_status VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'error';
        SET p_message = 'Order cancellation failed.';
    END;
    
    START TRANSACTION;
    
    SELECT order_status INTO v_order_status
    FROM orders WHERE order_id = p_order_id;
    
    IF v_order_status IN ('pending', 'confirmed') THEN
        -- Update order status (trigger akan handle stock release)
        UPDATE orders
        SET order_status = 'cancelled',
            cancelled_at = NOW(),
            internal_notes = CONCAT(COALESCE(internal_notes, ''), '\nCancellation reason: ', p_reason)
        WHERE order_id = p_order_id;
        
        SET p_status = 'success';
        SET p_message = 'Order cancelled successfully';
        
        COMMIT;
    ELSE
        SET p_status = 'error';
        SET p_message = CONCAT('Cannot cancel order with status: ', v_order_status);
        ROLLBACK;
    END IF;
END$

-- Procedure: Transfer Stock Between Locations
CREATE PROCEDURE sp_transfer_stock(
    IN p_variant_id BIGINT,
    IN p_from_location_id INT,
    IN p_to_location_id INT,
    IN p_quantity INT,
    IN p_reason VARCHAR(255),
    OUT p_status VARCHAR(50),
    OUT p_message TEXT
)
BEGIN
    DECLARE v_available_stock INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'error';
        SET p_message = 'Stock transfer failed.';
    END;
    
    START TRANSACTION;
    
    -- Cek stock di from_location
    SELECT quantity_available INTO v_available_stock
    FROM inventory
    WHERE variant_id = p_variant_id AND location_id = p_from_location_id;
    
    IF v_available_stock >= p_quantity THEN
        -- Kurangi stock dari location asal
        UPDATE inventory
        SET quantity_available = quantity_available - p_quantity,
            updated_at = NOW()
        WHERE variant_id = p_variant_id AND location_id = p_from_location_id;
        
        -- Tambah stock ke location tujuan
        INSERT INTO inventory (variant_id, location_id, quantity_available)
        VALUES (p_variant_id, p_to_location_id, p_quantity)
        ON DUPLICATE KEY UPDATE 
            quantity_available = quantity_available + p_quantity,
            updated_at = NOW();
        
        -- Log movement FROM
        INSERT INTO inventory_movements (
            variant_id, location_id, movement_type, quantity,
            quantity_before, quantity_after,
            reference_type, from_location_id, to_location_id,
            reason, created_by_name
        ) VALUES (
            p_variant_id, p_from_location_id, 'transfer', p_quantity,
            v_available_stock, v_available_stock - p_quantity,
            'transfer_out', p_from_location_id, p_to_location_id,
            p_reason, 'system'
        );
        
        -- Log movement TO
        INSERT INTO inventory_movements (
            variant_id, location_id, movement_type, quantity,
            quantity_before, quantity_after,
            reference_type, from_location_id, to_location_id,
            reason, created_by_name
        )
        SELECT 
            p_variant_id, p_to_location_id, 'transfer', p_quantity,
            quantity_available - p_quantity, quantity_available,
            'transfer_in', p_from_location_id, p_to_location_id,
            p_reason, 'system'
        FROM inventory
        WHERE variant_id = p_variant_id AND location_id = p_to_location_id;
        
        SET p_status = 'success';
        SET p_message = CONCAT('Transferred ', p_quantity, ' units successfully');
        
        COMMIT;
    ELSE
        SET p_status = 'error';
        SET p_message = CONCAT('Insufficient stock. Available: ', v_available_stock);
        ROLLBACK;
    END IF;
END$

DELIMITER ;

-- ============================================
-- SAMPLE DATA
-- ============================================

-- Insert Brands
INSERT INTO brands (brand_name, slug, is_active) VALUES
('Nike', 'nike', TRUE),
('Adidas', 'adidas', TRUE),
('Dell', 'dell', TRUE),
('Apple', 'apple', TRUE);

-- Insert Categories
INSERT INTO categories (category_name, slug, parent_category_id, level) VALUES
('Electronics', 'electronics', NULL, 0),
('Laptops', 'laptops', 1, 1),
('Apparel', 'apparel', NULL, 0),
('T-Shirts', 'tshirts', 3, 1),
('Shoes', 'shoes', 3, 1);

-- Insert Locations
INSERT INTO locations (location_name, location_code, location_type, city, can_fulfill_online_orders, can_pickup, priority) VALUES
('Store Jakarta Pusat', 'JKT-01', 'store', 'Jakarta', TRUE, TRUE, 1),
('Store Surabaya', 'SBY-01', 'store', 'Surabaya', TRUE, TRUE, 2),
('Store Bandung', 'BDG-01', 'store', 'Bandung', TRUE, TRUE, 3),
('Warehouse Jakarta', 'WH-JKT', 'warehouse', 'Jakarta', TRUE, FALSE, 10);

-- Insert Sample Customer
INSERT INTO customers (email, phone, first_name, last_name, customer_type, email_verified) VALUES
('john.doe@email.com', '081234567890', 'John', 'Doe', 'retail', TRUE),
('jane.smith@email.com', '081234567891', 'Jane', 'Smith', 'retail', TRUE),
('wholesale@company.com', '081234567892', 'Wholesale', 'Customer', 'wholesale', TRUE);