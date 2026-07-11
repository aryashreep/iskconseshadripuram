# Sudamaseva Module — Migration Specification

> **📜 HISTORICAL DOCUMENT** — This specification guided the Sudamaseva module implementation. The module is now fully implemented and live.
> **Status:** Implemented ✅ (with additions beyond original spec — see §12)
> **Current canonical source:** [`modules/Sudamaseva/README.md`](modules/Sudamaseva/README.md)
> **Parts still authoritative:** Sections 2 (Old System Analysis), 3.2 (Database Tables), 4 (Design Decisions)
> **Parts superseded:** Section 7.1 (Landing Page — actual implementation has mode toggle); Section 8a/8b (API Contracts — actual API has additional endpoints: enroll, create-order, verify-order, lookup)

---

> **Status:** Implemented ✅
> **Target Project:** ISKCON Sri Jagannath Mandir, Seshadripuram (Bangalore)  
> **Old Application:** `C:\laragon\www\sudamaseva` (DB: `iskcosf7_sudamasava`)  
> **Created:** July 6, 2026

---

## 1. Overview

### 1.1 What is Sudamaseva?

Sudamaseva (also spelled Sudama Seva) is a devotional donation program inspired by the story of **Sudama Vipra**, Lord Krishna's childhood friend from the *Srimad Bhagavatam* (Canto 10, Chapters 80-81). Sudama, a poor brahmana, visited Krishna in Dwarka and offered him a humble handful of chipped rice (*poha*). Krishna accepted this simple offering with immense love and reciprocated by blessing Sudama with unimaginable opulence.

The program embodies the principle of **madhukari** — collecting small, regular contributions from many devotees rather than large amounts from a few. It allows devotees to make humble, recurring offerings to support the temple's activities.

### 1.2 Goal

Migrate the existing standalone Sudamaseva application into the ISKCON Seshadripuram website as a new module (`modules/Sudamaseva/`) with:
- **Full data migration** — all 302 existing users, 3,278 installment payments, active subscriptions
- **Zero disruption** — active donors continue their schedules without interruption
- **Modern subscription model** — open-ended recurring subscriptions via Razorpay
- **Admin integration** — manage donors, track payments, generate reports within the existing admin panel

---

## 2. Existing System Analysis

### 2.1 Old Application Structure

```
sudamaseva/
├── index.php              # Entry point — user lookup by ID
├── reg.php                # Registration form
├── reg-pay.php            # Registration + Razorpay payment
├── dopay.php              # Installment payment processing
├── confirmpay.php         # Payment confirmation
├── payhook.php            # Razorpay webhook
├── do-pay.php             # Alternative payment handler
├── view-donate.php        # Donor dashboard — view payment schedule (24 installments)
├── seva.pdf               # Seva information PDF
├── config/
│   ├── db.php             # DB credentials (iskcosf7_sudamasava)
│   ├── config.php         # App settings
│   └── settings.php       # Razorpay keys (LIVE: rzp_live_RO3kUEGr2STgxX)
├── includes/
│   ├── common.php         # Bootstrap — session, DB, constants
│   ├── consts.php         # Constants (paths, DB prefix, currency)
│   ├── functions.php      # Helpers (DB connection, input, formatting, email)
│   ├── payment-mail.php   # Email notification template
│   └── messages.php       # Message display functions
├── classes/
│   ├── Users.php          # User CRUD, payment tracking
│   ├── Service.php        # Service applications
│   └── MysqliDb.php       # Database abstraction layer
├── admin-ana/             # Admin panel (login, user list, CRUD, export CSV)
└── api/                   # Basic REST API (events, contact, donations, FCM)
```

### 2.2 Old Database Schema

**Database:** `iskcosf7_sudamasava` (MySQL, 7 tables)

#### `tbl_users` — 302 records
| Field | Type | Notes |
|-------|------|-------|
| `id` | int (PK, AI) | |
| `user_name` | varchar(255) | Donor's full name |
| `email` | varchar(255) | Often empty |
| `phone` | varchar(15) | Used as `id_no` identifier |
| `pan` | varchar(20) | Optional |
| `id_no` | varchar(255) | Same as phone number |
| `area` | varchar(255) | Locality/neighborhood |
| `city` | varchar(255) | |
| `state` | varchar(255) | |
| `status` | int | Default: 1 (active) |
| `date_submit` | datetime | Registration date |
| `amount` | int | Monthly commitment amount |
| `number_of_times` | varchar(20) | Always NULL (indefinite commitment) |

