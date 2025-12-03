-- ================================================
-- Fiuu Payment System Database Schema
-- ================================================

-- Table: orders
-- Stores customer orders before payment
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Unique order ID sent to Fiuu',
  `user_id` INT(11) UNSIGNED NULL COMMENT 'Foreign key to users table',
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `name` VARCHAR(255) NOT NULL,
  `plan_name` VARCHAR(100) NOT NULL COMMENT 'Subscription plan name',
  `amount` DECIMAL(10,2) NOT NULL COMMENT 'Payment amount',
  `currency` VARCHAR(3) NOT NULL DEFAULT 'MYR',
  `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
  
  -- Fiuu response data (populated by webhooks)
  `fiuu_tran_id` VARCHAR(100) NULL COMMENT 'Fiuu transaction ID',
  `fiuu_appcode` VARCHAR(50) NULL COMMENT 'Approval code from Fiuu',
  `fiuu_channel` VARCHAR(50) NULL COMMENT 'Payment channel (FPX, card, etc.)',
  `fiuu_paydate` VARCHAR(50) NULL COMMENT 'Payment date from Fiuu',
  `fiuu_status_code` VARCHAR(10) NULL COMMENT 'Fiuu status code (00, 11, 22, 33)',
  
  -- Timestamps
  `payment_completed_at` DATETIME NULL,
  `cancelled_at` DATETIME NULL,
  `callback_received_at` DATETIME NULL,
  
  -- Error tracking
  `error_code` VARCHAR(50) NULL,
  `error_message` TEXT NULL,
  
  -- Metadata
  `metadata` TEXT NULL COMMENT 'JSON field for additional data',
  
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  KEY `idx_order_id` (`order_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Customer orders and payment records';

-- Table: payment_transactions
-- Logs every webhook/callback received from Fiuu
CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` VARCHAR(100) NOT NULL COMMENT 'References orders.order_id',
  `fiuu_tran_id` VARCHAR(100) NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'MYR',
  `status` VARCHAR(10) NOT NULL COMMENT 'Fiuu status code (00, 11, etc.)',
  `system_status` VARCHAR(50) NOT NULL COMMENT 'Our mapped status',
  `channel` VARCHAR(50) NULL,
  `appcode` VARCHAR(50) NULL,
  `paydate` VARCHAR(50) NULL,
  `error_code` VARCHAR(50) NULL,
  `error_message` TEXT NULL,
  `nbcb` VARCHAR(20) NULL COMMENT 'Callback number or type',
  `ip_address` VARCHAR(45) NULL,
  `raw_response` TEXT NULL COMMENT 'Full JSON response from Fiuu',
  
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  KEY `idx_order_id` (`order_id`),
  KEY `idx_fiuu_tran_id` (`fiuu_tran_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Transaction log for all Fiuu webhooks';

-- Table: payment_logs
-- Activity log for payment events
CREATE TABLE IF NOT EXISTS `payment_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` VARCHAR(100) NULL,
  `event_type` VARCHAR(50) NOT NULL COMMENT 'e.g., order_created, webhook_received, etc.',
  `level` ENUM('info', 'warning', 'error') NOT NULL DEFAULT 'info',
  `message` TEXT NOT NULL,
  `context` TEXT NULL COMMENT 'JSON context data',
  `ip_address` VARCHAR(45) NULL,
  
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  KEY `idx_order_id` (`order_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_level` (`level`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Activity log for payment system';

-- ================================================
-- Sample Data (for testing)
-- ================================================

-- Insert a test order
INSERT INTO `orders` (
  `order_id`, 
  `email`, 
  `phone`, 
  `name`, 
  `plan_name`, 
  `amount`, 
  `currency`, 
  `status`
) VALUES (
  'ORD-TEST-001',
  'test@example.com',
  '+60123456789',
  'Test User',
  'Premium Plan',
  99.00,
  'MYR',
  'pending'
);

-- ================================================
-- Indexes for Performance
-- ================================================

-- Add composite indexes for common queries
ALTER TABLE `orders` ADD INDEX `idx_status_created` (`status`, `created_at`);
ALTER TABLE `payment_transactions` ADD INDEX `idx_order_status` (`order_id`, `status`);
ALTER TABLE `payment_logs` ADD INDEX `idx_order_created` (`order_id`, `created_at`);

-- ================================================
-- Views (Optional - for reporting)
-- ================================================

-- View: Recent successful payments
CREATE OR REPLACE VIEW `v_successful_payments` AS
SELECT 
    o.order_id,
    o.name,
    o.email,
    o.plan_name,
    o.amount,
    o.currency,
    o.fiuu_channel,
    o.payment_completed_at,
    o.created_at
FROM orders o
WHERE o.status = 'completed'
ORDER BY o.payment_completed_at DESC;

-- View: Pending payments
CREATE OR REPLACE VIEW `v_pending_payments` AS
SELECT 
    o.order_id,
    o.name,
    o.email,
    o.plan_name,
    o.amount,
    o.fiuu_channel,
    TIMESTAMPDIFF(HOUR, o.created_at, NOW()) as hours_pending,
    o.created_at
FROM orders o
WHERE o.status IN ('pending', 'processing')
ORDER BY o.created_at ASC;

-- ================================================
-- Notes
-- ================================================
-- 1. Run this SQL on your MySQL database (nfc_business_card)
-- 2. Tables use utf8mb4 for emoji and international character support
-- 3. Timestamps are in server timezone (set in fiuu-config.php)
-- 4. Foreign key to users table is optional - set if you have users table
-- 5. Views are optional but useful for reporting
