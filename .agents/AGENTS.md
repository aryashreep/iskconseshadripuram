# ISKCON Sri Jagannath Mandir (ISJM) — Agent Guide

PHP website for ISKCON Seshadripuram, Bangalore. No framework — vanilla PHP with PDO, Apache mod_rewrite, and Razorpay payments. Runs on Laragon (Windows) locally.

All original file paths are preserved as backward-compatible wrappers that delegate to module files in `modules/`.

---

## 📚 Documentation Index

This project maintains comprehensive documentation. Key files for AI assistants:

| Document | Purpose |
|----------|---------|
| [`README.md`](../README.md) | Project overview, features, quick start |
| [`ARCHITECTURE.md`](../ARCHITECTURE.md) | System design, module layout, patterns |
| [`SECURITY.md`](../SECURITY.md) | **OWASP Top 10**, auth, CSRF, XSS, SQL injection, webhooks |
| [`CODING_STANDARDS.md`](../CODING_STANDARDS.md) | PHP/SQL/HTML/CSS/JS conventions, patterns |
| [`WORKFLOWS.md`](../WORKFLOWS.md) | All business workflows (donation, booking, admin) |
| [`MODULE_INDEX.md`](../MODULE_INDEX.md) | All modules with tables, endpoints, dependency chain |
| [`DOCUMENTATION_POLICY.md`](../DOCUMENTATION_POLICY.md) | When to update which docs |
| [`DEVELOPMENT_WORKFLOW.md`](../DEVELOPMENT_WORKFLOW.md) | Development process for common tasks |
| [`CHANGELOG.md`](../CHANGELOG.md) | Change history |

### Module Documentation
| Module | Documentation Files |
|--------|-------------------|
| **Kernel** | [`README.md`](../modules/Kernel/README.md), [`DECISIONS.md`](../modules/Kernel/DECISIONS.md) |
| **Donation** | [`README.md`](../modules/Donation/README.md), [`API.md`](../modules/Donation/API.md), [`DATABASE.md`](../modules/Donation/DATABASE.md), [`DECISIONS.md`](../modules/Donation/DECISIONS.md), [`TASKS.md`](../modules/Donation/TASKS.md), [`TESTING.md`](../modules/Donation/TESTING.md) |
| **Sudamaseva** | [`README.md`](../modules/Sudamaseva/README.md), [`API.md`](../modules/Sudamaseva/API.md), [`DATABASE.md`](../modules/Sudamaseva/DATABASE.md), [`DECISIONS.md`](../modules/Sudamaseva/DECISIONS.md), [`TESTING.md`](../modules/Sudamaseva/TESTING.md) |
| **Panihati** | [`README.md`](../modules/Panihati/README.md), [`DECISIONS.md`](../modules/Panihati/DECISIONS.md), [`TESTING.md`](../modules/Panihati/TESTING.md) |
| **Booking** | [`README.md`](../modules/Booking/README.md), [`DECISIONS.md`](../modules/Booking/DECISIONS.md) |
| **Festivals** | [`README.md`](../modules/Festivals/README.md), [`DECISIONS.md`](../modules/Festivals/DECISIONS.md) |
| **Blogs** | [`README.md`](../modules/Blogs/README.md), [`DECISIONS.md`](../modules/Blogs/DECISIONS.md) |
| **Content** | [`README.md`](../modules/Content/README.md), [`DECISIONS.md`](../modules/Content/DECISIONS.md) |
| **RBAC** | [`README.md`](../modules/RBAC/README.md), [`DECISIONS.md`](../modules/RBAC/DECISIONS.md) |