#### `tbl_rec_ins_pay` — 3,278 records
| Field | Type | Notes |
|-------|------|-------|
| `id` | int (PK, AI) | |
| `user_id` | int | FK to tbl_users.id |
| `amount` | int | Amount paid for this installment |
| `date_pay` | datetime | Payment date |
| `ins_no` | int | Installment number (1-25) |
| `payment_id` | varchar(255) | Razorpay payment ID (utf8mb3_unicode_ci collation) |
| `mode_pay` | int | 1=Razorpay |
| `order_id` | varchar(255) | Razorpay order ID |
| `rec_id` | varchar(255) | Receipt number |

#### `payments` — 101 records (supplemental payment log)
| Field | Type | Notes |
|-------|------|-------|
| `id` | int (PK, AI) | |
| `payment_id` | varchar(255) | Razorpay payment ID (utf8mb3_general_ci collation — collation mismatch with tbl_rec_ins_pay) |
| `amount` | int | Amount in INR (values like 5, likely paise) |
| `status` | varchar(50) | `success` |
| `created_at` | timestamp | |

**Note:** The `payments` table has a different collation than `tbl_rec_ins_pay.payment_id`, suggesting they were created independently. Contains duplicate `payment_id` values. These are likely authenticated/captured payment events logged alongside the main installment system. See §3.3 Phase 3a for migration strategy.

#### `tbl_receipt_list` — 0 records (unused/schema placeholder)
#### `admin` — Admin credentials (NOT migrated; new admin access granted via existing admin panel)
#### `api_events` — CMS-style events for old mobile app (NOT migrated)
#### `api_tokens` — FCM push notification tokens for old mobile app (NOT migrated)

### 2.3 Old App Data Profile

| Metric | Value |
|--------|-------|
| Total users | 302 |
| Users with payments | 344 (some foreign key references beyond 302) |
| Total installment payments | 3,278 |
| Payment date range | Feb 2023 – Jul 2026 (still active) |
| Amount range | ₹1 – ₹4,000 |
| Most common amounts | ₹500, ₹100, ₹1000, ₹1500 |
| Installment range | 1–25 (24-month commitment) |
| Payment mode | Razorpay only (mode_pay=1) |

### 2.4 Payment Tiers Found in Data

₹1, ₹100, ₹108, ₹111, ₹150, ₹200, ₹250, ₹300, ₹400, ₹500, ₹502, ₹1,000, ₹1,001, ₹1,008, ₹1,100, ₹1,500, ₹2,100, ₹3,000, ₹4,000

### 2.5 Old Razorpay Configuration

- **Key ID:** `rzp_live_RO3kUEGr2STgxX`
- **Key Secret:** `MzLgbEO9FCf1ie2Ey7HbSGHC`
- **Environment:** LIVE (production)
- **Integration:** Direct checkout via `checkout.razorpay.com/v1/checkout.js`

---

## 3. Target Architecture

### 3.1 Module Structure (Pattern: modules/Panihati/)

```
modules/Sudamaseva/
├── README.md
├── ARCHITECTURE.md
├── DATABASE.md
├── config.php                          # Module-specific config
├── routes.php                          # Route definitions (if needed)
│
├── src/
│   ├── SudamasevaRepository.php        # DB queries for users, subscriptions, payments
│   ├── SudamasevaService.php           # Business logic (tiers, subscriptions, migration)
│   └── SudamasevaRenderer.php          # HTML rendering helpers (consistent with DonationRenderer)
│
├── content/                            # Public-facing pages
│   ├── index.php                       # Sudamaseva landing page
│   ├── register.php                    # New donor registration + payment
│   ├── dashboard.php                   # Donor dashboard (view subscriptions, payment history)
│   ├── payment-success.php             # Post-payment success page
│   └── payment-failed.php              # Post-payment failure page
│
├── api/                                # API endpoints
│   ├── create-subscription.php         # Create Razorpay subscription
│   ├── verify-payment.php              # Verify Razorpay payment signature
│   └── webhook.php                     # Razorpay subscription webhook
│
├── Admin/                              # Admin panel pages
│   ├── index.php                       # Sudamaseva dashboard (stats, active donors)
│   ├── donors.php                      # Donor listing with search/filter/export
│   ├── donor-detail.php                # Single donor view (payment history, subscriptions)
│   ├── payments.php                    # All payments log
│   ├── subscriptions.php               # Subscription management
│   ├── reports.php                     # Reports (collection summary, trends)
│   └── export.php                      # CSV/Excel export
│
├── assets/
│   ├── css/sudamaseva.css              # Module-specific styles
│   └── js/sudamaseva.js               # Module-specific JS
│
├── migrations/
│   ├── 001_create_tables.php           # New module tables
│   └── 002_migrate_data.php            # Data migration from old DB
│
└── tests/
    ├── subscription-flow.spec.js       # E2E tests
    └── admin-donors.spec.js            # Admin tests
```

