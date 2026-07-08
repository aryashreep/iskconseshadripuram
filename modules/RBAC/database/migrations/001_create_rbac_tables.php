<?php
/**
 * Migration 001: Create RBAC Tables
 * 
 * Creates the core RBAC tables: roles, permissions, role_permissions, user_roles.
 * 
 * Run: php modules/RBAC/database/migrations/001_create_rbac_tables.php
 */

chdir(__DIR__ . '/../../../..');
require_once 'config.php';

echo "=== RBAC Migration 001: Create Tables ===\n\n";

try {
    $db = getDB();
    
    // 1. rbac_roles
    $db->exec("
        CREATE TABLE IF NOT EXISTS `rbac_roles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Machine-readable identifier, e.g. content_manager',
            `name` VARCHAR(255) NOT NULL COMMENT 'Human-readable name, e.g. Content Manager',
            `description` TEXT DEFAULT NULL COMMENT 'What this role is for',
            `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System roles cannot be deleted via UI',
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Created rbac_roles table.\n";
    
    // 2. rbac_permissions
    $db->exec("
        CREATE TABLE IF NOT EXISTS `rbac_permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(150) NOT NULL UNIQUE COMMENT 'e.g. donations.view',
            `module` VARCHAR(100) NOT NULL COMMENT 'Module name, e.g. donations, festivals',
            `action` VARCHAR(50) NOT NULL COMMENT 'Action name, e.g. view, create, edit, delete, export',
            `label` VARCHAR(255) NOT NULL COMMENT 'Human-readable label, e.g. View Donations',
            `description` TEXT DEFAULT NULL,
            `is_system` TINYINT(1) NOT NULL DEFAULT 0,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_perm_module` (`module`),
            INDEX `idx_perm_action` (`action`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Created rbac_permissions table.\n";
    
    // 3. rbac_role_permissions (Many-to-Many)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `rbac_role_permissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `role_id` INT NOT NULL,
            `permission_id` INT NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_role_perm` (`role_id`, `permission_id`),
            CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `rbac_permissions`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Created rbac_role_permissions table.\n";
    
    // 4. rbac_user_roles (Many-to-Many with admins)
    $db->exec("
        CREATE TABLE IF NOT EXISTS `rbac_user_roles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `admin_id` INT NOT NULL,
            `role_id` INT NOT NULL,
            `assigned_by` INT DEFAULT NULL COMMENT 'Admin ID who assigned this role',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_user_role` (`admin_id`, `role_id`),
            CONSTRAINT `fk_ur_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_ur_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Created rbac_user_roles table.\n";
    
    echo "\n=== Migration 001 Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration 001 failed: " . $e->getMessage() . "\n";
    exit(1);
}
