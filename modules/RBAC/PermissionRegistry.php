<?php

namespace Isjm\Modules\RBAC;

/**
 * PermissionRegistry — Central registry of all permission definitions.
 * 
 * Defines all the modules and actions available in the system.
 * Used by seed migrations and the permission matrix UI.
 */
class PermissionRegistry
{
    /**
     * All modules with their actions.
     * Each module has: label, icon, description, and actions list.
     */
    public static function getModules(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'icon' => 'fa-chart-line',
                'description' => 'Admin dashboard overview',
                'actions' => ['view'],
            ],
            'donations' => [
                'label' => 'Donations',
                'icon' => 'fa-hand-holding-heart',
                'description' => 'Transaction logs, cause management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'festivals' => [
                'label' => 'Festivals',
                'icon' => 'fa-calendar-alt',
                'description' => 'Festival/cause listing and management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'seva_catalog' => [
                'label' => 'Seva Catalog',
                'icon' => 'fa-list-check',
                'description' => 'Master seva catalog management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'blogs' => [
                'label' => 'Blogs & Content',
                'icon' => 'fa-newspaper',
                'description' => 'Blog posts and content management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'bookings' => [
                'label' => 'Bookings',
                'icon' => 'fa-hands-praying',
                'description' => 'Puja and Yagya bookings',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'panihati' => [
                'label' => 'Panihati Yatra',
                'icon' => 'fa-route',
                'description' => 'Yatra registration and management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'sudamaseva' => [
                'label' => 'Sudamaseva',
                'icon' => 'fa-sync',
                'description' => 'Subscription donation management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'reports' => [
                'label' => 'Reports',
                'icon' => 'fa-chart-bar',
                'description' => 'Donation reports and dashboards',
                'actions' => ['view', 'export'],
            ],
            'devotees' => [
                'label' => 'Devotees',
                'icon' => 'fa-users',
                'description' => 'Devotee management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'volunteers' => [
                'label' => 'Volunteers',
                'icon' => 'fa-hands-helping',
                'description' => 'Volunteer management',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'events' => [
                'label' => 'Events',
                'icon' => 'fa-star',
                'description' => 'Special events and programs',
                'actions' => ['view', 'create', 'edit', 'delete', 'export'],
            ],
            'audit_logs' => [
                'label' => 'Audit Logs',
                'icon' => 'fa-history',
                'description' => 'System audit log viewing (reserved for future use)',
                'actions' => ['view', 'export'],
            ],
        ];
    }

    /**
     * Get all permissions as flat array of slug => label.
     */
    public static function getAllPermissions(): array
    {
        $permissions = [];
        foreach (self::getModules() as $module => $config) {
            foreach ($config['actions'] as $action) {
                $slug = $module . '.' . $action;
                $label = ucfirst($action) . ' ' . $config['label'];
                $permissions[$slug] = $label;
            }
        }
        return $permissions;
    }

    /**
     * Get the sort order for a permission slug.
     * Used to maintain consistent ordering in the UI.
     */
    public static function getSortOrder(string $module, string $action): int
    {
        $actionOrder = ['view' => 10, 'create' => 20, 'edit' => 30, 'delete' => 40, 'export' => 50];
        $moduleIndex = 0;
        $modules = array_keys(self::getModules());
        $moduleIndex = array_search($module, $modules);
        if ($moduleIndex === false) {
            $moduleIndex = 999;
        }
        return ($moduleIndex * 100) + ($actionOrder[$action] ?? 99);
    }
}
