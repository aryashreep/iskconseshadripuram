<?php
/**
 * Sudamaseva Module — Record Offline Payment (Admin)
 *
 * Admin form to record a payment received via cash, cheque, or bank transfer
 * for a specific subscription installment.
 *
 * Access: ?subscription_id=N (pre-selects the subscription)
 */

require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.edit');

$pageTitle = 'Record Offline Payment';
$activePage = 'sudamaseva-subscriptions';
include 'partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaRepository;
use Isjm\Modules\Sudamaseva\SudamasevaService;

$repo = new SudamasevaRepository();
$service = new SudamasevaService($repo);
$error = '';
$success = '';

$subscriptionId = isset($_GET['subscription_id']) ? (int) $_GET['subscription_id'] : 0;
$subscription = null;
$donor = null;
$nextUnpaid = null;
$paidInstallments = [];

if ($subscriptionId > 0) {
    $subscription = $repo->getSubscriptionById($subscriptionId);
    if ($subscription) {
        $donor = $repo->getDonorById((int) $subscription['donor_id']);
        $nextUnpaid = $service->getNextUnpaidInstallment($subscriptionId);
        $paidInstallments = $repo->getPaidInstallmentNumbers($subscriptionId);

        // Calculate installment months based on subscription start_date
        $installmentMonths = [];
        $startDate = $subscription['start_date'] ?? null;
        if ($startDate) {
            try {
                $baseDate = new DateTime($startDate);
                $baseDate->modify('first day of this month');
                $maxInst = max(
                    (int) ($subscription['total_installments'] ?? 1),
                    $nextUnpaid ?? 1,
                    !empty($paidInstallments) ? max($paidInstallments) : 1
                );
                for ($i = 1; $i <= $maxInst; $i++) {
                    $instDate = clone $baseDate;
                    $instDate->modify('+' . ($i - 1) . ' months');
                    $installmentMonths[$i] = $instDate->format('M Y');
                }
            } catch (Exception $e) {
                // If date parsing fails, installmentMonths stays empty
            }
        }
    }
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-hand-holding-usd" style="margin-right:8px;"></i> Record Offline Payment</h1>
    <p>Record a payment received via cash, cheque, or bank transfer for a subscription installment.</p>
  </div>
  <div class="admin-page-actions">
    <?php if ($donor): ?>
      <a href="admin/sudamaseva-dashboard?donor_id=<?php echo (int) $donor['id']; ?>" class="btn btn-success btn-sm" style="text-decoration:none; padding:8px 16px; background:#2e7d32; color:white; border-radius:var(--radius-md); font-weight:600; font-size:12px;">
        <i class="fas fa-user"></i> Back to Donor Dashboard
      </a>
    <?php endif; ?>
    <a href="admin/sudamaseva-subscriptions" class="btn btn-outline-dark btn-sm" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">
      <i class="fas fa-arrow-left"></i> Back to Subscriptions
    </a>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success" style="background:#d4edda; border:1px solid #c3e6cb; padding:var(--space-md); border-radius:var(--radius-md); margin-bottom:var(--space-lg); display:flex; align-items:center; gap:var(--space-sm);">
    <i class="fas fa-check-circle" style="color:#155724; font-size:18px;"></i>
    <span style="color:#155724; font-weight:500;"><?php echo htmlspecialchars($success); ?></span>
  </div>
<?php endif; ?>

<!-- Step 1: Select Subscription (if not pre-selected) -->
<?php if (!$subscription): ?>
<div class="admin-card" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header">
    <h2>Step 1: Find Subscription</h2>
  </div>
  <div class="admin-card-body">
    <form action="admin/sudamaseva-record-payment" method="GET" style="display:flex; gap:var(--space-md); align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0; min-width:250px;">
        <label for="subscription_id">Subscription ID</label>
        <input type="number" id="subscription_id" name="subscription_id" class="form-control" placeholder="e.g. 317" required min="1">
      </div>
      <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">
        <i class="fas fa-search"></i> Look Up
      </button>
    </form>
    <div style="margin-top:var(--space-md); font-size:13px; color:var(--text-light);">
      <p>You can also find the subscription ID from the 
        <a href="admin/sudamaseva-subscriptions" style="color:var(--primary);">Subscriptions list</a> or 
        <a href="admin/sudamaseva-donors" style="color:var(--primary);">Donors list</a>.
      </p>
    </div>
  </div>
</div>

<?php else: ?>
  <!-- Step 2: Subscription Info + Record Payment Form -->
  <div class="admin-card" style="margin-bottom:var(--space-xl); border-left:4px solid var(--primary);">
    <div class="admin-card-header">
      <h2><i class="fas fa-receipt"></i> Subscription #<?php echo $subscription['id']; ?></h2>
    </div>
    <div class="admin-card-body">
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:var(--space-lg);">
        <div>
          <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Donor</div>
          <div style="font-size:16px; font-weight:600;"><?php echo htmlspecialchars($donor['donor_name'] ?? $subscription['donor_name'] ?? '—'); ?></div>
          <div style="font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($donor['phone'] ?? $subscription['phone'] ?? ''); ?></div>
        </div>
        <div>
          <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Monthly Amount</div>
          <div style="font-size:20px; font-weight:700; color:var(--maroon);"><?php echo $service->formatAmount((float) ($subscription['amount'] ?? 0)); ?></div>
        </div>
        <div>
          <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Collection Mode</div>
          <div><span class="badge badge-info"><?php echo $service->getCollectionModeLabel($subscription['collection_mode'] ?? 'recurring'); ?></span></div>
        </div>
        <div>
          <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Installments</div>
          <div style="font-size:16px; font-weight:600;">
            <?php echo (int) ($subscription['installments_paid'] ?? 0); ?> / <?php echo ($subscription['total_installments'] ?? 0) > 0 ? $subscription['total_installments'] : '∞'; ?> paid
          </div>
          <div style="font-size:12px; color:var(--text-light);">
            Next unpaid: #<?php echo $nextUnpaid ?? 'All paid'; ?>
            <?php if ($nextUnpaid && isset($installmentMonths[$nextUnpaid])): ?>
              <strong style="color:var(--maroon);"> — <?php echo $installmentMonths[$nextUnpaid]; ?></strong>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Record Payment Form -->
  <div class="admin-card" style="margin-bottom:var(--space-xl);">
    <div class="admin-card-header">
      <h2><i class="fas fa-pen"></i> Record Payment</h2>
    </div>
    <div class="admin-card-body">
      <form id="recordPaymentForm" style="max-width:600px;">
        <input type="hidden" id="subscriptionId" value="<?php echo $subscription['id']; ?>">

        <div class="form-group">
          <label for="installmentNumber">Installment Number *</label>
          <div style="display:flex; align-items:center; gap:8px;">
            <input type="number" id="installmentNumber" class="form-control" style="width:120px;"
                   value="<?php echo $nextUnpaid ?? 1; ?>" min="1"
                   max="<?php echo max((int) ($subscription['total_installments'] ?? 999), 999); ?>" required>
            <?php if ($nextUnpaid && isset($installmentMonths[$nextUnpaid])): ?>
              <span id="installmentMonth" style="font-size:15px; font-weight:700; color:var(--maroon); background:#fef3e9; padding:6px 14px; border-radius:var(--radius-md);">
                <?php echo $installmentMonths[$nextUnpaid]; ?>
              </span>
            <?php endif; ?>
          </div>
          <small style="color:var(--text-light); font-size:11px;">
            <span id="recordingMonthLabel">
              <?php if ($nextUnpaid && isset($installmentMonths[$nextUnpaid])): ?>
                Recording payment for <strong id="recordingMonth"><?php echo $installmentMonths[$nextUnpaid]; ?></strong>.
              <?php endif; ?>
            </span>
            Paid installments:
            <?php if (empty($paidInstallments)): ?>
              None
            <?php else:
              $paidLabels = [];
              foreach ($paidInstallments as $pi) {
                  $label = '#' . $pi;
                  if (isset($installmentMonths[$pi])) {
                      $label .= ' (' . $installmentMonths[$pi] . ')';
                  }
                  $paidLabels[] = $label;
              }
              echo implode(', ', $paidLabels);
            endif; ?>
          </small>
        </div>

        <div class="form-group">
          <label for="amount">Amount (₹)<span id="amountForLabel"> for <?php echo $nextUnpaid && isset($installmentMonths[$nextUnpaid]) ? $installmentMonths[$nextUnpaid] : ''; ?></span> *</label>
          <input type="number" id="amount" class="form-control"
                 value="<?php echo (int) ($subscription['amount'] ?? 0); ?>"
                 min="1" step="1" required>
        </div>

        <div class="form-group">
          <label for="paymentMethod">Payment Method *</label>
          <select id="paymentMethod" class="form-control" required>
            <option value="cash">Cash</option>
            <option value="cheque">Cheque</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label for="referenceNo">Reference Number <span style="color:var(--text-light);font-weight:400;font-size:11px;">(cheque no., transaction ID, etc.)</span></label>
          <input type="text" id="referenceNo" class="form-control" placeholder="e.g. CHQ-123456">
        </div>

        <div class="form-group">
          <label for="notes">Notes <span style="color:var(--text-light);font-weight:400;font-size:11px;">(optional)</span></label>
          <textarea id="notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" id="savePaymentBtn" style="background-color:var(--maroon); color:white; border:none; padding:12px 32px; border-radius:var(--radius-md); font-weight:700; font-size:14px; cursor:pointer; margin-top:var(--space-md);">
          <i class="fas fa-check-circle"></i> Record Payment
        </button>
      </form>

      <div id="paymentResult" style="display:none; margin-top:var(--space-lg); padding:var(--space-lg); background:#e8f5e9; border:1px solid #a5d6a7; border-radius:var(--radius-md);">
        <div style="display:flex; align-items:center; gap:var(--space-sm);">
          <i class="fas fa-check-circle" style="font-size:28px; color:#2e7d32;"></i>
          <div>
            <h4 style="margin:0; color:#2e7d32;" id="resultTitle">Payment Recorded!</h4>
            <p style="margin:4px 0 0; font-size:13px; color:var(--text);" id="resultMessage"></p>
          </div>
        </div>
        <div style="margin-top:var(--space-md); display:flex; gap:var(--space-sm); flex-wrap:wrap;">
          <button onclick="resetForm()" class="btn btn-outline-dark" style="padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); cursor:pointer; font-size:13px;">
            <i class="fas fa-plus"></i> Record Another
          </button>
          <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $subscription['id']; ?>" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); font-size:13px;">
            <i class="fas fa-redo"></i> Refresh
          </a>
          <a href="admin/sudamaseva-dashboard?donor_id=<?php echo (int) ($donor['id'] ?? $subscription['donor_id']); ?>&payment_recorded=1" class="btn btn-success" style="text-decoration:none; padding:8px 16px; background:#2e7d32; color:white; border-radius:var(--radius-md); font-weight:600; font-size:13px;">
            <i class="fas fa-user"></i> Back to Donor Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>
// Installment months lookup for client-side updates
var installmentMonths = <?php echo !empty($installmentMonths) ? json_encode($installmentMonths) : '{}'; ?>;

document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('recordPaymentForm');
  if (!form) return;

  // Update month label when installment number changes
  var instInput = document.getElementById('installmentNumber');
  if (instInput) {
    instInput.addEventListener('input', function() {
      updateInstallmentMonth(parseInt(this.value) || 0);
    });
    instInput.addEventListener('change', function() {
      updateInstallmentMonth(parseInt(this.value) || 0);
    });
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    var btn = document.getElementById('savePaymentBtn');
    var resultEl = document.getElementById('paymentResult');
    var resultMsg = document.getElementById('resultMessage');

    // Disable button
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    var payload = {
      subscription_id: parseInt(document.getElementById('subscriptionId').value),
      installment_number: parseInt(document.getElementById('installmentNumber').value),
      amount: parseInt(document.getElementById('amount').value),
      payment_method: document.getElementById('paymentMethod').value,
      reference_no: document.getElementById('referenceNo').value.trim(),
      notes: document.getElementById('notes').value.trim(),
    };

    fetch('<?php echo BASE_URL; ?>api/sudamaseva/record-offline-payment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.error) {
        alert('Error: ' + data.error + (data.details ? ' — ' + data.details : ''));
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Record Payment';
        return;
      }
      // Success
      resultMsg.textContent = data.message || 'Payment recorded successfully.';
      resultEl.style.display = 'block';
      form.style.display = 'none';
    })
    .catch(function(err) {
      alert('Failed to record payment. Please try again.');
      console.error('Record payment error:', err);
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-check-circle"></i> Record Payment';
    });
  });
});

