<?php
/**
 * Sudamaseva Module — Export Members (Donors) as CSV
 *
 * Accepts the same filter parameters as the donors list page:
 *   ?search=, ?status=, ?hide_orphans=
 *
 * "Enrolled Amount" is the monthly subscription amount of the donor's
 * latest subscription (preferring the active one).
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.export');

$db = getDB();

// Read filter parameters (same as donors list page)
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$hideOrphans = isset($_GET['hide_orphans']) ? $_GET['hide_orphans'] !== '0' : true;

// Build WHERE clause matching donors.php exactly
$where = [];
$params = [];

if ($status) {
    $where[] = 'd.status = ?';
    $params[] = $status;
}

if ($search) {
    $where[] = '(d.donor_name LIKE ? OR d.phone LIKE ? OR d.email LIKE ?)';
    $s = '%' . $search . '%';
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

if ($hideOrphans) {
    $where[] = "d.phone NOT LIKE 'orphan-%'";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Filename with timestamp
$filename = 'sudamaseva_members_' . date('Y-m-d_H-i-s') . '.csv';

// Output headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create stream resource
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// CSV header row
$headers = [
    'Member ID',
    'Name',
    'Phone',
    'Email',
    'PAN',
    'Enrolled Amount (Monthly INR)',
    'Subscription Duration (Months)',
    'Total Paid (INR)',
    'Status',
    'Source',
    'Created At',
];
fputcsv($output, $headers);

try {
    // Query all matching members (no pagination — full export)
    $sql = "
        SELECT d.id, d.donor_name, d.phone, d.email, d.pan, d.status, d.source, d.created_at,
               (SELECT s.amount FROM sudamaseva_subscriptions s
                WHERE s.donor_id = d.id
                ORDER BY (s.status = 'active') DESC, s.created_at DESC, s.id DESC
                LIMIT 1) as enrolled_amount,
               (SELECT s.total_installments FROM sudamaseva_subscriptions s
                WHERE s.donor_id = d.id
                ORDER BY (s.status = 'active') DESC, s.created_at DESC, s.id DESC
                LIMIT 1) as subscription_months,
               (SELECT COALESCE(SUM(p.amount), 0) FROM sudamaseva_payments p
                WHERE p.donor_id = d.id AND p.payment_status = 'paid') as total_paid
        FROM sudamaseva_donors d
        {$whereClause}
        ORDER BY d.created_at DESC, d.id DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['donor_name'] ?? '',
            $row['phone'] ?? '',
            $row['email'] ?? '',
            $row['pan'] ? strtoupper($row['pan']) : '',
            number_format((float) ($row['enrolled_amount'] ?? 0), 2, '.', ''),
            (int) ($row['subscription_months'] ?? 0),
            number_format((float) ($row['total_paid'] ?? 0), 2, '.', ''),
            $row['status'] ?? '',
            $row['source'] ?? '',
            $row['created_at'] ?? '',
        ]);
    }
} catch (PDOException $e) {
    fputcsv($output, ['Error exporting data']);
}

fclose($output);
exit;
