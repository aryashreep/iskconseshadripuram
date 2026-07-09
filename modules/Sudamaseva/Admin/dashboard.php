<?php
/**
 * Sudamaseva Module — Admin Dashboard
 *
 * Admin Dashboard — Main overview (KPI cards, revenue charts, recent payments)
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

$pageTitle = 'Sudamaseva Dashboard';
$activePage = 'sudamaseva-dashboard';
include 'partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaService;

$service = new SudamasevaService();
$error = '';
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
                  <a href="admin/sudamaseva-donor-detail?id=<?php echo htmlspecialchars($p['donor_id'] ?? ''); ?>" style="text-decoration:none; color:var(--primary); font-weight:600;">
                    <?php echo htmlspecialchars($p['donor_name'] ?? '—'); ?>
                  </a>
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
