<?php
$pageTitle = 'Donate - ISKCON The Palace Temple of Lord Jagannath';
$pageType = 'donate';

// Prepare FAQ data for schema.org structured data
$faqItems = [
    [
        'question' => 'How is my donation used?',
        'answer' => '100% of your donation goes directly to the seva or cause you choose. We maintain strict transparency and accountability, ensuring every rupee is utilized for its intended purpose — whether deity worship, prasadam distribution, festival celebrations, or temple development.'
    ],
    [
        'question' => 'Will I receive a receipt?',
        'answer' => 'Yes, you will receive an email receipt with your transaction details immediately after successful payment. You can use this for your records and for tax exemption purposes under 80G of the Income Tax Act.'
    ],
    [
        'question' => 'Can I donate via bank transfer?',
        'answer' => 'Absolutely! You can make a direct bank transfer using the account details provided on this page. Please send your transaction details so we can acknowledge your donation.'
    ],
    [
        'question' => 'Is my donation tax-exempt?',
        'answer' => 'Yes, donations to ISKCON The Palace Temple of Lord Jagannath are eligible for tax exemption under Section 80G of the Income Tax Act. You will receive a receipt with all necessary details for claiming the exemption.'
    ],
    [
        'question' => 'How does monthly subscription work?',
        'answer' => 'When you choose monthly mode, you will be charged the selected amount on the same day each month. You can cancel or modify your subscription at any time by contacting us. A receipt will be sent to your email each month.'
    ],
];

include '../partials/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/donate-seva.css">
<?php
require_once '../config.php';

// Get cause from URL — supports both new (?cause=) and legacy (?type=) params
$causeSlug = $_GET['cause'] ?? '';
$selectedType = $_GET['type'] ?? '';

if (!empty($causeSlug)) {
    // New DB-backed system
    $cause = getDonationCauseBySlug($causeSlug);
    if (!$cause) {
        $cause = getDonationCauseBySlug('general-donation');
    }
    $selectedType = $cause['slug'] ?? 'general-donation';
} elseif (!empty($selectedType) && array_key_exists($selectedType, $SEVA_TYPES)) {
    // Legacy $SEVA_TYPES fallback
    $cause = null;
    $seva = $SEVA_TYPES[$selectedType];
} else {
    // Default: try DB first, then fallback
    $cause = getDonationCauseBySlug('general-donation');
    if (!$cause) {
        $selectedType = 'general';
        $seva = $SEVA_TYPES['general'];
    } else {
        $selectedType = $cause['slug'];
    }
}

// If we have a DB cause, fetch its sevas (pricing tiers)
$groupedSevas = [];
$flatSevas = [];
$defaultAmount = 100;
if ($cause && isset($cause['id'])) {
    $groupedSevas = getCauseSevasGrouped($cause['id']);
    $flatSevas = getCauseSevas($cause['id']);
    $defaultAmount = !empty($flatSevas) ? (float)$flatSevas[0]['amount'] : (float)$cause['min_amount'];
}

// Use details from DB cause if available
$causeName = $cause ? $cause['title'] : ($seva['name'] ?? 'Donation');
$causeShortDesc = $cause ? ($cause['short_title'] ?? $cause['title']) : ($seva['short_desc'] ?? '');
$causeDesc = $cause ? ($cause['description'] ?? '') : ($seva['description'] ?? '');

// Category info for icon/label
$catInfo = $cause ? getCauseCategoryInfo($cause['category']) : ['label' => 'Donation', 'icon' => 'fa-hand-holding-heart'];
$causeIcon = $catInfo['icon'];
$causeCategory = $catInfo['label'];
$causeSubcategory = $cause ? ($cause['subcategory'] ?? '') : '';

// Image from DB or fallback
$causeImage = $cause && $cause['image_url'] 
    ? $cause['image_url'] 
    : 'https://picsum.photos/seed/' . ($cause['slug'] ?? 'donation') . '/1920/600';

$allowMonthly = $cause ? ($cause['allow_monthly'] ?? 0) : 0;
$defaultMode = $cause ? ($cause['default_mode'] ?? 'one_time') : 'one_time';
// Use cart_qty (add to cart with +/- buttons) for ALL donation pages by default
$formType = 'cart_qty';

