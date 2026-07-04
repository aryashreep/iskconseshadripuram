<?php
require_once __DIR__ . '/../config.php';

$paymentId = $_GET['payment_id'] ?? '';
$causeSlug = preg_replace('/[^a-z0-9\-]/', '', $_GET['cause'] ?? '');
$mode = $_GET['mode'] ?? 'one_time';

$causeName = 'Donation';
if (!empty($causeSlug)) {
    $cause = getDonationCauseBySlug($causeSlug);
    if ($cause) {
        $causeName = $cause['short_title'] ?: $cause['title'];
    }
}

$pageTitle = 'Payment Failed - ISKCON The Palace Temple of Lord Jagannath';
include '../partials/header.php';
?>

<!-- Failure Page -->
<section class="page-content">
  <div class="container">
    <div class="payment-result payment-failed">
      <div class="payment-result-icon failed">
        <i class="fas fa-times-circle"></i>
      </div>
      <h1>Payment Could Not Be Completed</h1>
      <p class="payment-result-subtitle">
        <?php if (!empty($causeName)): ?>
        Your <?php echo htmlspecialchars($mode === 'monthly' ? 'monthly subscription' : 'donation'); ?>
        for <strong><?php echo htmlspecialchars($causeName); ?></strong>
        could not be completed.
        <?php else: ?>
        The transaction was not completed.
        <?php endif; ?>
        This could be due to a network issue, 
        insufficient balance, or the payment was cancelled. Please try again.
      </p>

      <div class="payment-result-actions">
        <?php if (!empty($causeSlug)): ?>
        <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($causeSlug); ?><?php echo $mode === 'monthly' ? '?mode=monthly' : ''; ?>" class="btn btn-primary">
          <i class="fas fa-redo"></i> Try Again
        </a>
        <?php else: ?>
        <a href="<?php echo BASE_URL; ?>donate" class="btn btn-primary">
          <i class="fas fa-redo"></i> Try Again
        </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-dark">
          <i class="fas fa-envelope"></i> Need Help?
        </a>
        <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-dark">
          <i class="fas fa-home"></i> Return Home
        </a>
      </div>

      <div class="payment-blessing">
        <p>🕯️ If you continue to face issues, please contact us. We're here to help you complete your seva.</p>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
