<?php
/**
 * Sudamaseva Module — Verify Manual Installment Payment
 *
 * Verifies the Razorpay payment signature for a manual order payment,
 * creates a payment record in sudamaseva_payments, and increments
 * installments_paid on the subscription.
 *
 * POST /api/sudamaseva/verify-order
 */

header('Content-Type: application/json');
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = parse_url($requestOrigin, PHP_URL_HOST) ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($requestHost && $requestHost === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
}

require_once __DIR__ . '/../../../config.php';

use Isjm\Modules\Sudamaseva\SudamasevaRepository;
use Isjm\Modules\Sudamaseva\SudamasevaService;

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

$razorpayOrderId = $input['razorpay_order_id'] ?? '';
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';
$subscriptionId = (int) ($input['subscription_id'] ?? 0);
$installmentNumber = (int) ($input['installment_number'] ?? 0);
$amount = intval($input['amount'] ?? 0); // In paise

if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature) || $subscriptionId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payment verification data']);
    exit;
}

// Verify HMAC signature (Razorpay Orders use: order_id|payment_id)
$expectedSignature = hash_hmac(
    'sha256',
    $razorpayOrderId . '|' . $razorpayPaymentId,
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

try {
    $repo = new SudamasevaRepository();
    $service = new SudamasevaService($repo);

    // Find subscription
    $subscription = $repo->getSubscriptionById($subscriptionId);
    if (!$subscription) {
        http_response_code(404);
        echo json_encode(['error' => 'Subscription not found']);
        exit;
    }

    $donorId = (int) ($subscription['donor_id'] ?? 0);
    if ($donorId <= 0) {
        http_response_code(500);
        echo json_encode(['error' => 'Subscription has no linked donor']);
        exit;
    }

    // Check idempotency — don't process same payment twice
    $existingPayment = $repo->getPaymentByRazorpayId($razorpayPaymentId);
    if ($existingPayment) {
        echo json_encode([
            'success' => true,
            'already_processed' => true,
            'payment_id' => (int) $existingPayment['id'],
            'subscription_id' => $subscriptionId,
            'installment_number' => (int) ($existingPayment['installment_number'] ?? 0),
        ]);
        exit;
    }

    $amountInr = $amount > 0 ? (int) ($amount / 100) : (int) ($subscription['amount'] ?? 0);
    $donor = $repo->getDonorById($donorId);

    // Calculate billing month for reference
    $startDate = $subscription['start_date'] ?? $subscription['created_at'] ?? date('Y-m-d');
    $billingMonth = date('Y-m-d', strtotime('+' . ($installmentNumber - 1) . ' months', strtotime($startDate)));

    // Generate receipt number if eligible
    $paymentReceiptNo = null;
    if ($donor && $service->isEligibleFor80G($amountInr, $donor)) {
        $paymentReceiptNo = $service->generateReceiptNo();
    }

    // Create payment record
    $paymentId = $repo->createPayment([
        'subscription_id' => $subscriptionId,
        'donor_id' => $donorId,
        'amount' => $amountInr,
        'installment_number' => $installmentNumber,
        'razorpay_payment_id' => $razorpayPaymentId,
        'razorpay_order_id' => $razorpayOrderId,
        'razorpay_signature' => $razorpaySignature,
        'payment_status' => 'paid',
        'payment_date' => date('Y-m-d H:i:s'),
        'receipt_number' => $paymentReceiptNo,
        'notes' => 'Manual installment payment via order',
    ]);

    if (!$paymentId) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save payment record']);
        exit;
    }

    // Update the payment record with payment_source and billing_month
    try {
        $db = getDB();
        $updateStmt = $db->prepare("
            UPDATE sudamaseva_payments
            SET payment_source = 'manual_order', billing_month = ?
            WHERE id = ?
        ");
        $updateStmt->execute([
            $billingMonth ? date('Y-m-01', strtotime($billingMonth)) : null,
            $paymentId,
        ]);
    } catch (PDOException $e) {
        // Non-critical — log but don't fail
        error_log('Sudamaseva verify-order: could not set payment_source: ' . $e->getMessage());
    }

    // Increment installments_paid on subscription
    $repo->incrementInstallmentsPaid($subscriptionId);

    // Check if subscription is now complete
    $totalInstallments = (int) ($subscription['total_installments'] ?? 0);
    $updatedSub = $repo->getSubscriptionById($subscriptionId);
    $installmentsPaid = (int) ($updatedSub['installments_paid'] ?? 0);
    $subscriptionCompleted = false;

    if ($totalInstallments > 0 && $installmentsPaid >= $totalInstallments) {
        $repo->completeSubscription($subscriptionId);
        $subscriptionCompleted = true;
    }

    // Generate 80G receipt if eligible
    $receiptGenerated = false;
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

    // Log
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'payment_id' => $paymentId,
        'subscription_id' => $subscriptionId,
        'installment_number' => $installmentNumber,
        'razorpay_payment_id' => $razorpayPaymentId,
        'razorpay_order_id' => $razorpayOrderId,
        'amount' => $amountInr,
        'billing_month' => $billingMonth,
    ];
    @file_put_contents($logDir . '/sudamaseva_manual_payments.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'payment_id' => $paymentId,
        'subscription_id' => $subscriptionId,
        'installment_number' => $installmentNumber,
        'amount' => $amountInr,
        'receipt_number' => $paymentReceiptNo,
        'receipt_generated' => $receiptGenerated,
        'subscription_completed' => $subscriptionCompleted,
        'next_unpaid' => $service->getNextUnpaidInstallment($subscriptionId),
    ]);

} catch (Throwable $e) {
    error_log('Sudamaseva verify-order error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Payment verification failed. Please try again.',
        'details' => 'An error occurred while processing your payment.',
    ]);
}
