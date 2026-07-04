<?php
/**
 * Seed Missing Master Catalog Sevas
 * 
 * Copies master sevas from reference causes to causes that have none:
 * - 24 individual Ekadashis → copy sevas from "ekadashi-general" (id=46)
 * - Lord Vamanadeva → copy sevas from "janmashtami" (id=13, a grand festival)
 * 
 * Usage: php database/migrations/seed_missing_master_sevas.php
 */

require_once __DIR__ . '/../../config.php';

echo "=== Seed Missing Master Sevas ===\n\n";

$db = getDB();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ============================================================
// Get reference cause IDs
// ============================================================
$ekGeneral = $db->query("SELECT id FROM donation_causes WHERE slug = 'ekadashi-general'")->fetchColumn();
$janmashtami = $db->query("SELECT id FROM donation_causes WHERE slug = 'janmashtami'")->fetchColumn();

if (!$ekGeneral || !$janmashtami) {
    echo "ERROR: Reference causes not found (ekadashi-general or janmashtami).\n";
    exit(1);
}

echo "Reference causes:\n";
echo "  ekadashi-general: id={$ekGeneral}\n";
echo "  janmashtami: id={$janmashtami}\n\n";

// ============================================================
// Get Ekadashi causes that need sevas
// ============================================================
$ekadashiIds = $db->query("
    SELECT c.id, c.slug, c.title
    FROM donation_causes c
    WHERE c.category = 'ekadashi' AND c.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM donation_cause_master_sevas dcms 
          WHERE dcms.cause_id = c.id AND dcms.is_active = 1
      )
    ORDER BY c.sort_order, c.title
")->fetchAll(PDO::FETCH_ASSOC);

echo "Ekadashis needing sevas: " . count($ekadashiIds) . "\n";

// ============================================================
// Get Lord Vamanadeva (and any other grand festivals needing sevas)
// ============================================================
$festivalIds = $db->query("
    SELECT c.id, c.slug, c.title
    FROM donation_causes c
    WHERE c.category = 'festival' AND c.is_active = 1
      AND NOT EXISTS (
          SELECT 1 FROM donation_cause_master_sevas dcms 
          WHERE dcms.cause_id = c.id AND dcms.is_active = 1
      )
    ORDER BY c.sort_order, c.title
")->fetchAll(PDO::FETCH_ASSOC);

echo "Festivals needing sevas: " . count($festivalIds) . "\n\n";

// ============================================================
// Helper: copy sevas from source cause to target causes
// ============================================================
function copyMasterSevas(PDO $db, int $sourceCauseId, array $targetCauses, string $typeLabel): int {
    // Get all master sevas from the source cause
    $sourceSevas = $db->prepare("
        SELECT master_seva_id, override_amount, override_description, 
               override_max_quantity, sort_order, is_featured, is_active
        FROM donation_cause_master_sevas
        WHERE cause_id = ? AND is_active = 1
        ORDER BY sort_order ASC
    ");
    $sourceSevas->execute([$sourceCauseId]);
    $sevas = $sourceSevas->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sevas)) {
        echo "  WARNING: Source cause #{$sourceCauseId} has no active master sevas to copy.\n";
        return 0;
    }

    $insertStmt = $db->prepare("
        INSERT IGNORE INTO donation_cause_master_sevas 
        (cause_id, master_seva_id, override_amount, override_description, 
         override_max_quantity, sort_order, is_featured, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $totalInserted = 0;

    foreach ($targetCauses as $target) {
        $targetId = $target['id'];
        $targetSlug = $target['slug'];
        $count = 0;

        foreach ($sevas as $seva) {
            try {
                $insertStmt->execute([
                    $targetId,
                    $seva['master_seva_id'],
                    $seva['override_amount'],
                    $seva['override_description'],
                    $seva['override_max_quantity'],
                    $seva['sort_order'],
                    $seva['is_featured'],
                    $seva['is_active'],
                ]);
                $count++;
            } catch (PDOException $e) {
                // Skip duplicates (unique key violation)
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }

        echo "  {$typeLabel} '{$targetSlug}' (id={$targetId}): {$count} sevas linked\n";
        $totalInserted++;
    }

    return $totalInserted;
}

// ============================================================
// Seed Ekadashis
// ============================================================
echo "--- Seeding Ekadashis ---\n";
$ekCount = copyMasterSevas($db, $ekGeneral, $ekadashiIds, 'Ekadashi');
echo "\n";

// ============================================================
// Seed Grand Festivals
// ============================================================
echo "--- Seeding Grand Festivals ---\n";
$festCount = copyMasterSevas($db, $janmashtami, $festivalIds, 'Festival');
echo "\n";

// ============================================================
// Summary
// ============================================================
echo "=== Summary ===\n";
echo "Ekadashis seeded: " . count($ekadashiIds) . "\n";
echo "Festivals seeded: " . count($festivalIds) . "\n";
echo "Done.\n";
