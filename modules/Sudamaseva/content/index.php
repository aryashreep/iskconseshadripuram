<?php
/**
 * Sudamaseva Module — Subscription Donation Form (Public)
 *
 * Public landing page where donors can sign up for recurring monthly subscriptions.
 * Integrated with Razorpay subscriptions for automated recurring payments.
 */

$pageTitle = 'Sudamaseva — Monthly Seva Subscription';
$metaDescription = 'Support ISKCON The Palace Temple of Lord Jagannath with a monthly subscription donation. Choose your seva amount and receive 80G tax-exempt receipts.';
$pageType = 'sudamaseva';
include __DIR__ . '/../../Kernel/partials/header.php';

use Isjm\Modules\Sudamaseva\SudamasevaService;

$service = new SudamasevaService();
$defaultAmounts = $service->getDefaultAmounts();
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Sudamaseva — Monthly Seva</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>donate">Donate</a>
      <span>›</span>
      <span>Sudamaseva</span>
    </div>
  </div>
</section>

<!-- Introduction -->
<section class="page-content">
  <div class="container">
    <div class="donate-detail-layout cart-layout">
      <!-- Left Column: Info & Benefits -->
      <div class="donate-detail-info">
        <!-- Main Cause Banner Image -->
        <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:var(--space-lg);">
          <img src="<?php echo BASE_URL; ?>assets/images/banners/sudamaseva-subscribe.svg" alt="Sudamaseva Monthly Seva" style="width:100%;height:auto;max-height:400px;object-fit:cover;display:block;">
        </div>

        <div class="section-divider" style="justify-content: flex-start;">
          <span class="divider-icon">🪷</span>
        </div>
        <span class="section-subtitle reveal" style="text-align: left;">Recurring Devotion</span>
        <h2 class="reveal">What is Sudamaseva?</h2>

        <!-- Category Badges -->
        <div class="seva-meta-badges reveal">
          <span class="seva-badge seva-badge-category">
            <i class="fas fa-sync-alt"></i>
            Monthly Seva
          </span>
          <span class="seva-badge seva-badge-subcategory">
            <i class="fas fa-hand-holding-heart"></i>
            Subscription
          </span>
          <span class="seva-badge seva-badge-time">
            <i class="fas fa-calendar-check"></i>
            Recurring
          </span>
        </div>

        <p class="reveal">
          Sudamaseva is a monthly subscription donation program that lets you offer consistent,
          heartfelt support to the daily operations and spiritual activities of ISKCON 
          The Palace Temple of Lord Jagannath. Named after <strong>Sudama Brahmana</strong>,
          the beloved childhood devotee of Lord Krishna who offered a humble handful of 
          flattened rice with pure love, this program embodies the spirit of <em>seva</em> 
          (selfless service) offered regularly and with devotion.
        </p>

        <div class="seva-rich-section reveal">
          <div class="seva-rich-icon"><i class="fas fa-sync-alt"></i></div>
          <div class="seva-rich-content">
            <h4>How It Works</h4>
            <p>Choose your monthly offering amount, provide your details, and set up a recurring subscription. 
            You will be charged the same amount each month and receive a tax-exempt 80G receipt for every payment. 
            You can cancel anytime by contacting us.</p>
          </div>
        </div>

        <div class="seva-rich-section reveal">
          <div class="seva-rich-icon"><i class="fas fa-hand-holding-heart"></i></div>
          <div class="seva-rich-content">
            <h4>Where Your Donation Goes</h4>
            <p>Your monthly contribution supports daily deity offerings, 
            prasadam distribution, temple maintenance, festival celebrations, 
            educational programs, and community outreach activities. 
            100% of your donation goes directly to the temple's spiritual mission.</p>
          </div>
        </div>

        <div class="seva-rich-section reveal">
          <div class="seva-rich-icon"><i class="fas fa-file-invoice"></i></div>
          <div class="seva-rich-content">
            <h4>80G Tax Exemption</h4>
            <p>All donations to ISKCON The Palace Temple of Lord Jagannath are eligible for 
            tax exemption under Section 80G of the Income Tax Act, 1961. You will receive 
            a detailed receipt for each monthly payment with your PAN details for claiming 
            the exemption.</p>
          </div>
        </div>
        <!-- Prasadam Seva Tiers Info -->
        <div class="seva-offerings-grid-wrap reveal" style="margin-top: var(--space-xl);">
          <h4 style="font-family: var(--font-heading); margin-bottom: var(--space-md); color: var(--text-dark); border-bottom: 2px solid var(--primary); padding-bottom: var(--space-xs); display: inline-block;">
            <i class="fas fa-ribbon"></i> Choose Your Seva Level
          </h4>
          <div class="seva-card-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));">
            <?php foreach ($defaultAmounts as $amt => $label): 
              $isSelected = ($amt === 51);
            ?>
              <div class="tier-card<?php echo $isSelected ? ' selected' : ''; ?>" data-amount="<?php echo $amt; ?>" onclick="selectTier(this, <?php echo $amt; ?>)">
                <div class="tier-amount">₹<?php echo number_format($amt); ?></div>
                <div class="tier-label"><?php echo htmlspecialchars($label); ?></div>
              </div>
            <?php endforeach; ?>
            <div class="custom-tier" onclick="showCustomAmount()">
              <div class="tier-amount" style="color:var(--primary);"><i class="fas fa-pen"></i></div>
              <div class="tier-label">Custom Amount</div>
            </div>
          </div>
        </div>

        <!-- Sacred Promise -->
        <div class="donate-promise reveal" style="margin-top: var(--space-2xl);">
          <div class="donate-promise-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" style="height:28px;width:auto;"></div>
          <div class="donate-promise-text">
            <h4>Our Sacred Promise</h4>
            <p>100% of your monthly donation goes directly to the temple's spiritual activities. 
            We maintain complete transparency and ensure every rupee is utilized for 
            the divine mission of Srila Prabhupada.</p>
          </div>
        </div>
      </div>

      <!-- Right Column: Subscribe Form -->
      <div class="donate-form-sticky reveal">
        <div class="donate-form-card">
          <h3><i class="fas fa-sync-alt"></i> Join Sudamaseva</h3>

          <!-- Mode Toggle: Auto Monthly vs Pay Manually -->
          <div class="mode-toggle-row reveal" style="margin-bottom: var(--space-lg);">
            <button type="button" class="mode-btn active" data-mode="recurring" onclick="switchCollectionMode('recurring')">
              <i class="fas fa-sync-alt"></i> Auto Monthly
            </button>
            <button type="button" class="mode-btn" data-mode="manual" onclick="switchCollectionMode('manual')">
              <i class="fas fa-hand-holding-heart"></i> Pay Monthly
            </button>
          </div>

          <form id="sudamasevaForm" autocomplete="on">
            <div class="form-fields">
              <input type="hidden" id="collectionMode" name="collection_mode" value="recurring">
              <input type="hidden" id="selectedAmount" name="amount" value="5100">
              <input type="hidden" id="totalInstallments" name="total_installments" value="12">

              <!-- Selected Amount Display -->
              <div class="form-group" style="text-align:center; margin-bottom:var(--space-lg);">
                <label style="text-align:center; display:block;">Your Monthly Offering</label>
                <div id="displayAmount" style="font-size:32px; font-weight:700; color:var(--maroon);">₹51</div>
                <div style="font-size:11px; color:var(--text-light);" id="perMonthLabel">per month</div>
              </div>

              <!-- Custom Amount Row -->
              <div class="custom-amount-row" id="customAmountRow" onclick="showCustomAmount()">
                <div class="plus-icon"><i class="fas fa-plus"></i></div>
                <span>Enter Custom Amount</span>
              </div>
              <div class="custom-amount-input-wrap" id="customAmountWrap">
                <label for="customAmount">Enter Amount (₹)</label>
                <div class="input-group">
                  <span class="input-currency">₹</span>
                  <input type="number" id="customAmount" min="51" max="100000" step="1" placeholder="e.g. 1000" oninput="updateCustomAmount(this.value)">
                </div>
              </div>

              <!-- Subscription Duration (shown for recurring mode) -->
              <div class="form-group" id="durationGroup">
                <label for="installments">Subscription Duration</label>
                <select id="installments" name="total_installments" class="form-control" onchange="document.getElementById('totalInstallments').value=this.value">
                  <option value="6">6 Months</option>
                  <option value="12" selected>12 Months (1 Year)</option>
                  <option value="24">24 Months (2 Years)</option>
                  <option value="36">36 Months (3 Years)</option>
                  <option value="60">60 Months (5 Years)</option>
                  <option value="120">120 Months (10 Years)</option>
                </select>
              </div>

              <!-- Donor Name -->
              <div class="form-group">
                <label for="donorName">Donor Name *</label>
                <input type="text" id="donorName" name="donor_name" placeholder="Enter your full name" required>
              </div>

              <!-- Email + Phone row -->
              <div class="form-row-fields">
                <div class="form-group">
                  <label for="donorEmail">Email Address *</label>
                  <input type="email" id="donorEmail" name="donor_email" placeholder="name@domain.com" required>
                </div>
                <div class="form-group">
                  <label for="donorPhone">WhatsApp Phone *</label>
                  <input type="tel" id="donorPhone" name="donor_phone" placeholder="+91-98765" required>
                </div>
              </div>

              <!-- PAN (for 80G) -->
              <div class="form-group">
                <label for="panNumber">PAN Card <span style="color:var(--text-light);font-weight:400;font-size:11px;">(optional, for 80G receipt)</span></label>
                <input type="text" id="panNumber" name="pan_number" placeholder="e.g. ABCDE1234F" maxlength="10" style="text-transform:uppercase;">
              </div>

              <!-- Address (optional) -->
              <div class="form-row-fields">
                <div class="form-group">
                  <label for="area">Area / Locality</label>
                  <input type="text" id="area" name="area" placeholder="e.g. Seshadripuram">
                </div>
                <div class="form-group">
                  <label for="city">City</label>
                  <input type="text" id="city" name="city" placeholder="e.g. Bengaluru">
                </div>
              </div>

              <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" placeholder="e.g. Karnataka">
              </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary btn-lg donate-submit-btn" id="subscribeBtn" style="width:100%;">
              <i class="fas fa-lock"></i> <span id="btnLabel">Subscribe</span> — ₹<span id="payAmount">51</span>/month
            </button>

            <!-- Notice (changes based on mode) -->
            <div class="donate-monthly-notice" id="monthlyNotice">
              <i class="fas fa-info-circle"></i>
              <span id="noticeText">You authorize automated monthly charges of <strong>₹<span id="monthlyAmountDisplay">51</span></strong> via secure eMandate, eNACH, or UPI Autopay.</span>
            </div>

            <div class="donate-secure">
              <i class="fas fa-shield-alt"></i>
              <span>Secured by <strong>Razorpay</strong> — 128-bit SSL Encrypted</span>
            </div>

            <div class="donate-methods" id="paymentMethods">
              <span>We accept</span>
              <div class="payment-icons">
                <span><i class="fas fa-credit-card"></i> Cards</span>
                <span><i class="fas fa-mobile-alt"></i> UPI</span>
                <span><i class="fas fa-university"></i> Net Banking</span>
              </div>
            </div>
          </form>

          <!-- Loading Overlay -->
          <div class="donate-loading" id="sudamasevaLoading">
            <div class="donate-loading-spinner"></div>
            <p id="loadingText">Setting up your subscription...</p>
          </div>

          <!-- Returning Donor CTA -->
          <div style="margin-top:var(--space-lg); padding-top:var(--space-lg); border-top:1px solid var(--border); text-align:center;">
            <p style="font-size:13px; color:var(--text-light); margin-bottom:var(--space-sm);">Already enrolled in Sudamaseva?</p>
            <a href="<?php echo BASE_URL; ?>sudamaseva/lookup" class="btn btn-outline-dark" style="font-size:13px; padding:8px 20px; text-decoration:none; display:inline-block;">
              <i class="fas fa-search"></i> View My Seva
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
  <div class="container">
    <div class="section-divider">
      <span class="divider-icon">❓</span>
    </div>
    <span class="section-subtitle reveal" style="text-align:center;">Have Questions?</span>
    <h2 class="section-title reveal" style="text-align:center;">Frequently Asked Questions</h2>

    <div class="faq-list">
      <div class="faq-item active" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>How does the monthly subscription work?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">            <p>When you subscribe, you authorize Razorpay to charge your chosen amount on the same day each month. You'll receive a receipt and a 80G tax certificate for every payment. You can cancel or modify your subscription at any time by contacting us. Alternatively, you can choose the <strong>Pay Monthly</strong> option to manually pay each month without setting up auto-debit.</p>
        </div>
      </div>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>What is eMandate / eNACH / UPI Autopay?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>eMandate, eNACH, and UPI Autopay are secure electronic authorization systems regulated by NPCI and RBI. When setting up your subscription, you authorize a recurring payment mandate on your bank account (via net banking/debit card) or UPI app. This ensures your monthly seva is offered seamlessly without having to manually pay each month. You remain in full control and can pause or cancel the mandate at any time by contacting us.</p>
        </div>
      </div>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>Is my donation tax-exempt?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>Yes! All donations to ISKCON The Palace Temple of Lord Jagannath are eligible for tax exemption under Section 80G of the Income Tax Act. Please provide your PAN for the tax-exempt receipt. Minimum ₹200 per payment is required for 80G eligibility.</p>
        </div>
      </div>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>Can I change or cancel my subscription?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>Yes, you can cancel or modify your subscription anytime by contacting us at <strong>seva@iskconseshadripuram.org</strong> or calling <strong>+91 99860 77269</strong>. We will process your request within 2 business days.</p>
        </div>
      </div>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>What payment methods are accepted?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>We accept all major credit and debit cards, UPI (GPay, PhonePay, Paytm), net banking, and wallets through our secure Razorpay payment gateway. Your payment information is encrypted and secured with 128-bit SSL.</p>
        </div>
      </div>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>Can I donate via bank transfer instead?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>Absolutely! You can make a direct bank transfer using the account details below. Please send your transaction details to <strong>seva@iskconseshadripuram.org</strong> so we can acknowledge your donation and issue a receipt.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Bank Details Section -->
