<?php
/**
 * Migration: Create Panihati Yatra Expenses Table
 *
 * Stores manual expenses and manual income entries for the yatra.
 *
 * Run: php database/migrations/create_panihati_expenses.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Panihati Expenses Table Migration ===\n\n";

try {
    $db = getDB();

    // 1. Create table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `panihati_expenses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `type` ENUM('expense', 'income') NOT NULL DEFAULT 'expense',
            `particulars` VARCHAR(255) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `expense_date` DATE NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'panihati_expenses' created successfully.\n";

    // 2. Seed initial data from panihati_expences.jpeg for 2026
    $seededEntries = [
        // Expenses
        ['expense', 'VIPIN Pr Self Cheque Issued from Axis 8445', 50000.00, '2026-06-18'],
        ['expense', 'VIPIN Pr Self Cheque Issued from Axis 8445', 50000.00, '2026-06-18'],
        ['expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 60000.00, '2026-06-18'],
        ['expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 30000.00, '2026-06-18'],
        ['expense', 'Kitchen ICICI Bank Issued from Axis 8445', 200000.00, '2026-06-18'],
        ['expense', 'Nandish Bus transferred from Axis 8445', 50000.00, '2026-06-18'],
        ['expense', 'Mastaiah Gosai Ghat Booking transferred from Axis 8445', 25000.00, '2026-06-18'],
        ['expense', 'Nandish Bus cheque written from Axis 8445', 338500.00, '2026-06-18'],
        ['expense', 'Cash paid to Nandish Bus', 150000.00, '2026-06-18'],
        ['expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 103150.00, '2026-06-18'],
        ['expense', 'Rakhal Pr Self Cheque Issued from Axis 8445', 32000.00, '2026-06-18'],
        ['expense', 'Rakhal Pr took cash from Giteshwari Mtj on PH site', 35600.00, '2026-06-18'],
        ['expense', 'Keshav Pr Panihati Labour', 13000.00, '2026-06-18'],
        ['expense', 'Vipin pr Expenses for Deity', 750.00, '2026-06-18'],
        // Manual Income (Offline/Other)
        ['income', 'Online Collection', 102401.00, '2026-06-18'],
        ['income', 'BBT Counter Cash', 98100.00, '2026-06-18'],
    ];

    $insertStmt = $db->prepare("
        INSERT INTO `panihati_expenses` (`type`, `particulars`, `amount`, `expense_date`)
        VALUES (?, ?, ?, ?)
    ");

    // Clear existing to avoid duplicate seeds
    $db->exec("TRUNCATE TABLE `panihati_expenses`");

    foreach ($seededEntries as $entry) {
        $insertStmt->execute($entry);
    }
    echo "  [OK] Seeded 14 expenses and 2 manual income entries successfully.\n";

    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
