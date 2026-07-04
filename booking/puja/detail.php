<?php
require_once '../../config.php';

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
    <div class="section-divider"><span class="divider-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" style="height:24px;width:auto;"></span></div>

    <!-- Layout Grid -->
    <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:var(--space-2xl);align-items:start;" class="booking-grid">

      <!-- Left Column - Deity Image, Info, and Offerings Selection -->
      <div class="booking-left reveal">

        <!-- Main Deity Banner Image -->
        <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);margin-bottom:var(--space-lg);">
          <img src="<?php echo BASE_URL . $puja['image']; ?>" alt="<?php echo htmlspecialchars($puja['deity']); ?>" style="width:100%;height:auto;max-height:400px;object-fit:cover;display:block;">
        </div>

        <h2 style="font-family:var(--font-heading);color:var(--text-dark);margin-top:0;margin-bottom:var(--space-md);"><?php echo htmlspecialchars($puja['deity']); ?></h2>
        <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.8;margin-bottom:var(--space-2xl);">
          <?php echo htmlspecialchars($puja['description']); ?>
        </p>

        <!-- Offerings Sections -->
        <?php foreach ($offeringCategories as $catKey => $category): ?>
          <div style="margin-bottom:var(--space-2xl);">
            <h3 style="font-family:var(--font-heading);color:var(--text-dark);border-bottom:2px solid var(--border);padding-bottom:var(--space-xs);margin-bottom:var(--space-md);font-size:var(--font-size-md);">
              <?php echo htmlspecialchars($category['title']); ?>
            </h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:var(--space-md);">
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
      <div class="booking-right reveal" style="position:sticky;top:100px;">
        <div class="donate-form-card" style="position:relative;background:var(--white);border-radius:var(--radius-lg);padding:var(--space-xl);box-shadow:var(--shadow-sm);border:1px solid var(--border);">
          <h3 style="font-family:var(--font-heading);color:var(--text-dark);margin-top:0;margin-bottom:var(--space-lg);font-size:var(--font-size-md);border-bottom:1px solid var(--border);padding-bottom:var(--space-sm);">
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

                <div class="date-preset-group" style="margin-top:8px;">
                  <span class="date-preset-title">Tithis / Festivals:</span>
                  <div class="preset-buttons">
                    <button type="button" class="preset-btn" onclick="applyDatePreset(1, 'Ekadashi')">Ekadashi</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(1, 'Purnima')">Purnima</button>
                    <button type="button" class="preset-btn" onclick="applyDatePreset(1, 'Amavasya')">Amavasya</button>
                  </div>
                </div>
              </div>

              <div style="border-top:1px dashed var(--border);margin:var(--space-md) 0;padding-top:var(--space-sm);"></div>

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
                <label for="panNumber">PAN Card <span style="color:var(--text-light);font-weight:400;font-size:11px;">(optional, for 80G receipt)</span></label>
                <input type="text" id="panNumber" name="pan_number" placeholder="e.g. ABCDE1234F" maxlength="10" style="text-transform:uppercase;">
              </div>

              <!-- Selected Items Cart Summary Preview -->
              <div id="cartSummaryWrap" style="display:none;font-size:12px;background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius-md);padding:10px;margin-bottom:var(--space-md);">
                <div style="font-weight:600;color:var(--text-dark);margin-bottom:4px;">Selected Sevas:</div>
                <ul id="cartItemsList" style="padding-left:15px;margin:0;color:var(--text-light);max-height:100px;overflow-y:auto;">
                  <!-- Dynamically populated -->
                </ul>
              </div>

              <!-- Dynamic Total Price Banner -->
              <div style="background:var(--cream);border-radius:var(--radius-md);padding:var(--space-md);display:flex;justify-content:between;align-items:center;margin:var(--space-md) 0;border:1px solid var(--border);">
                <div style="font-weight:600;color:var(--text-dark);">Total Amount:</div>
                <div style="font-size:var(--font-size-xl);font-family:var(--font-heading);color:var(--primary);font-weight:700;" id="totalAmountLabel">₹0.00</div>
              </div>

              <!-- Submit -->
              <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;display:flex;align-items:center;gap:10px;" id="bookBtn" disabled>
                Choose Any Offering
              </button>

              <div class="donate-secure" style="text-align:center;font-size:11px;color:var(--text-light);margin-top:var(--space-sm);">
                <i class="fas fa-shield-alt"></i> Secured by <strong>Razorpay</strong> — 128-bit SSL Encrypted
              </div>

            </div>
          </form>

          <!-- Loading Overlay -->
          <div class="donate-loading" id="bookLoading" style="display:none;position:absolute;inset:0;background:rgba(255,255,255,0.85);z-index:10;border-radius:var(--radius-lg);flex-direction:column;justify-content:center;align-items:center;">
            <div class="donate-loading-spinner" style="width:40px;height:40px;border:3px solid var(--border);border-top:3px solid var(--primary);border-radius:50%;animation:spin 1s linear infinite;"></div>
            <p style="margin-top:var(--space-sm);font-weight:600;color:var(--text-dark);font-size:13px;">Processing your booking...</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Custom Styles for Multi-Seva Grid -->
