<?php
/**
 * Sudamaseva Module — Create Razorpay Subscription
 *
 * AJAX endpoint called from the frontend to create a new recurring subscription.
 * Flow:
 *   1. Find or create donor (by phone)
 *   2. Create or reuse Razorpay plan
 *   3. Create Razorpay subscription linked to the plan
 *   4. Save subscription record in sudamaseva_subscriptions
 *   5. Return subscription_id and short_url to frontend
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
// Extract & validate fields
// ============================================================
$donorName = trim($input['donor_name'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$panNumber = strtoupper(trim($input['pan_number'] ?? ''));
$amount = intval($input['amount'] ?? 0); // Amount in paise (as Razorpay expects)
$totalInstallments = max(1, intval($input['total_installments'] ?? 12)); // Minimum 1; Razorpay requires positive total_count
$totalInstallments = min($totalInstallments, 120); // Maximum 120 (10 years monthly)
$donorArea = trim($input['area'] ?? '');
$donorCity = trim($input['city'] ?? '');
$donorState = trim($input['state'] ?? '');

// Validate required fields
if (empty($donorName) || empty($donorPhone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Donor name and phone are required']);
    exit;
}

// Validate amount (minimum ₹51 = 5100 paise)
$amountInr = $amount / 100;
if ($amount < 5100) {
    $amount = 5100;
    $amountInr = 51;
}

// Clamp maximum (₹1,00,000 = 10000000 paise)
if ($amount > 10000000) {
    $amount = 10000000;
    $amountInr = 100000;
}

// ============================================================
// Find or create donor
// ============================================================
$repo = new SudamasevaRepository();
$service = new SudamasevaService($repo);

$donor = $repo->getDonorByPhone($donorPhone);

if ($donor) {
    $donorId = (int) $donor['id'];
    // Update donor info if new data provided
    $updateData = [];
    if (!empty($donorName) && $donor['donor_name'] !== $donorName) {
        $updateData['donor_name'] = $donorName;
    }
    if (!empty($donorEmail) && empty($donor['email'])) {
        $updateData['email'] = $donorEmail;
    }
    if (!empty($panNumber) && empty($donor['pan'])) {
        $updateData['pan'] = $panNumber;
    }
    if (!empty($updateData)) {
        $repo->updateDonor($donorId, $updateData);
    }
} else {
    $donorId = $repo->createDonor([
        'donor_name' => $donorName,
        'phone' => $donorPhone,
        'email' => $donorEmail ?: null,
        'pan' => $panNumber ?: null,
        'area' => $donorArea ?: null,
        'city' => $donorCity ?: null,
        'state' => $donorState ?: null,
        'source' => 'sudamaseva',
        'status' => 'active',
    ]);

    if (!$donorId) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create donor record']);
        exit;
    }
}

// ============================================================
// Razorpay API helper
// ============================================================
function sendRazorpayRequest(string $endpoint, array $payload, string $method = 'POST'): array
{
    $ch = curl_init('https://api.razorpay.com/v1/' . ltrim($endpoint, '/'));
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => RAZORPAY_TEST_MODE ? false : true,
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    } elseif ($method === 'GET') {
        $options[CURLOPT_HTTPGET] = true;
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new RuntimeException('Failed to connect to payment gateway: ' . $error);
    }

    $decoded = json_decode($response, true);
    if ($httpCode < 200 || $httpCode >= 300 || !is_array($decoded)) {
        throw new RuntimeException(
            $decoded['error']['description'] ?? 'Razorpay API error (HTTP ' . $httpCode . ')'
        );
    }

    return $decoded;
}

// ============================================================
// Create or reuse Razorpay plan
// ============================================================
$planId = null;
$receipt = 'sms_' . time() . '_' . rand(100, 999);

try {
    // Check if we already have a plan for this exact amount
    $db = getDB();
    $planStmt = $db->prepare("
        SELECT razorpay_plan_id FROM sudamaseva_subscriptions
        WHERE amount = ? AND razorpay_plan_id IS NOT NULL
        ORDER BY id DESC LIMIT 1
    ");
    $planStmt->execute([$amountInr]);
    $existingPlan = $planStmt->fetchColumn();

    if ($existingPlan) {
        $planId = $existingPlan;
    } else {
        // Create a new plan
        $planPayload = [
            'period' => 'monthly',
            'interval' => 1,
            'item' => [
                'name' => 'Sudamaseva Monthly Donation',
                'amount' => $amount,
                'currency' => CURRENCY,
                'description' => 'Monthly subscription donation of ₹' . number_format($amountInr),
            ],
        ];

        $planResponse = sendRazorpayRequest('/plans', $planPayload);
        $planId = $planResponse['id'] ?? null;
    }

    if (!$planId) {
        throw new RuntimeException('Could not create or reuse Razorpay plan');
    }

    // ============================================================
    // Create Razorpay subscription
    // ============================================================
    $subscriptionPayload = [
        'plan_id' => $planId,
        'total_count' => $totalInstallments,
        'quantity' => 1,
        'customer_notify' => 1,
        'notes' => [
            'module' => 'sudamaseva',
            'donor_id' => (string) $donorId,
            'donor_name' => $donorName,
            'donor_phone' => $donorPhone,
            'donor_email' => $donorEmail,
        ],
        'notify_info' => [
            'email' => $donorEmail ?: '',
        ],
    ];

    if (!empty($donorEmail)) {
        $subscriptionPayload['notify_info']['email'] = $donorEmail;
    }

    $subscriptionResponse = sendRazorpayRequest('/subscriptions', $subscriptionPayload);
    $razorpaySubscriptionId = $subscriptionResponse['id'] ?? null;

    if (!$razorpaySubscriptionId) {
        throw new RuntimeException('Could not create Razorpay subscription');
    }

    // ============================================================
    // Save subscription to database
    // ============================================================
    $subscriptionId = $repo->createSubscription([
        'donor_id' => $donorId,
        'amount' => $amountInr,
        'razorpay_subscription_id' => $razorpaySubscriptionId,
        'razorpay_plan_id' => $planId,
        'status' => 'active',
        'start_date' => date('Y-m-d H:i:s'),
        'total_installments' => $totalInstallments,
        'source' => 'new',
    ]);

    if (!$subscriptionId) {
        // Subscription created in Razorpay but DB save failed — try to cancel in Razorpay
        try {
            sendRazorpayRequest('/subscriptions/' . $razorpaySubscriptionId . '/cancel', [], 'POST');
        } catch (Exception $ignore) {
            // Best effort
        }
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save subscription record']);
        exit;
    }

    // ============================================================
    // Log to file
    // ============================================================
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'subscription_id' => $subscriptionId,
        'razorpay_subscription_id' => $razorpaySubscriptionId,
        'donor_id' => $donorId,
        'donor_name' => $donorName,
        'amount' => $amountInr,
        'total_installments' => $totalInstallments,
    ];
    @file_put_contents($logDir . '/sudamaseva_subscriptions.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    // ============================================================
    // Return success
    // ============================================================
    echo json_encode([
        'success' => true,
        'subscription_id' => $razorpaySubscriptionId,
        'db_subscription_id' => $subscriptionId,
        'plan_id' => $planId,
        'amount' => $amount,
        'currency' => CURRENCY,
        'receipt' => $receipt,
        'donor_id' => $donorId,
        'short_url' => $subscriptionResponse['short_url'] ?? null,
    ]);

} catch (Throwable $e) {
    error_log('Sudamaseva create-subscription error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create subscription',
        'details' => 'An error occurred while processing your request. Please try again.',
    ]);
}
