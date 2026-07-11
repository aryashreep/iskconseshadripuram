# DATABASE.md — Schema & Tables

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `MODULE_INDEX.md` (table ownership), `modules/*/DATABASE.md` (module-specific schema)
> **See also:** [`SECURITY.md`](SECURITY.md) (prepared statements, SQL injection prevention)

## Connection

```php
$db = getDB(); // PDO singleton from config.php
```

Credentials via `.env`: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

## Complete Table Inventory (by Module Ownership)

### 🟢 Active Tables

| Module | Table | Key Columns | Purpose | Status |
|--------|-------|-------------|---------|--------|
| **Kernel** | `admins` | id, username, password_hash, role (DEPRECATED), full_name | Admin users | Active. `role` column deprecated — use `rbac_user_roles` |
| **Kernel** | `login_attempts` | id, ip_address, username, attempted_at, success | Rate limiting tracking | Active |
| **Donation** | `donation_causes` | id, slug, title, category (8 values), is_active, form_type, min_amount | 74 activities/festivals | Active |
| **Donation** | `donation_transactions` | id, cause_id, master_seva_id, seva_id, amount, payment_status, donor_name, donor_email, donor_phone, razorpay_* | Payment records | **NEVER TRUNCATE** |
| **Donation** | `master_seva_categories` | id, slug, name, icon, sort_order | 10 top-level seva groupings | Active |
| **Donation** | `master_sevas` | id, slug, name, category_id, default_amount, allow_multiple, max_quantity | 363+ deduplicated seva catalog | Active — single source of truth |
| **Donation** | `donation_cause_master_sevas` | cause_id, master_seva_id, override_amount, override_description | Pivot: cause ↔ seva | Active — preferred join path |
| **Donation** | `donation_subscriptions` | id, donor_email, amount, cause_id, razorpay_subscription_id | Recurring donation subscriptions | Active |
| **Booking** | `booking_pujas` | id, transaction_id (→ donation_transactions), puja_type, puja_date, status | Puja/Yagya bookings | Active |
| **Panihati** | `panihati_yatra_registrations` | id, name, phone, travel_mode, amount | Travel bookings | **NEVER TRUNCATE** |
| **Panihati** | `panihati_pricing` | id, year, bus_adult, bus_kid, vehicle_adult, vehicle_kid | Yearly pricing | Active |
| **Panihati** | `panihati_bhakti_sadans` | id, name, capacity, location | Sadhan options | Active |
| **Panihati** | `panihati_pickup_locations` | id, name, area, route | Pickup points | Active |
| **Panihati** | `panihati_expenses` | id, particulars, amount, category, date | Yatra expenses | Active |
| **Panihati** | `panihati_yatra_offline_aggregates` | id, sadan_name, adults, kids, amount, year | Offline entry summary | Active |
| **Panihati** | `panihati_yatra_combined_stats` | (DB view) id, name, travel_mode, amount, source | View: paid + offline entries | Active |
| **RBAC** | `rbac_roles` | id, slug, name, description, is_system, sort_order, is_active | 11 seeded roles, managed via UI | Active |
| **RBAC** | `rbac_permissions` | id, slug (module.action), module, action, label, sort_order | 55 permissions across 13 modules | Active |
| **RBAC** | `rbac_role_permissions` | role_id, permission_id | Role ↔ Permission M:N | Active |
| **RBAC** | `rbac_user_roles` | admin_id, role_id, assigned_by | Admin ↔ Role M:N (replaces `admins.role`) | Active |
| **Sudamaseva** | `sudamaseva_donors` | id, uuid, legacy_id_no, donor_name, phone (UNIQUE), email, pan | 302 migrated + new donors | Active |
| **Sudamaseva** | `sudamaseva_subscriptions` | id, donor_id, amount, collection_mode (recurring/manual), total_installments, installments_paid | Subscription plans | Active |
| **Sudamaseva** | `sudamaseva_payments` | id, subscription_id, amount, installment_number, payment_source, billing_month | 3,278 migrated + new payments | Active |
| **Sudamaseva** | `sudamaseva_receipts` | id, payment_id, receipt_no (SMS/YYYY/NNNNN), receipt_data (JSON), is_80g_eligible | 80G tax receipts | Active |
| **Blogs** | `blogs` | id, title, slug (UNIQUE), content_body, description, tags, banner_image, is_published, meta_title, meta_description | Blog posts | Active |

### 🟡 Legacy Tables (Backward Compatible — Don't Delete)

