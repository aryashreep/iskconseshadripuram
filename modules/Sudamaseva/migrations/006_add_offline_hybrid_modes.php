<?php
/**
 * Sudamaseva Module — Phase 6: Add Offline & Hybrid Payment Modes
 *
 * Extends collection_mode ENUM to support:
 *   - 'offline' — Pay Monthly via bank/cash (admin records payments)
 *   - 'hybrid'  — Pay Monthly online (Pay Now) OR offline (admin records)
 *
 * Run: php modules/Sudamaseva/migrations/006_add_offline_hybrid_modes.php
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

echo "=== Sudamaseva Module — Phase 6: Offline & Hybrid Modes ===\n\n";

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check current ENUM values
    $colInfo = $db->query("SHOW COLUMNS FROM sudamaseva_subscriptions LIKE 'collection_mode'")->fetch();
    $currentType = $colInfo['Type'] ?? '';

    echo "[1/1] Extending collection_mode ENUM...\n";
    echo "  Current: {$currentType}\n";

    if (strpos($currentType, "'offline'") === false || strpos($currentType, "'hybrid'") === false) {
        $db->exec("
            ALTER TABLE `sudamaseva_subscriptions`
            MODIFY COLUMN `collection_mode`
                ENUM('recurring', 'manual', 'offline', 'hybrid')
                NOT NULL DEFAULT 'recurring'
        ");
        echo "  [OK] ENUM extended: 'recurring', 'manual', 'offline', 'hybrid'\n";
    } else {
        echo "  [SKIP] 'offline' and 'hybrid' already in ENUM.\n";
    }

    echo "\n=== Migration Complete ===\n\n";
    echo "New payment modes available:\n";
    echo "  - recurring  → Auto Monthly (Online via Razorpay subscription)\n";
    echo "  - manual     → Pay Monthly (Online via Razorpay order)\n";
    echo "  - offline    → Pay Monthly (Offline via bank/cash, admin records)\n";
    echo "  - hybrid     → Pay Monthly (Online via Razorpay OR offline)\n\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
