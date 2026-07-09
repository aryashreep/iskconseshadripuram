# API.md — Payment Endpoints

## Endpoints

### POST /api/create-order.php

Creates a Razorpay order for one-time donation or booking.

**Request** (JSON):
```json
{
  "amount": 100100,
  "cause_id": 5,
  "seva_id": 12,
  "donor_name": "Radha Krishna Das",
  "donor_email": "rk.das@gmail.com",
  "donor_phone": "9876543210"
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

**Validation**: Amount verified server-side against `donation_cause_master_sevas` or `donation_cause_sevas` catalog.

### POST /api/create-subscription.php

Creates a Razorpay subscription for recurring monthly donations.

**Request** (JSON):
```json
{
  "plan_id": "plan_xxxxx",
  "total_count": 12,
  "customer_notify": true,
  "donor_name": "Devotee Name",
  "donor_email": "devotee@example.com",
  "donor_phone": "9876543210",
  "cause_id": 5,
  "seva_id": 12
}
```

**Response** (JSON):
```json
{
  "subscription_id": "sub_xxxxx",
  "short_url": "https://rzp.io/..."
}
```

### POST /api/verify-payment.php

Verifies Razorpay payment signature and updates transaction status.

**Request** (JSON):
```json
{
  "razorpay_order_id": "order_xxxxx",
  "razorpay_payment_id": "pay_xxxxx",
  "razorpay_signature": "xxxxx"
}
```

**Response** (JSON):
```json
{
  "status": "success",
  "payment_id": "pay_xxxxx"
}
```

**Security**: HMAC-SHA256 signature verification using `RAZORPAY_KEY_SECRET`.

### POST /api/webhook.php

Server-to-server payment notification from Razorpay. Authoritative source of truth.

**Events handled**:
- `payment.captured` → status = 'paid'
- `payment.authorized` → status = 'paid'
- `payment.failed` → status = 'failed'

**Security**: Validates `X-Razorpay-Signature` header using HMAC-SHA256.

### POST /api/create-booking-order.php

Creates Razorpay order for puja/yagya booking.

### POST /api/create-panihati-order.php

Creates Razorpay order for Panihati Yatra registration.

## Sudamaseva Module API

Sudamaseva is the recurring/manual subscription donation system. Donors pledge a monthly amount that is donated via one of two modes:

| Mode | Description | Payment Method |
|------|-------------|----------------|
| **Auto Monthly** (recurring) | Donor authorizes automated monthly charges via eMandate/eNACH/UPI Autopay | Razorpay Subscription (auto-debit) |
| **Pay Monthly** (manual) | Donor pays each month manually via Razorpay checkout | Razorpay Order (one-time, repeated) |

### Flow Overview

**Auto Monthly (Recurring) Flow:**
```
1. Donor fills form on /sudamaseva (name, phone, email, amount, installments)
2. Selects "Auto Monthly" mode (default)
3. Frontend → POST /api/sudamaseva-create-subscription
4. Server finds or creates a donor record (keyed by phone)
5. Server creates/reuses a Razorpay Plan (monthly, fixed amount)
6. Server creates a Razorpay Subscription linked to the plan
7. Server saves subscription record (collection_mode='recurring') in sudamaseva_subscriptions table
8. Returns subscription_id + short_url to frontend
9. Razorpay checkout modal opens
10. Donor completes first installment payment
11. Frontend → POST /api/sudamaseva-verify-payment
12. Server verifies HMAC signature ({subscription_id}|{payment_id}), creates payment + receipt records
13. Subsequent installments are charged automatically (Razorpay handles billing)
14. Razorpay sends webhooks for events (subscription.charged, etc.)
15. Webhook → POST /api/sudamaseva-webhook (server-to-server, authoritative)
```

**Pay Monthly (Manual) Flow:**
```
1. Donor fills form on /sudamaseva (name, phone, email, amount, total installments)
2. Selects "Pay Monthly" mode
3. Frontend → POST /api/sudamaseva-enroll
4. Server finds or creates a donor record (keyed by phone)
5. Server creates subscription record (collection_mode='manual') with no Razorpay subscription
6. Server creates a Razorpay Order for the first installment amount
7. Returns order_id + donor_id + subscription_id to frontend
8. Razorpay checkout modal opens with order_id
9. Donor completes payment
10. Frontend → POST /api/sudamaseva-verify-order
11. Server verifies HMAC signature ({order_id}|{payment_id}), creates payment + receipt records
12. Donor is redirected to dashboard with paid installment marked
13. For subsequent months, donor clicks "Pay Now" on the dashboard
14. Frontend → POST /api/sudamaseva-create-order (creates Razorpay Order for next installment)
15. Repeat steps 8-12 each month
```

**Donor Lookup Flow:**
```
1. Returning donor visits /sudamaseva/lookup
2. Enters phone number (or legacy ID number)
3. Frontend → POST /api/sudamaseva/lookup
4. Server searches by phone or legacy_id_no
5. If found → redirect to /sudamaseva/dashboard?donor_id=X
6. If not found → show "register" CTA
```

---

---

### POST /api/sudamaseva-create-subscription

Creates a new Razorpay subscription for the Sudamaseva recurring donation program.

**URL:** `https://yourdomain.com/api/sudamaseva-create-subscription`
**Method:** `POST`
**Content-Type:** `application/json`
**Auth:** None (public API — protected by CORS origin check)

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `donor_name` | string | ✅ | Donor's full name |
| `donor_phone` | string | ✅ | Phone number (unique identifier — used to find/create donor) |
| `donor_email` | string | ❌ | Email address (recommended for notifications) |
| `amount` | int | ❌ | Amount in **paise** (default: 5100 = ₹51). Clamped to ₹51–₹1,00,000. |
| `total_installments` | int | ❌ | Number of monthly installments (default: 12, max: 120) |
| `pan_number` | string | ❌ | PAN for 80G tax receipt eligibility |
| `area` | string | ❌ | Donor's area/locality |
| `city` | string | ❌ | Donor's city |
| `state` | string | ❌ | Donor's state |

