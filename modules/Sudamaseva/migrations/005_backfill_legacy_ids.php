<?php
/**
 * Sudamaseva Module — Phase 5: Backfill Legacy IDs
 *
 * Populates the `legacy_id_no` column on `sudamaseva_donors` by matching
 * donors with their records from the old standalone Sudamaseva application
 * (database: iskcosf7_sudamasava, table: tbl_users).
 *
 * Matching strategy:
 *   1. PRIMARY: Match by phone number between old tbl_users and new sudamaseva_donors
 *   2. FALLBACK: Parse `notes` column for "Old ID: N" pattern (from initial migration)
 *   3. REPORT: List records that could not be matched
 *
 * Run: php modules/Sudamaseva/migrations/005_backfill_legacy_ids.php
 *
 * Requirements:
 *   - Old database must be accessible with credentials (configurable via env vars)
 *   - 004_add_manual_payment_fields.php must have been run first (adds legacy_id_no column)
 */

chdir(__DIR__ . '/../../..');
require_once 'config.php';

// ================================================================
// CONFIGURATION — Same pattern as existing migration scripts
// ================================================================
define('OLD_DB_HOST', $_ENV['OLD_DB_HOST'] ?? $_SERVER['OLD_DB_HOST'] ?? getenv('OLD_DB_HOST') ?? 'localhost');
define('OLD_DB_NAME', $_ENV['OLD_DB_NAME'] ?? $_SERVER['OLD_DB_NAME'] ?? getenv('OLD_DB_NAME') ?? 'iskcosf7_sudamasava');
define('OLD_DB_USER', $_ENV['OLD_DB_USER'] ?? $_SERVER['OLD_DB_USER'] ?? getenv('OLD_DB_USER') ?? 'root');
define('OLD_DB_PASS', $_ENV['OLD_DB_PASS'] ?? $_SERVER['OLD_DB_PASS'] ?? getenv('OLD_DB_PASS') ?? '');

echo "=== Sudamaseva Module — Phase 5: Backfill Legacy IDs ===\n\n";
echo "Source: " . OLD_DB_NAME . " (old standalone app, tbl_users.id_no)\n";
echo "Target: sudamaseva_donors.legacy_id_no\n\n";

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
    echo "  You can set OLD_DB_HOST, OLD_DB_NAME, OLD_DB_USER, OLD_DB_PASS env vars.\n";
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
// TRACKING
// ================================================================
$stats = [
    'old_users_read'       => 0,
    'matched_by_phone'     => 0,
    'matched_by_notes'     => 0,
    'already_had_legacy_id' => 0,
    'no_match'             => [],
    'orphan_donors'        => [],
];

// ================================================================
// STEP 1: Read all old users with their id_no
// ================================================================
echo "\n=== Step 1: Reading old user records ===\n";

