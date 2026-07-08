# Sudamaseva Spec Review — Refinements & Missing Details

## ✅ What's Good

- Comprehensive analysis of the old app structure, DB schema, and data profile
- Clear migration strategy with 5 phases
- Well-structured module layout following `modules/Panihati/` pattern
- Sensible risk table covering key concerns
- Good future considerations section

---

## 🔴 Critical Issues to Address

### 1. Wrapper File Location (Mismatch)
**Issue:** The spec places wrappers at `yatra/sudamaseva.php`, but Sudamaseva is **not a yatra** — it's a donation program. The Panihati module is under `yatra/` because Panihati *is* a yatra (pilgrimage event).

**Recommendation:** Create root-level wrapper files (`sudamaseva.php`, `sudamaseva-register.php`, etc.) in the project root, following the same pattern as `seva.php`, `contact.php`, `darshan.php`:

```
sudamaseva.php                    → require modules/Sudamaseva/content/index.php
sudamaseva-register.php           → require modules/Sudamaseva/content/register.php
sudamaseva-dashboard.php          → require modules/Sudamaseva/content/dashboard.php
sudamaseva-payment-success.php    → require modules/Sudamaseva/content/payment-success.php
sudamaseva-payment-failed.php     → require modules/Sudamaseva/content/payment-failed.php
```

**.htaccess:**
```apache
RewriteRule ^sudamaseva/?$ sudamaseva.php [L,NC]
RewriteRule ^sudamaseva/register/?$ sudamaseva-register.php [L,NC]
RewriteRule ^sudamaseva/dashboard/?$ sudamaseva-dashboard.php [L,NC]
RewriteRule ^sudamaseva/payment-success/?$ sudamaseva-payment-success.php [L,NC]
RewriteRule ^sudamaseva/payment-failed/?$ sudamaseva-payment-failed.php [L,NC]
```

### 2. Admin Role Name (`sudamaseva` is Inconsistent)
**Issue:** The spec proposes a new role `sudamaseva`, but existing roles are generic function-based names: `super_admin`, `editor`, `pujari`, `treasurer`, `travel_agent`. Adding a role named after a specific module sets a bad precedent (what about `panihati_admin` next?).

**Recommendation:** Use `['super_admin', 'treasurer']` for Sudamaseva admin pages, since it's a donation/financial program. The treasurer role already has access to donation reports. OR introduce a more generic role like `seva_admin` that could be reused. But simplest: just use `treasurer` + `super_admin`.

### 3. Reuse Existing Donation Infrastructure
**Issue:** The spec creates entirely new tables (`sudamaseva_subscriptions`, `sudamaseva_payments`) without considering the existing `donation_subscriptions` and `donation_plans` tables already in the project.

**Current infrastructure:**
- `donation_plans` table — stores Razorpay plan IDs with cause_id, amount, interval
- `donation_subscriptions` table — stores subscriptions with donor info, plan_id, subscription_id, status
- `modules/Donation/api/create-subscription.php` — already handles Razorpay plan creation + subscription creation via cURL

**Recommendation:** Either:
- **Option A (Reuse):** Store Sudamaseva subscriptions in `donation_subscriptions` with a `source = 'sudamaseva'` field, OR
- **Option B (Separate, clear):** Keep separate tables but reuse the Razorpay API logic from `create-subscription.php` as a shared utility

Spec should clarify which approach. Option A avoids duplicating payment infrastructure.

### 4. The `payments` Table (101 Records Unmapped)
**Issue:** The old DB has a `payments` table with 101 records that is NOT part of the migration plan. These appear to be standalone payment records (separate from the installment system) — possibly older/trial payments or webhook logs.

**Recommendation:** Add a migration phase to clarify:
- Query `payments` table and determine if they overlap with `tbl_rec_ins_pay` records
- If standalone, migrate them as orphan payments or skip with documentation
- Document the table's purpose in the spec

### 5. Missing API Contracts
**Issue:** The spec lists API endpoints but provides no request/response schemas. The existing project documents all API endpoints with full JSON schemas in `modules/Donation/API.md`.

**Recommendation:** Add a dedicated API section with:

