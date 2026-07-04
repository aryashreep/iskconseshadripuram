<?php
$pageTitle = 'Devotional Bookings & Services';
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
    <div class="section-divider"><span class="divider-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" style="height:24px;width:auto;"></span></div>
    
    <div class="reveal" style="text-align:center;margin-bottom:var(--space-3xl);">
      <span class="section-subtitle">Gateway to Devotional Services</span>
      <h2 class="section-title">Temple Bookings &amp; Offerings</h2>
      <p class="section-description" style="max-width:750px;margin:0 auto;">
        Welcome to the ISKCON Seshadripuram booking portal. Sponsor a sacred puja, reserve your temple stay, 
        book a Vedic fire sacrifice, or register for upcoming spiritual tours.
      </p>
    </div>

    <!-- Booking Gateway Cards Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:var(--space-xl);margin-bottom:var(--space-3xl);" class="gateway-grid">
      
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
<style>
.gateway-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: all var(--transition-base);
  display: flex;
  flex-direction: column;
}

.gateway-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-light);
}

.gateway-card-image {
  height: 180px;
  background-size: cover;
  background-position: center;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gateway-card-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  transition: all var(--transition-base);
}

.gateway-card:hover .gateway-card-overlay {
  background: rgba(0, 0, 0, 0.45);
}

.gateway-card-icon {
  position: relative;
  z-index: 2;
  font-size: 32px;
  color: var(--accent);
  width: 70px;
  height: 70px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base);
}

.gateway-card:hover .gateway-card-icon {
  transform: scale(1.1);
  background: var(--white);
  color: var(--primary);
  border-color: var(--white);
}

.gateway-card-body {
  padding: var(--space-xl);
  text-align: center;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-md);
}

.gateway-card-body h3 {
  font-family: var(--font-heading);
  color: var(--text-dark);
  font-size: var(--font-size-lg);
  margin: 0;
}

.gateway-card-body p {
  color: var(--text-light);
  font-size: var(--font-size-sm);
  line-height: 1.6;
  margin: 0;
  flex-grow: 1;
}

.gateway-card-body .btn {
  margin-top: auto;
  width: 100%;
  justify-content: center;
}
</style>

<?php include '../partials/footer.php'; ?>
