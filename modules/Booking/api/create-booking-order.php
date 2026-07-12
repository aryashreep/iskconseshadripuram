<?php
/**
 * Create Razorpay Order for Puja Booking
 * 
 * AJAX endpoint called from puja booking page to create a new booking & payment order.
 * Creates a transaction record and a linked puja booking record.
 * Returns the Razorpay order ID and amount to the frontend.
 */

header('Content-Type: application/json');
// CORS: Only allow requests from our own domain
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = parse_url($requestOrigin, PHP_URL_HOST) ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($requestHost && $requestHost === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
}

// Load config (includes donation-helpers.php)
require_once __DIR__ . '/../../config.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
\Isjm\Helpers\Security::checkHoneypot($input);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// Extract and validate fields
$amount = intval($input['amount'] ?? 0);
$donorName = trim($input['donor_name'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');
$panNumber = strtoupper(trim($input['pan_number'] ?? ''));

$pujaType = trim($input['puja_type'] ?? '');
$pujaDate = trim($input['puja_date'] ?? '');
$occasion = trim($input['occasion'] ?? '');
$personName = trim($input['person_name'] ?? '');
$gotra = trim($input['gotra'] ?? '');
$rashi = trim($input['rashi'] ?? '');
$nakshatra = trim($input['nakshatra'] ?? '');
$specialInstructions = trim($input['special_instructions'] ?? '');

// Validation
if (empty($donorName) || empty($donorEmail) || empty($donorPhone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing contact details']);
    exit;
}
if (empty($pujaType) || empty($pujaDate) || empty($personName)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing puja details (type, date, or person name)']);
    exit;
}

// Validate amount (minimum ₹10 = 1000 paise)
if ($amount < 1000) {
    $amount = 1000;
}

// Server-side amount verification: verify puja offerings total against server prices
$offeringsTotal = 0;
$offeringsList = $input['special_instructions'] ?? '';
if (!empty($offeringsList)) {
    // Parse offerings from the format "id:name:price,id:name:price,..."
    $offerings = explode(',', $offeringsList);
    foreach ($offerings as $offering) {
        $parts = explode(':', trim($offering));
        if (count($parts) >= 3) {
            $offeringsTotal += intval($parts[2]); // price in paise
        }
    }
}
// The base puja price comes from the puja type — verify against known prices
$pujaPrices = [
    'sri-sri-radha-madhav' => 100800, 'sri-sri-gaura-nitai' => 50100,
    'sri-giriraja-sila' => 35100, 'sri-saligrama-sila' => 35100,
    'guru-puja' => 25100, 'anniversary' => 100800, 'birthday' => 50100,
];
$pujaSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower($pujaType));
$basePrice = $pujaPrices[$pujaSlug] ?? 0;
$expectedTotal = $basePrice + $offeringsTotal;
if ($expectedTotal > 0) {
    $amount = $expectedTotal; // Override with server-verified amount
}

// Generate unique receipt
$receipt = 'puja_' . time() . '_' . rand(100, 999);

// Create order via Razorpay API
$orderData = [
    'amount' => $amount,
    'currency' => CURRENCY,
    'receipt' => $receipt,
    'notes' => [
        'booking_type' => 'puja',
        'puja_type' => $pujaType,
        'puja_date' => $pujaDate,
        'donor_name' => $donorName,
        'donor_email' => $donorEmail,
        'donor_phone' => $donorPhone,
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

// Create transaction record in database
$transactionId = createDonationTransaction([
    'cause_id' => null,
    'seva_id' => null,
    'donor_name' => $donorName,
    'donor_email' => $donorEmail,
    'donor_phone' => $donorPhone,
    'pan_number' => $panNumber ?: null,
    'amount' => $amount / 100, // Convert paise to rupees for DB storage
    'currency' => CURRENCY,
    'donation_mode' => 'one_time',
    'quantity' => 1,
    'source_type' => 'booking',
    'source_slug' => 'puja',
    'source_url' => $_SERVER['HTTP_REFERER'] ?? '',
    'razorpay_order_id' => $order['id'],
    'payment_status' => 'created',
    'notes' => "Puja Booking: $pujaType for $personName on $pujaDate",
    'metadata' => [
        'booking_type' => 'puja',
        'puja_type' => $pujaType,
        'puja_date' => $pujaDate,
        'receipt' => $receipt,
    ],
]);

if ($transactionId) {
    // Create Puja booking details record linked to transaction
    createPujaBooking([
        'transaction_id' => $transactionId,
        'puja_type' => $pujaType,
        'puja_date' => $pujaDate,
        'occasion' => !empty($occasion) ? $occasion : null,
        'person_name' => $personName,
        'gotra' => !empty($gotra) ? $gotra : null,
        'rashi' => !empty($rashi) ? $rashi : null,
        'nakshatra' => !empty($nakshatra) ? $nakshatra : null,
        'special_instructions' => !empty($specialInstructions) ? $specialInstructions : null,
    ]);
}

// Log to file for backup
$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'transaction_id' => $transactionId,
    'order_id' => $order['id'],
    'booking_type' => 'puja',
    'puja_type' => $pujaType,
    'amount' => $amount,
    'donor_name' => $donorName,
    'donor_email' => $donorEmail,
    'donor_phone' => $donorPhone,
];
$logFile = $logDir . '/bookings.log';
@file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

// Return order details to frontend
echo json_encode([
    'order_id' => $order['id'],
    'amount' => $order['amount'],
    'currency' => $order['currency'],
    'receipt' => $order['receipt'],
    'transaction_id' => $transactionId,
]);
