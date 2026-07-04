<?php
/**
 * Migration: Seed Temple Infrastructure Master Sevas
 *
 * Adds 12 Temple Infrastructure sevas to the Master Seva Catalog (category_id=6).
 * Idempotent — safe to re-run.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/donation-helpers.php';

$db = getDB();
echo "=== Seeding Temple Infrastructure Master Sevas ===\n\n";

$catStmt = $db->prepare("SELECT id, name FROM master_seva_categories WHERE slug = 'infrastructure-sevas' AND is_active = 1");
$catStmt->execute();
$category = $catStmt->fetch();

if (!$category) {
    echo "ERROR: Temple Infrastructure Sevas category not found!\n";
    exit(1);
}
echo "[OK] Category found: {$category['name']} (id={$category['id']})\n";
$category_id = (int)$category['id'];

$infraSevas = [
    [
        'slug'              => 'temple-decoration',
        'name'              => 'Temple Decoration',
        'default_amount'    => 2001,
        'description'       => 'Sponsor the decoration of the temple premises including flowers, lights, rangoli, and festive ornaments. Beautiful decorations create a divine atmosphere that uplifts the consciousness of all visitors and devotees.',
        'short_description' => 'Beautify the temple with decorations',
        'icon'              => 'fa-snowflake',
        'sort_order'        => 110,
    ],
    [
        'slug'              => 'hall-decoration',
        'name'              => 'Hall Decoration',
        'default_amount'    => 1501,
        'description'       => 'Sponsor the decoration of the main temple hall, prayer halls, and gathering spaces with flowers, drapes, and spiritual artwork. A well-decorated hall enhances the devotional experience during festivals and daily worship.',
        'short_description' => 'Decorate prayer halls and gathering spaces',
        'icon'              => 'fa-church',
        'sort_order'        => 120,
    ],
    [
        'slug'              => 'garden-maintenance',
        'name'              => 'Garden Maintenance',
        'default_amount'    => 1001,
        'description'       => 'Support the maintenance of the temple gardens including Tulasi Vrindavan, flower beds, lawns, and sacred plants. A beautiful garden provides a serene environment for meditation and spiritual contemplation.',
        'short_description' => 'Maintain temple gardens and Tulasi Vrindavan',
        'icon'              => 'fa-seedling',
        'sort_order'        => 130,
    ],
    [
        'slug'              => 'electrical-maintenance',
        'name'              => 'Electrical Maintenance',
        'default_amount'    => 1501,
        'description'       => "Sponsor the maintenance and upkeep of the temple's electrical systems including lighting, wiring, switches, and power distribution. Reliable electricity ensures uninterrupted deity worship and devotee services.",
        'short_description' => 'Maintain temple electrical systems',
        'icon'              => 'fa-bolt',
        'sort_order'        => 140,
    ],
    [
        'slug'              => 'cleaning-housekeeping',
        'name'              => 'Cleaning & Housekeeping',
        'default_amount'    => 501,
        'description'       => 'Support the daily cleaning and housekeeping services that keep the temple pristine and hygienic. A clean temple is essential for maintaining the sanctity of the sacred space and the comfort of devotees.',
        'short_description' => 'Keep the temple clean and hygienic',
        'icon'              => 'fa-broom',
        'sort_order'        => 150,
    ],
    [
        'slug'              => 'generator-sponsor',
        'name'              => 'Generator Sponsor',
        'default_amount'    => 2001,
        'description'       => 'Sponsor the purchase, fuel, and maintenance of backup generators to ensure uninterrupted power supply during outages. Continuous power is essential for deity worship, security systems, and devotee safety.',
        'short_description' => 'Ensure uninterrupted power with generators',
        'icon'              => 'fa-car-battery',
        'sort_order'        => 160,
    ],
    [
        'slug'              => 'tent-seating-sponsor',
        'name'              => 'Tent & Seating Sponsor',
        'default_amount'    => 2001,
        'description'       => 'Sponsor temporary tents, canopies, chairs, and seating arrangements for large gatherings, festivals, and special events. Comfortable seating ensures devotees can participate in programs without discomfort.',
        'short_description' => 'Provide seating for festivals and events',
        'icon'              => 'fa-chair',
        'sort_order'        => 170,
    ],
    [
        'slug'              => 'led-display-sponsor',
        'name'              => 'LED Display Sponsor',
        'default_amount'    => 2001,
        'description'       => 'Sponsor LED display screens for the temple to show live deity darshan, kirtan lyrics, event schedules, and spiritual messages. Modern displays enhance the devotee experience and help newcomers participate fully.',
        'short_description' => 'Install LED screens for darshan and events',
        'icon'              => 'fa-tv',
        'sort_order'        => 180,
    ],
    [
        'slug'              => 'cctv-maintenance',
        'name'              => 'CCTV Maintenance',
        'default_amount'    => 1001,
        'description'       => 'Support the installation and maintenance of CCTV cameras for temple security. Surveillance systems protect devotees, deity paraphernalia, and temple assets while ensuring a safe environment for all visitors.',
        'short_description' => 'Security cameras for temple safety',
        'icon'              => 'fa-video',
        'sort_order'        => 190,
    ],
    [
        'slug'              => 'drinking-water-facility',
        'name'              => 'Drinking Water Facility',
        'default_amount'    => 501,
        'description'       => 'Sponsor clean drinking water facilities including water purifiers, RO systems, and water stations throughout the temple premises. Providing pure water to devotees and visitors is a sacred act of hospitality.',
        'short_description' => 'Provide clean drinking water for devotees',
        'icon'              => 'fa-tint',
        'sort_order'        => 200,
    ],
    [
        'slug'              => 'shoe-stand-seva',
        'name'              => 'Shoe Stand Seva',
        'default_amount'    => 501,
        'description'       => 'Sponsor shoe racks and shoe management systems at the temple entrance. Proper shoe storage keeps the temple premises clean and organized, and ensures the comfort and convenience of all visitors.',
        'short_description' => 'Organize shoe storage at temple entrance',
        'icon'              => 'fa-shoe-prints',
        'sort_order'        => 210,
    ],
    [
        'slug'              => 'parking-arrangement',
        'name' => 'Parking Arrangement',
        'default_amount'    => 2001,
        'description'       => 'Support the development and maintenance of parking facilities for devotees and visitors. Adequate parking ensures smooth traffic flow, devotee convenience, and safety of vehicles during temple visits and festivals.',
        'short_description' => 'Develop and maintain parking facilities',
        'icon'              => 'fa-parking',
        'sort_order'        => 220,
    ],
];

$createdCount = 0;
$updatedCount = 0;

foreach ($infraSevas as $sData) {
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
        'icon'              => $sData['icon'] ?? 'fa-building',
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

// Verify
$verify = $db->prepare("SELECT name, default_amount, sort_order FROM master_sevas WHERE category_id = ? AND is_active = 1 ORDER BY sort_order");
$verify->execute([$category_id]);
echo "\n=== All Temple Infrastructure Sevas ===\n";
foreach ($verify->fetchAll() as $r) {
    echo "  sort=" . str_pad($r['sort_order'], 3) . " " . str_pad($r['name'], 40) . " ₹" . number_format($r['default_amount']) . "\n";
}

echo "\n=== Done! ===\n";
