# MODULE_INDEX.md — Module Index & Quick Reference

> **Last updated:** 2026-07-11
> **Purpose:** Quick overview of all modules, their responsibilities, entry points, owned tables, and key files.
> **How to use:** Find a module → see what it owns → navigate to its docs.

---

## Module Overview

| # | Module | Phase | Responsibility | Type | Status |
|---|--------|-------|---------------|------|--------|
| 1 | **Kernel** | 7 | Shared infrastructure (config, DB, auth, layout) | Core | ✅ Stable |
| 2 | **Donation** | 1 | Payment system, reports, seva catalog | Feature | ✅ Stable |
| 3 | **Sudamaseva** | — | Recurring/manual subscription donations | Feature | ✅ Stable |
| 4 | **Panihati** | 2 | Yatra registration & admin | Feature | ✅ Stable |
| 5 | **Booking** | 3 | Puja/yagya/guest house booking | Feature | ✅ Stable |
| 6 | **Festivals** | 4 | ~70 public festival detail pages | Content | ✅ Stable |
| 7 | **Blogs** | 5 | Blog posts & admin | Content | ✅ Stable |
| 8 | **Content** | 6 | Static content (about, services, courses) | Content | ✅ Stable |
| 9 | **RBAC** | 8 | Role-based access control | Core | ✅ Stable |

---

## 1. Kernel Module

**Responsibility:** Cross-cutting infrastructure shared by all modules.

**Location:** `modules/Kernel/`

**Owned Tables:**
- `admins` — Admin user accounts
- `login_attempts` — Login attempt tracking

**Key Files:**

| File | Purpose |
|------|---------|
| `config.php` | Site config, env vars, constants |
| `includes/db.php` | PDO singleton (`getDB()`) |
| `includes/bootstrap.php` | Config loading, session start, CSRF token |
| `includes/asset-helper.php` | Cache-busted asset URLs |
| `src/Helpers/SessionGuard.php` | Auth guard (login, permissions, RBAC) |
| `partials/header.php` | Public page header + nav |
| `partials/footer.php` | Public page footer + scripts |
| `Admin/partials/header.php` | Admin sidebar navigation |
| `Admin/partials/footer.php` | Admin footer |
| `Admin/auth-check.php` | Admin auth gate + permission loading |

**Dependency Direction:** No dependencies on other modules. All modules depend on Kernel.

---

## 2. Donation Module

**Responsibility:** Donation causes, seva offerings, Razorpay payments, transaction tracking, reports.

**Location:** `modules/Donation/`

**Owned Tables:**
- `donation_causes` — 74 activities/festivals
- `donation_transactions` — Payment records (NEVER TRUNCATE)
- `master_seva_categories` — 10 top-level seva categories
- `master_sevas` — 363+ deduplicated seva offerings
- `donation_cause_master_sevas` — Pivot: cause ↔ seva
- `donation_cause_sevas` — Legacy per-cause sevas (backward compatible)
- `donation_seva_categories` — Legacy seva categories (backward compatible)
- `donation_subscriptions` — Recurring donation subscriptions

**Key Classes:**
- `DonationRepository.php` — All DB queries
- `DonationService.php` — Business logic
- `DonationRenderer.php` — HTML rendering

**Public Pages:**
- `/donate/{slug}` — Donation form per cause
- `/checkout/` — Cart checkout

**Admin Pages:**
- `/admin/donations` — Transaction logs
- `/admin/report-dashboard` — KPI dashboard (8 charts)
- `/admin/report-category` — Category report
- `/admin/report-activity` — Activity report
- `/admin/report-seva` — Seva report
- `/admin/seva-catalogue` — Seva CRUD management
- `/admin/export-donations` — CSV export
- `/admin/export-report-*` — 3 report CSV exports

**API Endpoints:**
- `POST /api/create-order.php` — Create Razorpay order
- `POST /api/verify-payment.php` — Verify payment signature
- `POST /api/webhook.php` — Razorpay webhook

---

## 3. Sudamaseva Module

**Responsibility:** Recurring + manual subscription donation program (Sudama Vipra story).

**Location:** `modules/Sudamaseva/`

