# ARCHITECTURE.md — System Design

## High-Level Architecture

```
┌─────────────────────────────────────────────────────┐
│                    Browser (Devotee)                 │
│  donate/ → payment-success.php → Razorpay Checkout  │
└──────────────────────┬──────────────────────────────┘
                       │ HTTPS
┌──────────────────────▼──────────────────────────────┐
│              Apache + PHP 8 (Shared Hosting)         │
│                                                      │
│  ┌──────────────────────────────────────────────┐   │
│  │              Module System                    │   │
│  │                                               │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────┐ │   │
│  │  │ Donation │ │ Panihati │ │  Booking     │ │   │
│  │  └──────────┘ └──────────┘ └──────────────┘ │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────┐ │   │
│  │  │ Festivals│ │  Blogs   │ │   Content    │ │   │
│  │  └──────────┘ └──────────┘ └──────────────┘ │   │
│  │  ┌─────────────────────────────────────────┐ │   │
│  │  │              Kernel                     │ │   │
│  │  │  (config, partials, includes, src)      │ │   │
│  │  └─────────────────────────────────────────┘ │   │
│  └──────────────────────────────────────────────┘   │
│                      │                               │
│              ┌───────▼───────┐                       │
│              │  getDB() PDO  │                       │
│              │  Singleton    │                       │
│              └───────┬───────┘                       │
└──────────────────────┼──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                    MySQL Database                    │
│  donation_causes → donation_transactions             │
│  master_sevas → donation_cause_master_sevas          │
│  booking_pujas, panihati_yatra_registrations         │
└─────────────────────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│              Razorpay (Payment Gateway)              │
│  create-order → checkout → verify → webhook          │
└─────────────────────────────────────────────────────┘
```

## Module Layout

```
modules/
├── Donation/     (Phase 1 — Payment system, reports, seva catalog)
├── Panihati/     (Phase 2 — Yatra registration & admin)
├── Booking/      (Phase 3 — Puja/yagya/guest house booking)
├── Festivals/    (Phase 4 — ~70 public festival detail pages)
├── Blogs/        (Phase 5 — Blog posts & admin)
├── Content/      (Phase 6 — Static content: about, services, courses, yatra)
├── RBAC/         (Phase 8 — Role-based access control: roles, permissions, service layer)
└── Kernel/       (Phase 7 — Shared infrastructure: config, partials, includes, src)
```

Each module follows the same pattern:
```
modules/<Module>/
├── Admin/           (if applicable — admin management pages)
├── api/             (if applicable — REST endpoints)
├── assets/          (if applicable — CSS, JS)
├── content/         (public-facing pages)
└── src/             (PHP classes, helpers)
```

### Wrapper Convention

All original file paths (root-level PHP files, `partials/`, `includes/`, `admin/`, `services/`, `about/`, `courses/`, `yatra/`, `blogs/`, `festivals/`, `booking/`, `contact.php`, `darshan.php`, etc.) are preserved as **backward-compatible wrappers** that delegate to the module files:

```php
<?php
/**
 * Backward-compatibility wrapper.
 * File has been moved to modules/Kernel/partials/header.php.
 */
require_once __DIR__ . '/../modules/Kernel/partials/' . basename(__FILE__);
```

Wrappers maintain the same CWD as the original file, so CWD-based includes (`include '../partials/header.php'`) continue to resolve correctly without modification.

---

## Data Model — Donation Hierarchy

```
master_seva_categories (10)
    └── master_sevas (363+)

donation_causes (74)  ←── category field (8 values)
    │
    ├── donation_cause_master_sevas (pivot)
    │       └── master_sevas
    │
    └── donation_transactions
            ├── cause_id → donation_causes
            ├── seva_id → donation_cause_sevas (legacy)
            └── master_seva_id → master_sevas
```

**Category values**: festival, ekadashi, appearance, disappearance, event, service, construction, general

---

## Key Patterns

### Dual-Read Strategy
New code reads from `master_seva_catalog` tables first. Falls back to legacy `donation_cause_sevas` for causes not yet migrated. `getCauseSevas()` handles this transparently.

### Payment Flow
```
1. Client → POST /api/create-order.php (amount verified server-side)
2. Razorpay checkout modal opens
3. Client → POST /api/verify-payment.php (HMAC signature check)
4. Server updates donation_transactions.payment_status = 'paid'
5. Webhook /api/webhook.php provides secondary verification
```

### Admin Auth & RBAC
- Session-based with `admin/auth-check.php` (`SessionGuard` class)
- Permission-based access control via `hasPermission()` / `requirePermission()` — replaces old `requireRole()`
- Super Admin has implicit bypass for all permissions
- Permissions loaded into `$_SESSION['admin_permissions']` on each page load (fast in-memory checks)
- CSRF tokens on state-changing operations
- Sidebar menu items shown/hidden via `hasPermission()` checks

### Reporting Architecture
PHP queries aggregate `donation_transactions` → `donation_causes` → `master_sevas`. Results passed to Chart.js via `json_encode()`. CSV exports use `php://output` stream.

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
- `Isjm\Donations\*` → `modules/Kernel/src/Donations/` (Repository, Service, Renderer)
- `Isjm\Helpers\SessionGuard` → `modules/Kernel/src/Helpers/`
- `Isjm\Modules\RBAC\RbacService` → `modules/RBAC/RbacService.php`
- `Isjm\Modules\RBAC\PermissionRegistry` → `modules/RBAC/PermissionRegistry.php`
- `Isjm\Modules\*\*` → `modules/<Module>/` (RBAC, etc.)
- `Isjm\Tests\*` → `tests/` (dev only — PHPUnit test classes)

---

## Technology Decisions

| Decision | Choice | Why |
|----------|--------|-----|
| Framework | None (vanilla PHP) | Shared hosting, simplicity, zero dependencies |
| ORM | PDO directly | Full SQL control, no abstraction overhead |
| Payments | Razorpay | India-focused, handles PCI compliance |
| Charts | Chart.js CDN | Free, no build step, works on shared hosting |
| CSS | Custom with variables | Design consistency, no Tailwind overhead |
| Testing | Playwright (E2E) + PHPUnit (unit) | E2E for critical flows + PHPUnit for RBAC service layer (74 tests, 505 assertions) |
| Build | terser + lightningcss | Asset minification only |
