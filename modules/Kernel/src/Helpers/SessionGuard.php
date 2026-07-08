<?php

namespace Isjm\Helpers;

/**
 * Session-based authentication guard for the admin panel.
 * 
 * Handles: session start, CSRF token, login check, role-based access control,
 * and permission-based access control (RBAC).
 * 
 * Usage:
 *   $guard = new SessionGuard();
 *   $guard->requireLogin();                    // Redirects to login if not authenticated
 *   $guard->requireRole(['editor']);            // Shows 403 if role not allowed
 *   $guard->hasRole('super_admin');             // Returns bool
 *   $guard->requirePermission('donations.view');// Shows 403 if permission not granted
 *   $guard->hasPermission('donations.view');    // Returns bool
 */
class SessionGuard
{
    private bool $initialized = false;

    /**
     * Ensure session is started and CSRF token exists.
     */
    public function init(): void
    {
        if ($this->initialized) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->initialized = true;
    }

    /**
     * Redirect to login page if admin is not authenticated.
     */
    public function requireLogin(): void
    {
        $this->init();

        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: ' . BASE_URL . 'admin/login');
            exit;
        }

        // Self-healing: load role from DB if missing
        if (!isset($_SESSION['admin_role'])) {
            $this->loadRoleFromDb();
        }

        // Load permissions into session if not loaded (RBAC)
        if (!isset($_SESSION['admin_permissions'])) {
            $this->loadPermissions();
        }
    }

    /**
     * Check if current admin has any of the allowed roles.
     * Super_admin always returns true.
     */
    public function hasRole(array $allowedRoles): bool
    {
        $roleString = $_SESSION['admin_role'] ?? 'editor';
        $roles = array_map('trim', explode(',', $roleString));
        if (in_array('super_admin', $roles)) {
            return true;
        }
        return !empty(array_intersect($roles, $allowedRoles));
    }

    /**
     * Block access if current admin doesn't have any of the allowed roles.
     * Renders a 403 page and exits.
     */
    public function requireRole(array $allowedRoles): void
    {
        if (!$this->hasRole($allowedRoles)) {
            http_response_code(403);
            echo $this->render403();
            exit;
        }
    }

    // ==========================================
    // PERMISSION-BASED CHECKS (RBAC)
    // ==========================================

    /**
     * Check if current admin has a specific permission.
     * Super Admin always returns true.
     * Uses session cache for performance.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Super admin bypass
        $roleString = $_SESSION['admin_role'] ?? '';
        $roles = array_map('trim', explode(',', $roleString));
        if (in_array('super_admin', $roles)) {
            return true;
        }

        // Check session cache
        $permissions = $_SESSION['admin_permissions'] ?? [];
        return in_array($permissionSlug, $permissions);
    }

    /**
     * Check if current admin has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionSlugs): bool
    {
        // Super admin bypass
        $roleString = $_SESSION['admin_role'] ?? '';
        $roles = array_map('trim', explode(',', $roleString));
        if (in_array('super_admin', $roles)) {
            return true;
        }

        $permissions = $_SESSION['admin_permissions'] ?? [];
        return !empty(array_intersect($permissionSlugs, $permissions));
    }

    /**
     * Block access if current admin doesn't have the specified permission.
     * Renders a 403 page and exits.
     */
    public function requirePermission(string $permissionSlug): void
    {
        if (!$this->hasPermission($permissionSlug)) {
            http_response_code(403);
            echo $this->render403Permission($permissionSlug);
            exit;
        }
    }

    /**
     * Block access if current admin doesn't have ANY of the given permissions.
     * Renders a 403 page and exits.
     */
    public function requireAnyPermission(array $permissionSlugs): void
    {
        if (!$this->hasAnyPermission($permissionSlugs)) {
            $permName = htmlspecialchars(ucwords(str_replace(['.', '_'], ' ', implode(', ', $permissionSlugs))));
            http_response_code(403);
            echo $this->render403Permission($permName);
            exit;
        }
    }

    /**
     * Get current admin's role string.
     */
    public function getRole(): string
    {
        return $_SESSION['admin_role'] ?? 'editor';
    }

    /**
     * Get current admin's ID.
     */
    public function getAdminId(): ?int
    {
        return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
    }

    /**
     * Load permissions from RBAC tables into session.
     * Gracefully handles when RBAC tables don't exist yet.
     */
    public function loadPermissions(): void
    {
        $adminId = $this->getAdminId();
        if (!$adminId) {
            $_SESSION['admin_permissions'] = [];
            return;
        }

        try {
            $db = getDB();
            $stmt = $db->query("SHOW TABLES LIKE 'rbac_user_roles'");
            if ($stmt->fetch()) {
                $rbac = new \Isjm\Modules\RBAC\RbacService();
                $rbac->loadPermissionsIntoSession($adminId);
                return;
            }
        } catch (\PDOException $e) {
            // Tables don't exist yet
        } catch (\Exception $e) {
            // Class not autoloaded yet
        }

        $_SESSION['admin_permissions'] = [];
    }

    /**
     * Load role from database if not in session (self-healing for pre-migration users).
     */
    private function loadRoleFromDb(): void
    {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT role FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $_SESSION['admin_role'] = $stmt->fetchColumn() ?: 'editor';
        } catch (\PDOException $e) {
            $_SESSION['admin_role'] = 'editor';
        }
    }

    /**
     * Render the 403 access-denied page for role checks.
     */
    private function render403(): string
    {
        $role = htmlspecialchars(ucwords(str_replace('_', ' ', $this->getRole())));
        $returnUrl = BASE_URL . 'admin/dashboard';

        return <<<HTML
        <div style="text-align:center; padding: var(--space-4xl) 0;">
          <div style="font-size: 64px; color: var(--maroon); margin-bottom: var(--space-md);">
            <i class="fas fa-shield-halved"></i>
          </div>
          <h1 style="font-family:var(--font-heading); color:var(--dark); margin-bottom:var(--space-sm);">Access Denied</h1>
          <p style="color:var(--text-light); max-width:500px; margin: 0 auto var(--space-lg) auto; line-height:1.6; font-size:var(--font-size-sm);">
            Your account role (<strong>{$role}</strong>) does not have permission to access this page. Please contact a Super Administrator if you require elevated privileges.
          </p>
          <a href="{$returnUrl}" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 20px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; color:var(--text); font-size:13px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Return to Dashboard
          </a>
        </div>
        HTML;
    }

    /**
     * Render the 403 access-denied page for permission checks.
     */
    private function render403Permission(string $permissionSlug): string
    {
        $permName = htmlspecialchars(ucwords(str_replace(['.', '_'], ' ', $permissionSlug)));
        $returnUrl = BASE_URL . 'admin/dashboard';

        return <<<HTML
        <div style="text-align:center; padding: var(--space-4xl) 0;">
          <div style="font-size: 64px; color: var(--maroon); margin-bottom: var(--space-md);">
            <i class="fas fa-shield-halved"></i>
          </div>
          <h1 style="font-family:var(--font-heading); color:var(--dark); margin-bottom:var(--space-sm);">Permission Denied</h1>
          <p style="color:var(--text-light); max-width:500px; margin: 0 auto var(--space-lg) auto; line-height:1.6; font-size:var(--font-size-sm);">
            You do not have the <strong>{$permName}</strong> permission. Please contact a Super Administrator if you require elevated privileges.
          </p>
          <a href="{$returnUrl}" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 20px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; color:var(--text); font-size:13px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Return to Dashboard
          </a>
        </div>
        HTML;
    }
}
