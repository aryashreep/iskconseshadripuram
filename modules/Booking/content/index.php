<?php
$pageTitle = 'Devotional Bookings & Services';
$metaDescription = 'Book puja, yagya, guest house stay, and pilgrimage yatras at the official ISKCON temple in Seshadripuram, Bangalore. Sponsor sacred offerings and fire sacrifices online.';
$pageType = 'booking';
include '../partials/header.php';
require_once '../config.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Devotional Bookings</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Booking Gateway</span>
    </div>
  </div>
</section>

<!-- Content Gateway -->
<section class="page-content">
  <div class="container">
    <div class="section-divider"><span class="divider-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" class="iskcon-logo-divider"></span></div>
    
    <div class="reveal booking-section-center">
      <span class="section-subtitle">Gateway to Devotional Services</span>
      <h2 class="section-title">Temple Bookings &amp; Offerings</h2>
      <p class="section-description booking-desc-constrained">
        Welcome to the ISKCON Seshadripuram booking portal. Sponsor a sacred puja, reserve your temple stay, 
        book a Vedic fire sacrifice, or register for upcoming spiritual tours.
      </p>
    </div>

    <!-- Booking Gateway Cards Grid -->
    <div class="gateway-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:var(--space-xl);margin-bottom:var(--space-3xl);">
      
      <!-- Card 1: Book Puja -->
      <div class="gateway-card reveal">
        <div class="gateway-card-image" style="background-image:url('<?php echo BASE_URL; ?>assets/images/banners/puja_radha_madhav.jpg');">
          <div class="gateway-card-overlay"></div>
          <div class="gateway-card-icon"><i class="fas fa-om"></i></div>
        </div>
        <div class="gateway-card-body">
          <h3>Puja Offerings</h3>
          <p>Offer special archanas, garlands, and prayers to Sri Sri Radha Madhav, Sri Sri Gaura Nitai, Giriraja Sila, and Srila Prabhupada.</p>
          <a href="booking/puja" class="btn btn-primary btn-sm">Book Puja <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>

      <!-- Card 2: Book Yagya -->
      <div class="gateway-card reveal">
        <div class="gateway-card-image" style="background-image:url('<?php echo BASE_URL; ?>assets/images/banners/yagya_sudarshan_narasimha.jpg');">
          <div class="gateway-card-overlay"></div>
          <div class="gateway-card-icon"><i class="fas fa-fire"></i></div>
        </div>
        <div class="gateway-card-body">
          <h3>Yagyas &amp; Homas</h3>
          <p>Invoke protection, health, and prosperity for family milestones or home sanctifications via sacred fire sacrifices.</p>
          <a href="booking/yagya" class="btn btn-primary btn-sm">Book Yagya <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>

      <!-- Card 3: Guest House -->
      <div class="gateway-card reveal">
        <div class="gateway-card-image" style="background-image:url('<?php echo BASE_URL; ?>assets/images/banners/guest_house_room.jpg');">
          <div class="gateway-card-overlay"></div>
          <div class="gateway-card-icon"><i class="fas fa-bed"></i></div>
        </div>
        <div class="gateway-card-body">
          <h3>Guest House Stay</h3>
          <p>Experience a spiritual retreat inside the temple campus. Located foot-steps away from the main deities and restaurant.</p>
          <a href="booking/guest-house" class="btn btn-primary btn-sm">Reserve Room <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>

      <!-- Card 4: Yatras -->
      <div class="gateway-card reveal">
        <div class="gateway-card-image" style="background-image:url('<?php echo BASE_URL; ?>assets/images/banners/banner7.jpg');">
          <div class="gateway-card-overlay"></div>
          <div class="gateway-card-icon"><i class="fas fa-route"></i></div>
        </div>
        <div class="gateway-card-body">
          <h3>Yatra Pilgrimages</h3>
          <p>Register for upcoming tours and holy pilgrimages to sacred dhams accompanied by senior devotees and kirtan.</p>
          <a href="yatra/" class="btn btn-primary btn-sm">Explore Yatras <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Custom Styling for Gateway Cards -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/booking-utilities.css') ?>">
<link rel="stylesheet" href="<?= asset('modules/Donation/assets/css/booking-index.css') ?>">

<?php include '../partials/footer.php'; ?>
