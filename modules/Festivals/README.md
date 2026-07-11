# Module: Festivals

## Purpose
Manages ~70 public festival detail pages organized by category (Grand Festivals, Ekadashi, Appearance Days, Disappearance Days, Events). Pages are a hybrid of static PHP files (for unique editorial content) and DB-driven dynamic pages.

## Owned Data
- Festival content shared through `donation_causes` table (74 rows, `category` field)
- Static PHP files in `content/grand-festivals/`, `content/ekadashi/`, `content/appearance/`, `content/disappearance/`, `content/events/`

## Dependencies
- **Kernel** — Config, DB, partials (header/footer)
- **Donation** — Uses `donation_causes` table for festival data

## Entry Points
| URL | Description |
|-----|-------------|
| `/festivals/grand-festivals/{slug}` | Grand festival page (hardcoded .php or DB-driven) |
| `/festivals/ekadashi/{slug}` | Ekadashi page |
| `/festivals/appearance/{slug}` | Appearance day page |
| `/festivals/disappearance/{slug}` | Disappearance day page |
| `/festivals/events/{slug}` | Event page |

## URL Routing
Hardcoded `.php` files take priority over dynamic DB pages. `.htaccess` checks for `.php` file existence first, then falls back to `detail.php?slug=X`.

## Admin Pages
- `/admin/festivals` — Festival management (toggle featured, active, delete)
- `/admin/festival-edit` — Edit/create festival with content body

## Category Values (8)
`festival`, `ekadashi`, `appearance`, `disappearance`, `event`, `service`, `construction`, `general`