**Owned Tables:**
- `sudamaseva_donors` — Donor profiles (302 migrated + new)
- `sudamaseva_subscriptions` — Subscription plans (recurring/manual)
- `sudamaseva_payments` — Installment payments (3,278 migrated + new)
- `sudamaseva_receipts` — 80G tax receipts

**Key Classes:**
- `SudamasevaRepository.php` — DB queries for donors, subscriptions, payments
- `SudamasevaService.php` — Business logic, formatting, receipt generation

**Public Pages:**
- `/sudamaseva` — Signup with mode toggle (Auto Monthly / Pay Monthly)
- `/sudamaseva/lookup` — Find existing donation by phone/legacy ID
- `/sudamaseva/dashboard?donor_id=X` — Donor dashboard with installment grid
- `/sudamaseva/success` — Post-payment confirmation

**API Endpoints:**
- `POST /api/sudamaseva/create-subscription` — Auto Monthly: Create subscription
- `POST /api/sudamaseva/verify-payment` — Auto Monthly: Verify (HMAC: `{sub_id}|{pay_id}`)
- `POST /api/sudamaseva/webhook` — Webhook handler
- `POST /api/sudamaseva/enroll` — Pay Monthly: Enroll donor + create order
- `POST /api/sudamaseva/create-order` — Pay Monthly: Create order for next installment
- `POST /api/sudamaseva/verify-order` — Pay Monthly: Verify (HMAC: `{order_id}|{pay_id}`)
- `POST /api/sudamaseva/lookup` — Search donor by phone/legacy ID

---

## 4. Panihati Module

**Responsibility:** Panihati Yatra registration, pricing, sadans, pickups, expenses.

**Location:** `modules/Panihati/`

**Owned Tables:**
- `panihati_yatra_registrations` — Travel bookings (NEVER TRUNCATE)
- `panihati_pricing` — Pricing configuration per year
- `panihati_bhakti_sadans` — Sadhan (accommodation) options
- `panihati_pickup_locations` — Pickup point options
- `panihati_expenses` — Yatra expenses tracking
- `panihati_yatra_offline_aggregates` — Offline entry summary
- `panihati_yatra_combined_stats` — DB view (paid + offline)

**Public Pages:**
- `/yatra/panihati` — Registration form with mode toggle, price calculator

**Admin Pages (8):**
- `/admin/panihati-yatra` — Dashboard with KPIs
- `/admin/panihati-records` — Registration records
- `/admin/panihati-reports` — Download reports
- `/admin/panihati-pricing` — Pricing management
- `/admin/panihati-sadans` — Bhakti sadans management
- `/admin/panihati-pickups` — Pickup locations management
- `/admin/panihati-bulk-summary` — Bulk offline entry
- `/admin/panihati-expenses` — Finance & expenses

---

## 5. Booking Module

**Responsibility:** Puja & Yagya booking, offering tiers, payment integration.

**Location:** `modules/Booking/`

**Owned Tables:**
- `booking_pujas` — Puja/yagya bookings (links to `donation_transactions.id`)

**Public Pages:**
- `/booking/puja/{slug}` — Puja detail page with tiers
- `/booking/yagya/{slug}` — Yagya detail page with tiers
- `/booking/index.php` — Booking listing

**Admin Pages:**
- `/admin/bookings` — Booking management (toggle status, view details)

**API Endpoints:**
- `POST /api/create-booking-order.php` — Create order for booking

---

## 6. Festivals Module

**Responsibility:** Festival content pages (~70 files), categorized by festival type.

**Location:** `modules/Festivals/`

**Owned Tables:** No dedicated tables — festival data shared through `donation_causes` table.

**Public Pages:**
- `/festivals/` — Festival listing
- `/festivals/grand-festivals/{slug}` — Grand festival pages
- `/festivals/ekadashi/{slug}` — Ekadashi pages
- `/festivals/appearance/{slug}` — Appearance day pages
- `/festivals/disappearance/{slug}` — Disappearance day pages
- `/festivals/events/{slug}` — Event pages
- `/festivals/detail.php?slug=X` — Dynamic DB-driven festival pages

