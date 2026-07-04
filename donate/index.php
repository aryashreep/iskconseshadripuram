<?php
$pageTitle = 'Donate & Offer Seva - ISKCON The Palace Temple of Lord Jagannath';
$metaDescription = 'Support ISKCON The Palace Temple of Lord Jagannath in Bangalore. Donate for festivals, daily worship, prasadam distribution and temple maintenance. Offer online seva.';
$pageType = 'donate';
include '../partials/header.php';
require_once '../config.php';

// Load causes from DB, grouped by category
$groupedCauses = getDonationCausesGrouped();
$allCauses = getDonationCauses();

// Category display order
$categoryOrder = ['festival', 'ekadashi', 'appearance', 'disappearance', 'event', 'service', 'construction', 'general'];

// Build ordered sections list
$sections = [];
foreach ($categoryOrder as $cat) {
    if (isset($groupedCauses[$cat]) && !empty($groupedCauses[$cat])) {
        $catInfo = getCauseCategoryInfo($cat);
        $sections[] = [
            'slug' => $cat,
            'label' => $catInfo['label'],
            'icon' => $catInfo['icon'],
            'causes' => $groupedCauses[$cat],
            'count' => count($groupedCauses[$cat]),
        ];
    }
}
// Add any remaining categories not in the order
foreach ($groupedCauses as $cat => $causes) {
    if (!in_array($cat, $categoryOrder)) {
        $catInfo = getCauseCategoryInfo($cat);
        $sections[] = [
            'slug' => $cat,
            'label' => $catInfo['label'],
            'icon' => $catInfo['icon'],
            'causes' => $causes,
            'count' => count($causes),
        ];
    }
}
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/donate-overview/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">Donate &amp; Offer Seva</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a>
      <span>›</span>
      <span>Donate &amp; Offer Seva</span>
    </div>
  </div>
</section>

