# TESTING.md — Testing Guide

## Overview

This project uses two testing frameworks:

| Framework | Type | Tests | Location | Run Command |
|-----------|------|-------|----------|-------------|
| **Playwright** | E2E (browser) | 172 tests | `tests/*.spec.js` | `npx playwright test` |
| **PHPUnit** | Unit (PHP classes) | 74 tests, 505 assertions | `tests/Unit/*.php` | `vendor/bin/phpunit` |

---

## Quick Start

```bash
# Run all tests (E2E + unit)
npx playwright test && vendor/bin/phpunit

# Run only E2E tests
npx playwright test

# Run only PHPUnit tests
vendor/bin/phpunit

# Run a single E2E test file
npx playwright test tests/puja-booking.spec.js

# Run a single PHPUnit test class
vendor/bin/phpunit tests/Unit/RbacServiceTest.php
```

---

## Prerequisites

### E2E Tests (Playwright)

- **Chrome/Chromium** must be installed locally
- The **Laragon server** (or equivalent) must be running at `http://isjm.test:8080`
- The **MySQL database** must be seeded with test data (run migrations first)
- **Admin tests** (`e2e-admin.spec.js`) require authenticated sessions — set these env vars:
  ```bash
  ADMIN_USER=admin ADMIN_PASS="your-password" npx playwright test tests/e2e-admin.spec.js
  ```
  If no credentials are provided, auth-dependent tests auto-skip.

### PHPUnit Tests

- **No external services required** — PHPUnit tests use an in-memory SQLite database
- Just ensure `vendor/` is installed (`composer install`)
- The `autoload-dev` PSR-4 mapping maps `Isjm\Tests\` to `tests/`

---

## E2E Tests — Playwright Patterns

### Test Structure

Each spec file follows Playwright's `test.describe` / `test` pattern:

```js
const { test, expect } = require('@playwright/test');

test.describe('Feature Name', () => {
  test('specific behavior being tested', async ({ page }) => {
    await page.goto('/some-url');
    await expect(page.locator('.selector')).toBeVisible();
    await expect(page).toHaveTitle(/Expected Title/);
  });
});
```

### 6 Test Files

| File | Tests | What It Covers |
|------|-------|----------------|
| `e2e-all-pages.spec.js` | 64 | Smoke tests for all public pages (200 status), homepage content (nav, hero, footer), dynamic pages, cart flow, admin login redirect, asset loading, navigation, responsive design, security headers, performance |
| `e2e-admin.spec.js` | 54 (52 active, 2 skipped) | Unauthenticated access redirects, login form validation/error/POST method, branding, dashboard structure (auth-required), asset loading, CSRF, rate limiting, page titles, responsive, logout, API endpoints, form security, session handling |
| `puja-booking.spec.js` | 11 | Puja listing cards (7 cards, content validation), detail page navigation, offering categories, selection toggle/total calculation, multi-offering sums, form validation (required fields + valid input), date minimum, all 7 detail pages load, back navigation, invalid slug redirect |
| `yagya-booking.spec.js` | 11 | Yagya listing cards (5 tiers), detail page navigation, tier/render info sections, Book Now modal, modal pre-fill, all detail pages load, hymns/sections display, correct links, back navigation, inclusion badges, higher tier details |
| `panihati-yatra.spec.js` | 15 | Registration page load, form fields rendering, bus/own-vehicle toggle, price calculation (adults + kids), own-vehicle pricing diff, summary labels, bhakti sadan dropdown, pickup locations, validation, gallery, venue info, kids-free notice, Razorpay notice |
| `payment-flow.spec.js` | 16 | Donate page loads, cause/daily-seva/diwali pages, form required fields, amount selection, API create-order endpoint, verify-payment GET handling, booking/puja/yagya listing + detail loads, panihati registration + pricing, Razorpay script load, all donate pages load without 500 |

### Common Patterns

#### 1. Page load + title check

```js
await page.goto('/some-url');
await expect(page).toHaveTitle(/Expected Pattern/);
```

#### 2. Element visibility verification

```js
await expect(page.locator('.my-class')).toBeVisible();
await expect(page.locator('#myId')).toBeVisible({ timeout: 10000 });
```

#### 3. Form interaction

```js
await page.fill('#fieldName', 'test value');
await page.click('button[type="submit"]');
await expect(page.locator('#fieldName')).toHaveValue('test value');
```

#### 4. Data-driven tests (parameterized pages)

```js
const PAGES = [
  { url: '/about', name: 'About' },
  { url: '/donate', name: 'Donate' },
];

