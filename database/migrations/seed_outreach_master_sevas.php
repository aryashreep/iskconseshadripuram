<?php
/**
 * Migration: Seed Outreach Master Sevas
 * 
 * Adds 8 Outreach sevas to the Master Seva Catalog (category_id=7):
 * 1. Bhagavad Gita Distribution
 * 2. Book Distribution
 * 3. Children Gift Seva
 * 4. College Preaching Sponsor
 * 5. Harinam Sponsor
 * 6. Digital Preaching
 * 7. Temple Publications
 * 8. Festival Publicity
 * 
 * Idempotent — safe to re-run.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/donation-helpers.php';

$db = getDB();
echo "=== Seeding Outreach Master Sevas ===\n\n";

// Step 1: Verify category exists
$catStmt = $db->prepare("SELECT id, name FROM master_seva_categories WHERE slug = 'outreach-sevas' AND is_active = 1");
$catStmt->execute();
$category = $catStmt->fetch();

if (!$category) {
    echo "ERROR: Outreach Sevas category not found!\n";
    exit(1);
}
echo "[OK] Category found: {$category['name']} (id={$category['id']})\n";
$category_id = (int)$category['id'];

// Step 2: Define the 8 Outreach sevas
$outreachSevas = [
    [
        'slug'             => 'bhagavad-gita-distribution',
        'name'             => 'Bhagavad Gita Distribution',
        'default_amount'   => 1008,
        'description'      => 'Sponsor the distribution of Bhagavad Gitas to seekers and students. Each copy carries the divine message of Lord Krishna to new hearts and homes.',
        'short_description' => 'Distribute Bhagavad Gitas to seekers',
        'icon'             => 'fa-book-open',
        'sort_order'       => 10,
    ],
    [
        'slug'             => 'book-distribution',
        'name'             => 'Book Distribution',
        'default_amount'   => 508,
        'description'      => 'Support the distribution of spiritual books including Srimad Bhagavatam, Chaitanya Charitamrita, and other Vedic literature to spread the message of devotional service.',
        'short_description' => 'Distribute spiritual books and literature',
        'icon'             => 'fa-books',
        'sort_order'       => 20,
    ],
    [
        'slug'             => 'children-gift-seva',
        'name'             => 'Children Gift Seva',
        'default_amount'   => 301,
        'description'      => 'Delight children with gifts during festivals and temple programs. Help nurture the next generation of devotees with love and Krishna consciousness.',
        'short_description' => 'Gifts for children during festivals',
        'icon'             => 'fa-gift',
        'sort_order'       => 30,
    ],
    [
        'slug'             => 'college-preaching-sponsor',
        'name' => 'College Preaching Sponsor',
        'default_amount'   => 2008,
        'description'      => 'Sponsor preaching programs at colleges and universities. Engage students with spiritual knowledge, prasadam, and cultural presentations.',
        'short_description' => 'Sponsor preaching at colleges',
        'icon'             => 'fa-graduation-cap',
        'sort_order'       => 40,
    ],
    [
        'slug'             => 'harinam-sponsor',
        'name'             => 'Harinam Sponsor',
        'default_amount'   => 1008,
        'description'      => 'Sponsor public congregational chanting (Harinam) programs to spread the holy names of the Lord throughout the community and beyond.',
        'short_description' => 'Sponsor public Harinam chanting',
        'icon'             => 'fa-music',
        'sort_order'       => 50,
    ],
    [
        'slug'             => 'digital-preaching',
        'name'             => 'Digital Preaching',
        'default_amount'   => 1008,
        'description'      => 'Support online preaching through live streams, social media campaigns, YouTube content, and digital outreach to spread Krishna consciousness globally.',
        'short_description' => 'Online preaching and digital outreach',
        'icon'             => 'fa-video',
        'sort_order'       => 60,
    ],
    [
        'slug'             => 'temple-publications',
        'name'             => 'Temple Publications',
        'default_amount'   => 508,
        'description'      => 'Sponsor the publication of temple magazines, newsletters, spiritual literature, and educational materials for distribution to devotees and well-wishers.',
        'short_description' => 'Sponsor temple magazines and publications',
        'icon'             => 'fa-newspaper',
        'sort_order'       => 70,
    ],
    [
        'slug'             => 'festival-publicity',
        'name'             => 'Festival Publicity',
        'default_amount'   => 1508,
        'description'      => 'Help publicize temple festivals through banners, posters, social media, local advertising, and community outreach to attract and welcome new devotees.',
        'short_description' => 'Promote festivals through publicity',
        'icon'             => 'fa-bullhorn',
        'sort_order'       => 80,
    ],
];

// Step 3: Insert/update sevas
$createdCount = 0;
$updatedCount = 0;

foreach ($outreachSevas as $sData) {
    // Check if slug already exists
    $checkStmt = $db->prepare("SELECT id FROM master_sevas WHERE slug = ?");
    $checkStmt->execute([$sData['slug']]);
    $existingId = $checkStmt->fetchColumn();

    $data = [
        'slug'             => $sData['slug'],
        'name'             => $sData['name'],
        'category_id'      => $category_id,
        'default_amount'   => $sData['default_amount'],
        'description'      => $sData['description'],
        'short_description'=> $sData['short_description'] ?? null,
        'icon'             => $sData['icon'] ?? 'fa-hand-holding-heart',
        'allow_multiple'   => 1,
        'max_quantity'     => 99,
        'sort_order'       => $sData['sort_order'],
        'is_active'        => 1,
    ];

    if ($existingId) {
        // Update existing
        if (updateMasterSeva((int)$existingId, $data)) {
            $updatedCount++;
            echo "  [UPDATE] {$sData['name']} (id={$existingId})\n";
        } else {
            echo "  [FAIL]   {$sData['name']} — update failed\n";
        }
    } else {
        // Create new
        $newId = createMasterSeva($data);
        if ($newId) {
            $createdCount++;
            echo "  [CREATE] {$sData['name']} (id={$newId}) — ₹" . number_format($sData['default_amount']) . "\n";
        } else {
            echo "  [FAIL]   {$sData['name']} — create failed\n";
        }
    }
}

echo "\nSummary: {$createdCount} created, {$updatedCount} updated\n";

// Step 4: Link to outreach causes if they exist
$outreachCauseSlugs = ['book-distribution', 'preaching', 'harinam', 'outreach'];
$linkedCount = 0;

foreach ($outreachCauseSlugs as $causeSlug) {
    $causeStmt = $db->prepare("SELECT id, slug, title FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
    $causeStmt->execute([$causeSlug]);
    $cause = $causeStmt->fetch();
    
    if (!$cause) continue;
    
    echo "\n[OK] Found cause: {$cause['title']} (slug={$cause['slug']})\n";
    
    // Get all outreach sevas
    $sevaStmt = $db->prepare("SELECT id, name FROM master_sevas WHERE category_id = ? AND is_active = 1 ORDER BY sort_order");
    $sevaStmt->execute([$category_id]);
    $sevas = $sevaStmt->fetchAll();
    
    $causeId = (int)$cause['id'];
    $upsertStmt = $db->prepare("
        INSERT INTO donation_cause_master_sevas 
        (cause_id, master_seva_id, override_amount, override_description, override_max_quantity, sort_order, is_featured, is_active)
        VALUES (?, ?, NULL, NULL, NULL, ?, 0, 1)
        ON DUPLICATE KEY UPDATE
            sort_order = VALUES(sort_order),
            is_active = 1
    ");
    
    foreach ($sevas as $i => $seva) {
        try {
            $sOrder = ($i + 1) * 10;
            $upsertStmt->execute([$causeId, $seva['id'], $sOrder]);
            $linkedCount++;
        } catch (PDOException $e) {
            echo "  [LINK FAIL] {$seva['name']}: {$e->getMessage()}\n";
        }
    }
}

if ($linkedCount > 0) {
    echo "\nLinked {$linkedCount} seva-cause relationships\n";
} else {
    echo "\n[SKIP] No matching outreach causes found — sevas not linked to any cause.\n";
}

echo "\n=== Done! ===\n";