### 3.2 Database Tables (New — in isjm_donations)

> **Design Decision: Separate tables vs. reuse**
> The existing project already has `donation_subscriptions` and `donation_plans` tables used by the Donation module. Sudamaseva uses its own separate tables (`sudamaseva_*`) because:
> 1. Sudamaseva tracks legacy installment data (installment_number, total_installments, is_migrated, old_user_id, old_ins_pay_id) that doesn't fit the generic Donation schema
> 2. The Razorpay subscription creation logic is **shared** — the cURL-based API calls in `modules/Donation/api/create-subscription.php` serve as the reference implementation for Sudamaseva's equivalent endpoints
> 3. Separation avoids schema drift in the existing `donation_subscriptions` table which has hardcoded `cause_id` FK constraints
> 4. Future devotee care integration will use `sudamaseva_donors.uuid` as the cross-reference key

#### `sudamaseva_donors`
Stores donor information (migrated from `tbl_users`).

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `uuid` | varchar(36) | UUID for future devotee care cross-reference |
| `donor_name` | varchar(255) | |
| `phone` | varchar(15) | Unique identifier, indexed |
| `email` | varchar(255) | Nullable |
| `pan` | varchar(20) | Nullable |
| `area` | varchar(255) | |
| `city` | varchar(255) | |
| `state` | varchar(255) | |
| `source` | varchar(50) | Default: `sudamaseva` (for future: `devotee_care`) |
| `notes` | text | Admin notes |
| `status` | enum('active','inactive','paused') | Default: 'active' |
| `created_at` | datetime | |
| `updated_at` | datetime | |

Indexes: `phone` (UNIQUE), `email`, `status`, `source`

#### `sudamaseva_subscriptions`
Tracks active and historical subscriptions for donors.

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `donor_id` | int | FK → sudamaseva_donors.id |
| `amount` | int | Monthly amount in INR (min ₹50) |
| `razorpay_subscription_id` | varchar(255) | Razorpay subscription ID |
| `razorpay_plan_id` | varchar(255) | Razorpay plan ID |
| `status` | enum('active','completed','paused','cancelled') | |
| `start_date` | datetime | Subscription start |
| `end_date` | datetime | Nullable |
| `total_installments` | int | From old system (e.g., 24; 0 = open-ended) |
| `installments_paid` | int | Count of paid installments from old system |
| `source` | enum('migrated','new') | Whether from old app or fresh |
| `old_user_id` | int | Reference to old tbl_users.id (migrated only) |
| `created_at` | datetime | |
| `updated_at` | datetime | |

Indexes: `donor_id`, `status`, `razorpay_subscription_id` (UNIQUE)

#### `sudamaseva_payments`
Stores all payment records (one per installment).

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `subscription_id` | int | FK → sudamaseva_subscriptions.id (nullable for orphan payments) |
| `donor_id` | int | FK → sudamaseva_donors.id, nullable for orphan payments |
| `amount` | int | Amount paid |
| `installment_number` | int | Installment sequence number (0 = orphan/unlinked) |
| `razorpay_payment_id` | varchar(255) | |
| `razorpay_order_id` | varchar(255) | |
| `razorpay_signature` | varchar(255) | |
| `payment_status` | enum('created','attempted','paid','failed') | Default: 'created' |
| `payment_date` | datetime | |
| `receipt_number` | varchar(50) | Auto-generated receipt no |
| `notes` | text | |
| `is_migrated` | tinyint(1) | Whether imported from old system |
| `old_ins_pay_id` | int | Reference to old tbl_rec_ins_pay.id |
| `created_at` | datetime | |

Indexes: `subscription_id`, `donor_id`, `razorpay_payment_id` (UNIQUE), `installment_number`, `payment_date`

