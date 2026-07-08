<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('reports.view');

$pageTitle = 'Seva-wise Donation Report';
$activePage = 'report-seva';
include 'partials/header.php';

$db = getDB();
$error = '';

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$filterCategory = trim($_GET['report_category'] ?? '');
$filterCause = isset($_GET['cause_id']) && $_GET['cause_id'] !== '' ? intval($_GET['cause_id']) : '';

$where = ["t.payment_status = 'paid'"];
$params = [];

if ($startDate !== '') { $where[] = "t.created_at >= ?"; $params[] = $startDate . ' 00:00:00'; }
if ($endDate !== '') { $where[] = "t.created_at <= ?"; $params[] = $endDate . ' 23:59:59'; }
if ($filterCause !== '') { $where[] = "t.cause_id = ?"; $params[] = $filterCause; }
if ($filterCategory !== '') { $where[] = "c.category = ?"; $params[] = $filterCategory; }

$whereClause = implode(" AND ", $where);

$catLabels = [
    'festival' => 'Grand Festivals', 'ekadashi' => 'Ekadashi', 'appearance' => 'Appearance Days',
    'disappearance' => 'Disappearance Days', 'event' => 'Events & Programs', 'service' => 'Seva & Services',
    'construction' => 'Temple Construction', 'general' => 'General Donations',
];
$catIcons = [
    'festival' => 'fa-star', 'ekadashi' => 'fa-moon', 'appearance' => 'fa-sun',
    'disappearance' => 'fa-candle', 'event' => 'fa-calendar-check', 'service' => 'fa-hands-helping',
    'construction' => 'fa-building', 'general' => 'fa-heart',
];
$catOrder = ['festival','ekadashi','appearance','disappearance','event','service','construction','general'];

