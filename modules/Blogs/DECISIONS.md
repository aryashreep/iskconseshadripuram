# Blogs Module — Architecture Decisions

> **Last updated:** 2026-07-11
> **Related:** `README.md` (module overview), `WORKFLOWS.md` (blog publishing flow)

---

## [2026-06-XX] DB-Driven Blog Content with File-Based Image Uploads

### Decision
Blog content (title, body, metadata) is stored in the `blogs` database table, while banner images are uploaded as files and stored on disk with the file path saved in the database.

### Context
Blog posts need rich HTML content for articles, structured metadata fields (tags, SEO), and banner images. A pure file-based approach would make content search and management difficult. A pure DB approach would complicate image handling and increase database size.

### Options Considered
- **All files**: Blog content as `.php` or `.md` files — hard to search, no admin editing
- **All DB**: Store images as BLOBs in database — increases DB size, complicates backups
- **DB + file uploads (chosen)**: Content in DB, images on disk with path reference

### Rationale
Storing content in the database enables admin CRUD operations, search, tagging, and publishing workflows. Storing images on disk keeps the database lean and allows direct web serving through the asset pipeline. The file path in the DB acts as a clean reference without storing binary data.

### Trade-offs
- **File management**: Uploaded images must be backed up alongside the database
- **Orphaned files**: Deleting a blog post doesn't automatically clean up uploaded images
- **Path portability**: File paths may break when moving between environments

### Related Files
- `modules/Blogs/Admin/blog-edit.php` — Banner upload handling
- `database/schema.sql` — `blogs` table definition
- `modules/Blogs/content/detail.php` — Blog display

---

## [2026-06-XX] Comma-Separated Tags (Not a Tags Table)

### Decision
Blog tags are stored as a comma-separated string in the `blogs.tags` column, rather than using a separate tags table with many-to-many relationship.

### Context
Tags are used for display-only purposes (showing badges on blog cards and detail pages). There is no requirement for tag-based filtering, tag management, tag clouds, or querying by tag. The tags are set by the editor when creating the post.

### Options Considered
- **Separate tags table**: `tags` + `blog_tags` M:N — normalized but adds complexity with no benefit
- **JSON array**: MySQL JSON column — structured but over-engineered for simple display
- **Comma-separated string (chosen)**: Simple, straightforward, matches current usage

### Rationale
Tags are display-only metadata. A normalized tags table would add tables, joins, and admin UI complexity without enabling any features that aren't already served by the comma-separated approach. If tag-based filtering becomes a requirement later, a migration to a normalized structure is straightforward.

### Trade-offs
- **No tag queries**: Cannot efficiently query "all posts with tag X"
- **No tag management**: Cannot centrally manage tags (rename, merge, delete)
- **Inconsistent formatting**: Editors may use inconsistent tag formats (spaces, capitalization)

### Related Files
- `modules/Blogs/Admin/blog-edit.php` — Tag input field
- `modules/Blogs/content/index.php` — Tag badge display
- `modules/Blogs/content/detail.php` — Tag display on detail

---

## [2026-06-XX] Simple Publish/Unpublish Toggle (No Draft Workflow)

### Decision
Blog posts have a binary `is_published` flag with no intermediate states (draft, review, scheduled). Publishing is a single toggle action by any editor with permissions.

### Context
The temple website's blog is managed by a small content team. Posts are typically written and published in a single session. There is no multi-stage review workflow, scheduled publishing, or role-based approval process.

### Options Considered
- **Multi-state workflow**: Draft → Review → Approved → Published — over-engineered for current needs
- **Scheduled publishing**: `publish_at` datetime for future publishing — adds complexity
- **Simple toggle (chosen)**: `is_published` boolean, instant publish/unpublish

### Rationale
A simple toggle matches the current editorial workflow. Unpublished posts serve as "drafts" (visible only in admin), and published posts are immediately public. The `published_date` field provides chronological context without scheduling logic.

### Trade-offs
- **No scheduled posts**: Cannot queue content for future release
- **No review workflow**: Any editor can publish directly without approval
- **No version history**: Previous versions are lost on edit — no rollback

### Related Files
- `modules/Blogs/Admin/blogs.php` — Publish toggle admin UI
- `modules/Blogs/content/index.php` — Only shows `is_published = 1` posts
