# Authorization Matrix — Permission Mapping

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `SECURITY.md` (authorization section), `modules/RBAC/README.md`, `docs/ADMIN.md`

---

## Permission Format

All permissions follow the pattern: `{module}.{action}`

**Modules (13):** dashboard, donations, festivals, seva_catalog, blogs, bookings, panihati, sudamaseva, reports, devotees, volunteers, events, audit_logs

**Actions (5 per module):** view, create, edit, delete, export

**Total:** 55 permissions

## Admin Pages → Required Permissions

| Admin Page | Required Permission | Notes |
|------------|-------------------|-------|
| `/admin/dashboard` | `dashboard.view` | |
| `/admin/blogs` | `blogs.view` | Edit/delete requires `blogs.edit`, `blogs.delete` |
| `/admin/blog-edit` | `blogs.create` or `blogs.edit` | |
| `/admin/seva-catalogue` | `seva_catalog.view` | Edit/delete requires `seva_catalog.edit`, `seva_catalog.delete` |
| `/admin/seva-catalogue-edit` | `seva_catalog.create` or `seva_catalog.edit` | |
| `/admin/festivals` | `festivals.view` | Edit/delete/toggle requires `festivals.edit` |
| `/admin/festival-edit` | `festivals.create` or `festivals.edit` | |
| `/admin/donations` | `donations.view` | Export requires `donations.export` |
| `/admin/report-dashboard` | `reports.view` | |
| `/admin/report-category` | `reports.view` | Export requires `reports.export` |
| `/admin/report-activity` | `reports.view` | Export requires `reports.export` |
| `/admin/report-seva` | `reports.view` | Export requires `reports.export` |
| `/admin/export-donations` | `donations.export` | |
| `/admin/export-report-*` | `reports.export` | |
| `/admin/bookings` | `bookings.view` | |
| `/admin/panihati-yatra` | `panihati.view` | |
| `/admin/panihati-records` | `panihati.view` | |
| `/admin/panihati-reports` | `panihati.view` | |
| `/admin/panihati-pricing` | `panihati.edit` | |
| `/admin/panihati-sadans` | `panihati.edit` | |
| `/admin/panihati-pickups` | `panihati.edit` | |
| `/admin/panihati-expenses` | `panihati.edit` | |
| `/admin/sudamaseva-dashboard` | `sudamaseva.view` | |
| `/admin/sudamaseva-donors` | `sudamaseva.view` | |
| `/admin/sudamaseva-payments` | `sudamaseva.view` | |
| `/admin/sudamaseva-receipts` | `sudamaseva.view` | |
| `/admin/admins` | `dashboard.view` (super_admin effective) | Only super_admin can access |
| `/admin/admin-edit` | `dashboard.view` (super_admin effective) | Only super_admin can access |
| `/admin/roles` | `dashboard.view` (super_admin effective) | Only super_admin can access |
| `/admin/role-edit` | `dashboard.view` (super_admin effective) | Only super_admin can access |
| `/admin/permissions` | `dashboard.view` (super_admin effective) | Only super_admin can access |

## Important Notes

- **Super Admin** bypasses all permission checks — the `dashboard.view` reference above is for code compatibility
- **Action button visibility** (Edit, Delete, Export) is controlled via `hasPermission()` checks in the page templates
- **Sidebar menu items** are shown/hidden via `hasPermission()` checks in `modules/Kernel/Admin/partials/header.php`
- **Legacy pages** that haven't been fully migrated may still use `hasRole()` checks — these should be converted to `hasPermission()` over time
