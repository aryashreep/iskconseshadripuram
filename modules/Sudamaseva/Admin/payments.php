<?php
/**
 * Sudamaseva Module — Payments List (Admin)
 *
 * Paginated list with date range and status filters for subscription installments.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

use Isjm\Modules\Sudamaseva\SudamasevaService;

$service = new SudamasevaService();
$repo = new \Isjm\Modules\Sudamaseva\SudamasevaRepository();
$error = '';
$message = '';

// Handle delete action (must be executed before any HTML output for redirect to work)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('sudamaseva.delete')) {
        $error = 'You do not have permission to delete payments.';
    } else {
        $deleteId = intval($_GET['id']);
        try {
            if ($service->deletePayment($deleteId)) {
                $message = 'Payment deleted successfully.';
                header('Location: ' . BASE_URL . 'admin/sudamaseva-payments?message=' . urlencode($message));
                exit;
            } else {
                $error = 'Failed to delete payment. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error deleting payment: ' . $e->getMessage();
        }
    }
}

$message = trim($_GET['message'] ?? '');

$pageTitle = 'Sudamaseva Payments';
$activePage = 'sudamaseva-payments';
include 'partials/header.php';

$status = trim($_GET['status'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$search = trim($_GET['search'] ?? '');

// Build manual query for payments with filtering (since repo's getPaymentsByDateRange is date-only)
$db = \getDB();
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

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

try {
    // Count total
    $countStmt = $db->prepare("SELECT COUNT(*) FROM sudamaseva_payments p LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id WHERE {$whereClause}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();
    $pages = max(1, (int) ceil($total / $perPage));

    // Fetch page
    $sql = "
        SELECT p.*, d.donor_name, d.phone, d.email
        FROM sudamaseva_payments p
        LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
        WHERE {$whereClause}
        ORDER BY p.payment_date DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $db->prepare($sql);
    $paramIndex = 1;
    foreach ($params as $pVal) {
        $stmt->bindValue($paramIndex++, $pVal, PDO::PARAM_STR);
    }
    $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $payments = $stmt->fetchAll();

    // Stats from repo
    $overallStats = $repo->getPaymentStats();

    // Proper aggregate query on the full filtered dataset (not just the current page)
    $sumSql = "SELECT SUM(CASE WHEN p.payment_status = 'paid' THEN p.amount ELSE 0 END) as total_sum, SUM(CASE WHEN p.payment_status = 'paid' THEN 1 ELSE 0 END) as total_count FROM sudamaseva_payments p LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id WHERE {$whereClause}";
    $sumStmt = $db->prepare($sumSql);
    $sumStmt->execute($params);
    $sumRow = $sumStmt->fetch();
    $filteredPaidSum = (float) ($sumRow['total_sum'] ?? 0);
    $filteredPaidCount = (int) ($sumRow['total_count'] ?? 0);
} catch (Exception $e) {
    $error = 'Failed to load payments: ' . $e->getMessage();
    $payments = [];
    $total = 0;
    $pages = 1;
    $overallStats = [];
    $filteredPaidSum = 0;
    $filteredPaidCount = 0;
}

$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Payments</h1>
    <p>View all subscription installment payments with filters, search, and export.</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('sudamaseva.export')): ?>
      <a href="admin/sudamaseva-export-payments?<?php echo $queryString; ?>" class="btn btn-primary btn-sm" style="background-color: var(--maroon); text-decoration:none;">
        <i class="fas fa-file-csv"></i> Export to CSV
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<?php if ($message): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<!-- Summary Cards -->
<div style="display:flex; gap: var(--space-md); margin-bottom: var(--space-lg); flex-wrap:wrap;">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:200px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-funnel-dollar" style="font-size:24px; color:var(--primary);"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Filtered Revenue</div>
      <strong style="font-size:18px;"><?php echo $service->formatAmount($filteredPaidSum); ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-clipboard-check" style="font-size:24px; color:var(--accent);"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Paid in Filter</div>
      <strong style="font-size:18px;"><?php echo $filteredPaidCount; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-list" style="font-size:24px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Total Records</div>
      <strong style="font-size:18px;"><?php echo $total; ?></strong>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filter Payments</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/sudamaseva-payments" method="GET" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-md);">
        <div class="form-group" style="margin-bottom:0;">
          <label for="search">Search</label>
          <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Phone, Payment ID...">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label for="from">From Date</label>
          <input type="date" id="from" name="from" class="form-control" value="<?php echo htmlspecialchars($from); ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label for="to">To Date</label>
          <input type="date" id="to" name="to" class="form-control" value="<?php echo htmlspecialchars($to); ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label for="status">Payment Status</label>
          <select id="status" name="status" class="form-control">
            <option value="">-- All --</option>
            <option value="paid" <?php echo $status === 'paid' ? 'selected' : ''; ?>>Paid</option>
            <option value="created" <?php echo $status === 'created' ? 'selected' : ''; ?>>Created</option>
            <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
            <option value="attempted" <?php echo $status === 'attempted' ? 'selected' : ''; ?>>Attempted</option>
          </select>
        </div>
      </div>
      <div style="display:flex; justify-content: flex-end; gap:8px;">
        <a href="admin/sudamaseva-payments" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Apply</button>
      </div>
    </form>
  </div>
</div>

<!-- Payments Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Payment Records (Showing <?php echo count($payments); ?> of <?php echo $total; ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 900px;">
        <thead>
          <tr>
            <th>Date</th>
            <th>Donor</th>
            <th>Amount</th>
            <th>Inst.#</th>
            <th>Subscription</th>
            <th>Status</th>
            <th>Receipt</th>
            <th>Payment ID</th>
            <?php if (hasPermission('sudamaseva.delete')): ?>
              <th style="text-align:center;">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="<?php echo hasPermission('sudamaseva.delete') ? 9 : 8; ?>" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No payments found matching the filters.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($payments as $p): ?>
              <tr>
                <td style="font-size:12px; color:var(--text-light); white-space:nowrap;"><?php echo $service->formatDate($p['payment_date'] ?? null, 'd M Y'); ?></td>
                <td>
                  <a href="admin/sudamaseva-donor-detail?id=<?php echo htmlspecialchars($p['donor_id'] ?? ''); ?>" style="text-decoration:none; color:var(--primary); font-weight:600;">
                    <?php echo htmlspecialchars($p['donor_name'] ?? '—'); ?>
                  </a>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($p['phone'] ?? ''); ?></div>
                </td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($p['amount'] ?? 0)); ?></td>
                <td style="text-align:center;">#<?php echo (int) ($p['installment_number'] ?? 0); ?></td>
                <td>
                  <?php if (!empty($p['subscription_id'])): ?>
                    <a href="admin/sudamaseva-subscriptions?search=<?php echo $p['subscription_id']; ?>" style="font-family:monospace; font-size:11px; color:var(--primary);">#<?php echo $p['subscription_id']; ?></a>
                  <?php else: ?>
                    <span style="color:var(--text-light);">—</span>
                  <?php endif; ?>
                </td>
                <td><?php echo $service->renderStatusBadge($p['payment_status'] ?? 'created', 'payment'); ?></td>
                <td style="font-family:monospace; font-size:11px; white-space:nowrap;">
                  <?php if (!empty($p['receipt_number'])): ?>
                    <a href="admin/sudamaseva-receipt-print?payment_id=<?php echo $p['id']; ?>" target="_blank" style="color:var(--maroon); text-decoration:none;" title="Print Receipt">
                      <i class="fas fa-print"></i> <?php echo htmlspecialchars($p['receipt_number']); ?>
                    </a>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td style="font-size:10px; font-family:monospace; color:var(--text-light); max-width:120px; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($p['razorpay_payment_id'] ?? ''); ?>">
                  <?php echo htmlspecialchars($p['razorpay_payment_id'] ?: '—'); ?>
                </td>
                <?php if (hasPermission('sudamaseva.delete')): ?>
                  <td style="text-align:center;">
                    <a href="admin/sudamaseva-payments?action=delete&id=<?php echo $p['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" 
                       class="btn-sm-action btn-delete" 
                       title="Delete Payment" 
                       onclick="return confirm('Are you sure you want to delete this payment record? This will also delete any generated tax receipts and update the subscription installment count if applicable.');" 
                       style="padding: 6px 8px; border-radius: 4px; background:#dc3545; color:white; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; text-decoration:none;">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pagination -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/admin/donations.css') ?>">
<?php if ($pages > 1): ?>
  <div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:var(--space-xl); margin-bottom:var(--space-2xl);">
    <?php if ($page > 1): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-payments?page=<?php echo ($page - 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link"><i class="fas fa-chevron-left" style="font-size:10px; margin-right:4px;"></i> Prev</a>
    <?php endif; ?>

    <?php
      $maxVisible = 10;
      $startPage = max(1, $page - 5);
      $endPage = min($pages, $startPage + $maxVisible - 1);
      if ($endPage - $startPage + 1 < $maxVisible) {
          $startPage = max(1, $endPage - $maxVisible + 1);
      }
      
      if ($startPage > 1):
    ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-payments?page=1<?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">1</a>
      <?php if ($startPage > 2): ?>
        <span style="color:var(--text-light); padding: 0 4px;">...</span>
      <?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-payments?page=<?php echo $i; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link<?php echo $i === $page ? ' active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($endPage < $pages): ?>
      <?php if ($endPage < $pages - 1): ?>
        <span style="color:var(--text-light); padding: 0 4px;">...</span>
      <?php endif; ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-payments?page=<?php echo $pages; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link"><?php echo $pages; ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-payments?page=<?php echo ($page + 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">Next <i class="fas fa-chevron-right" style="font-size:10px; margin-left:4px;"></i></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