### Reference Docs (`docs/`)
| Document | Purpose |
|----------|---------|
| [`docs/API.md`](../docs/API.md) | All API endpoints with request/response schemas |
| [`docs/DATABASE.md`](../docs/DATABASE.md) | Complete table inventory with module ownership |
| [`docs/ADMIN.md`](../docs/ADMIN.md) | Admin panel navigation, RBAC roles, permission API |
| [`docs/DEVELOPER.md`](../docs/DEVELOPER.md) | Fresher's guide (setup, architecture, tasks) |
| [`docs/DEPLOYMENT.md`](../docs/DEPLOYMENT.md) | Production deployment checklist |
| [`docs/TESTING.md`](../docs/TESTING.md) | Testing guide (E2E + PHPUnit patterns) |
| [`docs/DONATIONS.md`](../docs/DONATIONS.md) | Donation system details |
| [`docs/AUTHORIZATION_MATRIX.md`](../docs/AUTHORIZATION_MATRIX.md) | Page → Permission mapping |
| [`docs/AUDIT_LOGGING.md`](../docs/AUDIT_LOGGING.md) | Audit logging current state + recommended schema |
| [`docs/FILE_UPLOADS.md`](../docs/FILE_UPLOADS.md) | File upload security standards |
| [`docs/SECURITY_CHECKLIST.md`](../docs/SECURITY_CHECKLIST.md) | Pre-deployment security checklist |
| [`docs/INCIDENT_RESPONSE.md`](../docs/INCIDENT_RESPONSE.md) | Incident response procedures |
| [`docs/RELEASE_CHECKLIST.md`](../docs/RELEASE_CHECKLIST.md) | Full release checklist |
| [`docs/DOCUMENTATION_TEMPLATE.md`](../docs/DOCUMENTATION_TEMPLATE.md) | Templates for consistent docs |

### Historical Specs (Reference Only — Current State Differs)
| Document | Notes |
|----------|-------|
| [`modularization-spec.md`](../modularization-spec.md) | Architecture migration guide — all phases complete |
| [`rbac-spec.md`](../rbac-spec.md) | RBAC design doc — implemented; refer to module docs for current state |
| [`sudamaseva-spec.md`](../sudamaseva-spec.md) | Sudamaseva spec — implemented with additions |
| [`sudamaseva-spec-review.md`](../sudamaseva-spec-review.md) | Spec review — most recommendations implemented |

---

## Quick Commands

| Task | Command |
|------|---------|
| Run all E2E tests | `npx playwright test` |
| Run single E2E test file | `npx playwright test tests/puja-booking.spec.js` |
| Run all PHPUnit unit tests | `vendor/bin/phpunit` |
| Check PHP syntax | `php -l <file>` |
| Run DB migration | `php database/migrations/<name>.php` |
| Run RBAC seed migration | `php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php` |
| Seed test data | `php database/migrations/seed_dashboard_data.php` |
| Regenerate autoloader | `composer dump-autoload` |
| Build assets | `npm run build` |

---

## Module Architecture

```
modules/
├── Donation/     (Phase 1 — Payment system, reports, seva catalog)
├── Panihati/     (Phase 2 — Yatra registration & admin)
├── Booking/      (Phase 3 — Puja/yagya/guest house booking)
├── Festivals/    (Phase 4 — ~70 public festival pages)
├── Blogs/        (Phase 5 — Blog posts & admin)
├── Content/      (Phase 6 — Static content pages)
├── RBAC/         (Phase 8 — Role-based access control)
├── Sudamaseva/   (— Recurring/manual subscription donations)
└── Kernel/       (Phase 7 — Shared infrastructure)
```

**📖 See [`MODULE_INDEX.md`](../MODULE_INDEX.md)** for a complete module reference with responsibilities, owned tables, entry points, and dependency chains.

### Full Module Tree

```
modules/
├── Donation/
│   ├── Admin/       (13 files — reports, festivals, seva catalogue, exports)
│   ├── api/         (5 files — create-order, verify-payment, webhook, etc.)
│   ├── assets/      (CSS + JS for donate, checkout, cart)
│   ├── content/     (4 files — donate landing, seva, payment success/fail)
│   ├── src/         (3 files — DonationRepository, Service, Renderer)
│   └── docs/        (markdown: README, API, DATABASE, etc.)
│
├── Panihati/
│   ├── Admin/       (8 files — dashboard, records, reports, pricing, etc.)
│   ├── api/         (2 files — create-panihati-order, verify-panihati-payment)
│   ├── content/     (2 files — public registration + success)
│   ├── assets/      (CSS)
│   └── panihati-helpers.php
│
├── Booking/
│   ├── Admin/       (bookings management)
│   ├── api/         (create-booking-order, create-cart-order)
│   ├── assets/      (CSS + JS for puja, yagya, guest house)
│   └── content/     (puja, yagya, guest-house public pages)
│
├── Festivals/
│   └── content/
│       ├── appearance/        (5 files)
│       ├── disappearance/     (6 files)
│       ├── ekadashi/          (25 files)
│       ├── events/            (3 files)
│       ├── grand-festivals/   (26 files)
│       ├── vaishnava-calendar/ (1 file)
│       ├── index.php, listing.php, detail.php
│
├── Blogs/
│   ├── Admin/       (blogs.php, blog-edit.php)
│   └── content/     (index.php, detail.php)
│
├── Content/
│   └── content/
│       ├── about/       (8 files — founder, mission, philosophy, schedule)
│       ├── courses/     (5 files — bhakti-shastri, vaibhava, idc, etc.)
│       ├── services/    (24 files — siksha, sunday-feast, life-membership)
│       ├── yatra/       (2 files — index, detail)
│       ├── contact.php, darshan.php, forums.php
│       ├── resources.php, seva.php, sitemap.php
│
├── RBAC/
│   ├── Admin/           (4 files — roles, role-edit, permissions)
│   ├── RbacService.php  (Core RBAC logic — permission checking, CRUD)
│   ├── PermissionRegistry.php (55 permission definitions)
│   └── database/migrations/ (3 migrations)
│
└── Kernel/
    ├── Admin/
    │   ├── auth-check.php
    │   └── partials/       (header.php, footer.php)
    ├── config.php
    ├── content/index.php   (homepage)
    ├── includes/
    │   ├── db.php, bootstrap.php, asset-helper.php
    │   ├── donation-helpers.php, panihati-helpers.php
    ├── partials/           (9 files — header, footer, home sections, schema, CTA)
    └── src/
        ├── Donations/      (Repository, Service, Renderer)
        └── Helpers/        (SessionGuard)
```