<section class="bank-details-section">
  <div class="container">
    <div class="section-divider">
      <span class="divider-icon">🏦</span>
    </div>
    <span class="section-subtitle reveal">Bank Transfer</span>
    <h2 class="section-title reveal">Direct Bank Donation</h2>
    <p class="section-description reveal">
      Prefer to donate directly? Use our bank account details below.
      Please send your transaction details so we can acknowledge your donation.
    </p>

    <?php if (isset($BANK_DETAILS)): ?>
    <div class="bank-details-card reveal">
      <div class="bank-details-header">
        <h3><?php echo htmlspecialchars($BANK_DETAILS['bank_name'] ?? ''); ?></h3>
        <p>Account Details for Direct Transfer</p>
      </div>
      <div class="bank-details-body">
        <div class="bank-detail-row">
          <span class="bank-detail-label">Account Name</span>
          <span class="bank-detail-value"><?php echo htmlspecialchars($BANK_DETAILS['account_name'] ?? ''); ?></span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">Account Number</span>
          <span class="bank-detail-value copyable"><?php echo htmlspecialchars($BANK_DETAILS['account_number'] ?? ''); ?></span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">IFSC Code</span>
          <span class="bank-detail-value copyable"><?php echo htmlspecialchars($BANK_DETAILS['ifsc_code'] ?? ''); ?></span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">Branch</span>
          <span class="bank-detail-value"><?php echo htmlspecialchars($BANK_DETAILS['branch'] ?? ''); ?></span>
        </div>
        <div class="bank-note">
          <i class="fas fa-info-circle"></i> After making a bank transfer, please send your transaction details 
          to <strong>seva@iskconseshadripuram.org</strong> so we can acknowledge your donation.
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Test Mode Notice -->
<?php if (defined('RAZORPAY_TEST_MODE') && RAZORPAY_TEST_MODE): ?>
<div class="test-mode-notice">
  <div class="container">
    <p>
      <i class="fas fa-flask"></i>
      <strong>Test Mode Active:</strong> No real payments will be processed.
      Use test card <code>4111 1111 1111 1111</code> with any future expiry and CVV.
    </p>
  </div>