for (const pg of PAGES) {
  test(`${pg.name} (${pg.url}) loads correctly`, async ({ request }) => {
    const response = await request.get(pg.url, { timeout: 15000 });
    expect(response.status()).toBe(200);
  });
}
```

#### 5. Auth-conditional tests (skip when no credentials)

```js
test.beforeEach(async ({ page }) => {
  const pass = process.env.ADMIN_PASS;
  if (!pass) {
    test.skip();
    return;
  }
  // login flow...
});
```

#### 6. Dynamic count assertions

```js
const cards = page.locator('.puja-card');
await expect(cards).toHaveCount(7);
const count = await cards.count();
expect(count).toBeGreaterThan(0);
```

#### 7. URL and navigation checks

```js
// URL pattern
await expect(page).toHaveURL(/\/booking\/puja\/some-slug/);

// Breadcrumb navigation
await page.locator('.breadcrumb a:has-text("Puja Offerings")').click();

// Redirect without following
const response = await request.get('/some-url', { maxRedirects: 0 });
expect(response.status()).toBe(302);
expect(response.headers()['location']).toContain('/expected-path');
```

#### 8. Response headers and security

```js
const response = await request.get('/');
expect(response.headers()['x-frame-options']).toBeTruthy();
expect(response.headers()['x-content-type-options']).toBe('nosniff');
```

#### 9. Console error monitoring

```js
test('no console errors', async ({ page }) => {
  const errors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') errors.push(msg.text());
  });
  await page.goto('/');
  await page.waitForLoadState('networkidle');
  expect(errors).toHaveLength(0);
});
```

#### 10. Performance timing

```js
const startTime = Date.now();
await page.goto('/', { waitUntil: 'domcontentloaded' });
expect(Date.now() - startTime).toBeLessThan(5000);
```

### Running Specific Tests

```bash
# Single file
npx playwright test tests/puja-booking.spec.js

# Files matching pattern
npx playwright test tests/e2e-*

# With grep (test name pattern)
npx playwright test --grep "login"

# With debug mode (headed browser + devtools)
npx playwright test --debug

# Generate trace for failures
npx playwright test --trace on
```

### Config Reference (`playwright.config.js`)

| Setting | Value | Notes |
|---------|-------|-------|
| `baseURL` | `http://isjm.test:8080` | Matches Laragon default |
| `timeout` | 60s | Per-test timeout |
| `expect.timeout` | 10s | Assertion wait time |
| `retries` | 0 | Fail fast in development |
| `fullyParallel` | `false` | Tests run sequentially to avoid session conflicts |
| `headless` | true | No visible browser |
| `screenshot` | `only-on-failure` | Debug artifacts |
| `trace` | `retain-on-failure` | Debug traces |

---

## PHPUnit Tests — Patterns

### Test Structure

PHPUnit tests use PHP 8 attributes (`#[Test]`, `#[DataProvider]`, `#[CoversClass]`):

```php
<?php

namespace Isjm\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MyClass::class)]
class MyClassTest extends TestCase
{
    // Section comment
    // ==========================================

    #[Test]
    public function method_scenario_expectedBehavior(): void
    {
        // Arrange
        // Act
        // Assert
        $this->assertTrue($result);
    }
}
```

### Current Test Files

| File | Tests | What It Covers |
|------|-------|----------------|
| `tests/Unit/PermissionRegistryTest.php` | ~17 | Module structure (13 modules, required keys, icon format), permission count (55), slug format (`module.action`), label format, sort order consistency (including DataProvider with 4 cases), unknown module fallback |
| `tests/Unit/RbacServiceTest.php` | ~55 | Permission checking (hasPermission, hasAnyPermission, hasAllPermissions), super_admin bypass, role CRUD (create/update/delete/system role protection), permission CRUD (filter, grouped), role-permission assignment (replace, clear), user-role assignment (replace, track assigner, clear), utility methods, edge cases (empty arrays, nonexistent IDs, multi-role unions, permission revocation) |

