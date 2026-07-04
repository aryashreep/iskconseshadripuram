<?php
/**
 * Export Donations log as CSV file
 */
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin', 'treasurer']);

$db = getDB();

// Read parameters
$search = trim($_GET['search'] ?? '');
$causeId = isset($_GET['cause_id']) && $_GET['cause_id'] !== '' ? intval($_GET['cause_id']) : '';
$status = trim($_GET['status'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(t.donor_name LIKE ? OR t.donor_email LIKE ? OR t.donor_phone LIKE ? OR t.razorpay_order_id = ? OR t.razorpay_payment_id = ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = $search;
    $params[] = $search;
}

if ($causeId !== '') {
    $where[] = "t.cause_id = ?";
    $params[] = $causeId;
}

if ($status !== '') {
    $where[] = "t.payment_status = ?";
    $params[] = $status;
}

if ($startDate !== '') {
    $where[] = "t.created_at >= ?";
    $params[] = $startDate . ' 00:00:00';
}

if ($endDate !== '') {
    $where[] = "t.created_at <= ?";
    $params[] = $endDate . ' 23:59:59';
}

$whereClause = implode(" AND ", $where);

// Filename construction
$filename = 'isjm_donations_export_' . date('Y-m-d_H-i-s') . '.csv';

// Output headers to trigger download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create stream resource
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel support
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers
$headers = [
    'Transaction ID', 
    'Date & Time', 
    'Donor Name', 
    'Donor Email', 
    'Donor Phone', 
    'PAN Number', 
    'Cause / Festival', 
    'Seva Name', 
    'Amount (INR)', 
    'Razorpay Order ID', 
    'Razorpay Payment ID', 
    'Payment Status', 
    'Notes'
];
fputcsv($output, $headers);

try {
    // Query records
    $sql = "
        SELECT t.id, t.created_at, t.donor_name, t.donor_email, t.donor_phone, t.pan_number, 
               c.title as cause_title, s.name as seva_name, t.amount, t.razorpay_order_id, 
               t.razorpay_payment_id, t.payment_status, t.notes
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN donation_cause_sevas s ON t.seva_id = s.id
        WHERE {$whereClause}
        ORDER BY t.created_at DESC
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['created_at'],
            $row['donor_name'],
            $row['donor_email'],
            $row['donor_phone'],
            $row['pan_number'] ?: '',
            $row['cause_title'] ?: 'General Donation',
            $row['seva_name'] ?: '',
            number_format($row['amount'], 2, '.', ''),
            $row['razorpay_order_id'] ?: '',
            $row['razorpay_payment_id'] ?: '',
            $row['payment_status'],
            $row['notes'] ?: ''
        ]);
    }
} catch (PDOException $e) {
    fputcsv($output, ['Error exporting data']);
}

fclose($output);
exit;
