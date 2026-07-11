# Donation Module — Tasks

> **Last updated:** 2026-07-11
> **Related:** [`ROADMAP.md`](../../ROADMAP.md) (project roadmap)

## In Progress
- [ ] Donor email notification (thank-you + finance team alert) after successful payment
- [ ] 80G tax receipt PDF generation
- [ ] Phase 1 modularization: move admin pages, assets, API endpoints to module directory

## Upcoming
- [ ] Recurring donation management dashboard
- [ ] Donation receipt PDF for donors
- [ ] Year-over-year comparison enhancements on dashboard
- [ ] Top donors leaderboard with category breakdown
- [ ] SMS notifications via MSG91

## Known Bugs
- [ ] Some legacy causes still use old `donation_cause_sevas` table (see DECISIONS.md — Dual-Read Strategy)
- [ ] `donation_cause_sevas.slug` column does NOT exist — only `name` column (can break queries expecting slug)
- [ ] Some test data uses `source_type = 'test_seed'` or `'seed_dashboard'` — needs cleanup

## Technical Debt
- [ ] Remove legacy `donation_cause_sevas` fallback once all causes migrated to master catalog
- [ ] Remove legacy `donation_seva_categories` table once migration complete
- [ ] Convert `includes/donation-helpers.php` facade calls to direct class usage throughout codebase
- [ ] Standardize error handling across all API endpoints (currently inconsistent)
- [ ] Add input validation middleware to API endpoints
- [ ] `seva.php` in project root is deprecated, redirects to `index.php`

## Backlog
- [ ] Donation analytics by geolocation
- [ ] Automated monthly finance report email
- [ ] Festival-specific landing pages with countdown timers
