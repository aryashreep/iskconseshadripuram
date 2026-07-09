<?php
/**
 * Sudamaseva Module — Enroll (Manual Mode)
 *
 * Creates a new manual subscription + Razorpay Order for the first installment.
 * Used when the donor selects "Pay Monthly Manually" on the signup form.
 *
 * For recurring mode, the existing create-subscription.php is used (unchanged).
 *
 * POST /api/sudamaseva/enroll
 *   { donor_name, donor_phone, donor_email, pan_number, amount, total_installments,
 *     area, city, state }
 *
 * Response (success):
 *   { success: true, order_id: "order_...", subscription_id: N, donor_id: N,
 *     amount: 5100, currency: "INR", ... }
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

// ============================================================
// Extract & validate fields
// ============================================================
$donorName = trim($input['donor_name'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');
$donorEmail = trim($input['donor_email'] ?? '');
$panNumber = strtoupper(trim($input['pan_number'] ?? ''));
$amount = intval($input['amount'] ?? 0); // In paise
$totalInstallments = max(1, min(120, intval($input['total_installments'] ?? 12)));
$donorArea = trim($input['area'] ?? '');
$donorCity = trim($input['city'] ?? '');
$donorState = trim($input['state'] ?? '');

if (empty($donorName) || empty($donorPhone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Donor name and phone are required']);
    exit;
}

$amountInr = $amount / 100;
if ($amount < 5100) {
    $amount = 5100;
    $amountInr = 51;
}
if ($amount > 10000000) {
    $amount = 10000000;
    $amountInr = 100000;
}

try {
    $repo = new SudamasevaRepository();
    $service = new SudamasevaService($repo);

    // ============================================================
    // Find or create donor
    // ============================================================
    $donor = $repo->getDonorByPhone($donorPhone);

    if ($donor) {
        $donorId = (int) $donor['id'];
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
            throw new RuntimeException('Failed to create donor record');
        }
    }

    // ============================================================
    // Create subscription with collection_mode = 'manual'
    // ============================================================
    $subscriptionId = $repo->createSubscription([
        'donor_id' => $donorId,
        'amount' => $amountInr,
        'status' => 'active',
        'start_date' => date('Y-m-d H:i:s'),
        'total_installments' => $totalInstallments,
        'source' => 'new',
    ]);

    if (!$subscriptionId) {
        throw new RuntimeException('Failed to create subscription record');
    }

    // Set collection_mode to manual
    try {
        $db = getDB();
        $db->prepare("UPDATE sudamaseva_subscriptions SET collection_mode = 'manual' WHERE id = ?")
           ->execute([$subscriptionId]);
    } catch (PDOException $e) {
        // Non-critical
        error_log('Sudamaseva enroll: could not set collection_mode: ' . $e->getMessage());
    }

    // ============================================================
    // Create Razorpay Order for installment 1
    // ============================================================
    $receipt = 'sms_manual_' . time() . '_' . rand(100, 999);
    if (strlen($receipt) > 40) {
        $receipt = substr($receipt, 0, 40);
    }

    $orderData = [
        'amount' => $amount,
        'currency' => CURRENCY,
        'receipt' => $receipt,
        'payment_capture' => 1,
        'notes' => [
            'module' => 'sudamaseva',
            'type' => 'manual_installment',
            'subscription_id' => (string) $subscriptionId,
            'installment_number' => '1',
            'donor_id' => (string) $donorId,
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

    // ============================================================
    // Log
    // ============================================================
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => 'manual_enroll',
        'donor_id' => $donorId,
        'subscription_id' => $subscriptionId,
        'order_id' => $order['id'],
        'amount' => $amountInr,
        'total_installments' => $totalInstallments,
    ];
    @file_put_contents($logDir . '/sudamaseva_enroll.log', json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    // ============================================================
    // Return success
    // ============================================================
    echo json_encode([
        'success' => true,
        'mode' => 'manual',
        'order_id' => $order['id'],
        'subscription_id' => (string) $order['id'], // For Razorpay subscription checkout compat
        'db_subscription_id' => $subscriptionId,
        'donor_id' => $donorId,
        'amount' => $order['amount'],
        'currency' => $order['currency'],
        'amount_inr' => $amountInr,
        'receipt' => $receipt,
    ]);

} catch (Throwable $e) {
    error_log('Sudamaseva enroll error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create enrollment',
        'details' => 'An error occurred. Please try again.',
    ]);
}
