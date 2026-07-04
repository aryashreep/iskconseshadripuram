<?php
require_once __DIR__ . '/../config.php';

$payload = '{"event":"payment.captured","payload":{"payment":{"entity":{"id":"pay_test123","order_id":"order_test123","amount":50100,"status":"captured","method":"card","email":"test@test.com","contact":"9999999999"}}}}';

// Test 1: Valid signature
$sig = hash_hmac('sha256', $payload, RAZORPAY_KEY_SECRET);
echo "=== Test 1: Valid Signature ===\n";
$ch = curl_init('http://isjm.test:8080/api/webhook.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Razorpay-Signature: ' . $sig,
    ],
    CURLOPT_TIMEOUT => 10,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "  HTTP {$code}: {$resp}\n";

// Test 2: Invalid signature
echo "\n=== Test 2: Invalid Signature ===\n";
$ch2 = curl_init('http://isjm.test:8080/api/webhook.php');
curl_setopt_array($ch2, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Razorpay-Signature: invalid_sig_12345',
    ],
    CURLOPT_TIMEOUT => 10,
]);
$resp2 = curl_exec($ch2);
$code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);
echo "  HTTP {$code2}: {$resp2}\n";

// Test 3: No signature
echo "\n=== Test 3: No Signature ===\n";
$ch3 = curl_init('http://isjm.test:8080/api/webhook.php');
curl_setopt_array($ch3, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10,
]);
$resp3 = curl_exec($ch3);
$code3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
curl_close($ch3);
echo "  HTTP {$code3}: {$resp3}\n";

// Test 4: payment.failed event
echo "\n=== Test 4: Payment Failed Event ===\n";
$failPayload = '{"event":"payment.failed","payload":{"payment":{"entity":{"id":"pay_fail123","order_id":"order_test123","amount":50100,"status":"failed","method":"card","email":"test@test.com","contact":"9999999999"}}}}';
$failSig = hash_hmac('sha256', $failPayload, RAZORPAY_KEY_SECRET);
$ch4 = curl_init('http://isjm.test:8080/api/webhook.php');
curl_setopt_array($ch4, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $failPayload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Razorpay-Signature: ' . $failSig,
    ],
    CURLOPT_TIMEOUT => 10,
]);
$resp4 = curl_exec($ch4);
$code4 = curl_getinfo($ch4, CURLINFO_HTTP_CODE);
curl_close($ch4);
echo "  HTTP {$code4}: {$resp4}\n";

echo "\n=== All webhook tests complete ===\n";
