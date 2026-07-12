<?php
/**
 * Sudamaseva Module — Migration 006: Fix Active Migrated Subscriptions
 * 
 * Sets total_installments to 24 (or actual if >24) and restores status to 'active'
 * for all migrated subscriptions that were active in the old system but got capped.
 * Also sets collection_mode = 'hybrid' so donors can pay online or offline.
 * 
 * Run: php modules/Sudamaseva/migrations/006_fix_active_migrated_subscriptions.php
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

$oldDbHost = !empty($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost';
$oldDbName = 'iskcosf7_sudamasava_prod';
$oldDbUser = 'root';
$oldDbPass = '';

echo "=== Sudamaseva — Reactivating Capped Migrated Subscriptions ===\n\n";

try {
    $oldDb = new PDO("mysql:host={$oldDbHost};dbname={$oldDbName};charset=utf8mb4", $oldDbUser, $oldDbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $newDb = getDB();

    // 1. Globally set collection_mode = 'hybrid' for all migrated subscriptions
    echo "Updating collection_mode to 'hybrid' for all migrated subscriptions...\n";
    $countHybrid = $newDb->exec("UPDATE sudamaseva_subscriptions SET collection_mode = 'hybrid' WHERE source = 'migrated'");
    echo "  [OK] Updated {$countHybrid} subscriptions to 'hybrid' mode.\n\n";

    // 2. Select all migrated subscriptions to reactivate capped ones
    $stmt = $newDb->query("
        SELECT s.*, d.phone 
        FROM sudamaseva_subscriptions s
        JOIN sudamaseva_donors d ON s.donor_id = d.id
        WHERE s.source = 'migrated'
    ");
    $newSubs = $stmt->fetchAll();

    $reactivatedCount = 0;
    $updatedCompletedCount = 0;
    $skippedOrphansCount = 0;

    $updateStmt = $newDb->prepare("
        UPDATE sudamaseva_subscriptions 
        SET total_installments = ?, status = ?, end_date = ? 
        WHERE id = ?
    ");

    foreach ($newSubs as $sub) {
        // Skip orphan/dummy accounts
        if (str_starts_with($sub['phone'] ?? '', 'orphan-')) {
            $skippedOrphansCount++;
            continue;
        }

        $oldUserId = $sub['old_user_id'];
        if (!$oldUserId) continue;

        $oldStmt = $oldDb->prepare("SELECT status FROM tbl_users WHERE id = ?");
        $oldStmt->execute([$oldUserId]);
        $oldUser = $oldStmt->fetch();

        if ($oldUser && (int)$oldUser['status'] === 1) {
            $subId = (int)$sub['id'];
            $paid = (int)$sub['installments_paid'];

            if ($paid < 24) {
                // Reactivate: set total_installments to 24 and status to active
                $updateStmt->execute([24, 'active', null, $subId]);
                $reactivatedCount++;
                echo "  Reactivated Sub #{$subId} (Donor #{$sub['donor_id']}): Capped at {$paid} -> reset to Active with 24 installments.\n";
            } else {
                // Keep completed but ensure total_installments is correct
                $updateStmt->execute([$paid, 'completed', $sub['end_date'] ?: date('Y-m-d H:i:s'), $subId]);
                $updatedCompletedCount++;
            }
        }
    }

    echo "\nMigration Complete!\n";
    echo "  - Reactivated subscriptions (paid < 24): {$reactivatedCount}\n";
    echo "  - Capped completed subscriptions (paid >= 24): {$updatedCompletedCount}\n";
    echo "  - Skipped orphan/dummy subscriptions: {$skippedOrphansCount}\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
