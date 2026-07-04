<?php
/**
 * Migration: Seed Rath Yatra Master Sevas
 * 
 * Adds 11 Rath Yatra sevas to the Master Seva Catalog (category_id=4).
 * Also links them to the Rath Yatra donation cause if it exists.
 * 
 * Idempotent — safe to re-run.
 */

require_once __DIR__ . '/../../config.php';

$db = getDB();
echo "=== Seeding Rath Yatra Master Sevas ===\n\n";

// Step 1: Verify category exists
$catStmt = $db->prepare("SELECT id, name FROM master_seva_categories WHERE id = 4 AND is_active = 1");
$catStmt->execute();
$category = $catStmt->fetch();

if (!$category) {
    echo "ERROR: Rath Yatra Sevas category (id=4) not found!\n";
    exit(1);
}
echo "[OK] Category found: {$category['name']} (id={$category['id']})\n";

// Step 2: Find Rath Yatra cause
$causeStmt = $db->prepare("SELECT id, slug, title FROM donation_causes WHERE slug IN ('ratha-yatra', 'rath-yatra') AND is_active = 1 LIMIT 1");
$causeStmt->execute();
$cause = $causeStmt->fetch();

if ($cause) {
    echo "[OK] Rath Yatra cause found: {$cause['title']} (slug={$cause['slug']}, id={$cause['id']})\n";
} else {
    echo "[WARN] No active Rath Yatra cause found. Sevas will be created but not linked.\n";
}

// Step 3: Define the 11 Rath Yatra sevas
$rathYatraSevas = [
    [
        'slug'             => 'rath-decoration',
        'name'             => 'Rath Decoration',
        'default_amount'   => 5008,
        'description'      => 'Sponsor the sacred decoration of Lord Jagannath\'s chariot for the grand Rath Yatra procession.',
        'sort_order'       => 10,
    ],
    [
        'slug'             => 'flower-decoration-for-rath',
        'name'             => 'Flower Decoration for Rath',
        'default_amount'   => 3008,
        'description'      => 'Offer fragrant flowers for decorating the divine chariot during Rath Yatra.',
        'sort_order'       => 20,
    ],
    [
        'slug'             => 'chariot-construction',
        'name'             => 'Chariot Construction',
        'default_amount'   => 25008,
        'description'      => 'Support the construction and preparation of the sacred chariot for Lord Jagannath.',
        'sort_order'       => 30,
    ],
    [
        'slug'             => 'chariot-maintenance',
        'name'             => 'Chariot Maintenance',
        'default_amount'   => 10008,
        'description'      => 'Help maintain and preserve the chariot so it can be used safely and beautifully for the festival.',
        'sort_order'       => 40,
    ],
    [
        'slug'             => 'chariot-rope-seva',
        'name'             => 'Chariot Rope Seva',
        'default_amount'   => 2108,
        'description'      => 'Sponsor the ceremonial ropes used to pull the Lord\'s chariot with devotion.',
        'sort_order'       => 50,
    ],
    [
        'slug'             => 'chariot-wheel-decoration',
        'name'             => 'Chariot Wheel Decoration',
        'default_amount'   => 3008,
        'description'      => 'Offer seva for adorning the wheels of the sacred chariot during Rath Yatra.',
        'sort_order'       => 60,
    ],
    [
        'slug'             => 'flag-seva',
        'name'             => 'Flag Seva',
        'default_amount'   => 1008,
        'description'      => 'Sponsor auspicious flags for the Rath Yatra chariot and procession.',
        'sort_order'       => 70,
    ],
    [
        'slug'             => 'canopy-decoration',
        'name'             => 'Canopy Decoration',
        'default_amount'   => 4008,
        'description'      => 'Support the decoration of the canopy adorning the Lord\'s chariot.',
        'sort_order'       => 80,
    ],
    [
        'slug'             => 'umbrella-chhatra-seva',
        'name'             => 'Umbrella (Chhatra) Seva',
        'default_amount'   => 2108,
        'description'      => 'Offer Chhatra Seva by sponsoring ceremonial umbrellas for the Lord\'s procession.',
        'sort_order'       => 90,
    ],
    [
        'slug'             => 'sound-system-sponsor',
        'name'             => 'Sound System Sponsor',
        'default_amount'   => 7008,
        'description'      => 'Support the sound system for kirtan, announcements, and spiritual programs during Rath Yatra.',
        'sort_order'       => 100,
    ],
    [
        'slug'             => 'lighting-sponsor',
        'name'             => 'Lighting Sponsor',
        'default_amount'   => 6008,
        'description'      => 'Sponsor festival lighting to illuminate the Rath Yatra celebration beautifully.',
        'sort_order'       => 110,
    ],
];

// Step 4: Insert/update sevas
$createdCount = 0;
$updatedCount = 0;
$sevaIds = [];

foreach ($rathYatraSevas as $sData) {
    // Check if slug already exists
    $checkStmt = $db->prepare("SELECT id FROM master_sevas WHERE slug = ?");
    $checkStmt->execute([$sData['slug']]);
    $existingId = $checkStmt->fetchColumn();

    $data = [
        'slug'           => $sData['slug'],
        'name'           => $sData['name'],
        'category_id'    => 4,
        'default_amount' => $sData['default_amount'],
        'description'    => $sData['description'],
        'allow_multiple' => 1,
        'max_quantity'   => 99,
        'sort_order'     => $sData['sort_order'],
        'is_active'      => 1,
    ];

    if ($existingId) {
        // Update existing
        if (updateMasterSeva((int)$existingId, $data)) {
            $updatedCount++;
            $sevaIds[] = (int)$existingId;
            echo "  [UPDATE] {$sData['name']} (id={$existingId})\n";
        } else {
            echo "  [FAIL]   {$sData['name']} — update failed\n";
        }
    } else {
        // Create new
        $newId = createMasterSeva($data);
        if ($newId) {
            $createdCount++;
            $sevaIds[] = (int)$newId;
            echo "  [CREATE] {$sData['name']} (id={$newId}) — ₹" . number_format($sData['default_amount']) . "\n";
        } else {
            echo "  [FAIL]   {$sData['name']} — create failed\n";
        }
    }
}

echo "\nSummary: {$createdCount} created, {$updatedCount} updated\n";

// Step 5: Link to Rath Yatra cause if found
if ($cause && !empty($sevaIds)) {
    $linkedCount = 0;
    $causeId = (int)$cause['id'];
    $upsertStmt = $db->prepare("
        INSERT INTO donation_cause_master_sevas 
        (cause_id, master_seva_id, override_amount, override_description, override_max_quantity, sort_order, is_featured, is_active)
        VALUES (?, ?, NULL, NULL, NULL, ?, 0, 1)
        ON DUPLICATE KEY UPDATE
            sort_order = VALUES(sort_order),
            is_active = 1
    ");

    foreach ($sevaIds as $i => $masterSevaId) {
        try {
            $sOrder = ($i + 1) * 10;
            $upsertStmt->execute([$causeId, $masterSevaId, $sOrder]);
            $linkedCount++;
        } catch (PDOException $e) {
            echo "  [LINK FAIL] master_seva_id={$masterSevaId}: {$e->getMessage()}\n";
        }
    }

    echo "Linked {$linkedCount}/" . count($sevaIds) . " sevas to cause '{$cause['slug']}' (id={$causeId})\n";
} elseif (!$cause) {
    echo "\n[SKIP] No Rath Yatra cause found — sevas not linked to any cause.\n";
}

echo "\n=== Done! ===\n";
