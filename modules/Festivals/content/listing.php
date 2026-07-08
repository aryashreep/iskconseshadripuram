<?php
/**
 * Unified Festival Category Listing Template
 * 
 * Used by: grand-festivals, ekadashi, appearance, disappearance, events
 * 
 * Expected variables (set by the including page):
 *   $pageTitle     - Page title (string)
 *   $listConfig    - Category display config (array):
 *     - category      - DB category slug (e.g. 'festival', 'ekadashi')  
 *     - title         - Heading (e.g. 'Grand Festivals')
 *     - icon          - Section divider icon (e.g. '🚩', '🌙')
 *     - description   - Section description text
 *     - cardLayout    - 'image_card' | 'bordered_card' | 'vertical_card' | 'icon_card'
 *     - showSearch    - bool, enable search/filter
 *     - searchFields  - array of data-attr field names to search
 *     - searchPlaceholder - Search input placeholder text
 *     - emptyMessage  - Text when no items
 *     - linkField     - Key name for detail link in each item (default: 'link')
 *     - slugField     - Key name for slug in each item (default: null, uses linkField)
 *     - donationSlug  - Donation slug for items without individual donation links
 *     - infoBox       - HTML string for info/guidance box (optional)
 *     - metadataKeys  - Array of keys to show as metadata chips (optional)
 *     - metadataLabels- Labels for metadata keys (optional)
 *   $listItems     - Array of item arrays, each with:
 *     - slug         - Item slug for links (if different from link)
 *     - title        - Item title
 *     - link         - Detail page URL (optional, item may not have it)
 *     - desc         - Description text (optional)
 *     - image        - Image URL for image_card layout (optional)
 *     - donationSlug - Donation slug override (optional)
 *     - Any additional metadata fields defined in metadataKeys
 */

require_once __DIR__ . '/../../../config.php';

// Guard: redirect to festivals index if accessed directly without config
if (!isset($listConfig) || !isset($listItems)) {
    header('Location: ' . BASE_URL . 'festivals/');
    exit;
}

// Shortcut aliases
$cat = $listConfig;
?>
<!--- Unified Category Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/rasa-lila.jpg');"></div>
  <div class="container">
    <h1 class="reveal"><?php echo htmlspecialchars($cat['title']); ?></h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/">Festivals</a><span>›</span><span><?php echo htmlspecialchars($cat['title']); ?></span>
    </div>
  </div>
</section>

