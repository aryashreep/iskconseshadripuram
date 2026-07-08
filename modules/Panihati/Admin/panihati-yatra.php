<?php

/**
 * Panihati Yatra Registrations Admin Panel Page
 * 
 * Allows super_admin and travel_agent to view, search, export, and upload offline registrations.
 */

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../panihati-helpers.php';

// Check CSV export actions before outputting any headers
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  require_once __DIR__ . '/../../Kernel/Admin/auth-check.php';
  requirePermission('panihati.view');

  $selectedYear = isset($_GET['year']) ? trim($_GET['year']) : 'all';
  $sql = "SELECT * FROM panihati_yatra_registrations";
  $params = [];
  if ($selectedYear !== 'all') {
    $sql .= " WHERE YEAR(created_at) = ?";
    $params[] = intval($selectedYear);
  }
  $sql .= " ORDER BY id DESC";

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=panihati_yatra_registrations_' . date('Y-m-d') . '.csv');

  $output = fopen('php://output', 'w');
  fputcsv($output, ['ID', 'Name', 'Phone', 'Email', 'Travel Mode', 'Adults', 'Kids', 'Bhakti Sadan', 'Pickup Location', 'Amount', 'Payment Status', 'Razorpay Order ID', 'Razorpay Payment ID', 'Offline Flag', 'Created At']);

  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    while ($row = $stmt->fetch()) {
      fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['phone'],
        $row['email'],
        $row['travel_mode'],
        $row['adults_count'],
        $row['kids_count'],
        $row['bhakti_sadan'],
        $row['pickup_location'],
        $row['amount'],
        $row['payment_status'],
        $row['razorpay_order_id'] ?? '',
        $row['razorpay_payment_id'] ?? '',
        $row['is_offline'] ? 'Yes' : 'No',
        $row['created_at']
      ]);
    }
  } catch (Exception $e) {
    // Silent error
  }
  fclose($output);
  exit;
}

if (isset($_GET['download_template'])) {
  require_once __DIR__ . '/../../Kernel/Admin/auth-check.php';
  requirePermission('panihati.view');

  $db = getDB();
  $stmt = $db->query("SELECT name FROM panihati_bhakti_sadans WHERE is_active = 1 ORDER BY name ASC");
  $sadans = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $stmt = $db->query("SELECT name FROM panihati_pickup_locations WHERE is_active = 1 ORDER BY name ASC");
  $pickups = $stmt->fetchAll(PDO::FETCH_COLUMN);

  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename=panihati_yatra_offline_template.xls');
  header('Cache-Control: max-age=0');

  echo '<?xml version="1.0"?>' . "\n";
  echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
  <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    xmlns:x="urn:schemas-microsoft-com:office:excel"
    xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
    xmlns:html="http://www.w3.org/TR/REC-html40">
    <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
        <Alignment ss:Vertical="Bottom" /><Borders/><Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000" /><Interior/><NumberFormat/><Protection/>
      </Style>
      <Style ss:ID="Header">
        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1" /><Interior ss:Color="#C86B1F" ss:Pattern="Solid" />
      </Style>
    </Styles>
    <Worksheet ss:Name="Offline Registrations">
      <Table ss:ExpandedColumnCount="9" ss:ExpandedRowCount="101" x:FullColumns="1" x:FullRows="1">
        <Column ss:Width="120" />
        <Column ss:Width="100" />
        <Column ss:Width="150" />
        <Column ss:Width="100" />
        <Column ss:Width="80" />
        <Column ss:Width="80" />
        <Column ss:Width="150" />
        <Column ss:Width="150" />
        <Column ss:Width="80" />
        <Row ss:Height="20">
          <Cell ss:StyleID="Header"><Data ss:Type="String">Name</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Phone</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Email</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Travel Mode (bus/own_vehicle)</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Adults Count</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Kids Count</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Bhakti Sadan</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Pickup Location</Data></Cell>
          <Cell ss:StyleID="Header"><Data ss:Type="String">Amount</Data></Cell>
        </Row>
        <?php for ($i = 2; $i <= 101; $i++): ?>
          <Row>
            <Cell><Data ss:Type="String"></Data></Cell>
            <Cell><Data ss:Type="String"></Data></Cell>
            <Cell><Data ss:Type="String"></Data></Cell>
            <Cell><Data ss:Type="String"></Data></Cell>
            <Cell><Data ss:Type="Number">1</Data></Cell>
            <Cell><Data ss:Type="Number">0</Data></Cell>
            <Cell><Data ss:Type="String"></Data></Cell>
            <Cell><Data ss:Type="String"></Data></Cell>
            <Cell><Data ss:Type="Number">0</Data></Cell>
          </Row>
        <?php endfor; ?>
      </Table>
      <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
        <PageSetup>
          <Header x:Margin="0.3" />
          <Footer x:Margin="0.3" />
          <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75" />
        </PageSetup>
        <Unsynced />
        <Print>
          <ValidPrinterInfo />
          <HorizontalResolution>600</HorizontalResolution>
          <VerticalResolution>600</VerticalResolution>
        </Print>
        <Selected />
        <ProtectObjects>False</ProtectObjects>
        <ProtectScenarios>False</ProtectScenarios>
        <DataValidation>
          <Range>R2C4:R101C4</Range>
          <Type>List</Type>
          <Value>&quot;bus,own_vehicle&quot;</Value>
        </DataValidation>
        <DataValidation>
          <Range>R2C7:R101C7</Range>
          <Type>List</Type>
          <Value>&quot;<?php echo implode(',', array_map(function ($val) {
                          return str_replace('"', '""', $val);
                        }, $sadans)); ?>&quot;</Value>
        </DataValidation>
        <DataValidation>
          <Range>R2C8:R101C8</Range>
          <Type>List</Type>
          <Value>&quot;Own Vehicle,<?php echo implode(',', array_map(function ($val) {
                                      return str_replace('"', '""', $val);
                                    }, $pickups)); ?>&quot;</Value>
        </DataValidation>
      </WorksheetOptions>
    </Worksheet>
  </Workbook>
<?php
  exit;
}

