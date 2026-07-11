<?php
$metaDescription = 'Sponsor sacred Vedic fire sacrifices online at ISKCON Seshadripuram, Bangalore. Choose from tiers including basic participation, sankalpa seva, and purnahuti seva for various yagyas.';
require_once '../../config.php';

// Define the yagya options and details
$yagyaOptions = [
  'sri-sudarshan-narasimha-yagya' => [
    'name' => 'Sri Sudarshan Narasimha Yagya',
    'deity' => 'Sri Sudarshan Narasimha Yagya',
    'subtitle' => 'Sri Sudarshan Narasimha Yagya For Protection, Peace & Divine Blessings',
    'description' => 'Sri Sudarshan Narasimha Yagya is a sacred fire ritual performed to invoke the divine blessings of Lord Narasimha, the fierce avatar of Lord Vishnu, and the Sudarshan Chakra, His powerful disc weapon. This yagya is conducted with Vedic mantras and offerings into the holy fire, seeking protection, removal of obstacles, and spiritual upliftment.',
    'image' => 'assets/images/banners/yagya_sudarshan_narasimha.jpg',
    'date' => 'Flexible / Call to Schedule',
    'time' => 'Morning (Flexible)',
    'location' => 'ISKCON Seshadripuram Yagya-Shala',
    'hymns' => [
      'Sankalpa (Sacred Vow)',
      'Swasti Vachan',
      'Shanti Panchakam',
      'Purvanga Krama: Guru Dhyan, Asan Sthapana, Cahanti Puja, Dravya Shuddhi, Pushpa Shuddhi, Atma Shuddhi and dig bandhan',
      'Kalash Sthapana: Kalash Puja and Vishvaksena Puja',
      'Kushandika: Rekhabhuksana, Trinadi Shodhan and Agni Sammukh Karana',
      'Panch Sukta (The Five Sacred Vedic Hymns)',
      'Narasimha Kavach (The Divine Armor of Lord Narasimha)',
      '108 Narasimha Gayatri Mantra',
      '108 Narasimha Namavali (Sacred Names of Lord Narasimha)',
      '108 Narasimha Mahamantra',
      '108 Sudarshan Namavali (Sacred Names of Sudarshan Chakra)',
      '108 Sudarshan Gayatri Mantra',
      'Sudarshan Shatakam (100 Verses Glorifying the Sudarshan Chakra)',
      '108 Sudarshan Mahamantra'
    ],
    'who_participate' => [
      'You often feel low, heavy, anxious, or disturbed without clear reasons.',
      'Despite effort, success slips away—whether in career, marriage, health, or finances.',
      'You or your family are affected by black magic, evil eye, or negative energies.',
      'Sudden fights, health issues, or bad luck entering your home.',
      'You feel stuck in court cases, false allegations, or power struggles.',
      'If you\'re trapped in karmic patterns or planetary afflictions (graha doshas).',
      'If your spiritual practice feels dry, unfocused, or blocked.',
      'Repeated illnesses, surgeries, or mysterious health problems.',
      'You simply want to move forward with the Lord\'s blessings and peaceful heart.'
    ],
    'benefits_spiritual' => [
      'Deepens focus in meditation and chanting, calming the restless mind.',
      'Strengthens inner resilience when facing repeated failures or fear.',
      'Clears invisible spiritual blockages that keep life stuck or energy drained.',
      'Offers divine protection against envy, harmful intentions, and opposition.',
      'Restores emotional strength, faith, and a heartfelt connection with God.',
      'Brings divine support when feeling spiritually alone or unheard.',
      'Uplifts consciousness beyond anger, distraction, and self-sabotaging habits.'
    ],
    'benefits_life' => [
      'Brings relief from recurring health issues and restores balance in the body and mind.',
      'Helps resolve court cases, disputes, and situations of injustice.',
      'Removes obstacles in career, business, and finances, allowing smooth progress.',
      'Cleanses and uplifts the home environment, creating peace, lightness, and spiritual energy.',
      'Heals emotional tension within the family and promotes harmony and mutual understanding.',
      'Soothes anxiety, dissolves fear, and protects against sudden or unknown problems.',
      'Shields from negative energies, draining influences, and unseen disturbances.'
    ]
  ],
  'vastu-yagya' => [
    'name' => 'Vastu Yagya & Bhoomi Puja',
    'deity' => 'Vastu Purusha',
    'subtitle' => 'Vastu Yagya For Harmony, Peace & Positive Energy',
    'description' => 'Sanctify your living or workspace by invoking Vastu Purusha. This yagya corrects directional flaws (Vastu doshas) and invites positive energies, auspiciousness, and prosperity into the premises.',
    'image' => 'assets/images/banners/yagya_vastu.jpg',
    'date' => 'First Sunday Every Month',
    'time' => '10:00 AM - 12:30 PM',
    'location' => 'Temple Yagya-Shala',
    'hymns' => [
      'Vastu Purusha Sankalpa',
      'Bhoomi Shuddhi Mantra',
      '108 Vastu Purusha Namavali',
      'Vastu Gayatri Mantra',
      'Vastu Shanti Patha',
      'Purvanga Puja',
      'Havan and Purnahuti'
    ],
    'who_participate' => [
      'Entering a new home, office, or plot.',
      'Experiencing continuous stress, illnesses, or arguments at home.',
      'Unexplainable financial drain or lack of progress after moving to a new space.',
      'Wanting to remove architectural flaws (Vastu doshas).'
    ],
    'benefits_spiritual' => [
      'Cleanses the home atmosphere from past negative residual vibrations.',
      'Establishes a peaceful, spiritually conducive environment for sadhana.'
    ],
    'benefits_life' => [
      'Brings health, wealth, and general prosperity to the inhabitants.',
      'Corrects minor Vastu defects without physical demolitions.'
    ]
  ],
  'dhanvantari-yagya' => [
    'name' => 'Dhanvantari Yagya',
    'deity' => 'Lord Dhanvantari',
    'subtitle' => 'Dhanvantari Yagya For Good Health, Healing & Longevity',
    'description' => 'Dedicated to Lord Dhanvantari, the incarnation of Lord Vishnu and the father of Ayurveda. Sponser to pray for speedy recovery from chronic illnesses and to maintain robust physical health.',
    'image' => 'assets/images/banners/yagya_dhanvantari.jpg',
    'date' => 'Weekly on Saturdays',
    'time' => '08:30 AM - 10:30 AM',
    'location' => 'Temple Yagya-Shala',
    'hymns' => [
      'Dhanvantari Sankalpa',
      '108 Dhanvantari Ashtottara Sata Namavali',
      'Dhanvantari Gayatri Mantra',
      'Arogya Suktam',
      'Ayushya Suktam',
      'Maha Mrityunjaya Mantra'
    ],
    'who_participate' => [
      'Suffering from chronic health conditions or mental stress.',
      'Undergoing major medical treatments or surgeries.',
      'Seeking longevity, youthfulness, and protection from sudden illnesses.'
    ],
    'benefits_spiritual' => [
      'Harmonizes the subtle life force energies (prana) in the body.',
      'Calms the mind, creating a strong foundation for spiritual focus.'
    ],
    'benefits_life' => [
      'Promotes rapid recovery and therapeutic healing.',
      'Brings vitality, strength, and longevity.'
    ]
  ],
  'navagraha-yagya' => [
    'name' => 'Navagraha Yagya',
    'deity' => 'Nine Planets',
    'subtitle' => 'Navagraha Yagya For Planetary Pacification & Success',
    'description' => 'A sacred fire sacrifice dedicated to the nine astrological planets (Grahas). Sponsoring this homa pacifies malefic planetary configurations, yielding peace, balance, and success in work and life.',
    'image' => 'assets/images/banners/yagya_navagraha.jpg',
    'date' => 'Monthly on Sankranti',
    'time' => '09:00 AM - 12:00 PM',
    'location' => 'Temple Yagya-Shala',
    'hymns' => [
      'Navagraha Shanti Patha',
      'Navagraha Suktam',
      'Individual Planet Gayatri Mantras',
      'Navagraha Ashtottara Sata Namavali',
      'Homa offerings for nine deities'
    ],
    'who_participate' => [
      'Experiencing continuous obstacles in career or business.',
      'Facing emotional imbalances, confusion, or delays in marriage.',
      'Going through malefic Dasa or Gochara transits.'
    ],
    'benefits_spiritual' => [
      'Dissolves karmic blocks, strengthening inner trust in the Lord.',
      'Creates balance and protection in spiritual practices.'
    ],
    'benefits_life' => [
      'Brings success in career, relationship harmony, and peace of mind.',
      'Pacifies the negative transit effects of Saturn, Rahu, and Ketu.'
    ]
  ],
  'ayushya-yagya' => [
    'name' => 'Ayushya Yagya',
    'deity' => 'Lord of Life',
    'subtitle' => 'Ayushya Homa For Longevity & Birthday Blessings',
    'description' => 'Performed on birthdays and family milestones, this yagya invokes longevity, physical vitality, and spiritual protection for the person starting another year of life.',
    'image' => 'assets/images/banners/yagya_ayushya.jpg',
    'date' => 'Custom / Booking Base',
    'time' => 'Flexible Timings',
    'location' => 'Temple Yagya-Shala',
    'hymns' => [
      'Ayushya Sankalpa',
      'Ayur Suktam',
      'Ayushya Mantra Japa',
      'Navagraha Shanti mantras',
      'Homa offerings'
    ],
    'who_participate' => [
      'Devotees celebrating birthdays (especially children).',
      'Family members recovering from a critical age milestone.',
      'Seeking healthy longevity to perform devotional service.'
    ],
    'benefits_spiritual' => [
      'Aligns the soul\'s life duration with devotional service (bhakti).',
      'Purifies past karmas linked with health blockages.'
    ],
    'benefits_life' => [
      'Invokes robust health and immunity.',
      'Brings auspiciousness, protection from sudden accidents.'
    ]
  ]
];

