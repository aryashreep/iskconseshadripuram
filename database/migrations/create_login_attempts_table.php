<?php
/**
 * Create Login Attempts Table for Rate Limiting
 * 
 * Run: php database/migrations/create_login_attempts_table.php
 */

require_once __DIR__ . '/../../config.php';

echo "=== Login Attempts Rate Limiting Migration ===\n\n";

try {
    $db = getDB();
    
    $sql = "
        CREATE TABLE IF NOT EXISTS `login_attempts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `ip_address` VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 address',
            `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `username_attempted` VARCHAR(255) DEFAULT NULL COMMENT 'The username that was tried',
            `successful` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether this attempt succeeded',
            INDEX `idx_ip` (`ip_address`),
            INDEX `idx_attempted_at` (`attempted_at`),
            INDEX `idx_ip_time` (`ip_address`, `attempted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    echo "  [OK] Table 'login_attempts' created successfully.\n\n";
    
} catch (PDOException $e) {
    echo "  [FAILED] " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "=== Rate limiting is now active ===\n";
echo "Configuration: Max 5 failed attempts per 15-minute window per IP address.\n";
echo "Login page: admin/login.php\n\n";