</div>
<?php endif; ?>

<!-- Page-specific CSS -->
<link rel="stylesheet" href="<?= asset('modules/Sudamaseva/assets/css/sudamaseva.css') ?>">

<!-- Razorpay Checkout -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
// ============================================================
// Seva Tier Selection
// ============================================================
window.selectedAmount = 51; // INR
window.selectedAmountPaise = 5100;

function selectTier(el, amountInr) {
  // Deselect all tier cards
  document.querySelectorAll('.tier-card, .custom-tier').forEach(function(c) {
    c.classList.remove('selected');
  });
  el.classList.add('selected');

  // Hide custom amount input
  document.getElementById('customAmountWrap').classList.remove('active');
  document.getElementById('customAmountRow').classList.remove('active');

  window.selectedAmount = amountInr;
  window.selectedAmountPaise = amountInr * 100;

  document.getElementById('displayAmount').textContent = '₹' + amountInr.toLocaleString('en-IN');
  document.getElementById('selectedAmount').value = window.selectedAmountPaise;
  document.getElementById('payAmount').textContent = amountInr.toLocaleString('en-IN');
  document.getElementById('monthlyAmountDisplay').textContent = amountInr.toLocaleString('en-IN');
  document.getElementById('subscribeBtn').innerHTML = '<i class="fas fa-lock"></i> Subscribe — ₹' + amountInr.toLocaleString('en-IN') + '/month';
}

