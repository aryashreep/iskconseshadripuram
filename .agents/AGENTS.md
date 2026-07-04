# ISKCON Sri Jagannath Mandir (ISJM) — Agent Guide

PHP website for ISKCON Seshadripuram, Bangalore. No framework — vanilla PHP with PDO, Apache mod_rewrite, and Razorpay payments. Runs on Laragon (Windows) locally.

---

## Quick Commands

| Task | Command |
|------|---------|
| Run all E2E tests | `npx playwright test` |
| Run single test file | `npx playwright test tests/puja-booking.spec.js` |
| Check PHP syntax | `php -l <file>` |
| Install Playwright | `npm install -D @playwright/test && npx playwright install chromium` |

No build step, no composer install needed for normal development. Composer autoload is used only for `vlucas/phpdotenv`.

---

## Project Structure

```
├── admin/                  # Admin panel (PHP, session-based auth)
│   ├── partials/           # Admin header/footer (includes auth-check.php)
│   ├── dashboard.php       # Role-specific dashboards (super_admin, editor, pujari)
│   ├── donations.php       # Transaction logs with filters
│   └── bookings.php        # Puja/Yagya booking management
├── api/                    # REST-ish endpoints (no auth — verified by CORS + Razorpay HMAC)
│   ├── create-order.php    # Razorpay order creation (donations)
│   ├── verify-payment.php  # Payment signature verification
│   ├── create-booking-order.php
│   ├── create-panihati-order.php
│   └── webhook.php         # Razorpay webhook (HMAC verified)
├── booking/                # Public booking pages
│   ├── puja/               # Puja listing + detail (detail.php uses slug param)
│   └── yagya/              # Yagya listing + detail
├── donate/                 # Donation cause pages
├── yatra/                  # Yatra pages (panihati registration)
├── database/
│   ├── schema.sql          # Table definitions
│   ├── seed.sql            # Seed data (categories, causes, sevas)
│   ├── booking_schema.sql  # Booking-specific tables
│   └── migrations/         # PHP migration scripts (run manually)
├── includes/
│   ├── db.php              # PDO singleton (getDB())
│   ├── donation-helpers.php # All donation/cause/seva helper functions
│   └── panihati-helpers.php # Panihati registration helpers
├── media/                  # User-uploaded images (auto-scanned by gallery)
├── assets/
│   ├── css/style.css       # Main stylesheet + design system tokens
│   ├── css/admin.css       # Admin panel styles
│   └── js/                 # cart.js, main.js, donate.js
├── config.php              # Site config, env loading, Razorpay keys
├── .htaccess               # URL rewriting, CSP headers, security headers
├── playwright.config.js    # E2E test config
└── package.json            # Test scripts only
```

---

## Database

- **Local DB name**: `isjm_donations`
- **Prod DB name**: `iskcop35_iskconseshadripuram`
- **Credentials**: env vars `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` (defaults: localhost/root/no password)
- **Connection**: `getDB()` returns a PDO singleton (prepared statements, emulated prepares off)
- **Schema setup**: Run `database/schema.sql` then `database/seed.sql`
- **Migrations**: Run PHP files in `database/migrations/` manually via CLI or phpMyAdmin

### Key Tables
- `donation_causes` — festivals/services (74 rows, drives the donation system)
- `donation_cause_sevas` — seva offerings per cause (pricing)
- `donation_cause_master_sevas` — master seva catalog links
- `donation_transactions` — payment records (production data — never truncate)
- `booking_pujas` — puja/yagya bookings
- `panihati_yatra_registrations` — yatra registrations (production data)
- `blogs` — CMS content
- `admins` — admin users with roles (super_admin, editor, pujari, treasurer, travel_agent)

---

## Routing

`.htaccess` rewrites clean URLs. Key rules:
- `/donate/{slug}` → `donate/donate-seva.php?cause={slug}`
- `/booking/puja/{slug}` → `booking/puja/detail.php?slug={slug}`
- `/booking/yagya/{slug}` → `booking/yagya/detail.php?slug={slug}`
- `/blogs/{slug}` → `blogs/detail.php?slug={slug}`
- `/yatra/{slug}` → `yatra/detail.php?slug={slug}`
- Any `/page` → `/page.php` (generic rewrite)

