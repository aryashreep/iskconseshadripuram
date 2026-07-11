# Module: Booking

## Purpose
Manages Puja and Yagya bookings — offering tiers, pricing, and Razorpay payment integration for temple services.

## Owned Database Tables
- `booking_pujas` — Puja/yagya bookings with status, transaction_id link to `donation_transactions`

## Dependencies
- **Kernel** — Config, DB, CSRF tokens
- **Donation** — Bookings link to `donation_transactions.id` for payment records

## Entry Points
| Type | URL | File | Description |
|------|-----|------|-------------|
| Public | `/booking` | `content/index.php` | Booking listing page |
| Public | `/booking/puja/{slug}` | `content/puja/detail.php` | Puja detail page with tiers |
| Public | `/booking/yagya/{slug}` | `content/yagya/detail.php` | Yagya detail page with tiers |
| API | `POST /api/create-booking-order.php` | `api/create-booking-order.php` | Create Razorpay order for booking |
| Admin | `/admin/bookings` | `Admin/bookings.php` | Booking management (view, toggle status) |

## Business Rules
- Booking date must be in the future (validated server-side)
- Puja/yagya pricing is loaded from DB and verified server-side
- Each booking links to a `donation_transactions` record for payment tracking
- Status lifecycle: `pending` → `confirmed` → `completed` → `cancelled`
- Offering tiers display on detail pages with inclusions, divine returns, and delivery info
