<?php
/**
 * Create Razorpay Order for Panihati Yatra Registration
 * 
 * POST /api/create-panihati-order.php
 * JSON body: { name, phone, email, travel_mode, adults_count, kids_count, bhakti_sadan, pickup_location, amount }
 */

header('Content-Type: application/json');
// CORS: Only allow requests from our own domain
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = parse_url($requestOrigin, PHP_URL_HOST) ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($requestHost && $requestHost === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
}

// Load config
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../panihati-helpers.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body']);
    exit;
}

// Validate fields
$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$travelMode = trim($input['travel_mode'] ?? '');
$adultsCount = intval($input['adults_count'] ?? 1);
$kidsCount = intval($input['kids_count'] ?? 0);
$bhaktiSadan = trim($input['bhakti_sadan'] ?? '');
$pickupLocation = trim($input['pickup_location'] ?? '');
$amount = intval($input['amount'] ?? 0); // in paise

if (empty($name) || empty($phone) || empty($email) || empty($travelMode) || empty($bhaktiSadan) || empty($pickupLocation)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields (Name, Phone, Email, Travel Mode, Bhakti Sadan, Pickup Location) are required']);
    exit;
}

if (!in_array($travelMode, ['bus', 'own_vehicle'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid travel mode']);
    exit;
}

// Double check amount logic on backend using dynamic pricing
$expectedAmountRupees = calculatePanihatiAmount($travelMode, $adultsCount, $kidsCount);
$expectedAmountPaise = (int)($expectedAmountRupees * 100);

if ($amount !== $expectedAmountPaise) {
    // If mismatch, use recalculated amount to avoid fraud
    $amount = $expectedAmountPaise;
}

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid registration amount']);
    exit;
}

// Generate unique receipt ID
$receipt = 'panihati_' . time() . '_' . rand(100, 999);

// Create order via Razorpay API
$orderData = [
    'amount' => $amount,
    'currency' => CURRENCY,
    'receipt' => $receipt,
    'notes' => [
        'yatra_type' => 'panihati',
        'travel_mode' => $travelMode,
        'adults_count' => (string)$adultsCount,
        'kids_count' => (string)$kidsCount,
        'bhakti_sadan' => $bhaktiSadan,
        'pickup_location' => $pickupLocation,
        'donor_name' => $name,
        'donor_email' => $email,
        'donor_phone' => $phone,
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
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to payment gateway: ' . $error]);
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

// Save registration record in database with status 'created'
try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO panihati_yatra_registrations 
        (name, phone, email, travel_mode, adults_count, kids_count, bhakti_sadan, pickup_location, amount, payment_status, razorpay_order_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'created', ?)
    ");
    $stmt->execute([
        $name,
        $phone,
        $email,
        $travelMode,
        $adultsCount,
        $kidsCount,
        $bhaktiSadan,
        $pickupLocation,
        $amount / 100, // DB stores rupees
        $order['id']
    ]);
    
    $registrationId = $db->lastInsertId();
    
    // Log order to file
    $logDir = __DIR__ . '/../../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'registration_id' => $registrationId,
        'order_id' => $order['id'],
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'amount' => $amount,
    ];
    @file_put_contents($logDir . '/panihati_orders.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

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
    http_response_code(500);
    echo json_encode(['error' => 'A database error occurred. Please try again.']);
    exit;
}
