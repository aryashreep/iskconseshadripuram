# Modular Monolith — Specification Document

> **📜 HISTORICAL DOCUMENT** — This specification guided the architecture migration (Phases 1-7) which has been completed.
> **Current state:** All 8 phases are complete (~201 files migrated across 9 modules).
> **Canonical sources:** [`ARCHITECTURE.md`](ARCHITECTURE.md) (current architecture), [`MODULE_INDEX.md`](MODULE_INDEX.md) (module structure), [`CODING_STANDARDS.md`](CODING_STANDARDS.md) (conventions)
> **Parts still authoritative:** Section 2 (Module Conventions), Section 5 (Code Patterns & Conventions)
> **Parts superseded:** Section 3 (Migration Plan) — all phases complete; Section 1.2 (Target Directory Structure) — actual structure differs

---

> **Date**: July 5, 2026
> **Architecture**: Modular Monolith
> **Hosting**: cPanel shared hosting (Apache + PHP 8.x + MySQL)
> **Goal**: Developer navigation — organize code so each feature has its own folder

---

## 1. Architecture Overview

### 1.1 What is a Modular Monolith?

A **Modular Monolith** lives between a classic monolith and microservices:

| Aspect | Classic Monolith | Modular Monolith | Microservices |
|--------|-----------------|-------------------|---------------|
| **Code organization** | Flat/folder-by-type | Feature/directory | Separate repos |
| **Deployment** | Single unit | Single unit | Multiple units |
| **Module boundaries** | None or loose | Clear interfaces | Network APIs |
| **Infrastructure** | Simple (cPanel) | Simple (cPanel) | Complex (Docker/K8s) |
| **Dev navigation** | Hard to find code | Easy per feature | Easy per service |

The Modular Monolith gives you microservices-style developer experience (clear ownership, focused folders) without the ops complexity (no Docker, no queues, no network calls).

### 1.2 Target Directory Structure

