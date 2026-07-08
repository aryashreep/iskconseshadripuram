<?php
/**
 * Migration: Normalize Panihati Yatra Pickup Location Names
 * 
 * Run: php database/migrations/normalize_pickup_locations.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Normalizing Pickup Locations ===\n\n";

$db = getDB();

$mappings = [
    // Duplicate Variations -> Target Standard
    'Byrati Bande' => 'Byrathi Bande',
    'Chikkabanavara/Abbigere' => 'Chikkabanavara / Abbigere',
    'ISKCON Seshadripuram(temple)' => 'ISKCON Seshadripuram (Temple)',
    'JP Nager 3rd Phase' => 'JP Nagar 3rd Phase',
    'JP Nager 8th Phase' => 'JP Nagar 8th Phase',
    'Old Madras Rd (Big Bazaar)' => 'Old Madras Road (Big Bazaar)',
    'Rajaji Nagar 1 st block' => 'Rajajinagar 1st Block',
    'Rajaji Nagar(Shankar Math)' => 'Rajajinagar (Shankar Math)',
    'RajaRajeswari Nagar' => 'Rajarajeshwari Nagar',
    'Vijay Nagar Maruthi Mandir' => 'Vijay Nagar (Maruthi Mandir)',
    
    // Spelling / Standardization Fixes
    'Hanumantha Nagar' => 'Hanumanthanagar',
    'Yelanka New Town' => 'Yelahanka New Town',
    'Rajaji Nagar' => 'Rajajinagar'
];

try {
    $db->beginTransaction();

    foreach ($mappings as $oldName => $newName) {
        echo "Processing mapping: '{$oldName}' -> '{$newName}'\n";

        // 1. Update online registrations table
        $stmt = $db->prepare("UPDATE panihati_yatra_registrations SET pickup_location = ? WHERE pickup_location = ?");
        $stmt->execute([$newName, $oldName]);
        $regUpdated = $stmt->rowCount();
        if ($regUpdated > 0) {
            echo "  - Updated {$regUpdated} registrations.\n";
        }

        // 2. Update offline aggregates table
        $stmt = $db->prepare("UPDATE panihati_yatra_offline_aggregates SET pickup_location = ? WHERE pickup_location = ?");
        $stmt->execute([$newName, $oldName]);
        $aggUpdated = $stmt->rowCount();
        if ($aggUpdated > 0) {
            echo "  - Updated {$aggUpdated} offline summaries.\n";
        }

        // 3. Update lookup table (panihati_pickup_locations)
        // Check if both exist
        $stmtExistOld = $db->prepare("SELECT id FROM panihati_pickup_locations WHERE name = ?");
        $stmtExistOld->execute([$oldName]);
        $oldId = $stmtExistOld->fetchColumn();

        $stmtExistNew = $db->prepare("SELECT id FROM panihati_pickup_locations WHERE name = ?");
        $stmtExistNew->execute([$newName]);
        $newId = $stmtExistNew->fetchColumn();

        if ($oldId && $newId) {
            // Both exist: delete the old one to avoid duplicates
            $stmtDel = $db->prepare("DELETE FROM panihati_pickup_locations WHERE id = ?");
            $stmtDel->execute([$oldId]);
            echo "  - Merged & deleted duplicate lookup entry (ID: {$oldId}).\n";
        } elseif ($oldId && !$newId) {
            // Only old exists: rename it
            $stmtUpd = $db->prepare("UPDATE panihati_pickup_locations SET name = ? WHERE id = ?");
            $stmtUpd->execute([$newName, $oldId]);
            echo "  - Renamed lookup entry (ID: {$oldId}) to '{$newName}'.\n";
        } elseif (!$oldId && !$newId) {
            // Neither exists: create new standard entry
            $stmtIns = $db->prepare("INSERT INTO panihati_pickup_locations (name, is_active) VALUES (?, 1)");
            $stmtIns->execute([$newName]);
            echo "  - Created new standard entry '{$newName}'.\n";
        } else {
            echo "  - Standard entry '{$newName}' already exists. Old entry already cleaned up.\n";
        }
    }

    $db->commit();
    echo "\n=== Normalization Complete Successfully ===\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "\n[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
