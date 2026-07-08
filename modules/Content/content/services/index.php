<?php
$pageTitle = 'All Services';
$metaDescription = 'Explore all services at ISKCON The Palace Temple of Lord Jagannath in Bangalore — Siksha Bhakti Steps, Harinam Sankirtana, corporate programs, Govinda\'s Prasadam, function hall, and Food For Life.';
include '../partials/header.php';
require_once __DIR__ . '/../../config.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/all-services/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">All Services</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>All Services</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container">
    <div style="text-align:center;margin-bottom:var(--space-3xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon">✨</span></div>
      <h2>Explore All Our Offerings</h2>
      <p class="section-description">Beyond our main programs, we offer a variety of services to nourish your spiritual journey.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-xl);">
      <a href="services/siksha" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:180px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:48px;color:rgba(255,255,255,0.3);">🪜</div>
          <div style="padding:var(--space-lg);text-align:center;">
            <h3 style="font-size:var(--font-size-base);margin-bottom:var(--space-sm);">Siksha – The Bhakti Steps</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;">A progressive system for spiritual growth through recognized chanting and practice milestones.</p>
            <span style="color:var(--primary);font-weight:600;font-size:var(--font-size-sm);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
      <a href="services/harinam-sankirtana" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:180px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:48px;color:rgba(255,255,255,0.3);">🎵</div>
          <div style="padding:var(--space-lg);text-align:center;">
            <h3 style="font-size:var(--font-size-base);margin-bottom:var(--space-sm);">Harinam Sankirtana</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;">Congregational chanting of the holy names accompanied by traditional instruments. Join our Sunday Nagar Sankirtana.</p>
            <span style="color:var(--primary);font-weight:600;font-size:var(--font-size-sm);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
      <a href="services/corporate-programs" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:180px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:48px;color:rgba(255,255,255,0.3);">💼</div>
          <div style="padding:var(--space-lg);text-align:center;">
            <h3 style="font-size:var(--font-size-base);margin-bottom:var(--space-sm);">Corporate Programs</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;">Workshops on stress management, leadership, ethics, and work-life balance for professionals and organizations.</p>
            <span style="color:var(--primary);font-weight:600;font-size:var(--font-size-sm);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
      <a href="services/govindas-prasadam" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:180px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:48px;color:rgba(255,255,255,0.3);">🍛</div>
          <div style="padding:var(--space-lg);text-align:center;">
            <h3 style="font-size:var(--font-size-base);margin-bottom:var(--space-sm);">Govinda's Prasadam</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;">Karma-free vegetarian food offered to Lord Krishna. Available at our restaurant and for catering services.</p>
            <span style="color:var(--primary);font-weight:600;font-size:var(--font-size-sm);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
      <a href="services/function-hall" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:180px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:48px;color:rgba(255,255,255,0.3);">🏛️</div>
          <div style="padding:var(--space-lg);text-align:center;">
            <h3 style="font-size:var(--font-size-base);margin-bottom:var(--space-sm);">Function Hall</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;">Beautiful spaces for weddings, ceremonies, and celebrations with temple facilities and catering available.</p>
            <span style="color:var(--primary);font-weight:600;font-size:var(--font-size-sm);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
      <a href="services/food-for-life" class="reveal" style="text-decoration:none;color:inherit;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);height:100%;">
          <div style="height:180px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:48px;color:rgba(255,255,255,0.3);">🍲</div>
          <div style="padding:var(--space-lg);text-align:center;">
            <h3 style="font-size:var(--font-size-base);margin-bottom:var(--space-sm);">Food For Life</h3>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;">World's largest vegetarian food relief program serving meals to the needy across 60+ countries.</p>
            <span style="color:var(--primary);font-weight:600;font-size:var(--font-size-sm);"><i class="fas fa-arrow-right"></i> Explore</span>
          </div>
        </div>
      </a>
    </div>

    <?php 
    include_once __DIR__ . '/../../partials/donation-cta.php';
    renderDonationSection([
      'cause_slug' => 'general-donation',
      'button_label' => 'Support Temple Services',
      'background' => 'linear-gradient(135deg, var(--maroon) 0%, var(--primary) 100%)'
    ]); 
    ?>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
