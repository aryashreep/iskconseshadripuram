<?php
/**
 * Panihati Pickup Locations Management Page
 * 
 * CRUD for managing Pickup Locations used in the Panihati Yatra registration form.
 * Accessible by: super_admin, travel_agent
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config.php';

$pageTitle = 'Manage Pickup Locations';
$activePage = 'panihati-pickups';
include 'partials/header.php';

// Enforce permission
requirePermission('panihati.edit');

$db = getDB();
$successMsg = '';
$errorMsg = '';

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $errorMsg = 'Pickup location name is required.';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO `panihati_pickup_locations` (`name`) VALUES (?)");
                $stmt->execute([$name]);
                $successMsg = 'Pickup Location "' . htmlspecialchars($name) . '" added successfully.';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMsg = 'A pickup location with this name already exists.';
                } else {
                    $errorMsg = 'A database error occurred. Please try again.';
                }
            }
        }
    }
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = 'Invalid CSRF token.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id <= 0 || empty($name)) {
            $errorMsg = 'Invalid request.';
        } else {
            try {
                $stmt = $db->prepare("UPDATE `panihati_pickup_locations` SET `name` = ? WHERE `id` = ?");
                $stmt->execute([$name, $id]);
                if ($stmt->rowCount() > 0) {
                    $successMsg = 'Pickup location updated successfully.';
                } else {
                    $errorMsg = 'No changes made or record not found.';
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMsg = 'A pickup location with this name already exists.';
                } else {
                    $errorMsg = 'A database error occurred. Please try again.';
                }
            }
        }
    }
}

// Handle Toggle Active
if (isset($_GET['toggle'])) {
    // CSRF validation
    if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['token'])) {
        $errorMsg = 'Invalid security token. Please try again.';
    } else {
        $id = intval($_GET['toggle']);
        if ($id > 0) {
            try {
                $stmt = $db->prepare("UPDATE `panihati_pickup_locations` SET `is_active` = NOT `is_active` WHERE `id` = ?");
                $stmt->execute([$id]);
                $successMsg = 'Pickup location status toggled successfully.';
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
                $stmt = $db->prepare("DELETE FROM `panihati_pickup_locations` WHERE `id` = ?");
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    $successMsg = 'Pickup location deleted successfully.';
                } else {
                    $errorMsg = 'Record not found.';
                }
            } catch (PDOException $e) {
                $errorMsg = 'A database error occurred. Please try again.';
            }
        }
    }
}

// Fetch all pickup locations
try {
    $stmt = $db->query("SELECT * FROM `panihati_pickup_locations` ORDER BY `is_active` DESC, `name` ASC");
    $pickups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalCount = count($pickups);
    $activeCount = count(array_filter($pickups, fn($p) => $p['is_active']));
    $inactiveCount = $totalCount - $activeCount;
} catch (PDOException $e) {
    $pickups = [];
    $totalCount = 0;
    $activeCount = 0;
    $inactiveCount = 0;
    $errorMsg = 'A database error occurred. Please try again.';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-map-pin" style="color:var(--primary);"></i> Pickup Locations</h1>
    <p>Manage pickup locations for the Panihati Yatra registration form.</p>
  </div>
  <div class="admin-page-actions">
    <button onclick="openAddModal()" class="btn btn-primary">
      <i class="fas fa-plus"></i> Add New Pickup Location
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

<!-- Stats Cards -->
<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Locations</h3>
      <div class="admin-stat-value"><?php echo $totalCount; ?></div>
    </div>
    <div class="admin-stat-icon">
      <i class="fas fa-map-marker-alt"></i>
    </div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Active</h3>
      <div class="admin-stat-value" style="color:#2e7d32;"><?php echo $activeCount; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:#d4edda; color:#2e7d32;">
      <i class="fas fa-check-circle"></i>
    </div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Inactive</h3>
      <div class="admin-stat-value" style="color:#c62828;"><?php echo $inactiveCount; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:#ffebee; color:#c62828;">
      <i class="fas fa-times-circle"></i>
    </div>
  </div>
</div>

<!-- Pickup Locations Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2><i class="fas fa-table"></i> All Pickup Locations</h2>
    <span style="font-size:12px; color:var(--text-light);">Showing all <?php echo $totalCount; ?> records</span>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:60px;">#</th>
            <th>Pickup Location Name</th>
            <th style="width:100px;">Status</th>
            <th style="width:160px;">Created Date</th>
            <th style="width:200px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pickups)): ?>
            <tr>
              <td colspan="5" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">
                <i class="fas fa-inbox" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                No Pickup Locations found. Click "Add New Pickup Location" to create one.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($pickups as $index => $p): ?>
              <tr>
                <td style="font-weight:600; color:var(--text-light);"><?php echo $index + 1; ?></td>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($p['name']); ?></strong>
                </td>
                <td>
                  <span class="badge <?php echo $p['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                    <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
                  </span>
                </td>
                <td style="font-size:12px; color:var(--text-light);">
                  <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <button onclick="openEditModal(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>')" class="btn-sm-action btn-edit">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="admin/panihati-pickups?toggle=<?php echo $p['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action <?php echo $p['is_active'] ? 'btn-delete' : 'btn-edit'; ?>" style="background-color:<?php echo $p['is_active'] ? '#fff3cd' : '#d4edda'; ?>; color:<?php echo $p['is_active'] ? '#856404' : '#155724'; ?>; border-color:<?php echo $p['is_active'] ? '#ffc107' : '#c3e6cb'; ?>;" onclick="return confirm('Toggle status for &quot;<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>&quot;?')">
                      <i class="fas <?php echo $p['is_active'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i> 
                      <?php echo $p['is_active'] ? 'Deactivate' : 'Activate'; ?>
                    </a>
                    <a href="admin/panihati-pickups?delete=<?php echo $p['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-delete" onclick="return confirm('Are you sure you want to permanently delete &quot;<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>&quot;? This action cannot be undone.')">
                      <i class="fas fa-trash"></i> Delete
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
  <div style="background:var(--white); border-radius:var(--radius-lg); padding:var(--space-xl); max-width:500px; width:90%; box-shadow:var(--shadow-xl);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); border-bottom:2px solid var(--border); padding-bottom:var(--space-sm);">
      <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">
        <i class="fas fa-plus-circle" style="color:var(--primary);"></i> Add New Pickup Location
      </h3>
      <button onclick="closeAddModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-light);">&times;</button>
    </div>
    <form method="POST" action="admin/panihati-pickups">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label for="addName">Pickup Location Name *</label>
        <input type="text" id="addName" name="name" class="form-control" required placeholder="e.g. Majestic Bus Stand" maxlength="100" autocomplete="off">
      </div>
      <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:var(--space-lg);">
        <button type="button" onclick="closeAddModal()" class="btn btn-outline-dark">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Pickup Location</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:var(--white); border-radius:var(--radius-lg); padding:var(--space-xl); max-width:500px; width:90%; box-shadow:var(--shadow-xl);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); border-bottom:2px solid var(--border); padding-bottom:var(--space-sm);">
      <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">
        <i class="fas fa-edit" style="color:var(--primary);"></i> Edit Pickup Location
      </h3>
      <button onclick="closeEditModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-light);">&times;</button>
    </div>
    <form method="POST" action="admin/panihati-pickups">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editId" value="">
      <div class="form-group">
        <label for="editName">Pickup Location Name *</label>
        <input type="text" id="editName" name="name" class="form-control" required placeholder="e.g. Majestic Bus Stand" maxlength="100" autocomplete="off">
      </div>
      <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:var(--space-lg);">
        <button type="button" onclick="closeEditModal()" class="btn btn-outline-dark">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Pickup Location</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
  document.getElementById('addName').focus();
}

function closeAddModal() {
  document.getElementById('addModal').style.display = 'none';
}

function openEditModal(id, name) {
  document.getElementById('editId').value = id;
  document.getElementById('editName').value = name;
  document.getElementById('editModal').style.display = 'flex';
  document.getElementById('editName').focus();
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}

// Close modals on click outside
document.addEventListener('click', function(e) {
  if (e.target.id === 'addModal') closeAddModal();
  if (e.target.id === 'editModal') closeEditModal();
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeAddModal();
    closeEditModal();
  }
});
</script>

<?php include 'partials/footer.php'; ?>
