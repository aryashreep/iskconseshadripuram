<?php
/**
 * Sudamaseva Module — Phase 3: Incremental Data Migration
 * 
 * Migrates all remaining/missing data from the old standalone Sudamaseva application
 * (database: iskcosf7_sudamasava) into the new module tables.
 * 
 * Run: php modules/Sudamaseva/migrations/003_incremental_migration.php
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

define('OLD_DB_HOST', $_ENV['OLD_DB_HOST'] ?? $_SERVER['OLD_DB_HOST'] ?? getenv('OLD_DB_HOST') ?? $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost');
define('OLD_DB_NAME', $_ENV['OLD_DB_NAME'] ?? $_SERVER['OLD_DB_NAME'] ?? getenv('OLD_DB_NAME') ?? 'iskcosf7_sudamasava');
define('OLD_DB_USER', $_ENV['OLD_DB_USER'] ?? $_SERVER['OLD_DB_USER'] ?? getenv('OLD_DB_USER') ?? $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?? 'root');
define('OLD_DB_PASS', $_ENV['OLD_DB_PASS'] ?? $_SERVER['OLD_DB_PASS'] ?? getenv('OLD_DB_PASS') ?? $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS') ?? '');


echo "=== Sudamaseva Module — Incremental Data Migration ===\n\n";
echo "Source: " . OLD_DB_NAME . " (old standalone app)\n";
echo "Target: isjm_donations (new module tables)\n\n";

// Helper: Generate UUID v4
function generateUUID(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Helper: Generate receipt number
function generateReceiptNo(int $sequence, string $year = null): string
{
    $year = $year ?? date('Y');
    return sprintf('SMS/%s/%05d', $year, $sequence);
}

// Connect to old DB
echo "--- Connecting to old database ---\n";
try {
    $oldDb = new PDO(
        "mysql:host=" . OLD_DB_HOST . ";dbname=" . OLD_DB_NAME . ";charset=utf8mb4",
        OLD_DB_USER,
        OLD_DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "  [OK] Connected to old database '" . OLD_DB_NAME . "'.\n";
} catch (PDOException $e) {
    echo "  [ERROR] Cannot connect to old database: " . $e->getMessage() . "\n";
    exit(1);
}

// Connect to new DB
echo "--- Connecting to new database ---\n";
try {
    $newDb = getDB();
    $newDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "  [OK] Connected to new database.\n";
} catch (Exception $e) {
    echo "  [ERROR] Cannot connect to new database: " . $e->getMessage() . "\n";
    exit(1);
}

$stats = [
    'donors_created' => 0,
    'subscriptions_created' => 0,
    'payments_migrated' => 0,
];

// ================================================================
// PHASE 1: Migrate / Map Missing Donors and Subscriptions
// ================================================================
echo "\n=== Phase 1: Migrating Missing Donors and Subscriptions ===\n";

try {
    $newDb->beginTransaction();

    // 1. Get list of already migrated old user IDs from target subscriptions table
    $migratedUserIds = $newDb->query("
        SELECT old_user_id FROM sudamaseva_subscriptions WHERE old_user_id IS NOT NULL
    ")->fetchAll(PDO::FETCH_COLUMN);
    $migratedUserIdsMap = array_flip($migratedUserIds);

    // 2. Read all users from old system
    $oldUsers = $oldDb->query("SELECT * FROM tbl_users ORDER BY id ASC")->fetchAll();
    echo "  [..] Read " . count($oldUsers) . " users from old database.\n";

    // 3. Keep cache of existing donors in local target database to handle duplicate phones
    $existingDonors = $newDb->query("SELECT id, phone FROM sudamaseva_donors")->fetchAll();
    $phoneToDonorIdMap = [];
    foreach ($existingDonors as $donor) {
        $phone = trim($donor['phone']);
        if (!empty($phone)) {
            $phoneToDonorIdMap[$phone] = (int)$donor['id'];
        }
    }

    $insertDonor = $newDb->prepare("
        INSERT INTO sudamaseva_donors 
        (uuid, donor_name, phone, email, pan, area, city, state, source, notes, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'migrated', ?, 'active', ?)
    ");

    $insertSubscription = $newDb->prepare("
        INSERT INTO sudamaseva_subscriptions
        (donor_id, amount, razorpay_subscription_id, razorpay_plan_id, status, 
         start_date, end_date, total_installments, installments_paid, 
         source, old_user_id, created_at)
        VALUES (?, ?, NULL, NULL, 'active', ?, NULL, ?, ?, 'migrated', ?, ?)
    ");

    foreach ($oldUsers as $user) {
        $oldUserId = (int)$user['id'];
        
        // Skip if already has subscription record with this old_user_id
        if (isset($migratedUserIdsMap[$oldUserId])) {
            continue;
        }

        $phone = trim($user['phone'] ?? '');
        $name = trim($user['user_name'] ?? 'Unknown Donor');
        $email = !empty($user['email']) ? trim($user['email']) : null;
        $pan = !empty($user['pan']) ? trim($user['pan']) : null;
        $area = !empty($user['area']) ? trim($user['area']) : null;
        $city = !empty($user['city']) ? trim($user['city']) : null;
        $state = !empty($user['state']) ? trim($user['state']) : null;
        $createdAt = $user['date_submit'] ?? date('Y-m-d H:i:s');
        $amount = (int)($user['amount'] ?? 0);

        // Fetch user payment aggregates to compute installments
        $summaryStmt = $oldDb->prepare("
            SELECT COUNT(*) as total_payments, MAX(ins_no) as max_installment
            FROM tbl_rec_ins_pay
            WHERE user_id = ?
        ");
        $summaryStmt->execute([$oldUserId]);
        $summary = $summaryStmt->fetch();
        $totalInstallments = (int)($summary['max_installment'] ?? 0);
        $installmentsPaid = (int)($summary['total_payments'] ?? 0);

        $donorId = null;

        // If phone number is not empty, check if donor already exists
        if (!empty($phone) && isset($phoneToDonorIdMap[$phone])) {
            $donorId = $phoneToDonorIdMap[$phone];
            echo "  [..] User ID {$oldUserId} ({$name}) shares phone '{$phone}' with existing Donor ID {$donorId}. Mapping to existing donor.\n";
        } else {
            // Create a new donor record
            $uuid = generateUUID();
            $notes = "Migrated from old system. Old ID: {$oldUserId}";
            
            $insertDonor->execute([
                $uuid,
                $name,
                $phone,
                $email,
                $pan,
                $area,
                $city,
                $state,
                $notes,
                $createdAt
            ]);
            
            $donorId = (int)$newDb->lastInsertId();
            if (!empty($phone)) {
                $phoneToDonorIdMap[$phone] = $donorId;
            }
            $stats['donors_created']++;
            echo "  [+] Created Donor ID {$donorId} for User ID {$oldUserId} ({$name}).\n";
        }

        // Create the subscription row
        $insertSubscription->execute([
            $donorId,
            $amount,
            $createdAt,
            $totalInstallments,
            $installmentsPaid,
            $oldUserId,
            $createdAt
        ]);
        $stats['subscriptions_created']++;
    }

    $newDb->commit();
    echo "  [OK] Phase 1 complete. Donors created: {$stats['donors_created']}, Subscriptions created: {$stats['subscriptions_created']}.\n";

} catch (Exception $e) {
    if ($newDb->inTransaction()) {
        $newDb->rollBack();
    }
    echo "  [ERROR] Phase 1 failed: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// PHASE 2: Migrate Missing Installment Payments
// ================================================================
echo "\n=== Phase 2: Migrating Missing Payments ===\n";

try {
    $newDb->beginTransaction();

    // 1. Build mapping of old_user_id -> (subscription_id, donor_id)
    $subscriptionRows = $newDb->query("
        SELECT id as subscription_id, donor_id, old_user_id 
        FROM sudamaseva_subscriptions 
        WHERE old_user_id IS NOT NULL
    ")->fetchAll();

    $subscriptionMap = [];
    foreach ($subscriptionRows as $sub) {
        $subscriptionMap[(int)$sub['old_user_id']] = [
            'subscription_id' => (int)$sub['subscription_id'],
            'donor_id' => (int)$sub['donor_id']
        ];
    }

    // 2. Fetch already migrated payment IDs (old_ins_pay_id) from target DB
    $migratedPayIds = $newDb->query("
        SELECT old_ins_pay_id FROM sudamaseva_payments WHERE old_ins_pay_id IS NOT NULL
    ")->fetchAll(PDO::FETCH_COLUMN);
    $migratedPayIdsMap = array_flip($migratedPayIds);

    // 2b. Fetch already migrated Razorpay Payment IDs to prevent constraint violations
    $migratedRPIds = $newDb->query("
        SELECT razorpay_payment_id FROM sudamaseva_payments WHERE razorpay_payment_id IS NOT NULL AND razorpay_payment_id != ''
    ")->fetchAll(PDO::FETCH_COLUMN);
    $migratedRPIdsMap = array_flip($migratedRPIds);

    // 3. Find the maximum receipt sequence number for the current year
    $currentYear = date('Y');
    $maxReceipt = $newDb->query("
        SELECT receipt_number FROM sudamaseva_payments 
        WHERE receipt_number LIKE 'SMS/{$currentYear}/%' 
        ORDER BY receipt_number DESC LIMIT 1
    ")->fetchColumn();
    
    $receiptSequence = 1;
    if ($maxReceipt) {
        if (preg_match('/\/(\d+)$/', $maxReceipt, $m)) {
            $receiptSequence = (int)$m[1] + 1;
        }
    }
    echo "  [..] Starting receipt sequence for year {$currentYear} at: {$receiptSequence}\n";

    // 4. Fetch all installment payments from old DB
    $oldPayments = $oldDb->query("SELECT * FROM tbl_rec_ins_pay ORDER BY id ASC")->fetchAll();
    echo "  [..] Read " . count($oldPayments) . " payment records from old database.\n";

    $insertPayment = $newDb->prepare("
        INSERT INTO sudamaseva_payments
        (subscription_id, donor_id, amount, installment_number,
         razorpay_payment_id, razorpay_order_id, razorpay_signature,
         payment_status, payment_date, receipt_number, notes,
         is_migrated, old_ins_pay_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NULL, 'paid', ?, ?, ?, 1, ?, ?)
    ");

    foreach ($oldPayments as $pay) {
        $oldPayId = (int)$pay['id'];
        $razorpayPaymentId = !empty($pay['payment_id']) ? $pay['payment_id'] : null;

        // Skip if already migrated by old_ins_pay_id
        if (isset($migratedPayIdsMap[$oldPayId])) {
            continue;
        }

        // Skip if already exists in target DB by razorpay_payment_id
        if ($razorpayPaymentId !== null && isset($migratedRPIdsMap[$razorpayPaymentId])) {
            continue;
        }

        $oldUserId = (int)$pay['user_id'];
        if (!isset($subscriptionMap[$oldUserId])) {
            echo "  [!] Warning: Skipping payment ID {$oldPayId} as old User ID {$oldUserId} has no subscription mapping.\n";
            continue;
        }

        $subInfo = $subscriptionMap[$oldUserId];
        $subscriptionId = $subInfo['subscription_id'];
        $donorId = $subInfo['donor_id'];

        $amount = (int)($pay['amount'] ?? 0);
        $installmentNo = (int)($pay['ins_no'] ?? 0);
        $paymentDate = $pay['date_pay'] ?? null;
        $razorpayOrderId = !empty($pay['order_id']) ? $pay['order_id'] : null;
        
        $receiptNo = generateReceiptNo($receiptSequence++, $currentYear);
        $notes = "Migrated installment #{$installmentNo} for user {$oldUserId}";


        $insertPayment->execute([
            $subscriptionId,
            $donorId,
            $amount,
            $installmentNo,
            $razorpayPaymentId,
            $razorpayOrderId,
            $paymentDate,
            $receiptNo,
            $notes,
            $oldPayId,
            $paymentDate ?: date('Y-m-d H:i:s'),
        ]);

        $stats['payments_migrated']++;
    }

    $newDb->commit();
    echo "  [OK] Phase 2 complete. Payments migrated: {$stats['payments_migrated']}.\n";

} catch (Exception $e) {
    if ($newDb->inTransaction()) {
        $newDb->rollBack();
    }
    echo "  [ERROR] Phase 2 failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Migration Complete ===\n";
echo "Summary:\n";
echo "- Donors Created: {$stats['donors_created']}\n";
echo "- Subscriptions Created: {$stats['subscriptions_created']}\n";
echo "- Payments Migrated: {$stats['payments_migrated']}\n";
