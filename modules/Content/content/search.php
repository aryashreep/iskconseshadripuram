<?php
$pageTitle = 'Search - ISKCON The Palace Temple of Lord Jagannath';
$metaDescription = 'Search the ISKCON The Palace Temple of Lord Jagannath website for festivals, sevas, blogs, services, and more.';
$pageType = 'default';
include 'partials/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/search.css?v=1.0.4">

<!-- Page Header -->
<section class="page-header search-header">
  <div class="page-header-bg search-header-bg"></div>
  <div class="container search-header-container">
    <div class="search-badge">
      <i class="fas fa-search"></i> Site Search
    </div>
    <h1 class="reveal search-header-title">Search the Temple Website</h1>
    <p class="search-header-desc">
      Find festivals, seva opportunities, blogs, services, and more
    </p>
    <div class="breadcrumb reveal search-breadcrumb">
      <a href="<?php echo BASE_URL; ?>" class="search-breadcrumb-link">Home</a>
      <span>›</span>
      <span class="search-breadcrumb-current">Search</span>
    </div>
  </div>
</section>

<!-- Search Section -->
<section class="page-content">
  <div class="container search-content-container">
    <div class="reveal text-center mb-xl">
      <div class="section-divider">
        <span class="divider-icon"><i class="fas fa-search"></i></span>
      </div>
      <h2>What are you looking for?</h2>
      <p class="search-intro-desc">
        Search across all pages, festivals, blogs, seva opportunities, and services
        of ISKCON The Palace Temple of Lord Jagannath.
      </p>
    </div>

    <!-- Google Custom Search -->
    <div class="reveal search-box-card">
      <script async src="https://cse.google.com/cse.js?cx=10c38c480d82e4cae"></script>
      <div class="gcse-search"></div>
    </div>

    <!-- Tips -->
    <div class="reveal search-tips-card">
      <p class="search-tips-text">
        <i class="fas fa-lightbulb search-tips-icon"></i>
        <strong>Search tips:</strong> Try searching for festival names like "Janmashtami", sevas like "Annadanam", 
        or general terms like "darshan", "booking", "donate" to find what you need.
      </p>
    </div>
  </div>
</section>

<?php include 'partials/footer.php'; ?>
