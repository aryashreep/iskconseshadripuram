# SECURITY.md — Security Policy & Implementation Guide

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `docs/SECURITY_CHECKLIST.md`, `docs/AUTHORIZATION_MATRIX.md`, `docs/AUDIT_LOGGING.md`, `docs/FILE_UPLOADS.md`

---

## Table of Contents

1. [Security Principles](#1-security-principles)
2. [Threat Model](#2-threat-model)
3. [OWASP Top 10 Mapping](#3-owasp-top-10-mapping)
4. [Authentication](#4-authentication)
5. [Authorization / RBAC](#5-authorization--rbac)
6. [Input Validation](#6-input-validation)
7. [CSRF Protection](#7-csrf-protection)
8. [XSS Prevention](#8-xss-prevention)
9. [SQL Injection Prevention](#9-sql-injection-prevention)
10. [File Upload Security](#10-file-upload-security)
11. [Webhook Security](#11-webhook-security)
12. [Payment Security](#12-payment-security)
13. [Session Security](#13-session-security)
14. [Audit Logging](#14-audit-logging)
15. [Environment & Secrets](#15-environment--secrets)
16. [Security Headers](#16-security-headers)
17. [Known Gaps & Future Hardening](#17-known-gaps--future-hardening)

---

## 1. Security Principles

These principles govern all security decisions in this project:

| Principle | Application |
|-----------|-------------|
| **Defense in Depth** | Multiple layers of security — server validation + client validation + RBAC + CSRF + prepared statements |
| **Least Privilege** | Admin users receive only the permissions required for their role. No blanket access. |
| **Secure by Default** | Features default to the most restrictive state. Admin pages require explicit permission. |
| **Fail Closed** | If a security check fails (e.g., missing permission, invalid CSRF, failed signature), access is denied — not granted. |
| **Never Trust User Input** | All `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`, `$_FILES`, and `$_COOKIE` values are untrusted until validated. |
| **Minimize Attack Surface** | No unnecessary endpoints, debug pages, or exposed internals. |
| **Backward Compatible Security** | Security improvements must not break existing functionality — use graceful migration paths. |
| **No Card Data** | PCI-DSS scope is minimized by using Razorpay for all payment processing. No credit card data is stored on our server. |

---

## 2. Threat Model

### Key Risks

| Risk | Impact | Likelihood | Current Mitigation |
|------|--------|------------|-------------------|
| SQL injection via donation forms | Data breach | Low | PDO prepared statements everywhere |
| CSRF on admin state-changing actions | Unauthorized modifications | Low | CSRF tokens on all forms + GET actions |
| XSS via blog/festival content | Session theft, defacement | Medium | `htmlspecialchars()` on output |
| IDOR on donor dashboard | Unauthorized access to donor data | Medium | Query param-based access (no auth wall) |
| Rate limiting bypass on login | Brute force | Low | 5 attempts / 15-min window per IP |
| Razorpay webhook spoofing | Fake payment records | Low | HMAC-SHA256 signature verification |
| Legacy wrapper path traversal | Code execution | Very low | Wrappers are hardcoded paths |
| Exposed error messages | Information disclosure | Low | `display_errors = 0` in production |
| CSV injection in exports | Excel/malware delivery | Low | Proper escaping in CSV output |

### Assets to Protect

| Asset | Sensitivity | Protection |
|-------|-------------|------------|
| Admin credentials | Critical | bcrypt hashing, rate limiting |
| Donation transaction records | High | Prepared statements, no direct exposure |
| Donor PII (name, phone, email, PAN) | High | Stored in DB, displayed only in admin + donor dashboard |
| Razorpay API keys | Critical | Environment variables only, never in code |
| Session tokens | High | httpOnly cookies, session regeneration |
| RBAC permissions | Medium | Server-enforced, no client-side trust |

---

## 3. OWASP Top 10 Mapping

### A01: Broken Access Control

**How it applies:** Admin pages, API endpoints, and donor-facing pages must all enforce access control. Donor dashboard uses query-param-based access (weaker).

**Current controls:**
- RBAC with 11 roles, 55 permissions across 13 modules
- `requirePermission('module.action')` on every admin page
- `hasPermission()` for UI visibility (sidebar, action buttons)
- Super admin bypass — implicit permission for all checks
- Admin edit/delete actions protect with CSRF + permission check

**Required standards for new code:**
- Every admin page must call `requirePermission()` or `hasPermission()` — never skip access control
- Donor-facing pages with sensitive data should implement stronger auth (not just `?donor_id=X`)
- Export endpoints must check `module.export` permission
- AJAX/admin endpoints must enforce the same permissions as their parent pages
- No security by hidden UI — always enforce at the server level

**Current gaps:**
- Donor dashboard (`/sudamaseva/dashboard?donor_id=X`) uses only query param access — no authentication or token
- Some GET-based destructive actions still exist (e.g., `?action=delete`) with CSRF token in URL
- No rate limiting on donation/subscription API endpoints

---

### A02: Cryptographic Failures

**How it applies:** Payment data, donor PII, admin credentials, API keys.

**Current controls:**
- No credit card data stored (Razorpay handles PCI compliance)
- HTTPS enforced by `.htaccess` HSTS header
- Razorpay HMAC-SHA256 signature verification on all payment callbacks
- Passwords hashed with `password_hash(PASSWORD_DEFAULT)` — bcrypt
- Session cookies are httpOnly (no JS access)
- API keys loaded from environment variables only — never hardcoded
- `random_bytes(32)` used for CSRF token generation

**Required standards for new code:**
- Never hardcode secrets — always use environment variables
- Use `password_hash()` and `password_verify()` for all password operations
- All payment signatures must use HMAC-SHA256
- Session IDs must be regenerated on login (`session_regenerate_id(true)`)
- No card data in logs, URLs, or error messages

**Current gaps:**
- No encryption at rest for sensitive PII (PAN numbers, phone numbers)
- No CSPRNG usage verification beyond CSRF tokens
- `.env` file in project root could be served if `.htaccess` fails

---

### A03: Injection

**How it applies:** SQL queries, HTML output, CSV exports, shell commands.

**Current controls:**
- **SQL**: PDO prepared statements (`prepare()` + `execute()`) — no string concatenation with user input
- **HTML**: `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` on all user-generated content output
- **CSV**: Values are escaped for CSV format
- **No shell execution**: No `exec()`, `system()`, `shell_exec()` in the codebase

**Required standards for new code:**
- All SQL must use prepared statements — no exceptions
- Dynamic `ORDER BY` must use an allowlist of valid column names
- Output must be escaped according to context (HTML, JSON, CSV, XML)
- Never pass user input directly to `preg_replace()` with `/e` modifier

**Current gaps:**
- Some `ORDER BY` fields may be concatenated into SQL — audit needed (see `admin/dashboards.php`, admin report files)
- `filter_var()` with `FILTER_SANITIZE_URL` is used but could be stricter

---

### A04: Insecure Design

**How it applies:** Business logic flaws in donation amounts, booking validation, installment sequencing.

**Current controls:**
- Donation amounts verified server-side against the catalog (never trust client-sent amounts)
- Razorpay order amounts are set by the server, not the client
- Installments must be paid sequentially (no skipping ahead in Sudamaseva)
- Rate-limited login (5 attempts per 15-min window per IP, stored in `login_attempts` table)

**Required standards for new code:**
- All financial amounts must be re-verified server-side
- Business rules (e.g., sequential installments, date constraints) must be enforced server-side
- Safe defaults for new admin permissions (default to no access)
- Pagination limits must be enforced server-side (no unlimited queries)

**Current gaps:**
- No CAPTCHA on public forms (donation, booking, yatra registration)
- No server-side request size limits enforced in PHP config
- Webhook idempotency relies on payment ID dedup — no explicit idempotency key

---

### A05: Security Misconfiguration

**How it applies:** Server configuration, headers, error handling, directory listing.

**Current controls:**
- **`.htaccess`**: Security headers (CSP, X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy, Permissions-Policy)
- **Error handling**: `display_errors = 0` in production, custom `register_shutdown_function()` for fatal errors
- **Directory listing**: `Options -Indexes` in `.htaccess`
- **Environment**: Configuration via environment variables, not hardcoded
- **Error logging**: All errors logged to `logs/php_errors.log`

**Required standards for new code:**
- No debug endpoints or debug mode in production
- All error messages to users must be generic — detailed errors go to logs only
- Never expose `$e->getMessage()` to end users
- Verify `.htaccess` is present after deployment
- Test that directory listing is disabled on all directories

**Current gaps:**
- CSP currently allows `'unsafe-inline'` and `'unsafe-eval'` for scripts — restricts tightening
- Logs directory should be above web root (currently `logs/` is accessible)

---

### A06: Vulnerable and Outdated Components

**How it applies:** Composer dependencies, npm packages, CDN-loaded libraries, Razorpay API versions.

**Current controls:**
- Composer dependencies versioned in `composer.lock`
- npm packages versioned in `package-lock.json`
- Razorpay Checkout loaded from CDN (`checkout.razorpay.com/v1/checkout.js`)
- Chart.js loaded from CDN (`cdn.jsdelivr.net`)
- No React, jQuery, or other large frontend frameworks

**Required standards for new code:**
- Run `composer audit` and `npm audit` before each deployment
- Pin CDN versions to specific releases (avoid `@latest`)
- Review Razorpay API changes before updating
- Keep PHP version updated (8.0+ minimum)

**Current gaps:**
- No automated dependency vulnerability scanning
- CDN versions could become stale or compromised

---

### A07: Identification and Authentication Failures

**How it applies:** Admin login, donor dashboard access, session management.

**Current controls:**
- Session-based admin authentication with `SessionGuard`
- `password_verify()` for credential validation
- Session regeneration on login (`session_regenerate_id(true)`)
- Rate-limited login (5 attempts / 15-min window per IP, stored in `login_attempts` table)
- Session timeout (browser session — no persistent token)
- Logout functionality destroys session

**Required standards for new code:**
- All admin pages must call `requireLogin()` via `auth-check.php`
- Session IDs must be regenerated on privilege elevation
- Donor-facing auth should not rely solely on URL query parameters
- Password must be hashed with bcrypt (`PASSWORD_DEFAULT`)

**Current gaps:**
- Donor dashboard access uses only query parameter (`?donor_id=X`) — no authentication
- No MFA for admin login
- No session timeout / idle timeout on admin sessions
- No password complexity requirements
- No account lockout — rate limiting resets after 15 minutes

---

### A08: Software and Data Integrity Failures

**How it applies:** Database migrations, deployment artifacts, Razorpay webhooks.

**Current controls:**
- Migrations are idempotent — safe to re-run
- Webhooks validate `X-Razorpay-Signature` header with HMAC-SHA256
- Build artifacts are generated by `scripts/build.js` (terser + lightningcss)
- `composer.lock` and `package-lock.json` committed to repository

**Required standards for new code:**
- Never manually edit the database in production — always use migrations
- Validate webhook authenticity before processing
- Review all migrations before running in production
- Trust but verify: client-side verification + server-side webhook double-check

**Current gaps:**
- No signature verification on migration scripts themselves
- No CI/CD pipeline for automated integrity checks

---

### A09: Security Logging and Monitoring Failures

**How it applies:** Failed logins, payment failures, permission violations, admin CRUD.

**Current controls:**
- **Login attempts**: Logged to `login_attempts` table (IP, username, timestamp, success/failure)
- **Payment failures**: Logged in Razorpay webhook and updated in `donation_transactions.payment_status`
- **PHP errors**: Logged to `logs/php_errors.log`
- **Sudamaseva events**: Logged to `logs/sudamaseva_subscriptions.log`, `logs/sudamaseva_payments.log`, `logs/sudamaseva_webhooks.log`

**Required standards for new code:**
- Log all authentication attempts (success and failure) with IP, username, timestamp
- Log all permission-denied events in admin pages
- Log all state-changing operations (create, update, delete) with admin ID
- Log all payment-related events (order creation, verification success/failure, webhook receipt)
- Log all export/download events
- Never log sensitive data (passwords, tokens, card numbers)

**Current gaps:**
- No centralized audit log table — logs are fragmented across files and DB tables
- Admin CRUD operations (create/edit/delete donors, toggle bookings) are not explicitly logged
- No log monitoring/alerting infrastructure
- No retention policy defined
- Logs directory is within the web root

---

### A10: Server-Side Request Forgery (SSRF)

**How it applies:** Source URL tracking, external image loading, webhook callbacks.

**Current controls:**
- No outbound HTTP requests from the server to user-supplied URLs
- Razorpay API calls use hardcoded URLs (`api.razorpay.com`)
- CDN loading uses specific, hardcoded URLs
- `source_url` is stored as metadata, not fetched by the server

**Required standards for new code:**
- Never fetch or process URLs supplied by user input
- If external URLs must be requested, use an allowlist of approved domains
- Set timeouts on all outbound HTTP requests

**Current gaps:**
- Low risk — no current feature fetches user-supplied URLs. Future features (e.g., image URL processing) must implement SSRF protection.

---

## 4. Authentication

### Admin Authentication Flow

```
1. User visits /admin/login
2. Submits username + password via POST form
3. login.php:
   a. Checks rate limit (5 attempts / 15 min per IP)
   b. Validates credentials against admins table via password_verify()
   c. On success:
      - Regenerates session ID (session_regenerate_id(true))
      - Stores admin_id, admin_username in $_SESSION
      - Loads permissions into $_SESSION['admin_permissions']
      - Clears failed login attempts for this IP
      - Redirects to /admin/dashboard
   d. On failure:
      - Logs failed attempt in login_attempts table
      - Shows generic error message
4. Every admin page includes admin/auth-check.php
5. auth-check.php calls SessionGuard::requireLogin()
6. SessionGuard checks $_SESSION['admin_logged_in']
```

### Password Storage

```php
// Creating/updating passwords:
$hash = password_hash($password, PASSWORD_DEFAULT); // bcrypt

// Verifying passwords:
if (password_verify($password, $admin['password_hash'])) { ... }
```

### Session Security

| Setting | Value | Location |
|---------|-------|----------|
| Cookie httpOnly | true | PHP default |
| Cookie SameSite | Lax | PHP config |
| Session regeneration | On login | `login.php` |
| Session name | PHPSESSID | PHP default; could be customized |
| Session storage | Files | PHP default (shared hosting compatible) |

---

## 5. Authorization / RBAC

See also: `docs/AUTHORIZATION_MATRIX.md` for the complete permission matrix.

### Permission Checking API

Available globally in all admin pages (after including `auth-check.php`):

| Function | Purpose | Example |
|----------|---------|---------|
| `hasPermission('module.action')` | Boolean check — for UI visibility | `if (hasPermission('donations.edit')): ?>` |
| `requirePermission('module.action')` | Blocks with 403 if not granted | `requirePermission('festivals.view');` |
| `hasAnyPermission(['a', 'b'])` | Boolean — any of the given perms | `if (hasAnyPermission(['donations.view', 'reports.view'])):` |
| `requireAnyPermission(['a', 'b'])` | Blocks with 403 if none granted | `requireAnyPermission(['panihati.view', 'panihati.edit']);` |
| `hasRole(['super_admin'])` | Legacy — prefer permissions | `if (hasRole(['super_admin'])):` |

### Super Admin Bypass

Super Admin implicitly bypasses ALL permission checks. The permission check logic:

```php
if (in_array('super_admin', $userRoles)) {
    return true; // Always allowed
}
// Otherwise, check explicit permission assignment
```

### Role Management

- Roles are data-driven — managed through the admin UI by super_admin
- Permission matrix UI for assigning permissions to roles
- Multi-role assignment on admin user edit page
- 11 seeded roles, 55 permissions across 13 modules

---

## 6. Input Validation

### Global Rules

| Field Type | Validation Rule | Sanitization |
|------------|----------------|--------------|
| **Email** | `filter_var($email, FILTER_VALIDATE_EMAIL)` | `filter_var($email, FILTER_SANITIZE_EMAIL)` |
| **Phone** | Match `/^\+?[\d\s\-()]{10,15}$/` | Strip non-numeric characters |
| **Amount** | Server-side comparison against catalog | Cast to `int` or `float` |
| **PAN** | Match `/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/` | `strtoupper(trim($pan))` |
| **URL** | `filter_var($url, FILTER_VALIDATE_URL)` | `filter_var($url, FILTER_SANITIZE_URL)` |
| **Integer IDs** | `filter_var($id, FILTER_VALIDATE_INT)` | `(int) $id` |
| **Slug** | Match `/^[a-z0-9\-]+$/` | `preg_replace('/[^a-z0-9\-]/', '', $slug)` |
| **Date** | `strtotime($date) !== false` | Store as `Y-m-d` format |
| **Sort/Order fields** | Allowlist of valid column names | Reject unknown columns |

### Server-Side Validation Pattern

```php
// Validate ID
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) {
    // Handle invalid input
}

// Validate email
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) {
    $errors[] = 'Valid email is required.';
}
```

---

## 7. CSRF Protection

### Token Generation

```php
// In bootstrap.php or auth-check.php:
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

### Token Validation (POST forms)

```php
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $error = 'CSRF validation failed. Unauthorized request.';
}
```

### Token Validation (GET destructive actions)

```php
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
    $error = 'CSRF validation failed. Unauthorized request.';
}
```

### Form Template

```php
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <!-- form fields -->
</form>
```

### Rules

- **Every form** must include a CSRF token — no exceptions
- **Every destructive GET action** (toggle, delete) must include and validate a CSRF token
- Use `hash_equals()` for comparison (timing-attack safe)
- Single token per session (not per-form) — standard PHP approach
- Future improvement: migrate GET destructive actions to POST-only

---

## 8. XSS Prevention

### Output Escaping Rules

| Context | Function | Example |
|---------|----------|---------|
| HTML body | `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` | `<?php echo htmlspecialchars($title); ?>` |
| HTML attribute | `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` | `value="<?php echo htmlspecialchars($name); ?>"` |
| JavaScript string | `json_encode($var)` + `htmlspecialchars()` | Never inject raw PHP into `<script>` tags |
| URL | `htmlspecialchars($url)` | `href="<?php echo htmlspecialchars($url); ?>"` |
| XML | `htmlspecialchars($var, ENT_XML1, 'UTF-8')` | Sitemap generation |

### Content Security Policy

The CSP header in `.htaccess` provides a defense-in-depth layer:

```apache
Content-Security-Policy: default-src 'self'; script-src 'self' ... 'unsafe-inline' 'unsafe-eval'; ...
```

**Note:** `'unsafe-inline'` and `'unsafe-eval'` are currently required for Razorpay checkout and some inline scripts. Future tightening should aim to remove these.

### Safe HTML Policy

For admin-editable content (blog posts, festival descriptions), HTML is allowed but:
- Output through `htmlspecialchars()` by default
- Rich text editors produce HTML that is stored in DB and rendered directly
- Currently no HTML sanitization layer (e.g., HTML Purifier) — known gap

---

## 9. SQL Injection Prevention

### Mandatory Pattern

```php
// ✅ CORRECT — Always use prepared statements
$stmt = $db->prepare("SELECT * FROM donation_causes WHERE slug = ?");
$stmt->execute([$slug]);
$cause = $stmt->fetch();

// ✅ CORRECT — Named parameters for complex queries
$stmt = $db->prepare(
    "SELECT * FROM donations WHERE status = :status AND amount > :min_amount"
);
$stmt->execute([':status' => 'paid', ':min_amount' => 100]);

// ❌ WRONG — Never concatenate user input
$result = $db->query("SELECT * FROM users WHERE id = " . $_GET['id']);
```

### Dynamic ORDER BY

```php
// ✅ CORRECT — Use an allowlist
$allowedSorts = ['amount', 'date', 'name'];
$sort = $_GET['sort'] ?? 'date';
if (!in_array($sort, $allowedSorts)) {
    $sort = 'date'; // Default to safe value
}
$stmt = $db->prepare("SELECT * FROM donations ORDER BY $sort");
```

### Rules

- **No string concatenation** of user input into SQL — no exceptions
- Use PDO prepared statements with `?` or `:named` placeholders
- Dynamic table/column names must come from an allowlist, not user input
- `LIKE` clauses must still use prepared statements: `$stmt->execute(['%' . $search . '%'])`

---

## 10. File Upload Security

> **Note:** File uploads are limited in this project. Current upload points:
> - Blog banner images
> - Festival/cause images
> - Panihati offline entry CSV/XLS (admin only)

**See also:** `docs/FILE_UPLOADS.md`

### Current Controls

- File types restricted to images (JPEG, PNG, WebP, GIF) and CSV/XLS for admin
- Filenames are not randomized — stored as provided
- No MIME type validation beyond extension check
- No file size limits in application code (server config may limit)

### Required Standards

- Restrict allowed MIME types to a strict allowlist
- Generate random filenames (UUID or `bin2hex(random_bytes(16))`) before storage
- Validate file content, not just extension (`finfo_*` functions for MIME detection)
- Set maximum file size (e.g., 5MB for images, 10MB for CSV)
- Store uploads outside web root where possible, serve through a script
- Reject executable files (`.php`, `.exe`, `.sh`, `.pl`, `.py`, `.htaccess`)
- Strip EXIF metadata from uploaded images

---

## 11. Webhook Security

### Razorpay Webhook

```php
// Validate X-Razorpay-Signature header
$webhookSecret = RAZORPAY_KEY_SECRET;
$expectedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);

if (!hash_equals($expectedSignature, $receivedSignature)) {
    http_response_code(400);
    die('Invalid signature');
}
```

### Rules

- Always verify HMAC-SHA256 signature using the **raw request body** (not JSON-decoded)
- Use `hash_equals()` for comparison (timing-attack safe)
- Implement idempotency — check if payment ID already processed before creating records
- Log all webhook events (including ignored ones) for audit trail
- Return HTTP 200 for acknowledged events, HTTP 400 for invalid signatures

---

## 12. Payment Security

### Razorpay Integration

- **No card data stored on our server** — Razorpay handles PCI-DSS compliance
- Amounts verified server-side against the catalog on every order creation
- HMAC signature verification on every payment callback
- Webhook provides secondary verification (authoritative source)
- Test mode (`RAZORPAY_TEST_MODE=true`) for development — never process live payments with test keys

### HMAC Verification Patterns

**Standard order (one-time donations, bookings):**
```php
$expected = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
```

**Subscription payment (Sudamaseva auto monthly):**
```php
$expected = hash_hmac('sha256', $subscriptionId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
```

**Manual order payment (Sudamaseva pay monthly):**
```php
$expected = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
```

### Rules

- Always register a shutdown function: if order creation fails, ensure no orphan DB records
- Log all order creation and verification attempts
- Never return detailed error messages to the client (log server-side)
- Webhook must be registered in Razorpay Dashboard with the correct secret

---

## 13. Session Security

### Session Configuration

```php
// In config.php or bootstrap.php:
ini_set('session.use_strict_mode', 1);       // Reject uninitialized session IDs
ini_set('session.use_only_cookies', 1);       // No URL-based session ID
ini_set('session.cookie_httponly', 1);        // No JavaScript access
ini_set('session.cookie_samesite', 'Lax');    // CSRF mitigation at browser level
```

### Session Lifecycle

1. **Start**: `session_start()` in `bootstrap.php` or `SessionGuard::init()`
2. **Regenerate**: On successful login (`session_regenerate_id(true)`)
3. **Validate**: Every admin page checks `$_SESSION['admin_logged_in']`
4. **Destroy**: On logout — `session_destroy()` + clear session data
5. **Timeout**: Currently browser-session based (expires when browser closes)

---

## 14. Audit Logging

### Current State

Audit logging exists in fragmented form:

| Event Type | Storage | Captures |
|-----------|---------|----------|
| Login attempts | `login_attempts` table | IP, username, timestamp, success/failure |
| PHP errors | `logs/php_errors.log` | Stack traces, error messages |
| Sudamaseva subscriptions | `logs/sudamaseva_subscriptions.log` | Subscription creation/errors |
| Sudamaseva payments | `logs/sudamaseva_payments.log` | Payment verification details |
| Sudamaseva webhooks | `logs/sudamaseva_webhooks.log` | All webhook events |

### Required Events (Future)

| Event | Data to Capture | Priority |
|-------|----------------|----------|
| Admin login (success) | admin_id, IP, timestamp, user-agent | High |
| Admin login (failure) | username, IP, timestamp | High |
| Permission denied | admin_id, page, IP, timestamp | Medium |
| Donor CRUD | admin_id, action, donor_id, timestamp | Medium |
| Payment verification | order_id, payment_id, status, IP | High |
| Webhook receipt | event_type, payment_id, status | High |
| CSV export | admin_id, report_type, timestamp | Medium |
| Role/permission change | admin_id, role_id, changes, timestamp | High |
| Content publish/unpublish | admin_id, content_id, action, timestamp | Low |

### Design Recommendation

If a centralized audit log table is added:

```sql
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT DEFAULT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_data JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_admin (admin_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 15. Environment & Secrets

### Environment Variables

```env
# Database
DB_HOST=localhost
DB_NAME=isjm_donations
DB_USER=root
DB_PASS=

# Razorpay (live/test)
RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxx
RAZORPAY_TEST_MODE=true
```

### Secrets Management Rules

- **Never hardcode secrets** in PHP files, configs, or `.htaccess`
- Production secrets set via hosting panel or server environment (not `.env` file)
- `.env` files used for local development only — must be `.gitignore`d
- Razorpay key secret acts as webhook secret (shared value)
- Rotate secrets periodically and when a team member with access leaves

---

## 16. Security Headers

Set in `.htaccess` and applied to all responses:

| Header | Value | Purpose |
|--------|-------|---------|
| `X-Frame-Options` | `DENY` | Prevent clickjacking |
| `X-Content-Type-Options` | `nosniff` | Prevent MIME sniffing |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Control referrer info |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` | Enforce HTTPS (1 year) |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=(), payment=(self), autoplay=()` | Restrict browser features |
| `Content-Security-Policy` | (see `.htaccess` for full value) | XSS mitigation |

---

## 17. Known Gaps & Future Hardening

| Gap | Risk Level | Target | Notes |
|-----|-----------|--------|-------|
| Donor dashboard uses query param auth | **High** | Short-term | `/sudamaseva/dashboard?donor_id=X` needs stronger auth (token/OTP) |
| GET-based destructive actions | **Medium** | Medium-term | Convert `?action=delete&csrf_token=X` to POST-only |
| No HTML sanitization for rich content | **Medium** | Medium-term | Consider HTML Purifier for blog/festival content |
| Logs directory inside web root | **Medium** | Short-term | Move `logs/` above `public_html/` |
| No file upload validation beyond extension | **Medium** | Medium-term | Add MIME validation + random filenames |
| CSP uses `unsafe-inline` and `unsafe-eval` | **Medium** | Long-term | Restructure inline scripts to use nonces |
| No MFA for admin login | **Low** | Long-term | Consider TOTP-based MFA for super_admin |
| No session idle timeout | **Low** | Medium-term | Implement 30-minute idle timeout |
| No CAPTCHA on public forms | **Low** | Long-term | Add reCAPTCHA to donation/booking forms |
| No centralized audit log table | **Medium** | Medium-term | Create `audit_log` table + logging service |
| `download` attribute not set on file links | **Low** | Low | Set `download` attribute on invoice/receipt links |
| No password complexity rules | **Low** | Long-term | Enforce min length + character variety |
| `.env` file in project root | **Medium** | Short-term | Move to `config/` or above web root |
