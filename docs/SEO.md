# SEO Guide — Search Engine Optimization

> **Last updated:** 2026-07-11
> **Canonical owner:** Development Team
> **Related:** [`WORKFLOWS.md`](../WORKFLOWS.md) (publishing flows), [`SECURITY.md`](../SECURITY.md) (CSP headers), [`DEPLOYMENT.md`](DEPLOYMENT.md) (deployment checklist)

---

## Table of Contents

1. [SEO Infrastructure Overview](#1-seo-infrastructure-overview)
2. [Meta Tags (header.php)](#2-meta-tags-headerphp)
3. [Canonical URLs](#3-canonical-urls)
4. [Open Graph & Twitter Cards](#4-open-graph--twitter-cards)
5. [Schema.org Structured Data (schema.php)](#5-schemaorg-structured-data-schemaphp)
6. [XML Sitemap (sitemap.php)](#6-xml-sitemap-sitemapphp)
7. [SEO-Friendly URLs (.htaccess)](#7-seo-friendly-urls-htaccess)
8. [Admin SEO Fields (Blogs & Festivals)](#8-admin-seo-fields-blogs--festivals)
9. [Adding SEO to a New Page](#9-adding-seo-to-a-new-page)
10. [SEO Checklist](#10-seo-checklist)

---

## 1. SEO Infrastructure Overview

The website has a comprehensive SEO foundation built into the core infrastructure:

| Component | File | What It Provides |
|-----------|------|------------------|
| **Meta Tags** | `modules/Kernel/partials/header.php` | Title, description, keywords, canonical, OG, Twitter Card tags |
| **Schema.org** | `modules/Kernel/partials/schema.php` | JSON-LD structured data (6+ schema types) |
| **XML Sitemap** | `/sitemap.xml` → `modules/Content/content/sitemap.php` | Dynamic sitemap of all public pages |
| **SEO-friendly URLs** | `.htaccess` | Clean URLs without `.php` extensions |
| **Blog SEO** | `modules/Blogs/Admin/blog-edit.php` | Custom `meta_title`, `meta_description` per blog post |
| **Festival SEO** | `modules/Donation/Admin/festival-edit.php` | Custom `meta_title`, `meta_description` per festival |

All pages automatically get basic SEO tags through the shared `header.php` partial. Pages can override defaults by setting variables before including the header.

---

## 2. Meta Tags (header.php)

Located in `modules/Kernel/partials/header.php`. Every page that includes this partial automatically gets:

```html
<meta name="description" content="...">
<meta name="keywords" content="...">
<meta name="theme-color" content="#c86b1f">
<title>Page Title | ISKCON The Palace Temple of Lord Jagannath</title>
<link rel="canonical" href="...">
```

### Variables You Can Set (before including header.php)

| Variable | Default | Purpose |
|----------|---------|---------|
| `$pageTitle` | `SITE_NAME` | Page title (appended with `| SITE_NAME` automatically) |
| `$metaDescription` | Default site description | Meta description tag (max ~160 chars recommended) |
| `$metaKeywords` | Default keywords | Comma-separated keywords |
| `$canonicalUrl` | Auto-detected from URL | Canonical URL (prevents duplicate content) |
| `$ogImage` | Default OG image (`og-default.svg`) | Open Graph image URL |
| `$ogType` | `'website'` | Open Graph type (`website`, `article`, etc.) |
| `$pageType` | `'default'` | Used by schema.php for choosing schema types |

### Example: Override SEO for a Custom Page

```php
<?php
// Before including header:
$pageTitle = 'Special Event - Janmashtami Celebrations';
$metaDescription = 'Celebrate Janmashtami 2026 at ISKCON Seshadripuram. Join us for midnight arati, dance, drama, and prasadam distribution.';
$ogImage = BASE_URL . 'assets/images/janmashtami-2026-og.jpg';
$canonicalUrl = BASE_URL . 'festivals/grand-festivals/janmashtami/';
$pageType = 'festival';
include 'partials/header.php';
?>
```

---

## 3. Canonical URLs

Canonical URLs are auto-detected in `header.php` and set in `<link rel="canonical">`.

### Auto-Detection Logic

```php
// 1. If $canonicalUrl was set by the page, use it
// 2. If the URL matches a festival pattern, build SEO-friendly URL:
//    /festivals/grand-festivals/janmashtami.php → /festivals/grand-festivals/janmashtami/
// 3. Fall back to the raw request path
```

### When to Override

- **Festival detail pages** (`modules/Festivals/content/detail.php`) override `$canonicalUrl` with the SEO-friendly festival URL format
- **Blog detail pages** — canonical is auto-generated from the slug
- **Pagination pages** — should set canonical to page 1 to prevent duplicate content
- **Filter/Search pages** — should set canonical to the base search page

### Best Practices

- Always set `$canonicalUrl` for pages that can be reached through multiple URLs
- Use trailing slashes consistently: `/festivals/grand-festivals/janmashtami/` not `/festivals/grand-festivals/janmashtami`
- The `<base>` tag in header.php means all relative URLs resolve against `BASE_URL`

---

## 4. Open Graph & Twitter Cards

Set automatically in `header.php`:

### Open Graph (Facebook, LinkedIn, WhatsApp, etc.)

```html
<meta property="og:title" content="...">
<meta property="og:description" content="...">
<meta property="og:url" content="...">
<meta property="og:image" content="...">
<meta property="og:type" content="website">
<meta property="og:site_name" content="ISKCON The Palace Temple of Lord Jagannath">
<meta property="og:locale" content="en_IN">
```

### Twitter Cards

```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="...">
<meta name="twitter:description" content="...">
<meta name="twitter:image" content="...">
```

### OG Image Guidelines

- **Default**: `assets/images/og-default.svg` (ISKCON logo)
- **Override**: Set `$ogImage` before including header
- **Best size**: 1200×630px (OG standard), < 5MB
- **Format**: PNG or JPG preferred; SVG works but some platforms may not render it

---

## 5. Schema.org Structured Data (schema.php)

Located in `modules/Kernel/partials/schema.php`. Included from `footer.php` and outputs JSON-LD `<script>` tags.

### Schema Types by Page Type

Set `$pageType` before including `header.php` to activate the appropriate schema:

| `$pageType` Value | Schema Types Generated | When to Use |
|------------------|----------------------|-------------|
| `'home'` | `HinduTemple`, `WebSite` (with search action), `BreadcrumbList` | Homepage only |
| `'festival'` | `HinduTemple`, `BreadcrumbList`, `Event` | Festival detail pages (also needs `$eventData`) |
| `'blog'` | `HinduTemple`, `BreadcrumbList`, `Article` | Blog detail pages (also needs `$articleData`) |
| `'donate'` | `HinduTemple`, `BreadcrumbList`, `FAQPage` | Donation/seva pages with FAQ (needs `$faqItems`) |
| `'gallery'` | `HinduTemple`, `BreadcrumbList`, `ImageGallery` | Darshan gallery page (needs `$galleryImages`) |
| `'contact'` | `HinduTemple`, `BreadcrumbList` | Contact page |
| `'about'` | `HinduTemple`, `BreadcrumbList` | About pages (main page and sub-pages) |
| `'service'` | `HinduTemple`, `BreadcrumbList` | Service pages (23 sub-pages + services listing) |
| `'course'` | `HinduTemple`, `BreadcrumbList` | Course pages (Bhakti Shastri, Bhakti Vaibhava, IDC, TTC, BIHE) |
| `'booking'` | `HinduTemple`, `BreadcrumbList` | Booking gateway, puja, yagya, and guest house pages |
| `'yatra'` | `HinduTemple`, `BreadcrumbList` | Yatra/pilgrimage listing and detail pages |
| `'default'` | `HinduTemple`, `BreadcrumbList` | All other pages |

### Required Data Variables for Schema

```php
// For festival pages ($pageType = 'festival'):
$eventData = [
    'name'        => 'Janmashtami 2026',
    'description' => 'Celebration of Lord Krishna's appearance...',
    'image'       => BASE_URL . 'assets/images/janmashtami.jpg',
    'url'         => BASE_URL . 'festivals/grand-festivals/janmashtami/',
    'startDate'   => '2026-08-15',  // ISO 8601 date
    'endDate'     => '2026-08-16',
    'category'    => 'festival',     // From donation_causes.category
];

// For blog pages ($pageType = 'blog'):
$articleData = [
    'headline'       => 'Blog Post Title',
    'description'    => 'Brief description...',
    'image'          => BASE_URL . $blog['banner_image'],
    'datePublished'  => $blog['published_date'],
    'dateModified'   => $blog['updated_at'],
    'author'         => 'ISKCON The Palace Temple of Lord Jagannath',
    'url'            => BASE_URL . 'blogs/' . $blog['slug'],
    'tags'           => ['Festival', 'Krishna'],  // Optional
];

// For donation pages with FAQ ($pageType = 'donate'):
$faqItems = [
    ['question' => 'How is my donation used?', 'answer' => 'Donations are allocated...'],
];
```

### Organization Schema (Always Present)

All pages automatically get `HinduTemple` schema with:
- Name, address, phone, email
- Opening hours (daily 5:00 AM – 8:30 PM)
- Founding date (January 31, 1998)
- Social media links (Facebook, Instagram, YouTube)
- Founder (A.C. Bhaktivedanta Swami Prabhupada)

---

## 6. XML Sitemap (sitemap.php)

Located at `/sitemap.xml` (rewritten via `.htaccess` to `modules/Content/content/sitemap.php`).

### What It Includes

| Section | Source | Pages |
|---------|--------|-------|
| **Static pages** | Hardcoded array | ~55 pages (home, about, services, courses, booking, yatra, etc.) |
| **Blogs** | `blogs` table (`is_published = 1`) | All published blog posts |
| **Festivals** | `donation_causes` table (`is_active = 1`) | All active festivals mapped to category URLs |
| **Donation services** | `donation_causes` table (`page_type = 'donation'`) | All donation/sevá pages |

### Priority & Frequency Rules

| Page Type | `changefreq` | `priority` | Rationale |
|-----------|-------------|-----------|-----------|
| Homepage | `daily` | `1.0` | Highest priority — link destination |
| Festival listings | `weekly` | `0.9` | Seasonal content, updated regularly |
| Blogs | `daily` | `0.8` | New content added frequently |
| About pages | `monthly` | `0.6` | Static content, rarely changes |
| Service pages | `monthly` | `0.5` | Static content, rarely changes |

### Adding a New Page Type to the Sitemap

Edit `modules/Content/content/sitemap.php` and add a new section following the existing patterns:

```php
// Add static pages to the $staticPages array
['loc' => BASE_URL . 'my-new-page', 'freq' => 'weekly', 'pri' => '0.7'],

// Or add a dynamic query section (see blogs/festivals sections for patterns)
try {
    $db = getDB();
    // Query and add entries
} catch (Exception $e) {
    // Skip if DB unavailable
}
```

### Sitemap Caching

- Cache header: `Cache-Control: public, max-age=10800` (3 hours)
- No server-side file caching — regenerated on each request
- For high-traffic sites, consider caching the sitemap output to a file

---

## 7. SEO-Friendly URLs (.htaccess)

The `.htaccess` file provides clean URLs for all content types:

| URL Pattern | Example | Benefit |
|-------------|---------|---------|
| `/festivals/grand-festivals/janmashtami/` | SEO-friendly festival URL with category hierarchy | Keyword-rich URL path |
| `/festivals/ekadashi/putrada/` | Clear categorization | Hierarchical understanding for search engines |
| `/blogs/welcome-to-iskcon/` | Blog posts without `.php` | Clean, readable URLs |
| `/donate/janmashtami/` | Donation causes | Keyword in URL |
| `/yatra/vrindavan/` | Yatra packages | Destination-as-URL |

---

## 8. Admin SEO Fields (Blogs & Festivals)

### Blog SEO Fields

In the blog editor (`/admin/blog-edit.php`):

| Field | Purpose | Best Practice |
|-------|---------|---------------|
| `meta_title` | Custom title tag override | 50-60 chars, include primary keyword |
| `meta_description` | Search snippet description | 150-160 chars, engaging summary with keyword |

The blog detail page (`modules/Blogs/content/detail.php`) sets:
- `$pageTitle = $blog['meta_title'] ?: $blog['title']`
- `$metaDescription` from blog description
- `$pageType = 'blog'`
- `$articleData` for Article schema

### Festival SEO Fields

In the festival editor (`/admin/festival-edit.php`):

| Field | Purpose | Best Practice |
|-------|---------|---------------|
| `meta_title` | Custom title tag override | 50-60 chars, include festival name + year |
| `meta_description` | Search snippet description | 150-160 chars |

The festival detail page (`modules/Festivals/content/detail.php`) sets:
- `$pageTitle = $festival['meta_title'] ?: $festival['title']`
- `$metaDescription` truncated to 160 chars from description
- `$pageType = 'festival'`
- `$canonicalUrl` = SEO-friendly festival URL
- `$eventData` for Event schema

---

## 9. Adding SEO to a New Page

### Step-by-Step

```php
<?php
// 1. Set SEO variables BEFORE including header
$pageTitle = 'My New Page Title';                       // Required
$metaDescription = 'Brief description for search...';   // Recommended (150-160 chars)
$canonicalUrl = BASE_URL . 'my-new-page/';             // Recommended if multiple URL variants
$ogImage = BASE_URL . 'assets/images/my-og-image.jpg'; // Optional — defaults to logo
$ogType = 'website';                                    // or 'article', 'product', etc.
$pageType = 'default';                                  // or 'festival', 'blog', 'about', 'service', 'course', 'booking', 'yatra', etc.

// 2. Include header (outputs all meta tags + Open Graph + Twitter Cards)
include 'partials/header.php';
?>

<!-- Page content -->

<?php
// 3. Schema.org structured data (if needed)
// For FAQ:
$faqItems = [
    ['question' => 'Q1', 'answer' => 'A1'],
];
// For Article:
$articleData = [
    'headline' => $pageTitle,
    'description' => $metaDescription,
    // ... see Section 5 for full structure
];

// 4. Include footer (outputs schema.org JSON-LD + closes HTML)
include 'partials/footer.php';
?>
```

### Adding to Sitemap

Add the new page to `modules/Content/content/sitemap.php`:
```php
['loc' => BASE_URL . 'my-new-page', 'freq' => 'monthly', 'pri' => '0.6'],
```

---

## 10. SEO Checklist

### For New Pages
- [ ] `$pageTitle` set to descriptive, keyword-rich title (50-60 chars)
- [ ] `$metaDescription` set (150-160 chars, includes primary keyword)
- [ ] `$canonicalUrl` set if page is reachable through multiple URLs
- [ ] `$ogImage` set to a relevant 1200×630px image
- [ ] `$pageType` set appropriately for schema.org markup
- [ ] Page added to sitemap (`sitemap.php`)
- [ ] Schema data prepared if applicable ($eventData, $articleData, $faqItems, etc.)

### For Content Changes
- [ ] Blog posts and festival pages have `meta_title` and `meta_description` filled in admin
- [ ] Images have descriptive `alt` text
- [ ] Links use descriptive anchor text (not "click here")
- [ ] Published content is visible in sitemap and searchable

### Technical SEO
- [ ] Security headers are present (CSP, HSTS, X-Frame-Options) — see `.htaccess`
- [ ] SSL/HTTPS is enforced (HSTS header with preload)
- [ ] Page load speed is optimized (minified assets, cache headers)
- [ ] Mobile responsive design is maintained
- [ ] No broken internal links (test before deployment)
- [ ] No duplicate content issues (canonical URLs set correctly)
