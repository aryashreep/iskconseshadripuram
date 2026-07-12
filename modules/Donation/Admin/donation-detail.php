<?php
/**
 * Donation Module — Transaction Detail View (Admin)
 *
 * Shows all details for a single donation_transaction, including:
 * - Donor contact info and PAN
 * - Cause/festival and seva details
 * - Razorpay payment identifiers
 * - Linked puja booking (if any)
 * - Linked subscription (if any)
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('donations.view');

use Isjm\Modules\Donation\DonationRepository;

$repo = new DonationRepository();
$error = '';

$transactionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$returnUrl = trim($_GET['return'] ?? 'admin/donations');

// Security: only allow internal admin URLs for return
if (!str_starts_with($returnUrl, 'admin/') && !str_starts_with($returnUrl, '/admin/')) {
    $returnUrl = 'admin/donations';
}

$pageTitle = 'Donation Transaction #' . $transactionId;
$activePage = 'donations';
include 'partials/header.php';

if ($transactionId <= 0) {
    echo '<div class="admin-page-header"><div class="admin-page-title"><h1>Invalid Request</h1></div></div>';
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> Transaction ID is required.</div>';
    echo '<a href="' . htmlspecialchars($returnUrl) . '" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">&larr; Back to Donation Logs</a>';
    include 'partials/footer.php';
    exit;
}

try {
    $txn = $repo->getTransactionById($transactionId);
} catch (Exception $e) {
    $txn = null;
    $error = 'Failed to load transaction: ' . $e->getMessage();
}

if (empty($txn)) {
    echo '<div class="admin-page-header"><div class="admin-page-title"><h1>Transaction Not Found</h1></div></div>';
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> Transaction #' . $transactionId . ' not found.</div>';
    echo '<a href="' . htmlspecialchars($returnUrl) . '" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md);">&larr; Back to Donation Logs</a>';
    include 'partials/footer.php';
    exit;
}

// Fetch optional linked records
$booking = null;
$subscription = null;
if (!empty($txn['subscription_id'])) {
    try {
        $subscription = $repo->getSubscriptionById((int) $txn['subscription_id']);
    } catch (Exception $e) {
        // Silently ignore — subscription is optional
        error_log('Failed to load subscription #' . $txn['subscription_id'] . ': ' . $e->getMessage());
    }
}
try {
    $booking = $repo->getPujaBookingByTransactionId($transactionId);
} catch (Exception $e) {
    // Silently ignore — booking is optional
}

// Helper for status badges
$badgeClass = 'badge-secondary';
$statusIcon = 'fa-circle';
if ($txn['payment_status'] === 'paid') {
    $badgeClass = 'badge-success';
    $statusIcon = 'fa-check-circle';
} elseif ($txn['payment_status'] === 'failed') {
    $badgeClass = 'badge-danger';
    $statusIcon = 'fa-times-circle';
} elseif ($txn['payment_status'] === 'attempted') {
    $badgeClass = 'badge-warning';
    $statusIcon = 'fa-clock';
} elseif ($txn['payment_status'] === 'refunded') {
    $badgeClass = 'badge-info';
    $statusIcon = 'fa-undo';
}
?>
<style>
  .txn-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--space-lg);
  }
  .txn-detail-section {
    margin-bottom: var(--space-md);
  }
  .txn-detail-section.full-width {
    grid-column: 1 / -1;
  }
  .txn-detail-label {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--text-light);
    margin-bottom: 4px;
    letter-spacing: 0.3px;
  }
  .txn-detail-label i { margin-right: 4px; }
  .txn-detail-value {
    font-size: 15px;
    font-weight: 500;
    color: var(--text);
  }
  .txn-detail-value.mono {
    font-family: 'Cascadia Code', 'Fira Code', 'Consolas', monospace;
    font-size: 13px;
  }
  .txn-detail-value.large { font-size: 20px; }
  .txn-detail-sub {
    font-size: 12px;
    color: var(--text-light);
    margin-top: 2px;
  }
  .txn-detail-notes {
    font-size: 14px;
    font-style: italic;
    background: var(--cream);
    padding: 12px;
    border-radius: var(--radius-sm);
    line-height: 1.5;
    color: var(--text);
  }
  .txn-status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 6px;
  }
  .txn-status-dot.paid { background: #2e7d32; }
  .txn-status-dot.failed { background: #c62828; }
  .txn-status-dot.attempted { background: #f57c00; }
  .txn-status-dot.refunded { background: #1565c0; }
  .txn-status-dot.created { background: #888; }
  .txn-linked-card {
    border-left: 4px solid var(--accent);
  }
  .txn-linked-card.booking { border-left-color: var(--accent); }
  .txn-linked-card.subscription { border-left-color: #1565c0; }
  .admin-stat-card-sm {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    box-shadow: var(--shadow-sm);
  }
</style>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>
      <i class="fas fa-receipt" style="color:var(--maroon);"></i>
      Donation Transaction <span style="color:var(--text-light); font-weight:400;">#<?php echo $transactionId; ?></span>
    </h1>
    <p>
      <span class="badge <?php echo $badgeClass; ?>"><i class="fas <?php echo $statusIcon; ?>" style="margin-right:3px;"></i> <?php echo ucfirst($txn['payment_status']); ?></span>
      &middot; <?php echo date('M d, Y H:i:s', strtotime($txn['created_at'])); ?>
      &middot; <span style="font-family:monospace; font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($txn['razorpay_order_id'] ?: '—'); ?></span>
    </p>
  </div>
  <div class="admin-page-actions">
    <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn btn-outline-dark" style="text-decoration:none; padding:9px 18px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-arrow-left" style="font-size:11px;"></i> Back to Donation Logs
    </a>
  </div>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- STATS ROW: Amount | Cause/Seva | Status -->
<!-- ============================================================ -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-md); margin-bottom: var(--space-lg);">

  <div class="admin-stat-card-sm" style="border-left:4px solid var(--maroon);">
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Amount</div>
      <div style="font-size:20px; font-weight:700; color:var(--maroon); margin-top:2px;">₹<?php echo number_format((float) $txn['amount'], 2); ?></div>
      <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
        <?php echo htmlspecialchars($txn['donation_mode'] ?? 'one_time'); ?>
        <?php if (!empty($txn['currency']) && $txn['currency'] !== 'INR'): ?>
          &middot; <?php echo htmlspecialchars($txn['currency']); ?>
        <?php endif; ?>
        <?php if ((int)($txn['quantity'] ?? 1) > 1): ?>
          &middot; Qty: <?php echo (int) $txn['quantity']; ?>
        <?php endif; ?>
      </div>
    </div>
    <div style="width:40px; height:40px; border-radius:var(--radius-md); background:rgba(123,30,30,0.1); color:var(--maroon); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
      <i class="fas fa-indian-rupee-sign"></i>
    </div>
  </div>

  <div class="admin-stat-card-sm" style="border-left:4px solid var(--primary);">
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Cause / Seva</div>
      <div style="font-size:16px; font-weight:600; margin-top:2px;"><?php echo htmlspecialchars($txn['cause_title'] ?: 'General Donation'); ?></div>
      <?php if (!empty($txn['seva_name'])): ?>
        <div style="font-size:12px; color:var(--primary-dark); font-weight:500; margin-top:2px;">
          <i class="fas fa-ribbon" style="font-size:10px;"></i> <?php echo htmlspecialchars($txn['seva_name']); ?>
          <?php if (!empty($txn['seva_category_name'])): ?>
            <span style="color:var(--text-light); font-weight:400;">&middot; <?php echo htmlspecialchars($txn['seva_category_name']); ?></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($txn['cause_category'])): ?>
        <div style="font-size:11px; color:var(--text-light); margin-top:2px;">Category: <?php echo htmlspecialchars(ucfirst($txn['cause_category'])); ?></div>
      <?php endif; ?>
    </div>
    <div style="width:40px; height:40px; border-radius:var(--radius-md); background:rgba(200,168,124,0.12); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
      <i class="fas fa-star"></i>
    </div>
  </div>

  <div class="admin-stat-card-sm" style="border-left:4px solid #2e7d32;">
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Status</div>
      <div style="font-size:16px; font-weight:700; margin-top:2px; display:flex; align-items:center; gap:6px;">
        <span class="txn-status-dot <?php echo $txn['payment_status']; ?>"></span>
        <span style="color:#2e7d32;"><?php echo ucfirst($txn['payment_status']); ?></span>
      </div>
      <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
        Created: <?php echo date('M d, Y H:i', strtotime($txn['created_at'])); ?>
      </div>
      <?php if (!empty($txn['updated_at']) && $txn['updated_at'] !== $txn['created_at']): ?>
        <div style="font-size:11px; color:var(--text-light);">
          Updated: <?php echo date('M d, Y H:i', strtotime($txn['updated_at'])); ?>
        </div>
      <?php endif; ?>
    </div>
    <div style="width:40px; height:40px; border-radius:var(--radius-md); background:rgba(46,125,50,0.1); color:#2e7d32; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
      <i class="fas fa-clipboard-check"></i>
    </div>
  </div>

</div>

<!-- ============================================================ -->
<!-- DONOR & DONATION DETAILS CARD (2-col grid) -->
<!-- ============================================================ -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2><i class="fas fa-user" style="color:var(--primary);"></i> Donor &amp; Donation Details</h2>
  </div>
  <div class="admin-card-body">
    <div class="txn-detail-grid">

      <!-- Donor Info -->
      <div class="txn-detail-section">
        <div class="txn-detail-label"><i class="fas fa-id-card"></i> Donor</div>
        <div class="txn-detail-value" style="font-size:18px; margin-bottom:2px;"><?php echo htmlspecialchars($txn['donor_name']); ?></div>
        <div class="txn-detail-sub"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($txn['donor_phone'] ?: '—'); ?></div>
        <div class="txn-detail-sub"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($txn['donor_email'] ?: '—'); ?></div>
        <?php if (!empty($txn['donor_address'])): ?>
          <div class="txn-detail-sub" style="margin-top:6px;">
            <i class="fas fa-map-marker-alt"></i> <?php echo nl2br(htmlspecialchars($txn['donor_address'])); ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Identification -->
      <div class="txn-detail-section">
        <div class="txn-detail-label"><i class="fas fa-id-badge"></i> Identification</div>
        <div class="txn-detail-value mono" style="text-transform:uppercase; font-size:15px; font-weight:600;">
          PAN: <?php echo htmlspecialchars($txn['pan_number'] ?: '—'); ?>
        </div>
        <div class="txn-detail-sub">Donation Mode: <strong><?php echo htmlspecialchars($txn['donation_mode'] ?? 'one_time'); ?></strong></div>
        <?php if (!empty($txn['source_type'])): ?>
          <div class="txn-detail-sub">Source: <strong><?php echo htmlspecialchars($txn['source_type']); ?></strong>
            <?php if (!empty($txn['source_slug'])): ?>
              (<?php echo htmlspecialchars($txn['source_slug']); ?>)
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($txn['source_url'])): ?>
          <div class="txn-detail-sub" style="font-size:11px; word-break:break-all; margin-top:4px;">
            <i class="fas fa-link"></i> <a href="<?php echo htmlspecialchars($txn['source_url']); ?>" target="_blank" rel="noopener" style="color:var(--primary);"><?php echo htmlspecialchars($txn['source_url']); ?></a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Cause & Seva -->
      <div class="txn-detail-section">
        <div class="txn-detail-label"><i class="fas fa-ribbon"></i> Cause &amp; Seva</div>
        <div class="txn-detail-value"><?php echo htmlspecialchars($txn['cause_title'] ?: 'General Donation'); ?></div>
        <?php if (!empty($txn['cause_category'])): ?>
          <div class="txn-detail-sub">
            <span class="badge badge-info" style="font-size:10px;"><?php echo htmlspecialchars(ucfirst($txn['cause_category'])); ?></span>
          </div>
        <?php endif; ?>
        <div class="txn-detail-sub" style="margin-top:4px;">
          <?php if (!empty($txn['seva_name'])): ?>
            <i class="fas fa-ribbon" style="color:var(--accent);"></i>
            Seva: <strong><?php echo htmlspecialchars($txn['seva_name']); ?></strong>
            <?php if (!empty($txn['master_seva_slug'])): ?>
              <span style="font-size:10px; color:var(--text-light);">(master: <?php echo htmlspecialchars($txn['master_seva_slug']); ?>)</span>
            <?php endif; ?>
          <?php else: ?>
            <span style="color:var(--text-light); font-style:italic;">No specific seva</span>
          <?php endif; ?>
        </div>
        <?php if (!empty($txn['quantity']) && (int)$txn['quantity'] > 1): ?>
          <div class="txn-detail-sub">Quantity: <strong><?php echo (int) $txn['quantity']; ?></strong></div>
        <?php endif; ?>
      </div>

      <!-- Donor Notes -->
      <div class="txn-detail-section">
        <div class="txn-detail-label"><i class="fas fa-sticky-note"></i> Donor Notes</div>
        <?php if (!empty($txn['notes'])): ?>
          <div class="txn-detail-notes"><?php echo nl2br(htmlspecialchars($txn['notes'])); ?></div>
        <?php else: ?>
          <div class="txn-detail-sub" style="font-style:italic;">—</div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- PAYMENT GATEWAY INFO -->
<!-- ============================================================ -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2><i class="fas fa-credit-card" style="color:var(--primary);"></i> Payment Gateway</h2>
  </div>
  <div class="admin-card-body">
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: var(--space-lg);">
      <div>
        <div class="txn-detail-label">Razorpay Order ID</div>
        <div class="txn-detail-value mono" style="font-size:14px; font-weight:600; word-break:break-all;">
          <?php echo htmlspecialchars($txn['razorpay_order_id'] ?: '—'); ?>
        </div>
      </div>
      <div>
        <div class="txn-detail-label">Razorpay Payment ID</div>
        <div class="txn-detail-value mono" style="font-size:14px; word-break:break-all;">
          <?php echo htmlspecialchars($txn['razorpay_payment_id'] ?: '—'); ?>
        </div>
      </div>
      <div>
        <div class="txn-detail-label">Razorpay Signature</div>
        <div class="txn-detail-value mono" style="font-size:11px; color:var(--text-light); word-break:break-all;">
          <?php echo htmlspecialchars($txn['razorpay_signature'] ?: '—'); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- LINKED PUJA BOOKING (if exists) -->
<!-- ============================================================ -->
<?php if (!empty($booking)): ?>
<div class="admin-card txn-linked-card booking">
  <div class="admin-card-header">
    <h2><i class="fas fa-pray" style="color:var(--accent);"></i> Linked Puja Booking</h2>
    <?php if (!empty($booking['status'])): ?>
      <span class="badge <?php echo ($booking['status'] === 'completed') ? 'badge-success' : (($booking['status'] === 'pending') ? 'badge-secondary' : 'badge-info'); ?>" style="font-size:10px;">
        <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
      </span>
    <?php endif; ?>
  </div>
  <div class="admin-card-body">
    <div class="txn-detail-grid">
      <div class="txn-detail-section">
        <div class="txn-detail-label"><i class="fas fa-calendar"></i> Puja Details</div>
        <div class="txn-detail-value"><?php echo htmlspecialchars($booking['puja_type'] ?? '—'); ?></div>
        <div class="txn-detail-sub">
          Date: <strong><?php echo !empty($booking['puja_date']) ? htmlspecialchars(date('M d, Y', strtotime($booking['puja_date']))) : '—'; ?></strong>
          <?php if (!empty($booking['occasion'])): ?>
            &middot; Occasion: <?php echo htmlspecialchars($booking['occasion']); ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="txn-detail-section">
        <div class="txn-detail-label"><i class="fas fa-user-tag"></i> Beneficiary</div>
        <div class="txn-detail-value"><?php echo htmlspecialchars($booking['person_name'] ?? '—'); ?></div>
        <div class="txn-detail-sub">
          <?php if (!empty($booking['gotra'])): ?>Gotra: <strong><?php echo htmlspecialchars($booking['gotra']); ?></strong><?php endif; ?>
          <?php if (!empty($booking['rashi'])): ?>&middot; Rashi: <strong><?php echo htmlspecialchars($booking['rashi']); ?></strong><?php endif; ?>
          <?php if (!empty($booking['nakshatra'])): ?>&middot; Nakshatra: <strong><?php echo htmlspecialchars($booking['nakshatra']); ?></strong><?php endif; ?>
        </div>
      </div>
      <?php if (!empty($booking['special_instructions'])): ?>
        <div class="txn-detail-section full-width">
          <div class="txn-detail-label"><i class="fas fa-sticky-note"></i> Special Instructions</div>
          <div class="txn-detail-notes"><?php echo nl2br(htmlspecialchars($booking['special_instructions'])); ?></div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- LINKED SUBSCRIPTION (if exists) -->
<!-- ============================================================ -->
<?php if (!empty($subscription)): ?>
<div class="admin-card txn-linked-card subscription">
  <div class="admin-card-header">
    <h2><i class="fas fa-sync" style="color:#1565c0;"></i> Linked Subscription</h2>
    <span class="badge <?php echo ($subscription['subscription_status'] ?? '') === 'active' ? 'badge-success' : 'badge-secondary'; ?>" style="font-size:10px;">
      <?php echo htmlspecialchars(ucfirst($subscription['subscription_status'] ?? 'unknown')); ?>
    </span>
  </div>
  <div class="admin-card-body">
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-lg);">
      <div>
        <div class="txn-detail-label">Subscription #</div>
        <div class="txn-detail-value mono">#<?php echo (int) $subscription['id']; ?></div>
      </div>
      <div>
        <div class="txn-detail-label">Amount</div>
        <div class="txn-detail-value" style="color:var(--maroon);">₹<?php echo number_format((float) ($subscription['amount'] ?? 0), 2); ?></div>
      </div>
      <div>
        <div class="txn-detail-label">Interval</div>
        <div class="txn-detail-value" style="text-transform:capitalize;">
          <?php echo htmlspecialchars($subscription['interval_unit'] ?? '—'); ?>
          (<?php echo (int) ($subscription['interval_count'] ?? 1); ?>)
        </div>
      </div>
      <div>
        <div class="txn-detail-label">Razorpay Subscription ID</div>
        <div class="txn-detail-value mono" style="font-size:11px; word-break:break-all;">
          <?php echo htmlspecialchars($subscription['razorpay_subscription_id'] ?: '—'); ?>
        </div>
      </div>
      <?php if (!empty($subscription['razorpay_customer_id'])): ?>
      <div>
        <div class="txn-detail-label">Razorpay Customer ID</div>
        <div class="txn-detail-value mono" style="font-size:11px;"><?php echo htmlspecialchars($subscription['razorpay_customer_id']); ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($subscription['start_at'])): ?>
      <div>
        <div class="txn-detail-label">Start Date</div>
        <div class="txn-detail-value" style="font-size:14px;"><?php echo date('M d, Y', strtotime($subscription['start_at'])); ?></div>
      </div>
      <?php endif; ?>
    </div>
    <?php if (!empty($subscription['notes'])): ?>
      <div style="margin-top:var(--space-md); padding-top:var(--space-md); border-top:1px solid var(--border);">
        <div class="txn-detail-label">Subscription Notes</div>
        <div class="txn-detail-notes" style="margin-top:4px;"><?php echo nl2br(htmlspecialchars($subscription['notes'])); ?></div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php include 'partials/footer.php'; ?>
