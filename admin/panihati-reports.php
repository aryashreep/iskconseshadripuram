<?php
/**
 * Panihati Yatra Report Download Panel
 * 
 * Allows super_admin and travel_agent to query and export registration records based on specific filters.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// Access control
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: " . BASE_URL . "admin/login");
    exit();
}

$role = $_SESSION['admin_role'] ?? 'editor';
if ($role !== 'super_admin' && $role !== 'travel_agent') {
    header("Location: " . BASE_URL . "admin/dashboard");
    exit();
}

$db = getDB();

// Handle Report Export Action
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $selectedYear = isset($_GET['year']) ? trim($_GET['year']) : 'all';
    $selectedStatus = isset($_GET['status']) ? trim($_GET['status']) : 'all';
    $selectedSadan = isset($_GET['sadan']) ? trim($_GET['sadan']) : 'all';
    $selectedPickup = isset($_GET['pickup']) ? trim($_GET['pickup']) : 'all';
    $format = isset($_GET['format']) ? trim($_GET['format']) : 'csv';

    $whereClauses = ["1=1"];
    $params = [];

    if ($selectedYear !== 'all') {
        $whereClauses[] = "YEAR(created_at) = ?";
        $params[] = intval($selectedYear);
    }
    if ($selectedStatus !== 'all') {
        $whereClauses[] = "payment_status = ?";
        $params[] = $selectedStatus;
    }
    if ($selectedSadan !== 'all') {
        $whereClauses[] = "bhakti_sadan = ?";
        $params[] = $selectedSadan;
    }
    if ($selectedPickup !== 'all') {
        $whereClauses[] = "pickup_location = ?";
        $params[] = $selectedPickup;
    }

    $whereSql = implode(" AND ", $whereClauses);
    $stmt = $db->prepare("SELECT * FROM panihati_yatra_registrations WHERE $whereSql ORDER BY id DESC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = 'panihati_report_' . ($selectedYear !== 'all' ? $selectedYear . '_' : '') . date('Ymd_His');

    if ($format === 'xls') {
        // Output Excel XML Spreadsheet
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        
        echo '<?xml version="1.0"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        echo ' <Worksheet ss:Name="Registrations">' . "\n";
        echo '  <Table>' . "\n";
        
        // Headers
        echo '   <Row>' . "\n";
        foreach (['ID', 'Name', 'Phone', 'Email', 'Travel Mode', 'Adults Count', 'Kids Count', 'Bhakti Sadan', 'Pickup Location', 'Amount Paid', 'Payment Status', 'Created At'] as $h) {
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>' . "\n";
        }
        echo '   </Row>' . "\n";
        
        // Data Rows
        foreach ($rows as $row) {
            echo '   <Row>' . "\n";
            echo '    <Cell><Data ss:Type="Number">' . $row['id'] . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['name']) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['phone']) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['email']) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['travel_mode']) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="Number">' . $row['adults_count'] . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="Number">' . $row['kids_count'] . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['bhakti_sadan']) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['pickup_location']) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="Number">' . $row['amount'] . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['payment_status']) . '</Data></Cell>' . "\n";
            echo '    <Cell><Data ss:Type="String">' . htmlspecialchars($row['created_at']) . '</Data></Cell>' . "\n";
            echo '   </Row>' . "\n";
        }
        
        echo '  </Table>' . "\n";
        echo ' </Worksheet>' . "\n";
        echo '</Workbook>' . "\n";
        exit();
    } else {
        // Output CSV File
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Phone', 'Email', 'Travel Mode', 'Adults Count', 'Kids Count', 'Bhakti Sadan', 'Pickup Location', 'Amount Paid', 'Payment Status', 'Created At']);
        
        foreach ($rows as $row) {
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
                $row['created_at']
            ]);
        }
        fclose($output);
        exit();
    }
}

// Populate Filter Values on Load
try {
    // 1. Available Years
    $stmt = $db->query("SELECT DISTINCT YEAR(created_at) as year FROM panihati_yatra_registrations ORDER BY year DESC");
    $availableYears = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Bhakti Sadans
    $stmt = $db->query("SELECT DISTINCT name FROM panihati_bhakti_sadans ORDER BY name ASC");
    $dbSadans = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Pickup Locations
    $stmt = $db->query("SELECT DISTINCT name FROM panihati_pickup_locations ORDER BY name ASC");
    $dbPickups = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Current page filter preview counts
    $prevYear = isset($_GET['year']) ? trim($_GET['year']) : 'all';
    $prevStatus = isset($_GET['status']) ? trim($_GET['status']) : 'all';
    $prevSadan = isset($_GET['sadan']) ? trim($_GET['sadan']) : 'all';
    $prevPickup = isset($_GET['pickup']) ? trim($_GET['pickup']) : 'all';

    $whereClauses = ["1=1"];
    $params = [];
    if ($prevYear !== 'all') {
        $whereClauses[] = "YEAR(created_at) = ?";
        $params[] = intval($prevYear);
    }
    if ($prevStatus !== 'all') {
        $whereClauses[] = "payment_status = ?";
        $params[] = $prevStatus;
    }
    if ($prevSadan !== 'all') {
        $whereClauses[] = "bhakti_sadan = ?";
        $params[] = $prevSadan;
    }
    if ($prevPickup !== 'all') {
        $whereClauses[] = "pickup_location = ?";
        $params[] = $prevPickup;
    }
    $whereSql = implode(" AND ", $whereClauses);
    $stmt = $db->prepare("SELECT COUNT(*) FROM panihati_yatra_registrations WHERE $whereSql");
    $stmt->execute($params);
    $previewCount = $stmt->fetchColumn();

} catch (PDOException $e) {
    $errorMsg = 'A database error occurred. Please try again.';
}

$pageTitle = 'Panihati Yatra Reports';
$activePage = 'panihati-reports';
include 'partials/header.php';
?>

<div class="admin-content-header" style="margin-bottom:var(--space-xl);">
  <div>
    <h1 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">Download Reports</h1>
    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:var(--font-size-sm);">Customize criteria and export devotee registration records.</p>
  </div>
</div>

<?php if (!empty($errorMsg)): ?>
  <div style="background:#ffebee; border:1px solid #ffcdd2; padding:var(--space-md); border-radius:var(--radius-md); color:#c62828; margin-bottom:var(--space-lg); font-size:var(--font-size-sm); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-exclamation-circle"></i>
    <div><?php echo htmlspecialchars($errorMsg); ?></div>
  </div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1.2fr 1fr; gap:var(--space-2xl); align-items:start; margin-bottom:var(--space-3xl);">
  
  <!-- Left Side: Filter Form -->
  <div style="background:var(--white); padding:var(--space-2xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm);">
    <h3 style="font-family:var(--font-heading); font-size:16px; color:var(--text-dark); margin-top:0; margin-bottom:var(--space-xl); border-bottom:2px solid var(--border); padding-bottom:8px;">Report Customization</h3>
    
    <form action="admin/panihati-reports" method="GET" id="reportForm">
      <input type="hidden" name="action" value="export">

      <!-- Year Option -->
      <div style="margin-bottom:var(--space-lg);">
        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:var(--space-xs);">Select Year</label>
        <select name="year" onchange="updatePreviewCount()" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:4px; font-family:var(--font-body); font-size:13px; background:var(--white); outline:none;">
          <option value="all" <?php echo $prevYear === 'all' ? 'selected' : ''; ?>>All Years</option>
          <?php foreach ($availableYears as $y): ?>
            <option value="<?php echo $y; ?>" <?php echo (string)$prevYear === (string)$y ? 'selected' : ''; ?>><?php echo $y; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Payment Status -->
      <div style="margin-bottom:var(--space-lg);">
        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:var(--space-xs);">Payment Status</label>
        <select name="status" onchange="updatePreviewCount()" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:4px; font-family:var(--font-body); font-size:13px; background:var(--white); outline:none;">
          <option value="all" <?php echo $prevStatus === 'all' ? 'selected' : ''; ?>>All Statuses</option>
          <option value="paid" <?php echo $prevStatus === 'paid' ? 'selected' : ''; ?>>Paid (Online)</option>
          <option value="offline" <?php echo $prevStatus === 'offline' ? 'selected' : ''; ?>>Offline (Manual Upload)</option>
          <option value="failed" <?php echo $prevStatus === 'failed' ? 'selected' : ''; ?>>Failed / Pending</option>
        </select>
      </div>

      <!-- Bhakti Sadan -->
      <div style="margin-bottom:var(--space-lg);">
        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:var(--space-xs);">Bhakti Sadan</label>
        <select name="sadan" onchange="updatePreviewCount()" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:4px; font-family:var(--font-body); font-size:13px; background:var(--white); outline:none;">
          <option value="all" <?php echo $prevSadan === 'all' ? 'selected' : ''; ?>>All Bhakti Sadans</option>
          <?php foreach ($dbSadans as $s): ?>
            <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $prevSadan === $s ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Pickup Location -->
      <div style="margin-bottom:var(--space-lg);">
        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:var(--space-xs);">Pickup Location</label>
        <select name="pickup" onchange="updatePreviewCount()" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:4px; font-family:var(--font-body); font-size:13px; background:var(--white); outline:none;">
          <option value="all" <?php echo $prevPickup === 'all' ? 'selected' : ''; ?>>All Locations</option>
          <option value="Own Vehicle" <?php echo $prevPickup === 'Own Vehicle' ? 'selected' : ''; ?>>Own Vehicle (No Bus)</option>
          <?php foreach ($dbPickups as $p): ?>
            <option value="<?php echo htmlspecialchars($p); ?>" <?php echo $prevPickup === $p ? 'selected' : ''; ?>><?php echo htmlspecialchars($p); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- File Format -->
      <div style="margin-bottom:var(--space-xl);">
        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:var(--space-xs);">Report File Format</label>
        <div style="display:flex; gap:20px; font-size:13px;">
          <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
            <input type="radio" name="format" value="csv" checked style="accent-color:var(--primary);"> CSV Format (.csv)
          </label>
          <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
            <input type="radio" name="format" value="xls" style="accent-color:var(--primary);"> Excel XML Format (.xls)
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; display:inline-flex; align-items:center; justify-content:center; gap:8px;">
        <i class="fas fa-file-download"></i> Download Report File
      </button>
    </form>
  </div>

  <!-- Right Side: Preview Stats -->
  <div style="background:var(--cream-light); padding:var(--space-2xl); border-radius:var(--radius-lg); border:1px solid var(--border); display:flex; flex-direction:column; justify-content:space-between; min-height:280px;">
    <div>
      <h3 style="font-family:var(--font-heading); font-size:16px; color:var(--text-dark); margin-top:0; margin-bottom:var(--space-md);">Report Summary Preview</h3>
      <p style="color:var(--text-light); font-size:13px; line-height:1.6; margin-bottom:var(--space-xl);">
        See how many registrations match your currently selected criteria before downloading. Changing fields on the left automatically updates this preview.
      </p>
    </div>
    
    <div style="text-align:center; background:var(--white); padding:var(--space-xl); border-radius:var(--radius-md); border:1px solid var(--border); box-shadow:var(--shadow-sm);">
      <span style="font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase; display:block; margin-bottom:5px;">Matching Records</span>
      <strong id="previewRecordCount" style="font-size:36px; color:var(--primary);"><?php echo number_format($previewCount); ?></strong>
      <span style="font-size:12px; color:var(--text-light); display:block; margin-top:5px;">devotees ready to export</span>
    </div>
  </div>

</div>

<script>
function updatePreviewCount() {
  const form = document.getElementById('reportForm');
  const year = form.year.value;
  const status = form.status.value;
  const sadan = form.sadan.value;
  const pickup = form.pickup.value;
  
  // Set window location search parameters to reload the page state dynamically to update the preview counts
  const url = new URL(window.location.href);
  url.searchParams.delete('action'); // prevent triggering export file download
  url.searchParams.set('year', year);
  url.searchParams.set('status', status);
  url.searchParams.set('sadan', sadan);
  url.searchParams.set('pickup', pickup);
  
  window.location.href = url.toString();
}
</script>

<?php include 'partials/footer.php'; ?>
