<?php
/**
 * Homepage Explore Categories Section
 * Shows 6 category tiles linking to major content areas
 */
$categories = getHomepageCategoryTiles();
?>
<section class="section categories-section">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">🔍</span></div>
    <span class="section-subtitle reveal">Discover</span>
    <h2 class="section-title reveal">Explore Temple Offerings &amp; Observances</h2>
    <p class="section-description reveal">From daily worship to grand festivals, discover the many ways to connect with the divine at ISKCON The Palace Temple of Lord Jagannath.</p>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="<?= BASE_URL . htmlspecialchars($cat['link']) ?>" class="category-tile reveal" style="--tile-color: <?= $cat['color'] ?>;">
        <div class="category-tile-icon"><i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i></div>
        <div class="category-tile-content">
          <h3><?= htmlspecialchars($cat['title']) ?></h3>
          <p><?= htmlspecialchars($cat['desc']) ?></p>
        </div>
        <div class="category-tile-arrow"><i class="fas fa-arrow-right"></i></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
