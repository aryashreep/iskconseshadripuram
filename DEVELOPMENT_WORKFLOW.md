# DEVELOPMENT_WORKFLOW.md — Development Process

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** `CODING_STANDARDS.md`, `DOCUMENTATION_POLICY.md`, `docs/DEPLOYMENT.md`, `docs/TESTING.md`

---

## 1. Change Types & Required Steps

| Change Type | Steps Required |
|-------------|---------------|
| **Bug fix** | Fix → Test → Update CHANGELOG → [Update docs if behavior changed] |
| **New feature** | Spec/plan → Implement → Unit tests → E2E tests → Update docs → Update CHANGELOG |
| **New admin page** | Create file → Add permission check → Add sidebar nav → Add to MODULE_INDEX.md → E2E test |
| **New API endpoint** | Create file → Add .htaccess rule → Document in docs/API.md → Test with curl → E2E test |
| **New module** | Create directory → Add README.md, DECISIONS.md → Add to MODULE_INDEX.md → Update ARCHITECTURE.md |
| **DB migration** | Create migration script → Test locally → Run on dev → Document in docs/DATABASE.md |
| **Security fix** | Fix → Add to SECURITY.md → Add to docs/SECURITY_CHECKLIST.md → Test |

## 2. Adding a New Admin Page

1. Create PHP file in `admin/` or `modules/<Module>/Admin/`
2. Add `requirePermission('module.action')` at top
3. Set `$pageTitle` and `$activePage`
4. Include `partials/header.php` and `partials/footer.php`
5. Add navigation entry in `modules/Kernel/Admin/partials/header.php`
6. Update `MODULE_INDEX.md` with the new page

## 3. Adding a New API Endpoint

1. Create PHP file in `api/` or `modules/<Module>/api/`
2. Add .htaccess rewrite rule (if needed for clean URL)
3. Document in `docs/API.md` with request/response schema
4. Test with curl: `curl -X POST http://localhost:8080/api/endpoint -d '{}'`
5. Add E2E test if the endpoint has a frontend flow

## 4. Adding a New Database Migration

1. Create `database/migrations/NNN_description.php`
2. Use idempotent pattern (check before insert)
3. Test locally
4. Run on production during deployment

## 5. Testing Before Commit

```bash
# 1. PHP syntax check for changed files
php -l path/to/file.php

# 2. Run PHPUnit tests
vendor/bin/phpunit

# 3. Run E2E tests for affected areas
npx playwright test tests/your-file.spec.js

# 4. Run full E2E suite if changing critical paths
npx playwright test
```

## 6. Documentation Update Rules

See `DOCUMENTATION_POLICY.md` for when each document must be updated.
