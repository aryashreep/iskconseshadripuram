<?php
/**
 * Export Dashboard Category → Activity hierarchy as CSV
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('reports.export');

$db = getDB();

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$where = ["t.payment_status = 'paid'"];
$params = [];

if ($startDate !== '') { $where[] = "t.created_at >= ?"; $params[] = $startDate . ' 00:00:00'; }
if ($endDate !== '') { $where[] = "t.created_at <= ?"; $params[] = $endDate . ' 23:59:59'; }

$whereClause = implode(" AND ", $where);

$catLabels = [
    'festival' => 'Grand Festivals', 'ekadashi' => 'Ekadashi', 'appearance' => 'Appearance Days',
    'disappearance' => 'Disappearance Days', 'event' => 'Events & Programs', 'service' => 'Seva & Services',
    'construction' => 'Temple Construction', 'general' => 'General Donations',
];

$filename = 'isjm_dashboard_export_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Level', 'Category', 'Activity', 'Donations', 'Total Amount (INR)', '% of Total']);

try {
    // Fetch all data
    $stmt = $db->prepare("
        SELECT COALESCE(c.category, 'general') as category, c.title as activity,
               COUNT(*) as cnt, SUM(t.amount) as total
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause}
        GROUP BY c.category, c.title
        ORDER BY FIELD(c.category, 'festival','ekadashi','appearance','disappearance','event','service','construction','general'), total DESC
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $totalAmount = array_sum(array_column($rows, 'total'));
    $totalDonations = array_sum(array_column($rows, 'cnt'));

    // Group by category
    $grouped = [];
    foreach ($rows as $r) {
        $cat = $r['category'];
        if (!isset($grouped[$cat])) $grouped[$cat] = ['total' => 0, 'count' => 0, 'items' => []];
        $grouped[$cat]['total'] += $r['total'];
        $grouped[$cat]['count'] += $r['cnt'];
        $grouped[$cat]['items'][] = $r;
    }

    foreach ($grouped as $catKey => $cat) {
        $catLabel = $catLabels[$catKey] ?? ucfirst($catKey);
        $catPct = $totalAmount > 0 ? round(($cat['total'] / $totalAmount) * 100, 1) : 0;

        // Category row
        fputcsv($output, ['CATEGORY', $catLabel, '', $cat['count'], number_format($cat['total'], 2, '.', ''), $catPct . '%']);

        // Activity rows
        foreach ($cat['items'] as $item) {
            $actPct = $totalAmount > 0 ? round(($item['total'] / $totalAmount) * 100, 1) : 0;
            fputcsv($output, ['  Activity', '', $item['activity'] ?: 'General', $item['cnt'], number_format($item['total'], 2, '.', ''), $actPct . '%']);
        }
    }

    // Grand total
    fputcsv($output, ['GRAND TOTAL', '', '', $totalDonations, number_format($totalAmount, 2, '.', ''), '100%']);

} catch (PDOException $e) {
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

fclose($output);
exit;
