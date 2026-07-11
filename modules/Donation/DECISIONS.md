# Donation Module — Architecture Decisions

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** [`README.md`](README.md) (module overview), [`API.md`](API.md) (API reference), [`DATABASE.md`](DATABASE.md) (schema), [`TASKS.md`](TASKS.md) (backlog), [`TESTING.md`](TESTING.md) (testing guide)
> **Project-wide:** [`SECURITY.md`](../../SECURITY.md) (payment security), [`CODING_STANDARDS.md`](../../CODING_STANDARDS.md) (patterns), [`WORKFLOWS.md`](../../WORKFLOWS.md) (donation workflows), [`MODULE_INDEX.md`](../../MODULE_INDEX.md) (module index), [`docs/DONATIONS.md`](../../docs/DONATIONS.md) (donation system)

All significant architecture and design decisions for this module.
Use this file to prevent future contributors (including AI) from accidentally
reversing deliberate trade-offs.

---

## [2026-07-03] Dual-Read Strategy for Seva Catalog

### Decision
New code reads from `master_seva_catalog` tables first. Falls back to legacy
`donation_cause_sevas` for causes not yet migrated. `getCauseSevas()` handles
this transparently.

### Context
The donation system has 74+ causes, each historically having its own set of sevas
in `donation_cause_sevas`. We built a master catalog (`master_sevas`) to
deduplicate and standardize seva offerings, but migrating all 74 causes at once
was too risky. The dual-read allows gradual, cause-by-cause migration.

### Options Considered
- **Big-bang migration**: Move all causes at once — high risk, potential downtime
- **Dual-read with fallback**: Read from new catalog first, fall back to old table
- **Copy-on-write**: Only use new catalog, migrate data in the background

### Rationale
Dual-read minimizes risk. Each cause can be migrated independently by adding
entries to `donation_cause_master_sevas`. Old causes continue to work with zero
downtime. The fallback is transparent to callers.

### Trade-offs
- **Performance**: Slightly slower queries (two reads for unmigrated causes)
- **Complexity**: Two code paths to maintain during migration period
- **Maintenance burden**: Legacy tables can't be dropped until all causes migrate

### Related Files
- `includes/donation-helpers.php` — Facade that uses dual-read internally
- DonationRepository.php — `getCauseSevas()` implementation

---

## [2026-06-15] No Framework Decision

### Decision
Continue using vanilla PHP with PDO, no framework (Laravel/Symfony/etc.).

### Context
Application runs on shared cPanel hosting with limited resources. No Composer
runtime, no queues, no caching layers. Adding a framework would require
infrastructure upgrades and introduce significant overhead.

### Options Considered
- **Laravel**: Too heavy for cPanel, requires artisan commands, queue workers
- **Slim Framework**: Lightweight but adds learning curve for future maintainers
- **Vanilla PHP**: Zero dependencies, full control, easy deployment

### Rationale
Vanilla PHP with PDO gives full SQL control with zero abstraction overhead.
The codebase is small enough that a framework's benefits (routing, ORM, auth)
don't justify the performance and complexity cost. Shared hosting can't run
artisan or queue workers anyway.

### Trade-offs
- **Dev speed**: More boilerplate for routing, auth, validation
- **Consistency risk**: Without framework conventions, team discipline is required
- **Migration path**: Framework adoption later would be a rewrite

### Related Files
- `config.php` — Application configuration
- `includes/db.php` — PDO singleton

---

## [2026-07-03] Razorpay as Payment Gateway

### Decision
Chose Razorpay as the sole payment gateway integration.

### Context
The temple is based in Bangalore, India. Razorpay is the dominant payment gateway
in India, handles PCI-DSS compliance, supports UPI/credit card/net banking, and
has a well-documented API with test mode. No other payment gateway was considered
necessary.

### Trade-offs
- **Vendor lock-in**: Migration to another gateway would require rewriting API endpoints
- **Fees**: Razorpay charges per-transaction fees (standard Indian rates)
- **Test mode**: Test keys (`rzp_test_*`) vs live keys (`rzp_live_*`) managed via env vars

### Related Files
- `api/create-order.php` — Order creation
- `api/verify-payment.php` — Signature verification
- `api/webhook.php` — Event handling

---

## [2026-07-05] Repository-Service-Renderer Pattern

### Decision
Separate DB access (Repository), business logic (Service), and HTML output
(Renderer) into distinct classes within the Donation module.

### Context
The donation system grew organically, with SQL queries, business logic, and HTML
templates mixed into the same helper functions in `includes/donation-helpers.php`.
As the system grew (74 causes, 363 sevas, 8 report types), this became
unmaintainable.

### Options Considered
- **Keep monolithic helpers**: Everything in one file — simple but unmaintainable
- **Repository-Service-Renderer pattern**: Split by concern with dependency injection
- **Active Record**: Let DB classes handle their own persistence — tied to MySQL

### Rationale
Repository-Service-Renderer provides clear separation of concerns. Repositories
handle all SQL, Services handle business rules, Renderers handle HTML. This
pattern is well-understood and allows testing each layer independently.

### Trade-offs
- **More files**: Three classes instead of one — but more navigable
- **Boilerplate**: Constructor injection and delegation methods
- **Learning curve**: New contributors need to understand the pattern

### Related Files
- DonationRepository.php — All DB queries
- DonationService.php — Business logic, formatting
- DonationRenderer.php — HTML rendering

---

## [2026-07-05] Legacy Facade for Backward Compatibility

### Decision
Keep `includes/donation-helpers.php` as a backward-compatible facade that
delegates to the new classes. All existing code continues to call global
functions (getDonationCauseBySlug, etc.) while new code uses the classes
directly.

### Context
The entire codebase (admin pages, public pages, API endpoints, partials) calls
functions like `getDonationCauseBySlug()`, `formatDonationAmount()`, and
`renderDonationCTA()`. Rewriting every call site was impractical.

### Rationale
The facade pattern allows gradual migration. Old code continues to work via
the facade functions. New code bypasses the facade and uses the classes directly.
Over time, call sites can be migrated one by one.

### Trade-offs
- **Maintenance**: The facade must be kept in sync with the underlying classes
- **Performance**: One extra function call per facade operation (negligible)
- **Awareness**: Developers might not know about the classes if the facade exists

### Related Files
- `includes/donation-helpers.php` — The facade

---

## [2026-07-05] Modular Monolith — Flat PSR-4 Structure

### Decision
Module PHP class files live directly in the module root (e.g.,
`modules/Donation/DonationRepository.php`), not in a `php/` subdirectory.

### Context
PSR-4 maps `Isjm\Modules\` to the `modules/` base directory. This means class
`Isjm\Modules\Donation\DonationRepository` resolves to
`modules/Donation/DonationRepository.php` — no extra subdirectory.

### Options Considered
- **Flat structure**: Files directly in module root — clean PSR-4 mapping, one mapping per prefix
- **php/ subdirectory**: Files in `modules/Donation/php/` — requires per-module namespace mappings

### Rationale
Flat structure means adding a new module requires zero configuration — just
create the directory and file, and PSR-4 resolves it automatically. No need to
edit `composer.json` for every new class.

### Trade-offs
- **Root directory clutter**: Module root has both code files and config files — manageable

### Related Files
- `composer.json` — PSR-4 mapping: `"Isjm\\Modules\\": "modules/"`
