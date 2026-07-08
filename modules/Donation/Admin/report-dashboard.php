<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('reports.view');

$pageTitle = 'Donation Dashboard';
$activePage = 'report-dashboard';
include 'partials/header.php';

$db = getDB();
$error = '';

// Read date filter
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$where = ["t.payment_status = 'paid'"];
$params = [];

if ($startDate !== '') { $where[] = "t.created_at >= ?"; $params[] = $startDate . ' 00:00:00'; }
if ($endDate !== '') { $where[] = "t.created_at <= ?"; $params[] = $endDate . ' 23:59:59'; }

$whereClause = implode(" AND ", $where);

$categoryLabels = [
    'festival' => 'Grand Festivals', 'ekadashi' => 'Ekadashi', 'appearance' => 'Appearance Days',
    'disappearance' => 'Disappearance Days', 'event' => 'Events & Programs', 'service' => 'Seva & Services',
    'construction' => 'Temple Construction', 'general' => 'General Donations',
];
$categoryIcons = [
    'festival' => 'fa-star', 'ekadashi' => 'fa-moon', 'appearance' => 'fa-sun',
    'disappearance' => 'fa-candle', 'event' => 'fa-calendar-check', 'service' => 'fa-hands-helping',
    'construction' => 'fa-building', 'general' => 'fa-heart',
];

