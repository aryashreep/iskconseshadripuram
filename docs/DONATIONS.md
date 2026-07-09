# DONATIONS.md — Donation System

## Hierarchy

```
Category → Activity → Seva
```

- **Category**: Top-level grouping (festival, ekadashi, appearance, disappearance, event, service, construction, general)
- **Activity**: Individual cause within a category (Rath Yatra, Janmashtami, Food for Life)
- **Seva**: Specific offering within an activity (Flower Decoration, Annadanam, Rajbhog)

## Payment Flow

### Standard One-Time Donation

1. Donor selects cause and seva on `/donate/{slug}`
2. Amount verified server-side against `donation_cause_master_sevas` catalog
3. Razorpay order created via `/api/create-order.php`
4. Donor completes payment in Razorpay checkout
5. Client calls `/api/verify-payment.php` with HMAC signature
6. Server updates `donation_transactions.payment_status = 'paid'`
7. Webhook `/api/webhook.php` provides secondary verification
8. Donor sees success page at `/donate/payment-success.php`

### Sudamaseva Recurring Donation

See [docs/API.md](/docs/API.md#sudamaseva-module-api) for full details on:
- **Auto Monthly**: Razorpay subscription with auto-debit
- **Pay Monthly**: Donor pays each installment manually via checkout
- **Lookup & Dashboard**: Returning donor search and payment management

## Key Tables

| Table | Records | Purpose |
|-------|---------|---------|
| `donation_causes` | 74 | Activities with category field |
| `donation_transactions` | varies | Payment records |
| `master_seva_categories` | 10 | Top-level seva groupings |
| `master_sevas` | 363+ | Deduplicated seva catalog |
| `donation_cause_master_sevas` | varies | Pivot: cause ↔ seva links |
| `donation_cause_sevas` | legacy | Old per-cause seva table |

## Category Values

| Value | Label | Icon |
|-------|-------|------|
| festival | Grand Festivals | fa-star |
| ekadashi | Ekadashi | fa-moon |
| appearance | Appearance Days | fa-sun |
| disappearance | Disappearance Days | fa-candle |
| event | Events & Programs | fa-calendar-check |
| service | Seva & Services | fa-hands-helping |
| construction | Temple Construction | fa-building |
| general | General Donations | fa-heart |

## Reporting Pages

| Page | URL | Grouping |
|------|-----|----------|
| Dashboard | `/admin/report-dashboard` | All levels + charts |
| Category | `/admin/report-category` | Category only |
| Activity | `/admin/report-activity` | Category → Activity |
| Seva | `/admin/report-seva` | Category → Activity → Seva |

## Dual-Read Strategy

`getCauseSevas()` in `DonationRepository.php` checks `donation_cause_master_sevas` first. If no rows found, falls back to `donation_cause_sevas`. This allows gradual migration of causes to the master catalog.