**Example request:**
```json
{
  "donor_name": "Radha Krishna Das",
  "donor_phone": "9876543210",
  "donor_email": "rk.das@example.com",
  "amount": 110000,
  "total_installments": 12,
  "pan_number": "ABCDE1234F",
  "area": "Seshadripuram",
  "city": "Bangalore",
  "state": "Karnataka"
}
```

**Success Response** (HTTP 200):
```json
{
  "success": true,
  "subscription_id": "sub_LhZ8xPydUoN4j9",
  "db_subscription_id": 42,
  "plan_id": "plan_LhZ8xPy123ABCD",
  "amount": 110000,
  "currency": "INR",
  "receipt": "sms_1712345678_123",
  "donor_id": 7,
  "short_url": "https://rzp.io/i/abc123"
}
```

**Error Response** (HTTP 400/500):
```json
{
  "error": "Donor name and phone are required"
}
```

#### Key Implementation Details

- **Donor deduplication**: Donors are looked up by phone number. If found, existing data is preserved and only empty fields are updated (name, email, PAN). If not found, a new donor is created.
- **Plan reuse**: The server checks if a Razorpay plan already exists for the exact amount. If so, it reuses it instead of creating a new one, minimizing the number of plans in the Razorpay dashboard.
- **Amount clamping**: Minimum ₹51 (5,100 paise), maximum ₹1,00,000 (10,000,000 paise).
- **Razorpay plan config**: Period = monthly, interval = 1.
- **Subscription notes**: Includes `module`, `donor_id`, `donor_name`, `donor_phone`, `donor_email` for webhook identification.
- **Database rollback**: If the subscription is created in Razorpay but the DB save fails, a best-effort cancel is attempted on the Razorpay subscription.
- **Logging**: All subscriptions are logged to `logs/sudamaseva_subscriptions.log`.

---

### POST /api/sudamaseva-verify-payment

Verifies a Sudamaseva subscription payment after the frontend Razorpay checkout completes.

