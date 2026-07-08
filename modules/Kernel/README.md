# Module: Kernel

## Purpose
Cross-cutting infrastructure shared by all other modules. The Kernel provides the foundation that every feature module depends on — authentication, routing, layout rendering, database connection, CSRF protection, and asset management.

No feature logic lives in this module. Its classes are used by all other modules but never call into them.

## Owned Infrastructure

| Component | File | Purpose |
|-----------|------|---------|
| **Database** | `includes/db.php` | PDO singleton (`getDB()`) — all DB queries go through this |
| **Database** | `includes/db.php` | PDO singleton (`getDB()`) — all DB queries go through this |
| **Auth Guard** | `SessionGuard.php` *(TODO: move from src/Helpers/)* | Login check, role-based access control (RBAC), CSRF tokens |
| **Auth Gate** | `admin/auth-check.php` *(TODO: move to Kernel)* | Admin page authentication gate, provides `hasRole()` and `requireRole()` |
| **Bootstrap** | `includes/bootstrap.php` *(TODO: move to Kernel)* | Config loading, session start, CSRF token generation |
| **Assets** | `includes/asset-helper.php` *(TODO: move to Kernel)* | Cache-busted asset URLs via `asset()` and `assetPath()` helpers |
| **Layout** | `partials/header.php` etc. *(TODO: move to Kernel/templates/)* | Shared HTML partials: site header, footer, admin header, admin footer |
| **Routing** | `Router.php` (planned, not yet created) | URL matching and controller dispatch (future) |
| **CSRF** | `CsrfService.php` (planned, not yet created) | CSRF token generation and validation (handled in SessionGuard for now) |

## Dependencies
- **None** — Kernel is at the bottom of the dependency chain
- All other modules depend on Kernel

## Owned Database Tables
- `admins` — Admin users with roles (super_admin, editor, pujari, treasurer, travel_agent)
- `login_attempts` — Login attempt tracking for brute-force protection

## Public Interface
```php
// Database (primarily for module repositories)
// File: includes/db.php
function getDB(): PDO

// Auth (used by admin pages and auth-check.php)
// File: src/Helpers/SessionGuard.php (TODO: move to modules/Kernel/SessionGuard.php)
Isjm\Helpers\SessionGuard  →  TODO: move to Isjm\Modules\Kernel\
    ->init(): void
    ->requireLogin(): void
    ->requireRole(array $roles): void
    ->hasRole(array $roles): bool
    ->getRole(): string
    ->getAdminId(): ?int

// Asset loading (used by all pages)
// File: includes/asset-helper.php
function asset(string $path): string       // Cache-busted URL
function assetPath(string $path): string   // Cache-busted filesystem path

// Bootstrap (used by all entry points)
// File: includes/bootstrap.php
//   → session_start() + CSRF token generation
```

## Entry Points
The Kernel module has no public-facing pages. It is loaded by every other module.

| Type | File | Used By |
|------|------|---------|
| Bootstrap | `includes/bootstrap.php` | Every entry point |
| Database | `includes/db.php` | Every module |
| Auth Gate | `admin/auth-check.php` | Every admin page |
| Assets | `includes/asset-helper.php` | Every page |
| Layout | `partials/header.php` | Public pages |
| Layout | `partials/footer.php` | Public pages |
| Layout | `admin/partials/header.php` | Admin pages |
| Layout | `admin/partials/footer.php` | Admin pages |

## Target Directory Structure (after migration)
```
modules/Kernel/
├── SessionGuard.php           # Auth class — TODO: move from src/Helpers/
├── Router.php                 # Route dispatcher (planned, not yet created)
├── CsrfService.php            # CSRF protection (planned, not yet created)
├── LayoutRenderer.php         # Layout rendering (planned, not yet created)
├── templates/                 # Shared layout partials — TODO: move from partials/
│   ├── header.php             # Site header (nav, preloader, meta tags)
│   ├── footer.php             # Site footer (scripts, close tags)
│   ├── admin-header.php       # Admin sidebar + header
│   └── admin-footer.php       # Admin footer + closing tags
├── README.md                  # This file
└── DECISIONS.md               # Architecture decisions
```

## Current State (Phase 1)
Most Kernel infrastructure still lives in the original locations. These will be moved in Phase 7:
- `src/Helpers/SessionGuard.php` → `modules/Kernel/SessionGuard.php`
- `partials/header.php` → `modules/Kernel/templates/header.php`
- `partials/footer.php` → `modules/Kernel/templates/footer.php`
- `admin/partials/header.php` → `modules/Kernel/templates/admin-header.php`
- `admin/partials/footer.php` → `modules/Kernel/templates/admin-footer.php`
- `admin/auth-check.php` → `modules/Kernel/AuthGate.php`
- `includes/bootstrap.php` → `modules/Kernel/Bootstrap.php`
- `includes/asset-helper.php` → `modules/Kernel/AssetHelper.php`
- `includes/db.php` → `modules/Kernel/Database.php`