---

## Wrapper Convention

Every file that was moved to a module has a **backward-compatibility wrapper** at its original path. Wrappers are one-liner `require_once` using `__DIR__` and `basename()`:

```php
<?php
require_once __DIR__ . '/../modules/Kernel/partials/' . basename(__FILE__);
```

Key benefit: CWD-based includes (`include '../partials/header.php'`) continue to work because the CWD stays with the wrapper's directory, not the module file's directory.

---

## Complete Wrapper Listing (~190 files)

Every `.php` file listed below is a backward-compatibility wrapper at its original path. The actual code lives in `modules/<Module>/content/` (or `Admin/`, `api/`, etc.).

### Root Level (8)
```
config.php   index.php   contact.php   darshan.php
forums.php   resources.php   seva.php   sitemap.php
```

### Partials (9)
```
partials/
├── donation-cta.php
├── footer.php
├── header.php
├── home-category-grid.php
├── home-hero.php
├── home-quick-links.php
├── home-seasonal-spotlight.php
├── home-service-cards.php
└── schema.php
```

### Includes (5)
```
includes/
├── asset-helper.php
├── bootstrap.php
├── db.php
├── donation-helpers.php
└── panihati-helpers.php
```

### Admin (19)
```
admin/
├── auth-check.php
├── admin-edit.php          (→ modules/RBAC/Admin/role-edit.php wrapper)
├── admins.php
├── blog-edit.php
├── blogs.php
├── bookings.php
├── dashboard.php
├── donations.php
├── export-dashboard.php
├── export-donations.php
├── export-report-activity.php
├── export-report-category.php
├── export-report-seva.php
├── festival-edit.php
├── festivals.php
├── panihati-*.php          (8 files)
├── permissions.php         (→ modules/RBAC/Admin/permissions.php wrapper)
├── report-*.php            (4 files)
├── role-edit.php           (→ modules/RBAC/Admin/role-edit.php wrapper)
├── roles.php               (→ modules/RBAC/Admin/roles.php wrapper)
├── seva-catalogue*.php     (2 files)
├── ajax/master-sevas-by-category.php
└── partials/
    ├── header.php
    └── footer.php
```

### API (9)
```
api/
├── create-booking-order.php
├── create-cart-order.php
├── create-order.php
├── create-panihati-order.php
├── create-subscription.php
├── track-view.php
├── verify-panihati-payment.php
├── verify-payment.php
└── webhook.php
```

### Donate (4)
```
donate/
├── index.php
├── donate-seva.php
├── payment-success.php
└── payment-failed.php
```

### Booking (9)
```
booking/
├── index.php
├── guest-house/index.php
├── puja/index.php
├── puja/detail.php
├── yagya/index.php
└── yagya/detail.php

admin/bookings.php
api/create-booking-order.php
api/create-cart-order.php
```

