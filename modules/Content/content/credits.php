<?php
$pageTitle = 'Design, Development & Maintenance - ISKCON The Palace Temple of Lord Jagannath';
$metaDescription = 'Website design, development, and maintenance credits for ISKCON The Palace Temple of Lord Jagannath, Seshadripuram, Bangalore.';
$pageType = 'default';
include 'partials/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/credits.css">

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/credits-banner.jpg');"></div>
  <div class="container">
    <!-- Decorative icon badge -->
    <div class="credits-badge">
      <i class="fas fa-tools"></i> Behind the Temple Website
    </div>
    <h1 class="reveal">Design, Development &amp; Maintenance</h1>
    <p class="credits-hero-desc">
      Crafted with devotion — the people and technology behind this spiritual platform
    </p>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Credits</span>
    </div>
  </div>
</section>

<!-- Credits Content -->
<section class="page-content">
  <div class="container credits-content-container">
    <div class="reveal credits-section-center">
      <div class="section-divider">
        <span class="divider-icon">🛠️</span>
      </div>
      <h2>Website Credits</h2>
      <p class="credits-desc-text">
        This website was designed, developed, and is maintained with care and devotion
        to serve the ISKCON Sri Jagannath Mandir community. We extend our heartfelt
        gratitude to everyone who contributed.
      </p>
    </div>

    <!-- Design -->
    <div class="reveal credits-section-block">
      <div class="credit-section-header credits-section-header-wrap">
        <div class="credits-icon-circle">
          <i class="fas fa-paint-brush"></i>
        </div>
        <div>
          <h3 class="credits-section-heading">Design</h3>
          <p class="credits-section-subtitle">Visual identity, UI/UX, and graphic design</p>
        </div>
      </div>
      <div class="credits-grid">
        <div class="credit-card">
          <div class="credits-avatar-circle">
            <i class="fas fa-user-tie"></i>
          </div>
          <h4 class="credits-person-name">Karunya Gauranga Dasa</h4>
          <p class="credits-person-role">Lead Designer</p>
          <p class="credits-person-desc">UI/UX design, wireframing, visual mockups, and brand identity</p>
        </div>
        <div class="credit-card">
          <div class="credits-avatar-circle">
            <i class="fas fa-palette"></i>
          </div>
          <h4 class="credits-person-name">Achyuta Priya Devi Dasi</h4>
          <p class="credits-person-role">Graphic Designer</p>
          <p class="credits-person-desc">Banner design, iconography, illustrations, and image assets</p>
        </div>
      </div>
    </div>

    <!-- Development -->
    <div class="reveal credits-section-block">
      <div class="credit-section-header credits-section-header-wrap">
        <div class="credits-icon-circle">
          <i class="fas fa-code"></i>
        </div>
        <div>
          <h3 class="credits-section-heading">Development</h3>
          <p class="credits-section-subtitle">Frontend, backend, database, and integration</p>
        </div>
      </div>
      <div class="credits-grid">
        <div class="credit-card">
          <div class="credits-avatar-circle">
            <i class="fas fa-laptop-code"></i>
          </div>
          <h4 class="credits-person-name">Aryashree Pritikrishna</h4>
          <p class="credits-person-role">Lead Developer</p>
          <p class="credits-person-desc">Full-stack architecture, PHP backend, Razorpay integration, and database design</p>
        </div>
        <div class="credit-card">
          <div class="credits-avatar-circle">
            <i class="fas fa-database"></i>
          </div>
          <h4 class="credits-person-name">Satya Narayana Ongolu</h4>
          <p class="credits-person-role">Backend Developer</p>
          <p class="credits-person-desc">Database optimization, API endpoints, payment webhooks, and reporting engine</p>
        </div>
        <div class="credit-card">
          <div class="credits-avatar-circle">
            <i class="fas fa-mobile-alt"></i>
          </div>
          <h4 class="credits-person-name">Bhakta Rajeev Krsna Das</h4>
          <p class="credits-person-role">Frontend Developer</p>
          <p class="credits-person-desc">Responsive layouts, CSS design system, JavaScript interactions, and performance optimization</p>
        </div>
      </div>
    </div>

    <!-- Maintenance -->
    <div class="reveal credits-section-block">
      <div class="credit-section-header credits-section-header-wrap">
        <div class="credits-icon-circle">
          <i class="fas fa-heart"></i>
        </div>
        <div>
          <h3 class="credits-section-heading">Maintained By</h3>
          <p class="credits-section-subtitle">Ongoing support, updates, and hosting</p>
        </div>
      </div>
      <div class="credits-grid">
        <div class="credit-card">
          <div class="credits-avatar-circle">
            <i class="fas fa-server"></i>
          </div>
          <h4 class="credits-person-name">Aryashree Pritikrishna</h4>
          <p class="credits-person-role">System Administrator</p>
          <p class="credits-person-desc">Server management, hosting, SSL certificates, backups, and security monitoring</p>
        </div>
        <div class="credit-card">
          <div class="credits-avatar-circle">
            <i class="fas fa-tasks"></i>
          </div>
          <h4 class="credits-person-name">Sanatana Nitai Dasa</h4>
          <p class="credits-person-role">Content & Operations</p>
          <p class="credits-person-desc">Content updates, event listings, blog posts, donation management, and community outreach</p>
        </div>
      </div>
    </div>

    <!-- Tech Stack -->
    <div class="reveal credits-tech-stack-wrap">
      <div class="section-divider">
        <span class="divider-icon">⚡</span>
      </div>
      <h3 class="credits-section-heading" style="margin-bottom:var(--space-lg);">Technology Stack</h3>
      <div class="credits-tech-grid">
        <span class="credits-tech-tag">PHP 8.x</span>
        <span class="credits-tech-tag">MySQL / MariaDB</span>
        <span class="credits-tech-tag">Apache</span>
        <span class="credits-tech-tag">HTML5 / CSS3</span>
        <span class="credits-tech-tag">JavaScript</span>
        <span class="credits-tech-tag">Razorpay</span>
        <span class="credits-tech-tag">Playwright</span>
        <span class="credits-tech-tag">Chart.js</span>
        <span class="credits-tech-tag">Swiper.js</span>
        <span class="credits-tech-tag">Composer</span>
      </div>
    </div>

    <!-- Support Our Digital Initiatives -->
    <div class="reveal credits-section-block">
      <div class="credits-donation-wrap">
        <div class="credits-donation-badge">
          <i class="fas fa-laptop-code"></i> Digital Seva
        </div>
        <div class="credits-donation-grid">
          <div class="credits-donation-text">
            <h3 class="credits-cta-title">Support Our Digital Initiatives</h3>
            <p class="credits-cta-text">
              This website is a humble digital offering in service to Lord Jagannath and the devotee community.
              It helps share festival updates, seva opportunities, spiritual resources, and temple information
              with devotees near and far.
            </p>
            <p class="credits-cta-text-light">
              To keep these services reliable, secure, and growing, we invite you to support our digital initiatives.
              Your contribution helps sustain the website, hosting, backups, communication systems, live-streaming
              support, and future digital tools for outreach and devotee care.
            </p>
          </div>
          <div class="credits-donation-cta">
            <div class="credits-donation-list-header">
              <i class="fas fa-check-circle"></i> Your support helps with:
            </div>
            <ul class="credits-donation-list">
              <li>Website development and maintenance</li>
              <li>Hosting, domain renewal, and SSL</li>
              <li>Data backups and cyber security</li>
              <li>Live streaming and digital outreach</li>
              <li>Mobile app and devotee care tools</li>
              <li>Email, SMS, and WhatsApp services</li>
            </ul>
            <a href="<?php echo BASE_URL; ?>donate/support-our-digital-initiatives" class="credits-donation-btn">
              <i class="fas fa-heart"></i> Support Digital Initiatives
            </a>
            <p class="credits-donation-sub">Seva options start from ₹501</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Thank You Note -->
    <div class="reveal credits-thankyou-card">
      <div class="credits-thankyou-icon">🙏</div>
      <h3 class="credits-thankyou-title">Thank You for Visiting</h3>
      <p class="credits-thankyou-text">
        This website is a humble offering to Lord Jagannath and the Vaishnava community.
        We pray that this platform serves as a valuable resource for all who seek spiritual
        knowledge and connection. All glory to Srila Prabhupada.
      </p>
    </div>
  </div>
</section>

<?php include 'partials/footer.php'; ?>