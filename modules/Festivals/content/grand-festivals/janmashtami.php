<?php
$pageTitle = 'Sri Krishna Janmashtami – Contests, Seva & Celebrations';
$metaDescription = 'Celebrate Janmashtami 2026 at ISKCON Seshadripuram, Bangalore. Participate in kids contests (Dress to be Blessed, Shlokanjali, Quiz), offer seva, and join grand celebrations.';
$pageType = 'festival';
include '../../partials/header.php';
require_once '../../config.php';

// Load contest & seva data
$janmashtamiData = include __DIR__ . '/data/janmashtami-contests.php';
$contests = $janmashtamiData['contests'] ?? [];
$sevas = $janmashtamiData['sevas'] ?? [];
$regConfig = $janmashtamiData['registration'] ?? [];
$contestFeePaise = ($regConfig['fee'] ?? 108) * 100; // Convert to paise for Razorpay

/**
 * Render a contest card for the overview grid.
 */
function renderContestCard(array $contest): void {
    $statusBadge = $contest['status'] === 'active'
        ? '<span class="j-badge j-badge-open">Open</span>'
        : '<span class="j-badge j-badge-soon">Coming Soon</span>';
    
    $modeIcons = '';
    if (str_contains($contest['mode'] ?? '', 'offline')) {
        $modeIcons .= '<span class="j-mode-tag"><i class="fas fa-map-marker-alt"></i> Offline</span>';
    }
    if (str_contains($contest['mode'] ?? '', 'online')) {
        $modeIcons .= '<span class="j-mode-tag"><i class="fas fa-video"></i> Online</span>';
    }
    
    $ageSummary = '';
    if (!empty($contest['age_groups'])) {
        $ranges = array_map(fn($g) => $g['age_range'], $contest['age_groups']);
        $ageSummary = implode(' · ', $ranges);
    }
    ?>
    <div class="j-contest-card" data-slug="<?= htmlspecialchars($contest['slug']) ?>">
        <div class="j-contest-card-header" style="background: <?= htmlspecialchars($contest['color'] ?? 'var(--primary)') ?>;">
            <i class="fas <?= htmlspecialchars($contest['icon'] ?? 'fa-star') ?>"></i>
        </div>
        <div class="j-contest-card-body">
            <div class="j-contest-card-badges">
                <?= $statusBadge ?>
                <?= $modeIcons ?>
            </div>
            <h3><?= htmlspecialchars($contest['title']) ?></h3>
            <p><?= htmlspecialchars($contest['summary']) ?></p>
            <?php if ($ageSummary): ?>
                <div class="j-contest-ages">
                    <i class="fas fa-users"></i> <?= htmlspecialchars($ageSummary) ?>
                </div>
            <?php endif; ?>
            <div class="j-contest-card-actions">
                <a href="#detail-<?= htmlspecialchars($contest['slug']) ?>" class="j-btn j-btn-outline j-btn-sm">
                    View Details <i class="fas fa-arrow-right"></i>
                </a>
                <?php if ($contest['status'] === 'active'): ?>
                    <a href="#register" class="j-btn j-btn-primary j-btn-sm" onclick="selectContest('<?= htmlspecialchars($contest['slug']) ?>', '<?= htmlspecialchars($contest['title']) ?>')">
                        Register ₹<?= htmlspecialchars(number_format($GLOBALS['regConfig']['fee'] ?? 108)) ?> <i class="fas fa-external-link-alt"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render detailed contest accordion panel.
 */
function renderContestDetail(array $contest): void {
    $isActive = $contest['status'] === 'active';
    ?>
    <div class="j-detail-accordion" id="detail-<?= htmlspecialchars($contest['slug']) ?>">
        <details>
            <summary>
                <div class="j-detail-summary">
                    <i class="fas <?= htmlspecialchars($contest['icon'] ?? 'fa-star') ?>" style="color:<?= htmlspecialchars($contest['color'] ?? 'var(--primary)') ?>"></i>
                    <span><?= htmlspecialchars($contest['title']) ?></span>
                    <?php if ($isActive): ?>
                        <span class="j-badge j-badge-open" style="margin-left:auto;">Open</span>
                    <?php else: ?>
                        <span class="j-badge j-badge-soon" style="margin-left:auto;">Coming Soon</span>
                    <?php endif; ?>
                    <i class="fas fa-chevron-down j-chevron"></i>
                </div>
            </summary>
            <div class="j-detail-body">
                <p class="j-detail-summary-text"><?= htmlspecialchars($contest['summary']) ?></p>
                
                <?php if ($isActive): ?>
                <div class="j-detail-register-top">
                    <a href="#register" class="j-btn j-btn-primary" onclick="selectContest('<?= htmlspecialchars($contest['slug']) ?>', '<?= htmlspecialchars($contest['title']) ?>')">
                        <i class="fas fa-user-plus"></i> Register Now — ₹<?= htmlspecialchars(number_format($GLOBALS['regConfig']['fee'] ?? 108)) ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Age Groups -->
                <?php if (!empty($contest['age_groups'])): ?>
                <div class="j-detail-section">
                    <h4><i class="fas fa-users"></i> Age Groups & Eligibility</h4>
                    <div class="j-age-grid">
                        <?php foreach ($contest['age_groups'] as $group): ?>
                        <div class="j-age-card">
                            <div class="j-age-name"><?= htmlspecialchars($group['name']) ?></div>
                            <div class="j-age-range"><?= htmlspecialchars($group['age_range']) ?></div>
                            <div class="j-age-inst"><?= htmlspecialchars($group['instructions']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Venue & Schedule -->
                <?php if ($contest['venue'] || $contest['schedule']): ?>
                <div class="j-detail-row">
                    <?php if ($contest['venue']): ?>
                    <div class="j-detail-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <strong>Venue:</strong> <?= htmlspecialchars($contest['venue']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($contest['schedule']): ?>
                    <div class="j-detail-info-item">
                        <i class="fas fa-clock"></i>
                        <strong>Schedule:</strong> <?= htmlspecialchars($contest['schedule']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Evaluation -->
                <?php if ($contest['evaluation']): ?>
                <div class="j-detail-section">
                    <h4><i class="fas fa-clipboard-check"></i> Evaluation Criteria</h4>
                    <p><?= htmlspecialchars($contest['evaluation']) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Prizes -->
                <?php if ($contest['prizes']): ?>
                <div class="j-detail-section j-detail-prizes">
                    <h4><i class="fas fa-trophy"></i> Prizes & Blessings</h4>
                    <p><?= htmlspecialchars($contest['prizes']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </details>
    </div>
    <?php
}
?>

<style>
/* =====================================================
   Janmashtami Page — Custom Styles
   ===================================================== */

/* --- Quick Nav Strip --- */
.j-quick-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    margin: var(--space-xl) 0 var(--space-2xl);
}
.j-quick-nav a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    color: var(--text-dark);
    transition: all var(--transition-base);
    box-shadow: var(--shadow-sm);
}
.j-quick-nav a:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
    color: var(--primary);
}
.j-quick-nav a i {
    font-size: 16px;
    color: var(--primary);
}

/* --- Section Headers --- */
.j-section {
    margin-bottom: var(--space-3xl);
}
.j-section-header {
    text-align: center;
    margin-bottom: var(--space-xl);
}
.j-section-header h2 {
    font-family: var(--font-heading);
    font-size: var(--font-size-2xl);
    color: var(--text-dark);
    margin: 0 0 var(--space-sm);
}
.j-section-header p {
    color: var(--text-light);
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.7;
    font-size: var(--font-size-base);
}
.j-section-divider {
    width: 60px;
    height: 3px;
    background: var(--primary);
    margin: 0 auto var(--space-md);
    border-radius: 2px;
}

/* --- Contest Card Grid --- */
.j-contest-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: var(--space-2xl);
}
@media (max-width: 900px) {
    .j-contest-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .j-contest-grid { grid-template-columns: 1fr; }
}

