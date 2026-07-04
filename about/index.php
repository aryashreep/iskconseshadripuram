<?php
$pageTitle = 'About Us';
$metaDescription = 'Learn about ISKCON The Palace Temple of Lord Jagannath in Seshadripuram, Bangalore. Established in 1998 by HH Jayapataka Swami Maharaj. Discover our mission, philosophy & spiritual services.';
$pageType = 'about';
include '../partials/header.php';
require_once '../config.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/about-header.jpg');"></div>
  <div class="container">
    <h1 class="reveal">About Us</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><span>About Us</span></div>
  </div>
</section>

<!-- About Content -->
<section class="page-content">
  <div class="container">
    <div class="about-intro">
      <div class="about-intro-image reveal">
        <img src="assets/images/banners/about-left.jpg" alt="ISKCON Bangalore Temple Darshan" loading="lazy">
      </div>
      <div class="about-intro-text reveal">
        <div class="decorative-line left"></div>
        <h2>The Palace Temple of Lord Sri Jagannath</h2>
        <p>ISKCON Seshadripuram, The Palace Temple of Lord Jagannath is one of the branches of ISKCON Juhu, the headquarters of International Society for Krishna Consciousness. ISKCON Seshadripuram was established on <strong>January 31, 1998</strong> and it was inaugurated by <strong>His Holiness Jayapataka Swami Maharaj</strong> for propagating the spiritual knowledge to society at large and to educate all people in the techniques of spiritual life.</p>
        <p>The temple is known for its service to society, primarily in preserving Vedic culture and also for profusely distributing Krishna prasadam, be it in times of crisis or in normal periods. With effective preaching, we have over a thousand congregational devotees located in every corner of Bengaluru.</p>
        <p>ISKCON Seshadripuram serves as a spiritual and cultural hub for individuals seeking a deeper connection with Krishna and a path of spiritual enlightenment. The temple complex is characterized by its beautiful architecture, vibrant deity worship, and a serene atmosphere that invites visitors to immerse themselves in devotion.</p>
        <p>The temple hosts regular religious ceremonies, kirtans (devotional singing), discourses, and spiritual gatherings, where devotees come together to engage in devotional practices and enhance their understanding of Vedic philosophy.</p>
        <p>ISKCON Seshadripuram also runs educational and social welfare programs. The temple provides spiritual education through seminars, workshops, and study circles, enabling people to deepen their understanding of ancient scriptures like the Bhagavad Gita and the Srimad Bhagavatam.</p>
        <div style="margin-top:var(--space-xl);display:flex;gap:var(--space-md);flex-wrap:wrap;">
          <a href="about/founder-acharya" class="btn btn-primary"><i class="fas fa-book"></i> Founder Acharya</a>
          <a href="about/temple-schedule" class="btn btn-outline-dark"><i class="fas fa-clock"></i> Temple Schedule</a>
          <a href="about/golden-temple" class="btn btn-outline-dark"><i class="fas fa-temple"></i> Golden Temple</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Explore More Section -->
<section class="section section-alt" id="explore">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">📖</span></div>
    <span class="section-subtitle reveal">Learn More</span>
    <h2 class="section-title reveal" style="text-align:center;">Explore ISKCON</h2>
    <p class="section-description reveal" style="text-align:center;">Dive deeper into the philosophy, history, and mission of the International Society for Krishna Consciousness.</p>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-xl);margin-top:var(--space-2xl);">
      <a href="about/our-mission" style="text-decoration:none;color:inherit;">
        <div class="reveal" style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);text-align:center;box-shadow:var(--shadow-sm);transition:all var(--transition-base);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);"><i class="fas fa-bullseye"></i></div>
          <h4 style="margin-bottom:var(--space-sm);">Our Mission</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">The seven purposes of ISKCON as given by Srila Prabhupada — the guiding vision for our movement.</p>
        </div>
      </a>
      <a href="about/history-of-iskcon" style="text-decoration:none;color:inherit;">
        <div class="reveal" style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);text-align:center;box-shadow:var(--shadow-sm);transition:all var(--transition-base);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);"><i class="fas fa-timeline"></i></div>
          <h4 style="margin-bottom:var(--space-sm);">History of ISKCON</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">From Srila Prabhupada's journey to the West in 1965 to a global movement spanning six continents.</p>
        </div>
      </a>
      <a href="about/our-philosophy" style="text-decoration:none;color:inherit;">
        <div class="reveal" style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);text-align:center;box-shadow:var(--shadow-sm);transition:all var(--transition-base);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);"><i class="fas fa-gem"></i></div>
          <h4 style="margin-bottom:var(--space-sm);">Our Philosophy</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">The core teachings — acintya bheda tattva, reincarnation, and the path of bhakti yoga.</p>
        </div>
      </a>
      <a href="about/hare-krishna-movement" style="text-decoration:none;color:inherit;">
        <div class="reveal" style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);text-align:center;box-shadow:var(--shadow-sm);transition:all var(--transition-base);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);"><i class="fas fa-globe"></i></div>
          <h4 style="margin-bottom:var(--space-sm);">Hare Krishna Movement</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">The global spread of the Hare Krishna movement and its impact on society worldwide.</p>
        </div>
      </a>
      <a href="about/golden-temple" style="text-decoration:none;color:inherit;">
        <div class="reveal" style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);text-align:center;box-shadow:var(--shadow-sm);transition:all var(--transition-base);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);"><i class="fas fa-temple"></i></div>
          <h4 style="margin-bottom:var(--space-sm);">Golden Temple Project</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">The visionary cultural complex project inaugurated by the Governor of Karnataka in 2022.</p>
        </div>
      </a>
      <a href="resources" style="text-decoration:none;color:inherit;">
        <div class="reveal" style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);text-align:center;box-shadow:var(--shadow-sm);transition:all var(--transition-base);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);"><i class="fas fa-link"></i></div>
          <h4 style="margin-bottom:var(--space-sm);">Resources</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Audio, video, books, and links to deepen your study of Vedic philosophy and Krishna consciousness.</p>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- Founder Quote -->
<section class="section section-alt" style="text-align:center;">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">🙏</span></div>
    <span class="section-subtitle reveal">Founder Acharya</span>
    <h2 class="section-title reveal">His Divine Grace</h2>
    <div style="max-width:700px;margin:0 auto;" class="reveal">
      <p style="font-family:var(--font-subheading);font-style:italic;font-size:var(--font-size-xl);color:var(--text);line-height:1.8;">"The Krishna consciousness movement is not a new movement. It is the eternal movement of the living entities back home, back to Godhead."</p>
      <p style="margin-top:var(--space-lg);color:var(--primary);font-weight:600;">— A.C. Bhaktivedanta Swami Prabhupada</p>
    </div>
    <div class="reveal" style="margin-top:var(--space-2xl);">
      <a href="about/founder-acharya" class="btn btn-primary"><i class="fas fa-book"></i> Read His Story</a>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
