# CHANGELOG.md

## [2026-07-08] — Sudamaseva Manual Mode & Donor Dashboard

### Added
- **Manual Payment Mode (Pay Monthly)** — Mode toggle on signup page lets donors choose between:
  - **Auto Monthly**: Razorpay subscription with auto-debit via eMandate/eNACH/UPI Autopay
  - **Pay Monthly**: Donor pays each installment manually via Razorpay checkout
- **Enroll API** (`/api/sudamaseva/enroll`) — Creates donor + manual subscription + Razorpay Order in one call
- **Create-Order API** (`/api/sudamaseva/create-order`) — Creates Razorpay Order for subsequent installments
- **Verify-Order API** (`/api/sudamaseva/verify-order`) — Verifies manual payments with `{order_id}|{payment_id}` HMAC format
- **Donor Lookup** (`/sudamaseva/lookup`) — Search by phone or legacy ID → redirect to dashboard
- **Donor Dashboard** (`/sudamaseva/dashboard`) — Shows subscription card, installment grid with Pay Now buttons, payment history
- **Returning Donor CTA** — "View My Seva" link on signup page for existing donors
- **Database Columns**:
  - `sudamaseva_donors.legacy_id_no` — Links to old system's `tbl_users.id_no`
  - `sudamaseva_subscriptions.collection_mode` — `'recurring'` or `'manual'`
  - `sudamaseva_payments.payment_source` — `'subscription_charge'`, `'manual_order'`, `'migrated'`, `'admin_manual'`
  - `sudamaseva_payments.billing_month` — Billing period for monthly aggregation
- **Backfill Script** (`modules/Sudamaseva/migrations/005_backfill_legacy_ids.php`) — 302 legacy IDs populated from old `tbl_users` by phone match

### Changed
- Signup page (`modules/Sudamaseva/content/index.php`) — Added mode toggle UI with dynamic button/labels
- Dashboard page — Installment grid now shows Pay Now buttons for unpaid installments on manual subscriptions

---

## [2026-07-07] — RBAC Implementation (Phase 8)

### Added
- **RBAC Module** (`modules/RBAC/`) — Complete role-based access control system
  - 11 roles with granular permissions across 13 modules (55 total permissions)
  - 4 database tables: `rbac_roles`, `rbac_permissions`, `rbac_role_permissions`, `rbac_user_roles`
  - 3 migrations: create tables, seed data, migrate existing admins
- **Role Management UI** (super_admin only):
  - Roles listing page (`admin/roles`) — view, edit, delete roles with user counts
  - Role edit page (`admin/role-edit`) — create/edit roles with permission matrix (Select All per row/column)
  - Permissions reference page (`admin/permissions`) — read-only catalog grouped by module
  - Multi-role assignment on admin edit page (`admin/admin-edit`)
- **Permission-based Access Control** across 27+ admin pages:
  - `requirePermission()` / `hasPermission()` API in SessionGuard and global helpers
  - Permission-based sidebar visibility (users only see accessible menu items)
  - Action button visibility (Edit/Delete/Export hidden per permissions)
- **RBAC Unit Tests** — 74 PHPUnit tests, 505 assertions (in-memory SQLite)
  - PermissionRegistryTest (14 tests) — module structure, permission count, sort order
  - RbacServiceTest (30 tests) — permission checking, CRUD, role assignment, edge cases
- **Sidebar Reorganization**:
  - New "Role Management" collapsible group with sub-menus: Manage Admins, Assign Roles, Roles, Permissions

### Changed
- `SessionGuard.php` — Added `hasPermission()`, `hasAnyPermission()`, `requirePermission()`, `requireAnyPermission()`
- `auth-check.php` — Added global helper functions, loads permissions into session on page load
- `header.php` — All sidebar items use `hasPermission()` instead of `hasRole()`
- All admin pages converted from `requireRole()` to `requirePermission()` for granular access

## [2026-07-06] — Architecture Migration Complete (All 7 Phases)

### Summary
All 193 PHP files across the project have been migrated from a flat structure into 7 modular directories under `modules/`. Every original file path is preserved as a backward-compatible wrapper. 153/155 E2E tests pass with zero regressions.