| Table | Module | Superseded By | Notes |
|-------|--------|---------------|-------|
| `donation_cause_sevas` | Donation | `master_sevas` + `donation_cause_master_sevas` | Dual-read fallback — remove when all causes migrated |
| `donation_seva_categories` | Donation | `master_seva_categories` | Legacy category table — remove after full migration |
| `admins.role` column | Kernel | `rbac_user_roles` | Column is still present but deprecated |

### Key Relationships

```
# Donation Hierarchy
donation_causes.id ← donation_transactions.cause_id (FK, nullable)
donation_causes.id ← donation_cause_master_sevas.cause_id (FK)
master_seva_categories.id ← master_sevas.category_id (FK)
master_sevas.id ← donation_cause_master_sevas.master_seva_id (FK)
master_sevas.id ← donation_transactions.master_seva_id (FK, nullable)

# Booking Linkage
booking_pujas.transaction_id → donation_transactions.id

# RBAC Relationships
rbac_user_roles.admin_id → admins.id (CASCADE on delete)
rbac_user_roles.role_id → rbac_roles.id (CASCADE on delete)
rbac_role_permissions.role_id → rbac_roles.id (CASCADE on delete)
rbac_role_permissions.permission_id → rbac_permissions.id (CASCADE on delete)

# Sudamaseva Relationships
sudamaseva_donors.id ← sudamaseva_subscriptions.donor_id
sudamaseva_subscriptions.id ← sudamaseva_payments.subscription_id
sudamaseva_payments.id ← sudamaseva_receipts.payment_id
```

### Reporting Hierarchy

```
Category (donation_causes.category: festival, ekadashi, etc.)
    → Activity (donation_causes.title: Rath Yatra, Janmashtami, etc.)
        → Seva (master_sevas.name via donation_cause_master_sevas pivot)
```

## Key Relationships

```
donation_transactions.cause_id → donation_causes.id
donation_transactions.master_seva_id → master_sevas.id (nullable)
donation_transactions.seva_id → donation_cause_sevas.id (nullable, legacy)
master_sevas.category_id → master_seva_categories.id
donation_cause_master_sevas.cause_id → donation_causes.id
donation_cause_master_sevas.master_seva_id → master_sevas.id
booking_pujas.transaction_id → donation_transactions.id

# RBAC Relationships
rbac_user_roles.admin_id → admins.id (CASCADE on delete)
rbac_user_roles.role_id → rbac_roles.id (CASCADE on delete)
rbac_user_roles.assigned_by → admins.id (SET NULL on delete)
rbac_role_permissions.role_id → rbac_roles.id (CASCADE on delete)
rbac_role_permissions.permission_id → rbac_permissions.id (CASCADE on delete)
```

## Database Access Rules

1. **Always use prepared statements** — no string concatenation of user input into SQL
2. **All DB access through `getDB()`** — PDO singleton from `config.php`
3. **Dynamic `ORDER BY`** must use an allowlist of valid column names
4. **Never truncate** `donation_transactions` or `panihati_yatra_registrations` in production
5. **Legacy tables** (`donation_cause_sevas`, `donation_seva_categories`, `admins.role`) are backward-compatible — don't delete
6. **Table ownership** by module is defined in `MODULE_INDEX.md`

## Data Integrity Rules

| Table | Integrity Rule |
|-------|---------------|
| `donation_transactions` | Never delete — always use `payment_status` for status changes |
| `panihati_yatra_registrations` | Never delete in production — financial audit trail |
| `admins` | Only super_admin can delete admin accounts |
| `rbac_roles` | System roles (`is_system=1`) cannot be deleted via UI |
| `rbac_permissions` | System permissions cannot be deleted via UI |
| `sudamaseva_donors` | Phone is unique identifier — deduplicated on insert |

## Migration Pattern

```php
require_once __DIR__ . '/../../config.php';
$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Idempotent: check before insert
$check = $db->prepare("SELECT COUNT(*) FROM table WHERE slug = ?");
$check->execute([$slug]);
if ($check->fetchColumn() > 0) { /* skip */ }

// Run via: php database/migrations/your-migration.php
```

### RBAC Migrations

```bash
# Create RBAC tables
php modules/RBAC/database/migrations/001_create_rbac_tables.php

# Seed roles, permissions, and role-permission matrix
php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php

# Migrate existing admin users to RBAC roles
php modules/RBAC/database/migrations/003_migrate_existing_admins.php
```

## Rules

- **Never truncate** `donation_transactions` or `panihati_yatra_registrations` in production
- Use prepared statements for all queries
- Legacy tables (`donation_cause_sevas`, `donation_seva_categories`, `admins.role`) are backward-compatible — don't delete
