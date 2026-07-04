<?php include_once __DIR__ . '/../config.php'; ?>
<?php
// SEO defaults — pages can override these before including header.php
$metaDescription ??= SITE_NAME . ', ' . SITE_TAGLINE . ' — Official Website. Daily darshan, puja booking, festivals, and spiritual programs since 1998.';
$metaKeywords ??= 'ISKCON, Jagannath Temple, Bangalore Temple, Seshadripuram, Hare Krishna, Hindu Temple Bangalore, Puja Booking, Vaishnavism';
$canonicalUrl ??= BASE_URL . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$ogImage ??= BASE_URL . 'assets/images/og-default.svg';
$ogType ??= 'website';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
  <meta name="theme-color" content="#c86b1f">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <title><?php echo htmlspecialchars($pageTitle ?? SITE_NAME); ?> | <?php echo SITE_NAME; ?></title>
  <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
  <base href="<?php echo BASE_URL; ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle ?? SITE_NAME); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
  <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
  <meta property="og:type" content="<?php echo $ogType; ?>">
  <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
  <meta property="og:locale" content="en_IN">

  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle ?? SITE_NAME); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  <!-- Custom Styles -->
  <link class="cache-buster" rel="stylesheet" href="assets/css/style.css?v=1.1.4">
  <link rel="stylesheet" href="assets/css/responsive.css?v=1.1.4">
  <link rel="stylesheet" href="assets/css/donate.css?v=1.1.4">

  <!-- Cart Module (must load before page-specific scripts) -->
  <script src="assets/js/cart.js"></script>
</head>