// Rich content from new DB columns
$causeHistory = $cause ? ($cause['history'] ?? '') : '';
$causeSignificance = $cause ? ($cause['significance'] ?? '') : '';
$causeBenefits = $cause ? ($cause['benefits'] ?? '') : '';

// Time-bound info (for festivals/events)
$isTimeBound = $cause ? ($cause['is_time_bound'] ?? 0) : 0;
$startDate = $cause ? ($cause['start_date'] ?? '') : '';
$endDate = $cause ? ($cause['end_date'] ?? '') : '';

// Get mode from URL (overrides default)
$selectedMode = $_GET['mode'] ?? $defaultMode;
if (!in_array($selectedMode, ['one_time', 'monthly'])) {
    $selectedMode = 'one_time';
}

// Source tracking
$sourceType = $cause['page_type'] ?? 'direct';
$sourceSlug = $cause['page_slug'] ?? '';
$sourceUrl = $_SERVER['HTTP_REFERER'] ?? '';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo $causeImage; ?>');"></div>
  <div class="container">
    <h1 class="reveal"><?php echo htmlspecialchars($causeName); ?></h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>donate">Donate</a>
      <span>›</span>
      <span><?php echo htmlspecialchars($causeName); ?></span>
    </div>
  </div>
</section>

<!-- Donation Detail -->
<section class="page-content">
  <div class="container">
    <div class="donate-detail-layout cart-layout">
      <!-- Left Column - Info -->
      <div class="donate-detail-info">
        <?php if ($causeImage): ?>
        <!-- Main Cause Banner Image -->
        <div class="donate-image-wrap">
          <img src="<?php echo $causeImage; ?>" alt="<?php echo htmlspecialchars($causeName); ?>" class="donate-responsive-img">
        </div>
        <?php endif; ?>
        <div class="section-divider donate-divider-left">
          <span class="divider-icon"><i class="fas <?php echo $causeIcon; ?>"></i></span>
        </div>
        <span class="section-subtitle reveal donate-subtitle-left"><?php echo htmlspecialchars($causeShortDesc); ?></span>
        <h2 class="reveal"><?php echo htmlspecialchars($causeName); ?></h2>

        <!-- Category & Type Badges -->
        <div class="seva-meta-badges reveal">
          <span class="seva-badge seva-badge-category">
            <i class="fas <?php echo $causeIcon; ?>"></i>
            <?php echo htmlspecialchars($causeCategory); ?>
          </span>
          <?php if ($causeSubcategory): ?>
          <span class="seva-badge seva-badge-subcategory">
            <i class="fas fa-tag"></i>
            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $causeSubcategory))); ?>
          </span>
          <?php endif; ?>
          <?php if ($isTimeBound): ?>
          <span class="seva-badge seva-badge-time">
            <i class="fas fa-calendar-alt"></i>
            Seasonal
          </span>
          <?php endif; ?>
        </div>

        <!-- Time-bound Date Info -->
        <?php if ($isTimeBound && ($startDate || $endDate)): ?>
        <div class="seva-date-info reveal">
          <i class="fas fa-clock"></i>
          <span>
            <?php if ($startDate && $endDate): ?>
              <?php echo date('d M Y', strtotime($startDate)); ?> – <?php echo date('d M Y', strtotime($endDate)); ?>
            <?php elseif ($startDate): ?>
              Starts: <?php echo date('d M Y', strtotime($startDate)); ?>
            <?php elseif ($endDate): ?>
              Until: <?php echo date('d M Y', strtotime($endDate)); ?>
            <?php endif; ?>
          </span>
        </div>
        <?php endif; ?>

        <p class="seva-full-description reveal"><?php echo nl2br(htmlspecialchars($causeDesc)); ?></p>

        <!-- Offerings Sections (Cart, Quantity, Multi-item form types) OR Donation Tiers (Tiers form type) -->
        <?php if (in_array($formType, ['tiers', 'cart', 'cart_qty', 'quantity', 'multi_item']) && !empty($groupedSevas)): ?>
        <div class="seva-offerings-grid-wrap reveal" style="margin-top: var(--space-xl); margin-bottom: var(--space-2xl);">
          <h4 class="seva-offerings-heading">Select Your Seva</h4>
          <?php renderDonationSevaOptions($cause, $groupedSevas, $formType); ?>
        </div>
        <?php endif; ?>

        <!-- History Section -->
        <?php if ($causeHistory): ?>
        <div class="seva-rich-section reveal">
          <div class="seva-rich-icon"><i class="fas fa-history"></i></div>
          <div class="seva-rich-content">
            <h4>Historical Background</h4>
            <p><?php echo nl2br(htmlspecialchars($causeHistory)); ?></p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Significance Section -->
        <?php if ($causeSignificance): ?>
        <div class="seva-rich-section reveal">
          <div class="seva-rich-icon"><i class="fas fa-star"></i></div>
          <div class="seva-rich-content">
            <h4>Spiritual Significance</h4>
            <p><?php echo nl2br(htmlspecialchars($causeSignificance)); ?></p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Benefits Section -->
        <?php if ($causeBenefits): ?>
        <div class="seva-rich-section reveal">
          <div class="seva-rich-icon"><i class="fas fa-gift"></i></div>
          <div class="seva-rich-content">
            <h4>Blessings &amp; Benefits</h4>
            <p><?php echo nl2br(htmlspecialchars($causeBenefits)); ?></p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Sacred Promise -->
        <div class="donate-promise reveal">
          <div class="donate-promise-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" class="iskcon-logo-sm"></div>
          <div class="donate-promise-text">
            <h4>Our Sacred Promise</h4>
            <p>100% of your donation goes directly to the seva you choose. We are committed to transparency and responsible stewardship of every contribution made with love and devotion.</p>
          </div>
        </div>
      </div>

      <!-- Right Column - Donation Form -->
      <div class="donate-form-sticky reveal">
        <div class="donate-form-card">
          <h3><i class="fas fa-hand-holding-heart"></i> Donate with Love</h3>

          <form id="donationForm" autocomplete="on" data-razorpay='{"keyId":"<?php echo RAZORPAY_KEY_ID; ?>","currency":"<?php echo CURRENCY; ?>","siteName":"<?php echo SITE_NAME; ?>","testMode":<?php echo RAZORPAY_TEST_MODE ? 'true' : 'false'; ?>}'>
            <!-- Mode Toggle (one-time / monthly) -->
            <?php if ($allowMonthly): ?>
            <div class="donation-mode-toggle">
              <button type="button" class="mode-btn <?php echo $selectedMode === 'one_time' ? 'active' : ''; ?>"
                      data-mode="one_time" onclick="switchMode('one_time')">
                <i class="fas fa-hand-holding-heart"></i> One-Time
              </button>
              <button type="button" class="mode-btn <?php echo $selectedMode === 'monthly' ? 'active' : ''; ?>"
                      data-mode="monthly" onclick="switchMode('monthly')">
                <i class="fas fa-sync-alt"></i> Monthly
              </button>
            </div>
            <?php endif; ?>

            <!-- Hidden Fields (Donation options/sevas grid are rendered in the left column) -->
            <input type="hidden" id="causeId" value="<?php echo $cause['id'] ?? ''; ?>">
            <input type="hidden" id="causeSlug" value="<?php echo htmlspecialchars($selectedType); ?>">
            <input type="hidden" id="selectedAmount" value="<?php echo $defaultAmount; ?>">
            <input type="hidden" id="donationMode" value="<?php echo $selectedMode; ?>">
            <input type="hidden" id="formType" value="<?php echo $formType; ?>">
            <input type="hidden" id="sourceType" value="<?php echo htmlspecialchars($sourceType); ?>">
            <input type="hidden" id="sourceSlug" value="<?php echo htmlspecialchars($sourceSlug); ?>">
            <input type="hidden" id="sourceUrl" value="<?php echo htmlspecialchars($sourceUrl); ?>">
            <input type="hidden" id="selectedOfferingsList" name="special_instructions" value="">

            <!-- Donor Details -->
            <div class="form-fields">
              <!-- Donor Name -->
              <div class="form-group">
                <label for="donorName">Donor Name *</label>
                <input type="text" id="donorName" name="donor_name" placeholder="Enter your full name" required>
              </div>

              <!-- Purpose -->
              <div class="form-group">
                <label for="occasion">Purpose of donation</label>
                <textarea id="occasion" name="occasion" rows="2" maxlength="100" placeholder="e.g. Birthday blessings, Good health (max 100 chars)" class="occasion-textarea"></textarea>
              </div>

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

              <!-- PAN Card (optional, for 80G tax exemption) -->
              <div class="form-group">
                <label for="panNumber">PAN Card <span class="pan-label-hint">(optional, for 80G receipt)</span></label>
                <input type="text" id="panNumber" name="pan_number" placeholder="e.g. ABCDE1234F" maxlength="10" class="pan-input-upper">
              </div>
            </div>

              <!-- Selected Items Cart Summary Preview -->
              <div id="cartSummaryWrap" class="cart-summary-wrap">
                <div class="cart-summary-title">Selected Sevas:</div>
                <ul id="cartItemsList" class="cart-items-list">
                  <!-- Dynamically populated -->
                </ul>
              </div>

              <!-- Submit -->
              <button type="submit" class="btn btn-primary btn-lg donate-submit-btn" id="donateBtn">
                <i class="fas fa-lock"></i> Pay <span id="payAmount"><?php echo CURRENCY_SYMBOL . number_format($defaultAmount); ?></span>
              </button>

              <?php if ($allowMonthly): ?>
              <div class="monthly-notice" id="monthlyNotice" style="display: <?php echo $selectedMode === 'monthly' ? 'flex' : 'none'; ?>;">
                <i class="fas fa-info-circle"></i>
                <span>You authorize automated monthly charges of <span id="monthlyAmountDisplay"><?php echo CURRENCY_SYMBOL . number_format($defaultAmount); ?></span> via secure eMandate, eNACH, or UPI Autopay.</span>
              </div>
              <?php endif; ?>

              <div class="donate-secure">
                <i class="fas fa-shield-alt"></i>
                <span>Secured by <strong>Razorpay</strong> — 128-bit SSL Encrypted</span>
              </div>

              <div class="donate-methods">
                <span>We accept</span>
                <div class="payment-icons">
                  <span><i class="fas fa-credit-card"></i> Cards</span>
                  <span><i class="fas fa-mobile-alt"></i> UPI</span>
                  <span><i class="fas fa-university"></i> Net Banking / eMandate</span>
                  <span><i class="fas fa-wallet"></i> Wallet</span>
                </div>
              </div>
            </div>
          </form>

          <!-- Loading Overlay -->
          <div class="donate-loading" id="donateLoading">
            <div class="donate-loading-spinner"></div>
            <p>Processing your payment...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Related Causes -->
