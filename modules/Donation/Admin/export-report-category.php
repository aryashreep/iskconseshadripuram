<?php
/**
 * Export Category-wise Donation Report as CSV
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('reports.export');

$db = getDB();

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$where = ["t.payment_status = 'paid'"];
$params = [];

if ($startDate !== '') {
    $where[] = "t.created_at >= ?";
    $params[] = $startDate . ' 00:00:00';
}
if ($endDate !== '') {
    $where[] = "t.created_at <= ?";
    $params[] = $endDate . ' 23:59:59';
}

$whereClause = implode(" AND ", $where);

$categoryLabels = [
    'festival' => 'Grand Festivals',
    'ekadashi' => 'Ekadashi',
    'appearance' => 'Appearance Days',
    'disappearance' => 'Disappearance Days',
    'event' => 'Events & Programs',
    'service' => 'Seva & Services',
    'construction' => 'Temple Construction',
    'general' => 'General Donations',
];

$filename = 'isjm_category_report_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Category', 'Number of Donations', 'Total Amount (INR)', '% of Total']);

try {
    $sql = "
        SELECT 
            COALESCE(c.category, 'general') as category,
            COUNT(*) as donation_count,
            SUM(t.amount) as total_amount
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause}
        GROUP BY COALESCE(c.category, 'general')
        ORDER BY total_amount DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

    $totalAmount = array_sum(array_column($reports, 'total_amount'));

    foreach ($reports as $r) {
        $label = $categoryLabels[$r['category']] ?? ucfirst($r['category']);
        $pct = $totalAmount > 0 ? round(($r['total_amount'] / $totalAmount) * 100, 1) : 0;
        fputcsv($output, [
            $label,
            $r['donation_count'],
            number_format($r['total_amount'], 2, '.', ''),
            $pct . '%',
        ]);
    }

    // Total row
    fputcsv($output, [
        'TOTAL',
        array_sum(array_column($reports, 'donation_count')),
        number_format($totalAmount, 2, '.', ''),
        '100%',
    ]);

} catch (PDOException $e) {
    fputcsv($output, ['Error exporting data: ' . $e->getMessage()]);
}

fclose($output);
exit;
