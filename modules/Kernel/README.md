# Module: Kernel

## Purpose
Cross-cutting infrastructure shared by all other modules. The Kernel provides the foundation that every feature module depends on — authentication, routing, layout rendering, database connection, CSRF protection, and asset management.

No feature logic lives in this module. Its classes are used by all other modules but never call into them.

## Owned Infrastructure

| Component | File | Purpose |
|-----------|------|---------|
| **Configuration** | `config.php` | Site config, env vars, constants, Razorpay keys |
| **Database** | `includes/db.php` | PDO singleton (`getDB()`) — all DB queries go through this |
| **Auth Guard** | `src/Helpers/SessionGuard.php` | Login check, RBAC permission checking, CSRF tokens |
| **Auth Gate** | `Admin/auth-check.php` | Admin page authentication gate, provides `hasPermission()` and `requirePermission()` |
| **Bootstrap** | `includes/bootstrap.php` | Config loading, session start, CSRF token generation |
| **Assets** | `includes/asset-helper.php` | Cache-busted asset URLs via `asset()` and `assetPath()` helpers |
| **Layout (Public)** | `partials/header.php`, `partials/footer.php` | Public site header and footer |
| **Layout (Admin)** | `Admin/partials/header.php`, `Admin/partials/footer.php` | Admin sidebar, header, footer |

## Dependencies
- **None** — Kernel is at the bottom of the dependency chain
- All other modules depend on Kernel

## Owned Database Tables
- `admins` — Admin users with roles (legacy `role` column deprecated — use `rbac_user_roles`)
- `login_attempts` — Login attempt tracking for brute-force protection

## Public Interface
```php
// Database (primarily for module repositories)
// File: includes/db.php
function getDB(): PDO

// Auth (used by admin pages and auth-check.php)
// File: src/Helpers/SessionGuard.php
class SessionGuard {
    public static function init(): void;
    public static function requireLogin(): void;
    public static function requireRole(array $roles): void;
    public static function hasRole(array $roles): bool;
    public static function getRole(): string;
    public static function getAdminId(): ?int;
}

// Permission checking (global helpers from auth-check.php)
function hasPermission(string $slug): bool;
function requirePermission(string $slug): void;
function hasAnyPermission(array $slugs): bool;
function requireAnyPermission(array $slugs): void;

// Asset loading (used by all pages)
// File: includes/asset-helper.php
function asset(string $path): string;       // Cache-busted URL
function assetPath(string $path): string;   // Cache-busted filesystem path

// Bootstrap (used by all entry points)
// File: includes/bootstrap.php
//   → require config.php + session_start() + CSRF token generation
```

## Entry Points
The Kernel module has no public-facing pages. It is loaded by every other module.

| Type | File | Used By |
|------|------|---------|
| Bootstrap | `includes/bootstrap.php` | Every entry point |
| Database | `includes/db.php` | Every module |
| Auth Gate | `Admin/auth-check.php` | Every admin page |
| Assets | `includes/asset-helper.php` | Every page |
| Layout (public) | `partials/header.php` | Public pages |
| Layout (public) | `partials/footer.php` | Public pages |
| Layout (admin) | `Admin/partials/header.php` | Admin pages |
| Layout (admin) | `Admin/partials/footer.php` | Admin pages |
| Config | `config.php` | Wrapper at root → actual here |

## Directory Structure
```
modules/Kernel/
├── config.php                   # Site configuration
├── includes/
│   ├── db.php                   # PDO singleton (getDB())
│   ├── bootstrap.php            # Config + session + CSRF
│   ├── asset-helper.php         # Cache-busted asset URLs
│   └── donation-helpers.php     # Donation facade (backward compatible)
├── partials/
│   ├── header.php               # Public site header
│   ├── footer.php               # Public site footer
│   └── home-*.php               # Homepage partials (hero, grid, etc.)
├── Admin/
│   ├── auth-check.php           # Admin auth gate + permission loading
│   └── partials/
│       ├── header.php           # Admin sidebar + header
│       └── footer.php           # Admin footer + scripts
├── src/
│   ├── Donations/               # Repository, Service, Renderer classes
│   │   ├── DonationRepository.php
│   │   ├── DonationService.php
│   │   └── DonationRenderer.php
│   └── Helpers/
│       └── SessionGuard.php     # Auth guard class
├── content/
│   └── index.php                # Homepage
├── README.md                    # This file
└── DECISIONS.md                 # Architecture decisions
```

## Security Responsibilities
- Session management (start, validate, regenerate, destroy)
- CSRF token generation and validation
- Global permission helpers (`hasPermission()`, `requirePermission()`)
- Login rate limiting
- Admin authentication gate
- Output escaping via `htmlspecialchars()` (enforced in coding standards)
- Prepared statements via PDO (enforced in coding standards)

See [`SECURITY.md`](../../SECURITY.md) for full security policy.

## Related Documentation
- [`SECURITY.md`](../../SECURITY.md) — Security policy with OWASP Top 10
- [`CODING_STANDARDS.md`](../../CODING_STANDARDS.md) — Coding conventions
- [`MODULE_INDEX.md`](../../MODULE_INDEX.md) — Module index
- [`docs/ADMIN.md`](../../docs/ADMIN.md) — Admin panel reference
