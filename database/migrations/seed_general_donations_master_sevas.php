<?php
/**
 * Migration: Seed General Donations Master Sevas
 *
 * Adds 9 General Donation sevas to the Master Seva Catalog (category_id=10):
 * 1. General Temple Donation
 * 2. Corpus Fund
 * 3. Building Fund
 * 4. Renovation Fund
 * 5. Emergency Fund
 * 6. Festival Fund
 * 7. Education Support
 * 8. Charity & Relief Fund
 * 9. Other Seva
 *
 * Idempotent — safe to re-run.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/donation-helpers.php';

$db = getDB();
echo "=== Seeding General Donations Master Sevas ===\n\n";

$catStmt = $db->prepare("SELECT id, name FROM master_seva_categories WHERE slug = 'general-donations' AND is_active = 1");
$catStmt->execute();
$category = $catStmt->fetch();

if (!$category) {
    echo "ERROR: General Donations category not found!\n";
    exit(1);
}
echo "[OK] Category found: {$category['name']} (id={$category['id']})\n";
$category_id = (int)$category['id'];

$generalDonations = [
    [
        'slug'              => 'general-temple-donation',
        'name'              => 'General Temple Donation',
        'default_amount'    => 1001,
        'description'       => 'Support the overall mission and daily operations of the temple. Your generous donation helps maintain deity worship, prasadam distribution, spiritual programs, and community services.',
        'short_description' => 'Support overall temple operations',
        'icon'              => 'fa-hand-holding-heart',
        'sort_order'        => 10,
    ],
    [
        'slug'              => 'corpus-fund',
        'name'              => 'Corpus Fund',
        'default_amount'    => 2001,
        'description'       => 'Contribute to the temple\'s corpus fund that provides long-term financial stability. This fund ensures the temple can sustain its operations, programs, and community services for generations.',
        'short_description' => 'Long-term financial stability for the temple',
        'icon'              => 'fa-piggy-bank',
        'sort_order'        => 20,
    ],
    [
        'slug'              => 'building-fund',
        'name'              => 'Building Fund',
        'default_amount'    => 2001,
        'description'       => 'Sponsor the construction and expansion of temple facilities. Your contribution helps build new halls, prayer rooms, community spaces, and infrastructure to serve the growing devotee community.',
        'short_description' => 'Build and expand temple facilities',
        'icon'              => 'fa-building',
        'sort_order'        => 30,
    ],
    [
        'slug'              => 'renovation-fund',
        'name'              => 'Renovation Fund',
        'default_amount'    => 1501,
        'description'       => 'Support the renovation and beautification of temple premises. Help restore and upgrade deity halls, corridors, kitchens, and other sacred spaces to their full glory.',
        'short_description' => 'Restore and beautify temple spaces',
        'icon'              => 'fa-paint-roller',
        'sort_order'        => 40,
    ],
    [
        'slug'              => 'emergency-fund',
        'name'              => 'Emergency Fund',
        'default_amount'    => 501,
        'description'       => 'Contribute to the temple\'s emergency fund for urgent needs including natural disasters, unexpected repairs, medical emergencies for devotees, and unforeseen operational challenges.',
        'short_description' => 'Prepare for urgent and unexpected needs',
        'icon'              => 'fa-exclamation-triangle',
        'sort_order'        => 50,
    ],
    [
        'slug'              => 'festival-fund',
        'name'              => 'Festival Fund',
        'default_amount'    => 1001,
        'description'       => 'Support the celebration of major festivals throughout the year. Your donation helps fund decorations, prasadam distribution, cultural programs, and community gatherings during auspicious occasions.',
        'short_description' => 'Support festival celebrations year-round',
        'icon'              => 'fa-star',
        'sort_order'        => 60,
    ],
    [
        'slug'              => 'education-support',
        'name'              => 'Education Support',
        'default_amount'    => 501,
        'description'       => 'Sponsor spiritual education programs including Vedic classes, Bhagavad-gita study groups, Sunday school, youth programs, and educational materials for the devotee community.',
        'short_description' => 'Fund spiritual education and learning',
        'icon'              => 'fa-book-open',
        'sort_order'        => 70,
    ],
    [
        'slug'              => 'charity-relief-fund',
        'name'              => 'Charity & Relief Fund',
        'default_amount'    => 501,
        'description'       => 'Support charitable activities and disaster relief efforts. Your contribution helps provide food, clothing, shelter, and medical assistance to those in need through the temple\'s outreach programs.',
        'short_description' => 'Help those in need through charity',
        'icon'              => 'fa-heart',
        'sort_order'        => 80,
    ],
    [
        'slug'              => 'other-seva',
        'name'              => 'Other Seva',
        'default_amount'    => 501,
        'description'       => 'Make a donation towards any seva or purpose not listed above. Please mention the specific seva or purpose in the notes section during checkout.',
        'short_description' => 'Donate towards any purpose',
        'icon'              => 'fa-ellipsis-h',
        'sort_order'        => 90,
    ],
];

$createdCount = 0;
$updatedCount = 0;

foreach ($generalDonations as $sData) {
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
        'icon'              => $sData['icon'] ?? 'fa-heart',
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

// Link to general-donation cause
$causeStmt = $db->prepare("SELECT id, slug, title FROM donation_causes WHERE slug = 'general-donation' AND is_active = 1 LIMIT 1");
$causeStmt->execute();
$cause = $causeStmt->fetch();
$linkedCount = 0;

if ($cause) {
    echo "\n[OK] Found cause: {$cause['title']}\n";
    $sevaStmt = $db->prepare("SELECT id, name FROM master_sevas WHERE category_id = ? AND is_active = 1 ORDER BY sort_order");
    $sevaStmt->execute([$category_id]);
    $sevas = $sevaStmt->fetchAll();

    $upsertStmt = $db->prepare("
        INSERT INTO donation_cause_master_sevas
        (cause_id, master_seva_id, override_amount, override_description, override_max_quantity, sort_order, is_featured, is_active)
        VALUES (?, ?, NULL, NULL, NULL, ?, 0, 1)
        ON DUPLICATE KEY UPDATE sort_order = VALUES(sort_order), is_active = 1
    ");

    foreach ($sevas as $i => $seva) {
        try {
            $upsertStmt->execute([$cause['id'], $seva['id'], ($i + 1) * 10]);
            $linkedCount++;
        } catch (PDOException $e) {
            echo "  [LINK FAIL] {$seva['name']}: {$e->getMessage()}\n";
        }
    }
    echo "Linked {$linkedCount} seva-cause relationships\n";
} else {
    echo "\n[SKIP] General Donation cause not found.\n";
}

echo "\n=== Done! ===\n";