#### `sudamaseva_receipts`
Generated receipts for each successful payment.

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `payment_id` | int | FK → sudamaseva_payments.id |
| `receipt_no` | varchar(50) | Formatted receipt number (e.g., SMS/2026/0001) |
| `receipt_date` | datetime | |
| `receipt_data` | json | Full receipt details (name, amount, date, etc.) |
| `is_80g_eligible` | tinyint(1) | Whether qualifies for 80G tax exemption |
| `created_at` | datetime | |

### 3.3 Migration Strategy

#### Phase 1: Seed New Tables
- Create `sudamaseva_donors` from `tbl_users` rows
- Map `user_name` → `donor_name`, `phone` → `phone` (set as UNIQUE)
- Generate UUID for each donor via MySQL `UUID()` or PHP `ramsey/uuid`
- Set `source = 'migrated'`
- **Duplicate phone handling:** If multiple users share the same phone, keep the record with the most recent `date_submit` and merge installment history from the duplicate

#### Phase 2: Migrate Subscriptions
- For each user with payments in `tbl_rec_ins_pay`:
  - Determine max `ins_no` (installment count) per user → `total_installments`
  - Set `installments_paid = COUNT(paid installments)`
  - `number_of_times` was NULL for all records → treat as indefinite/open-ended commitment
  - If max(ins_no) > 0, set `total_installments = max(ins_no)` (structured plan)
  - If no payments exist, set `total_installments = 0` (open-ended)
  - Set `status = 'active'` if most recent payment is within 60 days, else `'completed'`
- Do NOT create Razorpay subscription objects for migrated records (legacy tracking continues manually)

#### Phase 3: Migrate Installment Payment History (from tbl_rec_ins_pay, ~3,278 records)
- Insert each `tbl_rec_ins_pay` record into `sudamaseva_payments`
- Set `is_migrated = 1`
- Preserve `old_ins_pay_id` for cross-reference
- Auto-generate receipt numbers in format: `SMS/YYYY/NNNNN` (e.g., SMS/2026/00001)

#### Phase 3a: Migrate Supplemental Payments (from `payments` table, ~101 records)
- The `payments` table contains Razorpay payment logs that may overlap with `tbl_rec_ins_pay`
- **Collation issue:** `payments.payment_id` uses utf8mb3_general_ci while `tbl_rec_ins_pay.payment_id` uses utf8mb3_unicode_ci — cast when joining:
  ```sql
  SELECT p.* FROM payments p 
  LEFT JOIN tbl_rec_ins_pay r ON p.payment_id = r.payment_id COLLATE utf8mb3_unicode_ci
  WHERE r.id IS NULL
  ```
- Unmatched records → insert as orphan payments:
  - `subscription_id = NULL`, `donor_id = NULL`, `installment_number = 0`
  - `notes = 'Orphan — from payments table, no associated user/installment'`
- These serve as an audit trail but are not linked to any donor
- Matched records are skipped (already covered by Phase 3)

#### Phase 4: Remaining Installment Tracking
- For each migrated subscription:
  - `remaining_installments = total_installments - installments_paid` (if 0 → open-ended)
  - The next installment to be paid is `installments_paid + 1` in the new system
  - Continue the installment sequence from where the old system left off
- For new subscriptions, installment tracking is handled by Razorpay's billing engine

#### Phase 5: New Donor Registration Flow
- New donors register directly in the new system
- Razorpay subscription is created via API (reuses cURL-based API patterns from existing `modules/Donation/api/create-subscription.php`)
- Subscription invoice triggered monthly via Razorpay auto-debit
- See §3.4 for full flow

### 3.4 Subscription Flow (New Donors)

1. **Registration:** Donor fills form (name, phone, email, PAN, amount)
2. **Plan Creation:** Server creates/retrieves Razorpay plan for the amount
   - Reuses the same cURL-based Razorpay API approach as `modules/Donation/api/create-subscription.php`
3. **Subscription Creation:** Creates Razorpay subscription linked to the plan
4. **Checkout:** Opens Razorpay checkout modal
5. **First Payment:** Donor completes first payment → subscription activated
6. **Recurring Charges:** Razorpay charges the donor monthly
7. **Webhook:** `modules/Sudamaseva/api/webhook.php` processes `subscription.charged` events → records payment
8. **Email Receipt:** System sends email receipt for each successful charge

### 3.5 Migrated Donor Flow

