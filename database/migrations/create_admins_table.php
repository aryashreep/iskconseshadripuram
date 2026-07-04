<?php
/**
 * Create Admins Table Migration Script
 * 
 * Run: php database/migrations/create_admins_table.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Admin Table Migration ===\n\n";

try {
    $db = getDB();
    
    // Create admins table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `full_name` VARCHAR(100) DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Admins table ready.\n";

    // Check if default admin exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?");
    $stmt->execute(['admin', 'isjmadmin@gmail.com']);
    $exists = (int)$stmt->fetchColumn() > 0;

    if (!$exists) {
        $username = 'admin';
        $email = 'isjmadmin@gmail.com';
        $password = 'isjm@admin';
        $fullName = 'ISKCON The Palace Temple of Lord Jagannath Administrator';
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $insert = $db->prepare("
            INSERT INTO admins (username, email, password_hash, full_name)
            VALUES (?, ?, ?, ?)
        ");
        $insert->execute([$username, $email, $passwordHash, $fullName]);
        
        echo "  [OK] Default admin user seeded successfully.\n";
        echo "       Username: {$username}\n";
        echo "       Email: {$email}\n";
        echo "       Password: {$password} (Please change this after first login)\n";
    } else {
        echo "  [INFO] Default admin user (or email) already exists. Seeding skipped.\n";
    }
    
    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
