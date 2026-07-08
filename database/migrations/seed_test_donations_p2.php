<?php
/**
 * Phase 2: Seed remaining test donations with corrected slugs
 * Usage: php database/migrations/seed_test_donations_p2.php
 */
require_once __DIR__ . '/../../config.php';
$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get cause IDs by slug
function getCauseId(PDO $db, string $slug) {
    $s = $db->prepare("SELECT id FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
    $s->execute([$slug]);
    return $s->fetchColumn() ?: null;
}

// Get master seva ID by slug
function getSevaId(PDO $db, string $slug) {
    $s = $db->prepare("SELECT id FROM master_sevas WHERE slug = ? AND is_active = 1 LIMIT 1");
    $s->execute([$slug]);
    return $s->fetchColumn() ?: null;
}

$donations = [
    // Ekadashi (use actual slugs: amalaki, kamada, nirjala, sayana, utthana)
    ['cause' => 'amalaki', 'seva' => null, 'amount' => 501, 'qty' => 1, 'name' => 'Haridas Thakur', 'email' => 'haridas.t@test.com', 'phone' => '9876543220', 'date' => '2026-01-20 07:00:00'],
    ['cause' => 'kamada', 'seva' => null, 'amount' => 1100, 'qty' => 1, 'name' => 'Gauranga Das', 'email' => 'gauranga.d@test.com', 'phone' => '9876543221', 'date' => '2026-02-20 08:30:00'],
    ['cause' => 'nirjala', 'seva' => null, 'amount' => 2100, 'qty' => 1, 'name' => 'Nityananda Das', 'email' => 'nityananda.d@test.com', 'phone' => '9876543222', 'date' => '2026-03-20 09:00:00'],
    ['cause' => 'sayana', 'seva' => null, 'amount' => 501, 'qty' => 2, 'name' => 'Advaita Acharya', 'email' => 'advaita.a@test.com', 'phone' => '9876543223', 'date' => '2026-04-20 07:30:00'],
    ['cause' => 'utthana', 'seva' => null, 'amount' => 3100, 'qty' => 1, 'name' => 'Ekadashi Seva Trust', 'email' => 'ekadashi.st@test.com', 'phone' => '9876543258', 'date' => '2026-05-20 08:00:00'],

    // Book Distribution -> shastra-daan
    ['cause' => 'shastra-daan', 'seva' => null, 'amount' => 5000, 'qty' => 1, 'name' => 'Vedic Books Trust', 'email' => 'books.t@test.com', 'phone' => '9876543227', 'date' => '2026-01-28 10:00:00'],
    ['cause' => 'shastra-daan', 'seva' => null, 'amount' => 1500, 'qty' => 3, 'name' => 'BG Society', 'email' => 'bg.society@test.com', 'phone' => '9876543228', 'date' => '2026-02-28 14:00:00'],

    // Flower Decoration -> daily-seva + flower-decoration seva
    ['cause' => 'daily-seva', 'seva' => 'flower-decoration', 'amount' => 2008, 'qty' => 4, 'name' => 'Pushpa Seva Trust', 'email' => 'pushpa.st@test.com', 'phone' => '9876543231', 'date' => '2026-01-10 08:00:00'],
    ['cause' => 'daily-seva', 'seva' => 'flower-decoration', 'amount' => 1008, 'qty' => 6, 'name' => 'Tulasi Das', 'email' => 'tulasi.d@test.com', 'phone' => '9876543232', 'date' => '2026-02-10 09:30:00'],

    // Annadanam -> food-for-life + annadanam seva
    ['cause' => 'food-for-life', 'seva' => 'annadanam', 'amount' => 5008, 'qty' => 2, 'name' => 'Prasadam Seva', 'email' => 'prasadam.s@test.com', 'phone' => '9876543233', 'date' => '2026-01-12 11:00:00'],
    ['cause' => 'food-for-life', 'seva' => 'annadanam', 'amount' => 10008, 'qty' => 1, 'name' => 'Maha Annadanam Trust', 'email' => 'maha.at@test.com', 'phone' => '9876543234', 'date' => '2026-02-12 12:00:00'],
    ['cause' => 'food-for-life', 'seva' => 'rajbhog-prasad', 'amount' => 3008, 'qty' => 3, 'name' => 'Rajbhog Seva', 'email' => 'rajbhog.s@test.com', 'phone' => '9876543235', 'date' => '2026-03-12 10:00:00'],
    ['cause' => 'food-for-life', 'seva' => 'maha-prasad', 'amount' => 8008, 'qty' => 1, 'name' => 'Maha Prasad Seva', 'email' => 'mahaprasad.s@test.com', 'phone' => '9876543255', 'date' => '2026-04-12 11:30:00'],

    // Deity Dress -> nitya-seva + deity-dress seva
    ['cause' => 'nitya-seva', 'seva' => 'deity-dress', 'amount' => 8008, 'qty' => 1, 'name' => 'Vastra Seva', 'email' => 'vastra.s@test.com', 'phone' => '9876543236', 'date' => '2026-01-18 07:00:00'],

    // Garland -> nitya-seva + garland-seva
    ['cause' => 'nitya-seva', 'seva' => 'garland-seva', 'amount' => 1508, 'qty' => 5, 'name' => 'Mala Seva Trust', 'email' => 'mala.st@test.com', 'phone' => '9876543237', 'date' => '2026-02-18 08:00:00'],

    // Temple Construction -> donate-a-brick
    ['cause' => 'donate-a-brick', 'seva' => null, 'amount' => 50000, 'qty' => 1, 'name' => 'Temple Building Fund', 'email' => 'temple.bf@test.com', 'phone' => '9876543238', 'date' => '2026-01-05 10:00:00'],
    ['cause' => 'donate-a-brick', 'seva' => null, 'amount' => 25000, 'qty' => 1, 'name' => 'Kendra Development', 'email' => 'kendra.d@test.com', 'phone' => '9876543239', 'date' => '2026-02-05 11:00:00'],
    ['cause' => 'donate-a-brick', 'seva' => null, 'amount' => 10000, 'qty' => 2, 'name' => 'Grihastha Seva', 'email' => 'grihastha.s@test.com', 'phone' => '9876543240', 'date' => '2026-03-05 14:00:00'],
    ['cause' => 'donate-a-brick', 'seva' => null, 'amount' => 100000, 'qty' => 1, 'name' => 'Major Donor Foundation', 'email' => 'major.d@test.com', 'phone' => '9876543260', 'date' => '2026-04-01 09:00:00'],
];

$insertStmt = $db->prepare("
    INSERT INTO donation_transactions 
    (cause_id, seva_id, master_seva_id, donor_name, donor_email, donor_phone, 
     amount, currency, donation_mode, quantity, source_type, 
     razorpay_order_id, payment_status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'INR', 'one_time', ?, 'test_seed', ?, 'paid', ?, ?)
");

$ok = 0;
$skip = 0;
foreach ($donations as $i => $d) {
    $causeId = getCauseId($db, $d['cause']);
    if (!$causeId) { echo "SKIP: no cause '{$d['cause']}'\n"; $skip++; continue; }
    
    $masterSevaId = $d['seva'] ? getSevaId($db, $d['seva']) : null;
    $orderId = 'test_p2_' . str_pad($i+1, 3, '0', STR_PAD_LEFT) . '_' . time();
    
    $insertStmt->execute([
        $causeId, null, $masterSevaId, $d['name'], $d['email'], $d['phone'],
        $d['amount'], $d['qty'], $orderId, $d['date'], $d['date']
    ]);
    $ok++;
}
echo "Inserted: $ok, Skipped: $skip\n";
