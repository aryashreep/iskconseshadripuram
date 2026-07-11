# Incident Response

> **Last updated:** 2026-07-11
> **Purpose:** Basic incident response procedures for common security/operational events.
> **Related:** `SECURITY.md`, `docs/SECURITY_CHECKLIST.md`

---

## Incident Types

### Payment Verification Failure
1. Check `donation_transactions` table for status of affected transactions
2. Verify Razorpay Dashboard for actual payment status
3. Check webhook logs (`logs/sudamaseva_webhooks.log`)
4. Re-run webhook or manually update payment_status if needed
5. Contact finance team if amount discrepancy found

### Admin Account Compromise
1. Immediately change admin password via database
2. Invalidate all sessions (delete session files or change session secret)
3. Check `login_attempts` table for unusual activity
4. Review `audit_log` (if implemented) for actions performed by compromised account
5. Notify super_admin

### Data Breach (suspected)
1. Check logs for unusual access patterns
2. Review recent file changes in git history
3. Check .htaccess and PHP files for unauthorized modifications
4. Verify Razorpay API keys have not been exposed
5. If payment data exposed, contact Razorpay support

### Service Outage
1. Check PHP error logs (`logs/php_errors.log`)
2. Check database connectivity
3. Verify .htaccess is not corrupted
4. Test via direct PHP file access (not through URL rewriting)
5. Check hosting control panel for resource limits

### Rate Limiting Bypass (suspected)
1. Review `login_attempts` table for unusual patterns
2. Check for multiple IPs from same user
3. Consider implementing CAPTCHA
4. Lock affected accounts temporarily
