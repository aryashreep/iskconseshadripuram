<?php
/**
 * Migration: Create Panihati Yatra Pricing Table
 *
 * Stores yearly pricing for bus/vehicle adults and kids.
 * Enables admins to change prices per year without touching code.
 *
 * Run: php database/migrations/create_panihati_pricing_table.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Panihati Pricing Table Migration ===\n\n";

try {
    $db = getDB();

    // 1. Create pricing table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `panihati_pricing` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `year` INT NOT NULL UNIQUE,
            `bus_adult_price` DECIMAL(10,2) NOT NULL DEFAULT 1000.00,
            `bus_kid_price` DECIMAL(10,2) NOT NULL DEFAULT 600.00,
            `vehicle_adult_price` DECIMAL(10,2) NOT NULL DEFAULT 600.00,
            `vehicle_kid_price` DECIMAL(10,2) NOT NULL DEFAULT 600.00,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'panihati_pricing' created successfully.\n";

    // 2. Seed default data (2025 and 2026)
    $stmt = $db->prepare("
        INSERT INTO `panihati_pricing` (`year`, `bus_adult_price`, `bus_kid_price`, `vehicle_adult_price`, `vehicle_kid_price`)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            `bus_adult_price` = VALUES(`bus_adult_price`),
            `bus_kid_price` = VALUES(`bus_kid_price`),
            `vehicle_adult_price` = VALUES(`vehicle_adult_price`),
            `vehicle_kid_price` = VALUES(`vehicle_kid_price`)
    ");

    // Seed 2025
    $stmt->execute([2025, 1000, 600, 600, 600]);
    echo "  [OK] Seeded 2025 pricing (Bus: ₹1000/₹600, Vehicle: ₹600/₹600)\n";

    // Seed 2026
    $stmt->execute([2026, 1000, 600, 600, 600]);
    echo "  [OK] Seeded 2026 pricing (Bus: ₹1000/₹600, Vehicle: ₹600/₹600)\n";

    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