<body>

  <!-- Preloader -->
  <div class="preloader">
    <div class="preloader-inner">
      <div class="preloader-om"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON Logo" style="width: 80px; height: 80px; display: block; margin: 0 auto;"></div>
      <div class="preloader-text">Loading...</div>
    </div>
  </div>

  <!-- Site Header -->
  <header class="site-header" id="siteHeader">
    <!-- Top Bar -->
    <div class="top-bar">
      <div class="container">
        <div class="top-bar-left">
          <a href="booking/puja" class="top-bar-link"><i class="fas fa-hands-praying"></i> Book Puja</a>
          <span class="top-bar-sep">|</span>
          <a href="booking/yagya" class="top-bar-link"><i class="fas fa-fire"></i> Book Yagya</a>
          <span class="top-bar-sep">|</span>
          <a href="yatra/" class="top-bar-link"><i class="fas fa-route"></i> Yatra</a>
        </div>
        <div class="top-bar-center">
          <span><i class="fas fa-clock"></i> Temple Timings: 5:00 AM – 8:30 PM</span>
        </div>
        <div class="top-bar-right">
          <span class="top-bar-weather" id="topBarWeather">
            <i class="fas fa-spinner fa-spin"></i>
          </span>
          <a href="checkout/" class="top-bar-cart-link" aria-label="View Cart">
            <i class="fas fa-shopping-cart"></i>
            <span class="top-bar-cart-badge" data-cart-count hidden>0</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Header Inner -->
    <div class="header-inner container-wide">
      <a href="<?php echo BASE_URL; ?>" class="logo" aria-label="Home">
        <img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON The Palace Temple of Lord Jagannath" class="logo-img">
        <div class="logo-text">
          <span class="logo-title logo-title-full">ISKCON</span>
          <span class="logo-subtitle-full">The Palace Temple of Lord Jagannath</span>
        </div>
      </a>

      <nav class="main-nav" aria-label="Main navigation">
        <ul class="main-nav-list">
          <li><a href="<?php echo BASE_URL; ?>">Home</a></li>

          <!-- About -->
          <li class="nav-dropdown">
            <a href="#">About <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
            <ul class="dropdown-menu">
              <li><a href="about">About Us</a></li>
              <li><a href="about/founder-acharya">Founder Acharya</a></li>
              <li><a href="about/our-mission">Our Mission</a></li>
              <li><a href="about/history-of-iskcon">History of ISKCON</a></li>
              <li><a href="about/our-philosophy">Our Philosophy</a></li>
              <li><a href="about/hare-krishna-movement">Hare Krishna Movement</a></li>
              <li><a href="about/temple-schedule">Temple Schedule</a></li>
              <li><a href="about/golden-temple">Golden Temple</a></li>
              <li><a href="about#explore">Explore More</a></li>
            </ul>
          </li>

          <!-- Services -->
          <li class="nav-dropdown">
            <a href="#">Services <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
            <ul class="dropdown-menu">
              <li class="nav-dropdown">
                <a href="services/our-centers">Our Centers <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
                <ul class="dropdown-menu">
                  <li><a href="services/new-rajapur">New Rajapur Jagannatha Dham</a></li>
                  <li><a href="services/bhakti-sadan">Bhakti Sadan</a></li>
                  <li><a href="services/bhakti-vriksha">Bhakti Vriksha</a></li>
                </ul>
              </li>
              <li><a href="services/sunday-feast">The Sunday Feast</a></li>
              <li><a href="services/life-membership">Life Membership</a></li>
              <li class="nav-dropdown">
                <a href="#">Communities <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
                <ul class="dropdown-menu">
                  <li><a href="services/youth-forum">Youth Forum</a></li>
                  <li><a href="services/vaishnavi-forum">Vaishnavi Forum</a></li>
                  <li><a href="services/krishna-fun-school">Krishna Fun School</a></li>
                  <li><a href="services/music-school">Music School</a></li>
                </ul>
              </li>
              <li><a href="services/siksha">Siksha &ndash; Bhakti Steps</a></li>
              <li><a href="services/harinam-sankirtana">Harinam Sankirtana</a></li>
              <li><a href="services/corporate-programs">Corporate Programs</a></li>
              <li><a href="services/govindas-prasadam">Govinda&rsquo;s Prasadam</a></li>
              <li><a href="services/function-hall">Function Hall</a></li>
              <li><a href="services/food-for-life">Food For Life</a></li>
              <!-- Courses moved inside Services -->
              <li class="nav-dropdown">
                <a href="#">Courses <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
                <ul class="dropdown-menu">
                  <li><a href="courses/bhaktivedanta-education">Bhaktivedanta Education</a></li>
                  <li><a href="courses/bhakti-shastri">Bhakti Shastri</a></li>
                  <li><a href="courses/bhakti-vaibhava">Bhakti Vaibhava</a></li>
                  <li><a href="courses/idc">ISKCON Disciple Course (IDC)</a></li>
                  <li><a href="courses/teachers-training">Teachers Training</a></li>
                </ul>
              </li>
            </ul>
          </li>

          <!-- Booking -->
          <li class="nav-dropdown">
            <a href="booking">Booking <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
            <ul class="dropdown-menu">
              <li><a href="booking/puja">Book Puja</a></li>
              <li><a href="booking/yagya">Book Yagya</a></li>
              <li><a href="booking/guest-house">Guest House</a></li>
              <li><a href="yatra/">Yatras</a></li>
            </ul>
          </li>

          <!-- Festivals -->
          <li class="nav-dropdown">
            <a href="festivals/">Festivals <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
            <ul class="dropdown-menu">
              <li><a href="festivals/vaishnava-calendar/">Vaishnava Calendar</a></li>
              <li><a href="festivals/grand-festivals/">Grand Festivals</a></li>
              <li><a href="festivals/ekadashi/">Ekadashi</a></li>
              <li><a href="festivals/appearance/">Appearance Days</a></li>
              <li><a href="festivals/disappearance/">Disappearance Days</a></li>
              <li><a href="festivals/events/">Events</a></li>
            </ul>
          </li>

          <!-- Media -->
          <li class="nav-dropdown">
            <a href="#">Media <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px;"></i></a>
            <ul class="dropdown-menu">
              <li><a href="blogs">Blogs</a></li>
              <li><a href="darshan">Gallery</a></li>
            </ul>
          </li>

        </ul>

        <a href="donate/" class="btn btn-accent btn-sm">
          <i class="fas fa-hand-holding-heart"></i> Donate
        </a>
      </nav>

      <button class="hamburger" aria-label="Toggle menu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </header>

  <!-- Mobile Menu Overlay -->
  <div class="mobile-menu-overlay"></div>

  <!-- Mobile Menu -->
  <div class="mobile-menu">
    <ul class="mobile-menu-list">
      <li><a href="<?php echo BASE_URL; ?>">Home</a></li>

      <!-- About (mobile) -->
      <li class="mobile-submenu">
        <a href="#" onclick="this.nextElementSibling.classList.toggle('open'); return false;">About <i class="fas fa-chevron-down"></i></a>
        <ul class="mobile-sublist">
          <li><a href="about">About Us</a></li>
          <li><a href="about/founder-acharya">Founder Acharya</a></li>
          <li><a href="about/our-mission">Our Mission</a></li>
          <li><a href="about/history-of-iskcon">History of ISKCON</a></li>
          <li><a href="about/our-philosophy">Our Philosophy</a></li>
          <li><a href="about/hare-krishna-movement">Hare Krishna Movement</a></li>
          <li><a href="about/temple-schedule">Temple Schedule</a></li>
          <li><a href="about/golden-temple">Golden Temple</a></li>
          <li><a href="about#explore">Explore More</a></li>
        </ul>
      </li>

      <!-- Services (mobile) -->
      <li class="mobile-submenu">
        <a href="#" onclick="this.nextElementSibling.classList.toggle('open'); return false;">Services <i class="fas fa-chevron-down"></i></a>
        <ul class="mobile-sublist">
          <li><a href="#" onclick="this.nextElementSibling.classList.toggle('open'); return false;">Our Centers <i class="fas fa-chevron-down"></i></a>
            <ul class="mobile-sublist" style="padding-left:var(--space-lg);">
              <li><a href="services/new-rajapur">New Rajapur Jagannatha Dham</a></li>
              <li><a href="services/bhakti-sadan">Bhakti Sadan</a></li>
              <li><a href="services/bhakti-vriksha">Bhakti Vriksha</a></li>
            </ul>
          </li>
          <li><a href="services/sunday-feast">The Sunday Feast</a></li>
          <li><a href="services/life-membership">Life Membership</a></li>
          <li><a href="#" onclick="event.preventDefault(); this.nextElementSibling.classList.toggle('open'); return false;"><strong>Communities</strong> <i class="fas fa-chevron-down"></i></a>
            <ul class="mobile-sublist" style="padding-left:var(--space-lg);">
              <li><a href="services/youth-forum">Youth Forum</a></li>
              <li><a href="services/vaishnavi-forum">Vaishnavi Forum</a></li>
              <li><a href="services/krishna-fun-school">Krishna Fun School</a></li>
              <li><a href="services/music-school">Music School</a></li>
            </ul>
          </li>
          <li><a href="services/siksha">Siksha &ndash; Bhakti Steps</a></li>
          <li><a href="services/harinam-sankirtana">Harinam Sankirtana</a></li>
          <li><a href="services/corporate-programs">Corporate Programs</a></li>
          <li><a href="services/govindas-prasadam">Govinda's Prasadam</a></li>
          <li><a href="services/function-hall">Function Hall</a></li>
          <li><a href="services/food-for-life">Food For Life</a></li>
          <!-- Courses (mobile) moved inside Services -->
          <li class="mobile-submenu" style="padding-left:var(--space-md);">
            <a href="#" onclick="event.preventDefault(); this.nextElementSibling.classList.toggle('open'); return false;">Courses <i class="fas fa-chevron-down"></i></a>
            <ul class="mobile-sublist" style="padding-left:var(--space-lg);">
              <li><a href="courses/bhaktivedanta-education">Bhaktivedanta Education</a></li>
              <li><a href="courses/bhakti-shastri">Bhakti Shastri</a></li>
              <li><a href="courses/bhakti-vaibhava">Bhakti Vaibhava</a></li>
              <li><a href="courses/idc">ISKCON Disciple Course (IDC)</a></li>
              <li><a href="courses/teachers-training">Teachers Training</a></li>
            </ul>
          </li>
        </ul>
      </li>

      <!-- Booking (mobile) -->
      <li class="mobile-submenu">
        <a href="#" onclick="this.nextElementSibling.classList.toggle('open'); return false;">Booking <i class="fas fa-chevron-down"></i></a>
        <ul class="mobile-sublist">
          <li><a href="booking/puja">Book Puja</a></li>
          <li><a href="booking/yagya">Book Yagya</a></li>
          <li><a href="booking/guest-house">Guest House</a></li>
          <li><a href="yatra/">Yatras</a></li>
        </ul>
      </li>

      <!-- Festivals (mobile) -->
      <li class="mobile-submenu">
        <a href="festivals/" onclick="this.nextElementSibling.classList.toggle('open'); return false;">Festivals <i class="fas fa-chevron-down"></i></a>
        <ul class="mobile-sublist">
          <li><a href="festivals/"><strong>Overview</strong></a></li>
          <li><a href="festivals/vaishnava-calendar/">Vaishnava Calendar</a></li>
          <li><a href="festivals/grand-festivals/">Grand Festivals</a></li>
          <li><a href="festivals/ekadashi/">Ekadashi</a></li>
          <li><a href="festivals/appearance/">Appearance Days</a></li>
          <li><a href="festivals/disappearance/">Disappearance Days</a></li>
          <li><a href="festivals/events/">Events</a></li>
        </ul>
      </li>

      <!-- Media (mobile) -->
      <li class="mobile-submenu">
        <a href="#" onclick="this.nextElementSibling.classList.toggle('open'); return false;">Media <i class="fas fa-chevron-down"></i></a>
        <ul class="mobile-sublist">
          <li><a href="blogs">Blogs</a></li>
          <li><a href="darshan">Gallery</a></li>
        </ul>
      </li>

      <li><a href="contact"><i class="fas fa-envelope" style="margin-right:8px;"></i>Contact</a></li>
      <li>
        <a href="checkout/" class="mobile-cart-link">
          <i class="fas fa-cart-shopping"></i> Cart
          <span class="mobile-cart-badge" data-cart-count hidden>0</span>
        </a>
      </li>
    </ul>
    <a href="donate/" class="btn btn-accent">
      <i class="fas fa-hand-holding-heart"></i> Donate
    </a>
  </div>