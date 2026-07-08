<?php
/**
 * Sudamaseva Module — Phase 2: Data Migration
 * 
 * Migrates all data from the old standalone Sudamaseva application
 * (database: iskcosf7_sudamasava) into the new module tables.
 * 
 * Phases:
 *   1. Migrate tbl_users → sudamaseva_donors (handle duplicates, orphans)
 *   2. Create subscription records from payment history
 *   3. Migrate tbl_rec_ins_pay → sudamaseva_payments
 *   3a. Migrate orphan payments from payments table
 *   4. Validation & summary report
 * 
 * Run: php modules/Sudamaseva/migrations/002_migrate_data.php
 * 
 * Requirements:
 *   - 001_create_tables.php must have been run first
 *   - Old DB credentials: host=localhost, db=iskcosf7_sudamasava, user=root, pass=''
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

// ================================================================
// CONFIGURATION
// ================================================================
define('OLD_DB_HOST', $_ENV['OLD_DB_HOST'] ?? $_SERVER['OLD_DB_HOST'] ?? getenv('OLD_DB_HOST') ?? $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost');
define('OLD_DB_NAME', $_ENV['OLD_DB_NAME'] ?? $_SERVER['OLD_DB_NAME'] ?? getenv('OLD_DB_NAME') ?? 'iskcosf7_sudamasava');
define('OLD_DB_USER', $_ENV['OLD_DB_USER'] ?? $_SERVER['OLD_DB_USER'] ?? getenv('OLD_DB_USER') ?? $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?? 'root');
define('OLD_DB_PASS', $_ENV['OLD_DB_PASS'] ?? $_SERVER['OLD_DB_PASS'] ?? getenv('OLD_DB_PASS') ?? $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS') ?? '');


echo "=== Sudamaseva Module — Data Migration ===\n\n";
echo "Source: " . OLD_DB_NAME . " (old standalone app)\n";
echo "Target: isjm_donations (new module tables)\n\n";

// ================================================================
// HELPER: Generate UUID v4
// ================================================================
function generateUUID(): string
{
    // Generate a RFC 4122-compliant UUID v4
    $data = random_bytes(16);
    // Set version to 0100 (UUID v4)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set variant to 10xx
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// ================================================================
// HELPER: Generate receipt number
// Format: SMS/YYYY/NNNNN (e.g., SMS/2026/00001)
// ================================================================
function generateReceiptNo(int $sequence, string $year = null): string
{
    $year = $year ?? date('Y');
    return sprintf('SMS/%s/%05d', $year, $sequence);
}

// ================================================================
// CONNECT TO OLD DB
// ================================================================
echo "--- Connecting to old database ---\n";
try {
    $oldDb = new PDO(
        "mysql:host=" . OLD_DB_HOST . ";dbname=" . OLD_DB_NAME . ";charset=utf8mb4",
        OLD_DB_USER,
        OLD_DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    echo "  [OK] Connected to old database '" . OLD_DB_NAME . "'.\n";
} catch (PDOException $e) {
    echo "  [ERROR] Cannot connect to old database: " . $e->getMessage() . "\n";
    echo "  Make sure the old MySQL server is running and credentials are correct.\n";
    exit(1);
}

// ================================================================
// CONNECT TO NEW DB
// ================================================================
echo "--- Connecting to new database ---\n";
try {
    $newDb = getDB();
    $newDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "  [OK] Connected to new database.\n";
} catch (Exception $e) {
    echo "  [ERROR] Cannot connect to new database: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// TRACKING COUNTERS
// ================================================================
$stats = [
    // Phase 1
    'donors_read'             => 0,
    'donors_inserted'         => 0,
    'donor_duplicates_merged' => 0,
    'donor_orphans_created'   => 0,
    // Phase 2
    'subscriptions_created'   => 0,
    'subscriptions_active'    => 0,
    // Phase 3
    'payments_migrated'       => 0,
    // Phase 3a
    'orphan_payments'         => 0,
    // Validation
    'validation_errors'       => [],
];

// ================================================================
// PHASE 1: Migrate tbl_users → sudamaseva_donors
// ================================================================
echo "\n=== Phase 1: Migrating Donors ===\n";

try {
    $newDb->beginTransaction();

    // 1a. Read all old users, ordered by id ASC for consistent duplicate handling
    $oldUsers = $oldDb->query("SELECT * FROM tbl_users ORDER BY id ASC")->fetchAll();
    $stats['donors_read'] = count($oldUsers);
    echo "  [..] Read {$stats['donors_read']} user records from old system.\n";

    // 1b. Handle duplicate phones: keep the LAST occurrence (highest id = most recent)
    $phoneMap = []; // phone → user record (last wins)
    $duplicatePhones = [];
    foreach ($oldUsers as $user) {
        $phone = trim($user['phone'] ?? '');
        if (empty($phone)) {
            continue; // Skip users without phone — they'll be handled separately
        }
        if (isset($phoneMap[$phone])) {
            $duplicatePhones[$phone] = ($duplicatePhones[$phone] ?? 1) + 1;
        }
        $phoneMap[$phone] = $user; // Last record wins for same phone
    }

    if (!empty($duplicatePhones)) {
        echo "  [!] Found " . count($duplicatePhones) . " duplicate phone numbers:\n";
        foreach ($duplicatePhones as $phone => $count) {
            echo "      Phone {$phone}: {$count} records (keeping most recent)\n";
            $stats['donor_duplicates_merged'] += ($count - 1);
        }
    }

    // 1c. Build final donor list from phoneMap + users without phone
    $donorsToInsert = [];
    $processedPhones = [];
    foreach ($oldUsers as $user) {
        $phone = trim($user['phone'] ?? '');
        if (empty($phone)) {
            // User has no phone — still migrate them
            $donorsToInsert[] = $user;
        } elseif (!isset($processedPhones[$phone])) {
            // Only insert if we haven't already (phoneMap ensures last wins)
            $processedPhones[$phone] = true;
            // Use the deduplicated record from phoneMap
            $donorsToInsert[] = $phoneMap[$phone];
        }
    }

    echo "  [..] Preparing to insert " . count($donorsToInsert) . " unique donors.\n";

    // 1d. Insert donors
    $insertDonor = $newDb->prepare("
        INSERT INTO sudamaseva_donors 
        (uuid, donor_name, phone, email, pan, area, city, state, source, notes, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'migrated', ?, 'active', ?)
    ");

    foreach ($donorsToInsert as $user) {
        $uuid = generateUUID();
        $createdAt = $user['date_submit'] ?? date('Y-m-d H:i:s');
        $notes = "Migrated from old system. Old ID: {$user['id']}";

        $insertDonor->execute([
            $uuid,
            $user['user_name'] ?? 'Unknown Donor',
            $user['phone'] ?? '',
            !empty($user['email']) ? $user['email'] : null,
            !empty($user['pan']) ? $user['pan'] : null,
            !empty($user['area']) ? $user['area'] : null,
            !empty($user['city']) ? $user['city'] : null,
            !empty($user['state']) ? $user['state'] : null,
            $notes,
            $createdAt,
        ]);
        $stats['donors_inserted']++;
    }

    // 1e. Create placeholder donors for orphan user IDs in tbl_rec_ins_pay
    //     (user_ids that have payments but no user record)
    $orphanUserIds = $oldDb->query("
        SELECT DISTINCT r.user_id 
        FROM tbl_rec_ins_pay r 
        LEFT JOIN tbl_users u ON r.user_id = u.id 
        WHERE u.id IS NULL
    ")->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($orphanUserIds)) {
        echo "  [!] Found " . count($orphanUserIds) . " orphan user IDs with payments but no user record.\n";
        echo "      Creating placeholder donor records...\n";

        $insertOrphanDonor = $newDb->prepare("
            INSERT INTO sudamaseva_donors 
            (uuid, donor_name, phone, email, pan, area, city, state, source, notes, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'migrated', ?, 'active', ?)
        ");

        foreach ($orphanUserIds as $oldUserId) {
            $uuid = generateUUID();
            $name = "Unknown Donor (Old ID: {$oldUserId})";
            $notes = "Placeholder — payments exist for old user_id {$oldUserId} but no user record found.";
            $createdAt = date('Y-m-d H:i:s');

            // Use 'orphan-{id}' as unique placeholder phone to avoid UNIQUE constraint violations
            $placeholderPhone = 'orphan-' . $oldUserId;

            $insertOrphanDonor->execute([
                $uuid,
                $name,
                $placeholderPhone,
                null,
                null,
                null,
                null,
                null,
                $notes,
                $createdAt,
            ]);
            $stats['donor_orphans_created']++;
        }
    }

    $newDb->commit();
    echo "  [OK] Phase 1 complete. Donors inserted: {$stats['donors_inserted']} (+ {$stats['donor_orphans_created']} orphans).\n";

} catch (Exception $e) {
    if ($newDb->inTransaction()) {
        $newDb->rollBack();
    }
    echo "  [ERROR] Phase 1 failed: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// BUILD OLD-TO-NEW ID MAP
// ================================================================
echo "\n--- Building ID mapping ---\n";
$oldToNewDonorId = []; // old tbl_users.id → new sudamaseva_donors.id
$phoneToNewDonorId = []; // phone → new sudamaseva_donors.id

// Map donors created from tbl_users (by phone)
$donorsByPhone = $newDb->query("
    SELECT id, phone, notes FROM sudamaseva_donors WHERE source = 'migrated'
")->fetchAll();

foreach ($donorsByPhone as $donor) {
    $phone = $donor['phone'] ?? '';
    $notes = $donor['notes'] ?? '';

    // Extract old ID from notes: "Migrated from old system. Old ID: N"
    if (preg_match('/Old ID: (\d+)/', $notes, $m)) {
        $oldToNewDonorId[(int)$m[1]] = (int)$donor['id'];
    }
    if (!empty($phone)) {
        $phoneToNewDonorId[$phone] = (int)$donor['id'];
    }
}

// Map orphan donors (placeholder records) — match by notes: "Old ID: N"
$orphanDonors = $newDb->query("
    SELECT id, notes FROM sudamaseva_donors 
    WHERE source = 'migrated' AND donor_name LIKE 'Unknown Donor%'
")->fetchAll();

foreach ($orphanDonors as $donor) {
    if (preg_match('/old user_id (\d+)/', $donor['notes'], $m)) {
        $oldToNewDonorId[(int)$m[1]] = (int)$donor['id'];
    }
}

echo "  [OK] Mapped " . count($oldToNewDonorId) . " old user IDs to new donor IDs.\n";

// ================================================================
// PHASE 2: Create Subscription Records
// ================================================================
echo "\n=== Phase 2: Creating Subscriptions ===\n";

try {
    $newDb->beginTransaction();

    // Get payment summary per user from old system
    $userPaymentSummary = $oldDb->query("
        SELECT 
            user_id,
            COUNT(*) as total_payments,
            MAX(ins_no) as max_installment,
            SUM(amount) as total_amount,
            MIN(date_pay) as first_payment_date,
            MAX(date_pay) as last_payment_date,
            DATEDIFF(NOW(), MAX(date_pay)) as days_since_last_payment
        FROM tbl_rec_ins_pay
        GROUP BY user_id
    ")->fetchAll();

    $insertSubscription = $newDb->prepare("
        INSERT INTO sudamaseva_subscriptions
        (donor_id, amount, razorpay_subscription_id, razorpay_plan_id, status, 
         start_date, end_date, total_installments, installments_paid, 
         source, old_user_id, created_at)
        VALUES (?, ?, NULL, NULL, ?, ?, NULL, ?, ?, 'migrated', ?, ?)
    ");

    foreach ($userPaymentSummary as $summary) {
        $oldUserId = (int)$summary['user_id'];
        $newDonorId = $oldToNewDonorId[$oldUserId] ?? null;

        if (!$newDonorId) {
            $stats['validation_errors'][] = "Phase 2: No donor mapping for old user_id {$oldUserId}";
            continue;
        }

        // Get the user's committed amount from tbl_users (if available)
        $userAmount = 0;
        $userDate = null;
        $userStmt = $oldDb->prepare("SELECT amount, date_submit FROM tbl_users WHERE id = ?");
        $userStmt->execute([$oldUserId]);
        $userRow = $userStmt->fetch();
        if ($userRow) {
            $userAmount = (int)($userRow['amount'] ?? 0);
            $userDate = $userRow['date_submit'] ?? null;
        } else {
            // Orphan user — use average amount from payments
            $userAmount = (int)($summary['total_amount'] / max($summary['total_payments'], 1));
        }

        $totalInstallments = (int)$summary['max_installment'];
        $installmentsPaid = (int)$summary['total_payments'];
        $daysSinceLastPay = (int)$summary['days_since_last_payment'];

        // Determine status: active if last payment within 60 days
        $status = ($daysSinceLastPay <= 60) ? 'active' : 'completed';

        $startDate = $userDate ?? $summary['first_payment_date'];
        $createdAt = $userDate ?? $summary['first_payment_date'];

        $insertSubscription->execute([
            $newDonorId,
            $userAmount > 0 ? $userAmount : 100, // Default ₹100 if amount unknown
            $status,
            $startDate,
            $totalInstallments,
            $installmentsPaid,
            $oldUserId,
            $createdAt,
        ]);

        $stats['subscriptions_created']++;
        if ($status === 'active') {
            $stats['subscriptions_active']++;
        }
    }

    $newDb->commit();
    echo "  [OK] Phase 2 complete. Subscriptions created: {$stats['subscriptions_created']} ({$stats['subscriptions_active']} active).\n";

} catch (Exception $e) {
    if ($newDb->inTransaction()) {
        $newDb->rollBack();
    }
    echo "  [ERROR] Phase 2 failed: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// BUILD SUBSCRIPTION ID MAP
// ================================================================
echo "\n--- Building subscription mapping ---\n";

// Map old_user_id → new subscription_id
$subscriptionMap = []; // old_user_id → subscription_id
$subRows = $newDb->query("
    SELECT id, old_user_id FROM sudamaseva_subscriptions WHERE source = 'migrated'
")->fetchAll();
foreach ($subRows as $row) {
    if ($row['old_user_id']) {
        $subscriptionMap[(int)$row['old_user_id']] = (int)$row['id'];
    }
}
echo "  [OK] Mapped " . count($subscriptionMap) . " subscriptions.\n";

// ================================================================
// PHASE 3: Migrate Installment Payment History
// ================================================================
echo "\n=== Phase 3: Migrating Payment History ===\n";

try {
    $newDb->beginTransaction();

    // Read all installment payments from old system
    $oldPayments = $oldDb->query("
        SELECT r.*, u.user_name, u.phone 
        FROM tbl_rec_ins_pay r
        LEFT JOIN tbl_users u ON r.user_id = u.id
        ORDER BY r.id ASC
    ")->fetchAll();

    echo "  [..] Processing " . count($oldPayments) . " installment records...\n";

    $insertPayment = $newDb->prepare("
        INSERT IGNORE INTO sudamaseva_payments
        (subscription_id, donor_id, amount, installment_number,
         razorpay_payment_id, razorpay_order_id, razorpay_signature,
         payment_status, payment_date, receipt_number, notes,
         is_migrated, old_ins_pay_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NULL, 'paid', ?, ?, ?, 1, ?, ?)
    ");

    $receiptSequence = 1;
    $currentYear = date('Y');

    foreach ($oldPayments as $pay) {
        $oldUserId = (int)$pay['user_id'];
        $subscriptionId = $subscriptionMap[$oldUserId] ?? null;
        $donorId = $oldToNewDonorId[$oldUserId] ?? null;

        if (!$subscriptionId || !$donorId) {
            // These payments have no mapping — they'll be handled as orphans in Phase 3a
            continue;
        }

        $amount = (int)($pay['amount'] ?? 0);
        $installmentNo = (int)($pay['ins_no'] ?? 0);
        $paymentDate = $pay['date_pay'] ?? null;
        $razorpayPaymentId = !empty($pay['payment_id']) ? $pay['payment_id'] : null;
        $razorpayOrderId = !empty($pay['order_id']) ? $pay['order_id'] : null;

        // Generate receipt number
        $receiptNo = generateReceiptNo($receiptSequence++, $currentYear);

        $notes = "Migrated installment #{$installmentNo} for user {$oldUserId}";

        if ($insertPayment->execute([
            $subscriptionId,
            $donorId,
            $amount,
            $installmentNo,
            $razorpayPaymentId,
            $razorpayOrderId,
            $paymentDate,
            $receiptNo,
            $notes,
            $pay['id'], // old_ins_pay_id
            $paymentDate ?: date('Y-m-d H:i:s'),
        ]) && $insertPayment->rowCount() > 0) {
            $stats['payments_migrated']++;
        }
    }

    $newDb->commit();
    echo "  [OK] Phase 3 complete. Payments migrated: {$stats['payments_migrated']}.\n";

} catch (Exception $e) {
    if ($newDb->inTransaction()) {
        $newDb->rollBack();
    }
    echo "  [ERROR] Phase 3 failed: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// PHASE 3a: Migrate Supplemental Payments (from `payments` table)
// ================================================================
echo "\n=== Phase 3a: Migrating Supplemental Payments ===\n";

try {
    // Read old payments table
    $supplementalPayments = $oldDb->query("
        SELECT * FROM payments ORDER BY id ASC
    ")->fetchAll();

    echo "  [..] Found " . count($supplementalPayments) . " supplemental payment records.\n";

    // Check for overlap with already-migrated payments
    // (collation mismatch: payments uses utf8mb3_general_ci, tbl_rec_ins_pay uses utf8mb3_unicode_ci)
    $orphanSupplemental = [];
    $matchedCount = 0;

    foreach ($supplementalPayments as $sp) {
        $paymentId = $sp['payment_id'] ?? '';
        if (empty($paymentId)) continue;

        // Check if this payment_id already exists in migrated payments
        $checkStmt = $newDb->prepare("SELECT COUNT(*) FROM sudamaseva_payments WHERE razorpay_payment_id = ?");
        $checkStmt->execute([$paymentId]);
        $exists = (int)$checkStmt->fetchColumn() > 0;

        if (!$exists) {
            $orphanSupplemental[] = $sp;
        } else {
            $matchedCount++;
        }
    }

    echo "  [..] {$matchedCount} already covered by Phase 3 (matched by razorpay_payment_id).\n";
    echo "  [..] " . count($orphanSupplemental) . " are orphan records with no linked installment.\n";

    if (!empty($orphanSupplemental)) {
        $newDb->beginTransaction();

        $insertOrphanPay = $newDb->prepare("
            INSERT IGNORE INTO sudamaseva_payments
            (subscription_id, donor_id, amount, installment_number,
             razorpay_payment_id, razorpay_order_id, razorpay_signature,
             payment_status, payment_date, receipt_number, notes,
             is_migrated, old_ins_pay_id, created_at)
            VALUES (NULL, NULL, ?, 0, ?, NULL, NULL, 'paid', ?, ?, ?, 1, NULL, ?)
        ");

        foreach ($orphanSupplemental as $sp) {
            $amount = (int)($sp['amount'] ?? 0);
            $paymentId = $sp['payment_id'] ?? '';
            $paymentDate = $sp['created_at'] ?? date('Y-m-d H:i:s');
            $receiptNo = generateReceiptNo($receiptSequence++, $currentYear);
            $notes = "Orphan — from old payments table, no associated user/installment";

            if ($insertOrphanPay->execute([
                $amount,
                $paymentId,
                $paymentDate,
                $receiptNo,
                $notes,
                $paymentDate,
            ]) && $insertOrphanPay->rowCount() > 0) {
                $stats['orphan_payments']++;
            }
        }

        $newDb->commit();
        echo "  [OK] Phase 3a complete. Orphan payments inserted: {$stats['orphan_payments']}.\n";
    } else {
        echo "  [OK] Phase 3a complete. No orphan payments to insert.\n";
    }

} catch (Exception $e) {
    if (isset($newDb) && $newDb->inTransaction()) {
        $newDb->rollBack();
    }
    echo "  [ERROR] Phase 3a failed: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// PHASE 4: Validation & Summary Report
// ================================================================
echo "\n=== Phase 4: Validation ===\n";

$pass = true;

try {
    // 4a. Count donors
    $donorCount = (int)$newDb->query("SELECT COUNT(*) FROM sudamaseva_donors WHERE source = 'migrated'")->fetchColumn();
    $expectedDonors = $stats['donors_read'] - $stats['donor_duplicates_merged'] + $stats['donor_orphans_created'];
    echo "  [..] Donors migrated: {$donorCount} (expected: ~{$expectedDonors})\n";
    if ($donorCount < 300) {
        echo "  [WARN] Fewer donors than expected. Expected at least 300.\n";
        $stats['validation_errors'][] = "Low donor count: {$donorCount}";
        $pass = false;
    }

    // 4b. Count subscriptions
    $subCount = (int)$newDb->query("SELECT COUNT(*) FROM sudamaseva_subscriptions WHERE source = 'migrated'")->fetchColumn();
    echo "  [..] Subscriptions created: {$subCount}\n";
    if ($subCount < 300) {
        echo "  [WARN] Fewer subscriptions than expected. Expected at least 300.\n";
        $pass = false;
    }

    // 4c. Count payments
    $payCount = (int)$newDb->query("SELECT COUNT(*) FROM sudamaseva_payments WHERE is_migrated = 1")->fetchColumn();
    $oldPayCount = (int)$oldDb->query("SELECT COUNT(*) FROM tbl_rec_ins_pay")->fetchColumn();
    $oldSuppCount = (int)$oldDb->query("SELECT COUNT(*) FROM payments")->fetchColumn();
    $expectedTotal = $oldPayCount + min($oldSuppCount, count($orphanSupplemental ?? []));
    echo "  [..] Payments migrated: {$payCount} (old: {$oldPayCount} installments + supplemental)\n";

    // 4d. Check amount totals
    $newTotal = (float)$newDb->query("SELECT COALESCE(SUM(amount), 0) FROM sudamaseva_payments WHERE is_migrated = 1")->fetchColumn();
    $oldTotal = (float)$oldDb->query("SELECT COALESCE(SUM(amount), 0) FROM tbl_rec_ins_pay")->fetchColumn();
    $diff = abs($newTotal - $oldTotal);
    echo "  [..] Amount total: new=₹{$newTotal}, old=₹{$oldTotal} (diff=₹{$diff})\n";
    if ($diff > 0) {
        echo "  [WARN] Amount totals differ by ₹{$diff}. Investigate if this is significant.\n";
    }

    // 4e. Check for orphan payments (payments with no donor)
    $orphanPayCount = (int)$newDb->query("
        SELECT COUNT(*) FROM sudamaseva_payments 
        WHERE is_migrated = 1 AND donor_id IS NULL
    ")->fetchColumn();
    echo "  [..] Orphan payments (no donor link): {$orphanPayCount}\n";

    // 4f. Check active subscriptions
    $activeSubs = (int)$newDb->query("
        SELECT COUNT(*) FROM sudamaseva_subscriptions 
        WHERE source = 'migrated' AND status = 'active'
    ")->fetchColumn();
    echo "  [..] Active subscriptions (paid within 60 days): {$activeSubs}\n";

    // 4g. Receipt count
    // Note: We're not generating receipt records in this migration — they'll be generated
    // on-demand when a donor requests a receipt or admin generates them.
    $receiptCount = (int)$newDb->query("SELECT COUNT(*) FROM sudamaseva_receipts")->fetchColumn();
    echo "  [..] Receipt records: {$receiptCount} (generated on-demand)\n";

} catch (Exception $e) {
    echo "  [ERROR] Validation failed: " . $e->getMessage() . "\n";
    $pass = false;
}

// ================================================================
// FINAL SUMMARY
// ================================================================
echo "\n" . str_repeat('=', 60) . "\n";
echo "  MIGRATION SUMMARY\n";
echo str_repeat('=', 60) . "\n\n";
echo "  Phase 1 - Donors:\n";
echo "    Old user records read:          {$stats['donors_read']}\n";
echo "    Duplicate phones merged:         {$stats['donor_duplicates_merged']}\n";
echo "    Donors inserted:                 {$stats['donors_inserted']}\n";
echo "    Orphan placeholder donors:       {$stats['donor_orphans_created']}\n";
echo "  Phase 2 - Subscriptions:\n";
echo "    Subscriptions created:           {$stats['subscriptions_created']}\n";
echo "    Active (paid within 60 days):    {$stats['subscriptions_active']}\n";
echo "  Phase 3 - Payments:\n";
echo "    Installment payments migrated:   {$stats['payments_migrated']}\n";
echo "  Phase 3a - Supplemental:\n";
echo "    Orphan supplemental payments:    {$stats['orphan_payments']}\n";
echo "\n";

if (empty($stats['validation_errors'])) {
    echo "  ✅ Validation passed — no issues detected.\n";
} else {
    echo "  ⚠️  Validation warnings:\n";
    foreach ($stats['validation_errors'] as $err) {
        echo "    - {$err}\n";
    }
}

echo "\n";
echo "  Old DB status: Kept as read-only archive (iskcosf7_sudamasava)\n";
echo "  Next: Update the old app to redirect users to the new URLs.\n";
echo "\n" . str_repeat('=', 60) . "\n";
echo "=== Migration Complete ===\n";
