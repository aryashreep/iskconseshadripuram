# WORKFLOWS.md — Business Workflows & Operational Flows

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `docs/DONATIONS.md`, `modules/Donation/README.md`, `modules/Sudamaseva/README.md`

---

## Table of Contents

1. [Donation Flow (Standard One-Time)](#1-donation-flow-standard-one-time)
2. [Sudamaseva Recurring/Manual Donation Flow](#2-sudamaseva-recurringmanual-donation-flow)
3. [Puja & Yagya Booking Flow](#3-puja--yagya-booking-flow)
4. [Panihati Yatra Registration Flow](#4-panihati-yatra-registration-flow)
5. [Admin Login & Authorization Flow](#5-admin-login--authorization-flow)
6. [Blog Content Publishing Flow](#6-blog-content-publishing-flow)
7. [Festival Content Publishing Flow](#7-festival-content-publishing-flow)
8. [CSV Export & Reporting Flow](#8-csv-export--reporting-flow)
9. [Database Migration Flow](#9-database-migration-flow)
10. [Deployment Flow](#10-deployment-flow)

---

## 1. Donation Flow (Standard One-Time)

### Flow Diagram

```
Donor visits /donate/{cause-slug}
    │
    ▼
Select a seva offering (with amount)
    │
    ▼
Fill donor details (name, email, phone)
    │
    ▼
POST /api/create-order.php
    → Server verifies amount against catalog
    → Creates Razorpay order
    → Returns order_id
    │
    ▼
Razorpay checkout modal opens
    │
    ▼
Donor completes payment (UPI / Card / Net Banking)
    │
    ▼
On success → POST /api/verify-payment.php
    → Server verifies HMAC-SHA256 signature
    → Updates payment_status = 'paid'
    → Redirects to /donate/payment-success
    │
    ▼
Razorpay webhook → POST /api/webhook.php (secondary verification)
```

### Key Rules

| Rule | Details |
|------|---------|
| **Amount verification** | Always server-side against `donation_cause_master_sevas` or `donation_cause_sevas` catalog |
| **Donor data** | Name and phone are required. Email is optional but recommended for receipt. |
| **Payment statuses** | `created` → `attempted` → `paid` / `failed` → (optional) `refunded` |
| **Dual-read strategy** | `getCauseSevas()` checks master catalog first, falls back to legacy table (see `modules/Donation/DECISIONS.md`) |
| **Idempotency** | Webhook checks payment_id before creating records |
| **Transaction logging** | Every state change logged in `donation_transactions` table |

### Supported Form Types

| Type | Behavior | Example |
|------|----------|---------|
| `tiers` | Predefined amounts with labels | ₹501, ₹1,001, ₹5,001 |
| `quantity` | Amount × quantity (for bricks, sq ft) | Brick Donation: ₹1,001 × 5 |
| `multi_item` | Multiple items with checkboxes | Flower, Prasadam, etc. |
| `cart` | Add to cart (via localStorage) | Multiple causes |
| `cart_qty` | Cart + quantity selection | Combined |

---

## 2. Sudamaseva Recurring/Manual Donation Flow

### Two Payment Modes

| Mode | Payment Method | Razorpay Object | HMAC Format | Collection |
|------|---------------|-----------------|-------------|------------|
| **Auto Monthly** | Recurring auto-debit (eMandate/eNACH/UPI Autopay) | Subscription | `{sub_id}\|{pay_id}` | Razorpay charges monthly |
| **Pay Monthly** | Manual checkout each month | Order | `{order_id}\|{pay_id}` | Donor clicks "Pay Now" each month |

### Auto Monthly Flow

```
1. Donor visits /sudamaseva → selects "Auto Monthly" mode
2. Fills form: name, phone, email, amount, installments (optional)
3. POST /api/sudamaseva/create-subscription
4. Server:
   a. Finds or creates donor by phone (deduplication)
   b. Creates/reuses Razorpay Plan (monthly, fixed amount)
   c. Creates Razorpay Subscription linked to plan
   d. Saves subscription (collection_mode='recurring')
   e. Returns subscription_id + short_url
5. Razorpay checkout modal opens
6. Donor completes first installment payment
7. POST /api/sudamaseva/verify-payment (HMAC: {sub_id}|{pay_id})
8. Server creates payment + receipt records
9. Subsequent installments auto-charged by Razorpay
10. Webhook → POST /api/sudamaseva/webhook handles subscription.charged events
```

### Pay Monthly Flow

```
1. Donor visits /sudamaseva → selects "Pay Monthly" mode
2. Fills form: name, phone, email, amount
3. POST /api/sudamaseva/enroll
4. Server:
   a. Finds or creates donor by phone
   b. Creates subscription (collection_mode='manual')
   c. Creates Razorpay Order for first installment
   d. Returns order_id + donor_id + subscription_id
5. Razorpay checkout modal opens
6. Donor completes payment
7. POST /api/sudamaseva/verify-order (HMAC: {order_id}|{pay_id})
8. Server creates payment + receipt records
9. Donor redirected to dashboard with paid installment marked
10. Next month: donor clicks "Pay Now" on dashboard
11. POST /api/sudamaseva/create-order for next installment
12. Repeat steps 5-9 each month
```

### Donor Lookup Flow

```
1. Returning donor visits /sudamaseva/lookup
2. Enters phone number (or legacy ID number)
3. POST /api/sudamaseva/lookup
4. Server searches by phone → fallback to legacy_id_no
5. If found → redirect to /sudamaseva/dashboard?donor_id=X
6. If not found → show "register" CTA
```

### Dashboard Features

| Feature | Description |
|---------|-------------|
| Subscription card | Amount, mode badge, installments progress (e.g., "3 of 12 paid") |
| Installment grid | 12-month schedule with paid/upcoming/late status |
| Pay Now button | On unpaid installments (manual mode only) |
| Payment history | Table of all payments with dates and amounts |
| Total contribution | Sum of all payments made |

### 80G Receipt Logic

- Generated when: payment is successful AND donor has PAN AND amount ≥ ₹200
- Receipt format: `SMS/YYYY/NNNNN` (e.g., `SMS/2026/00001`)
- Receipt table: `sudamaseva_receipts` with full JSON data, is_80g_eligible flag
- Note: As of 2025-26, Form 10BE (via Income Tax portal) is required for official 80G compliance

---

## 3. Puja & Yagya Booking Flow

### Flow Diagram

```
1. Visitor browses /booking/puja or /booking/yagya
2. Views listing of available pujas/yagyas with cards
3. Clicks on a specific puja/yagya → detail page
4. Detail page shows:
   - Deity name and description
   - Offering tiers with prices
   - Inclusions, divine returns, delivery info
5. Donor selects an offering tier
6. Fills booking form (name, date, contact info)
7. POST /api/create-booking-order.php
8. Creates Razorpay order
9. Donor completes payment via Razorpay checkout
10. POST /api/verify-payment.php
11. Booking confirmed → status updated in `booking_pujas` table
```

### Puja Status Lifecycle

```
pending → confirmed → completed → cancelled
              ↓
           paid (payment link to donation_transactions)
```

### Key Rules

| Rule | Details |
|------|---------|
| **Date validation** | Booking date must be in the future |
| **Payment linkage** | `booking_pujas.transaction_id` → `donation_transactions.id` |
| **Pricing** | Server-side, loaded from database or catalog |
| **Cancellation** | Status change only — no refund logic implemented |

---

## 4. Panihati Yatra Registration Flow

### Flow Diagram

```
1. Visitor visits /yatra/panihati
2. Registration form loads with:
   - Travel mode toggle (Bus / Own Vehicle)
   - Bhakti Sadan dropdown (20+ options)
   - Pickup Location dropdown (40+ options)
   - Adult/Kid count selectors
3. Price auto-calculates based on:
   - Travel mode (bus vs own vehicle pricing)
   - Number of adults × rate
   - Number of kids × rate (kids free or reduced)
4. Donor fills personal details (name, phone, email)
5. POST /api/create-panihati-order.php
6. Creates Razorpay order with calculated amount
7. Donor completes payment
8. Registration recorded in `panihati_yatra_registrations`
```

### Pricing Calculation Rules

| Scenario | Calculation |
|----------|-------------|
| Bus mode, 1 adult | 1 × bus_adult_rate |
| Bus mode, 2 adults + 1 kid | (2 × bus_adult_rate) + (1 × bus_kid_rate) |
| Own vehicle, 1 adult | 1 × vehicle_adult_rate |
| Own vehicle, 1 adult + 2 kids | (1 × vehicle_adult_rate) + (2 × vehicle_kid_rate) |

Kids are typically free or at a reduced rate depending on the year's pricing.

### Admin Capabilities

| Page | Function |
|------|----------|
| Dashboard | KPIs, year-over-year comparison, rankings |
| Registration Records | View/search all registrations |
| Download Reports | CSV export of registration data |
| Add Offline Entry | Manual entry for cash/offline payments |
| Bhakti Sadans | Manage sadhan (accommodation) options |
| Pickup Locations | Manage pickup point options |
| Pricing | Set current year's pricing |
| Finance & Expenses | Track yatra expenses |
| Bulk Summary | Aggregate offline entries |

---

## 5. Admin Login & Authorization Flow

### Login Flow

```
1. User visits /admin/login
2. login.php checks rate limit (5 attempts / 15-min window per IP)
3. User submits username + password
4. Server:
   a. Looks up admin by username or email in `admins` table
   b. Verifies password with password_verify()
   c. On success:
      - Regenerates session ID
      - Stores admin identity in $_SESSION
      - Loads RBAC permissions into $_SESSION['admin_permissions']
      - Clears failed login attempts for this IP
      - Redirects to /admin/dashboard
   d. On failure:
      - Logs attempt in login_attempts table
      - Shows generic error ("Invalid credentials")
5. Every subsequent admin page includes admin/auth-check.php
6. auth-check.php validates session + loads permissions
7. Page enforces specific permission via requirePermission()
```

### Permission Checking

| Check Type | Method | When to Use |
|-----------|--------|-------------|
| Boolean check | `hasPermission('module.action')` | Hide/show UI elements |
| Permission gate | `requirePermission('module.action')` | Block page access (403) |
| Any permission | `hasAnyPermission(['a', 'b'])` | Multiple allowed permissions |
| Any gate | `requireAnyPermission(['a', 'b'])` | Multiple required permissions |
| Legacy role check | `hasRole(['super_admin'])` | Only for backward compatibility |

### Permission Cache

Permissions are loaded into `$_SESSION['admin_permissions']` on page load (via `auth-check.php`). This provides fast in-memory checks without DB queries on every permission check. The session is refreshed when:
- Admin logs in
- Roles/permissions are modified (page reload)

### Super Admin Bypass

Super Admin implicitly has all permissions — no explicit permission assignment needed. The check logic:

```php
if (in_array('super_admin', $userRoles)) {
    return true; // Always allowed
}
```

---

## 6. Blog Content Publishing Flow

### Flow

```
1. Admin (with blogs.view permission) visits /admin/blogs
2. Sees list of all blog posts with publish status
3. Creates new post via /admin/blog-edit
   - Title, slug, description, content_body
   - Banner image (upload)
   - Tags, published_date, icon
   - SEO fields: meta_title, meta_description
4. Saves as draft (is_published = 0) or publishes (is_published = 1)
5. Published posts appear on /blogs listing page
6. Individual post at /blogs/{slug} → blogs/detail.php
7. Admin can toggle publish status, edit, or delete posts
```

### Rules

| Rule | Details |
|------|---------|
| **Slug uniqueness** | Must be unique — checked on save |
| **Banner images** | Uploaded via file input, path stored in DB |
| **Tags** | Comma-separated string, displayed as badges |
| **SEO fields** | `meta_title` and `meta_description` for search engines |
| **Publishing** | Posts with `is_published = 0` are hidden from public |

---

## 7. Festival Content Publishing Flow

### Flow

```
1. Festival pages are a hybrid of:
   - Static PHP files (for unique editorial content)
   - Database-driven pages (via festivals/detail.php?slug=X)
2. URL routing (in .htaccess):
   a. Check if a hardcoded .php file exists at the URL path
   b. If not, route to festivals/detail.php with slug parameter
3. Categories: grand-festivals, ekadashi, appearance, disappearance, events
4. Each category has its own routing section in .htaccess
5. Festivals are managed via admin/festivals (CRUD)
6. Each festival can have:
   - Featured status (homepage spotlight)
   - Active/inactive toggle
   - Associated donation causes
```

### Festival Categories (8 values in `donation_causes.category`)

| Value | Label | Icon | Example |
|-------|-------|------|---------|
| `festival` | Grand Festivals | fa-star | Rath Yatra, Janmashtami |
| `ekadashi` | Ekadashi | fa-moon | Putrada Ekadashi |
| `appearance` | Appearance Days | fa-sun | Sri Advaita Acharya Appearance |
| `disappearance` | Disappearance Days | fa-candle | Srila Prabhupada Disappearance |
| `event` | Events & Programs | fa-calendar-check | Caturmasya |
| `service` | Seva & Services | fa-hands-helping | Food for Life |
| `construction` | Temple Construction | fa-building | Nila Chakra Installation |
| `general` | General Donations | fa-heart | General Support |

---

## 8. CSV Export & Reporting Flow

### Available Reports

| Page | Grouping | Permissions Required |
|------|----------|---------------------|
| `/admin/report-dashboard` | All levels + 8 charts | `reports.view` |
| `/admin/report-category` | Category only | `reports.view` |
| `/admin/report-activity` | Category → Activity | `reports.view` |
| `/admin/report-seva` | Category → Activity → Seva | `reports.view` |

### Export Flow

```
1. Admin navigates to report page
2. Applies filters (date range, category, etc.)
3. Sees Chart.js visualizations and summary cards
4. Clicks "Export CSV"
5. PHP generates CSV with:
   - UTF-8 BOM for Excel compatibility
   - Headers from query result columns
   - Data rows from aggregated query
   - php://output stream
6. File downloaded by browser
```

### Three-Level Reporting Hierarchy

```
Category (donation_causes.category)
    → Activity (donation_causes.title)
        → Seva (master_sevas.name)
```

Reports join `donation_transactions` through `donation_causes` and `master_sevas` via the pivot table.

### API Export Endpoints

| Endpoint | File |
|----------|------|
| `/admin/export-donations` | `admin/export-donations.php` |
| `/admin/export-report-activity` | `admin/export-report-activity.php` |
| `/admin/export-report-category` | `admin/export-report-category.php` |
| `/admin/export-report-seva` | `admin/export-report-seva.php` |

---

## 9. Database Migration Flow

### Process

```
1. Create migration file: database/migrations/NNN_description.php
2. Follow idempotent pattern (check before insert)
3. Test locally: php database/migrations/NNN_description.php
4. Verify data integrity
5. Commit migration file
6. Run on production during deployment
```

### Migration Pattern Template

```php
<?php
require_once __DIR__ . '/../../config.php';

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Migration: Description\n";

// Check existence
$check = $db->prepare("SELECT COUNT(*) FROM target WHERE slug = ?");
$check->execute([$slug]);

if ($check->fetchColumn() == 0) {
    $db->beginTransaction();
    try {
        $db->prepare("INSERT INTO ...")->execute([...]);
        $db->commit();
        echo "Inserted successfully.\n";
    } catch (Exception $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Already exists, skipping.\n";
}
```

### RBAC Migration Order

```bash
php modules/RBAC/database/migrations/001_create_rbac_tables.php
php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php
php modules/RBAC/database/migrations/003_migrate_existing_admins.php
```

### Sudamaseva Migration Order

```bash
php modules/Sudamaseva/migrations/001_create_tables.php
php modules/Sudamaseva/migrations/002_migrate_data.php
php modules/Sudamaseva/migrations/003_incremental_migration.php
php modules/Sudamaseva/migrations/004_add_manual_payment_fields.php
php modules/Sudamaseva/migrations/005_backfill_legacy_ids.php
```

**Important:** `donation_transactions` and `panihati_yatra_registrations` must NEVER be truncated in production.

---

## 10. Deployment Flow

### Pre-Deployment Checklist

- [ ] Run PHPUnit tests: `vendor/bin/phpunit`
- [ ] Run E2E tests: `npx playwright test`
- [ ] Run build: `npm run build`
- [ ] Run pending migrations locally
- [ ] Verify critical flows (donation, booking, payment)
- [ ] Check .env / environment variables are configured for production
- [ ] Review security headers in .htaccess

### Deployment Steps

1. Backup database: `mysqldump -u root -p db_name > backup.sql`
2. Upload files (exclude `node_modules/`, `tests/`, `scripts/`, `.git/`)
3. Upload `assets/dist/` (built assets)
4. Run pending migrations on production
5. Verify Razorpay webhook is configured
6. Clear CDN/browser caches

### Post-Deployment Verification

- [ ] Payment flow works in live mode
- [ ] Admin login works
- [ ] RBAC permissions load correctly
- [ ] Sidebar menu items reflect user permissions
- [ ] Webhook endpoints respond correctly
- [ ] Console/error logs show no new errors
- [ ] Monitor for 24 hours
