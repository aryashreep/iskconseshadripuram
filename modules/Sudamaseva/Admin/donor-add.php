<?php
/**
 * Sudamaseva Module — Register/Enroll New Donor (Admin)
 *
 * Admin form to register a donor and create a subscription.
 * If the donor already exists by phone, links the new subscription to their profile.
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

$pageTitle = 'Enroll New Donor';
$activePage = 'sudamaseva-donors';
include 'partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaRepository;
use Isjm\Modules\Sudamaseva\SudamasevaService;

$repo = new SudamasevaRepository();
$service = new SudamasevaService($repo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll_donor') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        // Extract fields
        $donorName = trim($_POST['donor_name'] ?? '');
        $donorPhone = trim($_POST['phone'] ?? '');
        $donorEmail = trim($_POST['email'] ?? '');
        $panNumber = strtoupper(trim($_POST['pan'] ?? ''));
        $donorArea = trim($_POST['area'] ?? '');
        $donorCity = trim($_POST['city'] ?? '');
        $donorState = trim($_POST['state'] ?? '');
        
        $collectionMode = trim($_POST['collection_mode'] ?? 'offline');
        $amountInr = (int) ($_POST['amount'] ?? 100);
        $totalInstallments = (int) ($_POST['total_installments'] ?? 24);
        $cycle = max(1, (int) ($_POST['cycle'] ?? 1));
        $startDate = trim($_POST['start_date'] ?? '');

        if (!$donorName) {
            $error = 'Donor name is required.';
        } elseif (!$donorPhone) {
            $error = 'Phone number is required.';
        } elseif ($amountInr < 50) {
            $error = 'Minimum monthly amount is ₹50.';
        } else {
            try {
                $db = getDB();
                $db->beginTransaction();

                // Find or create donor
                $donor = $repo->getDonorByPhone($donorPhone);
                if ($donor) {
                    $donorId = (int) $donor['id'];
                    $updateData = [];
                    if (!empty($donorName) && $donor['donor_name'] !== $donorName) {
                        $updateData['donor_name'] = $donorName;
                    }
                    if (!empty($donorEmail) && empty($donor['email'])) {
                        $updateData['email'] = $donorEmail;
                    }
                    if (!empty($panNumber) && empty($donor['pan'])) {
                        $updateData['pan'] = $panNumber;
                    }
                    if (!empty($updateData)) {
                        $repo->updateDonor($donorId, $updateData);
                    }
                } else {
                    $donorId = $repo->createDonor([
                        'donor_name' => $donorName,
                        'phone' => $donorPhone,
                        'email' => $donorEmail ?: null,
                        'pan' => $panNumber ?: null,
                        'area' => $donorArea ?: null,
                        'city' => $donorCity ?: null,
                        'state' => $donorState ?: null,
                        'source' => 'manual',
                        'status' => 'active',
                    ]);

                    if (!$donorId) {
                        throw new RuntimeException('Failed to create donor record');
                    }
                }

                // Create subscription
                $subscriptionId = $repo->createSubscription([
                    'donor_id' => $donorId,
                    'amount' => $amountInr,
                    'status' => 'active',
                    'start_date' => $startDate ?: date('Y-m-d H:i:s'),
                    'total_installments' => $totalInstallments,
                    'source' => $cycle > 1 ? 'renewal' : 'new',
                ]);

                if (!$subscriptionId) {
                    throw new RuntimeException('Failed to create subscription record');
                }

                // Update collection_mode and cycle columns
                $stmtMode = $db->prepare("UPDATE sudamaseva_subscriptions SET collection_mode = ?, cycle = ? WHERE id = ?");
                $stmtMode->execute([$collectionMode, $cycle, $subscriptionId]);

                $db->commit();
                
                // Redirect to donor detail page
                header('Location: ' . BASE_URL . 'admin/sudamaseva-donor-detail?id=' . $donorId . '&success=enroll');
                exit;

            } catch (Exception $e) {
                if (isset($db) && $db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Enrollment failed: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-user-plus" style="margin-right:8px;"></i> Enroll New Donor</h1>
    <p>Register a donor and start their Sudamaseva subscription cycle.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">
      <i class="fas fa-arrow-left"></i> Back to Donors
    </a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width: 800px; margin: 0 auto var(--space-2xl);">
  <div class="admin-card-header" style="background:var(--cream);">
    <h2>Donor Registration & Subscription Details</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-xl);">
    <form action="admin/sudamaseva-donor-add" method="POST">
      <input type="hidden" name="action" value="enroll_donor">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

      <h3 style="font-family:var(--font-heading); font-size:16px; margin-bottom:var(--space-md); color:var(--maroon); border-bottom:1px solid var(--border); padding-bottom:6px;"><i class="fas fa-id-card"></i> 1. Contact Information</h3>
      
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-md);">
        <div class="form-group">
          <label for="donor_name" style="font-weight:600;">Donor Name *</label>
          <input type="text" id="donor_name" name="donor_name" class="form-control" placeholder="Full name of donor" value="<?php echo htmlspecialchars($_GET['donor_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label for="phone" style="font-weight:600;">Phone Number *</label>
          <input type="text" id="phone" name="phone" class="form-control" placeholder="10-digit mobile" value="<?php echo htmlspecialchars($_GET['phone'] ?? ''); ?>" required>
          <small style="font-size:10px; color:var(--text-light);">If the phone number exists, the subscription will link to their existing profile.</small>
        </div>
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-xl);">
        <div class="form-group">
          <label for="email" style="font-weight:600;">Email Address</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="name@domain.com" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="pan" style="font-weight:600;">PAN Number (for 80G)</label>
          <input type="text" id="pan" name="pan" class="form-control" style="text-transform:uppercase;" placeholder="ABCDE1234F" pattern="[a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}" title="Standard PAN Format: 5 Letters, 4 Digits, 1 Letter" value="<?php echo htmlspecialchars($_GET['pan'] ?? ''); ?>">
        </div>
      </div>

      <h3 style="font-family:var(--font-heading); font-size:16px; margin-bottom:var(--space-md); color:var(--maroon); border-bottom:1px solid var(--border); padding-bottom:6px;"><i class="fas fa-map-marker-alt"></i> 2. Address Details</h3>

      <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:var(--space-lg); margin-bottom:var(--space-xl);">
        <div class="form-group">
          <label for="area" style="font-weight:600;">Area / Locality</label>
          <input type="text" id="area" name="area" class="form-control" placeholder="e.g. Malleswaram" value="<?php echo htmlspecialchars($_GET['area'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="city" style="font-weight:600;">City</label>
          <input type="text" id="city" name="city" class="form-control" placeholder="e.g. Bangalore" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="state" style="font-weight:600;">State</label>
          <input type="text" id="state" name="state" class="form-control" placeholder="e.g. Karnataka" value="<?php echo htmlspecialchars($_GET['state'] ?? ''); ?>">
        </div>
      </div>

      <h3 style="font-family:var(--font-heading); font-size:16px; margin-bottom:var(--space-md); color:var(--maroon); border-bottom:1px solid var(--border); padding-bottom:6px;"><i class="fas fa-sync"></i> 3. Subscription Settings</h3>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:var(--space-lg); margin-bottom:var(--space-md);">
        <div class="form-group">
          <label for="collection_mode" style="font-weight:600;">Collection Mode *</label>
          <select id="collection_mode" name="collection_mode" class="form-control" style="height:auto; padding:8px;" required>
            <option value="offline" selected>Offline (Cash / Cheque / Bank Transfer)</option>
            <option value="hybrid">Hybrid (Allows online pay OR offline cash)</option>
            <option value="manual">Pay Monthly (Online Razorpay order)</option>
            <option value="recurring">Auto Monthly (Razorpay auto-debit)</option>
          </select>
        </div>
        <div class="form-group">
          <label for="amount" style="font-weight:600;">Monthly Offering Amount *</label>
          <div style="display:flex; gap:6px; align-items:center;">
            <span style="font-weight:700; font-size:18px;">₹</span>
            <input type="number" id="amount" name="amount" class="form-control" value="500" min="50" required>
          </div>
          <div style="display:flex; gap:4px; flex-wrap:wrap; margin-top:6px;">
            <button type="button" onclick="setFormAmount(100)" style="padding:2px 8px; border:1px solid var(--border); background:#f9f9f9; border-radius:3px; font-size:10px; cursor:pointer;">₹100</button>
            <button type="button" onclick="setFormAmount(200)" style="padding:2px 8px; border:1px solid var(--border); background:#f9f9f9; border-radius:3px; font-size:10px; cursor:pointer;">₹200</button>
            <button type="button" onclick="setFormAmount(500)" style="padding:2px 8px; border:1px solid var(--border); background:#f9f9f9; border-radius:3px; font-size:10px; cursor:pointer;">₹500</button>
            <button type="button" onclick="setFormAmount(1000)" style="padding:2px 8px; border:1px solid var(--border); background:#f9f9f9; border-radius:3px; font-size:10px; cursor:pointer;">₹1000</button>
            <button type="button" onclick="setFormAmount(2000)" style="padding:2px 8px; border:1px solid var(--border); background:#f9f9f9; border-radius:3px; font-size:10px; cursor:pointer;">₹2000</button>
          </div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:var(--space-lg); margin-bottom:var(--space-xl);">
        <div class="form-group">
          <label for="total_installments" style="font-weight:600;">Duration *</label>
          <select id="total_installments" name="total_installments" class="form-control" style="height:auto; padding:8px;" required>
            <option value="6">6 Months</option>
            <option value="12">12 Months</option>
            <option value="24" selected>24 Months (Max)</option>
            <option value="0">Open-ended</option>
          </select>
        </div>
        <div class="form-group">
          <label for="cycle" style="font-weight:600;">Subscription Cycle</label>
          <input type="number" id="cycle" name="cycle" class="form-control" value="<?php echo htmlspecialchars($_GET['cycle'] ?? '1'); ?>" min="1" required>
        </div>
        <div class="form-group">
          <label for="start_date" style="font-weight:600;">Start Date</label>
          <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="background-color:var(--maroon); color:white; border:none; width:100%; padding:14px; border-radius:var(--radius-md); font-weight:700; font-size:14px; cursor:pointer;">
        <i class="fas fa-check-circle"></i> Create Donor & Subscription
      </button>
    </form>
  </div>
</div>

<script>
function setFormAmount(amt) {
  document.getElementById('amount').value = amt;
}
</script>

<?php include 'partials/footer.php'; ?>
