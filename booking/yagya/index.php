<?php
$pageTitle = 'Sacred Yagya Services';
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

    <div class="reveal" style="text-align:center;margin-bottom:var(--space-3xl);">
      <span class="section-subtitle">Sacred Fire Sacrifices</span>
      <h2 class="section-title">Yagya &amp; Homa Sponsoring</h2>
      <p class="section-description" style="max-width:750px;margin:0 auto;">
        Perform a sacred fire sacrifice in your name or on behalf of your family. Choose from our listed yagyas below to explore spiritual benefits, hymns, and book online.
      </p>
    </div>

    <!-- Yagya Listing Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:var(--space-xl);margin-bottom:var(--space-3xl);" class="yagya-list-grid">
      
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
<style>
.yagya-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all var(--transition-base);
}

.yagya-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-light);
}

.yagya-card-image {
  height: 200px;
  background-size: cover;
  background-position: center;
  position: relative;
}

.yagya-card-badge {
  position: absolute;
  top: var(--space-md);
  right: var(--space-md);
  background: var(--gradient-primary);
  color: var(--white);
  padding: 4px 12px;
  border-radius: 50px;
  font-weight: 700;
  font-size: var(--font-size-xs);
  box-shadow: var(--shadow-sm);
}

.yagya-card-body {
  padding: var(--space-xl);
  text-align: center;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-sm);
}

.yagya-card-icon {
  width: 50px;
  height: 50px;
  background: var(--cream);
  color: var(--primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  margin-top: calc(-1 * (var(--space-xl) + 25px));
  border: 4px solid var(--white);
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base);
}

.yagya-card:hover .yagya-card-icon {
  background: var(--primary);
  color: var(--white);
  transform: scale(1.05);
}

.yagya-card-body h3 {
  font-family: var(--font-heading);
  color: var(--text-dark);
  font-size: var(--font-size-lg);
  margin: var(--space-xs) 0 0 0;
}

.yagya-card-body p {
  color: var(--text-light);
  font-size: var(--font-size-sm);
  line-height: 1.6;
  margin: 0;
  flex-grow: 1;
}

.yagya-card-body .btn {
  width: 100%;
  justify-content: center;
  margin-top: var(--space-sm);
}
</style>

<?php include '../../partials/footer.php'; ?>
