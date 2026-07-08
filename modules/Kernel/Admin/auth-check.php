<?php
/**
 * Admin Authentication Gate & Role/Permission-Based Access Control (RBAC)
 * 
 * Include this at the top of every admin page to ensure only logged-in admins can access.
 * 
 * Provides:
 *   - hasRole(array $allowedRoles): bool       — check if current admin has any of the roles
 *   - requireRole(array $allowedRoles)          — block with 403 if role not allowed
 *   - hasPermission(string $slug): bool         — check if current admin has a specific permission
 *   - requirePermission(string $slug)           — block with 403 if permission not granted
 *   - hasAnyPermission(array $slugs): bool      — check if admin has any of the listed permissions
 */

require_once __DIR__ . '/../includes/bootstrap.php';

use Isjm\Helpers\SessionGuard;

$guard = new SessionGuard();
$guard->requireLogin();

/**
 * Check if the logged-in admin has any of the allowed roles.
 * Super Admin always has full access (returns true).
 * 
 * @param array $allowedRoles Array of role names, e.g. ['editor', 'pujari']
 * @return bool
 */
function hasRole(array $allowedRoles): bool
{
    global $guard;
    return $guard->hasRole($allowedRoles);
}

/**
 * Enforce role-based access control. If the current admin does not have any of the
 * allowed roles, blocks the request and shows an Access Denied message.
 * 
 * @param array $allowedRoles Array of role names
 */
function requireRole(array $allowedRoles)
{
    global $guard;
    $guard->requireRole($allowedRoles);
}

/**
 * Check if the logged-in admin has a specific permission.
 * Super Admin always has full access (returns true).
 * Uses the session-cached permission list for performance.
 * 
 * @param string $permissionSlug e.g. 'donations.view', 'festivals.create'
 * @return bool
 */
function hasPermission(string $permissionSlug): bool
{
    global $guard;
    return $guard->hasPermission($permissionSlug);
}

/**
 * Enforce permission-based access control. If the current admin does not have the
 * specified permission, blocks the request and shows a Permission Denied message.
 * 
 * @param string $permissionSlug e.g. 'donations.view'
 */
function requirePermission(string $permissionSlug)
{
    global $guard;
    $guard->requirePermission($permissionSlug);
}

/**
 * Check if the logged-in admin has ANY of the given permissions.
 * Useful for UI visibility checks where multiple permissions grant access.
 * 
 * @param array $permissionSlugs Array of permission slugs
 * @return bool
 */
function hasAnyPermission(array $permissionSlugs): bool
{
    global $guard;
    return $guard->hasAnyPermission($permissionSlugs);
}

/**
 * Enforce permission-based access control where any one of the given permissions
 * is sufficient. Blocks with 403 if none of the permissions are granted.
 * 
 * @param array $permissionSlugs Array of permission slugs
 */
function requireAnyPermission(array $permissionSlugs)
{
    global $guard;
    $guard->requireAnyPermission($permissionSlugs);
}