```
project-root/
├── config/                     # Configuration files (currently config.php)
│   ├── app.php                 # Site settings, temple info, bank details
│   ├── razorpay.php            # Razorpay credentials
│   └── database.php            # DB connection config
│
├── database/                   # Central schema & migrations (cPanel-friendly)
│   ├── schema.sql              # Full DB schema (for phpMyAdmin)
│   ├── seed.sql                # Seed data
│   └── migrations/             # Migration PHP scripts
│
├── includes/                   # Shared bootstrap & infrastructure
│   ├── bootstrap.php           # App bootstrap (config, session, CSRF)
│   ├── db.php                  # PDO singleton (getDB())
│   └── asset-helper.php        # Cache-busted asset URLs
│
├── modules/                    # ← NEW: All feature modules
│   ├── Donation/               # Donation system (first to migrate)
│   │   ├── php/                # PSR-4: Isjm\Modules\Donation\
│   │   │   ├── DonationRepository.php
│   │   │   ├── DonationService.php
│   │   │   └── DonationRenderer.php
│   │   ├── Admin/              # Admin pages for donations
│   │   │   ├── Dashboard.php
│   │   │   ├── Reports/
│   │   │   └── TransactionLogs.php
│   │   ├── api/                # API endpoints (create-order, verify-payment)
│   │   ├── assets/             # Module-owned JS, CSS
│   │   │   ├── js/donate.js
│   │   │   └── css/donate.css
│   │   ├── tests/              # Module-level tests
│   │   ├── README.md           # REQUIRED: Module overview
│   │   ├── DECISIONS.md        # REQUIRED: Architecture decisions
│   │   ├── DATABASE.md         # REQUIRED: Owned tables & schema
│   │   ├── API.md              # IF HAS API: Endpoint contracts
│   │   ├── TASKS.md            # IF HAS PENDING: Module backlog
│   │   └── routes.php          # Route definitions
│   │
│   ├── Booking/                # Puja & Yagya booking
│   │   ├── php/                # PSR-4: Isjm\Modules\Booking\
│   │   ├── Admin/              # Admin booking management
│   │   ├── api/                # Booking API endpoints
│   │   ├── assets/
│   │   ├── templates/          # Booking page templates
│   │   ├── tests/
│   │   ├── README.md           # REQUIRED
│   │   ├── DECISIONS.md        # REQUIRED
│   │   ├── DATABASE.md         # REQUIRED
│   │   ├── API.md              # IF HAS API
│   │   └── TASKS.md            # IF HAS PENDING
│   │
│   ├── Festival/               # Festival pages (consolidate 50+ files)
│   │   ├── php/                # PSR-4: Isjm\Modules\Festival\
│   │   ├── Admin/              # Festival content management
│   │   ├── assets/
│   │   ├── templates/          # Festival page templates
│   │   ├── tests/
│   │   ├── README.md           # REQUIRED
│   │   ├── DECISIONS.md        # REQUIRED
│   │   ├── DATABASE.md         # REQUIRED
│   │   └── TASKS.md            # IF HAS PENDING
│   │
│   ├── Blog/                   # Blog system
│   │   ├── php/
│   │   ├── Admin/
│   │   ├── templates/
│   │   ├── tests/
│   │   ├── README.md           # REQUIRED
│   │   ├── DECISIONS.md        # REQUIRED
│   │   ├── DATABASE.md         # REQUIRED
│   │   └── TASKS.md            # IF HAS PENDING
│   │
│   ├── Panihati/               # Panihati Yatra
│   │   ├── php/                # (currently includes/panihati-helpers.php)
│   │   ├── Admin/              # 8+ admin pages for Panihati
│   │   ├── api/
│   │   ├── assets/
│   │   ├── templates/
│   │   ├── tests/
│   │   ├── README.md           # REQUIRED
│   │   ├── DECISIONS.md        # REQUIRED
│   │   ├── DATABASE.md         # REQUIRED
│   │   ├── API.md              # IF HAS API
│   │   └── TASKS.md            # IF HAS PENDING
│   │
│   ├── Pages/                  # Static content pages (about, services, courses)
│   │   ├── php/                # Template rendering logic
│   │   ├── content/            # Page content definitions
│   │   ├── assets/
│   │   ├── README.md           # REQUIRED
│   │   └── DECISIONS.md        # REQUIRED
│   │
│   ├── Kernel/                 # Cross-cutting infrastructure
│   │   ├── php/                # PSR-4: Isjm\Modules\Kernel\
│   │   │   ├── SessionGuard.php
│   │   │   ├── Router.php
│   │   │   ├── CsrfService.php
│   │   │   └── LayoutRenderer.php
│   │   ├── templates/          # Shared layout partials
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── README.md           # REQUIRED
│   │   └── DECISIONS.md        # REQUIRED
│   │
│   └── [Future] DevoteeCare/   # Future module
│   └── [Future] BBT/           # Future module (Bhaktivedanta Book Trust)
│
├── public/                     # Publicly accessible entry points (OPTIONAL)
│   ├── index.php               # Front controller
│   ├── .htaccess               # URL rewriting
│   └── assets/                 # Built assets (compiled from modules)
│       └── dist/
│           ├── manifest.json
│           ├── style.*.css
│           └── main.*.js
│
├── src/                        # Existing PSR-4 classes (migrate gradually)
├── assets/                     # Existing assets (migrate gradually)
├── admin/                      # Existing admin pages (migrate gradually)
├── api/                        # Existing API endpoints (migrate gradually)
├── (root .php files)           # Migrate to modules incrementally
│
├── scripts/                    # Build scripts
│   ├── build.js                # Asset aggregation/compilation
│   └── generate_prod_migration.php
│
├── docs/                       # Documentation
├── tests/                      # Top-level E2E tests (Playwright)
│   └── acceptance/
│       ├── donation-flow.spec.js
│       ├── booking-flow.spec.js
│       └── panihati-flow.spec.js
│
├── composer.json               # PSR-4 autoloading for modules/
├── package.json                # Build & test scripts
└── modularization-spec.md      # This file
```

---

## 2. Module Conventions

### 2.1 Module Documentation Files

Every module must include a set of documentation files. These are organized by **priority** — REQUIRED files are essential for AI-assisted development; IF HAS files are added only when applicable.

