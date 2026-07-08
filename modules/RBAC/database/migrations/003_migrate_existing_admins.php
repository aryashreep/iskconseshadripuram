<?php
/**
 * Migration 003: Migrate Existing Admins to RBAC
 * 
 * Reads each admin's current comma-separated role column and maps
 * old roles to new RBAC roles, inserting into rbac_user_roles.
 * 
 * Safe to run multiple times (uses INSERT IGNORE for UNIQUE constraint).
 * 
 * Run: php modules/RBAC/database/migrations/003_migrate_existing_admins.php
 */

chdir(__DIR__ . '/../../../..');
require_once 'config.php';

echo "=== RBAC Migration 003: Migrate Existing Admins ===\n\n";

try {
    $db = getDB();

    // ==========================================
    // 1. Fetch all existing admins
    // ==========================================
    $admins = $db->query("SELECT id, username, role FROM admins ORDER BY id ASC")->fetchAll();

    if (empty($admins)) {
        echo "  [INFO] No admins found to migrate.\n";
        echo "\n=== Migration 003 Complete ===\n";
        exit(0);
    }

    echo "  Found " . count($admins) . " existing admin(s) to process.\n\n";

    // ==========================================
    // 2. Define old-to-new role mapping
    // ==========================================
    $roleMapping = [
        'super_admin'   => ['super_admin'],
        'editor'        => ['content_manager'],
        'pujari'        => ['temple_admin'],     // Pujari gets temple_admin (bookings focus)
        'treasurer'     => ['accounts'],
        'travel_agent'  => ['event_coordinator'],
        'sudamaseva'    => ['donation_manager'],
    ];

    // Get RBAC role IDs by slug
    // FETCH_KEY_PAIR returns [first_column => second_column], so we need slug => id
    $rbacRoles = $db->query("SELECT slug, id FROM rbac_roles")->fetchAll(\PDO::FETCH_KEY_PAIR);

    $insertUr = $db->prepare("
        INSERT IGNORE INTO rbac_user_roles (admin_id, role_id, assigned_by)
        VALUES (?, ?, ?)
    ");

    $migrated = 0;
    $errors = [];

    foreach ($admins as $admin) {
        $adminId = (int) $admin['id'];
        $oldRoles = array_map('trim', explode(',', $admin['role'] ?? ''));
        $newRoleSlugs = [];

        foreach ($oldRoles as $oldRole) {
            if (isset($roleMapping[$oldRole])) {
                $newRoleSlugs = array_merge($newRoleSlugs, $roleMapping[$oldRole]);
            } else {
                echo "  [WARN] Unknown old role '{$oldRole}' for admin #{$adminId} ({$admin['username']}). Skipping.\n";
            }
        }

        // Deduplicate
        $newRoleSlugs = array_unique($newRoleSlugs);

        foreach ($newRoleSlugs as $newSlug) {
            $roleId = $rbacRoles[$newSlug] ?? null;
            if (!$roleId) {
                $errors[] = "RBAC role '{$newSlug}' not found in database.";
                continue;
            }

            try {
                $insertUr->execute([$adminId, $roleId, $adminId]); // assigned_by = self (system migration)
                echo "  [OK] Admin #{$adminId} ({$admin['username']}) → Role '{$newSlug}'\n";
            } catch (\PDOException $e) {
                // Ignore duplicate key errors (INSERT IGNORE should handle it)
                if ($e->getCode() !== '23000') {
                    $errors[] = "Admin #{$adminId}: " . $e->getMessage();
                }
            }
        }
        $migrated++;
    }

    echo "\n  [DONE] Processed {$migrated} admin(s).\n";

    if (!empty($errors)) {
        echo "\n  Errors encountered:\n";
        foreach ($errors as $e) {
            echo "    - {$e}\n";
        }
    }

    // ==========================================
    // 3. Summary
    // ==========================================
    echo "\n  --- Assignment Summary ---\n";
    $summary = $db->query("
        SELECT r.name, r.slug, COUNT(ur.id) as user_count
        FROM rbac_roles r
        LEFT JOIN rbac_user_roles ur ON r.id = ur.role_id
        GROUP BY r.id, r.name, r.slug
        ORDER BY r.sort_order ASC
    ")->fetchAll();

    foreach ($summary as $row) {
        echo "  {$row['name']} ({$row['slug']}): {$row['user_count']} user(s)\n";
    }

    echo "\n=== Migration 003 Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration 003 failed: " . $e->getMessage() . "\n";
    exit(1);
}