```json
// POST /api/sudamaseva/create-subscription.php
{
  "amount": 50000,          // in paise (₹500)
  "donor_name": "Radha Das",
  "donor_phone": "9876543210",
  "donor_email": "radha@example.com",
  "pan": "ABCDE1234F",      // optional
  "area": "Malleswaram",    // city/area/state
  "city": "Bangalore",
  "state": "Karnataka"
}
→ { "subscription_id": "sub_xxxxx", "order_id": "order_xxxxx", "amount": 50000 }

// POST /api/sudamaseva/verify-payment.php
{
  "razorpay_order_id": "order_xxxxx",
  "razorpay_payment_id": "pay_xxxxx",
  "razorpay_signature": "xxxxx",
  "donor_id": 123
}
→ { "status": "success", "payment_id": "pay_xxxxx" }

// POST /api/sudamaseva/webhook.php
// Razorpay standard webhook payload + X-Razorpay-Signature header
// Events handled: subscription.charged, subscription.activated, subscription.cancelled, payment.failed
```

---

## 🟡 Important Refinements

### 6. 80G Compliance — Form 10BE Required
**Issue:** The spec says "ensure receipts follow Income Tax 80G format requirements" but the law has changed. As of 2025-26, **Form 10BE** (generated via the Income Tax Department portal) is required — not just a manually generated receipt.

**Requirements:**
- Institution must file **Form 10BD** with IT department periodically
- Then generate **Form 10BE** for each donor (system-generated via IT portal)
- Must include: Donee name, Donee PAN, 80G registration number, Donor name/address/PAN/Aadhaar, Unique certificate number, Amount, Mode of payment, Date
- Deadline: Issue by May 31st of the following financial year

**Recommendation:** Add a compliance section detailing:
- `sudamaseva_receipts` table stores the data needed for Form 10BE generation
- Admin workflow to generate/bulk-generate Form 10BE via IT portal integration (future)
- For now, receipts serve as donor records; Form 10BE generation is a future consideration
- All payments > ₹2,000 must be via electronic mode (no cash) for 80G eligibility

### 7. Mobile App Data (FCM Tokens, API Events)
**Issue:** The old DB has `api_tokens` (FCM push notification tokens) and `api_events` tables (CMS events). These are used by the old Android app.

**Recommendation:** Add a migration note:
- These tables belong to the old mobile app, not to the Sudamaseva module
- Document that they are preserved in the old DB but not migrated to the new module
- If a new mobile app is planned, this data may be needed — flag for future consideration

### 8. Pricing Tier Strategy
**Issue:** The old data has 19 distinct amount values (₹1 to ₹4,000). The minimum ₹50 applies to new subscriptions.

**Recommendation:** Define explicit pricing tiers for the registration page:

| Tier Name | Monthly Amount | Notes |
|-----------|---------------|-------|
| Tulsi Leaf | ₹51 | Entry level |
| Lotus Petal | ₹101 | Most popular |
| Golden Offering | ₹501 | Mid tier |
| Rath Seva | ₹1,001 | Supporter level |
| Sudama Special | ₹5,001 | Patron level |
| Custom | min ₹50 | Any amount |

This makes the UI cleaner while keeping the old data intact.

### 9. Nav Integration
**Issue:** The spec doesn't mention where Sudamaseva should appear in the site navigation. The current nav has: Home, About, Services, Courses, Yatra, Festivals, Media, Donate.

**Recommendation:** Add as a sub-page under **Donate** (as a donation program), or as a standalone nav item **Seva** next to the existing links. Or add a `seva` nav section. Clarify with user.

### 10. Missing `number_of_times` Handling
**Issue:** The old `tbl_users.number_of_times` field was NULL for all records. Users appear to have committed to an indefinite/24-month schedule based on `tbl_rec_ins_pay.ins_no` values (1-25).

**Recommendation:** For migrated subscriptions:
- Use `MAX(ins_no)` per user as `total_installments` (likely 24)
- Use `COUNT(paid installments)` as `installments_paid`
- Set remaining installments = total - paid
- The subscription is considered "completed" when all remaining installments would be exhausted OR convert to open-ended after the initial commitment

