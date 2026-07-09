<?php
/**
 * Sudamaseva Module — Phase 4: Manual Payment Support
 *
 * Adds columns needed to support manual/monthly payments alongside recurring subscriptions:
 *   1. sudamaseva_donors.legacy_id_no       — Old ID from legacy system for lookup continuity
 *   2. sudamaseva_subscriptions.collection_mode — 'recurring' (Razorpay sub) or 'manual' (pay per month)
 *   3. sudamaseva_payments.payment_source      — Origin of the payment record
 *
 * Run: php modules/Sudamaseva/migrations/004_add_manual_payment_fields.php
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

echo "=== Sudamaseva Module — Phase 4: Manual Payment Support ===\n\n";

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ================================================================
    // 1. Add legacy_id_no to sudamaseva_donors
    // ================================================================
    echo "[1/3] Adding legacy_id_no to sudamaseva_donors...\n";

    $checkLegacy = $db->query("SHOW COLUMNS FROM sudamaseva_donors LIKE 'legacy_id_no'");
    if ($checkLegacy->rowCount() === 0) {
        $db->exec("
            ALTER TABLE `sudamaseva_donors`
            ADD COLUMN `legacy_id_no` VARCHAR(50) DEFAULT NULL AFTER `uuid`,
            ADD KEY `idx_legacy_id` (`legacy_id_no`)
        ");
        echo "  [OK] Column 'legacy_id_no' added.\n";
    } else {
        echo "  [SKIP] Column 'legacy_id_no' already exists.\n";
    }

    // ================================================================
    // 2. Add collection_mode to sudamaseva_subscriptions
    // ================================================================
    echo "[2/3] Adding collection_mode to sudamaseva_subscriptions...\n";

    $checkMode = $db->query("SHOW COLUMNS FROM sudamaseva_subscriptions LIKE 'collection_mode'");
    if ($checkMode->rowCount() === 0) {
        $db->exec("
            ALTER TABLE `sudamaseva_subscriptions`
            ADD COLUMN `collection_mode` ENUM('recurring', 'manual') NOT NULL DEFAULT 'recurring'
                AFTER `total_installments`,
            ADD KEY `idx_collection_mode` (`collection_mode`)
        ");
        echo "  [OK] Column 'collection_mode' added.\n";
    } else {
        echo "  [SKIP] Column 'collection_mode' already exists.\n";
    }

    // ================================================================
    // 3. Add payment_source to sudamaseva_payments
    // ================================================================
    echo "[3/3] Adding payment_source to sudamaseva_payments...\n";

    $checkSource = $db->query("SHOW COLUMNS FROM sudamaseva_payments LIKE 'payment_source'");
    if ($checkSource->rowCount() === 0) {
        $db->exec("
            ALTER TABLE `sudamaseva_payments`
            ADD COLUMN `payment_source` ENUM('subscription_charge', 'manual_order', 'migrated', 'admin_manual')
                NOT NULL DEFAULT 'subscription_charge'
                AFTER `payment_date`,
            ADD COLUMN `billing_month` DATE DEFAULT NULL
                AFTER `payment_source`,
            ADD KEY `idx_payment_source` (`payment_source`),
            ADD KEY `idx_billing_month` (`billing_month`)
        ");
        echo "  [OK] Columns 'payment_source' and 'billing_month' added.\n";
    } else {
        echo "  [SKIP] Columns 'payment_source' / 'billing_month' already exist.\n";
    }

    // ================================================================
    // SUMMARY
    // ================================================================
    echo "\n=== Migration Complete ===\n\n";
    echo "Added/verified:\n";
    echo "  - sudamaseva_donors.legacy_id_no        — Legacy ID for lookup\n";
    echo "  - sudamaseva_subscriptions.collection_mode — recurring | manual\n";
    echo "  - sudamaseva_payments.payment_source       — Origin of payment\n";
    echo "  - sudamaseva_payments.billing_month        — Billing period reference\n\n";
    echo "All existing data preserved. Existing rows use safe defaults.\n";
    echo "Run a separate backfill script to populate legacy_id_no from old system if needed.\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