$pageTitle = 'Panihati Yatra Manager';
$activePage = 'panihati-yatra';
include 'partials/header.php';

// Enforce permission
requirePermission('panihati.view');

$db = getDB();
$successMsg = '';
$errorMsg = '';
$warningMsg = '';

// Get current pricing for inline display
$currentPricing = getPanihatiPricing();
$pricingYear = (int)date('Y');

// Read selected year (defined early so all blocks can use it)
$selectedYear = isset($_GET['year']) ? trim($_GET['year']) : 'all';

// Fetch aggregate offline entries for display
$aggregateEntries = [];
try {
  $aggWhere = "1=1";
  $aggParams = [];
  if ($selectedYear !== 'all') {
    $aggWhere = "reported_year = ?";
    $aggParams[] = intval($selectedYear);
  }
  $stmt = $db->prepare("
        SELECT a.*, adm.full_name as created_by_name
        FROM panihati_yatra_offline_aggregates a
        LEFT JOIN admins adm ON a.created_by_admin_id = adm.id
        WHERE $aggWhere
        ORDER BY a.created_at DESC
        LIMIT 50
    ");
  $stmt->execute($aggParams);
  $aggregateEntries = $stmt->fetchAll();
} catch (Exception $e) {
  // Table may not exist yet if migration hasn't been run — ignore
  $aggregateEntries = [];
}
try {
  // 1. Fetch available distinct registration years
  $stmt = $db->query("SELECT DISTINCT YEAR(created_at) as y FROM panihati_yatra_registrations ORDER BY y DESC");
  $availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);

  // Build SQL filters
  // Note: Stats queries use panihati_yatra_combined_stats view which already filters paid/offline
  $whereClause = "1=1";
  $listWhereClause = "1=1";
  $params = [];

  if ($selectedYear !== 'all') {
    $whereClause .= " AND report_year = ?";
    $listWhereClause .= " AND YEAR(created_at) = ?";
    $params[] = intval($selectedYear);
  }

  // Executive Summary & Chart Metrics Calculations
  // 1. Total Collection
  $stmt = $db->prepare("SELECT SUM(amount) FROM panihati_yatra_combined_stats WHERE $whereClause");
  $stmt->execute($params);
  $totalRevenue = (float)$stmt->fetchColumn();

  // 2. Total Passengers
  $stmt = $db->prepare("SELECT SUM(adults_count + kids_count) FROM panihati_yatra_combined_stats WHERE $whereClause");
  $stmt->execute($params);
  $totalPassengers = (int)$stmt->fetchColumn();

  // 3. Bus Collection
  $stmt = $db->prepare("SELECT SUM(amount) FROM panihati_yatra_combined_stats WHERE travel_mode = 'bus' AND $whereClause");
  $stmt->execute($params);
  $busRevenue = (float)$stmt->fetchColumn();

  // 4. Vehicle Collection
  $stmt = $db->prepare("SELECT SUM(amount) FROM panihati_yatra_combined_stats WHERE travel_mode = 'own_vehicle' AND $whereClause");
  $stmt->execute($params);
  $vehicleRevenue = (float)$stmt->fetchColumn();

  // 5. Bus Passengers
  $stmt = $db->prepare("SELECT SUM(adults_count + kids_count) FROM panihati_yatra_combined_stats WHERE travel_mode = 'bus' AND $whereClause");
  $stmt->execute($params);
  $busPassengers = (int)$stmt->fetchColumn();

  // 6. Vehicle Passengers
  $stmt = $db->prepare("SELECT SUM(adults_count + kids_count) FROM panihati_yatra_combined_stats WHERE travel_mode = 'own_vehicle' AND $whereClause");
  $stmt->execute($params);
  $vehiclePassengers = (int)$stmt->fetchColumn();

  // 7. Bus Adults
  $stmt = $db->prepare("SELECT SUM(adults_count) FROM panihati_yatra_combined_stats WHERE travel_mode = 'bus' AND $whereClause");
  $stmt->execute($params);
  $busAdults = (int)$stmt->fetchColumn();

  // 8. Bus Kids
  $stmt = $db->prepare("SELECT SUM(kids_count) FROM panihati_yatra_combined_stats WHERE travel_mode = 'bus' AND $whereClause");
  $stmt->execute($params);
  $busKids = (int)$stmt->fetchColumn();

  // 9. Adults (Total)
  $stmt = $db->prepare("SELECT SUM(adults_count) FROM panihati_yatra_combined_stats WHERE $whereClause");
  $stmt->execute($params);
  $totalAdults = (int)$stmt->fetchColumn();

  // 10. Kids (Total)
  $stmt = $db->prepare("SELECT SUM(kids_count) FROM panihati_yatra_combined_stats WHERE $whereClause");
  $stmt->execute($params);
  $totalKids = (int)$stmt->fetchColumn();

  // 11. Average per Person
  $avgPerPerson = $totalPassengers > 0 ? round($totalRevenue / $totalPassengers) : 0;

  // 12. Total Sadans
  $stmt = $db->prepare("SELECT COUNT(DISTINCT bhakti_sadan) FROM panihati_yatra_combined_stats WHERE bhakti_sadan != '' AND $whereClause");
  $stmt->execute($params);
  $totalSadansCount = (int)$stmt->fetchColumn();

  // 13. Pickup Points
  $stmt = $db->prepare("SELECT COUNT(DISTINCT pickup_location) FROM panihati_yatra_combined_stats WHERE pickup_location != 'Own Vehicle' AND pickup_location != '' AND $whereClause");
  $stmt->execute($params);
  $pickupPointsCount = (int)$stmt->fetchColumn();

  // 14. Top Sadan
  $stmt = $db->prepare("
        SELECT bhakti_sadan, SUM(adults_count + kids_count) as persons, SUM(amount) as revenue
        FROM panihati_yatra_combined_stats
        WHERE bhakti_sadan != '' AND $whereClause
        GROUP BY bhakti_sadan
        ORDER BY persons DESC
        LIMIT 1
    ");
  $stmt->execute($params);
  $topSadanRow = $stmt->fetch(PDO::FETCH_ASSOC);
  $topSadanText = $topSadanRow ? "{$topSadanRow['bhakti_sadan']} ({$topSadanRow['persons']} persons, INR " . number_format($topSadanRow['revenue']) . ")" : "-";

  // 15. Top Pickup Point
  $stmt = $db->prepare("
        SELECT pickup_location, SUM(adults_count + kids_count) as persons
        FROM panihati_yatra_combined_stats
        WHERE pickup_location != 'Own Vehicle' AND pickup_location != '' AND $whereClause
        GROUP BY pickup_location
        ORDER BY persons DESC
        LIMIT 1
    ");
  $stmt->execute($params);
  $topPickupRow = $stmt->fetch(PDO::FETCH_ASSOC);
  $topPickupText = $topPickupRow ? "{$topPickupRow['pickup_location']} ({$topPickupRow['persons']} persons)" : "-";

  // Bhakti Sadan Ranking (for Chart)
  $stmt = $db->prepare("
        SELECT bhakti_sadan, 
               SUM(CASE WHEN travel_mode = 'bus' THEN (adults_count + kids_count) ELSE 0 END) as bus_count,
               SUM(CASE WHEN travel_mode = 'own_vehicle' THEN (adults_count + kids_count) ELSE 0 END) as vehicle_count,
               SUM(adults_count + kids_count) as total_count
        FROM panihati_yatra_combined_stats
        WHERE bhakti_sadan != '' AND $whereClause
        GROUP BY bhakti_sadan
        ORDER BY total_count DESC
        LIMIT 12
    ");
  $stmt->execute($params);
  $sadanRankingData = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Top Bus Pickup Locations (for Chart)
  $stmt = $db->prepare("
        SELECT pickup_location, SUM(adults_count + kids_count) as count
        FROM panihati_yatra_combined_stats
        WHERE travel_mode = 'bus' AND pickup_location != 'Own Vehicle' AND pickup_location != '' AND $whereClause
        GROUP BY pickup_location
        ORDER BY count DESC
        LIMIT 10
    ");
  $stmt->execute($params);
  $pickupRankingData = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // YoY Comparison Calculations — query combined view
  $stmt = $db->query("SELECT SUM(amount) as revenue, SUM(adults_count + kids_count) as passengers FROM panihati_yatra_combined_stats WHERE report_year = 2025");
  $yoy2025 = $stmt->fetch(PDO::FETCH_ASSOC);
  $rev2025 = floatval($yoy2025['revenue'] ?? 0);
  $pass2025 = intval($yoy2025['passengers'] ?? 0);

  $stmt = $db->query("SELECT SUM(amount) as revenue, SUM(adults_count + kids_count) as passengers FROM panihati_yatra_combined_stats WHERE report_year = 2026");
  $yoy2026 = $stmt->fetch(PDO::FETCH_ASSOC);
  $rev2026 = floatval($yoy2026['revenue'] ?? 0);
  $pass2026 = intval($yoy2026['passengers'] ?? 0);

  $revDiff = $rev2026 - $rev2025;
  $revPct = $rev2025 > 0 ? ($revDiff / $rev2025) * 100 : 0;
  $passDiff = $pass2026 - $pass2025;
  $passPct = $pass2025 > 0 ? ($passDiff / $pass2025) * 100 : 0;

  // Detail registrations list
  $stmt = $db->prepare("SELECT * FROM panihati_yatra_registrations WHERE $listWhereClause ORDER BY id DESC");
  $stmt->execute($params);
  $registrations = $stmt->fetchAll();
} catch (PDOException $e) {
  $errorMsg = 'A database error occurred. Please try again.';
}
?>

<!-- Include Chart.js and DataLabels Plugin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<div class="admin-content-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-xl);">
  <div>
    <h1 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">Panihati Yatra Manager</h1>
    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:var(--font-size-sm);">Track collections, travel modes, pickup points, and manage offline data uploads.</p>
  </div>

  <div style="display:flex; gap:10px; align-items:center;">
    <!-- Year Filter Dropdown -->
    <div style="display:inline-flex; align-items:center; gap:6px; margin-right:10px;">
      <label for="filterYear" style="font-size:12px; font-weight:600; color:var(--text-dark); margin:0;">Filter Year:</label>
      <select id="filterYear" onchange="filterByYear(this.value)" style="padding:6px 12px; border:2px solid var(--border); border-radius:var(--radius-md); font-family:var(--font-body); font-size:12px; background:var(--white); outline:none; cursor:pointer;">
        <option value="all" <?php echo $selectedYear === 'all' ? 'selected' : ''; ?>>All Years</option>
        <?php foreach ($availableYears as $y): ?>
          <option value="<?php echo $y; ?>" <?php echo (string)$selectedYear === (string)$y ? 'selected' : ''; ?>><?php echo $y; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <a href="admin/panihati-yatra?export=csv&year=<?php echo urlencode($selectedYear); ?>" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; text-decoration:none;">
      <i class="fas fa-file-download"></i> Export CSV
    </a>

  </div>
</div>

<!-- Alert Notices -->
<?php if (!empty($successMsg)): ?>
  <div style="background:#e8f5e9; border:1px solid #c8e6c9; padding:var(--space-md); border-radius:var(--radius-md); color:#2e7d32; margin-bottom:var(--space-lg); font-size:var(--font-size-sm); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-check-circle"></i>
    <div><?php echo htmlspecialchars($successMsg); ?></div>
  </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
  <div style="background:#ffebee; border:1px solid #ffcdd2; padding:var(--space-md); border-radius:var(--radius-md); color:#c62828; margin-bottom:var(--space-lg); font-size:var(--font-size-sm); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-exclamation-circle"></i>
    <div><?php echo htmlspecialchars($errorMsg); ?></div>
  </div>
<?php endif; ?>

<?php if (!empty($warningMsg)): ?>
  <div style="background:#fff3e0; border:1px solid #ffe0b2; padding:var(--space-md); border-radius:var(--radius-md); color:#e65100; margin-bottom:var(--space-lg); font-size:var(--font-size-sm); display:flex; align-items:flex-start; gap:8px; white-space:pre-line;">
    <i class="fas fa-exclamation-triangle" style="margin-top:2px;"></i>
    <div><?php echo htmlspecialchars($warningMsg); ?></div>
  </div>
<?php endif; ?>

<!-- KPI Summary Cards -->
<div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:var(--space-lg); margin-bottom:var(--space-xl);">
  <!-- Total Revenue -->
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); border-left:4px solid var(--primary);">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--space-sm);">
      <span style="font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase;">Total Collection</span>
      <div style="width:36px; height:36px; background:var(--gradient-primary); border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-rupee-sign" style="color:var(--white); font-size:14px;"></i>
      </div>
    </div>
    <div style="font-size:24px; font-weight:700; color:var(--text-dark); font-family:var(--font-heading);">₹<?php echo number_format($totalRevenue); ?></div>
    <div style="font-size:11px; color:<?php echo $revPct >= 0 ? '#2e7d32' : '#c62828'; ?>; margin-top:4px;">
      <i class="fas <?php echo $revPct >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
      <?php echo number_format(abs($revPct), 1); ?>% vs last year
    </div>
  </div>

  <!-- Total Passengers -->
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); border-left:4px solid #06b6d4;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--space-sm);">
      <span style="font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase;">Total Passengers</span>
      <div style="width:36px; height:36px; background:linear-gradient(135deg, #06b6d4, #0891b2); border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-users" style="color:var(--white); font-size:14px;"></i>
      </div>
    </div>
    <div style="font-size:24px; font-weight:700; color:var(--text-dark); font-family:var(--font-heading);"><?php echo number_format($totalPassengers); ?></div>
    <div style="font-size:11px; color:var(--text-light); margin-top:4px;">
      <?php echo $busPassengers; ?> Bus + <?php echo $vehiclePassengers; ?> Vehicle
    </div>
  </div>

  <!-- Bus Collection -->
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); border-left:4px solid #8b5cf6;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--space-sm);">
      <span style="font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase;">Bus Collection</span>
      <div style="width:36px; height:36px; background:linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-bus" style="color:var(--white); font-size:14px;"></i>
      </div>
    </div>
    <div style="font-size:24px; font-weight:700; color:var(--text-dark); font-family:var(--font-heading);">₹<?php echo number_format($busRevenue); ?></div>
    <div style="font-size:11px; color:var(--text-light); margin-top:4px;">
      <?php echo $busPassengers; ?> passengers (<?php echo $totalRevenue > 0 ? number_format(($busRevenue / $totalRevenue) * 100, 1) : 0; ?>%)
    </div>
  </div>

  <!-- Payment Status -->
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); border-left:4px solid #22c55e;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--space-sm);">
      <span style="font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase;">Payment Status</span>
      <div style="width:36px; height:36px; background:linear-gradient(135deg, #22c55e, #16a34a); border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-check-circle" style="color:var(--white); font-size:14px;"></i>
      </div>
    </div>
    <?php
    // Get payment status counts
    $paidCount = 0;
    $failedCount = 0;
    $pendingCount = 0;
    try {
      $psStmt = $db->query("SELECT payment_status, COUNT(*) as cnt FROM panihati_yatra_registrations GROUP BY payment_status");
      foreach ($psStmt->fetchAll() as $ps) {
        if ($ps['payment_status'] === 'paid') $paidCount = $ps['cnt'];
        elseif ($ps['payment_status'] === 'failed') $failedCount = $ps['cnt'];
        else $pendingCount = $ps['cnt'];
      }
    } catch (Exception $e) {
    }
    $totalReg = $paidCount + $failedCount + $pendingCount;
    ?>
    <div style="font-size:24px; font-weight:700; color:#22c55e; font-family:var(--font-heading);"><?php echo number_format($paidCount); ?></div>
    <div style="font-size:11px; color:var(--text-light); margin-top:4px;">
      Paid | <?php echo $failedCount; ?> Failed | <?php echo $pendingCount; ?> Pending
    </div>
  </div>
