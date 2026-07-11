<?php
/**
 * Sudamaseva Module — Donor Dashboard (Public)
 *
 * Shows a donor's payment history, active subscriptions, and an installment
 * schedule grid with Pay buttons for manual subscriptions.
 *
 * Access: ?donor_id=N (passed after successful lookup)
 * Future: This page will be behind DevoteeCare auth.
 */

use Isjm\Modules\Sudamaseva\SudamasevaRepository;
use Isjm\Modules\Sudamaseva\SudamasevaService;

require_once __DIR__ . '/../../Kernel/config.php';
require_once __DIR__ . '/../../Kernel/includes/bootstrap.php';

$pageTitle = 'Sudamaseva — My Seva Dashboard';
$metaDescription = 'View your Sudamaseva donation history and manage monthly contributions.';
$pageType = 'default';
include __DIR__ . '/../../Kernel/partials/header.php';

$donorId = isset($_GET['donor_id']) ? (int) $_GET['donor_id'] : 0;

if ($donorId <= 0) {
    // No donor specified — redirect to lookup
    header('Location: ' . BASE_URL . 'sudamaseva/lookup');
    exit;
}

$repo = new SudamasevaRepository();
$service = new SudamasevaService($repo);
$error = '';

try {
    $donor = $repo->getDonorById($donorId);
    if (!$donor) {
        echo '<section class="page-content"><div class="container">';
        echo '<div class="payment-result payment-failed">';
        echo '<div class="payment-result-icon failed"><i class="fas fa-times-circle"></i></div>';
        echo '<h1>Donor Not Found</h1>';
        echo '<p class="payment-result-subtitle">We could not find a donor with that ID. Please try searching again.</p>';
        echo '<div class="payment-result-actions">';
        echo '<a href="' . BASE_URL . 'sudamaseva/lookup" class="btn btn-primary"><i class="fas fa-search"></i> Try Again</a>';
        echo '</div></div></div></section>';
        include __DIR__ . '/../../Kernel/partials/footer.php';
        exit;
    }

    // Get all subscriptions with payments
    $subscriptions = $repo->getSubscriptionsByDonorWithMode($donorId);
    $allPayments = $repo->getPaymentsByDonor($donorId);

    // Total calculations
    $totalPaid = 0;
    $lastPaymentDate = null;
    $activeSubscriptions = [];
    $manualSubscriptions = [];

    foreach ($subscriptions as &$s) {
        $s['schedule'] = $service->buildInstallmentSchedule($s);
        $s['next_unpaid'] = $service->getNextUnpaidInstallment((int) $s['id']);
        $s['can_pay'] = in_array($s['collection_mode'] ?? '', ['manual', 'hybrid'], true) && $s['status'] === 'active' && $s['next_unpaid'] !== null;
    $s['is_offline'] = ($s['collection_mode'] ?? '') === 'offline';
    $s['is_hybrid'] = ($s['collection_mode'] ?? '') === 'hybrid';

        if ($s['status'] === 'active') {
            $activeSubscriptions[] = $s;
            if (in_array($s['collection_mode'] ?? '', ['manual', 'hybrid'], true)) {
                $manualSubscriptions[] = $s;
            }
        }
    }
    unset($s); // break the reference

    foreach ($allPayments as $p) {
        if (($p['payment_status'] ?? '') === 'paid') {
            $totalPaid += (int) ($p['amount'] ?? 0);
        }
    }

    if (!empty($allPayments)) {
        $lastPaymentDate = $allPayments[0]['payment_date'] ?? null;
    }

} catch (Exception $e) {
    $error = 'Failed to load dashboard: ' . $e->getMessage();
    $donor = null;
    $subscriptions = [];
    $allPayments = [];
    $activeSubscriptions = [];
    $manualSubscriptions = [];
    $totalPaid = 0;
    $lastPaymentDate = null;
}
?>

<!-- Page Header -->
<section class="page-header" style="padding-bottom:0;">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal" style="font-size:28px;">🙏 My Seva Dashboard</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>sudamaseva">Sudamaseva</a>
      <span>›</span>
      <span>My Dashboard</span>
    </div>
  </div>
</section>

