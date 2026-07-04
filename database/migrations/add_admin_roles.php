<?php
/**
 * Add Admin Roles Migration Script
 * Adds a role column to the admins table and seeds default test users for each role.
 * 
 * Run: php database/migrations/add_admin_roles.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Admin Roles Migration ===\n\n";

try {
    $db = getDB();
    
    // 1. Add role column
    $stmt = $db->query("SHOW COLUMNS FROM `admins` LIKE 'role'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $db->exec("ALTER TABLE `admins` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'editor'");
        echo "  [OK] Added 'role' column to admins table.\n";
    } else {
        echo "  [INFO] 'role' column already exists in admins table.\n";
    }
    
    // 2. Set default admin to super_admin
    $db->exec("UPDATE `admins` SET `role` = 'super_admin' WHERE `username` = 'admin'");
    echo "  [OK] Set default admin role to 'super_admin'.\n";

    // 3. Seed other test users
    $testUsers = [
        [
            'username' => 'editor',
            'email' => 'editor@iskconbangalore.co.in',
            'password' => 'isjm@editor',
            'full_name' => 'ISKCON The Palace Temple of Lord Jagannath Content Editor',
            'role' => 'editor'
        ],
        [
            'username' => 'pujari',
            'email' => 'pujari@iskconbangalore.co.in',
            'password' => 'isjm@pujari',
            'full_name' => 'Temple Pujari Head',
            'role' => 'pujari'
        ],
        [
            'username' => 'treasurer',
            'email' => 'treasurer@iskconbangalore.co.in',
            'password' => 'isjm@treasurer',
            'full_name' => 'ISKCON The Palace Temple of Lord Jagannath Treasurer / Accountant',
            'role' => 'treasurer'
        ]
    ];

    foreach ($testUsers as $u) {
        $check = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?");
        $check->execute([$u['username'], $u['email']]);
        if ((int)$check->fetchColumn() === 0) {
            $passHash = password_hash($u['password'], PASSWORD_DEFAULT);
            $ins = $db->prepare("
                INSERT INTO admins (username, email, password_hash, full_name, role)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->execute([$u['username'], $u['email'], $passHash, $u['full_name'], $u['role']]);
            echo "  [OK] Seeded test user '{$u['username']}' with role '{$u['role']}'.\n";
        } else {
            echo "  [INFO] Test user '{$u['username']}' already exists. Seeding skipped.\n";
        }
    }
    
    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
