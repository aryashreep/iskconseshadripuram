<?php
/**
 * Sudamaseva Module — Razorpay Webhook Handler
 *
 * Handles server-to-server payment notifications from Razorpay.
 * This is the AUTHORITATIVE payment verifier for subscription installments.
 *
 * Events handled:
 *   - subscription.charged    → A new installment was charged successfully
 *   - payment.captured        → Payment was captured (supplementary event)
 *   - payment.failed          → Payment attempt failed
 *   - subscription.completed  → Subscription reached its total_count
 *   - subscription.cancelled  → Subscription was cancelled
 *
 * Security: Validates Razorpay webhook signature using HMAC-SHA256.
 *
 * Setup: Configure in Razorpay Dashboard → Settings → Webhooks
 *        URL: https://yourdomain.com/api/sudamaseva-webhook
 *        Secret: Same as RAZORPAY_KEY_SECRET
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../../config.php';

use Isjm\Modules\Sudamaseva\SudamasevaRepository;
use Isjm\Modules\Sudamaseva\SudamasevaService;

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
    error_log('Sudamaseva webhook: signature mismatch for event: ' . ($payload['event'] ?? 'unknown'));
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$event = $payload['event'] ?? '';
$repo = new SudamasevaRepository();
$service = new SudamasevaService($repo);

$logDir = __DIR__ . '/../../../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

// Log all webhook events
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'event' => $event,
];
@file_put_contents($logDir . '/sudamaseva_webhooks.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

try {
    switch ($event) {
        // ============================================================
        // subscription.charged — A new installment was charged
        // This is the main event for subscription payments
        // ============================================================
        case 'subscription.charged':
            $subEntity = $payload['payload']['subscription']['entity'] ?? null;
            $paymentEntity = $payload['payload']['payment']['entity'] ?? null;

            if (!$subEntity || !$paymentEntity) {
                error_log('Sudamaseva webhook: subscription.charged missing entity data');
                echo json_encode(['status' => 'error', 'message' => 'Missing entity data']);
                exit;
            }

            $razorpaySubId = $subEntity['id'] ?? '';
            $razorpayPaymentId = $paymentEntity['id'] ?? '';
            $amount = (int) ($paymentEntity['amount'] ?? 0); // In paise
            $amountInr = (int) ($amount / 100);
            $status = $paymentEntity['status'] ?? '';

            if (empty($razorpaySubId) || empty($razorpayPaymentId)) {
                echo json_encode(['status' => 'ignored', 'message' => 'Missing IDs']);
                exit;
            }

            // Find the subscription
            $subscription = $repo->getSubscriptionByRazorpayId($razorpaySubId);

            if (!$subscription) {
                error_log("Sudamaseva webhook: subscription {$razorpaySubId} not found in DB");
                echo json_encode(['status' => 'error', 'message' => 'Subscription not found']);
                exit;
            }

            $subscriptionId = (int) $subscription['id'];
            $donorId = (int) $subscription['donor_id'];

            // Check for duplicate (idempotency)
            $existingPayment = $repo->getPaymentByRazorpayId($razorpayPaymentId);
            if ($existingPayment) {
                echo json_encode([
                    'status' => 'already_processed',
                    'payment_id' => (int) $existingPayment['id'],
                ]);
                exit;
            }

            // Determine installment number (use service method for robustness)
            $installmentNumber = $service->getNextInstallmentNumber($subscriptionId);

            // Generate receipt
            $receiptNo = $service->generateReceiptNo();

            // Create payment record
            $paymentId = $repo->createPayment([
                'subscription_id' => $subscriptionId,
                'donor_id' => $donorId,
                'amount' => $amountInr,
                'installment_number' => $installmentNumber,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_order_id' => $paymentEntity['order_id'] ?? null,
                'payment_status' => ($status === 'captured' || $status === 'authorized') ? 'paid' : 'created',
                'payment_date' => date('Y-m-d H:i:s'),
                'receipt_number' => $receiptNo,
            ]);

            if (!$paymentId) {
                error_log("Sudamaseva webhook: failed to create payment for sub {$razorpaySubId}");
                echo json_encode(['status' => 'error', 'message' => 'Failed to create payment']);
                exit;
            }

            // Increment installment counter
            $repo->incrementInstallmentsPaid($subscriptionId);

            // Generate 80G receipt if eligible
            $donor = $repo->getDonorById($donorId);
            if ($donor && $service->isEligibleFor80G($amountInr, $donor)) {
                $repo->createReceipt([
                    'payment_id' => $paymentId,
                    'receipt_no' => $receiptNo,
                    'receipt_date' => date('Y-m-d H:i:s'),
                    'is_80g_eligible' => 1,
                    'receipt_data' => [
                        'donor_name' => $donor['donor_name'],
                        'donor_pan' => $donor['pan'],
                        'amount' => $amountInr,
                        'payment_date' => date('Y-m-d H:i:s'),
                        'razorpay_payment_id' => $razorpayPaymentId,
                        'fy' => $service->getFinancialYearLabel(),
                    ],
                ]);
            }

            // Check if subscription is complete
            $totalInstallments = (int) ($subscription['total_installments'] ?? 0);
            $installmentsPaid = (int) ($subscription['installments_paid'] ?? 0);
            $newPaidCount = $installmentsPaid + 1;
            if ($totalInstallments > 0 && $newPaidCount >= $totalInstallments) {
                $repo->completeSubscription($subscriptionId);
            }

            error_log("Sudamaseva webhook: processed subscription.charged for {$razorpaySubId}, payment {$razorpayPaymentId}, installment #{$installmentNumber}");
            echo json_encode(['status' => 'ok', 'event' => $event, 'payment_id' => $paymentId]);
            break;

        // ============================================================
        // payment.captured — Individual payment captured
        // ============================================================
        case 'payment.captured':
        case 'payment.authorized':
            $paymentEntity = $payload['payload']['payment']['entity'] ?? null;

            if (!$paymentEntity) {
                echo json_encode(['status' => 'ignored', 'message' => 'Missing payment entity']);
                exit;
            }

            $razorpayPaymentId = $paymentEntity['id'] ?? '';
            if (empty($razorpayPaymentId)) {
                echo json_encode(['status' => 'ignored']);
                exit;
            }

            // Update payment status if payment record already exists
            $existing = $repo->getPaymentByRazorpayId($razorpayPaymentId);
            if ($existing && ($existing['payment_status'] ?? '') !== 'paid') {
                $repo->updatePayment((int) $existing['id'], [
                    'payment_status' => 'paid',
                    'razorpay_order_id' => $paymentEntity['order_id'] ?? $existing['razorpay_order_id'],
                ]);
            }

            echo json_encode(['status' => 'ok', 'event' => $event]);
            break;

        // ============================================================
        // payment.failed — Payment attempt failed
        // ============================================================
        case 'payment.failed':
            $paymentEntity = $payload['payload']['payment']['entity'] ?? null;

            if (!$paymentEntity) {
                echo json_encode(['status' => 'ignored']);
                exit;
            }

            $razorpayPaymentId = $paymentEntity['id'] ?? '';
            $errorDescription = $paymentEntity['error_description'] ?? 'Unknown error';
            $errorCode = $paymentEntity['error_code'] ?? '';

            // Log the failure
            error_log("Sudamaseva webhook: payment.failed for {$razorpayPaymentId}: {$errorCode} - {$errorDescription}");

            // Mark existing payment record as failed if it exists
            if (!empty($razorpayPaymentId)) {
                $existing = $repo->getPaymentByRazorpayId($razorpayPaymentId);
                if ($existing) {
                    $repo->updatePayment((int) $existing['id'], [
                        'payment_status' => 'failed',
                        'notes' => ($existing['notes'] ?? '') . " | Failed: {$errorCode} - {$errorDescription}",
                    ]);
                }
            }

            echo json_encode(['status' => 'ok', 'event' => $event]);
            break;

        // ============================================================
        // subscription.completed — Subscription finished all installments
        // ============================================================
        case 'subscription.completed':
            $subEntity = $payload['payload']['subscription']['entity'] ?? null;
            if ($subEntity) {
                $razorpaySubId = $subEntity['id'] ?? '';
                if ($razorpaySubId) {
                    $subscription = $repo->getSubscriptionByRazorpayId($razorpaySubId);
                    if ($subscription) {
                        $repo->completeSubscription((int) $subscription['id']);
                        error_log("Sudamaseva webhook: subscription.completed for {$razorpaySubId}");
                    }
                }
            }
            echo json_encode(['status' => 'ok', 'event' => $event]);
            break;

        // ============================================================
        // subscription.cancelled — Subscription was cancelled
        // ============================================================
        case 'subscription.cancelled':
            $subEntity = $payload['payload']['subscription']['entity'] ?? null;
            if ($subEntity) {
                $razorpaySubId = $subEntity['id'] ?? '';
                if ($razorpaySubId) {
                    $subscription = $repo->getSubscriptionByRazorpayId($razorpaySubId);
                    if ($subscription) {
                        $repo->cancelSubscription((int) $subscription['id']);
                        error_log("Sudamaseva webhook: subscription.cancelled for {$razorpaySubId}");
                    }
                }
            }
            echo json_encode(['status' => 'ok', 'event' => $event]);
            break;

        // ============================================================
        // Default — Acknowledge unknown events
        // ============================================================
        default:
            echo json_encode(['status' => 'ignored', 'event' => $event]);
            break;
    }
} catch (Throwable $e) {
    error_log('Sudamaseva webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