try {
    $allCauses = $db->query("SELECT id, title FROM donation_causes WHERE is_active = 1 ORDER BY title")->fetchAll();

    $sql = "
        SELECT 
            COALESCE(c.category, 'general') as cause_category,
            c.title as cause_title,
            COALESCE(ms.name, legacy_s.name, 'Unspecified') as seva_name,
            COALESCE(msc.name, legacy_sc.name, 'General') as seva_type,
            COUNT(*) as donation_count,
            SUM(t.amount) as total_amount,
            SUM(COALESCE(t.quantity, 1)) as total_qty
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
        LEFT JOIN master_seva_categories msc ON ms.category_id = msc.id
        LEFT JOIN donation_cause_sevas legacy_s ON t.seva_id = legacy_s.id AND t.master_seva_id IS NULL
        LEFT JOIN donation_seva_categories legacy_sc ON legacy_s.category_id = legacy_sc.id AND t.master_seva_id IS NULL
        WHERE {$whereClause}
        GROUP BY c.category, c.title, COALESCE(ms.name, legacy_s.name, 'Unspecified'),
                 COALESCE(msc.name, legacy_sc.name, 'General')
        ORDER BY FIELD(c.category, 'festival','ekadashi','appearance','disappearance','event','service','construction','general'), c.title, total_amount DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $totalDonations = array_sum(array_column($rows, 'donation_count'));
    $totalAmount = array_sum(array_column($rows, 'total_amount'));
    $totalQty = array_sum(array_column($rows, 'total_qty'));

    // Three-level nesting: category -> activity -> sevas
    $tree = [];
    foreach ($rows as $r) {
        $cat = $r['cause_category'];
        $act = $r['cause_title'] ?: 'General';

        if (!isset($tree[$cat])) $tree[$cat] = [];
        if (!isset($tree[$cat][$act])) $tree[$cat][$act] = ['sevas' => [], 'total' => 0, 'qty' => 0, 'count' => 0];

        $tree[$cat][$act]['sevas'][] = $r;
        $tree[$cat][$act]['total'] += $r['total_amount'];
        $tree[$cat][$act]['qty'] += $r['total_qty'];
        $tree[$cat][$act]['count'] += $r['donation_count'];
    }

    // Compute category totals
    $catTotals = [];
    foreach ($tree as $cat => $activities) {
        $catTotals[$cat] = ['total' => 0, 'qty' => 0, 'count' => 0, 'actCount' => count($activities)];
        foreach ($activities as $act) {
            $catTotals[$cat]['total'] += $act['total'];
            $catTotals[$cat]['qty'] += $act['qty'];
            $catTotals[$cat]['count'] += $act['count'];
        }
    }

} catch (PDOException $e) {
    $tree = []; $catTotals = [];
    $totalDonations = $totalAmount = $totalQty = 0;
    $allCauses = [];
    $error = 'Failed to load report data.';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Seva-wise Donation Report</h1>
    <p>Category → Activity → Seva breakdown with quantity sponsored and total amount</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('reports.export')): ?>
      <a href="admin/export-report-seva?<?php echo http_build_query($_GET); ?>" class="btn btn-primary" style="background-color:var(--maroon); border:none; text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-file-csv"></i> Export CSV
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filters</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/report-seva" method="GET" style="display:flex; flex-wrap:wrap; gap:var(--space-md); align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0; min-width:180px;">
        <label for="report_category">Category</label>
        <select id="report_category" name="report_category" class="form-control">
          <option value="">-- All Categories --</option>
          <?php foreach ($catOrder as $ck): ?>
            <option value="<?php echo $ck; ?>" <?php echo $filterCategory === $ck ? 'selected' : ''; ?>><?php echo $catLabels[$ck]; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0; min-width:220px;">
        <label for="cause_id">Activity / Festival</label>
        <select id="cause_id" name="cause_id" class="form-control">
          <option value="">-- All Activities --</option>
          <?php foreach ($allCauses as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo $filterCause == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['title']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label for="start_date">From</label>
        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label for="end_date">To</label>
        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
      </div>
      <div style="display:flex; gap:6px;">
        <a href="admin/report-seva" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 14px; border:1px solid var(--border); border-radius:var(--radius-md); font-size:12px; font-weight:600;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background:var(--primary); color:white; border:none; padding:8px 22px; border-radius:var(--radius-md); font-size:12px; font-weight:600; cursor:pointer;">Apply</button>
      </div>
    </form>
  </div>
</div>

<!-- Summary Cards -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:var(--space-md); margin-bottom:var(--space-lg);">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); box-shadow:var(--shadow-sm);">
    <i class="fas fa-funnel-dollar" style="font-size:22px; color:var(--primary);"></i>
    <div>
      <div style="font-size:10px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Total Amount</div>
      <strong style="font-size:17px; color:var(--dark);">₹<?php echo number_format($totalAmount, 0); ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); box-shadow:var(--shadow-sm);">
    <i class="fas fa-ribbon" style="font-size:22px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:10px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Qty Sponsored</div>
      <strong style="font-size:17px; color:var(--dark);"><?php echo number_format($totalQty); ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); box-shadow:var(--shadow-sm);">
    <i class="fas fa-clipboard-check" style="font-size:22px; color:var(--accent);"></i>
    <div>
      <div style="font-size:10px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Transactions</div>
      <strong style="font-size:17px; color:var(--dark);"><?php echo number_format($totalDonations); ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); box-shadow:var(--shadow-sm);">
    <i class="fas fa-layer-group" style="font-size:22px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:10px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Categories</div>
      <strong style="font-size:17px; color:var(--dark);"><?php echo count($tree); ?></strong>
    </div>
  </div>
</div>

