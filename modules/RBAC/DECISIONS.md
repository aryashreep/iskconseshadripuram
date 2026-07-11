# RBAC Module — Architecture Decisions

> **Last updated:** 2026-07-11
> **Related:** `README.md` (module overview), `SECURITY.md` (authorization section), `docs/AUTHORIZATION_MATRIX.md`, `docs/ADMIN.md`, `Kernel/DECISIONS.md` (RBAC upgrade history)

---

## [2026-07-07] Permission-Based Access Control (Not Role-Based)

### Decision
Implement granular permission-based access control using `module.action` format (55 permissions across 13 modules), rather than coarse role-based checks. Permissions are data-driven via database tables, not hardcoded in PHP.

### Context
The original system had 5 hardcoded roles (super_admin, editor, pujari, treasurer, travel_agent) checked via `requireRole()` scattered across 34+ files. As the admin panel grew, these roles became too coarse. A treasurer needed view-only access without export capabilities; an editor needed blog access without festival access. Adding new combinations required PHP code changes.

### Options Considered
- **Keep hardcoded roles**: 5 roles, no flexibility — insufficient granularity
- **Permission-based (chosen)**: 55 granular permissions in `module.action` format, data-driven
- **Bitfield permissions**: Store permissions as bits in a BIGINT — limited to 64 permissions, not human-readable

### Rationale
Data-driven permissions enable role management through the admin UI without code changes. The `module.action` format is human-readable and consistent. 55 permissions across 13 modules provides the right level of granularity without over-engineering. See `rbac-spec.md` for the full design document.

### Trade-offs
- **More database queries**: Permission checks require JOIN queries on every page load — mitigated by session caching
- **Permission management overhead**: Setting up permissions for new roles requires UI interaction
- **Legacy migration**: All `requireRole()` calls had to be converted to `requirePermission()` equivalents

### Related Files
- `modules/RBAC/RbacService.php` — Permission checking logic
- `modules/RBAC/PermissionRegistry.php` — 55 permission definitions
- `modules/RBAC/database/migrations/` — Schema and seed data

---

## [2026-07-07] Super Admin Implicit Bypass

### Decision
Super Admin has implicit permission bypass — the system returns `true` for all permission checks without consulting the database.

### Context
Super Administrators need unrestricted access for emergencies, system configuration, and user management. Explicitly assigning all 55 permissions to super_admin would be redundant and error-prone (if new permissions are added, super_admin would need updating).

### Options Considered
- **Explicit permission assignment**: Assign all permissions like any other role — must keep in sync
- **Implicit bypass (chosen)**: Hardcoded check in `RbacService::hasPermission()` — always returns `true`
- **Special role flag**: `is_super = 1` on roles table — could be bypassed by removing the flag

### Rationale
The implicit bypass is a simple, reliable mechanism that guarantees super_admin always has access. The check is the first line in `hasPermission()`:

```php
if ($this->isSuperAdmin($adminId)) {
    return true;
}
```

This prevents accidental lockout and simplifies permission management.

### Trade-offs
- **No granular restrictions**: Cannot partially restrict a super_admin's access for specific tasks
- **Hardcoded check**: Not configurable through the UI — requires code change to modify
- **Implicit trust**: A compromised super_admin account has unrestricted damage potential

### Related Files
- `modules/RBAC/RbacService.php` — `hasPermission()`, `isSuperAdmin()` methods
- `modules/Kernel/src/Helpers/SessionGuard.php` — `sessionHasPermission()` uses role list check

---

## [2026-07-07] Session-Cached Permissions (Not Per-Request DB Queries)

### Decision
Admin permissions are loaded into `$_SESSION['admin_permissions']` on page load (via `auth-check.php`), then checked in-memory for the remainder of the request. Permission changes take effect on next page load.

### Context
A single admin page may check permissions multiple times (sidebar menu items, action buttons, page access gate). Each check hitting the database would add N queries per page load (where N can be 20+ for complex dashboards). On shared hosting with limited connections, this is wasteful.