function showCustomAmount() {
  // Deselect all tier cards
  document.querySelectorAll('.tier-card, .custom-tier').forEach(function(c) {
    c.classList.remove('selected');
  });
  document.querySelector('.custom-tier').classList.add('selected');

  // Show custom amount input with animation
  document.getElementById('customAmountWrap').classList.add('active');
  document.getElementById('customAmountRow').classList.add('active');
  document.getElementById('customAmount').value = '';
  document.getElementById('customAmount').focus();
}

function updateCustomAmount(val) {
  var amt = parseInt(val) || 0;
  if (amt < 51) amt = 51;
  if (amt > 100000) amt = 100000;

  window.selectedAmount = amt;
  window.selectedAmountPaise = amt * 100;

  document.getElementById('displayAmount').textContent = '₹' + amt.toLocaleString('en-IN');
  document.getElementById('selectedAmount').value = window.selectedAmountPaise;
  document.getElementById('payAmount').textContent = amt.toLocaleString('en-IN');
  document.getElementById('monthlyAmountDisplay').textContent = amt.toLocaleString('en-IN');
  document.getElementById('subscribeBtn').innerHTML = '<i class="fas fa-lock"></i> Subscribe — ₹' + amt.toLocaleString('en-IN') + '/month';
}

// ============================================================
// FAQ Toggle
// ============================================================
function toggleFaq(el) {
  el.classList.toggle('active');
}

