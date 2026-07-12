<?php
/**
 * Pujari Sevalist — CSV Export
 * 
 * Exports all paid seva donations and puja/yagya bookings in a single CSV file.
 * Accessible by pujari role with pujari_sevalist.view permission.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('pujari_sevalist.view');

$db = getDB();

// Read filter params (same as main page)
$search = trim($_GET['search'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

// ── FILENAME ──
$filename = 'pujari-sevalist_' . date('Y-m-d_H-i-s') . '.csv';

// ── HEADERS ──
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel support
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// ── CSV HEADERS ──
$headers = [
    'Record Type',
    'Date',
    'Cause',
    'Seva / Puja Type',
    'Donor Name',
    'Donor Phone',
    'Amount (INR)',
    'Beneficiary',
    'Gotra',
    'Rashi',
    'Nakshatra',
    'Occasion',
    'Instructions / Purpose',
    'Status',
];
fputcsv($output, $headers);

// ============================================================
// SECTION A: SEVA DONATIONS
// ============================================================
try {
    $sevaWhere = ["t.payment_status = 'paid'"];
    $sevaParams = [];

    if ($search !== '') {
        $sevaWhere[] = "(t.donor_name LIKE ? OR t.donor_phone LIKE ? OR t.donor_email LIKE ? OR t.notes LIKE ?)";
        $sevaParams = array_merge($sevaParams, ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
    }
    if ($startDate !== '') {
        $sevaWhere[] = "t.created_at >= ?";
        $sevaParams[] = $startDate . ' 00:00:00';
    }
    if ($endDate !== '') {
        $sevaWhere[] = "t.created_at <= ?";
        $sevaParams[] = $endDate . ' 23:59:59';
    }

    $sevaSql = "
        SELECT t.created_at, t.donor_name, t.donor_phone, t.amount, t.notes,
               COALESCE(c.title, 'General') as cause_title,
               c.category as cause_category,
               COALESCE(ms.name, s.name, c.title) as seva_name
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
        LEFT JOIN donation_cause_sevas s ON t.seva_id = s.id
        WHERE " . implode(" AND ", $sevaWhere) . "
        ORDER BY t.created_at DESC
    ";
    $sevaStmt = $db->prepare($sevaSql);
    $sevaStmt->execute($sevaParams);

    while ($row = $sevaStmt->fetch()) {
        $causeLabel = '';
        if (!empty($row['cause_category'])) {
            $causeLabel = ucfirst($row['cause_category']);
        }

        fputcsv($output, [
            'Donation',
            date('Y-m-d H:i', strtotime($row['created_at'])),
            $causeLabel,
            $row['seva_name'] ?: '',
            $row['donor_name'],
            $row['donor_phone'] ?: '',
            number_format($row['amount'], 2, '.', ''),
            '',  // Beneficiary
            '',  // Gotra
            '',  // Rashi
            '',  // Nakshatra
            '',  // Occasion
            $row['notes'] ?: '',
            'Paid',
        ]);
    }
} catch (PDOException $e) {
    // Silently skip donations export on error
}

// ============================================================
// SECTION B: PUJA & YAGYA BOOKINGS
// ============================================================
try {
    $bookWhere = ["t.payment_status = 'paid'"];
    $bookParams = [];

    if ($search !== '') {
        $bookWhere[] = "(b.person_name LIKE ? OR b.gotra LIKE ? OR b.rashi LIKE ? OR b.nakshatra LIKE ? OR b.occasion LIKE ? OR t.donor_name LIKE ? OR t.donor_phone LIKE ?)";
        $bookParams = array_merge($bookParams, ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
    }
    if ($startDate !== '') {
        $bookWhere[] = "b.puja_date >= ?";
        $bookParams[] = $startDate;
    }
    if ($endDate !== '') {
        $bookWhere[] = "b.puja_date <= ?";
        $bookParams[] = $endDate;
    }

    // Exclude completed bookings (they have their own section C)
    $bookWhere[] = "b.status != 'Completed'";

    $bookSql = "
        SELECT b.puja_date, b.puja_type, b.person_name, b.gotra, b.rashi, b.nakshatra,
               b.occasion, b.special_instructions, b.status,
               t.donor_name, t.donor_phone
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE " . implode(" AND ", $bookWhere) . "
        ORDER BY
            CASE WHEN b.status = 'Pending' THEN 0 ELSE 1 END,
            b.puja_date ASC
    ";
    $bookStmt = $db->prepare($bookSql);
    $bookStmt->execute($bookParams);

    $isYagya = function ($type) {
        return (stripos($type, 'yagya') !== false || stripos($type, 'homa') !== false);
    };

    while ($row = $bookStmt->fetch()) {
        $typeLabel = $isYagya($row['puja_type']) ? 'Yagya' : 'Puja';

        fputcsv($output, [
            $typeLabel . ' Booking',
            $row['puja_date'],
            '',
            $row['puja_type'],
            $row['donor_name'],
            $row['donor_phone'] ?: '',
            '',  // Amount
            $row['person_name'],
            $row['gotra'] ?: '',
            $row['rashi'] ?: '',
            $row['nakshatra'] ?: '',
            $row['occasion'] ?: '',
            $row['special_instructions'] ?: '',
            $row['status'],
        ]);
    }
} catch (PDOException $e) {
    // Silently skip bookings export on error
}

// ============================================================
// SECTION C: COMPLETED BOOKINGS (Last 7 Days)
// ============================================================
try {
    $completedSql = "
        SELECT b.puja_date, b.puja_type, b.person_name, b.gotra, b.rashi, b.nakshatra,
               b.occasion, b.special_instructions, b.status,
               t.donor_name, t.donor_phone
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE t.payment_status = 'paid'
          AND b.status = 'Completed'
          AND b.puja_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY b.puja_date DESC, b.id DESC
    ";
    $completedStmt = $db->prepare($completedSql);
    $completedStmt->execute();

    $completedRows = $completedStmt->fetchAll();
    if (count($completedRows) > 0) {
        // Add a separator row
        fputcsv($output, ['']);
        fputcsv($output, ['=== COMPLETED BOOKINGS (Last 7 Days) ===', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        fputcsv($output, $headers);

        $isYagya = function ($type) {
            return (stripos($type, 'yagya') !== false || stripos($type, 'homa') !== false);
        };

        foreach ($completedRows as $row) {
            $typeLabel = $isYagya($row['puja_type']) ? 'Yagya' : 'Puja';

            fputcsv($output, [
                $typeLabel . ' Booking (Completed)',
                $row['puja_date'],
                '',
                $row['puja_type'],
                $row['donor_name'],
                $row['donor_phone'] ?: '',
                '',  // Amount
                $row['person_name'],
                $row['gotra'] ?: '',
                $row['rashi'] ?: '',
                $row['nakshatra'] ?: '',
                $row['occasion'] ?: '',
                $row['special_instructions'] ?: '',
                $row['status'],
            ]);
        }
    }
} catch (PDOException $e) {
    // Silently skip completed export on error
}

fclose($output);
exit;
