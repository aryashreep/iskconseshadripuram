<?php
/**
 * Sudamaseva Module — Export Completed Subscriptions as CSV
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.export');

$db = getDB();

// Read filter parameters
$search = trim($_GET['search'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');

$filterApplied = isset($_GET['filter_applied']);
if ($filterApplied) {
    $hideOrphans = isset($_GET['hide_orphans']) && $_GET['hide_orphans'] === '1';
} else {
    $hideOrphans = true;
}

// Build query identical to list page
$where = ["(s.status = 'completed' OR (s.total_installments > 0 AND s.installments_paid >= s.total_installments))"];
$params = [];

if ($search) {
    $where[] = "(d.donor_name LIKE ? OR d.phone LIKE ? OR d.email LIKE ? OR s.id = ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = (int)$search;
}

if ($from) {
    $where[] = "COALESCE(s.end_date, DATE_ADD(s.start_date, INTERVAL (s.total_installments - 1) MONTH)) >= ?";
    $params[] = $from . ' 00:00:00';
}

if ($to) {
    $where[] = "COALESCE(s.end_date, DATE_ADD(s.start_date, INTERVAL (s.total_installments - 1) MONTH)) <= ?";
    $params[] = $to . ' 23:59:59';
}

if ($hideOrphans) {
    $where[] = "d.phone NOT LIKE 'orphan-%'";
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Filename with timestamp
$filename = 'sudamaseva_completed_subscriptions_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, [
    'Subscription ID',
    'Donor Name',
    'Phone',
    'Email',
    'PAN',
    'Monthly Amount (INR)',
    'Start Date',
    'End Date',
    'Duration (Months)',
    'Installments Paid',
    'Collection Mode',
    'Source',
    'Status',
]);

try {
    $sql = "
        SELECT s.*, d.donor_name, d.phone, d.email, d.pan
        FROM sudamaseva_subscriptions s
        JOIN sudamaseva_donors d ON s.donor_id = d.id
        {$whereClause}
        ORDER BY s.updated_at DESC, s.id DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch()) {
        $endDate = $row['end_date'] ?? '';
        if (empty($endDate) && (int)$row['total_installments'] > 0 && !empty($row['start_date'])) {
            $months = (int)$row['total_installments'] - 1;
            $endDate = date('Y-m-d H:i:s', strtotime("+{$months} months", strtotime($row['start_date'])));
        }

        fputcsv($output, [
            $row['id'],
            $row['donor_name'] ?? '',
            $row['phone'] ?? '',
            $row['email'] ?? '',
            $row['pan'] ? strtoupper($row['pan']) : '',
            number_format((float) ($row['amount'] ?? 0), 2, '.', ''),
            $row['start_date'] ?? '',
            $endDate,
            (int) ($row['total_installments'] ?? 0),
            (int) ($row['installments_paid'] ?? 0),
            $row['collection_mode'] ?? '',
            $row['source'] ?? '',
            $row['status'] ?? '',
        ]);
    }
} catch (PDOException $e) {
    fputcsv($output, ['Error exporting data: ' . $e->getMessage()]);
}

fclose($output);
exit;
