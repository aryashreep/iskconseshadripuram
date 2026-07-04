<?php
/**
 * Festival Content Migration Script
 * 
 * Extracts <article> content from static festival PHP files (grand-festivals + ekadashi),
 * stores in donation_causes.content_body with {{BASE_URL}} placeholders
 * so URLs work across different environments.
 *
 * Usage: php database/migrate_festival_content.php
 *
 * To add more categories:
 *   - Add the glob pattern to getFestivalFileMap()
 *   - Add the category to the DB query
 */

require_once __DIR__ . '/../config.php';

/**
 * Map of category => glob pattern for festival PHP files
 * For categories where filename differs from DB slug, use manual slug mapping.
 */
function getFestivalCategories(): array {
    return [
        'festival' => [
            'pattern' => __DIR__ . '/../festivals/grand-festivals/*.php',
        ],
        'ekadashi' => [
            'pattern' => __DIR__ . '/../festivals/ekadashi/*.php',
        ],
        'appearance' => [
            'pattern' => __DIR__ . '/../festivals/appearance/*.php',
            // DB slugs have an '-appearance' suffix, filenames don't
            'slug_map' => [
                'sri-advaita-acharya' => 'sri-advaita-acharya-appearance',
                'srila-bhaktisiddhanta-sarasvati-thakura' => 'srila-bhaktisiddhanta-sarasvati-appearance',
                'srila-bhaktivinoda-thakura' => 'srila-bhaktivinoda-thakura-appearance',
                'srila-prabhupada' => 'srila-prabhupada-appearance',
            ],
        ],
        'disappearance' => [
            'pattern' => __DIR__ . '/../festivals/disappearance/*.php',
            // DB slugs use '-disappearance' suffix, some are shorter than filenames
            'slug_map' => [
                'gaura-kisora-dasa-babaji' => 'gaura-kisora-dasa-babaji-disappearance',
                'srila-bhaktisiddhanta-sarasvati-thakura' => 'srila-bhaktisiddhanta-disappearance',
                'srila-bhaktivinoda-thakura' => 'srila-bhaktivinoda-thakura-disappearance',
                'srila-jagannatha-dasa-babaji' => 'srila-jagannatha-dasa-babaji-disappearance',
                'srila-prabhupada' => 'srila-prabhupada-disappearance',
            ],
        ],
        'event' => [
            'pattern' => __DIR__ . '/../festivals/events/*.php',
            // Event pages have no <article> tag; use custom extract function
            'extract_func' => 'extractEventContent',
        ],
    ];
}

function getFestivalFileMap(): array {
    $map = [];
    foreach (getFestivalCategories() as $category => $config) {
        $pattern = $config['pattern'];
        $slugMap = $config['slug_map'] ?? null;
        $extractFunc = $config['extract_func'] ?? null;
        $files = glob($pattern);
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if ($name === 'index') continue;
            $slug = $slugMap ? ($slugMap[$name] ?? $name) : $name;
            $map[$category . ':' . $name] = [
                'path' => $file,
                'slug' => $slug,
                'category' => $category,
                'extract_func' => $extractFunc,
            ];
        }
    }
    return $map;
}

/**
 * Extract content from event pages (no <article> tag, different structure).
 * Grabs content from the container div in page-content up to the donation CTA.
 */
function extractEventContent($path) {
    $src = file_get_contents($path);
    if ($src === false) return null;

    // Find the first <div class="container" after <section class="page-content">
    $sectionStart = strpos($src, '<section class="page-content"');
    if ($sectionStart === false) return null;
    $containerStart = strpos($src, '<div class="container"', $sectionStart);
    if ($containerStart === false) return null;
    $contentStart = strpos($src, '>', $containerStart) + 1;

    // End: before donation CTA (search for the path without <?php prefix to handle whitespace variance)
    $donationEnd = strpos($src, "include_once __DIR__ . '/../../partials/donation-cta.php'", $contentStart);
    if ($donationEnd === false) {
        $donationEnd = strpos($src, '<!-- Contact & Bottom CTA -->', $contentStart);
    }
    if ($donationEnd === false) {
        $donationEnd = strpos($src, "include '../../partials/footer.php';", $contentStart);
    }
    if ($donationEnd === false) return null;

    $body = substr($src, $contentStart, $donationEnd - $contentStart);

    // Process BASE_URL
    $body = str_replace("<?php echo BASE_URL; ?>", "{{BASE_URL}}", $body);
    $body = preg_replace('/<\?php echo BASE_URL \. ([^;]+); \?>/', '{{BASE_URL}}' . '$1', $body);

    // Handle relative paths (events use ../../assets/ instead of BASE_URL)
    $body = str_replace('../../assets/images/', '{{BASE_URL}}assets/images/', $body);
    $body = str_replace('href="../../', 'href="{{BASE_URL}}', $body);

    // Remove PHP blocks
    $body = preg_replace('/<\?php (include|include_once|require|require_once) .*? \?>/s', '', $body);
    $body = preg_replace('/<\?php (if|else|elseif|endif|for|endfor|foreach|endforeach|while|endwhile)\b .*? \?>/sx', '', $body);
    $body = preg_replace('/<\?php\s*\?>/', '', $body);
    $body = preg_replace('/<\?php\s+/', '', $body);
    $body = str_replace('?>', '', $body);

    $body = preg_replace("/\n{4,}/", "\n\n", $body);
    return trim($body);
}

