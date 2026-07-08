<?php
/**
 * Sudamaseva Module — Phase 1: Create Tables
 * 
 * Creates the 4 core tables for the Sudamaseva subscription donation system:
 *   1. sudamaseva_donors        — Donor information (migrated from old tbl_users)
 *   2. sudamaseva_subscriptions — Active & historical subscriptions
 *   3. sudamaseva_payments      — Individual payment records (one per installment)
 *   4. sudamaseva_receipts      — Generated receipts for tax/exemption purposes
 * 
 * Run: php modules/Sudamaseva/migrations/001_create_tables.php
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

echo "=== Sudamaseva Module — Create Tables ===\n\n";

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ================================================================
    // TABLE 1: sudamaseva_donors
    // Stores donor information migrated from old tbl_users + new donors
    // ================================================================
    echo "[1/4] Creating sudamaseva_donors table...\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS `sudamaseva_donors` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `uuid` VARCHAR(36) NOT NULL,
            `donor_name` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(15) NOT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `pan` VARCHAR(20) DEFAULT NULL,
            `area` VARCHAR(255) DEFAULT NULL,
            `city` VARCHAR(255) DEFAULT NULL,
            `state` VARCHAR(255) DEFAULT NULL,
            `source` VARCHAR(50) NOT NULL DEFAULT 'sudamaseva',
            `notes` TEXT DEFAULT NULL,
            `status` ENUM('active', 'inactive', 'paused') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_phone` (`phone`),
            KEY `idx_email` (`email`),
            KEY `idx_status` (`status`),
            KEY `idx_source` (`source`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'sudamaseva_donors' created.\n";

    // ================================================================
    // TABLE 2: sudamaseva_subscriptions
    // Tracks active and historical subscriptions for each donor
    // ================================================================
    echo "[2/4] Creating sudamaseva_subscriptions table...\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS `sudamaseva_subscriptions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `donor_id` INT NOT NULL,
            `amount` INT NOT NULL COMMENT 'Monthly amount in INR',
            `razorpay_subscription_id` VARCHAR(255) DEFAULT NULL,
            `razorpay_plan_id` VARCHAR(255) DEFAULT NULL,
            `status` ENUM('active', 'completed', 'paused', 'cancelled') NOT NULL DEFAULT 'active',
            `start_date` DATETIME DEFAULT NULL,
            `end_date` DATETIME DEFAULT NULL,
            `total_installments` INT NOT NULL DEFAULT 0 COMMENT '0 = open-ended; >0 = fixed plan',
            `installments_paid` INT NOT NULL DEFAULT 0,
            `source` ENUM('migrated', 'new') NOT NULL DEFAULT 'new',
            `old_user_id` INT DEFAULT NULL COMMENT 'Reference to old tbl_users.id (migrated only)',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_sub_donor` FOREIGN KEY (`donor_id`) REFERENCES `sudamaseva_donors`(`id`),
            KEY `idx_donor` (`donor_id`),
            KEY `idx_status` (`status`),
            UNIQUE KEY `uq_razorpay_sub` (`razorpay_subscription_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'sudamaseva_subscriptions' created.\n";

    // ================================================================
    // TABLE 3: sudamaseva_payments
    // Individual payment records — one per installment
    // ================================================================
    echo "[3/4] Creating sudamaseva_payments table...\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS `sudamaseva_payments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `subscription_id` INT DEFAULT NULL COMMENT 'NULL for orphan/migrated payments',
            `donor_id` INT DEFAULT NULL COMMENT 'Denormalized; nullable for orphan payments',
            `amount` INT NOT NULL COMMENT 'Amount paid in INR',
            `installment_number` INT NOT NULL DEFAULT 0 COMMENT '0 = orphan/unlinked payment',
            `razorpay_payment_id` VARCHAR(255) DEFAULT NULL,
            `razorpay_order_id` VARCHAR(255) DEFAULT NULL,
            `razorpay_signature` VARCHAR(255) DEFAULT NULL,
            `payment_status` ENUM('created', 'attempted', 'paid', 'failed') NOT NULL DEFAULT 'created',
            `payment_date` DATETIME DEFAULT NULL,
            `receipt_number` VARCHAR(50) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `is_migrated` TINYINT(1) NOT NULL DEFAULT 0,
            `old_ins_pay_id` INT DEFAULT NULL COMMENT 'Reference to old tbl_rec_ins_pay.id',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_pay_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `sudamaseva_subscriptions`(`id`) ON DELETE SET NULL,
            CONSTRAINT `fk_pay_donor` FOREIGN KEY (`donor_id`) REFERENCES `sudamaseva_donors`(`id`) ON DELETE SET NULL,
            KEY `idx_subscription` (`subscription_id`),
            KEY `idx_donor` (`donor_id`),
            UNIQUE KEY `uq_razorpay_pay` (`razorpay_payment_id`),
            KEY `idx_installment` (`installment_number`),
            KEY `idx_payment_date` (`payment_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'sudamaseva_payments' created.\n";

    // ================================================================
    // TABLE 4: sudamaseva_receipts
    // Generated receipts for tax exemption (80G / Form 10BE)
    // ================================================================
    echo "[4/4] Creating sudamaseva_receipts table...\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS `sudamaseva_receipts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `payment_id` INT NOT NULL,
            `receipt_no` VARCHAR(50) NOT NULL,
            `receipt_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `receipt_data` JSON DEFAULT NULL COMMENT 'Full receipt details (name, amount, date, etc.)',
            `is_80g_eligible` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_rec_payment` FOREIGN KEY (`payment_id`) REFERENCES `sudamaseva_payments`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `uq_receipt_no` (`receipt_no`),
            KEY `idx_payment` (`payment_id`),
            KEY `idx_receipt_date` (`receipt_date`),
            KEY `idx_80g` (`is_80g_eligible`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'sudamaseva_receipts' created.\n";

    // ================================================================
    // SUMMARY
    // ================================================================
    echo "\n=== Migration Complete ===\n\n";
    echo "Created 4 tables:\n";
    echo "  - sudamaseva_donors        — Donor profiles (phone-unique)\n";
    echo "  - sudamaseva_subscriptions — Subscription tracking (FK → donors)\n";
    echo "  - sudamaseva_payments      — Installment payments (FK → subscriptions, donors)\n";
    echo "  - sudamaseva_receipts      — Tax receipts (FK → payments, JSON metadata)\n\n";
    echo "All tables use InnoDB engine with utf8mb4_unicode_ci collation.\n";
    echo "Foreign keys and indexes are set up for performance.\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
