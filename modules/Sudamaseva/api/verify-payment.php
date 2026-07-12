<?php
/**
 * Sudamaseva Module — Verify Razorpay Payment
 *
 * AJAX endpoint called from the frontend after a successful subscription installment.
 * Flow:
 *   1. Verify HMAC signature (prevents tampering)
 *   2. Find the subscription by razorpay_subscription_id
 *   3. Create payment record in sudamaseva_payments
 *   4. Increment installments_paid on the subscription
 *   5. Mark subscription as completed if total_installments reached
 *   6. Generate a receipt if eligible for 80G
 *   7. Return success to frontend
 */

header('Content-Type: application/json');
// CORS: Only allow requests from our own domain
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = parse_url($requestOrigin, PHP_URL_HOST) ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($requestHost && $requestHost === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
}

require_once __DIR__ . '/../../../config.php';

use Isjm\Modules\Sudamaseva\SudamasevaRepository;
use Isjm\Modules\Sudamaseva\SudamasevaService;

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

// ============================================================
// Extract fields
// ============================================================
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpayOrderId = $input['razorpay_order_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';
$razorpaySubscriptionId = $input['razorpay_subscription_id'] ?? '';
$amount = intval($input['amount'] ?? 0); // In paise from Razorpay

if (empty($razorpayPaymentId) || empty($razorpaySignature) || empty($razorpaySubscriptionId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payment verification data']);
    exit;
}

// ============================================================
// Verify HMAC signature
// ============================================================
// Razorpay subscription payments use: razorpay_subscription_id|razorpay_payment_id
$expectedSignature = hash_hmac(
    'sha256',
    $razorpaySubscriptionId . '|' . $razorpayPaymentId,
    RAZORPAY_KEY_SECRET
);

if (!hash_equals($expectedSignature, $razorpaySignature)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Payment signature verification failed',
    ]);
    exit;
}

// ============================================================
// Process payment
// ============================================================
$repo = new SudamasevaRepository();
$service = new SudamasevaService($repo);

try {
    // Find the subscription
    $subscription = $repo->getSubscriptionByRazorpayId($razorpaySubscriptionId);

    if (!$subscription) {
        http_response_code(404);
        echo json_encode(['error' => 'Subscription not found']);
        exit;
    }

    $subscriptionId = (int) $subscription['id'];
    $donorId = (int) $subscription['donor_id'];
    $amountInr = $amount > 0 ? (int) ($amount / 100) : (int) ($subscription['amount'] ?? 0);
    $nextInstallment = $service->getNextInstallmentNumber($subscriptionId);

    // Check if payment with this razorpay_payment_id already exists (idempotency)
    $existingPayment = $repo->getPaymentByRazorpayId($razorpayPaymentId);
    if ($existingPayment) {
        // Payment was already processed — return existing data
        echo json_encode([
            'success' => true,
            'already_processed' => true,
            'payment_id' => (int) $existingPayment['id'],
            'subscription_id' => $subscriptionId,
            'installment_number' => (int) ($existingPayment['installment_number'] ?? 0),
        ]);
        exit;
    }

    // Create payment record (receipt_number set only if receipt will be generated)
    $donor = $repo->getDonorById($donorId);
    $receiptGenerated = false;

    // Only generate a receipt number if the donor is eligible for 80G
    $paymentReceiptNo = null;
    if ($donor && $service->isEligibleFor80G($amountInr, $donor)) {
        $paymentReceiptNo = $service->generateReceiptNo();
    }

    $paymentId = $repo->createPayment([
        'subscription_id' => $subscriptionId,
        'donor_id' => $donorId,
        'amount' => $amountInr,
        'installment_number' => $nextInstallment,
        'razorpay_payment_id' => $razorpayPaymentId,
        'razorpay_order_id' => $razorpayOrderId ?: null,
        'razorpay_signature' => $razorpaySignature,
        'payment_status' => 'paid',
        'payment_date' => date('Y-m-d H:i:s'),
        'receipt_number' => $paymentReceiptNo,
    ]);

    if (!$paymentId) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save payment record']);
        exit;
    }

    // Increment installments_paid on subscription
    $repo->incrementInstallmentsPaid($subscriptionId);

    // Check if subscription is now complete
    $totalInstallments = (int) ($subscription['total_installments'] ?? 0);
    $updatedSubscription = $repo->getSubscriptionById($subscriptionId);
    $installmentsPaid = (int) ($updatedSubscription['installments_paid'] ?? 0);

    $subscriptionCompleted = false;
    if ($totalInstallments > 0 && $installmentsPaid >= $totalInstallments) {
        $repo->completeSubscription($subscriptionId);
        $subscriptionCompleted = true;
    }

    // Generate 80G receipt if eligible
    if ($donor && $paymentReceiptNo && $service->isEligibleFor80G($amountInr, $donor)) {
        $receiptId = $repo->createReceipt([
            'payment_id' => $paymentId,
            'receipt_no' => $paymentReceiptNo,
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
        $receiptGenerated = $receiptId !== false;
    }

    // Log to file
    $logDir = __DIR__ . '/../../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'payment_id' => $paymentId,
        'subscription_id' => $subscriptionId,
        'donor_id' => $donorId,
        'razorpay_payment_id' => $razorpayPaymentId,
        'amount' => $amountInr,
        'installment' => $nextInstallment,
    ];
    @file_put_contents($logDir . '/sudamaseva_payments.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    // Return success
    echo json_encode([
        'success' => true,
        'payment_id' => $paymentId,
        'subscription_id' => $subscriptionId,
        'installment_number' => $nextInstallment,
        'amount' => $amountInr,
        'receipt_number' => $paymentReceiptNo,
        'receipt_generated' => $receiptGenerated,
        'subscription_completed' => $subscriptionCompleted,
        'installments_remaining' => $totalInstallments > 0 ? max(0, $totalInstallments - $installmentsPaid) : -1,
    ]);

} catch (Throwable $e) {
    error_log('Sudamaseva verify-payment error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Payment verification failed. Please try again.']);
}