**URL:** `https://yourdomain.com/api/sudamaseva-verify-payment`
**Method:** `POST`
**Content-Type:** `application/json`
**Auth:** None (public API — protected by CORS + HMAC signature verification)

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `razorpay_payment_id` | string | ✅ | Payment ID from Razorpay checkout |
| `razorpay_subscription_id` | string | ✅ | Subscription ID from create-subscription response |
| `razorpay_signature` | string | ✅ | HMAC signature from Razorpay checkout |
| `razorpay_order_id` | string | ❌ | Order ID (if applicable) |
| `amount` | int | ❌ | Amount in paise (for verification) |

**Example request:**
```json
{
  "razorpay_subscription_id": "sub_LhZ8xPydUoN4j9",
  "razorpay_payment_id": "pay_Ns2mX7qRkL9vW1",
  "razorpay_signature": "e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c9d0e1f2",
  "razorpay_order_id": "order_Pq3kW8rLmN4vX2",
  "amount": 110000
}
```

#### Signature Verification

The HMAC-SHA256 signature is computed as:
```
razorpay_subscription_id + "|" + razorpay_payment_id
```
and verified against the key secret.

#### Responses

**Success** (HTTP 200):
```json
{
  "success": true,
  "payment_id": 156,
  "subscription_id": 42,
  "installment_number": 1,
  "amount": 1100,
  "receipt_number": "SMS/2026/00001",
  "receipt_generated": true,
  "subscription_completed": false,
  "installments_remaining": 11
}
```

**Idempotent (already processed)** (HTTP 200):
```json
{
  "success": true,
  "already_processed": true,
  "payment_id": 156,
  "subscription_id": 42,
  "installment_number": 1
}
```

**Signature verification failed** (HTTP 400):
```json
{
  "success": false,
  "error": "Payment signature verification failed"
}
```

#### Key Implementation Details

- **Idempotency**: If a payment with the same `razorpay_payment_id` already exists, the endpoint returns the existing data without creating a duplicate. This makes it safe to retry after network errors.
- **HMAC verification**: Subscription payments use `{subscription_id}|{payment_id}` for HMAC, NOT the standard `{order_id}|{payment_id}` used for one-time donations.
- **Installment tracking**: Automatically calculates the next installment number based on existing payments for the subscription.
- **80G receipt generation**: If the donor has a PAN and the payment is ≥ ₹200, a receipt number is generated (`SMS/YYYY/NNNNN`) and a receipt record is created.
- **Subscription completion**: If `installments_paid` reaches `total_installments`, the subscription status is updated to `completed`.
- **Logging**: All payments are logged to `logs/sudamaseva_payments.log`.

---

### POST /api/sudamaseva-webhook

Server-to-server webhook handler for Razorpay subscription events. This is the **authoritative** payment verifier — it runs as a background callback from Razorpay's servers.

**URL:** `https://yourdomain.com/api/sudamaseva-webhook`
**Method:** `POST`
**Content-Type:** `application/json`
**Auth:** HMAC-SHA256 webhook signature (validated via `X-Razorpay-Signature` header)

#### Webhook Setup

