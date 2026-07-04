<?php
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin']); // Strictly Super Admin only

$pageTitle = 'Edit Admin';
$activePage = 'admins';
include 'partials/header.php';

$db = getDB();
$error = '';
$success = '';

// Determine Mode
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEditMode = ($id > 0);

$username = '';
$email = '';
$fullName = '';
$role = 'editor'; // default

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
    } catch (PDOException $e) {
        $error = 'Failed to load user details. Please try again.';
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } else {
        $emailInput = trim($_POST['email'] ?? '');
    $fullNameInput = trim($_POST['full_name'] ?? '');
    $roleInput = trim($_POST['role'] ?? 'editor');
    $passwordInput = $_POST['password'] ?? '';
    
    if (!$isEditMode) {
        $usernameInput = trim($_POST['username'] ?? '');
    } else {
        $usernameInput = $username;
    }

    // Basic Validations
    if (empty($emailInput) || empty($fullNameInput) || empty($roleInput)) {
        $error = 'Please fill in all required fields (Email, Full Name, Role).';
    } elseif (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!$isEditMode && empty($passwordInput)) {
        $error = 'Password is required for new accounts.';
    } elseif (!empty($passwordInput) && strlen($passwordInput) < 8) {
        $error = 'Password must be at least 8 characters long for security.';
    } elseif (!$isEditMode && !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $usernameInput)) {
        $error = 'Username must be alphanumeric (3-30 characters, letters/numbers/underscores only).';
    } else {
        try {
            // Check unique constraints
            if (!$isEditMode) {
                // Check unique username
                $chk1 = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
                $chk1->execute([$usernameInput]);
                if ((int)$chk1->fetchColumn() > 0) {
                    $error = 'Username already exists.';
                }
            }
            
            // Check unique email
            if (empty($error)) {
                $chk2 = $db->prepare("SELECT COUNT(*) FROM admins WHERE email = ? AND id != ?");
                $chk2->execute([$emailInput, $id]);
                if ((int)$chk2->fetchColumn() > 0) {
                    $error = 'Email address is already in use by another administrator.';
                }
            }

            // Save / Update
            if (empty($error)) {
                if ($isEditMode) {
                    // Update
                    if (!empty($passwordInput)) {
                        // Include password change
                        $passHash = password_hash($passwordInput, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("
                            UPDATE admins 
                            SET email = ?, full_name = ?, role = ?, password_hash = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$emailInput, $fullNameInput, $roleInput, $passHash, $id]);
                    } else {
                        // Standard update
                        $stmt = $db->prepare("
                            UPDATE admins 
                            SET email = ?, full_name = ?, role = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$emailInput, $fullNameInput, $roleInput, $id]);
                    }
                    
                    // Self-update session role if modifying own account
                    if ($id === (int)$_SESSION['admin_id']) {
                        $_SESSION['admin_role'] = $roleInput;
                        $_SESSION['admin_name'] = $fullNameInput;
                    }
                    
                    $success = 'Account updated successfully!';
                    
                    // Refresh local values
                    $email = $emailInput;
                    $fullName = $fullNameInput;
                    $role = $roleInput;
                } else {
                    // Insert
                    $passHash = password_hash($passwordInput, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        INSERT INTO admins (username, email, full_name, role, password_hash)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$usernameInput, $emailInput, $fullNameInput, $roleInput, $passHash]);
                    
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

<div class="admin-card" style="max-width: 600px; margin: 0 auto var(--space-3xl) auto;">
  <div class="admin-card-header">
    <h2>Account Details</h2>
  </div>
  <div class="admin-card-body" style="padding: var(--space-xl);">
    <form action="admin/admin-edit?id=<?php echo $id; ?>" method="POST" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      
      <div class="form-group">
        <label for="username">Username <?php if ($isEditMode): ?><span style="color:var(--text-light); font-weight:normal;">(Cannot be changed)</span><?php else: ?><span style="color:var(--maroon);">*</span><?php endif; ?></label>
        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" <?php echo $isEditMode ? 'disabled style="background:var(--cream);"' : 'required'; ?> placeholder="e.g. pujari_head">
      </div>

      <div class="form-group">
        <label for="full_name">Full Name <span style="color:var(--maroon);">*</span></label>
        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($fullName); ?>" required placeholder="e.g. Nitai Gauranga Das">
      </div>

      <div class="form-group">
        <label for="email">Email Address <span style="color:var(--maroon);">*</span></label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required placeholder="e.g. nitai@iskconbangalore.co.in">
      </div>

      <div class="form-group">
        <label for="role">Portal Access Role <span style="color:var(--maroon);">*</span></label>
        <select id="role" name="role" class="form-control" required>
          <option value="super_admin" <?php echo $role === 'super_admin' ? 'selected' : ''; ?>>Super Admin (Full Access)</option>
          <option value="editor" <?php echo $role === 'editor' ? 'selected' : ''; ?>>Editor (Manage Articles/Causes)</option>
          <option value="pujari" <?php echo $role === 'pujari' ? 'selected' : ''; ?>>Pujari (Manage Puja/Yagya Bookings)</option>
          <option value="treasurer" <?php echo $role === 'treasurer' ? 'selected' : ''; ?>>Treasurer (View Donations & CSV)</option>
        </select>
      </div>

      <div class="form-group">
        <label for="password">Password <?php if ($isEditMode): ?><span style="color:var(--text-light); font-weight:normal;">(Leave blank to keep unchanged)</span><?php else: ?><span style="color:var(--maroon);">*</span><?php endif; ?></label>
        <input type="password" id="password" name="password" class="form-control" <?php echo $isEditMode ? '' : 'required'; ?> placeholder="<?php echo $isEditMode ? '••••••••' : 'Enter account password'; ?>">
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
