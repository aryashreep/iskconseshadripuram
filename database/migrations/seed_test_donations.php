<?php
/**
 * Migration: Seed Test Donation Transactions
 *
 * Inserts ~60 sample donation transactions across various categories,
 * activities, and sevas to test the new reporting pages.
 *
 * Only inserts transactions that don't already exist (checks razorpay_order_id).
 * Idempotent — safe to re-run.
 *
 * Usage: php database/migrations/seed_test_donations.php
 */

require_once __DIR__ . '/../../config.php';

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Seeding Test Donation Transactions ===\n\n";

// Step 1: Get existing cause IDs by slug
$causeMap = [];
$causeStmt = $db->query("SELECT id, slug, title, category FROM donation_causes WHERE is_active = 1");
foreach ($causeStmt->fetchAll() as $row) {
    $causeMap[$row['slug']] = $row;
}
echo "Found " . count($causeMap) . " active causes.\n";

// Step 2: Get existing master seva IDs by slug
$sevaMap = [];
$sevaStmt = $db->query("SELECT id, slug, name, category_id FROM master_sevas WHERE is_active = 1");
foreach ($sevaStmt->fetchAll() as $row) {
    $sevaMap[$row['slug']] = $row;
}
echo "Found " . count($sevaMap) . " active master sevas.\n\n";

// Helper: find cause by partial slug match
function findCause(array $causeMap, string $pattern): ?array {
    foreach ($causeMap as $slug => $cause) {
        if (str_contains($slug, $pattern)) return $cause;
    }
    return null;
}

// Helper: find seva by partial slug match
function findSeva(array $sevaMap, string $pattern): ?array {
    foreach ($sevaMap as $slug => $seva) {
        if (str_contains($slug, $pattern)) return $seva;
    }
    return null;
}

