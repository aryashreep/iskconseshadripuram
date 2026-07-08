<?php
/**
 * Dashboard Visual Seeder - data for charts, YoY, retention, heatmap
 * Usage: php database/migrations/seed_dashboard_data.php
 */
require_once __DIR__ . '/../../config.php';
$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get cause IDs by slug
function getCause(PDO $db, string $slug) {
    $s = $db->prepare("SELECT id, category FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
    $s->execute([$slug]);
    return $s->fetch() ?: null;
}

// Get master seva IDs
function getSeva(PDO $db, string $slug) {
    $s = $db->prepare("SELECT id FROM master_sevas WHERE slug = ? AND is_active = 1 LIMIT 1");
    $s->execute([$slug]);
    return $s->fetchColumn() ?: null;
}

$causes = [
    'rath-yatra' => getCause($db, 'rath-yatra'),
    'janmashtami' => getCause($db, 'janmashtami'),
    'rama-navami' => getCause($db, 'rama-navami'),
    'gaura-purnima' => getCause($db, 'gaura-purnima'),
    'diwali' => getCause($db, 'diwali'),
    'food-for-life' => getCause($db, 'food-for-life'),
    'nitya-seva' => getCause($db, 'nitya-seva'),
    'shastra-daan' => getCause($db, 'shastra-daan'),
    'donate-a-brick' => getCause($db, 'donate-a-brick'),
    'general-donation' => getCause($db, 'general-donation'),
    'amalaki' => getCause($db, 'amalaki'),
    'nirjala' => getCause($db, 'nirjala'),
    'panihati' => getCause($db, 'panihati'),
    'gita-jayanti' => getCause($db, 'gita-jayanti'),
    'govardhan-puja' => getCause($db, 'govardhan-puja'),
    'support-our-digital-initiatives' => getCause($db, 'support-our-digital-initiatives'),
    'daily-seva' => getCause($db, 'daily-seva'),
    'caturmasya' => getCause($db, 'caturmasya'),
];

$sevas = [
    'rath-decoration' => getSeva($db, 'rath-decoration'),
    'annadanam-seva' => getSeva($db, 'annadanam-seva'),
    'general-temple-donation' => getSeva($db, 'general-temple-donation'),
    'temple-website-development' => getSeva($db, 'temple-website-development'),
    'sponsor-1-brick-for-temple-construction' => getSeva($db, 'sponsor-1-brick-for-temple-construction'),
    'flower-decoration' => getSeva($db, 'flower-decoration'),
    'ekadashi-festival-donation' => getSeva($db, 'ekadashi-festival-donation'),
    'bhagavad-gita-distribution' => getSeva($db, 'bhagavad-gita-distribution'),
    'devotee-care-sponsor' => getSeva($db, 'devotee-care-sponsor'),
    'corpus-fund' => getSeva($db, 'corpus-fund'),
];

// Donor pool - some repeat, some unique
$donors = [
    ['name' => 'Radha Krishna Das', 'email' => 'rk.das@gmail.com', 'phone' => '9876543001'],
    ['name' => 'Sita Raman', 'email' => 'sita.raman@gmail.com', 'phone' => '9876543002'],
    ['name' => 'Gopal Krishna', 'email' => 'gopal.k@gmail.com', 'phone' => '9876543003'],
    ['name' => 'Yamuna Devi', 'email' => 'yamuna.d@gmail.com', 'phone' => '9876543004'],
    ['name' => 'Narasimha Das', 'email' => 'narasimha.d@gmail.com', 'phone' => '9876543005'],
    ['name' => 'Lakshmi Priya', 'email' => 'lakshmi.p@gmail.com', 'phone' => '9876543006'],
    ['name' => 'Madhav Das', 'email' => 'madhav.d@gmail.com', 'phone' => '9876543007'],
    ['name' => 'Rukmini Devi', 'email' => 'rukmini.d@gmail.com', 'phone' => '9876543008'],
    ['name' => 'Haridas Thakur', 'email' => 'haridas.t@gmail.com', 'phone' => '9876543009'],
    ['name' => 'Gauranga Das', 'email' => 'gauranga.d@gmail.com', 'phone' => '9876543010'],
    ['name' => 'Nityananda Das', 'email' => 'nityananda.d@gmail.com', 'phone' => '9876543011'],
    ['name' => 'Advaita Acharya', 'email' => 'advaita.a@gmail.com', 'phone' => '9876543012'],
    ['name' => 'Chaitanya Mahaprabhu', 'email' => 'chaitanya.m@gmail.com', 'phone' => '9876543013'],
    ['name' => 'Balarama Das', 'email' => 'balarama.d@gmail.com', 'phone' => '9876543014'],
    ['name' => 'Jagannatha Das', 'email' => 'jagannatha.d@gmail.com', 'phone' => '9876543015'],
    ['name' => 'Subhadra Devi', 'email' => 'subhadra.d@gmail.com', 'phone' => '9876543016'],
    ['name' => 'Shyam Sunder', 'email' => 'shyam.s@gmail.com', 'phone' => '9876543017'],
    ['name' => 'Govinda Das', 'email' => 'govinda.d@gmail.com', 'phone' => '9876543018'],
    ['name' => 'Tulasi Devi', 'email' => 'tulasi.d@gmail.com', 'phone' => '9876543019'],
    ['name' => 'Prahlad Das', 'email' => 'prahlad.d@gmail.com', 'phone' => '9876543020'],
    // One-time donors
    ['name' => 'Donor A', 'email' => 'donor.a@test.com', 'phone' => '9876543101'],
    ['name' => 'Donor B', 'email' => 'donor.b@test.com', 'phone' => '9876543102'],
    ['name' => 'Donor C', 'email' => 'donor.c@test.com', 'phone' => '9876543103'],
    ['name' => 'Donor D', 'email' => 'donor.d@test.com', 'phone' => '9876543104'],
    ['name' => 'Donor E', 'email' => 'donor.e@test.com', 'phone' => '9876543105'],
];

$insertStmt = $db->prepare("
    INSERT INTO donation_transactions 
    (cause_id, seva_id, master_seva_id, donor_name, donor_email, donor_phone, amount, currency, donation_mode, quantity, source_type, razorpay_order_id, payment_status, created_at, updated_at)
    VALUES (?, NULL, ?, ?, ?, ?, ?, 'INR', 'one_time', 1, 'seed_dashboard', ?, 'paid', ?, ?)
");

$ok = 0;
$orderNum = 0;

// Generate transactions across months for YoY and trend
// Pattern: repeat donors contribute to multiple causes across months
$transactions = [];

// Year 2025 data (previous year) - 30 transactions across 6 months
$y25Months = [1,2,3,4,5,6,7,8,9,10,11,12];
$y25Causes = ['rath-yatra','janmashtami','rama-navami','gaura-purnima','diwali','food-for-life','nitya-seva','shastra-daan','general-donation','donate-a-brick','amalaki','panihati','govardhan-puja','gita-jayanti','daily-seva'];
foreach ($y25Months as $m) {
    $count = ($m == 7 || $m == 8) ? 4 : 2; // more in Jul/Aug (festival season)
    for ($i = 0; $i < $count; $i++) {
        $donor = $donors[array_rand($donors)];
        $causeSlug = $y25Causes[array_rand($y25Causes)];
        $cause = $causes[$causeSlug] ?? null;
        if (!$cause) continue;
        $sevaSlug = array_rand($sevas);
        $sevaId = $sevas[$sevaSlug] ?: null;
        $day = rand(1, 28);
        $hour = rand(7, 20);
        $min = rand(0, 59);
        $date = sprintf('2025-%02d-%02d %02d:%02d:00', $m, $day, $hour, $min);
        $amount = [501,1001,1500,2100,3100,5008,10008,1100,2500,800][array_rand([501,1001,1500,2100,3100,5008,10008,1100,2500,800])];
        $transactions[] = [$cause['id'], $sevaId, $donor['name'], $donor['email'], $donor['phone'], $amount, $date];
    }
}

// Year 2026 data (current year) - 50 transactions across all months
$y26Causes = ['rath-yatra','janmashtami','rama-navami','gaura-purnima','diwali','food-for-life','nitya-seva','shastra-daan','general-donation','donate-a-brick','amalaki','nirjala','panihati','govardhan-puja','gita-jayanti','support-our-digital-initiatives','daily-seva','caturmasya'];
foreach (range(1, 7) as $m) {
    $count = ($m == 1 || $m == 2) ? 6 : ($m >= 6 ? 8 : 5); // more in recent months
    for ($i = 0; $i < $count; $i++) {
        $donor = $donors[array_rand($donors)];
        $causeSlug = $y26Causes[array_rand($y26Causes)];
        $cause = $causes[$causeSlug] ?? null;
        if (!$cause) continue;
        $sevaSlug = array_rand($sevas);
        $sevaId = $sevas[$sevaSlug] ?: null;
        $day = rand(1, 28);
        $hour = rand(7, 20);
        $min = rand(0, 59);
        $date = sprintf('2026-%02d-%02d %02d:%02d:00', $m, $day, $hour, $min);
        $amount = [501,1001,1500,2100,3100,5008,10008,1100,2500,800,15000,25000][array_rand([501,1001,1500,2100,3100,5008,10008,1100,2500,800,15000,25000])];
        $transactions[] = [$cause['id'], $sevaId, $donor['name'], $donor['email'], $donor['phone'], $amount, $date];
    }
}

foreach ($transactions as $t) {
    $orderNum++;
    $orderId = 'seed_dash_' . str_pad($orderNum, 4, '0', STR_PAD_LEFT) . '_' . mt_rand(100000, 999999);
    $insertStmt->execute([
        $t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $orderId, $t[6], $t[6]
    ]);
    $ok++;
}

echo "Inserted: $ok transactions" . PHP_EOL;

// Summary
$stats = $db->query("SELECT source_type, COUNT(*) as cnt, SUM(amount) as total FROM donation_transactions WHERE source_type='seed_dashboard' GROUP BY source_type")->fetch();
echo "Total: {$stats['cnt']} txns, Rs" . number_format($stats['total']) . PHP_EOL;

// Show repeat donors
$repeat = $db->query("SELECT donor_email, donor_name, COUNT(*) as cnt, SUM(amount) as total FROM donation_transactions WHERE source_type='seed_dashboard' GROUP BY donor_email HAVING cnt > 1 ORDER BY total DESC LIMIT 5")->fetchAll();
echo "\nTop repeat donors:" . PHP_EOL;
foreach ($repeat as $r) echo "  {$r['donor_name']}: {$r['cnt']} donations, Rs" . number_format($r['total']) . PHP_EOL;
