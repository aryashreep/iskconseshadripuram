<?php
/**
 * Migration: Rebuild Festival-Seva Links
 *
 * Deletes ALL existing links and rebuilds with correct festival-specific sevas.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/donation-helpers.php';

$db = getDB();
echo "=== Rebuilding Festival-Seva Links ===\n\n";

// Step 1: Delete ALL existing links
$db->exec("DELETE FROM donation_cause_master_sevas");
echo "[OK] Cleared all existing links.\n\n";

// Step 2: Define mapping: cause_id => [master_seva_ids...]
// Each seva gets sort_order = (index+1)*10
$festivalMap = [
    // === FESTIVALS ===
    13 => [ // janmashtami
        130, 127, 131, 126, 129, 128, 125, 124, // Nandotsava sevas (Krishna's birthday celebration)
        222, 221, 220, 86, // Sri Krishna sevas
        69, 153, // Flower Decoration, Radha Krishna Garland
        1, // Abhishekam
        15, 16, // Annadhanam
        343, 348, // General Temple Donation, Festival Fund
    ],
    14 => [ // gaura-purnima
        134, 133, 229, // Nitai Gauranga sevas
        69, // Flower Decoration
        1, // Abhishekam
        15, 16, // Annadhanam
        343, 348,
    ],
    15 => [ // ratha-yatra
        299, 300, 301, 302, 303, 304, 305, 306, 307, 308, 309, // All Rath Yatra sevas
        100, 113, 110, 99, 112, 101, 111, // Jagannatha sevas
        181, 154, 230, 223, 226, 232, 153, 227, // More deity sevas
        69, 1, 15, 16,
        343, 348,
    ],
    16 => [ // diwali
        45, 46, 47, 141, 204, // Deepotsava / lamp sevas
        69, // Flower Decoration
        1, // Abhishekam
        15, 16, // Annadhanam
        343, 348,
    ],
    17 => [ // govardhan-puja
        95, 97, 93, 94, 96, // Govardhan-specific sevas
        76, 77, 75, // Giriraj sevas
        17, 118, 202, // Annakut sevas
        69, 1, 15, 16,
        343, 348,
    ],
    18 => [ // narasimha-chaturdashi
        132, // Narasimha Puja
        69, 1, 15, 16,
        343, 348,
    ],
    19 => [ // rama-navami
        69, 1, 15, 16, 317, // Generic sevas + Festival Publicity
        343, 348,
    ],
    20 => [ // radhashtami
        153, 226, 227, // Radha Krishna specific
        69, 1, 15, 16,
        343, 348,
    ],
    21 => [ // gopastami
        92, 91, 88, 89, 87, 90, // Gopastami-specific
        69, 1, 15, 16,
        343, 348,
    ],
    22 => [ // jhulan-yatra
        103, 104, 108, 105, 106, 107, 102, 205, // Jhulan sevas
        159, 228, // Jhulan-specific dress/bhoga
        69, 1, 15, 16,
        343, 348,
    ],
    23 => [ // nandotsava
        130, 127, 131, 126, 129, 128, 125, 124, 123, // All Nandotsava sevas
        69, 1, 15, 16,
        343, 348,
    ],
    24 => [ // balarama-purnima
        69, 1, 15, 16, 223, // Generic + Sri Krishna-Arjuna Dress (Balarama)
        343, 348,
    ],
    25 => [ // snana-yatra
        181, 182, 183, 184, 185, 209, // Snana-specific
        99, 112, // Jagannatha Snana sevas
        69, 1, 15, 16,
        343, 348,
    ],
    26 => [ // akshaya-tritiya
        11, 14, 10, 12, 13, // Akshaya-specific
        41, 48, 201, 207, // Chandan Yatra sevas
        69, 1, 15, 16,
        343, 348,
    ],
    27 => [ // gita-jayanti
        82, 79, 84, 78, 80, 85, 81, 83, 199, // Gita Jayanti-specific
        23, 24, // Bhagavad-gita books
        69, 1, 15, 16,
        343, 348,
    ],
    28 => [ // nityananda-trayodashi
        69, 1, 15, 16,
        343, 348,
    ],
    29 => [ // bahulastami
        19, 20, 21, 22, // Bahulastami-specific
        156, 155, 158, 157, // Radha Kunda sevas
        69, 1, 15, 16,
        343, 348,
    ],
    30 => [ // odana-sasthi
        140, 137, 136, 138, 139, // Odana-specific
        297, 211, // Winter dress/blankets
        69, 1, 15, 16,
        343, 348,
    ],
    31 => [ // tulasi-shaligram-vivaha
        69, 1, 15, 16,
        343, 348,
    ],
    32 => [ // panihati
        143, 142, 144, 145, // Panihati-specific
        42, 119, // Chida-Dahi sevas
        69, 1, 15, 16,
        343, 348,
    ],
    33 => [ // varaha-dwadashi
        115, 114, 295, 291, 293, 292, 294, // Varaha-specific
        116, 117, // Varaha dress/rajbhog
        69, 1, 15, 16,
        343, 348,
    ],
    34 => [ // pushya-abhisheka
        152, 149, 148, 150, 151, // Pushya-specific
        69, 1, 15, 16,
        343, 348,
    ],
    35 => [ // bhishma-panchaka
        28, 27, 26, 32, 29, 25, 30, 31, // Bhishma-specific
        69, 1, 15, 16,
        343, 348,
    ],
    36 => [ // sri-sri-radha-ramana
        165, 162, 168, 160, 161, 163, 164, 166, 167, // All Radha Ramana sevas
        69, 1, 15, 16,
        343, 348,
    ],
    49 => [ // lord-vamanadeva
        69, 1, 15, 16,
        343, 348,
    ],

    // === APPEARANCE DAYS ===
    37 => [ // srila-prabhupada-appearance
        274, 281, 283, 2, 4, 7, 9, 8, 3, 6, // Prabhupada appearance sevas + acharya sevas
        15, 16,
        343, 348,
    ],
    38 => [ // srila-bhaktivinoda-thakura-appearance
        246, 253, 255, 256, 254, 245, 247, 248,
        15, 16,
        343, 348,
    ],
    39 => [ // srila-bhaktisiddhanta-sarasvati-appearance
        234, 241, 243, 244, 242, 233, 235, 236,
        15, 16,
        343, 348,
    ],
    40 => [ // sri-advaita-acharya-appearance
        213, 216, 218, 219, 217, 212, 214, 215,
        15, 16,
        343, 348,
    ],

    // === DISAPPEARANCE DAYS ===
    41 => [ // srila-prabhupada-disappearance
        278, 281, 283, 284, 282, 277, 279, 280,
        15, 16,
        343, 348,
    ],
    42 => [ // srila-bhaktivinoda-thakura-disappearance
        250, 253, 255, 256, 254, 249, 251, 252,
        15, 16,
        343, 348,
    ],
    43 => [ // srila-bhaktisiddhanta-disappearance
        238, 241, 243, 244, 242, 237, 239, 240,
        15, 16,
        343, 348,
    ],
    44 => [ // srila-jagannatha-dasa-babaji-disappearance
        266, 269, 271, 272, 270, 265, 267, 268,
        15, 16,
        343, 348,
    ],
    45 => [ // gaura-kisora-dasa-babaji-disappearance
        258, 261, 263, 264, 262, 257, 259, 260,
        15, 16,
        343, 348,
    ],

    // === EVENTS ===
    47 => [ // caturmasya
        35, 34, 38, 39, 36, 33, 37, 40, 200, // Caturmasya-specific
        69, 1, 15, 16,
        343, 348,
    ],
    48 => [ // shiksha-ceremony
        174, 179, 177, 173, 175, 178, 176, 208, // Shiksha-specific
        69, 1, 15, 16,
        343, 348,
    ],

    // === EKADASHI (all 24 share same generic ekadashi sevas) ===
    50 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // saphala
    51 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // putrada
    52 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // sattila
    53 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // bhaimi
    54 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // vijaya
    55 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // amalaki
    56 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // papamocani
    57 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // kamada
    58 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // varuthini
    59 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // mohini
    60 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // apara
    61 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // nirjala
    62 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // yogini
    63 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // sayana
    64 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // kamika
    65 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // pavitropana
    66 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // annada
    67 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // parivartini
    68 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // indira
    69 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // papankusa
    70 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // rama
    71 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // utthana
    72 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // utpanna
    73 => [59, 60, 54, 53, 57, 56, 58, 55, 52, 69, 1, 15, 16, 343, 348], // moksada
];

// Step 3: Insert links
$totalLinks = 0;
$failedCount = 0;

// Prepare lookup: get all valid master_seva ids
$validSevas = [];
$sevaRows = $db->query("SELECT id FROM master_sevas WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
foreach ($sevaRows as $sid) {
    $validSevas[(int)$sid] = true;
}

// Prepare lookup: get all valid cause ids
$validCauses = [];
$causeRows = $db->query("SELECT id FROM donation_causes WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
foreach ($causeRows as $cid) {
    $validCauses[(int)$cid] = true;
}

// Verify all cause_ids exist
foreach ($festivalMap as $causeId => $sevaIds) {
    $causeId = (int)$causeId;
    if (!isset($validCauses[$causeId])) {
        echo "  [SKIP] cause_id={$causeId} not found in donation_causes\n";
        unset($festivalMap[$causeId]);
    }
}

$stmt = $db->prepare("
    INSERT INTO donation_cause_master_sevas
    (cause_id, master_seva_id, override_amount, override_description, override_max_quantity, sort_order, is_featured, is_active)
    VALUES (?, ?, NULL, NULL, NULL, ?, 0, 1)
");

foreach ($festivalMap as $causeId => $sevaIds) {
    $causeId = (int)$causeId;
    $sortOrder = 0;
    foreach ($sevaIds as $sevaId) {
        $sevaId = (int)$sevaId;
        // Skip placeholder keys (like 69_ek)
        if (!isset($validSevas[$sevaId])) {
            continue;
        }
        $sortOrder += 10;
        try {
            $stmt->execute([$causeId, $sevaId, $sortOrder]);
            $totalLinks++;
        } catch (PDOException $e) {
            echo "  [FAIL] cause={$causeId} seva={$sevaId}: " . $e->getMessage() . "\n";
            $failedCount++;
        }
    }
}

echo "\n=== Summary ===\n";
echo "Total links created: {$totalLinks}\n";
echo "Failed: {$failedCount}\n";
echo "Festivals mapped: " . count($festivalMap) . "\n";

// Verify a few
echo "\n=== Verification ===\n";
$verifyIds = [13, 15, 27, 32, 37]; // janmashtami, ratha-yatra, gita-jayanti, panihati, prabhupada-appearance
$verifyStmt = $db->prepare("
    SELECT c.title, ms.name as seva_name, msc.name as cat_name, dcms.sort_order
    FROM donation_cause_master_sevas dcms
    JOIN donation_causes c ON dcms.cause_id = c.id
    JOIN master_sevas ms ON dcms.master_seva_id = ms.id
    JOIN master_seva_categories msc ON ms.category_id = msc.id
    WHERE dcms.cause_id = ?
    ORDER BY dcms.sort_order
");
foreach ($verifyIds as $vid) {
    $verifyStmt->execute([$vid]);
    $rows = $verifyStmt->fetchAll();
    if (!empty($rows)) {
        echo "\n{$rows[0]['title']} (" . count($rows) . " sevas):\n";
        foreach ($rows as $r) {
            echo "  sort={$r['sort_order']} [{$r['cat_name']}] {$r['seva_name']}\n";
        }
    }
}

echo "\n=== Done! ===\n";