// Step 3: Define test transactions
$testDonations = [
    // ===== GRAND FESTIVALS (category: festival) =====
    // Rath Yatra donations
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'rath-decoration', 'amount' => 5008, 'qty' => 2, 'donor' => 'Radha Krishna Das', 'email' => 'rk.das@example.com', 'phone' => '9876543210', 'date' => '2026-01-15 10:30:00'],
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'flower-decoration', 'amount' => 3008, 'qty' => 3, 'donor' => 'Sita Raman', 'email' => 'sita.r@example.com', 'phone' => '9876543211', 'date' => '2026-01-16 14:20:00'],
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'chariot-construction', 'amount' => 25008, 'qty' => 1, 'donor' => 'Gopal Krishna', 'email' => 'gopal.k@example.com', 'phone' => '9876543212', 'date' => '2026-02-01 09:15:00'],
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'lighting', 'amount' => 6008, 'qty' => 1, 'donor' => 'Yamuna Devi', 'email' => 'yamuna.d@example.com', 'phone' => '9876543213', 'date' => '2026-02-10 16:45:00'],
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'sound', 'amount' => 7008, 'qty' => 1, 'donor' => 'Narasimha Das', 'email' => 'narasimha.d@example.com', 'phone' => '9876543214', 'date' => '2026-02-14 11:00:00'],

    // Janmashtami donations
    ['cause_slug_match' => 'janmashtami', 'seva_slug_match' => null, 'amount' => 2100, 'qty' => 1, 'donor' => 'Lakshmi Priya', 'email' => 'lakshmi.p@example.com', 'phone' => '9876543215', 'date' => '2026-03-01 08:00:00'],
    ['cause_slug_match' => 'janmashtami', 'seva_slug_match' => null, 'amount' => 5100, 'qty' => 1, 'donor' => 'Madhav Das', 'email' => 'madhav.d@example.com', 'phone' => '9876543216', 'date' => '2026-03-05 12:30:00'],
    ['cause_slug_match' => 'janmashtami', 'seva_slug_match' => null, 'amount' => 1100, 'qty' => 2, 'donor' => 'Rukmini Devi', 'email' => 'rukmini.d@example.com', 'phone' => '9876543217', 'date' => '2026-03-10 15:00:00'],

    // Rama Navami
    ['cause_slug_match' => 'rama-navami', 'seva_slug_match' => null, 'amount' => 3100, 'qty' => 1, 'donor' => 'Bharat Das', 'email' => 'bharat.d@example.com', 'phone' => '9876543218', 'date' => '2026-03-15 10:00:00'],
    ['cause_slug_match' => 'rama-navami', 'seva_slug_match' => null, 'amount' => 1500, 'qty' => 3, 'donor' => 'Shatrughna Das', 'email' => 'shatrughna.d@example.com', 'phone' => '9876543219', 'date' => '2026-03-20 14:00:00'],

    // ===== EKADASHI (category: ekadashi) =====
    ['cause_slug_match' => 'ekadashi', 'seva_slug_match' => null, 'amount' => 501, 'qty' => 1, 'donor' => 'Haridas Thakur', 'email' => 'haridas.t@example.com', 'phone' => '9876543220', 'date' => '2026-01-20 07:00:00'],
    ['cause_slug_match' => 'ekadashi', 'seva_slug_match' => null, 'amount' => 1100, 'qty' => 1, 'donor' => 'Gauranga Das', 'email' => 'gauranga.d@example.com', 'phone' => '9876543221', 'date' => '2026-02-20 08:30:00'],
    ['cause_slug_match' => 'ekadashi', 'seva_slug_match' => null, 'amount' => 2100, 'qty' => 1, 'donor' => 'Nityananda Das', 'email' => 'nityananda.d@example.com', 'phone' => '9876543222', 'date' => '2026-03-20 09:00:00'],
    ['cause_slug_match' => 'ekadashi', 'seva_slug_match' => null, 'amount' => 501, 'qty' => 2, 'donor' => 'Advaita Acharya', 'email' => 'advaita.a@example.com', 'phone' => '9876543223', 'date' => '2026-04-20 07:30:00'],

    // ===== EVENTS & PROGRAMS (category: event) =====
    ['cause_slug_match' => 'food-for-life', 'seva_slug_match' => null, 'amount' => 1000, 'qty' => 5, 'donor' => 'Anna Prasad Das', 'email' => 'anna.p@example.com', 'phone' => '9876543224', 'date' => '2026-01-25 12:00:00'],
    ['cause_slug_match' => 'food-for-life', 'seva_slug_match' => null, 'amount' => 2500, 'qty' => 1, 'donor' => 'Mahaprasad Seva', 'email' => 'mahaprasad.s@example.com', 'phone' => '9876543225', 'date' => '2026-02-25 13:00:00'],
    ['cause_slug_match' => 'food-for-life', 'seva_slug_match' => null, 'amount' => 500, 'qty' => 10, 'donor' => 'Sukhada Devi', 'email' => 'sukhada.d@example.com', 'phone' => '9876543226', 'date' => '2026-03-25 11:30:00'],

    ['cause_slug_match' => 'book-distribution', 'seva_slug_match' => null, 'amount' => 5000, 'qty' => 1, 'donor' => 'Vedic Books Trust', 'email' => 'books.t@example.com', 'phone' => '9876543227', 'date' => '2026-01-28 10:00:00'],
    ['cause_slug_match' => 'book-distribution', 'seva_slug_match' => null, 'amount' => 1500, 'qty' => 3, 'donor' => 'Bhagavad Gita Society', 'email' => 'bg.society@example.com', 'phone' => '9876543228', 'date' => '2026-02-28 14:00:00'],

    ['cause_slug_match' => 'digital', 'seva_slug_match' => 'website', 'amount' => 10000, 'qty' => 1, 'donor' => 'Tech Seva Foundation', 'email' => 'tech.sf@example.com', 'phone' => '9876543229', 'date' => '2026-02-05 09:00:00'],
    ['cause_slug_match' => 'digital', 'seva_slug_match' => 'live', 'amount' => 5000, 'qty' => 1, 'donor' => 'Digital Darshan', 'email' => 'darshan.d@example.com', 'phone' => '9876543230', 'date' => '2026-03-05 16:00:00'],

    // ===== SEVA & SERVICES (category: service) =====
    ['cause_slug_match' => 'flower-decoration', 'seva_slug_match' => 'flower', 'amount' => 2008, 'qty' => 4, 'donor' => 'Pushpa Seva Trust', 'email' => 'pushpa.st@example.com', 'phone' => '9876543231', 'date' => '2026-01-10 08:00:00'],
    ['cause_slug_match' => 'flower-decoration', 'seva_slug_match' => 'flower', 'amount' => 1008, 'qty' => 6, 'donor' => 'Tulasi Das', 'email' => 'tulasi.d@example.com', 'phone' => '9876543232', 'date' => '2026-02-10 09:30:00'],

    ['cause_slug_match' => 'annadanam', 'seva_slug_match' => 'annadanam', 'amount' => 5008, 'qty' => 2, 'donor' => 'Prasadam Seva', 'email' => 'prasadam.s@example.com', 'phone' => '9876543233', 'date' => '2026-01-12 11:00:00'],
    ['cause_slug_match' => 'annadanam', 'seva_slug_match' => 'annadanam', 'amount' => 10008, 'qty' => 1, 'donor' => 'Maha Annadanam Trust', 'email' => 'maha.at@example.com', 'phone' => '9876543234', 'date' => '2026-02-12 12:00:00'],
    ['cause_slug_match' => 'annadanam', 'seva_slug_match' => 'rajbhog', 'amount' => 3008, 'qty' => 3, 'donor' => 'Rajbhog Seva', 'email' => 'rajbhog.s@example.com', 'phone' => '9876543235', 'date' => '2026-03-12 10:00:00'],

    ['cause_slug_match' => 'deity-dress', 'seva_slug_match' => 'deity', 'amount' => 8008, 'qty' => 1, 'donor' => 'Vastra Seva', 'email' => 'vastra.s@example.com', 'phone' => '9876543236', 'date' => '2026-01-18 07:00:00'],
    ['cause_slug_match' => 'garland', 'seva_slug_match' => 'garland', 'amount' => 1508, 'qty' => 5, 'donor' => 'Mala Seva Trust', 'email' => 'mala.st@example.com', 'phone' => '9876543237', 'date' => '2026-02-18 08:00:00'],

    // ===== TEMPLE CONSTRUCTION (category: construction) =====
    ['cause_slug_match' => 'temple-construction', 'seva_slug_match' => null, 'amount' => 50000, 'qty' => 1, 'donor' => 'Temple Building Fund', 'email' => 'temple.bf@example.com', 'phone' => '9876543238', 'date' => '2026-01-05 10:00:00'],
    ['cause_slug_match' => 'temple-construction', 'seva_slug_match' => null, 'amount' => 25000, 'qty' => 1, 'donor' => 'Kendra Development', 'email' => 'kendra.d@example.com', 'phone' => '9876543239', 'date' => '2026-02-05 11:00:00'],
    ['cause_slug_match' => 'temple-construction', 'seva_slug_match' => null, 'amount' => 10000, 'qty' => 2, 'donor' => 'Grihastha Seva', 'email' => 'grihastha.s@example.com', 'phone' => '9876543240', 'date' => '2026-03-05 14:00:00'],

    // ===== GENERAL DONATIONS (category: general) =====
    ['cause_slug_match' => 'general', 'seva_slug_match' => 'general-temple', 'amount' => 1001, 'qty' => 1, 'donor' => 'Shyam Sunder Das', 'email' => 'shyam.s@example.com', 'phone' => '9876543241', 'date' => '2026-01-08 09:00:00'],
    ['cause_slug_match' => 'general', 'seva_slug_match' => 'corpus', 'amount' => 5001, 'qty' => 1, 'donor' => 'Vaishnav Seva Trust', 'email' => 'vaishnav.st@example.com', 'phone' => '9876543242', 'date' => '2026-02-08 10:00:00'],
    ['cause_slug_match' => 'general', 'seva_slug_match' => 'education', 'amount' => 2100, 'qty' => 1, 'donor' => 'Vedic Education Fund', 'email' => 'vedic.ef@example.com', 'phone' => '9876543243', 'date' => '2026-03-08 11:00:00'],
    ['cause_slug_match' => 'general', 'seva_slug_match' => 'charity', 'amount' => 1001, 'qty' => 2, 'donor' => 'Daya Das', 'email' => 'daya.d@example.com', 'phone' => '9876543244', 'date' => '2026-04-08 12:00:00'],
    ['cause_slug_match' => 'general', 'seva_slug_match' => 'emergency', 'amount' => 501, 'qty' => 1, 'donor' => 'Sewa Bhav', 'email' => 'sewa.b@example.com', 'phone' => '9876543245', 'date' => '2026-05-08 13:00:00'],

    // ===== DISAPPEARANCE DAYS (category: disappearance) =====
    ['cause_slug_match' => 'disappearance', 'seva_slug_match' => null, 'amount' => 1100, 'qty' => 1, 'donor' => 'Srila Prabhupada Devotee', 'email' => 'sp.devotee@example.com', 'phone' => '9876543246', 'date' => '2026-01-22 06:00:00'],
    ['cause_slug_match' => 'disappearance', 'seva_slug_match' => null, 'amount' => 2100, 'qty' => 1, 'donor' => 'Guru Seva', 'email' => 'guru.s@example.com', 'phone' => '9876543247', 'date' => '2026-03-22 07:00:00'],

    // ===== APPEARANCE DAYS (category: appearance) =====
    ['cause_slug_match' => 'appearance', 'seva_slug_match' => null, 'amount' => 1500, 'qty' => 1, 'donor' => 'Jay Nitai Das', 'email' => 'jay.n@example.com', 'phone' => '9876543248', 'date' => '2026-02-22 08:00:00'],
    ['cause_slug_match' => 'appearance', 'seva_slug_match' => null, 'amount' => 3100, 'qty' => 1, 'donor' => 'Appearance Day Seva', 'email' => 'appearance.s@example.com', 'phone' => '9876543249', 'date' => '2026-04-22 09:00:00'],

    // More Rath Yatra for spread
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'canopy', 'amount' => 4008, 'qty' => 2, 'donor' => 'Chhatra Seva', 'email' => 'chhatra.s@example.com', 'phone' => '9876543250', 'date' => '2026-03-18 10:30:00'],
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'wheel', 'amount' => 3008, 'qty' => 4, 'donor' => 'Chakra Seva', 'email' => 'chakra.s@example.com', 'phone' => '9876543251', 'date' => '2026-04-18 11:00:00'],
    ['cause_slug_match' => 'rath', 'seva_slug_match' => 'rope', 'amount' => 2108, 'qty' => 8, 'donor' => 'Rajju Seva', 'email' => 'rajju.s@example.com', 'phone' => '9876543252', 'date' => '2026-05-18 14:00:00'],

    // More Food for Life
    ['cause_slug_match' => 'food-for-life', 'seva_slug_match' => null, 'amount' => 750, 'qty' => 8, 'donor' => 'Prasadam Distribution', 'email' => 'prasadam.dist@example.com', 'phone' => '9876543253', 'date' => '2026-04-25 12:30:00'],
    ['cause_slug_match' => 'food-for-life', 'seva_slug_match' => null, 'amount' => 1200, 'qty' => 4, 'donor' => 'Anna Daan Seva', 'email' => 'annadaan.s@example.com', 'phone' => '9876543254', 'date' => '2026-05-25 13:00:00'],

    // More Annadanam
    ['cause_slug_match' => 'annadanam', 'seva_slug_match' => 'maha-prasad', 'amount' => 8008, 'qty' => 1, 'donor' => 'Maha Prasad Seva', 'email' => 'mahaprasad.s2@example.com', 'phone' => '9876543255', 'date' => '2026-04-12 11:30:00'],

    // More General
    ['cause_slug_match' => 'general', 'seva_slug_match' => 'building', 'amount' => 15001, 'qty' => 1, 'donor' => 'Mandir Nirman', 'email' => 'mandir.n@example.com', 'phone' => '9876543256', 'date' => '2026-04-15 10:00:00'],
    ['cause_slug_match' => 'general', 'seva_slug_match' => 'renovation', 'amount' => 7001, 'qty' => 1, 'donor' => 'Renovation Seva', 'email' => 'renovation.s@example.com', 'phone' => '9876543257', 'date' => '2026-05-15 11:00:00'],

    // More Ekadashi
    ['cause_slug_match' => 'ekadashi', 'seva_slug_match' => null, 'amount' => 3100, 'qty' => 1, 'donor' => 'Ekadashi Seva Trust', 'email' => 'ekadashi.st@example.com', 'phone' => '9876543258', 'date' => '2026-05-20 08:00:00'],

    // More Digital
    ['cause_slug_match' => 'digital', 'seva_slug_match' => 'app', 'amount' => 8000, 'qty' => 1, 'donor' => 'Mobile Seva', 'email' => 'mobile.s@example.com', 'phone' => '9876543259', 'date' => '2026-04-05 15:00:00'],

    // More Temple Construction
    ['cause_slug_match' => 'temple-construction', 'seva_slug_match' => null, 'amount' => 100000, 'qty' => 1, 'donor' => 'Major Donor Foundation', 'email' => 'major.d@example.com', 'phone' => '9876543260', 'date' => '2026-04-01 09:00:00'],
];