### RbacTestHelper — In-Memory SQLite Database

Both RBAC tests rely on `RbacTestHelper` which creates an in-memory SQLite database:

```php
protected function setUp(): void
{
    parent::setUp();
    RbacTestHelper::reset();         // Fresh DB for each test
    $db = RbacTestHelper::createDb(); // Creates tables + seeds data
    $this->service = new RbacService($db); // Inject test DB
}
```

The helper seeds:
- **9 roles** (super_admin, temple_admin, donation_manager, festival_manager, accounts, content_manager, report_viewer, devotee_care, read_only)
- **18 permissions** (dashboard, donations, festivals, blogs, reports × view/create/edit/delete/export)
- **Role-permission assignments** (e.g., temple_admin gets all 18, read_only gets 1)
- **7 admin users** with assigned roles

### Common Patterns

#### 1. Permission checking

```php
#[Test]
public function temple_admin_has_permissions(): void
{
    $adminId = $this->adminIds['admin_user'];

    $this->assertTrue($this->service->hasPermission($adminId, 'donations.view'));
    $this->assertTrue($this->service->hasPermission($adminId, 'donations.create'));
    $this->assertFalse($this->service->hasPermission($adminId, 'nonexistent.perm'));
}
```

#### 2. Super admin bypass

```php
#[Test]
public function super_admin_has_all_permissions(): void
{
    $adminId = $this->adminIds['super_admin'];

    $this->assertTrue($this->service->hasPermission($adminId, 'any.random.perm'));
    $this->assertTrue($this->service->hasAnyPermission($adminId, ['nothing.granted']));
}
```

#### 3. Role CRUD

```php
#[Test]
public function createRole_creates_and_returns_id(): void
{
    $id = $this->service->createRole('test_role', 'Test Role', 'A role for testing', false, 50);

    $this->assertGreaterThan(0, $id);
    $role = $this->service->getRole($id);
    $this->assertSame('test_role', $role['slug']);
}

#[Test]
public function deleteRole_returns_false_for_system_role(): void
{
    $this->assertFalse($this->service->deleteRole(1)); // super_admin
}
```

#### 4. Data provider (parameterized tests)

```php
#[Test]
#[DataProvider('sortOrderProvider')]
public function getSortOrder_returns_consistent_ordering(string $module, string $action, int $expectedMin): void
{
    $order = PermissionRegistry::getSortOrder($module, $action);
    $this->assertGreaterThanOrEqual($expectedMin, $order);
}

public static function sortOrderProvider(): array
{
    return [
        'dashboard view' => ['dashboard', 'view', 0],
        'donations view' => ['donations', 'view', 100],
    ];
}
```

#### 5. Role assignment replacement

```php
#[Test]
public function assignRoles_replaces_all_roles(): void
{
    $this->service->assignRoles($adminId, [3, 7]); // replaces existing

    $roles = $this->service->getAdminRoles($adminId);
    $this->assertCount(2, $roles);
}

#[Test]
public function assignRoles_clears_all_with_empty_array(): void
{
    $this->service->assignRoles($adminId, []);
    $this->assertEmpty($this->service->getAdminRoles($adminId));
}
```

#### 6. Edge cases and boundaries

```php
#[Test]
public function unknown_admin_returns_empty(): void
{
    $this->assertEmpty($this->service->getAdminPermissions(99999));
}

#[Test]
public function empty_permission_array_returns_false(): void
{
    $this->assertFalse($this->service->hasAnyPermission($adminId, []));
}

#[Test]
public function multiple_role_union_grants_all_permissions(): void
{
    $this->service->assignRoles($adminId, [3, 4]);
    $this->assertTrue($this->service->hasPermission($adminId, 'donations.create'));
    $this->assertTrue($this->service->hasPermission($adminId, 'festivals.create'));
}
```

### Adding New PHPUnit Tests

