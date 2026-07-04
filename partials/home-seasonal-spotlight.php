<?php
/**
 * Homepage Seasonal Spotlight & Featured Festivals Section
 * Shows a seasonal spotlight (month-driven) and 6 featured festivals from the DB
 */
$seasonalCause = getSeasonalHomepageCause();
$festivalCauses = getHomepageFestivalCauses(6);
$monthLabel = getSeasonalMonthLabel();
if (empty($festivalCauses)) return;
?>
<section class="section festivals-section" id="festivals">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">🌟</span></div>
    <span class="section-subtitle reveal">Festivals &amp; Celebrations</span>
    <h2 class="section-title reveal">Sacred Celebrations</h2>
    <p class="section-description reveal">Throughout the year, we celebrate the divine pastimes of the Lord with grand festivals, kirtans, abhishekams, and prasadam distribution.</p>

    <div class="festivals-layout">
      <?php if ($seasonalCause): 
        $sSlug = htmlspecialchars($seasonalCause['slug']);
        $sTitle = htmlspecialchars($seasonalCause['title']);
        $sShort = htmlspecialchars($seasonalCause['short_title'] ?? $seasonalCause['title']);
        $sDesc = htmlspecialchars($seasonalCause['description']);
        $sImg = !empty($seasonalCause['image_url']) ? htmlspecialchars($seasonalCause['image_url']) : 'https://picsum.photos/seed/' . $sSlug . '/800/500';
      ?>
      <!-- Seasonal Spotlight -->
      <div class="seasonal-spotlight reveal">
        <div class="seasonal-spotlight-badge"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($monthLabel) ?> Spotlight</div>
        <div class="seasonal-spotlight-inner">
          <div class="seasonal-spotlight-image">
            <img src="<?= $sImg ?>" alt="<?= $sTitle ?>" loading="lazy">
          </div>
          <div class="seasonal-spotlight-content">
            <span class="seasonal-spotlight-label">This Month's Spiritual Focus</span>
            <h3><?= $sTitle ?></h3>
            <p><?= $sDesc ?></p>
            <div class="seasonal-spotlight-actions">
              <a href="<?= BASE_URL ?>donate/<?= $sSlug ?>" class="btn btn-accent"><i class="fas fa-hand-holding-heart"></i> Offer Seva</a>
              <a href="<?= BASE_URL ?>festivals/grand-festivals/<?= $sSlug ?>" class="btn btn-primary"><i class="fas fa-info-circle"></i> View Festival</a>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Featured Festivals -->
      <div class="featured-festivals">
        <div class="featured-festivals-header reveal">
          <h3>Featured Festivals</h3>
          <a href="<?= BASE_URL ?>festivals/grand-festivals" class="btn btn-outline-dark btn-sm">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="featured-festivals-grid">
          <?php foreach ($festivalCauses as $fest): 
            $fSlug = htmlspecialchars($fest['slug']);
            $fTitle = htmlspecialchars($fest['title']);
            $fShort = htmlspecialchars($fest['short_title'] ?? $fest['title']);
            $fDesc = htmlspecialchars(substr($fest['description'], 0, 100)) . '...';
            $fImg = !empty($fest['image_url']) ? htmlspecialchars($fest['image_url']) : 'https://picsum.photos/seed/' . $fSlug . '/400/300';
          ?>
          <div class="festival-card reveal">
            <div class="festival-card-image">
              <img src="<?= $fImg ?>" alt="<?= $fTitle ?>" loading="lazy">
            </div>
            <div class="festival-card-body">
              <h4><?= $fTitle ?></h4>
              <p><?= $fDesc ?></p>
              <div class="festival-card-actions">
                <a href="<?= BASE_URL ?>donate/<?= $fSlug ?>" class="btn btn-primary btn-xs"><i class="fas fa-heart"></i> Offer Seva</a>
                <a href="<?= BASE_URL ?>festivals/grand-festivals/<?= $fSlug ?>" class="btn btn-outline-dark btn-xs"><i class="fas fa-info-circle"></i> View</a>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