<!-- Three-Level Accordion: Category → Activity → Seva -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
    <h2>Category → Activity → Seva</h2>
    <div style="display:flex; align-items:center; gap:8px;">
      <input type="text" id="sevaSearch" placeholder="Search sevas or activities..." oninput="filterSevas()" style="padding:6px 12px; border:1px solid var(--border); border-radius:var(--radius-md); font-size:12px; width:220px;">
      <button onclick="expandAll()" class="btn btn-outline-dark" style="padding:4px 12px; border-radius:var(--radius-md); font-size:12px; cursor:pointer; font-weight:600;">Expand All</button>
      <button onclick="collapseAll()" class="btn btn-outline-dark" style="padding:4px 12px; border-radius:var(--radius-md); font-size:12px; cursor:pointer; font-weight:600;">Collapse All</button>
    </div>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <?php if (empty($tree)): ?>
      <div style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No donation data found.</div>
    <?php else: ?>

      <?php foreach ($catOrder as $catKey):
        if (!isset($tree[$catKey])) continue;
        $activities = $tree[$catKey];
        $ct = $catTotals[$catKey];
        $catPct = $totalAmount > 0 ? ($ct['total'] / $totalAmount) * 100 : 0;
      ?>
        <!-- LEVEL 1: Category -->
        <div class="lv1-section">
          <div class="lv1-header" onclick="toggleLevel(this, 'lv1')">
            <div style="display:flex; align-items:center; gap:10px; flex:1;">
              <i class="fas fa-chevron-right acc-icon" style="font-size:10px; color:var(--text-light); transition:transform 0.2s;"></i>
              <i class="fas <?php echo $catIcons[$catKey] ?? 'fa-tag'; ?>" style="color:var(--primary); font-size:16px;"></i>
              <strong style="font-size:14px;"><?php echo $catLabels[$catKey] ?? ucfirst($catKey); ?></strong>
              <span style="font-size:11px; color:var(--text-light);"><?php echo $ct['actCount']; ?> activities</span>
            </div>
            <div style="display:flex; align-items:center; gap:20px;">
              <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($ct['qty']); ?></strong> qty</span>
              <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($ct['count']); ?></strong> txns</span>
              <strong style="color:var(--maroon); font-size:15px;">₹<?php echo number_format($ct['total'], 0); ?></strong>
              <span style="font-size:12px; color:var(--text-light); min-width:45px; text-align:right;"><?php echo round($catPct, 1); ?>%</span>
            </div>
          </div>
          <div class="lv1-body" style="display:none;">

            <?php foreach ($activities as $actName => $actData):
              $actPct = $totalAmount > 0 ? ($actData['total'] / $totalAmount) * 100 : 0;
            ?>
              <!-- LEVEL 2: Activity -->
              <div class="lv2-section">
                <div class="lv2-header" onclick="toggleLevel(this, 'lv2')">
                  <div style="display:flex; align-items:center; gap:8px; flex:1; padding-left:24px;">
                    <i class="fas fa-chevron-right acc-icon" style="font-size:9px; color:var(--text-light); transition:transform 0.2s;"></i>
                    <i class="fas fa-calendar" style="color:var(--accent); font-size:13px;"></i>
                    <strong style="font-size:13px;"><?php echo htmlspecialchars($actName); ?></strong>
                    <span style="font-size:11px; color:var(--text-light);"><?php echo count($actData['sevas']); ?> sevas</span>
                  </div>
                  <div style="display:flex; align-items:center; gap:20px;">
                    <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($actData['qty']); ?></strong> qty</span>
                    <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($actData['count']); ?></strong> txns</span>
                    <strong style="color:var(--maroon); font-size:14px;">₹<?php echo number_format($actData['total'], 0); ?></strong>
                    <span style="font-size:12px; color:var(--text-light); min-width:45px; text-align:right;"><?php echo round($actPct, 1); ?>%</span>
                  </div>
                </div>
                <div class="lv2-body" style="display:none;">

                  <!-- Seva table header -->
                  <div style="display:flex; align-items:center; padding:6px 20px 6px 52px; background:rgba(0,0,0,0.03); border-bottom:1px solid var(--border); font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">
                    <div style="flex:1;">Seva Name</div>
                    <div style="width:120px; text-align:right;">Seva Type</div>
                    <div style="width:80px; text-align:right;">Qty Sponsored</div>
                    <div style="width:80px; text-align:right;">Donations</div>
                    <div style="width:120px; text-align:right;">Total Amount</div>
                    <div style="width:90px; text-align:right;">% of Total</div>
                  </div>

                  <?php foreach ($actData['sevas'] as $s):
                    $sPct = $totalAmount > 0 ? ($s['total_amount'] / $totalAmount) * 100 : 0;
                  ?>
                    <div class="lv3-row">
                      <div style="flex:1; font-size:13px; color:var(--text); font-weight:500;"><?php echo htmlspecialchars($s['seva_name']); ?></div>
                      <div style="width:120px; text-align:right;"><span style="font-size:11px; background:var(--light); padding:2px 8px; border-radius:10px; color:var(--text-light);"><?php echo htmlspecialchars($s['seva_type']); ?></span></div>
                      <div style="width:80px; text-align:right; font-weight:600; font-size:13px;"><?php echo number_format($s['total_qty']); ?></div>
                      <div style="width:80px; text-align:right; font-size:13px; color:var(--text-light);"><?php echo number_format($s['donation_count']); ?></div>
                      <div style="width:120px; text-align:right; font-weight:700; color:var(--maroon); font-size:14px;">₹<?php echo number_format($s['total_amount'], 0); ?></div>
                      <div style="width:90px; text-align:right;">
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">
                          <div style="width:40px; height:4px; background:var(--light); border-radius:3px; overflow:hidden;">
                            <div style="width:<?php echo round($sPct); ?>%; height:100%; background:var(--primary); border-radius:3px;"></div>
                          </div>
                          <span style="font-size:11px; color:var(--text-light); min-width:32px;"><?php echo round($sPct, 1); ?>%</span>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>

                  <!-- Activity subtotal -->
                  <div style="display:flex; align-items:center; padding:8px 20px 8px 52px; background:var(--cream); border-top:1px solid var(--border); font-weight:700; font-size:12px;">
                    <div style="flex:1;">Subtotal</div>
                    <div style="width:80px; text-align:right;"><?php echo number_format($actData['qty']); ?></div>
                    <div style="width:80px; text-align:right;"><?php echo number_format($actData['count']); ?></div>
                    <div style="width:120px; text-align:right; color:var(--maroon);">₹<?php echo number_format($actData['total'], 0); ?></div>
                    <div style="width:90px; text-align:right;"><?php echo round($actPct, 1); ?>%</div>
                  </div>

                </div>
              </div>
            <?php endforeach; ?>

          </div>
        </div>
      <?php endforeach; ?>

      <!-- Grand Total -->
      <div style="background:var(--cream); padding:14px 20px; display:flex; align-items:center; justify-content:space-between; font-weight:700; border-top:2px solid var(--border); font-size:13px;">
        <div><strong>GRAND TOTAL</strong></div>
        <div style="display:flex; align-items:center; gap:20px;">
          <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($totalQty); ?></strong> qty</span>
          <span style="font-size:12px; color:var(--text-light);"><strong><?php echo number_format($totalDonations); ?></strong> txns</span>
          <strong style="color:var(--maroon); font-size:16px;">₹<?php echo number_format($totalAmount, 0); ?></strong>
          <span style="font-size:12px; color:var(--text-light); min-width:40px; text-align:right;">100%</span>
        </div>
      </div>

    <?php endif; ?>
  </div>