// ============================================================
// Collection Mode Toggle
// ============================================================
window.collectionMode = 'recurring';

function switchCollectionMode(mode) {
  window.collectionMode = mode;
  document.getElementById('collectionMode').value = mode;

  // Toggle active class on buttons
  document.querySelectorAll('.mode-btn').forEach(function(b) {
    b.classList.toggle('active', b.getAttribute('data-mode') === mode);
  });

  var durationGroup = document.getElementById('durationGroup');
  var noticeEl = document.getElementById('monthlyNotice');
  var noticeText = document.getElementById('noticeText');
  var btnLabel = document.getElementById('btnLabel');
  var payAmount = document.getElementById('payAmount');
  var monthlyAmount = document.getElementById('monthlyAmountDisplay');
  var subscribeBtn = document.getElementById('subscribeBtn');
  var paymentMethods = document.getElementById('paymentMethods');
  var loadingText = document.getElementById('loadingText');

  if (mode === 'recurring') {
    durationGroup.style.display = 'block';
    noticeEl.style.display = 'flex';
    document.getElementById('monthlyAmountDisplay').textContent = window.selectedAmount.toLocaleString('en-IN');
    noticeText.innerHTML = 'You authorize automated monthly charges of <strong>₹' + window.selectedAmount.toLocaleString('en-IN') + '</strong> via secure eMandate, eNACH, or UPI Autopay.';
    btnLabel.textContent = 'Subscribe';
    subscribeBtn.innerHTML = '<i class="fas fa-lock"></i> Subscribe — ₹' + window.selectedAmount.toLocaleString('en-IN') + '/month';
    paymentMethods.style.display = 'flex';
    loadingText.textContent = 'Setting up your subscription...';
  } else {
    durationGroup.style.display = 'none';
    noticeEl.style.display = 'flex';
    document.getElementById('monthlyAmountDisplay').textContent = window.selectedAmount.toLocaleString('en-IN');
    noticeText.innerHTML = 'You will be charged <strong>₹' + window.selectedAmount.toLocaleString('en-IN') + '</strong> now for the first month. Return each month to pay the next installment.';
    btnLabel.textContent = 'Pay First Month';
    subscribeBtn.innerHTML = '<i class="fas fa-lock"></i> Pay ₹' + window.selectedAmount.toLocaleString('en-IN') + ' Now';
    paymentMethods.style.display = 'flex';
    loadingText.textContent = 'Creating your enrollment...';
  }
}