<?php if ($cause && isset($cause['id'])): 
  $relatedCauses = getRelatedCauses($cause, 3);
  if (!empty($relatedCauses)):
?>
<section class="related-causes-section">
  <div class="container">
    <div class="section-divider">
      <span class="divider-icon"><i class="fas fa-hands-helping"></i></span>
    </div>
    <span class="section-subtitle reveal related-text-center">Explore More</span>
    <h2 class="section-title reveal related-text-center">Related Seva Opportunities</h2>
    <p class="section-description reveal">
      Discover other meaningful ways to offer your support and devotion.
    </p>

    <div class="seva-card-grid">
      <?php foreach ($relatedCauses as $rel): 
        $relCatInfo = getCauseCategoryInfo($rel['category']);
      ?>
      <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($rel['slug']); ?>" class="seva-card reveal">
        <div class="seva-card-image">
          <img src="<?php echo $rel['image_url'] ?: 'https://picsum.photos/seed/' . $rel['slug'] . '/600/400'; ?>" alt="<?php echo htmlspecialchars($rel['title']); ?>" loading="lazy">
          <span class="seva-card-badge"><?php echo $rel['allow_monthly'] ? 'One-Time / Monthly' : 'One-Time'; ?></span>
        </div>
        <div class="seva-card-body">
          <div class="seva-card-icon"><i class="fas <?php echo $relCatInfo['icon']; ?>"></i></div>
          <h3><?php echo htmlspecialchars($rel['short_title'] ?: $rel['title']); ?></h3>
          <?php if ($rel['short_title'] && $rel['short_title'] !== $rel['title']): ?>
            <p class="seva-card-subtitle"><?php echo htmlspecialchars($rel['title']); ?></p>
          <?php endif; ?>
          <p><?php echo htmlspecialchars(mb_substr($rel['description'] ?? '', 0, 120)) . '...'; ?></p>
          <span class="btn btn-primary btn-sm">Donate Now <i class="fas fa-arrow-right"></i></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; endif; ?>

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
          <span>How is my donation used?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>100% of your donation goes directly to the seva or cause you choose. We maintain strict transparency and accountability, ensuring every rupee is utilized for its intended purpose — whether deity worship, prasadam distribution, festival celebrations, or temple development.</p>
        </div>
      </div>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>Will I receive a receipt?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>Yes, you will receive an email receipt with your transaction details immediately after successful payment. You can use this for your records and for tax exemption purposes under 80G of the Income Tax Act.</p>
        </div>
      </div>

      <?php if ($allowMonthly): ?>
      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>How does monthly subscription work?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>When you choose monthly mode, you will be charged the selected amount on the same day each month. You can cancel or modify your subscription at any time by contacting us. A receipt will be sent to your email each month.</p>
        </div>
      </div>
      <?php endif; ?>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>Can I donate via bank transfer?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>Absolutely! You can make a direct bank transfer using the account details provided in the Bank Transfer section below. Please send your transaction details to <strong>info@iskconseshadripuram.org</strong> so we can acknowledge your donation.</p>
        </div>
      </div>

      <div class="faq-item" onclick="toggleFaq(this)">
        <button class="faq-question">
          <span>Is my donation tax-exempt?</span>
          <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
        </button>
        <div class="faq-answer">
          <p>Yes, donations to ISKCON The Palace Temple of Lord Jagannath are eligible for tax exemption under Section 80G of the Income Tax Act. You will receive a receipt with all necessary details for claiming the exemption.</p>
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
      You can also make your donation directly via bank transfer. Please use your 
      donation purpose as the payment reference.
    </p>

    <div class="bank-details-card reveal">
      <div class="bank-details-header">
        <h3><?php echo htmlspecialchars($BANK_DETAILS['bank_name']); ?></h3>
        <p>Account Details for Direct Transfer</p>
      </div>
      <div class="bank-details-body">
        <div class="bank-detail-row">
          <span class="bank-detail-label">Account Name</span>
          <span class="bank-detail-value"><?php echo htmlspecialchars($BANK_DETAILS['account_name']); ?></span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">Account Number</span>
          <span class="bank-detail-value copyable" onclick="copyToClipboard('<?php echo htmlspecialchars($BANK_DETAILS['account_number']); ?>')">
            <?php echo htmlspecialchars($BANK_DETAILS['account_number']); ?>
            <i class="fas fa-copy"></i>
          </span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">Branch</span>
          <span class="bank-detail-value"><?php echo htmlspecialchars($BANK_DETAILS['branch']); ?></span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">IFSC Code</span>
          <span class="bank-detail-value copyable" onclick="copyToClipboard('<?php echo htmlspecialchars($BANK_DETAILS['ifsc_code']); ?>')">
            <?php echo htmlspecialchars($BANK_DETAILS['ifsc_code']); ?>
            <i class="fas fa-copy"></i>
          </span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">SWIFT Code</span>
          <span class="bank-detail-value"><?php echo htmlspecialchars($BANK_DETAILS['swift_code']); ?></span>
        </div>
        <div class="bank-detail-row">
          <span class="bank-detail-label">UPI ID</span>
          <span class="bank-detail-value copyable" onclick="copyToClipboard('<?php echo htmlspecialchars($BANK_DETAILS['upi_id']); ?>')">
            <?php echo htmlspecialchars($BANK_DETAILS['upi_id']); ?>
            <i class="fas fa-copy"></i>
          </span>
        </div>
        <div class="bank-note">
          <i class="fas fa-info-circle"></i> After making a bank transfer, please send your transaction details 
          to <strong>info@iskconseshadripuram.org</strong> so we can acknowledge your donation.
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Test Mode Notice -->
<?php if (RAZORPAY_TEST_MODE): ?>
<div class="test-mode-notice">
  <div class="container">
    <p>
      <i class="fas fa-flask"></i>
      <strong>Test Mode Active:</strong> No real payments will be processed.
      Use test card number <code>4111 1111 1111 1111</code> with any future expiry and CVV.
    </p>
  </div>
</div>
<?php endif; ?>

<script src="<?= asset('modules/Donation/assets/js/donate.js') ?>"></script>
<script>
function toggleFaq(el) {
  el.classList.toggle('active');
}
</script>

<?php include '../partials/footer.php'; ?>