</div>

<!-- Current Year Pricing Inline Display -->
<div style="background:var(--white); padding:var(--space-lg) var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); margin-bottom:var(--space-lg); display:flex; align-items:center; justify-content:space-between;">
  <div style="display:flex; align-items:center; gap:var(--space-lg);">
    <div style="display:flex; align-items:center; gap:6px;">
      <i class="fas fa-tags" style="color:var(--primary); font-size:16px;"></i>
      <span style="font-weight:600; font-size:13px; color:var(--text-dark);"><?php echo $pricingYear; ?> Pricing:</span>
    </div>
    <div style="display:flex; gap:var(--space-md); font-size:12px;">
      <span><strong style="color:var(--primary);">Bus</strong> Adult <strong>₹<?php echo number_format($currentPricing['bus_adult_price']); ?></strong> / Kid <strong>₹<?php echo number_format($currentPricing['bus_kid_price']); ?></strong></span>
      <span style="color:var(--border);">|</span>
      <span><strong style="color:#06b6d4;">Vehicle</strong> Adult <strong>₹<?php echo number_format($currentPricing['vehicle_adult_price']); ?></strong> / Kid <strong>₹<?php echo number_format($currentPricing['vehicle_kid_price']); ?></strong></span>
    </div>
  </div>
  <a href="admin/panihati-pricing" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
    <i class="fas fa-edit"></i> Edit Pricing
  </a>
