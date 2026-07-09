<?php
/**
 * Sudamaseva Module — Record Offline Payment (Admin)
 *
 * Creates a paid payment record for a subscription installment,
 * typically used when a donor pays via cash, cheque, or bank transfer.
 *
 * POST /api/sudamaseva/record-offline-payment
 *   { subscription_id, installment_number, amount, payment_method, reference_no, notes }
 *
 * Response (success):
 *   { success: true, payment_id: N }
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

// Admin-only access
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.edit');

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

$subscriptionId = (int) ($input['subscription_id'] ?? 0);
$installmentNumber = (int) ($input['installment_number'] ?? 0);
$amountInr = (int) ($input['amount'] ?? 0);
$paymentMethod = trim($input['payment_method'] ?? 'cash');
$referenceNo = trim($input['reference_no'] ?? '');
$notes = trim($input['notes'] ?? '');

// Validate
if ($subscriptionId <= 0 || $installmentNumber <= 0 || $amountInr <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid subscription_id, installment_number, or amount']);
    exit;
}

$validMethods = ['cash', 'cheque', 'bank_transfer', 'other'];
if (!in_array($paymentMethod, $validMethods, true)) {
    $paymentMethod = 'cash';
}

try {
    $repo = new SudamasevaRepository();
    $service = new SudamasevaService($repo);

    // Fetch subscription
    $subscription = $repo->getSubscriptionById($subscriptionId);
    if (!$subscription) {
        http_response_code(404);
        echo json_encode(['error' => 'Subscription not found']);
        exit;
    }

    if ($subscription['status'] !== 'active') {
        http_response_code(400);
        echo json_encode(['error' => 'Subscription is not active']);
        exit;
    }

    $donorId = (int) $subscription['donor_id'];

    // Check if this installment is already paid
    $existingPayments = $repo->getPaidInstallmentNumbers($subscriptionId);
    if (in_array($installmentNumber, $existingPayments)) {
        http_response_code(409);
        echo json_encode(['error' => "Installment #{$installmentNumber} is already paid"]);
        exit;
    }

    // Generate receipt
    $receiptNo = $service->generateReceiptNo();

    // Create payment record
    $paymentId = $repo->createPayment([
        'subscription_id' => $subscriptionId,
        'donor_id' => $donorId,
        'amount' => $amountInr,
        'installment_number' => $installmentNumber,
        'payment_status' => 'paid',
        'payment_date' => date('Y-m-d H:i:s'),
        'receipt_number' => $receiptNo,
        'payment_source' => 'admin_manual',
        'notes' => 'Offline payment: ' . ucfirst(str_replace('_', ' ', $paymentMethod))
            . ($referenceNo ? " (Ref: {$referenceNo})" : '')
            . ($notes ? " — {$notes}" : ''),
    ]);

    if (!$paymentId) {
        throw new RuntimeException('Failed to create payment record');
    }

    // Increment the installments_paid counter
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
                'payment_method' => $paymentMethod,
                'reference_no' => $referenceNo,
                'fy' => $service->getFinancialYearLabel(),
            ],
        ]);
    }

    // Check if subscription is complete
    $totalInstallments = (int) ($subscription['total_installments'] ?? 0);
    $newPaidCount = (int) ($subscription['installments_paid'] ?? 0) + 1;
    if ($totalInstallments > 0 && $newPaidCount >= $totalInstallments) {
        $repo->completeSubscription($subscriptionId);
    }

    // Log
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => 'offline_payment_recorded',
        'admin_user' => $_SESSION['admin_username'] ?? 'unknown',
        'subscription_id' => $subscriptionId,
        'installment_number' => $installmentNumber,
        'amount' => $amountInr,
        'payment_method' => $paymentMethod,
        'reference_no' => $referenceNo,
    ];
    @file_put_contents($logDir . '/sudamaseva_offline_payments.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'payment_id' => $paymentId,
        'receipt_no' => $receiptNo,
        'installment_number' => $installmentNumber,
        'amount' => $amountInr,
        'subscription_id' => $subscriptionId,
        'donor_id' => $donorId,
        'message' => "Installment #{$installmentNumber} of ₹{$amountInr} recorded as paid.",
    ]);

} catch (Throwable $e) {
    error_log('Sudamaseva record-offline-payment error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to record payment',
        'details' => 'An error occurred. Please try again.',
    ]);
}
