<?php

/**
 * Create Razorpay Subscription
 *
 * AJAX endpoint called from donate.js for monthly donations.
 * Creates a Razorpay plan (if needed), an optional customer, and a subscription.
 * Saves the subscription record in the database and returns the subscription ID.
 */

header('Content-Type: application/json');
// CORS: Only allow requests from our own domain
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = parse_url($requestOrigin, PHP_URL_HOST) ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($requestHost && $requestHost === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../includes/donation-helpers.php';

$input = json_decode(file_get_contents('php://input'), true);

if (class_exists('\\Isjm\\Helpers\\Security')) {
    \Isjm\Helpers\Security::checkHoneypot($input);
}

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$amount = intval($input['amount'] ?? 0);
$causeSlug = preg_replace('/[^a-z0-9\-]/', '', $input['cause_slug'] ?? 'general-donation');
$causeId = intval($input['cause_id'] ?? 0);
$sevaId = (isset($input['seva_id']) && intval($input['seva_id']) > 0) ? intval($input['seva_id']) : null;
$donationMode = in_array($input['donation_mode'] ?? '', ['one_time', 'monthly']) ? $input['donation_mode'] : 'monthly';
$formType = in_array($input['form_type'] ?? '', ['tiers', 'quantity', 'multi_item', 'cart', 'cart_qty']) ? $input['form_type'] : 'tiers';
$donorName = trim($input['donor_name'] ?? '');
if (empty($donorName)) {
    $donorName = 'ISJM Donor';
}
$donorEmail = trim($input['donor_email'] ?? '');
$donorPhone = trim($input['donor_phone'] ?? '');
$panNumber = strtoupper(trim($input['pan_number'] ?? ''));
$specialInstructions = trim($input['special_instructions'] ?? '');
$gotra = trim($input['gotra'] ?? '');
$relation = trim($input['relation'] ?? '');
$occasion = trim($input['occasion'] ?? '');
$sevaDate = trim($input['seva_date'] ?? '');

$sourceType = preg_replace('/[^a-z0-9\-]/', '', $input['source_type'] ?? 'direct');
$sourceSlug = preg_replace('/[^a-z0-9\-]/', '', $input['source_slug'] ?? '');
$sourceUrl = filter_var($input['source_url'] ?? $_SERVER['HTTP_REFERER'] ?? '', FILTER_SANITIZE_URL);

// Min ₹10 (1000 paise), max ₹1,00,00,000
if ($amount < 1000) {
    $amount = 1000;
}
if ($amount > 100000000) {
    $amount = 100000000;
}

$receipt = 'sub_' . $causeSlug . '_' . time() . '_' . rand(100, 999);

function sendRazorpayRequest(string $endpoint, array $payload = [], string $method = 'POST'): array
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
        throw new RuntimeException($decoded['error']['description'] ?? ('Razorpay error (HTTP ' . $httpCode . ')'));
    }

    return $decoded;
}

