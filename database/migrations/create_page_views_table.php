<?php
/**
 * Migration: Create page_views table for conversion funnel tracking
 */

require_once __DIR__ . '/../../config.php';

$db = getDB();
echo "=== Creating page_views table ===\n";

$db->exec("
    CREATE TABLE IF NOT EXISTS `page_views` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` VARCHAR(64) NOT NULL,
        `page_url` VARCHAR(500) NOT NULL,
        `cause_slug` VARCHAR(150) DEFAULT NULL,
        `referrer` VARCHAR(500) DEFAULT NULL,
        `user_agent` VARCHAR(500) DEFAULT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_pv_cause` (`cause_slug`),
        INDEX `idx_pv_session` (`session_id`),
        INDEX `idx_pv_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "  [OK] page_views table created\n";

// Also create a lightweight page view tracker endpoint
echo "\n=== Done! ===\n";
