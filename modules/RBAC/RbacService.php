<?php

namespace Isjm\Modules\RBAC;

/**
 * RbacService — Core RBAC logic for the admin panel.
 * 
 * Handles permission checking, role/permission CRUD, and user-role assignments.
 * Designed to work with the existing SessionGuard system.
 */
class RbacService
{
    private \PDO $db;

    /**
     * @param \PDO|null $db Optional PDO instance for dependency injection (testing).
     *                       Defaults to getDB() for production use.
     */
    public function __construct(?\PDO $db = null)
    {
        $this->db = $db ?? getDB();
    }

    // ==========================================
    // PERMISSION CHECKING
    // ==========================================

    /**
     * Check if an admin has a specific permission.
     * Super Admin always returns true.
     */
    public function hasPermission(int $adminId, string $permissionSlug): bool
    {
        // Super admin bypass
        if ($this->isSuperAdmin($adminId)) {
            return true;
        }

        $sql = "SELECT COUNT(*)
                FROM rbac_user_roles ur
                JOIN rbac_role_permissions rp ON ur.role_id = rp.role_id
                JOIN rbac_permissions p ON rp.permission_id = p.id
                WHERE ur.admin_id = ? AND p.slug = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId, $permissionSlug]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if admin has ANY of the given permissions.
     */
    public function hasAnyPermission(int $adminId, array $permissionSlugs): bool
    {
        if ($this->isSuperAdmin($adminId)) {
            return true;
        }
        if (empty($permissionSlugs)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($permissionSlugs), '?'));
        $sql = "SELECT COUNT(*)
                FROM rbac_user_roles ur
                JOIN rbac_role_permissions rp ON ur.role_id = rp.role_id
                JOIN rbac_permissions p ON rp.permission_id = p.id
                WHERE ur.admin_id = ? AND p.slug IN ($placeholders)
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$adminId], $permissionSlugs));
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if admin has ALL of the given permissions.
     */
    public function hasAllPermissions(int $adminId, array $permissionSlugs): bool
    {
        if ($this->isSuperAdmin($adminId)) {
            return true;
        }
        if (empty($permissionSlugs)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($permissionSlugs), '?'));
        $sql = "SELECT COUNT(DISTINCT p.slug)
                FROM rbac_user_roles ur
                JOIN rbac_role_permissions rp ON ur.role_id = rp.role_id
                JOIN rbac_permissions p ON rp.permission_id = p.id
                WHERE ur.admin_id = ? AND p.slug IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge([$adminId], $permissionSlugs));
        return (int) $stmt->fetchColumn() === count($permissionSlugs);
    }

    /**
     * Get all permission slugs for an admin (union of all roles).
     * Results are cached in a flat array.
     */
    public function getAdminPermissions(int $adminId): array
    {
        // Super admin gets all permissions
        if ($this->isSuperAdmin($adminId)) {
            return array_keys(PermissionRegistry::getAllPermissions());
        }

        $sql = "SELECT DISTINCT p.slug
                FROM rbac_user_roles ur
                JOIN rbac_role_permissions rp ON ur.role_id = rp.role_id
                JOIN rbac_permissions p ON rp.permission_id = p.id
                WHERE ur.admin_id = ?
                ORDER BY p.slug ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Load admin permissions into session for fast checking.
     */
    public function loadPermissionsIntoSession(int $adminId): void
    {
        $permissions = $this->getAdminPermissions($adminId);
        $_SESSION['admin_permissions'] = $permissions;
    }

    /**
     * Check if the current session has a permission.
     * Uses session cache — call loadPermissionsIntoSession() first.
     */
    public static function sessionHasPermission(string $permissionSlug): bool
    {
        $roles = $_SESSION['admin_role'] ?? '';
        $roleList = array_map('trim', explode(',', $roles));
        if (in_array('super_admin', $roleList)) {
            return true;
        }

        $permissions = $_SESSION['admin_permissions'] ?? [];
        return in_array($permissionSlug, $permissions);
    }

    /**
     * Check if any of the given permissions are in the session.
     */
    public static function sessionHasAnyPermission(array $permissionSlugs): bool
    {
        $roles = $_SESSION['admin_role'] ?? '';
        $roleList = array_map('trim', explode(',', $roles));
        if (in_array('super_admin', $roleList)) {
            return true;
        }

        $permissions = $_SESSION['admin_permissions'] ?? [];
        return !empty(array_intersect($permissionSlugs, $permissions));
    }

    // ==========================================
    // SUPER ADMIN CHECK
    // ==========================================

    /**
     * Check if admin has the super_admin role.
     */
    public function isSuperAdmin(int $adminId): bool
    {
        $sql = "SELECT COUNT(*)
                FROM rbac_user_roles ur
                JOIN rbac_roles r ON ur.role_id = r.id
                WHERE ur.admin_id = ? AND r.slug = 'super_admin'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ==========================================
    // ROLE CRUD
    // ==========================================

    /**
     * Get all roles.
     */
    public function getAllRoles(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM rbac_roles";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get a single role by ID.
     */
    public function getRole(int $roleId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM rbac_roles WHERE id = ?");
        $stmt->execute([$roleId]);
        $role = $stmt->fetch();
        return $role ?: null;
    }

    /**
     * Create a new role.
     */
    public function createRole(string $slug, string $name, string $description = null, bool $isSystem = false, int $sortOrder = 0): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO rbac_roles (slug, name, description, is_system, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$slug, $name, $description, $isSystem ? 1 : 0, $sortOrder]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a role.
     */
    public function updateRole(int $roleId, string $name, string $description = null, int $sortOrder = 0, bool $isActive = true): void
    {
        $stmt = $this->db->prepare("
            UPDATE rbac_roles SET name = ?, description = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $sortOrder, $isActive ? 1 : 0, $roleId]);
    }

    /**
     * Delete a role. System roles cannot be deleted.
     */
    public function deleteRole(int $roleId): bool
    {
        $role = $this->getRole($roleId);
        if (!$role || $role['is_system']) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM rbac_roles WHERE id = ? AND is_system = 0");
        $stmt->execute([$roleId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get user count for a role.
     */
    public function getRoleUserCount(int $roleId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rbac_user_roles WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return (int) $stmt->fetchColumn();
    }

    // ==========================================
    // PERMISSION CRUD
    // ==========================================

    /**
     * Get all permissions, optionally filtered by module.
     */
    public function getAllPermissions(string $module = null): array
    {
        $sql = "SELECT * FROM rbac_permissions";
        $params = [];
        if ($module) {
            $sql .= " WHERE module = ?";
            $params[] = $module;
        }
        $sql .= " ORDER BY sort_order ASC, module ASC, action ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get permissions grouped by module.
     */
    public function getPermissionsGrouped(): array
    {
        $permissions = $this->getAllPermissions();
        $grouped = [];
        foreach ($permissions as $p) {
            $grouped[$p['module']][] = $p;
        }
        return $grouped;
    }

    /**
     * Get a single permission by ID.
     */
    public function getPermission(int $permissionId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM rbac_permissions WHERE id = ?");
        $stmt->execute([$permissionId]);
        $perm = $stmt->fetch();
        return $perm ?: null;
    }

    /**
     * Get permissions by their slugs.
     */
    public function getPermissionsBySlug(string ...$slugs): array
    {
        if (empty($slugs)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $stmt = $this->db->prepare("SELECT * FROM rbac_permissions WHERE slug IN ($placeholders)");
        $stmt->execute($slugs);
        return $stmt->fetchAll();
    }

    // ==========================================
    // ROLE-PERMISSION ASSIGNMENT
    // ==========================================

    /**
     * Get all permission IDs assigned to a role.
     */
    public function getRolePermissionIds(int $roleId): array
    {
        $stmt = $this->db->prepare("
            SELECT permission_id FROM rbac_role_permissions WHERE role_id = ?
        ");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Set permissions for a role (replaces all existing).
     */
    public function setRolePermissions(int $roleId, array $permissionIds): void
    {
        $this->db->beginTransaction();
        try {
            // Remove existing
            $stmt = $this->db->prepare("DELETE FROM rbac_role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);

            // Insert new
            if (!empty($permissionIds)) {
                $stmt = $this->db->prepare("
                    INSERT INTO rbac_role_permissions (role_id, permission_id) VALUES (?, ?)
                ");
                foreach ($permissionIds as $permId) {
                    $stmt->execute([$roleId, (int) $permId]);
                }
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get all permissions for a role with full details.
     */
    public function getRolePermissionsWithDetails(int $roleId): array
    {
        $sql = "SELECT p.*
                FROM rbac_role_permissions rp
                JOIN rbac_permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = ?
                ORDER BY p.sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    // ==========================================
    // USER-ROLE ASSIGNMENT
    // ==========================================

    /**
     * Get roles assigned to an admin.
     */
    public function getAdminRoles(int $adminId): array
    {
        $sql = "SELECT r.*
                FROM rbac_user_roles ur
                JOIN rbac_roles r ON ur.role_id = r.id
                WHERE ur.admin_id = ?
                ORDER BY r.sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$adminId]);
        return $stmt->fetchAll();
    }

    /**
     * Get role IDs assigned to an admin.
     */
    public function getAdminRoleIds(int $adminId): array
    {
        $stmt = $this->db->prepare("SELECT role_id FROM rbac_user_roles WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Assign roles to an admin (replaces all existing).
     */
    public function assignRoles(int $adminId, array $roleIds, ?int $assignedBy = null): void
    {
        $this->db->beginTransaction();
        try {
            // Remove existing
            $stmt = $this->db->prepare("DELETE FROM rbac_user_roles WHERE admin_id = ?");
            $stmt->execute([$adminId]);

            // Insert new
            if (!empty($roleIds)) {
                $stmt = $this->db->prepare("
                    INSERT INTO rbac_user_roles (admin_id, role_id, assigned_by) VALUES (?, ?, ?)
                ");
                foreach ($roleIds as $roleId) {
                    $stmt->execute([$adminId, (int) $roleId, $assignedBy]);
                }
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ==========================================
    // UTILITY
    // ==========================================

    /**
     * Get the role slug for a given role ID.
     */
    public function getRoleSlug(int $roleId): ?string
    {
        $role = $this->getRole($roleId);
        return $role ? $role['slug'] : null;
    }

    /**
     * Get role ID by slug.
     */
    public function getRoleIdBySlug(string $slug): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM rbac_roles WHERE slug = ?");
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    /**
     * Get permission ID by slug.
     */
    public function getPermissionIdBySlug(string $slug): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM rbac_permissions WHERE slug = ?");
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }
}
