<?php
/**
 * Razorpay Webhook Endpoint
 *
 * Handles server-to-server payment notifications from Razorpay.
 * This is the AUTHORITATIVE payment verification — client-side verify-payment.php
 * is a convenience flow, but the webhook is the source of truth.
 *
 * Events handled:
 *   - payment.captured → Mark transaction as 'paid'
 *   - payment.failed   → Mark transaction as 'failed'
 *
 * Security: Validates Razorpay signature using HMAC-SHA256.
 *
 * Setup: Configure this URL in Razorpay Dashboard → Settings → Webhooks
 *        URL: https://yourdomain.com/api/webhook.php
 *        Secret: Same as RAZORPAY_KEY_SECRET
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../../config.php';

// Read the raw POST body
$rawPayload = file_get_contents('php://input');
$payload = json_decode($rawPayload, true);

if (!$payload) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// Get Razorpay signature from headers
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (empty($signature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing signature header']);
    exit;
}

// Verify webhook signature
$expectedSignature = hash_hmac('sha256', $rawPayload, RAZORPAY_KEY_SECRET);

if (!hash_equals($expectedSignature, $signature)) {
    error_log("Webhook signature mismatch for event: " . ($payload['event'] ?? 'unknown'));
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$event = $payload['event'] ?? '';
$entity = $payload['payload']['payment']['entity'] ?? null;

if (!$entity) {
    // Not a payment event — acknowledge but do nothing
    echo json_encode(['status' => 'ignored', 'event' => $event]);
    exit;
}

$paymentId = $entity['id'] ?? '';
$orderId = $entity['order_id'] ?? '';
$amount = $entity['amount'] ?? 0;
$status = $entity['status'] ?? '';
$method = $entity['method'] ?? '';
$email = $entity['email'] ?? '';
$contact = $entity['contact'] ?? '';

// Log the webhook event
$logDir = __DIR__ . '/../../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'event' => $event,
    'payment_id' => $paymentId,
    'order_id' => $orderId,
    'amount' => $amount,
    'status' => $status,
    'method' => $method,
];

@file_put_contents($logDir . '/webhooks.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

$db = getDB();

switch ($event) {
    case 'payment.captured':
        // Payment was captured — mark as paid
        $updateStatus = 'paid';
        break;

    case 'payment.authorized':
        // Payment authorized but not yet captured — mark as authorized
        $updateStatus = 'paid'; // Treat authorized as paid for donations
        break;

    case 'payment.failed':
        $updateStatus = 'failed';
        break;

    case 'payment.dispute.created':
    case 'payment.dispute.won':
    case 'payment.dispute.lost':
        // Log disputes but don't change payment status
        error_log("Payment dispute event: {$event} for payment {$paymentId}");
        echo json_encode(['status' => 'logged', 'event' => $event]);
        exit;

    default:
        // Unknown event — acknowledge
        echo json_encode(['status' => 'ignored', 'event' => $event]);
        exit;
}

// Update the transaction in the database
try {
    $stmt = $db->prepare("
        UPDATE donation_transactions 
        SET payment_status = ?, 
            razorpay_payment_id = COALESCE(razorpay_payment_id, ?),
            updated_at = NOW()
        WHERE razorpay_order_id = ? AND payment_status = 'created'
    ");
    $stmt->execute([$updateStatus, $paymentId, $orderId]);
    $affected = $stmt->rowCount();

    if ($affected > 0) {
        error_log("Webhook: Updated order {$orderId} to status={$updateStatus} (payment={$paymentId})");
    } else {
        // Either already updated by client-side verify, or order not found
        // Check if it's already in the correct status
        $check = $db->prepare("SELECT payment_status FROM donation_transactions WHERE razorpay_order_id = ?");
        $check->execute([$orderId]);
        $current = $check->fetchColumn();

        if ($current === $updateStatus) {
            error_log("Webhook: Order {$orderId} already at status={$updateStatus} — no change needed");
        } else {
            error_log("Webhook: Order {$orderId} not found or in unexpected status={$current}");
        }
    }
} catch (PDOException $e) {
    error_log("Webhook DB error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    exit;
}

echo json_encode(['status' => 'ok', 'event' => $event]);