| File | Priority | Purpose | AI Value |
|------|----------|---------|----------|
| `README.md` | **REQUIRED** | Module overview, purpose, owned tables, public interface | AI learns what the module *is* and what it owns |
| `DECISIONS.md` | **REQUIRED** | Architecture decisions, trade-offs, rationale | AI learns *why* code is written a certain way — prevents accidental "fixes" of intentional choices |
| `DATABASE.md` | **REQUIRED** | Owned tables, columns, relationships, indexes | AI writes correct SQL without parsing `schema.sql` |
| `API.md` | IF HAS API | Endpoint contracts, request/response shapes | AI doesn't break API contracts |
| `TASKS.md` | IF HAS PENDING | Module backlog, known issues, planned features | AI prioritizes work correctly |
| `tests/` | **RECOMMENDED** | Unit tests, E2E tests for module | AI validates changes don't break existing behavior |
| `routes.php` | **REQUIRED** | Route registrations (see §4.2 for format) | AI knows which URLs map to which handlers |

---

#### 2.1.1 README.md (REQUIRED)

The module's identity card — what it does, what it owns, what it needs.

```markdown
# Module: Donation

## Purpose
Handles all donation-related functionality: donation causes, seva offerings, Razorpay payment processing, transaction tracking, and donation reporting.

## Owned Database Tables
- `donation_causes` — Activities/festivals (74 rows, category field)
- `donation_transactions` — Payment records
- `master_seva_categories` — Top-level seva groupings (10)
- `master_sevas` — Deduplicated seva catalog (363+)
- `donation_cause_master_sevas` — Pivot: cause ↔ seva with override support
- `donation_cause_sevas` — Legacy per-cause seva (backward compat)
- `donation_seva_categories` — Legacy seva categories (backward compat)

## Dependencies
- **Kernel** — Auth, CSRF, Layout rendering
- **Booking** — Puja bookings reference donation_transactions

## Public Interface
```php
DonationRepository    // DB queries for causes, sevas, transactions
DonationService       // Business logic: formatting, grouping, seasonal
DonationRenderer      // HTML rendering: CTA buttons, seva forms
```

## Entry Points
| Type | URL | Handler |
|------|-----|--------|
| Public | `/donate/{slug}` | DonationController::showCause |
| Public | `/checkout/` | CheckoutController::showCheckout |
| API | POST `/api/create-order.php` | PaymentController::createOrder |
| API | POST `/api/verify-payment.php` | PaymentController::verifyPayment |
| API | POST `/api/webhook.php` | PaymentController::handleWebhook |
| Admin | `/admin/donations` | Admin\TransactionLogs::index |
| Admin | `/admin/report-dashboard` | Admin\Reports\Dashboard::index |
```

## Directory Structure
```
modules/Donation/
├── php/                     # PSR-4 classes (Isjm\Modules\Donation\)
├── Admin/                   # Admin panel pages
├── api/                     # API endpoints
├── assets/                  # JS, CSS
├── templates/               # Page templates
├── tests/                   # Module tests
├── README.md
├── DECISIONS.md
├── DATABASE.md
├── API.md
├── TASKS.md
└── routes.php
```
```

---

#### 2.1.2 DECISIONS.md (REQUIRED)

This is the **most important file for AI**. It records why things are the way they are — the non-obvious trade-offs, the deliberate compromises, the constraints that shaped the code. AI tools read this file first before making changes, preventing them from "fixing" intentional decisions.

Full template with examples from this codebase:

```markdown
# Donation Module — Architecture Decisions

All significant architecture and design decisions for this module.
Use this file to prevent future contributors (including AI) from accidentally
reversing deliberate trade-offs.

---

## [YYYY-MM-DD] Decision Title

### Decision
One sentence stating the decision made.

### Context
What problem or constraint led to this decision?

### Options Considered
- **Option A**: What it was
- **Option B**: What it was

### Rationale
Why the chosen option won over alternatives.

### Trade-offs
- **Downside**: What was sacrificed
- **Risk**: What could go wrong

### Related Files
- `path/to/file.php`

---

## [2026-07-03] Dual-Read Strategy for Seva Catalog

### Decision
New code reads from `master_seva_catalog` tables first. Falls back to legacy
`donation_cause_sevas` for causes not yet migrated. `getCauseSevas()` handles
this transparently.

