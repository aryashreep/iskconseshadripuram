<?php

namespace Isjm\Tests\Unit;

/**
 * RbacTestHelper — Sets up an in-memory SQLite database with the RBAC schema
 * and seeds test data for unit testing.
 */
class RbacTestHelper
{
    private static ?\PDO $pdo = null;

    /**
     * Create an in-memory SQLite PDO with RBAC tables and sample data.
     */
    public static function createDb(): \PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');

        self::createTables($pdo);
        self::seedData($pdo);

        self::$pdo = $pdo;
        return $pdo;
    }

    /**
     * Reset the singleton for test isolation between suites.
     */
    public static function reset(): void
    {
        self::$pdo = null;
    }

    private static function createTables(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rbac_roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug VARCHAR(100) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                is_system INTEGER NOT NULL DEFAULT 0,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rbac_permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug VARCHAR(150) NOT NULL UNIQUE,
                module VARCHAR(100) NOT NULL,
                action VARCHAR(50) NOT NULL,
                label VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                is_system INTEGER NOT NULL DEFAULT 0,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rbac_role_permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                role_id INTEGER NOT NULL,
                permission_id INTEGER NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (role_id, permission_id),
                FOREIGN KEY (role_id) REFERENCES rbac_roles(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES rbac_permissions(id) ON DELETE CASCADE
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS rbac_user_roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                admin_id INTEGER NOT NULL,
                role_id INTEGER NOT NULL,
                assigned_by INTEGER DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (admin_id, role_id),
                FOREIGN KEY (role_id) REFERENCES rbac_roles(id) ON DELETE CASCADE
            )
        ");

        // We need an admins table for FK references (simplified version)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(100) NOT NULL,
                role VARCHAR(50) NOT NULL DEFAULT 'editor'
            )
        ");
    }

    private static function seedData(\PDO $pdo): void
    {
        // —— Seed roles ——
        $roles = [
            ['super_admin', 'Super Administrator', 'Unrestricted access', 1, 1],
            ['temple_admin', 'Temple Administrator', 'Full operational access', 1, 2],
            ['donation_manager', 'Donation Manager', 'Manage donations', 1, 3],
            ['festival_manager', 'Festival Manager', 'Manage festivals', 1, 4],
            ['accounts', 'Accounts / Finance', 'View financial data', 1, 5],
            ['content_manager', 'Content Manager', 'Manage content', 1, 6],
            ['report_viewer', 'Report Viewer', 'Read-only reports', 1, 7],
            ['devotee_care', 'Devotee Care', 'Manage devotees', 1, 8],
            ['read_only', 'Read Only User', 'View-only access', 1, 11],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO rbac_roles (slug, name, description, is_system, sort_order) VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($roles as $r) {
            $stmt->execute($r);
        }

        // —— Seed permissions ——
        $permissions = [
            ['dashboard.view', 'dashboard', 'view', 'View Dashboard', 10],
            ['donations.view', 'donations', 'view', 'View Donations', 110],
            ['donations.create', 'donations', 'create', 'Create Donations', 120],
            ['donations.edit', 'donations', 'edit', 'Edit Donations', 130],
            ['donations.delete', 'donations', 'delete', 'Delete Donations', 140],
            ['donations.export', 'donations', 'export', 'Export Donations', 150],
            ['festivals.view', 'festivals', 'view', 'View Festivals', 210],
            ['festivals.create', 'festivals', 'create', 'Create Festivals', 220],
            ['festivals.edit', 'festivals', 'edit', 'Edit Festivals', 230],
            ['festivals.delete', 'festivals', 'delete', 'Delete Festivals', 240],
            ['festivals.export', 'festivals', 'export', 'Export Festivals', 250],
            ['blogs.view', 'blogs', 'view', 'View Blogs', 310],
            ['blogs.create', 'blogs', 'create', 'Create Blogs', 320],
            ['blogs.edit', 'blogs', 'edit', 'Edit Blogs', 330],
            ['blogs.delete', 'blogs', 'delete', 'Delete Blogs', 340],
            ['blogs.export', 'blogs', 'export', 'Export Blogs', 350],
            ['reports.view', 'reports', 'view', 'View Reports', 810],
            ['reports.export', 'reports', 'export', 'Export Reports', 850],
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO rbac_permissions (slug, module, action, label, sort_order) VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($permissions as $p) {
            $stmt->execute($p);
        }

        // —— Seed role-permission assignments ——
        // Format: [role_slug, permission_slug]
        $assignments = [
            // temple_admin gets most permissions
            ['temple_admin', 'dashboard.view'],
            ['temple_admin', 'donations.view'],
            ['temple_admin', 'donations.create'],
            ['temple_admin', 'donations.edit'],
            ['temple_admin', 'donations.delete'],
            ['temple_admin', 'donations.export'],
            ['temple_admin', 'festivals.view'],
            ['temple_admin', 'festivals.create'],
            ['temple_admin', 'festivals.edit'],
            ['temple_admin', 'festivals.delete'],
            ['temple_admin', 'festivals.export'],
            ['temple_admin', 'blogs.view'],
            ['temple_admin', 'blogs.create'],
            ['temple_admin', 'blogs.edit'],
            ['temple_admin', 'blogs.delete'],
            ['temple_admin', 'blogs.export'],
            ['temple_admin', 'reports.view'],
            ['temple_admin', 'reports.export'],
            // donation_manager
            ['donation_manager', 'dashboard.view'],
            ['donation_manager', 'donations.view'],
            ['donation_manager', 'donations.create'],
            ['donation_manager', 'donations.edit'],
            ['donation_manager', 'donations.delete'],
            ['donation_manager', 'donations.export'],
            ['donation_manager', 'reports.view'],
            ['donation_manager', 'reports.export'],
            // festival_manager
            ['festival_manager', 'dashboard.view'],
            ['festival_manager', 'festivals.view'],
            ['festival_manager', 'festivals.create'],
            ['festival_manager', 'festivals.edit'],
            ['festival_manager', 'festivals.delete'],
            ['festival_manager', 'festivals.export'],
            // accounts
            ['accounts', 'donations.view'],
            ['accounts', 'donations.export'],
            ['accounts', 'reports.view'],
            ['accounts', 'reports.export'],
            // content_manager
            ['content_manager', 'blogs.view'],
            ['content_manager', 'blogs.create'],
            ['content_manager', 'blogs.edit'],
            ['content_manager', 'blogs.delete'],
            ['content_manager', 'blogs.export'],
            // report_viewer
            ['report_viewer', 'dashboard.view'],
            ['report_viewer', 'donations.view'],
            ['report_viewer', 'reports.view'],
            ['report_viewer', 'reports.export'],
            // devotee_care
            ['devotee_care', 'dashboard.view'],
            // read_only
            ['read_only', 'dashboard.view'],
        ];

        // Get role IDs and permission IDs
        $roleIds = [];
        $rStmt = $pdo->query('SELECT id, slug FROM rbac_roles');
        foreach ($rStmt->fetchAll() as $r) {
            $roleIds[$r['slug']] = $r['id'];
        }

        $permIds = [];
        $pStmt = $pdo->query('SELECT id, slug FROM rbac_permissions');
        foreach ($pStmt->fetchAll() as $p) {
            $permIds[$p['slug']] = $p['id'];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO rbac_role_permissions (role_id, permission_id) VALUES (?, ?)'
        );
        foreach ($assignments as [$roleSlug, $permSlug]) {
            $stmt->execute([$roleIds[$roleSlug], $permIds[$permSlug]]);
        }

        // —— Seed admin users ——
        $admins = [
            ['super_admin', 'super_admin'],
            ['admin_user', 'temple_admin'],
            ['donation_user', 'donation_manager'],
            ['festival_user', 'festival_manager'],
            ['accounts_user', 'accounts'],
            ['report_user', 'report_viewer'],
            ['readonly_user', 'read_only'],
        ];

        $stmt = $pdo->prepare('INSERT INTO admins (username, role) VALUES (?, ?)');
        $adminIds = [];
        foreach ($admins as $idx => [$username, $role]) {
            $stmt->execute([$username, $role]);
            $adminIds[$username] = (int) $pdo->lastInsertId();
        }

        // —— Seed user-role assignments ——
        $stmt = $pdo->prepare(
            'INSERT INTO rbac_user_roles (admin_id, role_id) VALUES (?, ?)'
        );
        foreach ($admins as [$username, $roleSlug]) {
            $stmt->execute([$adminIds[$username], $roleIds[$roleSlug]]);
        }
    }


}

