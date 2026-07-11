<?php
/**
 * Sudamaseva Module — Subscription Success Page
 *
 * Called via POST after Razorpay checkout completes.
 * Verifies the payment signature, then shows confirmation.
 */

use Isjm\Modules\Sudamaseva\SudamasevaRepository;
use Isjm\Modules\Sudamaseva\SudamasevaService;

require_once __DIR__ . '/../../Kernel/config.php';
require_once __DIR__ . '/../../Kernel/includes/bootstrap.php';

$pageTitle = 'Subscription Successful — Sudamaseva';
$metaDescription = 'Your monthly subscription donation to ISKCON The Palace Temple of Lord Jagannath has been set up successfully.';
$pageType = 'default';
include __DIR__ . '/../../Kernel/partials/header.php';

// Get parameters from URL (passed from Razorpay handler)
$subscriptionId = $_GET['subscription_id'] ?? '';
$paymentId = $_GET['payment_id'] ?? '';
$signature = $_GET['signature'] ?? '';
$amount = intval($_GET['amount'] ?? 0);

$verified = false;
$subscriptionDetails = null;
$error = '';

if (!empty($subscriptionId) && !empty($paymentId) && !empty($signature)) {
    // Verify HMAC signature
    $expectedSignature = hash_hmac(
        'sha256',
        $subscriptionId . '|' . $paymentId,
        RAZORPAY_KEY_SECRET
    );

    if (hash_equals($expectedSignature, $signature)) {
        $verified = true;

        // Fetch subscription details from DB (best-effort)
        try {
            $repo = new SudamasevaRepository();
            $service = new SudamasevaService($repo);
            $subscriptionDetails = $repo->getSubscriptionByRazorpayId($subscriptionId);
        } catch (Exception $e) {
            $error = 'Could not load subscription details.';
        }
    } else {
        $error = 'Signature verification failed.';
    }
} else {
    // No parameters — check if this is just the initial page load without a payment
    if (empty($subscriptionId) && empty($paymentId)) {
        $error = 'No subscription data received.';
    } else {
        $error = 'Missing payment verification parameters.';
    }
}

$amountInr = $amount > 0 ? (int) ($amount / 100) : 0;
$donorName = $subscriptionDetails['donor_name'] ?? '';
$subAmount = $subscriptionDetails['amount'] ?? $amountInr;
$installments = $subscriptionDetails['total_installments'] ?? 12;
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/sudamaseva-success.svg');"></div>
  <div class="container">
    <h1 class="reveal">Subscription Active 🙏</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>sudamaseva">Sudamaseva</a>
      <span>›</span>
      <span>Success</span>
    </div>
  </div>
</section>

<!-- Success Page -->
<section class="page-content">
  <div class="container">
    <?php if ($verified): ?>
      <div class="payment-result payment-success">
        <div class="payment-result-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h1>Subscription Active! 🙏</h1>
        <p class="payment-result-subtitle">
          <?php if (!empty($donorName)): ?>
            Dear <strong><?php echo htmlspecialchars($donorName); ?></strong>,
          <?php endif; ?>
          Your monthly subscription of <strong>₹<?php echo number_format($subAmount); ?></strong>
          has been set up successfully. May the divine blessings of Sri Sri Krishna Balaram 
          be with you and your family always.
        </p>

        <div class="payment-details-card">
          <h4>Subscription Details</h4>
          <div class="payment-detail-row">
            <span>Subscription ID</span>
            <span class="payment-detail-value" style="font-family:monospace;font-size:12px;"><?php echo htmlspecialchars($subscriptionId); ?></span>
          </div>
          <div class="payment-detail-row">
            <span>Monthly Amount</span>
            <span class="payment-detail-value amount">₹<?php echo number_format($subAmount); ?></span>
          </div>
          <?php if ($installments > 0): ?>
          <div class="payment-detail-row">
            <span>Duration</span>
            <span class="payment-detail-value"><?php echo $installments; ?> months</span>
          </div>
          <?php endif; ?>
          <div class="payment-detail-row">
            <span>Payment ID</span>
            <span class="payment-detail-value" style="font-family:monospace;font-size:12px;"><?php echo htmlspecialchars($paymentId); ?></span>
          </div>
          <div class="payment-detail-row">
            <span>Date</span>
            <span class="payment-detail-value"><?php echo date('d M Y, h:i A'); ?></span>
          </div>
          <div class="payment-detail-row">
            <span>Status</span>
            <span class="payment-detail-value success-badge">✓ Active</span>
          </div>
        </div>

        <div style="background:var(--cream); border-radius:var(--radius-lg); padding:var(--space-lg); margin-bottom:var(--space-2xl); text-align:center; border:1px dashed var(--primary);">
          <p style="margin:0; font-size:var(--font-size-sm); color:var(--text); line-height:1.6;">
            <i class="fas fa-sync-alt" style="color:var(--primary); margin-right:6px;"></i>
            Your monthly subscription is active. You will be charged <strong>₹<?php echo number_format($subAmount); ?></strong> on the same day each month.
            A receipt will be sent to your email for every payment.
          </p>
        </div>

        <div class="payment-result-actions">
          <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Return Home
          </a>
          <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-dark">
            <i class="fas fa-envelope"></i> Contact Us
          </a>
        </div>

        <div class="payment-blessing">
          <p>🌺 "Whatever you do, whatever you eat, whatever you offer or give away, and whatever austerities you perform — do that, O son of Kunti, as an offering to Me."</p>
          <span>— Bhagavad Gita 9.27</span>
        </div>
      </div>

    <?php elseif (!empty($subscriptionId) && !empty($paymentId) && !$verified): ?>
      <!-- Failed verification — but subscription might still be active via webhook -->
      <div class="payment-result payment-failed">
        <div class="payment-result-icon failed">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Verification Pending</h1>
        <p class="payment-result-subtitle">
          Your payment was received, but the verification could not be completed immediately.
          This is usually temporary — your subscription will be activated within a few minutes 
          once the payment is confirmed by our payment gateway.
        </p>
        <div class="payment-result-actions">
          <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Return Home
          </a>
          <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-dark">
            <i class="fas fa-envelope"></i> Need Help?
          </a>
        </div>
      </div>

    <?php else: ?>
      <!-- Error state -->
      <div class="payment-result payment-failed">
        <div class="payment-result-icon failed">
          <i class="fas fa-times-circle"></i>
        </div>
        <h1>Subscription Could Not Be Set Up</h1>
        <p class="payment-result-subtitle">
          <?php echo htmlspecialchars($error ?: 'An unexpected error occurred.'); ?>
        </p>
        <div class="payment-result-actions">
          <a href="<?php echo BASE_URL; ?>sudamaseva" class="btn btn-primary">
            <i class="fas fa-redo"></i> Try Again
          </a>
          <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-dark">
            <i class="fas fa-envelope"></i> Need Help?
          </a>
        </div>
        <div class="payment-blessing">
          <p>🕯️ If you continue to face issues, please contact us. We're here to help you complete your seva.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../../Kernel/partials/footer.php'; ?>
