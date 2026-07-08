<?php
require_once 'config.php';
$db = getDB();

$rows = $db->query("SELECT * FROM panihati_yatra_offline_aggregates")->fetchAll(PDO::FETCH_ASSOC);
echo "Current Offline Aggregates:\n";
print_r($rows);
