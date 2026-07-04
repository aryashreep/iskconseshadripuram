<?php
$pageTitle = 'Forums';
include 'partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/forums/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">Forums & Communities</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Forums</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <div style="text-align:center;margin-bottom:var(--space-3xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon">👥</span></div>
      <h2>Join Our Spiritual Communities</h2>
      <p class="section-description">Connect with like-minded devotees through our various forums catering to different age groups and interests.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--space-xl);max-width:900px;margin:0 auto;">
      <a href="services/youth-forum" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:200px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:56px;color:rgba(255,255,255,0.3);">🔥</div>
          <div style="padding:var(--space-xl);text-align:center;">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);">ISKCON Youth Forum</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Empowering young men through Vedic wisdom. Workshops, retreats, seminars, and personality development programs based on Bhagavad Gita.</p>
            <span class="btn btn-primary btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-arrow-right"></i> Learn More</span>
          </div>
        </div>
      </a>
      <a href="services/vaishnavi-forum" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:200px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:56px;color:rgba(255,255,255,0.3);">🌸</div>
          <div style="padding:var(--space-xl);text-align:center;">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);">ISKCON Vaishnavi Forum</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Empowering young women through devotion. Scripture study, counselling, music, arts & crafts, and personality development programs.</p>
            <span class="btn btn-primary btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-arrow-right"></i> Learn More</span>
          </div>
        </div>
      </a>
      <a href="services/krishna-fun-school" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:200px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:56px;color:rgba(255,255,255,0.3);">🎨</div>
          <div style="padding:var(--space-xl);text-align:center;">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);">Krishna Fun School</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Weekend program for children (ages 3-16) with games, stories, art, shloka recitation, and Vedic values in a fun-filled atmosphere.</p>
            <span class="btn btn-primary btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-arrow-right"></i> Learn More</span>
          </div>
        </div>
      </a>
      <a href="services/music-school" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:200px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:56px;color:rgba(255,255,255,0.3);">🎵</div>
          <div style="padding:var(--space-xl);text-align:center;">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);">Nilachal School of Music</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Learn bhajan, kirtan, harmonium, kartal, mridangam, and djembe from expert teachers. All ages welcome.</p>
            <span class="btn btn-primary btn-sm" style="margin-top:var(--space-md);"><i class="fas fa-arrow-right"></i> Learn More</span>
          </div>
        </div>
      </a>
    </div>
  </div>
</section>

<?php include 'partials/footer.php'; ?>
