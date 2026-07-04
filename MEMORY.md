# ISJM Project Memory

## Project Overview
ISKCON Jagannath Mandir (ISKM) - A PHP-based temple website for donations, sevas, and temple services.

## Tech Stack
- **Language**: PHP (vanilla, no framework)
- **Database**: MySQL (via PDO)
- **Server**: Laragon (local development)
- **URL**: http://isjm.test:8080

## Architecture Decisions

### Seva Catalogue System (2026-07-03)
**Master Catalog Pattern**: Reusable library of seva offerings linked to festivals/causes.

**Database Schema**:
- `master_seva_categories` - 10 top-level categories (id, slug, name, sanskrit_name, icon, sort_order, is_active)
- `master_sevas` - Single source of truth (id, slug, name, category_id FK, default_amount, allow_multiple, max_quantity, is_active)
- `donation_cause_master_sevas` - Pivot table linking causes to sevas with override support (cause_id, master_seva_id, override_amount, override_description)
- `donation_transactions.master_seva_id` - Nullable FK for backward compatibility

**Key Files**:
- `/database/migrations/create_master_seva_catalog.php` - Core migration creating normalized tables
- `/database/migrations/seed_rath_yatra_master_sevas.php` - Seeds 11 Rath Yatra sevas (category_id=4)
- `/database/migrations/seed_missing_master_sevas.php` - Copies sevas from reference causes to causes that have none

**Admin CRUD**:
- `/admin/seva-catalogue.php` - List page with accordion grouped by category, toggle active/featured, archive
- `/admin/seva-catalogue-edit.php` - Create/edit form with auto-generated slug
- `/admin/ajax/master-sevas-by-category.php` - AJAX endpoint for dynamic seva loading
- `/admin/festival-edit.php` - Festival editor with Master Seva Catalog picker panel

**Helper Functions** (`/includes/donation-helpers.php`):
- `hasMasterCatalogSevas($causeId)` - Check if cause has active master seva links
- `getMasterSevaCategories()` / `getMasterSevas()` - Fetch categories/sevas
- `getCauseSevasGrouped($causeId)` / `getCauseSevas($causeId)` - Dual-read: new master catalog first, fallback to legacy
- `createMasterSeva($data)` / `updateMasterSeva($id, $data)` - CRUD operations
- `archiveMasterSeva($id, $hardDelete)` - Soft-delete or hard-delete if unused

**Dual-Read Strategy**: `getCauseSevas()` tries new master catalog first, falls back to legacy `donation_cause_sevas` table for causes not yet migrated.

### Donation System
- **Donation helpers** in `/includes/donation-helpers.php` provide reusable functions
- **Cause-to-seva linking** via `donation_cause_master_sevas` table
- **Festival edit page** (`/admin/festival-edit.php`) has Master Seva Catalog picker
- **Payment integration** via Razorpay in `/api/create-order.php`
- **Public donation page** at `/donate/donate-seva.php` renders customer-facing form

## Discovered Durable Knowledge

### File Structure
- **Admin panel**: `/admin/` directory with auth-check, partials, and ajax endpoints
- **AJAX endpoints**: `/admin/ajax/master-sevas-by-category.php` for dynamic seva loading
- **Services**: `/services/krishna-sevaka.php` (Siksha Level 2 page, not part of catalogue)
- **Donations**: `/donate/donate-seva.php` and `/donate/` directory
- **Payment**: `/api/create-order.php` - Razorpay integration

### Database Tables
| Table | Purpose |
|---|---|
| `master_seva_categories` | 10 top-level categories |
| `master_sevas` | Single source of truth for all seva offerings |
| `donation_cause_master_sevas` | Links causes/festivals to sevas with override support |
| `donation_transactions.master_seva_id` | Nullable FK for backward compatibility |
| `donation_cause_sevas` (legacy) | Old per-cause seva table, still functional |
| `donation_seva_categories` (legacy) | Old seva category table |

### Legacy System
- `seva.php` in project root is deprecated, redirects to `index.php`
- Old `donation_cause_sevas` table preserved for backward compatibility
- Dual-read strategy handles migration seamlessly

## Patterns

### Migration Pattern
- Idempotent migrations (safe to re-run)
- Use `require_once __DIR__ . '/../../config.php'` for database access
- Check for existence before inserting (prevent duplicates)
- Step-by-step approach: verify category exists → define sevas → insert/update

### Admin Navigation
- Active page state tracked via `$activePage` variable
- Role-based access: `requireRole(['super_admin', 'editor'])`
- CSRF tokens required for state-changing operations (toggle_active_id, etc.)

### Database Pattern
- PDO with prepared statements throughout
- `getDB()` helper from `config.php` for all connections
- Error handling via try-catch with `error_log()`

## Pending Work

### Outreach Sevas (Requested 2026-07-03)
User requested adding these sevas to the Outreach Sevas category:
- Bhagavad Gita Distribution
- Book Distribution
- Children Gift Seva
- College Preaching Sponsor
- Harinam Sponsor
- Digital Preaching
- Temple Publications
- Festival Publicity

**Status**: Task was explored but not completed. Needs to be continued.

## Gotchas

- `seva.php` in project root is a redirect to `index.php` (deprecated)
- Admin panel requires authentication via `auth-check.php`
- CSRF tokens required for state-changing operations

## Rules

- All database operations should go through `getDB()` helper from `config.php`
- Use prepared statements for all SQL queries
- Follow existing migration patterns for new seva additions
- Maintain backward compatibility with old `donation_cause_sevas` table

---

*Last updated: 2026-07-03*
*Source sessions: ses_0d7091a7affeQk2oM4uFNAFSkx, ses_0d7091a24ffeUqz4eU3O18ioVh*
