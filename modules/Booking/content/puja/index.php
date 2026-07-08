<?php
$pageTitle = 'Devotional Puja Offerings';
$metaDescription = 'Sponsor sacred puja offerings at ISKCON Seshadripuram, Bangalore. Offer archanas, garlands, and prayers to Sri Sri Radha Madhav, Gaura Nitai, Giriraja Sila, and Srila Prabhupada online.';
include '../../partials/header.php';
require_once '../../config.php';

// Define the puja options and details
$pujaOptions = [
  'sri-sri-radha-madhav' => [
    'name' => 'Sri Sri Radha Madhav Puja',
    'deity' => 'Sri Sri Radha Madhav',
    'price' => 1008,
    'description' => 'Offer a special archana, flower garlands, and prayers for the pleasure of Sri Sri Radha Madhav, the divine couple who bestow transcendental love and spiritual guidance.',
    'icon' => 'fa-om',
    'image' => 'assets/images/banners/puja_radha_madhav.jpg'
  ],
  'sri-sri-gaura-nitai' => [
    'name' => 'Sri Sri Gaura Nitai Puja',
    'deity' => 'Sri Sri Gaura Nitai',
    'price' => 501,
    'description' => 'Offer worship to Lord Chaitanya Mahaprabhu and Lord Nityananda. This offering invokes their ultimate mercy, bringing joy, spiritual strength, and purification.',
    'icon' => 'fa-hands-praying',
    'image' => 'assets/images/banners/puja_gaura_nitai.jpg'
  ],
  'sri-giriraja-sila' => [
    'name' => 'Sri Giriraja Sila Puja',
    'deity' => 'Sri Giriraja Sila',
    'price' => 351,
    'description' => 'Offer special prayers and worship to Govardhan Sila, the sacred stone from Govardhan Hill representing Lord Krishna Himself. Invokes protection, prosperity, and devotion.',
    'icon' => 'fa-mountain',
    'image' => 'assets/images/banners/puja_giriraja_sila.jpg'
  ],
  'sri-saligrama-sila' => [
    'name' => 'Sri Saligrama Sila Puja',
    'deity' => 'Sri Saligrama Sila',
    'price' => 351,
    'description' => 'Worship of the sacred Saligrama stone from the Gandaki River. This puja brings peace, eliminates obstacles, and fills the home with auspiciousness.',
    'icon' => 'fa-gem',
    'image' => 'assets/images/banners/puja_saligrama_sila.jpg'
  ],
  'guru-puja' => [
    'name' => 'Guru Puja Offering',
    'deity' => 'Guru puja',
    'price' => 251,
    'description' => 'Offer worship to His Divine Grace A.C. Bhaktivedanta Swami Prabhupada, the Founder-Acharya of ISKCON. Receive the blessings of the spiritual master to progress in bhakti.',
    'icon' => 'fa-user-tie',
    'image' => 'assets/images/banners/puja_guru.jpg'
  ],
  'anniversary' => [
    'name' => 'Anniversary Special Puja',
    'deity' => 'Anniversary',
    'price' => 1008,
    'description' => 'Celebrate your wedding anniversary or any special family milestone by offering prayers and obtaining the blessings of the Lord for a happy, spiritually-centered life.',
    'icon' => 'fa-heart',
    'image' => 'assets/images/banners/puja_anniversary.jpg'
  ],
  'birthday' => [
    'name' => 'Birthday Blessing Puja',
    'deity' => 'Birthday',
    'price' => 501,
    'description' => 'Begin another year of life with divine blessings. A special archana and prayers are offered in your name for good health, long life, and advancement in Krishna consciousness.',
    'icon' => 'fa-cake-candles',
    'image' => 'assets/images/banners/puja_birthday.jpg'
  ]
];
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Puja Offerings</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>booking">Booking</a>
      <span>›</span>
      <span>Puja Offerings</span>
    </div>
  </div>
</section>

<!-- Content Grid -->
<section class="page-content">
  <div class="container">
    <div class="section-divider"><span class="divider-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" class="iskcon-logo-divider"></span></div>

    <div class="reveal booking-section-center">
      <span class="section-subtitle">Devotional Service</span>
      <h2 class="section-title">Sacred Puja Services</h2>
      <p class="section-description booking-desc-constrained">
        Perform a sacred seva in your name or on behalf of your family. Choose from our listed pujas below to explore spiritual benefits, deities, and book online.
      </p>
    </div>

    <!-- Puja Listing Grid -->
    <div class="puja-list-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:var(--space-xl);margin-bottom:var(--space-3xl);">
      
      <?php foreach ($pujaOptions as $key => $puja): ?>
      <div class="puja-card reveal">
        <div class="puja-card-image" style="background-image: url('<?php echo BASE_URL . $puja['image']; ?>');">
          <div class="puja-card-badge">₹<?php echo number_format($puja['price']); ?></div>
        </div>
        <div class="puja-card-body">
          <div class="puja-card-icon"><i class="fas <?php echo $puja['icon']; ?>"></i></div>
          <h3><?php echo htmlspecialchars($puja['deity']); ?></h3>
          <p><?php echo htmlspecialchars($puja['description']); ?></p>
          <a href="booking/puja/<?php echo $key; ?>" class="btn btn-primary btn-sm">Offer Puja <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <?php endforeach; ?>

    </div>

  </div>
</section>

<!-- CSS Styling for Puja Listing -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/booking-utilities.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/puja-index.css') ?>">

<?php include '../../partials/footer.php'; ?>
