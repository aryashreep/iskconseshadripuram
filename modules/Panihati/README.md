# Module: Panihati

## Purpose
Manages the Panihati Yatra — a pilgrimage event registration system with bus/vehicle booking, pricing management, bhakti sadans, pickup locations, and expense tracking.

## Owned Database Tables
- `panihati_yatra_registrations` — Travel bookings (NEVER TRUNCATE in production)
- `panihati_pricing` — Yearly pricing configuration
- `panihati_bhakti_sadans` — Sadhan (accommodation) options
- `panihati_pickup_locations` — Pickup point options
- `panihati_expenses` — Yatra expense tracking
- `panihati_yatra_offline_aggregates` — Offline entry summaries
- `panihati_yatra_combined_stats` — DB view (paid + offline entries)

## Dependencies
- **Kernel** — Config, DB connection, CSRF tokens

## Entry Points
| Type | URL | File | Description |
|------|-----|------|-------------|
| Public | `/yatra/panihati` | `content/panihati.php` | Registration form with travel mode toggle, price calculator |
| Admin | `/admin/panihati-yatra` | `Admin/panihati-yatra.php` | Dashboard with KPIs, charts |
| Admin | `/admin/panihati-records` | `Admin/panihati-records.php` | Registration records |
| Admin | `/admin/panihati-pricing` | `Admin/panihati-pricing.php` | Pricing management |
| Admin | `/admin/panihati-sadans` | `Admin/panihati-sadans.php` | Bhakti sadan management |
| Admin | `/admin/panihati-pickups` | `Admin/panihati-pickups.php` | Pickup location management |
| Admin | `/admin/panihati-reports` | `Admin/panihati-reports.php` | Download reports |
| Admin | `/admin/panihati-bulk-summary` | `Admin/panihati-bulk-summary.php` | Bulk offline entry |
| Admin | `/admin/panihati-expenses` | `Admin/panihati-expenses.php` | Finance & expenses |

## Business Rules
- Pricing is year-specific — current year pricing calculated by `getPanihatiPricing()`
- Two travel modes: Bus and Own Vehicle, with different pricing
- Kids may be free or reduced depending on year
- Bhakti sadan and pickup location dropdowns are DB-driven
- Offline entries track cash/offline payments alongside online Razorpay payments
- Bulk summary allows aggregate offline entries for multiple registrations

## Key Files
- `modules/Panihati/panihati-helpers.php` — Core helper functions (pricing, registration)
- `modules/Panihati/Admin/` — 8 admin management pages
- `modules/Panihati/TESTING.md` — Testing guide