// Get selected yagya from slug
$slug = $_GET['slug'] ?? '';
if (!array_key_exists($slug, $yagyaOptions)) {
  header('Location: ' . BASE_URL . 'booking/yagya');
  exit;
}

$yagya = $yagyaOptions[$slug];
$pageTitle = $yagya['name'] . ' - ISKCON Booking';
$pageType = 'booking';
include '../../partials/header.php';

// Define Yagya Sponsoring Tiers
$sponsoringTiers = [
  [
    'id' => 'tier1',
    'name' => 'Basic Participation',
    'price' => 501,
    'desc' => 'Offer your humble oblation and join the sacred yagya through devotion.',
    'includes' => ['Offering', 'Oblation']
  ],
  [
    'id' => 'tier2',
    'name' => 'Devotional Offering',
    'price' => 1101,
    'desc' => 'Sanctify your worship with fruits offered at the lotus feet of the Lord.',
    'includes' => ['Offering', 'Oblation', 'Fruits']
  ],
  [
    'id' => 'tier3',
    'name' => 'Sankalpa Seva',
    'price' => 2101,
    'desc' => 'Adorn your vow with flowers, symbolizing purity and surrender.',
    'includes' => ['Offering', 'Oblation', 'Fruits', 'Flowers']
  ],
  [
    'id' => 'tier4',
    'name' => 'Shraddha Bhakti Seva',
    'price' => 3101,
    'desc' => 'Offer Tulsi and sacred cloth, receiving the Lord\'s blessings as yagya prasada.',
    'includes' => ['Offering', 'Oblation', 'Fruits', 'Flowers', 'Holy Cloth', 'Tulsi'],
    'returns' => 'Yagya Ashes, Flowers, Dry Fruits',
    'delivery' => 'Shipping team will contact for address'
  ],
  [
    'id' => 'tier5',
    'name' => 'Maha Yagya Sankalpa',
    'price' => 4101,
    'desc' => 'Fuel the yagya with pure ghee and firewood, receiving the sacred Pavitra.',
    'includes' => ['Offering', 'Oblation', 'Fruits', 'Flowers', 'Holy Cloth', 'Tulsi', 'Firewood', 'Desi Pure Ghee', 'Brahmabhoj'],
    'returns' => 'Yagya Ashes, Flowers, Dry Fruits, Pavitra',
    'delivery' => 'Shipping team will contact for address'
  ],
  [
    'id' => 'tier6',
    'name' => 'Purnahuti Seva',
    'price' => 5101,
    'desc' => 'Complete the sacred fire with full purnahuti and receive a protective Kavach.',
    'includes' => ['Offering', 'Oblation', 'Fruits', 'Flowers', 'Holy Cloth', 'Tulsi', 'Firewood', 'Desi Pure Ghee', 'Brahmabhoj', 'Purnahuti Plate'],
    'returns' => 'Yagya Ashes, Flowers, Dry Fruits, Yagya Cloth or Kavach',
    'delivery' => 'Shipping team will contact for address'
  ],
  [
    'id' => 'tier7',
    'name' => 'Divya Ashirvad Seva',
    'price' => 7101,
    'desc' => 'Complete the sacred fire, host Brahmabhoj, and receive full kavach and deity key ring.',
    'includes' => ['Offering', 'Oblation', 'Fruits', 'Flowers', 'Holy Cloth', 'Tulsi', 'Firewood', 'Desi Pure Ghee', 'Brahmabhoj', 'Purnahuti Plate'],
    'returns' => 'Yagya Ashes, Flowers, Dry Fruits, Key Ring, Yagya Cloth and Kavach',
    'delivery' => 'Shipping team will contact for address'
  ]
];
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL . $yagya['image']; ?>');"></div>
  <div class="container">
    <h1 class="reveal"><?php echo htmlspecialchars($yagya['name']); ?></h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>booking">Booking</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>booking/yagya">Yagyas</a>
      <span>›</span>
      <span><?php echo htmlspecialchars($yagya['name']); ?></span>
    </div>
  </div>
