<?php
/**
 * Update Bookings Status Migration Script
 * Adds a status column to the booking_pujas table for Pujari tracking.
 * 
 * Run: php database/migrations/update_bookings_status.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Booking Status Migration ===\n\n";

try {
    $db = getDB();
    
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM `booking_pujas` LIKE 'status'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE `booking_pujas` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'Pending'");
        echo "  [OK] Added 'status' column to booking_pujas table.\n";
    } else {
        echo "  [INFO] 'status' column already exists in booking_pujas table. Migration skipped.\n";
    }
    
    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
