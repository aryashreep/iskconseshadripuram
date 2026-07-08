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

## Sudamaseva Subscription API

Sudamaseva is the recurring subscription donation system. Donors pledge a monthly amount that is automatically charged each billing cycle via Razorpay subscriptions.

### Flow Overview

```
1. Donor fills form on /sudamaseva (name, phone, email, amount, installments)
2. Frontend → POST /api/sudamaseva-create-subscription
3. Server finds or creates a donor record (keyed by phone)
4. Server creates/reuses a Razorpay Plan (monthly, fixed amount)
5. Server creates a Razorpay Subscription linked to the plan
6. Server saves subscription record in sudamaseva_subscriptions table
7. Returns subscription_id + short_url to frontend
8. Razorpay checkout modal opens
9. Donor completes first installment payment
10. Frontend → POST /api/sudamaseva-verify-payment
11. Server verifies HMAC signature, creates payment + receipt records
12. Subsequent installments are charged automatically (Razorpay handles billing)
13. Razorpay sends webhooks for each event (subscription.charged, etc.)
14. Webhook → POST /api/sudamaseva-webhook (server-to-server, authoritative)
```

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

## Database Tables (Sudamaseva)

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `sudamaseva_donors` | Donor profiles | id, uuid, donor_name, phone (UNIQUE), email, pan, area, city, state, source, status |
| `sudamaseva_subscriptions` | Subscription records | id, donor_id, amount, razorpay_subscription_id, razorpay_plan_id, status, start_date, end_date, total_installments, installments_paid, source |
| `sudamaseva_payments` | Individual installment payments | id, subscription_id, donor_id, amount, installment_number, razorpay_payment_id, razorpay_order_id, payment_status, receipt_number, notes |
| `sudamaseva_receipts` | 80G tax receipts | id, payment_id, receipt_no (SMS/YYYY/NNNNN), receipt_date, receipt_data (JSON), is_80g_eligible |

### Key Relationships

```
sudamaseva_donors.id ← sudamaseva_subscriptions.donor_id
    └── sudamaseva_subscriptions.id ← sudamaseva_payments.subscription_id
            └── sudamaseva_payments.id ← sudamaseva_receipts.payment_id
```

### 80G Receipt Format

Receipt numbers follow the format: `SMS/<year>/<5-digit-sequence>`

Examples: `SMS/2026/00001`, `SMS/2026/00152`

### Migrations

```bash
php modules/Sudamaseva/migrations/001_create_tables.php
php modules/Sudamaseva/migrations/002_migrate_data.php
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
