<?php
/**
 * Panihati Yatra — Add Offline Entry
 * 
 * Dedicated page for adding offline registrations via Excel upload or manual entry.
 * Accessible by: super_admin, travel_agent
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/panihati-helpers.php';

$pageTitle = 'Add Offline Entry';
$activePage = 'panihati-add-offline';
include 'partials/header.php';

// Enforce role
requireRole(['travel_agent']);

$db = getDB();
$successMsg = '';
$errorMsg = '';
$warningMsg = '';

// Get current pricing
$pricing = getPanihatiPricing();
$busAdultPrice = $pricing['bus_adult_price'];
$busKidPrice = $pricing['bus_kid_price'];
$vehicleAdultPrice = $pricing['vehicle_adult_price'];
$vehicleKidPrice = $pricing['vehicle_kid_price'];
$defaultAmount = ($busAdultPrice * 1) + ($busKidPrice * 0); // 1 adult bus default

// Fetch dynamic options
try {
    $stmt = $db->query("SELECT name FROM panihati_bhakti_sadans WHERE is_active = 1 ORDER BY name ASC");
    $dbSadans = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $stmt = $db->query("SELECT name FROM panihati_pickup_locations WHERE is_active = 1 ORDER BY name ASC");
    $dbPickups = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $dbSadans = [];
    $dbPickups = [];
}

// Handle File upload (CSV or Excel XML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = "Invalid CSRF token.";
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        if (!empty($file)) {
            $fileContent = file_get_contents($file);
            $isXml = (strpos($fileContent, '<?xml') !== false) || (strpos($fileContent, '<Workbook') !== false);
            
            $db->beginTransaction();
            $successCount = 0;
            $failedRows = [];
            $rowNum = 1;
            
            if ($isXml) {
                $xml = @simplexml_load_string($fileContent);
                if ($xml === false) {
                    $errorMsg = "Failed to parse uploaded Excel XML template.";
                    $db->rollBack();
                } else {
                    $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
                    $rows = $xml->xpath('//ss:Row');
                    foreach ($rows as $index => $row) {
                        if ($index === 0) continue;
                        $rowNum++;
                        
                        $cells = $row->Cell;
                        $rowData = array_fill(0, 9, '');
                        $cellIndex = 0;
                        foreach ($cells as $cell) {
                            $cellAttr = $cell->attributes('urn:schemas-microsoft-com:office:spreadsheet');
                            if (isset($cellAttr['Index'])) {
                                $cellIndex = (int)$cellAttr['Index'] - 1;
                            }
                            $val = trim((string)$cell->Data);
                            $rowData[$cellIndex] = $val;
                            $cellIndex++;
                        }
                        
                        if (empty(array_filter($rowData))) {
                            continue;
                        }
                        
                        $name = $rowData[0] ?? '';
                        $phone = $rowData[1] ?? '';
                        $email = $rowData[2] ?? '';
                        $travelMode = strtolower($rowData[3] ?? '');
                        $adults = intval($rowData[4] ?? 1);
                        $kids = intval($rowData[5] ?? 0);
                        $bhaktiSadan = $rowData[6] ?? '';
                        $pickup = $rowData[7] ?? '';
                        $amount = floatval($rowData[8] ?? 0);
                        
                        if (empty($name) || empty($phone) || empty($email) || !in_array($travelMode, ['bus', 'own_vehicle']) || empty($bhaktiSadan)) {
                            $failedRows[] = "Row $rowNum: Missing required values (Name, Phone, Email, Travel Mode, Sadan) or invalid mode ('{$travelMode}')";
                            continue;
                        }
                        
                        try {
                            $stmt = $db->prepare("
                                INSERT INTO panihati_yatra_registrations 
                                (name, phone, email, travel_mode, adults_count, kids_count, bhakti_sadan, pickup_location, amount, payment_status, is_offline)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'offline', 1)
                            ");
                            $stmt->execute([$name, $phone, $email, $travelMode, $adults, $kids, $bhaktiSadan, $pickup, $amount]);
                            $successCount++;
                        } catch (Exception $ex) {
                            $failedRows[] = "Row $rowNum: Database error (" . $ex->getMessage() . ")";
                        }
                    }
                    $db->commit();
                }
            } else {
                if (($handle = fopen($file, "r")) !== FALSE) {
                    fgetcsv($handle);
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $rowNum++;
                        if (empty($data) || count(array_filter($data)) === 0) {
                            continue;
                        }
                        if (count($data) < 9) {
                            $failedRows[] = "Row $rowNum: Insufficient columns (Expected 9, got " . count($data) . ")";
                            continue;
                        }
                        $name = trim($data[0]);
                        $phone = trim($data[1]);
                        $email = trim($data[2]);
                        $travelMode = strtolower(trim($data[3]));
                        $adults = intval($data[4]);
                        $kids = intval($data[5]);
                        $bhaktiSadan = trim($data[6]);
                        $pickup = trim($data[7]);
                        $amount = floatval($data[8]);
                        
                        if (empty($name) || empty($phone) || empty($email) || !in_array($travelMode, ['bus', 'own_vehicle']) || empty($bhaktiSadan)) {
                            $failedRows[] = "Row $rowNum: Missing required values or invalid travel mode ('{$travelMode}')";
                            continue;
                        }
                        
                        try {
                            $stmt = $db->prepare("
                                INSERT INTO panihati_yatra_registrations 
                                (name, phone, email, travel_mode, adults_count, kids_count, bhakti_sadan, pickup_location, amount, payment_status, is_offline)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'offline', 1)
                            ");
                            $stmt->execute([$name, $phone, $email, $travelMode, $adults, $kids, $bhaktiSadan, $pickup, $amount]);
                            $successCount++;
                        } catch (Exception $ex) {
                            $failedRows[] = "Row $rowNum: Database error (" . $ex->getMessage() . ")";
                        }
                    }
                    fclose($handle);
                    $db->commit();
                } else {
                    $errorMsg = "Please upload a valid CSV/Excel file.";
                    $db->rollBack();
                }
            }
            
            if ($successCount > 0) {
                $successMsg = "Successfully imported $successCount offline registrations.";
            }
            if (!empty($failedRows)) {
                $warningMsg = "Errors encountered in some rows:\n" . implode("\n", $failedRows);
            }
        } else {
            $errorMsg = "Please select a file to upload.";
        }
    }
}

// Handle manual entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'manual_offline') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = "Invalid CSRF token.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $travelMode = trim($_POST['travel_mode'] ?? '');
        $adults = intval($_POST['adults_count'] ?? 1);
        $kids = intval($_POST['kids_count'] ?? 0);
        $bhaktiSadan = trim($_POST['bhakti_sadan'] ?? '');
        $pickup = trim($_POST['pickup_location'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        
        if (empty($name) || empty($phone) || empty($email) || !in_array($travelMode, ['bus', 'own_vehicle']) || empty($bhaktiSadan)) {
            $errorMsg = "Please fill in all required fields.";
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO panihati_yatra_registrations 
                    (name, phone, email, travel_mode, adults_count, kids_count, bhakti_sadan, pickup_location, amount, payment_status, is_offline)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'offline', 1)
                ");
                $stmt->execute([$name, $phone, $email, $travelMode, $adults, $kids, $bhaktiSadan, $pickup, $amount]);
                $successMsg = "Manual offline registration added successfully.";
            } catch (Exception $ex) {
                $errorMsg = "Failed to save offline entry: " . $ex->getMessage();
            }
        }
    }
}
?>

<div class="admin-content-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-xl);">
  <div>
    <h1 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;"><i class="fas fa-plus-circle" style="color:var(--primary);"></i> Add Offline Entry</h1>
    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:var(--font-size-sm);">Add Panihati Yatra registrations via Excel upload or manual form entry.</p>
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

<!-- Offline Entry Forms -->
<div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-md); margin-bottom:var(--space-2xl);">
  
  <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:var(--space-2xl); align-items:start;">
    
    <!-- 1. Excel File Import -->
    <div style="border-right: 1px solid var(--border); padding-right: var(--space-xl);">
      <h4 style="font-family:var(--font-heading); color:var(--text-dark); margin-top:0; margin-bottom:var(--space-md);"><i class="fas fa-file-excel" style="color:var(--primary);"></i> Offline Excel Upload</h4>
      <p style="font-size:12px; color:var(--text-light); line-height:1.6; margin-bottom:var(--space-md);">
        Download the dynamic Excel template (.xls) which contains drop-down lists for Bhakti Sadan and Pickup Locations, fill in details, and upload it back.
      </p>
      
      <form action="admin/panihati-add-offline" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div style="margin-bottom:var(--space-md);">
          <input type="file" name="csv_file" accept=".xls,.csv" required style="width:100%; font-size:var(--font-size-sm);">
        </div>
        
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-file-upload"></i> Upload Template</button>
          <a href="admin/panihati-yatra?download_template=xls" class="btn btn-outline-dark btn-sm"><i class="fas fa-download"></i> Download XLS Template</a>
        </div>
      </form>
    </div>

    <!-- 2. Single Manual Entry -->
    <div>
      <h4 style="font-family:var(--font-heading); color:var(--text-dark); margin-top:0; margin-bottom:var(--space-md);"><i class="fas fa-keyboard" style="color:var(--primary);"></i> Manual Input</h4>
      <form action="admin/panihati-add-offline" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="action" value="manual_offline">
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Name *</label>
            <input type="text" name="name" required placeholder="Name" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
          </div>
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Phone *</label>
            <input type="tel" name="phone" required placeholder="Phone" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Email *</label>
            <input type="email" name="email" required placeholder="Email" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
          </div>
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Travel Mode *</label>
            <select name="travel_mode" id="offMode" required onchange="calculateOfflineAmount()" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
              <option value="bus">Bus</option>
              <option value="own_vehicle">Own Vehicle</option>
            </select>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Adults *</label>
            <input type="number" name="adults_count" id="offAdults" min="1" value="1" onchange="calculateOfflineAmount()" required style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
          </div>
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Kids (5-10 yrs)</label>
            <input type="number" name="kids_count" id="offKids" min="0" value="0" onchange="calculateOfflineAmount()" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
          </div>
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Amount (₹) *</label>
            <input type="number" name="amount" id="offAmount" required value="<?php echo $defaultAmount; ?>" min="0" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px; font-weight:700; color:var(--primary);">
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:15px;">
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Bhakti Sadan *</label>
            <select name="bhakti_sadan" required style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
              <option value="" disabled selected>-- Select Sadan --</option>
              <?php foreach ($dbSadans as $s): ?>
                <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label style="display:block; font-size:11px; font-weight:600; color:var(--text-dark); margin-bottom:2px;">Pickup Point</label>
            <select name="pickup_location" style="width:100%; padding:6px 10px; border:1px solid var(--border); border-radius:4px; font-size:12px;">
              <option value="" selected>-- Select Pickup (or N/A) --</option>
              <option value="Own Vehicle">Own Vehicle</option>
              <?php foreach ($dbPickups as $p): ?>
                <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Add Entry</button>
      </form>
    </div>

  </div>
</div>

<script>
// Dynamic pricing from the database
var PANIHATI_PRICES = {
  bus_adult: <?php echo $busAdultPrice; ?>,
  bus_kid: <?php echo $busKidPrice; ?>,
  vehicle_adult: <?php echo $vehicleAdultPrice; ?>,
  vehicle_kid: <?php echo $vehicleKidPrice; ?>
};

function calculateOfflineAmount() {
  var mode = document.getElementById('offMode').value;
  var adults = parseInt(document.getElementById('offAdults').value, 10) || 1;
  var kids = parseInt(document.getElementById('offKids').value, 10) || 0;
  
  var adultRate = (mode === 'bus') ? PANIHATI_PRICES.bus_adult : PANIHATI_PRICES.vehicle_adult;
  var kidRate = (mode === 'bus') ? PANIHATI_PRICES.bus_kid : PANIHATI_PRICES.vehicle_kid;
  
  var total = (adults * adultRate) + (kids * kidRate);
  document.getElementById('offAmount').value = total;
}
</script>

<?php include 'partials/footer.php'; ?>