// ============================================================
// Subscription / Enrollment Form Submission
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('sudamasevaForm');
  var loadingEl = document.getElementById('sudamasevaLoading');
  var btn = document.getElementById('subscribeBtn');

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate required fields
    var name = document.getElementById('donorName').value.trim();
    var phone = document.getElementById('donorPhone').value.trim();
    var email = document.getElementById('donorEmail').value.trim();

    if (!name || !phone || !email) {
      alert('Please fill in all required fields (Name, Phone, Email).');
      return;
    }

    if (window.selectedAmountPaise < 5100) {
      alert('Minimum subscription amount is ₹51.');
      return;
    }

    // Show loading
    btn.disabled = true;
    loadingEl.style.display = 'flex';

    var mode = window.collectionMode;

    if (mode === 'recurring') {
      // ============================================================
      // AUTO MONTHLY FLOW — Uses existing create-subscription API
      // ============================================================
      var payload = {
        donor_name: name,
        donor_phone: phone,
        donor_email: email,
        pan_number: document.getElementById('panNumber').value.trim().toUpperCase(),
        amount: window.selectedAmountPaise,
        total_installments: parseInt(document.getElementById('totalInstallments').value),
        area: document.getElementById('area').value.trim(),
        city: document.getElementById('city').value.trim(),
        state: document.getElementById('state').value.trim(),
      };

      fetch('<?php echo BASE_URL; ?>api/sudamaseva/create-subscription', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.error) {
          alert(data.error + (data.details ? ': ' + data.details : ''));
          btn.disabled = false;
          loadingEl.style.display = 'none';
          return;
        }

        // Open Razorpay subscription checkout
        var options = {
          key: '<?php echo RAZORPAY_KEY_ID; ?>',
          subscription_id: data.subscription_id,
          name: 'ISKCON Palace Temple',
          description: 'Sudamaseva Monthly: ₹' + window.selectedAmount.toLocaleString('en-IN'),
          image: '<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg',
          currency: '<?php echo CURRENCY; ?>',
          handler: function(response) {
            window.location.href = '<?php echo BASE_URL; ?>sudamaseva/success?subscription_id=' + encodeURIComponent(data.subscription_id)
              + '&payment_id=' + encodeURIComponent(response.razorpay_payment_id)
              + '&signature=' + encodeURIComponent(response.razorpay_signature)
              + '&amount=' + window.selectedAmountPaise;
          },
          modal: {
            ondismiss: function() {
              btn.disabled = false;
              loadingEl.style.display = 'none';
            }
          }
        };

        var rzp = new Razorpay(options);
        rzp.open();
      })
      .catch(function(err) {
        alert('Failed to create subscription. Please try again.');
        console.error('Sudamaseva subscribe error:', err);
        btn.disabled = false;
        loadingEl.style.display = 'none';
      });

    } else {
      // ============================================================
      // PAY MONTHLY MANUALLY FLOW — Uses new enroll API
      // ============================================================
      var payload = {
        donor_name: name,
        donor_phone: phone,
        donor_email: email,
        pan_number: document.getElementById('panNumber').value.trim().toUpperCase(),
        amount: window.selectedAmountPaise,
        total_installments: parseInt(document.getElementById('totalInstallments').value),
        area: document.getElementById('area').value.trim(),
        city: document.getElementById('city').value.trim(),
        state: document.getElementById('state').value.trim(),
      };

      fetch('<?php echo BASE_URL; ?>api/sudamaseva/enroll', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.error) {
          alert(data.error + (data.details ? ': ' + data.details : ''));
          btn.disabled = false;
          loadingEl.style.display = 'none';
          return;
        }

        // Open Razorpay order checkout (not subscription)
        var options = {
          key: '<?php echo RAZORPAY_KEY_ID; ?>',
          order_id: data.order_id,
          name: 'ISKCON Palace Temple',
          description: 'Sudamaseva First Month: ₹' + window.selectedAmount.toLocaleString('en-IN'),
          image: '<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg',
          currency: '<?php echo CURRENCY; ?>',
          handler: function(response) {
            // Verify the order payment
            verifyManualPayment(data.db_subscription_id, 1, window.selectedAmountPaise, response, data.donor_id);
          },
          modal: {
            ondismiss: function() {
              btn.disabled = false;
              loadingEl.style.display = 'none';
            }
          }
        };

        var rzp = new Razorpay(options);
        rzp.open();
      })
      .catch(function(err) {
        alert('Failed to create enrollment. Please try again.');
        console.error('Sudamaseva enroll error:', err);
        btn.disabled = false;
        loadingEl.style.display = 'none';
      });
    }
  });
});

