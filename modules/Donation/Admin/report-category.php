<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('reports.view');

$pageTitle = 'Category-wise Donation Report';
$activePage = 'report-category';
include 'partials/header.php';

$db = getDB();
$error = '';

// Read date filter parameters
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$where = ["t.payment_status = 'paid'"];
$params = [];

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
    // Category-wise aggregation
    $sql = "
        SELECT 
            COALESCE(c.category, 'general') as category,
            COUNT(*) as donation_count,
            SUM(t.amount) as total_amount
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause}
        GROUP BY COALESCE(c.category, 'general')
        ORDER BY total_amount DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

    // Grand totals
    $totalDonations = array_sum(array_column($reports, 'donation_count'));
    $totalAmount = array_sum(array_column($reports, 'total_amount'));

    // Category labels mapping
    $categoryLabels = [
        'festival' => 'Grand Festivals',
        'ekadashi' => 'Ekadashi',
        'appearance' => 'Appearance Days',
        'disappearance' => 'Disappearance Days',
        'event' => 'Events & Programs',
        'service' => 'Seva & Services',
        'construction' => 'Temple Construction',
        'general' => 'General Donations',
    ];

    $categoryIcons = [
        'festival' => 'fa-star',
        'ekadashi' => 'fa-moon',
        'appearance' => 'fa-sun',
        'disappearance' => 'fa-candle',
        'event' => 'fa-calendar-check',
        'service' => 'fa-hands-helping',
        'construction' => 'fa-building',
        'general' => 'fa-heart',
    ];

} catch (PDOException $e) {
    $reports = [];
    $totalDonations = 0;
    $totalAmount = 0;
    $error = 'Failed to load report data.';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Category-wise Donation Report</h1>
    <p>Donations grouped by category (Festivals, Ekadashi, Events, Services, etc.)</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('reports.export')): ?>
      <a href="admin/export-report-category?<?php echo http_build_query($_GET); ?>" class="btn btn-primary" style="background-color: var(--maroon); border: none; text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-file-csv"></i> Export CSV
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- Date Filter -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Date Range Filter</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/report-category" method="GET" style="display:flex; flex-wrap:wrap; gap: var(--space-md); align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0;">
        <label for="start_date">From Date</label>
        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label for="end_date">To Date</label>
        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
      </div>
      <div style="display:flex; gap: 8px;">
        <a href="admin/report-category" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Apply</button>
      </div>
    </form>
  </div>
</div>

<!-- Summary Cards -->
<div style="display:flex; gap: var(--space-md); margin-bottom: var(--space-lg); flex-wrap:wrap;">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap: var(--space-md); min-width: 220px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-funnel-dollar" style="font-size:24px; color:var(--primary);"></i>
    <div>
      <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Total Amount</div>
      <strong style="font-size:18px; color:var(--dark);">₹<?php echo number_format($totalAmount, 2); ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap: var(--space-md); min-width: 200px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-clipboard-check" style="font-size:24px; color:var(--accent);"></i>
    <div>
      <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Total Donations</div>
      <strong style="font-size:18px; color:var(--dark);"><?php echo number_format($totalDonations); ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap: var(--space-md); min-width: 180px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-layer-group" style="font-size:24px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Categories</div>
      <strong style="font-size:18px; color:var(--dark);"><?php echo count($reports); ?></strong>
    </div>
  </div>
</div>

<!-- Report Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Category Breakdown</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 700px;">
        <thead>
          <tr>
            <th>Category</th>
            <th style="text-align:right;">Number of Donations</th>
            <th style="text-align:right;">Total Amount</th>
            <th style="text-align:right;">% of Total</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reports)): ?>
            <tr>
              <td colspan="4" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No donation data found for the selected period.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($reports as $r):
              $catKey = $r['category'];
              $label = $categoryLabels[$catKey] ?? ucfirst($catKey);
              $icon = $categoryIcons[$catKey] ?? 'fa-tag';
              $pct = $totalAmount > 0 ? ($r['total_amount'] / $totalAmount) * 100 : 0;
            ?>
              <tr>
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--cream); display:flex; align-items:center; justify-content:center;">
                      <i class="fas <?php echo $icon; ?>" style="color:var(--primary); font-size:14px;"></i>
                    </div>
                    <strong style="color:var(--dark); font-size:14px;"><?php echo htmlspecialchars($label); ?></strong>
                  </div>
                </td>
                <td style="text-align:right; font-weight:500; color:var(--text);"><?php echo number_format($r['donation_count']); ?></td>
                <td style="text-align:right; font-weight:600; color:var(--maroon); font-size:14px;">₹<?php echo number_format($r['total_amount'], 2); ?></td>
                <td style="text-align:right;">
                  <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                    <div style="width:80px; height:6px; background:var(--light); border-radius:3px; overflow:hidden;">
                      <div style="width:<?php echo round($pct); ?>%; height:100%; background:var(--primary); border-radius:3px;"></div>
                    </div>
                    <span style="font-size:12px; color:var(--text-light); min-width:40px; text-align:right;"><?php echo round($pct, 1); ?>%</span>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <!-- Total Row -->
            <tr style="background:var(--cream); font-weight:700;">
              <td><strong>TOTAL</strong></td>
              <td style="text-align:right;"><?php echo number_format($totalDonations); ?></td>
              <td style="text-align:right; color:var(--maroon);">₹<?php echo number_format($totalAmount, 2); ?></td>
              <td style="text-align:right;">100%</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