try {
    // 1. Summary Cards
    $stmt = $db->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as total FROM donation_transactions t WHERE {$whereClause}");
    $stmt->execute($params);
    $summary = $stmt->fetch();
    $totalDonations = (int)$summary['cnt'];
    $totalAmount = (float)$summary['total'];
    $avgDonation = $totalDonations > 0 ? $totalAmount / $totalDonations : 0;

    $stmt = $db->prepare("SELECT COUNT(DISTINCT t.donor_email) FROM donation_transactions t WHERE {$whereClause} AND t.donor_email IS NOT NULL AND t.donor_email != ''");
    $stmt->execute($params);
    $uniqueDonors = (int)$stmt->fetchColumn();

    // 1b. Month-over-month comparison
    $thisMonthStart = date('Y-m-01');
    $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
    $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

    $stmt = $db->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as total FROM donation_transactions WHERE payment_status='paid' AND created_at >= ? AND created_at <= ?");
    $stmt->execute([$thisMonthStart . ' 00:00:00', date('Y-m-d 23:59:59')]);
    $thisMonth = $stmt->fetch();
    $thisMonthAmount = (float)$thisMonth['total'];
    $thisMonthCount = (int)$thisMonth['cnt'];

    $stmt->execute([$lastMonthStart . ' 00:00:00', $lastMonthEnd . ' 23:59:59']);
    $lastMonth = $stmt->fetch();
    $lastMonthAmount = (float)$lastMonth['total'];
    $lastMonthCount = (int)$lastMonth['cnt'];

    $revenueChange = $lastMonthAmount > 0 ? round((($thisMonthAmount - $lastMonthAmount) / $lastMonthAmount) * 100, 1) : null;
    $countChange = $lastMonthCount > 0 ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) : null;

    // 2. Category breakdown (doughnut)
    $stmt = $db->prepare("
        SELECT COALESCE(c.category, 'general') as category, COUNT(*) as cnt, SUM(t.amount) as total
        FROM donation_transactions t LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause} GROUP BY COALESCE(c.category, 'general') ORDER BY total DESC
    ");
    $stmt->execute($params);
    $catData = $stmt->fetchAll();
    $catLabels = []; $catValues = []; $catCounts = [];
    foreach ($catData as $r) { $catLabels[] = $categoryLabels[$r['category']] ?? ucfirst($r['category']); $catValues[] = (float)$r['total']; $catCounts[] = (int)$r['cnt']; }

    // 3. Monthly trend (line)
    $stmt = $db->prepare("
        SELECT DATE_FORMAT(t.created_at, '%b %Y') as label, DATE_FORMAT(t.created_at, '%Y-%m') as sort_key,
               SUM(t.amount) as total, COUNT(*) as cnt
        FROM donation_transactions t WHERE {$whereClause}
        GROUP BY sort_key, label ORDER BY sort_key ASC
    ");
    $stmt->execute($params);
    $monthlyData = $stmt->fetchAll();
    $monthLabels = []; $monthAmounts = []; $monthCounts = [];
    foreach ($monthlyData as $r) { $monthLabels[] = $r['label']; $monthAmounts[] = (float)$r['total']; $monthCounts[] = (int)$r['cnt']; }

    // 4. Top 10 Activities by revenue
    $stmt = $db->prepare("
        SELECT c.title, COALESCE(c.category, 'general') as category, SUM(t.amount) as total, COUNT(*) as cnt
        FROM donation_transactions t LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause} GROUP BY t.cause_id, c.title, c.category ORDER BY total DESC LIMIT 10
    ");
    $stmt->execute($params);
    $topActivities = $stmt->fetchAll();
    $actLabels = []; $actValues = [];
    foreach ($topActivities as $r) { $actLabels[] = strlen($r['title']) > 25 ? substr($r['title'], 0, 22) . '...' : $r['title']; $actValues[] = (float)$r['total']; }

    // 6. Recent 10 donations
    $stmt = $db->prepare("
        SELECT t.*, c.title as cause_title, COALESCE(c.category, 'general') as cat
        FROM donation_transactions t LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause} ORDER BY t.created_at DESC LIMIT 10
    ");
    $stmt->execute($params);
    $recentDonations = $stmt->fetchAll();

    // 7. Category → Activity hierarchy
    $stmt = $db->prepare("
        SELECT COALESCE(c.category, 'general') as category, c.title as activity, SUM(t.amount) as total, COUNT(*) as cnt
        FROM donation_transactions t LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause} GROUP BY c.category, c.title ORDER BY category, total DESC
    ");
    $stmt->execute($params);
    $hierarchyRaw = $stmt->fetchAll();
    $hierarchy = [];
    foreach ($hierarchyRaw as $r) {
        $cat = $r['category'];
        if (!isset($hierarchy[$cat])) $hierarchy[$cat] = ['total' => 0, 'count' => 0, 'items' => []];
        $hierarchy[$cat]['total'] += $r['total'];
        $hierarchy[$cat]['count'] += $r['cnt'];
        $hierarchy[$cat]['items'][] = $r;
    }
    arsort($hierarchy);
    foreach ($hierarchy as &$cat) { usort($cat['items'], fn($a, $b) => $b['total'] <=> $a['total']); }

    // 8. Top 10 Donors
    $stmt = $db->prepare("
        SELECT t.donor_name, t.donor_email, COUNT(*) as donation_count, SUM(t.amount) as total_amount,
               GROUP_CONCAT(DISTINCT COALESCE(c.category, 'general') SEPARATOR ', ') as categories
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause} AND t.donor_email IS NOT NULL AND t.donor_email != ''
        GROUP BY t.donor_email, t.donor_name
        ORDER BY total_amount DESC LIMIT 10
    ");
    $stmt->execute($params);
    $topDonors = $stmt->fetchAll();

    // 9. Donations by day of week (heatmap)
    $stmt = $db->prepare("
        SELECT DAYNAME(t.created_at) as day_name, DAYOFWEEK(t.created_at) as day_num,
               COUNT(*) as cnt, SUM(t.amount) as total
        FROM donation_transactions t WHERE {$whereClause}
        GROUP BY day_name, day_num ORDER BY day_num ASC
    ");
    $stmt->execute($params);
    $heatmapData = $stmt->fetchAll();
    $heatmap = [];
    foreach ($heatmapData as $r) { $heatmap[$r['day_name']] = ['count' => (int)$r['cnt'], 'total' => (float)$r['total'], 'num' => (int)$r['day_num']]; }
    $heatmapMax = max(array_column($heatmap, 'count') ?: [1]);
    $dayOrder = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    // 10. Year-over-year: current year vs previous year monthly
    $currentYear = (int)date('Y');
    $prevYear = $currentYear - 1;
    $stmt = $db->prepare("
        SELECT YEAR(t.created_at) as yr, MONTH(t.created_at) as mn, SUM(t.amount) as total
        FROM donation_transactions t
        WHERE t.payment_status = 'paid' AND YEAR(t.created_at) IN (?, ?)
        GROUP BY yr, mn ORDER BY yr, mn
    ");
    $stmt->execute([$prevYear, $currentYear]);
    $yoyRaw = $stmt->fetchAll();
    $yoyPrev = array_fill(1, 12, 0); $yoyCurr = array_fill(1, 12, 0);
    foreach ($yoyRaw as $r) {
        if ($r['yr'] == $prevYear) $yoyPrev[(int)$r['mn']] = (float)$r['total'];
        if ($r['yr'] == $currentYear) $yoyCurr[(int)$r['mn']] = (float)$r['total'];
    }
    $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $hasYoY = array_sum($yoyPrev) > 0 && array_sum($yoyCurr) > 0;

    // 11. Donor retention: first-time vs returning
    $stmt = $db->prepare("
        SELECT t.donor_email, MIN(t.created_at) as first_donation, COUNT(*) as total_count
        FROM donation_transactions t
        WHERE {$whereClause} AND t.donor_email IS NOT NULL AND t.donor_email != ''
        GROUP BY t.donor_email
    ");
    $stmt->execute($params);
    $donorHistory = $stmt->fetchAll();
    $firstTimeCount = 0; $returningCount = 0;
    $retentionByMonth = [];
    foreach ($donorHistory as $d) {
        $month = date('Y-m', strtotime($d['first_donation']));
        if (!isset($retentionByMonth[$month])) $retentionByMonth[$month] = ['first' => 0, 'returning' => 0];
        if ($d['total_count'] > 1) { $returningCount++; $retentionByMonth[$month]['returning']++; }
        else { $firstTimeCount++; $retentionByMonth[$month]['first']++; }
    }
    ksort($retentionByMonth);
    $retentionLabels = array_keys($retentionByMonth);
    $retentionFirst = array_values(array_map(fn($m) => $retentionByMonth[$m]['first'], $retentionLabels));
    $retentionReturn = array_values(array_map(fn($m) => $retentionByMonth[$m]['returning'], $retentionLabels));
    $retentionRate = ($firstTimeCount + $returningCount) > 0 ? round(($returningCount / ($firstTimeCount + $returningCount)) * 100, 1) : 0;

} catch (PDOException $e) {
    $error = 'Failed to load dashboard data.';
    $totalDonations = $totalAmount = $avgDonation = $uniqueDonors = 0;
    $thisMonthAmount = $thisMonthCount = $lastMonthAmount = $lastMonthCount = 0;
    $revenueChange = $countChange = null;
    $catLabels = $catValues = $catCounts = $monthLabels = $monthAmounts = $monthCounts = [];
    $actLabels = $actValues = [];
    $topDonors = $heatmap = $dayOrder = [];
    $heatmapMax = 1; $yoyPrev = $yoyCurr = $monthNames = [];
    $hasYoY = false; $retentionLabels = $retentionFirst = $retentionReturn = [];
    $firstTimeCount = $returningCount = 0; $retentionRate = 0;
    $recentDonations = $hierarchy = [];
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Donation Dashboard</h1>
    <p>Visual overview of donations across Category, Activity & Seva</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('reports.export')): ?>
      <a href="admin/export-dashboard?<?php echo http_build_query($_GET); ?>" class="btn btn-primary" style="background-color:var(--maroon); border:none; text-decoration:none; padding:8px 16px; border-radius:var(--radius-md); font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px;"><i class="fas fa-file-csv"></i> Export CSV</a>
    <?php endif; ?>
    <?php if (hasPermission('reports.view')): ?>
      <a href="admin/report-category" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; font-size:13px;"><i class="fas fa-layer-group"></i> Category Report</a>
      <a href="admin/report-activity" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; font-size:13px;"><i class="fas fa-calendar-alt"></i> Activity Report</a>
      <a href="admin/report-seva" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; font-size:13px;"><i class="fas fa-ribbon"></i> Seva Report</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Date Filter -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-body" style="padding:var(--space-md) var(--space-lg);">
    <form action="admin/report-dashboard" method="GET" style="display:flex; flex-wrap:wrap; gap:var(--space-md); align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0;">
        <label for="start_date" style="font-size:12px;">From</label>
        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label for="end_date" style="font-size:12px;">To</label>
        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
      </div>
      <div style="display:flex; gap:6px;">
        <a href="admin/report-dashboard" class="btn btn-outline-dark" style="text-decoration:none; padding:6px 14px; border:1px solid var(--border); border-radius:var(--radius-md); font-size:12px; font-weight:600;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background:var(--primary); color:white; border:none; padding:6px 20px; border-radius:var(--radius-md); font-size:12px; font-weight:600; cursor:pointer;">Apply</button>
      </div>
    </form>
  </div>
</div>

<!-- Summary Cards -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:var(--space-md); margin-bottom:var(--space-xl);">
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Revenue</h3>
      <div class="admin-stat-value">₹<?php echo number_format($totalAmount, 0); ?></div>
      <div style="font-size:11px; margin-top:4px; color:<?php echo $revenueChange !== null ? ($revenueChange >= 0 ? '#2e7d32' : '#c62828') : 'var(--text-light)'; ?>;">
        <?php if ($revenueChange !== null): ?>
          <i class="fas fa-<?php echo $revenueChange >= 0 ? 'arrow-up' : 'arrow-down'; ?>" style="font-size:10px;"></i>
          <?php echo abs($revenueChange); ?>% vs last month
        <?php elseif ($lastMonthAmount == 0 && $thisMonthAmount > 0): ?>
          <i class="fas fa-sparkles" style="font-size:10px;"></i> New this month
        <?php elseif ($lastMonthAmount == 0 && $thisMonthAmount == 0): ?>
          No data yet
        <?php endif; ?>
      </div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-indian-rupee-sign"></i></div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Donations</h3>
      <div class="admin-stat-value"><?php echo number_format($totalDonations); ?></div>
      <div style="font-size:11px; margin-top:4px; color:<?php echo $countChange !== null ? ($countChange >= 0 ? '#2e7d32' : '#c62828') : 'var(--text-light)'; ?>;">
        <?php if ($countChange !== null): ?>
          <i class="fas fa-<?php echo $countChange >= 0 ? 'arrow-up' : 'arrow-down'; ?>" style="font-size:10px;"></i>
          <?php echo abs($countChange); ?>% vs last month
        <?php elseif ($lastMonthCount == 0 && $thisMonthCount > 0): ?>
          <i class="fas fa-sparkles" style="font-size:10px;"></i> New this month
        <?php elseif ($lastMonthCount == 0 && $thisMonthCount == 0): ?>
          No data yet
        <?php endif; ?>
      </div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-hand-holding-heart"></i></div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Unique Donors</h3>
      <div class="admin-stat-value"><?php echo number_format($uniqueDonors); ?></div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-users"></i></div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Avg Donation</h3>
      <div class="admin-stat-value">₹<?php echo number_format($avgDonation, 0); ?></div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-calculator"></i></div>
  </div>
</div>

<!-- Charts Row 1: Trend + Category Doughnut -->
<div style="display:grid; grid-template-columns:2fr 1fr; gap:var(--space-md); margin-bottom:var(--space-md);">
  <div class="admin-card">
    <div class="admin-card-header"><h2>Monthly Donation Trend</h2></div>
    <div class="admin-card-body">
      <div class="chart-container">
        <?php if (empty($monthAmounts)): ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data</div>
        <?php else: ?>
          <canvas id="monthlyTrendChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>Revenue by Category</h2></div>
    <div class="admin-card-body">
      <div class="chart-container">
        <?php if (empty($catValues)): ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data</div>
        <?php else: ?>
          <canvas id="categoryDoughnut"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row 2: Top Activities + Donation Count by Category -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md); margin-bottom:var(--space-md);">
  <div class="admin-card">
    <div class="admin-card-header"><h2>Top 10 Activities by Revenue</h2></div>
    <div class="admin-card-body">
      <div class="chart-container">
        <?php if (empty($actValues)): ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data</div>
        <?php else: ?>
          <canvas id="topActivitiesChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>Donation Count by Category</h2></div>
    <div class="admin-card-body">
      <div class="chart-container">
        <?php if (empty($catCounts)): ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data</div>
        <?php else: ?>
          <canvas id="catCountChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row 3: Heatmap + Donor Retention -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-md); margin-bottom:var(--space-md);">
  <div class="admin-card">
    <div class="admin-card-header"><h2>Donations by Day of Week</h2></div>
    <div class="admin-card-body">
      <div style="display:grid; grid-template-columns:repeat(7, 1fr); gap:8px;">
        <?php foreach ($dayOrder as $day):
          $d = $heatmap[$day] ?? ['count' => 0, 'total' => 0];
          $intensity = $heatmapMax > 0 ? $d['count'] / $heatmapMax : 0;
          $bg = 'rgba(200,107,31,' . round(0.08 + $intensity * 0.82, 2) . ')';
        ?>
          <div style="text-align:center; padding:12px 4px; border-radius:var(--radius-md); background:<?php echo $bg; ?>;">
            <div style="font-size:12px; font-weight:600; color:var(--dark);"><?php echo substr($day, 0, 3); ?></div>
            <div style="font-size:18px; font-weight:700; color:var(--maroon); margin:4px 0;"><?php echo $d['count']; ?></div>
            <div style="font-size:10px; color:var(--text-light);">₹<?php echo number_format($d['total'], 0); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><h2>Donor Retention</h2></div>
    <div class="admin-card-body">
      <?php if ($firstTimeCount + $returningCount > 0): ?>
        <div style="text-align:center; margin-bottom:12px;">
          <div style="font-size:32px; font-weight:700; color:<?php echo $retentionRate >= 30 ? '#2e7d32' : ($retentionRate >= 15 ? 'var(--primary)' : 'var(--text-light)'); ?>;"><?php echo $retentionRate; ?>%</div>
          <div style="font-size:11px; color:var(--text-light);">Return Rate</div>
        </div>
        <div style="display:flex; gap:16px; justify-content:center; margin-bottom:16px;">
          <div style="text-align:center;">
            <div style="font-size:20px; font-weight:700; color:var(--primary);"><?php echo $firstTimeCount; ?></div>
            <div style="font-size:10px; color:var(--text-light);">First-time</div>
          </div>
          <div style="text-align:center;">
            <div style="font-size:20px; font-weight:700; color:#2e7d32;"><?php echo $returningCount; ?></div>
            <div style="font-size:10px; color:var(--text-light);">Returning</div>
          </div>
        </div>
        <?php if (count($retentionLabels) > 1): ?>
          <div class="chart-container" style="height:140px;">
            <canvas id="retentionChart"></canvas>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No donor data yet</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Charts Row 4: YoY + Top Donors -->
<div style="display:grid; grid-template-columns:<?php echo $hasYoY ? '1fr 1fr' : '1fr'; ?>; gap:var(--space-md); margin-bottom:var(--space-md);">
  <?php if ($hasYoY): ?>
  <div class="admin-card">
    <div class="admin-card-header"><h2>Year-over-Year Comparison</h2></div>
    <div class="admin-card-body">
      <div class="chart-container">
        <canvas id="yoyChart"></canvas>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="admin-card">
    <div class="admin-card-header"><h2>Top 10 Donors</h2></div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
        <table class="admin-table">
          <thead><tr><th>#</th><th>Donor</th><th>Donations</th><th style="text-align:right;">Total</th><th>Categories</th></tr></thead>
          <tbody>
            <?php if (empty($topDonors)): ?>
              <tr><td colspan="5" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No donor data yet.</td></tr>
            <?php else: ?>
              <?php foreach ($topDonors as $i => $d): ?>
                <tr>
                  <td style="font-weight:700; color:var(--primary);"><?php echo $i + 1; ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($d['donor_name']); ?></strong>
                    <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($d['donor_email']); ?></div>
                  </td>
                  <td style="font-size:13px;"><?php echo $d['donation_count']; ?></td>
                  <td style="text-align:right; font-weight:600; color:var(--maroon);">₹<?php echo number_format($d['total_amount'], 0); ?></td>
                  <td style="font-size:11px; color:var(--text-light); max-width:160px;"><?php echo htmlspecialchars($d['categories']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Category → Activity Hierarchy (Accordion) -->
<div class="admin-card" style="margin-bottom:var(--space-md);">
  <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h2>Category → Activity Breakdown</h2>
    <div style="display:flex; gap:6px;">
      <button onclick="toggleAllAcc(true)" class="btn btn-outline-dark" style="padding:4px 12px; border-radius:var(--radius-md); font-size:12px; cursor:pointer; font-weight:600;">Expand All</button>
      <button onclick="toggleAllAcc(false)" class="btn btn-outline-dark" style="padding:4px 12px; border-radius:var(--radius-md); font-size:12px; cursor:pointer; font-weight:600;">Collapse All</button>
    </div>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <?php if (empty($hierarchy)): ?>
      <div style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No data</div>
    <?php else: ?>
      <?php foreach ($hierarchy as $catKey => $cat): ?>
        <div class="acc-section">
          <div class="acc-header" onclick="toggleAcc(this)">
            <div style="display:flex; align-items:center; gap:8px; flex:1;">
              <i class="fas fa-chevron-right acc-icon" style="font-size:10px; color:var(--text-light); transition:transform 0.2s;"></i>
              <i class="fas <?php echo $categoryIcons[$catKey] ?? 'fa-tag'; ?>" style="color:var(--primary); font-size:14px;"></i>
              <strong style="font-size:13px;"><?php echo htmlspecialchars($categoryLabels[$catKey] ?? ucfirst($catKey)); ?></strong>
              <span style="font-size:11px; color:var(--text-light);"><?php echo count($cat['items']); ?> activities</span>
            </div>
            <div style="display:flex; align-items:center; gap:16px;">
              <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($cat['count']); ?></strong> txns</span>
              <strong style="color:var(--maroon); font-size:14px;">₹<?php echo number_format($cat['total'], 0); ?></strong>
              <span style="font-size:12px; color:var(--text-light); min-width:40px; text-align:right;"><?php echo $totalAmount > 0 ? round(($cat['total'] / $totalAmount) * 100, 1) : 0; ?>%</span>
            </div>
          </div>
          <div class="acc-body" style="display:none;">
            <?php foreach ($cat['items'] as $item):
              $pct = $totalAmount > 0 ? ($item['total'] / $totalAmount) * 100 : 0;
            ?>
              <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 20px 8px 40px; border-bottom:1px solid rgba(0,0,0,0.04);">
                <span style="font-size:13px; color:var(--text);"><?php echo htmlspecialchars($item['activity'] ?: 'General'); ?></span>
                <div style="display:flex; align-items:center; gap:16px;">
                  <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($item['cnt']); ?></strong></span>
                  <strong style="color:var(--maroon); font-size:13px;">₹<?php echo number_format($item['total'], 0); ?></strong>
                  <div style="display:flex; align-items:center; gap:6px; min-width:80px;">
                    <div style="width:50px; height:4px; background:var(--light); border-radius:3px; overflow:hidden;">
                      <div style="width:<?php echo round($pct); ?>%; height:100%; background:var(--primary); border-radius:3px;"></div>
                    </div>
                    <span style="font-size:11px; color:var(--text-light); min-width:35px; text-align:right;"><?php echo round($pct, 1); ?>%</span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Recent Donations Table -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h2>Recent Donations</h2>
    <a href="admin/donations" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">View All Logs</a>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr><th>Donor</th><th>Category</th><th>Activity</th><th>Amount</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php if (empty($recentDonations)): ?>
            <tr><td colspan="5" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No donations yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentDonations as $d): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($d['donor_name']); ?></strong>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($d['donor_email']); ?></div>
                </td>
                <td><span style="font-size:12px; background:var(--cream); padding:2px 8px; border-radius:10px;"><?php echo htmlspecialchars($categoryLabels[$d['cat']] ?? ucfirst($d['cat'])); ?></span></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($d['cause_title'] ?: 'General'); ?></td>
                <td style="font-weight:600; color:var(--maroon);">₹<?php echo number_format($d['amount'], 0); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo date('M d, Y', strtotime($d['created_at'])); ?></td>
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
document.addEventListener('DOMContentLoaded', function() {
  const colors = ['#c86b1f','#d4af37','#7b1e1e','#2c1b12','#8a7a6a','#e8ddd0','#4a7c59','#1565c0','#6a1b9a','#c62828'];
  const primaryColor = '#c86b1f';

  // Monthly Trend
  <?php if (!empty($monthAmounts)): ?>
    new Chart(document.getElementById('monthlyTrendChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($monthLabels); ?>,
        datasets: [{
          label: 'Revenue (₹)',
          data: <?php echo json_encode($monthAmounts); ?>,
          borderColor: primaryColor,
          backgroundColor: 'rgba(200,107,31,0.15)',
          borderWidth: 3, fill: true, tension: 0.3,
          pointBackgroundColor: primaryColor, pointBorderColor: '#fff', pointHoverRadius: 7
        },{
          label: 'Donations',
          data: <?php echo json_encode($monthCounts); ?>,
          borderColor: '#d4af37',
          backgroundColor: 'rgba(212,175,55,0.1)',
          borderWidth: 2, fill: false, tension: 0.3, yAxisID: 'y1',
          pointBackgroundColor: '#d4af37', pointBorderColor: '#fff', pointRadius: 4
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => '₹' + v.toLocaleString() } },
          y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { callback: v => v + ' txns' } }
        }
      }
    });
  <?php endif; ?>

  // Category Doughnut
  <?php if (!empty($catValues)): ?>
    new Chart(document.getElementById('categoryDoughnut').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($catLabels); ?>,
        datasets: [{ data: <?php echo json_encode($catValues); ?>, backgroundColor: colors.slice(0, <?php echo count($catValues); ?>), borderWidth: 2, borderColor: '#fff' }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: {
          legend: { position: 'right', labels: { boxWidth: 10, font: { size: 11 }, padding: 8 } },
          tooltip: { callbacks: { label: ctx => { const t = ctx.dataset.data.reduce((a,b)=>a+b,0); const counts = <?php echo json_encode($catCounts); ?>; const idx = ctx.dataIndex; const pct = t>0?((ctx.parsed/t)*100).toFixed(1):0; return ctx.label + ': ₹' + ctx.parsed.toLocaleString() + ' (' + counts[idx] + ' donations, ' + pct + '%)'; } } }
        }
      }
    });
  <?php endif; ?>

  // Top Activities (Horizontal Bar)
  <?php if (!empty($actValues)): ?>
    new Chart(document.getElementById('topActivitiesChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($actLabels); ?>,
        datasets: [{ label: 'Revenue', data: <?php echo json_encode($actValues); ?>, backgroundColor: colors.slice(0, <?php echo count($actValues); ?>), borderRadius: 4, maxBarThickness: 24 }]
      },
      options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { callback: v => '₹' + v.toLocaleString() } } }
      }
    });
  <?php endif; ?>

  // Donation Count by Category (Vertical Bar)
  <?php if (!empty($catCounts)): ?>
    new Chart(document.getElementById('catCountChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($catLabels); ?>,
        datasets: [{ label: 'Donations', data: <?php echo json_encode($catCounts); ?>, backgroundColor: colors.slice(0, <?php echo count($catCounts); ?>), borderRadius: 4, maxBarThickness: 30 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.parsed.y + ' donations (₹' + <?php echo json_encode($catValues); ?>[ctx.dataIndex].toLocaleString() + ')' } } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' donations' } } }
      }
    });
  <?php endif; ?>

  // Retention Stacked Bar
  <?php if (count($retentionLabels) > 1): ?>
    new Chart(document.getElementById('retentionChart').getContext('2d'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($retentionLabels); ?>,
        datasets: [
          { label: 'First-time', data: <?php echo json_encode($retentionFirst); ?>, backgroundColor: primaryColor, borderRadius: 3 },
          { label: 'Returning', data: <?php echo json_encode($retentionReturn); ?>, backgroundColor: '#2e7d32', borderRadius: 3 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } },
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
      }
    });
  <?php endif; ?>

  // Year-over-Year Line
  <?php if ($hasYoY): ?>
    new Chart(document.getElementById('yoyChart').getContext('2d'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($monthNames); ?>,
        datasets: [
          { label: '<?php echo $prevYear; ?>', data: <?php echo json_encode(array_values($yoyPrev)); ?>, borderColor: '#8a7a6a', backgroundColor: 'rgba(138,122,106,0.1)', borderWidth: 2, fill: false, tension: 0.3, pointRadius: 3 },
          { label: '<?php echo $currentYear; ?>', data: <?php echo json_encode(array_values($yoyCurr)); ?>, borderColor: primaryColor, backgroundColor: 'rgba(200,107,31,0.15)', borderWidth: 3, fill: true, tension: 0.3, pointRadius: 4 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => '₹' + v.toLocaleString() } } }
      }
    });
  <?php endif; ?>
});
</script>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/admin/report-dashboard.css">
<script>
function toggleAcc(h) { const b=h.nextElementSibling, i=h.querySelector('.acc-icon'); if(b.style.display==='none'){b.style.display='block';i.style.transform='rotate(90deg)';}else{b.style.display='none';i.style.transform='rotate(0deg)';} }
function toggleAllAcc(exp) { document.querySelectorAll('.acc-body').forEach(b=>b.style.display=exp?'block':'none'); document.querySelectorAll('.acc-icon').forEach(i=>i.style.transform=exp?'rotate(90deg)':'rotate(0deg)'); }
</script>

<?php include 'partials/footer.php'; ?>
