<?php
/**
 * Sudamaseva Module — Export Payments as CSV
 *
 * Accepts the same filter parameters as the payments list page:
 *   ?search=, ?status=, ?from=, ?to=
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.export');

$db = getDB();

// Read filter parameters (same as payments list page)
$status = trim($_GET['status'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$search = trim($_GET['search'] ?? '');

// Build WHERE clause matching payments.php exactly
$where = ['1 = 1'];
$params = [];

if ($status) {
    $where[] = 'p.payment_status = ?';
    $params[] = $status;
}

if ($from) {
    $where[] = 'p.payment_date >= ?';
    $params[] = $from . ' 00:00:00';
}

if ($to) {
    $where[] = 'p.payment_date <= ?';
    $params[] = $to . ' 23:59:59';
}

if ($search) {
    $where[] = '(d.donor_name LIKE ? OR d.phone LIKE ? OR p.razorpay_payment_id LIKE ?)';
    $s = '%' . $search . '%';
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$whereClause = implode(' AND ', $where);

// Filename with timestamp
$filename = 'sudamaseva_payments_export_' . date('Y-m-d_H-i-s') . '.csv';

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
    'Payment ID',
    'Date',
    'Donor Name',
    'Phone',
    'Email',
    'PAN',
    'Amount (INR)',
    'Installment #',
    'Payment Status',
    'Razorpay Payment ID',
    'Razorpay Order ID',
    'Subscription ID',
    'Receipt Number',
    'Notes',
];
fputcsv($output, $headers);

try {
    // Query all matching records (no pagination — full export)
    $sql = "
        SELECT p.id, p.payment_date, p.amount, p.installment_number,
               p.payment_status, p.razorpay_payment_id, p.razorpay_order_id,
               p.subscription_id, p.receipt_number, p.notes,
               d.donor_name, d.phone, d.email, d.pan
        FROM sudamaseva_payments p
        LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
        WHERE {$whereClause}
        ORDER BY p.payment_date DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['payment_date'] ?? '',
            $row['donor_name'] ?? '',
            $row['phone'] ?? '',
            $row['email'] ?? '',
            $row['pan'] ? strtoupper($row['pan']) : '',
            number_format((float) ($row['amount'] ?? 0), 2, '.', ''),
            (int) ($row['installment_number'] ?? 0),
            $row['payment_status'] ?? '',
            $row['razorpay_payment_id'] ?? '',
            $row['razorpay_order_id'] ?? '',
            $row['subscription_id'] ? '#' . $row['subscription_id'] : '',
            $row['receipt_number'] ?? '',
            $row['notes'] ?? '',
        ]);
    }
} catch (PDOException $e) {
    fputcsv($output, ['Error exporting data']);
}

fclose($output);
exit;
