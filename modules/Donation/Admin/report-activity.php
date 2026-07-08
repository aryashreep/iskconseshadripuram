<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('reports.view');

$pageTitle = 'Activity-wise Donation Report';
$activePage = 'report-activity';
include 'partials/header.php';

$db = getDB();
$error = '';

// Read filters
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$filterCategory = trim($_GET['category'] ?? '');

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
if ($filterCategory !== '') {
    $where[] = "c.category = ?";
    $params[] = $filterCategory;
}

$whereClause = implode(" AND ", $where);

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

try {
    // Get distinct categories for filter dropdown
    $catStmt = $db->query("SELECT DISTINCT category FROM donation_causes WHERE is_active = 1 ORDER BY FIELD(category, 'festival','ekadashi','appearance','disappearance','event','service','construction','general')");
    $allCategories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

    // Activity-wise aggregation
    $sql = "
        SELECT 
            COALESCE(c.category, 'general') as category,
            c.title as activity_title,
            c.slug as activity_slug,
            COUNT(*) as donation_count,
            SUM(t.amount) as total_amount
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        WHERE {$whereClause}
        GROUP BY COALESCE(c.category, 'general'), c.title, c.slug
        ORDER BY total_amount DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();

    // Grand totals
    $totalDonations = array_sum(array_column($reports, 'donation_count'));
    $totalAmount = array_sum(array_column($reports, 'total_amount'));

    // Group by category for display
    $grouped = [];
    foreach ($reports as $r) {
        $cat = $r['category'];
        if (!isset($grouped[$cat])) {
            $grouped[$cat] = [
                'label' => $categoryLabels[$cat] ?? ucfirst($cat),
                'items' => [],
                'cat_total' => 0,
                'cat_count' => 0,
            ];
        }
        $grouped[$cat]['items'][] = $r;
        $grouped[$cat]['cat_total'] += $r['total_amount'];
        $grouped[$cat]['cat_count'] += $r['donation_count'];
    }

} catch (PDOException $e) {
    $reports = [];
    $grouped = [];
    $totalDonations = 0;
    $totalAmount = 0;
    $allCategories = [];
    $error = 'Failed to load report data.';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Activity-wise Donation Report</h1>
    <p>Donations grouped by activity (Rath Yatra, Janmashtami, Food for Life, etc.) within each category</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('reports.export')): ?>
      <a href="admin/export-report-activity?<?php echo http_build_query($_GET); ?>" class="btn btn-primary" style="background-color: var(--maroon); border: none; text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
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