<section class="page-content" style="padding-top:var(--space-lg);">
  <div class="container">

    <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['enrolled']) && $_GET['enrolled'] === 'offline'): ?>
      <div class="reveal" style="background:#fff8e1; border:2px solid #ffd54f; border-radius:var(--radius-lg); padding:var(--space-lg); margin-bottom:var(--space-xl); text-align:center;">
        <div style="font-size:36px; margin-bottom:var(--space-sm);">🙏</div>
        <h3 style="margin-bottom:var(--space-sm); color:var(--maroon);">Enrolled Successfully!</h3>
        <p style="color:var(--text); max-width:500px; margin:0 auto;">
          Thank you for enrolling in Sudamaseva. Your monthly offering has been noted. Please pay via bank transfer or cash each month using the bank details below. Our team will contact you to confirm.
        </p>
        <div style="margin-top:var(--space-md); font-size:13px; color:var(--text-light);">
          <i class="fas fa-phone"></i> Call us at <strong>+91 99860 77269</strong> for any assistance.
        </div>
      </div>
    <?php elseif (isset($_GET['payment']) && $_GET['payment'] === 'success'): ?>
      <div class="reveal" style="background:#e8f5e9; border:2px solid #a5d6a7; border-radius:var(--radius-lg); padding:var(--space-lg); margin-bottom:var(--space-xl); text-align:center;">
        <div style="font-size:36px; margin-bottom:var(--space-sm);">🎉</div>
        <h3 style="margin-bottom:var(--space-sm); color:#2e7d32;">Payment Successful!</h3>
        <p style="color:var(--text);">Your first month's offering has been received. Thank you for your support!</p>
      </div>
    <?php endif; ?>

    <?php if ($donor): ?>

    <!-- ============================================================ -->
    <!-- Donor Summary Card -->
    <!-- ============================================================ -->
    <div class="reveal" style="background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:var(--space-lg); margin-bottom:var(--space-xl); box-shadow:var(--shadow-sm);">
      <div style="display:flex; align-items:center; gap:var(--space-md); flex-wrap:wrap;">
        <div style="width:56px; height:56px; background:var(--cream); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; color:var(--primary);">
          <i class="fas fa-user"></i>
        </div>
        <div style="flex:1;">
          <h3 style="margin:0; font-size:20px;"><?php echo htmlspecialchars($donor['donor_name']); ?></h3>
          <div style="color:var(--text-light); font-size:13px;">
            <?php echo htmlspecialchars($service->formatPhoneMasked($donor['phone'] ?? '')); ?>
            <?php if (!empty($donor['email'])): ?>
               &middot; <?php echo htmlspecialchars($donor['email']); ?>
            <?php endif; ?>
            <?php if (!empty($donor['legacy_id_no'])): ?>
               &middot; Legacy ID: <?php echo htmlspecialchars($donor['legacy_id_no']); ?>
            <?php endif; ?>
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:12px; color:var(--text-light);">Total Contribution</div>
          <div style="font-size:24px; font-weight:700; color:var(--maroon);"><?php echo $service->formatAmount($totalPaid); ?></div>
        </div>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="reveal" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:var(--space-md); margin-bottom:var(--space-xl);">
      <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); text-align:center; box-shadow:var(--shadow-sm);">
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Plans</div>
        <div style="font-size:22px; font-weight:700;"><?php echo count($subscriptions); ?></div>
      </div>
      <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); text-align:center; box-shadow:var(--shadow-sm);">
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Active</div>
        <div style="font-size:22px; font-weight:700; color:green;"><?php echo count($activeSubscriptions); ?></div>
      </div>
      <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); text-align:center; box-shadow:var(--shadow-sm);">
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Payments</div>
        <div style="font-size:22px; font-weight:700;"><?php echo count($allPayments); ?></div>
      </div>
      <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); text-align:center; box-shadow:var(--shadow-sm);">
        <div style="font-size:11px; color:var(--text-light); text-transform:uppercase; font-weight:600;">Last Payment</div>
        <div style="font-size:14px; font-weight:600;"><?php echo $lastPaymentDate ? $service->formatDate($lastPaymentDate, 'd M Y') : '—'; ?></div>
      </div>
    </div>

    <?php
      // Renewal: Show banner only when NO active subscriptions remain
      // and at least one completed subscription was fully paid.
      $completedForRenewal = false;
      $hasActive = false;
      foreach ($subscriptions as $sub) {
          if ($sub['status'] === 'active') {
              $hasActive = true;
          }
      }
      if (!$hasActive) {
          foreach ($subscriptions as $sub) {
              $totalInst = (int) ($sub['total_installments'] ?? 0);
              $paidInst = (int) ($sub['installments_paid'] ?? 0);
              if ($sub['status'] === 'completed' && $totalInst > 0 && $paidInst >= $totalInst) {
                  $completedForRenewal = true;
                  break;
              }
          }
      }
    ?>

    <?php if ($completedForRenewal): ?>
      <div class="reveal" style="background:linear-gradient(135deg, #e8f5e9, #c8e6c9); border:2px solid #66bb6a; border-radius:var(--radius-lg); padding:var(--space-lg); margin-bottom:var(--space-xl); text-align:center;">
        <div style="font-size:40px; margin-bottom:var(--space-sm);">🎉</div>
        <h3 style="margin-bottom:var(--space-sm); color:#2e7d32;">Your Sudamaseva Plan is Complete!</h3>
        <p style="color:var(--text); max-width:500px; margin:0 auto var(--space-md);">
          You have successfully completed your seva cycle. Thank you for your devotion and support!
          Would you like to continue your seva for another term?
        </p>
        <a href="<?php echo BASE_URL; ?>sudamaseva?renew=<?php echo $donorId; ?>" class="btn btn-primary" style="background-color:#2e7d32; color:white; border:none; padding:12px 36px; border-radius:var(--radius-md); font-weight:700; font-size:15px; text-decoration:none; display:inline-block;">
          <i class="fas fa-sync-alt"></i> Renew Now — Start a New Cycle
        </a>
      </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- My Plans / Subscriptions -->
    <!-- ============================================================ -->
    <?php if (empty($subscriptions)): ?>
      <div class="reveal" style="text-align:center; padding:var(--space-3xl); background:var(--cream); border-radius:var(--radius-lg); margin-bottom:var(--space-xl);">
        <div style="font-size:40px; margin-bottom:var(--space-md);">📋</div>
        <h3>No Seva Plans Yet</h3>
        <p style="color:var(--text-light);">You haven't enrolled in a Sudamaseva plan yet. Start your monthly seva today.</p>
        <a href="<?php echo BASE_URL; ?>sudamaseva" class="btn btn-primary" style="margin-top:var(--space-md);">
          <i class="fas fa-sync-alt"></i> Start Seva
        </a>
      </div>
    <?php else: ?>
      <?php foreach ($subscriptions as $sub): ?>
        <?php
          $subId = (int) $sub['id'];
          $collectionMode = $sub['collection_mode'] ?? 'recurring';
          $totalInst = (int) ($sub['total_installments'] ?? 0);
          $paidInst = (int) ($sub['installments_paid'] ?? 0);
          $progress = $service->calculateSubscriptionProgress($sub);
          $nextUnpaid = $service->getNextUnpaidInstallment($subId);
          $canPay = in_array($collectionMode, ['manual', 'hybrid'], true) && $sub['status'] === 'active' && $nextUnpaid !== null;
          $isOffline = $collectionMode === 'offline';
          $isHybrid = $collectionMode === 'hybrid';
        ?>

        <!-- Subscription Card -->
        <div class="reveal" style="background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:var(--space-lg); overflow:hidden; box-shadow:var(--shadow-sm);">
          <!-- Header -->
          <div style="padding:var(--space-md) var(--space-lg); background:<?php echo $sub['status'] === 'active' ? 'var(--cream)' : '#f5f5f5'; ?>; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--space-sm);">
            <div>
              <strong style="font-size:16px;">
                <?php echo $service->formatAmount((float) ($sub['amount'] ?? 0)); ?>/month
              </strong>
              <span style="margin-left:8px;"><?php echo $service->renderStatusBadge($sub['status']); ?></span>
              <span class="badge badge-info" style="margin-left:4px;"><?php echo $service->getCollectionModeLabel($collectionMode); ?></span>
            </div>
            <div style="font-size:13px; color:var(--text-light);">
              Started <?php echo $service->formatDate($sub['start_date'] ?? $sub['created_at'] ?? null, 'd M Y'); ?>
              <?php if ($totalInst > 0): ?>
                &middot; <?php echo $paidInst; ?>/<?php echo $totalInst; ?> paid
              <?php endif; ?>
            </div>
          </div>

          <div style="padding:var(--space-lg);">

            <!-- Progress Bar -->
            <?php if ($totalInst > 0): ?>
            <div style="margin-bottom:var(--space-lg);">
              <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                <span>Progress</span>
                <span style="font-weight:600;"><?php echo $progress; ?>%</span>
              </div>
              <div style="height:8px; background:#eee; border-radius:4px; overflow:hidden;">
                <div style="height:100%; width:<?php echo $progress; ?>%; background:linear-gradient(90deg, var(--primary), var(--accent)); border-radius:4px; transition:width 0.5s;"></div>
              </div>
            </div>
            <?php endif; ?>

            <!-- Installment Grid -->
            <h4 style="font-size:14px; margin-bottom:var(--space-md); color:var(--text-light);">
              <i class="fas fa-calendar-alt"></i>
              Payment Schedule
              <?php if ($totalInst > 0): ?>
                (<?php echo $totalInst; ?> months)
              <?php else: ?>
                (Open-ended)
              <?php endif; ?>
            </h4>

            <div class="installment-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:8px;">
              <?php $schedule = $sub['schedule'] ?? []; ?>
              <?php foreach ($schedule as $inst): ?>
                <div class="inst-card <?php
                  echo $inst['is_paid'] ? 'inst-paid' : ($inst['is_next_unpaid'] && $canPay ? 'inst-due' : 'inst-upcoming');
                ?>" data-inst="<?php echo $inst['number']; ?>">
                  <div class="inst-month"><?php echo $inst['month']; ?></div>
                  <div class="inst-number">#<?php echo $inst['number']; ?></div>
                  <div class="inst-status">
                    <?php if ($inst['is_paid']): ?>
                      <span class="inst-paid-badge">✓ Paid</span>
                    <?php elseif ($inst['is_next_unpaid'] && $canPay): ?>
                      <button class="pay-inst-btn" data-sub-id="<?php echo $subId; ?>" data-inst="<?php echo $inst['number']; ?>" data-amount="<?php echo ($sub['amount'] ?? 0) * 100; ?>">
                        Pay Now
                      </button>
                    <?php elseif ($inst['is_next_unpaid'] && $isOffline): ?>
                      <span class="inst-pending" style="color:var(--primary-dark); font-weight:600;">Pay via Bank</span>
                    <?php elseif ($inst['is_next_unpaid'] && $isHybrid): ?>
                      <span class="inst-pending" style="color:var(--primary-dark); font-weight:600;">Pay Online or Bank</span>
                    <?php else: ?>
                      <span class="inst-pending">Pending</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Offline/Hybrid Payment Info -->
            <?php if ($isOffline || $isHybrid): ?>
              <div style="margin-top:var(--space-lg); padding:var(--space-md); background:#f9f6f0; border:1px solid #e8dcc8; border-radius:var(--radius-md);">
                <h4 style="font-size:14px; margin-bottom:var(--space-sm); color:var(--maroon);">
                  <i class="fas fa-university"></i>
                  <?php echo $isOffline ? 'Pay via Bank Transfer' : 'Also Pay via Bank Transfer'; ?>
                </h4>
                <div style="font-size:13px; line-height:1.8;">
                  <div><strong>Account Name:</strong> <?php echo htmlspecialchars($BANK_DETAILS['account_name'] ?? ''); ?></div>
                  <div><strong>Account No.:</strong> <span style="font-family:monospace;font-weight:600;"><?php echo htmlspecialchars($BANK_DETAILS['account_number'] ?? ''); ?></span></div>
                  <div><strong>IFSC Code:</strong> <span style="font-family:monospace;font-weight:600;"><?php echo htmlspecialchars($BANK_DETAILS['ifsc_code'] ?? ''); ?></span></div>
                  <div><strong>Bank:</strong> <?php echo htmlspecialchars($BANK_DETAILS['bank_name'] ?? ''); ?>, <?php echo htmlspecialchars($BANK_DETAILS['branch'] ?? ''); ?></div>
                </div>
                <div style="margin-top:var(--space-sm); padding:var(--space-sm); background:#fff8e1; border-radius:var(--radius-sm); font-size:12px; color:#856404;">
                  <i class="fas fa-info-circle"></i>
                  After making a transfer, email the transaction details to <strong>seva@iskconseshadripuram.org</strong> or call <strong>+91 99860 77269</strong>.
                </div>
              </div>
            <?php endif; ?>

            <!-- Subscriptions with Razorpay ID -->
            <?php if (!empty($sub['razorpay_subscription_id'])): ?>
              <div style="margin-top:var(--space-md); font-size:11px; color:var(--text-light);">
                <i class="fas fa-sync-alt"></i> Recurring Subscription ID: <span style="font-family:monospace;"><?php echo htmlspecialchars($sub['razorpay_subscription_id']); ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- All Payment History -->
    <!-- ============================================================ -->
    <div class="reveal" style="background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:var(--space-xl); box-shadow:var(--shadow-sm);">
      <div style="padding:var(--space-md) var(--space-lg); background:var(--cream); border-bottom:1px solid var(--border);">
        <h3 style="font-size:16px; margin:0;"><i class="fas fa-list"></i> Payment History</h3>
      </div>
      <?php if (empty($allPayments)): ?>
        <div style="padding:var(--space-xl); text-align:center; color:var(--text-light);">
          <p>No payments recorded yet.</p>
        </div>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
              <tr style="background:#fafafa; border-bottom:2px solid var(--border);">
                <th style="padding:10px 14px; text-align:left;">Date</th>
                <th style="padding:10px 14px; text-align:left;">Amount</th>
                <th style="padding:10px 14px; text-align:center;">Inst.#</th>
                <th style="padding:10px 14px; text-align:left;">Source</th>
                <th style="padding:10px 14px; text-align:left;">Status</th>
                <th style="padding:10px 14px; text-align:left;">Receipt</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allPayments as $p): ?>
                <tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:10px 14px; white-space:nowrap; color:var(--text-light);"><?php echo $service->formatDate($p['payment_date'] ?? null, 'd M Y'); ?></td>
                  <td style="padding:10px 14px; font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($p['amount'] ?? 0)); ?></td>
                  <td style="padding:10px 14px; text-align:center;">#<?php echo (int) ($p['installment_number'] ?? 0); ?></td>
                  <td style="padding:10px 14px; font-size:12px;"><?php echo $service->getPaymentSourceLabel($p['payment_source'] ?? null); ?></td>
                  <td style="padding:10px 14px;"><?php echo $service->renderStatusBadge($p['payment_status'] ?? 'created', 'payment'); ?></td>
                  <td style="padding:10px 14px; font-family:monospace; font-size:11px;"><?php echo $service->formatReceiptNo($p['receipt_number'] ?? null); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============================================================ -->
    <!-- Actions -->
    <!-- ============================================================ -->
    <div class="reveal" style="display:flex; gap:var(--space-md); flex-wrap:wrap; justify-content:center; margin-bottom:var(--space-2xl);">
      <a href="<?php echo BASE_URL; ?>sudamaseva" class="btn btn-primary">
        <i class="fas fa-sync-alt"></i> Start New Seva
      </a>
      <a href="<?php echo BASE_URL; ?>sudamaseva/lookup" class="btn btn-outline-dark">
        <i class="fas fa-search"></i> Find Another Donor
      </a>
      <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-dark">
        <i class="fas fa-envelope"></i> Need Help?
      </a>
    </div>

    <!-- Blessing -->
    <div class="payment-blessing reveal" style="margin-bottom:var(--space-2xl);">
      <p>🌺 "Whatever you do, whatever you eat, whatever you offer or give away, and whatever austerities you perform — do that, O son of Kunti, as an offering to Me."</p>
      <span>— Bhagavad Gita 9.27</span>
    </div>

    <?php endif; ?>
  </div>