- Existing donors continue without re-registering
- Old installment schedule preserved as read-only history
- New payments tracked as continuation of their installment sequence
- Donors can view their payment history in the dashboard
- Admin can manually record offline/migrated payments

---

## 4. Design Decisions (Approved)

| Decision | Choice | Rationale |
|----------|--------|-----------|
| **Module pattern** | New `modules/Sudamaseva/` | Follows existing patterns (Panihati, Donation) |
| **Payment model** | Open-ended subscription | Modern, scalable; builds on existing Razorpay subscription support |
| **Migration strategy** | Full with continuity | 302 users, 3,278 payments preserved; active donors unaffected |
| **Remaining installments** | Tracked and continued | Installment sequence continues seamlessly |
| **User identifier** | Phone (UNIQUE) | Used as id_no in old system; practical for SMS/communication |
| **Minimum amount** | ₹50 for new subscriptions | Grandfather existing lower amounts |
| **Additional fields** | +UUID, source, admin notes | Prepares for future devotee care integration |
| **Admin integration** | Integrated + `treasurer` role | Follows existing donation report patterns; no new role needed |
| **Notifications** | Email receipts only | Matches current system capabilities |
| **Old DB** | Kept as read-only archive | Allows rollback and cross-reference |
| **Razorpay keys** | Continue using existing live keys | `rzp_live_RO3kUEGr2STgxX` (no disruption) |
| **Old mobile app data** | NOT migrated | `api_tokens` (FCM), `api_events`, `admin` tables stay in old DB |
| **Subscription tables** | Separate `sudamaseva_*` tables | Needed for legacy installment tracking fields not in generic `donation_subscriptions` |
| **Razorpay API approach** | cURL (same as Donation module) | Reuses patterns from `modules/Donation/api/create-subscription.php` — no PHP SDK dependency |

---

## 5. Database Migrations

### 5.1 New Module Migrations (in `modules/Sudamaseva/migrations/`)

#### `001_create_tables.php`
- Creates the 4 new tables (donors, subscriptions, payments, receipts)
- Sets up indexes and foreign keys
- Inserts initial config if needed

#### `002_migrate_data.php`
- Connects to old DB (`iskcosf7_sudamasava`) via config
- Migrates `tbl_users` → `sudamaseva_donors`
- Migrates installment data → `sudamaseva_subscriptions` + `sudamaseva_payments`
- Generates UUIDs and receipt numbers
- Validates data integrity after migration
- Reports summary (total donors, payments migrated, etc.)

### 5.2 Migration Validation (Post-Migration)

| Check | Expected |
|-------|----------|
| Donor count | ≥ 302 |
| Payment records | ≥ 3,278 |
| Active subscriptions | Users with payments in last 60 days |
| Amount totals match | Σ old payments = Σ new payments |
| No orphan data | Every payment has a donor |
| Phone uniqueness | No duplicate phone numbers |

---

## 6. Admin Panel

### 6.1 Role: `treasurer`
- Sudamaseva admin pages use the existing `treasurer` role (consistent with donation/financial pages)
- `requireRole(['super_admin', 'treasurer'])` on all Sudamaseva admin pages
- No new role needed — keeps the RBAC system clean and follows existing patterns (donation reports, transactions, exports all use `treasurer`)
- Assigned by `super_admin` via existing `admin/admins.php`

### 6.2 Admin Pages

#### Dashboard (`admin/sudamaseva/index.php`)
- Total donors (active/inactive)
- Monthly collection (current month, this year)
- Active subscriptions count
- Recent payments (last 10)
- Collection trend chart (monthly)
- Due payments this month

#### Donors (`admin/sudamaseva/donors.php`)
- Searchable table (name, phone, amount, status)
- Filter by status, source, date range
- Quick actions: view detail, toggle status, add payment note
- CSV export
- Pagination (50 per page)

#### Donor Detail (`admin/sudamaseva/donor-detail.php`)
- Full donor profile (old + new fields)
- Subscription history
- Payment timeline with installment numbers
- Add manual payment (offline donations)
- Generate receipt
- Administrative notes

#### Payments (`admin/sudamaseva/payments.php`)
- All payments log (migrated + new)
- Filters: date range, donor, status, amount range
- CSV export
- Receipt re-generation

#### Subscriptions (`admin/sudamaseva/subscriptions.php`)
- Active subscriptions list
- Cancel/pause/resume subscription
- View Razorpay subscription details

