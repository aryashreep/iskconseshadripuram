# Testing — Panihati Yatra Module

> **Last updated:** 2026-07-11
> **Related:** [`docs/TESTING.md`](../../docs/TESTING.md) (project-wide testing guide), [`MODULE_INDEX.md`](../../MODULE_INDEX.md)

## Overview

Tests for the Panihati module are E2E browser tests using Playwright. They verify the public registration form, admin management pages, and the Razorpay payment flow.

## Test Files Covering This Module

| Test File | Tests | What It Covers |
|-----------|-------|----------------|
| `tests/panihati-yatra.spec.js` | 15 | Public registration form — page load, form fields, travel mode toggle, price calculation, dropdown data, validation, gallery, venue |
| `tests/e2e-all-pages.spec.js` | 1 | Smoke test: `/yatra/panihati` returns HTTP 200 |
| `tests/e2e-admin.spec.js` | 8 | All 8 Panihati admin pages redirect to login when unauthenticated |
| `tests/payment-flow.spec.js` | 2 | Panihati price calculation and travel mode switching against production |

## Running Tests

```bash
# Panihati-specific tests only
npm run test:panihati

# Full test suite
npm test

# Admin Panihati pages
npx playwright test tests/e2e-admin.spec.js -g "Panihati"

# Smoke test + Panihati-specific
npx playwright test tests/panihati-yatra.spec.js tests/e2e-all-pages.spec.js
```

## Data Dependencies

These tests require a seeded database with the following tables populated:

| Table | Why | Minimum Rows |
|-------|-----|--------------|
| `panihati_bhakti_sadans` | Dropdown options in registration form | 20+ |
| `panihati_pickup_locations` | Dropdown options in registration form | 40+ |
| `panihati_pricing` | Dynamic pricing for bus/vehicle adults & kids | Current year |
| `panihati_yatra_registrations` | Registration records for admin dashboard stats | 0+ (empty is fine) |
| `panihati_yatra_offline_aggregates` | Aggregate offline entries for admin display | 0+ (empty is fine) |
| `panihati_yatra_combined_stats` | Database view combining paid + offline entries | Depends on above |

The sadans and pickup locations are seeded by the migration at `database/migrations/create_panihati_dynamic_tables.php`. If the test expects >20 sadans and >40 pickups but actual DB has fewer, the dropdown count assertions will fail.

## Key Test Scenarios

### Public Registration Form (15 tests)
1. **Page load** — Banner image visible, "About the Event" section present, venue details shown
2. **Form fields** — All input fields rendered, bus mode checked by default, dropdowns populated
3. **Travel mode toggle** — Clicking "Own Vehicle" disables pickup dropdown, price switches to vehicle rate
4. **Price calculation** — 1 adult × ₹1,000 = ₹1,000; adding kids updates total correctly
5. **Dropdown data** — Bhakti sadan dropdown has >20 options; pickup has >40 options
6. **Form validation** — Required attributes present on all mandatory fields
7. **UI elements** — Gallery images visible, venue info correct, free notice displayed, Razorpay security notice shown

### Admin Pages (8 tests in e2e-admin.spec.js)
Each of the 8 Panihati admin pages redirects to login when accessed without authentication:
- `/admin/panihati-yatra`
- `/admin/panihati-records`
- `/admin/panihati-pricing`
- `/admin/panihati-sadans`
- `/admin/panihati-pickups`
- `/admin/panihati-reports`
- `/admin/panihati-add-offline`
- `/admin/panihati-bulk-summary`

## Known Gotchas

- **Price hardcoding**: Tests assert ₹1,000 for bus adult and ₹600 for own-vehicle adult. These match the current year's defaults from `getPanihatiPricing()`. If pricing changes, update the test assertions accordingly:
  - `tests/panihati-yatra.spec.js` — lines asserting `₹1,000`, `₹600`, `₹2,200`, `₹3,000`
  - `tests/payment-flow.spec.js` — lines asserting `₹1,000`, `₹600`, `₹2,000`
- **Dropdown count assertions**: `expect(count).toBeGreaterThan(20)` for sadans and `toBeGreaterThan(40)` for pickups depend on seed data. If fewer sadans/pickups are seeded, these will fail.
- **`<base>` tag**: The admin header sets `<base href="...">`. Relative URLs in admin tests resolve against `BASE_URL`.
- **Year filter**: The admin dashboard year filter reloads the page via JS. Tests that change the year filter need to wait for page navigation.
- **File upload tests**: The "Add Offline Entry" page supports CSV/XLS upload. E2E tests for file upload require fixture files — currently not covered.
- **Bulk summary tests**: The "Add Bulk Summary" page has dynamic JS rows. Tests for this page would need to interact with the JS-powered form — currently not covered.

## Adding New Tests

When adding a new Panihati feature, add tests following these patterns:

```javascript
// Smoke test — add to e2e-all-pages.spec.js
{ url: '/yatra/new-feature', name: 'New Feature Page' },

// Form interaction test — add to panihati-yatra.spec.js
test('new feature works correctly', async ({ page }) => {
  await page.goto('/yatra/panihati');
  await page.locator('#newElement').click();
  await expect(page.locator('#result')).toContainText('Expected Value');
});

// Admin page test — add to e2e-admin.spec.js
test('/admin/panihati-new-page is protected', async ({ request }) => {
  const response = await request.get('/admin/panihati-new-page', { maxRedirects: 0 });
  expect([200, 302]).toContain(response.status());
});
```