function extractArticle($path) {
    $src = file_get_contents($path);
    if ($src === false) return null;

    $start = strpos($src, '<article');
    if ($start === false) return null;
    $start = strpos($src, '>', $start) + 1;

    $end = strpos($src, '</article>', $start);
    if ($end === false) return null;

    $body = substr($src, $start, $end - $start);

    // Use {{BASE_URL}} placeholder instead of hardcoding the literal URL
    $body = str_replace("<?php echo BASE_URL; ?>", "{{BASE_URL}}", $body);
    $body = preg_replace('/<\?php echo BASE_URL \. ([^;]+); \?>/', '{{BASE_URL}}' . '$1', $body);

    // Remove PHP blocks
    $body = preg_replace('/<\?php (include|include_once|require|require_once) .*? \?>/s', '', $body);
    $body = preg_replace('/<\?php (if|else|elseif|endif|for|endfor|foreach|endforeach|while|endwhile)\b .*? \?>/sx', '', $body);
    $body = preg_replace('/<\?php\s*\?>/', '', $body);
    $body = preg_replace('/<\?php\s+/', '', $body);
    $body = str_replace('?>', '', $body);

    // Clean whitespace
    $body = preg_replace("/\n{3,}/", "\n\n", $body);
    return trim($body);
}

function extractStats($path) {
    $src = file_get_contents($path);
    if ($src === false) return null;
    if (!preg_match('/<h4[^>]*>.*?(?:Vrata & Worship Overview|Festival Overview|Holy Observance Overview|Fasting Overview).*?<\/h4>\s*<p[^>]*>(.*?)<\/p>/s', $src, $m)) return null;
    $html = $m[1];
    // Use {{BASE_URL}} placeholder
    $html = str_replace("<?php echo BASE_URL; ?>", "{{BASE_URL}}", $html);
    $html = preg_replace('/<\?php.*?\?>/s', '', $html);
    $parts = [];
    foreach (explode('<br>', $html) as $line) {
        $line = trim(strip_tags($line));
        if ($line === '') continue;
        if (preg_match('/^([^:]+):\s*(.*)$/s', $line, $p)) {
            $parts[] = trim($p[1]) . ': ' . trim($p[2]);
        }
    }
    return empty($parts) ? null : implode('|', $parts);
}

$db = getDB();
$files = getFestivalFileMap();
$ok = 0; $fail = 0;

echo "=== Festival Content Migration (with {{BASE_URL}} placeholders) ===\n\n";

foreach ($files as $key => $info) {
    $slug = $info['slug'];
    $path = $info['path'];
    $category = $info['category'];
    echo "  [$category] $slug ... ";

    $c = $db->prepare("SELECT id FROM donation_causes WHERE slug = ? AND category = ?");
    $c->execute([$slug, $category]);
    $r = $c->fetch();
    if (!$r) { echo "NO DB (slug=$slug, cat=$category)\n"; $fail++; continue; }

    // Use custom extract function if configured, otherwise default to extractArticle
    $extractFn = $info['extract_func'] ?? 'extractArticle';
    $body = $extractFn($path);
    if ($body === null || strlen($body) < 50) {
        echo "NO CONTENT (" . strlen($body ?? '') . ")\n";
        $fail++; continue;
    }

    $stats = extractStats($path);
    $u = $db->prepare("UPDATE donation_causes SET content_body = ?, quick_stats = ? WHERE id = ?");
    $u->execute([$body, $stats, $r['id']]);
    $info = strlen($body) . ' chars';
    if ($stats) $info .= ' + stats';
    echo "OK [$info]\n";
    $ok++;
}

echo "\nDone: $ok OK, $fail failed\n";
echo "Content stored with {{BASE_URL}} placeholders.\n";