<!-- Donate Overview -->
<section class="donate-overview">
  <div class="container">
    <div class="section-divider">
      <span class="divider-icon">🙏</span>
    </div>
    <div class="donate-intro-text">
      <span class="section-subtitle reveal">Give with Love</span>
      <h2 class="section-title reveal">Choose Your Seva</h2>
      <p class="reveal">
        Every contribution, no matter how small, is a sacred offering to the divine. 
        Select the seva that resonates with your heart and make a meaningful difference 
        in the spiritual mission.
      </p>
    </div>

    <?php if (!empty($allCauses)): ?>
      <!-- Tab Navigation -->
      <div class="donate-tabs" role="tablist">
        <button class="tab-pill active" role="tab" aria-selected="true" aria-controls="tab-all" data-tab="all" onclick="switchTab('all')">
          <i class="fas fa-th-large"></i> All
        </button>
        <?php foreach ($sections as $section): ?>
        <button class="tab-pill" role="tab" aria-selected="false" aria-controls="tab-<?php echo $section['slug']; ?>" data-tab="<?php echo $section['slug']; ?>" onclick="switchTab('<?php echo $section['slug']; ?>')">
          <i class="fas <?php echo $section['icon']; ?>"></i>
          <?php echo htmlspecialchars($section['label']); ?>
          <span class="tab-count"><?php echo $section['count']; ?></span>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Tab Content -->
      <div class="donate-tab-content">                      <!-- All tab (default active) - Accordion Layout -->
        <div class="tab-panel active" id="tab-all" role="tabpanel">
          <div class="donate-accordion">
            <?php $firstSection = true; ?>
            <?php foreach ($sections as $section): ?>
            <div class="accordion-item <?php echo $firstSection ? 'active' : ''; ?>">
              <button class="accordion-header" onclick="toggleAccordion(this)" aria-expanded="<?php echo $firstSection ? 'true' : 'false'; ?>">
                <span class="accordion-header-left">
                  <span class="tab-section-icon"><i class="fas <?php echo $section['icon']; ?>"></i></span>
                  <span class="tab-section-label"><?php echo htmlspecialchars($section['label']); ?></span>
                </span>
                <span class="accordion-header-right">
                  <span class="tab-section-count"><?php echo $section['count']; ?> <?php echo $section['count'] === 1 ? 'cause' : 'causes'; ?></span>
                  <i class="fas fa-chevron-down accordion-arrow"></i>
                </span>
              </button>
              <div class="accordion-body" <?php echo $firstSection ? 'style="max-height: none;"' : ''; ?>>
                <div class="accordion-body-inner">
                  <div class="seva-card-grid">
                    <?php foreach ($section['causes'] as $cause): ?>
                    <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($cause['slug']); ?>" class="seva-card reveal">
                      <div class="seva-card-image">
                        <img src="<?php echo $cause['image_url'] ?: 'https://picsum.photos/seed/' . $cause['slug'] . '/600/400'; ?>" alt="<?php echo htmlspecialchars($cause['title']); ?>" loading="lazy">
                        <span class="seva-card-badge"><?php echo $cause['allow_monthly'] ? 'One-Time / Monthly' : 'One-Time'; ?></span>
                      </div>
                      <div class="seva-card-body">
                        <div class="seva-card-icon"><i class="fas fa-hand-holding-heart"></i></div>
                        <h3><?php echo htmlspecialchars($cause['short_title'] ?: $cause['title']); ?></h3>
                        <?php if ($cause['short_title'] && $cause['short_title'] !== $cause['title']): ?>
                          <p class="seva-card-subtitle"><?php echo htmlspecialchars($cause['title']); ?></p>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars(mb_substr($cause['description'] ?? '', 0, 150)) . '...'; ?></p>
                        <span class="btn btn-primary btn-sm">Donate Now <i class="fas fa-arrow-right"></i></span>
                      </div>
                    </a>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php $firstSection = false; ?>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Individual category tabs -->
        <?php foreach ($sections as $section): ?>
        <div class="tab-panel" id="tab-<?php echo $section['slug']; ?>" role="tabpanel">
          <div class="tab-section-header" style="justify-content:center; margin-bottom:var(--space-xl);">
            <span class="tab-section-icon"><i class="fas <?php echo $section['icon']; ?>"></i></span>
            <span class="tab-section-label"><?php echo htmlspecialchars($section['label']); ?></span>
            <span class="tab-section-count"><?php echo $section['count']; ?> <?php echo $section['count'] === 1 ? 'cause' : 'causes'; ?></span>
          </div>
          <div class="seva-card-grid">
            <?php foreach ($section['causes'] as $cause): ?>
            <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($cause['slug']); ?>" class="seva-card reveal">
              <div class="seva-card-image">
                <img src="<?php echo $cause['image_url'] ?: 'https://picsum.photos/seed/' . $cause['slug'] . '/600/400'; ?>" alt="<?php echo htmlspecialchars($cause['title']); ?>" loading="lazy">
                <span class="seva-card-badge"><?php echo $cause['allow_monthly'] ? 'One-Time / Monthly' : 'One-Time'; ?></span>
              </div>
              <div class="seva-card-body">
                <div class="seva-card-icon"><i class="fas fa-hand-holding-heart"></i></div>
                <h3><?php echo htmlspecialchars($cause['short_title'] ?: $cause['title']); ?></h3>
                <?php if ($cause['short_title'] && $cause['short_title'] !== $cause['title']): ?>
                  <p class="seva-card-subtitle"><?php echo htmlspecialchars($cause['title']); ?></p>
                <?php endif; ?>
                <p><?php echo htmlspecialchars(mb_substr($cause['description'] ?? '', 0, 150)) . '...'; ?></p>
                <span class="btn btn-primary btn-sm">Donate Now <i class="fas fa-arrow-right"></i></span>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <!-- Fallback to legacy SEVA_TYPES if no DB -->
      <div class="seva-card-grid">
        <?php foreach ($SEVA_TYPES as $key => $s): ?>
        <a href="<?php echo BASE_URL; ?>donate/<?php echo $key; ?>" class="seva-card reveal">
          <div class="seva-card-image">
            <img src="<?php echo $s['image']; ?>" alt="<?php echo htmlspecialchars($s['name']); ?>" loading="lazy">
            <span class="seva-card-badge"><?php echo htmlspecialchars($s['subtitle']); ?></span>
          </div>
          <div class="seva-card-body">
            <div class="seva-card-icon"><i class="fas <?php echo $s['icon']; ?>"></i></div>
            <h3><?php echo htmlspecialchars($s['name']); ?></h3>
            <p><?php echo htmlspecialchars($s['short_desc']); ?></p>
            <span class="btn btn-primary btn-sm">Donate Now <i class="fas fa-arrow-right"></i></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Bottom CTA -->