try {
    $db = getDB();

    // Ensure valid cause_id to satisfy foreign key constraints
    if ($causeId <= 0 && !empty($causeSlug)) {
        $causeRow = getDonationCauseBySlug($causeSlug);
        if ($causeRow && !empty($causeRow['id'])) {
            $causeId = (int) $causeRow['id'];
        }
    }
    if ($causeId <= 0) {
        $causeRow = getDonationCauseBySlug('general-donation');
        $causeId = $causeRow ? (int) $causeRow['id'] : 12;
    }

    $amountInr = $amount / 100;

    $planStmt = $db->prepare(
        "SELECT razorpay_plan_id FROM donation_plans
         WHERE cause_id = ? AND amount = ? AND interval_unit = 'monthly' AND interval_count = 1 AND is_active = 1
         ORDER BY id DESC LIMIT 1"
    );
    $planStmt->execute([$causeId, $amountInr]);
    $planRow = $planStmt->fetch();

    $planId = $planRow['razorpay_plan_id'] ?? null;

    if (!$planId) {
        $planPayload = [
            'period' => 'monthly',
            'interval' => 1,
            'item' => [
                'name' => ucfirst(str_replace('-', ' ', $causeSlug)) . ' Monthly Donation',
                'amount' => $amount,
                'currency' => CURRENCY,
                'description' => 'Monthly donation for ' . $causeSlug,
            ],
            'notes' => [
                'cause_slug' => mb_substr($causeSlug, 0, 30),
                'cause_id' => (string) $causeId,
                'seva_id' => (string) ($sevaId ?? ''),
                'donation_mode' => $donationMode,
                'form_type' => $formType,
                'source_type' => mb_substr($sourceType, 0, 30),
                'source_slug' => mb_substr($sourceSlug, 0, 30),
            ],
        ];

        $planResponse = sendRazorpayRequest('/plans', $planPayload);
        $planId = $planResponse['id'] ?? null;

        if ($planId) {
            $insertPlanStmt = $db->prepare(
                "INSERT INTO donation_plans (cause_id, amount, interval_unit, interval_count, razorpay_plan_id, is_active)
                 VALUES (?, ?, 'monthly', 1, ?, 1)"
            );
            $insertPlanStmt->execute([$causeId, $amountInr, $planId]);
        }
    }

    if (!$planId) {
        throw new RuntimeException('Could not create or reuse Razorpay plan');
    }

    // Attempt customer creation gracefully (customer_id is optional for subscriptions)
    $customerId = null;
    if (!empty($donorPhone) || !empty($donorEmail)) {
        try {
            $customerPayload = array_filter([
                'name' => $donorName,
                'email' => $donorEmail ?: null,
                'contact' => $donorPhone ?: null,
                'notes' => [
                    'cause_slug' => mb_substr($causeSlug, 0, 30),
                    'source_type' => mb_substr($sourceType, 0, 30),
                    'source_slug' => mb_substr($sourceSlug, 0, 30),
                ],
            ]);
            $customerResponse = sendRazorpayRequest('/customers', $customerPayload);
            $customerId = $customerResponse['id'] ?? null;
        } catch (Throwable $e) {
            // Customer already exists or failed — Razorpay Checkout links customer automatically
            error_log('create-subscription: customer creation skipped (' . $e->getMessage() . ')');
            $customerId = null;
        }
    }

    $subscriptionPayload = [
        'plan_id' => $planId,
        'total_count' => 12,
        'quantity' => 1,
        'customer_notify' => 1,
        'notes' => [
            'cause_slug' => mb_substr($causeSlug, 0, 30),
            'cause_id' => (string) $causeId,
            'seva_id' => (string) ($sevaId ?? ''),
            'donation_mode' => $donationMode,
            'form_type' => $formType,
            'source_type' => mb_substr($sourceType, 0, 30),
            'source_slug' => mb_substr($sourceSlug, 0, 30),
            'donor_name' => mb_substr($donorName, 0, 250),
            'donor_email' => mb_substr($donorEmail, 0, 250),
            'donor_phone' => mb_substr($donorPhone, 0, 50),
            'special_instructions' => mb_substr($specialInstructions, 0, 250),
        ],
    ];

    if ($customerId) {
        $subscriptionPayload['customer_id'] = $customerId;
    }

    $notifyInfo = [];
    if (!empty($donorEmail)) {
        $notifyInfo['email'] = $donorEmail;
    }
    if (!empty($donorPhone)) {
        $notifyInfo['sms'] = $donorPhone;
    }
    if (!empty($notifyInfo)) {
        $subscriptionPayload['notify_info'] = $notifyInfo;
    }

    $subscriptionResponse = sendRazorpayRequest('/subscriptions', $subscriptionPayload);
    $subscriptionId = $subscriptionResponse['id'] ?? null;

    if (!$subscriptionId) {
        throw new RuntimeException('Could not create Razorpay subscription');
    }

    $subscriptionStmt = $db->prepare(
        "INSERT INTO donation_subscriptions
         (cause_id, seva_id, donor_name, donor_email, donor_phone, pan_number, amount, currency, interval_unit, interval_count,
          source_type, source_slug, source_url, razorpay_plan_id, razorpay_subscription_id, razorpay_customer_id,
          subscription_status, notes, metadata_json)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'monthly', 1, ?, ?, ?, ?, ?, ?, 'created', ?, ?)"
    );

    $notes = empty($specialInstructions)
        ? "Cause: $causeSlug, Form: $formType"
        : "Cause: $causeSlug, Form: $formType | Selected: $specialInstructions" .
        (!empty($gotra) ? " | Gotra: $gotra" : "") .
        (!empty($relation) ? " | Relation: $relation" : "") .
        (!empty($occasion) ? " | Purpose: $occasion" : "") .
        (!empty($sevaDate) ? " | Seva Date: $sevaDate" : "");

    $metadata = [
        'cause_slug' => $causeSlug,
        'form_type' => $formType,
        'receipt' => $receipt,
        'special_instructions' => $specialInstructions,
        'gotra' => $gotra,
        'relation' => $relation,
        'occasion' => $occasion,
        'seva_date' => $sevaDate,
    ];

    $subscriptionStmt->execute([
        $causeId,
        $sevaId,
        $donorName,
        $donorEmail ?: null,
        $donorPhone ?: null,
        $panNumber ?: null,
        $amountInr,
        CURRENCY,
        $sourceType,
        $sourceSlug,
        $sourceUrl,
        $planId,
        $subscriptionId,
        $customerId,
        $notes,
        json_encode($metadata),
    ]);

    $dbSubId = (int) $db->lastInsertId();

    echo json_encode([
        'subscription_id' => $subscriptionId,
        'plan_id' => $planId,
        'amount' => $amount,
        'currency' => CURRENCY,
        'receipt' => $receipt,
        'transaction_id' => $dbSubId,
    ]);
} catch (Throwable $e) {
    error_log('create-subscription error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create subscription',
        'details' => RAZORPAY_TEST_MODE ? $e->getMessage() : 'An error occurred while creating subscription',
    ]);
}
