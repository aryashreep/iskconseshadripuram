<?php
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin']); // Strictly Super Admin only

use Isjm\Modules\RBAC\RbacService;

$pageTitle = 'Edit Admin';
$activePage = 'admin-edit';
include 'partials/header.php';

$db = getDB();
$rbac = new RbacService();
$error = '';
$success = '';

// Determine Mode
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEditMode = ($id > 0);

$username = '';
$email = '';
$fullName = '';
$role = 'editor';
$assignedRoleIds = [];

if ($isEditMode) {
    try {
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $admin = $stmt->fetch();
        if (!$admin) {
            echo "<div class='alert alert-danger'>Admin not found.</div>";
            include 'partials/footer.php';
            exit;
        }
        $username = $admin['username'];
        $email = $admin['email'];
        $fullName = $admin['full_name'];
        $role = $admin['role'];

        // Get RBAC role assignments
        $assignedRoles = $rbac->getAdminRoles($id);
        $assignedRoleIds = array_map(function($r) { return $r['id']; }, $assignedRoles);
    } catch (PDOException $e) {
        $error = 'Failed to load user details. Please try again.';
    }
}

// Get all available RBAC roles
$availableRoles = [];
try {
    $availableRoles = $rbac->getAllRoles(true);
} catch (Exception $e) {
    $error = 'Failed to load roles. RBAC tables may not be initialized. Run RBAC migrations first.';
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } else {
        $emailInput = trim($_POST['email'] ?? '');
        $fullNameInput = trim($_POST['full_name'] ?? '');
        $passwordInput = $_POST['password'] ?? '';

        // New RBAC role IDs
        $rbacRoleIdsInput = $_POST['rbac_roles'] ?? [];
        $rbacRoleIdsSanitized = array_map('intval', $rbacRoleIdsInput);

        // Check if super_admin role is among selected RBAC roles
        $hasSuperAdminInRbac = false;
        foreach ($availableRoles as $ar) {
            if ($ar['slug'] === 'super_admin' && in_array($ar['id'], $rbacRoleIdsSanitized)) {
                $hasSuperAdminInRbac = true;
                break;
            }
        }
        $roleInput = $hasSuperAdminInRbac ? 'super_admin' : 'editor';

        if (!$isEditMode) {
            $usernameInput = trim($_POST['username'] ?? '');
        } else {
            $usernameInput = $username;
        }

        // Basic Validations
        if (empty($emailInput) || empty($fullNameInput)) {
            $error = 'Please fill in all required fields (Email, Full Name).';
        } elseif (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($isEditMode && $id === (int)$_SESSION['admin_id'] && !$hasSuperAdminInRbac && in_array('super_admin', array_map('trim', explode(',', $role)))) {
            $error = 'For security reasons, you cannot remove the Super Admin role from your own account.';
        } elseif (!$isEditMode && empty($passwordInput)) {
            $error = 'Password is required for new accounts.';
        } elseif (!empty($passwordInput) && strlen($passwordInput) < 8) {
            $error = 'Password must be at least 8 characters long for security.';
        } elseif (!$isEditMode && !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $usernameInput)) {
            $error = 'Username must be alphanumeric (3-30 characters, letters/numbers/underscores only).';
        } else {
            try {
                if (!$isEditMode) {
                    $chk1 = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
                    $chk1->execute([$usernameInput]);
                    if ((int)$chk1->fetchColumn() > 0) {
                        $error = 'Username already exists.';
                    }
                }

                if (empty($error)) {
                    $chk2 = $db->prepare("SELECT COUNT(*) FROM admins WHERE email = ? AND id != ?");
                    $chk2->execute([$emailInput, $id]);
                    if ((int)$chk2->fetchColumn() > 0) {
                        $error = 'Email address is already in use by another administrator.';
                    }
                }

                if (empty($error)) {
                    if ($isEditMode) {
                        if (!empty($passwordInput)) {
                            $passHash = password_hash($passwordInput, PASSWORD_DEFAULT);
                            $stmt = $db->prepare("UPDATE admins SET email = ?, full_name = ?, role = ?, password_hash = ? WHERE id = ?");
                            $stmt->execute([$emailInput, $fullNameInput, $roleInput, $passHash, $id]);
                        } else {
                            $stmt = $db->prepare("UPDATE admins SET email = ?, full_name = ?, role = ? WHERE id = ?");
                            $stmt->execute([$emailInput, $fullNameInput, $roleInput, $id]);
                        }

                        // Update RBAC role assignments
                        $rbac->assignRoles($id, $rbacRoleIdsSanitized, (int) $_SESSION['admin_id']);

                        // Self-update session if modifying own account
                        if ($id === (int)$_SESSION['admin_id']) {
                            $_SESSION['admin_role'] = $roleInput;
                            $_SESSION['admin_name'] = $fullNameInput;
                            $rbac->loadPermissionsIntoSession($id);
                        }

                        $success = 'Account updated successfully!';
                        $email = $emailInput;
                        $fullName = $fullNameInput;
                        $role = $roleInput;
                        $assignedRoles = $rbac->getAdminRoles($id);
                        $assignedRoleIds = array_map(function($r) { return $r['id']; }, $assignedRoles);
                    } else {
                        $passHash = password_hash($passwordInput, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO admins (username, email, full_name, role, password_hash) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$usernameInput, $emailInput, $fullNameInput, $roleInput, $passHash]);

                        $newAdminId = (int) $db->lastInsertId();
                        $rbac->assignRoles($newAdminId, $rbacRoleIdsSanitized, (int) $_SESSION['admin_id']);

                        header('Location: ' . BASE_URL . 'admin/admins?success=' . urlencode('Account created successfully!'));
                        exit;
                    }
                }
            } catch (PDOException $e) {
                $error = 'A database error occurred. Please try again.';
            }
        }
    }
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><?php echo $isEditMode ? 'Edit Administrative Account' : 'Add New Administrator'; ?></h1>
    <p>Define administrative access and roles for the temple database portal.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/admins" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 20px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; color:var(--text); font-size:13px; display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-arrow-left"></i> Back to List
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

