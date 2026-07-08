<?php

namespace Isjm\Tests\Unit;

use Isjm\Modules\RBAC\RbacService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RbacService::class)]
class RbacServiceTest extends TestCase
{
    private ?RbacService $service = null;

    /**
     * Admin IDs populated via seed data (indexed by username).
     */
    private array $adminIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        RbacTestHelper::reset();
        $db = RbacTestHelper::createDb();
        $this->service = new RbacService($db);

        // Map admin usernames to their IDs
        $stmt = $db->query('SELECT id, username FROM admins ORDER BY id ASC');
        foreach ($stmt->fetchAll() as $row) {
            $this->adminIds[$row['username']] = (int) $row['id'];
        }
    }

    // ==========================================
    // Permission Checking
    // ==========================================

    #[Test]
    public function super_admin_has_all_permissions(): void
    {
        $adminId = $this->adminIds['super_admin'];

        $this->assertTrue($this->service->hasPermission($adminId, 'dashboard.view'));
        $this->assertTrue($this->service->hasPermission($adminId, 'donations.delete'));
        $this->assertTrue($this->service->hasPermission($adminId, 'blogs.export'));
        $this->assertTrue($this->service->hasPermission($adminId, 'reports.view'));
    }

    #[Test]
    public function temple_admin_has_permissions(): void
    {
        $adminId = $this->adminIds['admin_user'];

        $this->assertTrue($this->service->hasPermission($adminId, 'dashboard.view'));
        $this->assertTrue($this->service->hasPermission($adminId, 'donations.view'));
        $this->assertTrue($this->service->hasPermission($adminId, 'donations.create'));
        $this->assertTrue($this->service->hasPermission($adminId, 'donations.edit'));
        $this->assertTrue($this->service->hasPermission($adminId, 'donations.delete'));
        $this->assertTrue($this->service->hasPermission($adminId, 'donations.export'));
        $this->assertTrue($this->service->hasPermission($adminId, 'blogs.view'));
        $this->assertTrue($this->service->hasPermission($adminId, 'blogs.edit'));
    }

    #[Test]
    public function temple_admin_lacks_unassigned_permissions(): void
    {
        $adminId = $this->adminIds['admin_user'];
        // audit_logs was not assigned to temple_admin in test seed
        $this->assertFalse($this->service->hasPermission($adminId, 'audit_logs.view'));
    }

    #[Test]
    public function read_only_user_has_limited_permissions(): void
    {
        $adminId = $this->adminIds['readonly_user'];

        $this->assertTrue($this->service->hasPermission($adminId, 'dashboard.view'));
        $this->assertFalse($this->service->hasPermission($adminId, 'donations.create'));
        $this->assertFalse($this->service->hasPermission($adminId, 'donations.edit'));
        $this->assertFalse($this->service->hasPermission($adminId, 'donations.delete'));
        $this->assertFalse($this->service->hasPermission($adminId, 'donations.export'));
        $this->assertFalse($this->service->hasPermission($adminId, 'blogs.view'));
    }

    #[Test]
    public function users_without_role_return_false(): void
    {
        // A non-existent admin ID should have no permissions
        $this->assertFalse($this->service->hasPermission(99999, 'dashboard.view'));
    }

    #[Test]
    public function empty_permission_slug_returns_false(): void
    {
        $adminId = $this->adminIds['donation_user'];

        $this->assertFalse($this->service->hasPermission($adminId, ''));
    }

    // ==========================================
    // hasAnyPermission()
    // ==========================================

    #[Test]
    public function hasAnyPermission_returns_true_when_admin_has_one(): void
    {
        $adminId = $this->adminIds['donation_user'];

        $this->assertTrue(
            $this->service->hasAnyPermission($adminId, ['donations.view', 'blogs.view'])
        );
    }

    #[Test]
    public function hasAnyPermission_returns_false_when_admin_has_none(): void
    {
        $adminId = $this->adminIds['readonly_user'];

        $this->assertFalse(
            $this->service->hasAnyPermission($adminId, ['blogs.view', 'festivals.view'])
        );
    }

    #[Test]
    public function hasAnyPermission_returns_true_for_super_admin(): void
    {
        $adminId = $this->adminIds['super_admin'];

        $this->assertTrue(
            $this->service->hasAnyPermission($adminId, ['nonexistent.perm'])
        );
    }

    #[Test]
    public function hasAnyPermission_with_empty_array_returns_false(): void
    {
        $adminId = $this->adminIds['donation_user'];

        $this->assertFalse($this->service->hasAnyPermission($adminId, []));
    }

    // ==========================================
    // hasAllPermissions()
    // ==========================================

    #[Test]
    public function hasAllPermissions_returns_true_when_admin_has_all(): void
    {
        $adminId = $this->adminIds['donation_user'];

        $this->assertTrue(
            $this->service->hasAllPermissions($adminId, ['donations.view', 'donations.create'])
        );
    }

    #[Test]
    public function hasAllPermissions_returns_false_when_admin_lacks_one(): void
    {
        $adminId = $this->adminIds['donation_user'];

        $this->assertFalse(
            $this->service->hasAllPermissions($adminId, ['donations.view', 'blogs.view'])
        );
    }

    #[Test]
    public function hasAllPermissions_returns_true_for_super_admin(): void
    {
        $adminId = $this->adminIds['super_admin'];

        $this->assertTrue(
            $this->service->hasAllPermissions($adminId, ['donations.delete', 'blogs.export'])
        );
    }

    #[Test]
    public function hasAllPermissions_with_empty_array_returns_false(): void
    {
        $adminId = $this->adminIds['donation_user'];

        $this->assertFalse($this->service->hasAllPermissions($adminId, []));
    }

    // ==========================================
    // getAdminPermissions()
    // ==========================================

    #[Test]
    public function getAdminPermissions_returns_all_for_super_admin(): void
    {
        $adminId = $this->adminIds['super_admin'];
        $permissions = $this->service->getAdminPermissions($adminId);

        // Super admin should have all 55 permissions from PermissionRegistry
        $this->assertCount(55, $permissions);
        $this->assertContains('dashboard.view', $permissions);
        $this->assertContains('donations.export', $permissions);
    }

    #[Test]
    public function getAdminPermissions_returns_correct_for_temple_admin(): void
    {
        $adminId = $this->adminIds['admin_user'];
        $permissions = $this->service->getAdminPermissions($adminId);

        $this->assertContains('dashboard.view', $permissions);
        $this->assertContains('donations.create', $permissions);
        $this->assertContains('blogs.edit', $permissions);
        $this->assertNotContains('audit_logs.view', $permissions);
    }

    #[Test]
    public function getAdminPermissions_returns_sorted(): void
    {
        $adminId = $this->adminIds['admin_user'];
        $permissions = $this->service->getAdminPermissions($adminId);

        for ($i = 1; $i < count($permissions); $i++) {
            $this->assertLessThan(
                $permissions[$i],
                $permissions[$i - 1],
                'Permissions should be sorted alphabetically'
            );
        }
    }

    #[Test]
    public function getAdminPermissions_returns_empty_for_unknown_admin(): void
    {
        $permissions = $this->service->getAdminPermissions(99999);

        $this->assertEmpty($permissions);
    }

    // ==========================================
    // isSuperAdmin()
    // ==========================================

    #[Test]
    public function isSuperAdmin_returns_true_for_super_admin(): void
    {
        $adminId = $this->adminIds['super_admin'];

        $this->assertTrue($this->service->isSuperAdmin($adminId));
    }

    #[Test]
    public function isSuperAdmin_returns_false_for_other_roles(): void
    {
        $nonSuperAdmin = ['admin_user', 'donation_user', 'festival_user', 'readonly_user'];

        foreach ($nonSuperAdmin as $username) {
            $this->assertFalse(
                $this->service->isSuperAdmin($this->adminIds[$username]),
                "{$username} should not be super_admin"
            );
        }
    }

    #[Test]
    public function isSuperAdmin_returns_false_for_nonexistent_admin(): void
    {
        $this->assertFalse($this->service->isSuperAdmin(99999));
    }

    // ==========================================
    // Role CRUD
    // ==========================================

    #[Test]
    public function getAllRoles_returns_all_roles(): void
    {
        $roles = $this->service->getAllRoles();

        $this->assertCount(9, $roles);
        $this->assertSame('super_admin', $roles[0]['slug']);
    }

    #[Test]
    public function getAllRoles_filters_active_only(): void
    {
        // All test roles are active, so count should be same
        $roles = $this->service->getAllRoles(activeOnly: true);

        $this->assertCount(9, $roles);
    }

    #[Test]
    public function getRole_returns_role_by_id(): void
    {
        $role = $this->service->getRole(1);

        $this->assertNotNull($role);
        $this->assertSame('super_admin', $role['slug']);
    }

    #[Test]
    public function getRole_returns_null_for_nonexistent_id(): void
    {
        $role = $this->service->getRole(99999);

        $this->assertNull($role);
    }

    #[Test]
    public function createRole_creates_and_returns_id(): void
    {
        $id = $this->service->createRole(
            'test_role',
            'Test Role',
            'A role for testing',
            isSystem: false,
            sortOrder: 50
        );

        $this->assertGreaterThan(0, $id);
        $role = $this->service->getRole($id);
        $this->assertNotNull($role);
        $this->assertSame('test_role', $role['slug']);
        $this->assertSame('Test Role', $role['name']);
    }

    #[Test]
    public function updateRole_updates_fields(): void
    {
        // Create then update
        $id = $this->service->createRole('updatable', 'Updatable Role');

        $this->service->updateRole(
            roleId: $id,
            name: 'Updated Name',
            description: 'Updated description',
            sortOrder: 99,
            isActive: true
        );

        $role = $this->service->getRole($id);
        $this->assertSame('Updated Name', $role['name']);
        $this->assertSame('Updated description', $role['description']);
        $this->assertSame(99, (int) $role['sort_order']);
    }

    #[Test]
    public function deleteRole_deletes_non_system_role(): void
    {
        $id = $this->service->createRole('deletable', 'Deletable Role');

        $result = $this->service->deleteRole($id);

        $this->assertTrue($result);
        $this->assertNull($this->service->getRole($id));
    }

    #[Test]
    public function deleteRole_returns_false_for_system_role(): void
    {
        // super_admin is a system role (id=1)
        $result = $this->service->deleteRole(1);

        $this->assertFalse($result);
        $this->assertNotNull($this->service->getRole(1));
    }

    #[Test]
    public function getRoleUserCount_returns_correct_count(): void
    {
        // super_admin (id=1) has 1 user assigned
        $count = $this->service->getRoleUserCount(1);

        $this->assertEquals(1, $count);
    }

    // ==========================================
    // Permission CRUD
    // ==========================================

    #[Test]
    public function getAllPermissions_returns_all(): void
    {
        $permissions = $this->service->getAllPermissions();

        $this->assertCount(18, $permissions);
    }

    #[Test]
    public function getAllPermissions_filters_by_module(): void
    {
        $permissions = $this->service->getAllPermissions(module: 'donations');

        $this->assertCount(5, $permissions);
        foreach ($permissions as $p) {
            $this->assertSame('donations', $p['module']);
        }
    }

    #[Test]
    public function getPermissionsGrouped_returns_grouped(): void
    {
        $grouped = $this->service->getPermissionsGrouped();

        $this->assertArrayHasKey('dashboard', $grouped);
        $this->assertArrayHasKey('donations', $grouped);
        $this->assertArrayHasKey('blogs', $grouped);
        $this->assertCount(1, $grouped['dashboard']);
        $this->assertCount(5, $grouped['donations']);
    }

    #[Test]
    public function getPermission_returns_permission_by_id(): void
    {
        $perm = $this->service->getPermission(1);

        $this->assertNotNull($perm);
        $this->assertSame('dashboard.view', $perm['slug']);
    }

    #[Test]
    public function getPermission_returns_null_for_nonexistent(): void
    {
        $perm = $this->service->getPermission(99999);

        $this->assertNull($perm);
    }

    #[Test]
    public function getPermissionsBySlug_returns_matching(): void
    {
        $perms = $this->service->getPermissionsBySlug('dashboard.view', 'donations.view');

        $this->assertCount(2, $perms);
        $slugs = array_column($perms, 'slug');
        $this->assertContains('dashboard.view', $slugs);
        $this->assertContains('donations.view', $slugs);
    }

    #[Test]
    public function getPermissionsBySlug_returns_empty_for_no_args(): void
    {
        $perms = $this->service->getPermissionsBySlug();

        $this->assertEmpty($perms);
    }

    // ==========================================
    // Role-Permission Assignment
    // ==========================================

    #[Test]
    public function getRolePermissionIds_returns_assigned_ids(): void
    {
        // temple_admin (id=2) should have many permissions
        $ids = $this->service->getRolePermissionIds(2);

        $this->assertGreaterThan(10, count($ids));
    }

    #[Test]
    public function setRolePermissions_replaces_all(): void
    {
        $roleId = 2; // temple_admin
        $permId = 1; // dashboard.view

        // Replace all temple_admin permissions with just dashboard.view
        $this->service->setRolePermissions($roleId, [$permId]);

        $newIds = $this->service->getRolePermissionIds($roleId);
        $this->assertCount(1, $newIds);
        $this->assertSame($permId, (int) $newIds[0]);
    }

    #[Test]
    public function setRolePermissions_clears_all_with_empty_array(): void
    {
        $roleId = 2; // temple_admin

        $this->service->setRolePermissions($roleId, []);

        $newIds = $this->service->getRolePermissionIds($roleId);
        $this->assertEmpty($newIds);
    }

    #[Test]
    public function getRolePermissionsWithDetails_returns_full_permissions(): void
    {
        $perms = $this->service->getRolePermissionsWithDetails(2);

        $this->assertGreaterThan(10, count($perms));
        $this->assertArrayHasKey('slug', $perms[0]);
        $this->assertArrayHasKey('module', $perms[0]);
        $this->assertArrayHasKey('action', $perms[0]);
    }

    // ==========================================
    // User-Role Assignment
    // ==========================================

    #[Test]
    public function getAdminRoles_returns_assigned_roles(): void
    {
        $adminId = $this->adminIds['donation_user'];
        $roles = $this->service->getAdminRoles($adminId);

        $this->assertCount(1, $roles);
        $this->assertSame('donation_manager', $roles[0]['slug']);
    }

    #[Test]
    public function getAdminRoleIds_returns_assigned_role_ids(): void
    {
        $adminId = $this->adminIds['donation_user'];
        $ids = $this->service->getAdminRoleIds($adminId);

        $this->assertCount(1, $ids);
    }

    #[Test]
    public function assignRoles_replaces_all_roles(): void
    {
        $adminId = $this->adminIds['donation_user'];

        // Assign donation_user to both donation_manager (3) and report_viewer (7)
        $this->service->assignRoles($adminId, [3, 7]);

        $roles = $this->service->getAdminRoles($adminId);
        $this->assertCount(2, $roles);
        $roleSlugs = array_column($roles, 'slug');
        $this->assertContains('donation_manager', $roleSlugs);
        $this->assertContains('report_viewer', $roleSlugs);
    }

    #[Test]
    public function assignRoles_with_assigned_by_records_assigner(): void
    {
        $adminId = $this->adminIds['donation_user'];
        $assignerId = $this->adminIds['super_admin'];

        $this->service->assignRoles($adminId, [3], assignedBy: $assignerId);

        // Verify via DB query
        $db = RbacTestHelper::createDb();
        $stmt = $db->prepare(
            'SELECT assigned_by FROM rbac_user_roles WHERE admin_id = ?'
        );
        $stmt->execute([$adminId]);
        $assignedBy = $stmt->fetchColumn();

        $this->assertEquals($assignerId, (int) $assignedBy);
    }

    #[Test]
    public function assignRoles_clears_all_with_empty_array(): void
    {
        $adminId = $this->adminIds['donation_user'];

        $this->service->assignRoles($adminId, []);

        $roles = $this->service->getAdminRoles($adminId);
        $this->assertEmpty($roles);
    }

    // ==========================================
    // Utility Methods
    // ==========================================

    #[Test]
    public function getRoleSlug_returns_slug_by_id(): void
    {
        $slug = $this->service->getRoleSlug(1);

        $this->assertSame('super_admin', $slug);
    }

    #[Test]
    public function getRoleSlug_returns_null_for_nonexistent(): void
    {
        $slug = $this->service->getRoleSlug(99999);

        $this->assertNull($slug);
    }

    #[Test]
    public function getRoleIdBySlug_returns_id(): void
    {
        $id = $this->service->getRoleIdBySlug('super_admin');

        $this->assertSame(1, $id);
    }

    #[Test]
    public function getRoleIdBySlug_returns_null_for_nonexistent(): void
    {
        $id = $this->service->getRoleIdBySlug('nonexistent_role');

        $this->assertNull($id);
    }

    #[Test]
    public function getPermissionIdBySlug_returns_id(): void
    {
        $id = $this->service->getPermissionIdBySlug('dashboard.view');

        $this->assertSame(1, $id);
    }

    #[Test]
    public function getPermissionIdBySlug_returns_null_for_nonexistent(): void
    {
        $id = $this->service->getPermissionIdBySlug('nonexistent.perm');

        $this->assertNull($id);
    }

    // ==========================================
    // Edge Cases
    // ==========================================

    #[Test]
    public function multiple_role_union_grants_all_permissions(): void
    {
        // Donation user has donation_manager role (= donations CRUD + reports)
        // If we also give them festival_manager, they should get festival permissions too
        $adminId = $this->adminIds['donation_user'];

        $this->service->assignRoles($adminId, [3, 4]); // donation_manager + festival_manager

        $this->assertTrue($this->service->hasPermission($adminId, 'donations.create'));
        $this->assertTrue($this->service->hasPermission($adminId, 'festivals.create'));
        $this->assertTrue($this->service->hasPermission($adminId, 'festivals.delete'));
    }

    #[Test]
    public function removing_role_revokes_permissions(): void
    {
        $adminId = $this->adminIds['donation_user'];

        // First verify they have donation permissions
        $this->assertTrue($this->service->hasPermission($adminId, 'donations.view'));

        // Remove all roles
        $this->service->assignRoles($adminId, []);

        // Now they should have no permissions
        $this->assertFalse($this->service->hasPermission($adminId, 'donations.view'));
    }
}
