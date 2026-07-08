<?php
/**
 * Migration 002: Seed RBAC Roles, Permissions & Matrix
 * 
 * Seeds all roles, all 65 permissions, and the complete role-permission matrix.
 * Uses INSERT IGNORE so it's safe to run multiple times.
 * 
 * Run: php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php
 */

chdir(__DIR__ . '/../../../..');
require_once 'config.php';

// Load PermissionRegistry (must be at file level for use statement)
require_once __DIR__ . '/../../PermissionRegistry.php';

use Isjm\Modules\RBAC\PermissionRegistry;

echo "=== RBAC Migration 002: Seed Roles & Permissions ===\n\n";

try {
    $db = getDB();

    // ==========================================
    // 1. SEED ROLES
    // ==========================================
    $roles = [
        ['slug' => 'super_admin',           'name' => 'Super Administrator',          'description' => 'Unrestricted access across all system modules. Bypasses all permission checks.',                                          'is_system' => 1, 'sort_order' => 1],
        ['slug' => 'temple_admin',          'name' => 'Temple Administrator',         'description' => 'Full access to all operational modules. Cannot manage admin users, roles, or system settings.',                   'is_system' => 1, 'sort_order' => 2],
        ['slug' => 'donation_manager',      'name' => 'Donation Manager',             'description' => 'Manage donations, causes, and related reporting.',                                                              'is_system' => 1, 'sort_order' => 3],
        ['slug' => 'festival_manager',      'name' => 'Festival Manager',             'description' => 'Manage festivals, events, and seva catalog.',                                                                   'is_system' => 1, 'sort_order' => 4],
        ['slug' => 'accounts',              'name' => 'Accounts / Finance',           'description' => 'View financial data, reports, exports, and process refunds.',                                                 'is_system' => 1, 'sort_order' => 5],
        ['slug' => 'content_manager',       'name' => 'Content Manager',              'description' => 'Manage blogs and website content.',                                                                           'is_system' => 1, 'sort_order' => 6],
        ['slug' => 'report_viewer',         'name' => 'Report Viewer',                'description' => 'Read-only access to reports and dashboards.',                                                                  'is_system' => 1, 'sort_order' => 7],
        ['slug' => 'devotee_care',          'name' => 'Devotee Care',                 'description' => 'Manage devotee records and relationships.',                                                                    'is_system' => 1, 'sort_order' => 8],
        ['slug' => 'volunteer_coordinator', 'name' => 'Volunteer Coordinator',        'description' => 'Manage volunteers and assignments.',                                                                           'is_system' => 1, 'sort_order' => 9],
        ['slug' => 'event_coordinator',     'name' => 'Event Coordinator',            'description' => 'Manage special events and programs.',                                                                           'is_system' => 1, 'sort_order' => 10],
        ['slug' => 'read_only',             'name' => 'Read Only User',               'description' => 'View-only access across permitted modules. No create/edit/delete rights.',                                    'is_system' => 1, 'sort_order' => 11],
    ];

    $roleIds = [];
    $stmt = $db->prepare("
        INSERT IGNORE INTO rbac_roles (slug, name, description, is_system, sort_order)
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($roles as $r) {
        $stmt->execute([$r['slug'], $r['name'], $r['description'], $r['is_system'], $r['sort_order']]);
        // Get the ID (whether inserted or existing via INSERT IGNORE)
        $idStmt = $db->prepare("SELECT id FROM rbac_roles WHERE slug = ?");
        $idStmt->execute([$r['slug']]);
        $roleIds[$r['slug']] = (int) $idStmt->fetchColumn();
        echo "  [OK] Role: {$r['name']} ({$r['slug']})\n";
    }
    echo "\n";

    // ==========================================
    // 2. SEED PERMISSIONS
    // ==========================================

    $modules = PermissionRegistry::getModules();
    $permIds = [];

    $insertPerm = $db->prepare("
        INSERT IGNORE INTO rbac_permissions (slug, module, action, label, description, sort_order)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $definedModules = array_keys($modules);
    $actionLabels = [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'export' => 'Export',
    ];

    foreach ($modules as $module => $config) {
        foreach ($config['actions'] as $action) {
            $slug = $module . '.' . $action;
            $label = ($actionLabels[$action] ?? ucfirst($action)) . ' ' . $config['label'];
            $description = $config['description'];
            $sortOrder = PermissionRegistry::getSortOrder($module, $action);
            $insertPerm->execute([$slug, $module, $action, $label, $description, $sortOrder]);

            // Get the permission ID
            $idStmt = $db->prepare("SELECT id FROM rbac_permissions WHERE slug = ?");
            $idStmt->execute([$slug]);
            $permIds[$slug] = (int) $idStmt->fetchColumn();
        }
    }

    $totalPerms = count($permIds);
    echo "  [OK] Seeded {$totalPerms} permissions across " . count($modules) . " modules.\n\n";

    // ==========================================
    // 3. SEED ROLE-PERMISSION MATRIX
    // ==========================================
    $insertRp = $db->prepare("
        INSERT IGNORE INTO rbac_role_permissions (role_id, permission_id) VALUES (?, ?)
    ");

    /**
     * Define which permissions each role gets.
     * Format: role_slug => [permission_slug, ...]
     * 
     * Note: super_admin is not listed here because it uses an implicit bypass.
     */
    $rolePermissions = [
        'temple_admin' => [
            'dashboard.view',
            'donations.view', 'donations.create', 'donations.edit', 'donations.delete', 'donations.export',
            'festivals.view', 'festivals.create', 'festivals.edit', 'festivals.delete', 'festivals.export',
            'seva_catalog.view', 'seva_catalog.create', 'seva_catalog.edit', 'seva_catalog.delete', 'seva_catalog.export',
            'blogs.view', 'blogs.create', 'blogs.edit', 'blogs.delete', 'blogs.export',
            'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.delete', 'bookings.export',
            'panihati.view', 'panihati.create', 'panihati.edit', 'panihati.delete', 'panihati.export',
            'sudamaseva.view', 'sudamaseva.create', 'sudamaseva.edit', 'sudamaseva.delete', 'sudamaseva.export',
            'reports.view', 'reports.export',
            'devotees.view', 'devotees.create', 'devotees.edit', 'devotees.delete', 'devotees.export',
            'volunteers.view', 'volunteers.create', 'volunteers.edit', 'volunteers.delete', 'volunteers.export',
            'events.view', 'events.create', 'events.edit', 'events.delete',
            'audit_logs.view', 'audit_logs.export',
        ],
        'donation_manager' => [
            'dashboard.view',
            'donations.view', 'donations.create', 'donations.edit', 'donations.delete', 'donations.export',
            'seva_catalog.view',
            'sudamaseva.view', 'sudamaseva.create', 'sudamaseva.edit', 'sudamaseva.export',
            'reports.view', 'reports.export',
        ],
        'festival_manager' => [
            'dashboard.view',
            'festivals.view', 'festivals.create', 'festivals.edit', 'festivals.delete', 'festivals.export',
            'seva_catalog.view', 'seva_catalog.create', 'seva_catalog.edit', 'seva_catalog.delete', 'seva_catalog.export',
            'events.view', 'events.create', 'events.edit', 'events.delete',
        ],
        'accounts' => [
            'dashboard.view',
            'donations.view', 'donations.edit', 'donations.export',
            'bookings.view', 'bookings.export',
            'panihati.export',
            'sudamaseva.view', 'sudamaseva.export',
            'reports.view', 'reports.export',
        ],
        'content_manager' => [
            'dashboard.view',
            'festivals.view', 'festivals.create', 'festivals.edit', 'festivals.delete',
            'blogs.view', 'blogs.create', 'blogs.edit', 'blogs.delete', 'blogs.export',
        ],
        'report_viewer' => [
            'dashboard.view',
            'donations.view',
            'festivals.view',
            'reports.view', 'reports.export',
        ],
        'devotee_care' => [
            'dashboard.view',
            'devotees.view', 'devotees.create', 'devotees.edit', 'devotees.delete', 'devotees.export',
            'volunteers.view',
        ],
        'volunteer_coordinator' => [
            'dashboard.view',
            'devotees.view', 'devotees.create', 'devotees.edit',
            'volunteers.view', 'volunteers.create', 'volunteers.edit', 'volunteers.delete', 'volunteers.export',
        ],
        'event_coordinator' => [
            'dashboard.view',
            'festivals.view', 'festivals.create', 'festivals.edit',
            'panihati.view', 'panihati.create', 'panihati.edit',
            'events.view', 'events.create', 'events.edit', 'events.delete',
        ],
        'read_only' => [
            'dashboard.view',
            'donations.view',
            'festivals.view',
            'blogs.view',
            'reports.view',
        ],
    ];

    $count = 0;
    foreach ($rolePermissions as $roleSlug => $perms) {
        $roleId = $roleIds[$roleSlug] ?? null;
        if (!$roleId) {
            echo "  [WARN] Role slug '{$roleSlug}' not found, skipping.\n";
            continue;
        }
        foreach ($perms as $permSlug) {
            $permId = $permIds[$permSlug] ?? null;
            if (!$permId) {
                echo "  [WARN] Permission slug '{$permSlug}' not found, skipping.\n";
                continue;
            }
            $insertRp->execute([$roleId, $permId]);
            $count++;
        }
    }

    echo "  [OK] Seeded {$count} role-permission assignments.\n";
    echo "\n=== Migration 002 Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration 002 failed: " . $e->getMessage() . "\n";
    exit(1);
}
