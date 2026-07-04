<?php
/**
 * Create Panihati Yatra Registrations Table & Seed Travel Agent Role
 * 
 * Run: php database/migrations/create_panihati_table.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Panihati Yatra Migration ===\n\n";

try {
    $db = getDB();
    
    // 1. Create table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `panihati_yatra_registrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `travel_mode` ENUM('bus', 'own_vehicle') NOT NULL,
            `adults_count` INT NOT NULL DEFAULT 1,
            `kids_count` INT NOT NULL DEFAULT 0,
            `bhakti_sadan` VARCHAR(100) NOT NULL,
            `pickup_location` VARCHAR(100) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `payment_status` ENUM('created', 'paid', 'failed', 'offline') NOT NULL DEFAULT 'created',
            `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
            `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
            `razorpay_signature` VARCHAR(255) DEFAULT NULL,
            `is_offline` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'panihati_yatra_registrations' created successfully.\n";

    // 2. Check and seed travel_agent user
    $username = 'agent';
    $email = 'agent@iskconbangalore.co.in';
    $password = 'isjm@agent';
    $fullName = 'Panihati Yatra Travel Agent';
    $role = 'travel_agent';
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $exists = (int)$stmt->fetchColumn() > 0;
    
    if (!$exists) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $db->prepare("
            INSERT INTO admins (username, email, password_hash, full_name, role)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->execute([$username, $email, $passwordHash, $fullName, $role]);
        echo "  [OK] Seeded 'travel_agent' user:\n";
        echo "       Username: {$username}\n";
        echo "       Password: {$password}\n";
    } else {
        echo "  [INFO] User '{$username}' already exists. Seeding skipped.\n";
    }
    
    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
