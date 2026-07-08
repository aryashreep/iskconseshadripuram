<?php
require_once '../../config.php';
require_once '../../includes/asset-helper.php';

$metaDescription = 'Book sacred puja offerings at ISKCON Seshadripuram, Bangalore. Select garlands, tulsi, dry fruits, sweets, attar, deepdan, and fruits for Sri Sri Radha Madhav, Gaura Nitai, and other deities.';

// Define the puja options and details
$pujaOptions = [
  'sri-sri-radha-madhav' => [
    'name' => 'Sri Sri Radha Madhav Puja',
    'deity' => 'Sri Sri Radha Madhav',
    'price' => 1008,
    'description' => 'The divine deities of Sri Sri Radha Madhav embody the eternal love of Radha and Krishna. Installed by His Divine Grace A.C. Bhaktivedanta Swami Prabhupada, these sacred deities inspire deep devotion and longing for Krishna\'s lotus feet. Their daily worship, arati, and seasonal festivals immerse visitors in the nectar of divine love (rasa). A glimpse of Radha-Madhav awakens the heart\'s purest devotion.',
    'icon' => 'fa-om',
    'image' => 'assets/images/banners/puja_radha_madhav.jpg'
  ],
  'sri-sri-gaura-nitai' => [
    'name' => 'Sri Sri Gaura Nitai Puja',
    'deity' => 'Sri Sri Gaura Nitai',
    'price' => 501,
    'description' => 'The radiant Gaura-Nitai deities embody the divine union of Lord Chaitanya Mahaprabhu and Lord Nityananda, whose compassionate mission was to freely distribute Krishna bhakti. Adorned in vibrant attire, Their joyful forms stand in the temple hall, blessing devotees with Their merciful gaze. These sacred deities symbolize the essence of sankirtan — congregational chanting of the Holy Names.',
    'icon' => 'fa-hands-praying',
    'image' => 'assets/images/banners/puja_gaura_nitai.jpg'
  ],
  'sri-giriraja-sila' => [
    'name' => 'Sri Giriraja Sila Puja',
    'deity' => 'Sri Giriraja Sila',
    'price' => 351,
    'description' => 'Sri Giriraja Sila is Govardhan Hill itself, lifted by Lord Krishna to protect His devotees in Vrindavan. Sila worship brings protection, happiness, and steady bhakti. Sponsoring offerings to Giriraja Sila invokes the mercy of Krishna\'s beloved hill, removing material and spiritual obstacles.',
    'icon' => 'fa-mountain',
    'image' => 'assets/images/banners/puja_giriraja_sila.jpg'
  ],
  'sri-saligrama-sila' => [
    'name' => 'Sri Saligrama Sila Puja',
    'deity' => 'Sri Saligrama Sila',
    'price' => 351,
    'description' => 'The sacred Saligrama Sila, sourced from the Gandaki River, represents Lord Vishnu. Worshiping the Sila removes obstacles, purifies the home, and invites peace and spiritual prosperity.',
    'icon' => 'fa-gem',
    'image' => 'assets/images/banners/puja_saligrama_sila.jpg'
  ],
  'guru-puja' => [
    'name' => 'Guru Puja Offering',
    'deity' => 'Guru puja',
    'price' => 251,
    'description' => 'Worship of Srila Prabhupada, the Founder-Acharya of ISKCON. Serving the spiritual master is the secret of success in bhakti, bringing spiritual clarity and progress.',
    'icon' => 'fa-user-tie',
    'image' => 'assets/images/banners/puja_guru.jpg'
  ],
  'anniversary' => [
    'name' => 'Anniversary Special Puja',
    'deity' => 'Anniversary',
    'price' => 1008,
    'description' => 'Sponsor prayers on your wedding anniversary to invoke the blessings of the Lord for a happy, spiritually-centered grihastha (household) life.',
    'icon' => 'fa-heart',
    'image' => 'assets/images/banners/puja_anniversary.jpg'
  ],
  'birthday' => [
    'name' => 'Birthday Blessing Puja',
    'deity' => 'Birthday',
    'price' => 501,
    'description' => 'Begin a new year of life with arati and archana in your name. Invokes longevity, protection, and spiritual focus.',
    'icon' => 'fa-cake-candles',
    'image' => 'assets/images/banners/puja_birthday.jpg'
  ]
];

// Get selected puja from slug
$slug = $_GET['slug'] ?? '';
if (!array_key_exists($slug, $pujaOptions)) {
  header('Location: ' . BASE_URL . 'booking/puja');
  exit;
}

$puja = $pujaOptions[$slug];
$pageTitle = $puja['name'] . ' - ISKCON Booking';
include '../../partials/header.php';