<div class="admin-card" style="max-width: 650px; margin: 0 auto var(--space-3xl) auto;">
  <div class="admin-card-header">
    <h2>Account Details</h2>
  </div>
  <div class="admin-card-body" style="padding: var(--space-xl);">
    <form action="admin/admin-edit?id=<?php echo $id; ?>" method="POST" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <div class="form-group">
        <label for="username">Username <?php if ($isEditMode): ?><span style="color:var(--text-light); font-weight:normal;">(Cannot be changed)</span><?php else: ?><span style="color:var(--maroon);">*</span><?php endif; ?></label>
        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" <?php echo $isEditMode ? 'disabled style="background:var(--cream);"' : 'required'; ?> placeholder="e.g. manager">
      </div>

      <div class="form-group">
        <label for="full_name">Full Name <span style="color:var(--maroon);">*</span></label>
        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($fullName); ?>" required placeholder="e.g. Nitai Gauranga Das">
      </div>

      <div class="form-group">
        <label for="email">Email Address <span style="color:var(--maroon);">*</span></label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required placeholder="e.g. manager@iskconbangalore.co.in">
      </div>

      <!-- RBAC Role Multi-Select -->
      <div class="form-group">
        <label style="font-weight: 600; display: block; margin-bottom: var(--space-sm);">
          <i class="fas fa-shield-alt" style="color:var(--primary);"></i> RBAC Roles <span style="color:var(--maroon);">*</span>
        </label>
        <?php if (empty($availableRoles)): ?>
          <div style="background:var(--cream); border:1px solid var(--border); border-radius:var(--radius-md); padding:12px; font-size:12px; color:var(--text-light);">
            <i class="fas fa-exclamation-triangle" style="color:var(--primary);"></i>
            RBAC system not initialized. Run the RBAC seed migration first.
            <a href="admin/roles" style="color:var(--primary);">Manage Roles</a>
          </div>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:6px; background:var(--white); border:1px solid var(--border); padding:12px; border-radius:var(--radius-md);">
            <?php foreach ($availableRoles as $ar):
              $isSelfSuperAdmin = $isEditMode && $id === (int)$_SESSION['admin_id'] && $ar['slug'] === 'super_admin';
            ?>
              <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:normal; margin:0; padding:4px 0;">
                <input type="checkbox" name="rbac_roles[]" value="<?php echo $ar['id']; ?>"
                  <?php echo in_array($ar['id'], $assignedRoleIds) ? 'checked' : ''; ?>
                  <?php echo $isSelfSuperAdmin ? 'disabled' : ''; ?>
                  style="width:16px; height:16px; cursor:pointer;">
                <span>
                  <strong style="font-size:13px;"><?php echo htmlspecialchars($ar['name']); ?></strong>
                  <?php if ($ar['slug'] === 'super_admin'): ?>
                    <span class="badge" style="background:#fff3e0; color:#e65100; font-size:9px; border:1px solid #ffe0b2; margin-left:4px;"><i class="fas fa-bolt"></i> Bypass</span>
                  <?php endif; ?>
                  <?php if ($ar['is_system']): ?>
                    <span class="badge" style="background:#f0f0f0; color:#666; font-size:9px; border:1px solid #ddd; margin-left:2px;">System</span>
                  <?php endif; ?>
                  <span style="display:block; font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($ar['description'] ?? ''); ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <?php if ($isEditMode && in_array(true, array_map(function($ar) use ($id) { return $id === (int)$_SESSION['admin_id'] && $ar['slug'] === 'super_admin'; }, $availableRoles))): ?>
            <div style="background:var(--cream); border:1px solid var(--border); border-radius:var(--radius-md); padding:10px; margin-top:8px; font-size:11px; color:var(--text-light);">
              <i class="fas fa-info-circle" style="color:var(--primary);"></i>
              You cannot remove the Super Administrator role from your own account for security reasons.
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password">Password <?php if ($isEditMode): ?><span style="color:var(--text-light); font-weight:normal;">(Leave blank to keep unchanged)</span><?php else: ?><span style="color:var(--maroon);">*</span><?php endif; ?></label>
        <input type="password" id="password" name="password" class="form-control" <?php echo $isEditMode ? '' : 'required'; ?> placeholder="<?php echo $isEditMode ? 'Leave blank to keep unchanged' : 'Enter account password'; ?>">
      </div>

      <div style="margin-top:var(--space-md); padding-top:var(--space-md); border-top:1px solid var(--border); display:flex; justify-content:flex-end;">
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:10px 24px; border-radius:var(--radius-md); font-weight:600; cursor:pointer;">
          <i class="fas fa-save" style="margin-right:6px;"></i> Save Account
        </button>
      </div>

    </form>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
