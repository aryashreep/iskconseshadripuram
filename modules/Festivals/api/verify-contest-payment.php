<?php
/**
 * Janmashtami Module — Verify Contest Payment
 *
 * Verifies Razorpay payment signature and updates registration status.
 *
 * POST /api/verify-contest-payment
 * JSON body: { razorpay_order_id, razorpay_payment_id, razorpay_signature, registration_id }
 *
 * Response: { success, message, registration_id }
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
    // Record failed attempt
    try {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE janmashtami_contest_registrations
            SET payment_status = 'failed', razorpay_payment_id = ?, razorpay_signature = ?
            WHERE id = ? AND razorpay_order_id = ?
        ");
        $stmt->execute([$paymentId, $signature, $registrationId, $orderId]);
    } catch (PDOException $e) {
        error_log('Janmashtami contest verify failed status update: ' . $e->getMessage());
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Payment signature verification failed']);
    exit;
}

// Update registration as PAID
try {
    $db = getDB();
    $stmt = $db->prepare("
        UPDATE janmashtami_contest_registrations
        SET payment_status = 'paid', razorpay_payment_id = ?, razorpay_signature = ?
        WHERE id = ? AND razorpay_order_id = ?
    ");
    $updated = $stmt->execute([$paymentId, $signature, $registrationId, $orderId]);

    // Log successful payment
    $logDir = __DIR__ . '/../../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'janmashtami_contest_payment',
        'registration_id' => $registrationId,
        'order_id' => $orderId,
        'payment_id' => $paymentId,
        'status' => 'paid',
    ];
    @file_put_contents($logDir . '/janmashtami_contest_payments.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified and contest registration complete.',
        'registration_id' => $registrationId,
        'payment_id' => $paymentId,
    ]);

} catch (PDOException $e) {
    error_log('Janmashtami contest verify DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Payment verification failed. Please try again.']);
    exit;
}
