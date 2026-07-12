<?php
/**
 * Janmashtami Module — Create Contest Registration Order
 *
 * Creates a Razorpay order for contest registration and saves the
 * registration record in the database.
 *
 * POST /api/create-contest-order
 * JSON body: { contest_slug, contest_name, participant_name, age, phone, email, amount }
 *
 * Response: { success, order_id, amount, currency, registration_id }
 */

header('Content-Type: application/json');

// CORS: only allow requests from own domain
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = parse_url($requestOrigin, PHP_URL_HOST) ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($requestHost && $requestHost === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
}

require_once __DIR__ . '/../../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
\Isjm\Helpers\Security::checkHoneypot($input);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body']);
    exit;
}

// Validate fields
$contestSlug = trim($input['contest_slug'] ?? '');
$contestName = trim($input['contest_name'] ?? '');
$participantName = trim($input['participant_name'] ?? '');
$ageGroup = trim($input['age_group'] ?? '');
$participantType = trim($input['participant_type'] ?? '');
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$amount = intval($input['amount'] ?? 0); // in paise

if (empty($contestSlug) || empty($contestName) || empty($participantName) || empty($ageGroup) || empty($participantType) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: contest, name, age_group, participant_type, phone']);
    exit;
}

// Validate age_group
$validAgeGroups = ['group1', 'group2', 'group3'];
if (!in_array($ageGroup, $validAgeGroups, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid age group selected']);
    exit;
}

// Validate participant_type
$validTypes = ['online', 'offline'];
if (!in_array($participantType, $validTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid participant type']);
    exit;
}

// Validate contest slug against data
$janmashtamiData = include __DIR__ . '/../content/grand-festivals/data/janmashtami-contests.php';
$validSlugs = [];
foreach ($janmashtamiData['contests'] ?? [] as $c) {
    if ($c['status'] === 'active') {
        $validSlugs[] = $c['slug'];
    }
}
if (!in_array($contestSlug, $validSlugs, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or inactive contest selected']);
    exit;
}

// Validate amount (must equal registration fee from config)
$expectedFeePaise = (($janmashtamiData['registration']['fee'] ?? 108) * 100);
if ($amount !== $expectedFeePaise) {
    $amount = $expectedFeePaise; // Use correct fee to prevent fraud
}

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid registration amount']);
    exit;
}

// Generate unique receipt
$receipt = 'jcontest_' . time() . '_' . rand(100, 999);

// Create order via Razorpay API
$orderData = [
    'amount' => $amount,
    'currency' => CURRENCY,
    'receipt' => $receipt,
    'notes' => [
        'type' => 'janmashtami_contest',
        'contest_slug' => $contestSlug,
        'contest_name' => $contestName,
        'participant_name' => $participantName,
        'age_group' => $ageGroup,
        'participant_type' => $participantType,
        'phone' => $phone,
        'email' => $email,
    ],
];

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($orderData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => RAZORPAY_TEST_MODE ? false : true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to payment gateway: ' . $curlError]);
    exit;
}

$order = json_decode($response, true);

if ($httpCode !== 200 || !isset($order['id'])) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create payment order',
        'details' => $order['error']['description'] ?? 'Unknown error',
    ]);
    exit;
}

// Save registration record in database
try {
    $db = getDB();

    // Ensure table exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS `janmashtami_contest_registrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `participant_name` VARCHAR(150) NOT NULL,
            `age_group` VARCHAR(20) NOT NULL COMMENT 'group1, group2, group3',
            `participant_type` VARCHAR(20) NOT NULL COMMENT 'online, offline',
            `phone` VARCHAR(30) NOT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `contest_slug` VARCHAR(100) NOT NULL,
            `contest_name` VARCHAR(255) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL DEFAULT 108.00,
            `payment_status` ENUM('created','attempted','paid','failed') NOT NULL DEFAULT 'created',
            `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
            `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
            `razorpay_signature` VARCHAR(255) DEFAULT NULL,
            `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_status` (`payment_status`),
            INDEX `idx_contest` (`contest_slug`),
            INDEX `idx_phone` (`phone`),
            INDEX `idx_order` (`razorpay_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $stmt = $db->prepare("
        INSERT INTO janmashtami_contest_registrations
        (participant_name, age_group, participant_type, phone, email, contest_slug, contest_name, amount, payment_status, razorpay_order_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'created', ?)
    ");
    $stmt->execute([
        $participantName,
        $ageGroup,
        $participantType,
        $phone,
        $email ?: null,
        $contestSlug,
        $contestName,
        $amount / 100, // Store in rupees
        $order['id'],
    ]);

    $registrationId = (int) $db->lastInsertId();

    // Log order
    $logDir = __DIR__ . '/../../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'janmashtami_contest',
        'registration_id' => $registrationId,
        'order_id' => $order['id'],
        'contest' => $contestSlug,
        'participant' => $participantName,
        'phone' => $phone,
        'amount' => $amount,
    ];
    @file_put_contents($logDir . '/janmashtami_contest_orders.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    // Return order details to frontend
    echo json_encode([
        'success' => true,
        'order_id' => $order['id'],
        'amount' => $order['amount'],
        'currency' => $order['currency'],
        'receipt' => $order['receipt'],
        'registration_id' => $registrationId,
    ]);

} catch (PDOException $e) {
    error_log('Janmashtami contest order DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'A database error occurred. Please try again.']);
    exit;
}