</div>

<!-- Year-on-Year Comparison (2025 vs 2026) -->
<div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); margin-bottom:var(--space-2xl);">
  <h3 style="font-family:var(--font-heading); font-size:16px; color:var(--text-dark); margin-top:0; margin-bottom:var(--space-lg); border-bottom:2px solid var(--border); padding-bottom:8px;">Year-on-Year Comparison (2025 vs 2026)</h3>

  <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:var(--space-2xl); align-items: center;">

    <!-- Left: Comparison Metrics & Pct Changes -->
    <div>
      <div style="margin-bottom:var(--space-lg);">
        <span style="display:block; font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase;">Collections Growth</span>
        <div style="display:flex; align-items:baseline; gap:10px; margin:5px 0;">
          <strong style="font-size:24px; color:var(--text-dark);">₹<?php echo number_format($rev2026); ?></strong>
          <span style="font-size:12px; color:var(--text-light);">vs ₹<?php echo number_format($rev2025); ?> in 2025</span>
        </div>
        <span style="font-size:13px; font-weight:600; color:<?php echo $revPct >= 0 ? '#2e7d32' : '#c62828'; ?>;">
          <i class="fas <?php echo $revPct >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>"></i>
          <?php echo number_format(abs($revPct), 2); ?>% <?php echo $revPct >= 0 ? 'Increase' : 'Decrease'; ?>
        </span>
      </div>

      <div>
        <span style="display:block; font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase;">Devotee Attendance Growth</span>
        <div style="display:flex; align-items:baseline; gap:10px; margin:5px 0;">
          <strong style="font-size:24px; color:var(--text-dark);"><?php echo number_format($pass2026); ?> Devotees</strong>
          <span style="font-size:12px; color:var(--text-light);">vs <?php echo number_format($pass2025); ?> in 2025</span>
        </div>
        <span style="font-size:13px; font-weight:600; color:<?php echo $passPct >= 0 ? '#2e7d32' : '#c62828'; ?>;">
          <i class="fas <?php echo $passPct >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>"></i>
          <?php echo number_format(abs($passPct), 2); ?>% <?php echo $passPct >= 0 ? 'Increase' : 'Decrease'; ?>
        </span>
      </div>
    </div>

    <!-- Right: Comparison Grouped Bar Chart -->
    <div style="height: 250px; position: relative;">
      <canvas id="yoyComparisonChart"></canvas>
    </div>

  </div>