#### Reports (`admin/sudamaseva/reports.php`)
- Monthly collection summary
- Donor growth over time
- Amount distribution (how many ₹100 donors, ₹500, etc.)
- City/state distribution
- Outstanding/remaining installments (for migrated donors)

---

## 7. Public Pages

### 7.1 Landing Page (`modules/Sudamaseva/content/index.php`)
- Story of Sudama and Krishna (illustrated narrative)
- Philosophy of humble offering (madhukari principle)
- Benefits of participating
- Testimonial quotes from existing donors
- CTA: "Register Now" button
- Existing donor lookup (by phone number)

### 7.2 Registration Page (`modules/Sudamaseva/content/register.php`)
- Two-column layout (info left, form right)
- Form fields: Name, Phone, Email, PAN (optional), Area, City, State
- Amount selection: predefined tiers (₹51, ₹101, ₹501, ₹1,001, ₹5,001) or custom amount (min ₹50)
- Payment mode: Monthly subscription via Razorpay
- Terms and privacy notice
- Submit → opens Razorpay checkout

### 7.3 Donor Dashboard (`modules/Sudamaseva/content/dashboard.php`)
- Accessed via lookup by phone number (+OTP or simple link)
- Shows current subscription status
- Payment history timeline
- Upcoming installment schedule
- Download receipts
- Update contact details
- Cancel/pause subscription

### 7.4 Wrappers (Backward Compatible)

Root-level wrapper files following the project's convention (see `seva.php`, `contact.php`, `darshan.php`):

```
sudamaseva.php                    → require __DIR__ . '/modules/Sudamaseva/content/index.php'
sudamaseva-register.php           → require __DIR__ . '/modules/Sudamaseva/content/register.php'
sudamaseva-dashboard.php          → require __DIR__ . '/modules/Sudamaseva/content/dashboard.php'
sudamaseva-payment-success.php    → require __DIR__ . '/modules/Sudamaseva/content/payment-success.php'
sudamaseva-payment-failed.php     → require __DIR__ . '/modules/Sudamaseva/content/payment-failed.php'
```

Each wrapper follows the pattern:
```php
<?php
require_once __DIR__ . '/modules/Sudamaseva/content/' . basename(__FILE__);
```

**Why root-level, not `yatra/`:** Sudamaseva is a donation program, not a pilgrimage/yatra. The `yatra/` directory is reserved for pilgrimage packages. Root-level wrappers are consistent with other top-level pages like `seva.php`, `contact.php`, `forums.php`.

---

## 8. URL Structure

```
/sudamaseva                    → sudamaseva.php → modules/Sudamaseva/content/index.php
/sudamaseva/register           → sudamaseva-register.php → modules/Sudamaseva/content/register.php
/sudamaseva/dashboard          → sudamaseva-dashboard.php → modules/Sudamaseva/content/dashboard.php
/sudamaseva/payment-success    → sudamaseva-payment-success.php → modules/Sudamaseva/content/payment-success.php
/sudamaseva/payment-failed     → sudamaseva-payment-failed.php → modules/Sudamaseva/content/payment-failed.php

Admin:
/admin/sudamaseva              → admin/sudamaseva/index.php → modules/Sudamaseva/Admin/index.php
/admin/sudamaseva/donors       → admin/sudamaseva/donors.php → modules/Sudamaseva/Admin/donors.php
/admin/sudamaseva/donor/123    → admin/sudamaseva/donor-detail.php?id=123 → modules/Sudamaseva/Admin/donor-detail.php
/admin/sudamaseva/payments     → admin/sudamaseva/payments.php → modules/Sudamaseva/Admin/payments.php
/admin/sudamaseva/subscriptions → admin/sudamaseva/subscriptions.php → modules/Sudamaseva/Admin/subscriptions.php
/admin/sudamaseva/reports      → admin/sudamaseva/reports.php → modules/Sudamaseva/Admin/reports.php
```

### Admin Wrapper Convention
Following the Panihati admin pattern (`admin/panihati-yatra.php` → `modules/Panihati/Admin/panihati-yatra.php`):
```php
// admin/sudamaseva/index.php
<?php
require_once __DIR__ . '/../../modules/Sudamaseva/Admin/index.php';
```

---

## 8a. API Contracts

### POST /api/sudamaseva/create-subscription.php
Creates a Razorpay subscription for a new donor.

