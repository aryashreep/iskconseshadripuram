<?php
/**
 * Verify Razorpay Signature for Panihati Yatra Registration
 * 
 * POST /api/verify-panihati-payment.php
 * JSON body: { razorpay_order_id, razorpay_payment_id, razorpay_signature, registration_id }
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

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$orderId = $input['razorpay_order_id'] ?? '';
$paymentId = $input['razorpay_payment_id'] ?? '';
$signature = $input['razorpay_signature'] ?? '';
$registrationId = intval($input['registration_id'] ?? 0);

if (empty($orderId) || empty($paymentId) || empty($signature) || empty($registrationId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing verification payload parameters']);
    exit;
}

// Generate expected signature
$expectedSignature = hash_hmac(
    'sha256',
    $orderId . '|' . $paymentId,
    RAZORPAY_KEY_SECRET
);

// Verify signature
if (!hash_equals($expectedSignature, $signature)) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE panihati_yatra_registrations 
            SET payment_status = 'failed', razorpay_payment_id = ?, razorpay_signature = ?
            WHERE id = ? AND razorpay_order_id = ?
        ");
        $stmt->execute([$paymentId, $signature, $registrationId, $orderId]);
    } catch (PDOException $e) {
        // Log error silently
    }
    
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Payment signature verification failed']);
    exit;
}

// Update registration as PAID
try {
    $db = getDB();
    $stmt = $db->prepare("
        UPDATE panihati_yatra_registrations 
        SET payment_status = 'paid', razorpay_payment_id = ?, razorpay_signature = ?
        WHERE id = ? AND razorpay_order_id = ?
    ");
    $updated = $stmt->execute([$paymentId, $signature, $registrationId, $orderId]);
    
    // Log verification to file
    $logDir = __DIR__ . '/../../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'registration_id' => $registrationId,
        'order_id' => $orderId,
        'payment_id' => $paymentId,
        'status' => 'paid',
        'db_updated' => $updated ? 'yes' : 'no'
    ];
    @file_put_contents($logDir . '/panihati_payments.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified and registration complete',
        'registration_id' => $registrationId,
        'payment_id' => $paymentId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Payment verification failed. Please try again.']);
    exit;
}
