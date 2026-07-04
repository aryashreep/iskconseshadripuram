<?php
/**
 * Panihati Yatra — Add Bulk Summary
 * 
 * Dedicated page for entering aggregate headcount totals per sadan without individual contact details.
 * Accessible by: super_admin, travel_agent
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/panihati-helpers.php';

$pageTitle = 'Add Bulk Summary';
$activePage = 'panihati-bulk-summary';
include 'partials/header.php';

// Enforce role
requireRole(['travel_agent']);

$db = getDB();
$successMsg = '';
$errorMsg = '';

// Get current pricing for JS
$pricing = getPanihatiPricing();

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

// Handle bulk aggregate offline entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'aggregate_offline') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = "Invalid CSRF token.";
    } else {
        $rows = $_POST['rows'] ?? [];
        $sourceLabel = trim($_POST['source_label'] ?? 'Manual count');
        $reportedYear = intval($_POST['reported_year'] ?? date('Y'));
        $adminId = intval($_SESSION['admin_id'] ?? 0);
        $insertedCount = 0;

        // Use pricing for the reported year
        $yrPricing = getPanihatiPricing($reportedYear);

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
                    $successMsg = "Successfully added $insertedCount aggregate offline entry/entries.";
                } else {
                    $errorMsg = "No valid data rows were inserted. Please enter at least some counts.";
                }
            } catch (Exception $ex) {
                $db->rollBack();
                $errorMsg = "Failed to save aggregate entries: " . $ex->getMessage();
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
    <h1 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;"><i class="fas fa-layer-group" style="color:var(--primary);"></i> Add Bulk Summary</h1>
    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:var(--font-size-sm);">Enter headcount totals per sadan without individual contact details. Amounts auto-calculate.</p>
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

<!-- Bulk Aggregate Offline Summary Entry Form -->
<div style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-md); margin-bottom:var(--space-2xl);">
  
  <p style="font-size:13px; color:var(--text-light); line-height:1.6; margin-bottom:var(--space-lg);">
    Use this form when management has <strong>headcount totals per sadan</strong> without individual names/contact details.
    Each row can include both bus and own-vehicle counts. Amounts are auto-calculated based on current pricing.
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
        <button type="button" onclick="addAggregateRow()" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:4px;">
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
        <i class="fas fa-save"></i> Save Aggregate Entries
      </button>
    </div>

  </form>
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

function getCurrentPrices() {
  // In a full implementation, you'd fetch pricing for the selected year via AJAX.
  // For now, use the current year pricing which covers most use cases.
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
          <?php foreach ($dbPickups as $p): ?>\
            <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>\
          <?php endforeach; ?>\
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
      <i class="fas fa-layer-group" style="color:var(--primary);"></i> Recently Added Aggregate Entries
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
