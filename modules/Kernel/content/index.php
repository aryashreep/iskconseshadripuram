<?php
$pageTitle = 'Official ISKCON Temple — Seshadripuram, Bangalore';
$metaDescription = 'The official ISKCON temple in Seshadripuram, Bangalore — established in 1998 as a branch of ISKCON Juhu, Mumbai. Daily darshan, puja booking, festivals, and spiritual programs at the authorized Palace Temple of Lord Jagannath.';
$pageType = 'home';
include 'partials/header.php';
require_once 'config.php';
?>

<!-- Hero Section -->
<?php include 'partials/home-hero.php'; ?>

<!-- Quick Links -->
<?php include 'partials/home-quick-links.php'; ?>

<!-- About Intro -->
<section class="section welcome-section" id="about">
  <div class="container">
    <div class="section-divider"><span class="divider-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON"></span></div>
    <span class="section-subtitle reveal">Welcome</span>
    <h2 class="section-title reveal">ISKCON The Palace Temple of Lord Jagannath</h2>
    <p class="section-description reveal">A spiritual haven in the heart of Bangalore since 1998.</p>
    <div class="welcome-content">
      <div class="welcome-image reveal">
        <img src="<?php echo BASE_URL; ?>assets/images/banners/about-left.jpg" alt="ISKCON Bangalore Temple" loading="lazy">
      </div>
      <div class="welcome-text reveal">
        <h3>A Beacon of Spiritual Enlightenment</h3>
        <p>Established on January 31, 1998, and inaugurated by His Holiness Jayapataka Swami Maharaj, ISKCON Seshadripuram is a branch of ISKCON Juhu, dedicated to propagating spiritual knowledge and preserving Vedic culture.</p>
        <p>The temple serves as a spiritual and cultural hub, hosting regular religious ceremonies, kirtans, discourses, and spiritual gatherings that inspire individuals to lead a spiritually conscious life.</p>
        <div class="welcome-features">
          <div class="welcome-feature"><div class="welcome-feature-icon"><i class="fas fa-temple"></i></div><div class="welcome-feature-text"><h4>Palace Temple</h4><p>Of Lord Jagannath in Bangalore</p></div></div>
          <div class="welcome-feature"><div class="welcome-feature-icon"><i class="fas fa-hands-praying"></i></div><div class="welcome-feature-text"><h4>Daily Aarti</h4><p>Seven Vedic ceremonies daily</p></div></div>
          <div class="welcome-feature"><div class="welcome-feature-icon"><i class="fas fa-users"></i></div><div class="welcome-feature-text"><h4>Global Community</h4><p>Thousands of devotees</p></div></div>
          <div class="welcome-feature"><div class="welcome-feature-icon"><i class="fas fa-book"></i></div><div class="welcome-feature-text"><h4>Vedic Wisdom</h4><p>Bhagavad Gita teachings</p></div></div>
        </div>
        <a href="about" class="btn btn-primary mt-lg"><i class="fas fa-info-circle"></i> Learn More</a>
      </div>
    </div>
  </div>
</section>

<!-- Explore Categories -->
<?php include 'partials/home-category-grid.php'; ?>

<!-- Ways to Serve -->
<?php include 'partials/home-service-cards.php'; ?>

<!-- Seasonal Spotlight + Featured Festivals -->
<?php include 'partials/home-seasonal-spotlight.php'; ?>

<!-- Temple Schedule Quick View -->
<section class="section events-section">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">⏰</span></div>
    <span class="section-subtitle reveal">Temple Schedule</span>
    <h2 class="section-title reveal">Daily Timings</h2>
    <p class="section-description reveal">Experience the divine presence of Lord Jagannath throughout the day with our daily ceremonies.</p>
    <div class="visit-grid text-left">
      <?php 
      // Show the 6 most important timings (skip less critical ones)
      $keyTimings = array_filter($TEMPLE_SCHEDULE, function($item) {
        $keyActivities = ['Mangal Arati', 'Shringar Arati', 'Raj Bhoga Arati', 'Sandhya Arati', 'Bhagavad Gita Class', 'Shayan Arati'];
        return in_array($item['activity'], $keyActivities);
      });
      foreach ($keyTimings as $item): 
      ?>
      <div class="visit-card reveal text-center">
        <div class="visit-card-icon mb-md"><i class="fas fa-clock"></i></div>
        <h4><?php echo htmlspecialchars($item['time']); ?></h4>
        <p><strong><?php echo htmlspecialchars($item['activity']); ?></strong></p>
        <p class="fs-xs"><?php echo htmlspecialchars($item['desc']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="reveal text-center mt-2xl">
      <a href="about/temple-schedule" class="btn btn-primary"><i class="fas fa-clock"></i> View Full Schedule</a>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section section-dark">
  <div class="container">
    <div class="section-divider"><span class="divider-icon">💛</span></div>
    <span class="section-subtitle reveal text-accent-light">Support the Mission</span>
    <h2 class="section-title reveal">Support Daily Worship, Festivals &amp; Prasadam Distribution</h2>
    <p class="section-description reveal">Your generous support helps us maintain the temple, serve prasadam to thousands, and spread the divine message of love and devotion worldwide.</p>
    <div class="cta-actions reveal">
      <a href="donate/daily-seva" class="btn btn-accent btn-lg"><i class="fas fa-hands-praying"></i> Daily Seva</a>
      <a href="donate/food-for-life" class="btn btn-accent btn-lg"><i class="fas fa-utensils"></i> Food for Life</a>
      <a href="donate" class="btn btn-outline btn-lg"><i class="fas fa-hand-holding-heart"></i> All Donations</a>
    </div>
  </div>
</section>

<?php include 'partials/footer.php'; ?>
