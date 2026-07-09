<?php
/**
 * Role Edit — Create/Edit Role with Permission Matrix
 * 
 * Features:
 * - Create new roles or edit existing ones
 * - Permission matrix with checkboxes (Module × Action)
 * - Select All / Deselect All per row and column
 * - System role protection
 * 
 * Super Admin only.
 */

require_once __DIR__ . '/../../Kernel/Admin/auth-check.php';
requireRole(['super_admin']);

use Isjm\Modules\RBAC\RbacService;
use Isjm\Modules\RBAC\PermissionRegistry;

$rbac = new RbacService();
$db = getDB();
$error = '';
$success = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEditMode = ($id > 0);

// Default values
$roleName = '';
$roleSlug = '';
$roleDescription = '';
$isSystem = false;
$rolePermissionIds = [];

if ($isEditMode) {
    $role = $rbac->getRole($id);
    if (!$role) {
        echo "<div class='alert alert-danger'>Role not found.</div>";
        include __DIR__ . '/../../Kernel/Admin/partials/footer.php';
        exit;
    }
    $roleName = $role['name'];
    $roleSlug = $role['slug'];
    $roleDescription = $role['description'];
    $isSystem = (bool) $role['is_system'];
    $rolePermissionIds = $rbac->getRolePermissionIds($id);
}

// Handle form submission (before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed.';
    } else {
        $nameInput = trim($_POST['name'] ?? '');
        $slugInput = trim($_POST['slug'] ?? '');
        $descriptionInput = trim($_POST['description'] ?? '');
        $permissionIdsInput = $_POST['permissions'] ?? [];

        if (empty($nameInput)) {
            $error = 'Role name is required.';
        } elseif (empty($slugInput)) {
            $error = 'Role slug is required.';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $slugInput)) {
            $error = 'Slug must contain only lowercase letters, numbers, and underscores.';
        } else {
            try {
                if ($isEditMode) {
                    // Cannot change slug of system roles
                    if (!$isSystem) {
                        // Check slug uniqueness (excluding current role)
                        $chk = $db->prepare("SELECT COUNT(*) FROM rbac_roles WHERE slug = ? AND id != ?");
                        $chk->execute([$slugInput, $id]);
                        if ((int) $chk->fetchColumn() > 0) {
                            $error = 'A role with this slug already exists.';
                        }
                    }
                    
                    if (empty($error)) {
                        if (!$isSystem) {
                            $rbac->updateRole($id, $nameInput, $descriptionInput, (int) ($_POST['sort_order'] ?? 0), !empty($_POST['is_active']));
                        }
                        // Update permissions (for all roles including system)
                        $rbac->setRolePermissions($id, $permissionIdsInput);
                        $success = 'Role updated successfully!';
                        
                        // Refresh values
                        $roleName = $nameInput;
                        $roleSlug = $role['slug']; // Keep original for system roles
                        $roleDescription = $descriptionInput;
                        $rolePermissionIds = $rbac->getRolePermissionIds($id);
                    }
                } else {
                    // Check slug uniqueness
                    $chk = $db->prepare("SELECT COUNT(*) FROM rbac_roles WHERE slug = ?");
                    $chk->execute([$slugInput]);
                    if ((int) $chk->fetchColumn() > 0) {
                        $error = 'A role with this slug already exists.';
                    }

                    if (empty($error)) {
                        $newId = $rbac->createRole($slugInput, $nameInput, $descriptionInput, false, (int) ($_POST['sort_order'] ?? 0));
                        $rbac->setRolePermissions($newId, $permissionIdsInput);
                        
                        header('Location: ' . BASE_URL . 'admin/roles?success=' . urlencode('Role created successfully!'));
                        exit;
                    }
                }
            } catch (\PDOException $e) {
                $error = 'A database error occurred. Please try again.';
            }
        }
    }
}

// Get all permissions grouped by module
$allPermissions = $rbac->getAllPermissions();
$groupedPermissions = [];
foreach ($allPermissions as $p) {
    $groupedPermissions[$p['module']][] = $p;
}

// Get module metadata from registry
$modulesMeta = PermissionRegistry::getModules();

// Render page
$pageTitle = 'Edit Role';
$activePage = 'roles';
include __DIR__ . '/../../Kernel/Admin/partials/header.php';
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><?php echo $isEditMode ? 'Edit Role: ' . htmlspecialchars($roleName) : 'Create New Role'; ?></h1>
    <p><?php echo $isEditMode ? 'Modify role details and assign granular permissions.' : 'Define a new role with specific permissions.'; ?></p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/roles" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 20px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; color:var(--text); font-size:13px; display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-arrow-left"></i> Back to Roles
    </a>
  </div>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($success); ?>
  </div>
