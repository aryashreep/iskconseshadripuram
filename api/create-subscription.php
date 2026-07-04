<?php

/**
 * Create Razorpay Subscription
 *
 * AJAX endpoint called from donate.js for monthly donations.
 * Creates a Razorpay plan (if needed), a customer, and a subscription.
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

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/donation-helpers.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$amount = intval($input['amount'] ?? 0);
$causeSlug = preg_replace('/[^a-z0-9\-]/', '', $input['cause_slug'] ?? 'general-donation');
$causeId = intval($input['cause_id'] ?? 0);
$sevaId = isset($input['seva_id']) ? intval($input['seva_id']) : null;
$donationMode = in_array($input['donation_mode'] ?? '', ['one_time', 'monthly']) ? $input['donation_mode'] : 'monthly';
$formType = in_array($input['form_type'] ?? '', ['tiers', 'quantity', 'multi_item', 'cart']) ? $input['form_type'] : 'tiers';
$donorName = trim($input['donor_name'] ?? '');
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

if ($amount < 1000) {
    $amount = 1000;
}

if ($amount > 100000000) {
    $amount = 100000000;
}

$receipt = 'sub_' . $causeSlug . '_' . time() . '_' . rand(100, 999);

function sendRazorpayRequest(string $endpoint, array $payload): array
{
    $ch = curl_init('https://api.razorpay.com/v1/' . ltrim($endpoint, '/'));
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
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

    $decoded = json_decode($response, true);
    if ($httpCode < 200 || $httpCode >= 300 || !is_array($decoded)) {
        throw new RuntimeException($decoded['error']['description'] ?? 'Unknown Razorpay error');
    }

    return $decoded;
}

try {
    $db = getDB();

    $planStmt = $db->prepare(
        "SELECT razorpay_plan_id FROM donation_plans
         WHERE cause_id = ? AND amount = ? AND interval_unit = 'monthly' AND interval_count = 1 AND is_active = 1
         ORDER BY id DESC LIMIT 1"
    );
    $planStmt->execute([$causeId ?: 0, $amount / 100]);
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
                'cause_slug' => $causeSlug,
                'cause_id' => (string) $causeId,
                'seva_id' => (string) ($sevaId ?? ''),
                'donation_mode' => $donationMode,
                'form_type' => $formType,
                'source_type' => $sourceType,
                'source_slug' => $sourceSlug,
            ],
        ];

        $planResponse = sendRazorpayRequest('/plans', $planPayload);
        $planId = $planResponse['id'] ?? null;

        if ($planId) {
            $insertPlanStmt = $db->prepare(
                "INSERT INTO donation_plans (cause_id, amount, interval_unit, interval_count, razorpay_plan_id, is_active)
                 VALUES (?, ?, 'monthly', 1, ?, 1)"
            );
            $insertPlanStmt->execute([$causeId ?: 0, $amount / 100, $planId]);
        }
    }

    if (!$planId) {
        throw new RuntimeException('Could not create or reuse Razorpay plan');
    }

    $customerPayload = [
        'name' => $donorName ?: 'ISJM Donor',
        'email' => $donorEmail ?: 'donor@iskconvrindavan.org',
        'contact' => $donorPhone ?: '9999999999',
        'notes' => [
            'cause_slug' => $causeSlug,
            'source_type' => $sourceType,
            'source_slug' => $sourceSlug,
        ],
    ];
    $customerResponse = sendRazorpayRequest('/customers', $customerPayload);
    $customerId = $customerResponse['id'] ?? null;

    if (!$customerId) {
        throw new RuntimeException('Could not create Razorpay customer');
    }

    $subscriptionPayload = [
        'plan_id' => $planId,
        'customer_id' => $customerId,
        'total_count' => 12,
        'quantity' => 1,
        'customer_notify' => 1,
        'notes' => [
            'cause_slug' => $causeSlug,
            'cause_id' => (string) $causeId,
            'seva_id' => (string) ($sevaId ?? ''),
            'donation_mode' => $donationMode,
            'form_type' => $formType,
            'source_type' => $sourceType,
            'source_slug' => $sourceSlug,
            'donor_name' => $donorName,
            'donor_email' => $donorEmail,
            'donor_phone' => $donorPhone,
            'special_instructions' => $specialInstructions,
        ],
        'notify_info' => [
            'email' => $donorEmail ?: 'donor@iskconvrindavan.org',
            'sms' => $donorPhone ?: '9999999999',
        ],
    ];

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
         VALUES (?, ?, ?, ?, ?, ?, ?, 'monthly', 1, ?, ?, ?, ?, ?, ?, 'created', ?, ?)"
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
        $causeId ?: 0,
        $sevaId,
        $donorName,
        $donorEmail ?: null,
        $donorPhone ?: null,
        $panNumber ?: null,
        $amount / 100,
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

    echo json_encode([
        'subscription_id' => $subscriptionId,
        'plan_id' => $planId,
        'amount' => $amount,
        'currency' => CURRENCY,
        'receipt' => $receipt,
        'transaction_id' => (int) $db->lastInsertId(),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to create subscription',
        'details' => 'An error occurred',
    ]);
}