/* Contest Card */
.j-contest-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-base);
    display: flex;
    flex-direction: column;
}
.j-contest-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}
.j-contest-card-header {
    padding: 20px;
    text-align: center;
    color: white;
    font-size: 36px;
}
.j-contest-card-body {
    padding: var(--space-lg);
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.j-contest-card-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}
.j-contest-card-body h3 {
    font-family: var(--font-heading);
    font-size: var(--font-size-lg);
    color: var(--text-dark);
    margin: 0 0 8px;
}
.j-contest-card-body p {
    font-size: var(--font-size-sm);
    color: var(--text-light);
    line-height: 1.6;
    margin: 0 0 12px;
    flex-grow: 1;
}
.j-contest-ages {
    font-size: 12px;
    color: var(--text-light);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.j-contest-card-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* --- Badges --- */
.j-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.j-badge-open {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}
.j-badge-soon {
    background: #fff8e1;
    color: #b78103;
    border: 1px solid #ffd54f;
}
.j-mode-tag {
    display: inline-block;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 12px;
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid #90caf9;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* --- Buttons --- */
.j-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 12px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all var(--transition-fast);
    font-family: inherit;
}
.j-btn-primary {
    background: var(--maroon, #7b1e1e);
    color: white;
}
.j-btn-primary:hover {
    background: #5a1515;
    color: white;
}
.j-btn-outline {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-dark);
}
.j-btn-outline:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.j-btn-sm { padding: 6px 12px; font-size: 11px; }
.j-btn-lg { padding: 14px 32px; font-size: 15px; }

/* --- Detail Accordions --- */
.j-detail-accordion {
    margin-bottom: 12px;
}
.j-detail-accordion details {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-base);
}
.j-detail-accordion details[open] {
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}
.j-detail-accordion summary {
    padding: 16px 20px;
    cursor: pointer;
    list-style: none;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    font-size: 15px;
    color: var(--text-dark);
    background: #fafafa;
    user-select: none;
}
.j-detail-accordion summary::-webkit-details-marker {
    display: none;
}
.j-detail-accordion summary:hover {
    background: #f5f5f5;
}
.j-detail-summary {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.j-detail-summary i:first-child {
    font-size: 20px;
    width: 24px;
    text-align: center;
}
.j-chevron {
    transition: transform var(--transition-base);
    color: var(--text-light);
    font-size: 14px;
}
details[open] .j-chevron {
    transform: rotate(180deg);
}
.j-detail-body {
    padding: 0 20px 20px;
    border-top: 1px solid var(--border);
}
.j-detail-summary-text {
    color: var(--text-light);
    line-height: 1.6;
    margin: 16px 0;
}
.j-detail-register-top {
    margin-bottom: 16px;
}
.j-detail-section {
    margin: 16px 0;
}
.j-detail-section h4 {
    font-family: var(--font-heading);
    font-size: 14px;
    color: var(--text-dark);
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.j-detail-section h4 i {
    color: var(--primary);
    font-size: 16px;
}
.j-detail-section p {
    font-size: 14px;
    color: var(--text);
    line-height: 1.7;
    margin: 0;
}
.j-detail-prizes {
    background: #fff8e1;
    padding: 12px 16px;
    border-radius: var(--radius-md);
    border: 1px solid #ffd54f;
}
.j-detail-prizes h4 i {
    color: #f57c00;
}
.j-age-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 12px;
}
.j-age-card {
    background: #fafafa;
    border: 1px solid #eee;
    border-radius: var(--radius-md);
    padding: 14px;
}
.j-age-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--primary);
    margin-bottom: 2px;
}
.j-age-range {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-light);
    margin-bottom: 6px;
}
.j-age-inst {
    font-size: 13px;
    color: var(--text);
    line-height: 1.5;
}
.j-detail-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin: 16px 0;
}
.j-detail-info-item {
    font-size: 14px;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 6px;
}
.j-detail-info-item i {
    color: var(--primary);
}

