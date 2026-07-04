<?php
/**
 * Panihati Yatra — Pricing Management
 * 
 * CRUD for managing yearly pricing (bus/vehicle adults & kids).
 * Accessible by: super_admin, travel_agent
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/panihati-helpers.php';

$pageTitle = 'Panihati Yatra Pricing';
$activePage = 'panihati-pricing';
include 'partials/header.php';

// Enforce role
requireRole(['travel_agent']);

$db = getDB();
$successMsg = '';
$errorMsg = '';

// Handle Add / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'];
        $year = intval($_POST['year'] ?? 0);
        $busAdult = floatval($_POST['bus_adult_price'] ?? 0);
        $busKid = floatval($_POST['bus_kid_price'] ?? 0);
        $vehicleAdult = floatval($_POST['vehicle_adult_price'] ?? 0);
        $vehicleKid = floatval($_POST['vehicle_kid_price'] ?? 0);

        if ($year < 2024 || $year > 2100) {
            $errorMsg = 'Please enter a valid year.';
        } elseif ($busAdult < 0 || $busKid < 0 || $vehicleAdult < 0 || $vehicleKid < 0) {
            $errorMsg = 'Prices cannot be negative.';
        } elseif ($action === 'add') {
            try {
                $stmt = $db->prepare("INSERT INTO `panihati_pricing` (`year`, `bus_adult_price`, `bus_kid_price`, `vehicle_adult_price`, `vehicle_kid_price`) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$year, $busAdult, $busKid, $vehicleAdult, $vehicleKid]);
                $successMsg = "Pricing for $year added successfully.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMsg = "Pricing for $year already exists. Use the edit option instead.";
                } else {
                    $errorMsg = 'A database error occurred. Please try again.';
                }
            }
        } elseif ($action === 'edit') {
            $id = intval($_POST['id'] ?? 0);
            try {
                $stmt = $db->prepare("UPDATE `panihati_pricing` SET `year` = ?, `bus_adult_price` = ?, `bus_kid_price` = ?, `vehicle_adult_price` = ?, `vehicle_kid_price` = ? WHERE `id` = ?");
                $stmt->execute([$year, $busAdult, $busKid, $vehicleAdult, $vehicleKid, $id]);
                if ($stmt->rowCount() > 0) {
                    $successMsg = "Pricing for $year updated successfully.";
                } else {
                    $errorMsg = 'No changes made or record not found.';
                }
            } catch (PDOException $e) {
                $errorMsg = 'A database error occurred. Please try again.';
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    // CSRF validation
    if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['token'])) {
        $errorMsg = 'Invalid security token. Please try again.';
    } else {
        $id = intval($_GET['delete']);
        if ($id > 0) {
            try {
                $stmt = $db->prepare("DELETE FROM `panihati_pricing` WHERE `id` = ?");
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    $successMsg = 'Pricing record deleted successfully.';
                } else {
                    $errorMsg = 'Record not found.';
                }
            } catch (PDOException $e) {
                $errorMsg = 'A database error occurred. Please try again.';
            }
        }
    }
}

// Fetch all pricing records
try {
    $stmt = $db->query("SELECT * FROM `panihati_pricing` ORDER BY `year` DESC");
    $pricingRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pricingRecords = [];
    $errorMsg = 'A database error occurred. Please try again.';
}

// Get current year pricing for preview
$currentPricing = getPanihatiPricing();
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-tags" style="color:var(--primary);"></i> Panihati Yatra Pricing</h1>
    <p>Manage yearly pricing for bus and own-vehicle travel. Prices can change each year.</p>
  </div>
  <div class="admin-page-actions">
    <button onclick="openAddModal()" class="btn btn-primary">
      <i class="fas fa-plus"></i> Add Year Pricing
    </button>
  </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($successMsg)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?>
  </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMsg); ?>
  </div>
<?php endif; ?>

<!-- Current Year Pricing Preview -->
<div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:var(--space-md); margin-bottom:var(--space-2xl);">
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); font-weight:600; text-transform:uppercase; margin-bottom:4px;">Bus Adult</div>
    <div style="font-size:28px; font-weight:700; color:var(--primary);">₹<?php echo number_format($currentPricing['bus_adult_price']); ?></div>
    <div style="font-size:11px; color:var(--text-light);"><?php echo date('Y'); ?> price</div>
  </div>
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); font-weight:600; text-transform:uppercase; margin-bottom:4px;">Bus Kid</div>
    <div style="font-size:28px; font-weight:700; color:var(--text-dark);">₹<?php echo number_format($currentPricing['bus_kid_price']); ?></div>
    <div style="font-size:11px; color:var(--text-light);"><?php echo date('Y'); ?> price</div>
  </div>
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); font-weight:600; text-transform:uppercase; margin-bottom:4px;">Vehicle Adult</div>
    <div style="font-size:28px; font-weight:700; color:var(--primary);">₹<?php echo number_format($currentPricing['vehicle_adult_price']); ?></div>
    <div style="font-size:11px; color:var(--text-light);"><?php echo date('Y'); ?> price</div>
  </div>
  <div style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); text-align:center;">
    <div style="font-size:11px; color:var(--text-light); font-weight:600; text-transform:uppercase; margin-bottom:4px;">Vehicle Kid</div>
    <div style="font-size:28px; font-weight:700; color:var(--text-dark);">₹<?php echo number_format($currentPricing['vehicle_kid_price']); ?></div>
    <div style="font-size:11px; color:var(--text-light);"><?php echo date('Y'); ?> price</div>
  </div>
</div>

<!-- Pricing Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2><i class="fas fa-table"></i> Yearly Pricing</h2>
    <span style="font-size:12px; color:var(--text-light);"><?php echo count($pricingRecords); ?> year(s) configured</span>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:80px;">#</th>
            <th>Year</th>
            <th style="text-align:right;">Bus Adult (₹)</th>
            <th style="text-align:right;">Bus Kid (₹)</th>
            <th style="text-align:right;">Vehicle Adult (₹)</th>
            <th style="text-align:right;">Vehicle Kid (₹)</th>
            <th style="text-align:center;">Bus Rate</th>
            <th style="text-align:center;">Vehicle Rate</th>
            <th style="width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pricingRecords)): ?>
            <tr>
              <td colspan="9" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">
                <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                No pricing records found. Click "Add Year Pricing" to create one.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pricingRecords as $index => $p): 
              $isCurrentYear = ((int)$p['year'] === (int)date('Y'));
              $rowClass = $isCurrentYear ? 'style="background:var(--cream-light);"' : '';
            ?>
              <tr <?php echo $rowClass; ?>>
                <td style="font-weight:600; color:var(--text-light);"><?php echo $index + 1; ?></td>
                <td>
                  <strong style="font-size:15px; color:var(--text-dark);"><?php echo $p['year']; ?></strong>
                  <?php if ($isCurrentYear): ?>
                    <span style="background:var(--primary); color:var(--white); font-size:10px; padding:1px 6px; border-radius:3px; margin-left:6px; font-weight:600;">Current</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:right; font-weight:600;">₹<?php echo number_format($p['bus_adult_price']); ?></td>
                <td style="text-align:right; font-weight:600;">₹<?php echo number_format($p['bus_kid_price']); ?></td>
                <td style="text-align:right; font-weight:600;">₹<?php echo number_format($p['vehicle_adult_price']); ?></td>
                <td style="text-align:right; font-weight:600;">₹<?php echo number_format($p['vehicle_kid_price']); ?></td>
                <td style="text-align:center;">
                  <span style="font-size:11px; padding:2px 8px; border-radius:4px; background:#e8eaf6; color:#283593;">
                    ₹<?php echo number_format($p['bus_adult_price']); ?> / ₹<?php echo number_format($p['bus_kid_price']); ?>
                  </span>
                </td>
                <td style="text-align:center;">
                  <span style="font-size:11px; padding:2px 8px; border-radius:4px; background:#e0f2f1; color:#00695c;">
                    ₹<?php echo number_format($p['vehicle_adult_price']); ?> / ₹<?php echo number_format($p['vehicle_kid_price']); ?>
                  </span>
                </td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <button onclick="openEditModal(<?php echo $p['id']; ?>, <?php echo $p['year']; ?>, <?php echo $p['bus_adult_price']; ?>, <?php echo $p['bus_kid_price']; ?>, <?php echo $p['vehicle_adult_price']; ?>, <?php echo $p['vehicle_kid_price']; ?>)" class="btn-sm-action btn-edit">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="admin/panihati-pricing?delete=<?php echo $p['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-delete" onclick="return confirm('Delete pricing for <?php echo $p['year']; ?>? This cannot be undone.')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div id="addModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:var(--white); border-radius:var(--radius-lg); padding:var(--space-xl); max-width:550px; width:90%; box-shadow:var(--shadow-xl);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); border-bottom:2px solid var(--border); padding-bottom:var(--space-sm);">
      <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">
        <i class="fas fa-plus-circle" style="color:var(--primary);"></i> Add Year Pricing
      </h3>
      <button onclick="closeAddModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-light);">&times;</button>
    </div>
    <form method="POST" action="admin/panihati-pricing">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="action" value="add">
      
      <div class="form-group">
        <label>Year *</label>
        <input type="number" name="year" class="form-control" required min="2024" max="2100" value="<?php echo date('Y'); ?>" placeholder="e.g. 2026">
      </div>
      
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-md);">
        <div class="form-group">
          <label>Bus Adult Price (₹) *</label>
          <input type="number" name="bus_adult_price" class="form-control" required min="0" step="1" value="1000">
        </div>
        <div class="form-group">
          <label>Bus Kid Price (₹) *</label>
          <input type="number" name="bus_kid_price" class="form-control" required min="0" step="1" value="600">
        </div>
        <div class="form-group">
          <label>Vehicle Adult Price (₹) *</label>
          <input type="number" name="vehicle_adult_price" class="form-control" required min="0" step="1" value="600">
        </div>
        <div class="form-group">
          <label>Vehicle Kid Price (₹) *</label>
          <input type="number" name="vehicle_kid_price" class="form-control" required min="0" step="1" value="600">
        </div>
      </div>
      
      <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:var(--space-lg);">
        <button type="button" onclick="closeAddModal()" class="btn btn-outline-dark">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Pricing</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:var(--white); border-radius:var(--radius-lg); padding:var(--space-xl); max-width:550px; width:90%; box-shadow:var(--shadow-xl);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); border-bottom:2px solid var(--border); padding-bottom:var(--space-sm);">
      <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">
        <i class="fas fa-edit" style="color:var(--primary);"></i> Edit Year Pricing
      </h3>
      <button onclick="closeEditModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-light);">&times;</button>
    </div>
    <form method="POST" action="admin/panihati-pricing">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editId" value="">
      
      <div class="form-group">
        <label>Year *</label>
        <input type="number" name="year" id="editYear" class="form-control" required min="2024" max="2100">
      </div>
      
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-md);">
        <div class="form-group">
          <label>Bus Adult Price (₹) *</label>
          <input type="number" name="bus_adult_price" id="editBusAdult" class="form-control" required min="0" step="1">
        </div>
        <div class="form-group">
          <label>Bus Kid Price (₹) *</label>
          <input type="number" name="bus_kid_price" id="editBusKid" class="form-control" required min="0" step="1">
        </div>
        <div class="form-group">
          <label>Vehicle Adult Price (₹) *</label>
          <input type="number" name="vehicle_adult_price" id="editVehicleAdult" class="form-control" required min="0" step="1">
        </div>
        <div class="form-group">
          <label>Vehicle Kid Price (₹) *</label>
          <input type="number" name="vehicle_kid_price" id="editVehicleKid" class="form-control" required min="0" step="1">
        </div>
      </div>
      
      <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:var(--space-lg);">
        <button type="button" onclick="closeEditModal()" class="btn btn-outline-dark">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Pricing</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
}

function closeAddModal() {
  document.getElementById('addModal').style.display = 'none';
}

function openEditModal(id, year, busAdult, busKid, vehicleAdult, vehicleKid) {
  document.getElementById('editId').value = id;
  document.getElementById('editYear').value = year;
  document.getElementById('editBusAdult').value = busAdult;
  document.getElementById('editBusKid').value = busKid;
  document.getElementById('editVehicleAdult').value = vehicleAdult;
  document.getElementById('editVehicleKid').value = vehicleKid;
  document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}

// Close modals on click outside
document.addEventListener('click', function(e) {
  if (e.target.id === 'addModal') closeAddModal();
  if (e.target.id === 'editModal') closeEditModal();
});

// Close modals on Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeAddModal();
    closeEditModal();
  }
});
</script>

<?php include 'partials/footer.php'; ?>
