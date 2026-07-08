<?php
/**
 * Comprehensive Test Donations Seeder
 * Seeds transactions across ALL categories, activities, and sevas.
 * Usage: php database/migrations/seed_test_comprehensive.php
 */
require_once __DIR__ . '/../../config.php';
$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getCauseId(PDO $db, string $slug) {
    $s = $db->prepare("SELECT id FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
    $s->execute([$slug]);
    return $s->fetchColumn() ?: null;
}

function getSevaId(PDO $db, string $slug) {
    $s = $db->prepare("SELECT id FROM master_sevas WHERE slug = ? AND is_active = 1 LIMIT 1");
    $s->execute([$slug]);
    return $s->fetchColumn() ?: null;
}

$donations = [
    // ================================================================
    // FESTIVAL (25 activities)
    // ================================================================
    ['cat'=>'festival','cause'=>'ratha-yatra','seva'=>'rath-decoration','amt'=>5008,'name'=>'Rath Yatra Decor Sponsor','date'=>'2026-01-15'],
    ['cat'=>'festival','cause'=>'ratha-yatra','seva'=>'chariot-construction','amt'=>25008,'name'=>'Chariot Builder Trust','date'=>'2026-01-16'],
    ['cat'=>'festival','cause'=>'ratha-yatra','seva'=>'lighting-sponsor','amt'=>6008,'name'=>'Festival Lights Co','date'=>'2026-01-17'],
    ['cat'=>'festival','cause'=>'janmashtami','seva'=>'ekadashi-festival-donation','amt'=>2100,'name'=>'Krishna Bhakt Das','date'=>'2026-02-01'],
    ['cat'=>'festival','cause'=>'janmashtami','seva'=>'nandotsava-festival-donation','amt'=>5100,'name'=>'Nandotsava Seva Trust','date'=>'2026-02-02'],
    ['cat'=>'festival','cause'=>'rama-navami','seva'=>'snana-yatra-festival-donation','amt'=>3100,'name'=>'Rama Bhakti Foundation','date'=>'2026-02-10'],
    ['cat'=>'festival','cause'=>'gaura-purnima','seva'=>'acharya-appearance-festival-donation','amt'=>2100,'name'=>'Gaura Purnima Seva','date'=>'2026-02-15'],
    ['cat'=>'festival','cause'=>'diwali','seva'=>'deepotsava-seva-sponsor-108-lamps','amt'=>1008,'name'=>'Diwali Lamp Sponsor','date'=>'2026-03-01'],
    ['cat'=>'festival','cause'=>'govardhan-puja','seva'=>'govardhan-puja-festival-donation','amt'=>2100,'name'=>'Govardhan Seva Trust','date'=>'2026-03-05'],
    ['cat'=>'festival','cause'=>'gita-jayanti','seva'=>'gita-jayanti-festival-donation','amt'=>2100,'name'=>'Gita Jayanti Foundation','date'=>'2026-03-10'],
    ['cat'=>'festival','cause'=>'jhulan-yatra','seva'=>'jhulan-yatra-festival-donation','amt'=>2100,'name'=>'Jhulan Seva','date'=>'2026-03-15'],
    ['cat'=>'festival','cause'=>'nandotsava','seva'=>'nandotsava-dahihandi-seva','amt'=>1008,'name'=>'Nandotsava Dahi Handi','date'=>'2026-03-20'],
    ['cat'=>'festival','cause'=>'odana-sasthi','seva'=>'odana-sasthi-festival-donation','amt'=>2100,'name'=>'Odana Sasthi Seva','date'=>'2026-03-25'],
    ['cat'=>'festival','cause'=>'pushya-abhisheka','seva'=>'pushya-abhisheka-festival-donation','amt'=>2100,'name'=>'Pushya Abhisheka Trust','date'=>'2026-04-01'],
    ['cat'=>'festival','cause'=>'snana-yatra','seva'=>'sponsor-snana-herbs-perfumes','amt'=>1008,'name'=>'Snana Herbs Sponsor','date'=>'2026-04-05'],
    ['cat'=>'festival','cause'=>'akshaya-tritiya','seva'=>'akshaya-tritiya-festival-donation','amt'=>2100,'name'=>'Akshaya Tritiya Seva','date'=>'2026-04-10'],
    ['cat'=>'festival','cause'=>'bahulastami','seva'=>'bahulastami-festival-donation','amt'=>2100,'name'=>'Bahulastami Seva','date'=>'2026-04-15'],
    ['cat'=>'festival','cause'=>'bhishma-panchaka','seva'=>'bhishma-panchaka-utsav-donation','amt'=>2100,'name'=>'Bhishma Panchaka Seva','date'=>'2026-04-20'],
    ['cat'=>'festival','cause'=>'gopastami','seva'=>'gopastami-festival-donation','amt'=>2100,'name'=>'Gopastami Seva','date'=>'2026-04-25'],
    ['cat'=>'festival','cause'=>'narasimha-chaturdashi','seva'=>'event-sponsorship','amt'=>10008,'name'=>'Narasimha Chaturdashi Sponsor','date'=>'2026-05-01'],
    ['cat'=>'festival','cause'=>'nityananda-trayodashi','seva'=>'modest-support','amt'=>501,'name'=>'Nityananda Trayodasi Devotee','date'=>'2026-05-05'],
    ['cat'=>'festival','cause'=>'panihati','seva'=>'panihati-utsav-festival-donation','amt'=>2100,'name'=>'Panihati Chida Dahi Trust','date'=>'2026-05-10'],
    ['cat'=>'festival','cause'=>'radhashtami','seva'=>'generous-gift','amt'=>1001,'name'=>'Radhashtami Seva','date'=>'2026-05-15'],
    ['cat'=>'festival','cause'=>'sri-sri-radha-ramana','seva'=>'radha-ramana-appearance-donation','amt'=>2100,'name'=>'Radha Ramana Seva','date'=>'2026-05-20'],
    ['cat'=>'festival','cause'=>'tulasi-shaligram-vivaha','seva'=>'major-donation','amt'=>5001,'name'=>'Tulasi Vivaha Sponsor','date'=>'2026-05-25'],

    // ================================================================
    // EKADASHI (25 activities - pick 5 representative)
    // ================================================================
    ['cat'=>'ekadashi','cause'=>'amalaki','seva'=>'ekadashi-festival-donation','amt'=>2100,'name'=>'Amalaki Ekadashi Seva','date'=>'2026-01-20'],
    ['cat'=>'ekadashi','cause'=>'kamada','seva'=>'ekadashi-deepotsava-lamps-offering','amt'=>1008,'name'=>'Kamada Ekadashi Lamps','date'=>'2026-02-20'],
    ['cat'=>'ekadashi','cause'=>'nirjala','seva'=>'ekadashi-mukhya-yajaman-seva','amt'=>21000,'name'=>'Nirjala Ekadashi Mukhya','date'=>'2026-03-20'],
    ['cat'=>'ekadashi','cause'=>'sayana','seva'=>'ekadashi-maha-abhishekam-seva','amt'=>5008,'name'=>'Sayana Ekadashi Abhishekam','date'=>'2026-04-20'],
    ['cat'=>'ekadashi','cause'=>'utthana','seva'=>'ekadashi-satvik-prasadam-distribution','amt'=>5008,'name'=>'Utthana Ekadashi Prasadam','date'=>'2026-05-20'],

    // ================================================================
    // APPEARANCE (4 activities)
    // ================================================================
    ['cat'=>'appearance','cause'=>'srila-prabhupada-appearance','seva'=>'acharya-appearance-mukhya-yajaman','amt'=>21000,'name'=>'SP Appearance Mukhya','date'=>'2026-02-25'],
    ['cat'=>'appearance','cause'=>'srila-prabhupada-appearance','seva'=>'acharya-pushpanjali-abhishekam','amt'=>5008,'name'=>'SP Appearance Pushpanjali','date'=>'2026-02-26'],
    ['cat'=>'appearance','cause'=>'sri-advaita-acharya-appearance','seva'=>'sri-advaita-acharya-appearance-festival-donation','amt'=>2100,'name'=>'Advaita Acharya Seva','date'=>'2026-03-01'],
    ['cat'=>'appearance','cause'=>'srila-bhaktisiddhanta-sarasvati-appearance','seva'=>'srila-bhaktisiddhanta-sarasvati-appearance-festival-donation','amt'=>2100,'name'=>'Bhaktisiddhanta Seva','date'=>'2026-03-05'],
    ['cat'=>'appearance','cause'=>'srila-bhaktivinoda-thakura-appearance','seva'=>'srila-bhaktivinoda-thakura-appearance-festival-donation','amt'=>2100,'name'=>'Bhaktivinoda Thakura Seva','date'=>'2026-03-10'],

    // ================================================================
    // DISAPPEARANCE (5 activities)
    // ================================================================
    ['cat'=>'disappearance','cause'=>'srila-prabhupada-disappearance','seva'=>'srila-prabhupada-disappearance-festival-donation','amt'=>2100,'name'=>'SP Disappearance Seva','date'=>'2026-01-25'],
    ['cat'=>'disappearance','cause'=>'srila-bhaktisiddhanta-disappearance','seva'=>'srila-bhaktisiddhanta-sarasvati-disappearance-festival-donation','amt'=>2100,'name'=>'Bhaktisiddhanta Dis','date'=>'2026-02-15'],
    ['cat'=>'disappearance','cause'=>'bhaktivinoda-thakura-disappearance','seva'=>'srila-bhaktivinoda-thakura-disappearance-festival-donation','amt'=>2100,'name'=>'Bhaktivinoda Dis Seva','date'=>'2026-03-15'],
    ['cat'=>'disappearance','cause'=>'gaura-kisora-dasa-babaji-disappearance','seva'=>'srila-gaura-kisora-dasa-babaji-disappearance-festival-donation','amt'=>2100,'name'=>'Gaura Kisora Dis Seva','date'=>'2026-04-15'],
    ['cat'=>'disappearance','cause'=>'srila-jagannatha-dasa-babaji-disappearance','seva'=>'srila-jagannatha-dasa-babaji-disappearance-festival-donation','amt'=>2100,'name'=>'Jagannatha Dasa Dis','date'=>'2026-05-15'],

    // ================================================================
    // EVENT (2 activities)
    // ================================================================
    ['cat'=>'event','cause'=>'caturmasya','seva'=>'caturmasya-vrata-donation','amt'=>2100,'name'=>'Caturmasya Vrata Donor','date'=>'2026-01-30'],
    ['cat'=>'event','cause'=>'caturmasya','seva'=>'caturmasya-daily-abhishekam-seva','amt'=>5008,'name'=>'Caturmasya Abhishekam','date'=>'2026-02-10'],
    ['cat'=>'event','cause'=>'shiksha-ceremony','seva'=>'shiksha-ceremony-contribution','amt'=>2100,'name'=>'Shiksha Ceremony Trust','date'=>'2026-03-30'],
    ['cat'=>'event','cause'=>'shiksha-ceremony','seva'=>'shiksha-homa-yajna-sponsor','amt'=>5008,'name'=>'Shiksha Homa Sponsor','date'=>'2026-04-01'],

    // ================================================================
    // SERVICE (6 activities)
    // ================================================================
    // Daily Seva
    ['cat'=>'service','cause'=>'daily-seva','seva'=>'flower-decoration','amt'=>5008,'name'=>'Daily Flower Seva','date'=>'2026-01-05'],
    ['cat'=>'service','cause'=>'daily-seva','seva'=>'archana-seva','amt'=>108,'name'=>'Daily Archana Donor','date'=>'2026-02-05'],
    // Food for Life
    ['cat'=>'service','cause'=>'food-for-life','seva'=>'annadhanam-seva','amt'=>5008,'name'=>'Annadanam Seva Trust','date'=>'2026-01-10'],
    ['cat'=>'service','cause'=>'food-for-life','seva'=>'daily-annadanam-feed-50-devotees','amt'=>1008,'name'=>'Daily Annadanam','date'=>'2026-02-10'],
    // Nitya Seva
    ['cat'=>'service','cause'=>'nitya-seva','seva'=>'lord-jagannath-seva-deity-worship-care','amt'=>20000,'name'=>'Jagannath Seva Patron','date'=>'2026-01-15'],
    ['cat'=>'service','cause'=>'nitya-seva','seva'=>'sri-sri-radha-krishnachandra-mukhya-seva','amt'=>25000,'name'=>'RK Mukhya Seva Patron','date'=>'2026-02-15'],
    // Shastra Daan
    ['cat'=>'service','cause'=>'shastra-daan','seva'=>'bhagavad-gita-distribution','amt'=>1008,'name'=>'BG Distribution Trust','date'=>'2026-01-20'],
    ['cat'=>'service','cause'=>'shastra-daan','seva'=>'book-distribution','amt'=>508,'name'=>'Book Seva Donor','date'=>'2026-02-20'],
    // Tula Daan
    ['cat'=>'service','cause'=>'tula-daan-utsav','seva'=>'event-sponsorship','amt'=>10008,'name'=>'Tula Daan Utsav Sponsor','date'=>'2026-03-20'],
    // Digital Initiatives
    ['cat'=>'service','cause'=>'support-our-digital-initiatives','seva'=>'temple-website-development','amt'=>2001,'name'=>'Website Dev Sponsor','date'=>'2026-01-25'],
    ['cat'=>'service','cause'=>'support-our-digital-initiatives','seva'=>'mobile-app-development','amt'=>2001,'name'=>'Mobile App Sponsor','date'=>'2026-02-25'],
    ['cat'=>'service','cause'=>'support-our-digital-initiatives','seva'=>'live-streaming-infrastructure','amt'=>2001,'name'=>'Live Stream Sponsor','date'=>'2026-03-25'],
    ['cat'=>'service','cause'=>'support-our-digital-initiatives','seva'=>'cyber-security','amt'=>2001,'name'=>'Cyber Security Sponsor','date'=>'2026-04-25'],

    // ================================================================
    // CONSTRUCTION (1 activity)
    // ================================================================
    ['cat'=>'construction','cause'=>'donate-a-brick','seva'=>'sponsor-1-brick-for-temple-construction','amt'=>1108,'name'=>'Brick Donor 1','date'=>'2026-01-08'],
    ['cat'=>'construction','cause'=>'donate-a-brick','seva'=>'sponsor-108-bricks-mahasponsor','amt'=>108000,'name'=>'Maha Brick Sponsor','date'=>'2026-02-08'],
    ['cat'=>'construction','cause'=>'donate-a-brick','seva'=>'sponsor-1-square-foot-temple-area','amt'=>5008,'name'=>'1 Sq Ft Donor','date'=>'2026-03-08'],
    ['cat'=>'construction','cause'=>'donate-a-brick','seva'=>'temple-decoration','amt'=>2001,'name'=>'Temple Decor Fund','date'=>'2026-04-08'],
    ['cat'=>'construction','cause'=>'donate-a-brick','seva'=>'electrical-maintenance','amt'=>1501,'name'=>'Electrical Seva','date'=>'2026-05-08'],

    // ================================================================
    // GENERAL (1 activity)
    // ================================================================
    ['cat'=>'general','cause'=>'general-donation','seva'=>'general-temple-donation','amt'=>1001,'name'=>'General Temple Donor','date'=>'2026-01-12'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'corpus-fund','amt'=>2001,'name'=>'Corpus Fund Donor','date'=>'2026-02-12'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'building-fund','amt'=>2001,'name'=>'Building Fund Donor','date'=>'2026-03-12'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'renovation-fund','amt'=>1501,'name'=>'Renovation Donor','date'=>'2026-04-12'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'education-support','amt'=>501,'name'=>'Education Seva','date'=>'2026-05-12'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'charity-relief-fund','amt'=>501,'name'=>'Charity Donor','date'=>'2026-01-28'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'emergency-fund','amt'=>501,'name'=>'Emergency Fund Donor','date'=>'2026-02-28'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'festival-fund','amt'=>1001,'name'=>'Festival Fund Donor','date'=>'2026-03-28'],
    ['cat'=>'general','cause'=>'general-donation','seva'=>'other-seva','amt'=>501,'name'=>'Other Seva Donor','date'=>'2026-04-28'],
];

$insertStmt = $db->prepare("
    INSERT INTO donation_transactions 
    (cause_id, seva_id, master_seva_id, donor_name, donor_email, donor_phone, 
     amount, currency, donation_mode, quantity, source_type, 
     razorpay_order_id, payment_status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'INR', 'one_time', 1, 'test_comprehensive', ?, 'paid', ?, ?)
");

$ok = 0;
$skip = 0;
$errors = [];

foreach ($donations as $i => $d) {
    $causeId = getCauseId($db, $d['cause']);
    if (!$causeId) { $errors[] = "SKIP: no cause '{$d['cause']}'"; $skip++; continue; }
    
    $masterSevaId = getSevaId($db, $d['seva']);
    if (!$masterSevaId) { $errors[] = "SKIP: no seva '{$d['seva']}'"; $skip++; continue; }
    
    $orderId = 'test_comp_' . str_pad($i+1, 3, '0', STR_PAD_LEFT) . '_' . mt_rand(100000, 999999);
    $email = strtolower(preg_replace('/[^a-zA-Z]/', '', $d['name'])) . '@test.com';
    $phone = '9' . str_pad(mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
    
    try {
        $insertStmt->execute([
            $causeId, null, $masterSevaId, $d['name'], $email, $phone,
            $d['amt'], $orderId, $d['date'] . ' ' . str_pad(mt_rand(7,17), 2, '0', STR_PAD_LEFT) . ':' . str_pad(mt_rand(0,59), 2, '0', STR_PAD_LEFT) . ':00',
            $d['date'] . ' ' . str_pad(mt_rand(7,17), 2, '0', STR_PAD_LEFT) . ':' . str_pad(mt_rand(0,59), 2, '0', STR_PAD_LEFT) . ':00'
        ]);
        $ok++;
    } catch (PDOException $e) {
        $errors[] = "ERROR: {$d['name']}: " . $e->getMessage();
    }
}

echo "=== Comprehensive Seed Complete ===\n";
echo "Inserted: $ok\n";
echo "Skipped: $skip\n";
if ($errors) { echo "Issues:\n"; foreach ($errors as $e) echo "  $e\n"; }

// Summary
$catCount = $db->query("SELECT COUNT(DISTINCT c.category) FROM donation_transactions t JOIN donation_causes c ON t.cause_id = c.id WHERE t.source_type = 'test_comprehensive'")->fetchColumn();
$actCount = $db->query("SELECT COUNT(DISTINCT t.cause_id) FROM donation_transactions t WHERE t.source_type = 'test_comprehensive'")->fetchColumn();
$sevaCount = $db->query("SELECT COUNT(DISTINCT t.master_seva_id) FROM donation_transactions t WHERE t.source_type = 'test_comprehensive' AND t.master_seva_id IS NOT NULL")->fetchColumn();
$total = $db->query("SELECT COUNT(*), SUM(amount) FROM donation_transactions WHERE source_type = 'test_comprehensive'")->fetch();

echo "\n=== Coverage Summary ===\n";
echo "Categories covered: $catCount\n";
echo "Activities covered: $actCount\n";
echo "Distinct sevas used: $sevaCount\n";
echo "Total transactions: {$total[0]}\n";
echo "Total amount: Rs" . number_format($total[1]) . "\n";
