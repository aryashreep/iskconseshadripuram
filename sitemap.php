<?php
/**
 * Dynamic XML Sitemap Generator
 *
 * Queries the database for all public pages and generates a sitemap.xml
 * that helps search engines discover all content efficiently.
 *
 * Access: /sitemap.xml (rewritten to /sitemap.php via .htaccess)
 */

// Suppress errors to keep XML clean
error_reporting(0);

// Bootstrap minimal context without full header/footer
require_once __DIR__ . '/config.php';

// Set XML content type
header('Content-Type: application/xml; charset=utf-8');
// Cache for 3 hours
header('Cache-Control: public, max-age=10800');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Helper to add a URL entry
function sitemap_add(string $loc, string $lastmod = '', string $changefreq = 'monthly', string $priority = '0.5'): void {
    $loc = htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    if ($lastmod) {
        echo "    <lastmod>" . htmlspecialchars($lastmod, ENT_XML1) . "</lastmod>\n";
    }
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

// =========================================
// 1. STATIC PAGES (highest priority)
// =========================================
$staticPages = [
    ['loc' => BASE_URL,                                       'freq' => 'daily',   'pri' => '1.0'],
    ['loc' => BASE_URL . 'about/',                            'freq' => 'weekly',  'pri' => '0.8'],
    ['loc' => BASE_URL . 'about/founder-acharya',             'freq' => 'monthly', 'pri' => '0.6'],
    ['loc' => BASE_URL . 'about/history-of-iskcon',           'freq' => 'monthly', 'pri' => '0.6'],
    ['loc' => BASE_URL . 'about/hare-krishna-movement',       'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'about/our-mission',                 'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'about/our-philosophy',              'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'about/golden-temple',               'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'about/temple-schedule',             'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'darshan',                            'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'festivals/',                        'freq' => 'weekly',  'pri' => '0.9'],
    ['loc' => BASE_URL . 'festivals/grand-festivals/',        'freq' => 'weekly',  'pri' => '0.8'],
    ['loc' => BASE_URL . 'festivals/ekadashi/',               'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'festivals/appearance/',             'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'festivals/disappearance/',          'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'festivals/events/',                 'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'festivals/vaishnava-calendar/',     'freq' => 'monthly', 'pri' => '0.7'],
    ['loc' => BASE_URL . 'blogs',                              'freq' => 'daily',   'pri' => '0.8'],
    ['loc' => BASE_URL . 'donate/',                            'freq' => 'weekly',  'pri' => '0.8'],
    ['loc' => BASE_URL . 'contact',                            'freq' => 'monthly', 'pri' => '0.6'],
    ['loc' => BASE_URL . 'services/',                          'freq' => 'monthly', 'pri' => '0.7'],
    ['loc' => BASE_URL . 'services/food-for-life',             'freq' => 'monthly', 'pri' => '0.6'],
    ['loc' => BASE_URL . 'services/sunday-feast',              'freq' => 'monthly', 'pri' => '0.6'],
    ['loc' => BASE_URL . 'services/harinam-sankirtana',        'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/harinam-initiation',        'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/bhakti-vriksha',            'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/bhakti-sadan',              'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/life-membership',           'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/krishna-sadhaka',           'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/krishna-upasaka',           'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/krishna-sevaka',            'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/krishna-fun-school',        'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/music-school',              'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/vaishnavi-forum',           'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/youth-forum',               'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/corporate-programs',        'freq' => 'monthly', 'pri' => '0.4'],
    ['loc' => BASE_URL . 'services/siksha',                    'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/function-hall',             'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/govindas-prasadam',         'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'services/new-rajapur',               'freq' => 'monthly', 'pri' => '0.4'],
    ['loc' => BASE_URL . 'services/sraddhavan',                'freq' => 'monthly', 'pri' => '0.4'],
    ['loc' => BASE_URL . 'services/sri-guru-carana-ashraya',   'freq' => 'monthly', 'pri' => '0.4'],
    ['loc' => BASE_URL . 'services/srila-prabhupada-ashraya',  'freq' => 'monthly', 'pri' => '0.4'],
    ['loc' => BASE_URL . 'services/our-centers',               'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'courses/bhakti-shastri',             'freq' => 'monthly', 'pri' => '0.6'],
    ['loc' => BASE_URL . 'courses/bhakti-vaibhava',            'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'courses/bhaktivedanta-education',    'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'courses/idc',                        'freq' => 'monthly', 'pri' => '0.4'],
    ['loc' => BASE_URL . 'courses/teachers-training',          'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'booking/',                           'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'booking/guest-house',                'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'yatra/',                             'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'yatra/panihati',                     'freq' => 'weekly',  'pri' => '0.7'],
    ['loc' => BASE_URL . 'seva',                               'freq' => 'monthly', 'pri' => '0.6'],
    ['loc' => BASE_URL . 'resources',                          'freq' => 'monthly', 'pri' => '0.5'],
    ['loc' => BASE_URL . 'forums',                             'freq' => 'monthly', 'pri' => '0.5'],
];
foreach ($staticPages as $p) {
    sitemap_add($p['loc'], '', $p['freq'], $p['pri']);
}

// =========================================
// 2. DYNAMIC BLOGS
// =========================================
try {
    $db = getDB();
    $stmt = $db->query("SELECT slug, published_date, updated_at FROM blogs WHERE is_published = 1 ORDER BY published_date DESC");
    $blogs = $stmt->fetchAll();
    foreach ($blogs as $b) {
        $lastmod = $b['updated_at'] ? date('Y-m-d', strtotime($b['updated_at'])) : ($b['published_date'] ?: '');
        sitemap_add(BASE_URL . 'blogs/' . urlencode($b['slug']), $lastmod, 'monthly', '0.6');
    }
} catch (Exception $e) {
    // DB not available — skip blogs
}

// =========================================
// 3. DYNAMIC FESTIVALS (from donation_causes)
// =========================================
try {
    $db = getDB();
    $stmt_festivals = $db->query(
        "SELECT slug, updated_at, category FROM donation_causes WHERE is_active = 1 ORDER BY category, sort_order"
    );
    $festivals = $stmt_festivals->fetchAll();
    $festivalCategories = [
        'festival' => 'grand-festivals',
        'ekadashi' => 'ekadashi',
        'appearance' => 'appearance',
        'disappearance' => 'disappearance',
        'event' => 'events',
    ];
    foreach ($festivals as $f) {
        $lastmod = $f['updated_at'] ? date('Y-m-d', strtotime($f['updated_at'])) : '';
        $catPath = $festivalCategories[$f['category']] ?? 'festivals';
        $url = BASE_URL . 'festivals/' . $catPath . '/' . urlencode($f['slug']);
        sitemap_add($url, $lastmod, 'monthly', '0.6');
    }
} catch (Exception $e) {
    // DB not available — skip festivals
}

// =========================================
// 4. DONATION SERVICE PAGES (from donation_causes — service/general)
// =========================================
try {
    $db = getDB();
    $stmt_services = $db->query(
        "SELECT slug, updated_at FROM donation_causes WHERE is_active = 1 AND page_type = 'donation' ORDER BY sort_order"
    );
    $services = $stmt_services->fetchAll();
    foreach ($services as $s) {
        $lastmod = $s['updated_at'] ? date('Y-m-d', strtotime($s['updated_at'])) : '';
        sitemap_add(BASE_URL . 'donate/' . urlencode($s['slug']), $lastmod, 'monthly', '0.5');
    }
} catch (Exception $e) {
    // Skip
}

echo '</urlset>' . "\n";