// ============================================================
// Verify manual payment after Razorpay checkout
// ============================================================
function verifyManualPayment(subscriptionId, installmentNumber, amount, response, donorId) {
  var loadingEl = document.getElementById('sudamasevaLoading');
  var btn = document.getElementById('subscribeBtn');
  loadingEl.querySelector('p').textContent = 'Verifying payment...';

  fetch('<?php echo BASE_URL; ?>api/sudamaseva/verify-order', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      razorpay_order_id: response.razorpay_order_id,
      razorpay_payment_id: response.razorpay_payment_id,
      razorpay_signature: response.razorpay_signature,
      subscription_id: subscriptionId,
      installment_number: installmentNumber,
      amount: amount
    })
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      // Redirect to dashboard with success
      window.location.href = '<?php echo BASE_URL; ?>sudamaseva/dashboard?donor_id=' + donorId + '&payment=success';
    } else {
      alert('Payment verification failed: ' + (data.error || 'Unknown error'));
      loadingEl.style.display = 'none';
      btn.disabled = false;
    }
  })
  .catch(function(err) {
    alert('Payment verification failed. Please contact support.');
    console.error('Verify error:', err);
    loadingEl.style.display = 'none';
    btn.disabled = false;
  });
}

// ============================================================
// FAQ Toggle
// ============================================================
function toggleFaq(el) {
  el.classList.toggle('active');
}
</script>

<?php include __DIR__ . '/../../Kernel/partials/footer.php'; ?>
