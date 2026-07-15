<?php
/**
 * Sudamaseva Module — Completed Subscriptions List (Admin)
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

$pageTitle = 'Completed Sudamaseva Subscriptions';
$activePage = 'sudamaseva-completed-subscriptions';
include 'partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaService;

$service = new SudamasevaService();
$error = '';

$search = trim($_GET['search'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');

$filterApplied = isset($_GET['filter_applied']);
if ($filterApplied) {
    $hideOrphans = isset($_GET['hide_orphans']) && $_GET['hide_orphans'] === '1';
} else {
    $hideOrphans = true; // default on first load
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;

try {
    $result = $service->getCompletedSubscriptions($page, $perPage, $search ?: null, $from ?: null, $to ?: null, $hideOrphans);
    $subscriptions = $result['subscriptions'];
    $total = $result['total'];
    $pages = $result['pages'];
} catch (Exception $e) {
    $error = 'Failed to load completed subscriptions: ' . $e->getMessage();
    $subscriptions = [];
    $total = 0;
    $pages = 1;
}

$queryParams = $_GET;
$queryParams['filter_applied'] = '1';
$queryParams['hide_orphans'] = $hideOrphans ? '1' : '0';
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Completed Subscriptions</h1>
    <p>View all donors who completed their chosen subscription duration (6, 12, or 24 months).</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('sudamaseva.export')): ?>
      <a href="admin/sudamaseva-export-completed-subscriptions?<?php echo $queryString; ?>" class="btn btn-primary btn-sm" style="background-color: var(--maroon); text-decoration:none;">
        <i class="fas fa-file-csv"></i> Export Completed
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- Filter Card -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filter Completed Subscriptions</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/sudamaseva-completed-subscriptions" method="GET" style="display:flex; flex-wrap:wrap; gap:var(--space-md); align-items:flex-end;">
      <input type="hidden" name="filter_applied" value="1">
      
      <div class="form-group" style="margin-bottom:0; flex:1; min-width:200px;">
        <label for="search">Search</label>
        <input type="text" id="search" name="search" class="form-control" placeholder="Name, Phone, Email, ID..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      
      <div class="form-group" style="margin-bottom:0; width:150px;">
        <label for="from">Completed From</label>
        <input type="date" id="from" name="from" class="form-control" value="<?php echo htmlspecialchars($from); ?>">
      </div>
      
      <div class="form-group" style="margin-bottom:0; width:150px;">
        <label for="to">Completed To</label>
        <input type="date" id="to" name="to" class="form-control" value="<?php echo htmlspecialchars($to); ?>">
      </div>

      <div class="form-group" style="margin-bottom:8px; display:flex; align-items:center; gap:6px;">
        <input type="checkbox" id="hide_orphans" name="hide_orphans" value="1" <?php echo $hideOrphans ? 'checked' : ''; ?> style="width:16px; height:16px; cursor:pointer;">
        <label for="hide_orphans" style="margin:0; font-weight:600; cursor:pointer; user-select:none;">Hide Orphaned Donors</label>
      </div>

      <div style="display:flex; gap:8px;">
        <a href="admin/sudamaseva-completed-subscriptions" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>Completed Records (Showing <?php echo count($subscriptions); ?> of <?php echo $total; ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 900px;">
        <thead>
          <tr>
            <th>ID</th>
            <th>Donor Name</th>
            <th>Contact Details</th>
            <th>Monthly Amount</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>End Date (Completed)</th>
            <th>Duration</th>
            <th>Installments Paid</th>
            <th>Collection Mode</th>
            <th style="text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($subscriptions)): ?>
            <tr>
              <td colspan="11" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No completed subscriptions found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($subscriptions as $s):
              $totalInst = (int) ($s['total_installments'] ?? 0);
              $paidInst = (int) ($s['installments_paid'] ?? 0);
              
              $endDate = $s['end_date'] ?? null;
              if (empty($endDate) && $totalInst > 0 && !empty($s['start_date'])) {
                  $months = $totalInst - 1;
                  $endDate = date('Y-m-d H:i:s', strtotime("+{$months} months", strtotime($s['start_date'])));
              }
            ?>
              <tr>
                <td style="font-family:monospace; font-size:12px;">#<?php echo $s['id']; ?></td>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($s['donor_name'] ?? '—'); ?></strong>
                  <?php if (!empty($s['pan'])): ?>
                    <div style="font-size:10px; color:var(--text-light); text-transform:uppercase;">PAN: <?php echo htmlspecialchars($s['pan']); ?></div>
                  <?php endif; ?>
                </td>
                <td>
                   <?php 
                    $phone = $s['phone'] ?? '';
                    $cleanPhone = preg_replace('/\D+/', '', $phone);
                    if (strlen($cleanPhone) === 10) {
                        $cleanPhone = '91' . $cleanPhone;
                    } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '91' . substr($cleanPhone, 1);
                    }
                    $isOrphan = str_starts_with($phone, 'orphan-');
                  ?>
                  <div>
                    <i class="fab fa-whatsapp" style="color:#25d366; margin-right:4px;"></i>
                    <?php if (!$isOrphan && !empty($cleanPhone)): 
                      $donorName = trim($s['donor_name'] ?? 'Devotee');
                      $waMessage = "Hare Krishna {$donorName}, \n\n"
                                 . "Please accept my humble obeisances. All glories to Śrīla Prabhupāda.\n\n"
                                 . "Thank you very much for successfully completing your Sudama Seva Monthly Subscription.\n\n"
                                 . "Your generous support will help us continue the service of Sri Sri Jagannath, Baladeva, and Subhadra Maharani, as well as serve devotees through our temple activities.\n\n"
                                 . "We sincerely pray that Sri Sri Jagannath, Baladeva, and Subhadra Maharani shower Their divine blessings upon you and your family with good health, happiness, prosperity, and steady devotional progress.\n\n"
                                 . "Thank you for being a part of this sacred seva.\n\n"
                                 . "Hare Krishna! \n\n"
                                 . "ISKCON Seshadripuram\n"
                                 . "The Palace Temple of Lord Jagannath\n"
                                 . "https://www.iskconseshadripuram.org/";
                      $waUrl = "https://wa.me/" . $cleanPhone . "?text=" . rawurlencode($waMessage);
                    ?>
                      <a href="<?php echo $waUrl; ?>" target="_blank" style="text-decoration:none; color:var(--primary); font-weight:600;">
                        <?php echo htmlspecialchars($phone); ?>
                      </a>
                    <?php else: ?>
                      <?php echo htmlspecialchars($phone); ?>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($s['email'])): ?>
                    <div style="font-size:11px; color:var(--text-light);"><i class="fas fa-envelope" style="margin-right:4px;"></i><?php echo htmlspecialchars($s['email']); ?></div>
                  <?php endif; ?>
                </td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($s['amount'] ?? 0)); ?></td>
                <td><?php echo $service->renderStatusBadge($s['status'] ?? 'completed'); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo $service->formatDate($s['start_date'] ?? null, 'd M Y'); ?></td>
                <td style="font-size:12px; color:var(--text-light); font-weight:600;"><?php echo $service->formatDate($endDate, 'd M Y'); ?></td>
                <td style="text-align:center;"><?php echo $totalInst > 0 ? "{$totalInst} Months" : 'Open-ended'; ?></td>
                <td style="text-align:center; font-weight:600; color:green;"><?php echo $paidInst; ?></td>
                <td><span class="badge badge-info"><?php echo htmlspecialchars($s['collection_mode'] ?? '—'); ?></span></td>
                <td style="text-align:center;">
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
      <a href="admin/sudamaseva-completed-subscriptions?page=<?php echo ($page - 1); ?>&<?php echo $queryString; ?>" class="page-link"><i class="fas fa-chevron-left" style="font-size:10px; margin-right:4px;"></i> Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a href="admin/sudamaseva-completed-subscriptions?page=<?php echo $i; ?>&<?php echo $queryString; ?>" class="page-link<?php echo $i === $page ? ' active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($page < $pages): ?>
      <a href="admin/sudamaseva-completed-subscriptions?page=<?php echo ($page + 1); ?>&<?php echo $queryString; ?>" class="page-link">Next <i class="fas fa-chevron-right" style="font-size:10px; margin-left:4px;"></i></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
