<?php
/**
 * Homepage Hero Section - Dynamic Swiper hero slider with premium banners
 */
$slides = [
  [
    'image' => 'assets/images/banners/banner1.jpg',
    'badge' => '★ Official ISKCON Temple — Seshadripuram, Bangalore',
    'title' => 'The Palace Temple of<br><span class="highlight">Lord Jagannath</span>',
    'subtitle' => 'Experience the divine presence of Lord Sri Jagannath at the official ISKCON Palace Temple of Bangalore since 1998.'
  ],
  [
    'image' => 'assets/images/banners/banner4.jpg',
    'badge' => 'Daily Darshan & Aarti',
    'title' => 'Divine<br><span class="highlight">Association</span>',
    'subtitle' => 'Purify your consciousness with seven Vedic ceremonies daily. Behold the beautiful forms of Sri Sri Jagannath, Baladeva, and Subhadra Devi.'
  ],
  [
    'image' => 'assets/images/banners/banner3.jpg',
    'badge' => 'Offer Seva & Worship',
    'title' => 'Sacred<br><span class="highlight">Service</span>',
    'subtitle' => 'Serve the Lord through various offerings, puja services, and Anna Daan. Support the temple activities and receive divine blessings.'
  ],
  [
    'image' => 'assets/images/banners/banner6.jpg',
    'badge' => 'Grand Celebrations',
    'title' => 'Joyous<br><span class="highlight">Festivals</span>',
    'subtitle' => 'Celebrate the appearances of incarnations, anniversaries of acharyas, and holy observances with ecstatic kirtans and feasts.'
  ],
  [
    'image' => 'assets/images/banners/banner7.jpg',
    'badge' => 'Spiritual Education',
    'title' => 'Vedic<br><span class="highlight">Wisdom</span>',
    'subtitle' => 'Discover the timeless teachings of Bhagavad Gita and Srimad Bhagavatam through classes, seminars, and programs.'
  ]
];
?>
<section class="hero-section" id="home">
  <!-- Swiper Slider -->
  <div class="swiper hero-slider">
    <div class="swiper-wrapper">
      <?php foreach ($slides as $slide): ?>
        <div class="swiper-slide hero-slide">
          <div class="hero-slide-bg" style="background-image: url('<?php echo BASE_URL . $slide['image']; ?>');"></div>
          <div class="hero-overlay"></div>
          <div class="hero-content">
            <span class="hero-badge"><i class="fas fa-om"></i> <?php echo htmlspecialchars($slide['badge']); ?></span>
            <h1 class="hero-title"><?php echo $slide['title']; ?></h1>
            <p class="hero-subtitle"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
            <div class="hero-actions">
              <a href="<?php echo BASE_URL; ?>about/temple-schedule" class="btn btn-accent btn-lg"><i class="fas fa-clock"></i> Plan Your Visit</a>
              <a href="<?php echo BASE_URL; ?>donate" class="btn btn-outline btn-lg"><i class="fas fa-hand-holding-heart"></i> Offer Seva</a>
              <a href="<?php echo BASE_URL; ?>festivals/grand-festivals" class="btn btn-outline btn-lg"><i class="fas fa-star"></i> Explore Festivals</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Navigation & Pagination -->
  <div class="swiper-pagination hero-pagination"></div>
  <div class="swiper-button-prev hero-button-prev"></div>
  <div class="swiper-button-next hero-button-next"></div>

  <!-- Scroll Indicator -->
  <div class="hero-scroll-indicator">
    <span>Scroll</span>
    <div class="scroll-line"></div>
  </div>
</section>