<style>
  .offering-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: var(--space-md) var(--space-sm);
    text-align: center;
    transition: all var(--transition-base);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
  }

  .offering-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
    border-color: var(--primary-light);
  }

  .offering-icon-box {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background: var(--cream-light);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 4px;
    color: var(--primary);
    font-size: 20px;
    transition: all var(--transition-base);
  }

  .offering-card:hover .offering-icon-box {
    background: var(--primary);
    color: var(--white);
    border-color: var(--primary);
  }

  .offering-name {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-dark);
    line-height: 1.3;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .offering-price {
    font-size: var(--font-size-xs);
    color: var(--primary);
    font-weight: 700;
  }

  .add-to-puja-btn {
    width: 100%;
    padding: 5px 8px;
    font-size: 11px;
    border: 1px solid var(--primary);
    color: var(--primary);
    background: transparent;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    transition: all var(--transition-fast);
    margin-top: 4px;
  }

  .add-to-puja-btn:hover {
    background: var(--primary);
    color: var(--white);
  }

  .add-to-puja-btn.added {
    background: #2e7d32;
    color: var(--white);
    border-color: #2e7d32;
  }

  /* Date presets styling */
  .date-presets-section {
    background: var(--light);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 10px;
    margin-top: 10px;
  }

  .date-preset-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 6px;
  }

  .date-preset-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .date-preset-title {
    font-size: 10px;
    color: var(--text-light);
  }

  .preset-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
  }

  .preset-btn {
    padding: 3px 8px;
    font-size: 10px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 4px;
    cursor: pointer;
    color: var(--text);
    font-weight: 500;
    transition: all var(--transition-fast);
  }

  .preset-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
  }

  .preset-btn.active {
    background: var(--gradient-primary);
    color: var(--white);
    border-color: transparent;
  }
