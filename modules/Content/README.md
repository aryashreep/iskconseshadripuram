# Module: Content

## Purpose
Manages static content pages — about the temple, services, courses, contact, photo gallery, forums, resources, and yatra listing. Pages are file-based (PHP templates) with no dedicated database tables.

## Owned Data
- 46+ PHP page files organized by content type
- Dynamic photo gallery from `media/` directory
- Yatra listing and detail pages

## Dependencies
- **Kernel** — Config, partials (header/footer), DB (for some pages)

## Entry Points
| URL | Page | Description |
|-----|------|-------------|
| `/about/*` | 8 pages | History, philosophy, mission, temple schedule, founder acharya, etc. |
| `/services/*` | 24 pages | Food for Life, Sunday Feast, Harinam Sankirtana, etc. |
| `/courses/*` | 5 pages | Bhakti Shastri, Bhakti Vaibhava, Teachers Training, IDC |
| `/contact` | Contact form | Contact information and form |
| `/darshan` | Photo gallery | Dynamic gallery from `media/` directory |
| `/forums` | Forums | Community forum listing |
| `/resources` | Resources | Links and resources |
| `/sitemap` | XML sitemap | Auto-generated XML sitemap |
| `/yatra` | Yatra listing | Pilgrimage/travel packages listing |
| `/yatra/{slug}` | Yatra detail | Individual yatra package detail |

## File Structure
```
modules/Content/content/
├── about/          # About temple pages (8 files)
├── services/       # Service pages (24 files)
├── courses/        # Course pages (5 files)
├── darshan.php     # Photo gallery
├── contact.php     # Contact form
├── forums.php      # Forum listing
├── resources.php   # Resources page
├── sitemap.php     # XML sitemap generator
└── yatra/          # Yatra listing + detail
```

## Notes
- Content is file-based, not DB-driven — editing requires PHP file changes
- Photo gallery scans the `media/` directory dynamically
- Yatra detail pages use slug-based routing via `.htaccess`