</section>

<!-- Razorpay Checkout -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // ================================================================
  // Manual Installment Payment via Razorpay Orders
  // ================================================================
  document.querySelectorAll('.pay-inst-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var subId = parseInt(this.getAttribute('data-sub-id'));
      var inst = parseInt(this.getAttribute('data-inst'));
      var amount = parseInt(this.getAttribute('data-amount')); // in paise

      // Disable all pay buttons to prevent double clicks
      document.querySelectorAll('.pay-inst-btn').forEach(function(b) {
        b.disabled = true;
        b.textContent = 'Processing...';
      });

      // Step 1: Create Razorpay Order
      fetch('<?php echo BASE_URL; ?>api/sudamaseva/create-order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          subscription_id: subId,
          installment_number: inst
        })
      })
      .then(function(res) { return res.json(); })
      .then(function(orderData) {
        if (orderData.error) {
          alert(orderData.error);
          resetPayButtons();
          return;
        }

        // Step 2: Open Razorpay Checkout
        var options = {
          key: '<?php echo RAZORPAY_KEY_ID; ?>',
          order_id: orderData.order_id,
          name: 'ISKCON Palace Temple',
          description: 'Sudamaseva Installment #' + inst + ' — ₹' + (amount / 100).toLocaleString('en-IN'),
          image: '<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg',
          currency: '<?php echo CURRENCY; ?>',
          handler: function(response) {
            // Step 3: Verify payment
            verifyPayment(subId, inst, amount, response);
          },
          modal: {
            ondismiss: function() {
              resetPayButtons();
            }
          }
        };

        var rzp = new Razorpay(options);
        rzp.open();
      })
      .catch(function(err) {
        alert('Failed to create payment. Please try again.');
        console.error('Create order error:', err);
        resetPayButtons();
      });
    });
  });

  function verifyPayment(subId, inst, amount, response) {
    var btn = document.querySelector('.pay-inst-btn[data-sub-id="' + subId + '"][data-inst="' + inst + '"]');
    if (btn) btn.textContent = 'Verifying...';

    fetch('<?php echo BASE_URL; ?>api/sudamaseva/verify-order', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        razorpay_order_id: response.razorpay_order_id,
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_signature: response.razorpay_signature,
        subscription_id: subId,
        installment_number: inst,
        amount: amount
      })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.success) {
        // Success — reload to show updated status
        window.location.reload();
      } else {
        alert('Payment verification failed: ' + (data.error || 'Unknown error'));
        resetPayButtons();
      }
    })
    .catch(function(err) {
      alert('Payment verification failed. Please contact support.');
      console.error('Verify payment error:', err);
      resetPayButtons();
    });
  }

  function resetPayButtons() {
    document.querySelectorAll('.pay-inst-btn').forEach(function(b) {
      b.disabled = false;
      b.textContent = 'Pay Now';
    });
  }
});
</script>