### Content Pages (46)
```
about/                          (8 files)
├── index.php, founder-acharya.php, golden-temple.php
├── hare-krishna-movement.php, history-of-iskcon.php
├── our-mission.php, our-philosophy.php, temple-schedule.php

courses/                        (5 files)
├── bhakti-shastri.php, bhakti-vaibhava.php
├── bhaktivedanta-education.php
├── idc.php, teachers-training.php

services/                       (24 files)
├── index.php, bhakti-sadan.php, bhakti-vriksha.php
├── corporate-programs.php, food-for-life.php
├── function-hall.php, govindas-prasadam.php
├── harinam-initiation.php, harinam-sankirtana.php
├── krishna-fun-school.php, krishna-sadhaka.php
├── krishna-sevaka.php, krishna-upasaka.php
├── life-membership.php, music-school.php
├── new-rajapur.php, our-centers.php
├── siksha.php, sraddhavan.php
├── sri-guru-carana-ashraya.php
├── srila-prabhupada-ashraya.php
├── sunday-feast.php, vaishnavi-forum.php
└── youth-forum.php

yatra/                          (4 files — 2 wrap Content, 2 wrap Panihati)
├── index.php, detail.php        → modules/Content/content/yatra/
├── panihati.php                 → modules/Panihati/content/panihati.php
└── panihati-success.php         → modules/Panihati/content/panihati-success.php
```

### Festivals (~70)
```
festivals/
├── index.php
├── listing.php
├── detail.php
├── appearance/       (5 files)
├── disappearance/    (6 files)
├── ekadashi/         (25 files)
├── events/           (3 files)
├── grand-festivals/  (26 files)
└── vaishnava-calendar/index.php
```

### Blogs (4)
```
blogs/index.php
blogs/detail.php
admin/blogs.php
admin/blog-edit.php
```

---

## Database

- **Local DB name**: `isjm_donations`
- **Prod DB name**: `iskcop35_iskconseshadripuram`
- **Credentials**: env vars `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- **Connection**: `getDB()` returns a PDO singleton

**📖 See [`docs/DATABASE.md`](../docs/DATABASE.md)** for the complete table inventory with module ownership, active/legacy status, and relationships.

### Key Tables

| Table | Purpose | Module |
|-------|---------|--------|
| `donation_causes` | Activities/festivals (74 rows, has `category` field) | Donation |
| `donation_transactions` | Payment records — **NEVER TRUNCATE** | Donation |
| `master_seva_categories` | 10 top-level seva categories | Donation |
| `master_sevas` | 363+ deduplicated seva offerings | Donation |
| `donation_cause_master_sevas` | Pivot: cause ↔ seva links | Donation |
| `booking_pujas` | Puja/yagya bookings | Booking |
| `panihati_yatra_registrations` | Yatra registrations — **NEVER TRUNCATE** | Panihati |
| `sudamaseva_donors` | Donor profiles (302 migrated + new) | Sudamaseva |
| `sudamaseva_subscriptions` | Subscription plans (recurring/manual) | Sudamaseva |
| `sudamaseva_payments` | Installment payments (3,278 migrated + new) | Sudamaseva |
| `sudamaseva_receipts` | 80G tax receipts | Sudamaseva |
| `rbac_roles` | 11 role definitions, managed via UI | RBAC |
| `rbac_permissions` | 55 permission definitions across 13 modules | RBAC |
| `rbac_role_permissions` | Role ↔ Permission M:N | RBAC |
| `rbac_user_roles` | Admin ↔ Role M:N (replaces `admins.role`) | RBAC |
| `admins` | Admin users — `role` column deprecated | Kernel |

### Donation Reporting Hierarchy

```
Category (donation_causes.category: festival, ekadashi, etc.)
    → Activity (donation_causes.title: Rath Yatra, Janmashtami, etc.)
        → Seva (master_sevas.name via donation_cause_master_sevas pivot)
