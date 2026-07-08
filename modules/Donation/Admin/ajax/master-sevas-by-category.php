<?php
/**
 * AJAX Endpoint: Get Master Sevas by Category
 * Returns JSON of active sevas for a given category.
 * Used by the festival-edit.php Master Seva Catalog picker.
 */

require_once __DIR__ . '/../../../../admin/auth-check.php';
requireAnyPermission(['seva_catalog.view', 'festivals.edit']);

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($categoryId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

// Fetch sevas for this category
require_once __DIR__ . '/../../../../config.php';
$sevas = getMasterSevas($categoryId, true); // only active

if ($sevas === false || $sevas === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Fetch category info
$db = getDB();
$catStmt = $db->prepare("SELECT id, name, slug, icon FROM master_seva_categories WHERE id = ? AND is_active = 1");
$catStmt->execute([$categoryId]);
$category = $catStmt->fetch();

if (!$category) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Category not found']);
    exit;
}

// Build a clean payload — only needed fields
$items = [];
foreach ($sevas as $s) {
    $items[] = [
        'id'             => (int)$s['id'],
        'name'           => $s['name'],
        'description'    => $s['description'] ?? '',
        'default_amount' => (float)$s['default_amount'],
        'allow_multiple' => (int)$s['allow_multiple'],
        'max_quantity'   => (int)$s['max_quantity'],
        'icon'           => $s['icon'] ?? 'fa-hand-holding-heart',
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success'  => true,
    'category' => $category,
    'items'    => $items,
]);
