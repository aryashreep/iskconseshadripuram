<?php
/**
 * Roles & Permissions — Role Listing Page
 * 
 * Lists all RBAC roles with user counts and management actions.
 * Super Admin only.
 */

require_once __DIR__ . '/../../Kernel/Admin/auth-check.php';
requireRole(['super_admin']);

use Isjm\Modules\RBAC\RbacService;

$pageTitle = 'Roles & Permissions';
$activePage = 'roles';
include __DIR__ . '/../../Kernel/Admin/partials/header.php';

$rbac = new RbacService();
$db = getDB();
$error = '';
$message = '';

// Handle delete role
if (isset($_GET['delete_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed.';
    } else {
        $deleteId = (int) $_GET['delete_id'];
        if ($rbac->deleteRole($deleteId)) {
            $message = 'Role deleted successfully.';
        } else {
            $error = 'Cannot delete system roles or role not found.';
        }
    }
}

// Fetch all roles with user counts
try {
    $roles = $db->query("
        SELECT r.*, COUNT(ur.id) as user_count
        FROM rbac_roles r
        LEFT JOIN rbac_user_roles ur ON r.id = ur.role_id
        GROUP BY r.id
        ORDER BY r.sort_order ASC, r.name ASC
    ")->fetchAll();
} catch (\PDOException $e) {
    $roles = [];
    $error = 'Failed to load roles. Ensure migrations have been run.';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-shield-alt" style="color:var(--primary); margin-right:8px;"></i> Roles & Permissions</h1>
    <p>Define roles and assign granular permissions for the admin portal.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/role-edit" class="btn btn-primary" style="background-color: var(--primary); text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-plus"></i> Add New Role
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
    <h2>All Roles (<?php echo count($roles); ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Role Name</th>
            <th>Slug</th>
            <th>Description</th>
            <th style="text-align:center;">Users</th>
            <th style="text-align:center;">Status</th>
            <th style="text-align:center; width:120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($roles)): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No roles found. Run the seed migration first.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($roles as $role): ?>
              <tr>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($role['name']); ?></strong>
                  <?php if ($role['is_system']): ?>
                    <span class="badge" style="background:#f0f0f0; color:#666; font-size:10px; border:1px solid #ddd; margin-left:4px;">
                      <i class="fas fa-lock" style="font-size:9px;"></i> System
                    </span>
                  <?php endif; ?>
                  <?php if ($role['slug'] === 'super_admin'): ?>
                    <span class="badge" style="background:#fff3e0; color:#e65100; font-size:10px; border:1px solid #ffe0b2; margin-left:4px;">
                      <i class="fas fa-bolt" style="font-size:9px;"></i> Bypass
                    </span>
                  <?php endif; ?>
                </td>
                <td style="font-family:monospace; font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($role['slug']); ?></td>
                <td style="font-size:13px; color:var(--text-light); max-width:300px;"><?php echo htmlspecialchars($role['description'] ?? '-'); ?></td>
                <td style="text-align:center; font-weight:600;">
                  <?php if ($role['user_count'] > 0): ?>
                    <a href="admin/admins?role=<?php echo urlencode($role['slug']); ?>" style="color:var(--primary); text-decoration:none;">
                      <?php echo (int) $role['user_count']; ?>
                    </a>
                  <?php else: ?>
                    <span style="color:var(--text-light);">0</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;">
                  <?php if ($role['is_active']): ?>
                    <span class="badge" style="background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9;">Active</span>
                  <?php else: ?>
                    <span class="badge" style="background:#ffebee; color:#c62828; border:1px solid #ffcdd2;">Inactive</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;">
                  <div style="display:flex; justify-content:center; gap:6px;">
                    <a href="admin/role-edit?id=<?php echo $role['id']; ?>" class="btn-sm-action btn-edit" title="Edit Role & Permissions">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php if (!$role['is_system']): ?>
                      <a href="admin/roles?delete_id=<?php echo $role['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-delete" onclick="return confirm('Are you sure you want to delete the role &quot;<?php echo htmlspecialchars(addslashes($role['name'])); ?>&quot;? Users assigned this role will lose its permissions.');" title="Delete Role">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php else: ?>
                      <span class="btn-sm-action" style="opacity:0.3; cursor:not-allowed;" title="System role cannot be deleted"><i class="fas fa-trash"></i></span>
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

<!-- Super Admin Info Card -->
<div class="admin-card" style="margin-top: var(--space-lg); background:var(--cream); border:1px solid var(--border);">
  <div class="admin-card-body" style="padding: var(--space-lg);">
    <div style="display:flex; align-items:flex-start; gap:var(--space-md);">
      <div style="font-size:32px; color:var(--primary);"><i class="fas fa-info-circle"></i></div>
      <div>
        <h3 style="margin:0 0 var(--space-xs) 0; font-size:15px;">How Permissions Work</h3>
        <ul style="margin:0; padding-left:16px; font-size:13px; line-height:1.6; color:var(--text);">
          <li><strong>Super Administrator</strong> has an implicit bypass — all permissions are automatically granted regardless of explicit assignments.</li>
          <li>When a user has <strong>multiple roles</strong>, they receive the <strong>union</strong> of all permissions from all assigned roles.</li>
          <li>New roles and permissions can be created and modified through the UI — no code changes needed.</li>
          <li>System roles (marked with <i class="fas fa-lock" style="font-size:9px;"></i>) cannot be deleted to prevent accidental lockout.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../Kernel/Admin/partials/footer.php'; ?>
