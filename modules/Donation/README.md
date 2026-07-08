# Module: Donation

## Purpose
Handles all donation-related functionality: donation causes, seva offerings, Razorpay payment processing, transaction tracking, donation reporting, and checkout cart management.

This is the largest and most complex module in the application, serving as the primary revenue engine for the temple's online presence.

## Owned Database Tables
- `donation_causes` — Activities/festivals (74 rows, category field: festival, ekadashi, appearance, disappearance, event, service, construction, general)
- `donation_transactions` — Payment records (cause_id, seva_id, master_seva_id, amount, donor info, payment_status)
- `master_seva_categories` — Top-level seva groupings (10 categories)
- `master_sevas` — Deduplicated seva catalog (363+ sevas)
- `donation_cause_master_sevas` — Pivot: cause ↔ seva with override support (override_amount, override_description)
- `donation_cause_sevas` — Legacy per-cause seva table (backward compatible)
- `donation_seva_categories` — Legacy seva category table (backward compatible)
- `donation_subscriptions` — Recurring donation subscriptions

## Dependencies
- **Kernel** — Auth, CSRF token, Layout rendering (header/footer)
- **Booking** — Puja/yagya bookings reference `donation_transactions.id` via `booking_pujas.transaction_id`

## Public Interface
```php
// Primary classes — use these directly in new code
Isjm\Modules\Donation\DonationRepository    // All DB queries (causes, sevas, transactions)
Isjm\Modules\Donation\DonationService       // Business logic (formatting, grouping, seasonal)
Isjm\Modules\Donation\DonationRenderer      // HTML rendering (CTA buttons, seva forms)
```

```php
// Legacy facade — backward compatible, use only for existing code paths
// See includes/donation-helpers.php
getDonationCauseBySlug(string $slug): ?array
getDonationCauses(?string $category, bool $featuredOnly): array
getCauseSevas(int $causeId): array
getCauseSevasGrouped(int $causeId): array
createDonationTransaction(array $data): int|false
formatDonationAmount(float $amount): string
renderDonationCTA(array $options): void
renderDonationSevaOptions(array $cause, array $groupedSevas): void
```

## Entry Points

| Type | URL | Handler |
|------|-----|---------|
| Public | `/donate/{slug}` | Donation page by cause slug |
| Public | `/checkout/` | Cart checkout page |
| Public | `/donate/payment-success` | Post-payment success receipt |
| Public | `/donate/payment-failed` | Post-payment failure message |
| API | POST `/api/create-order.php` | Create Razorpay order |
| API | POST `/api/verify-payment.php` | Verify Razorpay payment signature |
| API | POST `/api/webhook.php` | Razorpay webhook (authoritative) |
| Admin | `/admin/donations` | Transaction logs with filters |
| Admin | `/admin/report-dashboard` | KPI dashboard with 8 charts |
| Admin | `/admin/report-category` | Category-wise donation report |
| Admin | `/admin/report-activity` | Activity-wise report with search |
| Admin | `/admin/report-seva` | 3-level Category → Activity → Seva report |
| Admin | `/admin/seva-catalogue` | Master seva CRUD management |
| Admin | `/admin/export-donations` | Transaction CSV export |
| Admin | `/admin/export-report-*` | Report CSV exports (3 files) |

## Directory Structure
```
modules/Donation/
├── DonationRepository.php     # DB queries (PSR-4: Isjm\Modules\Donation\)
├── DonationService.php        # Business logic
├── DonationRenderer.php       # HTML rendering
├── Admin/                     # Admin panel pages
│   ├── Reports/               # Report page handlers
│   └── Exports/               # CSV export handlers
├── api/                       # API endpoint handlers
├── assets/                    # Module-owned JS, CSS
│   ├── js/
│   └── css/
├── templates/                 # Page templates
├── tests/                     # Module tests
│   └── e2e/                   # E2E test specs
├── content/                   # Content data files
├── README.md                  # This file
├── DECISIONS.md               # Architecture decisions
├── DATABASE.md                # Schema documentation
├── API.md                     # API contracts
├── TASKS.md                   # Module backlog
└── routes.php                 # Route registration
```