<style>
.installment-grid {
  margin-top: var(--space-sm);
}

.inst-card {
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 10px;
  text-align: center;
  transition: all 0.2s ease;
  background: var(--white);
}

.inst-card.inst-paid {
  background: #e8f5e9;
  border-color: #a5d6a7;
}

.inst-card.inst-due {
  background: #fff8e1;
  border-color: #ffd54f;
  animation: pulse-border 2s infinite;
}

.inst-card.inst-upcoming {
  background: #fafafa;
  border-color: #eee;
  opacity: 0.7;
}

.inst-month {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-light);
  margin-bottom: 2px;
}

.inst-number {
  font-size: 18px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 4px;
}

.inst-status {
  font-size: 11px;
}

.inst-paid-badge {
  color: #2e7d32;
  font-weight: 600;
}

.inst-pending {
  color: var(--text-light);
}

.pay-inst-btn {
  background: var(--primary);
  color: white;
  border: none;
  padding: 4px 12px;
  border-radius: var(--radius-sm);
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.pay-inst-btn:hover {
  background: var(--primary-dark);
}

.pay-inst-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

@keyframes pulse-border {
  0%, 100% { border-color: #ffd54f; }
  50% { border-color: #ffb300; }
}
</style>

<?php include __DIR__ . '/../../Kernel/partials/footer.php'; ?>
