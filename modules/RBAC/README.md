# Module: RBAC (Role-Based Access Control)

## Purpose
Provides granular role-based access control for the admin panel. Supports 11 roles, 55 permissions across 13 modules, with a permission matrix UI for role management and multi-role assignment for admin users.

## Owned Database Tables
- `rbac_roles` — 11 role definitions (data-driven, managed via UI)
- `rbac_permissions` — 55 permission definitions (`module.action` format)
- `rbac_role_permissions` — Many-to-many: role ↔ permission assignments
- `rbac_user_roles` — Many-to-many: admin ↔ role assignments (replaces `admins.role` column)

## Dependencies
- **Kernel** — DB connection (`getDB()`)

## Key Classes
| Class | File | Purpose |
|-------|------|---------|
| `RbacService` | `RbacService.php` | Core RBAC — permission checking, CRUD for roles/permissions |
| `PermissionRegistry` | `PermissionRegistry.php` | Registry of all 55 permission definitions |

## Admin Pages (super_admin only)
- `/admin/roles` — Role listing with user counts, edit/delete
- `/admin/role-edit` — Create/edit role with permission matrix
- `/admin/permissions` — Read-only permission reference

## Permission Checking API
See also: `SECURITY.md` (authorization section), `docs/ADMIN.md`

```php
// Check if current admin has a permission
hasPermission('donations.view')

// Block access if not granted (403)
requirePermission('donations.edit')

// Check any of the given permissions
hasAnyPermission(['donations.view', 'reports.view'])

// Block if none granted
requireAnyPermission(['panihati.view', 'panihati.edit'])
```

## Super Admin Bypass
Super Admin implicitly has all permissions — no explicit assignment needed:
```php
if (in_array('super_admin', $userRoles)) {
    return true; // Always allowed
}
```

## Testing
- 74 PHPUnit tests, 505 assertions
- In-memory SQLite for full isolation
- Test classes: `PermissionRegistryTest` (~14 tests), `RbacServiceTest` (~55 tests)
- Helper: `RbacTestHelper` (seeds 9 roles, 18 permissions, 7 admins)

## Migrations
```bash
php modules/RBAC/database/migrations/001_create_rbac_tables.php
php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php
php modules/RBAC/database/migrations/003_migrate_existing_admins.php
```
