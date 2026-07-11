# Donation Module — Database Schema

> **Last updated:** 2026-07-11
> **Related:** [`docs/DATABASE.md`](../../docs/DATABASE.md) (project-wide schema), [`MODULE_INDEX.md`](../../MODULE_INDEX.md)

## Owned Tables

### donation_causes
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| slug | VARCHAR(100) | URL-friendly unique identifier |
| title | VARCHAR(255) | Display name |
| short_title | VARCHAR(100) | Short display name for cards |
| description | TEXT | Long description |
| category | VARCHAR(50) | One of: festival, ekadashi, appearance, disappearance, event, service, construction, general |
| significance | TEXT | Religious significance |
| image_url | VARCHAR(255) | Banner/icon image path |
| is_active | TINYINT(1) | Soft delete flag (0=hidden, 1=visible) |
| is_featured | TINYINT(1) | Featured on homepage |
| is_time_bound | TINYINT(1) | Has start/end date |
| start_date | DATE | NULL if not time-bound |
| end_date | DATE | NULL if not time-bound |
| min_amount | DECIMAL(12,2) | Minimum donation amount |
| sort_order | INT | Display ordering |
| form_type | VARCHAR(50) | tiers, quantity, multi_item, cart, cart_qty |
| page_type | VARCHAR(50) | Content page type (festival, service, etc.) |
| page_slug | VARCHAR(100) | Reference to page file |
| feature_icon | VARCHAR(50) | Icon class |
| quick_stats | TEXT | Pipe-delimited: "Key: Value\|Key2: Value2" |
| content_body | TEXT | Full HTML content |
| meta_title | VARCHAR(255) | SEO title |
| meta_description | TEXT | SEO description |

### donation_transactions
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| cause_id | INT (FK, nullable) | → donation_causes.id |
| seva_id | INT (FK, nullable) | → donation_cause_sevas.id (legacy) |
| master_seva_id | INT (FK, nullable) | → master_sevas.id |
| donor_name | VARCHAR(255) | Donor full name |
| donor_email | VARCHAR(255) | Donor email |
| donor_phone | VARCHAR(20) | Donor phone number |
| donor_address | TEXT | Donor postal address |
| pan_number | VARCHAR(20) | PAN card for 80G receipt |
| amount | DECIMAL(12,2) | Donation amount in INR |
| currency | VARCHAR(3) | Default: INR |
| donation_mode | VARCHAR(20) | one_time, monthly |
| quantity | INT | Quantity (for brick/sqft sevas) |
| source_type | VARCHAR(50) | page, festival, admin_seed |
| source_slug | VARCHAR(100) | Slug of source page |
| source_url | VARCHAR(500) | Full URL of source |
| razorpay_order_id | VARCHAR(100) | Razorpay order reference |
| razorpay_payment_id | VARCHAR(100) | Razorpay payment reference |
| razorpay_signature | VARCHAR(255) | HMAC signature |
| payment_status | VARCHAR(20) | created, attempted, paid, failed, refunded |
| notes | TEXT | Donor notes |
| metadata_json | TEXT | JSON: browser info, utm params |
| subscription_id | INT (FK, nullable) | → donation_subscriptions.id |
| created_at | DATETIME | Auto-set on insert |
| updated_at | DATETIME | Auto-updated |

### master_seva_categories
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| slug | VARCHAR(100) | Unique identifier |
| name | VARCHAR(255) | Display name |
| sanskrit_name | VARCHAR(255) | Sanskrit name (if applicable) |
| description | TEXT | Description |
| icon | VARCHAR(50) | Font Awesome icon class |
| sort_order | INT | Display ordering |
| is_active | TINYINT(1) | Soft delete flag |

