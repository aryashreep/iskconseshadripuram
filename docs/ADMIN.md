# ADMIN.md — Admin Panel

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** [`SECURITY.md`](../SECURITY.md) (authentication, authorization), [`MODULE_INDEX.md`](../MODULE_INDEX.md) (module entry points), [`docs/AUTHORIZATION_MATRIX.md`](AUTHORIZATION_MATRIX.md) (permission mapping), [`modules/RBAC/README.md`](../modules/RBAC/README.md), [`CODING_STANDARDS.md`](../CODING_STANDARDS.md) (admin page patterns)

## Access

- URL: `/admin/`
- Auth: Session-based via `admin/auth-check.php`
- Login: `/admin/login.php`

## RBAC Roles (Permission-Based)

The admin panel uses a granular Role-Based Access Control (RBAC) system. Roles are data-driven and managed through the admin UI.

| Role | Description |
|------|-------------|
| `super_admin` | Unrestricted access — implicit bypass of all permission checks |
| `temple_admin` | Full access to all operational modules (no user/role management) |
| `donation_manager` | Manage donations, causes, and related reporting |
| `festival_manager` | Manage festivals, events, and seva catalog |
| `accounts` | View financial data, reports, exports |
| `content_manager` | Manage blogs and website content |
| `report_viewer` | Read-only access to reports and dashboards |
| `devotee_care` | Manage devotee records |
| `volunteer_coordinator` | Manage volunteers |
| `event_coordinator` | Manage special events |
| `read_only` | View-only access across permitted modules |

Permissions use the format `module.action` (e.g., `donations.view`, `festivals.create`). See the [Permissions Reference](/admin/permissions) page for the complete catalog.

## Navigation Structure

```
Dashboard (requires: dashboard.view)
Manage Blogs (requires: blogs.view)
Seva Catalogue (requires: seva_catalog.view)
Manage Festivals (requires: festivals.view)
Donations (group)
  ├── Dashboard (charts)
  ├── Transaction Logs
  ├── Category Report
  ├── Activity Report
  └── Seva Report
Puja & Yagya Bookings (requires: bookings.view)
Panihati Yatra (group)
  ├── Dashboard
  ├── Registration Records
  ├── Download Reports
  ├── Add Offline Entry
  ├── Bhakti Sadans
  ├── Pickup Locations
  ├── Pricing
  └── Finance & Expenses
Sudamaseva (group)
  ├── Dashboard
  ├── Donors
  ├── Subscriptions
  ├── Payments
  └── Receipts
Role Management (super_admin only)
  ├── Manage Admins
  ├── Assign Roles
  ├── Roles
  └── Permissions
View Website
Logout
```

Menu items are shown/hidden based on `hasPermission()` checks — users only see pages they have access to.

## RBAC Module Location

All RBAC code lives in `modules/RBAC/`:

```
modules/RBAC/
├── Admin/
│   ├── roles.php              # Role listing & management
│   ├── role-edit.php          # Create/edit role + permission matrix
│   └── permissions.php        # Read-only permission reference
├── RbacService.php            # Core RBAC logic
├── PermissionRegistry.php     # Permission definitions
└── database/migrations/
    ├── 001_create_rbac_tables.php
    ├── 002_seed_roles_and_permissions.php
    └── 003_migrate_existing_admins.php
```

## Permission Checking API

Global helper functions available in all admin pages:

| Function | Purpose |
|----------|---------|
| `hasPermission('module.action')` | Boolean check — use for UI visibility |
| `hasAnyPermission(['perm1', 'perm2'])` | Boolean check — any of the given permissions |
| `requirePermission('module.action')` | Blocks with 403 if not granted |
| `requireAnyPermission(['perm1', 'perm2'])` | Blocks with 403 if none granted |

Super Admin bypasses all checks implicitly.

## Key Files

| File | Purpose |
|------|---------|
| `admin/auth-check.php` | Session validation, loads permissions into `$_SESSION` |
| `admin/partials/header.php` | Sidebar navigation with `hasPermission()` checks |
| `admin/partials/footer.php` | Closing HTML, scripts |
| `modules/Kernel/src/Helpers/SessionGuard.php` | Auth guard — `requireLogin()`, `hasPermission()`, `hasRole()` |
| `modules/RBAC/RbacService.php` | Core RBAC logic — permission checking, role CRUD |
| `modules/RBAC/PermissionRegistry.php` | Central registry of all 55 permission definitions |
| `admin/dashboard.php` | Role-specific dashboards |
| `admin/report-dashboard.php` | Donation dashboard with charts |
| `admin/roles.php` | RBAC role listing (super_admin only) |
| `admin/role-edit.php` | Create/edit roles with permission matrix (super_admin only) |
| `admin/permissions.php` | Read-only permission reference (super_admin only) |
| `admin/admins.php` | Admin user listing with RBAC role display |
| `admin/admin-edit.php` | Create/edit admin users with multi-role assignment |

## Pattern: Adding a New Admin Page

1. Create `admin/your-page.php`
2. Add `require_once __DIR__ . '/auth-check.php';` at top
3. Add `requirePermission('module.view');` for access control (replaces old `requireRole()`)
4. Set `$pageTitle` and `$activePage` variables
5. Include `partials/header.php` and `partials/footer.php`
6. Add navigation entry in `modules/Kernel/Admin/partials/header.php` under the appropriate group
