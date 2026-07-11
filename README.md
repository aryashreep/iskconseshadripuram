# ISKCON The Palace Temple of Lord Jagannath

Official website for ISKCON Seshadripuram, Bangalore — the Palace Temple of Lord Jagannath.

## Overview

A full-featured temple website for ISKCON Seshadripuram, Bangalore with donation management (one-time + subscriptions), puja/yagya booking, festival listings, yatra registration, and an admin panel with role-based access control. Built with PHP 8, MySQL, and vanilla JavaScript on shared hosting (cPanel/Laragon).

## Documentation Index

This project maintains comprehensive documentation for AI coding assistants. Key documents:

| Document | Purpose |
|----------|---------|
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | System design, module layout, key patterns |
| [`SECURITY.md`](SECURITY.md) | Security policy, OWASP Top 10, authentication, authorization |
| [`CODING_STANDARDS.md`](CODING_STANDARDS.md) | Coding conventions, patterns, templates |
| [`WORKFLOWS.md`](WORKFLOWS.md) | Business workflows (donations, bookings, admin) |
| [`MODULE_INDEX.md`](MODULE_INDEX.md) | Module index with responsibilities, tables, entry points |
| [`DOCUMENTATION_POLICY.md`](DOCUMENTATION_POLICY.md) | Documentation maintenance policy |
| [`docs/API.md`](docs/API.md) | All API endpoints with request/response schemas |
| [`docs/DATABASE.md`](docs/DATABASE.md) | Database schema, tables, relationships |
| [`docs/ADMIN.md`](docs/ADMIN.md) | Admin panel navigation, RBAC roles, permission API |
| [`docs/DEVELOPER.md`](docs/DEVELOPER.md) | Fresher's guide (setup, architecture, common tasks) |
| [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) | Production deployment checklist |
| [`docs/TESTING.md`](docs/TESTING.md) | Testing guide (E2E + PHPUnit) |
| [`docs/DONATIONS.md`](docs/DONATIONS.md) | Donation system details |

### Module Documentation
| Module | Docs |
|--------|------|
| [Donation](modules/Donation/README.md) | Payment system, reports, seva catalog |
| [Kernel](modules/Kernel/README.md) | Shared infrastructure (config, DB, auth) |
| [Sudamaseva](modules/Sudamaseva/README.md) | Recurring/manual subscriptions |
| [Panihati](modules/Panihati/README.md) | Yatra registration & admin |
| [Booking](modules/Booking/README.md) | Puja/yagya booking |
| [Festivals](modules/Festivals/README.md) | ~70 festival pages |
| [Blogs](modules/Blogs/README.md) | Blog posts & admin |
| [Content](modules/Content/README.md) | Static content pages |
| [RBAC](modules/RBAC/README.md) | Role-based access control |

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8, MySQL |
| Frontend | HTML, CSS, Vanilla JS |
| Charts | Chart.js (dashboard visualizations) |
| Payments | Razorpay (test + live) |
| Build | Node.js (terser + lightningcss) |
| Testing | Playwright (E2E) + PHPUnit (unit) |
| Hosting | Shared (cPanel/Apache) |
| Auth | RBAC with 11 roles, 55 permissions across 13 modules |

## Project Structure

```
├── admin/                  # Admin panel (wrappers)
│   ├── partials/           # Admin header/footer
│   ├── admins.php          # Admin user management + RBAC role display
│   ├── admin-edit.php      # Create/edit admin with multi-role assignment
│   ├── roles.php           # RBAC role listing (super_admin only)
│   ├── role-edit.php       # Create/edit role with permission matrix
│   ├── permissions.php     # Permission reference (read-only)
│   ├── dashboard.php       # Role-specific dashboards
│   ├── report-dashboard.php    # Donation dashboard (charts, KPIs)
│   ├── donations.php       # Transaction logs with filters
│   ├── seva-catalogue.php  # Master seva management
│   ├── festivals.php       # Festival management
│   ├── bookings.php        # Puja/Yagya booking management
│   └── panihati-*.php      # Panihati Yatra management (8 pages)
├── modules/
│   ├── RBAC/               # Role-based access control
│   │   ├── Admin/          # roles.php, role-edit.php, permissions.php
│   │   ├── RbacService.php # Core RBAC logic
│   │   ├── PermissionRegistry.php # 55 permission definitions
│   │   └── database/migrations/
│   └── ... (Donation, Panihati, Booking, etc.)
├── api/                    # REST endpoints
├── assets/                 # Stylesheets, images, JS
├── database/               # Schema, seeds, migrations
├── tests/
│   ├── *.spec.js           # Playwright E2E tests (172 tests)
│   └── Unit/               # PHPUnit unit tests (74 tests)
│       ├── PermissionRegistryTest.php
│       ├── RbacServiceTest.php
│       └── RbacTestHelper.php
```

## Quick Start

### Prerequisites

- PHP 8.0+ with PDO MySQL
- MySQL 5.7+ / MariaDB
- Composer
- Node.js 18+ (for build pipeline)
- Apache with mod_rewrite

### Installation

```bash
git clone <repo-url> isjm
cd isjm
composer install
npm install
cp .env.example .env
# Edit .env with your database and Razorpay credentials
mysql -u root -p isjm_donations < database/schema.sql
mysql -u root -p isjm_donations < database/seed.sql
npm run build
```

