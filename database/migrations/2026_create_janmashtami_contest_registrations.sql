-- ============================================
-- Janmashtami Contest Registration Table
-- Stores registrations with payment tracking
-- ============================================

CREATE TABLE IF NOT EXISTS `janmashtami_contest_registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `participant_name` VARCHAR(150) NOT NULL,
    `age_group` VARCHAR(20) NOT NULL COMMENT 'group1, group2, group3',
    `participant_type` VARCHAR(20) NOT NULL COMMENT 'online, offline',
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `contest_slug` VARCHAR(100) NOT NULL COMMENT 'e.g., dress-to-be-blessed, shlokanjali',
    `contest_name` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 108.00,
    `payment_status` ENUM('created','attempted','paid','failed') NOT NULL DEFAULT 'created',
    `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_signature` VARCHAR(255) DEFAULT NULL,
    `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`payment_status`),
    INDEX `idx_contest` (`contest_slug`),
    INDEX `idx_phone` (`phone`),
    INDEX `idx_order` (`razorpay_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