</section>

<!-- Content Body -->
<section class="page-content">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">🔥</span></div>

    <!-- Yagya Date and Timing Badge -->
    <div class="reveal booking-info-badge">
      <span style="font-size:24px;color:var(--primary);">🔥</span>
      <div>
        <span class="yagya-badge-label" style="display:block;font-size:11px;text-transform:uppercase;color:var(--text-light);font-weight:600;letter-spacing:1px;">Yagya Date &amp; Timing</span>
        <strong class="yagya-badge-value" style="color:var(--text-dark);font-size:var(--font-size-md);"><?php echo $yagya['date']; ?> &amp; <?php echo $yagya['time']; ?></strong>
      </div>
    </div>

    <!-- Main Banner Image -->
    <div class="img-wrap-rounded" style="margin-bottom:var(--space-2xl);">
      <img src="<?php echo BASE_URL . $yagya['image']; ?>" alt="<?php echo htmlspecialchars($yagya['name']); ?>" class="img-responsive" style="max-height:450px;object-fit:cover;">
    </div>

    <!-- Description & Badges Grid -->
    <div class="booking-grid" style="display:grid;grid-template-columns:1.5fr 1fr;gap:var(--space-2xl);align-items:start;margin-bottom:var(--space-3xl);">

      <!-- Left: Description -->
      <div class="reveal">
        <h2 class="booking-heading"><?php echo htmlspecialchars($yagya['subtitle']); ?></h2>
        <p class="booking-desc-text" style="margin:0;">
          <?php echo htmlspecialchars($yagya['description']); ?>
        </p>
      </div>

      <!-- Right: Info Badges Grid -->
      <div class="reveal" style="display:grid;grid-template-columns:1fr;gap:var(--space-sm);">
        <div class="yagya-info-badge"><i class="fas fa-map-location-dot"></i> <span><strong>Yagya Place:</strong> <?php echo $yagya['location']; ?></span></div>
        <div class="yagya-info-badge"><i class="fas fa-calendar-days"></i> <span><strong>Date &amp; Time:</strong> <?php echo $yagya['date']; ?> (<?php echo $yagya['time']; ?>)</span></div>
        <div class="yagya-info-badge"><i class="fas fa-phone-alt"></i> <span><strong>Helpline:</strong> +91 99860 77269</span></div>
        <div class="yagya-info-badge"><i class="fab fa-whatsapp"></i> <span><strong>WhatsApp:</strong> +91 99860 77269</span></div>
      </div>

    </div>

    <!-- Sponsoring Tiers Section -->
    <div style="margin-bottom:var(--space-3xl);">
      <h3 style="font-family:var(--font-heading);color:var(--text-dark);text-align:center;margin-bottom:var(--space-2xl);font-size:var(--font-size-2xl);">Participate in <?php echo htmlspecialchars($yagya['name']); ?> Online</h3>

      <div class="tier-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:var(--space-xl);">
        <?php foreach ($sponsoringTiers as $tier): ?>
          <div class="yagya-tier-card reveal">
            <div>
              <div class="flex-between" style="margin-bottom:var(--space-sm);">
                <h4 style="font-family:var(--font-heading);color:var(--text-dark);font-size:var(--font-size-md);margin:0;"><?php echo htmlspecialchars($tier['name']); ?></h4>
              </div>
              <p style="color:var(--text-light);font-size:var(--font-size-xs);line-height:1.6;margin-bottom:var(--space-md);"><?php echo htmlspecialchars($tier['desc']); ?></p>

              <div class="flex-center" style="flex-wrap:wrap;gap:5px;margin-bottom:var(--space-md);">
                <?php foreach ($tier['includes'] as $inc): ?>
                  <span class="yagya-inc-badge"><i class="fas fa-circle-check"></i> <?php echo htmlspecialchars($inc); ?></span>
                <?php endforeach; ?>
              </div>

              <?php if (isset($tier['returns'])): ?>
                <div style="font-size:11px;color:var(--text-light);margin-bottom:var(--space-xs);">
                  <strong>Divine Returns:</strong> <?php echo htmlspecialchars($tier['returns']); ?>
                </div>
              <?php endif; ?>

              <?php if (isset($tier['delivery'])): ?>
                <div style="font-size:10px;color:var(--primary);margin-bottom:var(--space-md);display:flex;align-items:center;gap:4px;">
                  <i class="fas fa-truck-fast"></i> <?php echo htmlspecialchars($tier['delivery']); ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="dashed-divider flex-between" style="margin:var(--space-md) 0;padding-top:var(--space-md);">
              <div style="font-size:var(--font-size-lg);font-weight:700;color:var(--primary);">₹<?php echo number_format($tier['price']); ?></div>
              <button type="button" class="btn btn-primary btn-sm" onclick="openBookingModal('<?php echo htmlspecialchars($tier['name']); ?>', <?php echo $tier['price']; ?>)">Book Now</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Hymns & Who Should Participate Grid -->
    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:var(--space-3xl);margin-bottom:var(--space-3xl);" class="booking-grid reveal">

      <!-- Left: Hymns -->
      <div>
        <h3 class="booking-sub-heading">
          Sacred Vedic Hymns &amp; Mantras
        </h3>
        <ul class="checkmark-list">
          <?php foreach ($yagya['hymns'] as $hymn): ?>
            <li class="checkmark-item">
              <i class="fas fa-circle-check"></i>
              <span><?php echo htmlspecialchars($hymn); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Right: Who Participate -->
      <div>
        <h3 class="booking-sub-heading">
          Who Should Participate?
        </h3>
        <ul class="checkmark-list">
          <?php foreach ($yagya['who_participate'] as $who): ?>
            <li style="display:flex;gap:10px;align-items:start;font-size:var(--font-size-sm);color:var(--text);line-height:1.6;">
              <span style="color:var(--primary);margin-top:2px;"><i class="fas fa-circle-check"></i></span>
              <span><?php echo htmlspecialchars($who); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>

    <!-- Benefits Grid -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-3xl);margin-bottom:var(--space-3xl);" class="booking-grid reveal">

      <!-- Left: Spiritual Benefits -->
      <div>
        <h3 class="booking-sub-heading">
          Spiritual Benefits
        </h3>
        <ul class="checkmark-list">
          <?php foreach ($yagya['benefits_spiritual'] as $b): ?>
            <li style="display:flex;gap:10px;align-items:start;font-size:var(--font-size-sm);color:var(--text);line-height:1.6;">
              <span style="color:var(--primary);margin-top:2px;"><i class="fas fa-circle-check"></i></span>
              <span><?php echo htmlspecialchars($b); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Right: Life Changing Benefits -->
      <div>
        <h3 class="booking-sub-heading">
          Life Changing Benefits
        </h3>
        <ul class="checkmark-list">
          <?php foreach ($yagya['benefits_life'] as $b): ?>
            <li style="display:flex;gap:10px;align-items:start;font-size:var(--font-size-sm);color:var(--text);line-height:1.6;">
              <span style="color:var(--primary);margin-top:2px;"><i class="fas fa-circle-check"></i></span>
              <span><?php echo htmlspecialchars($b); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>

  </div>
