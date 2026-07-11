# Security Checklist

> **Last updated:** 2026-07-11
> **Use this checklist when:** Adding new features, deploying, or reviewing code changes.
> **Related:** `SECURITY.md` (full policy), `docs/DEPLOYMENT.md` (deployment process)

---

## Before Deploying Code

### Authentication & Authorization
- [ ] Every admin page has `requirePermission()` or `requireLogin()` at the top
- [ ] New admin pages added to sidebar with `hasPermission()` check
- [ ] Action buttons (Edit, Delete, Export) hidden via `hasPermission()` checks
- [ ] No `requireRole()` used in new code — use `requirePermission()` instead
- [ ] Donor-facing pages with sensitive data have appropriate access controls

### Input Validation & Output Escaping
- [ ] All SQL uses prepared statements — no string concatenation of user input
- [ ] Dynamic `ORDER BY` uses an allowlist
- [ ] All user-generated content is escaped with `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- [ ] All `$_GET`, `$_POST`, `$_REQUEST` values are validated/filtered
- [ ] File uploads validate MIME type + extension (see `docs/FILE_UPLOADS.md`)

### CSRF
- [ ] Every form includes `<input type="hidden" name="csrf_token" value="...">`
- [ ] Every destructive GET action validates CSRF token
- [ ] Token validated with `hash_equals()` (not `==`)

### Payment Security
- [ ] Server-side amount verification against catalog
- [ ] HMAC signature verified on payment callbacks
- [ ] Webhook signature validated with `hash_equals()`

### Error Handling
- [ ] No `$e->getMessage()` exposed to users
- [ ] `display_errors = 0` in production config
- [ ] Error messages are generic ("An error occurred")
- [ ] Detailed errors logged server-side

### Session Security
- [ ] Session regenerated on login (`session_regenerate_id(true)`)
- [ ] Session cookies are httpOnly
- [ ] Logout destroys session

### Operations
- [ ] Database backed up before migration
- [ ] Migrations are idempotent (safe to re-run)
- [ ] Environment variables set on production server
- [ ] `.htaccess` security headers present
- [ ] Directory listing disabled (`Options -Indexes`)
- [ ] No debug/test endpoints in production
- [ ] Razorpay webhook configured in dashboard
