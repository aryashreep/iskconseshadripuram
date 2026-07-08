<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('festivals.view');

$pageTitle = 'Manage Festivals & Causes';
$activePage = 'festivals';
include 'partials/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle quick actions: Toggle Active Status
if (isset($_GET['toggle_active_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('festivals.edit')) {
        $error = 'You do not have permission to modify festival active status.';
    } else {
        $toggleId = intval($_GET['toggle_active_id']);
        try {
            $stmt = $db->prepare("SELECT is_active FROM donation_causes WHERE id = ?");
            $stmt->execute([$toggleId]);
            $status = $stmt->fetchColumn();
            
            if ($status !== false) {
                $newStatus = $status ? 0 : 1;
                $update = $db->prepare("UPDATE donation_causes SET is_active = ? WHERE id = ?");
                $update->execute([$newStatus, $toggleId]);
                $message = 'Cause active status updated successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update active status. Please try again.';
        }
    }
}

// Handle quick actions: Toggle Featured Status
if (isset($_GET['toggle_featured_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('festivals.edit')) {
        $error = 'You do not have permission to modify festival featured status.';
    } else {
        $toggleId = intval($_GET['toggle_featured_id']);
        try {
            $stmt = $db->prepare("SELECT featured FROM donation_causes WHERE id = ?");
            $stmt->execute([$toggleId]);
            $status = $stmt->fetchColumn();
            
            if ($status !== false) {
                $newStatus = $status ? 0 : 1;
                $update = $db->prepare("UPDATE donation_causes SET featured = ? WHERE id = ?");
                $update->execute([$newStatus, $toggleId]);
                $message = 'Cause featured status updated successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update featured status. Please try again.';
        }
    }
}

// Handle quick actions: Delete Cause
if (isset($_GET['delete_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('festivals.delete')) {
        $error = 'You do not have permission to delete festivals/causes.';
    } else {
        $deleteId = intval($_GET['delete_id']);
        try {
            $stmt = $db->prepare("DELETE FROM donation_causes WHERE id = ?");
            $stmt->execute([$deleteId]);
            $message = 'Festival / Cause deleted successfully.';
        } catch (PDOException $e) {
            $error = 'Failed to delete cause. Please try again.';
        }
    }
}

// Fetch causes
try {
    $stmt = $db->query("SELECT id, slug, title, short_title, category, sort_order, featured, is_active FROM donation_causes ORDER BY category ASC, sort_order ASC, title ASC");
    $causesList = $stmt->fetchAll();
} catch (PDOException $e) {
    $causesList = [];
    $error = 'Failed to fetch causes. Please try again.';
}

// Helper category color label map
function getCategoryLabel($cat) {
    switch ($cat) {
        case 'festival': return 'Grand Festival';
        case 'ekadashi': return 'Ekadashi';
        case 'appearance': return 'Appearance Day';
        case 'disappearance': return 'Disappearance Day';
        case 'event': return 'Special Event';
        case 'service': return 'Evergreen Service';
        case 'construction': return 'Construction';
        case 'general': return 'General Fund';
        default: return ucfirst($cat);
    }
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Manage Festivals & Donation Causes</h1>
    <p>Configure temple services, grand celebrations, calendars, and setup their donation sevas.</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('festivals.create')): ?>
      <a href="admin/festival-edit" class="btn btn-primary" style="background-color: var(--primary); text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-plus"></i> Add Festival / Cause
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>All Donation Causes & Festivals (<?php echo count($causesList); ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 80px;">Sort Order</th>
            <th>Title</th>
            <th>Slug</th>
            <th>Category</th>
            <th style="text-align: center;">Featured</th>
            <th style="text-align: center;">Status</th>
            <th style="width: 200px; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($causesList)): ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No donation causes found. Click "Add Festival / Cause" to create one.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($causesList as $c): ?>
              <tr>
                <td style="text-align:center; font-weight: 600; color: var(--text-light);"><?php echo $c['sort_order']; ?></td>
                <td>
                  <strong style="color: var(--dark); font-size:14px;"><?php echo htmlspecialchars($c['title']); ?></strong>
                  <?php if (!empty($c['short_title'])): ?>
                    <div style="font-size:11px;color:var(--text-light);">Short: <?php echo htmlspecialchars($c['short_title']); ?></div>
                  <?php endif; ?>
                </td>
                <td style="font-family: monospace; font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($c['slug']); ?></td>
                <td>
                  <span style="font-size: 11px; font-weight: 500; background: var(--cream); color: var(--primary-dark); padding: 3px 8px; border-radius: 20px;">
                    <?php echo getCategoryLabel($c['category']); ?>
                  </span>
                </td>
                <td style="text-align: center;">
                  <?php if (hasPermission('festivals.edit')): ?>
                  <a href="admin/festivals?toggle_featured_id=<?php echo $c['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action" style="background:none; border:none; text-decoration:none; color:<?php echo $c['featured'] ? 'var(--accent)' : '#ccc'; ?>; font-size:16px;" title="Toggle Featured Status">
                    <i class="<?php echo $c['featured'] ? 'fas fa-star' : 'far fa-star'; ?>"></i>
                  </a>
                  <?php else: ?>
                  <i class="<?php echo $c['featured'] ? 'fas fa-star' : 'far fa-star'; ?>" style="color:<?php echo $c['featured'] ? 'var(--accent)' : '#ccc'; ?>; font-size:16px;"></i>
                  <?php endif; ?>
                </td>
                <td style="text-align: center;">
                  <?php if ($c['is_active']): ?>
                    <span class="badge badge-active">Active</span>
                  <?php else: ?>
                    <span class="badge badge-inactive">Inactive</span>
                  <?php endif; ?>
                </td>
                <td style="text-align: center;">
                  <div style="display:flex; justify-content:center; gap:6px;">
                    <?php if (hasPermission('festivals.edit')): ?>
                      <a href="admin/festivals?toggle_active_id=<?php echo $c['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-edit" title="Toggle Active Status" style="padding: 4px 6px;">
                        <i class="fas <?php echo $c['is_active'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                      </a>
                      <a href="admin/festival-edit?id=<?php echo $c['id']; ?>" class="btn-sm-action btn-edit" title="Edit Cause & Sevas">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                    <?php endif; ?>
                    <?php if (hasPermission('festivals.delete')): ?>
                      <a href="admin/festivals?delete_id=<?php echo $c['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-delete" onclick="return confirm('Are you sure you want to delete this cause? All transactions and pricing sevas connected to this cause will remain in history, but active links will break. This action is irreversible.');" title="Delete Cause">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php endif; ?>
                    <?php if (!hasPermission('festivals.edit') && !hasPermission('festivals.delete')): ?>
                      <span style="color:var(--text-light); font-size:12px; font-style:italic;">View-Only</span>
                    <?php endif; ?>
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

<?php include 'partials/footer.php'; ?>