</section>

<!-- Sponsoring Booking Form Modal -->
<div id="bookingModal" class="modal-overlay">
  <div class="modal-content">

    <div class="modal-header">
      <h4 id="modalYagyaTitle">Maha Yagya Sankalpa</h4>
      <span class="modal-close" onclick="closeBookingModal()">&times;</span>
    </div>

    <form id="yagyaBookingModalForm" class="modal-body">
      <input type="hidden" id="selectedYagyaName" name="puja_name" value="<?php echo htmlspecialchars($yagya['name']); ?>">
      <input type="hidden" id="selectedTierName" name="tier_name" value="">
      <input type="hidden" id="selectedAmount" name="amount" value="0">

      <div class="form-fields">

        <!-- Devotee / Participant Info -->
        <div class="form-group">
          <label for="donorName">Participant Name *</label>
          <input type="text" id="donorName" placeholder="Enter full name" required>
        </div>

        <div class="form-row-fields">
          <div class="form-group">
            <label for="gotra">Gotra</label>
            <input type="text" id="gotra" placeholder="e.g. Kasyapa">
          </div>
          <div class="form-group">
            <label for="relation">Relation</label>
            <select id="relation">
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

        <div class="form-group">
          <label for="occasion">Purpose for Yagya / Sankalpa</label>
          <input type="text" id="occasion" placeholder="e.g. Birthday, Good health, Family well-being">
        </div>

        <!-- Date Selection -->
        <div class="form-group">
          <label for="yagyaDate">Select Yagya Date *</label>
          <input type="date" id="yagyaDate" required min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="dashed-divider"></div>

        <!-- Contact Info -->
        <div class="form-row-fields">
          <div class="form-group">
            <label for="donorEmail">Email Address *</label>
            <input type="email" id="donorEmail" placeholder="name@domain.com" required>
          </div>
          <div class="form-group">
            <label for="donorPhone">WhatsApp Phone *</label>
            <input type="tel" id="donorPhone" placeholder="+91-98765" required>
          </div>
        </div>

        <!-- PAN Card (optional, for 80G tax exemption) -->
        <div class="form-group">
          <label for="panNumber">PAN Card <span class="pan-label-hint">(optional, for 80G receipt)</span></label>
          <input type="text" id="panNumber" name="pan_number" placeholder="e.g. ABCDE1234F" maxlength="10" class="pan-input-upper">
        </div>

        <!-- Dynamic Total Price Banner -->
        <div class="booking-cream-bg">
          <div style="font-weight:600;color:var(--text-dark);">Sponsoring Amount:</div>
          <div class="total-amount-label" id="modalAmountLabel">₹0.00</div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-full-width" id="bookBtn">
          <i class="fas fa-lock"></i> Sponsor &amp; Pay <span id="payBtnAmount">₹0.00</span>
        </button>

      </div>
    </form>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="bookLoading">
      <div class="loading-spinner"></div>
      <p class="loading-text">Processing your booking...</p>
    </div>

  </div>
