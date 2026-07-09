<?php
/**
 * Sudamaseva Module — Admin Dashboard
 *
 * Two modes:
 *   1. Main overview — KPI cards, revenue charts, recent payments (default)
 *   2. Donor-specific — when ?donor_id=N is passed, shows single-donor details
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

$pageTitle = 'Sudamaseva Dashboard';
$activePage = 'sudamaseva-dashboard';
include 'partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaService;

$service = new SudamasevaService();
$error = '';

// ============================================================
// MODE 1: Donor-specific view (?donor_id=N)
// ============================================================
$donorId = isset($_GET['donor_id']) ? (int) $_GET['donor_id'] : 0;

if ($donorId > 0) {
    try {
        $dashboard = $service->getDonorDashboard($donorId);
    } catch (Exception $e) {
        $error = 'Failed to load donor data: ' . $e->getMessage();
        $dashboard = [];
    }

    if (empty($dashboard) || empty($dashboard['donor'])) {
        echo '<div class="admin-page-header"><div class="admin-page-title"><h1>Donor Not Found</h1></div></div>';
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> Donor #' . $donorId . ' not found.</div>';
        echo '<a href="admin/sudamaseva-dashboard" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">&larr; Back to Dashboard</a>';
        include 'partials/footer.php';
        exit;
    }

    $donor = $dashboard['donor'];
    $activeSub = $dashboard['active_subscription'] ?? null;
    $subscriptions = $dashboard['subscriptions'] ?? [];
    $recentPayments = $dashboard['recent_payments'] ?? [];
    $totalPaid = $dashboard['total_paid'] ?? 0;
    $totalPaidFormatted = $dashboard['total_paid_formatted'] ?? '₹0';
    $fyTotal = $dashboard['current_fy_total'] ?? 0;
    $fyLabel = $service->getFinancialYearLabel();
?>
<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-user" style="margin-right:8px;"></i> <?php echo htmlspecialchars($donor['donor_name']); ?></h1>
    <p>Donor #<?php echo $donor['id']; ?> &middot; <?php echo $service->getDonorSourceLabel($donor['source'] ?? 'sudamaseva'); ?>
       &middot; Joined <?php echo $service->formatDate($donor['created_at'] ?? null); ?>
    </p>
  </div>
  <div class="admin-page-actions">
    <?php if ($activeSub): ?>
      <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $activeSub['id']; ?>" class="btn btn-success btn-sm" style="text-decoration:none; padding:8px 16px; background:#2e7d32; color:white; border-radius:var(--radius-md); font-weight:600; font-size:12px;">
        <i class="fas fa-hand-holding-usd"></i> Record Payment
      </a>
    <?php endif; ?>
    <a href="admin/sudamaseva-dashboard" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">
      <i class="fas fa-arrow-left"></i> Main Dashboard
    </a>
    <a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">
      <i class="fas fa-users"></i> All Donors
    </a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (isset($_GET['payment_recorded']) && $_GET['payment_recorded'] === '1'): ?>
  <div id="paymentFlash" class="alert alert-success" style="background:#d4edda; border:1px solid #c3e6cb; padding:var(--space-md); border-radius:var(--radius-md); margin-bottom:var(--space-lg); display:flex; align-items:center; gap:var(--space-sm); transition: opacity 0.8s ease;">
    <i class="fas fa-check-circle" style="color:#155724; font-size:18px;"></i>
    <span style="color:#155724; font-weight:500;">Payment recorded successfully!</span>
  </div>
  <script>
    setTimeout(function() {
      var el = document.getElementById('paymentFlash');
      if (el) {
        el.style.opacity = '0';
        setTimeout(function() { el.style.display = 'none'; }, 800);
      }
    }, 5000);
  </script>
<?php endif; ?>

<!-- Donor Info Card -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-body">
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:var(--space-lg);">
      <div>
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; margin-bottom:4px;">Contact</div>
        <div style="font-size:14px; font-weight:500;"><?php echo htmlspecialchars($donor['phone'] ?: '—'); ?></div>
        <div style="font-size:13px; color:var(--text-light);"><?php echo htmlspecialchars($donor['email'] ?: '—'); ?></div>
      </div>
      <div>
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; margin-bottom:4px;">PAN / Area</div>
        <div style="font-family:monospace; font-size:14px; font-weight:600; text-transform:uppercase;"><?php echo htmlspecialchars($donor['pan'] ?: '—'); ?></div>
        <div style="font-size:13px; color:var(--text-light);"><?php echo htmlspecialchars(implode(', ', array_filter([$donor['area'] ?? '', $donor['city'] ?? '', $donor['state'] ?? ''])) ?: '—'); ?></div>
      </div>
      <div>
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; margin-bottom:4px;">Status</div>
        <div>
          <?php
            $statusClass = ($donor['status'] ?? '') === 'active' ? 'badge-success' : 'badge-secondary';
          ?>
          <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($donor['status'] ?? 'unknown')); ?></span>
        </div>
        <div style="font-size:12px; color:var(--text-light); margin-top:4px;">UUID: <span style="font-family:monospace;"><?php echo htmlspecialchars($donor['uuid'] ?? '—'); ?></span></div>
      </div>
      <div>
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; margin-bottom:4px;">Renewals</div>
        <div style="font-size:20px; font-weight:700;"><?php echo count($subscriptions) > 1 ? (count($subscriptions) - 1) : 0; ?></div>
        <div style="font-size:12px; color:var(--text-light); margin-top:4px;"><?php echo count($subscriptions); ?> total cycles</div>
      </div>
    </div>
  </div>
</div>

<!-- Donor Stats -->
<div class="admin-stats-grid" style="margin-bottom:var(--space-xl);">
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Paid</h3>
      <div class="admin-stat-value"><?php echo $totalPaidFormatted; ?></div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-indian-rupee-sign"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Current FY Total</h3>
      <div class="admin-stat-value" style="color:var(--maroon);"><?php echo $service->formatAmount($fyTotal); ?></div>
      <div style="font-size:11px; color:var(--text-light); margin-top:2px;"><?php echo $fyLabel; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:rgba(123,30,30,0.1); color:var(--maroon);"><i class="fas fa-calendar-year"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Active Subscriptions</h3>
      <div class="admin-stat-value"><?php echo count(array_filter($subscriptions, fn($s) => ($s['status'] ?? '') === 'active')); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:#d4edda; color:green;"><i class="fas fa-sync"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Last Payment</h3>
      <div class="admin-stat-value" style="font-size:14px;"><?php echo $dashboard['last_payment_date'] ? $service->formatDate($dashboard['last_payment_date'], 'd M Y') : '—'; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:#f0f7ff; color:#0b5ed7;"><i class="fas fa-clock"></i></div>
  </div>
</div>

<!-- Active Subscription Detail -->
<?php if ($activeSub): ?>
<div class="admin-card" style="margin-bottom:var(--space-xl); border-left:4px solid #2e7d32;">
  <div class="admin-card-header">
    <h2><i class="fas fa-sync" style="color:#2e7d32; margin-right:6px;"></i> Active Subscription</h2>
    <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $activeSub['id']; ?>" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:var(--maroon); color:white; border-radius:var(--radius-md); font-weight:600; font-size:12px; text-decoration:none;">
      <i class="fas fa-hand-holding-usd"></i> Record Payment
    </a>
  </div>
  <div class="admin-card-body">
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:var(--space-lg); margin-bottom:var(--space-lg);">
      <div>
        <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Monthly Amount</div>
        <div style="font-size:20px; font-weight:700; color:var(--maroon);"><?php echo $service->formatAmount((float) ($activeSub['amount'] ?? 0)); ?></div>
      </div>
      <div>
        <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Installments</div>
        <div style="font-size:20px; font-weight:700;"><?php echo ($activeSub['installments_paid'] ?? 0); ?> / <?php echo ($activeSub['total_installments'] ?? 0) > 0 ? ($activeSub['total_installments'] ?? 0) : '∞'; ?></div>
      </div>
      <div>
        <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Next Installment</div>
        <div style="font-size:20px; font-weight:700;">#<?php echo $activeSub['next_installment'] ?? 1; ?></div>
      </div>
      <div>
        <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Remaining Value</div>
        <div style="font-size:20px; font-weight:700; color:var(--text-light);"><?php echo $activeSub['remaining_formatted'] ?? '—'; ?></div>
      </div>
    </div>
    <div>
      <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
        <span>Progress</span>
        <span style="font-weight:600;"><?php echo $activeSub['progress'] ?? 0; ?>%</span>
      </div>
      <div style="height:10px; background:#eee; border-radius:5px; overflow:hidden;">
        <div style="height:100%; width:<?php echo $activeSub['progress'] ?? 0; ?>%; background:linear-gradient(90deg, #2e7d32, #43a047); border-radius:5px; transition:width 0.5s;"></div>
      </div>
      <?php if (!empty($activeSub['razorpay_subscription_id'])): ?>
        <div style="margin-top:8px; font-size:11px; color:var(--text-light);">
          Razorpay Sub: <span style="font-family:monospace;"><?php echo htmlspecialchars($activeSub['razorpay_subscription_id']); ?></span>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- All Subscriptions Table -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header">
    <h2>Subscriptions (<?php echo count($subscriptions); ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>Installments</th>
            <th>Progress</th>
            <th>Source</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($subscriptions)): ?>
            <tr><td colspan="7" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No subscriptions for this donor.</td></tr>
          <?php else: ?>
            <?php foreach ($subscriptions as $s):
              $progress = $service->calculateSubscriptionProgress($s);
              $totalInst = (int) ($s['total_installments'] ?? 0);
              $paidInst = (int) ($s['installments_paid'] ?? 0);
            ?>
              <tr>
                <td style="font-family:monospace;">#<?php echo $s['id']; ?></td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($s['amount'] ?? 0)); ?></td>
                <td><?php echo $service->renderStatusBadge($s['status'] ?? 'unknown'); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo $service->formatDate($s['start_date'] ?? null, 'd M Y'); ?></td>
                <td style="text-align:center;"><?php echo $totalInst > 0 ? "{$paidInst} / {$totalInst}" : $paidInst; ?></td>
                <td style="min-width:100px;">
                  <?php if ($totalInst > 0): ?>
                    <div style="display:flex; align-items:center; gap:6px;">
                      <div style="flex:1; height:6px; background:#eee; border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:<?php echo $progress; ?>%; background:<?php echo $progress >= 100 ? '#2e7d32' : ($progress >= 50 ? 'var(--accent)' : 'var(--primary)'); ?>; border-radius:3px;"></div>
                      </div>
                      <span style="font-size:10px; color:var(--text-light); font-weight:600;"><?php echo $progress; ?>%</span>
                    </div>
                  <?php else: ?>
                    <span style="font-size:11px; color:var(--text-light); font-style:italic;">Open-ended</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-info"><?php echo htmlspecialchars($s['source'] ?? '—'); ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Recent Payments -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header">
    <h2>Recent Payments (Last <?php echo count($recentPayments); ?>)</h2>
    <a href="admin/sudamaseva-payments?search=<?php echo urlencode($donor['phone'] ?? ''); ?>" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">View All Payments</a>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Installment</th>
            <th>Status</th>
            <th>Receipt</th>
            <th>Payment ID</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentPayments)): ?>
            <tr><td colspan="6" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No payments recorded yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentPayments as $p): ?>
              <tr>
                <td style="font-size:12px; color:var(--text-light); white-space:nowrap;"><?php echo $p['date_formatted'] ?? $service->formatDate($p['payment_date'] ?? null); ?></td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $p['amount_formatted'] ?? $service->formatAmount((float) ($p['amount'] ?? 0)); ?></td>
                <td style="color:var(--text-light);">#<?php echo (int) ($p['installment_number'] ?? 0); ?></td>
                <td><?php echo $p['status_badge'] ?? $service->renderStatusBadge($p['payment_status'] ?? 'created', 'payment'); ?></td>
                <td style="font-family:monospace; font-size:11px;"><?php echo $service->formatReceiptNo($p['receipt_number'] ?? null); ?></td>
                <td style="font-size:10px; font-family:monospace; color:var(--text-light); max-width:100px; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($p['razorpay_payment_id'] ?? ''); ?>">
                  <?php echo htmlspecialchars($p['razorpay_payment_id'] ?: '—'); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
    include 'partials/footer.php';
    exit;
}

// ============================================================
// MODE 2: Main Overview Dashboard (default)
// ============================================================
try {
    $dashStats = $service->getDashboardStats();
    $recentPayments = $service->getRecentPayments(10);
    $monthlyRevenue = $service->getMonthlyRevenue(12);
    $subscriptionStats = $service->getSubscriptionStats();
    $donorStats = $service->getDonorStats();
    $receiptStats = $service->getReceiptStats();
} catch (Exception $e) {
    $error = 'Failed to load dashboard data: ' . $e->getMessage();
    $dashStats = [];
    $recentPayments = [];
    $monthlyRevenue = [];
    $subscriptionStats = [];
    $donorStats = [];
    $receiptStats = [];
}

// Prepare chart data
$chartMonths = [];
$chartRevenue = [];
$chartCounts = [];

// Sort monthly revenue chronologically
usort($monthlyRevenue, function ($a, $b) {
    $cmp = $a['year'] - $b['year'];
    return $cmp !== 0 ? $cmp : $a['month'] - $b['month'];
});

foreach ($monthlyRevenue as $row) {
    $chartMonths[] = date('M Y', mktime(0, 0, 0, (int) $row['month'], 1, (int) $row['year']));
    $chartRevenue[] = (float) ($row['total_amount'] ?? 0);
    $chartCounts[] = (int) ($row['payment_count'] ?? 0);
}

$revenueAllTime = $dashStats['revenue_all_time'] ?? 0;
$revenueToday = $dashStats['revenue_today'] ?? 0;
$revenueMonth = $dashStats['revenue_this_month'] ?? 0;
$activeSubs = $subscriptionStats['active'] ?? 0;
$totalDonors = $donorStats['total'] ?? 0;
$pendingReceipts = $dashStats['pending_receipts'] ?? 0;
$paidCount = $service->getPaymentStats()['paid_payments'] ?? 0;
$totalMonthlyAmount = $subscriptionStats['total_monthly_amount'] ?? 0;
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Sudamaseva — Subscription Donations</h1>
    <p>Dashboard overview of recurring donation system with donor management, subscriptions, and 80G receipts.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/sudamaseva-payments" class="btn btn-primary btn-sm"><i class="fas fa-list-ul"></i> View All Payments</a>
    <a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm"><i class="fas fa-users"></i> Donors</a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Revenue</h3>
      <div class="admin-stat-value"><?php echo $service->formatAmount($revenueAllTime); ?></div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-indian-rupee-sign"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>This Month</h3>
      <div class="admin-stat-value"><?php echo $service->formatAmount($revenueMonth); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: rgba(200,107,31,0.15); color: var(--primary-dark);"><i class="fas fa-calendar-alt"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Today's Revenue</h3>
      <div class="admin-stat-value"><?php echo $service->formatAmount($revenueToday); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: #d4edda; color: green;"><i class="fas fa-sun"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Active Subscriptions</h3>
      <div class="admin-stat-value"><?php echo $activeSubs; ?></div>
      <div style="font-size:11px; color:var(--text-light); margin-top:2px;">Monthly: <?php echo $service->formatAmount($totalMonthlyAmount); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: #f0f7ff; color: #0b5ed7;"><i class="fas fa-sync"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Donors</h3>
      <div class="admin-stat-value"><?php echo $totalDonors; ?></div>
      <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
        <?php echo $donorStats['active'] ?? 0; ?> active &middot; <?php echo $donorStats['migrated'] ?? 0; ?> migrated
      </div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-users"></i></div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Pending Receipts</h3>
      <div class="admin-stat-value" style="color: <?php echo $pendingReceipts > 0 ? 'var(--primary-dark)' : 'green'; ?>;"><?php echo $pendingReceipts; ?></div>
      <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
        <?php echo $paidCount; ?> paid total &middot; <?php echo $receiptStats['total'] ?? 0; ?> receipts generated
      </div>
    </div>
    <div class="admin-stat-icon" style="background-color: <?php echo $pendingReceipts > 0 ? 'rgba(200,107,31,0.15)' : '#d4edda'; ?>; color: <?php echo $pendingReceipts > 0 ? 'var(--primary-dark)' : 'green'; ?>;">
      <i class="fas fa-receipt"></i>
    </div>
  </div>
</div>

<!-- Charts Grid -->
<div class="admin-charts-grid">
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Monthly Revenue Trend</h2>
      <span style="font-size:11px; color:var(--text-light);"><i class="fas fa-info-circle"></i> Last 12 months</span>
    </div>
    <div class="admin-card-body">
      <div class="chart-container">
        <?php if (empty($chartRevenue)): ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data available yet</div>
        <?php else: ?>
          <canvas id="revenueChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Subscription Status Breakdown</h2>
    </div>
    <div class="admin-card-body">
      <div class="chart-container">
        <?php if (($subscriptionStats['total'] ?? 0) === 0): ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No subscriptions yet</div>
        <?php else: ?>
          <canvas id="subStatusChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- KPI Summary Row -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-md); margin-bottom: var(--space-xl);">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Subscriptions</div>
    <div style="font-size:22px; font-weight:700; color:var(--dark);"><?php echo $subscriptionStats['total'] ?? 0; ?></div>
    <div style="font-size:11px; color:var(--text-light);"><?php echo $subscriptionStats['completed'] ?? 0; ?> completed &middot; <?php echo $subscriptionStats['cancelled'] ?? 0; ?> cancelled</div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Active Subs Monthly</div>
    <div style="font-size:22px; font-weight:700; color:green;"><?php echo $service->formatAmount($totalMonthlyAmount); ?></div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Donors (Migrated)</div>
    <div style="font-size:22px; font-weight:700; color:var(--maroon);"><?php echo $donorStats['migrated'] ?? 0; ?></div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Receipts Today</div>
    <div style="font-size:22px; font-weight:700; color:var(--primary-dark);"><?php echo $receiptStats['generated_today'] ?? 0; ?></div>
  </div>
</div>

<!-- Recent Payments Table -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header">
    <h2>Recent Payments</h2>
    <a href="admin/sudamaseva-payments" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight:600;">View All</a>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Donor</th>
            <th>Amount</th>
            <th>Installment</th>
            <th>Status</th>
            <th>Receipt</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentPayments)): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No payments recorded yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentPayments as $p): ?>
              <tr>
                <td style="font-size:12px; color:var(--text-light); white-space:nowrap;"><?php echo $service->formatDate($p['payment_date'] ?? null); ?></td>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($p['donor_name'] ?? '—'); ?></strong>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($p['phone'] ?? ''); ?></div>
                </td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($p['amount'] ?? 0)); ?></td>
                <td style="color:var(--text-light);">#<?php echo (int) ($p['installment_number'] ?? 0); ?></td>
                <td><?php echo $service->renderStatusBadge($p['payment_status'] ?? 'created', 'payment'); ?></td>
                <td style="font-family:monospace; font-size:11px;"><?php echo $service->formatReceiptNo($p['receipt_number'] ?? null); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const primaryColor = '#c86b1f';
  const accentColor = '#d4af37';
  const maroonColor = '#7b1e1e';

  // 1. Monthly Revenue Chart (dual-axis: revenue bar + count line)
  <?php if (!empty($chartRevenue)): ?>
    new Chart(document.getElementById('revenueChart'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($chartMonths); ?>,
        datasets: [{
          label: 'Revenue (₹)',
          data: <?php echo json_encode($chartRevenue); ?>,
          backgroundColor: 'rgba(200,107,31,0.6)',
          borderColor: primaryColor,
          borderWidth: 1,
          borderRadius: 4,
          order: 2
        }, {
          label: 'Payment Count',
          data: <?php echo json_encode($chartCounts); ?>,
          type: 'line',
          borderColor: accentColor,
          backgroundColor: 'rgba(212,175,55,0.1)',
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: accentColor,
          tension: 0.3,
          order: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top', labels: { boxWidth: 12, padding: 12 } }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: function (v) { return '₹' + v.toLocaleString(); } }
          }
        }
      }
    });
  <?php endif; ?>

  // 2. Subscription Status Doughnut
  <?php if (($subscriptionStats['total'] ?? 0) > 0): ?>
    new Chart(document.getElementById('subStatusChart'), {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Completed', 'Cancelled'],
        datasets: [{
          data: [
            <?php echo $subscriptionStats['active'] ?? 0; ?>,
            <?php echo $subscriptionStats['completed'] ?? 0; ?>,
            <?php echo $subscriptionStats['cancelled'] ?? 0; ?>
          ],
          backgroundColor: ['#2e7d32', '#0b5ed7', '#757575'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } }
        },
        cutout: '60%'
      }
    });
  <?php endif; ?>
});
</script>

<?php include 'partials/footer.php'; ?>
