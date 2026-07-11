# DEVELOPER.md — Fresher's Guide to the ISKCON Temple Website

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** [`CODING_STANDARDS.md`](../CODING_STANDARDS.md) (conventions), [`SECURITY.md`](../SECURITY.md) (security policy), [`WORKFLOWS.md`](../WORKFLOWS.md) (business flows), [`DEVELOPMENT_WORKFLOW.md`](../DEVELOPMENT_WORKFLOW.md) (process), [`DOCUMENTATION_POLICY.md`](../DOCUMENTATION_POLICY.md)

Welcome! This guide will help you understand the codebase, set up your local environment, and start contributing. Read this from top to bottom if you're new.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Quick Start (Installation)](#2-quick-start-installation)
3. [Architecture Overview](#3-architecture-overview)
4. [Module System](#4-module-system)
5. [The Wrapper Convention](#5-the-wrapper-convention)
6. [URL Routing (.htaccess)](#6-url-routing-htaccess)
7. [Database & Migrations](#7-database--migrations)
8. [Admin Panel & RBAC](#8-admin-panel--rbac)
9. [Frontend: CSS, JS & Build Pipeline](#9-frontend-css-js--build-pipeline)
10. [Payment Flow (Razorpay)](#10-payment-flow-razorpay)
11. [Testing Guide](#11-testing-guide)
12. [Coding Conventions & Best Practices](#12-coding-conventions--best-practices)
13. [Common Tasks for Freshers](#13-common-tasks-for-freshers)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Project Overview

**ISKCON The Palace Temple of Lord Jagannath** — official website for ISKCON Seshadripuram, Bangalore.

### Tech Stack at a Glance

| Layer | What We Use |
|-------|-------------|
| **Backend** | PHP 8 (vanilla — no framework like Laravel) |
| **Database** | MySQL / MariaDB via PDO |
| **Frontend** | HTML, Vanilla CSS + JavaScript |
| **Charts** | Chart.js (loaded from CDN on dashboard pages) |
| **Payments** | Razorpay (test + live modes) |
| **Build** | Node.js (terser for JS, lightningcss for CSS) |
| **Testing** | Playwright (E2E, 172 tests) + PHPUnit (unit, 74 tests) |
| **Auth** | Session-based + RBAC (role-based access control) |
| **Hosting** | Shared cPanel/Apache (no Node.js runtime in production) |

### Key URLs

| Environment | URL |
|-------------|-----|
| Local (Laragon) | `http://isjm.test:8080` |
| Admin Panel | `http://isjm.test:8080/admin/login` |
| Admin Credentials | username: `admin`, password: `isjm@admin` |

---

## 2. Quick Start (Installation)

### Step 1: Install Prerequisites

```
1. PHP 8.0+ (with PDO MySQL extension)
2. MySQL 5.7+ or MariaDB
3. Composer (PHP package manager)
4. Node.js 18+ (for build pipeline)
5. Apache with mod_rewrite (Laragon provides all of the above)
```

**Recommended**: Use [Laragon](https://laragon.org/) on Windows — it bundles Apache, PHP, MySQL, and Composer in one click.

### Step 2: Clone & Setup

> **💡 Terminal Tip:** This project is developed on Windows with Laragon. The commands below use bash syntax — use **Git Bash** (included with Laragon/Node.js) or WSL. For Windows CMD/PowerShell, replace `cp` with `copy`, `find` with `dir /s`, and use backslashes for paths.

```bash
# Clone the repository
git clone <repo-url> isjm
cd isjm

# Install PHP dependencies (vlucas/phpdotenv for .env, phpunit for testing)
composer install

# Install Node.js dependencies (terser, lightningcss, playwright)
npm install

# Create .env file
# Option A: Copy from example if it exists
cp .env.example .env 2>/dev/null || true
# Option B: If no .env.example exists, create manually:
# Paste the block from Step 4 below into .env
```

> **Note**: If `.env.example` doesn't exist in the repo, manually create `.env` with the contents shown in Step 4 below.

### Step 3: Create the Database

```bash
# Open phpMyAdmin (http://localhost/phpmyadmin) or use CLI:
mysql -u root -p

# In MySQL prompt:
CREATE DATABASE isjm_donations CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE isjm_donations;
SOURCE database/schema.sql;
SOURCE database/seed.sql;
```

### Step 4: Configure .env

Edit `.env` in the project root:

```env
DB_HOST=localhost
DB_NAME=isjm_donations
DB_USER=root
DB_PASS=

RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxx
RAZORPAY_TEST_MODE=true
```

> **Note**: Razorpay test keys are available for free at https://dashboard.razorpay.com. For local development, test mode is fine.

### Step 5: Build Assets

```bash
npm run build
```

> **Ready to log in?** After building, proceed to create an admin user and run RBAC migrations below.

### Step 6: Create an Admin User

The database seed (`database/seed.sql`) only seeds donation data — it does **not** create admin accounts. You need to create one manually:

**Option A — Via migration** (if one exists):
```bash
php database/migrations/seed_admins.php 2>/dev/null || echo "No admin seed migration, use Option B"
```

**Option B — Via phpMyAdmin / SQL**:
```sql
INSERT INTO `admins` (`username`, `password_hash`, `email`, `full_name`, `role`) 
VALUES (
  'admin', 
  '$2y$10$BW7IdKWlqP04/CaUUxivVul',  -- bcrypt hash of 'admin123' (truncated — generate in PHP)
  'admin@example.com',
  'Administrator', 
  'super_admin'
);
```

**Option C — Via PHP** (run this once):
```bash
php -r "
require 'config.php';
\$hash = password_hash('isjm@admin', PASSWORD_BCRYPT);
\$db = getDB();
\$db->prepare('INSERT INTO admins (username, password_hash, email, full_name, role) VALUES (?, ?, ?, ?, ?)')
   ->execute(['admin', \$hash, 'admin@example.com', 'Administrator', 'super_admin']);
echo 'Admin user created with password: isjm@admin\n';
"
```

### Step 7: Run RBAC Migrations

```bash
php modules/RBAC/database/migrations/001_create_rbac_tables.php
php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php
php modules/RBAC/database/migrations/003_migrate_existing_admins.php
```

### Step 8: Open in Browser

- Website: `http://isjm.test:8080`
- Admin Login: `http://isjm.test:8080/admin/login`
- Username: `admin`, Password: `isjm@admin`

---

## 3. Architecture Overview

### Key Concept: `$pageType` Coverage

All ~125+ public content pages now set `$pageType` before including the header. This variable tells `schema.php` (included from `footer.php`) which Schema.org structured data to output. Types include `'home'`, `'festival'`, `'blog'`, `'donate'`, `'gallery'`, `'contact'`, `'about'`, `'service'`, `'course'`, `'booking'`, `'yatra'`, and `'default'`.

See [`docs/SEO.md`](SEO.md) for the full schema type reference and how to add structured data to a new page.

### High-Level Diagram

```
Browser (Devotee)
    │ HTTPS
    ▼
Apache + PHP 8
    │
    ├── config.php (env, DB connection, constants)
    ├── .htaccess (URL rewriting, security headers)
    │
    ├── Public Pages (services/, about/, donate/, festivals/)
    │   └── include partials/header.php + partials/footer.php
    │
    ├── Admin Pages (admin/dashboard, admin/donations)
    │   └── include admin/partials/header.php + footer.php
    │       └── admin/auth-check.php (session + permission guard)
    │
    ├── API Endpoints (api/create-order.php, api/webhook.php)
    │   └── JSON in, JSON out
    │
    └── Backward-Compatible Wrappers → modules/<Module>/
        └── Actual code lives here

Database (MySQL via getDB() PDO singleton)
    │
Razorpay (Payment Gateway)
```

### Directory Structure Explained

```
isjm/
├── config.php              ← Wrapper (delegates to modules/Kernel/config.php)
├── .htaccess               ← Apache URL rewriting + security headers
├── index.php               ← Homepage wrapper
│
├── admin/                  ← Wrappers (delegate to modules/<Module>/Admin/)
├── api/                    ← Wrappers (delegate to modules/<Module>/api/)
├── assets/                 ← CSS, JS, images (source files)
├── database/               ← Schema SQL, seed data, migrations
├── includes/               ← Wrappers (delegate to modules/Kernel/includes/)
├── partials/               ← Wrappers (delegate to modules/Kernel/partials/)
│
├── modules/                ★ ACTUAL CODE LIVES HERE ★
│   ├── Donation/           Payment system, reports, seva catalog
│   ├── Panihati/           Yatra registration & admin
│   ├── Booking/            Puja/yagya/guest house booking
│   ├── Festivals/          ~70 public festival pages
│   ├── Blogs/              Blog posts & admin
│   ├── Content/            Static pages (about, services, courses, etc.)
│   ├── RBAC/               Role-based access control
│   └── Kernel/             Shared infrastructure (config, DB, partials)
│
├── tests/                  ★ TEST FILES ★
│   ├── *.spec.js           Playwright E2E tests (172 tests)
│   └── Unit/*.php          PHPUnit unit tests (74 tests)
│
├── composer.json           PHP dependencies (phpdotenv, phpunit)
├── package.json            Node.js dependencies (terser, lightningcss, playwright)
├── phpunit.xml             PHPUnit configuration
└── playwright.config.js    Playwright configuration
```

### Key Principle: All Original Files Are Wrappers

Every PHP file in the root, `admin/`, `api/`, `partials/`, `includes/`, `services/`, `about/`, `courses/`, `blogs/`, `booking/`, `festivals/`, etc. is a **one-line wrapper** that delegates to the actual file in `modules/`.

This means:
- ✅ Old links and bookmarks still work
- ✅ CWD-based includes (`include '../partials/header.php'`) still work
- ✅ You can edit actual code in `modules/` without moving files

---

## 4. Module System

Each module follows a standard structure:

```
modules/<ModuleName>/
├── Admin/           Admin panel pages (if applicable)
├── api/             API endpoint handlers (if applicable)
├── assets/          Module-owned CSS/JS (if applicable)
├── content/         Public-facing PHP pages
├── src/             PHP classes (Repository, Service, etc.)
├── database/        Migrations (if applicable)
├── README.md        Module documentation
└── routes.php       Route definitions (planned, not yet fully active)
```

### Module Responsibilities

| Module | Purpose | Key Files |
|--------|---------|-----------|
| **Kernel** | Shared infrastructure — config, DB, auth, layout partials | `config.php`, `db.php`, `SessionGuard.php`, `header.php`, `footer.php` |
| **Donation** | Donation causes, payments, reports, seva catalog | `DonationRepository.php`, `DonationService.php`, `DonationRenderer.php` |
| **Panihati** | Panihati Yatra registration system | `panihati-helpers.php`, `Admin/panihati-yatra.php` |
| **Booking** | Puja, yagya, and guest house booking | `admin/bookings.php`, `api/create-booking-order.php` |
| **Festivals** | All festival content pages (~70 files) | `content/grand-festivals/*.php` |
| **Blogs** | Blog content management | `Admin/blogs.php`, `content/index.php` |
| **Content** | Static content pages | `content/about/*`, `content/services/*`, `content/courses/*` |
| **RBAC** | Role-based access control system | `RbacService.php`, `PermissionRegistry.php`, `Admin/roles.php` |
| **Sudamaseva** | Recurring + manual seva donation system | `content/index.php` (signup w/ mode toggle), `content/lookup.php`, `content/dashboard.php`, `api/enroll.php`, `api/create-subscription.php`, `api/verify-order.php`, `api/verify-payment.php`, `api/webhook.php`, `SudamasevaService.php`, `SudamasevaRepository.php` |

### Module Dependency Chain

```
Content ─┐
Festivals┤
Blogs────┤
Booking──┤──→ all use Kernel (config, DB, header/footer)
Donation─┤
Panihati─┘
    │
    └── Donation (reuses transaction tables)
    │
RBAC (standalone, only depends on Kernel for DB connection)
```

---

## 5. The Wrapper Convention

Every file moved to `modules/` has a **wrapper** at its original path.

### How Wrappers Work

Original path: `admin/dashboard.php`
Actual code: `modules/Kernel/Admin/dashboard.php`

The wrapper at `admin/dashboard.php` contains only:

```php
<?php
/**
 * Backward-compatibility wrapper.
 * File has been moved to modules/Kernel/Admin/dashboard.php.
 */
require_once __DIR__ . '/../modules/Kernel/Admin/' . basename(__FILE__);
```

### Why This Matters

When you `include '../partials/header.php'` from `admin/dashboard.php`:
- The wrapper's CWD is still `admin/`
- So `../partials/header.php` resolves to `admin/../partials/header.php` = `partials/header.php` (the wrapper)
- Which delegates to `modules/Kernel/partials/header.php`

**Everything works without any path changes.** This is the key design decision that made the migration seamless.

### When You Create a New File

**You don't need to create wrappers** for new files! Only existing files have wrappers. When you add a new admin page, just put it directly in `admin/` (or in `modules/<Module>/Admin/` and then create a wrapper in `admin/`).

---

## 6. URL Routing (.htaccess)

The `.htaccess` file handles all URL rewriting. Key rules:

```apache
# Clean URLs: /page → /page.php
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}.php -f
RewriteRule ^(.+?)/?$ $1.php [L]

# Donation pages: /donate/cause-slug → donate/donate-seva.php?cause=cause-slug
RewriteRule ^donate/([a-z0-9\-]+)/?$ donate/donate-seva.php?cause=$1 [L,QSA]

# Blog pages: /blogs/blog-slug → blogs/detail.php?slug=blog-slug
RewriteRule ^blogs/([a-z0-9\-]+)/?$ blogs/detail.php?slug=$1 [L,QSA]

# Yatra pages: /yatra/package-name → yatra/detail.php?slug=package-name
RewriteRule ^yatra/([^/]+)/?$ yatra/detail.php?slug=$1 [L,QSA]

# Puja/Yagya detail: /booking/puja/slug → booking/puja/detail.php?slug=slug
RewriteRule ^booking/puja/([a-z0-9\-]+)/?$ booking/puja/detail.php?slug=$1 [L,QSA]
RewriteRule ^booking/yagya/([a-z0-9\-]+)/?$ booking/yagya/detail.php?slug=$1 [L,QSA]

# Festival detail pages (SEO-friendly URLs)
# Actual hardcoded .php files (e.g. festivals/grand-festivals/janmashtami.php) take priority via !-f check
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^festivals/grand-festivals/([^/]+)/?$ festivals/detail.php?slug=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^festivals/ekadashi/([^/]+)/?$ festivals/detail.php?slug=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^festivals/appearance/([^/]+)/?$ festivals/detail.php?slug=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^festivals/disappearance/([^/]+)/?$ festivals/detail.php?slug=$1 [L,QSA]

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^festivals/events/([^/]+)/?$ festivals/detail.php?slug=$1 [L,QSA]

# Sudamaseva Module — Public Page Rewrites
RewriteRule ^sudamaseva/?$ sudamaseva/index.php [L,QSA]
RewriteRule ^sudamaseva/lookup/?$ sudamaseva/lookup.php [L,QSA]
RewriteRule ^sudamaseva/dashboard/?$ sudamaseva/dashboard.php [L,QSA]

# Sudamaseva API rewrites
RewriteRule ^api/sudamaseva/(create-subscription|verify-payment|webhook|lookup|enroll|create-order|verify-order)/?$ api/sudamaseva-$1.php [L,QSA]

# Sitemap: /sitemap.xml → sitemap.php
RewriteRule ^sitemap\.xml$ sitemap.php [L]
```

### URL Pattern Summary

| URL Pattern | Maps To | Example |
|-------------|---------|---------|
| `/page` | `/page.php` | `/about` → `/about/index.php` (directory index) |
| `/donate/{slug}` | `donate/donate-seva.php?cause={slug}` | `/donate/janmashtami` |
| `/blogs/{slug}` | `blogs/detail.php?slug={slug}` | `/blogs/my-post` |
| `/yatra/{slug}` | `yatra/detail.php?slug={slug}` | `/yatra/vrindavan` |
| `/booking/puja/{slug}` | `booking/puja/detail.php?slug={slug}` | `/booking/puja/laxmi-narayan-puja` |
| `/festivals/grand-festivals/{slug}` | `festivals/detail.php?slug={slug}` (or hardcoded `.php` page if exists) | `/festivals/grand-festivals/janmashtami` |
| `/festivals/ekadashi/{slug}` | `festivals/detail.php?slug={slug}` | `/festivals/ekadashi/putrada` |
| `/festivals/appearance/{slug}` | `festivals/detail.php?slug={slug}` | `/festivals/appearance/sri-advaita-acharya-appearance` |
| `/festivals/disappearance/{slug}` | `festivals/detail.php?slug={slug}` | `/festivals/disappearance/srila-prabhupada-disappearance` |
| `/festivals/events/{slug}` | `festivals/detail.php?slug={slug}` | `/festivals/events/caturmasya` |
| `/sudamaseva` | `sudamaseva/index.php` | Signup w/ mode toggle |
| `/sudamaseva/lookup` | `sudamaseva/lookup.php` | Find existing donation |
| `/sudamaseva/dashboard` | `sudamaseva/dashboard.php` | Donor dashboard (requires `?donor_id=X`) |
| `/admin/page` | `/admin/page.php` (generic) | `/admin/dashboard` → `/admin/dashboard.php` |

### Important Gotcha

**Always use absolute URLs** for pagination and links with query parameters:

```php
// ❌ WRONG — relative ?page=2 gets stripped by .htaccess rewrite
<a href="?page=2">Next</a>

// ✅ CORRECT — use BASE_URL
<a href="<?php echo BASE_URL; ?>darshan?page=2">Next</a>
```

---

## 7. Database & Migrations

### Connection

```php
$db = getDB(); // Returns PDO singleton (from modules/Kernel/includes/db.php)
```

Always use prepared statements — never concatenate user input into SQL:

```php
// ✅ CORRECT
$stmt = $db->prepare("SELECT * FROM donation_causes WHERE slug = ?");
$stmt->execute([$slug]);
$cause = $stmt->fetch();

// ❌ WRONG — SQL injection risk
$cause = $db->query("SELECT * FROM donation_causes WHERE slug = '$slug'")->fetch();
```

### Key Tables

| Table | Purpose | Notes |
|-------|---------|-------|
| `donation_causes` | 74 donation activities/festivals | Has `category` field (festival, ekadashi, etc.) |
| `donation_transactions` | Payment records | **NEVER TRUNCATE IN PRODUCTION** |
| `master_seva_categories` | 10 seva categories | Deity Sevas, Puja & Ritual, Festival, etc. |
| `master_sevas` | 363+ deduplicated seva offerings | Single source of truth |
| `donation_cause_master_sevas` | Pivot: cause ↔ seva links | With override_amount support |
| `donation_cause_sevas` | Legacy per-cause sevas | Backward compatible, don't delete |
| `rbac_roles` | 11 RBAC roles | Managed via admin UI |
| `rbac_permissions` | 55 RBAC permissions | Managed via admin UI |
| `rbac_role_permissions` | Role ↔ Permission assignments | Many-to-many |
| `rbac_user_roles` | Admin ↔ Role assignments | Replaces `admins.role` column |
| `admins` | Admin users | `role` column is legacy — use `rbac_user_roles` |
| `booking_pujas` | Puja/yagya bookings | Links to `donation_transactions.id` |
| `panihati_yatra_registrations` | Travel bookings | **NEVER TRUNCATE IN PRODUCTION** |
| `login_attempts` | Rate limiting tracking | Auto-cleared on successful login |

### Migration Pattern

Migrations are PHP scripts, run from the command line:

```bash
php database/migrations/your-migration.php
```

Migration template:

```php
<?php
require_once __DIR__ . '/../../config.php';

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Always idempotent — check before inserting
$check = $db->prepare("SELECT COUNT(*) FROM table WHERE slug = ?");
$check->execute([$slug]);
if ($check->fetchColumn() > 0) {
    echo "Already exists, skipping.\n";
} else {
    // Insert
    $db->prepare("INSERT INTO ...")->execute([...]);
    echo "Inserted.\n";
}
```

### RBAC Migrations (run in order)

```bash
php modules/RBAC/database/migrations/001_create_rbac_tables.php
php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php
php modules/RBAC/database/migrations/003_migrate_existing_admins.php
```

---

## 8. Admin Panel & RBAC

### Authentication Flow

```
1. User visits /admin/login
2. Submits username + password
3. login.php verifies against admins table (password_hash via password_verify)
4. If successful:
   - Regenerates session ID (prevents fixation)
   - Stores admin_id, admin_username, admin_role in session
   - Loads permissions from rbac tables into $_SESSION['admin_permissions']
   - Redirects to /admin/dashboard
5. Every admin page includes admin/auth-check.php
6. auth-check.php calls SessionGuard::requireLogin()
7. SessionGuard checks $_SESSION['admin_logged_in']
8. Then checks permissions via hasPermission() / requirePermission()
```

### Page Template (Admin)

Every admin page follows this pattern:

```php
<?php
$pageTitle = 'Page Title';
$activePage = 'dashboard'; // Must match the 'active' check in sidebar

// Include auth + header (this handles permission checks)
include 'partials/header.php';

// Your content here
?>

<div class="admin-page-header">
  <h1>Page Title</h1>
</div>

<div class="admin-card">
  <!-- Content -->
</div>

<?php include 'partials/footer.php'; ?>
```

### Permission Checking API

Available globally in all admin pages (after including `auth-check.php`):

| Function | Purpose | Example |
|----------|---------|---------|
| `hasPermission('module.action')` | Boolean check — use for UI visibility | `if (hasPermission('donations.edit')): ?>` |
| `requirePermission('module.action')` | Blocks with 403 if not granted | `requirePermission('festivals.view');` |
| `hasAnyPermission(['a', 'b'])` | Boolean — any of the given perms | `if (hasAnyPermission(['donations.view', 'reports.view'])):` |
| `requireAnyPermission(['a', 'b'])` | Blocks with 403 if none granted | `requireAnyPermission(['panihati.view', 'panihati.edit']);` |
| `hasRole(['super_admin'])` | Legacy — prefer permissions | `if (hasRole(['super_admin'])):` |

Super Admin bypasses all checks automatically.

### Admin Sidebar Structure

The sidebar is defined in `modules/Kernel/Admin/partials/header.php`. It has two types of items:

**Flat items** (single link):
```php
<li class="admin-nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
  <a href="admin/dashboard">
    <i class="fas fa-chart-line"></i> Dashboard
  </a>
</li>
```

**Group items** (collapsible accordion with sub-nav):
```php
<?php
  $subPages = ['donations', 'report-dashboard', 'report-category'];
  $isActive = in_array($activePage, $subPages);
?>
<li class="admin-nav-group <?php echo $isActive ? 'active' : ''; ?>">
  <a href="admin/donations">
    <span><i class="fas fa-hand-holding-heart"></i> Donations</span>
    <i class="fas fa-chevron-down nav-chevron"></i>
  </a>
  <ul class="admin-subnav">
    <li class="admin-subnav-item <?php echo $activePage === 'donations' ? 'active' : ''; ?>">
      <a href="admin/donations"><i class="fas fa-list"></i> Transaction Logs</a>
    </li>
  </ul>
</li>
```

**Accordion Behavior** (handled by inline JS in header.php):
- First group opens by default
- Active group (current page) auto-opens
- Clicking a parent link toggles the group and closes all others

### Adding a New Admin Page

```php
<?php
// 1. Set page variables
$pageTitle = 'My New Page';
$activePage = 'my-page'; // This must match the sidebar active check

// 2. Enforce permission
requirePermission('my_module.view');
// OR requireRole(['super_admin', 'editor']);

// 3. Include header
include 'partials/header.php';
?>

<!-- 4. Content -->
<div class="admin-page-header">
  <h1>My New Page</h1>
</div>

<?php include 'partials/footer.php'; ?>
```

Then add the nav item in `modules/Kernel/Admin/partials/header.php`.

---

## 9. Frontend: CSS, JS & Build Pipeline

### CSS Architecture

CSS uses **CSS custom properties (variables)** defined in `assets/css/style.css`:

```css
:root {
  --primary: #c86b1f;
  --accent: #d4af37;
  --maroon: #7b1e1e;
  --cream: #f8f1e7;
  --dark: #2c1b12;
  --font-heading: 'Cinzel', serif;
  --font-subheading: 'Cormorant Garamond', serif;
  --font-body: 'Poppins', sans-serif;
}
```

**Always use CSS variables** — never hardcode colors, fonts, or spacing values.

### Asset Loading

Use the `asset()` helper to get cache-busted URLs:

```php
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<script src="<?= asset('assets/js/main.js') ?>"></script>
```

This reads from `assets/dist/manifest.json` (generated by the build script) and returns content-hashed filenames. Falls back to original if manifest doesn't exist.

### Base URL for All Assets

```php
<base href="<?php echo BASE_URL; ?>">
```

This tag in `<head>` means all relative URLs in the page resolve relative to the site root — so you don't need to prefix every link.

### Build Pipeline

```bash
npm run build          # Build all (JS + CSS minification)
npm run build:js       # JS only
npm run build:css      # CSS only
npm run build -- --watch  # Watch mode
npm run build -- --clean  # Clean dist/
```

The build script:
1. Reads source files from `assets/js/` and `assets/css/`
2. Minifies with terser (JS) and lightningcss (CSS)
3. Generates content-hashed filenames (e.g., `style.a1b2c3d4.css`)
4. Writes `assets/dist/manifest.json` with the mapping

### JavaScript Conventions

- **Vanilla JS only** — no jQuery, no React, no frameworks
- Cart system uses `localStorage` and is loaded globally via `cart.js`
- Donation forms use `donate.js`
- Checkout page uses `checkout.js`
- Event handlers use `addEventListener` (not inline `onclick`)

---

## 10. Payment Flow (Razorpay)

### Standard Donation Flow

```
1. Donor visits /donate/{cause-slug}
2. Selects a seva offering (amount)
3. Fills in name, email, phone
4. Clicks "Donate Now"
5. JavaScript sends POST to /api/create-order.php
   → Server verifies amount against catalog
   → Creates Razorpay order
   → Returns order_id
6. Razorpay checkout modal opens
7. Donor completes payment
8. On success, JS sends POST to /api/verify-payment.php
   → Server verifies HMAC-SHA256 signature
   → Updates donation_transactions.payment_status = 'paid'
   → Shows success page
9. Razorpay webhook (server-to-server) at /api/webhook.php
   → Provides secondary verification (authoritative source)
```

### Sudamaseva (Auto Monthly + Pay Monthly) Flow

Sudamaseva supports two payment modes:

**Auto Monthly (Recurring):**
```
  /api/sudamaseva/create-subscription → Creates Razorpay subscription
  /api/sudamaseva/verify-payment      → Verifies + creates donor record (HMAC: {sub_id}|{pay_id})
  /api/sudamaseva/webhook             → Handles subscription.charged, .completed, .halted
```

**Pay Monthly (Manual):**
```
  /api/sudamaseva/enroll              → Creates donor + manual subscription + Razorpay Order
  /api/sudamaseva/create-order        → Creates Razorpay Order for next installment
  /api/sudamaseva/verify-order        → Verifies payment (HMAC: {order_id}|{pay_id})
```

**Donor Management:**
```
  /sudamaseva/lookup                  → Search by phone → redirect to dashboard
  /sudamaseva/dashboard?donor_id=X    → View subscription, installment grid, pay now
  /sudamaseva                         → Signup with mode toggle (Auto Monthly / Pay Monthly)
```

#### Key Differences Between Modes

| Aspect | Auto Monthly | Pay Monthly |
|--------|-------------|-------------|
| Razorpay Object | Subscription (auto-debit) | Order (one-time) |
| HMAC Format | `{subscription_id}|{payment_id}` | `{order_id}|{payment_id}` |
| Billing | Razorpay auto-charges monthly | Donor clicks "Pay Now" each month |
| Dashboard | View-only schedule | Installment grid with "Pay Now" buttons |
| `collection_mode` | `'recurring'` | `'manual'` |
| `payment_source` | `'subscription_charge'` | `'manual_order'` |

### Key Security Rules

- **Amounts are always verified server-side** — never trust client-sent amounts
- **HMAC signatures** are verified on every payment callback
- **Webhooks** validate `X-Razorpay-Signature` header
- **No card data** is stored on our server — Razorpay handles PCI compliance

---

## 11. Testing Guide

### E2E Tests (Playwright) — 172 Tests

Run with the Laragon server running:

```bash
# All E2E tests
npx playwright test

# Single file
npx playwright test tests/puja-booking.spec.js

# With admin credentials (for admin tests)
ADMIN_USER=admin ADMIN_PASS="isjm@admin" npx playwright test tests/e2e-admin.spec.js

# With visible browser (for debugging)
npx playwright test --headed
```

**Test files** (in `tests/`):

| File | Tests | What It Covers |
|------|-------|----------------|
| `e2e-all-pages.spec.js` | 64 | All public pages (200 status), homepage, admin redirect, assets, security headers |
| `e2e-admin.spec.js` | 54 | Admin login, access control, protected pages, logout |
| `puja-booking.spec.js` | 11 | Puja listing, detail, offering selection, form validation |
| `yagya-booking.spec.js` | 11 | Yagya listing, tiers, booking modal |
| `panihati-yatra.spec.js` | 15 | Registration form, pricing, travel mode toggle |
| `payment-flow.spec.js` | 16 | Donate pages, API endpoints, Razorpay script load |

### PHPUnit Tests (Unit) — 74 Tests

No server required — uses in-memory SQLite:

```bash
# All PHPUnit tests
vendor/bin/phpunit

# Single test class
vendor/bin/phpunit tests/Unit/RbacServiceTest.php

# With filter
vendor/bin/phpunit --filter=testCreateRole
```

**Test files** (in `tests/Unit/`):

| File | Tests | What It Covers |
|------|-------|----------------|
| `PermissionRegistryTest.php` | ~17 | Module structure, 55 permissions, slug/label format, sort order |
| `RbacServiceTest.php` | ~55 | Permission checking, CRUD, role assignment, super_admin bypass, edge cases |
| `RbacTestHelper.php` | Helper | Creates in-memory SQLite with RBAC schema + seed data (9 roles, 18 permissions, 7 admins) |

### Writing a New E2E Test

```js
const { test, expect } = require('@playwright/test');

test.describe('Feature Name', () => {
  test('does something specific', async ({ page }) => {
    await page.goto('/my-page');
    await expect(page.locator('h1')).toContainText('Expected Title');
    await expect(page.locator('.my-class')).toBeVisible();
  });
});
```

### Writing a New PHPUnit Test

```php
<?php
namespace Isjm\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MyClass::class)]
class MyClassTest extends TestCase
{
    #[Test]
    public function method_scenario_expectedBehavior(): void
    {
        $result = MyClass::someMethod();
        $this->assertSame('expected', $result);
    }
}
```

---

## 12. Coding Conventions & Best Practices

### PHP

| Rule | Details |
|------|---------|
| **Framework** | None — we use vanilla PHP. No Laravel, no Symfony. |
| **SQL** | Always use prepared statements with PDO. Never concatenate user input. |
| **Output** | Use `htmlspecialchars()` for all user-generated content in HTML context. |
| **Errors** | Never expose `$e->getMessage()` to users. Log server-side, show generic messages. |
| **Session** | Start with `session_start()` in `bootstrap.php` or call `SessionGuard::init()`. |
| **CSRF** | Every form must include a CSRF token: `<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">` |
| **File Structure** | New code goes in `modules/<Module>/`. Use wrappers only for backward compat. |
| **Naming** | `$camelCase` for variables, `snake_case` for DB columns, `PascalCase` for classes. |
| **Includes** | Use `require_once` for dependencies, `include` for optional partials. |
| **Base URL** | Always use `BASE_URL` constant for links: `<a href="<?php echo BASE_URL; ?>about">About</a>` |

### Page Template (Public)

```php
<?php
$pageTitle = 'Page Name';
$metaDescription = 'Brief description for search engines (150–160 chars recommended).';
$pageType = 'default';   // See docs/SEO.md for available types
include 'partials/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1 class="reveal">Page Name</h1>
    <div class="breadcrumb">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Page Name</span>
    </div>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <!-- Content here -->
  </div>
</section>

<?php include 'partials/footer.php'; ?>
```

### CSS

| Rule | Details |
|------|---------|
| **Variables** | Use CSS custom properties from `:root` — never hardcode colors/fonts |
| **Classes** | Use descriptive, lowercase-hyphenated class names (e.g., `.admin-stat-card`) |
| **Layout** | Use CSS Grid and Flexbox. Avoid floats. |
| **Responsive** | Use `@media (max-width: 768px)` breakpoints. Test on mobile. |
| **Animations** | Use CSS transitions and `@keyframes`. Keep under 300ms. |

### JavaScript

| Rule | Details |
|------|---------|
| **No jQuery** | Vanilla JS only. Use `querySelector`, `addEventListener`, `fetch`. |
| **DOM Ready** | Wrap code in `document.addEventListener('DOMContentLoaded', function() { ... })` |
| **Cart** | Uses `localStorage` via `cart.js` — loaded globally on all pages |
| **Payments** | Razorpay checkout modal handled in `donate.js` |
| **Forms** | Validate required fields client-side, always validate server-side too |

### Security Golden Rules

1. **Never trust user input** — sanitize, validate, escape
2. **Prepared statements for all SQL** — no exceptions
3. **CSRF tokens on all forms** — validate with `hash_equals()`
4. **Permission-check every admin page** — use `requirePermission()` or `hasPermission()`
5. **Verify amounts server-side** — never accept client-sent amounts at face value
6. **Rate-limited login** — 5 attempts per 15-minute window (stored in `login_attempts` table)
7. **Session regeneration on login** — `session_regenerate_id(true)` prevents fixation
8. **Error messages** — generic to users, detailed in error logs

---

## 13. Common Tasks for Freshers

### Task 1: Fix a Bug on a Public Page

```bash
# 1. Find the file — look in modules/<Module>/content/
#    For example, services/ files are in modules/Content/content/services/

# 2. Edit the PHP file
#    Add your fix following existing patterns

# 3. Test
#    Open http://isjm.test:8080/the-page in your browser

# 4. Check PHP syntax
php -l modules/Content/content/services/your-file.php
```

### Task 2: Add a New Page to the Website

```php
<?php
// 1. Create the file directly at e.g. services/new-page.php
//    This automatically becomes a "wrapper" that IS the actual file
//    (no need for module structure if it's a simple page)

// OR, better: create in modules/Content/content/services/new-service.php
//     and create a wrapper at services/new-service.php

$pageTitle = 'My New Page';
$metaDescription = 'Brief description for search results.';
$pageType = 'default';   // Choose from docs/SEO.md: 'home', 'festival', 'blog', 'donate', 'gallery', 'contact', 'about', 'service', 'course', 'booking', 'yatra'
include 'partials/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1 class="reveal">My New Page</h1>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <p>Welcome to my page!</p>
  </div>
</section>

<?php include 'partials/footer.php'; ?>
```

Now visit `http://isjm.test:8080/services/new-page` — the generic `.htaccess` rewrite handles it!

### Task 3: Add a New Admin Page

See [Section 8 — Adding a New Admin Page](#adding-a-new-admin-page).

### Task 4: Create a Database Migration

```bash
# 1. Create file: database/migrations/my_change.php
# 2. Follow the migration pattern (see Section 7)
# 3. Run it: php database/migrations/my_change.php
# 4. Verify: check in phpMyAdmin
```

### Task 5: Add a Donation Cause

Insert into `donation_causes` table via migration or phpMyAdmin:

```sql
INSERT INTO `donation_causes` 
  (`slug`, `title`, `category`, `description`, `sort_order`, `is_active`)
VALUES
  ('my-new-festival', 'My New Festival', 'festival', 
   'Description here...', 55, 1);
```

Then add associated sevas in `donation_cause_master_sevas`.

### Task 6: Run All Tests Before Deploying

```bash
# 1. PHPUnit (no server needed)
vendor/bin/phpunit

# 2. E2E (Laragon must be running)
npx playwright test

# 3. If admin tests need auth:
ADMIN_USER=admin ADMIN_PASS="isjm@admin" npx playwright test tests/e2e-admin.spec.js
```

---

## 14. Troubleshooting

### "403 Forbidden" on admin page

**Possible causes:**
1. User doesn't have the required role/permission — check RBAC assignments
2. Empty directory with same name as the PHP file — run `find admin -type d -empty` (Git Bash) or `for /d %i in (admin\*) do dir /b "%i" 2>nul | findstr /v . >nul && echo %i is empty` (CMD)
3. Apache directory listing disabled — check no empty `admin/roles/` folder exists alongside `admin/roles.php`

### "500 Internal Server Error"

**Check in order:**
```bash
# 1. PHP syntax
php -l the-file.php

# 2. PHP error log
# In Laragon: Menu → PHP → PHP error log

# 3. File permissions
ls -la the-file.php
```

### "Too many login attempts"

The login is rate-limited: 5 failed attempts per 15-minute window per IP.

```bash
# Clear from database
php -r "require 'config.php'; getDB()->prepare('DELETE FROM login_attempts WHERE ip_address = ?')->execute(['127.0.0.1']);"
```

### "Class not found" (autoloading issue)

```bash
composer dump-autoload
```

### Pages not loading (404 or wrong content)

Check `.htaccess` — if you add a new rewrite rule, make sure it's above the generic `^(.+?)/?$` rule.

### E2E tests fail with "socket hang up"

The server might be down. Start Laragon and try again. The `/services` page has a known intermittent timeout issue — re-run the test to confirm.

### CSS/JS changes not showing

```bash
# Rebuild assets
npm run build

# Hard refresh browser (Ctrl+Shift+R / Cmd+Shift+R)
```

---

## Quick Reference Card

```bash
# === Local Development ===
http://isjm.test:8080                    # Website
http://isjm.test:8080/admin/login        # Admin login (admin / isjm@admin)

# === Commands ===
composer install                         # Install PHP dependencies
npm install                              # Install Node.js dependencies
npm run build                            # Build CSS + JS assets
vendor/bin/phpunit                       # Run PHPUnit tests (74 tests)
npx playwright test                      # Run E2E tests (172 tests)
php -l file.php                          # Check PHP syntax
composer dump-autoload                   # Regenerate autoloader

# === Migrations ===
php database/migrations/<name>.php       # Run regular migration
php modules/RBAC/database/migrations/001_create_rbac_tables.php  # RBAC step 1
php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php  # step 2
php modules/RBAC/database/migrations/003_migrate_existing_admins.php  # step 3

# === Key Files ===
modules/Kernel/config.php                # Site config, env vars, constants
modules/Kernel/includes/db.php           # Database connection (getDB())
modules/Kernel/Admin/auth-check.php      # Auth gate (hasPermission, requirePermission)
modules/Kernel/Admin/partials/header.php # Admin sidebar navigation
modules/Kernel/partials/header.php       # Public page header + nav
modules/Kernel/partials/footer.php       # Public page footer + scripts
.htaccess                                # URL rewriting + security headers
composer.json                            # PHP dependencies + autoloading
package.json                             # Node.js scripts + dev dependencies
```

---

*Happy coding! Hare Krishna! 🦚*
