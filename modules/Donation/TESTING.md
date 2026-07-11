# Testing — Donation Module

> **Last updated:** 2026-07-11
> **Related:** [`docs/TESTING.md`](../../docs/TESTING.md) (project-wide testing guide)

## Overview

Tests for the Donation module are part of the project-wide E2E test suite using Playwright. They live in `tests/` at the project root rather than inside the module directory, because they test the full stack through browser interaction (wrappers → module code → database → Razorpay sandbox).

## Test Files Covering This Module

| Test File | Tests | What It Covers |
|-----------|-------|----------------|
| `tests/e2e-all-pages.spec.js` | 64 | Smoke tests for `/donate`, `/checkout`, `/donate/payment-success`, `/donate/payment-failed`; asset loading (`donate.js`, CSS); navigation; security headers |
| `tests/payment-flow.spec.js` | ~16 | Full Razorpay payment flow against **production URL** — order creation, verification, booking pages |
| `tests/puja-booking.spec.js` | 14 | Puja booking listing/detail pages (references donation transactions) |
| `tests/yagya-booking.spec.js` | 14 | Yagya booking listing/detail pages (references donation transactions) |
| `tests/e2e-admin.spec.js` | ~30 | Admin donation reports, seva catalogue, festivals, exports — auth redirect tests |

## Running Tests

```bash
# All tests
npm test

# Quick smoke test for donation pages
npx playwright test tests/e2e-all-pages.spec.js -g "donate"

# Admin donation reports
npx playwright test tests/e2e-admin.spec.js -g "donation|seva|festival"

# Puja/yagya booking flows
npm run test:puja
npm run test:yagya
```

## Data Dependencies

These tests require a seeded database with the following tables populated:

| Table | Why |
|-------|-----|
| `donation_causes` | 74 festival/service causes — drives `/donate` listing and detail pages |
| `donation_cause_master_sevas` | Pivot linking causes to seva offerings with pricing |
| `master_sevas` | 363+ seva items across categories |
| `master_seva_categories` | 10 top-level categories |
| `donation_causes_sevas` | Legacy per-cause seva table (backward compat) |
| `admins` | Required for admin page tests |

Run the seed migration:

```bash
php database/migrations/seed_dashboard_data.php
```

## Key Test Scenarios

### Donation Page Flow
1. `/donate` listing loads with all 74 cause cards
2. Click a cause → `/donate/{slug}` shows seva options with pricing
3. Select a seva → amount updates in summary
4. Fill donor form → submit → Razorpay modal opens
5. Payment success → redirects to `/donate/payment-success` with receipt

### Donation Admin Reports
1. `/admin/donations` — transaction log with date/status/category filters
2. `/admin/report-dashboard` — 8 Chart.js charts load without errors
3. `/admin/report-category` — category-wise aggregation with drill-down
4. `/admin/report-activity` — activity-wise breakdown with search
5. `/admin/report-seva` — 3-level Category → Activity → Seva hierarchy
6. `/admin/export-donations` — CSV download with correct headers

### Seva Catalogue CRUD
1. `/admin/seva-catalogue` — lists all 363+ sevas grouped by category
2. Add/edit/archive a seva — form validation, duplicate name detection
3. Link sevas to causes — pivot table management

## Known Gotchas

- **`<base>` tag**: The admin header sets `<base href="<?php echo BASE_URL; ?>">`. Relative URLs in tests (e.g. form actions) resolve against `BASE_URL`, not the current page URL.
- **CSS class selectors**: Some tests rely on CSS class names like `.cause-card`, `.offering-card`. These break if styling changes. Prefer stable data attributes or text content when writing new tests.
- **Amount assertions**: Tests hardcode ₹1,008 (puja) and ₹501 (yagya) as default prices. Update these if default pricing changes.
- **CSRF tokens**: Admin POST endpoints require CSRF tokens. Tests that submit forms must either extract the token from the page or use GET-based actions.
- **AJAX endpoints**: `admin/ajax/master-sevas-by-category.php` returns JSON. Tests should verify JSON structure, not HTML content.

## Production-Only Tests

`tests/payment-flow.spec.js` uses a hardcoded production URL (`https://iskconseshadripuram.org`). It does NOT run against the local dev server. Run it separately:

```bash
npx playwright test tests/payment-flow.spec.js
```

These tests verify the full Razorpay payment loop — useful before deploying payment-related changes.