/* --- Seva Cards --- */
.j-seva-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
@media (max-width: 768px) {
    .j-seva-grid { grid-template-columns: 1fr; }
}
@media (max-width: 1024px) and (min-width: 769px) {
    .j-seva-grid { grid-template-columns: repeat(2, 1fr); }
}
.j-seva-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: var(--space-xl);
    text-align: center;
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-base);
}
.j-seva-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}
.j-seva-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-md);
    font-size: 28px;
    color: white;
}
.j-seva-card h3 {
    font-family: var(--font-heading);
    font-size: var(--font-size-lg);
    color: var(--text-dark);
    margin: 0 0 var(--space-sm);
}
.j-seva-card p {
    font-size: var(--font-size-sm);
    color: var(--text-light);
    line-height: 1.6;
    margin: 0 0 var(--space-md);
}

/* --- Registration Section --- */
.j-register-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: var(--space-2xl);
    max-width: 600px;
    margin: 0 auto;
    box-shadow: var(--shadow-md);
    border-top: 4px solid var(--maroon, #7b1e1e);
}
.j-register-card h3 {
    font-family: var(--font-heading);
    text-align: center;
    margin: 0 0 var(--space-md);
    color: var(--text-dark);
}
.j-register-card .j-form-group {
    margin-bottom: var(--space-md);
}
.j-register-card label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 4px;
}
.j-register-card input,
.j-register-card select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 14px;
    font-family: inherit;
    background: var(--white);
    box-sizing: border-box;
    transition: border-color var(--transition-fast);
}
.j-register-card input:focus,
.j-register-card select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(200, 107, 31, 0.15);
}
.j-register-card .j-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
}
@media (max-width: 500px) {
    .j-register-card .j-form-row { grid-template-columns: 1fr; }
}
.j-price-summary {
    background: var(--cream);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    margin: var(--space-md) 0;
    text-align: center;
    border: 1px solid var(--border);
}
.j-price-summary .j-price-amount {
    font-size: 28px;
    font-weight: 700;
    color: var(--maroon, #7b1e1e);
}
.j-price-summary .j-price-label {
    font-size: 12px;
    color: var(--text-light);
    margin-top: 4px;
}
</style>

<!-- ================================================================ -->
<!-- HERO BANNER -->
<!-- ================================================================ -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/janmashtami-contest.jpg');"></div>
  <div class="container" style="position:relative; z-index:1;">
    <span style="display:inline-block; background:rgba(200, 107, 31, 0.2); border:1px solid var(--primary); color:var(--accent-light); padding:6px 16px; border-radius:var(--radius-xl); font-size:var(--font-size-xs); font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-md); backdrop-filter:blur(4px);">Grand Festival</span>
    <h1 class="reveal" style="font-family:var(--font-heading); color:var(--white); font-size:calc(var(--font-size-3xl) + 1vw); line-height:1.2; text-shadow:0 2px 10px rgba(0,0,0,0.6); max-width:900px; margin:0 auto var(--space-md) auto;">Krishna Janmashtami</h1>
    <div class="breadcrumb reveal" style="display:flex; justify-content:center; gap:8px; color:rgba(255,255,255,0.8); font-size:var(--font-size-sm);">
      <a href="<?php echo BASE_URL; ?>" style="color:var(--accent-light);">Home</a><span>›</span>
      <a href="<?php echo BASE_URL; ?>festivals/" style="color:var(--accent-light);">Festivals</a><span>›</span>
      <a href="<?php echo BASE_URL; ?>festivals/grand-festivals/" style="color:var(--accent-light);">Grand Festivals</a><span>›</span>
      <span style="color:var(--white);">Janmashtami</span>
    </div>

  </div>
</section>

<!-- ================================================================ -->
<!-- MAIN CONTENT -->
<!-- ================================================================ -->
<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0; position:relative; overflow:hidden;">
  <div class="container" style="max-width:960px; position:relative; z-index:1;">

    <!-- Top Image Banner -->
    <div style="margin-bottom:var(--space-xl); text-align:center; overflow:hidden; border-radius:var(--radius-lg); box-shadow:var(--shadow-md);">
      <img src="<?php echo BASE_URL; ?>assets/images/banners/janmashtami-contest.jpg" alt="Krishna Janmashtami" style="width:100%; height:auto; display:block;">
    </div>

    <!-- Quick Navigation Jump Strip -->
    <div class="j-quick-nav reveal">
      <a href="#contests"><i class="fas fa-trophy"></i> Kids Contests</a>
      <a href="#seva"><i class="fas fa-hand-holding-heart"></i> Offer Seva</a>
      <a href="#register"><i class="fas fa-user-plus"></i> Contest Registration</a>
      <a href="#story"><i class="fas fa-book"></i> Festival Story</a>
    </div>

    <!-- ============================================================ -->
    <!-- CONTESTS SECTION -->
    <!-- ============================================================ -->
    <div id="contests" class="j-section reveal">
      <div class="j-section-header">
        <div class="j-section-divider"></div>
        <h2><i class="fas fa-trophy" style="color:var(--primary);"></i> Janmashtami Contests for Kids & Families</h2>
        <p>Encourage your children to celebrate Krishna consciousness through recitation, dress-up, quiz, music, storytelling, and creative arts. All participants receive the blessings of Lord Krishna!</p>
      </div>

      <!-- Contest Overview Cards -->
      <div class="j-contest-grid">
        <?php foreach ($contests as $contest): ?>
          <?php renderContestCard($contest); ?>
        <?php endforeach; ?>
      </div>

      <!-- Contest Detail Accordions -->
      <div style="margin-top: var(--space-xl);">
        <h3 style="font-family:var(--font-heading); font-size:var(--font-size-lg); color:var(--text-dark); margin-bottom:var(--space-md); display:flex; align-items:center; gap:8px;">
          <i class="fas fa-chevron-circle-down" style="color:var(--primary);"></i> Detailed Contest Rules
        </h3>
        <?php foreach ($contests as $contest): ?>
          <?php renderContestDetail($contest); ?>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- SEVA SECTION -->
    <!-- ============================================================ -->
    <div id="seva" class="j-section reveal">
      <div class="j-section-header">
        <div class="j-section-divider"></div>
        <h2><i class="fas fa-hand-holding-heart" style="color:var(--primary);"></i> Offer Your Seva for Janmashtami</h2>
        <p>Participate in the sacred festivities by offering your devotional service. Your contributions help make this grand celebration possible.</p>
      </div>

      <div class="j-seva-grid">
        <?php foreach ($sevas as $seva): ?>
          <div class="j-seva-card">
            <div class="j-seva-icon" style="background: <?= htmlspecialchars($seva['color'] ?? 'var(--primary)') ?>;">
              <i class="fas <?= htmlspecialchars($seva['icon'] ?? 'fa-hand-holding-heart') ?>"></i>
            </div>
            <h3><?= htmlspecialchars($seva['title']) ?></h3>
            <p><?= htmlspecialchars($seva['summary']) ?></p>
            <a href="<?= BASE_URL ?>donate/janmashtami" class="j-btn j-btn-primary">
              <i class="fas fa-heart"></i> Offer Seva
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- REGISTRATION SECTION -->
    <!-- ============================================================ -->
    <div id="register" class="j-section reveal">
      <div class="j-section-header">
        <div class="j-section-divider"></div>
        <h2><i class="fas fa-user-plus" style="color:var(--primary);"></i> Contest Registration</h2>
        <p>Register for your chosen contest. Registration fee is <strong>₹<?= number_format($regConfig['fee'] ?? 108) ?></strong> per participant per contest. Secure your spot today!</p>
      </div>

      <div class="j-register-card">
        <h3>Participant Details</h3>
        <form id="contestRegForm">
          <!-- Contest Selection -->
          <div class="j-form-group">
            <label for="regContest">Select Contest *</label>
            <select id="regContest" required>
              <option value="" disabled selected>-- Choose a contest --</option>
              <?php foreach ($contests as $c): ?>
                <?php if ($c['status'] === 'active'): ?>
                  <option value="<?= htmlspecialchars($c['slug']) ?>" data-name="<?= htmlspecialchars($c['title']) ?>">
                    <?= htmlspecialchars($c['title']) ?> — ₹<?= number_format($regConfig['fee'] ?? 108) ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="j-form-row">
            <div class="j-form-group">
              <label for="regName">Participant Name *</label>
              <input type="text" id="regName" required placeholder="Full name of participant">
            </div>
            <div class="j-form-group">
              <label for="regAgeGroup">Age Group *</label>
              <select id="regAgeGroup" required>
                <option value="" disabled selected>-- Select age group --</option>
                <option value="group1">Group 1 (Up to 6 years)</option>
                <option value="group2">Group 2 (7 – 10 years)</option>
                <option value="group3">Group 3 (11 – 15 years)</option>
              </select>
            </div>
          </div>

          <div class="j-form-row">
            <div class="j-form-group">
              <label for="regPhone">Phone Number *</label>
              <input type="tel" id="regPhone" required placeholder="+91 9876543210">
            </div>
            <div class="j-form-group">
              <label for="regParticipantType">Participant Type *</label>
              <select id="regParticipantType" required>
                <option value="" disabled selected>-- Select type --</option>
                <option value="offline">Offline</option>
                <option value="online">Online</option>
              </select>
            </div>
          </div>

          <div class="j-form-row">
            <div class="j-form-group">
              <label for="regPhone">Phone Number *</label>
              <input type="tel" id="regPhone" required placeholder="+91 9876543210">
            </div>
            <div class="j-form-group">
              <label for="regEmail">Email Address</label>
              <input type="email" id="regEmail" placeholder="name@domain.com">
            </div>
          </div>

          <!-- Price Summary -->
          <div class="j-price-summary">
            <div class="j-price-label">Registration Fee</div>
            <div class="j-price-amount">₹<?= number_format($regConfig['fee'] ?? 108) ?></div>
            <div class="j-price-label">Per participant · Per contest</div>
          </div>

          <!-- Submit -->
          <button type="submit" id="regSubmitBtn" class="j-btn j-btn-primary j-btn-lg" style="width:100%; justify-content:center; text-align:center;">
            <i class="fas fa-lock"></i> Register & Pay ₹<?= number_format($regConfig['fee'] ?? 108) ?>
          </button>

          <div style="text-align:center; font-size:11px; color:var(--text-light); margin-top:var(--space-md);">
            <i class="fas fa-shield-alt"></i> Secured by <strong>Razorpay</strong> — 128-bit SSL Encrypted
          </div>
        </form>

        <!-- Loading indicator -->
        <div id="regLoading" style="display:none; text-align:center; padding:var(--space-xl);">
          <div style="font-size:48px; color:var(--primary); margin-bottom:var(--space-md);"><i class="fas fa-spinner fa-spin"></i></div>
          <p style="color:var(--text-light);">Processing your registration and payment...</p>
        </div>
      </div>

      <div style="text-align:center; margin-top:var(--space-lg); font-size:13px; color:var(--text-light);">
        <i class="fas fa-info-circle" style="color:var(--primary);"></i>
        For any queries regarding contest registration, please contact the temple at <strong><?= SITE_PHONE ?></strong>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- FESTIVAL STORY / ARTICLE (Existing Content) -->
    <!-- ============================================================ -->
    <div id="story" class="reveal" style="background:var(--white); padding:var(--space-2xl) var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border:1px solid var(--border); margin-bottom:var(--space-2xl);">
      
      <div style="margin-bottom:var(--space-xl); text-align:center; overflow:hidden; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid var(--border);">
        <img src="<?php echo BASE_URL; ?>assets/images/banners/birth-of-lord-krishna.jpg" alt="Birth of Lord Krishna" style="width:100%; height:auto; display:block;">
      </div>

      <div style="background:var(--cream); border-left:4px solid var(--primary); padding:var(--space-lg); border-radius:var(--radius-sm); margin-bottom:var(--space-2xl); display:flex; flex-direction:column; gap:10px;">
        <h4 style="margin:0; color:var(--primary); font-family:var(--font-heading); font-weight:600; display:flex; align-items:center; gap:8px;">
          <i class="fas fa-info-circle"></i> Vrata & Worship Overview
        </h4>
        <p style="margin:0; font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.6;">
          <strong>Calendar:</strong> Celebrated on Bhadrapada Krishna Ashtami (8th day of waning moon) (August/September)<br>
          <strong>Significance:</strong> The appearance day of the Supreme Personality of Godhead, Lord Sri Krishna, to end evil and protect His devotees.<br>
          <strong>Key Highlights:</strong> Day-long fasting until midnight, grand abhishekam, elaborate flower decorations, 108 dishes offered, and ecstatic midnight arati.<br>
          <strong>Observance:</strong> Held on the scheduled event date. Please check the <a href="<?php echo BASE_URL; ?>festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a> for the exact day this year.
        </p>
      </div>

      <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8; display:flex; flex-direction:column; gap:var(--space-lg);">
        
        <p>Janmashtami is one of the most prominent festivals, celebrated with great grandeur and enthusiasm to commemorate the birth of Lord Krishna. Devotees express their love for the Lord through fasts, night sankirtans, dramatic presentations, and temple offerings.</p>
        
        <p>The advent of Lord Krishna is highly significant. The reason for the Supreme Lord's descent is to end the cruel rule of demon Kansa and give pleasure to His surrendered devotees. Lord Krishna appeared in the prison of Mathura at midnight on the eighth day of Krishna paksha to Mother Devaki and Vasudeva.</p>

        <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Prophecy and the Trials of Devaki</h3>
        <p>According to the Puranas, King Kansa made grand arrangements for the marriage of his sister Devaki. But during the wedding procession, a voice from the sky foretold that Devaki's eighth son would destroy Kansa and end his dictatorial rule. In a rage, Kansa grabbed Devaki's hair to kill her. Vasudeva stepped in with patience and wisdom to save his wife, promising Kansa that they would hand over every child born to them.</p>
        
        <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">The Divine Descent</h3>
        <p>At midnight, the eighth child appeared. Lord Krishna descended not as an ordinary human child, but in His four-armed (chaturbhuj) form, adorned with jewels, holding a disc, conch, club, and lotus. He then adopted the form of a small baby. As soon as the Lord appeared, the shackles unlocked, the iron gates of the prison cell opened by themselves, and all the guards fell under a deep magic spell.</p>

        <blockquote style="border-left:4px solid var(--maroon); padding-left:var(--space-md); font-style:italic; color:var(--dark); margin:var(--space-md) 0;">
          "yadā yadā hi dharmasya glānir bhavati bhārata<br>
          abhyutthānam adharmasya tadātmānaṁ sṛijāmyaham"<br><br>
          "Whenever and wherever there is a decline in religious practice, O descendant of Bharata, and a predominant rise of irreligion—at that time I descend Myself." &mdash; Bhagavad Gita 4.7
        </blockquote>

        <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">How Janmashtami is Celebrated</h3>
        <ul style="margin-left:var(--space-lg); line-height:1.8; display:flex; flex-direction:column; gap:6px;">
          <li><strong>Fasting:</strong> Devotees fast the entire day until midnight to focus their thoughts on Krishna.</li>
          <li><strong>Abhisheka:</strong> The deities are bathed in auspicious liquids (milk, honey, yogurt, fruit juices) in a grand, two-hour ceremony.</li>
          <li><strong>Offerings:</strong> Priests offer a massive feast of over a hundred different preparations.</li>
          <li><strong>Bhajans & Kirtans:</strong> Congregational singing of the Hare Krishna Mahamantra echoes throughout the day.</li>
          <li><strong>Altar Opening:</strong> At midnight, the curtains pull back to reveal the freshly dressed deities on a beautifully festooned altar, followed by a rousing, ecstatic arati.</li>
        </ul>

      </article>

      <?php 
      include_once __DIR__ . '/../../../../partials/donation-cta.php';
      renderDonationSection([
        'cause_slug' => 'janmashtami',
        'button_label' => 'Offer Janmashtami Seva',
        'background' => 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)'
      ]); 
      ?>

    </div>

  </div>