// Define the detailed offerings grid
$offeringCategories = [
  'Garlands' => [
    'title' => 'Offering of Divine Garland',
    'items' => [
      ['id' => 'g1', 'name' => 'Marigold Garland', 'price' => 201, 'icon' => 'fa-seedling'],
      ['id' => 'g2', 'name' => 'Tulsi Garland', 'price' => 251, 'icon' => 'fa-leaf'],
      ['id' => 'g3', 'name' => 'Naurangi Garland', 'price' => 251, 'icon' => 'fa-clover'],
      ['id' => 'g4', 'name' => 'Rose Garland', 'price' => 301, 'icon' => 'fa-spa'],
      ['id' => 'g5', 'name' => 'Mogra Garland', 'price' => 301, 'icon' => 'fa-sun'],
      ['id' => 'g6', 'name' => 'Lotus Garland', 'price' => 401, 'icon' => 'fa-certificate'],
      ['id' => 'g7', 'name' => 'Makhana Garland', 'price' => 501, 'icon' => 'fa-ellipsis'],
      ['id' => 'g8', 'name' => 'Orchid Garland', 'price' => 501, 'icon' => 'fa-fan']
    ]
  ],
  'Tulsi' => [
    'title' => 'Sacred Offering of Tulsi Leaves',
    'items' => [
      ['id' => 't1', 'name' => '21 leaves', 'price' => 101, 'icon' => 'fa-leaf'],
      ['id' => 't2', 'name' => '51 leaves', 'price' => 151, 'icon' => 'fa-leaf'],
      ['id' => 't3', 'name' => '108 leaves', 'price' => 151, 'icon' => 'fa-leaf'],
      ['id' => 't4', 'name' => '151 leaves', 'price' => 301, 'icon' => 'fa-leaf'],
      ['id' => 't5', 'name' => '251 leaves', 'price' => 501, 'icon' => 'fa-leaf']
    ]
  ],
  'DryFruits' => [
    'title' => 'Divine Dry Fruits Seva',
    'items' => [
      ['id' => 'df1', 'name' => 'Raisins', 'price' => 201, 'icon' => 'fa-bowl-rice'],
      ['id' => 'df2', 'name' => 'Almond', 'price' => 201, 'icon' => 'fa-bowl-rice'],
      ['id' => 'df3', 'name' => 'Cashew', 'price' => 201, 'icon' => 'fa-bowl-rice'],
      ['id' => 'df4', 'name' => 'Walnut', 'price' => 201, 'icon' => 'fa-bowl-rice'],
      ['id' => 'df5', 'name' => 'Pistachio', 'price' => 251, 'icon' => 'fa-bowl-rice']
    ]
  ],
  'Sweets' => [
    'title' => 'Madhur Samarpan - Offering of Special Sweets',
    'items' => [
      ['id' => 'sw1', 'name' => 'Coconut', 'price' => 151, 'icon' => 'fa-cookie-bite'],
      ['id' => 'sw2', 'name' => 'Khajoor', 'price' => 201, 'icon' => 'fa-cookie-bite'],
      ['id' => 'sw3', 'name' => 'Brijwasi Peda', 'price' => 251, 'icon' => 'fa-cookie-bite'],
      ['id' => 'sw4', 'name' => 'Besan Laddu', 'price' => 251, 'icon' => 'fa-cookie-bite'],
      ['id' => 'sw5', 'name' => 'Soan Papdi', 'price' => 301, 'icon' => 'fa-cookie-bite'],
      ['id' => 'sw6', 'name' => 'Honey', 'price' => 301, 'icon' => 'fa-jar'],
      ['id' => 'sw7', 'name' => 'Kaju Kesar Barfi', 'price' => 501, 'icon' => 'fa-cookie-bite']
    ]
  ],
  'Attar' => [
    'title' => 'Divine Fragrance - Attar Seva',
    'items' => [
      ['id' => 'at1', 'name' => 'Rose Attar', 'price' => 501, 'icon' => 'fa-eye-dropper'],
      ['id' => 'at2', 'name' => 'Chandan Attar', 'price' => 501, 'icon' => 'fa-eye-dropper'],
      ['id' => 'at3', 'name' => 'Khus Attar', 'price' => 501, 'icon' => 'fa-eye-dropper'],
      ['id' => 'at4', 'name' => 'Jasmine Attar', 'price' => 501, 'icon' => 'fa-eye-dropper'],
      ['id' => 'at5', 'name' => 'Mogra Attar', 'price' => 501, 'icon' => 'fa-eye-dropper'],
      ['id' => 'at6', 'name' => 'Lavender Attar', 'price' => 501, 'icon' => 'fa-eye-dropper']
    ]
  ],
  'Deepdan' => [
    'title' => 'Special Deepdan - Light of Devotion',
    'items' => [
      ['id' => 'dd1', 'name' => '3 Deepdan', 'price' => 151, 'icon' => 'fa-fire-burner'],
      ['id' => 'dd2', 'name' => '5 Deepdan', 'price' => 251, 'icon' => 'fa-fire-burner'],
      ['id' => 'dd3', 'name' => '7 Deepdan', 'price' => 351, 'icon' => 'fa-fire-burner'],
      ['id' => 'dd4', 'name' => '9 Deepdan', 'price' => 451, 'icon' => 'fa-fire-burner'],
      ['id' => 'dd5', 'name' => '11 Deepdan', 'price' => 551, 'icon' => 'fa-fire-burner']
    ]
  ],
  'Fruits' => [
    'title' => 'Fruits Seva',
    'items' => [
      ['id' => 'fr1', 'name' => 'Banana', 'price' => 151, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr2', 'name' => 'Papaya', 'price' => 201, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr3', 'name' => 'Peach', 'price' => 201, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr4', 'name' => 'Grapes', 'price' => 251, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr5', 'name' => 'Mango', 'price' => 251, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr6', 'name' => 'Coconut Water', 'price' => 251, 'icon' => 'fa-glass-water'],
      ['id' => 'fr7', 'name' => 'Nashpati (Pear)', 'price' => 301, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr8', 'name' => 'Kiwi', 'price' => 301, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr9', 'name' => 'Pineapple', 'price' => 301, 'icon' => 'fa-apple-whole'],
      ['id' => 'fr10', 'name' => 'Avocado', 'price' => 351, 'icon' => 'fa-apple-whole']
    ]
  ]
];
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL . $puja['image']; ?>');"></div>
  <div class="container">
    <h1 class="reveal"><?php echo htmlspecialchars($puja['deity']); ?></h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>booking">Booking</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>booking/puja">Puja Offerings</a>
      <span>›</span>
      <span><?php echo htmlspecialchars($puja['deity']); ?></span>
    </div>
  </div>
</section>

<!-- Content Body -->
<section class="page-content">
  <div class="container">
    <div id="pujaDetailApp" data-config='{"razorpay":{"keyId":"<?php echo RAZORPAY_KEY_ID; ?>","currency":"<?php echo CURRENCY; ?>","siteName":"<?php echo SITE_NAME; ?>","testMode":<?php echo RAZORPAY_TEST_MODE ? 'true' : 'false'; ?>},"pujaSlug":"<?php echo $slug; ?>","pujaName":"<?php echo htmlspecialchars($puja['name'], ENT_QUOTES); ?>"}'></div>
    <div class="section-divider"><span class="divider-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" class="iskcon-logo-divider"></span></div>

    <!-- Layout Grid -->
    <div class="booking-grid" style="display:grid;grid-template-columns:1.5fr 1fr;gap:var(--space-2xl);align-items:start;">

      <!-- Left Column - Deity Image, Info, and Offerings Selection -->
      <div class="booking-left reveal">

        <!-- Main Deity Banner Image -->
        <div class="img-wrap-rounded" style="margin-bottom:var(--space-lg);">
          <img src="<?php echo BASE_URL . $puja['image']; ?>" alt="<?php echo htmlspecialchars($puja['deity']); ?>" class="img-responsive" style="max-height:400px;object-fit:cover;">
        </div>

        <h2 class="booking-heading"><?php echo htmlspecialchars($puja['deity']); ?></h2>
        <p class="booking-desc-text" style="margin-bottom:var(--space-2xl);">
          <?php echo htmlspecialchars($puja['description']); ?>
        </p>

        <!-- Offerings Sections -->
        <?php foreach ($offeringCategories as $catKey => $category): ?>
          <div style="margin-bottom:var(--space-2xl);">
            <h3 class="booking-section-heading" style="font-size:var(--font-size-md);">
              <?php echo htmlspecialchars($category['title']); ?>
            </h3>

            <div class="offering-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:var(--space-md);">
              <?php foreach ($category['items'] as $item): ?>
                <div class="offering-card">
                  <div class="offering-icon-box"><i class="fas <?php echo $item['icon']; ?>"></i></div>
                  <div class="offering-name"><?php echo htmlspecialchars($item['name']); ?></div>
                  <div class="offering-price">₹<?php echo number_format($item['price']); ?></div>
                  <button type="button" class="add-to-puja-btn" id="btn-<?php echo $item['id']; ?>" onclick="toggleOffering('<?php echo $item['id']; ?>', '<?php echo htmlspecialchars($item['name']); ?>', <?php echo $item['price']; ?>)">Add to Puja</button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <!-- Right Column - Sticky Sidebar Form -->
      <div class="booking-right reveal booking-sticky-sidebar">
        <div class="card-white-padded" style="position:relative;">
          <h3 class="booking-section-heading" style="font-size:var(--font-size-md);border-bottom:1px solid var(--border);">
            Booking Form
          </h3>

          <form id="pujaCartBookingForm">
            <!-- Hidden inputs -->
            <input type="hidden" id="selectedPujaName" name="puja_name" value="<?php echo htmlspecialchars($puja['name']); ?>">
            <input type="hidden" id="totalAmountVal" name="amount" value="0">
            <input type="hidden" id="selectedOfferingsList" name="special_instructions" value="">

            <div class="form-fields">

              <!-- Participant Info -->
              <div class="form-group">
                <label for="donorName">Participant Name *</label>
                <input type="text" id="donorName" name="donor_name" placeholder="Enter name" required>
              </div>

              <!-- Gotra & Relation -->
              <div class="form-row-fields">
                <div class="form-group">
                  <label for="gotra">Gotra</label>
                  <input type="text" id="gotra" name="gotra" placeholder="e.g. Kasyapa">
                </div>
                <div class="form-group">
                  <label for="relation">Relation</label>
                  <select id="relation" name="relation">
                    <option value="Self">Self</option>
                    <option value="Family">Family</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Son">Son</option>
                    <option value="Daughter">Daughter</option>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                  </select>
                </div>
              </div>

              <!-- Purpose -->
              <div class="form-group">
                <label for="occasion">Purpose for Puja / Offering</label>
                <textarea id="occasion" name="occasion" rows="2" maxlength="100" placeholder="e.g. Birthday blessings, Good health (max 100 chars)"></textarea>
              </div>

              <!-- Puja Dates -->
              <div class="form-group">
                <label for="pujaDate">Select Puja Date *</label>
                <input type="date" id="pujaDate" name="puja_date" required min="<?php echo date('Y-m-d'); ?>" onchange="recalculateTotal()">
              </div>

              <!-- Date Helper Presets -->
              <div class="date-presets-section">
                <div class="date-preset-label">📅 Multi-day Presets</div>

                <div class="date-preset-group">
                  <span class="date-preset-title">Days wise:</span>
                  <div class="preset-buttons">
                    <button type="button" class="preset-btn" onclick="applyDatePreset(3, '3 Days')">3 Days</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(7, '1 Week')">1 Week</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(10, '10 Days')">10 Days</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(15, '15 Days')">15 Days</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(30, '1 Month')">1 Month</button>
                  </div>
                </div>

                <div class="date-preset-group">
                  <span class="date-preset-title">Tithis / Festivals:</span>
                  <div class="preset-buttons">
                    <button type="button" class="preset-btn" onclick="applyDatePreset(1, 'Ekadashi')">Ekadashi</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(1, 'Purnima')">Purnima</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(1, 'Amavasya')">Amavasya</button>
                  </div>
                </div>
              </div>

              <div class="dashed-divider"></div>

              <!-- Contact Info -->
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

              <!-- Selected Items Cart Summary Preview -->
              <div id="cartSummaryWrap" class="cart-summary-wrap">
                <div class="cart-summary-title">Selected Sevas:</div>
                <ul id="cartItemsList" class="cart-items-list">
                  <!-- Dynamically populated -->
                </ul>
              </div>

              <!-- Dynamic Total Price Banner -->
              <div class="booking-cream-bg">
                <div style="font-weight:600;color:var(--text-dark);">Total Amount:</div>
                <div class="total-amount-label" id="totalAmountLabel">₹0.00</div>
              </div>

              <!-- Submit -->
              <button type="submit" class="btn btn-primary btn-lg btn-full-width" id="bookBtn" disabled>
                Choose Any Offering
              </button>

              <div class="donate-secure" style="text-align:center;font-size:11px;color:var(--text-light);margin-top:var(--space-sm);">
                <i class="fas fa-shield-alt"></i> Secured by <strong>Razorpay</strong> — 128-bit SSL Encrypted
              </div>

            </div>
          </form>

          <!-- Loading Overlay -->
          <div class="loading-overlay" id="bookLoading">
            <div class="loading-spinner"></div>
            <p class="loading-text">Processing your booking...</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Custom Styles for Multi-Seva Grid -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/booking-utilities.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/puja-detail.css') ?>">

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="<?= asset('assets/js/puja-detail.js') ?>"></script>

<?php include '../../partials/footer.php'; ?>