<?php
/**
 * Migration: Add Category Column to Panihati Expenses Table
 * 
 * Run: php database/migrations/add_panihati_expenses_category.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Adding Category Column to Panihati Expenses ===\n\n";

try {
    $db = getDB();

    // 1. Add column if it doesn't exist
    $db->exec("
        ALTER TABLE `panihati_expenses` 
        ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT 'Miscellaneous' 
        AFTER `amount`
    ");
    echo "  [OK] Added 'category' column to 'panihati_expenses' table.\n";
} catch (PDOException $e) {
    // If column already exists, just output info
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "  [INFO] Column 'category' already exists.\n";
    } else {
        echo "  [ERROR] Failed to alter table: " . $e->getMessage() . "\n";
        exit(1);
    }
}

try {
    // 2. Classify existing records into standard categories based on keywords
    $updates = [
        ['Transport', '%Nandish%'],
        ['Transport', '%Bus%'],
        ['Transport', '%VIPIN Pr Self Cheque%'],
        ['Transport', '%Rakhal Pr Self Cheque%'],
        ['Prasadam & Kitchen', '%Kitchen%'],
        ['Prasadam & Kitchen', '%Keshav Pr%'], // Keshav Pr Labour is under Labour, but let's check
        ['Labour & Seva', '%Labour%'],
        ['Venue Bookings', '%Ghat%'],
        ['Venue Bookings', '%Booking%'],
        ['Deity Worship', '%Deity%'],
        ['Deity Worship', '%vipin pr Expenses%']
    ];

    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE `panihati_expenses` SET `category` = ? WHERE `particulars` LIKE ?");
    foreach ($updates as $upd) {
        $stmt->execute($upd);
        $count = $stmt->rowCount();
        if ($count > 0) {
            echo "  - Classified {$count} entries under '{$upd[0]}' matching pattern '{$upd[1]}'.\n";
        }
    }

    $db->commit();
    echo "\n=== Migration Complete ===\n";

} catch (Exception $ex) {
    $db->rollBack();
    echo "  [ERROR] Failed to classify entries: " . $ex->getMessage() . "\n";
    exit(1);
}
