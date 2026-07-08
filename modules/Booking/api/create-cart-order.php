<?php
/**
 * Create Cart Order API
 * 
 * Creates a Razorpay order for cart-based checkout.
 * Accepts items from both donation and puja modes.
 * 
 * POST /api/create-cart-order.php
 * JSON body: { mode, source, items, totalAmount, donor_name, donor_email, donor_phone, ... }
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config.php';

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

// Validate required fields
$mode = $input['mode'] ?? '';
$totalAmount = (int)($input['totalAmount'] ?? 0);
$donorName = trim($input['donor_name'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');

if (!in_array($mode, ['donation', 'puja'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid cart mode']);
    exit;
}

if ($totalAmount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid total amount']);
    exit;
}

if (empty($donorName) || empty($donorEmail) || empty($donorPhone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Donor name, email, and phone are required']);
    exit;
}

// Initialize Razorpay
$key_id = RAZORPAY_KEY_ID;
$key_secret = RAZORPAY_KEY_SECRET;

// Create Razorpay Order
$order_data = [
    'receipt' => 'cart_' . time() . '_' . substr(md5(uniqid()), 0, 8),
    'amount' => $totalAmount, // already in paise
    'currency' => CURRENCY,
    'notes' => [
        'mode' => $mode,
        'source_slug' => $input['source']['slug'] ?? '',
        'source_title' => $input['source']['title'] ?? '',
        'donor_name' => $donorName,
        'donor_email' => $donorEmail,
        'donor_phone' => $donorPhone,
        'item_count' => count($input['items'] ?? []),
    ]
];

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, RAZORPAY_TEST_MODE ? false : true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Payment gateway error: ' . $error]);
    exit;
}

$order = json_decode($response, true);

if ($http_code !== 200 || !$order || !isset($order['id'])) {
    $error_msg = $order['error']['description'] ?? 'Failed to create payment order';
    http_response_code(500);
    echo json_encode(['error' => $error_msg]);
    exit;
}

// Return order details to frontend
echo json_encode([
    'success' => true,
    'order_id' => $order['id'],
    'amount' => $order['amount'],
    'currency' => $order['currency'],
    'receipt' => $order['receipt'],
]);
