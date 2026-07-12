<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('donations.view');

$pageTitle = 'Donation Logs';
$activePage = 'donations';
include 'partials/header.php';

$db = getDB();
$error = '';

// Load Causes for dropdown filter
$causesFilterList = [];
try {
    $stmt = $db->query("SELECT id, title FROM donation_causes ORDER BY title ASC");
    $causesFilterList = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Failed to load filter parameters. Please try again.';
}

// Read parameters
$search = trim($_GET['search'] ?? '');
$causeId = isset($_GET['cause_id']) && $_GET['cause_id'] !== '' ? intval($_GET['cause_id']) : '';
$status = trim($_GET['status'] ?? 'paid');
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

try {
    // 1. Calculate Sum metrics of PAID transactions inside this filtered set
    $sumSql = "SELECT SUM(t.amount) as total_sum, COUNT(*) as total_count FROM donation_transactions t WHERE {$whereClause} AND t.payment_status = 'paid'";
    $sumStmt = $db->prepare($sumSql);
    $sumStmt->execute($params);
    $sumRow = $sumStmt->fetch();
    $filteredPaidSum = (float)$sumRow['total_sum'];
    $filteredPaidCount = (int)$sumRow['total_count'];

    // 2. Count total rows overall in filtered set (including failed/created)
    $countSql = "SELECT COUNT(*) FROM donation_transactions t WHERE {$whereClause}";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRows = (int)$countStmt->fetchColumn();

    // 3. Pagination setup
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perPage = 25;
    $totalPages = max(1, ceil($totalRows / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    // 4. Fetch the records
    $sql = "
        SELECT t.*, c.title as cause_title,
               COALESCE(ms.name, s.name) as seva_name
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
        LEFT JOIN donation_cause_sevas s ON t.seva_id = s.id
        WHERE {$whereClause}
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($sql);
    
    // Bind parameters
    $paramIdx = 1;
    foreach ($params as $pVal) {
        $stmt->bindValue($paramIdx++, $pVal);
    }
    $stmt->bindValue($paramIdx++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($paramIdx++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $transactions = $stmt->fetchAll();

} catch (PDOException $e) {
    $transactions = [];
    $totalRows = 0;
    $filteredPaidSum = 0;
    $filteredPaidCount = 0;
    $totalPages = 1;
    $error = 'Query execution failed. Please try again.';
}

// Build URL query string for export & paging links
$queryParams = $_GET;
unset($queryParams['page']); // page will be appended separately
$queryString = http_build_query($queryParams);

// Preserve current list URL for return navigation from detail page
$returnUrl = urlencode($_SERVER['REQUEST_URI'] ?? 'admin/donations');
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Donation Logs</h1>
    <p>View transaction reports, export spreadsheets, and verify payment identifiers.</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('donations.export')): ?>
      <a href="admin/export-donations?<?php echo $queryString; ?>" class="btn btn-primary" style="background-color: var(--maroon); border: none; text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-file-csv"></i> Export Filtered CSV
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- Filtering Bar -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filter & Search</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/donations" method="GET" style="display:flex; flex-direction:column; gap:var(--space-md);">
      
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-md);">
        
        <div class="form-group" style="margin-bottom:0;">
          <label for="search">Text Search</label>
          <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Email, Phone, Order ID...">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="cause_id">Cause / Festival</label>
          <select id="cause_id" name="cause_id" class="form-control">
            <option value="">-- All Causes --</option>
            <?php foreach ($causesFilterList as $c): ?>
              <option value="<?php echo $c['id']; ?>" <?php echo $causeId == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['title']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="status">Payment Status</label>
          <select id="status" name="status" class="form-control">
            <option value="">-- All Statuses --</option>
            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="attempted" <?php echo $status === 'attempted' ? 'selected' : ''; ?>>Attempted</option>
            <option value="created" <?php echo $status === 'created' ? 'selected' : ''; ?>>Created</option>
            <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
            <option value="refunded" <?php echo $status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="start_date">From Date</label>
          <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="end_date">To Date</label>
          <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
        </div>

      </div>

      <div style="display:flex; justify-content: flex-end; gap: 8px;">
        <a href="admin/donations" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px; text-align:center;">Clear Filters</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Apply Filters</button>
      </div>

    </form>
  </div>
</div>

<!-- Filter Summary Cards -->
<div style="display:flex; gap: var(--space-md); margin-bottom: var(--space-lg); flex-wrap:wrap;">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap: var(--space-md); min-width: 250px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-funnel-dollar" style="font-size:24px; color:var(--primary);"></i>
    <div>
      <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Filtered Revenue (Paid)</div>
      <strong style="font-size:18px; color:var(--dark);">₹<?php echo number_format($filteredPaidSum, 2); ?></strong>
    </div>
  </div>
  
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap: var(--space-md); min-width: 200px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-clipboard-check" style="font-size:24px; color:var(--accent);"></i>
    <div>
      <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Filtered Paid Count</div>
      <strong style="font-size:18px; color:var(--dark);"><?php echo $filteredPaidCount; ?></strong>
    </div>
  </div>

  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap: var(--space-md); min-width: 200px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-list" style="font-size:24px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Total Matching Logs</div>
      <strong style="font-size:18px; color:var(--dark);"><?php echo $totalRows; ?></strong>
    </div>
  </div>
</div>

<!-- Transactions Table Card -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Transaction Records (Showing <?php echo count($transactions); ?> of <?php echo $totalRows; ?> logs)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 900px;">
        <thead>
          <tr>
            <th>Date</th>
            <th>Donor Details</th>
            <th>PAN</th>
            <th>Cause & Seva Name</th>
            <th>Amount</th>
            <th>Razorpay Identifiers</th>
            <th>Status</th>
            <th style="width:80px; text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($transactions)): ?>
            <tr>
              <td colspan="8" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No transaction logs found matching the selected filters.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($transactions as $t): 
              $badgeClass = 'badge-secondary';
              if ($t['payment_status'] === 'paid') $badgeClass = 'badge-success';
              elseif ($t['payment_status'] === 'failed') $badgeClass = 'badge-danger';
              elseif ($t['payment_status'] === 'attempted') $badgeClass = 'badge-warning';
              elseif ($t['payment_status'] === 'refunded') $badgeClass = 'badge-info';
              $detailHref = 'admin/donation-detail?id=' . $t['id'] . '&return=' . $returnUrl;
            ?>
              <tr class="donation-row" data-href="<?php echo $detailHref; ?>">
                <td style="font-size:12px; color:var(--text-light); white-space:nowrap;"><?php echo date('M d, Y H:i:s', strtotime($t['created_at'])); ?></td>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($t['donor_name']); ?></strong>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($t['donor_email']); ?></div>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($t['donor_phone']); ?></div>
                </td>
                <td style="font-family:monospace; font-size:12px; text-transform:uppercase; color:var(--text-dark);"><?php echo htmlspecialchars($t['pan_number'] ?: '-'); ?></td>
                <td>
                  <strong style="color:var(--text); font-size:13px;"><?php echo htmlspecialchars($t['cause_title'] ?: 'General Donation'); ?></strong>
                  <?php if (!empty($t['seva_name'])): ?>
                    <div style="font-size:11px; color:var(--primary-dark); font-weight:500;"><i class="fas fa-ribbon" style="font-size:10px;"></i> <?php echo htmlspecialchars($t['seva_name']); ?></div>
                  <?php endif; ?>
                  <?php if (!empty($t['notes'])): ?>
                    <div style="font-size:11px; color:var(--text-light); font-style:italic; background:var(--light); padding:4px; border-radius:4px; margin-top:4px;">Notes: <?php echo htmlspecialchars($t['notes']); ?></div>
                  <?php endif; ?>
                </td>
                <td style="font-weight:600; color:var(--maroon); font-size:14px; white-space:nowrap;">₹<?php echo number_format($t['amount'], 2); ?></td>
                <td>
                  <div style="font-size:11px; color:var(--text-light); font-family:monospace;">Order: <?php echo htmlspecialchars($t['razorpay_order_id'] ?: '-'); ?></div>
                  <div style="font-size:11px; color:var(--text-light); font-family:monospace;">Pay: <?php echo htmlspecialchars($t['razorpay_payment_id'] ?: '-'); ?></div>
                </td>
                <td>
                  <span class="badge <?php echo $badgeClass; ?>"><?php echo $t['payment_status']; ?></span>
                </td>
                <td style="text-align:center;">
                  <a href="admin/donation-detail?id=<?php echo $t['id']; ?>&return=<?php echo $returnUrl; ?>" class="btn btn-outline-dark btn-xs" style="text-decoration:none; padding:5px 10px; font-size:11px; border:1px solid var(--border); border-radius:var(--radius-sm); font-weight:600; color:var(--text); display:inline-flex; align-items:center; gap:4px;">
                    <i class="fas fa-eye" style="font-size:10px;"></i> View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pagination Footer -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/admin/donations.css') ?>">
<?php if ($totalPages > 1): ?>
  <div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:var(--space-xl); margin-bottom:var(--space-2xl);">
    
    <?php if ($page > 1): ?>
      <a href="admin/donations?page=<?php echo ($page - 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">
        <i class="fas fa-chevron-left" style="font-size:10px; margin-right:4px;"></i> Prev
      </a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): 
      $isActive = ($i === $page);
    ?>
      <a href="admin/donations?page=<?php echo $i; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>"
         class="page-link<?php echo $isActive ? ' active' : ''; ?>">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <a href="admin/donations?page=<?php echo ($page + 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">
        Next <i class="fas fa-chevron-right" style="font-size:10px; margin-left:4px;"></i>
      </a>
    <?php endif; ?>

  </div>
<?php endif; ?>

<!-- Row-click navigation: clicking a transaction row opens its detail page -->
<style>
  .admin-table tbody tr.donation-row {
    cursor: pointer;
    transition: background-color 0.12s ease;
  }
  .admin-table tbody tr.donation-row:hover {
    background-color: #fffde6;
  }
  .admin-table tbody tr.donation-row:active {
    background-color: #fff3c4;
  }
</style>
<script>
(function() {
  // Make each donation row clickable, ignoring clicks on links/buttons inside
  var rows = document.querySelectorAll('.admin-table tbody tr.donation-row');
  rows.forEach(function(row) {
    var href = row.getAttribute('data-href');
    if (!href) return;
    row.addEventListener('click', function(e) {
      // Don't navigate if the user clicked an interactive element
      var target = e.target;
      while (target && target !== row) {
        if (target.tagName === 'A' || target.tagName === 'BUTTON' || target.tagName === 'INPUT' || target.tagName === 'SELECT') {
          return;
        }
        target = target.parentElement;
      }
      window.location.href = href;
    });
  });
})();
</script>

<?php include 'partials/footer.php'; ?>
