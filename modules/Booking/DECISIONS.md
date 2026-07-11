# Booking Module — Architecture Decisions

> **Last updated:** 2026-07-11
> **Related:** `README.md` (module overview), `WORKFLOWS.md` (booking flow), `docs/DATABASE.md` (table ownership)

---

## [2026-06-XX] Booking Payments Linked to Donation Transactions

### Decision
Booking payments are stored in `booking_pujas` with a `transaction_id` foreign key referencing `donation_transactions.id`, rather than having a separate payment table or storing payment details directly in the booking record.

### Context
Puja and yagya bookings require payment processing through Razorpay. The existing Donation module already handles Razorpay order creation, payment verification, and webhook processing through the `donation_transactions` table. Creating a separate payment flow for bookings would duplicate this infrastructure.

### Options Considered
- **Separate payment table**: `booking_payments` table with its own Razorpay integration — duplicates payment code
- **Link to donation_transactions (chosen)**: Reuse existing payment infrastructure via FK
- **Payment columns in booking_pujas**: Store razorpay_* fields directly in booking row — mixes concerns

### Rationale
Reusing `donation_transactions` avoids duplicating the entire Razorpay payment flow (order creation, HMAC verification, webhook handling). The FK relationship allows reporting queries to join booking data with payment data. The shared transaction ID provides a single source of truth for payment status.

### Trade-offs
- **Cross-module dependency**: Booking depends on Donation module's payment infrastructure
- **Transaction separation**: A `donation_transactions` record for a booking looks similar to a donation record — must distinguish via `source_type` or booking FK
- **Cascade complexity**: Deleting a transaction (rare) would orphan the booking reference

### Related Files
- `api/create-booking-order.php` — Creates Razorpay order for booking
- `modules/Booking/Admin/bookings.php` — Admin booking management
- `modules/Donation/api/verify-payment.php` — Shared payment verification

---

## [2026-06-XX] Puja and Yagya as Content Types, Not Separate Modules

### Decision
Puja and Yagya booking are managed within the same Booking module as separate content types, sharing the same `booking_pujas` table distinguished by `puja_type` field.

### Context
Both pujas and yagyas follow the same flow: listing → detail page with offering tiers → booking form → payment → confirmation. The differences are primarily in the UI content (descriptions, tiers, inclusions) rather than the backend flow.

### Options Considered
- **Separate modules**: `modules/Puja/` and `modules/Yagya/` — duplication of booking infrastructure
- **Single module with content types (chosen)**: Same code paths, differentiated by type
- **Single module with separate tables**: `booking_pujas` and `booking_yagyas` — nearly identical schemas

### Rationale
The booking flow is identical for both. Sharing code reduces maintenance burden and ensures consistent behavior. The `puja_type` discriminator field cleanly separates the two while allowing unified admin management.

### Trade-offs
- **Content differences**: UI content varies (hymns, inclusions, delivery) — requires conditional rendering
- **Future divergence**: If puja and yagya flows diverge significantly, the shared code would need refactoring
- **Admin confusion**: All bookings appear in one admin list — mitigated by type filter

### Related Files
- `modules/Booking/content/puja/detail.php` — Puja detail template
- `modules/Booking/content/yagya/detail.php` — Yagya detail template
- `modules/Booking/Admin/bookings.php` — Unified booking management

---

## [2026-06-XX] Status-Based Booking Lifecycle (No Refund Logic)

### Decision
Booking status follows a simple lifecycle (`pending → confirmed → completed → cancelled`) without implementing refund logic. Status changes are manual (admin toggles).

### Context
The project runs on shared hosting with no background job infrastructure. Implementing automated refund processing (initiating Razorpay refunds, sending notifications, updating booking status) would require async processing or webhook orchestration beyond the current infrastructure.

### Options Considered
- **Full lifecycle with refunds**: Automated refund processing via Razorpay API — requires async infrastructure
- **Simple status lifecycle (chosen)**: Manual admin status management, no automated refunds
- **Status only, no confirmations**: Admin manually confirms payments after verifying in Razorpay dashboard

### Rationale
Manual status management matches the current operational model where temple staff verify payments in the Razorpay dashboard and update booking status accordingly. Refunds, when needed, are processed through the Razorpay dashboard directly, not through the application.

### Trade-offs
- **Manual overhead**: Admin must manually confirm bookings and update status
- **No refund automation**: Refunds require Razorpay dashboard access
- **No expiry/auto-cancel**: Unpaid bookings remain pending indefinitely

### Related Files
- `modules/Booking/Admin/bookings.php` — Toggle status functionality
- `api/create-booking-order.php` — Payment order creation
