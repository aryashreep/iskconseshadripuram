# DATABASE.md — Schema & Tables

## Connection

```php
$db = getDB(); // PDO singleton from config.php
```

Credentials via `.env`: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

## Core Tables

### Donation System

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `donation_causes` | id, slug, title, category, is_active | Activities/festivals (74 rows) |
| `donation_transactions` | id, cause_id, seva_id, master_seva_id, amount, payment_status, donor_name, donor_email | Payment records |
| `master_seva_categories` | id, slug, name, icon, sort_order | 10 top-level seva groupings |
| `master_sevas` | id, slug, name, category_id, default_amount, allow_multiple, max_quantity | Deduplicated seva catalog |
| `donation_cause_master_sevas` | cause_id, master_seva_id, override_amount | Pivot: links causes to sevas |
| `donation_cause_sevas` | id, cause_id, name, amount, category_id | Legacy per-cause seva table |
| `donation_seva_categories` | id, slug, name | Legacy seva category table |

### Booking System

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `booking_pujas` | id, transaction_id, puja_type, puja_date, status | Puja/Yagya bookings |

### Yatra

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `panihati_yatra_registrations` | id, name, phone, travel_mode, amount | Travel bookings |

### Admin

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `admins` | id, username, password_hash, role, full_name | Admin users (legacy `role` column deprecated — use RBAC) |

### RBAC System

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `rbac_roles` | id, slug, name, description, is_system, sort_order, is_active | Role definitions (11 seeded roles, data-driven via UI) |
| `rbac_permissions` | id, slug, module, action, label, sort_order | Permission definitions (55 across 13 modules) |
| `rbac_role_permissions` | role_id, permission_id | Many-to-many: which permissions each role has |
| `rbac_user_roles` | admin_id, role_id, assigned_by | Many-to-many: which roles each admin has (replaces `admins.role` column) |

### Content

| Table | Key Columns | Purpose |
|-------|-------------|---------|
| `blogs` | id, title, content, is_published | CMS blog posts |

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