**Important**: Pagination links must use absolute URLs (`BASE_URL . 'darshan?page=2'`) — relative `?page=2` gets stripped by the generic rewrite rule.

---

## Security Rules

- **Prepared statements** everywhere — never concatenate user input into SQL
- **CSRF tokens**: All forms and GET-based destructive actions must include `csrf_token` validated with `hash_equals()`
- **Auth**: `admin/auth-check.php` handles session validation. All admin pages include it via `partials/header.php`
- **RBAC**: Use `requireRole(['super_admin', 'editor'])` — never check `$_SESSION['admin_role']` inline
- **CORS**: API endpoints use origin validation, not `*` wildcard
- **File uploads**: Validate extension + MIME type (`finfo_file()`), enforce size limits
- **Error messages**: Never expose `$e->getMessage()` to users — log server-side, show generic messages
- **Amount verification**: Always verify payment amounts server-side against the catalog, never trust client-sent amounts
- **Column whitelists**: When building dynamic UPDATE queries, whitelist allowed column names

---

## Admin Roles

| Role | Access |
|------|--------|
| `super_admin` | Everything |
| `treasurer` | Dashboard (donations), donation logs |
| `editor` | Blogs, festivals, seva catalogue |
| `pujari` | Dashboard (bookings), booking management |
| `travel_agent` | Panihati Yatra management only |

---

## Design System

Defined in `assets/css/style.css`. Key tokens:

**Colors**: `--primary` (#c86b1f), `--accent` (#d4af37), `--maroon` (#7b1e1e), `--cream` (#f8f1e7), `--dark` (#2c1b12)

**Typography**: `--font-heading` (Cinzel), `--font-subheading` (Cormorant Garamond), `--font-body` (Poppins)

**Spacing**: `--space-xs` through `--space-3xl` (0.25rem to 4rem)

**Always use CSS variables** — never hardcode colors, fonts, or spacing values.

---

## Asset Loading

- Use `BASE_URL` for all asset paths: `<?php echo BASE_URL; ?>assets/images/...`
- Never use relative paths (`../../`) — they break under pretty URLs
- Images for gallery/media: stored in `media/` folder, auto-scanned by `RecursiveDirectoryIterator`
- External images: not allowed in production. Download to `assets/images/` first

---

## E2E Testing

Playwright with Chromium. Tests cover:
- **Puja booking flow**: listing → detail → offering selection → form validation
- **Yagya booking flow**: listing → detail → tier selection → modal
- **Panihati registration**: form fields, pricing calculation, travel mode toggle

Config: `playwright.config.js` (baseURL: `http://isjm.test:8080`)

---

## Payment Flow (Razorpay)

1. Client sends POST to `/api/create-order.php` with amount + cause details
2. Server verifies amount against catalog (`donation_cause_sevas.amount`)
3. Server creates Razorpay order via API, returns `order_id` to client
4. Client opens Razorpay checkout modal
5. On success, client calls `/api/verify-payment.php` with signature
6. Server verifies HMAC signature, updates `donation_transactions.payment_status = 'paid'`
7. Webhook (`/api/webhook.php`) provides secondary verification

**Test mode**: `RAZORPAY_TEST_MODE=true` in `.env`. Use test keys (`rzp_test_*`).

---

## Environment

Copy `.env.example` to `.env` and set:
```
DB_HOST=localhost
DB_NAME=isjm_donations
DB_USER=root
DB_PASS=
RAZORPAY_KEY_ID=rzp_test_...
RAZORPAY_KEY_SECRET=...
RAZORPAY_TEST_MODE=true
```

---

## Production Deployment

1. **Backup prod DB** before any changes
2. **Generate migration**: `php scripts/generate_prod_migration.php` → `scripts/prod_migration.sql`
3. **Review** the SQL, then run on prod via phpMyAdmin
4. **Never truncate** transaction tables (`donation_transactions`, `panihati_yatra_registrations`)
5. **Set env vars** on prod server (not `.env` file for credentials)
6. **Clear browser cache** after deploying CSS/JS changes