Configure in [Razorpay Dashboard](https://dashboard.razorpay.com) → Settings → Webhooks:
```
Webhook URL: https://yourdomain.com/api/sudamaseva-webhook
Secret:      Same as RAZORPAY_KEY_SECRET
Events:      subscription.charged, payment.captured, payment.failed,
             subscription.completed, subscription.cancelled
```

#### Events Handled

| Event | Action | Description |
|-------|--------|-------------|
| `subscription.charged` | ✅ Processed | **Main event.** Creates payment record, increments installments_paid, generates 80G receipt if eligible. Handles idempotency via razorpay_payment_id dedup. |
| `payment.captured` | ✅ Processed | Supplementary. Updates payment status to `paid` if record exists. |
| `payment.authorized` | ✅ Processed | Same as captured. Updates payment status to `paid`. |
| `payment.failed` | ✅ Processed | Logs the failure, updates payment status to `failed` with error code/description. |
| `subscription.completed` | ✅ Processed | Marks subscription as `completed` with end_date. |
| `subscription.cancelled` | ✅ Processed | Marks subscription as `cancelled` with end_date. |
| All other events | ⏭️ Ignored | Acknowledged with `{"status": "ignored"}`. |

#### Security

- HMAC-SHA256 signature verified against `RAZORPAY_KEY_SECRET` using the **raw request body** (not JSON-decoded).
- Always verify using `hash_equals()` to prevent timing attacks.
- Requests without a signature header are rejected with HTTP 400.

#### Logging

All webhook events (including ignored ones) are logged to `logs/sudamaseva_webhooks.log`.

#### Example Payloads

**subscription.charged:**
```json
{
  "event": "subscription.charged",
  "payload": {
    "subscription": {
      "entity": {
        "id": "sub_LhZ8xPydUoN4j9",
        "plan_id": "plan_LhZ8xPy123ABCD",
        "status": "active",
        "current_start": "2026-01-15T10:30:00Z",
        "current_end": "2026-02-15T10:30:00Z",
        "charge_at": 1736932200,
        "total_count": 12,
        "paid_count": 1
      }
    },
    "payment": {
      "entity": {
        "id": "pay_Ns2mX7qRkL9vW1",
        "order_id": "order_Pq3kW8rLmN4vX2",
        "amount": 110000,
        "currency": "INR",
        "status": "captured",
        "method": "upi"
      }
    }
  }
}
```

**payment.failed:**
```json
{
  "event": "payment.failed",
  "payload": {
    "payment": {
      "entity": {
        "id": "pay_Ns2mX7qRkL9vW1",
        "amount": 110000,
        "error_code": "BAD_PAYMENT",
        "error_description": "The payment was declined by the bank.",
        "error_source": "bank",
        "error_step": "payment_authentication"
      }
    }
  }
}
```

---

### POST /api/sudamaseva-lookup

Looks up an existing donor by phone number or legacy ID for dashboard access.

**URL:** `https://yourdomain.com/api/sudamaseva/lookup`
**Method:** `POST`
**Content-Type:** `application/json`
**Auth:** None (public API)

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `query` | string | ✅ | Phone number or legacy ID number to search |

**Example request:**
```json
{
  "query": "9876543210"
}
```

**Success Response** (HTTP 200 — found):
```json
{
  "found": true,
  "donor_id": 301,
  "donor_name": "Radha Krishna Das",
  "redirect": "http://localhost:8080/isjm/sudamaseva/dashboard?donor_id=301"
}
```

**Success Response** (HTTP 200 — not found):
```json
{
  "found": false,
  "message": "No donor found with this phone number. Please register for Sudamaseva."
}
```

#### Key Implementation Details

- Searches by exact phone match first, then falls back to `legacy_id_no` match
- Legacy IDs were backfilled from the old `tbl_users.id_no` field (≈302 records)
- On success, returns the relative redirect URL for the frontend to navigate

---

### POST /api/sudamaseva-enroll

Creates a new manual-mode enrollment (Pay Monthly). Creates donor + subscription + Razorpay Order in one call.

**URL:** `https://yourdomain.com/api/sudamaseva/enroll`
**Method:** `POST`
**Content-Type:** `application/json`
**Auth:** None (public API — protected by CORS origin check)

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `donor_name` | string | ✅ | Donor's full name |
| `donor_phone` | string | ✅ | Phone number (unique identifier) |
| `donor_email` | string | ❌ | Email address |
| `amount` | int | ❌ | Amount in **paise** (default: 5100 = ₹51). Clamped to ₹51–₹1,00,000. |
| `total_installments` | int | ❌ | Number of monthly installments (default: 12, max: 120) |
| `pan_number` | string | ❌ | PAN for 80G tax receipt eligibility |
| `area` | string | ❌ | Donor's area/locality |
| `city` | string | ❌ | Donor's city |
| `state` | string | ❌ | Donor's state |

**Example request:**
```json
{
  "donor_name": "Radha Krishna Das",
  "donor_phone": "9876543210",
  "donor_email": "rk.das@example.com",
  "amount": 110000,
  "total_installments": 12,
  "pan_number": "ABCDE1234F",
  "area": "Seshadripuram",
  "city": "Bangalore",
  "state": "Karnataka"
}
```

**Success Response** (HTTP 200):
```json
{
  "success": true,
  "subscription_id": 42,
  "donor_id": 301,
  "order_id": "order_TAyJR2Yswpk0PB",
  "amount": 110000,
  "currency": "INR",
  "receipt": "sms_1712345678_123",
  "collection_mode": "manual"
}
```

**Error Response** (HTTP 400/500):
```json
{
  "error": "Donor name and phone are required"
}
```

#### Key Implementation Details

- Creates a subscription with `collection_mode = 'manual'` (no Razorpay subscription object)
- Creates a Razorpay Order for the first installment amount
- Donor deduplication by phone (same as create-subscription)
- The order_id is used to open Razorpay checkout (`order_id` NOT `subscription_id`)

---

### POST /api/sudamaseva-create-order

Creates a Razorpay Order for a subsequent installment on an existing manual subscription. Used when a donor clicks "Pay Now" on the dashboard.

**URL:** `https://yourdomain.com/api/sudamaseva/create-order`
**Method:** `POST`
**Content-Type:** `application/json`
**Auth:** None (public API)

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `subscription_id` | int | ✅ | Internal subscription ID (from sudamaseva_subscriptions) |
| `installment_number` | int | ✅ | Which installment to pay for (e.g., 2, 3, 4...) |

**Example request:**
```json
{
  "subscription_id": 42,
  "installment_number": 2
}
```

**Success Response** (HTTP 200):
```json
{
  "success": true,
  "order_id": "order_NEW123456",
  "amount": 110000,
  "currency": "INR",
  "receipt": "sms_42_2",
  "subscription_id": 42,
  "installment_number": 2,
  "donor_id": 301
}
```

**Error Response** (HTTP 400):
```json
{
  "success": false,
  "error": "Installment 2 has already been paid"
}
```

#### Key Implementation Details

- Validates that the subscription exists and has `collection_mode = 'manual'`
- Validates that the requested installment hasn't already been paid
- Prevents paying future installments out of order (must pay sequentially)
- Creates a standard Razorpay Order (not a Subscription)

---

### POST /api/sudamaseva-verify-order

Verifies a manual-mode payment after Razorpay checkout completes. Uses `{order_id}|{payment_id}` HMAC format (standard Razorpay Order verification).

**URL:** `https://yourdomain.com/api/sudamaseva/verify-order`
**Method:** `POST`
**Content-Type:** `application/json`
**Auth:** None (public API — protected by CORS + HMAC signature verification)

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `razorpay_order_id` | string | ✅ | Order ID from the create-order or enroll response |
| `razorpay_payment_id` | string | ✅ | Payment ID from Razorpay checkout |
| `razorpay_signature` | string | ✅ | HMAC signature from Razorpay checkout |
| `subscription_id` | int | ✅ | Internal subscription ID |
| `installment_number` | int | ✅ | Which installment this payment is for |
| `amount` | int | ❌ | Amount in paise (for verification) |

#### Signature Verification

Manual-mode payments use the **standard Razorpay Order** HMAC format:
```
razorpay_order_id + "|" + razorpay_payment_id
```
This is different from subscription payments which use `{subscription_id}|{payment_id}`.

#### Responses

**Success** (HTTP 200):
```json
{
  "success": true,
  "payment_id": 156,
  "subscription_id": 42,
  "installment_number": 2,
  "amount": 1100,
  "receipt_number": "SMS/2026/00002",
  "receipt_generated": true,
  "subscription_completed": false,
  "installments_remaining": 10
}
```

**Idempotent (already processed)** (HTTP 200):
```json
{
  "success": true,
  "already_processed": true,
  "payment_id": 156,
  "subscription_id": 42,
  "installment_number": 2
}
```

#### Key Implementation Details

- Same idempotency logic as verify-payment (dedup by razorpay_payment_id)
- HMAC uses `{order_id}|{payment_id}` format (different from subscription verify which uses `{subscription_id}|{payment_id}`)
- Creates payment record with `payment_source = 'manual_order'`
- Increments `installments_paid` and calculates `next_unpaid`
- Generates 80G receipt if eligible

---

## Sudamaseva Public Pages

### Lookup Page (`/sudamaseva/lookup`)

Find your existing donation. Search by phone number or legacy ID.

- **URL:** `/sudamaseva/lookup` → `sudamaseva/lookup.php` → `modules/Sudamaseva/content/lookup.php`
- **Method:** GET for form, POST for API
- **Flow:** Enter phone → POST `/api/sudamaseva/lookup` → redirect to dashboard
- **Features:**
  - Clean, minimal search form
  - Links to new registration page
  - Handles not-found gracefully with register CTA

### Dashboard Page (`/sudamaseva/dashboard?donor_id=X`)

Donor's personal donation overview.

- **URL:** `/sudamaseva/dashboard?donor_id=X` → `sudamaseva/dashboard.php` → `modules/Sudamaseva/content/dashboard.php`
- **Auth:** Query param-based (simple access, no login required)
- **Features:**
  - Donor name, total contribution
  - Subscription card with amount, mode badge, installments progress
  - Installment grid showing 12-month schedule with paid/upcoming/late status
  - "Pay Now" button on unpaid installments (manual mode only)
  - Payment history table
  - Return to lookup link

### Signup Page (`/sudamaseva` — with mode toggle)

New donor registration with mode toggle between Auto Monthly and Pay Monthly.

- **URL:** `/sudamaseva` → `sudamaseva/index.php` → `modules/Sudamaseva/content/index.php`
- **Features:**
  - Mode toggle: Auto Monthly (default) / Pay Monthly
  - Auto Monthly shows subscription duration dropdown, auto-debit notice
  - Pay Monthly hides duration (implied 12 installments), shows manual payment notice
  - Donor fills name, phone, email, PAN, amount, address
  - Submit button changes based on mode: "Subscribe — ₹X/month" vs "Pay ₹X Now"
  - "View My Seva" returning donor CTA link to lookup

---

## Database Tables (Sudamaseva)

### `sudamaseva_donors`

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `uuid` | varchar(36) | UUID for future devotee care cross-reference |
| `legacy_id_no` | varchar(50) | **New.** Backfilled from old `tbl_users.id_no` (≈302 records). Indexed. |
| `donor_name` | varchar(255) | |
| `phone` | varchar(15) | Unique identifier, indexed |
| `email` | varchar(255) | Nullable |
| `pan` | varchar(20) | Nullable |
| `area` | varchar(255) | |
| `city` | varchar(255) | |
| `state` | varchar(255) | |
| `source` | varchar(50) | Default: `migrated` (from old system) or `sudamaseva` |
| `notes` | text | Admin notes |
| `status` | enum('active','inactive','paused') | Default: 'active' |
| `created_at` | datetime | |
| `updated_at` | datetime | |

Indexes: `phone` (UNIQUE), `email`, `status`, `source`, `legacy_id_no`

### `sudamaseva_subscriptions`

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `donor_id` | int | FK → sudamaseva_donors.id |
| `amount` | int | Monthly amount in paise (min ₹51) |
| `razorpay_subscription_id` | varchar(255) | Nullable for manual subscriptions |
| `razorpay_plan_id` | varchar(255) | Nullable for manual subscriptions |
| `status` | enum('active','completed','paused','cancelled') | |
| `start_date` | datetime | Subscription start |
| `end_date` | datetime | Nullable |
| `total_installments` | int | Total installment count (e.g., 12, 24) |
| `collection_mode` | enum('recurring','manual') | **New.** `'recurring'` = auto-debit via Razorpay subscription; `'manual'` = pay each month manually |
| `installments_paid` | int | Count of paid installments |
| `source` | enum('migrated','new') | Whether from old app or fresh |
| `old_user_id` | int | Reference to old tbl_users.id (migrated only) |
| `created_at` | datetime | |
| `updated_at` | datetime | |

Indexes: `donor_id`, `status`, `razorpay_subscription_id` (UNIQUE), `collection_mode`

### `sudamaseva_payments`

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `subscription_id` | int | FK → sudamaseva_subscriptions.id (nullable for orphan payments) |
| `donor_id` | int | FK → sudamaseva_donors.id, nullable for orphan payments |
| `amount` | int | Amount in paise |
| `installment_number` | int | Installment sequence number (0 = orphan/unlinked) |
| `razorpay_payment_id` | varchar(255) | |
| `razorpay_order_id` | varchar(255) | |
| `razorpay_signature` | varchar(255) | |
| `payment_status` | enum('created','attempted','paid','failed') | Default: 'created' |
| `payment_date` | datetime | |
| `payment_source` | enum('subscription_charge','manual_order','migrated','admin_manual') | **New.** Tracks how payment was made: via Razorpay subscription auto-charge, manual order, legacy migration, or admin entry |
| `billing_month` | date | **New.** The billing period this payment covers (e.g., '2026-01-01' for January 2026). Indexed. |
| `receipt_number` | varchar(50) | Auto-generated receipt no |
| `notes` | text | |
| `is_migrated` | tinyint(1) | Whether imported from old system |
| `old_ins_pay_id` | int | Reference to old tbl_rec_ins_pay.id |
| `created_at` | datetime | |

Indexes: `subscription_id`, `donor_id`, `razorpay_payment_id` (UNIQUE), `installment_number`, `payment_date`, `payment_source`, `billing_month`

### `sudamaseva_receipts`

| Column | Type | Notes |
|--------|------|-------|
| `id` | int (PK, AI) | |
| `payment_id` | int | FK → sudamaseva_payments.id |
| `receipt_no` | varchar(50) | Format: `SMS/YYYY/NNNNN` (e.g., SMS/2026/00001) |
| `receipt_date` | datetime | |
| `receipt_data` | json | Full receipt details |
| `is_80g_eligible` | tinyint(1) | Whether qualifies for 80G |
| `created_at` | datetime | |

### Key Relationships

```
sudamaseva_donors.id ← sudamaseva_subscriptions.donor_id
    └── sudamaseva_subscriptions.id ← sudamaseva_payments.subscription_id
            └── sudamaseva_payments.id ← sudamaseva_receipts.payment_id
```

**New columns highlighted:**
- `sudamaseva_donors.legacy_id_no` — Links back to old system's `tbl_users.id_no`
- `sudamaseva_subscriptions.collection_mode` — Distinguishes recurring vs manual subscriptions
- `sudamaseva_payments.payment_source` — Identifies how each payment was collected
- `sudamaseva_payments.billing_month` — Enables monthly aggregation queries

### 80G Receipt Format

Receipt numbers follow the format: `SMS/<year>/<5-digit-sequence>`

Examples: `SMS/2026/00001`, `SMS/2026/00152`

### Migrations

```bash
# Create tables + initial schema
php modules/Sudamaseva/migrations/001_create_tables.php

# Migrate data from old sudamaseva system (302 users, 3,278 payments)
php modules/Sudamaseva/migrations/002_migrate_data.php

# Incremental fixes after initial migration
php modules/Sudamaseva/migrations/003_incremental_migration.php

# Add manual payment fields (legacy_id_no, collection_mode, payment_source, billing_month)
php modules/Sudamaseva/migrations/004_add_manual_payment_fields.php

# Backfill legacy_id_no from old tbl_users (match by phone)
php modules/Sudamaseva/migrations/005_backfill_legacy_ids.php
```

---

## Error Handling

All endpoints return JSON with appropriate HTTP status codes:
- `200` — Success
- `400` — Invalid request / missing parameters / signature failed
- `404` — Subscription not found during verification
- `405` — Method not allowed (non-POST requests)
- `500` — Server error (logged, generic message returned to client)

## Test Mode

Set `RAZORPAY_TEST_MODE=true` in `.env`. Use test keys (`rzp_test_*`). All payment flows work identically in test mode — amounts are verified against the same catalog. Sudamaseva webhooks validate HMAC signatures in both test and live modes.

For testing webhooks locally, use a tool like `ngrok` to expose your local server, then configure the ngrok URL in Razorpay Dashboard → Webhooks.
