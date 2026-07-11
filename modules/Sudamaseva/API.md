# Sudamaseva — API Reference

See `docs/API.md` for the full project API reference. This file documents Sudamaseva-specific endpoints.

## POST /api/sudamaseva/create-subscription
Creates a Razorpay subscription (Auto Monthly mode).

**Request:**
```json
{
  "donor_name": "Radha Krishna Das",
  "donor_phone": "9876543210",
  "donor_email": "rk.das@example.com",
  "amount": 110000,
  "total_installments": 12
}
```

**Response:**
```json
{ "success": true, "subscription_id": "sub_xxx", "db_subscription_id": 42, "short_url": "https://rzp.io/..." }
```

## POST /api/sudamaseva/enroll
Creates manual-mode enrollment (Pay Monthly) — donor + subscription + order in one call.

**Response:** Returns `{order_id}` for Razorpay checkout.

## POST /api/sudamaseva/verify-payment
Verifies subscription payment. HMAC: `{subscription_id}|{payment_id}`

## POST /api/sudamaseva/verify-order
Verifies manual order payment. HMAC: `{order_id}|{payment_id}` (standard Razorpay format)

## POST /api/sudamaseva/webhook
Handles subscription events: `subscription.charged`, `payment.captured`, `payment.failed`, `subscription.completed`

## POST /api/sudamaseva/lookup
Search donor by phone or legacy_id_no. Returns `{found, donor_id, redirect}`

## POST /api/sudamaseva/create-order
Creates Razorpay Order for subsequent manual installment.
