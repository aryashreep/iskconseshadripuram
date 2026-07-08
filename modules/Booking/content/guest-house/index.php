<?php
$pageTitle = 'International Guest House - Temple Stay';
$metaDescription = 'Stay at the ISKCON Seshadripuram International Guest House in Bangalore. Clean AC and non-AC rooms inside the temple campus with Govinda\'s Restaurant and daily darshan.';
include '../../partials/header.php';
require_once '../../config.php';

// Actual contact coordinates from the codebase
$bookingPhone = '+91 99860 77269';
$bookingWhatsapp = '+91 99860 77269';
$bookingEmail = 'isjmadmin@gmail.com';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/guest_house_room.jpg');"></div>
  <div class="container">
    <h1 class="reveal">International Guest House</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Guest House</span>
    </div>
  </div>
</section>

<!-- Content Body -->
<section class="page-content">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">🏢</span></div>

    <!-- Intro Grid -->
    <div class="guesthouse-grid" style="display:grid;grid-template-columns:1.2fr 1fr;gap:var(--space-3xl);align-items:center;margin-bottom:var(--space-3xl);">
      
      <!-- Left: Descriptive Content -->
      <div class="reveal">
        <span class="section-subtitle">Temple Stay Experience</span>
        <h2 class="booking-heading" style="font-size:clamp(1.8rem, 4vw, var(--font-size-3xl));">
          Stay Where the Heart of the Temple Beats
        </h2>
        <p style="color:var(--text-light);font-size:var(--font-size-base);line-height:1.8;margin-bottom:var(--space-md);">
          Located right within the sacred ISKCON campus, our guest house offers clean, affordable rooms with the unique benefit of being footsteps away from daily temple programs and Srila Prabhupada's Samadhi Mandir.
        </p>
        <p style="color:var(--text-light);font-size:var(--font-size-base);line-height:1.8;margin-bottom:var(--space-md);">
          Many guests describe waking up to the sound of morning prayers and kirtan as an unforgettable, spiritually rejuvenating experience.
        </p>
        <p style="color:var(--text-light);font-size:var(--font-size-base);line-height:1.8;margin-bottom:var(--space-lg);">
          Enjoy wholesome, sanctified meals at <strong>Govinda's Restaurant</strong>, situated conveniently on the ground floor of the guest house building.
        </p>

        <!-- Features Grid -->
        <div class="guest-house-features" style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);margin-top:var(--space-xl);">
          <div class="flex-center" style="gap:var(--space-sm);">
            <div class="feature-icon-circle"><i class="fas fa-wifi"></i></div>
            <span style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-dark);">Free Wi-Fi</span>
          </div>
          <div class="flex-center" style="gap:var(--space-sm);">
            <div class="feature-icon-circle"><i class="fas fa-utensils"></i></div>
            <span style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-dark);">Govinda's Restaurant</span>
          </div>
          <div class="flex-center" style="gap:var(--space-sm);">
            <div class="feature-icon-circle"><i class="fas fa-shower"></i></div>
            <span style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-dark);">24hr Hot Water</span>
          </div>
          <div class="flex-center" style="gap:var(--space-sm);">
            <div class="feature-icon-circle"><i class="fas fa-face-smile"></i></div>
            <span style="font-size:var(--font-size-sm);font-weight:600;color:var(--text-dark);">Proximity to Darshan</span>
          </div>
        </div>
      </div>

      <!-- Right: Call to Book Box -->
      <div class="reveal">
        <div class="gradient-primary-box">
          <div class="gradient-deco-circle"></div>
          
          <h3 class="booking-heading" style="color:var(--accent);font-size:var(--font-size-xl);display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,0.15);padding-bottom:var(--space-sm);margin-top:0;margin-bottom:var(--space-md);">
            <i class="fas fa-envelope"></i> Request Booking
          </h3>
          <p style="color:rgba(255,255,255,0.9);font-size:var(--font-size-sm);line-height:1.6;margin-bottom:var(--space-xl);">
            Please note that henceforth one is needed to write a mail to <strong>isjmadmin@gmail.com</strong> for Guest Room booking mentioning basic details. We have limited rooms and hence will respond asap accordingly looking at various factors.
          </p>

          <div style="display:flex;flex-direction:column;gap:var(--space-md);">
            <div style="display:flex;align-items:start;gap:var(--space-md);">
              <div style="font-size:20px;color:var(--accent);margin-top:2px;"><i class="fas fa-phone-alt"></i></div>
              <div>
                <span style="display:block;font-size:12px;opacity:0.7;">Booking Phone</span>
                <a href="tel:<?php echo str_replace(' ', '', $bookingPhone); ?>" style="color:var(--white);text-decoration:none;font-weight:600;font-size:var(--font-size-md);"><?php echo htmlspecialchars($bookingPhone); ?></a>
              </div>
            </div>

            <div style="display:flex;align-items:start;gap:var(--space-md);">
              <div style="font-size:20px;color:var(--accent);margin-top:2px;"><i class="fab fa-whatsapp"></i></div>
              <div>
                <span style="display:block;font-size:12px;opacity:0.7;">WhatsApp</span>
                <a href="https://wa.me/<?php echo str_replace([' ', '+'], '', $bookingWhatsapp); ?>" target="_blank" style="color:var(--white);text-decoration:none;font-weight:600;font-size:var(--font-size-md);"><?php echo htmlspecialchars($bookingWhatsapp); ?></a>
              </div>
            </div>

            <div style="display:flex;align-items:start;gap:var(--space-md);">
              <div style="font-size:20px;color:var(--accent);margin-top:2px;"><i class="fas fa-envelope"></i></div>
              <div>
                <span style="display:block;font-size:12px;opacity:0.7;">Email Address</span>
                <a href="mailto:<?php echo htmlspecialchars($bookingEmail); ?>" style="color:var(--white);text-decoration:none;font-weight:600;font-size:var(--font-size-md);"><?php echo htmlspecialchars($bookingEmail); ?></a>
              </div>
            </div>

            <div style="display:flex;align-items:start;gap:var(--space-md);border-top:1px solid rgba(255,255,255,0.15);padding-top:var(--space-md);margin-top:var(--space-xs);">
              <div style="font-size:20px;color:var(--accent);margin-top:2px;"><i class="fas fa-clock"></i></div>
              <div>
                <span style="display:block;font-size:12px;opacity:0.7;">Office Timings</span>
                <span style="color:var(--white);font-weight:500;font-size:var(--font-size-sm);">Monday to Saturday, 10:00 AM – 5:00 PM IST</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Room Types Grid -->
    <div style="margin-top:var(--space-3xl);">
      <h3 style="font-family:var(--font-heading);color:var(--text-dark);text-align:center;margin-bottom:var(--space-2xl);">Available Accommodations</h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:var(--space-xl);" class="room-grid">
        
        <!-- Standard Non-AC -->
        <div class="reveal" style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--border);overflow:hidden;transition:all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
          <div class="room-card-img" style="background-image:url('<?php echo BASE_URL; ?>assets/images/banners/room_standard_non_ac.jpg');">
            <div class="room-card-overlay"></div>
          </div>
          <div style="padding:var(--space-lg);">
            <h4 style="font-family:var(--font-heading);color:var(--text-dark);font-size:var(--font-size-lg);margin-top:0;margin-bottom:var(--space-xs);">Standard Non-AC Room</h4>
            <p class="booking-desc-text" style="margin-bottom:var(--space-md);">Affordable and comfortable room featuring clean twin beds, attachment bathroom, and fan ventilation. Perfect for single travellers and families looking for a simple stay.</p>
            <div class="flex-center" style="gap:var(--space-sm);color:var(--text-light);font-size:12px;">
              <span><i class="fas fa-bed"></i> 2 Twin Beds</span>
              <span>•</span>
              <span><i class="fas fa-bath"></i> Attached Bath</span>
            </div>
          </div>
        </div>

        <!-- Standard AC -->
        <div class="reveal card-white hover-lift" style="overflow:hidden;">
          <div class="room-card-img" style="background-image:url('<?php echo BASE_URL; ?>assets/images/banners/room_standard_ac.jpg');">
            <div class="room-card-overlay"></div>
          </div>
          <div style="padding:var(--space-lg);">
            <h4 style="font-family:var(--font-heading);color:var(--text-dark);font-size:var(--font-size-lg);margin-top:0;margin-bottom:var(--space-xs);">Standard AC Room</h4>
            <p class="booking-desc-text" style="margin-bottom:var(--space-md);">Features full air-conditioning, standard double bed/twin beds, attached bathroom, and study table. A pleasant stay for pilgrims looking for a relaxed retreat after daily temple visits.</p>
            <div class="flex-center" style="gap:var(--space-sm);color:var(--text-light);font-size:12px;">
              <span><i class="fas fa-wind"></i> Air Conditioned</span>
              <span>•</span>
              <span><i class="fas fa-bed"></i> Double Bed</span>
            </div>
          </div>
        </div>

        <!-- Deluxe Suite -->
        <div class="reveal card-white hover-lift" style="overflow:hidden;">
          <div class="room-card-img" style="background-image:url('<?php echo BASE_URL; ?>assets/images/banners/room_deluxe_suite.jpg');">
            <div class="room-card-overlay"></div>
          </div>
          <div style="padding:var(--space-lg);">
            <h4 style="font-family:var(--font-heading);color:var(--text-dark);font-size:var(--font-size-lg);margin-top:0;margin-bottom:var(--space-xs);">Deluxe Suite</h4>
            <p class="booking-desc-text" style="margin-bottom:var(--space-md);">Spacious premier room with air conditioning, king-size bed, sofa seating area, dressing space, and superior bathroom amenities. Ideal for families and couples seeking premium comfort.</p>
            <div class="flex-center" style="gap:var(--space-sm);color:var(--text-light);font-size:12px;">
              <span><i class="fas fa-expand"></i> Extra Space</span>
              <span>•</span>
              <span><i class="fas fa-couch"></i> Sofa Seating</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Campus Rules & Guidelines -->
    <div style="margin-top:var(--space-3xl);background:var(--cream-light);border:1px solid var(--border);border-radius:var(--radius-lg);padding:var(--space-2xl);" class="reveal">
      <h3 style="font-family:var(--font-heading);color:var(--text-dark);margin-top:0;margin-bottom:var(--space-md);">Guest Guidelines &amp; Policies</h3>
      <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.8;margin-bottom:var(--space-lg);">
        Since the guest house is located inside the temple campus, guests are requested to kindly observe and respect the local rules and ashram regulations to maintain the spiritual purity of the Dham:
      </p>
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);" class="guesthouse-rules">
        <div style="display:flex;gap:var(--space-sm);align-items:start;">
          <div style="color:var(--primary);margin-top:2px;"><i class="fas fa-times-circle"></i></div>
          <span style="font-size:var(--font-size-sm);color:var(--text);line-height:1.6;">No consumption of non-vegetarian food, eggs, onions, or garlic on campus.</span>
        </div>
        <div style="display:flex;gap:var(--space-sm);align-items:start;">
          <div style="color:var(--primary);margin-top:2px;"><i class="fas fa-times-circle"></i></div>
          <span style="font-size:var(--font-size-sm);color:var(--text);line-height:1.6;">Smoking, tobacco consumption, and alcohol are strictly prohibited on campus.</span>
        </div>
        <div style="display:flex;gap:var(--space-sm);align-items:start;">
          <div style="color:var(--primary);margin-top:2px;"><i class="fas fa-info-circle"></i></div>
          <span style="font-size:var(--font-size-sm);color:var(--text);line-height:1.6;">Guests are encouraged to attend daily Aarti (Morning at 5:00 AM, Evening at 7:00 PM).</span>
        </div>
        <div style="display:flex;gap:var(--space-sm);align-items:start;">
          <div style="color:var(--primary);margin-top:2px;"><i class="fas fa-info-circle"></i></div>
          <span style="font-size:var(--font-size-sm);color:var(--text);line-height:1.6;">Please dress modestly during your stay and when visiting the main temple halls.</span>
        </div>
      </div>
    </div>

  </div>
</section>

<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/booking-utilities.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/pages/booking/guest-house.css') ?>">

<?php include '../../partials/footer.php'; ?>
