<?php
/**
 * Sudamaseva Module — Create Razorpay Order for Manual Installment
 *
 * Creates a one-time Razorpay Order for a donor to pay a single installment
 * on a manual (non-recurring) subscription.
 *
 * Flow:
 *   1. Validate the subscription exists, is active, and collection_mode = 'manual' or 'hybrid'
 *   2. Validate the installment number is the next unpaid one
 *   3. Create a Razorpay Order for the subscription amount
 *   4. Return order_id + amount to frontend
 *
 * POST /api/sudamaseva/create-order
 *   { subscription_id: N, installment_number: N }
 *
 * Response:
 *   { order_id: "order_...", amount: 5000, currency: "INR", ... }
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

$subscriptionId = (int) ($input['subscription_id'] ?? 0);
$installmentNumber = (int) ($input['installment_number'] ?? 0);
$donorName = trim($input['donor_name'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');

if ($subscriptionId <= 0 || $installmentNumber <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid subscription or installment number']);
    exit;
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

    // Validate subscription is active and manual
    if ($subscription['status'] !== 'active') {
        http_response_code(400);
        echo json_encode(['error' => 'Subscription is not active']);
        exit;
    }

    $collectionMode = $subscription['collection_mode'] ?? 'recurring';
    // Allow Pay Now for both manual (online) and hybrid (online or offline) subscriptions
    if (!in_array($collectionMode, ['manual', 'hybrid'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'This subscription does not support manual online payments.']);
        exit;
    }

    // Validate installment
    if (!$service->canPayInstallment($subscription, $installmentNumber)) {
        http_response_code(400);
        echo json_encode(['error' => 'This installment cannot be paid right now.']);
        exit;
    }

    $amountInr = (int) ($subscription['amount'] ?? 0);
    if ($amountInr <= 0) {
        http_response_code(500);
        echo json_encode(['error' => 'Invalid subscription amount']);
        exit;
    }

    $amountPaise = $amountInr * 100;
    $donorId = (int) $subscription['donor_id'];

    // Generate receipt
    $receipt = 'sms_manual_' . time() . '_' . rand(100, 999);
    if (strlen($receipt) > 40) {
        $receipt = substr($receipt, 0, 40);
    }

    // Create order via Razorpay API
    $orderData = [
        'amount' => $amountPaise,
        'currency' => CURRENCY,
        'receipt' => $receipt,
        'payment_capture' => 1,
        'notes' => [
            'module' => 'sudamaseva',
            'type' => 'manual_installment',
            'subscription_id' => (string) $subscriptionId,
            'installment_number' => (string) $installmentNumber,
            'donor_id' => (string) $donorId,
            'donor_name' => $donorName,
            'donor_email' => $donorEmail,
            'donor_phone' => $donorPhone,
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
        throw new RuntimeException('Failed to connect to payment gateway: ' . $error);
    }

    $order = json_decode($response, true);
    if ($httpCode !== 200 || !isset($order['id'])) {
        throw new RuntimeException(
            $order['error']['description'] ?? 'Razorpay API error (HTTP ' . $httpCode . ')'
        );
    }

    // Log
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'subscription_id' => $subscriptionId,
        'installment_number' => $installmentNumber,
        'donor_id' => $donorId,
        'order_id' => $order['id'],
        'amount' => $amountInr,
    ];
    @file_put_contents($logDir . '/sudamaseva_manual_orders.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo json_encode([
        'success' => true,
        'order_id' => $order['id'],
        'amount' => $order['amount'],
        'currency' => $order['currency'],
        'amount_inr' => $amountInr,
        'receipt' => $receipt,
        'subscription_id' => $subscriptionId,
        'installment_number' => $installmentNumber,
    ]);

} catch (Throwable $e) {
    error_log('Sudamaseva create-order error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create payment order',
        'details' => 'An error occurred. Please try again.',
    ]);
}