### Environment Variables

```env
DB_HOST=localhost
DB_NAME=isjm_donations
DB_USER=root
DB_PASS=

RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxx
RAZORPAY_TEST_MODE=true
```

## Key Features

### Public Website
- **Donations** — 68+ causes with tiered/cart/quantity forms
- **Puja Booking** — Online puja scheduling with Razorpay payments
- **Festival Listings** — Category-based festival pages (Grand Festivals, Ekadashi, Appearance/Disappearance Days)
- **Yatra Registration** — Panihati Yatra bus/vehicle booking with pickup locations
- **Checkout** — Unified cart system with localStorage

### Admin Panel
- **RBAC** — Role-based access control with 11 roles, 55 permissions across 13 modules
  - Role Management UI: create/edit roles with permission matrix (super_admin only)
  - Permission reference page: read-only catalog of all permissions by module
  - Multi-role assignment on admin user edit page
  - Permission-based sidebar visibility (users only see what they can access)
- **Dashboard** — Permission-role-specific overviews
- **Donation Dashboard** (`report-dashboard.php`) — Chart.js visualizations:
  - Monthly trend (dual-axis: revenue + count)
  - Revenue by category (doughnut with tooltips)
  - Top 10 activities (horizontal bar)
  - Donation count by category (vertical bar)
  - Category → Activity accordion hierarchy
  - Top 10 donors table
  - Day-of-week heatmap
  - Donor retention funnel (first-time vs returning)
  - Year-over-year comparison chart
  - Month-over-month trend indicators
  - CSV export
- **Category Report** — Aggregated by donation category
- **Activity Report** — Grouped by activity with search/filter
- **Seva Report** — Three-level hierarchy: Category → Activity → Seva
- **Transaction Logs** — Detailed transaction history with export
- **Panihati Yatra Manager** — KPI cards, year-over-year comparison, rankings charts
- **Seva Catalogue** — Master seva management with category picker
- **Blog/Festival Editor** — Content management

### Security
- Full OWASP Top 10 coverage — see [`SECURITY.md`](SECURITY.md) for details
- PDO prepared statements (SQL injection prevention)
- CSRF tokens on all forms and destructive GET actions
- RBAC with 11 roles, 55 permissions across 13 modules
- Rate-limited admin login (5 attempts / 15-min window)
- CSP, X-Frame-Options, HSTS, Referrer-Policy, Permissions-Policy headers
- Session fixation prevention (regeneration on login)
- Environment-based credential management (OWASP A02)
- Razorpay HMAC-SHA256 signature verification on all payments
- Webhook signature validation

## Database

Key tables:
- `donation_causes` — 68+ donation causes (activities)
- `donation_transactions` — Payment records with cause_id, seva_id, master_seva_id
- `master_seva_categories` — 10 top-level seva categories
- `master_sevas` — Single source of truth for all seva offerings
- `donation_cause_master_sevas` — Pivot linking causes to sevas with override support
- `donation_cause_sevas` — Legacy per-cause seva table (backward compatible)
- `panihati_yatra_registrations` — Travel bookings
- `booking_pujas` — Puja bookings
- `admins` — Admin users with roles (super_admin, editor, pujari, treasurer, travel_agent)

## Donation Reporting Hierarchy

The reporting system follows a three-level structure:

```
Category (Grand Festivals, Ekadashi, Services, etc.)
    ↓
Activity (Rath Yatra, Janmashtami, Food for Life, etc.)
    ↓
Seva (Flower Decoration, Annadanam, Rajbhog, etc.)
```

Each report page supports:
- Date range filtering
- CSV export
- Search/filter (Activity and Seva reports)
- Accordion expand/collapse
- Summary cards with totals

## Development

### Build Commands
```bash
npm run build          # Build all (JS + CSS minification + cache busting)
npm run build:js       # JS only
npm run build:css      # CSS only
```

### Testing

#### E2E Tests (Playwright)
```bash
npx playwright test                                    # All 172 E2E tests
npx playwright test tests/e2e-all-pages.spec.js        # Public pages
npx playwright test tests/e2e-admin.spec.js            # Admin pages
ADMIN_USER=admin ADMIN_PASS="isjm@admin" npx playwright test tests/e2e-admin.spec.js
```

#### Unit Tests (PHPUnit)
```bash
vendor/bin/phpunit                                     # All 74 RBAC unit tests
```

PHPUnit tests cover:
- `PermissionRegistry` — Module structure, permission count (55), slug/label format, sort order
- `RbacService` — Permission checking (hasPermission/hasAny/hasAll), super_admin bypass, role CRUD, permission CRUD, role-permission assignment, user-role assignment, edge cases

Tests use an in-memory SQLite database for full isolation — no external MySQL connection needed.

### Migrations
```bash
php database/migrations/<migration_name>.php
```

## Deployment

1. Run `npm run build` to generate minified assets
2. Upload all files except `node_modules/`, `tests/`, `scripts/`
3. Set environment variables on the server
4. Import database schema and run migrations

## License

© 2026 ISKCON The Palace Temple of Lord Jagannath, Seshadripuram. All rights reserved.