</div>

<!-- Executive Summary & Doughnut Charts Overview -->
<div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-xl); align-items: stretch;">

  <!-- Left Side: Executive Summary Card -->
  <div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); display:flex; flex-direction:column;">
    <h3 style="font-family:var(--font-heading); color:var(--text-dark); font-size:18px; margin-top:0; margin-bottom:var(--space-lg); border-bottom:2px solid var(--border); padding-bottom:8px;">Executive Summary</h3>

    <div style="flex-grow:1; display:flex; flex-direction:column; justify-content:space-between; font-size:13px; line-height:1.6;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="border-bottom:1px solid var(--border); color:var(--text-light); font-weight:600; font-size:12px;">
            <th style="padding:8px 10px; text-align:left; width:50%;">Metric</th>
            <th style="padding:8px 10px; text-align:right; width:50%;">Value</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom:1px solid var(--border); background:var(--cream-light);">
            <td style="padding:8px 10px; font-weight:600;">Total Collection</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--primary);">INR <?php echo number_format($totalRevenue); ?></td>
          </tr>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 10px; font-weight:600;">Total Passengers</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--text-dark);"><?php echo $totalPassengers; ?> (Bus + Vehicle)</td>
          </tr>
          <tr style="border-bottom:1px solid var(--border); background:var(--cream-light);">
            <td style="padding:8px 10px; font-weight:600;">Bus Collection</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--primary);">INR <?php echo number_format($busRevenue); ?></td>
          </tr>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 10px; font-weight:600;">Vehicle Collection</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--primary);">INR <?php echo number_format($vehicleRevenue); ?></td>
          </tr>
          <tr style="border-bottom:1px solid var(--border); background:var(--cream-light);">
            <td style="padding:8px 10px; font-weight:600;">Bus Passengers</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--text-dark);"><?php echo $busPassengers; ?> (<?php echo $busAdults; ?> Adults + <?php echo $busKids; ?> Kids)</td>
          </tr>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 10px; font-weight:600;">Vehicle Passengers</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--text-dark);"><?php echo $vehiclePassengers; ?></td>
          </tr>
          <tr style="border-bottom:1px solid var(--border); background:var(--cream-light);">
            <td style="padding:8px 10px; font-weight:600;">Average per Person</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--text-dark);">INR <?php echo number_format($avgPerPerson); ?></td>
          </tr>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 10px; font-weight:600; padding-left:20px; color:var(--text-light);"><i class="fas fa-angle-right" style="font-size:10px;"></i> Adults</td>
            <td style="padding:8px 10px; text-align:right; font-weight:600; color:var(--text-dark);"><?php echo $busAdults; ?> (via Bus)</td>
          </tr>
          <tr style="border-bottom:1px solid var(--border); background:var(--cream-light);">
            <td style="padding:8px 10px; font-weight:600; padding-left:20px; color:var(--text-light);"><i class="fas fa-angle-right" style="font-size:10px;"></i> Kids</td>
            <td style="padding:8px 10px; text-align:right; font-weight:600; color:var(--text-dark);"><?php echo $busKids; ?> (via Bus)</td>
          </tr>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 10px; font-weight:600; padding-left:20px; color:var(--text-light);"><i class="fas fa-angle-right" style="font-size:10px;"></i> Total Sadans</td>
            <td style="padding:8px 10px; text-align:right; font-weight:600; color:var(--text-dark);"><?php echo $totalSadansCount; ?> centers</td>
          </tr>
          <tr style="border-bottom:1px solid var(--border); background:var(--cream-light);">
            <td style="padding:8px 10px; font-weight:600; padding-left:20px; color:var(--text-light);"><i class="fas fa-angle-right" style="font-size:10px;"></i> Pickup Points</td>
            <td style="padding:8px 10px; text-align:right; font-weight:600; color:var(--text-dark);"><?php echo $pickupPointsCount; ?> locations</td>
          </tr>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:8px 10px; font-weight:600;">Top Sadan</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--maroon); font-size:11px;"><?php echo htmlspecialchars($topSadanText); ?></td>
          </tr>
          <tr style="border-bottom:none; background:var(--cream-light);">
            <td style="padding:8px 10px; font-weight:600;">Top Pickup Point</td>
            <td style="padding:8px 10px; text-align:right; font-weight:700; color:var(--maroon); font-size:11px;"><?php echo htmlspecialchars($topPickupText); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Side: Two Small Charts -->
  <div style="display:flex; flex-direction:column; gap:var(--space-lg); justify-content:space-between;">

    <!-- Doughnut 1: Financial Split -->
    <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); flex-grow:1; display:flex; flex-direction:column; justify-content:space-between; height: 50%;">
      <div style="text-align:center;">
        <h4 style="font-family:var(--font-heading); color:var(--text-dark); font-size:14px; margin:0 0 5px 0;">Financial Split: Bus vs Vehicle</h4>
        <span style="font-size:11px; color:var(--text-light); display:block; margin-bottom:5px;">
          Bus: <?php echo $busPassengers; ?> passengers | Vehicle: <?php echo $vehiclePassengers; ?> passengers
        </span>
      </div>
      <div style="height:140px; position:relative; display:flex; align-items:center; justify-content:center;">
        <canvas id="finSplitChart"></canvas>
      </div>
    </div>

    <!-- Doughnut 2: Bus Passengers: Adults vs Kids -->
    <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); flex-grow:1; display:flex; flex-direction:column; justify-content:space-between; height: 50%;">
      <div style="text-align:center;">
        <h4 style="font-family:var(--font-heading); color:var(--text-dark); font-size:14px; margin:0 0 5px 0;">Bus Passengers: Adults vs Kids</h4>
        <span style="font-size:11px; color:var(--text-light); display:block; margin-bottom:5px;">
          Total: <?php echo $busPassengers; ?> passengers
        </span>
      </div>
      <div style="height:140px; position:relative; display:flex; align-items:center; justify-content:center;">
        <canvas id="adultsKidsChart"></canvas>
      </div>
    </div>

  </div>


  <!-- Detailed Rankings Charts Grid -->
  <div style="display:grid; gap:var(--space-lg); margin-bottom:var(--space-2xl);">

    <!-- Left Chart: Top Bus Pickup Locations with Total -->
    <div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm);">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-md);">
        <h3 style="font-family:var(--font-heading); font-size:16px; color:var(--text-dark); margin:0;">Top Bus Pickup Locations</h3>
        <span style="background:var(--gradient-primary); color:var(--white); padding:4px 12px; border-radius:var(--radius-md); font-size:12px; font-weight:600;">Total: <?php echo number_format(array_sum(array_map('intval', array_column($pickupRankingData, 'count')))); ?> devotees</span>
      </div>
      <div style="height: 380px; position: relative;">
        <canvas id="pickupLocationChart"></canvas>
      </div>
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:var(--space-lg); justify-content:space-between;">
  <!-- Right Chart: Bhakti Sadan Ranking (by Persons) -->
  <div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-md);">
      <h3 style="font-family:var(--font-heading); font-size:16px; color:var(--text-dark); margin:0;">Bhakti Sadan Ranking (by Persons)</h3>
      <span style="background:linear-gradient(135deg, #6366f1, #4f46e5); color:var(--white); padding:4px 12px; border-radius:var(--radius-md); font-size:12px; font-weight:600;">Total: <?php echo number_format($totalPassengers); ?> devotees</span>
    </div>
      <div style="height: 380px; position: relative;">
        <canvas id="bhaktiSadanChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    function filterByYear(year) {
      var url = new URL(window.location.href);
      url.searchParams.set('year', year);
      window.location.href = url.toString();
    }

    // Chart Initializations
    document.addEventListener('DOMContentLoaded', function() {

      // Register DataLabels plugin
      if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
      }

      // 1. Bhakti Sadan Ranking (by Persons) Stacked Horizontal Bar Chart
      var sadanLabels = <?php echo json_encode(array_column($sadanRankingData, 'bhakti_sadan')); ?>;
      var busCounts = <?php echo json_encode(array_map('intval', array_column($sadanRankingData, 'bus_count'))); ?>;
      var vehicleCounts = <?php echo json_encode(array_map('intval', array_column($sadanRankingData, 'vehicle_count'))); ?>;

      var ctxSadan = document.getElementById('bhaktiSadanChart').getContext('2d');
      new Chart(ctxSadan, {
        type: 'bar',
        data: {
          labels: sadanLabels,
          datasets: [{
              label: 'Bus',
              data: busCounts,
              backgroundColor: '#6366f1', // Indigo
              borderRadius: 4
            },
            {
              label: 'Vehicle',
              data: vehicleCounts,
              backgroundColor: '#06b6d4', // Cyan
              borderRadius: 4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y', // horizontal bars
          scales: {
            x: {
              stacked: true,
              beginAtZero: true
            },
            y: {
              stacked: true
            }
          },
          plugins: {
            datalabels: {
              color: '#fff',
              anchor: 'center',
              align: 'center',
              font: { weight: 'bold', size: 10 },
              formatter: function(value) {
                return value > 0 ? value : '';
              }
            }
          }
        },
        plugins: [{
          id: 'stackedTotal',
          afterDatasetsDraw: function(chart) {
            var ctx = chart.ctx;
            var meta2 = chart.getDatasetMeta(1);

            meta2.data.forEach(function(bar, index) {
              var busVal = chart.data.datasets[0].data[index] || 0;
              var vehicleVal = chart.data.datasets[1].data[index] || 0;
              var total = busVal + vehicleVal;

              if (total > 0) {
                var xPos = bar.x + 8;
                var yPos = bar.y;

                ctx.save();
                ctx.fillStyle = '#1a1a1a';
                ctx.font = 'bold 11px sans-serif';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(total, xPos, yPos);
                ctx.restore();
              }
            });
          }
        }]
      });

      // 2. Financial Split: Bus vs Vehicle Doughnut Chart
      var ctxFinSplit = document.getElementById('finSplitChart').getContext('2d');

      var busRev = <?php echo $busRevenue; ?>;
      var vehRev = <?php echo $vehicleRevenue; ?>;
      var totalRev = busRev + vehRev;

      var busPct = totalRev > 0 ? ((busRev / totalRev) * 100).toFixed(1) : 0;
      var vehPct = totalRev > 0 ? ((vehRev / totalRev) * 100).toFixed(1) : 0;

      new Chart(ctxFinSplit, {
        type: 'doughnut',
        data: {
          labels: [
            'Bus: INR ' + busRev.toLocaleString() + ' (' + busPct + '%)',
            'Vehicle: INR ' + vehRev.toLocaleString() + ' (' + vehPct + '%)'
          ],
          datasets: [{
            data: [busRev, vehRev],
            backgroundColor: ['#6366f1', '#06b6d4'] // Indigo, Cyan
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                font: {
                  size: 10
                }
              }
            },
            datalabels: {
              display: false
            }
          }
        }
      });

      // 3. Top Bus Pickup Locations Horizontal Bar Chart
      var pickupLabels = <?php echo json_encode(array_column($pickupRankingData, 'pickup_location')); ?>;
      var pickupCounts = <?php echo json_encode(array_map('intval', array_column($pickupRankingData, 'count'))); ?>;

      var ctxPickup = document.getElementById('pickupLocationChart').getContext('2d');
      new Chart(ctxPickup, {
        type: 'bar',
        data: {
          labels: pickupLabels,
          datasets: [{
            label: 'Devotees Count',
            data: pickupCounts,
            backgroundColor: [
              '#e8944a', // Orange
              '#7b1e1e', // Maroon
              '#d4af37', // Gold Accent
              '#6366f1', // Indigo
              '#06b6d4', // Cyan
              '#2e7d32', // Green
              '#7b1fa2', // Purple
              '#c62828', // Red
              '#1565c0', // Blue
              '#e65100' // Dark Orange
            ],
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          scales: {
            x: {
              beginAtZero: true
            }
          },
          plugins: {
            datalabels: {
              color: '#fff',
              anchor: 'end',
              align: 'left',
              font: {
                weight: 'bold',
                size: 11
              },
              formatter: function(value, context) {
                return value > 0 ? value : '';
              }
            }
          }
        }
      });

      // 4. Bus Passengers: Adults vs Kids Doughnut Chart
      var ctxKids = document.getElementById('adultsKidsChart').getContext('2d');
      var busAd = <?php echo $busAdults; ?>;
      var busKd = <?php echo $busKids; ?>;
      var totalBusPass = busAd + busKd;

      var adPct = totalBusPass > 0 ? ((busAd / totalBusPass) * 100).toFixed(1) : 0;
      var kdPct = totalBusPass > 0 ? ((busKd / totalBusPass) * 100).toFixed(1) : 0;

      new Chart(ctxKids, {
        type: 'doughnut',
        data: {
          labels: [
            'Adults: ' + busAd + ' (' + adPct + '%)',
            'Kids: ' + busKd + ' (' + kdPct + '%)'
          ],
          datasets: [{
            data: [busAd, busKd],
            backgroundColor: ['#6366f1', '#f59e0b'] // Indigo, Amber
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                font: {
                  size: 10
                }
              }
            },
            datalabels: {
              display: false
            }
          }
        }
      });

      // 5. YoY Comparison Chart
      var ctxYoY = document.getElementById('yoyComparisonChart').getContext('2d');
      new Chart(ctxYoY, {
        type: 'bar',
        data: {
          labels: ['Collections (in ₹100s)', 'Devotees Count'],
          datasets: [{
              label: '2025 (Last Year)',
              data: [<?php echo $rev2025 / 100; ?>, <?php echo $pass2025; ?>],
              backgroundColor: '#9966FF',
              borderRadius: 4
            },
            {
              label: '2026 (This Year)',
              data: [<?php echo $rev2026 / 100; ?>, <?php echo $pass2026; ?>],
              backgroundColor: '#FF6384',
              borderRadius: 4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            datalabels: {
              color: '#fff',
              anchor: 'end',
              align: 'left',
              font: {
                weight: 'bold',
                size: 11
              },
              formatter: function(value, context) {
                if (context.dataIndex === 0) {
                  return '₹' + (value * 100).toLocaleString();
                }
                return value;
              }
            }
          }
        }
      });

    });
  </script>

  <!-- Aggregate Offline Summary Entries Table -->
  <?php if (!empty($aggregateEntries)): ?>
    <div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); margin-bottom:var(--space-2xl);">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg);">
        <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">
          <i class="fas fa-layer-group" style="color:var(--primary);"></i> Aggregate Offline Summary Entries
        </h3>
        <span style="font-size:12px; color:var(--text-light);">
          Showing <?php echo count($aggregateEntries); ?> entries — these are headcount totals without individual contact details
        </span>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table" style="min-width:800px;">
          <thead>
            <tr>
              <th>#</th>
              <th>Bhakti Sadan</th>
              <th>Travel Mode</th>
              <th>Adults</th>
              <th>Kids</th>
              <th>Total Persons</th>
              <th>Amount</th>
              <th>Pickup</th>
              <th>Source</th>
              <th>Year</th>
              <th>Entered By</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($aggregateEntries as $agg):
              $totalPersons = $agg['adults_count'] + $agg['kids_count'];
              $modeLabel = $agg['travel_mode'] === 'bus' ? 'Bus' : 'Own Vehicle';
            ?>
              <tr>
                <td style="font-weight:600; color:var(--text-light);"><?php echo $agg['id']; ?></td>
                <td><strong style="color:var(--dark);"><?php echo htmlspecialchars($agg['bhakti_sadan']); ?></strong></td>
                <td><span class="badge <?php echo $agg['travel_mode'] === 'bus' ? 'badge-info' : 'badge-secondary'; ?>"><?php echo $modeLabel; ?></span></td>
                <td style="text-align:center; font-weight:600;"><?php echo $agg['adults_count']; ?></td>
                <td style="text-align:center;"><?php echo $agg['kids_count']; ?></td>
                <td style="text-align:center; font-weight:700; color:var(--primary);"><?php echo $totalPersons; ?></td>
                <td style="font-weight:600; color:var(--maroon);">₹<?php echo number_format($agg['amount'], 2); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($agg['pickup_location'] ?: '-'); ?></td>
                <td><span style="font-size:11px; background:var(--cream); padding:2px 6px; border-radius:3px;"><?php echo htmlspecialchars($agg['source_label'] ?: '-'); ?></span></td>
                <td style="font-weight:600;"><?php echo $agg['reported_year']; ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($agg['created_by_name'] ?: 'N/A'); ?></td>
                <td style="font-size:11px; color:var(--text-light); white-space:nowrap;"><?php echo date('d-M-Y H:i', strtotime($agg['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <?php include 'partials/footer.php'; ?>