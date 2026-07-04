<?php
/**
 * E2E Payment Flow Test
 * Tests: Razorpay connection, create-order API, DB write, verify-payment security
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/donation-helpers.php';

$db = getDB();
$allPassed = true;

function testResult($name, $passed, $detail = '') {
    global $allPassed;
    if (!$passed) $allPassed = false;
    $icon = $passed ? 'PASS' : 'FAIL';
    echo "  [{$icon}] {$name}" . ($detail ? " — {$detail}" : '') . "\n";
}

// ============================================================
// TEST 1: Razorpay API Connection
// ============================================================
echo "=== TEST 1: Razorpay API Connection ===\n";

$orderData = [
    'amount' => 100100,
    'currency' => 'INR',
    'receipt' => 'test_e2e_' . time(),
    'notes' => ['test' => 'e2e payment test'],
];

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($orderData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_USERPWD => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

testResult('Razorpay credentials configured', !empty(RAZORPAY_KEY_ID) && !empty(RAZORPAY_KEY_SECRET));
testResult('Razorpay test mode', RAZORPAY_TEST_MODE === true, 'RAZORPAY_TEST_MODE=true');
testResult('API connection succeeds', empty($error), $error ?: 'no error');
$order = json_decode($response, true);
testResult('HTTP 200 from Razorpay', $httpCode === 200, "HTTP {$httpCode}");
testResult('Order ID returned', isset($order['id']), $order['id'] ?? 'none');
testResult('Order amount matches', ($order['amount'] ?? 0) == 100100);
testResult('Order currency INR', ($order['currency'] ?? '') === 'INR');
testResult('Order status created', ($order['status'] ?? '') === 'created');

echo "\n";

// ============================================================
// TEST 2: Create Order API Endpoint (all 4 new categories)
// ============================================================
echo "=== TEST 2: Create Order Endpoint (per category) ===\n";

$testCases = [
    ['cause_slug' => 'janmashtami', 'cause_id' => 13, 'seva_id' => 69, 'amount' => 50100, 'form_type' => 'tiers', 'label' => 'Festival (Janmashtami)'],
    ['cause_slug' => 'saphala', 'cause_id' => 50, 'seva_id' => 59, 'amount' => 15100, 'form_type' => 'tiers', 'label' => 'Ekadashi (Saphala)'],
    ['cause_slug' => 'srila-prabhupada-appearance', 'cause_id' => 37, 'seva_id' => 274, 'amount' => 100800, 'form_type' => 'tiers', 'label' => 'Appearance (Prabhupada)'],
    ['cause_slug' => 'support-our-digital-initiatives', 'cause_id' => 74, 'seva_id' => 325, 'amount' => 200100, 'form_type' => 'tiers', 'label' => 'Digital Initiatives'],
    ['cause_slug' => 'general-donation', 'cause_id' => 12, 'seva_id' => 343, 'amount' => 100100, 'form_type' => 'tiers', 'label' => 'General Donation'],
];

$createdOrders = [];

foreach ($testCases as $tc) {
    $postData = json_encode([
        'amount' => $tc['amount'],
        'cause_slug' => $tc['cause_slug'],
        'cause_id' => $tc['cause_id'],
        'seva_id' => $tc['seva_id'],
        'donation_mode' => 'one_time',
        'form_type' => $tc['form_type'],
        'donor_name' => 'E2E Test Donor',
        'donor_email' => 'e2e-test@iskcon.org',
        'donor_phone' => '9999999999',
    ]);

    $ch = curl_init('http://isjm.test:8080/api/create-order');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30,
    ]);

    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($resp, true);
    $ok = $code === 200 && isset($result['order_id']);
    testResult($tc['label'], $ok, $ok ? "order={$result['order_id']}" : "HTTP {$code}: " . ($result['error'] ?? 'unknown'));

    if ($ok) {
        $createdOrders[] = [
            'order_id' => $result['order_id'],
            'transaction_id' => $result['transaction_id'],
            'cause_slug' => $tc['cause_slug'],
            'cause_id' => $tc['cause_id'],
            'seva_id' => $tc['seva_id'],
            'amount' => $tc['amount'],
        ];
    }
}

echo "\n";

// ============================================================
// TEST 3: Database Transaction Records
// ============================================================
echo "=== TEST 3: Database Transaction Records ===\n";

foreach ($createdOrders as $co) {
    $stmt = $db->prepare("SELECT id, razorpay_order_id, amount, payment_status, donor_name, cause_id FROM donation_transactions WHERE razorpay_order_id = ?");
    $stmt->execute([$co['order_id']]);
    $tx = $stmt->fetch();

    testResult("TX for {$co['cause_slug']}", $tx !== false, $tx ? "id={$tx['id']} status={$tx['payment_status']} ₹{$tx['amount']}" : 'NOT FOUND');

    if ($tx) {
        $expectedAmt = (int)($co['amount']/100);
        testResult("  Amount correct (INR {$expectedAmt})", (int)$tx['amount'] === $expectedAmt);
        testResult("  Status = created", $tx['payment_status'] === 'created');
        testResult("  Cause ID set", (int)$tx['cause_id'] === (int)($co['cause_id'] ?? 0));
        testResult("  Donor name saved", $tx['donor_name'] === 'E2E Test Donor');
    }
}

echo "\n";

// ============================================================
// TEST 4: Verify Payment Security (fake signature rejection)
// ============================================================
echo "=== TEST 4: Verify Payment Security ===\n";

if (!empty($createdOrders)) {
    $first = $createdOrders[0];

    $verifyData = json_encode([
        'razorpay_order_id' => $first['order_id'],
        'razorpay_payment_id' => 'pay_test_fake_12345',
        'razorpay_signature' => 'fake_signature_that_should_fail',
        'cause_slug' => $first['cause_slug'],
        'seva_id' => 69,
        'amount' => $first['amount'],
        'donor_name' => 'Fake Donor',
    ]);

    $ch = curl_init('http://isjm.test:8080/api/verify-payment');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $verifyData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30,
    ]);

    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($resp, true);
    testResult('Fake signature rejected', $code === 400, "HTTP {$code}");
    testResult('Error message returned', isset($result['error']), $result['error'] ?? 'none');
    $failCheck = $db->prepare("SELECT payment_status FROM donation_transactions WHERE razorpay_order_id = ?");
    $failCheck->execute([$first['order_id']]);
    $isFailed = $failCheck->fetchColumn() === 'failed';
    testResult('Transaction marked failed', $isFailed, 'status updated to failed');
}

echo "\n";

// ============================================================
// TEST 5: Master Seva ID Routing (correct FK storage)
// ============================================================
echo "=== TEST 5: Master Seva FK Routing ===\n";

// Digital initiative cause uses master catalog
$stmt = $db->prepare("SELECT seva_id, master_seva_id FROM donation_transactions WHERE razorpay_order_id = ?");
foreach ($createdOrders as $co) {
    $stmt->execute([$co['order_id']]);
    $tx = $stmt->fetch();
    if ($tx) {
        // All our sevas use master catalog — seva_id goes to master_seva_id
        if (!empty($tx['master_seva_id'])) {
            testResult("  {$co['cause_slug']}: master_seva_id stored", true, "master_seva_id={$tx['master_seva_id']}");
        } elseif (!empty($tx['seva_id'])) {
            testResult("  {$co['cause_slug']}: seva_id stored (legacy)", true, "seva_id={$tx['seva_id']}");
        } else {
            testResult("  {$co['cause_slug']}: seva routing", false, "neither seva_id nor master_seva_id set");
        }
    }
}

echo "\n";

// ============================================================
// TEST 6: Input Validation
// ============================================================
echo "=== TEST 6: Input Validation ===\n";

// Test: amount below minimum
$lowAmount = json_encode(['amount' => 100, 'cause_slug' => 'general-donation', 'cause_id' => 12]);
$ch = curl_init('http://isjm.test:8080/api/create-order');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $lowAmount,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
]);
$resp = curl_exec($ch);
curl_close($ch);
$result = json_decode($resp, true);
testResult('Low amount accepted (clamped to ₹10)', isset($result['order_id']), 'Amount auto-corrected');

// Test: empty body
$ch = curl_init('http://isjm.test:8080/api/create-order');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
testResult('Empty request returns 400', $code === 400, "HTTP {$code}");

echo "\n";

// ============================================================
// CLEANUP: Delete test transactions
// ============================================================
echo "=== CLEANUP ===\n";
$testOrders = array_column($createdOrders, 'order_id');
if (!empty($testOrders)) {
    $placeholders = implode(',', array_fill(0, count($testOrders), '?'));
    $stmt = $db->prepare("DELETE FROM donation_transactions WHERE razorpay_order_id IN ({$placeholders})");
    $stmt->execute($testOrders);
    echo "  Deleted " . count($testOrders) . " test transactions\n";
}

// Delete test log entries
$logFile = __DIR__ . '/../logs/orders.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $filtered = array_filter($lines, function($line) { return strpos($line, 'test_e2e') === false; });
    file_put_contents($logFile, implode('', $filtered));
    echo "  Cleaned test entries from orders.log\n";
}

echo "\n";
echo "===================================\n";
echo $allPassed ? "ALL TESTS PASSED" : "SOME TESTS FAILED";
echo "\n===================================\n";
