# PROJECT.md — ISKCON Sri Jagannath Mandir

## Vision

Build and maintain the official digital presence for ISKCON Seshadripuram, Bangalore — enabling seamless devotee engagement through online donations, puja bookings, festival participation, and community outreach.

## Scope

### In Scope
- **Donation Management** — 68+ causes with Category → Activity → Seva hierarchy, Razorpay payments, donor tracking
- **Puja & Yagya Booking** — Online scheduling with payment integration
- **Festival Listings** — Category-based festival pages with donation integration
- **Yatra Registration** — Panihati Yatra travel/vehicle booking
- **Admin Panel** — Role-based dashboard, donation reporting, content management
- **Master Seva Catalog** — Reusable seva library linked across festivals/causes
- **Outreach** — Book distribution, digital initiatives, devotee care

### Out of Scope
- Mobile app (future consideration)
- Multi-language support (current: English only)
- E-commerce / merchandise sales
- Live streaming infrastructure (funded via donations, not built here)

## Architecture Migration — 8 Phases Complete ✅

The project was migrated from a flat file structure to a modular architecture, with subsequent feature additions:

| Phase | Module | Files Moved / Added |
|:------|:-------|:-------------------:|
| **Phase 1** | `modules/Donation/` — Reports, payment API, seva catalog | ~35 |
| **Phase 2** | `modules/Panihati/` — Yatra registration & admin | ~14 |
| **Phase 3** | `modules/Booking/` — Puja/yagya/guest house booking | ~15 |
| **Phase 4** | `modules/Festivals/` — 70+ public festival pages | ~70 |
| **Phase 5** | `modules/Blogs/` — Blog posts & admin | 4 |
| **Phase 6** | `modules/Content/` — Static content (services, courses, about, yatra) | 46 |
| **Phase 7** | `modules/Kernel/` — Shared infrastructure (config, partials, includes, src) | 24 |
| **Phase 8** | `modules/RBAC/` — Role-based access control with permission management UI | 8 |
| **Total** | **~201 files across 8 modules** | **~201** |

All original file paths are preserved as backward-compatible wrappers. See [ARCHITECTURE.md](ARCHITECTURE.md) for details.

## Stakeholders

| Role | Responsibility |
|------|----------------|
| Temple Administration | Feature priorities, data ownership |
| Finance Team (seva@iskconseshadripuram.org) | Donation tracking, 80G receipts |
| Development Team | Code, infrastructure, maintenance |
| Devotees / Donors | End users of public-facing features |

## Success Metrics

- Donation collection efficiency (online vs offline ratio)
- Booking completion rate
- Page load performance (< 3s on shared hosting)
- Zero payment processing errors
- Admin task completion time

## Constraints

- **Hosting**: Shared cPanel/Apache — no Node.js runtime, no queues, no Redis
- **Database**: MySQL on shared hosting — limited connections, no stored procedures
- **Budget**: Minimal operational cost — prefer free tiers (Gmail SMTP, Chart.js CDN)
- **Security**: PCI-DSS scope minimized via Razorpay (no card data stored)