function resetForm() {
  var form = document.getElementById('recordPaymentForm');
  var resultEl = document.getElementById('paymentResult');
  var btn = document.getElementById('savePaymentBtn');
  if (form) form.style.display = 'block';
  if (resultEl) resultEl.style.display = 'none';
  if (btn) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check-circle"></i> Record Payment';
  }
  // Increment installment number for convenience and update month label
  var instInput = document.getElementById('installmentNumber');
  if (instInput) {
    var newVal = parseInt(instInput.value) + 1;
    instInput.value = newVal;
  }
  document.getElementById('referenceNo').value = '';
  document.getElementById('notes').value = '';
  updateInstallmentMonth(instInput ? parseInt(instInput.value) : 0);
}

/** Update the month label and help text when installment number changes */
function updateInstallmentMonth(instNum) {
  var badge = document.getElementById('installmentMonth');
  var recLabel = document.getElementById('recordingMonthLabel');
  var recMonth = document.getElementById('recordingMonth');
  var month = installmentMonths[instNum];
  if (month) {
    if (badge) { badge.textContent = month; badge.style.display = 'inline'; }
    if (recLabel) { recLabel.style.display = 'inline'; }
    if (recMonth) { recMonth.textContent = month; }
    var amtLabel = document.getElementById('amountForLabel');
    if (amtLabel) amtLabel.textContent = 'for ' + month;
  } else {
    if (badge) badge.style.display = 'none';
    if (recLabel) recLabel.style.display = 'none';
    var amtLabel = document.getElementById('amountForLabel');
    if (amtLabel) amtLabel.textContent = '';
  }
}
</script>

<?php include 'partials/footer.php'; ?>
