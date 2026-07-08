<?php
$pageTitle = 'Checkout - ISKCON The Palace Temple of Lord Jagannath';
include '../partials/header.php';
require_once '../config.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Checkout</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Checkout</span>
    </div>
  </div>
</section>

<!-- Checkout Content -->
<section class="page-content">
  <div class="container">
    <!-- Cart Loaded via JS -->
    <div id="checkoutApp" data-config='{"razorpay":{"keyId":"<?php echo RAZORPAY_KEY_ID; ?>","currency":"<?php echo CURRENCY; ?>","siteName":"<?php echo SITE_NAME; ?>","testMode":<?php echo RAZORPAY_TEST_MODE ? 'true' : 'false'; ?>}}'>
      <!-- Loading State -->
      <div class="checkout-loading" id="checkoutLoading" style="text-align:center;padding:var(--space-4xl) 0;">
        <div style="width:40px;height:40px;border:3px solid var(--border);border-top:3px solid var(--primary);border-radius:50%;animation:spin 1s linear infinite;margin:0 auto var(--space-lg);"></div>
        <p style="color:var(--text-light);">Loading your cart...</p>
      </div>
    </div>
  </div>
</section>

<!-- Checkout Styles -->
<link rel="stylesheet" href="<?= asset('modules/Donation/assets/css/checkout.css') ?>">

<script src="<?= asset('modules/Donation/assets/js/checkout.js') ?>"></script>

<?php include '../partials/footer.php'; ?>