<?php
/**
 * Alter Admins Table Role Column Length Migration Script
 * 
 * Run: php database/migrations/alter_admin_role_length.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Alter Admins Role Column Length Migration ===\n\n";

try {
    $db = getDB();
    
    // Modify column to VARCHAR(255)
    $db->exec("ALTER TABLE `admins` MODIFY COLUMN `role` VARCHAR(255) NOT NULL DEFAULT 'editor'");
    echo "  [OK] Modified 'role' column to VARCHAR(255).\n";

    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
