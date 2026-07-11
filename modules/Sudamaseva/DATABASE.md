# Sudamaseva — Database Schema

## Owned Tables

### sudamaseva_donors
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK, AI) | |
| uuid | VARCHAR(36) | UUID for future devotee care cross-reference |
| legacy_id_no | VARCHAR(50) | Backfilled from old tbl_users.id_no (≈302 records). Indexed. |
| donor_name | VARCHAR(255) | |
| phone | VARCHAR(15) | Unique identifier, indexed |
| email | VARCHAR(255) | Nullable |
| pan | VARCHAR(20) | Nullable |
| area | VARCHAR(255) | |
| city | VARCHAR(255) | |
| state | VARCHAR(255) | |
| source | VARCHAR(50) | Default: `migrated` or `sudamaseva` |
| notes | TEXT | |
| status | ENUM('active','inactive','paused') | Default: 'active' |
| created_at | DATETIME | |
| updated_at | DATETIME | |

Indexes: `phone` (UNIQUE), `email`, `status`, `source`, `legacy_id_no`

### sudamaseva_subscriptions
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK, AI) | |
| donor_id | INT (FK) | → sudamaseva_donors.id |
| amount | INT | Monthly amount in paise (min ₹51) |
| razorpay_subscription_id | VARCHAR(255) | Nullable for manual subscriptions |
| razorpay_plan_id | VARCHAR(255) | Nullable for manual |
| status | ENUM('active','completed','paused','cancelled') | |
| start_date | DATETIME | |
| end_date | DATETIME | Nullable |
| total_installments | INT | |
| collection_mode | ENUM('recurring','manual') | `recurring`=auto-debit, `manual`=pay monthly |
| installments_paid | INT | |
| source | ENUM('migrated','new') | |
| old_user_id | INT | Reference to old tbl_users.id |

### sudamaseva_payments
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK, AI) | |
| subscription_id | INT (FK, nullable) | → sudamaseva_subscriptions.id |
| donor_id | INT (FK, nullable) | → sudamaseva_donors.id |
| amount | INT | Amount in paise |
| installment_number | INT | 0 = orphan/unlinked |
| razorpay_payment_id | VARCHAR(255) | UNIQUE |
| razorpay_order_id | VARCHAR(255) | |
| payment_status | ENUM('created','attempted','paid','failed') | |
| payment_date | DATETIME | |
| payment_source | ENUM('subscription_charge','manual_order','migrated','admin_manual') | |
| billing_month | DATE | Billing period for aggregation |
| receipt_number | VARCHAR(50) | |
| is_migrated | TINYINT(1) | |

### sudamaseva_receipts
| Column | Type | Notes |
|--------|------|-------|
| id | INT (PK, AI) | |
| payment_id | INT (FK) | → sudamaseva_payments.id |
| receipt_no | VARCHAR(50) | Format: SMS/YYYY/NNNNN |
| receipt_date | DATETIME | |
| receipt_data | JSON | Full receipt details |
| is_80g_eligible | TINYINT(1) | |

## Key Relationships
```
sudamaseva_donors.id ← sudamaseva_subscriptions.donor_id
sudamaseva_subscriptions.id ← sudamaseva_payments.subscription_id
sudamaseva_payments.id ← sudamaseva_receipts.payment_id
```