**URL Routing:**
- Hardcoded `.php` files take priority over dynamic DB-driven pages
- `.htaccess` checks for `.php` file existence first, falls back to `detail.php?slug=X`
- See `.htaccess` for routing rules for each category

---

## 7. Blogs Module

**Responsibility:** Blog post management, content publishing.

**Location:** `modules/Blogs/`

**Owned Tables:**
- `blogs` — Blog posts (title, content, slug, tags, published status)

**Public Pages:**
- `/blogs` — Blog listing page
- `/blogs/{slug}` — Blog detail page

**Admin Pages:**
- `/admin/blogs` — Blog listing with publish toggle
- `/admin/blog-edit` — Create/edit blog posts

---

## 8. Content Module

**Responsibility:** Static content pages — about, services, courses, contact, darshan, etc.

**Location:** `modules/Content/`

**Owned Tables:** None — content is file-based (PHP pages).

**Public Pages (46 files):**
- `/about/*` — About pages (history, philosophy, mission, temple schedule, etc.)
- `/services/*` — Service pages (24: food-for-life, sunday-feast, etc.)
- `/courses/*` — Course pages (5: bhakti-shastri, etc.)
- `/contact` — Contact form
- `/darshan` — Photo gallery (dynamic from `media/` directory)
- `/forums` — Forum listing
- `/resources` — Resources
- `/seva` — Redirects to donation
- `/sitemap` — XML sitemap

**Yatra Pages:**
- `/yatra/index.php` — Yatra listing
- `/yatra/detail.php?slug=X` — Yatra detail
- `/yatra/panihati` — Panihati specific (delegates to Panihati module)

---

## 9. RBAC Module

**Responsibility:** Role-based access control — roles, permissions, permission matrix UI.

**Location:** `modules/RBAC/`

**Owned Tables:**
- `rbac_roles` — 11 role definitions
- `rbac_permissions` — 55 permission definitions
- `rbac_role_permissions` — Role ↔ Permission assignments
- `rbac_user_roles` — Admin ↔ Role assignments (replaces `admins.role`)

**Key Classes:**
- `RbacService.php` — Core RBAC logic (permission checking, CRUD)
- `PermissionRegistry.php` — Permission definitions registry

**Admin Pages:**
- `/admin/roles` — Role listing (super_admin only)
- `/admin/role-edit` — Create/edit role with permission matrix (super_admin only)
- `/admin/permissions` — Read-only permission reference (super_admin only)

**Tests:**
- 74 PHPUnit tests (in-memory SQLite), 505 assertions
- `tests/Unit/PermissionRegistryTest.php` — 14 tests
- `tests/Unit/RbacServiceTest.php` — 55 tests
- `tests/Unit/RbacTestHelper.php` — Seed data helper

---

## Dependency Chain

```
Kernel ──► All modules depend on Kernel
    │
    ├── Donation ──► Booking (references donation_transactions)
    ├── Sudamaseva ──► Kernel (standalone tables)
    ├── Panihati ──► Kernel (standalone tables)
    ├── Booking ──► Donation (via transaction reference)
    ├── Festivals ──► Donation (via donation_causes table)
    ├── Blogs ──► Kernel (standalone table)
    ├── Content ──► Kernel (file-based, no DB)
    └── RBAC ──► Kernel (standalone tables, only DB from Kernel)
```

---

## Permission Modules (55 permissions across 13 modules)

| Module | Slug | Description |
|--------|------|-------------|
| Dashboard | `dashboard` | Admin dashboard overview |
| Donations | `donations` | Transaction logs, cause management |
| Festivals | `festivals` | Festival/cause listing & detail |
| Seva Catalog | `seva_catalog` | Master seva catalog management |
| Blogs | `blogs` | Blog posts & content management |
| Bookings | `bookings` | Puja & Yagya bookings |
| Panihati Yatra | `panihati` | Yatra registration & management |
| Sudamaseva | `sudamaseva` | Subscription donation management |
| Reports | `reports` | Donation reports & dashboards |
| Devotees | `devotees` | Devotee management |
| Volunteers | `volunteers` | Volunteer management |
| Events | `events` | Special events management |
| Audit Logs | `audit_logs` | System audit log viewing |

Each module supports 5 actions: `view`, `create`, `edit`, `delete`, `export`.
