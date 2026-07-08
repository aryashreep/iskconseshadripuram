<?php
/**
 * Dynamic Festival Detail Page
 * Reads festival content from donation_causes table (content_body column).
 * 
 * Usage: festivals/detail.php?slug=janmashtami
 * Uses the same URL pattern as the existing hardcoded pages.
 */

require_once __DIR__ . '/../../../config.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    // Redirect to festivals listing
    header('Location: ' . BASE_URL . 'festivals/');
    exit;
}

// Fetch festival data from DB
$festival = getFestivalDetail($slug);

// If not found, try category-based fallback for existing hardcoded pages
if (!$festival) {
    // Check if the slug corresponds to a festival category
    $festival = getDonationCauseBySlug($slug);
}

// If still not found, 404
if (!$festival) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Festival Not Found';
    include '../partials/header.php';
    ?>
    <section class="page-header">
      <div class="container">
        <h1>Festival Not Found</h1>
        <p style="color:rgba(255,255,255,0.8);">The festival you're looking for could not be found.</p>
        <a href="<?php echo BASE_URL; ?>festivals/" class="btn btn-accent" style="margin-top:var(--space-lg);">Browse All Festivals</a>
      </div>
    </section>
    <?php
    include '../partials/footer.php';
    exit;
}

$metaDescription = htmlspecialchars(mb_substr($festival['description'] ?? $festival['title'], 0, 160));
$ogImage = !empty($festival['image_url']) ? BASE_URL . $festival['image_url'] : (BASE_URL . 'assets/images/iskcon_logo.svg');
$pageType = 'festival';
$pageTitle = $festival['meta_title'] ?: $festival['title'] . ' - ISKCON The Palace Temple of Lord Jagannath';

// Determine category label
$catLabel = '';
$catBadge = '';
switch ($festival['category']) {
    case 'festival': $catLabel = 'Grand Festival'; $catBadge = '🚩'; break;
    case 'appearance': $catLabel = 'Appearance Day'; $catBadge = '🪷'; break;
    case 'disappearance': $catLabel = 'Disappearance Day'; $catBadge = '🌟'; break;
    case 'ekadashi': $catLabel = 'Ekadashi'; $catBadge = '🌙'; break;
    case 'event': $catLabel = 'Special Event'; $catBadge = '📜'; break;
    default: $catLabel = 'Festival'; $catBadge = '🎉';
}

// Parse quick stats if available
$quickStats = $festival['quick_stats'] ? parseQuickStats($festival['quick_stats']) : [];

// Breadcrumb path
$categorySlug = $festival['category'];
$categoryMap = [
    'festival' => 'grand-festivals',
    'appearance' => 'appearance',
    'disappearance' => 'disappearance',
    'ekadashi' => 'ekadashi',
    'event' => 'events',
];
$catPath = $categoryMap[$categorySlug] ?? 'grand-festivals';
$catName = $catLabel . 's';

// Prepare Event data for schema.org structured data
$eventData = null;
if ($festival) {
    $eventName = $festival['title'];
    $eventDesc = mb_substr($festival['description'] ?? $festival['title'], 0, 300);
    $eventImage = !empty($festival['image_url']) ? BASE_URL . $festival['image_url'] : (BASE_URL . 'assets/images/iskcon_logo.svg');
    $eventUrl = BASE_URL . 'festivals/' . $catPath . '/' . urlencode($slug);
    $eventStartDate = '';
    $eventEndDate = '';
    if (!empty($festival['start_date'])) {
        $eventStartDate = $festival['start_date'];
        $eventEndDate = $festival['end_date'] ?? $festival['start_date'];
    } else {
        // If no DB dates, use a generic yearly occurrence
        $eventStartDate = date('Y-01-01');
        $eventEndDate = date('Y-12-31');
    }
    $eventData = [
        'name' => $eventName,
        'description' => $eventDesc,
        'image' => $eventImage,
        'url' => $eventUrl,
        'startDate' => $eventStartDate,
        'endDate' => $eventEndDate,
        'category' => $festival['category'],
        'offers' => [
            '@type' => 'Offer',
            'url' => BASE_URL . 'donate/' . urlencode($slug),
            'price' => '0',
            'priceCurrency' => 'INR',
            'availability' => 'https://schema.org/OnlineOnly',
            'validFrom' => $eventStartDate,
        ],
        'organizer' => [
            '@type' => 'HinduTemple',
            'name' => SITE_NAME,
            'url' => BASE_URL,
        ],
        'location' => [
            '@type' => 'Place',
            'name' => SITE_NAME,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '159, 1st Main road, Beside TRUGAS, Seshadripuram',
                'addressLocality' => 'Bengaluru',
                'addressRegion' => 'Karnataka',
                'postalCode' => '560020',
                'addressCountry' => 'IN',
            ],
        ],
    ];
}

include '../partials/header.php';
?>

