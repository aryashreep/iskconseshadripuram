# Kernel Module — Architecture Decisions

All significant architecture and design decisions for the shared infrastructure.
Use this file to prevent future contributors (including AI) from accidentally
reversing deliberate trade-offs.

---

## [2026-06-15] PDO Singleton for Database Access

### Decision
Use a single PDO connection singleton (`getDB()`) shared across all modules.

### Context
The application runs on shared hosting with limited MySQL connections. Each page
load queries the database for causes, sevas, transactions, or content. Creating a
new PDO connection per request is wasteful; multiple concurrent users could
exhaust the connection limit.

### Options Considered
- **PDO Singleton**: One connection per request, reused across all includes
- **Connection per query**: Simple but wasteful on shared hosting
- **Connection pool**: Overkill for shared hosting, no infrastructure support

### Rationale
PDO singleton is the simplest approach that ensures connection reuse within a
single request. PHP's execution model (one request = one process) means there's
no cross-request state issue. The singleton is statically cached so multiple
`getDB()` calls from different modules return the same PDO instance.

### Trade-offs
- **No multi-DB support**: All modules share one database — good for simplicity
- **Global state**: Harder to test in isolation (singleton must be reset)
- **Connection limit**: If one query is slow, it blocks others on the same connection

### Related Files
- `includes/db.php` — PDO singleton implementation

---

## [2026-07-05] Session-Based Admin Auth (No JWT)

### Decision
Use PHP session-based authentication for the admin panel, not JWTs or OAuth.

### Context
The admin panel is a traditional server-rendered PHP app. Admins are temple staff
(not external API consumers). There's no mobile app, no API-only access, and no
third-party integrations that need token-based auth.

### Options Considered
- **Session-based**: Simple, secure (httpOnly cookies), built into PHP
- **JWT tokens**: Stateless but requires token storage on client side
- **OAuth2**: Overkill for an admin panel with 5 roles and ~10 users

### Rationale
Sessions are the natural choice for server-rendered PHP. They're httpOnly
(not accessible to JavaScript), simple to implement, and PHP handles all the
cookie management. JWT would add complexity (refresh tokens, expiration,
secure storage) with no benefit since there's no API-first consumer.

### Trade-offs
- **Server state**: Sessions consume memory on the server (negligible for < 10 admins)
- **No API token**: Future API clients would need separate auth
- **Session fixation**: Mitigated by regenerating session ID on login

### Related Files
- `src/Helpers/SessionGuard.php` — Auth guard class
- `admin/auth-check.php` — Auth gate for admin pages

---

## [2026-07-05] Role-Based Access Control (RBAC)

### Decision
Implement RBAC with 5 roles: super_admin, treasurer, editor, pujari, travel_agent.
Super_admin has implicit access to everything.

### Context
Different temple staff need access to different parts of the admin panel:
- Finance team (treasurer): donation reports, transaction logs
- Content team (editor): blogs, festivals, seva catalogue
- Pujaris: booking management
- Travel agents: Panihati Yatra management
- Temple leads (super_admin): everything

### Options Considered
- **Flat admin access**: Everyone sees everything — too permissive
- **Role-based (current)**: 5 roles with granular permissions
- **Permission-based**: Each action has a permission flag — over-engineered for 5 roles

### Rationale
The 5-role system maps directly to the temple's organizational structure.
`requireRole(['treasurer', 'super_admin'])` is simple, explicit, and easy to audit.
Super_admin bypasses all checks, which is important for emergencies.

### Trade-offs
- **Role granularity**: Adding a new role requires code changes
- **No permission inheritance**: Each role's access is specified independently
- **Role explosion risk**: Could lead to many roles if not managed

### Related Files
- `src/Helpers/SessionGuard.php` — `hasRole()`, `requireRole()` implementation
- `admin/partials/header.php` — Sidebar navigation with role checks

---

## [2026-07-05] CSRF Token on All State-Changing Operations

### Decision
Every form and GET-based destructive action requires a CSRF token validated with
`hash_equals()`. Tokens are generated on session start.

### Context
The admin panel performs state-changing operations (update donations, toggle
bookings, edit content). Without CSRF protection, an attacker could trick an
admin into performing actions via a malicious link or form.

### Options Considered
- **SameSite cookies**: Browser-level protection but not supported in all cases
- **CSRF token per session**: Single token, generated on login
- **CSRF token per form**: Unique token per form — more secure but complex

### Rationale
A single CSRF token per session is the standard PHP approach. The token is
generated with `random_bytes(32)` and validated with `hash_equals()` to prevent
timing attacks. SameSite cookies are also set as an additional layer.

### Trade-offs
- **Single token**: If stolen, all actions are vulnerable until session expires
- **No per-form token**: More secure but adds significant complexity
- **Token in URL**: GET-based toggles expose the token in URLs (mitigated by HTTPS)

### Related Files
- `includes/bootstrap.php` — Token generation on session start
- `admin/dashboard.php` — Token used in toggle-status links

---

## [2026-07-05] Cache-Busted Asset URLs via Manifest

### Decision
Use a `manifest.json` file to map original asset filenames to content-hashed
versions. The `asset()` helper reads the manifest and returns the hashed URL.
Falls back to the original path if no manifest exists.

### Context
Static assets (CSS, JS) are cached by browsers for up to 1 year (see .htaccess
Expires headers). Without cache busting, deployed changes wouldn't be picked up
by returning visitors.

### Options Considered
- **Query string versioning**: `style.css?v=1.0` — unreliable (proxies may ignore)
- **Content-hashed filenames**: `style.a1b2c3.css` — reliable, built by lightningcss
- **No cache busting**: Simple but changes require manual cache clearing

### Rationale
Content-hashed filenames generated by the build pipeline are the most reliable
approach. The manifest file is generated by `scripts/build.js` using
LightningCSS (CSS) and Terser (JS). The `asset()` helper provides a clean
interface so templates don't need to know about hashing.

### Trade-offs
- **Build step required**: Manifest must be regenerated on every deployment
- **Two reads**: The manifest is loaded once and cached in a static variable
- **Stale cache**: If manifest isn't regenerated, users see old files

### Related Files
- `includes/asset-helper.php` — `asset()` and `assetPath()` implementations
- `scripts/build.js` — Build pipeline
- `.htaccess` — Browser caching headers (1 year for css/js)