</div>

<!-- Custom CSS for Yagya details -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/booking-utilities.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/yagya-detail.css') ?>">

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  function openBookingModal(tierName, price) {
    document.getElementById('modalYagyaTitle').textContent = '<?php echo htmlspecialchars($yagya['name']); ?> - ' + tierName;
    document.getElementById('selectedTierName').value = tierName;
    document.getElementById('selectedAmount').value = price;
    document.getElementById('modalAmountLabel').textContent = '₹' + price.toLocaleString('en-IN');
    document.getElementById('payBtnAmount').textContent = '₹' + price.toLocaleString('en-IN');

    // Open today date as default
    var dateField = document.getElementById('yagyaDate');
    if (!dateField.value) {
      var today = new Date().toISOString().split('T')[0];
      dateField.value = today;
    }

    document.getElementById('bookingModal').style.display = 'flex';
  }

  function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
  }

  // Pass config to JS
  var RAZORPAY_CONFIG = {
    keyId: '<?php echo RAZORPAY_KEY_ID; ?>',
    currency: '<?php echo CURRENCY; ?>',
    siteName: '<?php echo SITE_NAME; ?>',
    testMode: <?php echo RAZORPAY_TEST_MODE ? 'true' : 'false'; ?>,
  };

  document.getElementById('yagyaBookingModalForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var bookLoading = document.getElementById('bookLoading');
    var bookBtn = document.getElementById('bookBtn');

    // Get values
    var yagyaName = document.getElementById('selectedYagyaName').value;
    var tierName = document.getElementById('selectedTierName').value;
    var finalAmount = parseInt(document.getElementById('selectedAmount').value, 10);

    var dateVal = document.getElementById('yagyaDate').value;
    var occasionVal = document.getElementById('occasion').value;
    var gotraVal = document.getElementById('gotra').value;
    var relationVal = document.getElementById('relation').value;

    var donorNameVal = document.getElementById('donorName').value;
    var donorEmailVal = document.getElementById('donorEmail').value;
    var donorPhoneVal = document.getElementById('donorPhone').value;
    var panNumberVal = document.getElementById('panNumber') ? document.getElementById('panNumber').value.trim() : '';

    if (finalAmount <= 0) {
      alert('Invalid sponsoring amount.');
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
      puja_type: yagyaName, // maps to puja_type column in DB
      puja_date: dateVal,
      occasion: occasionVal ? relationVal + ': ' + occasionVal : relationVal + ' offering',
      person_name: donorNameVal.trim(),
      gotra: gotraVal.trim(),
      rashi: relationVal,
      nakshatra: tierName,
      special_instructions: 'Sponsoring: ' + tierName + ' (' + yagyaName + ')'
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
          description: yagyaName + ' (' + tierName + ')',
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
            verifyBookingPayment(response, yagyaName + ' (' + tierName + ')', finalAmount * 100, donorNameVal.trim(), donorEmailVal.trim(), donorPhoneVal.trim());
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
          window.location.href = '../../donate/payment-failed.php?cause=' + encodeURIComponent(yagyaName);
        });
        rzp1.open();
      })
      .catch(function(error) {
        if (bookLoading) bookLoading.style.display = 'none';
        if (bookBtn) bookBtn.disabled = false;
        alert('Error: ' + error.message);
      });
  });

  function verifyBookingPayment(response, yagyaName, amount, name, email, phone) {
    fetch('../../api/verify-payment.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          razorpay_order_id: response.razorpay_order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature: response.razorpay_signature,
          cause_slug: 'booking-yagya',
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
            '&cause=' + encodeURIComponent(yagyaName);
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