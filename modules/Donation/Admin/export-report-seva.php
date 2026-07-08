<?php
/**
 * Export Seva-wise Report as CSV
 * Category → Activity → Seva with qty and amount
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('reports.export');

$db = getDB();

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$filterCategory = trim($_GET['report_category'] ?? '');
$filterCause = isset($_GET['cause_id']) && $_GET['cause_id'] !== '' ? intval($_GET['cause_id']) : '';

$where = ["t.payment_status = 'paid'"];
$params = [];

if ($startDate !== '') { $where[] = "t.created_at >= ?"; $params[] = $startDate . ' 00:00:00'; }
if ($endDate !== '') { $where[] = "t.created_at <= ?"; $params[] = $endDate . ' 23:59:59'; }
if ($filterCause !== '') { $where[] = "t.cause_id = ?"; $params[] = $filterCause; }
if ($filterCategory !== '') { $where[] = "c.category = ?"; $params[] = $filterCategory; }

$whereClause = implode(" AND ", $where);

$catLabels = [
    'festival' => 'Grand Festivals', 'ekadashi' => 'Ekadashi', 'appearance' => 'Appearance Days',
    'disappearance' => 'Disappearance Days', 'event' => 'Events & Programs', 'service' => 'Seva & Services',
    'construction' => 'Temple Construction', 'general' => 'General Donations',
];

$filename = 'isjm_seva_report_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['Category', 'Activity', 'Seva Name', 'Seva Type', 'Quantity Sponsored', 'Donations', 'Total Amount (INR)', '% of Total']);

try {
    $sql = "
        SELECT 
            COALESCE(c.category, 'general') as cause_category,
            c.title as cause_title,
            COALESCE(ms.name, legacy_s.name, 'Unspecified') as seva_name,
            COALESCE(msc.name, legacy_sc.name, 'General') as seva_type,
            COUNT(*) as donation_count,
            SUM(t.amount) as total_amount,
            SUM(COALESCE(t.quantity, 1)) as total_qty
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
        LEFT JOIN master_seva_categories msc ON ms.category_id = msc.id
        LEFT JOIN donation_cause_sevas legacy_s ON t.seva_id = legacy_s.id AND t.master_seva_id IS NULL
        LEFT JOIN donation_seva_categories legacy_sc ON legacy_s.category_id = legacy_sc.id AND t.master_seva_id IS NULL
        WHERE {$whereClause}
        GROUP BY c.category, c.title, COALESCE(ms.name, legacy_s.name, 'Unspecified'),
                 COALESCE(msc.name, legacy_sc.name, 'General')
        ORDER BY FIELD(c.category, 'festival','ekadashi','appearance','disappearance','event','service','construction','general'), c.title, total_amount DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $totalAmount = array_sum(array_column($rows, 'total_amount'));

    foreach ($rows as $r) {
        $catLabel = $catLabels[$r['cause_category']] ?? ucfirst($r['cause_category']);
        $pct = $totalAmount > 0 ? round(($r['total_amount'] / $totalAmount) * 100, 1) : 0;
        fputcsv($output, [
            $catLabel,
            $r['cause_title'] ?: 'General',
            $r['seva_name'],
            $r['seva_type'],
            $r['total_qty'],
            $r['donation_count'],
            number_format($r['total_amount'], 2, '.', ''),
            $pct . '%',
        ]);
    }

    fputcsv($output, [
        '', '', 'TOTAL', '',
        array_sum(array_column($rows, 'total_qty')),
        array_sum(array_column($rows, 'donation_count')),
        number_format($totalAmount, 2, '.', ''),
        '100%',
    ]);

} catch (PDOException $e) {
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

fclose($output);
exit;
