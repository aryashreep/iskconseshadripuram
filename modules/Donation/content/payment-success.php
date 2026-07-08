<?php
require_once __DIR__ . '/../../../config.php';

$paymentId = $_GET['payment_id'] ?? '';
$orderId = $_GET['order_id'] ?? '';
$amount = intval($_GET['amount'] ?? 0);
$amountInr = $amount / 100; // Convert paise to rupees
$causeSlug = preg_replace('/[^a-z0-9\-]/', '', $_GET['cause'] ?? '');
$subscriptionId = $_GET['subscription_id'] ?? '';
$mode = $_GET['mode'] ?? 'one_time';

// Fetch cause details from DB if available
$causeName = 'Donation';
$causeShortTitle = '';
if (!empty($causeSlug)) {
    $cause = getDonationCauseBySlug($causeSlug);
    if ($cause) {
        $causeName = $cause['short_title'] ?: $cause['title'];
        $causeShortTitle = $cause['title'];
    }
}

$pageTitle = 'Payment Successful - ISKCON The Palace Temple of Lord Jagannath';
include '../partials/header.php'; ?>

<!-- Google Analytics: Purchase Conversion -->
<script>
gtag('event', 'purchase', {
  transaction_id: '<?php echo htmlspecialchars($paymentId); ?>',
  value: <?php echo $amountInr; ?>,
  currency: 'INR',
  tax: 0,
  shipping: 0,
  items: [{
    item_id: '<?php echo htmlspecialchars($causeSlug ?: 'general-donation'); ?>',
    item_name: '<?php echo htmlspecialchars($causeName); ?>',
    item_category: 'Donation',
    price: <?php echo $amountInr; ?>,
    quantity: 1
  }],
  non_interaction: true
});
</script>

<?php
?>

<!-- Success Page -->
<section class="page-content">
  <div class="container">
    <div class="payment-result payment-success">
      <div class="payment-result-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h1>Seva Offered Successfully! 🙏</h1>
      <p class="payment-result-subtitle">
        Your <?php echo htmlspecialchars($mode === 'monthly' ? 'monthly' : 'generous'); ?> contribution
        for <strong><?php echo htmlspecialchars($causeName); ?></strong>
        has been received with gratitude. 
        May the divine blessings of Sri Sri Krishna Balaram be with you and your family.
      </p>

      <div class="payment-details-card">
        <h4>Payment Receipt</h4>
        <?php if (!empty($causeName)): ?>
        <div class="payment-detail-row">
          <span>Cause</span>
          <span class="payment-detail-value"><?php echo htmlspecialchars($causeShortTitle ?: $causeName); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($mode === 'monthly' && !empty($subscriptionId)): ?>
        <div class="payment-detail-row">
          <span>Subscription ID</span>
          <span class="payment-detail-value"><?php echo htmlspecialchars($subscriptionId); ?></span>
        </div>
        <div class="payment-detail-row">
          <span>Mode</span>
          <span class="payment-detail-value success-badge">✓ Monthly (Auto-renew)</span>
        </div>
        <?php endif; ?>
        
        <div class="payment-detail-row">
          <span>Payment ID</span>
          <span class="payment-detail-value"><?php echo htmlspecialchars($paymentId); ?></span>
        </div>
        <div class="payment-detail-row">
          <span>Order ID</span>
          <span class="payment-detail-value"><?php echo htmlspecialchars($orderId); ?></span>
        </div>
        <div class="payment-detail-row">
          <span>Amount</span>
          <span class="payment-detail-value amount">₹<?php echo number_format($amountInr); ?></span>
        </div>
        <div class="payment-detail-row">
          <span>Date</span>
          <span class="payment-detail-value"><?php echo date('d M Y, h:i A'); ?></span>
        </div>
        <div class="payment-detail-row">
          <span>Status</span>
          <span class="payment-detail-value success-badge">✓ <?php echo $mode === 'monthly' ? 'Subscription Active' : 'Successful'; ?></span>
        </div>
      </div>

      <?php if ($mode === 'monthly'): ?>
      <div style="background:var(--cream); border-radius:var(--radius-lg); padding:var(--space-lg); margin-bottom:var(--space-2xl); text-align:center; border:1px dashed var(--primary);">
        <p style="margin:0; font-size:var(--font-size-sm); color:var(--text); line-height:1.6;">
          <i class="fas fa-sync-alt" style="color:var(--primary); margin-right:6px;"></i>
          Your monthly subscription is active. You will be charged on the same day each month.
          A receipt will be sent to your email.
        </p>
      </div>
      <?php endif; ?>

      <div class="payment-result-actions">
        <a href="<?php echo BASE_URL; ?>donate" class="btn btn-primary">
          <i class="fas fa-hands-helping"></i> Explore More Seva
        </a>
        <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-dark">
          <i class="fas fa-home"></i> Return Home
        </a>
      </div>

      <div class="payment-blessing">
        <p>🌺 "Whatever you do, whatever you eat, whatever you offer or give away, and whatever austerities you perform — do that, O son of Kunti, as an offering to Me."</p>
        <span>— Bhagavad Gita 9.27</span>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
