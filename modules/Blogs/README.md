# Module: Blogs

## Purpose
Manages blog posts — create, edit, publish/unpublish, and display on public-facing blog pages with SEO metadata.

## Owned Database Tables
- `blogs` — Blog entries (title, slug, content_body, description, tags, banner_image, is_published, published_date, meta_title, meta_description)

## Dependencies
- **Kernel** — Config, DB, partials (header/footer)

## Entry Points
| Type | URL | File | Description |
|------|-----|------|-------------|
| Public | `/blogs` | `content/index.php` | Blog listing with tag filters |
| Public | `/blogs/{slug}` | `content/detail.php` | Blog detail page |
| Admin | `/admin/blogs` | `Admin/blogs.php` | Blog listing with publish toggle, edit/delete |
| Admin | `/admin/blog-edit` | `Admin/blog-edit.php` | Create/edit blog with banner upload, SEO fields |

## Business Rules
- Slug must be unique — validated before save
- Published posts (`is_published = 1`) visible on public blog listing
- Tags stored as comma-separated string, displayed as badges
- Banner images uploaded and stored, path saved in DB
- SEO fields: `meta_title` and `meta_description` for search snippets