**Request** (POST JSON):
```json
{
  "amount": 50000,
  "donor_name": "Radha Krishna Das",
  "donor_phone": "9876543210",
  "donor_email": "rk.das@example.com",
  "pan_number": "ABCDE1234F",
  "area": "Malleswaram",
  "city": "Bangalore",
  "state": "Karnataka"
}
```

**Response** (200):
```json
{
  "subscription_id": "sub_xxxxx",
  "plan_id": "plan_xxxxx",
  "amount": 50000,
  "currency": "INR",
  "donor_id": 301
}
```

**Error** (400/500):
```json
{
  "error": "Failed to create subscription",
  "details": "Minimum amount is ₹5,000 (50000 paise)"
}
```

**Validation rules:**
- `amount` ≥ 5000 paise (₹50 minimum)
- `donor_name` required, max 255 chars
- `donor_phone` required, must be 10-15 digits
- `pan_number` optional, must be 10-char PAN format if provided

---

### POST /api/sudamaseva/verify-payment.php
Verifies a Razorpay payment signature and records the first subscription payment.

**Request** (POST JSON):
```json
{
  "razorpay_order_id": "order_xxxxx",
  "razorpay_payment_id": "pay_xxxxx",
  "razorpay_signature": "xxxxx",
  "subscription_id": "sub_xxxxx",
  "donor_id": 301
}
```

**Response** (200):
```json
{
  "status": "success",
  "payment_id": "pay_xxxxx",
  "installment_number": 1
}
```

**Security:** HMAC-SHA256 signature verification using `RAZORPAY_KEY_SECRET`.

---

### POST /api/sudamaseva/webhook.php
Handles Razorpay subscription webhook events.

**Events handled:**
| Event | Action |
|-------|--------|
| `subscription.activated` | Update subscription status → 'active' |
| `subscription.charged` | Create new payment record, increment installments_paid |
| `subscription.cancelled` | Update subscription status → 'cancelled' |
| `subscription.paused` / `.resumed` | Update subscription status accordingly |
| `payment.failed` | Log failed payment attempt, flag subscription for review |

**Request:** Razorpay standard webhook payload (JSON) with `X-Razorpay-Signature` header.
**Security:** HMAC-SHA256 signature validation using `RAZORPAY_KEY_SECRET`.

---

### POST /api/sudamaseva/lookup.php
Looks up an existing donor by phone number (for donor dashboard access).

**Request** (POST JSON):
```json
{
  "phone": "9876543210"
}
```

**Response** (200 — found):
```json
{
  "found": true,
  "donor_id": 301,
  "donor_name": "Radha Krishna Das",
  "redirect": "/sudamaseva/dashboard?donor_id=301"
}
```

**Response** (200 — not found):
```json
{
  "found": false,
  "message": "No donor found with this phone number. Please register."
}
```

---

## 8b. Public Page Contracts (Form → API Flow)

### Registration Page → create-subscription
```
1. Donor fills form → submits
2. Client-side validation (name, phone, amount ≥ ₹50)
3. POST /api/sudamaseva/create-subscription.php
4. If successful:
   a. Open Razorpay checkout with subscription_id
   b. On success → POST /api/sudamaseva/verify-payment.php
   c. Redirect to /sudamaseva/payment-success
5. If failed → Redirect to /sudamaseva/payment-failed
```

### Donor Dashboard → lookup
```
1. Donor enters phone number on /sudamaseva
2. POST /api/sudamaseva/lookup.php
3. If found → redirect to /sudamaseva/dashboard?donor_id=X
4. If not found → show "register" CTA
```

---

## 9. .htaccess (Proposed Additions)

```apache
# Sudamaseva
RewriteRule ^sudamaseva/?$ sudamaseva.php [L,NC]
RewriteRule ^sudamaseva/register/?$ sudamaseva-register.php [L,NC]
RewriteRule ^sudamaseva/dashboard/?$ sudamaseva-dashboard.php [L,NC]
RewriteRule ^sudamaseva/payment-success/?$ sudamaseva-payment-success.php [L,NC]
RewriteRule ^sudamaseva/payment-failed/?$ sudamaseva-payment-failed.php [L,NC]

# Admin
RewriteRule ^admin/sudamaseva/?$ admin/sudamaseva/index.php [L,NC]
RewriteRule ^admin/sudamaseva/donor/(\d+)/?$ admin/sudamaseva/donor-detail.php?id=$1 [L,QSA]
```

**CSP Note:** The existing Content-Security-Policy in `.htaccess` already allows `https://checkout.razorpay.com` and `https://api.razorpay.com` — no CSP changes needed.

