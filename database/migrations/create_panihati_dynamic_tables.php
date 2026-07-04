<?php
/**
 * Create Panihati Dynamic Bhakti Sadans & Pickup Locations Tables
 * 
 * Run: php database/migrations/create_panihati_dynamic_tables.php
 */

chdir(__DIR__ . '/../..');
require_once 'config.php';

echo "=== Panihati Dynamic Options Migration ===\n\n";

try {
    $db = getDB();
    
    // 1. Create Bhakti Sadan table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `panihati_bhakti_sadans` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'panihati_bhakti_sadans' created.\n";

    // 2. Create Pickup Location table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `panihati_pickup_locations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] Table 'panihati_pickup_locations' created.\n";

    // 3. Seed Bhakti Sadans
    $sadans = [
        'Anekal', 'Annapurnishwara', 'HAL', 'HSR Layout', 'JP Nagar', 'Kudlu(Electronics City)',
        'Nelamangala', 'Raja Rajeswari Nagar', 'RT Nagar', 'Sahakara Nagar', 'Sarjapur Road',
        'Seshadripuram', 'Nagarbhavi', 'Yelahanka', 'Hulimavu', 'Byrathi Bande', 'Vijayanagar',
        'Hoskote', 'Haralur', 'Prakashnagar', 'Mahadevapura'
    ];
    
    $insertSadan = $db->prepare("INSERT IGNORE INTO `panihati_bhakti_sadans` (`name`) VALUES (?)");
    $sadanCount = 0;
    foreach ($sadans as $s) {
        $insertSadan->execute([$s]);
        if ($insertSadan->rowCount() > 0) {
            $sadanCount++;
        }
    }
    echo "  [OK] Seeded {$sadanCount} Bhakti Sadans.\n";

    // 4. Seed Pickup Locations
    $pickups = [
        'Abbigere', 'Ayyappa Nagar', 'BEL Circle', 'BHEL Mysore Road', 'BTM Layout', 'Bapuji Nagar',
        'Basaveshwara Nagar', 'Budigere Cross', 'Byarahalli', 'Byrathi Bande', 'Chikkabanavara / Abbigere',
        'Domlur', 'GM Palya', 'HAL', 'HSR Layout', 'Hanumantha Nagar', 'Hebbal', 'Hoskote', 'Hulimavu',
        'JP Nagar 3rd Phase', 'JP Nagar 8th Phase', 'KR Puram', 'Kudlu', 'Kumarswamy Layout', 'Laggere',
        'Magadi Road', 'Marathahalli', 'Mico Layout', 'Muddenapalya', 'Mysore Road (Nayandahalli)',
        'Nagarbhavi', 'Nelamangala', 'Old Madras Road (Big Bazaar)', 'Peenya Jalahalli Cross', 'RT Nagar',
        'Rajajinagar 1st Block', 'Rajajinagar (Shankar Math)', 'Rajarajeswari Medical College', 'Ramamurthy Nagar',
        'Sahakarnagar', 'Sarjapur Road', 'ISKCON Seshadripuram (Temple)', 'Shankar Math', 'Sivanahalli',
        'Vijay Nagar (Maruthi Mandir)', 'Wilson Garden', 'West of Chord Road (Warrier Bakery)', 'Yelanka New Town',
        'Yeshwanthpur', 'Haralur', 'RajaRajeswari Nagar', 'Sanjay Nagar', 'Prakashnagar', 'Mahadevapura'
    ];
    
    $insertPickup = $db->prepare("INSERT IGNORE INTO `panihati_pickup_locations` (`name`) VALUES (?)");
    $pickupCount = 0;
    foreach ($pickups as $p) {
        $insertPickup->execute([$p]);
        if ($insertPickup->rowCount() > 0) {
            $pickupCount++;
        }
    }
    echo "  [OK] Seeded {$pickupCount} Pickup Locations.\n";
    
    echo "\n=== Migration Complete ===\n";

} catch (Exception $e) {
    echo "  [ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
