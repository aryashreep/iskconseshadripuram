<?php
$pageTitle = 'Our Centers';
$metaDescription = 'Explore ISKCON Seshadripuram\'s centres across Bangalore — New Rajapur Jagannatha Dham, Bhakti Sadan neighbourhood programs, and Bhakti Vriksha weekly congregational meetings.';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/our-centers/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">Our Centers</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Our Centers</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <div style="text-align:center;margin-bottom:var(--space-3xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon">🏛️</span></div>
      <h2>Explore Our Spiritual Centres</h2>
      <p class="section-description">ISKCON Seshadripuram extends its reach through multiple centres across Bangalore, bringing Krishna consciousness closer to your neighbourhood.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-xl);">
      <a href="services/new-rajapur" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:220px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:64px;color:rgba(255,255,255,0.3);">🛕</div>
          <div style="padding:var(--space-xl);text-align:center;">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);">New Rajapur Jagannatha Dham</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">A sacred abode of Lord Jagannath with daily darshan, kirtan, prasadam distribution, and regular spiritual programs for the local community.</p>
            <span class="btn btn-primary btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
      <a href="services/bhakti-sadan" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:220px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:64px;color:rgba(255,255,255,0.3);">🏡</div>
          <div style="padding:var(--space-xl);text-align:center;">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);">Bhakti Sadan</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Taking the temple to your neighbourhood. Weekly Sunday programs across 20+ locations in Bangalore replicating the temple experience.</p>
            <span class="btn btn-primary btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
      <a href="services/bhakti-vriksha" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:220px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:64px;color:rgba(255,255,255,0.3);">🌱</div>
          <div style="padding:var(--space-xl);text-align:center;">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);">Bhakti Vriksha</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Weekly congregational meetings for systematic spiritual growth. Small groups meet in homes across Bangalore for kirtan, discussion, and prasadam.</p>
            <span class="btn btn-primary btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