<!-- Custom Page Header with Hero Banner -->
<section class="page-header" style="position:relative; overflow:hidden; padding: var(--space-4xl) 0; text-align:center;">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL . ($festival['image_url'] ?? 'assets/images/banners/calendar.jpg'); ?>'); background-size: cover; background-position: center; position: absolute; inset:0; z-index:-1; filter: brightness(0.4) contrast(1.1);"></div>
  <div class="container" style="position:relative; z-index:1;">
    <span style="display:inline-block; background:rgba(200, 107, 31, 0.2); border:1px solid var(--primary); color:var(--accent-light); padding:6px 16px; border-radius:var(--radius-xl); font-size:var(--font-size-xs); font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-md); backdrop-filter:blur(4px);"><?php echo $catBadge; ?> <?php echo $catLabel; ?></span>
    <h1 class="reveal" style="font-family:var(--font-heading); color:var(--white); font-size:calc(var(--font-size-3xl) + 1vw); line-height:1.2; text-shadow:0 2px 10px rgba(0,0,0,0.6); max-width:900px; margin:0 auto var(--space-md) auto;"><?php echo htmlspecialchars($festival['title']); ?></h1>
    <div class="breadcrumb reveal" style="display:flex; justify-content:center; gap:8px; color:rgba(255,255,255,0.8); font-size:var(--font-size-sm);">
      <a href="<?php echo BASE_URL; ?>" style="color:var(--accent-light);">Home</a><span>›</span>
      <a href="<?php echo BASE_URL; ?>festivals/" style="color:var(--accent-light);">Festivals</a><span>›</span>
      <a href="<?php echo BASE_URL; ?>festivals/<?php echo $catPath; ?>/" style="color:var(--accent-light);"><?php echo $catName; ?></a><span>›</span>
      <span style="color:var(--white);"><?php echo htmlspecialchars($festival['short_title'] ?: $festival['title']); ?></span>
    </div>
  </div>
</section>

<!-- Main Page Content -->
<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:850px; background:var(--white); padding:var(--space-2xl) var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border:1px solid var(--border);">
    
    <!-- Banner Image -->
    <?php if ($festival['image_url']): ?>
    <div style="margin-bottom:var(--space-xl); text-align:center; overflow:hidden; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid var(--border);">
      <img src="<?php echo BASE_URL . $festival['image_url']; ?>" alt="<?php echo htmlspecialchars($festival['title']); ?> Banner" style="width:100%; height:auto; display:block;">
    </div>
    <?php endif; ?>

    <!-- Quick Stats / Info Box -->
    <?php if (!empty($quickStats)): ?>
    <div style="background:var(--cream); border-left:4px solid var(--primary); padding:var(--space-lg); border-radius:var(--radius-sm); margin-bottom:var(--space-2xl); display:flex; flex-direction:column; gap:10px;">
      <h4 style="margin:0; color:var(--primary); font-family:var(--font-heading); font-weight:600; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-info-circle"></i> <?php echo $festival['category'] === 'festival' ? 'Vrata & Worship Overview' : 'Overview'; ?>
      </h4>
      <p style="margin:0; font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.6;">
        <?php foreach ($quickStats as $key => $value): ?>
          <strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?><br>
        <?php endforeach; ?>
        <?php if (!empty($festival['is_time_bound'])): ?>
          <strong>Observance:</strong> Held on the scheduled event date. Please check the <a href="<?php echo BASE_URL; ?>festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a> for the exact day this year.
        <?php endif; ?>
      </p>
    </div>
    <?php elseif ($festival['description']): ?>
    <!-- Fallback description -->
    <div style="background:var(--cream); border-left:4px solid var(--primary); padding:var(--space-lg); border-radius:var(--radius-sm); margin-bottom:var(--space-2xl);">
      <h4 style="margin:0 0 var(--space-sm) 0; color:var(--primary); font-family:var(--font-heading); font-weight:600; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-info-circle"></i> About This Festival
      </h4>
      <p style="margin:0; font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.6;"><?php echo nl2br(htmlspecialchars($festival['description'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Article Body -->
    <?php if (!empty($festival['content_body'])): ?>
    <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8; display:flex; flex-direction:column; gap:var(--space-lg);">
      <?php echo str_replace('{{BASE_URL}}', BASE_URL, $festival['content_body']); ?>
    </article>
    <?php else: ?>
    <!-- Fallback content from DB fields -->
    <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8;">
      <?php if ($festival['history']): ?>
        <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Historical Background</h3>
        <p><?php echo nl2br(htmlspecialchars($festival['history'])); ?></p>
      <?php endif; ?>
      
      <?php if ($festival['significance']): ?>
        <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Spiritual Significance</h3>
        <p><?php echo nl2br(htmlspecialchars($festival['significance'])); ?></p>
      <?php endif; ?>
      
      <?php if ($festival['benefits']): ?>
        <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Blessings &amp; Benefits</h3>
        <p><?php echo nl2br(htmlspecialchars($festival['benefits'])); ?></p>
      <?php endif; ?>
    </article>
    <?php endif; ?>

    <!-- Donation Section -->
    <?php 
    include_once __DIR__ . '/../../../partials/donation-cta.php';
    if (function_exists('renderDonationSection')) {
      renderDonationSection([
        'cause_slug' => $slug,
        'button_label' => 'Offer Seva for ' . ($festival['short_title'] ?: $festival['title']),
        'background' => 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)'
      ]);
    } else {
      // Fallback CTA
      ?>
      <div style="text-align:center; margin-top:var(--space-2xl); padding:var(--space-2xl); background:linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%); border-radius:var(--radius-lg);">
        <h3 style="color:var(--white); margin-bottom:var(--space-md);">Support This Festival</h3>
        <p style="color:rgba(255,255,255,0.85); margin-bottom:var(--space-lg);">Your generous contribution helps us celebrate with grandeur and distribute prasadam to all.</p>
        <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($slug); ?>" class="btn btn-accent btn-lg"><i class="fas fa-heart"></i> Offer Seva</a>
      </div>
      <?php
    }
    ?>

  </div>
</section>

<?php include '../partials/footer.php'; ?>
