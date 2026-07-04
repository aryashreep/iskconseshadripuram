<?php
$pageTitle = 'Contact Us';
$metaDescription = 'Contact ISKCON The Palace Temple of Lord Jagannath, Seshadripuram Bangalore. Address: 159, 1st Main Road, Seshadripuram. Phone: +91 99860 77269. Email: info@iskconseshadripuram.org.';
$pageType = 'contact';
include 'partials/header.php';
require_once 'config.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/srila-prabhupada-appearance-banner.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Contact Us</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Contact Us</span>
    </div>
  </div>
</section>

<!-- Content Area -->
<section class="page-content">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">📞</span></div>

    <!-- Contact Grid -->
    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:var(--space-3xl);align-items:start;" class="contact-layout-grid">
      
      <!-- Left Column: Temple Info -->
      <div class="reveal">
        <h2 style="font-family:var(--font-heading);color:var(--text-dark);margin-top:0;margin-bottom:var(--space-md);">ISKCON The Palace Temple of Lord Jagannath</h2>
        <span style="display:block;color:var(--primary);font-weight:600;margin-bottom:var(--space-lg);font-size:var(--font-size-sm);">International Society for Krishna Consciousness (ISKCON)</span>
        
        <div class="contact-info-list" style="display:flex;flex-direction:column;gap:var(--space-xl);">
          
          <!-- Founder Acharya -->
          <div style="display:flex;gap:var(--space-md);align-items:start;">
            <div style="font-size:20px;color:var(--primary);margin-top:2px;"><i class="fas fa-om"></i></div>
            <div>
              <h4 style="font-family:var(--font-heading);color:var(--text-dark);margin:0 0 4px 0;font-size:var(--font-size-sm);">Founder-Acharya</h4>
              <p style="color:var(--text-light);font-size:var(--font-size-sm);margin:0;line-height:1.6;">His Divine Grace A.C. Bhaktivedanta Swami Prabhupada</p>
            </div>
          </div>

          <!-- Temple Address -->
          <div style="display:flex;gap:var(--space-md);align-items:start;">
            <div style="font-size:20px;color:var(--primary);margin-top:2px;"><i class="fas fa-map-marker-alt"></i></div>
            <div>
              <h4 style="font-family:var(--font-heading);color:var(--text-dark);margin:0 0 4px 0;font-size:var(--font-size-sm);">Temple Location</h4>
              <p style="color:var(--text-light);font-size:var(--font-size-sm);margin:0;line-height:1.6;">
                ISKCON Seshadripuram, Bangalore<br>
                159, 1st Main road, Beside TRUGAS,<br>
                Seshadripuram, Bengaluru - 560020
              </p>
            </div>
          </div>

          <!-- Regd Office -->
          <div style="display:flex;gap:var(--space-md);align-items:start;">
            <div style="font-size:20px;color:var(--primary);margin-top:2px;"><i class="fas fa-building"></i></div>
            <div>
              <h4 style="font-family:var(--font-heading);color:var(--text-dark);margin:0 0 4px 0;font-size:var(--font-size-sm);">Registered Office</h4>
              <p style="color:var(--text-light);font-size:var(--font-size-sm);margin:0;line-height:1.6;">ISKCON, Hare Krishna Land, Juhu Mumbai - 400049</p>
            </div>
          </div>

          <!-- Contact Numbers -->
          <div style="display:flex;gap:var(--space-md);align-items:start;">
            <div style="font-size:20px;color:var(--primary);margin-top:2px;"><i class="fas fa-phone-alt"></i></div>
            <div>
              <h4 style="font-family:var(--font-heading);color:var(--text-dark);margin:0 0 4px 0;font-size:var(--font-size-sm);">Helpline &amp; WhatsApp</h4>
              <p style="color:var(--text-light);font-size:var(--font-size-sm);margin:0;line-height:1.6;">
                Phone: <a href="tel:+919986077269" style="color:var(--primary);text-decoration:none;font-weight:600;">+91 99860 77269</a><br>
                WhatsApp: <a href="https://wa.me/919986077269" target="_blank" style="color:var(--primary);text-decoration:none;font-weight:600;">+91 99860 77269</a>
              </p>
            </div>
          </div>

          <!-- Email -->
          <div style="display:flex;gap:var(--space-md);align-items:start;">
            <div style="font-size:20px;color:var(--primary);margin-top:2px;"><i class="fas fa-envelope"></i></div>
            <div>
              <h4 style="font-family:var(--font-heading);color:var(--text-dark);margin:0 0 4px 0;font-size:var(--font-size-sm);">Email Address</h4>
              <p style="color:var(--text-light);font-size:var(--font-size-sm);margin:0;line-height:1.6;">
                <a href="mailto:info@iskconseshadripuram.org" style="color:var(--primary);text-decoration:none;">info@iskconseshadripuram.org</a>
              </p>
            </div>
          </div>

        </div>
      </div>

      <!-- Right Column: Quick Bookings Dashboard -->
      <div class="reveal">
        <div style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:var(--space-xl);box-shadow:var(--shadow-sm);">
          <h3 style="font-family:var(--font-heading);color:var(--text-dark);margin-top:0;margin-bottom:var(--space-md);font-size:var(--font-size-md);display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--border);padding-bottom:var(--space-xs);">
            <span>📅</span> Quick Bookings
          </h3>
          <p style="color:var(--text-light);font-size:var(--font-size-xs);margin-bottom:var(--space-lg);line-height:1.6;">
            Select a service below to book online or explore details:
          </p>

          <div style="display:flex;flex-direction:column;gap:12px;" class="quick-booking-links">
            
            <!-- Link 1: Puja -->
            <a href="booking/puja" style="display:flex;align-items:center;gap:15px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-md);text-decoration:none;transition:all var(--transition-base);" onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--cream-light)';" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent';">
              <div style="width:40px;height:40px;background:var(--cream);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><i class="fas fa-om"></i></div>
              <div style="flex-grow:1;">
                <strong style="color:var(--text-dark);display:block;font-size:var(--font-size-sm);font-family:var(--font-heading);">Puja Offerings</strong>
                <span style="color:var(--text-light);font-size:11px;display:block;">Sponsor archanas, garlands, and daily sevas</span>
              </div>
              <div style="color:var(--text-light);font-size:14px;"><i class="fas fa-chevron-right"></i></div>
            </a>

            <!-- Link 2: Yagya -->
            <a href="booking/yagya" style="display:flex;align-items:center;gap:15px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-md);text-decoration:none;transition:all var(--transition-base);" onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--cream-light)';" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent';">
              <div style="width:40px;height:40px;background:var(--cream);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><i class="fas fa-fire"></i></div>
              <div style="flex-grow:1;">
                <strong style="color:var(--text-dark);display:block;font-size:var(--font-size-sm);font-family:var(--font-heading);">Yagyas &amp; Homas</strong>
                <span style="color:var(--text-light);font-size:11px;display:block;">Sponsor fire sacrifices for protection &amp; health</span>
              </div>
              <div style="color:var(--text-light);font-size:14px;"><i class="fas fa-chevron-right"></i></div>
            </a>

            <!-- Link 3: Guest House -->
            <a href="booking/guest-house" style="display:flex;align-items:center;gap:15px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-md);text-decoration:none;transition:all var(--transition-base);" onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--cream-light)';" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent';">
              <div style="width:40px;height:40px;background:var(--cream);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><i class="fas fa-bed"></i></div>
              <div style="flex-grow:1;">
                <strong style="color:var(--text-dark);display:block;font-size:var(--font-size-sm);font-family:var(--font-heading);">Guest House Stay</strong>
                <span style="color:var(--text-light);font-size:11px;display:block;">Reserve accommodations in Seshadripuram</span>
              </div>
              <div style="color:var(--text-light);font-size:14px;"><i class="fas fa-chevron-right"></i></div>
            </a>

            <!-- Link 4: Yatras -->
            <a href="yatra" style="display:flex;align-items:center;gap:15px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-md);text-decoration:none;transition:all var(--transition-base);" onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--cream-light)';" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent';">
              <div style="width:40px;height:40px;background:var(--cream);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><i class="fas fa-route"></i></div>
              <div style="flex-grow:1;">
                <strong style="color:var(--text-dark);display:block;font-size:var(--font-size-sm);font-family:var(--font-heading);">Yatra Pilgrimages</strong>
                <span style="color:var(--text-light);font-size:11px;display:block;">Explore and register for upcoming holy tours</span>
              </div>
              <div style="color:var(--text-light);font-size:14px;"><i class="fas fa-chevron-right"></i></div>
            </a>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Map Section -->
<section class="section section-alt" style="padding-top:0;">
  <div class="container">
    <div style="text-align:center;margin-bottom:var(--space-xl);">
      <div class="section-divider"><span class="divider-icon">📍</span></div>
      <span class="section-subtitle">Find Us</span>
      <h2 class="section-title">Visit Our Temple</h2>
    </div>
    
    <div class="reveal" style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);border:1px solid var(--border);">
      <div style="position:relative;padding-bottom:40%;height:0;overflow:hidden;">
        <iframe src="https://maps.google.com/maps?q=ISKCON%20Sri%20Jagannath%20Mandir%20Seshadripuram%20Bangalore&t=&z=16&ie=UTF8&iwloc=&output=embed" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allowfullscreen="" loading="lazy" title="ISKCON Seshadripuram Location Map"></iframe>
      </div>
    </div>
  </div>
</section>

<!-- Responsive layout adjustments -->
<style>
@media (max-width: 768px) {
  .contact-layout-grid {
    grid-template-columns: 1fr !important;
    gap: var(--space-2xl) !important;
  }
}
</style>

<?php include 'partials/footer.php'; ?>
