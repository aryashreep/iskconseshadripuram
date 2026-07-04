<?php
/**
 * Migration: Create Panihati Yatra Offline Aggregate Entries Table & Combined View
 *
 * This table stores aggregate/summary offline data where management has
 * headcounts per sadan (e.g., "50 adults + 20 kids by bus from HSR Layout")
 * without individual name/phone/email for each person.
 *
 * Run: php database/migrations/create_panihati_offline_aggregates.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Panihati Offline Aggregates Migration ===\n\n";

try {
    $db = getDB();

    // 1. Create offline aggregates table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `panihati_yatra_offline_aggregates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `bhakti_sadan` VARCHAR(100) NOT NULL,
            `travel_mode` ENUM('bus', 'own_vehicle') NOT NULL,
            `adults_count` INT NOT NULL DEFAULT 0,
            `kids_count` INT NOT NULL DEFAULT 0,
            `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `pickup_location` VARCHAR(100) DEFAULT NULL,
            `reported_year` INT NOT NULL,
            `source_label` VARCHAR(100) DEFAULT NULL COMMENT 'e.g. paper register, phone report, manual count',
            `notes` TEXT DEFAULT NULL,
            `created_by_admin_id` INT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'panihati_yatra_offline_aggregates' created successfully.\n";

    // 2. Create a combined reporting view for easier dashboard queries
    $db->exec("
        CREATE OR REPLACE VIEW `panihati_yatra_combined_stats` AS
        SELECT
            'individual' AS entry_type,
            id AS source_id,
            name,
            phone,
            email,
            travel_mode,
            adults_count,
            kids_count,
            bhakti_sadan,
            COALESCE(pickup_location, '') AS pickup_location,
            amount,
            payment_status,
            is_offline,
            YEAR(created_at) AS report_year,
            created_at AS report_date
        FROM panihati_yatra_registrations
        WHERE payment_status IN ('paid', 'offline')

        UNION ALL

        SELECT
            'aggregate' AS entry_type,
            id AS source_id,
            '' AS name,
            '' AS phone,
            '' AS email,
            travel_mode,
            adults_count,
            kids_count,
            bhakti_sadan,
            COALESCE(pickup_location, '') AS pickup_location,
            amount,
            'offline' AS payment_status,
            1 AS is_offline,
            reported_year,
            STR_TO_DATE(CONCAT(reported_year, '-01-01'), '%Y-%m-%d') AS report_date
        FROM panihati_yatra_offline_aggregates
    ");
    echo "  [OK] View 'panihati_yatra_combined_stats' created successfully.\n";

    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