</section>

<!-- ================================================================ -->
<!-- JAVASCRIPT: Contest Registration with Razorpay -->
<!-- ================================================================ -->
<script>
// Razorpay Configuration
var RAZORPAY_CONFIG = {
  keyId: '<?php echo RAZORPAY_KEY_ID; ?>',
  siteName: '<?php echo SITE_NAME; ?>',
  themeColor: '#c86b1f'
};

var CONTEST_FEE_PAISE = <?php echo $contestFeePaise; ?>;
var BASE_URL = '<?php echo BASE_URL; ?>';

/**
 * Pre-select a contest from card buttons.
 */
function selectContest(slug, name) {
  var select = document.getElementById('regContest');
  if (select) {
    for (var i = 0; i < select.options.length; i++) {
      if (select.options[i].value === slug) {
        select.selectedIndex = i;
        break;
      }
    }
  }
  // Scroll to registration form
  var regSection = document.getElementById('register');
  if (regSection) {
    regSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('contestRegForm');
  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    var contestSlug = document.getElementById('regContest').value;
    var contestName = document.getElementById('regContest').selectedOptions[0]?.getAttribute('data-name') || '';
    var name = document.getElementById('regName').value.trim();
    var ageGroup = document.getElementById('regAgeGroup').value;
    var phone = document.getElementById('regPhone').value.trim();
    var email = document.getElementById('regEmail').value.trim();
    var participantType = document.getElementById('regParticipantType').value;

    // Validate
    if (!contestSlug) { alert('Please select a contest.'); return; }
    if (!name) { alert('Please enter participant name.'); return; }
    if (!ageGroup) { alert('Please select an age group.'); return; }
    if (!participantType) { alert('Please select participant type (Online/Offline).'); return; }
    if (!phone || phone.length < 10) { alert('Please enter a valid phone number.'); return; }

    var btn = document.getElementById('regSubmitBtn');
    var loading = document.getElementById('regLoading');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    form.style.display = 'none';
    loading.style.display = 'block';

    // Step 1: Create Razorpay Order
    fetch(BASE_URL + 'api/create-contest-order', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        contest_slug: contestSlug,
        contest_name: contestName,
        participant_name: name,
        age_group: ageGroup,
        participant_type: participantType,
        phone: phone,
        email: email,
        amount: CONTEST_FEE_PAISE
      })
    })
    .then(function(res) {
      if (!res.ok) {
        return res.json().then(function(err) { throw new Error(err.error || 'Failed to create order'); });
      }
      return res.json();
    })
    .then(function(orderData) {
      // Step 2: Open Razorpay Checkout
      var options = {
        key: RAZORPAY_CONFIG.keyId,
        amount: orderData.amount,
        currency: orderData.currency,
        name: RAZORPAY_CONFIG.siteName,
        description: 'Janmashtami Contest: ' + contestName,
        order_id: orderData.order_id,
        prefill: {
          name: name,
          email: email || undefined,
          contact: phone
        },
        theme: { color: RAZORPAY_CONFIG.themeColor },
        handler: function(response) {
          verifyContestPayment(response, orderData.registration_id);
        },
        modal: {
          ondismiss: function() {
            // Re-enable form if user closes Razorpay modal
            loading.style.display = 'none';
            form.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Register & Pay ₹<?php echo number_format($regConfig['fee'] ?? 108); ?>';
          }
        }
      };
      var rzp = new Razorpay(options);
      rzp.on('payment.failed', function(resp) {
        alert('Payment failed: ' + (resp.error.description || 'Please try again.'));
        loading.style.display = 'none';
        form.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Register & Pay ₹<?php echo number_format($regConfig['fee'] ?? 108); ?>';
      });
      rzp.open();
    })
    .catch(function(err) {
      alert('Error: ' + err.message);
      loading.style.display = 'none';
      form.style.display = 'block';
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-lock"></i> Register & Pay ₹<?php echo number_format($regConfig['fee'] ?? 108); ?>';
    });
  });
});

/**
 * Verify payment signature on the server.
 */
function verifyContestPayment(response, registrationId) {
  var loading = document.getElementById('regLoading');

  fetch(BASE_URL + 'api/verify-contest-payment', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      razorpay_order_id: response.razorpay_order_id,
      razorpay_payment_id: response.razorpay_payment_id,
      razorpay_signature: response.razorpay_signature,
      registration_id: registrationId
    })
  })
  .then(function(res) {
    if (!res.ok) { throw new Error('Payment verification failed'); }
    return res.json();
  })
  .then(function(data) {
    if (data.success) {
      // Redirect to success page
      window.location.href = BASE_URL + 'festivals/contest-registration-success?reg_id=' + registrationId + '&pay_id=' + encodeURIComponent(response.razorpay_payment_id);
    } else {
      alert('Payment verification failed. Please contact the temple.');
      location.reload();
    }
  })
  .catch(function(err) {
    alert('Verification error: ' + err.message);
    location.reload();
  });
}
</script>

<?php include '../../partials/footer.php'; ?>