// Step 4: Insert transactions
$successCount = 0;
$skipCount = 0;
$errorCount = 0;

$insertStmt = $db->prepare("
    INSERT INTO donation_transactions 
    (cause_id, seva_id, master_seva_id, donor_name, donor_email, donor_phone, 
     amount, currency, donation_mode, quantity, source_type, 
     razorpay_order_id, payment_status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'INR', 'one_time', ?, 'test_seed', ?, 'paid', ?, ?)
");

foreach ($testDonations as $i => $donation) {
    // Find cause
    $cause = findCause($causeMap, $donation['cause_slug_match']);
    if (!$cause) {
        echo "  [SKIP] No cause matching '{$donation['cause_slug_match']}'\n";
        $skipCount++;
        continue;
    }

    // Find seva (optional)
    $sevaId = null;
    $masterSevaId = null;
    if ($donation['seva_slug_match']) {
        $seva = findSeva($sevaMap, $donation['seva_slug_match']);
        if ($seva) {
            $masterSevaId = $seva['id'];
        }
    }

    // Generate unique order ID
    $orderId = 'test_order_' . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '_' . time();

    try {
        $insertStmt->execute([
            $cause['id'],
            $sevaId,
            $masterSevaId,
            $donation['donor'],
            $donation['email'],
            $donation['phone'],
            $donation['amount'],
            $donation['qty'],
            $orderId,
            $donation['date'],
            $donation['date'],
        ]);
        $successCount++;
    } catch (PDOException $e) {
        echo "  [ERROR] {$donation['donor']}: {$e->getMessage()}\n";
        $errorCount++;
    }
}

echo "\n=== Summary ===\n";
echo "  Inserted: {$successCount} transactions\n";
echo "  Skipped:  {$skipCount} (no matching cause)\n";
echo "  Errors:   {$errorCount}\n";
echo "\nTotal test donations now in database.\n";
echo "Visit admin/report-category, admin/report-activity, admin/report-seva to verify.\n";