<?php endif; ?>

<div class="admin-card" style="max-width: 500px; margin: 0 auto var(--space-xl) auto;">
  <div class="admin-card-header">
    <h2>Role Details</h2>
  </div>
  <div class="admin-card-body" style="padding: var(--space-xl);">
    <form action="admin/role-edit<?php echo $isEditMode ? '?id=' . $id : ''; ?>" method="POST" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <div class="form-group">
        <label for="name">Role Name <span style="color:var(--maroon);">*</span></label>
        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($roleName); ?>" required placeholder="e.g. Content Manager">
      </div>

      <div class="form-group">
        <label for="slug">Slug <span style="color:var(--maroon);">*</span></label>
        <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($roleSlug); ?>" <?php echo $isEditMode && $isSystem ? 'disabled style="background:var(--cream);"' : 'required'; ?> placeholder="e.g. content_manager" pattern="[a-z0-9_]+" title="Lowercase letters, numbers, and underscores only">
        <small style="color:var(--text-light); font-size:11px;">Machine-readable identifier. Cannot be changed after creation.</small>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" class="form-control" rows="2" placeholder="What this role is for"><?php echo htmlspecialchars($roleDescription); ?></textarea>
      </div>

      <?php if (!$isEditMode || !$isSystem): ?>
        <div style="display:flex; gap:var(--space-md);">
          <div class="form-group" style="flex:1;">
            <label for="sort_order">Sort Order</label>
            <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo $isEditMode ? ($role['sort_order'] ?? 0) : 0; ?>" min="0" style="width:100px;">
          </div>
          <?php if ($isEditMode): ?>
            <div class="form-group" style="flex:1;">
              <label for="is_active">Status</label>
              <select id="is_active" name="is_active" class="form-control">
                <option value="1" <?php echo ($role['is_active'] ?? 1) ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo !($role['is_active'] ?? 1) ? 'selected' : ''; ?>>Inactive</option>
              </select>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($isEditMode && $isSystem): ?>
        <div style="background:var(--cream); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px; font-size:12px; color:var(--text-light);">
          <i class="fas fa-lock" style="color:var(--primary); margin-right:4px;"></i>
          System role — name, slug, and status cannot be modified. Permissions can still be updated.
        </div>
      <?php endif; ?>

      <hr style="border:none; border-top:1px solid var(--border); margin:var(--space-md) 0;">

      <div style="display:flex; justify-content:flex-end;">
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:10px 24px; border-radius:var(--radius-md); font-weight:600; cursor:pointer;">
          <i class="fas fa-save" style="margin-right:6px;"></i> Save Role
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ========================================== -->
<!-- PERMISSION MATRIX -->
<!-- ========================================== -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Permission Matrix</h2>
    <div style="display:flex; gap:8px; align-items:center;">
      <span style="font-size:11px; color:var(--text-light);">
        <i class="fas fa-check-square" style="color:var(--primary);"></i> Select permissions for this role
      </span>
      <button type="button" id="selectAllBtn" class="btn btn-sm" style="background:var(--cream); border:1px solid var(--border); padding:4px 10px; border-radius:4px; cursor:pointer; font-size:11px; font-weight:600;">
        <i class="fas fa-check-double"></i> All
      </button>
      <button type="button" id="deselectAllBtn" class="btn btn-sm" style="background:var(--cream); border:1px solid var(--border); padding:4px 10px; border-radius:4px; cursor:pointer; font-size:11px; font-weight:600;">
        <i class="fas fa-times"></i> None
      </button>
    </div>
  </div>
  <div class="admin-card-body" style="padding: var(--space-md); overflow-x: auto;">
    <form id="permMatrixForm" action="admin/role-edit<?php echo $isEditMode ? '?id=' . $id : ''; ?>" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="name" value="<?php echo htmlspecialchars($roleName); ?>">
      <input type="hidden" name="slug" value="<?php echo htmlspecialchars($roleSlug); ?>">
      <input type="hidden" name="description" value="<?php echo htmlspecialchars($roleDescription); ?>">
      <input type="hidden" name="sort_order" value="<?php echo $isEditMode ? ($role['sort_order'] ?? 0) : 0; ?>">
      <?php if ($isEditMode): ?>
        <input type="hidden" name="is_active" value="<?php echo $role['is_active'] ?? 1; ?>">
      <?php endif; ?>

      <table class="admin-table" style="min-width:700px;" id="permMatrix">
        <thead>
          <tr>
            <th style="min-width:180px;">Module</th>
            <?php
            $allActions = ['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete', 'export' => 'Export'];
            foreach ($allActions as $actionKey => $actionLabel):
            ?>
              <th style="text-align:center; width:80px;" class="col-<?php echo $actionKey; ?>">
                <?php echo $actionLabel; ?>
                <br>
                <button type="button" class="col-toggle" data-action="<?php echo $actionKey; ?>" style="background:none; border:1px solid #ccc; border-radius:3px; padding:2px 6px; cursor:pointer; font-size:10px; margin-top:2px;" title="Toggle all <?php echo $actionLabel; ?>">
                  <i class="fas fa-check"></i>
                </button>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($modulesMeta as $module => $meta): 
            $modulePerms = $groupedPermissions[$module] ?? [];
            $moduleIcon = $meta['icon'] ?? 'fa-cube';
          ?>
            <tr class="perm-row" data-module="<?php echo $module; ?>">
              <td>
                <div style="display:flex; align-items:center; gap:8px;">
                  <i class="fas <?php echo $moduleIcon; ?>" style="color:var(--primary); width:16px; text-align:center;"></i>
                  <strong style="font-size:13px;"><?php echo htmlspecialchars($meta['label']); ?></strong>
                  <button type="button" class="row-toggle" data-module="<?php echo $module; ?>" style="background:none; border:1px solid #ccc; border-radius:3px; padding:2px 6px; cursor:pointer; font-size:10px; color:var(--text-light);" title="Toggle all in <?php echo $meta['label']; ?>">
                    <i class="fas fa-check-double"></i>
                  </button>
                </div>
              </td>
              <?php foreach ($allActions as $actionKey => $actionLabel):
                $permExists = false;
                $permId = null;
                foreach ($modulePerms as $p) {
                  if ($p['action'] === $actionKey) {
                    $permExists = true;
                    $permId = $p['id'];
                    break;
                  }
                }
              ?>
                <td style="text-align:center;">
                  <?php if ($permExists): ?>
                    <label style="display:inline-flex; align-items:center; justify-content:center; width:100%; height:100%; cursor:pointer; padding:6px 0;">
                      <input type="checkbox" name="permissions[]" value="<?php echo $permId; ?>"
                        <?php echo in_array($permId, $rolePermissionIds) ? 'checked' : ''; ?>
                        style="width:18px; height:18px; cursor:pointer;"
                        class="perm-checkbox" data-module="<?php echo $module; ?>" data-action="<?php echo $actionKey; ?>">
                    </label>
                  <?php else: ?>
                    <span style="color:#ddd; font-size:11px;">—</span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="margin-top:var(--space-md); padding-top:var(--space-md); border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:12px; color:var(--text-light);">
          <span id="selectedCount"><?php echo count($rolePermissionIds); ?></span> of <?php echo count($allPermissions); ?> permissions selected
        </div>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:10px 24px; border-radius:var(--radius-md); font-weight:600; cursor:pointer;">
          <i class="fas fa-save" style="margin-right:6px;"></i> Save Permissions
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const checkboxes = document.querySelectorAll('.perm-checkbox');
  const selectedCount = document.getElementById('selectedCount');

  function updateCount() {
    const checked = document.querySelectorAll('.perm-checkbox:checked').length;
    if (selectedCount) selectedCount.textContent = checked;
  }

  // Select All
  document.getElementById('selectAllBtn')?.addEventListener('click', function() {
    checkboxes.forEach(cb => cb.checked = true);
    updateCount();
  });

  // Deselect All
  document.getElementById('deselectAllBtn')?.addEventListener('click', function() {
    checkboxes.forEach(cb => cb.checked = false);
    updateCount();
  });

  // Toggle column
  document.querySelectorAll('.col-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
      const action = this.dataset.action;
      const colCheckboxes = document.querySelectorAll(`.perm-checkbox[data-action="${action}"]`);
      const allChecked = Array.from(colCheckboxes).every(cb => cb.checked);
      colCheckboxes.forEach(cb => cb.checked = !allChecked);
      updateCount();
    });
  });

  // Toggle row (module)
  document.querySelectorAll('.row-toggle').forEach(btn => {
    btn.addEventListener('click', function() {
      const module = this.dataset.module;
      const rowCheckboxes = document.querySelectorAll(`.perm-checkbox[data-module="${module}"]`);
      const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
      rowCheckboxes.forEach(cb => cb.checked = !allChecked);
      updateCount();
    });
  });

  // Update count on any checkbox change
  checkboxes.forEach(cb => {
    cb.addEventListener('change', updateCount);
  });
});
</script>

<?php include __DIR__ . '/../../Kernel/Admin/partials/footer.php'; ?>
