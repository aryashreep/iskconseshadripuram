# Festivals Module — Architecture Decisions

> **Last updated:** 2026-07-11
> **Related:** `README.md` (module overview), `.htaccess` (URL routing), `WORKFLOWS.md` (festival publishing flow)

---

## [2026-06-XX] Hybrid Static + DB-Driven Festival Pages

### Decision
Festival pages use a hybrid approach: hardcoded `.php` files for festivals with unique editorial content, and a dynamic `detail.php` page for DB-driven common layouts. The `.htaccess` routing checks for a `.php` file first, then falls back to the dynamic handler.

### Context
The festival system has ~70 pages. Some festivals (like Janmashtami, Rath Yatra) have extensive custom content, photo galleries, and multi-section layouts that don't fit a template. Others (like Ekadashi days) follow a consistent pattern and can be rendered from database content.

### Options Considered
- **All static PHP files**: 70+ individual files — high maintenance, duplicated code, but maximum flexibility
- **All DB-driven**: Single template renders all festivals — consistent but limited for unique content
- **Hybrid (chosen)**: Static files for unique pages, DB-driven for template-based pages

### Rationale
The hybrid approach provides flexibility for major festivals with unique content while keeping the maintenance burden low for the 50+ smaller festivals (Ekadashis, appearance/disappearance days) that follow predictable patterns. The routing logic is straightforward: check file existence, then fall back.

### Trade-offs
- **Routing complexity**: `.htaccess` must check PHP file existence for each category — adds rewrite rules
- **Inconsistent editing**: Some festivals edited via PHP files, others via admin DB interface
- **Discovery**: Developers must check both static files and DB to find all festival content

### Related Files
- `.htaccess` — Routing rules for each festival category
- `modules/Festivals/content/detail.php` — DB-driven fallback handler
- `modules/Festivals/content/grand-festivals/` — Static festival files

---

## [2026-06-XX] Category-Based Routing (Not Slug-Based)

### Decision
Festivals are organized into 5 URL categories (grand-festivals, ekadashi, appearance, disappearance, events) with separate `.htaccess` rewrite rules per category, rather than a single `/festivals/{slug}` routing rule.

### Context
Each festival category has different presentation needs, SEO URLs, and content relationships. A flat slug-based routing would lose the categorical context that helps both search engines and users understand the festival type.

### Options Considered
- **Single `/festivals/{slug}` route**: Simpler routing but loses category context
- **Category-based routing (chosen)**: `/festivals/{category}/{slug}` with separate rules per category
- **Query parameter**: `/festivals?category=grand-festivals&slug=janmashtami` — less SEO-friendly

### Rationale
Category-based URLs provide meaningful SEO hierarchies (`/festivals/ekadashi/putrada` clearly indicates the festival type), allow category-specific styling/behavior, and separate routing rules that can each have different caching or redirect behavior.

### Trade-offs
- **5x routing rules**: Each category needs its own `.htaccess` RewriteCond/RewriteRule block
- **Inconsistent errors**: Invalid slug in one category may behave differently than another
- **Harder to add new category**: Requires new `.htaccess` rules and directory

### Related Files
- `.htaccess` — All festival routing rules
- `modules/Festivals/Admin/festivals.php` — Festival management
- `modules/Donation/DonationRepository.php` — `donation_causes` queries

---

## [2026-06-XX] Shared `donation_causes` Table for Festival Data

### Decision
Festival content metadata (title, description, images, SEO fields) is stored in the `donation_causes` table alongside donation data, rather than having a separate `festivals` table.

### Context
Many festivals have associated donation causes — in fact, the festival and the donation cause are often the same entity (e.g., "Janmashtami" is both a festival page and a donation category). Maintaining separate tables would require duplicating data and keeping them in sync.

### Options Considered
- **Separate `festivals` table**: Clean separation but duplicate data for linked entities
- **Shared table (chosen)**: `donation_causes` serves both festival content and donation categories
- **DB view**: Create festival-specific view from `donation_causes` — adds abstraction layer

### Rationale
A shared table avoids data duplication and synchronization issues. Columns like `content_body`, `image_url`, `meta_title`, and `meta_description` serve both festival display and donation cause presentation. The `category` field (festival, ekadashi, appearance, etc.) effectively partitions the data.

### Trade-offs
- **Schema coupling**: Changes for donation affect festival display and vice versa
- **Nullable columns**: Some festival-specific columns are NULL for pure donation causes
- **Query complexity**: Must filter by category to get festival vs donation rows

### Related Files
- `database/schema.sql` — `donation_causes` table definition
- `modules/Donation/DonationRepository.php` — DB queries
- `modules/Festivals/content/detail.php` — Festival display
