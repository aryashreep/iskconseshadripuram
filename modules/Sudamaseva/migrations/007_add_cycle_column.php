<?php
/**
 * Sudamaseva Module — Phase 7: Add Cycle/Renewal Tracking
 *
 * Adds a `cycle` column to sudamaseva_subscriptions to track whether
 * a subscription is the donor's 1st (original), 2nd (first renewal), etc.
 *
 * Run: php modules/Sudamaseva/migrations/007_add_cycle_column.php
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

echo "=== Sudamaseva Module — Phase 7: Cycle/Renewal Tracking ===\n\n";

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column already exists
    $check = $db->query("SHOW COLUMNS FROM sudamaseva_subscriptions LIKE 'cycle'");
    if ($check->rowCount() === 0) {
        $db->exec("
            ALTER TABLE `sudamaseva_subscriptions`
            ADD COLUMN `cycle` INT NOT NULL DEFAULT 1 AFTER `source`,
            ADD KEY `idx_cycle` (`cycle`)
        ");
        echo "  [OK] Column 'cycle' added with default 1.\n";

        // Backfill: For each donor, number their subscriptions chronologically
        $db->exec("
            UPDATE sudamaseva_subscriptions s
            JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY donor_id ORDER BY created_at ASC) AS rn
                FROM sudamaseva_subscriptions
            ) seq ON s.id = seq.id
            SET s.cycle = seq.rn
        ");
        echo "  [OK] Existing subscriptions backfilled with cycle numbers.\n";
    } else {
        echo "  [SKIP] Column 'cycle' already exists.\n";
    }

    echo "\n=== Migration Complete ===\n\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
