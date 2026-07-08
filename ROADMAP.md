# ROADMAP.md — Planned Features

## Architecture Migration — All 8 Phases Complete ✅

- [x] **Phase 1** — Donation module (`modules/Donation/`)
- [x] **Phase 2** — Panihati module (`modules/Panihati/`)
- [x] **Phase 3** — Booking module (`modules/Booking/`)
- [x] **Phase 4** — Festivals module (`modules/Festivals/`)
- [x] **Phase 5** — Blogs module (`modules/Blogs/`)
- [x] **Phase 6** — Content module (`modules/Content/`)
- [x] **Phase 7** — Kernel module (`modules/Kernel/`)
- [x] **Phase 8** — RBAC module (`modules/RBAC/`) — Role-based access control with permission management UI, 55 permissions across 13 modules, 74 PHPUnit tests

~201 files across 8 modules. Original paths preserved as backward-compatible wrappers.

---

## In Progress

- [ ] Donor email notifications (thank-you + finance team alert)
- [ ] 80G tax receipt PDF generation

## Upcoming

- [ ] Year-over-year comparison enhancements on dashboard
- [ ] Top donors leaderboard with category breakdown
- [ ] Donation receipt PDF for donors
- [ ] SMS notifications via MSG91
- [ ] Admin sidebar accordion persistence (remember open/closed state across page loads via localStorage)

## Backlog

- [ ] Multi-language support (Hindi, Kannada)
- [ ] Recurring donation management dashboard
- [ ] Donor management CRM (contact history, preferences)
- [ ] Automated monthly finance report email
- [ ] Festival-specific landing pages with countdown timers
- [ ] Push notifications for upcoming festivals
- [ ] Donation analytics by geolocation

## Previously Completed

- [x] RBAC system — 11 roles, 55 permissions, permission matrix UI, multi-role admin assignment, super_admin bypass
- [x] Admin sidebar accordion — collapsible nav groups, first group opens by default, active group auto-opens
- [x] PHPUnit test framework — 74 RBAC unit tests (in-memory SQLite), PermissionRegistry + RbacService coverage
- [x] Role management UI — roles listing, create/edit with permission matrix, permissions reference page
- [x] Sudamaseva subscription system — recurring donation management with Razorpay
- [x] Donation reporting system (Category → Activity → Seva)
- [x] Dashboard with Chart.js visualizations
- [x] CSV export for all reports
- [x] Master Seva Catalog system
- [x] Panihati Yatra management
- [x] Puja & Yagya booking system
- [x] Festival content management
- [x] Admin role-based access control
