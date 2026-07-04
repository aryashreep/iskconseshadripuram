-- ============================================
-- ISKCON Booking System - Schema Extensions
-- ============================================

-- 1. BOOKING PUJAS TABLE
CREATE TABLE IF NOT EXISTS `booking_pujas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT DEFAULT NULL,
    `puja_type` VARCHAR(100) NOT NULL,
    `puja_date` DATE NOT NULL,
    `occasion` VARCHAR(150) DEFAULT NULL,
    `person_name` VARCHAR(150) NOT NULL,
    `gotra` VARCHAR(100) DEFAULT NULL,
    `rashi` VARCHAR(100) DEFAULT NULL,
    `nakshatra` VARCHAR(100) DEFAULT NULL,
    `special_instructions` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_booking_pujas_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `donation_transactions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
