<?php
/**
 * Homepage Ways to Serve Section
 * Shows dynamic service/construction/general cause cards from the database
 */
$serviceCauses = getHomepageServiceCauses(6);
if (empty($serviceCauses)) return;
?>
<section class="section section-alt services-section" id="ways-to-serve">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">✨</span></div>
    <span class="section-subtitle reveal">Ways to Serve</span>
    <h2 class="section-title reveal">Support the Divine Mission</h2>
    <p class="section-description reveal">Every act of service brings you closer to the divine. Choose a way to serve that resonates with your heart.</p>
    <div class="home-service-grid">
      <?php foreach ($serviceCauses as $cause): 
        $slug = htmlspecialchars($cause['slug']);
        $title = htmlspecialchars($cause['title']);
        $shortTitle = htmlspecialchars($cause['short_title'] ?? $title);
        $desc = htmlspecialchars($cause['description']);
        $img = !empty($cause['image_url']) ? htmlspecialchars($cause['image_url']) : 'https://picsum.photos/seed/' . $slug . '/600/400';
        $sigPreview = !empty($cause['significance']) ? htmlspecialchars(substr($cause['significance'], 0, 120)) . '...' : '';
      ?>
      <div class="home-service-card reveal">
        <div class="home-service-card-image">
          <img src="<?= $img ?>" alt="<?= $title ?>" loading="lazy">
        </div>
        <div class="home-service-card-body">
          <h3><?= $title ?></h3>
          <p class="home-service-card-desc"><?= $desc ?></p>
          <?php if ($sigPreview): ?>
          <p class="home-service-card-sig"><i class="fas fa-quote-left"></i> <?= $sigPreview ?></p>
          <?php endif; ?>
          <div class="home-service-card-actions">
            <a href="<?= BASE_URL ?>donate/<?= $slug ?>" class="btn btn-primary btn-sm"><i class="fas fa-hand-holding-heart"></i> Offer Seva</a>
            <a href="<?= BASE_URL ?>donate/<?= $slug ?>" class="btn btn-outline-dark btn-sm"><i class="fas fa-info-circle"></i> Learn More</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="reveal" style="text-align:center;margin-top:var(--space-2xl);">
      <a href="<?= BASE_URL ?>services" class="btn btn-primary"><i class="fas fa-star"></i> View All Services</a>
    </div>
  </div>
</section>
