<?php
/**
 * Sudamaseva Module — Edit Donor Profile (Admin)
 *
 * Separate page to update a donor's profile details.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.edit');

// Initialize Session CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Edit Donor Profile';
$activePage = 'sudamaseva-donors';
include 'partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaService;
use Isjm\Modules\Sudamaseva\SudamasevaRepository;

$service = new SudamasevaService();
$repo = new SudamasevaRepository();
$error = '';
$success = '';

$donorId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($donorId <= 0) {
    echo '<div class="admin-page-header"><div class="admin-page-title"><h1>Invalid Request</h1></div></div>';
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> Donor ID is required.</div>';
    echo '<a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">&larr; Back to Donors</a>';
    include 'partials/footer.php';
    exit;
}

// POST Handler for Donor Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['donor_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pan = trim($_POST['pan'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $source = trim($_POST['source'] ?? 'sudamaseva');
        $status = trim($_POST['status'] ?? 'active');
        $notes = trim($_POST['notes'] ?? '');

        if (!$name) {
            $error = 'Donor name is required.';
        } elseif (!$phone) {
            $error = 'Phone number is required.';
        } else {
            // Check phone uniqueness
            $existing = $repo->getDonorByPhone($phone);
            if ($existing && (int)$existing['id'] !== $donorId) {
                $error = 'Phone number is already registered to another donor.';
            } else {
                $updateData = [
                    'donor_name' => $name,
                    'phone' => $phone,
                    'email' => $email ?: null,
                    'pan' => $pan ? strtoupper($pan) : null,
                    'area' => $area,
                    'city' => $city,
                    'state' => $state,
                    'source' => $source,
                    'status' => $status,
                    'notes' => $notes ?: null
                ];

                if ($repo->updateDonor($donorId, $updateData)) {
                    // Redirect back to detail page with success flag
                    header('Location: ' . BASE_URL . 'admin/sudamaseva-donor-detail?id=' . $donorId . '&success=edit');
                    exit;
                } else {
                    $error = 'Failed to update donor profile in database.';
                }
            }
        }
    }
}

// Load current donor data
$donor = $repo->getDonorById($donorId);

if (!$donor) {
    echo '<div class="admin-page-header"><div class="admin-page-title"><h1>Donor Not Found</h1></div></div>';
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> Donor #' . $donorId . ' not found.</div>';
    echo '<a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">&larr; Back to Donors</a>';
    include 'partials/footer.php';
    exit;
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-user-edit" style="margin-right:8px;"></i> Edit Profile: <?php echo htmlspecialchars($donor['donor_name']); ?></h1>
    <p>Modify contact information, PAN card, address, or admin notes for Donor #<?php echo $donor['id']; ?>.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/sudamaseva-donor-detail?id=<?php echo $donorId; ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">
      <i class="fas fa-arrow-left"></i> Cancel & Back
    </a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width: 700px; margin: 0 auto var(--space-2xl);">
  <div class="admin-card-header" style="background:var(--cream);">
    <h2>Profile details</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-xl);">
    <form action="admin/sudamaseva-donor-edit?id=<?php echo $donorId; ?>" method="POST">
      <input type="hidden" name="action" value="update_profile">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

      <div class="form-group">
        <label for="donor_name" style="font-weight:600;">Donor Name *</label>
        <input type="text" id="donor_name" name="donor_name" class="form-control" value="<?php echo htmlspecialchars($donor['donor_name']); ?>" required>
      </div>

      <div class="form-group">
        <label for="phone" style="font-weight:600;">Phone *</label>
        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($donor['phone']); ?>" required>
      </div>

      <div class="form-group">
        <label for="email" style="font-weight:600;">Email</label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($donor['email'] ?? ''); ?>">
      </div>

      <div class="form-group">
        <label for="pan" style="font-weight:600;">PAN</label>
        <input type="text" id="pan" name="pan" class="form-control" style="text-transform:uppercase;" placeholder="ABCDE1234F" pattern="[a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}" title="Standard PAN Format: 5 Letters, 4 Digits, 1 Letter" value="<?php echo htmlspecialchars($donor['pan'] ?? ''); ?>">
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-md);">
        <div class="form-group">
          <label for="area" style="font-weight:600;">Area</label>
          <input type="text" id="area" name="area" class="form-control" value="<?php echo htmlspecialchars($donor['area'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="city" style="font-weight:600;">City</label>
          <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($donor['city'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="state" style="font-weight:600;">State</label>
        <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($donor['state'] ?? ''); ?>">
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-md);">
        <div class="form-group">
          <label for="source" style="font-weight:600;">Source</label>
          <select id="source" name="source" class="form-control" style="height:auto; padding:8px;">
            <option value="sudamaseva" <?php echo ($donor['source'] ?? '') === 'sudamaseva' ? 'selected' : ''; ?>>Sudamaseva (New)</option>
            <option value="migrated" <?php echo ($donor['source'] ?? '') === 'migrated' ? 'selected' : ''; ?>>Legacy (Old)</option>
            <option value="manual" <?php echo ($donor['source'] ?? '') === 'manual' ? 'selected' : ''; ?>>Manual Entry</option>
            <option value="api" <?php echo ($donor['source'] ?? '') === 'api' ? 'selected' : ''; ?>>API Import</option>
          </select>
        </div>
        <div class="form-group">
          <label for="status" style="font-weight:600;">Status</label>
          <select id="status" name="status" class="form-control" style="height:auto; padding:8px;">
            <option value="active" <?php echo ($donor['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo ($donor['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="paused" <?php echo ($donor['status'] ?? '') === 'paused' ? 'selected' : ''; ?>>Paused</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="notes" style="font-weight:600;">Admin Notes</label>
        <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($donor['notes'] ?? ''); ?></textarea>
      </div>

      <div style="display:flex; gap:10px; margin-top:15px;">
        <button type="submit" class="btn btn-primary" style="background-color:var(--maroon); color:white; border:none; flex:1; padding:12px; border-radius:var(--radius-md); font-weight:700; font-size:14px; cursor:pointer;">
          <i class="fas fa-save"></i> Save Profile Changes
        </button>
        <a href="admin/sudamaseva-donor-detail?id=<?php echo $donorId; ?>" class="btn btn-outline-dark" style="text-decoration:none; text-align:center; padding:12px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; font-size:14px; color:var(--text);">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
