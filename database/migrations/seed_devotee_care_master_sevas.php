<?php
/**
 * Migration: Seed Devotee Care Master Sevas
 *
 * Adds 7 Devotee Care sevas to the Master Seva Catalog (category_id=8):
 * 1. Devotee Care Sponsor
 * 2. Volunteer Care
 * 3. Sadhu Bhojan
 * 4. Vaishnava Seva
 * 5. Senior Devotee Care
 * 6. Youth Program Sponsor
 * 7. Children's Program Sponsor
 *
 * Idempotent — safe to re-run.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/donation-helpers.php';

$db = getDB();
echo "=== Seeding Devotee Care Master Sevas ===\n\n";

// Step 1: Verify category exists
$catStmt = $db->prepare("SELECT id, name FROM master_seva_categories WHERE slug = 'devotee-care-sevas' AND is_active = 1");
$catStmt->execute();
$category = $catStmt->fetch();

if (!$category) {
    echo "ERROR: Devotee Care Sevas category not found!\n";
    exit(1);
}
echo "[OK] Category found: {$category['name']} (id={$category['id']})\n";
$category_id = (int)$category['id'];

// Step 2: Define the 7 Devotee Care sevas
$devoteeCareSevas = [
    [
        'slug'              => 'devotee-care-sponsor',
        'name'              => 'Devotee Care Sponsor',
        'default_amount'    => 1008,
        'description'       => 'Sponsor the overall well-being and care of devotees at the temple. Your contribution supports prasadam, accommodation, and spiritual sustenance for visiting and resident devotees.',
        'short_description' => 'Support overall devotee well-being',
        'icon'              => 'fa-hands-helping',
        'sort_order'        => 10,
    ],
    [
        'slug'              => 'volunteer-care',
        'name'              => 'Volunteer Care',
        'default_amount'    => 508,
        'description'       => 'Show gratitude to our dedicated volunteers who serve selflessly. Support their meals, refreshments, transportation, and recognition during temple events and daily sevas.',
        'short_description' => 'Appreciate and support our volunteers',
        'icon'              => 'fa-people-carry',
        'sort_order'        => 20,
    ],
    [
        'slug'              => 'sadhu-bhojan',
        'name'              => 'Sadhu Bhojan',
        'default_amount'    => 1001,
        'description'       => 'Sponsor sanctified meals for sadhus, visiting sannyasis, and traveling Vaishnavas. Feeding a devotee of the Lord is considered one of the highest forms of charity.',
        'short_description' => 'Sponsor meals for sadhus and Vaishnavas',
        'icon'              => 'fa-utensils',
        'sort_order'        => 30,
    ],
    [
        'slug'              => 'vaishnava-seva',
        'name'              => 'Vaishnava Seva',
        'default_amount'    => 1008,
        'description'       => 'Serve the Vaishnava community through various acts of care and respect. Support their travel, accommodations, deity worship needs, and personal well-being as they spread Krishna consciousness.',
        'short_description' => 'Serve and support the Vaishnava community',
        'icon'              => 'fa-praying-hands',
        'sort_order'        => 40,
    ],
    [
        'slug'              => 'senior-devotee-care',
        'name'              => 'Senior Devotee Care',
        'default_amount'    => 1508,
        'description'       => 'Care for our senior devotees who have dedicated their lives to serving the Lord and the community. Support their medical needs, daily sustenance, comfortable living, and spiritual programs.',
        'short_description' => 'Medical and daily care for senior devotees',
        'icon'              => 'fa-heart',
        'sort_order'        => 50,
    ],
    [
        'slug'              => 'youth-program-sponsor',
        'name'              => 'Youth Program Sponsor',
        'default_amount'    => 1008,
        'description'       => 'Sponsor spiritual programs for young devotees and students. Support youth camps, educational workshops, cultural activities, and skill development programs rooted in Krishna consciousness.',
        'short_description' => 'Spiritual programs for youth and students',
        'icon'              => 'fa-user-graduate',
        'sort_order'        => 60,
    ],
    [
        'slug'              => 'children-program-sponsor',
        'name'              => 'Children\'s Program Sponsor',
        'default_amount'    => 508,
        'description'       => 'Nurture the spiritual education of children at the temple. Support Sunday school, storytelling programs, festivals, gifts, and cultural activities that plant the seeds of devotion in young hearts.',
        'short_description' => 'Spiritual education and activities for children',
        'icon'              => 'fa-child',
        'sort_order'        => 70,
    ],
];

// Step 3: Insert/update sevas
$createdCount = 0;
$updatedCount = 0;

foreach ($devoteeCareSevas as $sData) {
    $checkStmt = $db->prepare("SELECT id FROM master_sevas WHERE slug = ?");
    $checkStmt->execute([$sData['slug']]);
    $existingId = $checkStmt->fetchColumn();

    $data = [
        'slug'              => $sData['slug'],
        'name'              => $sData['name'],
        'category_id'       => $category_id,
        'default_amount'    => $sData['default_amount'],
        'description'       => $sData['description'],
        'short_description' => $sData['short_description'] ?? null,
        'icon'              => $sData['icon'] ?? 'fa-hands-helping',
        'allow_multiple'    => 1,
        'max_quantity'      => 99,
        'sort_order'        => $sData['sort_order'],
        'is_active'         => 1,
    ];

    if ($existingId) {
        if (updateMasterSeva((int)$existingId, $data)) {
            $updatedCount++;
            echo "  [UPDATE] {$sData['name']} (id={$existingId})\n";
        } else {
            echo "  [FAIL]   {$sData['name']} — update failed\n";
        }
    } else {
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

// Step 4: Link to devotee care causes if they exist
$devoteeCareCauseSlugs = ['daily-seva', 'nitya-seva', 'general-donation'];
$linkedCount = 0;

foreach ($devoteeCareCauseSlugs as $causeSlug) {
    $causeStmt = $db->prepare("SELECT id, slug, title FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
    $causeStmt->execute([$causeSlug]);
    $cause = $causeStmt->fetch();

    if (!$cause) continue;

    echo "\n[OK] Found cause: {$cause['title']} (slug={$cause['slug']})\n";

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
    echo "\n[SKIP] No matching devotee care causes found — sevas not linked to any cause.\n";
}

echo "\n=== Done! ===\n";