### master_sevas
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| slug | VARCHAR(100) | Unique identifier |
| name | VARCHAR(255) | Display name |
| sanskrit_name | VARCHAR(255) | Sanskrit name |
| description | TEXT | Full description |
| short_description | VARCHAR(500) | Card description |
| category_id | INT (FK) | → master_seva_categories.id |
| default_amount | DECIMAL(12,2) | Default donation amount |
| min_amount | DECIMAL(12,2) | Minimum allowed amount |
| max_amount | DECIMAL(12,2) | Maximum allowed amount |
| image_url | VARCHAR(255) | Icon/image path |
| icon | VARCHAR(50) | Font Awesome icon |
| allow_multiple | TINYINT(1) | Allow quantity selection |
| max_quantity | INT | Max purchasable quantity |
| is_featured | TINYINT(1) | Featured flag |
| is_active | TINYINT(1) | Soft delete flag |
| is_time_bound | TINYINT(1) | Seasonal availability |
| available_from | DATETIME | Season start |
| available_until | DATETIME | Season end |
| sort_order | INT | Display ordering |

### donation_cause_master_sevas (Pivot)
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| cause_id | INT (FK) | → donation_causes.id |
| master_seva_id | INT (FK) | → master_sevas.id |
| override_amount | DECIMAL(12,2) | NULL = use master default |
| override_description | TEXT | NULL = use master default |
| override_max_quantity | INT | NULL = use master default |
| is_featured | TINYINT(1) | Featured in cause |
| sort_order | INT | Display ordering |
| is_active | TINYINT(1) | Soft delete flag |

### donation_cause_sevas (Legacy)
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| cause_id | INT (FK) | → donation_causes.id |
| name | VARCHAR(255) | Seva name |
| amount | DECIMAL(12,2) | Donation amount |
| description | TEXT | Seva description |
| category_id | INT (FK) | → donation_seva_categories.id |
| sort_order | INT | Display ordering |

### donation_seva_categories (Legacy)
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| slug | VARCHAR(100) | Unique identifier |
| name | VARCHAR(255) | Display name |
| sanskrit_name | VARCHAR(255) | Sanskrit name |
| icon | VARCHAR(50) | Font Awesome icon |
| sort_order | INT | Display ordering |
| is_active | TINYINT(1) | Soft delete flag |

### donation_subscriptions
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK) | Auto-increment |
| donor_email | VARCHAR(255) | Subscriber email |
| amount | DECIMAL(12,2) | Monthly amount |
| cause_id | INT (FK, nullable) | → donation_causes.id |
| razorpay_subscription_id | VARCHAR(100) | Razorpay ref |
| subscription_status | VARCHAR(20) | active, paused, cancelled |
| created_at | DATETIME | Auto-set |

## Key Relationships

```
donation_causes.id ← donation_transactions.cause_id (FK, nullable)
donation_causes.id ← donation_cause_master_sevas.cause_id (FK)
donation_causes.id ← donation_cause_sevas.cause_id (FK, legacy)
donation_causes.id ← donation_subscriptions.cause_id (FK, nullable)

master_seva_categories.id ← master_sevas.category_id (FK)
master_sevas.id ← donation_cause_master_sevas.master_seva_id (FK)
master_sevas.id ← donation_transactions.master_seva_id (FK, nullable)

donation_seva_categories.id ← donation_cause_sevas.category_id (FK, legacy)
donation_cause_sevas.id ← donation_transactions.seva_id (FK, nullable, legacy)
```

## Reporting Hierarchy
```
Category (donation_causes.category)
    → Activity (donation_causes.title)
        → Seva (master_sevas.name)
```

Reports aggregate `donation_transactions` joined through this chain:
- Category report: `GROUP BY donation_causes.category`
- Activity report: `GROUP BY donation_causes.title`
- Seva report: `GROUP BY master_sevas.name` (via pivot tables)

## Legacy Notes
- `donation_cause_sevas` and `donation_seva_categories` are legacy tables
- New causes use the master catalog (`master_sevas` + `donation_cause_master_sevas`)
- `getCauseSevas()` implements dual-read: new catalog first, legacy fallback
- Never truncate `donation_transactions` in production