</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/admin/report-seva.css">
<script>
function toggleLevel(header, level) {
  const body = header.nextElementSibling;
  const icon = header.querySelector('.acc-icon');
  if (body.style.display === 'none') {
    body.style.display = 'block';
    icon.style.transform = 'rotate(90deg)';
  } else {
    body.style.display = 'none';
    icon.style.transform = 'rotate(0deg)';
  }
}
function expandAll() {
  document.querySelectorAll('.lv1-body, .lv2-body').forEach(b => b.style.display = 'block');
  document.querySelectorAll('.acc-icon').forEach(i => i.style.transform = 'rotate(90deg)');
}
function collapseAll() {
  document.querySelectorAll('.lv1-body, .lv2-body').forEach(b => b.style.display = 'none');
  document.querySelectorAll('.acc-icon').forEach(i => i.style.transform = 'rotate(0deg)');
}
function filterSevas() {
  const q = document.getElementById('sevaSearch').value.toLowerCase();
  document.querySelectorAll('.lv3-row').forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = (q === '' || text.includes(q)) ? '' : 'none';
  });
  document.querySelectorAll('.lv2-section').forEach(lv2 => {
    const visRows = lv2.querySelectorAll('.lv3-row:not([style*="display: none"])');
    lv2.style.display = (q === '' || visRows.length > 0) ? '' : 'none';
    if (q !== '' && visRows.length > 0) {
      lv2.querySelector('.lv2-body').style.display = 'block';
      lv2.querySelector('.acc-icon').style.transform = 'rotate(90deg)';
    }
  });
  document.querySelectorAll('.lv1-section').forEach(lv1 => {
    const visLv2 = lv1.querySelectorAll('.lv2-section:not([style*="display: none"])');
    lv1.style.display = (q === '' || visLv2.length > 0) ? '' : 'none';
    if (q !== '' && visLv2.length > 0) {
      lv1.querySelector('.lv1-body').style.display = 'block';
      lv1.querySelector('.acc-icon').style.transform = 'rotate(90deg)';
    }
  });
}
</script>

<?php include 'partials/footer.php'; ?>
