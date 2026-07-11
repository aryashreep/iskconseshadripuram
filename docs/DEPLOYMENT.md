# DEPLOYMENT.md — Production Checklist

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** [`SECURITY.md`](../SECURITY.md) (security checks), [`docs/SECURITY_CHECKLIST.md`](SECURITY_CHECKLIST.md) (security checklist), [`WORKFLOWS.md`](../WORKFLOWS.md) (deployment flow)

## Pre-Deployment

1. **Backup database** — export via phpMyAdmin or CLI
   ```bash
   mysqldump -u root -p isjm_donations > backup_$(date +%Y%m%d).sql
   ```

2. **Run PHPUnit tests** — verify RBAC and service layer integrity
   ```bash
   vendor/bin/phpunit
   # Expected: 74 tests, 505 assertions, all passed
   ```

3. **Run E2E tests** — verify critical flows
   ```bash
   npx playwright test
   # Expected: 172 tests passed
   ```

4. **Run build** — generate minified assets
   ```bash
   npm run build
   ```

5. **Run pending migrations** — on local first, verify, then on prod
   ```bash
   php database/migrations/your-migration.php
   ```

6. **Test critical flows** — donation, booking, payment verification

## Deployment Steps

1. Upload files (exclude `node_modules/`, `tests/`, `scripts/`, `.git/`)
2. Upload `assets/dist/` (built assets)
3. Set environment variables on production server (not `.env` file)
4. Import database schema if new tables added
5. Run migrations on production database (run RBAC migrations in order):
   ```bash
   php modules/RBAC/database/migrations/001_create_rbac_tables.php
   php modules/RBAC/database/migrations/002_seed_roles_and_permissions.php
   php modules/RBAC/database/migrations/003_migrate_existing_admins.php
   ```
6. Clear browser cache for CSS/JS changes

## Post-Deployment

1. Verify payment flow works in live mode
2. Check admin login and role access (log in as super_admin)
3. Verify RBAC roles and permissions loaded (visit `/admin/roles`)
4. Check sidebar navigation — menu items should reflect user permissions
5. Monitor error logs for 24 hours
6. Verify Razorpay webhook is configured in Razorpay Dashboard

## RBAC Migration Notes

RBAC migrations are **idempotent** — safe to re-run if interrupted:
- `001_create_rbac_tables.php` — Creates `rbac_roles`, `rbac_permissions`, `rbac_role_permissions`, `rbac_user_roles` tables
- `002_seed_roles_and_permissions.php` — Seeds 11 roles, 55 permissions, and default role-permission assignments
- `003_migrate_existing_admins.php` — Transfers existing admin users from `admins.role` to `rbac_user_roles`

## Razorpay Webhook Setup

In Razorpay Dashboard → Settings → Webhooks:
- URL: `https://yourdomain.com/api/webhook.php`
- Secret: Same as `RAZORPAY_KEY_SECRET`
- Events: `payment.captured`, `payment.failed`

## Environment Variables (Production)

```env
DB_HOST=localhost
DB_NAME=iskcop35_iskconseshadripuram
DB_USER=iskcop35_user
DB_PASS=<secure-password>

RAZORPAY_KEY_ID=rzp_live_xxxxx
RAZORPAY_KEY_SECRET=<live-secret>
RAZORPAY_TEST_MODE=false
```

## Rollback

If issues arise:
1. Restore database from backup
2. Revert code to previous version
3. Clear CDN/browser caches