### Context
The donation system has 74+ causes, each historically having its own set of sevas
in `donation_cause_sevas`. We built a master catalog (`master_sevas`) to
deduplicate and standardize seva offerings, but migrating all 74 causes at once
was too risky. The dual-read allows gradual, cause-by-cause migration.

### Options Considered
- **Big-bang migration**: Move all causes at once — high risk, potential downtime
- **Dual-read with fallback**: Read from new catalog first, fall back to old table
- **Copy-on-write**: Only use new catalog, migrate data in the background

### Rationale
Dual-read minimizes risk. Each cause can be migrated independently by adding
entries to `donation_cause_master_sevas`. Old causes continue to work with zero
downtime. The fallback is transparent to callers.

### Trade-offs
- **Performance**: Slightly slower queries (two reads for unmigrated causes)
- **Complexity**: Two code paths to maintain during migration period
- **Maintenance burden**: Legacy tables can't be dropped until all causes migrate

### Related Files
- `includes/donation-helpers.php` — Facade that uses dual-read internally
- `src/Donations/DonationRepository.php` — `getCauseSevas()` implementation

---

## [2026-06-15] No Framework Decision

### Decision
Continue using vanilla PHP with PDO, no framework (Laravel/Symfony/etc.).

### Context
Application runs on shared cPanel hosting with limited resources. No Composer
runtime, no queues, no caching layers. Adding a framework would require
infrastructure upgrades and introduce significant overhead.

### Options Considered
- **Laravel**: Too heavy for cPanel, requires artisan commands, queue workers
- **Slim Framework**: Lightweight but adds learning curve for future maintainers
- **Vanilla PHP**: Zero dependencies, full control, easy deployment

### Rationale
Vanilla PHP with PDO gives full SQL control with zero abstraction overhead.
The codebase is small enough that a framework's benefits (routing, ORM, auth)
don't justify the performance and complexity cost. Shared hosting can't run
artisan or queue workers anyway.

### Trade-offs
- **Dev speed**: More boilerplate for routing, auth, validation
- **Consistency risk**: Without framework conventions, team discipline is required
- **Migration path**: Framework adoption later would be a rewrite

### Related Files
- `config.php` — Application configuration
- `includes/db.php` — PDO singleton
```

---

#### 2.1.3 DATABASE.md (REQUIRED)

Describes the tables this module owns. AI reads this to write correct SQL without
parsing the full `schema.sql`.

```markdown
# Donation Module — Database Schema

## Owned Tables

### donation_causes
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| slug | VARCHAR(100) | URL-friendly unique identifier |
| title | VARCHAR(255) | Display name |
| category | VARCHAR(50) | One of: festival, ekadashi, appearance, disappearance, event, service, construction, general |
| is_active | TINYINT(1) | Soft delete flag |
| min_amount | DECIMAL(12,2) | Minimum donation amount |
| sort_order | INT | Display ordering |
| is_time_bound | TINYINT(1) | Has start/end date |
| start_date | DATE | NULL if not time-bound |
| end_date | DATE | NULL if not time-bound |
| image_url | VARCHAR(255) | Banner/icon image |
| form_type | VARCHAR(50) | tiers, quantity, multi_item, cart, cart_qty |

### donation_transactions
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| cause_id | INT (FK) | → donation_causes.id |
| master_seva_id | INT (FK, nullable) | → master_sevas.id |
| seva_id | INT (FK, nullable) | → donation_cause_sevas.id (legacy) |
| amount | DECIMAL(12,2) | Donation amount in INR |
| payment_status | VARCHAR(20) | created, attempted, paid, failed, refunded |
| donor_name, donor_email, donor_phone | VARCHAR | Donor contact info |
| razorpay_order_id | VARCHAR(100) | Razorpay order reference |
| razorpay_payment_id | VARCHAR(100) | Razorpay payment reference |
| created_at | DATETIME | Auto-set on insert |

## Key Relationships
```
donation_causes.id ← donation_transactions.cause_id
donation_causes.id ← donation_cause_master_sevas.cause_id
master_sevas.id ← donation_cause_master_sevas.master_seva_id
master_sevas.category_id → master_seva_categories.id
master_sevas.id ← donation_transactions.master_seva_id (nullable)
```