<section class="cta-section section-dark">
  <div class="container">
    <div class="section-divider">
      <span class="divider-icon">💛</span>
    </div>
    <h2 class="section-title reveal" style="color: var(--white);">Every Contribution Matters</h2>
    <p class="section-description reveal" style="color: rgba(255,255,255,0.8);">
      Whether through service, donation, or simply your prayers — every act of devotion 
      contributes to the divine mission. If you have any questions, please reach out.
    </p>
    <div class="cta-actions reveal">
      <a href="<?php echo BASE_URL; ?>contact" class="btn btn-accent btn-lg">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
      <a href="<?php echo BASE_URL; ?>" class="btn btn-outline btn-lg">
        <i class="fas fa-home"></i> Return Home
      </a>
    </div>
  </div>
</section>

<script>
function switchTab(tabSlug) {
  // Update URL hash (for bookmarking and refresh persistence)
  if (tabSlug === 'all') {
    history.replaceState(null, '', window.location.pathname);
  } else {
    history.replaceState(null, '', '#' + tabSlug);
  }
  
  // Update tab pills
  document.querySelectorAll('.tab-pill').forEach(function(pill) {
    var isActive = pill.getAttribute('data-tab') === tabSlug;
    pill.classList.toggle('active', isActive);
    pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
  });
  
  // Update tab panels
  document.querySelectorAll('.tab-panel').forEach(function(panel) {
    var isActive = panel.id === 'tab-' + tabSlug;
    panel.classList.toggle('active', isActive);
  });

  // Re-trigger reveal animations on the newly visible panel
  if (tabSlug === 'all') {
    // Refresh accordion open state for smooth reveal
    var activeItems = document.querySelectorAll('#tab-all .accordion-item.active');
    activeItems.forEach(function(item) {
      var body = item.querySelector('.accordion-body');
      if (body && !body.style.maxHeight || body.style.maxHeight === '0px') {
        body.style.maxHeight = body.scrollHeight + 'px';
      }
    });
  }
}

// Accordion toggle – only one section open at a time
function toggleAccordion(header) {
  var item = header.closest('.accordion-item');
  var accordion = item.closest('.donate-accordion');
  var isActive = item.classList.contains('active');
  
  // Close all items in this accordion
  accordion.querySelectorAll('.accordion-item').forEach(function(other) {
    other.classList.remove('active');
    var otherBody = other.querySelector('.accordion-body');
    if (otherBody) {
      otherBody.style.maxHeight = '0px';
    }
    var otherBtn = other.querySelector('.accordion-header');
    if (otherBtn) {
      otherBtn.setAttribute('aria-expanded', 'false');
    }
  });
  
  // If the clicked item was not active, open it
  if (!isActive) {
    item.classList.add('active');
    var body = item.querySelector('.accordion-body');
    if (body) {
      body.style.maxHeight = body.scrollHeight + 'px';
    }
    header.setAttribute('aria-expanded', 'true');
  }
}

// Helper to animate accordion body heights on window resize
function refreshAccordionHeights() {
  document.querySelectorAll('.accordion-item.active .accordion-body').forEach(function(body) {
    body.style.maxHeight = body.scrollHeight + 'px';
  });
}

// Restore tab from URL hash on page load
document.addEventListener('DOMContentLoaded', function() {
  var hash = window.location.hash.replace('#', '');
  if (hash && document.getElementById('tab-' + hash)) {
    switchTab(hash);
  }
});

// Refresh accordion heights on window resize (for responsive images loading)
window.addEventListener('resize', function() {
  refreshAccordionHeights();
});
</script>

<?php include '../partials/footer.php'; ?>
