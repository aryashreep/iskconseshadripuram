<?php
/**
 * Sudamaseva Module — Donor Profile Detail View (Admin)
 *
 * Displays a single donor's contact info, subscriptions, and payment history.
 * Supports updating the donor profile directly from the admin panel.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

// Initialize Session CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

use Isjm\Modules\Sudamaseva\SudamasevaService;
use Isjm\Modules\Sudamaseva\SudamasevaRepository;

$service = new SudamasevaService();
$repo = new SudamasevaRepository();
$error = '';
$success = '';

$donorId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Handle delete payment action (must be executed before any HTML output for redirect to work)
if (isset($_GET['action']) && $_GET['action'] === 'delete_payment' && isset($_GET['payment_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('sudamaseva.delete')) {
        $error = 'You do not have permission to delete payments.';
    } else {
        $deletePaymentId = intval($_GET['payment_id']);
        try {
            if ($service->deletePayment($deletePaymentId)) {
                header('Location: ' . BASE_URL . 'admin/sudamaseva-donor-detail?id=' . $donorId . '&success=delete_payment');
                exit;
            } else {
                $error = 'Failed to delete payment. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error deleting payment: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Donor Profile';
$activePage = 'sudamaseva-donors';

if ($donorId <= 0) {
    include 'partials/header.php';
    echo '<div class="admin-page-header"><div class="admin-page-title"><h1>Invalid Request</h1></div></div>';
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> Donor ID is required.</div>';
    echo '<a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">&larr; Back to Donors</a>';
    include 'partials/footer.php';
    exit;
}

include 'partials/header.php';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'enroll') {
        $success = 'Donor registered and subscription cycle initialized successfully!';
    } elseif ($_GET['success'] === 'edit') {
        $success = 'Donor profile updated successfully!';
    } elseif ($_GET['success'] === 'delete_payment') {
        $success = 'Payment record deleted successfully!';
    }
}

try {
    $dashboard = $service->getDonorDashboard($donorId);
} catch (Exception $e) {
    $error = 'Failed to load donor data: ' . $e->getMessage();
    $dashboard = [];
}

if (empty($dashboard) || empty($dashboard['donor'])) {
    echo '<div class="admin-page-header"><div class="admin-page-title"><h1>Donor Not Found</h1></div></div>';
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> Donor #' . $donorId . ' not found.</div>';
    echo '<a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">&larr; Back to Donors</a>';
    include 'partials/footer.php';
    exit;
}

$donor = $dashboard['donor'];
$activeSub = $dashboard['active_subscription'] ?? null;
$subscriptions = $dashboard['subscriptions'] ?? [];
$recentPayments = $dashboard['recent_payments'] ?? [];
$totalPaid = $dashboard['total_paid'] ?? 0;
$totalPaidFormatted = $dashboard['total_paid_formatted'] ?? '₹0';
$fyTotal = $dashboard['current_fy_total'] ?? 0;
$fyLabel = $service->getFinancialYearLabel();

// Determine which subscription's schedule to display
$selectedSubId = isset($_GET['sub_id']) ? (int) $_GET['sub_id'] : 0;
$selectedSub = null;

if ($selectedSubId > 0) {
    foreach ($subscriptions as $sub) {
        if ((int)$sub['id'] === $selectedSubId) {
            $selectedSub = $sub;
            break;
        }
    }
}

// Fallback to active subscription
if (!$selectedSub) {
    $selectedSub = $activeSub;
}

// Fallback to first subscription (most recent) if active is null
if (!$selectedSub && !empty($subscriptions)) {
    $selectedSub = $subscriptions[0];
}
?>
<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-user" style="margin-right:8px;"></i> <?php echo htmlspecialchars($donor['donor_name']); ?></h1>
    <p>Donor #<?php echo $donor['id']; ?> &middot; <?php echo $service->getDonorSourceLabel($donor['source'] ?? 'sudamaseva'); ?>
       &middot; Joined <?php echo $service->formatDate($donor['created_at'] ?? null); ?>
    </p>
  </div>
  <div class="admin-page-actions">
    <?php if ($activeSub): ?>
      <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $activeSub['id']; ?>" class="btn btn-success btn-sm" style="text-decoration:none; padding:8px 16px; background:#2e7d32; color:white; border-radius:var(--radius-md); font-weight:600; font-size:12px;">
        <i class="fas fa-hand-holding-usd"></i> Record Payment
      </a>
    <?php elseif (hasPermission('sudamaseva.edit')): 
      $maxCycle = 0;
      foreach ($subscriptions as $s) {
          if (isset($s['cycle']) && (int) $s['cycle'] > $maxCycle) {
              $maxCycle = (int) $s['cycle'];
          }
      }
      $nextCycle = $maxCycle + 1;
      $enrollUrl = "admin/sudamaseva-donor-add?phone=" . urlencode($donor['phone'] ?? '') 
        . "&donor_name=" . urlencode($donor['donor_name'] ?? '')
        . "&email=" . urlencode($donor['email'] ?? '')
        . "&pan=" . urlencode($donor['pan'] ?? '')
        . "&area=" . urlencode($donor['area'] ?? '')
        . "&city=" . urlencode($donor['city'] ?? '')
        . "&state=" . urlencode($donor['state'] ?? '')
        . "&cycle=" . $nextCycle;
    ?>
      <a href="<?php echo $enrollUrl; ?>" class="btn btn-primary btn-sm" style="text-decoration:none; padding:8px 16px; background:#0b5ed7; color:white; border-radius:var(--radius-md); font-weight:600; font-size:12px;" title="Enroll / Renew Subscription (New Cycle)">
        <i class="fas fa-user-plus"></i> Start New Cycle
      </a>
    <?php endif; ?>
    <?php if (hasPermission('sudamaseva.edit')): ?>
      <a href="admin/sudamaseva-donor-edit?id=<?php echo $donorId; ?>" class="btn btn-primary btn-sm" style="text-decoration:none;">
        <i class="fas fa-user-edit"></i> Edit Profile
      </a>
    <?php endif; ?>
    <a href="admin/sudamaseva-dashboard" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">
      <i class="fas fa-chart-pie"></i> Main Dashboard
    </a>
    <a href="admin/sudamaseva-donors" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">
      <i class="fas fa-users"></i> All Donors
    </a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if (isset($_GET['payment_recorded']) && $_GET['payment_recorded'] === '1'): ?>
  <div id="paymentFlash" class="alert alert-success" style="background:#d4edda; border:1px solid #c3e6cb; padding:var(--space-md); border-radius:var(--radius-md); margin-bottom:var(--space-lg); display:flex; align-items:center; gap:var(--space-sm); transition: opacity 0.8s ease;">
    <i class="fas fa-check-circle" style="color:#155724; font-size:18px;"></i>
    <span style="color:#155724; font-weight:500;">Payment recorded successfully!</span>
  </div>
  <script>
    setTimeout(function() {
      var el = document.getElementById('paymentFlash');
      if (el) {
        el.style.opacity = '0';
        setTimeout(function() { el.style.display = 'none'; }, 800);
      }
    }, 5000);
  </script>
<?php endif; ?>

<!-- Donor Profile Layout -->
<div style="display:flex; flex-direction:column; gap:var(--space-xl); margin-bottom:var(--space-xl);">
    
    <!-- Donor Info Card -->
    <div class="admin-card">
      <div class="admin-card-body">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:var(--space-lg);">
          <div>
            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; margin-bottom:4px;">Contact</div>
            <div style="font-size:14px; font-weight:500;"><?php echo htmlspecialchars($donor['phone'] ?: '—'); ?></div>
            <div style="font-size:13px; color:var(--text-light);"><?php echo htmlspecialchars($donor['email'] ?: '—'); ?></div>
          </div>
          <div>
            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; margin-bottom:4px;">PAN / Area</div>
            <div style="font-family:monospace; font-size:14px; font-weight:600; text-transform:uppercase;"><?php echo htmlspecialchars($donor['pan'] ?: '—'); ?></div>
            <div style="font-size:13px; color:var(--text-light);"><?php echo htmlspecialchars(implode(', ', array_filter([$donor['area'] ?? '', $donor['city'] ?? '', $donor['state'] ?? ''])) ?: '—'); ?></div>
          </div>
          <div>
            <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600; margin-bottom:4px;">Status</div>
            <div>
              <?php
                $statusClass = ($donor['status'] ?? '') === 'active' ? 'badge-success' : 'badge-secondary';
              ?>
              <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($donor['status'] ?? 'unknown')); ?></span>
            </div>
            <div style="font-size:12px; color:var(--text-light); margin-top:4px;">UUID: <span style="font-family:monospace; font-size:10px;"><?php echo htmlspecialchars($donor['uuid'] ?? '—'); ?></span></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Donor Stats -->
    <div class="admin-stats-grid" style="grid-template-columns: 1fr 1fr; gap:var(--space-md); margin-bottom: 0;">
      <div class="admin-stat-card" style="margin-bottom:0;">
        <div class="admin-stat-info">
          <h3>Total Paid</h3>
          <div class="admin-stat-value"><?php echo $totalPaidFormatted; ?></div>
        </div>
        <div class="admin-stat-icon"><i class="fas fa-indian-rupee-sign"></i></div>
      </div>

      <div class="admin-stat-card" style="margin-bottom:0;">
        <div class="admin-stat-info">
          <h3>Current FY Total</h3>
          <div class="admin-stat-value" style="color:var(--maroon);"><?php echo $service->formatAmount($fyTotal); ?></div>
          <div style="font-size:11px; color:var(--text-light); margin-top:2px;"><?php echo $fyLabel; ?></div>
        </div>
        <div class="admin-stat-icon" style="background-color:rgba(123,30,30,0.1); color:var(--maroon);"><i class="fas fa-calendar-alt"></i></div>
      </div>
    </div>

    <!-- Active Subscription Detail -->
    <?php if ($activeSub): ?>
    <div class="admin-card" style="border-left:4px solid #2e7d32;">
      <div class="admin-card-header">
        <h2><i class="fas fa-sync" style="color:#2e7d32; margin-right:6px;"></i> Active Subscription</h2>
        <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $activeSub['id']; ?>" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:var(--maroon); color:white; border-radius:var(--radius-md); font-weight:600; font-size:12px; text-decoration:none;">
          <i class="fas fa-hand-holding-usd"></i> Record Payment
        </a>
      </div>
      <div class="admin-card-body">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:var(--space-lg); margin-bottom:var(--space-lg);">
          <div>
            <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Monthly Amount</div>
            <div style="font-size:18px; font-weight:700; color:var(--maroon);"><?php echo $service->formatAmount((float) ($activeSub['amount'] ?? 0)); ?></div>
          </div>
          <div>
            <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Installments</div>
            <div style="font-size:18px; font-weight:700;"><?php echo ($activeSub['installments_paid'] ?? 0); ?> / <?php echo ($activeSub['total_installments'] ?? 0) > 0 ? ($activeSub['total_installments'] ?? 0) : '∞'; ?></div>
          </div>
          <div>
            <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Next Installment</div>
            <div style="font-size:18px; font-weight:700;">#<?php echo $activeSub['next_installment'] ?? 1; ?></div>
          </div>
        </div>
        <div>
          <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
            <span>Progress</span>
            <span style="font-weight:600;"><?php echo $activeSub['progress'] ?? 0; ?>%</span>
          </div>
          <div style="height:10px; background:#eee; border-radius:5px; overflow:hidden;">
            <div style="height:100%; width:<?php echo $activeSub['progress'] ?? 0; ?>%; background:linear-gradient(90deg, #2e7d32, #43a047); border-radius:5px; transition:width 0.5s;"></div>
          </div>
          <?php if (!empty($activeSub['razorpay_subscription_id'])): ?>
            <div style="margin-top:8px; font-size:11px; color:var(--text-light);">
              Razorpay Sub: <span style="font-family:monospace;"><?php echo htmlspecialchars($activeSub['razorpay_subscription_id']); ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
</div>

<!-- Payment Schedule Grid (Full Width) -->
<?php if ($selectedSub): 
  $schedule = $service->buildInstallmentSchedule($selectedSub);
?>
<style>
.admin-inst-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
  gap: 10px;
  margin-top: var(--space-md);
}
.admin-inst-card {
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 12px 8px;
  text-align: center;
  background: var(--white);
  box-shadow: var(--shadow-sm);
  transition: all 0.2s ease;
}
.admin-inst-card.inst-paid {
  background: #e8f5e9;
  border-color: #a5d6a7;
}
.admin-inst-card.inst-due {
  background: #fff8e1;
  border-color: #ffd54f;
  animation: pulse-border 2s infinite;
}
.admin-inst-card.inst-upcoming {
  background: #fafafa;
  border-color: #eee;
  opacity: 0.75;
}
.admin-inst-month {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-light);
  margin-bottom: 4px;
}
.admin-inst-number {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-dark);
  margin-bottom: 6px;
}
.admin-inst-status {
  font-size: 10px;
  font-weight: 700;
}
.admin-inst-status.paid {
  color: #2e7d32;
}
.admin-inst-status.due {
  color: #b78103;
}
.admin-inst-status.upcoming {
  color: #777;
}
</style>

<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header" style="background:var(--cream);">
    <h2><i class="fas fa-calendar-alt" style="color:var(--primary);"></i> Payment Schedule (Subscription #<?php echo $selectedSub['id']; ?>)</h2>
    <span style="font-size:12px; color:var(--text-light); font-weight:600;">
      Status: <span class="badge <?php echo ($selectedSub['status'] === 'active') ? 'badge-success' : 'badge-secondary'; ?>" style="font-size: 10px; padding: 2px 6px; vertical-align: middle;"><?php echo htmlspecialchars(ucfirst($selectedSub['status'])); ?></span> &middot; Showing <?php echo count($schedule); ?> months
    </span>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <div class="admin-inst-grid">
      <?php foreach ($schedule as $inst): ?>
        <div class="admin-inst-card <?php echo $inst['is_paid'] ? 'inst-paid' : ($inst['is_next_unpaid'] && $selectedSub['status'] === 'active' ? 'inst-due' : 'inst-upcoming'); ?>">
          <div class="admin-inst-month"><?php echo $inst['month']; ?></div>
          <div class="admin-inst-number">#<?php echo $inst['number']; ?></div>
          <div class="admin-inst-status <?php echo $inst['is_paid'] ? 'paid' : ($inst['is_next_unpaid'] && $selectedSub['status'] === 'active' ? 'due' : 'upcoming'); ?>">
            <?php if ($inst['is_paid']): ?>
              ✓ Paid
            <?php elseif ($inst['is_next_unpaid'] && $selectedSub['status'] === 'active'): ?>
              <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $selectedSub['id']; ?>&installment_number=<?php echo $inst['number']; ?>" class="btn-record-pay" style="display:inline-block; font-size:10px; padding:2px 8px; border-radius:3px; background:var(--maroon); color:white; text-decoration:none; font-weight:700; margin-top:4px;" title="Record offline payment for this month">
                Record Pay
              </a>
            <?php else: ?>
              Pending
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h2>Subscriptions (<?php echo count($subscriptions); ?>)</h2>
    <?php if (!$activeSub && hasPermission('sudamaseva.edit')): 
      $maxCycle = 0;
      foreach ($subscriptions as $s) {
          if (isset($s['cycle']) && (int) $s['cycle'] > $maxCycle) {
              $maxCycle = (int) $s['cycle'];
          }
      }
      $nextCycle = $maxCycle + 1;
      $enrollUrl = "admin/sudamaseva-donor-add?phone=" . urlencode($donor['phone'] ?? '') 
        . "&donor_name=" . urlencode($donor['donor_name'] ?? '')
        . "&email=" . urlencode($donor['email'] ?? '')
        . "&pan=" . urlencode($donor['pan'] ?? '')
        . "&area=" . urlencode($donor['area'] ?? '')
        . "&city=" . urlencode($donor['city'] ?? '')
        . "&state=" . urlencode($donor['state'] ?? '')
        . "&cycle=" . $nextCycle;
    ?>
      <a href="<?php echo $enrollUrl; ?>" class="btn btn-primary btn-sm" style="text-decoration:none; padding:6px 14px; background:#0b5ed7; color:white; border-radius:var(--radius-md); font-weight:600; font-size:12px;">
        <i class="fas fa-plus"></i> Start New Cycle
      </a>
    <?php endif; ?>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Start Date</th>
            <th>Installments</th>
            <th>Progress</th>
            <th>Source</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($subscriptions)): ?>
            <tr><td colspan="8" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No subscriptions for this donor.</td></tr>
          <?php else: ?>
            <?php foreach ($subscriptions as $s):
              $progress = $service->calculateSubscriptionProgress($s);
              $totalInst = (int) ($s['total_installments'] ?? 0);
              $paidInst = (int) ($s['installments_paid'] ?? 0);
              $isSelected = $selectedSub && (int)$selectedSub['id'] === (int)$s['id'];
            ?>
              <tr <?php echo $isSelected ? 'style="background-color: #fffde6; font-weight: 500;"' : ''; ?>>
                <td style="font-family:monospace;">#<?php echo $s['id']; ?></td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($s['amount'] ?? 0)); ?></td>
                <td><?php echo $service->renderStatusBadge($s['status'] ?? 'unknown'); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo $service->formatDate($s['start_date'] ?? null, 'd M Y'); ?></td>
                <td style="text-align:center;"><?php echo $totalInst > 0 ? "{$paidInst} / {$totalInst}" : $paidInst; ?></td>
                <td style="min-width:100px;">
                  <?php if ($totalInst > 0): ?>
                    <div style="display:flex; align-items:center; gap:6px;">
                      <div style="flex:1; height:6px; background:#eee; border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:<?php echo $progress; ?>%; background:<?php echo $progress >= 100 ? '#2e7d32' : ($progress >= 50 ? 'var(--accent)' : 'var(--primary)'); ?>; border-radius:3px;"></div>
                      </div>
                      <span style="font-size:10px; color:var(--text-light); font-weight:600;"><?php echo $progress; ?>%</span>
                    </div>
                  <?php else: ?>
                    <span style="font-size:11px; color:var(--text-light); font-style:italic;">Open-ended</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-info"><?php echo htmlspecialchars($s['source'] ?? '—'); ?></span></td>
                <td>
                  <?php if ($isSelected): ?>
                    <span style="font-size:11px; font-weight:700; color:var(--primary);"><i class="fas fa-eye"></i> Selected</span>
                  <?php else: ?>
                    <a href="admin/sudamaseva-donor-detail?id=<?php echo $donorId; ?>&sub_id=<?php echo $s['id']; ?>" class="btn btn-outline-dark btn-xs" style="text-decoration:none; padding:4px 8px; font-size:11px; border:1px solid var(--border); border-radius:var(--radius-sm); font-weight:600;">
                      View Schedule
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Recent Payments -->
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header">
    <h2>Recent Payments (Last <?php echo count($recentPayments); ?>)</h2>
    <a href="admin/sudamaseva-payments?search=<?php echo urlencode($donor['phone'] ?? ''); ?>" style="font-size:12px; color:var(--primary); text-decoration:none; font-weight:600;">View All Payments</a>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Installment</th>
            <th>Status</th>
            <th>Receipt</th>
            <th>Payment ID</th>
            <?php if (hasPermission('sudamaseva.delete')): ?>
              <th style="text-align:center;">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentPayments)): ?>
            <tr><td colspan="<?php echo hasPermission('sudamaseva.delete') ? 7 : 6; ?>" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No payments recorded yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentPayments as $p): ?>
              <tr>
                <td style="font-size:12px; color:var(--text-light); white-space:nowrap;"><?php echo $p['date_formatted'] ?? $service->formatDate($p['payment_date'] ?? null); ?></td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $p['amount_formatted'] ?? $service->formatAmount((float) ($p['amount'] ?? 0)); ?></td>
                <td style="color:var(--text-light);">#<?php echo (int) ($p['installment_number'] ?? 0); ?></td>
                <td><?php echo $p['status_badge'] ?? $service->renderStatusBadge($p['payment_status'] ?? 'created', 'payment'); ?></td>
                <td style="font-family:monospace; font-size:11px;"><?php echo $service->formatReceiptNo($p['receipt_number'] ?? null); ?></td>
                <td style="font-size:10px; font-family:monospace; color:var(--text-light); max-width:100px; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($p['razorpay_payment_id'] ?? ''); ?>">
                  <?php echo htmlspecialchars($p['razorpay_payment_id'] ?: '—'); ?>
                </td>
                <?php if (hasPermission('sudamaseva.delete')): ?>
                  <td style="text-align:center;">
                    <a href="admin/sudamaseva-donor-detail?id=<?php echo $donorId; ?>&action=delete_payment&payment_id=<?php echo $p['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" 
                       class="btn-sm-action btn-delete" 
                       title="Delete Payment" 
                       onclick="return confirm('Are you sure you want to delete this payment record? This will also delete any generated tax receipts and update the subscription installment count if applicable.');" 
                       style="padding: 6px 8px; border-radius: 4px; background:#dc3545; color:white; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; text-decoration:none;">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
