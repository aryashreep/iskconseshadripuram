# Sudamaseva — Architecture Decisions

## [2026-07-08] Two Payment Modes: Auto Monthly + Pay Monthly

### Decision
Support two modes: recurring (auto-debit via Razorpay subscription) and manual (donor pays each month via order).

### Context
The old system only supported recurring charges. Some donors prefer to pay manually each month. The mode toggle on the signup page lets donors choose.

### Key Trade-offs
- Auto Monthly: Relies on Razorpay's billing engine — donor must have valid payment method
- Pay Monthly: Requires donor to remember to pay — PayPal-style payment links would be a future improvement

## [2026-07-08] Separate sudamaseva_* Tables (Not Reusing donation_* Tables)

### Decision
Created dedicated tables (`sudamaseva_donors`, `sudamaseva_subscriptions`, etc.) rather than reusing `donation_subscriptions`.

### Rationale
Sudamaseva tracks legacy installment data (installment_number, old_user_id, etc.) that doesn't fit the generic Donation schema. Separation avoids schema drift.

## [2026-07-08] HMAC Format Difference Between Modes

### Decision
Auto Monthly uses `{subscription_id}|{payment_id}` HMAC. Pay Monthly uses standard `{order_id}|{payment_id}` HMAC.

### Rationale
Auto Monthly uses Razorpay Subscription objects (no order_id present). Pay Monthly uses Razorpay Order objects (standard HMAC format).