<!-- Filters -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filters</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/report-activity" method="GET" style="display:flex; flex-wrap:wrap; gap: var(--space-md); align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0;">
        <label for="category">Category</label>
        <select id="category" name="category" class="form-control">
          <option value="">-- All Categories --</option>
          <?php foreach ($allCategories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filterCategory === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($categoryLabels[$cat] ?? ucfirst($cat)); ?></option>
          <?php endforeach; ?>
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
      <div style="display:flex; gap: 8px;">
        <a href="admin/report-activity" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px;">Clear</a>
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
    <i class="fas fa-calendar-alt" style="font-size:24px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Activities</div>
      <strong style="font-size:18px; color:var(--dark);"><?php echo count($reports); ?></strong>
    </div>
  </div>
</div>

<!-- Report Table Grouped by Category -->
<div class="admin-card">
  <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
    <h2>Activity Breakdown by Category</h2>
    <div style="display:flex; align-items:center; gap:8px;">
      <input type="text" id="activitySearch" placeholder="Search activities..." oninput="filterActivities()" style="padding:6px 12px; border:1px solid var(--border); border-radius:var(--radius-md); font-size:12px; width:200px;">
      <button onclick="toggleAllAccordions(true)" class="btn btn-outline-dark" style="padding:4px 12px; border-radius:var(--radius-md); font-size:12px; cursor:pointer; font-weight:600;">Expand All</button>
      <button onclick="toggleAllAccordions(false)" class="btn btn-outline-dark" style="padding:4px 12px; border-radius:var(--radius-md); font-size:12px; cursor:pointer; font-weight:600;">Collapse All</button>
    </div>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div style="min-width: 800px;">
      <?php if (empty($grouped)): ?>
        <div style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No donation data found for the selected period.</div>
      <?php else: ?>
        <?php $groupIdx = 0; foreach ($grouped as $catKey => $group): ?>
          <div class="accordion-section">
            <div class="accordion-header" onclick="toggleAccordion(this)">
              <div style="display:flex; align-items:center; gap:8px; flex:1;">
                <i class="fas fa-chevron-right accordion-icon" style="font-size:10px; color:var(--text-light); transition:transform 0.2s;"></i>
                <i class="fas fa-folder" style="color:var(--primary); font-size:14px;"></i>
                <strong style="color:var(--dark); font-size:13px;"><?php echo htmlspecialchars($group['label']); ?></strong>
              </div>
              <div style="display:flex; align-items:center; gap:20px;">
                <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($group['cat_count']); ?></strong> donations</span>
                <strong style="color:var(--maroon); font-size:14px;">₹<?php echo number_format($group['cat_total'], 2); ?></strong>
                <span style="font-size:12px; color:var(--text-light); min-width:40px; text-align:right;"><?php echo $totalAmount > 0 ? round(($group['cat_total'] / $totalAmount) * 100, 1) : 0; ?>%</span>
              </div>
            </div>
            <div class="accordion-body" style="display:none;">
              <?php foreach ($group['items'] as $r):
                $pct = $totalAmount > 0 ? ($r['total_amount'] / $totalAmount) * 100 : 0;
              ?>
                <div class="accordion-row">
                  <div style="display:flex; align-items:center; flex:1; padding-left:28px;">
                    <strong style="color:var(--text); font-size:13px;"><?php echo htmlspecialchars($r['activity_title'] ?: 'General'); ?></strong>
                  </div>
                  <div style="display:flex; align-items:center; gap:20px;">
                    <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($r['donation_count']); ?></strong> donations</span>
                    <strong style="color:var(--maroon); font-size:14px;">₹<?php echo number_format($r['total_amount'], 2); ?></strong>
                    <div style="display:flex; align-items:center; gap:6px; min-width:80px;">
                      <div style="width:50px; height:5px; background:var(--light); border-radius:3px; overflow:hidden;">
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
        <!-- Grand Total -->
        <div style="background:var(--cream); padding:12px 20px; display:flex; align-items:center; justify-content:space-between; font-weight:700; border-top:2px solid var(--border);">
          <div><strong>GRAND TOTAL</strong></div>
          <div style="display:flex; align-items:center; gap:20px;">
            <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($totalDonations); ?></strong> donations</span>
            <strong style="color:var(--maroon); font-size:14px;">₹<?php echo number_format($totalAmount, 2); ?></strong>
            <span style="font-size:12px; color:var(--text-light); min-width:40px; text-align:right;">100%</span>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/admin/report-activity.css">
<script>
function toggleAccordion(header) {
  const body = header.nextElementSibling;
  const icon = header.querySelector('.accordion-icon');
  if (body.style.display === 'none') {
    body.style.display = 'block';
    icon.style.transform = 'rotate(90deg)';
  } else {
    body.style.display = 'none';
    icon.style.transform = 'rotate(0deg)';
  }
}
function toggleAllAccordions(expand) {
  document.querySelectorAll('.accordion-body').forEach(b => b.style.display = expand ? 'block' : 'none');
  document.querySelectorAll('.accordion-icon').forEach(i => i.style.transform = expand ? 'rotate(90deg)' : 'rotate(0deg)');
}
function filterActivities() {
  const q = document.getElementById('activitySearch').value.toLowerCase();
  document.querySelectorAll('.accordion-section').forEach(sec => {
    const rows = sec.querySelectorAll('.accordion-row');
    let visible = 0;
    rows.forEach(row => {
      const name = row.querySelector('strong')?.textContent?.toLowerCase() || '';
      if (q === '' || name.includes(q)) { row.style.display = ''; visible++; }
      else { row.style.display = 'none'; }
    });
    sec.style.display = visible > 0 || q === '' ? '' : 'none';
    if (q !== '' && visible > 0) {
      sec.querySelector('.accordion-body').style.display = 'block';
      sec.querySelector('.accordion-icon').style.transform = 'rotate(90deg)';
    }
  });
}
</script>

<?php include 'partials/footer.php'; ?>