## Reporting Hierarchy
```
Category (donation_causes.category)
    → Activity (donation_causes.title)
        → Seva (master_sevas.name)
```
```

---

#### 2.1.4 API.md (IF HAS API)

Documents API endpoints owned by this module.

```markdown
# Donation Module — API

## POST /api/create-order.php
Creates a Razorpay order for a donation.

**Request** (JSON):
```json
{
  "amount": 100100,
  "cause_id": 5,
  "seva_id": 12,
  "donor_name": "Radha Krishna Das",
  "donor_email": "rk.das@gmail.com"
}
```

**Response** (JSON):
```json
{
  "order_id": "order_xxxxx",
  "amount": 100100,
  "currency": "INR"
}
```

**Security**: Amount verified server-side against catalog.
**HTTP Codes**: 200 (success), 400 (invalid), 500 (server error)

## POST /api/verify-payment.php
Verifies Razorpay payment signature.

**Security**: HMAC-SHA256 signature verification.

## POST /api/webhook.php
Server-to-server payment notification (authoritative).

**Events**: payment.captured, payment.failed
**Security**: X-Razorpay-Signature header validation.
```

---

#### 2.1.5 TASKS.md (IF HAS PENDING WORK)

Tracks module-specific backlog items. Keeps AI focused on what actually needs doing.

```markdown
# Donation Module — Tasks

## In Progress
- [ ] Donor email notification after successful payment
- [ ] 80G tax receipt PDF generation

## Upcoming
- [ ] Recurring donation management dashboard
- [ ] Donation receipt PDF for donors

## Bugs
- [ ] Some legacy causes still use old `donation_cause_sevas` table (see DECISIONS.md)

## Technical Debt
- [ ] Remove legacy `donation_cause_sevas` fallback once all causes migrated
```

---

### 2.2 Module Communication Rules

1. **No direct cross-module includes** — Modules communicate only through:
   - Namespaced class calls (e.g., `new \Isjm\Modules\Donation\DonationService()`)
   - Interface contracts defined in the Kernel module
   - Shared infrastructure (getDB(), SessionGuard, etc.)

2. **Kernel module** contains shared infrastructure:
   - `SessionGuard` — auth & RBAC
   - `Router` — URL routing & dispatching
   - `CsrfService` — CSRF protection
   - `LayoutRenderer` — header/footer rendering

3. **Allowed cross-module access**:
   - Repository classes can be instantiated by other modules
   - Service classes expose public methods
   - Renderer classes are UI-only, called by templates

4. **Forbidden cross-module access**:
   - Direct `include` of another module's PHP files
   - Direct database queries on another module's tables
   - Global function calls across module boundaries (except in shared kernel)

### 2.3 PSR-4 Autoloading

```json
{
  "autoload": {
    "psr-4": {
      "Isjm\\Modules\\": "modules/"
    },
    "files": [
      "includes/db.php",
      "includes/asset-helper.php"
    ]
  }
}
```

Class naming: `Isjm\Modules\Donation\DonationController`
File path: `modules/Donation/php/DonationController.php`

### 2.4 Module Assets & Build Pipeline

Each module owns its assets in `modules/[Module]/assets/`:

```
modules/Donation/assets/
├── js/
│   ├── donate.js
│   └── checkout.js
└── css/
    └── donate.css
