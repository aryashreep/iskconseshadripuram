<?php
/**
 * Import Panihati Yatra Registrations from Razorpay Payment Link CSV Exports
 * Correctly groups items by transaction (Payment ID) to map Adults and Kids.
 * 
 * Run: php database/migrations/import_panihati_csv.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';
require_once __DIR__ . '/../../includes/panihati-helpers.php';

echo "=== Grouping & Importing Panihati Yatra CSV Data ===\n\n";

$db = getDB();

// Clear the registrations table before re-importing to prevent corrupt data
try {
    $db->exec("TRUNCATE TABLE `panihati_yatra_registrations`");
    echo "  [OK] Cleared table 'panihati_yatra_registrations' for clean import.\n";
} catch (Exception $e) {
    echo "  [ERROR] Failed to clear table: " . $e->getMessage() . "\n";
    exit(1);
}

$files = [
    [
        'path' => 'payment_links - 13 Apr 25.csv',
        'has_pickup' => true
    ],
    [
        'path' => 'payment_links - 04 May 25.csv',
        'has_pickup' => false
    ]
];

$transactions = [];

foreach ($files as $fInfo) {
    $filePath = $fInfo['path'];
    if (!file_exists($filePath)) {
        echo "  [WARNING] File not found: {$filePath}\n";
        continue;
    }
    
    echo "Reading file: {$filePath}...\n";
    
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle);
        $rowNum = 1;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rowNum++;
            if (empty($data) || count(array_filter($data)) === 0) {
                continue;
            }
            
            $paymentId = trim($data[11] ?? ''); // Column 12 (0-indexed 11)
            if (empty($paymentId) || strpos($paymentId, 'pay_') !== 0) {
                continue;
            }
            
            if (!isset($transactions[$paymentId])) {
                $transactions[$paymentId] = [];
            }
            
            // Store context information
            $data['has_pickup'] = $fInfo['has_pickup'];
            $transactions[$paymentId][] = $data;
        }
        fclose($handle);
    }
}

echo "Found " . count($transactions) . " unique transactions. Importing...\n";

$importedCount = 0;

foreach ($transactions as $paymentId => $rows) {
    $name = '';
    $phone = '';
    $email = '';
    $bhaktiSadan = '';
    $pickup = '';
    $amount = 0.0;
    $paymentStatus = '';
    $orderId = '';
    $createdAt = '';
    $mode = 'own_vehicle';
    
    $adultsCount = 0;
    $kidsCount = 0;
    
    // Fetch pricing early — use bus rate for in-loop kid calculation
    // (both modes share the same kid rate in practice; mode is determined during the loop)
    $pricingYear = (int)date('Y');
    $yrPricing = getPanihatiPricing($pricingYear);
    
    foreach ($rows as $row) {
        // Collect common fields
        if (empty($name)) $name = trim($row[12] ?? '');
        if (empty($phone)) $phone = trim($row[13] ?? '');
        if (empty($email)) $email = trim($row[14] ?? '');
        if (empty($bhaktiSadan)) $bhaktiSadan = trim($row[15] ?? '');
        
        if ($row['has_pickup'] && empty($pickup)) {
            $pickup = trim($row[16] ?? '');
        }
        
        $rowTotal = floatval($row[8] ?? 0);
        if ($rowTotal > $amount) {
            $amount = $rowTotal;
        }
        
        if (empty($paymentStatus)) $paymentStatus = strtolower(trim($row[10] ?? ''));
        if (empty($orderId)) $orderId = trim($row[3] ?? '');
        
        if (empty($createdAt)) {
            $dateStr = trim($row[2] ?? '');
            if (!empty($dateStr)) {
                $dt = DateTime::createFromFormat('d/m/Y H:i:s', $dateStr);
                if ($dt) {
                    $createdAt = $dt->format('Y-m-d H:i:s');
                } else {
                    $dt2 = DateTime::createFromFormat('d/m/Y H:i', $dateStr);
                    if ($dt2) {
                        $createdAt = $dt2->format('Y-m-d H:i:s');
                    }
                }
            }
        }
        
        // Parse items details
        $itemName = trim($row[4] ?? '');
        $qty = intval($row[6] ?? 1);
        $itemPaymentAmt = floatval($row[7] ?? 0);
        
        if (strpos(strtolower($itemName), '5 to 10 years') !== false || strpos(strtolower($itemName), 'child') !== false) {
            // Kids item — use current pricing year from createdAt if available
            if (!empty($createdAt)) {
                $pricingYear = (int)date('Y', strtotime($createdAt));
                $yrPricing = getPanihatiPricing($pricingYear);
            }
            $calcKids = intval($itemPaymentAmt / $yrPricing['bus_kid_price']);
            $kidsCount += max($qty, $calcKids);
        } else {
            // Adults item
            $adultsCount += $qty;
        }
        
        // Mode detection
        $itemLower = strtolower($itemName);
        if (strpos($itemLower, 'bus') !== false) {
            $mode = 'bus';
        }
    }
    
    // Compute final rates based on determined mode and latest pricing year
    $yrPricing = getPanihatiPricing($pricingYear);
    $adultRate = ($mode === 'bus') ? $yrPricing['bus_adult_price'] : $yrPricing['vehicle_adult_price'];
    $kidRate = ($mode === 'bus') ? $yrPricing['bus_kid_price'] : $yrPricing['vehicle_kid_price'];
    
    // Set fallback defaults
    if (empty($pickup)) {
        $pickup = 'Own Vehicle';
    }
    if (empty($createdAt)) {
        $createdAt = date('Y-m-d H:i:s');
    }
    if ($amount <= 0) {
        // Fallback to calculation using dynamic pricing
        $amount = ($adultsCount * $adultRate) + ($kidsCount * $kidRate);
    }
    
    $dbStatus = 'created';
    if ($paymentStatus === 'captured' || $paymentStatus === 'refunded' || $paymentStatus === 'paid') {
        $dbStatus = 'paid';
    } elseif ($paymentStatus === 'failed') {
        $dbStatus = 'failed';
    }
    
    try {
        // Dynamic options caching
        if (!empty($bhaktiSadan)) {
            $db->prepare("INSERT IGNORE INTO panihati_bhakti_sadans (name) VALUES (?)")->execute([$bhaktiSadan]);
        }
        if (!empty($pickup) && strtolower($pickup) !== 'own vehicle') {
            $db->prepare("INSERT IGNORE INTO panihati_pickup_locations (name) VALUES (?)")->execute([$pickup]);
        }
        
        // Insert registration record
        $ins = $db->prepare("
            INSERT INTO panihati_yatra_registrations 
            (name, phone, email, travel_mode, adults_count, kids_count, bhakti_sadan, pickup_location, amount, payment_status, razorpay_order_id, razorpay_payment_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $name,
            $phone,
            $email,
            $mode,
            $adultsCount,
            $kidsCount,
            $bhaktiSadan,
            $pickup,
            $amount,
            $dbStatus,
            $orderId,
            $paymentId,
            $createdAt
        ]);
        $importedCount++;
    } catch (Exception $ex) {
        echo "  [ERROR] Payment {$paymentId}: Failed to import ({$ex->getMessage()})\n";
    }
}

echo "\n=== Import Summary ===\n";
echo "  Total transactions imported: {$importedCount}\n";

// Fetch total stats now in DB
try {
    $totalRev = $db->query("SELECT SUM(amount) FROM panihati_yatra_registrations WHERE payment_status IN ('paid', 'offline')")->fetchColumn();
    $totalDevs = $db->query("SELECT SUM(adults_count + kids_count) FROM panihati_yatra_registrations WHERE payment_status IN ('paid', 'offline')")->fetchColumn();
    $totalAds = $db->query("SELECT SUM(adults_count) FROM panihati_yatra_registrations WHERE payment_status IN ('paid', 'offline')")->fetchColumn();
    $totalKds = $db->query("SELECT SUM(kids_count) FROM panihati_yatra_registrations WHERE payment_status IN ('paid', 'offline')")->fetchColumn();
    echo "  New system total revenue: ₹" . number_format((float)$totalRev, 2) . "\n";
    echo "  New system total devotees: " . (int)$totalDevs . " (Adults: $totalAds, Kids: $totalKds)\n";
} catch (Exception $ex) {
    // Ignore
}
echo "\n=== Migration Done ===\n";
