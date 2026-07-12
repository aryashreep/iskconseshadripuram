<?php
/**
 * Create Razorpay Order
 * 
 * AJAX endpoint called from donate.js to create a new payment order.
 * Creates a transaction record in the database with status 'created'.
 * Returns the order ID and amount to the frontend.
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
require_once __DIR__ . '/../../../config.php';

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
$causeSlug = preg_replace('/[^a-z0-9\-]/', '', $input['cause_slug'] ?? 'general-donation');
$causeId = intval($input['cause_id'] ?? 0);
$sevaId = isset($input['seva_id']) ? intval($input['seva_id']) : null;
$donationMode = in_array($input['donation_mode'] ?? '', ['one_time', 'monthly']) ? $input['donation_mode'] : 'one_time';
$formType = in_array($input['form_type'] ?? '', ['tiers', 'quantity', 'multi_item', 'cart']) ? $input['form_type'] : 'tiers';
$donorName = trim($input['donor_name'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');
$panNumber = strtoupper(trim($input['pan_number'] ?? ''));
$specialInstructions = trim($input['special_instructions'] ?? '');
$gotra = trim($input['gotra'] ?? '');
$relation = trim($input['relation'] ?? '');
$occasion = trim($input['occasion'] ?? '');
$sevaDate = trim($input['seva_date'] ?? '');

// Source tracking (from hidden fields in the form or referrer)
$sourceType = preg_replace('/[^a-z0-9\-]/', '', $input['source_type'] ?? 'direct');
$sourceSlug = preg_replace('/[^a-z0-9\-]/', '', $input['source_slug'] ?? '');
$sourceUrl = filter_var($input['source_url'] ?? $_SERVER['HTTP_REFERER'] ?? '', FILTER_SANITIZE_URL);

// Validate amount (minimum ₹10 = 1000 paise)
if ($amount < 1000) {
    $amount = 1000; // Minimum ₹10
}

// Server-side amount verification: validate against catalog
if ($sevaId > 0) {
    // Look up the seva's actual price from the database
    try {
        $db = getDB();
        $sevaStmt = $db->prepare("SELECT amount FROM donation_cause_sevas WHERE id = ?");
        $sevaStmt->execute([$sevaId]);
        $sevaRow = $sevaStmt->fetch();
        if ($sevaRow) {
            $serverAmount = intval($sevaRow['amount'] * 100); // Convert to paise
            $quantity = max(1, intval($input['quantity'] ?? 1));
            $expectedAmount = $serverAmount * $quantity;
            // Allow the server amount to override (prevents tampering)
            $amount = $expectedAmount;
        }
    } catch (PDOException $e) {
        error_log('create-order: seva price lookup failed: ' . $e->getMessage());
    }
} elseif ($causeId > 0 && empty($sevaId)) {
    // For tier-based donations, verify against the cause's min_amount
    try {
        $db = getDB();
        $causeStmt = $db->prepare("SELECT min_amount FROM donation_causes WHERE id = ?");
        $causeStmt->execute([$causeId]);
        $causeRow = $causeStmt->fetch();
        if ($causeRow) {
            $minAmount = intval($causeRow['min_amount'] * 100); // Convert to paise
            if ($amount < $minAmount) {
                $amount = $minAmount; // Enforce server-side minimum
            }
        }
    } catch (PDOException $e) {
        error_log('create-order: cause min_amount lookup failed: ' . $e->getMessage());
    }
}

// Clamp maximum (₹10,00,000 = 100000000 paise)
if ($amount > 100000000) {
    $amount = 100000000;
}

// Generate unique receipt (Razorpay limit: 40 chars)
$receipt = 's_' . substr($causeSlug, 0, 20) . '_' . time() . '_' . rand(10, 99);
if (strlen($receipt) > 40) {
    $receipt = substr($receipt, 0, 40);
}

// Create order via Razorpay API
$orderData = [
    'amount' => $amount,
    'currency' => CURRENCY,
    'receipt' => $receipt,
    'notes' => [
        'cause_slug' => $causeSlug,
        'cause_id' => (string)$causeId,
        'seva_id' => (string)($sevaId ?? ''),
        'donation_mode' => $donationMode,
        'form_type' => $formType,
        'source_type' => $sourceType,
        'source_slug' => $sourceSlug,
        'donor_name' => $donorName,
        'donor_email' => $donorEmail,
        'donor_phone' => $donorPhone,
        'special_instructions' => $specialInstructions,
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

// Detect if this is a Master Catalog cause (Phase 2 dual-read)
// If so, store the seva_id in master_seva_id (new FK) instead of seva_id (old FK on donation_cause_sevas)
$isMasterCatalog = $causeId ? hasMasterCatalogSevas($causeId) : false;

// Create transaction record in database
$transactionId = createDonationTransaction([
    'cause_id' => $causeId ?: null,
    'seva_id' => $isMasterCatalog ? null : $sevaId,
    'master_seva_id' => $isMasterCatalog ? $sevaId : null,
    'donor_name' => $donorName,
    'donor_email' => $donorEmail,
    'donor_phone' => $donorPhone,
    'pan_number' => $panNumber ?: null,
    'amount' => $amount / 100, // Convert paise to rupees for DB storage
    'currency' => CURRENCY,
    'donation_mode' => $donationMode,
    'quantity' => 1,
    'source_type' => $sourceType,
    'source_slug' => $sourceSlug,
    'source_url' => $sourceUrl,
    'razorpay_order_id' => $order['id'],
    'payment_status' => 'created',
    'notes' => empty($specialInstructions) ? "Cause: $causeSlug, Form: $formType" : "Cause: $causeSlug, Form: $formType | Selected: $specialInstructions" . 
        (!empty($gotra) ? " | Gotra: $gotra" : "") .
        (!empty($relation) ? " | Relation: $relation" : "") .
        (!empty($occasion) ? " | Purpose: $occasion" : "") .
        (!empty($sevaDate) ? " | Seva Date: $sevaDate" : ""),
    'metadata' => [
        'cause_slug' => $causeSlug,
        'form_type' => $formType,
        'receipt' => $receipt,
        'special_instructions' => $specialInstructions,
        'gotra' => $gotra,
        'relation' => $relation,
        'occasion' => $occasion,
        'seva_date' => $sevaDate
    ],
]);

// Also log to file for backup (existing behavior)
$logDir = __DIR__ . '/../../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'transaction_id' => $transactionId,
    'order_id' => $order['id'],
    'cause_slug' => $causeSlug,
    'cause_id' => $causeId,
    'seva_id' => $sevaId,
    'donation_mode' => $donationMode,
    'amount' => $amount,
    'donor_name' => $donorName,
    'donor_email' => $donorEmail,
    'donor_phone' => $donorPhone,
];
$logFile = $logDir . '/orders.log';
@file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

// Return order details to frontend
echo json_encode([
    'order_id' => $order['id'],
    'amount' => $order['amount'],
    'currency' => $order['currency'],
    'receipt' => $order['receipt'],
    'transaction_id' => $transactionId,
]);