---

## 10. Risk & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Live payments during migration | Loss of transaction data | Run migration during low-traffic window; keep old webhook running in parallel |
| Duplicate phone numbers in old data | Migration failure | Script to deduplicate (merge records or keep most recent) |
| Razorpay subscription differences | Payment flow broken | Test subscription flow thoroughly with razorpay test mode first |
| Old app still accessible | Confusion | After migration, redirect old app URLs to new module; keep old DB read-only |
| Donor data mismatch | Incorrect remaining installments | Validate each donor's installment count via reconciliation report |
| 80G receipt formatting | Non-compliance | Ensure receipts follow Income Tax 80G format requirements |

---

## 11. Future Considerations

- **Devotee Care Integration**: The `uuid` and `source` fields on `sudamaseva_donors` allow seamless linking when a unified devotee management system is built
- **SMS Notifications**: Infrastructure for payment reminders can be added later via Fast2SMS (already used by old app)
- **Multi-language Support**: Can be added by following the project's existing i18n patterns
- **Annual Subscription Option**: Can be layered on top of the monthly subscription model
- **Receipt Download**: PDF receipt generation can be added later
- **WhatApp Integration**: Automated receipts via WhatsApp API

---

## 12. Implementation Status

| Step | Status | Notes |
|------|--------|-------|
| 1. Specification written and approved | ✅ Complete | Initial spec approved Jul 2026 |
| 2. Create module directory structure | ✅ Complete | `modules/Sudamaseva/` with src/, content/, api/, migrations/, assets/ |
| 3. Create migration files | ✅ Complete | 001 (tables), 002 (data), 003 (fixes), 004 (manual fields), 005 (backfill) |
| 4. Create `SudamasevaRepository.php` | ✅ Complete | All DB queries for donors, subscriptions, payments, receipts |
| 5. Create `SudamasevaService.php` | ✅ Complete | Business logic, formatting, status labels, receipt generation |
| 6. Create public pages | ✅ Complete | Signup w/ mode toggle, lookup, dashboard (inc. success/failure) |
| 7. Create API endpoints | ✅ Complete | create-subscription, verify-payment, webhook, **lookup**, **enroll**, **create-order**, **verify-order** |
| 8. Create admin pages | ⏳ Pending | Dashboard, donors, payments, subscriptions, reports |
| 9. Create wrapper files | ✅ Complete | `sudamaseva/` directory with page wrappers + `.htaccess` rules |
| 10. Add .htaccess rules | ✅ Complete | Page rewrites + API rewrites for all 7 endpoints |
| 11. Run migration and validate data | ✅ Complete | 302 users, 3,278 payments migrated; legacy IDs backfilled |
| 12. Test subscription flow end-to-end | ✅ Complete | Auto Monthly + Pay Monthly flows tested via API and browser |
| 13. Deploy | ✅ Complete | Live on production `iskconseshadripuram.org` |

### What Was Added Beyond the Original Spec

| Feature | Original Spec | Actual Implementation |
|---------|---------------|----------------------|
| Payment modes | Recurring only | **Auto Monthly** (recurring) + **Pay Monthly** (manual) via mode toggle |
| Manual payment | Not planned | New `enroll.php`, `create-order.php`, `verify-order.php` APIs |
| Donor lookup | Brief mention | Full `lookup.php` page + API with phone/legacy ID search |
| Donor dashboard | Brief mention | Full `dashboard.php` with installment grid, pay now, payment history |
| Mode toggle | Not planned | UI on signup page — switches between subscription and order flows |
| `collection_mode` column | Not in spec | New ENUM on subscriptions: `'recurring'` or `'manual'` |
| `payment_source` column | Not in spec | New ENUM on payments: `'subscription_charge'`, `'manual_order'`, etc. |
| `billing_month` column | Not in spec | New DATE on payments for monthly aggregation |
| `legacy_id_no` column | Not in spec | Backfilled from `tbl_users.id_no` for cross-reference |
| Backfill script | Not in spec | `005_backfill_legacy_ids.php` — 302 records matched by phone |
| Return to dashboard | Not planned | After manual payment, donor redirected to dashboard with paid status |

### Remaining Work

- **Admin pages** (dashboard, donors, payments, subscriptions, reports) — not yet implemented
- **SMS notifications** — can be added later via Fast2SMS
- **PDF receipt download** — can be added later
