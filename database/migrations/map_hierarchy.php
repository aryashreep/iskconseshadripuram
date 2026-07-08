<?php
require_once __DIR__ . '/../../config.php';
$db = getDB();

echo "=== DONATION CAUSES (Activities) by Category ===\n";
$rows = $db->query("SELECT category, slug, title FROM donation_causes WHERE is_active = 1 ORDER BY category, title")->fetchAll();
$grouped = [];
foreach($rows as $r) { $grouped[$r['category']][] = $r; }
foreach($grouped as $cat => $items) {
    echo strtoupper($cat) . " (" . count($items) . ")\n";
    foreach($items as $i) echo "  " . $i['slug'] . " | " . $i['title'] . "\n";
}

echo "\n=== CAUSE-SEVA LINKS (donation_cause_master_sevas) ===\n";
$rows = $db->query("
    SELECT c.title as cause_title, c.category, ms.name as seva_name, ms.slug as seva_slug, msc.name as seva_cat
    FROM donation_cause_master_sevas dcms
    JOIN donation_causes c ON dcms.cause_id = c.id
    JOIN master_sevas ms ON dcms.master_seva_id = ms.id
    JOIN master_seva_categories msc ON ms.category_id = msc.id
    WHERE dcms.is_active = 1
    ORDER BY c.category, c.title, msc.sort_order, ms.sort_order
")->fetchAll();
$grouped = [];
foreach($rows as $r) { $grouped[$r['category']][$r['cause_title']][] = $r; }
foreach($grouped as $cat => $causes) {
    echo strtoupper($cat) . "\n";
    foreach($causes as $cause => $sevas) {
        echo "  [" . $cause . "]\n";
        foreach($sevas as $s) echo "    - " . $s['seva_name'] . " (" . $s['seva_cat'] . ")\n";
    }
}

echo "\n=== MASTER SEVAS by Category ===\n";
$rows = $db->query("SELECT msc.name as cat_name, ms.slug, ms.name, ms.default_amount FROM master_sevas ms JOIN master_seva_categories msc ON ms.category_id = msc.id WHERE ms.is_active = 1 ORDER BY msc.sort_order, ms.sort_order")->fetchAll();
$grouped = [];
foreach($rows as $r) { $grouped[$r['cat_name']][] = $r; }
foreach($grouped as $cat => $items) {
    echo $cat . " (" . count($items) . ")\n";
    foreach($items as $i) echo "  " . $i['slug'] . " | " . $i['name'] . " | Rs" . $i['default_amount'] . "\n";
}