### Phase 7 — Kernel Module (`modules/Kernel/`)
- Moved `config.php`, `partials/` (9 files), `includes/` (5 files), `src/` (4 files)
- Moved `admin/auth-check.php` + `admin/partials/` (2 files)
- Moved `index.php` (homepage) to `modules/Kernel/content/`
- Updated `composer.json` autoload: `"Isjm\\": "modules/Kernel/src/"`
- Updated `__DIR__` paths: `config.php` uses `realpath(__DIR__ . '/../..')` for project root; `asset-helper.php` uses `dirname(__DIR__, 2)`

### Phase 6 — Content Pages Module (`modules/Content/`)
- Moved 6 root-level pages: `contact.php`, `darshan.php`, `forums.php`, `resources.php`, `seva.php`, `sitemap.php`
- Moved `services/` (24 files), `courses/` (5 files), `about/` (8 files), `yatra/` (2 files)

### Phase 5 — Blogs Module (`modules/Blogs/`)
- Moved `blogs/index.php`, `blogs/detail.php` → `modules/Blogs/content/`
- Moved `admin/blogs.php`, `admin/blog-edit.php` → `modules/Blogs/Admin/`

### Phase 4 — Festivals Module (`modules/Festivals/`)
- Moved ~70 festival PHP files to `modules/Festivals/content/`
- Subdirectories: appearance (5), disappearance (6), ekadashi (25), events (3), grand-festivals (26), vaishnava-calendar (1)

### Phase 3 — Booking Module (`modules/Booking/`)
- Moved `booking/` pages, `admin/bookings.php`, API endpoints, and assets to `modules/Booking/`

### Phase 2 — Panihati Module (`modules/Panihati/`)
- Moved yatra registration, admin panel (8 pages), API endpoints, and helpers to `modules/Panihati/`

### Phase 1 — Donation Module (`modules/Donation/`)
- Moved donation reports, payment API, seva catalog, checkout assets, and admin pages to `modules/Donation/`

---

## [2026-07-05] — Donation Reporting System

### Added
- **Donation Dashboard** (`admin/report-dashboard.php`) with 8 Chart.js visualizations:
  - Monthly trend (dual-axis: revenue + count)
  - Revenue by category (doughnut)
  - Top 10 activities (horizontal bar)
  - Donation count by category (vertical bar)
  - Day-of-week heatmap
  - Donor retention funnel
  - Year-over-year comparison
  - Top 10 donors table
- **Category Report** (`admin/report-category.php`) — aggregated by donation category
- **Activity Report** (`admin/report-activity.php`) — grouped by activity with search filter
- **Seva Report** (`admin/report-seva.php`) — three-level hierarchy (Category → Activity → Seva) with quantity sponsored
- **CSV Exports** for all reports
- **Month-over-month trend indicators** on dashboard summary cards
- **Admin navigation** — "Donations" group with sub-nav (Dashboard, Logs, 3 Reports)
- **Search/filter** on Activity and Seva report pages

### Changed
- Upgraded README.md with complete project documentation
- Updated AGENTS.md with reporting pages and chart details
- Restructured project documentation (PROJECT.md, ARCHITECTURE.md, ROADMAP.md, docs/)

---

## [2026-07-03] — Seva Catalogue & Master Seva System

### Added
- Master Seva Catalog (10 categories, 363+ sevas)
- `master_seva_categories`, `master_sevas`, `donation_cause_master_sevas` tables
- Admin CRUD for seva catalogue management
- Dual-read strategy (new catalog → legacy fallback)
- Category-seed scripts for all 10 seva categories

---

## [2026-06-XX] — Panihati Yatra Management

### Added
- Panihati Yatra admin panel (8 pages)
- KPI cards, year-over-year comparison, rankings
- Offline entry, bulk summary, pricing management

---

## [2026-05-XX] — Core Donation System

### Added
- 68+ donation causes with Razorpay integration
- Cart-based checkout with localStorage
- Puja & Yagya booking system
- Festival content management
- Admin role-based access control