try {
    $oldUsers = $oldDb->query("
        SELECT id, id_no, user_name, phone
        FROM tbl_users
        WHERE id_no IS NOT NULL AND id_no != ''
        ORDER BY id ASC
    ")->fetchAll();

    $stats['old_users_read'] = count($oldUsers);
    echo "  [OK] Read {$stats['old_users_read']} user records with id_no from old system.\n";

    if (empty($oldUsers)) {
        echo "  [!] No records found. Check that the old database has data in tbl_users.id_no.\n";
        exit(0);
    }

} catch (PDOException $e) {
    echo "  [ERROR] Failed to read old users: " . $e->getMessage() . "\n";
    echo "  The old database table structure may differ. Check if 'tbl_users' and 'id_no' exist.\n";
    exit(1);
}

// ================================================================
// STEP 2: Read all migrated donors from new database
// ================================================================
echo "\n=== Step 2: Reading new donor records ===\n";

try {
    $newDonors = $newDb->query("
        SELECT id, donor_name, phone, legacy_id_no, notes, source
        FROM sudamaseva_donors
        ORDER BY id ASC
    ")->fetchAll();

    echo "  [OK] Read " . count($newDonors) . " donor records from new system.\n";

} catch (PDOException $e) {
    echo "  [ERROR] Failed to read new donors: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// STEP 3: Build lookup maps for matching
// ================================================================
echo "\n=== Step 3: Matching donors ===\n";

// Build phone-to-donor map (normalize: strip non-digits)
$phoneToDonor = [];
foreach ($newDonors as $donor) {
    $normalizedPhone = preg_replace('/[^0-9]/', '', $donor['phone'] ?? '');
    if (!empty($normalizedPhone)) {
        if (!isset($phoneToDonor[$normalizedPhone])) {
            $phoneToDonor[$normalizedPhone] = $donor;
        }
    }
}

// Build notes-based map for donors with "Old ID: N" pattern
$notesToDonor = [];
foreach ($newDonors as $donor) {
    if (!empty($donor['notes']) && preg_match('/Old ID:\s*(\d+)/', $donor['notes'], $m)) {
        $oldUserId = (int) $m[1];
        $notesToDonor[$oldUserId] = $donor;
    }
}

// ================================================================
// STEP 4: Match and update
// ================================================================
try {
    $newDb->beginTransaction();

    $updateStmt = $newDb->prepare("
        UPDATE sudamaseva_donors SET legacy_id_no = ? WHERE id = ?
    ");

    foreach ($oldUsers as $oldUser) {
        $oldUserId = (int) $oldUser['id'];
        $legacyIdNo = trim($oldUser['id_no'] ?? '');
        $oldPhone = preg_replace('/[^0-9]/', '', $oldUser['phone'] ?? '');
        $matched = false;

        // Check if phone is set via OLD_DB_MIGRATE_SKIP_PHONE env var
        $skipPhoneMatch = filter_var(
            $_ENV['OLD_DB_MIGRATE_SKIP_PHONE'] ?? getenv('OLD_DB_MIGRATE_SKIP_PHONE') ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        // Strategy A: Match by phone number
        if (!$skipPhoneMatch && !empty($oldPhone) && isset($phoneToDonor[$oldPhone])) {
            $donor = $phoneToDonor[$oldPhone];

            // Skip if donor already has a legacy_id_no (don't overwrite)
            if (!empty($donor['legacy_id_no'])) {
                $stats['already_had_legacy_id']++;
                continue;
            }

            $updateStmt->execute([$legacyIdNo, (int) $donor['id']]);
            if ($updateStmt->rowCount() > 0) {
                $stats['matched_by_phone']++;
                echo "  [+] Phone match: Old User #{$oldUserId} (id_no: {$legacyIdNo}, phone: {$oldPhone}) → Donor #{$donor['id']} ({$donor['donor_name']})\n";
                $matched = true;
            }
        }

        // Strategy B: Match by notes parsing (Old ID: N)
        if (!$matched && isset($notesToDonor[$oldUserId])) {
            $donor = $notesToDonor[$oldUserId];

            if (!empty($donor['legacy_id_no'])) {
                $stats['already_had_legacy_id']++;
                continue;
            }

            $updateStmt->execute([$legacyIdNo, (int) $donor['id']]);
            if ($updateStmt->rowCount() > 0) {
                $stats['matched_by_notes']++;
                echo "  [+] Notes match: Old User #{$oldUserId} (id_no: {$legacyIdNo}) → Donor #{$donor['id']} ({$donor['donor_name']})\n";
                $matched = true;
            }
        }

        // No match found — report for manual review
        if (!$matched) {
            $stats['no_match'][] = [
                'old_user_id' => $oldUserId,
                'old_name' => $oldUser['user_name'] ?? 'Unknown',
                'old_phone' => $oldUser['phone'] ?? '',
                'legacy_id_no' => $legacyIdNo,
            ];
        }
    }

    $newDb->commit();
    echo "\n  [OK] Updates applied successfully.\n";

} catch (Exception $e) {
    if ($newDb->inTransaction()) {
        $newDb->rollBack();
    }
    echo "  [ERROR] Update failed: " . $e->getMessage() . "\n";
    exit(1);
}

// ================================================================
// STEP 5: Identify orphan donors (migrated donors with no old user match)
// ================================================================
echo "\n=== Step 5: Orphan donor check ===\n";

try {
    $orphanCheck = $newDb->query("
        SELECT id, donor_name, phone, source, legacy_id_no
        FROM sudamaseva_donors
        WHERE legacy_id_no IS NULL
          AND source = 'migrated'
        ORDER BY id ASC
    ")->fetchAll();

    if (!empty($orphanCheck)) {
        echo "  [!] Found " . count($orphanCheck) . " migrated donors without a legacy_id_no:\n";
        foreach (array_slice($orphanCheck, 0, 20) as $orphan) {
            echo "      Donor #{$orphan['id']}: {$orphan['donor_name']} (phone: {$orphan['phone']})\n";
            $stats['orphan_donors'][] = $orphan['id'];
        }
        if (count($orphanCheck) > 20) {
            echo "      ... and " . (count($orphanCheck) - 20) . " more\n";
        }
    } else {
        echo "  [OK] All migrated donors have a legacy_id_no.\n";
    }

} catch (PDOException $e) {
    echo "  [WARN] Could not check orphans: " . $e->getMessage() . "\n";
}

// ================================================================
// UNMATCHED RECORDS REPORT
// ================================================================
if (!empty($stats['no_match'])) {
    echo "\n=== Unmatched Records ===\n";
    echo "  [!] " . count($stats['no_match']) . " old user records could not be matched to any donor:\n";
    echo "      These may be users whose phone number changed or was not migrated.\n";
    echo "      Manually set legacy_id_no for these if needed.\n\n";

    foreach (array_slice($stats['no_match'], 0, 30) as $nm) {
        echo "      Old User #{$nm['old_user_id']}: {$nm['old_name']} (phone: {$nm['old_phone']}, id_no: {$nm['legacy_id_no']})\n";
    }
    if (count($stats['no_match']) > 30) {
        echo "      ... and " . (count($stats['no_match']) - 30) . " more\n";
    }

    // Generate SQL for manual backfill
    echo "\n      SQL to manually set legacy_id_no (run after identifying correct donor_id):\n";
    echo "      -- UPDATE sudamaseva_donors SET legacy_id_no = 'ID_VALUE' WHERE id = DONOR_ID;\n";
}

// ================================================================
// SUMMARY
// ================================================================
echo "\n" . str_repeat('=', 60) . "\n";
echo "  BACKFILL SUMMARY\n";
echo str_repeat('=', 60) . "\n\n";
echo "  Old user records with id_no:   {$stats['old_users_read']}\n";
echo "  Matched by phone:               {$stats['matched_by_phone']}\n";
echo "  Matched by notes:               {$stats['matched_by_notes']}\n";
echo "  Already had legacy_id_no:       {$stats['already_had_legacy_id']}\n";
echo "  No match found:                 " . count($stats['no_match']) . "\n";
echo "  Migrated donors still missing:  " . count($stats['orphan_donors']) . "\n";
echo "\n";

$totalMatched = $stats['matched_by_phone'] + $stats['matched_by_notes'] + $stats['already_had_legacy_id'];
if ($totalMatched > 0) {
    echo "  ✅ Legacy IDs backfilled for {$totalMatched} donors.\n";
}
if (empty($stats['no_match']) && empty($stats['orphan_donors'])) {
    echo "  ✅ All records successfully matched.\n";
} elseif (empty($stats['no_match'])) {
    echo "  ✅ All old user records matched. Orphan donors with no legacy ID may need manual review.\n";
} else {
    echo "  ⚠️  " . count($stats['no_match']) . " old records could not be matched. Review the list above.\n";
}
echo "\n" . str_repeat('=', 60) . "\n";
echo "=== Backfill Complete ===\n";