```

The build script (`scripts/build.js`) aggregates all module assets:

```
scripts/build.js
├── Scans modules/*/assets/ recursively
├── Compiles/minifies with Terser (JS) + LightningCSS (CSS)
├── Generates content-hashed filenames
└── Outputs manifest.json → assets/dist/
```

Module pages reference assets via the existing `asset()` helper:

```php
<script src="<?= asset('modules/Donation/assets/js/donate.js') ?>"></script>
```

### 2.5 Admin Pages

Each module that has admin functionality contains an `Admin/` subfolder:

```
modules/Donation/Admin/
├── Dashboard.php
├── TransactionLogs.php
├── Reports/
│   ├── CategoryReport.php
│   ├── ActivityReport.php
│   └── SevaReport.php
└── Exports/
    ├── DonationsExport.php
    └── ReportExport.php
```

The admin sidebar navigation is defined centrally in `Kernel` but references module routes:
```php
// Kernel defines menu structure, modules register their items
$menu->addItem('Donations', 'admin/donations', ['super_admin', 'treasurer']);
```

### 2.6 API Endpoints

Each module contains its API endpoints in an `api/` subfolder:

```
modules/Donation/api/
├── CreateOrder.php
├── VerifyPayment.php
└── Webhook.php
```

A routing layer in Kernel dispatches requests:
```php
// public/index.php or .htaccess → Kernel\Router
Router::post('/api/create-order', 'Donation', 'CreateOrder');
Router::post('/api/verify-payment', 'Donation', 'VerifyPayment');
Router::post('/api/webhook', 'Donation', 'Webhook');
```

### 2.7 Database Table Ownership

| Module | Owned Tables |
|--------|-------------|
| **Kernel** | admins, login_attempts |
| **Donation** | donation_causes, donation_cause_sevas, donation_cause_master_sevas, donation_transactions, donation_subscriptions, master_seva_categories, master_sevas, donation_seva_categories |
| **Booking** | booking_pujas, booking_schema (guest_house) |
| **Festival** | festival_content (shared data in donation_causes table) |
| **Blog** | blogs |
| **Panihati** | panihati_yatra_registrations, panihati_pricing, panihati_sadans, panihati_offline_aggregates, panihati_dynamic_* |
| **Pages** | page_views (if tracking) |

Central schema file `database/schema.sql` continues to hold all table definitions.

---

## 3. Migration Plan

### 3.1 Principles

- **Incremental**: One module at a time
- **Backward compatible**: Existing files continue to work while being migrated
- **No downtime**: Each migration step maintains all functionality
- **No data changes**: Only code reorganization

### 3.2 Phase 1: Foundation (Donation Module)

**Rationale**: Donation already has PSR-4 classes in `src/Donations/` — easiest migration.

1. Create `modules/` directory structure
2. Move `src/Donations/` → `modules/Donation/php/`
3. Create `modules/Donation/MANIFEST.md`
4. Move admin donation pages → `modules/Donation/Admin/`
5. Move donation API endpoints → `modules/Donation/api/`
6. Move donation JS/CSS → `modules/Donation/assets/`
7. Update `composer.json` PSR-4 mapping
8. Update file references throughout codebase
9. Create backward-compatible aliases in `includes/donation-helpers.php`
10. Run `composer dump-autoload`
11. Test all donation flows (create order, verify, webhook, admin reports)

**Backward compatibility during Phase 1**:
- Old files at `admin/donations.php`, `api/create-order.php` etc. become thin wrappers:
  ```php
  // admin/donations.php → thin wrapper
  require_once __DIR__ . '/../includes/bootstrap.php';
  (new \Isjm\Modules\Donation\Admin\TransactionLogs())->render();
  ```
- This allows URLs to stay the same while code moves to its module.

### 3.3 Phase 2: Panihati Module

**Rationale**: Self-contained feature with its own helpers, admin pages, and API endpoints.

1. Create `modules/Panihati/` structure
2. Move `includes/panihati-helpers.php` → `modules/Panihati/php/`
3. Move admin Panihati pages → `modules/Panihati/Admin/`
4. Move Panihati API → `modules/Panihati/api/`
5. Update references, test all flows

### 3.4 Phase 3: Booking Module

1. Create `modules/Booking/` structure
2. Move `booking/puja/`, `booking/yagya/`, `booking/guest-house/` → templates
3. Move booking API → `modules/Booking/api/`
4. Move admin booking pages → `modules/Booking/Admin/`
5. Create backward-compatible includes

### 3.5 Phase 4: Festival Consolidation

**Current state**: 50+ individual PHP files in `festivals/` — many duplicating the same pattern.

1. Create `modules/Festival/php/` with a dynamic festival renderer
2. Create `modules/Festival/templates/` for festival page layouts
3. Load festival content from database (donation_causes table already has content fields)
4. Convert static festival files to database records where possible
5. Keep unique editorial pages (ones with custom content) as `modules/Festival/templates/special/`

### 3.6 Phase 5: Blog Module

1. Create `modules/Blog/` structure
2. Move blog pages, admin, and helpers

### 3.7 Phase 6: Pages Module (static content)

1. Create `modules/Pages/` structure
2. Move about/, services/, courses/, contact.php, darshan.php, forums.php, resources.php, seva.php
3. Create a page content registry or keep as content file templates

### 3.8 Phase 7: Admin Navigation & Kernel

1. Refactor admin sidebar to be module-registered
2. Add Router class for clean request dispatch
3. Clean up backward-compatible wrappers

---

## 4. Module Interfaces & Contracts

### 4.1 Kernel Interfaces

```php
namespace Isjm\Modules\Kernel;

interface AuthInterface {
    public function requireLogin(): void;
    public function requireRole(array $roles): void;
    public function hasRole(array $roles): bool;
    public function getAdminId(): ?int;
}

interface RouterInterface {
    public function get(string $path, string $moduleName, string $handler): void;
    public function post(string $path, string $moduleName, string $handler): void;
    public function dispatch(): void;
}

interface LayoutInterface {
    public function renderHeader(string $title, array $meta = []): void;
    public function renderFooter(): void;
    public function renderAdminHeader(string $title, string $activePage): void;
    public function renderAdminFooter(): void;
}
```

### 4.2 Module Registration

Each module defines its routes and permissions in `routes.php`:

```php
// modules/Donation/routes.php
return [
    'routes' => [
        'public' => [
            'donate/{slug}' => ['GET', 'DonationController', 'showCause'],
            'donate/{slug}/checkout' => ['GET', 'DonationController', 'showCheckout'],
        ],
        'api' => [
            'api/create-order' => ['POST', 'PaymentController', 'createOrder'],
            'api/verify-payment' => ['POST', 'PaymentController', 'verifyPayment'],
        ],
        'admin' => [
            'admin/donations' => ['GET', 'Admin\TransactionLogs', 'index', ['super_admin', 'treasurer']],
        ],
    ],
    'menu' => [
        'label' => 'Donations',
        'icon' => 'fa-hand-holding-heart',
        'roles' => ['super_admin', 'treasurer'],
        'children' => [
            ['label' => 'Dashboard', 'route' => 'admin/report-dashboard'],
            ['label' => 'Transaction Logs', 'route' => 'admin/donations'],
            ['label' => 'Category Report', 'route' => 'admin/report-category'],
        ],
    ],
];
```

---

## 5. Code Patterns & Conventions

### 5.1 File Naming

- Classes: PascalCase, matching class name (e.g., `DonationRepository.php`)
- Templates: kebab-case (e.g., `cause-detail.php`, `donation-form.php`)
- Assets: kebab-case (e.g., `donate.js`, `donation-form.css`)
- Tests: PascalCase with `Test` suffix (e.g., `DonationServiceTest.php`)

### 5.2 Routing Pattern

```php
// Instead of direct PHP file access:
// donate/donate-seva.php?cause=janmashtami

// New pattern — routed through Front Controller:
// Router::dispatch('/donate/janmashtami')
//   → Modules\Donation\DonationController::showCause('janmashtami')

// But for backward compatibility, thin wrapper files remain:
// donate/donate-seva.php → include bootstrap → call module class
```

### 5.3 Template Pattern

Each page template follows a consistent pattern:

```php
<?php
// PAGE TEMPLATE
$layout = new \Isjm\Modules\Kernel\LayoutRenderer();
$layout->renderHeader('Donate - Janmashtami', ['canonical' => 'donate/janmashtami']);
?>

<section class="donation-page">
  <!-- Page content -->
</section>

<?php $layout->renderFooter(); ?>
```

### 5.4 Controller Pattern

```php
namespace Isjm\Modules\Donation;

class DonationController
{
    private DonationService $service;
    private LayoutRenderer $layout;

    public function __construct()
    {
        $this->service = new DonationService();
        $this->layout = new LayoutRenderer();
    }

    public function showCause(string $slug): void
    {
        $cause = $this->service->getCauseBySlug($slug);
        if (!$cause) {
            // 404 handling
            $this->layout->renderHeader('Not Found');
            echo '<p>Cause not found.</p>';
            $this->layout->renderFooter();
            return;
        }

        $this->layout->renderHeader($cause['title']);
        include __DIR__ . '/../templates/cause-detail.php';
        $this->layout->renderFooter();
    }
}
```

---

## 6. Testing Strategy

### 6.1 Module-Level Tests (PHP)

Each module can have `tests/` with PHP unit tests:

```
modules/Donation/tests/
├── DonationRepositoryTest.php
├── DonationServiceTest.php
├── DonationRendererTest.php
└── PaymentFlowTest.php
```

### 6.2 E2E Tests (Playwright)

Top-level `tests/` folder holds Playwright acceptance tests:

```
tests/
├── donation-flow.spec.js        # Donate → pay → success
├── booking-flow.spec.js          # Book puja → pay → confirmation
├── panihati-flow.spec.js         # Register → calculate → pay
├── admin-donations.spec.js       # Admin donation management flow
├── admin-bookings.spec.js        # Admin booking management flow
└── all-pages.spec.js             # Smoke test all public pages
```

Module-specific E2E tests can also live in the module:

```
modules/Donation/tests/e2e/
├── donate-flow.spec.js
└── admin-reports.spec.js
```

The Playwright config includes paths to both.

---

## 7. Future-Proofing

### 7.1 Adding New Modules

To add a new module (e.g., DevoteeCare, BBT):

1. Create `modules/DevoteeCare/` directory structure
2. Create required documentation:
   - `README.md` — Module overview, purpose, owned tables, public interface
   - `DECISIONS.md` — Architecture decisions (even if empty initially, add as you go)
   - `DATABASE.md` — Schema details if module owns tables
   - `API.md` — If module has endpoints
   - `TASKS.md` — Module backlog
3. Create `php/` directory with PSR-4 classes
4. Add PSR-4 namespace in composer.json:
   ```json
   "Isjm\\Modules\\DevoteeCare\\": "modules/DevoteeCare/php/"
   ```
5. Create `routes.php` for URL registration
6. Run `composer dump-autoload`
7. Module is live and AI-ready

### 7.2 Extracting to Microservices (Future)

If needed later, any module can be extracted into its own service because:
- Module owns its database tables
- Module has clear interfaces
- Module communicates through the Kernel (which can become an API gateway)
- Module assets are self-contained

---

## 8. Risks & Mitigations

| Risk | Mitigation |
|------|-----------|
| **Broken URLs**: .htaccess rewrites break after restructuring | Keep thin wrapper files at old URLs during migration |
| **Performance**: Autoloading overhead on shared hosting | Use `composer dump-autoload -o` for optimized classmap |
| **Team confusion**: Mixed old/new structure during migration | Clear migration phases, keep a migration tracker |
| **cPanel limitations**: Can't set document root to public/ | Keep public-facing PHP at root level, move only backend code to modules |
| **Lost git history**: Moving files loses blame history | Use `git mv` + `git log --follow` for tracking |

---

## 9. Immediate Next Steps

1. ✅ **This spec** — approved and finalized
2. **Create module directory structure** — `modules/Kernel/`, `modules/Donation/`
3. **Phase 1 migration** — Move `src/Donations/` to `modules/Donation/php/`
4. **Update composer.json** — Add PSR-4 mapping for `Isjm\Modules\`
5. **Create documentation files** for Donation module:
   - `modules/Donation/README.md` — Module overview
   - `modules/Donation/DECISIONS.md` — Record existing architecture decisions
   - `modules/Donation/DATABASE.md` — Owned tables & schema
   - `modules/Donation/API.md` — API endpoint contracts
   - `modules/Donation/TASKS.md` — Pending work & debt
   - `modules/Donation/routes.php` — Route registration
6. **Create documentation files** for Kernel module:
   - `modules/Kernel/README.md`
   - `modules/Kernel/DECISIONS.md`
7. **Move donation assets** to `modules/Donation/assets/`
8. **Move admin donation pages** to `modules/Donation/Admin/`
9. **Create thin wrappers** at old file paths for backward compatibility
10. **Test thoroughly** — all donation flows, admin reports, API endpoints
11. **Proceed to Phase 2**: Panihati module
