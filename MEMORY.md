# ISJM Project Memory

## Project Overview
ISKCON Sri Jagannath Mandir (ISJM) — PHP-based temple website for ISKCON Seshadripuram, Bangalore. Donations, sevas, puja bookings, festival listings, yatra registration, and admin panel.

## Tech Stack
- **Language**: PHP 8 (vanilla, no framework)
- **Database**: MySQL via PDO
- **Charts**: Chart.js (dashboard visualizations)
- **Payments**: Razorpay (test + live)
- **Server**: Laragon (local), cPanel/Apache (production)
- **Auth**: RBAC with 11 roles, 55 permissions across 13 modules
- **Testing**: Playwright (172 E2E) + PHPUnit (74 unit, 505 assertions)
- **Autoloader**: Composer PSR-4 (`Isjm\` → `modules/Kernel/src/`, `Isjm\Modules\` → `modules/`)
- **URL**: http://isjm.test:8080

## Architecture — 8 Modules, ~201 Files

All original file paths preserved as backward-compatible wrappers. Actual code lives under `modules/`.

```
modules/
├── Donation/     — Payment system, reports, seva catalog (Phase 1)
├── Panihati/     — Yatra registration & admin (Phase 2)
├── Booking/      — Puja/yagya/guest house booking (Phase 3)
├── Festivals/    — 70+ public festival detail pages (Phase 4)
├── Blogs/        — Blog posts & admin (Phase 5)
├── Content/      — Static content: about, services, courses, yatra (Phase 6)
├── RBAC/         — Role-based access control, permission matrix UI (Phase 8)
└── Kernel/       — Shared infrastructure: config, partials, includes, src (Phase 7)
```

### Module Conventions
- `Admin/` — Admin management pages
- `api/` — REST endpoints
- `assets/` — CSS/JS
- `content/` — Public-facing pages
- `src/` — PHP classes

### Wrapper Pattern
```php
<?php
/**
 * Backward-compatibility wrapper.
 * File has been moved to modules/Kernel/partials/header.php.
 */
require_once __DIR__ . '/../modules/Kernel/partials/' . basename(__FILE__);
```
CWD-based includes (`include '../partials/header.php'`) continue to work because the CWD stays with the wrapper's directory.

### Composer Autoloading
```json
{
    "autoload": {
        "psr-4": {
            "Isjm\\": "modules/Kernel/src/",
            "Isjm\\Modules\\": "modules/"
        },
        "files": [
            "includes/db.php",
            "includes/donation-helpers.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Isjm\\Tests\\": "tests/"
        }
    }
}
```
- `Isjm\Donations\*` → `modules/Kernel/src/Donations/`
- `Isjm\Helpers\*` → `modules/Kernel/src/Helpers/`
- `Isjm\Modules\RBAC\*` → `modules/RBAC/`
- `Isjm\Tests\*` → `tests/` (dev only)

## Architecture Decisions

### RBAC System (2026-07-07)
- 11 data-driven roles managed through admin UI
- 55 permissions across 13 modules (`module.action` format)
- Permission matrix on role edit page with Select All per row/column
- Multi-role assignment on admin user edit
- Super Admin has implicit permission bypass
- Permissions cached in `$_SESSION['admin_permissions']` for fast in-memory checks
- Sidebar menu items shown/hidden via `hasPermission()` checks

### Admin Sidebar Accordion (2026-07-07)
- Collapsible nav groups (Donations, Panihati Yatra, Sudamaseva, Role Management)
- First group opens by default when no group is active
- Active group (current page) opens automatically on page load
- Proper accordion: clicking one group closes all others
- Parent link clicks prevent navigation (sub-items serve as navigation links)
- Uses CSS transitions (`max-height`, `opacity`) for smooth open/close animation

### PHPUnit Testing Framework (2026-07-07)
- 74 tests, 505 assertions
- In-memory SQLite database for full isolation (no MySQL needed)
- `RbacTestHelper` — seeds 9 roles, 18 permissions, 7 admin users
- Two test classes: `PermissionRegistryTest` (~17 tests) + `RbacServiceTest` (~55 tests)
- Covers: permission checking, CRUD, role assignment, super_admin bypass, edge cases

### Donation Reporting System (2026-07-05)
**Three-level hierarchy**: Category → Activity → Seva

**Pages**: `admin/report-dashboard.php`, `admin/report-category.php`, `admin/report-activity.php`, `admin/report-seva.php`
**CSV Exports**: 4 export files

**Dashboard visualizations** (Chart.js):
- Monthly trend (dual-axis: revenue ₹ + donation count)
- Revenue by category (doughnut)
- Top 10 activities (horizontal bar)
- Donation count by category (vertical bar)
- Day-of-week heatmap (CSS grid)
- Donor retention funnel (first-time vs returning)
- Year-over-year comparison (dual line)
- Category → Activity accordion

**Data model**: Queries aggregate `donation_transactions` joined through `donation_causes` (category/activity) and `master_sevas` (seva-level).

### Seva Catalogue System (2026-07-03)
- 10 `master_seva_categories` (Deity Sevas, Puja & Ritual, Festival, Rath Yatra, Prasadam, Infrastructure, Outreach, Devotee Care, Digital, General)
- 363+ `master_sevas` — deduplicated single source of truth
- `donation_cause_master_sevas` pivot with override support
- **Dual-Read Strategy**: new master catalog first, fallback to legacy `donation_cause_sevas`

### Payment Flow
1. Client → POST `/api/create-order.php` (amount verified server-side)
2. Razorpay checkout modal opens
3. Client → POST `/api/verify-payment.php` (HMAC signature check)
4. Server updates `donation_transactions.payment_status = 'paid'`
5. Webhook `/api/webhook.php` provides secondary verification

### Admin Auth
- Session-based via `admin/auth-check.php` → `modules/Kernel/Admin/auth-check.php`
- RBAC via `hasPermission()` / `requirePermission()` (replaces old `requireRole()`)
- Super Admin bypasses all permission checks implicitly
- Rate-limited login (5 attempts per 15-minute window, stored in `login_attempts` table)
- CSRF tokens on state-changing operations
- Session fixation prevention via `session_regenerate_id(true)`

## Database Tables

| Table | Purpose |
|-------|---------|
| `donation_causes` | Activities/festivals (74 rows, category field: festival, ekadashi, appearance, disappearance, event, service, construction, general) |
| `donation_transactions` | Payment records (cause_id, seva_id, master_seva_id, amount, donor info) |
| `master_seva_categories` | 10 top-level seva categories |
| `master_sevas` | Deduplicated seva offerings (linked to categories) |
| `donation_cause_master_sevas` | Pivot: cause ↔ seva with override support |
| `donation_cause_sevas` | Legacy per-cause seva table (still functional) |
| `donation_seva_categories` | Legacy seva category table |
| `rbac_roles` | RBAC role definitions (11 seeded roles) |
| `rbac_permissions` | Permission definitions (55 across 13 modules) |
| `rbac_role_permissions` | Role ↔ Permission assignments |
| `rbac_user_roles` | Admin ↔ Role assignments (replaces `admins.role` column) |
| `booking_pujas` | Puja/yagya bookings |
| `panihati_yatra_registrations` | Yatra registrations |
| `admins` | Admin users (legacy `role` column deprecated) |
| `page_views` | Page view tracking |
| `blogs` | Blog posts |
| `login_attempts` | Admin login audit / rate limiting |

## Key Patterns

### Migration Pattern
- Idempotent (safe to re-run)
- `require_once __DIR__ . '/../../config.php'` for DB access
- Check existence before inserting
- Transactions for multi-step operations

### Reporting Pattern
- PHP queries aggregate data, pass to JS as `json_encode()`
- Chart.js renders client-side
- CSV exports use `php://output` with UTF-8 BOM
- Accordion sections use CSS classes + JS toggle functions

### RBAC Pattern
- `hasPermission('module.action')` — Boolean check for UI visibility
- `requirePermission('module.action')` — Block with 403 if not granted
- `hasAnyPermission(['a', 'b'])` — Check if user has any of the listed permissions
- Super Admin returns `true` for all permission checks without DB query
- Permissions loaded into `$_SESSION` on `auth-check.php` include (fast, no DB per page)

## Pending Work

### Email Notifications
- Donor thank-you email after successful payment
- Finance team notification to seva@iskconseshadripuram.org
- Hook point identified: `api/webhook.php` on `payment.captured`
- No email infrastructure yet — plan to use Gmail SMTP (free tier)

### Sidebar Persistence
- Admin sidebar accordion currently resets on page reload
- Future: store open/closed state in localStorage

## Gotchas

- `seva.php` in project root is deprecated, redirects to `index.php`
- `donation_cause_sevas.slug` column does NOT exist — only `name` column
- All test data uses `source_type` field ('test_seed', 'test_comprehensive', 'seed_dashboard')
- Legacy dual-read: new master catalog first, fallback to old table
- Pagination links must use absolute URLs (`BASE_URL . 'darshan?page=2'`) — relative `?page=2` gets stripped by generic rewrite rule
- `media/` directory is scanned dynamically by `darshan.php` for gallery images
- Admin login is rate-limited: 5 attempts per 15-minute window per IP
- RBAC migrations must be run in order: `001_create_tables.php` → `002_seed_data.php` → `003_migrate_admins.php`

## Rules

- All DB operations through `getDB()` from `config.php`
- Prepared statements for all SQL
- Follow existing migration patterns
- Maintain backward compatibility with legacy tables
- Don't expose `$e->getMessage()` to users — log server-side
- Use `printf` (not `echo`) when generating wrapper files — `echo '\n'` produces literal `\n` in bash
- Use `hasPermission()` not `hasRole()` for new admin pages

---

*Last updated: 2026-07-07 (RBAC implementation, accordion sidebar, PHPUnit testing)*