### 11. Test Strategy
**Issue:** The spec only mentions E2E tests. The existing project also needs unit tests.

**Recommendation:** Add test types:
- **Unit tests:** PHP tests for `SudamasevaRepository`, `SudamasevaService` (via PHPUnit or simple assertion scripts)
- **E2E tests (Playwright):** Registration flow, donor dashboard, admin pages, payment flow
- **Migration test:** Run `002_migrate_data.php` against a copy of the old DB, validate counts match
- **Smoke test:** All Sudamaseva URLs return HTTP 200 (add to `tests/e2e-all-pages.spec.js`)

### 12. Security Considerations
**Issue:** The spec doesn't address:
- Rate limiting on API endpoints
- CSRF tokens on forms (existing project requires them on all state-changing requests)
- Phone number validation (as the unique identifier)
- Razorpay signature verification (already in existing infrastructure)

**Recommendation:** Add security section covering:
- CSRF validation on all registration/admin forms
- Phone number format validation (+91 XXXXXXXXXX)
- Razorpay HMAC verification on all payment callbacks
- Rate limiting on `/api/sudamaseva/create-subscription.php`
- Input sanitization on all user fields

### 13. Old Admin Credentials Migration
**Issue:** The old `admin` table has admin credentials. Should these be migrated to the new `admins` table?

**Recommendation:** Document that old admin accounts are NOT migrated automatically. New `sudamaseva` role access is granted by `super_admin` via the existing `admin/admins.php` page.

### 14. Email Template and Notifications
**Issue:** The old app has `includes/payment-mail.php` with a template system using `{{PLACEHOLDERS}}`. The new system needs to send email receipts.

**Recommendation:** Create an email template at `modules/Sudamaseva/templates/email/payment-receipt.html` with placeholders for:
- Donor name, amount, receipt number, payment date, installment number
- Use PHP's `mail()` function (same as old app) or extend the existing approach
- Template should follow the existing project's email pattern

---

## 📋 Summary of Recommended Changes

| # | Type | Change | Priority |
|---|------|--------|----------|
| 1 | 🔴 Fix | Wrappers at root level, not in `yatra/` | Critical |
| 2 | 🔴 Fix | Use `treasurer` role instead of new `sudamaseva` role | Critical |
| 3 | 🔴 Fix | Clarify reuse of existing `donation_subscriptions` infrastructure | Critical |
| 4 | 🔴 Fix | Add migration for the `payments` table (101 records) | Critical |
| 5 | 🔴 Add | Full API contracts with request/response schemas | High |
| 6 | 🟡 Add | 80G Form 10BE compliance section | High |
| 7 | 🟡 Add | Document mobile app data (api_tokens, api_events) | Medium |
| 8 | 🟡 Add | Define recommended pricing tiers | Medium |
| 9 | 🟡 Add | Navigation integration section | Medium |
| 10 | 🟡 Clarify | Handle `number_of_times` NULL values in migration logic | Medium |
| 11 | 🟡 Add | Test strategy (unit + E2E + migration validation) | Medium |
| 12 | 🟡 Add | Security considerations section | High |
| 13 | 🟢 Add | Document admin credentials migration plan | Low |
| 14 | 🟢 Add | Email template approach | Medium |

---

## Implementation Order (Revised)

1. ✅ Specification written and reviewed ← **You are here**
2. Apply refinements to the spec
3. Create new module directory structure
4. Create `001_create_tables.php` migration
5. Create `002_migrate_data.php` (old DB → new tables)
6. Create `SudamasevaRepository.php` (all DB queries)
7. Create `SudamasevaService.php` (business logic)
8. Create `SudamasevaRenderer.php` (HTML rendering)
9. Create public pages (landing, register, dashboard, success/failure)
10. Create API endpoints (create-subscription, verify-payment, webhook)
11. Create admin pages (dashboard, donors, payments, subscriptions, reports)
12. Create wrapper files and .htaccess rules
13. Create email templates
14. Run migration and validate data
15. Write tests (E2E + unit + migration validation)
16. Test subscription flow end-to-end
17. Deploy
