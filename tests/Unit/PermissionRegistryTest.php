<?php

namespace Isjm\Tests\Unit;

use Isjm\Modules\RBAC\PermissionRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PermissionRegistry::class)]
class PermissionRegistryTest extends TestCase
{
    // ==========================================
    // getModules()
    // ==========================================

    #[Test]
    public function getModules_returns_all_13_modules(): void
    {
        $modules = PermissionRegistry::getModules();

        $this->assertCount(13, $modules);
    }

    #[Test]
    public function each_module_has_required_keys(): void
    {
        $modules = PermissionRegistry::getModules();

        foreach ($modules as $module => $config) {
            $this->assertArrayHasKey('label', $config, "Module '{$module}' missing 'label'");
            $this->assertArrayHasKey('icon', $config, "Module '{$module}' missing 'icon'");
            $this->assertArrayHasKey('description', $config, "Module '{$module}' missing 'description'");
            $this->assertArrayHasKey('actions', $config, "Module '{$module}' missing 'actions'");
            $this->assertNotEmpty($config['actions'], "Module '{$module}' has empty actions");
            $this->assertIsArray($config['actions'], "Module '{$module}' actions must be an array");
        }
    }

    #[Test]
    public function module_icons_follow_fontawesome_pattern(): void
    {
        $modules = PermissionRegistry::getModules();

        foreach ($modules as $module => $config) {
            $this->assertStringStartsWith(
                'fa-',
                $config['icon'],
                "Module '{$module}' icon '{$config['icon']}' should start with 'fa-'"
            );
        }
    }

    #[Test]
    public function dashboard_module_has_view_only(): void
    {
        $modules = PermissionRegistry::getModules();

        $this->assertArrayHasKey('dashboard', $modules);
        $this->assertSame(['view'], $modules['dashboard']['actions']);
    }

    #[Test]
    public function reports_module_has_view_and_export_only(): void
    {
        $modules = PermissionRegistry::getModules();

        $this->assertArrayHasKey('reports', $modules);
        $this->assertEqualsCanonicalizing(
            ['view', 'export'],
            $modules['reports']['actions']
        );
    }

    #[Test]
    public function audit_logs_module_has_view_and_export_only(): void
    {
        $modules = PermissionRegistry::getModules();

        $this->assertArrayHasKey('audit_logs', $modules);
        $this->assertEqualsCanonicalizing(
            ['view', 'export'],
            $modules['audit_logs']['actions']
        );
    }

    #[Test]
    public function full_crud_modules_have_view_create_edit_delete_export(): void
    {
        $fullCrudModules = [
            'donations', 'festivals', 'seva_catalog', 'blogs',
            'bookings', 'panihati', 'sudamaseva',
            'devotees', 'volunteers', 'events',
        ];

        $modules = PermissionRegistry::getModules();

        foreach ($fullCrudModules as $module) {
            $this->assertArrayHasKey($module, $modules, "Module '{$module}' not found");
            $this->assertEqualsCanonicalizing(
                ['view', 'create', 'edit', 'delete', 'export'],
                $modules[$module]['actions'],
                "Module '{$module}' should have all 5 CRUD+L actions"
            );
        }
    }

    // ==========================================
    // getAllPermissions()
    // ==========================================

    #[Test]
    public function getAllPermissions_returns_55_permissions(): void
    {
        $permissions = PermissionRegistry::getAllPermissions();

        $this->assertCount(55, $permissions);
    }

    #[Test]
    public function getAllPermissions_keys_are_slug_format(): void
    {
        $permissions = PermissionRegistry::getAllPermissions();

        foreach ($permissions as $slug => $label) {
            $this->assertMatchesRegularExpression(
                '/^[a-z_]+\.[a-z]+$/',
                $slug,
                "Permission slug '{$slug}' does not match 'module.action' format"
            );
        }
    }

    #[Test]
    public function getAllPermissions_has_dashboard_view(): void
    {
        $permissions = PermissionRegistry::getAllPermissions();

        $this->assertArrayHasKey('dashboard.view', $permissions);
        $this->assertSame('View Dashboard', $permissions['dashboard.view']);
    }

    #[Test]
    public function getAllPermissions_has_all_expected_exports(): void
    {
        $permissions = PermissionRegistry::getAllPermissions();

        $exportSlugs = [
            'donations.export',
            'festivals.export',
            'seva_catalog.export',
            'blogs.export',
            'bookings.export',
            'panihati.export',
            'sudamaseva.export',
            'devotees.export',
            'volunteers.export',
            'events.export',
            'reports.export',
            'audit_logs.export',
        ];

        foreach ($exportSlugs as $slug) {
            $this->assertArrayHasKey($slug, $permissions, "Missing export permission: {$slug}");
        }
    }

    #[Test]
    public function labels_are_properly_formatted(): void
    {
        $permissions = PermissionRegistry::getAllPermissions();

        // Each label should be like "View Dashboard", "Edit Donations", etc.
        foreach ($permissions as $slug => $label) {
            $parts = explode(' ', $label);
            $this->assertGreaterThanOrEqual(2, count($parts), "Label '{$label}' seems too short");
            // First word should be action (View, Create, Edit, Delete, Export)
            $validActions = ['View', 'Create', 'Edit', 'Delete', 'Export'];
            $this->assertContains($parts[0], $validActions, "Label '{$label}' doesn't start with a valid action");
        }
    }

    #[Test]
    public function getAllPermissions_is_consistent_with_getModules(): void
    {
        $modules = PermissionRegistry::getModules();
        $permissions = PermissionRegistry::getAllPermissions();

        $expectedCount = 0;
        foreach ($modules as $config) {
            $expectedCount += count($config['actions']);
        }

        $this->assertCount($expectedCount, $permissions);
    }

    // ==========================================
    // getSortOrder()
    // ==========================================

    #[Test]
    #[DataProvider('sortOrderProvider')]
    public function getSortOrder_returns_consistent_ordering(string $module, string $action, int $expectedMin): void
    {
        $order = PermissionRegistry::getSortOrder($module, $action);

        $this->assertGreaterThanOrEqual($expectedMin, $order);
        $this->assertLessThan($expectedMin + 100, $order);
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function sortOrderProvider(): array
    {
        return [
            'dashboard view' => ['dashboard', 'view', 0],
            'donations view' => ['donations', 'view', 100],
            'festivals edit' => ['festivals', 'edit', 230],
            'reports export' => ['reports', 'export', 850],
        ];
    }

    #[Test]
    public function getSortOrder_increments_by_module(): void
    {
        $modules = array_keys(PermissionRegistry::getModules());

        for ($i = 1; $i < count($modules); $i++) {
            $prev = PermissionRegistry::getSortOrder($modules[$i - 1], 'view');
            $curr = PermissionRegistry::getSortOrder($modules[$i], 'view');
            $this->assertGreaterThan(
                $prev,
                $curr,
                "Module '{$modules[$i]}' should have higher sort order than '{$modules[$i - 1]}'"
            );
        }
    }

    #[Test]
    public function getSortOrder_unknown_module_returns_high_value(): void
    {
        $order = PermissionRegistry::getSortOrder('nonexistent_module', 'view');

        $this->assertGreaterThanOrEqual(99900, $order);
    }

    #[Test]
    public function getSortOrder_unknown_action_returns_fallback(): void
    {
        $order = PermissionRegistry::getSortOrder('dashboard', 'unknown_action');

        $this->assertSame(99, $order % 100);
    }
}
