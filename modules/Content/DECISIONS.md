# Content Module — Architecture Decisions

> **Last updated:** 2026-07-11
> **Related:** `README.md` (module overview), `ARCHITECTURE.md` (wrapper convention)

---

## [2026-06-XX] File-Based Content Pages (Not DB-Driven)

### Decision
Static content pages (about, services, courses, contact, etc.) are PHP template files rather than database records. Each page is a standalone `.php` file that includes shared partials.

### Context
The Content module encompasses 46+ pages describing temple services, courses, about information, and other static content. These pages change infrequently (monthly or yearly) and each has unique layout and content requirements. A DB-driven approach would require a content management system with WYSIWYG editing.

### Options Considered
- **DB-driven pages**: Store content in database with rich text editor — over-engineered for infrequently changing content
- **File-based PHP templates (chosen)**: Each page is a PHP file — maximum flexibility, zero infrastructure
- **Markdown files**: Store as `.md`, render to HTML — adds rendering dependency

### Rationale
File-based PHP templates provide the maximum flexibility for unique page layouts. Each service page has its own HTML structure, imagery, and styling. A DB-driven CMS would either constrain layouts (template-based) or require storing raw HTML (defeating the purpose of DB storage). PHP files can be version-controlled and deployed like any other code.

### Trade-offs
- **No admin editing**: Content changes require PHP file edits and deployment
- **Duplicated structure**: Common layout patterns are repeated across files (mitigated by shared partials)
- **No content preview**: Changes go live immediately on deployment — no staging

### Related Files
- `modules/Content/content/services/` — 24 individual service page files
- `modules/Content/content/about/` — 8 about page files
- `modules/Kernel/partials/header.php` — Shared layout partial
- `modules/Kernel/partials/footer.php` — Shared layout partial

---

## [2026-06-XX] Dynamic Media Gallery (Directory Scanning)

### Decision
The photo gallery (`/darshan`) scans the `media/` directory dynamically at page load time, rather than maintaining a database of images with metadata.

### Context
The temple adds new photos to the gallery periodically. A DB-driven gallery would require an admin upload interface, image metadata management, and database storage of image paths. Directory scanning eliminates this overhead entirely.

### Options Considered
- **DB-driven gallery**: Images managed via admin UI with categories, descriptions — more control but more maintenance
- **File-based with manifest**: Images listed in a configuration file — requires manual updates when adding photos
- **Dynamic directory scan (chosen)**: Read directory contents at page load — zero maintenance, always up to date

### Rationale
Directory scanning requires zero administration — any image placed in the `media/` directory via FTP or file manager automatically appears in the gallery. The performance impact is negligible for the typical gallery size (< 100 images). Image metadata (name, date) is extracted from the file system.

### Trade-offs
- **No image metadata**: Cannot add descriptions, categories, or captions per image
- **No ordering control**: Images displayed in filesystem order (typically alphabetical by filename)
- **Performance**: Directory scan on every page load — though trivial for small directories
- **No access control**: Any file in the directory appears publicly — must be careful with uploads

### Related Files
- `modules/Content/content/darshan.php` — Gallery page with directory scanning
- `.htaccess` — Media directory access rules

---

## [2026-06-XX] Module Migration: Wrappers Preserve All URLs

### Decision
During the architecture migration (Phase 6), all content pages were moved to `modules/Content/content/` with backward-compatible wrappers at the original file paths.

### Context
Before modularization, content pages lived at the project root (`services/food-for-life.php`, `about/history.php`, `contact.php`). After migration to the Content module, these files became wrappers that delegate to the module directory. See `ARCHITECTURE.md` for the wrapper convention.

### Options Considered
- **In-place migration**: Keep files at original paths — no modular structure, defeats purpose
- **Move with redirects**: Move files and add 301 redirects — breaks existing bookmarks
- **Move with wrappers (chosen)**: Original paths become thin wrappers — all URLs work without changes

### Rationale
The wrapper pattern ensures zero broken links, zero SEO impact, and zero user-facing changes. All existing bookmarks, search engine indexes, and internal `include` calls continue to work. New code can reference the module path directly.

### Trade-offs
- **Dual file maintenance**: Each content page now has 2 files (wrapper + actual) — though wrappers are one-liners
- **Developer confusion**: Finding the actual file requires following the wrapper — mitigated by clear comments
- **Path complexity**: Relative includes must resolve through the wrapper's CWD — works automatically

### Related Files
- All root-level wrappers (e.g., `contact.php`, `services/food-for-life.php`)
- All module content files in `modules/Content/content/`
