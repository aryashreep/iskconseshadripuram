<?php
/**
 * Sudamaseva Module — Subscriptions List (Admin)
 *
 * Paginated list with status filter and quick view of progress.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

$pageTitle = 'Sudamaseva Subscriptions';
$activePage = 'sudamaseva-subscriptions';
include 'partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaService;

$service = new SudamasevaService();
$error = '';

$status = trim($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;

try {
    $result = $service->getSubscriptions($status ?: null, null, $page, $perPage);
    $subscriptions = $result['subscriptions'];
    $total = $result['total'];
    $pages = $result['pages'];
    $subStats = $service->getSubscriptionStats();
} catch (Exception $e) {
    $error = 'Failed to load subscriptions: ' . $e->getMessage();
    $subscriptions = [];
    $total = 0;
    $pages = 1;
    $subStats = [];
}

$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Subscriptions</h1>
    <p>Manage recurring subscription donation plans. Track active, completed, and cancelled subscriptions.</p>
  </div>
  <div class="admin-page-actions">
    <span style="font-size:12px; color:var(--text-light);">
      <i class="fas fa-database"></i> <?php echo $total; ?> total subscriptions
    </span>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- Summary Cards -->
<div style="display:flex; gap: var(--space-md); margin-bottom: var(--space-lg); flex-wrap:wrap;">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:140px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-sync" style="font-size:24px; color:#2e7d32;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Active</div>
      <strong style="font-size:18px; color:#2e7d32;"><?php echo $subStats['active'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:140px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-check-double" style="font-size:24px; color:#0b5ed7;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Completed</div>
      <strong style="font-size:18px; color:#0b5ed7;"><?php echo $subStats['completed'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:140px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-times-circle" style="font-size:24px; color:#c62828;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Cancelled</div>
      <strong style="font-size:18px; color:#c62828;"><?php echo $subStats['cancelled'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:180px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-indian-rupee-sign" style="font-size:24px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Monthly MRR</div>
      <strong style="font-size:18px; color:var(--maroon);"><?php echo $service->formatAmount($subStats['total_monthly_amount'] ?? 0); ?></strong>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filter Subscriptions</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/sudamaseva-subscriptions" method="GET" style="display:flex; gap:var(--space-md); align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0; min-width:200px;">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control">
          <option value="">-- All Statuses --</option>
          <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
          <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          <option value="paused" <?php echo $status === 'paused' ? 'selected' : ''; ?>>Paused</option>
        </select>
      </div>
      <div style="display:flex; gap:8px;">
        <a href="admin/sudamaseva-subscriptions" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Filter</button>
      </div>
    </form>
  </div>
</div>

<!-- Subscriptions Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Subscription Records (Showing <?php echo count($subscriptions); ?> of <?php echo $total; ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 900px;">
        <thead>
          <tr>
            <th>ID</th>
            <th>Cycle</th>
            <th>Donor</th>
            <th>Monthly Amount</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>Installments</th>
            <th>Progress</th>
            <th>Source</th>
            <th style="text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($subscriptions)): ?>
            <tr>
              <td colspan="10" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No subscriptions found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($subscriptions as $s):
              $progress = $service->calculateSubscriptionProgress($s);
              $totalInst = (int) ($s['total_installments'] ?? 0);
              $paidInst = (int) ($s['installments_paid'] ?? 0);
              $progressLabel = $totalInst > 0 ? "{$paidInst} / {$totalInst}" : "{$paidInst}";
            ?>
              <tr>
                <td style="font-family:monospace; font-size:12px;">#<?php echo $s['id']; ?></td>
                <td style="text-align:center;">
                  <span class="badge <?php echo ((int)($s['cycle'] ?? 1)) > 1 ? 'badge-success' : 'badge-secondary'; ?>">
                    #<?php echo (int) ($s['cycle'] ?? 1); ?>
                  </span>
                </td>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($s['donor_name'] ?? '—'); ?></strong>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($s['phone'] ?? ''); ?></div>
                </td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($s['amount'] ?? 0)); ?></td>
                <td><?php echo $service->renderStatusBadge($s['status'] ?? 'unknown'); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo $service->formatDate($s['start_date'] ?? null, 'd M Y'); ?></td>
                <td style="text-align:center;">
                  <span title="Paid: <?php echo $paidInst; ?> / Total: <?php echo $totalInst > 0 ? $totalInst : '∞'; ?>">
                    <?php echo $progressLabel; ?>
                  </span>
                </td>
                <td style="min-width:120px;">
                  <?php if ($totalInst > 0): ?>
                    <div style="display:flex; align-items:center; gap:6px;">
                      <div style="flex:1; height:6px; background:#eee; border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:<?php echo $progress; ?>%; background:<?php echo $progress >= 100 ? '#2e7d32' : ($progress >= 50 ? 'var(--accent)' : 'var(--primary)'); ?>; border-radius:3px; transition:width 0.3s;"></div>
                      </div>
                      <span style="font-size:10px; color:var(--text-light); font-weight:600;"><?php echo $progress; ?>%</span>
                    </div>
                  <?php else: ?>
                    <span style="font-size:11px; color:var(--text-light); font-style:italic;">Open-ended</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge badge-info"><?php echo htmlspecialchars($s['source'] ?? '—'); ?></span>
                </td>
                <td style="text-align:center; white-space:nowrap;">
                  <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $s['id']; ?>" class="btn-sm-action" title="Record Offline Payment" style="color:var(--maroon);">
                    <i class="fas fa-hand-holding-usd"></i>
                  </a>
                  <a href="admin/sudamaseva-donor-detail?id=<?php echo $s['donor_id']; ?>" class="btn-sm-action" title="View Donor Profile" style="color:var(--primary);"><i class="fas fa-user"></i></a>
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
<link rel="stylesheet" href="<?= asset('assets/css/pages/admin/donations.css') ?>">
<?php if ($pages > 1): ?>
  <div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:var(--space-xl); margin-bottom:var(--space-2xl);">
    <?php if ($page > 1): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-subscriptions?page=<?php echo ($page - 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link"><i class="fas fa-chevron-left" style="font-size:10px; margin-right:4px;"></i> Prev</a>
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
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-subscriptions?page=1<?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">1</a>
      <?php if ($startPage > 2): ?>
        <span style="color:var(--text-light); padding: 0 4px;">...</span>
      <?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-subscriptions?page=<?php echo $i; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link<?php echo $i === $page ? ' active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($endPage < $pages): ?>
      <?php if ($endPage < $pages - 1): ?>
        <span style="color:var(--text-light); padding: 0 4px;">...</span>
      <?php endif; ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-subscriptions?page=<?php echo $pages; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link"><?php echo $pages; ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-subscriptions?page=<?php echo ($page + 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">Next <i class="fas fa-chevron-right" style="font-size:10px; margin-left:4px;"></i></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
