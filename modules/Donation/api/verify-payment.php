<?php
/**
 * Verify Razorpay Payment
 * 
 * AJAX endpoint called from donate.js after successful payment.
 * Verifies the payment signature to ensure the payment is legitimate.
 * Updates the transaction record in the database with payment details.
 * On success, logs the payment and returns confirmation.
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

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$razorpayOrderId = $input['razorpay_order_id'] ?? '';
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';

if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payment verification data']);
    exit;
}

// Extract additional fields
$causeSlug = preg_replace('/[^a-z0-9\-]/', '', $input['cause_slug'] ?? 'general-donation');
$sevaId = isset($input['seva_id']) ? intval($input['seva_id']) : null;
$donationMode = in_array($input['donation_mode'] ?? '', ['one_time', 'monthly']) ? $input['donation_mode'] : 'one_time';
$amount = intval($input['amount'] ?? 0);
$donorName = trim($input['donor_name'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');

// Generate expected signature
$expectedSignature = hash_hmac(
    'sha256',
    $razorpayOrderId . '|' . $razorpayPaymentId,
    RAZORPAY_KEY_SECRET
);

// Compare signatures (using hash_equals to prevent timing attacks)
if (!hash_equals($expectedSignature, $razorpaySignature)) {
    // Update transaction status to failed if we have the order ID
    if ($razorpayOrderId) {
        updateDonationTransaction($razorpayOrderId, [
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature,
            'payment_status' => 'failed',
        ]);
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Payment signature verification failed',
    ]);
    exit;
}

// Payment is verified! Update the transaction in the database
$amountInr = $amount / 100; // Convert paise to rupees for DB

$updated = updateDonationTransaction($razorpayOrderId, [
    'razorpay_payment_id' => $razorpayPaymentId,
    'razorpay_signature' => $razorpaySignature,
    'payment_status' => 'paid',
    'donor_name' => $donorName,
    'donor_email' => $donorEmail,
    'donor_phone' => $donorPhone,
]);

// Log to file for backup (existing behavior)
$logDir = __DIR__ . '/../../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'order_id' => $razorpayOrderId,
    'payment_id' => $razorpayPaymentId,
    'cause_slug' => $causeSlug,
    'seva_id' => $sevaId,
    'donation_mode' => $donationMode,
    'amount' => $amount,
    'donor_name' => $donorName,
    'donor_email' => $donorEmail,
    'donor_phone' => $donorPhone,
    'db_updated' => $updated ? 'yes' : 'no',
];

$logFile = $logDir . '/payments.log';
$logLine = json_encode($logEntry) . PHP_EOL;
@file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

// Also record individual cause log files
$causeFile = $logDir . '/' . $causeSlug . '.log';
@file_put_contents($causeFile, $logLine, FILE_APPEND | LOCK_EX);

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Payment verified successfully',
    'payment_id' => $razorpayPaymentId,
    'order_id' => $razorpayOrderId,
    'amount' => $amount,
    'cause_slug' => $causeSlug,
]);