</style>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  var selectedOfferings = {};
  var dateMultiplier = 1;
  var presetName = '';
  var currentPujaSlug = '<?php echo $slug; ?>';
  var currentPujaName = '<?php echo htmlspecialchars($puja['name'], ENT_QUOTES); ?>';

  /* Sync local offerings to global cart */
  function syncPujaCart() {
    // Build cart source
    var source = {
      mode: 'puja',
      type: 'puja',
      slug: currentPujaSlug,
      title: currentPujaName,
      formType: 'puja'
    };

    // Build items
    var items = [];
    for (var key in selectedOfferings) {
      items.push({
        key: key,
        itemId: key,
        name: selectedOfferings[key].name,
        qty: 1,
        unitAmount: selectedOfferings[key].price,
        lineTotal: selectedOfferings[key].price * dateMultiplier
      });
    }

    // Build context
    var context = {
      dateMultiplier: dateMultiplier,
      presetName: presetName || '',
      pujaDate: document.getElementById('pujaDate') ? document.getElementById('pujaDate').value : '',
      gotra: document.getElementById('gotra') ? document.getElementById('gotra').value : '',
      relation: document.getElementById('relation') ? document.getElementById('relation').value : 'Self',
      occasion: document.getElementById('occasion') ? document.getElementById('occasion').value : ''
    };

    // Check compatibility or clear
    if (typeof ISJMCart !== 'undefined') {
      var currentCart = ISJMCart.get();
      if (currentCart.mode && !ISJMCart.isCompatible(source)) {
        // Prompt to replace
        if (!confirm('Your cart already contains items from another seva. Clear cart and add this puja?')) {
          return false;
        }
        ISJMCart.clear();
      }

      if (items.length > 0) {
        ISJMCart.replaceFromPage(source, items, context);
      } else {
        // No items selected, but don't clear cart if it's from the same source
        // Actually, if same source and no items, let's keep previous state
      }
    }
    return true;
  }

  function toggleOffering(id, name, price) {
    var btn = document.getElementById('btn-' + id);
    if (selectedOfferings[id]) {
      // Remove
      delete selectedOfferings[id];
      btn.classList.remove('added');
      btn.textContent = 'Add to Puja';
    } else {
      // Add
      selectedOfferings[id] = {
        name: name,
        price: price
      };
      btn.classList.add('added');
      btn.textContent = 'Added';
    }
    recalculateTotal();
    syncPujaCart();
  }

  function applyDatePreset(days, label) {
    // Toggle preset class
    document.querySelectorAll('.preset-btn').forEach(function(btn) {
      btn.classList.remove('active');
    });

    // Toggle or apply
    if (dateMultiplier === days && presetName === label) {
      dateMultiplier = 1;
      presetName = '';
    } else {
      dateMultiplier = days;
      presetName = label;

      // Highlight the active button
      if (event && event.target) event.target.classList.add('active');
    }

    // Pre-fill date with today if not selected
    var dateField = document.getElementById('pujaDate');
    if (!dateField.value) {
      var today = new Date().toISOString().split('T')[0];
      dateField.value = today;
    }

    recalculateTotal();
    syncPujaCart();
  }

  function recalculateTotal() {
    var baseTotal = 0;
    var itemsList = [];

    for (var key in selectedOfferings) {
      baseTotal += selectedOfferings[key].price;
      itemsList.push(selectedOfferings[key].name);
    }

    var finalTotal = baseTotal * dateMultiplier;
    document.getElementById('totalAmountVal').value = finalTotal;
    document.getElementById('totalAmountLabel').textContent = '₹' + finalTotal.toLocaleString('en-IN');

    // Update selected offerings lists
    var summaryWrap = document.getElementById('cartSummaryWrap');
    var listElement = document.getElementById('cartItemsList');
    var instructionsInput = document.getElementById('selectedOfferingsList');

    if (itemsList.length > 0) {
      summaryWrap.style.display = 'block';
      listElement.innerHTML = '';
      itemsList.forEach(function(name) {
        var li = document.createElement('li');
        li.textContent = name;
        listElement.appendChild(li);
      });

      var instructions = itemsList.join(', ');
      if (presetName) {
        instructions += ' (' + presetName + ' duration)';
      }
      instructionsInput.value = instructions;

      // Enable button
      var bookBtn = document.getElementById('bookBtn');
      bookBtn.disabled = false;
      bookBtn.textContent = 'Book & Pay ₹' + finalTotal.toLocaleString('en-IN');
    } else {
      summaryWrap.style.display = 'none';
      instructionsInput.value = '';

      // Disable button
      var bookBtn = document.getElementById('bookBtn');
      bookBtn.disabled = true;
      bookBtn.textContent = 'Choose Any Offering';
    }
  }

  // Pass config to JS
  var RAZORPAY_CONFIG = {
    keyId: '<?php echo RAZORPAY_KEY_ID; ?>',
    currency: '<?php echo CURRENCY; ?>',
    siteName: '<?php echo SITE_NAME; ?>',
    testMode: <?php echo RAZORPAY_TEST_MODE ? 'true' : 'false'; ?>,
  };

  document.getElementById('pujaCartBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var bookLoading = document.getElementById('bookLoading');
    var bookBtn = document.getElementById('bookBtn');

    // Get values
    var pujaName = document.getElementById('selectedPujaName').value;
    var finalAmount = parseInt(document.getElementById('totalAmountVal').value, 10);

    var dateVal = document.getElementById('pujaDate').value;
    var occasionVal = document.getElementById('occasion').value;
    var gotraVal = document.getElementById('gotra').value;
    var relationVal = document.getElementById('relation').value;
    var specialInstructionsVal = document.getElementById('selectedOfferingsList').value;

    var donorNameVal = document.getElementById('donorName').value;
    var donorEmailVal = document.getElementById('donorEmail').value;
    var donorPhoneVal = document.getElementById('donorPhone').value;
    var panNumberVal = document.getElementById('panNumber') ? document.getElementById('panNumber').value.trim() : '';

    if (finalAmount <= 0) {
      alert('Please select at least one puja offering.');
      return;
    }
    if (!dateVal) {
      alert('Please select a starting date.');
      return;
    }

    // Show loading
    if (bookLoading) bookLoading.style.display = 'flex';
    if (bookBtn) bookBtn.disabled = true;

    var payload = {
      amount: finalAmount * 100, // paise
      donor_name: donorNameVal.trim(),
      donor_email: donorEmailVal.trim(),
      donor_phone: donorPhoneVal.trim(),
      pan_number: panNumberVal,
      puja_type: pujaName,
      puja_date: dateVal,
      occasion: occasionVal ? relationVal + ': ' + occasionVal : relationVal + ' offering',
      person_name: donorNameVal.trim(),
      gotra: gotraVal.trim(),
      rashi: relationVal,
      nakshatra: presetName || 'Standard',
      special_instructions: specialInstructionsVal
    };

    // Create Order API
    fetch('../../api/create-booking-order.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(function(response) {
        if (!response.ok) {
          return response.json().then(function(err) {
            throw new Error(err.error || 'Failed to create order');
          });
        }
        return response.json();
      })
      .then(function(result) {
        // Open Razorpay Checkout Modal
        var options = {
          key: RAZORPAY_CONFIG.keyId,
          amount: result.amount,
          currency: result.currency,
          name: RAZORPAY_CONFIG.siteName,
          description: pujaName,
          order_id: result.order_id,
          prefill: {
            name: donorNameVal.trim(),
            email: donorEmailVal.trim(),
            contact: donorPhoneVal.trim()
          },
          theme: {
            color: '#c86b1f'
          },
          handler: function(response) {
            verifyBookingPayment(response, pujaName, finalAmount * 100, donorNameVal.trim(), donorEmailVal.trim(), donorPhoneVal.trim());
          },
          modal: {
            ondismiss: function() {
              if (bookLoading) bookLoading.style.display = 'none';
              if (bookBtn) bookBtn.disabled = false;
            }
          }
        };

        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function() {
          window.location.href = '../../donate/payment-failed.php?cause=' + encodeURIComponent(pujaName);
        });
        rzp1.open();
      })
      .catch(function(error) {
        if (bookLoading) bookLoading.style.display = 'none';
        if (bookBtn) bookBtn.disabled = false;
        alert('Error: ' + error.message);
      });
  });

  function verifyBookingPayment(response, pujaName, amount, name, email, phone) {
    fetch('../../api/verify-payment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          razorpay_order_id: response.razorpay_order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature: response.razorpay_signature,
          cause_slug: 'booking-puja',
          seva_id: null,
          donation_mode: 'one_time',
          amount: amount,
          donor_name: name,
          donor_email: email,
          donor_phone: phone
        })
      })
      .then(function(res) {
        if (!res.ok) {
          window.location.href = '../../donate/payment-failed.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id);
          return null;
        }
        return res.json();
      })
      .then(function(data) {
        if (data && data.success) {
          window.location.href = '../../donate/payment-success.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id) +
            '&order_id=' + encodeURIComponent(response.razorpay_order_id) +
            '&amount=' + encodeURIComponent(amount) +
            '&cause=' + encodeURIComponent(pujaName);
        } else if (data) {
          window.location.href = '../../donate/payment-failed.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id);
        }
      })
      .catch(function() {
        window.location.href = '../../donate/payment-failed.php?payment_id=' + encodeURIComponent(response.razorpay_payment_id) +
          '&order_id=' + encodeURIComponent(response.razorpay_order_id);
      });
  }
</script>

<?php include '../../partials/footer.php'; ?>