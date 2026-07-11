# CODING_STANDARDS.md — Coding Standards & Conventions

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `SECURITY.md`, `WORKFLOWS.md`, `DEVELOPMENT_WORKFLOW.md`

---

## Table of Contents

1. [PHP Standards](#1-php-standards)
2. [SQL Standards](#2-sql-standards)
3. [HTML Standards](#3-html-standards)
4. [CSS Standards](#4-css-standards)
5. [JavaScript Standards](#5-javascript-standards)
6. [File & Directory Naming](#6-file--directory-naming)
7. [Module Structure](#7-module-structure)
8. [Code Patterns](#8-code-patterns)
9. [Testing Standards](#9-testing-standards)
10. [Documentation Standards](#10-documentation-standards)
11. [Migration Standards](#11-migration-standards)
12. [Error Handling](#12-error-handling)

---

## 1. PHP Standards

### 1.1 Framework

This project uses **vanilla PHP** — no Laravel, Symfony, Slim, or other frameworks. All code is plain PHP with PDO for database access.

### 1.2 Language Features

| Feature | Status | Notes |
|---------|--------|-------|
| PHP 8.0+ features | ✅ Encouraged | Named arguments, match expressions, union types, nullsafe operator |
| PHP 8.1+ features | ✅ Allowed | Enums, readonly properties, `never` return type, fiber (not used) |
| PHP 8.2+ features | ❌ Avoid | `readonly` classes OK; `#[\SensitiveParameter]` — not yet tested |
| Attributes | ✅ Encouraged | `#[Test]`, `#[DataProvider]`, `#[CoversClass]` in tests |
| Strict types | ✅ Preferred | `declare(strict_types=1);` at top of new files |
| Named arguments | ✅ Allowed | Provide clarity for functions with many optional params |
| Match expressions | ✅ Preferred | Over `switch/case` for value matching |

### 1.3 Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Classes | `PascalCase` | `DonationService`, `SessionGuard` |
| Methods | `camelCase` | `getCauseBySlug()`, `formatAmount()` |
| Functions | `camelCase` (global) / `snake_case` (legacy) | `getDB()`, `getDonationCauseBySlug()` |
| Variables | `$camelCase` | `$donorName`, `$sevaList` |
| Constants | `UPPER_SNAKE_CASE` | `RAZORPAY_KEY_ID`, `BASE_URL` |
| DB Columns | `snake_case` | `donor_name`, `payment_status`, `master_seva_id` |
| Namespaces | `PascalCase` with `Isjm\` prefix | `Isjm\Modules\Donation\DonationRepository` |
| Interfaces | `PascalCase` with `Interface` suffix | `DonationRepositoryInterface` |
| File names | Match class/function name | `DonationRepository.php` |

### 1.4 File Structure

```php
<?php

declare(strict_types=1); // Optional but preferred for new files

namespace Isjm\Modules\Donation;

/**
 * One-line summary of the class purpose.
 *
 * Longer description if needed, explaining what this class does
 * and any important behavioral details.
 */
class DonationService
{
    // Properties first (typed)
    private DonationRepository $repo;
    private array $config;

    // Constructor
    public function __construct(DonationRepository $repo)
    {
        $this->repo = $repo;
    }

    // Public methods
    public function getCauseBySlug(string $slug): ?array
    {
        return $this->repo->findCauseBySlug($slug);
    }

    // Private/protected methods
    private function validateAmount(int $amount): bool
    {
        return $amount >= 100; // Minimum ₹1 in paise
    }
}
```

### 1.5 Documentation Blocks

Use PHPDoc-style comments for all classes and public methods:

```php
/**
 * Brief description of the method.
 *
 * Longer description if the behavior is non-obvious.
 *
 * @param string $slug The URL-friendly identifier
 * @param bool $includeInactive Whether to include inactive items (default: false)
 * @return array|null The cause data, or null if not found
 */
public function getCauseBySlug(string $slug, bool $includeInactive = false): ?array
```

### 1.6 Output Escaping

**NEVER output raw user data without escaping:**

```php
// ✅ CORRECT — always escape
<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>

// ✅ CORRECT — for HTML attributes
value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"

// ❌ WRONG — XSS vulnerability
<?php echo $title; ?>
```

### 1.7 Includes & Requires

| Directive | When to Use |
|-----------|-------------|
| `require_once` | Dependencies that MUST load (config, bootstrap, auth-check) |
| `include` | Optional partials (header, footer on pages that might not need them) |
| `include_once` | Rarely — when a partial might be included multiple times |

### 1.8 Error Handling

```php
// ✅ CORRECT — catch and handle
try {
    $result = $this->repo->findById($id);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage()); // Log details
    // Show generic message to user
    return ['error' => 'A database error occurred. Please try again.'];
}

// ❌ WRONG — exposing details
try {
    // ...
} catch (PDOException $e) {
    echo $e->getMessage(); // NEVER expose to users
}
```

### 1.9 PSR-4 Autoloading

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
  }
}
```

- `Isjm\Modules\Donation\DonationRepository` → `modules/Donation/DonationRepository.php`
- `Isjm\Helpers\SessionGuard` → `modules/Kernel/src/Helpers/SessionGuard.php`
- `Isjm\Modules\RBAC\RbacService` → `modules/RBAC/RbacService.php`

---

## 2. SQL Standards

### 2.1 Prepared Statements — Mandatory

```php
// ✅ CORRECT — positional parameters
$stmt = $db->prepare("SELECT * FROM donation_causes WHERE slug = ? AND is_active = ?");
$stmt->execute([$slug, 1]);

// ✅ CORRECT — named parameters
$stmt = $db->prepare(
    "SELECT * FROM donation_transactions 
     WHERE cause_id = :causeId AND payment_status = :status"
);
$stmt->execute([':causeId' => $causeId, ':status' => 'paid']);

// ❌ WRONG — NEVER string concatenation
$stmt = $db->query("SELECT * FROM users WHERE id = " . $_GET['id']);
```

### 2.2 Dynamic Queries

**ORDER BY** columns must come from an allowlist:

```php
$allowedSorts = ['amount', 'date', 'name', 'status'];
$sort = $_GET['sort'] ?? 'date';
if (!in_array($sort, $allowedSorts)) {
    $sort = 'date';
}
$stmt = $db->prepare("SELECT * FROM donations ORDER BY $sort");
```

**LIMIT/OFFSET** must be integers:

```php
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(100, max(1, (int)($_GET['per_page'] ?? 50)));
$offset = ($page - 1) * $perPage;
$stmt = $db->prepare("SELECT * FROM donations LIMIT ? OFFSET ?");
$stmt->execute([$perPage, $offset]);
```

### 2.3 LIKE Clauses

```php
// ✅ CORRECT
$stmt = $db->prepare("SELECT * FROM donors WHERE donor_name LIKE ?");
$stmt->execute(['%' . $searchTerm . '%']);
```

### 2.4 INSERT Pattern

```php
$stmt = $db->prepare(
    "INSERT INTO donation_causes (slug, title, category, is_active, sort_order) 
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([$slug, $title, $category, 1, $sortOrder]);
$newId = (int) $db->lastInsertId();
```

### 2.5 UPDATE Pattern

```php
$stmt = $db->prepare(
    "UPDATE donation_causes SET title = ?, is_active = ? WHERE id = ?"
);
$stmt->execute([$title, $isActive, $id]);
```

### 2.6 DELETE — Soft Delete Preferered

Use `is_active = 0` or `status = 'deleted'` for soft deletes where possible.
Hard deletes are reserved for cleanup operations only.

---

## 3. HTML Standards

### 3.1 Page Template (Public)

```php
<?php
$pageTitle = 'Page Name';
include 'partials/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1 class="reveal"><?php echo htmlspecialchars($pageTitle); ?></h1>
    <div class="breadcrumb">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span><?php echo htmlspecialchars($pageTitle); ?></span>
    </div>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <!-- Content -->
  </div>
</section>

<?php include 'partials/footer.php'; ?>
```

### 3.2 Page Template (Admin)

```php
<?php
$pageTitle = 'Page Title';
$activePage = 'page-slug';
requirePermission('module.view');

include 'partials/header.php';
?>

<div class="admin-page-header">
  <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
</div>

<div class="admin-card">
  <!-- Content -->
</div>

<?php include 'partials/footer.php'; ?>
```

### 3.3 Form Template with CSRF

```php
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" class="form-control" 
               value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
    </div>
    
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

### 3.4 `<base>` Tag

The admin header sets `<base href="<?php echo BASE_URL; ?>">`. All relative URLs resolve against `BASE_URL`, not the current page URL. Use absolute paths for CSS, JS, and links:

```php
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<a href="<?php echo BASE_URL; ?>about">About</a>
```

---

## 4. CSS Standards

### 4.1 Use CSS Custom Properties

```css
/* ✅ CORRECT */
color: var(--primary);
font-family: var(--font-heading);
margin: var(--space-md);

/* ❌ WRONG */
color: #c86b1f;
font-family: 'Cinzel', serif;
margin: 16px;
```

### 4.2 CSS Variable Reference

| Variable | Value | Usage |
|----------|-------|-------|
| `--primary` | `#c86b1f` | Primary accent color |
| `--accent` | `#d4af37` | Gold accent |
| `--maroon` | `#7b1e1e` | Secondary dark accent |
| `--cream` | `#f8f1e7` | Background/light areas |
| `--dark` | `#2c1b12` | Text on light backgrounds |
| `--font-heading` | `'Cinzel', serif` | Heading font |
| `--font-subheading` | `'Cormorant Garamond', serif` | Subheading font |
| `--font-body` | `'Poppins', sans-serif` | Body text font |
| `--text` | `#4a3728` | Body text color |
| `--text-light` | `#8b7355` | Muted/secondary text |

### 4.3 Naming Convention

Use descriptive, lowercase-hyphenated class names:

```css
/* ✅ CORRECT */
.admin-stat-card { }
.donation-page-header { }
.seva-offering-grid { }
.cause-card-title { }

/* ❌ WRONG */
.BigCard { }
.donationPage { }
.a { }
```

### 4.4 Layout

- Use **CSS Grid** for page-level layouts
- Use **Flexbox** for component-level layouts
- **Avoid floats** for layout purposes
- Use `@media (max-width: 768px)` breakpoint for mobile responsive

### 4.5 Animations

```css
/* ✅ CORRECT — use CSS transitions */
.admin-nav-group .admin-subnav {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, opacity 0.3s ease;
}

.admin-nav-group.active .admin-subnav {
    max-height: 500px;
    opacity: 1;
}
```

---

## 5. JavaScript Standards

### 5.1 No jQuery or Frameworks

Vanilla JavaScript only — no jQuery, React, Vue, or other frontend frameworks.

### 5.2 DOM Ready

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Your code here
});
```

### 5.3 Event Listeners

```javascript
// ✅ CORRECT
document.querySelector('#submitBtn').addEventListener('click', function(e) {
    e.preventDefault();
    // Handle click
});

// ❌ WRONG — inline event handlers
<button onclick="doSomething()">Click</button>
```

### 5.4 Fetch API

```javascript
// ✅ CORRECT
fetch('/api/create-order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => {
    // Handle success
})
.catch(error => {
    console.error('Error:', error);
});
```

### 5.5 Local Storage (Cart System)

The cart system uses `localStorage` via `cart.js` — loaded globally on all pages.

```javascript
// Cart operations
cart.addItem({ id: 1, name: 'Seva', amount: 501 });
cart.getItems();
cart.clear();
cart.getTotalAmount();
```

### 5.6 Naming

```javascript
// Variables and functions: camelCase
let totalAmount = 0;
function calculateTotal() { }

// Classes: PascalCase
class CartManager { }
```

---

## 6. File & Directory Naming

| Type | Convention | Example |
|------|-----------|---------|
| PHP classes | `PascalCase.php` | `DonationService.php` |
| PHP templates | `kebab-case.php` | `cause-detail.php`, `donation-form.php` |
| Admin pages | Matches module slug | `panihati-yatra.php` |
| API endpoints | kebab-case | `create-order.php`, `verify-payment.php` |
| JavaScript | `kebab-case.js` | `donate.js`, `checkout.js` |
| CSS | `kebab-case.css` | `sudamaseva.css`, `admin-style.css` |
| Images | `kebab-case.ext` | `rath-yatra-banner.jpg` |
| Database migrations | `NNN_descriptive_name.php` | `001_create_rbac_tables.php` |
| Test files | `*Test.php` / `*.spec.js` | `RbacServiceTest.php`, `puja-booking.spec.js` |

---

## 7. Module Structure

Each module follows this pattern:

```
modules/<Module>/
├── Admin/           # Admin panel pages (if applicable)
├── api/             # API endpoints (if applicable)
├── assets/          # Module-owned CSS/JS (if applicable)
│   ├── css/
│   └── js/
├── content/         # Public-facing pages
├── src/             # PHP classes (Repository, Service, etc.)
│   ├── <Module>Repository.php
│   └── <Module>Service.php
├── database/        # Module-specific migrations (if applicable)
│   └── migrations/
├── migrations/      # Alternative migration location
├── templates/       # Page templates (if applicable)
├── tests/           # Module tests (if applicable)
├── README.md        # REQUIRED: Module overview
├── DATABASE.md      # IF HAS TABLES: Schema documentation
├── API.md           # IF HAS API: Endpoint contracts
├── DECISIONS.md     # REQUIRED: Architecture decisions
├── TASKS.md         # IF HAS PENDING: Module backlog
├── TESTING.md       # IF HAS TESTS: Testing guide
└── routes.php       # Route definitions (planned, not yet fully active)
```

### Wrapper Convention

All original file paths are preserved as backward-compatible wrappers:

```php
<?php
/**
 * Backward-compatibility wrapper.
 * File has been moved to modules/Module/Subdir/filename.php.
 */
require_once __DIR__ . '/../modules/Module/Subdir/' . basename(__FILE__);
```

**When adding new files:** You don't need to create wrappers. Only existing files have wrappers for backward compatibility.

---

## 8. Code Patterns

### 8.1 Repository Pattern

```php
class DonationRepository
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM donation_causes WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
```

### 8.2 Service Pattern

```php
class DonationService
{
    private DonationRepository $repo;
    
    public function __construct(DonationRepository $repo)
    {
        $this->repo = $repo;
    }
    
    public function getCauseBySlug(string $slug): ?array
    {
        $cause = $this->repo->findCauseBySlug($slug);
        if (!$cause) {
            return null;
        }
        // Apply business logic
        $cause['sevas'] = $this->repo->getCauseSevas($cause['id']);
        return $cause;
    }
}
```

### 8.3 Renderer Pattern

```php
class DonationRenderer
{
    public function renderDonationCTA(array $cause): void
    {
        ?>
        <div class="donation-cta">
            <h3><?php echo htmlspecialchars($cause['title']); ?></h3>
            <a href="<?php echo BASE_URL; ?>donate/<?php echo $cause['slug']; ?>" 
               class="btn btn-primary">Donate Now</a>
        </div>
        <?php
    }
}
```

### 8.4 SessionGuard Pattern

```php
// Initialize
SessionGuard::init();

// Check login
SessionGuard::requireLogin();

// Check permissions
if (hasPermission('donations.view')) {
    // Show content
}

// Require permission (blocks with 403)
requirePermission('donations.edit');
```

### 8.5 Migration Pattern

```php
<?php
require_once __DIR__ . '/../../config.php';

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Running migration: Description of what this does...\n";

// Always idempotent — check before inserting
$check = $db->prepare("SELECT COUNT(*) FROM target_table WHERE slug = ?");
$check->execute([$slug]);
if ($check->fetchColumn() > 0) {
    echo "Already exists, skipping.\n";
} else {
    $db->prepare("INSERT INTO target_table (...) VALUES (...)")->execute([...]);
    echo "Inserted.\n";
}

echo "Migration complete.\n";
```

---

## 9. Testing Standards

### 9.1 PHPUnit Tests

Located in `tests/Unit/` (or module-level `tests/` directories):

```php
<?php
namespace Isjm\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassName::class)]
class ClassNameTest extends TestCase
{
    #[Test]
    public function method_scenario_expectedBehavior(): void
    {
        // Arrange
        // Act
        // Assert
        $this->assertSame('expected', $result);
    }
}
```

### 9.2 E2E Tests (Playwright)

Located in `tests/`:

```javascript
const { test, expect } = require('@playwright/test');

test.describe('Feature Name', () => {
    test('specific behavior being tested', async ({ page }) => {
        await page.goto('/some-url');
        await expect(page.locator('.selector')).toBeVisible();
        await expect(page).toHaveTitle(/Expected Title/);
    });
});
```

### 9.3 Test Coverage Expectations

| Change Type | Required Tests |
|-------------|---------------|
| New PHP class | PHPUnit tests for all public methods |
| New API endpoint | E2E test + manual curl verification |
| New admin page | E2E test for auth + basic functionality |
| New public page | Add to smoke test (e2e-all-pages.spec.js) |
| Bug fix | Test reproducing the bug + verifying the fix |
| DB migration | Manual verification script |
| Payment flow change | E2E test + manual test with Razorpay test keys |

---

## 10. Documentation Standards

### 10.1 Required Module Documentation

Every module MUST have:
- `README.md` — Overview, purpose, entry points, dependencies, owned tables
- `DECISIONS.md` — Architecture decisions and trade-offs

SHOULD have:
- `DATABASE.md` — If module owns tables
- `API.md` — If module exposes API endpoints
- `TASKS.md` — If there is active backlog
- `TESTING.md` — If there are important test patterns

### 10.2 README Template

```markdown
# Module: [Module Name]

## Purpose
[One paragraph describing what this module does]

## Owned Database Tables
- `table_name` — Purpose

## Dependencies
- **[Module Name]** — What this module needs from other modules

## Entry Points
| Type | URL | File | Description |
|------|-----|------|-------------|
| Public | `/url` | `module/content/file.php` | Description |

## Directory Structure
```
modules/Module/
├── Admin/
├── api/
├── content/
└── README.md
```
```

---

## 11. Migration Standards

### 11.1 Migration Rules

1. **Idempotent**: Safe to re-run — check existence before inserting
2. **Transactional**: Use DB transactions for multi-step operations
3. **Sequenced**: Numbered prefixes (`001_`, `002_`) for ordering
4. **Self-contained**: Include all SQL within the PHP file
5. **Non-destructive**: Never delete data without backup

### 11.2 RBAC Migration Order

```bash
php modules/RBAC/database/migrations/001_create_rbac_tables.php
php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php
php modules/RBAC/database/migrations/003_migrate_existing_admins.php
```

---

## 12. Error Handling

### 12.1 User-Facing Errors

Always show generic error messages to users — log detailed errors server-side:

```php
// ✅ CORRECT
try {
    // operation
} catch (\Exception $e) {
    error_log('Operation failed: ' . $e->getMessage());
    $error = 'An error occurred. Please try again or contact support.';
}

// ❌ WRONG
catch (\Exception $e) {
    echo $e->getMessage(); // Security risk
}
```

### 12.2 API Error Responses

```php
http_response_code(400);
echo json_encode(['error' => 'Valid email is required.']);
exit;
```

### 12.3 Flash Messages (Admin)

```php
// Set flash message
$_SESSION['flash_success'] = 'Record updated successfully.';
$_SESSION['flash_error'] = 'Failed to update record.';

// Display (in header.php or page template)
if (!empty($_SESSION['flash_success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
    unset($_SESSION['flash_success']);
}
```