<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:<?php echo $cat['cardLayout'] === 'image_card' ? '1100' : '850'; ?>px;">
    <div style="text-align:center;margin-bottom:var(--space-2xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon"><?php echo $cat['icon']; ?></span></div>
      <h2 style="font-family:var(--font-heading); color:var(--dark); font-weight:600;"><?php echo htmlspecialchars($cat['title']); ?></h2>
      <p class="section-description" style="max-width:650px; margin:var(--space-sm) auto 0 auto; color:var(--text); line-height:1.6;">
        <?php echo htmlspecialchars($cat['description']); ?>
      </p>
    </div>

    <?php if (!empty($cat['infoBox'])): ?>
    <!-- Info Box -->
    <div class="reveal" style="background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);margin-bottom:var(--space-xl);font-size:var(--font-size-sm);line-height:1.7;color:var(--text-dark);border-left:4px solid var(--primary);box-shadow:var(--shadow-sm);">
      <?php echo $cat['infoBox']; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($cat['showSearch'])): ?>
    <!-- Interactive Search Bar -->
    <div class="reveal" style="margin-bottom:var(--space-xl); position:relative;">
      <input type="text" id="festivalSearch" placeholder="<?php echo htmlspecialchars($cat['searchPlaceholder'] ?? 'Search...'); ?>" style="width:100%; padding: 12px 20px 12px 45px; border-radius:var(--radius-md); border:1px solid var(--border); font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text-dark); background:var(--white); box-shadow:var(--shadow-sm); outline:none; transition:all var(--transition-base);" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='var(--shadow-md)';" onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='var(--shadow-sm)';" onkeyup="filterFestivalCards()">
      <i class="fas fa-search" style="position:absolute; left:18px; top:50%; transform:translateY(-50%); color:var(--text-light); font-size:16px;"></i>
    </div>
    <?php endif; ?>

    <?php if (!empty($listItems)): ?>
    <!-- Cards Container -->
    <div id="festivalCardsContainer" class="reveal <?php echo $cat['cardLayout'] === 'image_card' ? 'events-grid' : ''; ?>" style="
      <?php if ($cat['cardLayout'] === 'image_card'): ?>
        display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:var(--space-xl); margin-bottom:var(--space-3xl);
      <?php elseif ($cat['cardLayout'] === 'bordered_card'): ?>
        display:grid; grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:var(--space-lg); margin-bottom:var(--space-3xl);
      <?php elseif ($cat['cardLayout'] === 'vertical_card'): ?>
        display:flex; flex-direction:column; gap:18px; margin-bottom:var(--space-3xl);
      <?php elseif ($cat['cardLayout'] === 'icon_card'): ?>
        display:flex; flex-direction:column; gap:var(--space-xl); margin-bottom:var(--space-3xl);
      <?php endif; ?>
    ">
      <?php foreach ($listItems as $idx => $item): 
        $hasLink = !empty($item[$cat['linkField'] ?? 'link']);
        $hasImage = !empty($item['image']);
        $hasDonation = !empty($item['donationSlug']);
        $donationSlug = $hasDonation ? $item['donationSlug'] : ($cat['donationSlug'] ?? '');
        $itemSlug = $item['slug'] ?? '';
        $itemTitle = $item['title'] ?? $item['name'] ?? '';
        $itemDesc = $item['desc'] ?? '';
      ?>

      <?php if ($cat['cardLayout'] === 'image_card'): ?>
      <!-- Image Card (Grand Festivals) -->
      <div class="event-card" data-title="<?php echo htmlspecialchars($itemTitle); ?>" data-desc="<?php echo htmlspecialchars($itemDesc); ?>" style="background:var(--white); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); border:1px solid var(--border); display:flex; flex-direction:column; transition:all var(--transition-base);"
           onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-4px)'; this.style.borderColor='var(--primary-light)';"
           onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';"
      >
        <?php if ($hasImage): ?>
        <div style="position:relative; height:220px; overflow:hidden; background:var(--dark);">
          <img src="<?php echo BASE_URL . $item['image']; ?>" alt="<?php echo htmlspecialchars($itemTitle); ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover; transition:transform var(--transition-base);" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';" onerror="this.src='<?php echo BASE_URL; ?>assets/images/banners/calendar.jpg';">
        </div>
        <?php endif; ?>
        <div style="padding:var(--space-lg); display:flex; flex-direction:column; gap:var(--space-sm); flex-grow:1;">
          <h3 style="margin:0; font-family:var(--font-heading); color:var(--dark); font-size:var(--font-size-lg); font-weight:600;"><?php echo htmlspecialchars($itemTitle); ?></h3>
          <p style="margin:0; color:var(--text); font-size:var(--font-size-base); line-height:1.6; flex-grow:1;"><?php echo htmlspecialchars(mb_substr($itemDesc, 0, 200)) . (mb_strlen($itemDesc) > 200 ? '...' : ''); ?></p>
          <div style="margin-top:var(--space-sm); display:flex; gap:var(--space-sm); flex-wrap:wrap;">
            <?php if (!empty($donationSlug)): ?>
            <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($donationSlug); ?>" class="btn btn-accent btn-sm" style="text-decoration:none;"><i class="fas fa-heart"></i> Offer Seva</a>
            <?php endif; ?>
            <?php if ($hasLink): ?>
            <a href="<?php echo BASE_URL . $item['link']; ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none;"><i class="fas fa-info-circle"></i> Details</a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php elseif ($cat['cardLayout'] === 'bordered_card'): ?>
      <!-- Bordered Card (Ekadashi) -->
      <div class="festival-card" 
           data-title="<?php echo htmlspecialchars($itemTitle); ?>" 
           <?php foreach (($cat['searchFields'] ?? []) as $sf): if (isset($item[$sf])): ?>
           data-<?php echo $sf; ?>="<?php echo htmlspecialchars($item[$sf]); ?>"
           <?php endif; endforeach; ?>
           style="background:var(--white); padding:var(--space-lg); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); border:1px solid var(--border); border-left:5px solid <?php echo $item['cardColor'] ?? 'var(--primary)'; ?>; display:flex; flex-direction:column; gap:var(--space-sm); transition:all var(--transition-base); position:relative;"
           onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-3px)'; this.style.borderColor='var(--primary-light)';"
           onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';"
      >
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:var(--space-md); flex-wrap:wrap;">
          <div style="display:flex; align-items:center; gap:var(--space-md);">
            <?php if (!empty($item['cardIcon'])): ?>
            <div style="width:40px; height:40px; border-radius:var(--radius-md); background:<?php echo ($item['cardColor'] ?? 'var(--primary)'); ?>12; color:<?php echo $item['cardColor'] ?? 'var(--primary)'; ?>; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
              <i class="<?php echo $item['cardIcon']; ?>"></i>
            </div>
            <?php endif; ?>
            <div>
              <h3 style="margin:0; font-size:var(--font-size-lg); color:var(--dark); font-family:var(--font-heading); font-weight:600; line-height:1.3;"><?php echo htmlspecialchars($itemTitle); ?></h3>
              <?php if (!empty($cat['metadataKeys'])): ?>
              <div style="display:flex; align-items:center; gap:var(--space-xs); flex-wrap:wrap; margin-top:4px;">
                <?php foreach ($cat['metadataKeys'] as $mk): if (!empty($item[$mk])): ?>
                <span style="font-size:11px; font-weight:600; color:<?php echo $item['cardColor'] ?? 'var(--primary)'; ?>; text-transform:uppercase; letter-spacing:0.5px;"><?php echo $item[$mk]; ?></span>
                <span style="font-size:11px; color:var(--text-light);">•</span>
                <?php endif; endforeach; ?>
                <?php if (!empty($cat['metadataLabels'])): foreach ($cat['metadataLabels'] as $labelKey => $labelPrefix): if (!empty($item[$labelKey])): ?>
                <span style="font-size:11px; color:var(--text-light);"><?php echo $labelPrefix; ?> <strong><?php echo htmlspecialchars($item[$labelKey]); ?></strong></span>
                <?php endif; endforeach; endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <p style="color:var(--text); font-size:var(--font-size-base); line-height:1.6; margin:0; flex-grow:1;"><?php echo htmlspecialchars($itemDesc); ?></p>

        <?php if ($hasLink || !empty($donationSlug)): ?>
        <div style="margin-top:var(--space-xs); display:flex; gap:var(--space-sm); justify-content:flex-end; flex-wrap:wrap;">
          <?php if (!empty($donationSlug)): ?>
          <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($donationSlug); ?>" class="btn btn-accent btn-sm" style="text-decoration:none;"><i class="fas fa-heart"></i> <?php echo $cat['donationBtnLabel'] ?? 'Offer Seva'; ?></a>
          <?php endif; ?>
          <?php if ($hasLink): ?>
          <a href="<?php echo BASE_URL . $item['link']; ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none;"><i class="fas fa-info-circle"></i> <?php echo $cat['detailBtnLabel'] ?? 'Details'; ?></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <?php elseif ($cat['cardLayout'] === 'vertical_card'): ?>
      <!-- Vertical Card (Appearance / Disappearance) -->
      <div class="festival-card" 
           data-title="<?php echo htmlspecialchars($itemTitle); ?>" 
           data-desc="<?php echo htmlspecialchars($itemDesc); ?>"
           <?php foreach (($cat['searchFields'] ?? []) as $sf): if (isset($item[$sf])): ?>
           data-<?php echo $sf; ?>="<?php echo htmlspecialchars($item[$sf]); ?>"
           <?php endif; endforeach; ?>
           style="background:var(--white); padding:var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); border:1px solid var(--border); border-left:5px solid <?php echo $item['cardColor'] ?? 'var(--primary)'; ?>; display:flex; flex-direction:column; gap:var(--space-sm); transition:all var(--transition-base); position:relative; overflow:hidden;"
           onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-3px)'; this.style.borderColor='var(--primary-light)';"
           onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';"
      >
        <div style="display:flex; align-items:center; gap:var(--space-md);">
          <?php if (!empty($item['cardIcon'])): ?>
          <div style="width:48px; height:48px; border-radius:var(--radius-md); background:<?php echo ($item['cardColor'] ?? 'var(--primary)'); ?>12; color:<?php echo $item['cardColor'] ?? 'var(--primary)'; ?>; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
            <i class="fas <?php echo $item['cardIcon']; ?>"></i>
          </div>
          <?php endif; ?>
          <div>
            <h3 style="margin:0; font-size:var(--font-size-xl); color:var(--dark); font-family:var(--font-heading); font-weight:600;"><?php echo htmlspecialchars($item['name'] ?? $itemTitle); ?></h3>
            <?php if (!empty($item['title'] ?? $item['subtitle'] ?? '')): ?>
            <span style="font-size:var(--font-size-xs); font-weight:600; color:var(--text-light); text-transform:uppercase; letter-spacing:0.5px;"><?php echo htmlspecialchars($item['subtitle'] ?? $item['title'] ?? ''); ?></span>
            <?php endif; ?>
          </div>
        </div>
        
        <p style="color:var(--text); font-size:var(--font-size-base); line-height:1.6; margin:0;"><?php echo htmlspecialchars($itemDesc); ?></p>

        <?php if (!empty($item['legacy'])): ?>
        <!-- Legacy Quote Box -->
        <div style="background:var(--light); padding:var(--space-md); border-radius:var(--radius-sm); border-left:3px solid <?php echo $item['cardColor'] ?? 'var(--primary)'; ?>; margin-top:var(--space-xs); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
          <div style="flex:1; min-width:200px;">
            <span style="display:block; font-size:10px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;"><?php echo $cat['legacyLabel'] ?? 'Legacy'; ?></span>
            <p style="color:var(--text-dark); font-size:var(--font-size-sm); line-height:1.5; margin:0; font-style:italic;">"<?php echo htmlspecialchars($item['legacy']); ?>"</p>
          </div>
          <?php if ($hasLink || !empty($donationSlug)): ?>
          <div style="display:flex; gap:var(--space-sm); flex-wrap:wrap;">
            <?php if (!empty($donationSlug)): ?>
            <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($donationSlug); ?>" class="btn btn-accent btn-sm" style="text-decoration:none;"><i class="fas fa-heart"></i> <?php echo $cat['donationBtnLabel'] ?? 'Offer Seva'; ?></a>
            <?php endif; ?>
            <?php if ($hasLink): ?>
            <a href="<?php echo $item['link']; ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none;"><i class="fas fa-info-circle"></i> <?php echo $cat['detailBtnLabel'] ?? 'Details'; ?></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php elseif ($hasLink || !empty($donationSlug)): ?>
        <div style="margin-top:var(--space-xs); display:flex; gap:var(--space-sm); justify-content:flex-end; flex-wrap:wrap;">
          <?php if (!empty($donationSlug)): ?>
          <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($donationSlug); ?>" class="btn btn-accent btn-sm" style="text-decoration:none;"><i class="fas fa-heart"></i> <?php echo $cat['donationBtnLabel'] ?? 'Offer Seva'; ?></a>
          <?php endif; ?>
          <?php if ($hasLink): ?>
          <a href="<?php echo BASE_URL . $item['link']; ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none;"><i class="fas fa-info-circle"></i> <?php echo $cat['detailBtnLabel'] ?? 'Details'; ?></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <?php elseif ($cat['cardLayout'] === 'icon_card'): ?>
      <!-- Icon Card (Events) -->
      <div class="festival-card" data-title="<?php echo htmlspecialchars($itemTitle); ?>" data-desc="<?php echo htmlspecialchars($itemDesc); ?>" style="background:var(--white); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); border:1px solid var(--border); padding:var(--space-xl); display:flex; gap:var(--space-xl); align-items:center; flex-wrap:wrap; transition:all var(--transition-base);"
           onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-3px)'; this.style.borderColor='var(--primary-light)';"
           onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';"
      >
        <?php if (!empty($item['cardIcon'])): ?>
        <div style="font-size:52px; color:var(--primary); width:70px; text-align:center; flex-shrink:0;">
          <i class="fas <?php echo $item['cardIcon']; ?>"></i>
        </div>
        <?php endif; ?>
        <div style="flex:1; min-width:280px; display:flex; flex-direction:column; gap:4px;">
          <h3 style="margin:0; font-family:var(--font-heading); color:var(--dark); font-size:var(--font-size-lg); font-weight:600;"><?php echo htmlspecialchars($itemTitle); ?></h3>
          <?php if (!empty($item['date'])): ?>
          <p style="color:var(--primary); font-weight:600; font-size:var(--font-size-xs); text-transform:uppercase; letter-spacing:0.5px; margin:0;"><?php echo htmlspecialchars($item['date']); ?></p>
          <?php endif; ?>
          <p style="color:var(--text); font-size:var(--font-size-sm); margin:var(--space-xs) 0 0 0; line-height:1.6;"><?php echo htmlspecialchars($itemDesc); ?></p>
          <?php if ($hasLink || !empty($donationSlug)): ?>
          <div style="margin-top:var(--space-md); display:flex; gap:var(--space-sm); justify-content:flex-end; flex-wrap:wrap;">
            <?php if (!empty($donationSlug)): ?>
            <a href="<?php echo BASE_URL; ?>donate/<?php echo urlencode($donationSlug); ?>" class="btn btn-accent btn-sm" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;"><i class="fas fa-heart"></i> <?php echo $cat['donationBtnLabel'] ?? 'Offer Seva'; ?></a>
            <?php endif; ?>
            <?php if ($hasLink): ?>
            <a href="<?php echo BASE_URL . $item['link']; ?>" class="btn btn-outline-dark btn-sm" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;"><i class="fas fa-info-circle"></i> <?php echo $cat['detailBtnLabel'] ?? 'Learn More'; ?></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php endforeach; ?>

      <!-- No results message (for search) -->
      <?php if (!empty($cat['showSearch'])): ?>
      <div id="noResultsMsg" style="display:none; text-align:center; padding:var(--space-2xl); color:var(--text-light); font-size:var(--font-size-base); background:var(--white); border-radius:var(--radius-lg); border:1px solid var(--border); <?php echo $cat['cardLayout'] === 'image_card' || $cat['cardLayout'] === 'bordered_card' ? 'grid-column: 1 / -1;' : ''; ?>">
        <i class="fas fa-search" style="font-size:24px; margin-bottom:12px; display:block; color:var(--border);"></i>
        No matching items found.
      </div>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Empty state -->
    <div style="text-align:center; padding:var(--space-4xl) 0;">
      <p style="color:var(--text-light); font-size:var(--font-size-lg);"><?php echo htmlspecialchars($cat['emptyMessage'] ?? 'No items found.'); ?></p>
      <a href="<?php echo BASE_URL; ?>festivals/" class="btn btn-primary" style="margin-top:var(--space-lg);">Browse All Festivals</a>
    </div>
    <?php endif; ?>

    <!-- Donation CTA -->
    <?php if (!empty($cat['donationCta'])): 
      include_once __DIR__ . '/../../../partials/donation-cta.php';
      if (function_exists('renderDonationSection')) {
        renderDonationSection($cat['donationCta']);
      }
    endif; ?>
  </div>
</section>

<?php if (!empty($cat['showSearch'])): ?>
<script>
function filterFestivalCards() {
  const query = document.getElementById('festivalSearch').value.toLowerCase();
  const cards = document.querySelectorAll('#festivalCardsContainer .festival-card, #festivalCardsContainer .event-card');
  let hasResults = false;

  cards.forEach(card => {
    const searchFields = <?php echo json_encode($cat['searchFields'] ?? ['title', 'desc']); ?>;
    let text = '';
    searchFields.forEach(field => {
      const attr = card.getAttribute('data-' + field);
      if (attr) text += ' ' + attr;
    });
    text += ' ' + (card.getAttribute('data-title') || '') + ' ' + (card.getAttribute('data-desc') || '');

    if (text.toLowerCase().includes(query)) {
      card.style.display = '';
      hasResults = true;
    } else {
      card.style.display = 'none';
    }
  });

  const noResults = document.getElementById('noResultsMsg');
  if (noResults) {
    noResults.style.display = hasResults ? 'none' : 'block';
  }
}
</script>
<?php endif; ?>
