<?php
$pageTitle = 'Sacred Yagya Services';
$metaDescription = 'Sponsor sacred Vedic fire sacrifices (yagya and homa) at the official ISKCON temple in Seshadripuram, Bangalore. Sudarshan Narasimha, Vastu, Dhanvantari, Navagraha, and Ayushya yagyas available.';
include '../../partials/header.php';
require_once '../../config.php';

// Define the yagya options and details
$yagyaOptions = [
  'sri-sudarshan-narasimha-yagya' => [
    'name' => 'Sri Sudarshan Narasimha Yagya',
    'deity' => 'Sri Sudarshan Narasimha Yagya',
    'price' => 501,
    'description' => 'A powerful fire ritual to invoke the blessings of Lord Narasimha, the fierce avatar of Lord Vishnu, and Lord Sudarshana, the holy weapon disc. Invokes peace, protection, and divine energy.',
    'icon' => 'fa-sun',
    'image' => 'assets/images/banners/yagya_sudarshan_narasimha.jpg'
  ],
  'vastu-yagya' => [
    'name' => 'Vastu Yagya & Bhoomi Puja',
    'deity' => 'Vastu Yagya & Bhoomi Puja',
    'price' => 501,
    'description' => 'Sanctify a new home, land, or business space by pacifying Vastu Purusha (the deity of architecture). Invokes peace, prosperity, and removes negative energies.',
    'icon' => 'fa-house',
    'image' => 'assets/images/banners/yagya_vastu.jpg'
  ],
  'dhanvantari-yagya' => [
    'name' => 'Dhanvantari Yagya',
    'deity' => 'Dhanvantari Yagya',
    'price' => 501,
    'description' => 'A sacred fire sacrifice dedicated to Lord Dhanvantari, the divine physician. Invoked for physical wellness, recovery from illness, and longevity of life.',
    'icon' => 'fa-heart-pulse',
    'image' => 'assets/images/banners/yagya_dhanvantari.jpg'
  ],
  'navagraha-yagya' => [
    'name' => 'Navagraha Yagya',
    'deity' => 'Navagraha Yagya',
    'price' => 501,
    'description' => 'A specialized homa performed to pacify planetary configurations. Sponser to seek blessings for balanced living, peace of mind, and career growth.',
    'icon' => 'fa-globe',
    'image' => 'assets/images/banners/yagya_navagraha.jpg'
  ],
  'ayushya-yagya' => [
    'name' => 'Ayushya Yagya',
    'deity' => 'Ayushya Yagya',
    'price' => 501,
    'description' => 'Seek longevity, robust health, and spiritual protection on birthdays or milestones by sponsoring this sacred birthday fire sacrifice.',
    'icon' => 'fa-hourglass-half',
    'image' => 'assets/images/banners/yagya_ayushya.jpg'
  ]
];
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner4.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Yagya &amp; Homas</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <a href="<?php echo BASE_URL; ?>booking">Booking</a>
      <span>›</span>
      <span>Yagya &amp; Homas</span>
    </div>
  </div>
</section>

<!-- Content Grid -->
<section class="page-content">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">🔥</span></div>

    <div class="reveal booking-section-center">
      <span class="section-subtitle">Sacred Fire Sacrifices</span>
      <h2 class="section-title">Yagya &amp; Homa Sponsoring</h2>
      <p class="section-description booking-desc-constrained">
        Perform a sacred fire sacrifice in your name or on behalf of your family. Choose from our listed yagyas below to explore spiritual benefits, hymns, and book online.
      </p>
    </div>

    <!-- Yagya Listing Grid -->
    <div class="yagya-list-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:var(--space-xl);margin-bottom:var(--space-3xl);">
      
      <?php foreach ($yagyaOptions as $key => $yagya): ?>
      <div class="yagya-card reveal">
        <div class="yagya-card-image" style="background-image: url('<?php echo BASE_URL . $yagya['image']; ?>');">
          <div class="yagya-card-badge">Starts at ₹<?php echo number_format($yagya['price']); ?></div>
        </div>
        <div class="yagya-card-body">
          <div class="yagya-card-icon"><i class="fas <?php echo $yagya['icon']; ?>"></i></div>
          <h3><?php echo htmlspecialchars($yagya['deity']); ?></h3>
          <p><?php echo htmlspecialchars($yagya['description']); ?></p>
          <a href="booking/yagya/<?php echo $key; ?>" class="btn btn-primary btn-sm">Sponsor Yagya <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      <?php endforeach; ?>

    </div>

  </div>
</section>

<!-- CSS Styling for Yagya Listing -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/booking-utilities.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/yagya-index.css') ?>">

<?php include '../../partials/footer.php'; ?>