### Options Considered
- **Per-request DB queries**: Fresh permission check for every `hasPermission()` call — accurate but slow
- **Session caching (chosen)**: Load once into session, check in-memory — fast but slightly stale
- **APCu caching**: Shared memory cache — not available on most shared hosting plans

### Rationale
Session caching provides fast in-memory permission checks (array lookup) for the entire request. The staleness window is minimal — permission changes take effect on the next page load (which is the same behavior as session-based role storage). For a temple website with < 50 admin users, this is more than sufficient.

### Trade-offs
- **Stale permissions**: If roles change while an admin is active, they won't see changes until next page load
- **Session size**: 55+ permission strings in session per admin — negligible memory impact (~2KB)
- **Cache invalidation**: No mechanism to invalidate session cache without user re-login

### Related Files
- `modules/Kernel/Admin/auth-check.php` — Loads permissions into session
- `modules/RBAC/RbacService.php` — `loadPermissionsIntoSession()` method
- `modules/RBAC/RbacService.php` — `sessionHasPermission()` static method

---

## [2026-07-07] No Audit Logging (Future Placeholder)

### Decision
The `audit_logs` module exists as a permission placeholder with `view` and `export` actions, but no audit logging infrastructure is implemented. Permission changes, admin actions, and security events are not centrally logged.

### Context
The initial RBAC implementation scope excluded audit logging to keep the project manageable and avoid database bloat. The permission module exists so that when audit logging is implemented, permission checks are already in place to restrict access.

### Options Considered
- **Full audit logging**: Log every permission check, role change, and sensitive action — significant storage and performance impact
- **Selective logging**: Log only critical events (role changes, admin creation/deletion)
- **No logging (chosen for now)**: No centralized audit log — relies on fragmented error and payment logs

### Rationale
The decision to defer audit logging was pragmatic. The `audit_logs` permission module is reserved for future implementation. See `SECURITY.md` (A09 section) and `docs/AUDIT_LOGGING.md` for the recommended audit schema and event list.

### Trade-offs
- **No audit trail**: Cannot investigate "who changed what and when"
- **Fragmented logs**: Security-relevant information is scattered across error logs, payment logs, and database tables
- **Compliance risk**: May not meet financial audit requirements for donation tracking

### Related Files
- `modules/RBAC/PermissionRegistry.php` — `audit_logs` module definition
- `docs/AUDIT_LOGGING.md` — Recommended audit schema
- `SECURITY.md` — A09 section on logging gaps

---

## [2026-07-07] Union-Based Permission Resolution (Not Hierarchical)

### Decision
When an admin has multiple roles, permissions are resolved via UNION (OR logic) — if ANY role grants a permission, the admin has it. No role inheritance or hierarchy is implemented.

### Context
Admins can be assigned multiple roles (e.g., both `donation_manager` and `report_viewer`). The system needs to determine the effective permissions. Union-based resolution means the admin has permissions from ALL assigned roles combined.

### Options Considered
- **Union/OR (chosen)**: Admin has permission if any role grants it — simple, permissive
- **Intersection/AND**: Admin has permission only if ALL roles grant it — too restrictive for multi-role users
- **Hierarchical inheritance**: Roles can inherit from parent roles — more complex to manage

### Rationale
Union resolution is the simplest and most intuitive model. It matches how multi-role systems typically work — giving someone the `donation_manager` role and the `report_viewer` role means they can both manage donations AND view reports. Intersection would mean they can only do what both roles allow (potentially nothing).

### Trade-offs
- **Permission accumulation**: Adding more roles only adds permissions, never removes them
- **No negation**: Cannot say "this role grants everything EXCEPT delete" — must manage via role design
- **Role explosion risk**: May create many fine-grained roles instead of properly designing permission groups

### Related Files
- `modules/RBAC/RbacService.php` — `getAdminPermissions()` uses UNION-based SQL
- `modules/RBAC/database/migrations/002_seed_roles_and_permissions.php` — Role-permission matrix
