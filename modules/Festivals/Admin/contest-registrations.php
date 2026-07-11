<?php
/**
 * Janmashtami Module — View Contest Registrations (Admin)
 *
 * Lists all contest registrations with payment status.
 * Supports filtering and export.
 */

$pageTitle = 'Janmashtami Contest Registrations';
$activePage = 'sudamaseva-dashboard';

require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');
require_once __DIR__ . '/../../../config.php';

include 'partials/header.php';

$db = getDB();

// Filters
$statusFilter = $_GET['status'] ?? '';
$contestFilter = $_GET['contest'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$where = [];
$params = [];

if ($statusFilter) {
    $where[] = 'r.payment_status = ?';
    $params[] = $statusFilter;
}
if ($contestFilter) {
    $where[] = 'r.contest_slug = ?';
    $params[] = $contestFilter;
}
if ($search) {
    $where[] = '(r.participant_name LIKE ? OR r.phone LIKE ? OR r.email LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM janmashtami_contest_registrations r {$whereClause}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

// Fetch page
$stmt = $db->prepare("
    SELECT r.*
    FROM janmashtami_contest_registrations r
    {$whereClause}
    ORDER BY r.registered_at DESC
    LIMIT ? OFFSET ?
");
$paramIndex = 1;
foreach ($params as $pVal) {
    $stmt->bindValue($paramIndex++, $pVal, PDO::PARAM_STR);
}
$stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
$stmt->execute();
$registrations = $stmt->fetchAll();

// Get distinct contests for filter
$contests = $db->query("SELECT DISTINCT contest_slug, contest_name FROM janmashtami_contest_registrations ORDER BY contest_name")->fetchAll();

// Stats
$stats = [
    'total' => (int) $db->query("SELECT COUNT(*) FROM janmashtami_contest_registrations")->fetchColumn(),
    'paid' => (int) $db->query("SELECT COUNT(*) FROM janmashtami_contest_registrations WHERE payment_status = 'paid'")->fetchColumn(),
    'pending' => (int) $db->query("SELECT COUNT(*) FROM janmashtami_contest_registrations WHERE payment_status = 'created'")->fetchColumn(),
    'failed' => (int) $db->query("SELECT COUNT(*) FROM janmashtami_contest_registrations WHERE payment_status = 'failed'")->fetchColumn(),
    'revenue' => (float) $db->query("SELECT COALESCE(SUM(amount), 0) FROM janmashtami_contest_registrations WHERE payment_status = 'paid'")->fetchColumn(),
];

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="janmashtami-contest-registrations-' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Participant Name', 'Age Group', 'Participant Type', 'Phone', 'Email', 'Contest', 'Amount', 'Payment Status', 'Payment ID', 'Registered At']);
    
    $exportStmt = $db->query("SELECT * FROM janmashtami_contest_registrations ORDER BY registered_at DESC");
    while ($row = $exportStmt->fetch(PDO::FETCH_ASSOC)) {
        $ageLabel = match ($row['age_group'] ?? '') {
            'group1' => 'Group 1 (Up to 6)',
            'group2' => 'Group 2 (7-10)',
            'group3' => 'Group 3 (11-15)',
            default => $row['age_group'] ?? '',
        };
        fputcsv($output, [
            $row['id'],
            $row['participant_name'],
            $ageLabel,
            $row['participant_type'] ?? '',
            $row['phone'],
            $row['email'] ?? '',
            $row['contest_name'],
            $row['amount'],
            $row['payment_status'],
            $row['razorpay_payment_id'] ?? '',
            $row['registered_at'],
        ]);
    }
    fclose($output);
    exit;
}

// Payment status badge function
function renderPaymentBadge(string $status): string {
    return match ($status) {
        'paid' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Paid</span>',
        'created' => '<span class="badge badge-info"><i class="fas fa-clock"></i> Pending</span>',
        'attempted' => '<span class="badge badge-warning"><i class="fas fa-exclamation-circle"></i> Attempted</span>',
        'failed' => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Failed</span>',
        default => '<span class="badge badge-secondary">' . htmlspecialchars(ucfirst($status)) . '</span>',
    };
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-trophy" style="margin-right:8px;"></i> Janmashtami Contest Registrations</h1>
    <p>Manage and monitor contest registrations and payments.</p>
  </div>
  <div class="admin-page-actions">
    <a href="?export=csv<?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?>" class="btn btn-success btn-sm" style="text-decoration:none;">
      <i class="fas fa-download"></i> Export CSV
    </a>
    <a href="../festivals/grand-festivals/janmashtami/" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);" target="_blank">
      <i class="fas fa-external-link-alt"></i> View Page
    </a>
  </div>
</div>

<!-- Stats Cards -->
<div class="admin-stats-grid" style="grid-template-columns: repeat(5, 1fr); gap:var(--space-md); margin-bottom:var(--space-lg);">
  <div class="admin-stat-card" style="margin-bottom:0;">
    <div class="admin-stat-info">
      <h3>Total</h3>
      <div class="admin-stat-value"><?php echo $stats['total']; ?></div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(200,107,31,0.1); color:var(--primary);"><i class="fas fa-users"></i></div>
  </div>
  <div class="admin-stat-card" style="margin-bottom:0;">
    <div class="admin-stat-info">
      <h3>Paid</h3>
      <div class="admin-stat-value" style="color:#2e7d32;"><?php echo $stats['paid']; ?></div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(46,125,50,0.1); color:#2e7d32;"><i class="fas fa-check-circle"></i></div>
  </div>
  <div class="admin-stat-card" style="margin-bottom:0;">
    <div class="admin-stat-info">
      <h3>Pending</h3>
      <div class="admin-stat-value" style="color:#1565c0;"><?php echo $stats['pending']; ?></div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(21,101,192,0.1); color:#1565c0;"><i class="fas fa-clock"></i></div>
  </div>
  <div class="admin-stat-card" style="margin-bottom:0;">
    <div class="admin-stat-info">
      <h3>Failed</h3>
      <div class="admin-stat-value" style="color:#d32f2f;"><?php echo $stats['failed']; ?></div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(211,47,47,0.1); color:#d32f2f;"><i class="fas fa-times-circle"></i></div>
  </div>
  <div class="admin-stat-card" style="margin-bottom:0;">
    <div class="admin-stat-info">
      <h3>Revenue</h3>
      <div class="admin-stat-value" style="color:var(--maroon);">₹<?php echo number_format($stats['revenue']); ?></div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(123,30,30,0.1); color:var(--maroon);"><i class="fas fa-indian-rupee-sign"></i></div>
  </div>
</div>

<!-- Filters -->
<div class="admin-card" style="margin-bottom:var(--space-lg);">
  <div class="admin-card-body">
    <form method="GET" style="display:flex; gap:var(--space-md); align-items:flex-end; flex-wrap:wrap;">
      <div class="form-group" style="margin-bottom:0; min-width:150px;">
        <label for="status">Payment Status</label>
        <select name="status" id="status" class="form-control">
          <option value="">All Statuses</option>
          <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
          <option value="created" <?php echo $statusFilter === 'created' ? 'selected' : ''; ?>>Pending</option>
          <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0; min-width:180px;">
        <label for="contest">Contest</label>
        <select name="contest" id="contest" class="form-control">
          <option value="">All Contests</option>
          <?php foreach ($contests as $c): ?>
            <option value="<?php echo htmlspecialchars($c['contest_slug']); ?>" <?php echo $contestFilter === $c['contest_slug'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($c['contest_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0; min-width:200px; flex:1;">
        <label for="search">Search</label>
        <input type="text" name="search" id="search" class="form-control" placeholder="Name, phone, or email..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      <button type="submit" class="btn btn-primary" style="padding:8px 20px;">Filter</button>
      <a href="?" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">Clear</a>
    </form>
  </div>
</div>

<!-- Registrations Table -->
<div class="admin-card">
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Participant</th>
            <th>Age Group</th>
            <th>Type</th>
            <th>Contact</th>
            <th>Contest</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Payment ID</th>
            <th>Registered</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($registrations)): ?>
            <tr>
              <td colspan="10" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">
                <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:var(--space-sm);"></i>
                No registrations found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($registrations as $reg): ?>
              <tr>
                <td style="font-family:monospace; font-size:12px;">#<?php echo $reg['id']; ?></td>
                <td style="font-weight:600;"><?php echo htmlspecialchars($reg['participant_name']); ?></td>
                <td><?php
                  $ageLabel = match ($reg['age_group'] ?? '') {
                    'group1' => 'Group 1<br><span style="font-size:11px;color:var(--text-light)">Up to 6 yrs</span>',
                    'group2' => 'Group 2<br><span style="font-size:11px;color:var(--text-light)">7-10 yrs</span>',
                    'group3' => 'Group 3<br><span style="font-size:11px;color:var(--text-light)">11-15 yrs</span>',
                    default => htmlspecialchars($reg['age_group'] ?? ''),
                  };
                  echo $ageLabel;
                ?></td>
                <td style="font-size:12px;">
                  <?php
                  $typeLabel = match ($reg['participant_type'] ?? '') {
                    'online' => '<span class="badge badge-info"><i class="fas fa-video"></i> Online</span>',
                    'offline' => '<span class="badge badge-success"><i class="fas fa-map-marker-alt"></i> Offline</span>',
                    default => htmlspecialchars($reg['participant_type'] ?? '—'),
                  };
                  echo $typeLabel;
                  ?>
                </td>
                <td style="font-size:12px;">
                  <div><?php echo htmlspecialchars($reg['phone']); ?></div>
                  <?php if ($reg['email']): ?>
                    <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($reg['email']); ?></div>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-info"><?php echo htmlspecialchars($reg['contest_name']); ?></span></td>
                <td style="font-weight:600; color:var(--maroon);">₹<?php echo number_format((float) $reg['amount']); ?></td>
                <td><?php echo renderPaymentBadge($reg['payment_status']); ?></td>
                <td style="font-size:10px; font-family:monospace; color:var(--text-light); max-width:120px; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($reg['razorpay_payment_id'] ?? ''); ?>">
                  <?php echo htmlspecialchars($reg['razorpay_payment_id'] ?: '—'); ?>
                </td>
                <td style="font-size:11px; color:var(--text-light); white-space:nowrap;">
                  <?php echo date('d M Y', strtotime($reg['registered_at'])); ?>
                  <div style="font-size:10px;"><?php echo date('h:i A', strtotime($reg['registered_at'])); ?></div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-top:var(--space-lg); font-size:13px; color:var(--text-light);">
  <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $total; ?> total)</span>
  <div style="display:flex; gap:var(--space-sm);">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>&status=<?php echo urlencode($statusFilter); ?>&contest=<?php echo urlencode($contestFilter); ?>&search=<?php echo urlencode($search); ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:6px 12px; border:1px solid var(--border); border-radius:var(--radius-sm);">&larr; Previous</a>
    <?php endif; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?php echo $page + 1; ?>&status=<?php echo urlencode($statusFilter); ?>&contest=<?php echo urlencode($contestFilter); ?>&search=<?php echo urlencode($search); ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:6px 12px; border:1px solid var(--border); border-radius:var(--radius-sm);">Next &rarr;</a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
