<?php
/**
 * Panihati Yatra — Add Offline Entry (Bulk Summary)
 * 
 * Dedicated page for entering aggregate headcount totals per sadan manually or via CSV upload.
 * Accessible by: super_admin, travel_agent
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../panihati-helpers.php';

$pageTitle = 'Add Offline Entry';
$activePage = 'panihati-bulk-summary';
include 'partials/header.php';

// Enforce permission
requirePermission('panihati.create');

$db = getDB();
$successMsg = '';
$errorMsg = '';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle CSV Template Download
if (isset($_GET['action']) && $_GET['action'] === 'download_template') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="panihati_offline_template.csv"');
    $output = fopen('php://output', 'w');
    // Header row
    fputcsv($output, ['Bhakti Sadan', 'Bus Adults', 'Bus Kids', 'Own Vehicle Adults', 'Own Vehicle Kids', 'Pickup Location', 'Notes']);
    // Sample rows
    fputcsv($output, ['Sri Sri Krishna Balaram Sadan', '10', '2', '5', '0', 'Majestic', 'Sponsors']);
    fputcsv($output, ['Sridhar Sadan', '0', '0', '8', '2', 'Own Vehicle', 'Regular devotees']);
    fclose($output);
    exit();
}

// Fetch dynamic options for validation and dropdowns
try {
    $stmt = $db->query("SELECT name FROM panihati_bhakti_sadans WHERE is_active = 1 ORDER BY name ASC");
    $dbSadans = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $db->query("SELECT name FROM panihati_pickup_locations WHERE is_active = 1 ORDER BY name ASC");
    $dbPickups = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $dbSadans = [];
    $dbPickups = [];
}

$pricing = getPanihatiPricing();

$pickupOptionsHtml = '';
foreach ($dbPickups as $p) {
    if (trim(strtolower($p)) === 'own vehicle') {
        continue;
    }
    $pickupOptionsHtml .= '<option value="' . htmlspecialchars($p, ENT_QUOTES) . '">' . htmlspecialchars($p) . '</option>';
}

// Handle Form Posts (Manual Grid & CSV Upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = "Invalid CSRF token.";
    } else {
        $action = $_POST['action'];
        $reportedYear = intval($_POST['reported_year'] ?? date('Y'));
        $sourceLabel = trim($_POST['source_label'] ?? 'Manual count');
        $adminId = intval($_SESSION['admin_id'] ?? 0);

        // Fetch pricing for the selected year
        $yrPricing = getPanihatiPricing($reportedYear);

        if ($action === 'aggregate_offline') {
            $rows = $_POST['rows'] ?? [];
            $insertedCount = 0;

            if (empty($rows) || !is_array($rows)) {
                $errorMsg = "No data rows submitted.";
            } else {
                $db->beginTransaction();
                try {
                    $stmt = $db->prepare("
                        INSERT INTO panihati_yatra_offline_aggregates
                        (bhakti_sadan, travel_mode, adults_count, kids_count, amount, pickup_location, reported_year, source_label, notes, created_by_admin_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    foreach ($rows as $row) {
                        $sadan = trim($row['sadan'] ?? '');
                        if (empty($sadan)) continue;

                        $busAdults = intval($row['bus_adults'] ?? 0);
                        $busKids = intval($row['bus_kids'] ?? 0);
                        $vehicleAdults = intval($row['vehicle_adults'] ?? 0);
                        $vehicleKids = intval($row['vehicle_kids'] ?? 0);
                        $pickup = trim($row['pickup'] ?? '');
                        $notes = trim($row['notes'] ?? '');

                        if ($busAdults > 0 || $busKids > 0) {
                            $busAmount = ($busAdults * $yrPricing['bus_adult_price']) + ($busKids * $yrPricing['bus_kid_price']);
                            $stmt->execute([
                                $sadan, 'bus', $busAdults, $busKids, $busAmount,
                                !empty($pickup) ? $pickup : null,
                                $reportedYear, $sourceLabel, $notes, $adminId
                            ]);
                            $insertedCount++;
                        }

                        if ($vehicleAdults > 0 || $vehicleKids > 0) {
                            $vehicleAmount = ($vehicleAdults * $yrPricing['vehicle_adult_price']) + ($vehicleKids * $yrPricing['vehicle_kid_price']);
                            $stmt->execute([
                                $sadan, 'own_vehicle', $vehicleAdults, $vehicleKids, $vehicleAmount,
                                !empty($pickup) ? $pickup : null,
                                $reportedYear, $sourceLabel, $notes, $adminId
                            ]);
                            $insertedCount++;
                        }
                    }

                    $db->commit();

                    if ($insertedCount > 0) {
                        $successMsg = "Successfully added $insertedCount aggregate offline entries.";
                    } else {
                        $errorMsg = "No valid data rows were inserted. Please enter at least some counts.";
                    }
                } catch (Exception $ex) {
                    $db->rollBack();
                    $errorMsg = "Failed to save aggregate entries: " . $ex->getMessage();
                }
            }
        } elseif ($action === 'csv_upload') {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $errorMsg = "Please select a valid CSV file to upload.";
            } else {
                $tmpPath = $_FILES['csv_file']['tmp_name'];
                
                // Validate CSV extension
                $fileExtension = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
                if ($fileExtension !== 'csv') {
                    $errorMsg = "Invalid file type. Please upload a standard CSV file.";
                } else {
                    $fileHandle = fopen($tmpPath, 'r');
                    if ($fileHandle === false) {
                        $errorMsg = "Failed to open uploaded CSV file.";
                    } else {
                        $db->beginTransaction();
                        try {
                            $stmt = $db->prepare("
                                INSERT INTO panihati_yatra_offline_aggregates
                                (bhakti_sadan, travel_mode, adults_count, kids_count, amount, pickup_location, reported_year, source_label, notes, created_by_admin_id)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");

                            // Skip header row
                            $header = fgetcsv($fileHandle);
                            $insertedCount = 0;

                            while (($row = fgetcsv($fileHandle)) !== false) {
                                // Match columns: Bhakti Sadan | Bus Adults | Bus Kids | Own Vehicle Adults | Own Vehicle Kids | Pickup Location | Notes
                                $sadan = trim($row[0] ?? '');
                                if (empty($sadan)) continue;

                                $busAdults = intval($row[1] ?? 0);
                                $busKids = intval($row[2] ?? 0);
                                $vehicleAdults = intval($row[3] ?? 0);
                                $vehicleKids = intval($row[4] ?? 0);
                                $pickup = trim($row[5] ?? '');
                                $notes = trim($row[6] ?? '');

                                if ($busAdults > 0 || $busKids > 0) {
                                    $busAmount = ($busAdults * $yrPricing['bus_adult_price']) + ($busKids * $yrPricing['bus_kid_price']);
                                    $stmt->execute([
                                        $sadan, 'bus', $busAdults, $busKids, $busAmount,
                                        !empty($pickup) ? $pickup : null,
                                        $reportedYear, $sourceLabel, $notes, $adminId
                                    ]);
                                    $insertedCount++;
                                }

                                if ($vehicleAdults > 0 || $vehicleKids > 0) {
                                    $vehicleAmount = ($vehicleAdults * $yrPricing['vehicle_adult_price']) + ($vehicleKids * $yrPricing['vehicle_kid_price']);
                                    $stmt->execute([
                                        $sadan, 'own_vehicle', $vehicleAdults, $vehicleKids, $vehicleAmount,
                                        !empty($pickup) ? $pickup : null,
                                        $reportedYear, $sourceLabel, $notes, $adminId
                                    ]);
                                    $insertedCount++;
                                }
                            }

                            fclose($fileHandle);
                            $db->commit();

                            if ($insertedCount > 0) {
                                $successMsg = "Successfully uploaded CSV. Imported $insertedCount aggregate offline records.";
                            } else {
                                $errorMsg = "No valid data rows found in the CSV file.";
                            }
                        } catch (Exception $ex) {
                            fclose($fileHandle);
                            $db->rollBack();
                            $errorMsg = "CSV parsing failed: " . $ex->getMessage();
                        }
                    }
                }
            }
        }
    }
}

// Fetch aggregate offline entries for display
$aggregateEntries = [];
try {
    $stmt = $db->prepare("
        SELECT a.*, adm.full_name as created_by_name
        FROM panihati_yatra_offline_aggregates a
        LEFT JOIN admins adm ON a.created_by_admin_id = adm.id
        ORDER BY a.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $aggregateEntries = $stmt->fetchAll();
} catch (Exception $e) {
    $aggregateEntries = [];
}
?>

<div class="admin-content-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-xl);">
  <div>
    <h1 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">
      <i class="fas fa-file-upload" style="color:var(--primary);"></i> Add Offline Entry
    </h1>
    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:var(--font-size-sm);">Enter headcount totals per sadan manually or upload a CSV file. Amounts auto-calculate.</p>
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

<!-- Tab Selection Switcher -->
<div style="display:flex; gap:12px; margin-bottom:var(--space-xl); border-bottom:1px solid var(--border); padding-bottom:12px;">
  <button type="button" onclick="switchTab('manual')" id="tab-manual" class="btn" style="padding: 8px 16px; font-size: 13px; font-weight:600; display:inline-flex; align-items:center; gap:6px; background:var(--primary); color:var(--white); border:none; border-radius:var(--radius-md); cursor:pointer; transition:all 0.2s;">
    <i class="fas fa-keyboard"></i> Enter Manually
  </button>
  <button type="button" onclick="switchTab('csv')" id="tab-csv" class="btn" style="padding: 8px 16px; font-size: 13px; font-weight:600; display:inline-flex; align-items:center; gap:6px; background:transparent; color:var(--text-dark); border:1px solid var(--border); border-radius:var(--radius-md); cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='var(--cream-light)'" onmouseout="if(activeMode!=='csv')this.style.background='transparent'">
    <i class="fas fa-file-csv"></i> Upload CSV File
  </button>
</div>

<!-- ========================================== -->
<!-- CONTAINER 1: MANUAL GRID ENTRY             -->
<!-- ========================================== -->
<div id="manual-container" style="display:block;">
  <div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-md); margin-bottom:var(--space-2xl);">
    <p style="font-size:13px; color:var(--text-light); line-height:1.6; margin-bottom:var(--space-lg); margin-top:0;">
      Use this form to enter headcount totals per sadan. You can add multiple sadan rows in a single batch.
    </p>

    <form action="admin/panihati-bulk-summary" method="POST" id="aggregateForm">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="action" value="aggregate_offline">

      <!-- Meta fields -->
      <div style="display:grid; grid-template-columns: 1fr 1fr 2fr; gap:var(--space-md); margin-bottom:var(--space-lg); padding:var(--space-md); background:var(--cream-light); border-radius:var(--radius-md);">
        <div>
          <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Reported Year *</label>
          <select name="reported_year" id="aggReportedYear" onchange="recalcAggregate()" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
            <?php for ($y = date('Y'); $y >= 2025; $y--): ?>
              <option value="<?php echo $y; ?>" <?php echo $y === (int)date('Y') ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Source *</label>
          <select name="source_label" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
            <option value="Manual count">Manual count</option>
            <option value="Paper register">Paper register</option>
            <option value="Phone report">Phone report</option>
            <option value="WhatsApp group">WhatsApp group</option>
            <option value="Office record">Office record</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div style="display:flex; align-items:flex-end; justify-content:flex-end;">
          <button type="button" onclick="addAggregateRow()" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:4px; font-size:12px;">
            <i class="fas fa-plus"></i> Add Sadan Row
          </button>
        </div>
      </div>

      <!-- Dynamic rows container -->
      <div id="aggregateRowsContainer">
        <!-- Rows added via JS -->
      </div>

      <!-- Preview & Submit -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-top:var(--space-lg); padding-top:var(--space-lg); border-top:1px solid var(--border);">
        <div style="font-size:13px; color:var(--text-dark);">
          <strong>Total so far:</strong>
          <span id="aggTotalAdults" style="font-weight:700; color:var(--primary);">0</span> adults,
          <span id="aggTotalKids" style="font-weight:700;">0</span> kids,
          <span id="aggTotalAmount" style="font-weight:700; color:var(--maroon);">₹0</span>
        </div>
        <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
          <i class="fas fa-save"></i> Save Entries
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================== -->
<!-- CONTAINER 2: CSV FILE UPLOAD               -->
<!-- ========================================== -->
<div id="csv-container" style="display:none;">
  <div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-md); margin-bottom:var(--space-2xl);">
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--space-lg); flex-wrap:wrap; gap:15px;">
      <div>
        <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0; font-size:15px;">CSV Template Upload</h3>
        <p style="font-size:12px; color:var(--text-light); margin:4px 0 0 0;">Upload a CSV file containing offline counts. Prices are computed automatically.</p>
      </div>
      <a href="admin/panihati-bulk-summary?action=download_template" class="btn btn-sm" style="display:inline-flex; align-items:center; gap:6px; border:1px solid var(--primary); color:var(--primary); background:transparent; font-size:11px;" onmouseover="this.style.background='var(--cream-light)'" onmouseout="this.style.background='transparent'">
        <i class="fas fa-download"></i> Download CSV Template
      </a>
    </div>

    <!-- Instructions / Mapping guide -->
    <div style="font-size:12px; line-height:1.6; color:var(--text-dark); background:var(--cream-light); padding:var(--space-md); border-radius:var(--radius-md); margin-bottom:var(--space-xl); border:1px solid var(--border);">
      <h4 style="margin:0 0 6px 0; font-weight:600;"><i class="fas fa-info-circle" style="color:var(--primary);"></i> CSV Column Ordering Guidelines:</h4>
      <p style="margin:0 0 4px 0;">Please ensure your uploaded CSV matches the following column order exactly:</p>
      <ol style="margin:0; padding-left:20px; font-family:var(--font-mono); font-size:11px; color:#c62828;">
        <li>Bhakti Sadan (string) *</li>
        <li>Bus Adults (integer)</li>
        <li>Bus Kids (integer)</li>
        <li>Own Vehicle Adults (integer)</li>
        <li>Own Vehicle Kids (integer)</li>
        <li>Pickup Location (string)</li>
        <li>Notes (string)</li>
      </ol>
    </div>

    <form action="admin/panihati-bulk-summary" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="action" value="csv_upload">

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-md); margin-bottom:var(--space-xl);">
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:4px;">Reported Year *</label>
          <select name="reported_year" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:4px; font-size:12px; background:var(--white);">
            <?php for ($y = date('Y'); $y >= 2025; $y--): ?>
              <option value="<?php echo $y; ?>" <?php echo $y === (int)date('Y') ? 'selected' : ''; ?>><?php echo $y; ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:12px; font-weight:600; color:var(--text-dark); margin-bottom:4px;">Source Label</label>
          <input type="text" name="source_label" value="CSV upload" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
        </div>
      </div>

      <!-- Drag & Drop Zone CSS visual container -->
      <div style="border: 2px dashed var(--border); border-radius:var(--radius-lg); background:#fcfcfc; padding:var(--space-2xl) var(--space-xl); text-align:center; margin-bottom:var(--space-xl); transition:all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#faf8f5';" onmouseout="this.style.borderColor='var(--border)'; this.style.background='#fcfcfc';">
        <i class="fas fa-file-excel" style="font-size:36px; color:var(--text-light); margin-bottom:var(--space-md);"></i>
        <div style="font-size:14px; font-weight:600; color:var(--text-dark); margin-bottom:4px;">Select CSV file to import</div>
        <div style="font-size:11px; color:var(--text-light); margin-bottom:var(--space-lg);">Supported formats: .csv only (max 2MB)</div>
        <input type="file" name="csv_file" accept=".csv" required style="font-size:12px; display:inline-block; margin:0 auto; padding:6px 12px; background:var(--white); border:1px solid var(--border); border-radius:4px;">
      </div>

      <div style="display:flex; justify-content:flex-end;">
        <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
          <i class="fas fa-upload"></i> Upload & Import File
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Dynamic pricing from the database
var PANIHATI_PRICES = {
  bus_adult: <?php echo $pricing['bus_adult_price']; ?>,
  bus_kid: <?php echo $pricing['bus_kid_price']; ?>,
  vehicle_adult: <?php echo $pricing['vehicle_adult_price']; ?>,
  vehicle_kid: <?php echo $pricing['vehicle_kid_price']; ?>
};

let aggRowCount = 0;
let activeMode = 'manual';

function switchTab(mode) {
    activeMode = mode;
    const tabManual = document.getElementById('tab-manual');
    const tabCsv = document.getElementById('tab-csv');
    const manualContainer = document.getElementById('manual-container');
    const csvContainer = document.getElementById('csv-container');

    if (mode === 'manual') {
        tabManual.style.background = 'var(--primary)';
        tabManual.style.color = 'var(--white)';
        tabManual.style.borderColor = 'var(--primary)';
        
        tabCsv.style.background = 'transparent';
        tabCsv.style.color = 'var(--text-dark)';
        tabCsv.style.borderColor = 'var(--border)';
        
        manualContainer.style.display = 'block';
        csvContainer.style.display = 'none';
    } else {
        tabCsv.style.background = 'var(--primary)';
        tabCsv.style.color = 'var(--white)';
        tabCsv.style.borderColor = 'var(--primary)';
        
        tabManual.style.background = 'transparent';
        tabManual.style.color = 'var(--text-dark)';
        tabManual.style.borderColor = 'var(--border)';
        
        csvContainer.style.display = 'block';
        manualContainer.style.display = 'none';
    }
}

function getCurrentPrices() {
  return PANIHATI_PRICES;
}

function addAggregateRow() {
  aggRowCount++;
  var container = document.getElementById('aggregateRowsContainer');
  
  var sadanOptions = <?php echo json_encode($dbSadans); ?>;
  var sadanHtml = '<option value="" disabled selected>-- Select Sadan --</option>';
  sadanOptions.forEach(function(s) {
    sadanHtml += '<option value="' + s.replace(/'/g, "&apos;") + '">' + s + '</option>';
  });

  var pickupOptionsHtml = <?php echo json_encode($pickupOptionsHtml); ?>;

  var row = document.createElement('div');
  row.id = 'aggRow_' + aggRowCount;
  row.style.cssText = 'background:var(--cream-light); border:1px solid var(--border); border-radius:var(--radius-md); padding:var(--space-md); margin-bottom:var(--space-md); position:relative;';
  
  row.innerHTML = '\
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">\
      <strong style="font-size:13px; color:var(--text-dark);">Sadan #' + aggRowCount + '</strong>\
      <button type="button" onclick="removeAggregateRow(' + aggRowCount + ')" style="background:none; border:none; color:#c62828; cursor:pointer; font-size:14px;" title="Remove this row">\
        <i class="fas fa-times-circle"></i>\
      </button>\
    </div>\
    <div style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1.5fr 1fr; gap:8px; align-items:end;">\
      <div>\
        <label style="display:block; font-size:10px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Bhakti Sadan *</label>\
        <select name="rows[' + aggRowCount + '][sadan]" required style="width:100%; padding:6px 8px; border:1px solid var(--border); border-radius:4px; font-size:11px; background:var(--white);">' + sadanHtml + '</select>\
      </div>\
      <div>\
        <label style="display:block; font-size:10px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Bus Adults</label>\
        <input type="number" name="rows[' + aggRowCount + '][bus_adults]" min="0" value="0" onchange="recalcAggregate()" style="width:100%; padding:6px 8px; border:1px solid var(--border); border-radius:4px; font-size:11px;">\
      </div>\
      <div>\
        <label style="display:block; font-size:10px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Bus Kids</label>\
        <input type="number" name="rows[' + aggRowCount + '][bus_kids]" min="0" value="0" onchange="recalcAggregate()" style="width:100%; padding:6px 8px; border:1px solid var(--border); border-radius:4px; font-size:11px;">\
      </div>\
      <div>\
        <label style="display:block; font-size:10px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Vehicle Adults</label>\
        <input type="number" name="rows[' + aggRowCount + '][vehicle_adults]" min="0" value="0" onchange="recalcAggregate()" style="width:100%; padding:6px 8px; border:1px solid var(--border); border-radius:4px; font-size:11px;">\
      </div>\
      <div>\
        <label style="display:block; font-size:10px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Vehicle Kids</label>\
        <input type="number" name="rows[' + aggRowCount + '][vehicle_kids]" min="0" value="0" onchange="recalcAggregate()" style="width:100%; padding:6px 8px; border:1px solid var(--border); border-radius:4px; font-size:11px;">\
      </div>\
      <div>\
        <label style="display:block; font-size:10px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Pickup (optional)</label>\
        <select name="rows[' + aggRowCount + '][pickup]" style="width:100%; padding:6px 8px; border:1px solid var(--border); border-radius:4px; font-size:11px; background:var(--white);">\
          <option value="">-- Not known --</option>\
          <option value="Own Vehicle">Own Vehicle</option>\
          ' + pickupOptionsHtml + '\
        </select>\
      </div>\
      <div>\
        <label style="display:block; font-size:10px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Notes</label>\
        <input type="text" name="rows[' + aggRowCount + '][notes]" placeholder="Optional" style="width:100%; padding:6px 8px; border:1px solid var(--border); border-radius:4px; font-size:11px;">\
      </div>\
    </div>\
  ';
  
  container.appendChild(row);
  recalcAggregate();
}

function removeAggregateRow(id) {
  var row = document.getElementById('aggRow_' + id);
  if (row) {
    row.remove();
    recalcAggregate();
  }
}

function recalcAggregate() {
  var totalAdults = 0, totalKids = 0, totalAmount = 0;
  var prices = getCurrentPrices();
  
  var inputs = document.querySelectorAll('#aggregateRowsContainer input[type="number"]');
  inputs.forEach(function(inp) {
    var val = parseInt(inp.value, 10) || 0;
    var name = inp.getAttribute('name');
    if (name && name.indexOf('bus_adults') > -1) {
      totalAdults += val;
      totalAmount += val * prices.bus_adult;
    } else if (name && name.indexOf('bus_kids') > -1) {
      totalKids += val;
      totalAmount += val * prices.bus_kid;
    } else if (name && name.indexOf('vehicle_adults') > -1) {
      totalAdults += val;
      totalAmount += val * prices.vehicle_adult;
    } else if (name && name.indexOf('vehicle_kids') > -1) {
      totalKids += val;
      totalAmount += val * prices.vehicle_kid;
    }
  });
  
  document.getElementById('aggTotalAdults').textContent = totalAdults;
  document.getElementById('aggTotalKids').textContent = totalKids;
  document.getElementById('aggTotalAmount').textContent = '₹' + totalAmount.toLocaleString('en-IN');
}

// Auto-add first row on page load
document.addEventListener('DOMContentLoaded', function() {
  addAggregateRow();
});
</script>

<!-- Aggregate Offline Summary Entries Table -->
<?php if (!empty($aggregateEntries)): ?>
<div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); margin-bottom:var(--space-2xl);">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg);">
    <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">
      <i class="fas fa-table" style="color:var(--primary);"></i> Recently Added Aggregate Entries
    </h3>
    <span style="font-size:12px; color:var(--text-light);">
      Showing <?php echo count($aggregateEntries); ?> most recent entries
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
