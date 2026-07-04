<?php
/**
 * Blog Migration Script
 * Hybrid approach: parses detail.php's \$blogs array by extracting from file content,
 * includes index.php via ob_start() for card data.
 * 
 * Run: php database/migrations/migrate_blogs.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

// ---------------------------------------------------------------------------
// Blog helpers (moved here from config.php after production pages switched to DB)
// ---------------------------------------------------------------------------

function get_blog_date($slug) {
  $seed = crc32($slug);
  srand($seed);
  $start_time = strtotime('2016-06-29');
  $end_time = strtotime('2026-06-29');
  $rand_time = rand($start_time, $end_time);
  $date_str = date('F d, Y', $rand_time);
  srand();
  return $date_str;
}

function get_blog_tags($slug, $title = '') {
  $manual = [
    'hindu-ratna-award-jayapataka-swami' => ['Jayapataka Swami', 'Preaching', 'Awards'],
    'six-categories-of-avatars-krishna' => ['Philosophy', 'Avatars', 'Scriptures'],
    'death-row-prisoner-pardoned-krsna-devotee' => ['Devotee Stories', 'Lifestyle', 'Miracles'],
    'no-limit-to-gurus-mercy-jayapataka-swami' => ['Jayapataka Swami', 'Miracles', 'Devotee Stories'],
    'best-topic-to-hear-ramananda-raya-chaitanya' => ['Lord Chaitanya', 'Philosophy', 'Scriptures'],
    'aindra-prabhu-kirtan-revolution-vrindavan' => ['Music', 'Kirtan', 'Vrindavan'],
    'radha-govinda-dasa-babaji-biography' => ['Biography', 'Devotee Stories', 'Puri'],
    'sri-caitanya-mahaprabhu-appearance-day' => ['Festivals', 'Lord Chaitanya', 'Pastimes'],
    'madhavas-rock-band-interview' => ['Music', 'Chanting', 'Interview'],
    'family-fortune-godruma-dasa' => ['Lifestyle', 'Devotee Stories', 'Family'],
    'sri-narasimha-kavacha-stotra' => ['Prayers', 'Narasimha', 'Scriptures'],
    '108-divya-desams' => ['Deities', 'Pilgrimage', 'Temples'],
    'favorable-unfavorable-devotional-principles' => ['Philosophy', 'Bhakti', 'Sadhana'],
    'grihastha-ashram-householder' => ['Lifestyle', 'Grihastha', 'Family'],
    'learning-tolerance-shipwrecked-sailor' => ['Philosophy', 'Lifestyle', 'Tolerance'],
    'the-show-must-go-on-radha-madhava' => ['Devotee Stories', 'Sadhana', 'Chanting'],
    'mayapur-lights-up-kartik' => ['Festivals', 'Kartik', 'Mayapur'],
    'gopal-krishna-goswami-president-pranab-mukherjee' => ['Preaching', 'Gita', 'Award'],
    'mahaprabhu-meets-lord-siva' => ['Lord Chaitanya', 'Pastimes', 'Lord Shiva'],
    'physiognomy-of-a-pure-devotee' => ['Srila Prabhupada', 'Appreciation'],
    'rr-nagar-bhakti-sadan' => ['Preaching', 'Community', 'Inauguration']
  ];
  if (isset($manual[$slug])) { return $manual[$slug]; }
  $tags = [];
  $text = strtolower($slug . ' ' . $title);
  if (strpos($text, 'gita') !== false || strpos($text, 'gītā') !== false) { $tags[] = 'Bhagavad Gita'; }
  if (strpos($text, 'prabhupada') !== false || strpos($text, 'prabhupāda') !== false) { $tags[] = 'Srila Prabhupada'; }
  if (strpos($text, 'chaitanya') !== false || strpos($text, 'caitanya') !== false || strpos($text, 'mahaprabhu') !== false) { $tags[] = 'Lord Chaitanya'; }
  if (strpos($text, 'narasimha') !== false || strpos($text, 'nrsimha') !== false) { $tags[] = 'Lord Narasimha'; }
  if (strpos($text, 'kirtan') !== false || strpos($text, 'chant') !== false || strpos($text, 'japa') !== false || strpos($text, 'holy-name') !== false) { $tags[] = 'Chanting'; }
  if (strpos($text, 'philosophy') !== false || strpos($text, 'soul') !== false || strpos($text, 'liberation') !== false || strpos($text, 'karma') !== false) { $tags[] = 'Philosophy'; }
  if (strpos($text, 'ekadashi') !== false || strpos($text, 'ekādaśī') !== false) { $tags[] = 'Ekadashi'; }
  if (strpos($text, 'kartik') !== false || strpos($text, 'damodara') !== false) { $tags[] = 'Kartik'; }
  if (strpos($text, 'festival') !== false || strpos($text, 'appearance') !== false || strpos($text, 'janmashtami') !== false) { $tags[] = 'Festivals'; }
  if (strpos($text, 'preach') !== false || strpos($text, 'distribution') !== false || strpos($text, 'book') !== false) { $tags[] = 'Preaching'; }
  if (empty($tags)) {
    $categories = ['Philosophy', 'Devotion', 'Lifestyle', 'Scriptures', 'Pastimes'];
    $tags[] = $categories[strlen($slug) % count($categories)];
    $tags[] = 'General';
  }
  return array_slice(array_unique($tags), 0, 3);
}

echo "=== Blog Migration ===\n\n";

// -------------------------------------------------------
// 1. Create blogs table if not exists
// -------------------------------------------------------
$db = getDB();
$db->exec("
    CREATE TABLE IF NOT EXISTS `blogs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(255) NOT NULL UNIQUE,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `icon` VARCHAR(100) NOT NULL,
        `banner_image` VARCHAR(500) DEFAULT NULL,
        `published_date` DATE DEFAULT NULL,
        `tags` TEXT DEFAULT NULL,
        `content_body` LONGTEXT DEFAULT NULL,
        `meta_title` VARCHAR(255) DEFAULT NULL,
        `meta_description` VARCHAR(500) DEFAULT NULL,
        `is_published` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "  [OK] Blogs table ready.\n\n";

// -------------------------------------------------------
// 2. Extract \$blogs array from detail.php via file content parsing
// -------------------------------------------------------
echo "Step 1/4: Extracting detail content... ";

$detailSrc = file_get_contents('blogs/detail.php');

// Find $blogs = [ start
$startMarker = '$blogs = [';
$posStart = strpos($detailSrc, $startMarker);
if ($posStart === false) {
    die("ERROR: Could not find \$blogs in blogs/detail.php\n");
}

// Find matching ]; by bracket counting
$arrStart = $posStart + strlen($startMarker);
$depth = 0;
$inString = false;
$stringChar = null;
$posEnd = $arrStart;
$len = strlen($detailSrc);

for ($i = $arrStart; $i < $len; $i++) {
    $ch = $detailSrc[$i];
    if ($inString) {
        if ($ch === '\\' && $i + 1 < $len) { $i++; continue; }
        if ($ch === $stringChar) { $inString = false; }
        continue;
    }
    if ($ch === "'" || $ch === '"') { $inString = true; $stringChar = $ch; continue; }
    if ($ch === '[') { $depth++; }
    elseif ($ch === ']') {
        if ($depth === 0) { $posEnd = $i + 1; break; }
        $depth--;
    }
}

$arrayCode = substr($detailSrc, $posStart, $posEnd - $posStart) . ';';
$blogs = [];
eval($arrayCode);
$detailBlogs = $blogs ?? [];

echo "found " . count($detailBlogs) . " articles.\n";

// -------------------------------------------------------
// 3. Get card data by including index.php with output buffering
// -------------------------------------------------------
echo "Step 2/4: Loading card data... ";

$_GET['page'] = 1;
$_GET['tag'] = '';
ob_start();
require 'blogs/index.php';
ob_end_clean();

$cardBlogs = $all_blogs ?? [];
echo "found " . count($cardBlogs) . " cards.\n";

// -------------------------------------------------------
// 4. Process dates & tags
// -------------------------------------------------------
echo "Step 3/4: Processing dates & tags... ";
foreach ($cardBlogs as &$b) {
    if ($b['slug'] !== 'rr-nagar-bhakti-sadan') {
        $b['date'] = get_blog_date($b['slug']);
    }
    $b['tags'] = get_blog_tags($b['slug'], $b['title']);
}
unset($b);
echo "done.\n\n";

// -------------------------------------------------------
// 5. Helper: render blog sections into HTML
// -------------------------------------------------------
function renderBlogContent(array $detail): ?string {
    $parts = [];
    if (!empty($detail['intro'])) {
        $parts[] = '<div class="blog-intro" style="font-size:var(--font-size-lg);color:var(--text-dark);font-weight:500;line-height:1.7;margin-bottom:var(--space-lg);">';
        $parts[] = '  <p>' . $detail['intro'] . '</p>';
        $parts[] = '</div>';
    }
    if (!empty($detail['sections'])) {
        foreach ($detail['sections'] as $section) {
            $parts[] = '<div class="blog-section" style="margin-bottom:var(--space-xl);">';
            if (!empty($section['title'])) {
                $parts[] = '  <h2 style="color:var(--primary);font-family:var(--font-heading);font-size:var(--font-size-xl);font-weight:600;margin-bottom:var(--space-sm);">' . $section['title'] . '</h2>';
            }
            if (!empty($section['content'])) {
                $parts[] = '  <div style="font-size:var(--font-size-base);color:var(--text);line-height:1.8;">' . $section['content'] . '</div>';
            }
            $parts[] = '</div>';
        }
    }
    return empty($parts) ? null : implode("\n", $parts);
}

// -------------------------------------------------------
// 6. Insert into blogs table
// -------------------------------------------------------
echo "Step 4/4: Inserting into database...\n";

$stmt = $db->prepare(
    "INSERT INTO blogs (slug, title, description, icon, banner_image, published_date, tags, content_body, is_published)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
     ON DUPLICATE KEY UPDATE 
       title = VALUES(title), description = VALUES(description),
       icon = VALUES(icon), banner_image = VALUES(banner_image),
       published_date = VALUES(published_date), tags = VALUES(tags),
       content_body = VALUES(content_body), updated_at = CURRENT_TIMESTAMP"
);

$count = 0;
$withContent = 0;
$errors = 0;

foreach ($cardBlogs as $blog) {
    $slug = $blog['slug'];
    $title = $blog['title'];
    $desc = $blog['desc'];
    $icon = $blog['icon'];
    $bannerImage = $blog['banner_image'] ?? null;
    $pubDateStr = $blog['date'] ?? '';
    $tags = $blog['tags'] ?? [];

    $mysqlDate = null;
    if (!empty($pubDateStr)) {
        $ts = strtotime($pubDateStr);
        if ($ts !== false) { $mysqlDate = date('Y-m-d', $ts); }
    }

    $contentBody = null;
    if (isset($detailBlogs[$slug])) {
        $detail = $detailBlogs[$slug];
        $contentBody = renderBlogContent($detail);
        if (!empty($detail['date'])) {
            $ts = strtotime($detail['date']);
            if ($ts !== false) { $mysqlDate = date('Y-m-d', $ts); }
        }
    }

    try {
        $stmt->execute([
            $slug, $title, $desc, $icon, $bannerImage,
            $mysqlDate, json_encode($tags), $contentBody,
        ]);
        $count++;
        if ($contentBody) $withContent++;
        $size = $contentBody ? strlen($contentBody) . ' chars' : 'card only';
        echo "  [OK] " . str_pad(substr($slug, 0, 52), 52) . " {$size}\n";
    } catch (Exception $e) {
        echo "  [ERR] {$slug}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Migration Complete ===\n";
echo "  Total: {$count} | With content: {$withContent} | Cards only: " . ($count - $withContent) . " | Errors: {$errors}\n";