1. **Create the test class** in `tests/Unit/` with namespace `Isjm\Tests\Unit`
2. **Use `#[CoversClass]`** to document which class is under test
3. **Use `#[Test]`** attribute on each test method (PHP 8.1+)
4. **Name methods** as `methodName_scenario_expectedBehavior`
5. **Use `RbacTestHelper`** if you need a database (creates fresh SQLite for each test)
6. **Run**: `vendor/bin/phpunit tests/Unit/YourTest.php`

```php
<?php

namespace Isjm\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(YourClass::class)]
class YourClassTest extends TestCase
{
    #[Test]
    public function method_does_something(): void
    {
        $result = YourClass::someMethod();
        $this->assertSame('expected', $result);
    }
}
```

### Config Reference (`phpunit.xml`)

| Setting | Value | Notes |
|---------|-------|-------|
| `bootstrap` | `vendor/autoload.php` | Composer autoloader |
| `cacheDirectory` | `.phpunit.cache` | Generated cache (gitignored) |
| `colors` | `true` | Terminal color output |
| `failOnRisky` | `true` | Fail on risky tests |
| `failOnWarning` | `true` | Fail on warnings |
| `testsuite name="RBAC"` | `tests/Unit` | Directory containing test classes |

---

## Debugging

### E2E Test Failures

```bash
# Run with visible browser
npx playwright test --headed

# Run a single test with trace
npx playwright test tests/puja-booking.spec.js --trace on

# View trace
npx playwright show-trace test-results/.../trace.zip

# Slow down for visual debugging
npx playwright test --slow-mo 500
```

Artifacts are saved to `test-results/` on failure:
- `screenshot.png` — Page state at failure
- `trace.zip` — Full interaction trace (open with `show-trace`)

### PHPUnit Test Failures

```bash
# Verbose output
vendor/bin/phpunit --verbose

# Run specific test
vendor/bin/phpunit --filter=testCreateRole

# Stop on first failure
vendor/bin/phpunit --stop-on-failure

# Test suite with coverage
vendor/bin/phpunit --coverage-html coverage/
```

---

## CI Considerations

### Environment Variables

```bash
# E2E tests (admin section)
ADMIN_USER=admin
ADMIN_PASS=your-admin-password

# PHPUnit — none needed (uses SQLite in-memory)
```

### Expected Results

| Suite | Tests | Expected |
|-------|-------|----------|
| E2E (all public pages) | 64 | 64 passed |
| E2E (admin) | 54 (52 active) | 52 passed, 2 skipped (auth-required) |
| Puja booking | 11 | 11 passed |
| Yagya booking | 11 | 11 passed |
| Panihati yatra | 15 | 15 passed |
| Payment flow | 16 | 16 passed |
| PHPUnit (RBAC) | 74 | 74 passed, 505 assertions |

Total: **244 tests** (170 E2E + 74 PHPUnit)

### Common Gotchas

1. **Port conflicts** — Ensure Laragon is on port 8080, or update `playwright.config.js`
2. **Database state** — E2E tests expect seeded MySQL data. Run migrations first.
3. **Flaky `/services` page** — Known intermittent "socket hang up" on this route. Re-run isolates it.
4. **Windows line endings** — `php -l` checks work fine on Windows (CRLF).
5. **Session interference** — E2E tests run sequentially (`fullyParallel: false`) to avoid session conflicts.

---

## Writing New Tests: Quick Checklist

### Adding an E2E Test

- [ ] Add test description in the data-driven pattern if testing multiple pages
- [ ] Use `expect().toBeVisible()` for presence checks
- [ ] Use `expect().toContainText()` for content verification
- [ ] Use `maxRedirects: 0` for redirect assertion tests
- [ ] Add `timeout: 15000` for slow-loading pages
- [ ] Add to the appropriate `test.describe` block or create a new one
- [ ] Run it in isolation first: `npx playwright test your-file.spec.js`

### Adding a PHPUnit Test

- [ ] Use `#[CoversClass(ClassName::class)]` annotation
- [ ] Use `#[Test]` attribute on each method
- [ ] Use `RbacTestHelper::reset()` + `createDb()` for DB-dependent tests
- [ ] Name descriptively: `method_scenario_expectedBehavior`
- [ ] Test edge cases: empty arrays, null values, nonexistent IDs, super_admin bypass
- [ ] Run in isolation: `vendor/bin/phpunit tests/Unit/YourTest.php`
