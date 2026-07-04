<?php
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin']); // Strictly Super Admin only

$pageTitle = 'Manage Admins';
$activePage = 'admins';
include 'partials/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle quick actions: Delete Admin
if (isset($_GET['delete_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } else {
        $deleteId = intval($_GET['delete_id']);
        
        // Prevent self-deletion
        if ($deleteId === (int)$_SESSION['admin_id']) {
            $error = 'You cannot delete your own administrative account.';
        } else {
            try {
                // Count super admins remaining
                $stmt = $db->prepare("SELECT role FROM admins WHERE id = ?");
                $stmt->execute([$deleteId]);
                $targetRole = $stmt->fetchColumn();
                
                if ($targetRole === 'super_admin') {
                    $countStmt = $db->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'");
                    $superAdminCount = (int)$countStmt->fetchColumn();
                    if ($superAdminCount <= 1) {
                        $error = 'Cannot delete this account. At least one Super Admin account must remain active.';
                    }
                }
                
                if (empty($error)) {
                    $delStmt = $db->prepare("DELETE FROM admins WHERE id = ?");
                    $delStmt->execute([$deleteId]);
                    $message = 'Administrator deleted successfully.';
                }
            } catch (PDOException $e) {
                $error = 'Failed to delete account. Please try again.';
            }
        }
    }
}

// Fetch all administrators
try {
    $stmt = $db->query("SELECT id, username, email, full_name, role, created_at FROM admins ORDER BY role ASC, username ASC");
    $adminsList = $stmt->fetchAll();
} catch (PDOException $e) {
    $adminsList = [];
    $error = 'Failed to retrieve administrators list. Please try again.';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Manage Admins & Roles</h1>
    <p>Create administrative accounts and assign roles for the ISKCON The Palace Temple of Lord Jagannath portal.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/admin-edit" class="btn btn-primary" style="background-color: var(--primary); text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-plus"></i> Add New Admin
    </a>
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
    <h2>All Administrative Accounts (<?php echo count($adminsList); ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th style="text-align: center; width: 150px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($adminsList)): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No administrative accounts found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($adminsList as $u): 
              $roleBadge = 'badge-draft';
              if ($u['role'] === 'super_admin') $roleBadge = 'badge-published';
              elseif ($u['role'] === 'editor') $roleBadge = 'badge-active';
              elseif ($u['role'] === 'pujari') $roleBadge = 'badge-warning';
              elseif ($u['role'] === 'treasurer') $roleBadge = 'badge-secondary';
              
              $isSelf = ((int)$u['id'] === (int)$_SESSION['admin_id']);
            ?>
              <tr>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($u['full_name'] ?: 'N/A'); ?></strong>
                  <?php if ($isSelf): ?>
                    <span class="badge" style="background-color:var(--cream); color:var(--primary); font-size:10px; border:1px solid var(--border); margin-left:4px;">You</span>
                  <?php endif; ?>
                </td>
                <td style="font-weight: 600; color:var(--text-light);"><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                  <span class="badge <?php echo $roleBadge; ?>">
                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $u['role']))); ?>
                  </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                <td style="text-align: center;">
                  <div style="display:flex; justify-content:center; gap:6px;">
                    <a href="admin/admin-edit?id=<?php echo $u['id']; ?>" class="btn-sm-action btn-edit" title="Edit Admin & Role">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php if (!$isSelf): ?>
                      <a href="admin/admins?delete_id=<?php echo $u['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-delete" onclick="return confirm('Are you sure you want to delete this admin account? This action is permanent.');" title="Delete Admin">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php else: ?>
                      <span class="btn-sm-action" style="opacity:0.4; cursor:not-allowed;" title="Cannot delete self"><i class="fas fa-trash"></i></span>
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
