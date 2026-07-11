# Release Checklist

> **Last updated:** 2026-07-11
> **Related:** `docs/DEPLOYMENT.md`, `docs/SECURITY_CHECKLIST.md`

---

## Pre-Release

### Code Review
- [ ] All changes code-reviewed
- [ ] No debug code or commented-out code
- [ ] No hardcoded secrets or credentials
- [ ] Environment variables documented for any new secrets

### Testing
- [ ] PHPUnit tests pass: `vendor/bin/phpunit`
- [ ] E2E tests pass: `npx playwright test`
- [ ] New features manually tested
- [ ] Payment flow tested with Razorpay test mode

### Build
- [ ] `npm run build` completes without errors
- [ ] `assets/dist/manifest.json` generated
- [ ] No new warnings in build output

### Database
- [ ] New migrations tested locally
- [ ] Backup of production database taken
- [ ] Migrations are idempotent (safe to re-run)
- [ ] Rollback plan documented

### Documentation
- [ ] `CHANGELOG.md` updated
- [ ] Relevant module docs updated (if module changed)
- [ ] `docs/API.md` updated (if endpoints changed)
- [ ] `docs/DATABASE.md` updated (if schema changed)
- [ ] `SECURITY.md` updated (if security controls changed)

## Release

### Deployment
- [ ] Files uploaded (excluding node_modules/, tests/, scripts/, .git/)
- [ ] `assets/dist/` uploaded (built assets)
- [ ] Environment variables set on production server
- [ ] Database schema imported (if new tables)
- [ ] Migrations run in correct order
- [ ] Razorpay webhook configured (if new endpoints)
- [ ] `.htaccess` deployed and verified

### Verification
- [ ] Site loads without errors
- [ ] Admin login works
- [ ] Payment flow works in live mode (if applicable)
- [ ] Error logs show no new errors
- [ ] Security headers present (check with browser dev tools)
- [ ] Razorpay webhook test event succeeds

## Post-Release

### Monitoring
- [ ] Monitor error logs for 24 hours
- [ ] Verify all critical flows (donation, booking, lookup)
- [ ] Check no unexpected database errors
- [ ] Monitor Razorpay dashboard for payment issues
