<?php
require_once 'config.php';
$db = getDB();

$count = $db->query("SELECT COUNT(*) FROM panihati_yatra_registrations")->fetchColumn();
echo "Total Registrations Count: {$count}\n";

$by_mode = $db->query("SELECT travel_mode, SUM(adults_count) as adults, SUM(kids_count) as kids, SUM(amount) as amt FROM panihati_yatra_registrations GROUP BY travel_mode")->fetchAll(PDO::FETCH_ASSOC);
print_r($by_mode);