```

---

## Admin Roles (RBAC)

| Role | Description |
|------|-------------|
| `super_admin` | Unrestricted access — bypasses all permission checks |
| `temple_admin` | Full access to all operational modules (no user/role management) |
| `donation_manager` | Manage donations, causes, and reporting |
| `festival_manager` | Manage festivals, events, and seva catalog |
| `accounts` | View financial data, reports, exports |
| `content_manager` | Manage blogs and website content |
| `report_viewer` | Read-only access to reports and dashboards |
| `devotee_care` | Manage devotee records |
| `volunteer_coordinator` | Manage volunteers |
| `event_coordinator` | Manage special events |
| `read_only` | View-only across permitted modules |

Permissions use `module.action` format (e.g., `donations.view`). 55 total permissions across 13 modules.

Permission helpers (globally available in admin pages):
- `hasPermission('module.action')` — Boolean check
- `hasAnyPermission(['a', 'b'])` — Check if user has any of the listed permissions
- `requirePermission('module.action')` — Block with 403 if not granted
- `requireAnyPermission(['a', 'b'])` — Block with 403 if none granted

## RBAC Admin Pages

| Page | URL | Access | Purpose |
|------|-----|--------|---------|
| Roles | `/admin/roles` | super_admin | List roles with user counts, edit/delete |
| Role Edit | `/admin/role-edit` | super_admin | Create/edit role + permission matrix |
| Permissions | `/admin/permissions` | super_admin | Read-only permission reference |
| Admins | `/admin/admins` | super_admin | List admin users with RBAC roles |
| Admin Edit | `/admin/admin-edit` | super_admin | Create/edit admin with multi-role assignment |

## Admin Sidebar Navigation

```
Dashboard (requires: dashboard.view)
Manage Blogs (requires: blogs.view)
Seva Catalogue (requires: seva_catalog.view)
Manage Festivals (requires: festivals.view)
Donations (group: donations.view | reports.view)
Puja & Yagya Bookings (requires: bookings.view)
Panihati Yatra (group: panihati.*)
Sudamaseva (group: sudamaseva.view)
Role Management (group: super_admin only)
  ├── Manage Admins
  ├── Assign Roles
  ├── Roles
  └── Permissions
View Website
Logout
```

## PHPUnit Tests

74 tests, 505 assertions — run with `vendor/bin/phpunit`.

- `PermissionRegistryTest` (14 tests) — Module structure, 55 permissions, slug/label format, sort order
- `RbacServiceTest` (30 tests) — Permission checking, super_admin bypass, role CRUD, permission CRUD, role-permission assignment, user-role assignment, edge cases

Tests use an **in-memory SQLite database** for full isolation — no MySQL connection required.

---

## Donation Report Pages

| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/admin/report-dashboard` | KPIs, charts, YoY, heatmap, retention, top donors |
| Category Report | `/admin/report-category` | Aggregated by donation category |
| Activity Report | `/admin/report-activity` | Grouped by activity with search |
| Seva Report | `/admin/report-seva` | 3-level: Category → Activity → Seva |

All reports support:
- Date range filtering
- CSV export
- Accordion expand/collapse
- Summary cards

---

## Dashboard Charts

Uses Chart.js. Data queries are PHP-side, rendered as JSON into JS.

| Chart | Type | Data |
|-------|------|------|
| Monthly Trend | Dual-axis line | Revenue (₹) + donation count |
| Revenue by Category | Doughnut | 8 categories with % tooltips |
| Top 10 Activities | Horizontal bar | Revenue by activity |
| Donation Count by Category | Vertical bar | Count per category |
| Day-of-Week Heatmap | CSS grid | Color intensity by count |
| Donor Retention | Stacked bar | First-time vs returning by month |
| Year-over-Year | Dual line | Current vs previous year monthly |
| Category → Activity | Accordion | Drill-down hierarchy |

---

## Composer Autoloading

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

After changing `composer.json`, run `composer dump-autoload` to regenerate.

---

## Security

- Prepared statements everywhere — never concatenate user input
- CSRF tokens on all forms and state-changing GET requests
- `requirePermission()` / `hasPermission()` for granular access control
- Super Admin has implicit bypass for all permissions
- Permissions cached in `$_SESSION['admin_permissions']` for fast in-memory checking
- Action buttons hidden when user lacks edit/delete/export permissions
- API endpoints use Razorpay HMAC signature verification
- Amount verification against server-side catalog

---

## Payment Flow (Razorpay)

1. Client POSTs to `/api/create-order.php` with amount + cause
2. Server verifies amount against `donation_cause_master_sevas` catalog
3. Creates Razorpay order, returns `order_id`
4. Client opens Razorpay checkout modal
5. On success, calls `/api/verify-payment.php` with signature
6. Server verifies HMAC, updates `payment_status = 'paid'`
7. Webhook (`/api/webhook.php`) provides secondary verification (authoritative)

---

## Production Deployment

1. Backup prod DB
2. Generate migration: `php scripts/generate_prod_migration.php`
3. Review SQL, run via phpMyAdmin
4. **Never truncate** transaction tables
5. Set env vars on server (not `.env` file)
6. Clear browser cache after CSS/JS changes
