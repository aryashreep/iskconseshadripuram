# Sudamaseva Module

Sudamaseva (also spelled Sudama Seva) is a devotional donation program inspired by the story of **Sudama Vipra**, Lord Krishna's childhood friend from the *Srimad Bhagavatam* (Canto 10, Chapters 80-81). The program allows devotees to make humble, recurring offerings to support the temple's activities.

## Module Structure

```
modules/Sudamaseva/
├── README.md
├── routes.php                           # Route definitions
├── src/
│   ├── SudamasevaRepository.php         # DB queries for donors, subscriptions, payments
│   └── SudamasevaService.php            # Business logic, formatting, receipt generation
├── content/                             # Public-facing pages
│   ├── index.php                        # Signup with mode toggle (Auto Monthly / Pay Monthly)
│   ├── lookup.php                       # Find existing donation by phone/legacy ID
│   ├── dashboard.php                    # Donor dashboard with installment grid, pay now
│   └── success.php                      # Post-payment success page
├── api/                                 # API endpoints
│   ├── create-subscription.php          # Auto Monthly: Creates Razorpay subscription
│   ├── verify-payment.php               # Auto Monthly: Verifies subscription payment
│   ├── webhook.php                      # Razorpay subscription webhook handler
│   ├── enroll.php                       # Pay Monthly: Creates donor + subscription + order
│   ├── create-order.php                 # Pay Monthly: Creates order for next installment
│   └── verify-order.php                 # Pay Monthly: Verifies order payment
├── Admin/                               # Admin panel pages (in progress)
├── assets/
│   └── css/sudamaseva.css               # Module-specific styles
└── migrations/
    ├── 001_create_tables.php            # Create module tables
    ├── 002_migrate_data.php             # Migrate data from old system (302 users, 3,278 payments)
    ├── 003_incremental_migration.php    # Post-migration fixes
    ├── 004_add_manual_payment_fields.php # Add legacy_id_no, collection_mode, payment_source, billing_month
    └── 005_backfill_legacy_ids.php      # Backfill legacy_id_no from old tbl_users
```

## Payment Modes

| Mode | Description | API Used | Payment Method |
|------|-------------|----------|----------------|
| **Auto Monthly** | Donor authorizes automated monthly charges | `create-subscription` + `verify-payment` | Razorpay Subscription (auto-debit) |
| **Pay Monthly** | Donor pays each month manually | `enroll` / `create-order` + `verify-order` | Razorpay Order (one-time, repeated) |

## Public URLs

| URL | Page | Description |
|-----|------|-------------|
| `/sudamaseva` | Signup | New donor registration with mode toggle |
| `/sudamaseva/lookup` | Lookup | Find existing donation by phone/legacy ID |
| `/sudamaseva/dashboard?donor_id=X` | Dashboard | Installment grid, payment history, pay now |
| `/sudamaseva/success` | Success | Post-payment confirmation |

## API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/sudamaseva/lookup` | POST | Search donor by phone or legacy ID |
| `/api/sudamaseva/enroll` | POST | Create manual enrollment (donor + subscription + order) |
| `/api/sudamaseva/create-subscription` | POST | Create Razorpay subscription (auto monthly) |
| `/api/sudamaseva/verify-payment` | POST | Verify subscription payment (HMAC: `{sub_id}|{pay_id}`) |
| `/api/sudamaseva/create-order` | POST | Create Razorpay order for next installment |
| `/api/sudamaseva/verify-order` | POST | Verify order payment (HMAC: `{order_id}|{pay_id}`) |
| `/api/sudamaseva/webhook` | POST | Razorpay subscription webhook handler |

## Database

Key tables with columns added in migration 004:

| Table | New Columns |
|-------|-------------|
| `sudamaseva_donors` | `legacy_id_no` (varchar(50), indexed) |
| `sudamaseva_subscriptions` | `collection_mode` (enum: recurring/manual) |
| `sudamaseva_payments` | `payment_source` (enum: subscription_charge/manual_order/migrated/admin_manual), `billing_month` (date) |

## Migrations

```bash
php modules/Sudamaseva/migrations/001_create_tables.php
php modules/Sudamaseva/migrations/002_migrate_data.php
php modules/Sudamaseva/migrations/003_incremental_migration.php
php modules/Sudamaseva/migrations/004_add_manual_payment_fields.php
php modules/Sudamaseva/migrations/005_backfill_legacy_ids.php
```
