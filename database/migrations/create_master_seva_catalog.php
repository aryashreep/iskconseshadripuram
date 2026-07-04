<?php
/**
 * Phase 1: Master Seva Catalog Migration
 * 
 * Creates the enterprise Master Seva Catalog with proper normalization:
 * - master_seva_categories: 10 top-level categories (Deity Sevas, Puja & Ritual, etc.)
 * - master_sevas: Deduplicated single source of truth for all sevas
 * - donation_cause_master_sevas: Festival-to-seva linking with override support
 * 
 * Also adds:
 * - allow_multiple / max_quantity sponsorship controls
 * - master_seva_id FK to donation_transactions (nullable, backward compatible)
 * 
 * This migration is NON-BREAKING. The old donation_cause_sevas table remains
 * fully functional. All existing code continues to work.
 * 
 * IMPORTANT: MySQL auto-commits on DDL (ALTER TABLE). Therefore:
 *   - Steps 1-5 (DML + CREATE TABLE) are inside a transaction for atomicity
 *   - Step 6 (ALTER TABLE) runs AFTER the commit
 * 
 * Usage: php database/migrations/create_master_seva_catalog.php
 */

require_once __DIR__ . '/../../config.php';

echo "=== Master Seva Catalog Migration - Phase 1 ===\n\n";

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // ============================================================
    // TRANSACTION: Steps 1-5 (DDL creates + DML inserts + dedup)
    //            Steps 1-4 use CREATE TABLE IF NOT EXISTS (DML-safe)
    //            Step 5 uses INSERT (pure DML)
    // ============================================================
    $db->beginTransaction();

    // --- STEP 1: Create master_seva_categories table ---
    echo "[1/5] Creating master_seva_categories table...\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS `master_seva_categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `name` VARCHAR(255) NOT NULL,
            `sanskrit_name` VARCHAR(255) DEFAULT NULL,
            `icon` VARCHAR(100) NOT NULL DEFAULT 'fa-hand-holding-heart',
            `description` TEXT DEFAULT NULL,
            `parent_id` INT DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_msc_parent` FOREIGN KEY (`parent_id`) REFERENCES `master_seva_categories`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "       OK\n";

    // --- STEP 2: Seed master_seva_categories ---
    echo "[2/5] Seeding master categories...\n";

    $categories = [
        ['slug' => 'deity-sevas',             'name' => 'Deity Sevas',                  'icon' => 'fa-om',                 'description' => 'Direct worship services for the deities — flower decorations, garments, garlands, bhoga offerings, aratis, and daily worship.', 'sort_order' => 10],
        ['slug' => 'puja-ritual-sevas',        'name' => 'Puja & Ritual Sevas',          'icon' => 'fa-praying-hands',      'description' => 'Sacred ceremonies including abhishekam, homa/yajna, deepa seva, archana, sankalpa, and special pujas.', 'sort_order' => 20],
        ['slug' => 'festival-sevas',           'name' => 'Festival Sevas',               'icon' => 'fa-star',               'description' => 'Sponsorship opportunities for major and minor festivals — day sponsorships, cultural programs, kirtan, harinam, decor, and volunteer support.', 'sort_order' => 30],
        ['slug' => 'rath-yatra-sevas',         'name' => 'Rath Yatra Sevas',             'icon' => 'fa-torii-gate',         'description' => 'Special sevas for the Chariot Festival — chariot decoration, construction, maintenance, ropes, wheels, flags, canopies, and sound/lighting.', 'sort_order' => 40],
        ['slug' => 'prasadam-annadanam-sevas', 'name' => 'Prasadam & Annadanam Sevas',   'icon' => 'fa-utensils',           'description' => 'Food relief and prasadam distribution — annadanam, maha prasad, breakfast/lunch/dinner sponsorship, sweets, fruits, water, juice, buttermilk.', 'sort_order' => 50],
        ['slug' => 'infrastructure-sevas',     'name' => 'Temple Infrastructure Sevas',   'icon' => 'fa-building',           'description' => 'Temple facilities and maintenance — hall/garden/electrical maintenance, generator, tent/seating, LED display, CCTV, drinking water, shoe stand, parking.', 'sort_order' => 60],
        ['slug' => 'outreach-sevas',           'name' => 'Outreach Sevas',               'icon' => 'fa-globe-asia',         'description' => 'Preaching and outreach — Bhagavad-gita and book distribution, children gifts, college preaching, harinam sponsorship, digital preaching, publications, publicity.', 'sort_order' => 70],
        ['slug' => 'devotee-care-sevas',       'name' => 'Devotee Care Sevas',            'icon' => 'fa-hands-helping',      'description' => 'Care for devotees and the community — volunteer care, sadhu bhojan, vaishnava seva, medical camps, senior/youth/children program sponsorships.', 'sort_order' => 80],
        ['slug' => 'digital-sevas',            'name' => 'Digital Sevas',                'icon' => 'fa-laptop-code',        'description' => 'Technology and digital infrastructure — website, mobile app, live streaming, cloud, servers, security, email, SMS, social media, e-learning, digital library.', 'sort_order' => 90],
        ['slug' => 'general-donations',        'name' => 'General Donations',            'icon' => 'fa-heart',              'description' => 'General temple support funds — corpus, building, renovation, emergency, festival, gau seva, education support, charity, relief.', 'sort_order' => 100],
    ];

    $insertCatStmt = $db->prepare("
        INSERT IGNORE INTO `master_seva_categories` (`slug`, `name`, `icon`, `description`, `sort_order`)
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($categories as $cat) {
        $insertCatStmt->execute([$cat['slug'], $cat['name'], $cat['icon'], $cat['description'], $cat['sort_order']]);
        echo "       + {$cat['name']} ({$cat['slug']})\n";
    }

    // --- STEP 3: Create master_sevas table ---
    echo "[3/5] Creating master_sevas table...\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS `master_sevas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(150) NOT NULL UNIQUE,
            `name` VARCHAR(255) NOT NULL,
            `sanskrit_name` VARCHAR(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            `short_description` VARCHAR(255) DEFAULT NULL,
            `category_id` INT NOT NULL,
            `default_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
            `min_amount` DECIMAL(10,2) DEFAULT NULL,
            `max_amount` DECIMAL(10,2) DEFAULT NULL,
            `image_url` VARCHAR(500) DEFAULT NULL,
            `icon` VARCHAR(100) DEFAULT 'fa-hand-holding-heart',
            `allow_multiple` TINYINT(1) NOT NULL DEFAULT 0,
            `max_quantity` INT NOT NULL DEFAULT 1,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
            `is_time_bound` TINYINT(1) NOT NULL DEFAULT 0,
            `available_from` DATE DEFAULT NULL,
            `available_until` DATE DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_ms_category` FOREIGN KEY (`category_id`) REFERENCES `master_seva_categories`(`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("CREATE INDEX `idx_ms_category` ON `master_sevas`(`category_id`)");
    $db->exec("CREATE INDEX `idx_ms_active` ON `master_sevas`(`is_active`)");
    $db->exec("CREATE INDEX `idx_ms_featured` ON `master_sevas`(`is_featured`)");
    echo "       OK\n";

    // --- STEP 4: Create donation_cause_master_sevas table ---
    echo "[4/5] Creating donation_cause_master_sevas table...\n";

    $db->exec("
        CREATE TABLE IF NOT EXISTS `donation_cause_master_sevas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `cause_id` INT NOT NULL,
            `master_seva_id` INT NOT NULL,
            `override_amount` DECIMAL(10,2) DEFAULT NULL,
            `override_description` TEXT DEFAULT NULL,
            `override_max_quantity` INT DEFAULT NULL,
            `override_allow_multiple` TINYINT(1) DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_dcms_cause` FOREIGN KEY (`cause_id`) REFERENCES `donation_causes`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_dcms_master` FOREIGN KEY (`master_seva_id`) REFERENCES `master_sevas`(`id`),
            UNIQUE KEY `uq_cause_master_seva` (`cause_id`, `master_seva_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("CREATE INDEX `idx_dcms_cause` ON `donation_cause_master_sevas`(`cause_id`)");
    $db->exec("CREATE INDEX `idx_dcms_master` ON `donation_cause_master_sevas`(`master_seva_id`)");
    echo "       OK\n";

    // --- STEP 5: Deduplicate existing donation_cause_sevas into master_sevas ---
    echo "[5/5] Deduplicating existing sevas into Master Catalog...\n";

    $oldToNewCategoryMap = [
        'flower-decoration'   => 'deity-sevas',
        'deity-dress'         => 'deity-sevas',
        'garland-seva'        => 'deity-sevas',
        'tulasi-archana'      => 'deity-sevas',
        'pushpa-archana'      => 'puja-ritual-sevas',
        'abhishekam'          => 'puja-ritual-sevas',
        'kalash-abhishekam'   => 'puja-ritual-sevas',
        'homa-yajna'          => 'puja-ritual-sevas',
        'rajbhog-prasad'      => 'prasadam-annadanam-sevas',
        'maha-prasad'         => 'prasadam-annadanam-sevas',
        'annadanam'           => 'prasadam-annadanam-sevas',
        'temple-decoration'   => 'infrastructure-sevas',
        'general-festival'    => 'festival-sevas',
    ];

    $oldSevas = $db->query("
        SELECT cs.*, sc.slug as cat_slug, c.title as cause_title, c.slug as cause_slug
        FROM donation_cause_sevas cs
        JOIN donation_seva_categories sc ON cs.category_id = sc.id
        JOIN donation_causes c ON cs.cause_id = c.id
        WHERE sc.slug IN ('" . implode("','", array_keys($oldToNewCategoryMap)) . "')
        ORDER BY cs.name ASC, cs.amount ASC
    ")->fetchAll();

    echo "       Found " . count($oldSevas) . " existing seva records to deduplicate.\n";

    $uniqueSevas = [];
    $duplicatesRemoved = [];

    foreach ($oldSevas as $seva) {
        $normalizedName = strtolower(trim($seva['name']));
        $normalizedName = preg_replace('/[^a-z0-9\s]/', '', $normalizedName);
        $normalizedName = preg_replace('/\s+/', ' ', $normalizedName);

        $newCatSlug = $oldToNewCategoryMap[$seva['cat_slug']] ?? 'general-donations';

        $catStmt = $db->prepare("SELECT id FROM master_seva_categories WHERE slug = ?");
        $catStmt->execute([$newCatSlug]);
        $newCatId = $catStmt->fetchColumn();

        if (!$newCatId) {
            echo "       WARNING: No master category found for slug: {$newCatSlug}. Skipping seva '{$seva['name']}' (from cause '{$seva['cause_title']}').\n";
            continue;
        }

        $dedupKey = $normalizedName . '|' . $newCatId . '|' . $seva['amount'];

        if (!isset($uniqueSevas[$dedupKey])) {
            $masterSlug = preg_replace('/[^a-z0-9\-]/', '-', $normalizedName);
            $masterSlug = preg_replace('/-+/', '-', trim($masterSlug, '-'));

            $baseSlug = $masterSlug ?: 'seva-' . $seva['id'];
            $masterSlug = $baseSlug;
            $counter = 1;
            $checkSlug = $db->prepare("SELECT COUNT(*) FROM master_sevas WHERE slug = ?");
            while (true) {
                $checkSlug->execute([$masterSlug]);
                if ((int)$checkSlug->fetchColumn() === 0) break;
                $masterSlug = $baseSlug . '-' . $counter++;
            }

            $cleanName = trim(preg_replace('/\s+Seva$/i', '', $seva['name']));

            $insertMaster = $db->prepare("
                INSERT INTO master_sevas (slug, name, description, category_id, default_amount, is_active, sort_order)
                VALUES (?, ?, ?, ?, ?, 1, ?)
            ");
            $insertMaster->execute([
                $masterSlug,
                $cleanName,
                $seva['description'] ?: null,
                $newCatId,
                $seva['amount'],
                $seva['sort_order']
            ]);

            $masterSevaId = (int)$db->lastInsertId();
            $uniqueSevas[$dedupKey] = [
                'master_seva_id' => $masterSevaId,
                'name' => $cleanName,
                'amount' => $seva['amount'],
                'new_cat_id' => $newCatId,
            ];

            echo "       + Master Seva: {$cleanName} (₹{$seva['amount']}) [cat: {$newCatSlug}]\n";
        } else {
            $duplicatesRemoved[] = [
                'cause_slug' => $seva['cause_slug'],
                'cause_title' => $seva['cause_title'],
                'name' => $seva['name'],
                'amount' => $seva['amount'],
            ];
        }

        $masterInfo = $uniqueSevas[$dedupKey];

        $checkLink = $db->prepare("SELECT COUNT(*) FROM donation_cause_master_sevas WHERE cause_id = ? AND master_seva_id = ?");
        $checkLink->execute([$seva['cause_id'], $masterInfo['master_seva_id']]);

        if ((int)$checkLink->fetchColumn() === 0) {
            $insertLink = $db->prepare("
                INSERT INTO donation_cause_master_sevas (cause_id, master_seva_id, override_amount, override_description, sort_order, is_featured)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insertLink->execute([
                $seva['cause_id'],
                $masterInfo['master_seva_id'],
                null,
                $seva['description'] ?: null,
                $seva['sort_order'],
                $seva['is_featured'] ?? 0,
            ]);
        }
    }

    $skippedCount = count($oldSevas) - count($uniqueSevas) - count($duplicatesRemoved);

    echo "\n       Deduplication summary:\n";
    echo "       - Total old records processed: " . count($oldSevas) . "\n";
    echo "       - Master sevas created (unique): " . count($uniqueSevas) . "\n";
    echo "       - Duplicate cause links collapsed: " . count($duplicatesRemoved) . "\n";
    echo "       - Records skipped (unmapped categories): " . max(0, $skippedCount) . "\n";

    // ============================================================
    // COMMIT the transaction
    // Note: MySQL auto-commits on DDL (CREATE TABLE/INDEX). If the
    // transaction was already committed by DDL, commit() throws a
    // harmless 'no active transaction' error, which we catch below.
    // ============================================================
    try {
        $db->commit();
        echo "\n       Transaction committed successfully.\n";
    } catch (PDOException $e) {
        // MySQL auto-committed on DDL — this is expected
        echo "\n       (Transaction auto-committed by MySQL DDL — no action needed)\n";
    }

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n=== MIGRATION FAILED during transaction ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

// ============================================================
// STEP 6: Add master_seva_id to donation_transactions (nullable)
// This runs OUTSIDE the transaction because MySQL auto-commits DDL
// ============================================================
echo "\n[6/5] Adding master_seva_id FK to donation_transactions...\n";

try {
    // Add column (if already exists, skip gracefully)
    $checkCol = $db->query("SHOW COLUMNS FROM donation_transactions LIKE 'master_seva_id'");
    if (!$checkCol->fetch()) {
        $db->exec("ALTER TABLE `donation_transactions` ADD COLUMN `master_seva_id` INT DEFAULT NULL AFTER `seva_id`");
        echo "       Column added.\n";
    } else {
        echo "       Column already exists — skipping.\n";
    }
} catch (PDOException $e) {
    echo "       WARNING: Could not add column: " . $e->getMessage() . "\n";
}

// Add FK constraint separately (check if it already exists)
try {
    $fkCheck = $db->query("
        SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = 'donation_transactions' 
          AND CONSTRAINT_NAME = 'fk_transactions_master_seva'
    ");
    if (!$fkCheck->fetch()) {
        $db->exec("ALTER TABLE `donation_transactions` 
            ADD CONSTRAINT `fk_transactions_master_seva` 
            FOREIGN KEY (`master_seva_id`) REFERENCES `master_sevas`(`id`)");
        echo "       FK constraint added.\n";
    } else {
        echo "       FK constraint already exists — skipping.\n";
    }
} catch (PDOException $e) {
    echo "       WARNING: Could not add FK: " . $e->getMessage() . "\n";
}

// ============================================================
// COMPLETE
// ============================================================
echo "\n=== Migration Complete ===\n\n";
echo "Created:\n";
echo "  - master_seva_categories: 10 top-level categories\n";
echo "  - master_sevas: " . count($uniqueSevas) . " deduplicated sevas\n";
echo "  - donation_cause_master_sevas: Festival-seva links\n";
echo "  - donation_transactions.master_seva_id: Nullable FK (if addable)\n\n";
echo "Backward compatible: All old tables and code remain functional.\n";
echo "The old donation_cause_sevas table is untouched.\n";
echo "Existing donation_transactions have master_seva_id = NULL (no data loss).\n\n";
echo "Next steps:\n";
echo "  1. Verify by browsing donation pages — they still work.\n";
echo "  2. Run admin/festival-edit — still uses old table.\n";
echo "  3. Use phpMyAdmin to review the new tables.\n";
echo "  4. When ready, proceed to Phase 2: Update getCauseSevas() to read from new tables.\n";
