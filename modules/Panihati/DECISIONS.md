# Panihati Module — Architecture Decisions

> **Last updated:** 2026-07-11
> **Related:** `README.md` (module overview), `TESTING.md` (testing guide), `docs/DATABASE.md` (table ownership)

---

## [2026-06-XX] Separate Tables per Concern Rather Than Single Monolithic Table

### Decision
Create separate database tables for each Panihati domain concept (pricing, sadans, pickups, expenses, offline aggregates) rather than one large table with type-discriminator columns.

### Context
Panihati Yatra involves multiple independent data domains: pricing (changes yearly), accommodation options (bhakti sadans), pickup locations (multiple routes), expense tracking, and offline entry aggregation. Mixing these into one table would create a wide, sparse schema with many nullable columns.

### Options Considered
- **Single table with type column**: One `panihati_data` table with `type` discriminator — simpler queries but wider schema
- **Separate tables per domain (chosen)**: Each concept has its own table with focused columns
- **JSON column for flexible data**: MySQL JSON column for varying metadata — loses referential integrity

### Rationale
Separate tables provide clear column constraints per domain, better referential integrity, simpler indexing, and clearer code paths. The DB view `panihati_yatra_combined_stats` unifies across tables when needed.

### Trade-offs
- **More tables**: 7 tables instead of 1-2 — but each is simpler and more focused
- **Joins required**: Combined queries need UNION or view — mitigated by `combined_stats` view
- **Migration overhead**: Schema changes must be applied to multiple tables

### Related Files
- `database/migrations/create_panihati_table.php` — Core tables
- `database/migrations/create_panihati_expenses.php` — Expenses table
- `database/migrations/create_panihati_dynamic_tables.php` — Sadans, pickups, pricing

---

## [2026-06-XX] Year-Specific Pricing (Not Universal Pricing)

### Decision
Pricing is configured per-year, not as a universal price list. Each year's rates for bus/vehicle adults and kids are stored separately.

### Context
Yatra costs (fuel, accommodation, food) change yearly. Previous year's pricing should remain in the database for historical reporting, while current year uses new rates. The `getPanihatiPricing()` function calculates current year pricing dynamically.

### Options Considered
- **Universal pricing**: Single set of prices, overwrite each year — loses historical data
- **Year-specific (chosen)**: Each year has its own pricing row — preserves history
- **Percentage adjustment**: Base price + yearly percentage modifier — adds calculation complexity

### Rationale
Year-specific pricing gives clear audit trail for financial reporting. Admin can set pricing for upcoming yatra without affecting past records. The pricing function defaults to current year but supports querying any year.

### Trade-offs
- **Must maintain**: Admin must set pricing for each new yatra year
- **No automatic inflation**: Price increases must be manually configured
- **Query complexity**: Reports must specify which year's pricing to use

### Related Files
- `modules/Panihati/panihati-helpers.php` — `getPanihatiPricing()` function
- `modules/Panihati/Admin/panihati-pricing.php` — Pricing management UI

---

## [2026-06-XX] Offline Entries Alongside Online Payments

### Decision
Support both online (Razorpay) and offline (cash/cheque) payment registration within the same system, using separate aggregate tables for offline entries.

### Context
Not all yatra participants pay online — many donate cash or by cheque at the temple office. The system needs to track both payment methods to provide a complete picture of registrations and collections.

### Options Considered
- **Online-only**: Only track Razorpay payments — misses significant portion of registrations
- **Single table with payment_mode**: Store all in `panihati_yatra_registrations` with payment mode flag
- **Separate aggregates for offline (chosen)**: Online in main table, offline in aggregate summary tables

### Rationale
Offline entries are typically entered as aggregates (e.g., "Bank of Ireland group: 25 adults, 10 kids, ₹35,000") rather than individual records. The aggregate approach matches the real-world workflow where temple staff receive bulk cash/cheque donations. The DB view `panihati_yatra_combined_stats` provides unified reporting.

### Trade-offs
- **Dual maintenance**: Two entry paths (online form + admin offline entry)
- **Reconciliation effort**: Must ensure offline + online totals match bank deposits
- **No per-devotee tracking**: Offline entries record group data, not individual devotee details

### Related Files
- `modules/Panihati/Admin/panihati-bulk-summary.php` — Bulk offline entry
- `database/create_panihati_tables.php` — `panihati_yatra_offline_aggregates` table
