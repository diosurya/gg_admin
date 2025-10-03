-- ============================================
-- FIXED STORED PROCEDURES
-- Copy semua dan paste di phpMyAdmin SQL tab
-- ============================================

-- Drop existing procedures jika ada
DROP PROCEDURE IF EXISTS sp_create_order;
DROP PROCEDURE IF EXISTS sp_confirm_payment;
DROP PROCEDURE IF EXISTS sp_cancel_order;
DROP PROCEDURE IF EXISTS sp_transfer_stock;

-- ============================================
-- 1. CREATE ORDER (dengan Auto Stock Reserve)
-- ============================================

DELIMITER $$

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
    DECLARE v_error_flag INT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'error';
        SET p_message = 'Failed to create order. Transaction rolled back.';
    END;
    
    -- Initialize status
    SET p_status = 'pending';
    
    START TRANSACTION;
    
    -- Generate order number
    SET v_order_number = CONCAT('ORD-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 10000), 4, '0'));
    
    -- Hitung jumlah item
    SET v_item_count = JSON_LENGTH(p_items);
    
    -- Validasi semua items sebelum create order
    SET v_idx = 0;
    validation_loop: WHILE v_idx < v_item_count DO
        SET v_variant_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].variant_id'))) AS UNSIGNED);
        SET v_quantity = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].quantity'))) AS UNSIGNED);
        
        -- Cek stock availability
        SELECT COALESCE(quantity_sellable, 0) INTO v_stock_available
        FROM inventory
        WHERE variant_id = v_variant_id AND location_id = p_location_id;
        
        IF v_stock_available < v_quantity THEN
            SET p_status = 'error';
            SET p_message = CONCAT('Insufficient stock for variant_id: ', v_variant_id, '. Available: ', v_stock_available);
            SET v_error_flag = 1;
            LEAVE validation_loop;
        END IF;
        
        SET v_idx = v_idx + 1;
    END WHILE;
    
    -- Jika validasi OK, create order
    IF v_error_flag = 0 THEN
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
        insert_loop: WHILE v_idx < v_item_count DO
            SET v_variant_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].variant_id'))) AS UNSIGNED);
            SET v_quantity = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', v_idx, '].quantity'))) AS UNSIGNED);
            
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
        END WHILE insert_loop;
        
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
    ELSE
        ROLLBACK;
    END IF;
END$$

-- ============================================
-- 2. CONFIRM PAYMENT
-- ============================================

CREATE PROCEDURE sp_confirm_payment(
    IN p_order_id BIGINT,
    IN p_payment_method VARCHAR(50),
    IN p_transaction_id VARCHAR(255),
    OUT p_status VARCHAR(50),
    OUT p_message TEXT
)
BEGIN
    DECLARE v_order_status VARCHAR(20);
    DECLARE v_total_amount DECIMAL(15,2);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'error';
        SET p_message = 'Payment confirmation failed.';
    END;
    
    START TRANSACTION;
    
    -- Cek order status
    SELECT order_status, total_amount 
    INTO v_order_status, v_total_amount
    FROM orders WHERE order_id = p_order_id;
    
    IF v_order_status = 'pending' THEN
        -- Insert payment record
        INSERT INTO payments (
            order_id, payment_method, amount, payment_status,
            transaction_id, payment_date
        ) VALUES (
            p_order_id, p_payment_method, v_total_amount, 'success',
            p_transaction_id, NOW()
        );
        
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
END$$

-- ============================================
-- 3. CANCEL ORDER
-- ============================================

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
END$$

-- ============================================
-- 4. TRANSFER STOCK
-- ============================================

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
    DECLARE v_qty_before_from INT;
    DECLARE v_qty_before_to INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_status = 'error';
        SET p_message = 'Stock transfer failed.';
    END;
    
    START TRANSACTION;
    
    -- Cek stock di from_location
    SELECT COALESCE(quantity_available, 0) INTO v_available_stock
    FROM inventory
    WHERE variant_id = p_variant_id AND location_id = p_from_location_id;
    
    SET v_qty_before_from = v_available_stock;
    
    IF v_available_stock >= p_quantity THEN
        -- Kurangi stock dari location asal
        UPDATE inventory
        SET quantity_available = quantity_available - p_quantity,
            updated_at = NOW()
        WHERE variant_id = p_variant_id AND location_id = p_from_location_id;
        
        -- Cek apakah destination location sudah ada inventory record
        SELECT COALESCE(quantity_available, 0) INTO v_qty_before_to
        FROM inventory
        WHERE variant_id = p_variant_id AND location_id = p_to_location_id;
        
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
            v_qty_before_from, v_qty_before_from - p_quantity,
            'transfer_out', p_from_location_id, p_to_location_id,
            p_reason, 'system'
        );
        
        -- Log movement TO
        INSERT INTO inventory_movements (
            variant_id, location_id, movement_type, quantity,
            quantity_before, quantity_after,
            reference_type, from_location_id, to_location_id,
            reason, created_by_name
        ) VALUES (
            p_variant_id, p_to_location_id, 'transfer', p_quantity,
            v_qty_before_to, v_qty_before_to + p_quantity,
            'transfer_in', p_from_location_id, p_to_location_id,
            p_reason, 'system'
        );
        
        SET p_status = 'success';
        SET p_message = CONCAT('Transferred ', p_quantity, ' units successfully');
        
        COMMIT;
    ELSE
        SET p_status = 'error';
        SET p_message = CONCAT('Insufficient stock. Available: ', v_available_stock);
        ROLLBACK;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- TEST QUERIES (Uncomment untuk test)
-- ============================================

/*
-- Test 1: Create Order
CALL sp_create_order(
    1,  -- customer_id
    1,  -- location_id
    'online',
    '[{"variant_id": 101, "quantity": 2}]',
    @order_id,
    @status,
    @message
);

SELECT @order_id AS order_id, @status AS status, @message AS message;

-- Test 2: Confirm Payment
CALL sp_confirm_payment(
    @order_id,
    'credit_card',
    'TRX-123456',
    @status,
    @message
);

SELECT @status, @message;

-- Test 3: Cancel Order
CALL sp_cancel_order(
    @order_id,
    'Customer request',
    @status,
    @message
);

SELECT @status, @message;

-- Test 4: Transfer Stock
CALL sp_transfer_stock(
    101,  -- variant_id
    4,    -- from: Warehouse
    1,    -- to: Store Jakarta
    50,   -- quantity
    'Stock replenishment',
    @status,
    @message
);

SELECT @status, @message;
*/