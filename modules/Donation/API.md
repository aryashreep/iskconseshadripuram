# Donation Module — API

> **Last updated:** 2026-07-11
> **Related:** [`docs/API.md`](../../docs/API.md) (project-wide API reference), [`SECURITY.md`](../../SECURITY.md) (payment security)

## POST /api/create-order.php
Creates a Razorpay order for a donation.

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

**Response** (200):
```json
{
  "order_id": "order_xxxxx",
  "amount": 100100,
  "currency": "INR"
}
```

**Validation**: Amount verified server-side against `donation_cause_master_sevas` or `donation_cause_sevas` catalog.
**HTTP Codes**: 200 (success), 400 (invalid request), 500 (server error)
**Amount format**: Paise (e.g., 100100 = ₹1,001.00)

---

## POST /api/verify-payment.php
Verifies Razorpay payment signature and updates transaction status to 'paid'.

**Request** (JSON):
```json
{
  "razorpay_order_id": "order_xxxxx",
  "razorpay_payment_id": "pay_xxxxx",
  "razorpay_signature": "xxxxx"
}
```

**Response** (200):
```json
{
  "status": "success",
  "payment_id": "pay_xxxxx"
}
```

**Security**: HMAC-SHA256 signature verification using `RAZORPAY_KEY_SECRET`.
**HTTP Codes**: 200 (success), 400 (invalid request), 403 (signature mismatch)

---

## POST /api/webhook.php
Server-to-server payment notification from Razorpay. Authoritative source of truth for all payment events.

**Events handled**:
- `payment.captured` → status = 'paid'
- `payment.authorized` → status = 'paid'
- `payment.failed` → status = 'failed'

**Request**: Razorpay webhook payload (JSON) with `X-Razorpay-Signature` header.
**Security**: HMAC-SHA256 signature validation using `RAZORPAY_KEY_SECRET`.

---

## POST /api/create-booking-order.php
Creates a Razorpay order for a puja or yagya booking.

> **TODO**: Move to Booking module when created.

**Request** (JSON):
```json
{
  "amount": 51000,
  "booking_type": "puja",
  "puja_slug": "maha-abhishekam",
  "puja_date": "2026-08-15"
}
```

**Response** (200):
```json
{
  "order_id": "order_xxxxx",
  "amount": 51000,
  "currency": "INR"
}
```

---

## POST /api/create-panihati-order.php
Creates a Razorpay order for Panihati Yatra registration.

> **TODO**: Move to Panihati module when created.

**Security**: All endpoints require:
- Amount verification against server-side catalog (never trust client-sent amounts)
- Razorpay HMAC signature verification for payment confirmation
- Prepared statements for all DB queries
- No sensitive data in error messages (logged server-side)
