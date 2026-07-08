<?php
/**
 * Donation CTA & Rich Content Partial
 * Include this in any festival/service page to:
 * 1. Display history/significance/benefits from the donation_causes database
 * 2. Show a prominent donation CTA button
 *
 * Usage:
 *   <?php renderDonationSection(['cause_slug' => 'janmashtami']); ?>
 *   <?php renderDonationCTA(['cause_slug' => 'janmashtami', 'label' => 'Offer Seva for Janmashtami']); ?>
 */

require_once __DIR__ . '/../config.php';

/**
 * Render a full donation information section with rich content from DB
 * Includes history, significance, benefits, and a CTA button
 * 
 * @param array $options
 *   - cause_slug: string (required) - The slug of the donation cause
 *   - title: string (optional) - Custom title for the CTA box
 *   - button_label: string (optional) - Custom button text
 *   - show_history: bool (default true)
 *   - show_significance: bool (default true)
 *   - show_benefits: bool (default true)
 *   - background: string (optional) - CSS gradient background
 */
function renderDonationSection(array $options = []): void {
    $slug = $options['cause_slug'] ?? '';
    $buttonLabel = $options['button_label'] ?? 'Offer Seva';
    $showHistory = $options['show_history'] ?? true;
    $showSignificance = $options['show_significance'] ?? true;
    $showBenefits = $options['show_benefits'] ?? true;
    $bgGradient = $options['background'] ?? 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)';
    
    if (empty($slug)) return;
    
    $cause = getDonationCauseBySlug($slug);
    if (!$cause) return;
    
    $causeHistory = $cause['history'] ?? '';
    $causeSignificance = $cause['significance'] ?? '';
    $causeBenefits = $cause['benefits'] ?? '';
    $causeName = $cause['short_title'] ?: $cause['title'];
    $donateUrl = BASE_URL . 'donate/' . urlencode($slug);
    
    $hasContent = ($showHistory && $causeHistory) || ($showSignificance && $causeSignificance) || ($showBenefits && $causeBenefits);
    ?>
    
    <?php if ($hasContent): ?>
    <!-- Rich Content from Donation Database -->
    <div style="margin: var(--space-2xl) 0;">
        <?php if ($showHistory && $causeHistory): ?>
        <div style="background: var(--cream); border-left: 4px solid var(--primary); padding: var(--space-lg); border-radius: 0 var(--radius-md) var(--radius-md) 0; margin-bottom: var(--space-lg);">
            <h4 style="margin: 0 0 var(--space-sm) 0; font-family: var(--font-heading); color: var(--primary); font-size: var(--font-size-base); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-history"></i> Historical Background
            </h4>
            <p style="margin: 0; font-size: var(--font-size-sm); color: var(--text); line-height: 1.7;"><?php echo nl2br(htmlspecialchars($causeHistory)); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($showSignificance && $causeSignificance): ?>
        <div style="background: var(--cream); border-left: 4px solid var(--accent); padding: var(--space-lg); border-radius: 0 var(--radius-md) var(--radius-md) 0; margin-bottom: var(--space-lg);">
            <h4 style="margin: 0 0 var(--space-sm) 0; font-family: var(--font-heading); color: var(--primary); font-size: var(--font-size-base); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-star"></i> Spiritual Significance
            </h4>
            <p style="margin: 0; font-size: var(--font-size-sm); color: var(--text); line-height: 1.7;"><?php echo nl2br(htmlspecialchars($causeSignificance)); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($showBenefits && $causeBenefits): ?>
        <div style="background: var(--cream); border-left: 4px solid var(--maroon); padding: var(--space-lg); border-radius: 0 var(--radius-md) var(--radius-md) 0; margin-bottom: var(--space-lg);">
            <h4 style="margin: 0 0 var(--space-sm) 0; font-family: var(--font-heading); color: var(--primary); font-size: var(--font-size-base); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-gift"></i> Blessings &amp; Benefits
            </h4>
            <p style="margin: 0; font-size: var(--font-size-sm); color: var(--text); line-height: 1.7;"><?php echo nl2br(htmlspecialchars($causeBenefits)); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Donation CTA Box -->
    <div style="background: <?php echo $bgGradient; ?>; color: var(--white); padding: var(--space-xl); border-radius: var(--radius-lg); margin-top: var(--space-2xl); text-align: center; box-shadow: var(--shadow-lg);">
        <h4 style="margin: 0 0 var(--space-sm) 0; font-family: var(--font-heading); color: var(--white); font-size: var(--font-size-lg); font-weight: 600;">
            <i class="fas fa-hand-holding-heart"></i> <?php echo htmlspecialchars($causeName); ?>
        </h4>
        <p style="margin: 0 0 var(--space-md) 0; font-size: var(--font-size-sm); color: rgba(255,255,255,0.9); line-height: 1.6;">
            Support this sacred occasion by offering your contribution. Every donation, no matter how small, is a cherished offering to the Lord.
        </p>
        <a href="<?php echo htmlspecialchars($donateUrl); ?>" 
           style="display: inline-block; background: var(--white); color: var(--primary); font-weight: 600; padding: 12px 32px; border-radius: var(--radius-md); text-decoration: none; transition: all var(--transition-base); box-shadow: var(--shadow-sm); font-size: var(--font-size-sm); text-transform: uppercase; letter-spacing: 1px;"
           onmouseover="this.style.background='var(--cream)'; this.style.transform='translateY(-2px)';" 
           onmouseout="this.style.background='var(--white)'; this.style.transform='translateY(0)';">
            <i class="fas fa-heart" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($buttonLabel); ?>
        </a>
    </div>
    
    <?php
}

/**
 * Render a simple donation CTA link on a page
 * Lighter weight than the full renderDonationSection
 * 
 * @param array $options
 *   - cause_slug: string (required)
 *   - label: string (default: "Donate Now")
 *   - icon: string (default: "fa-heart")
 *   - style: string (default: "btn-primary")
 *   - button: bool (default: true) - render as button vs text link
 */
function renderSimpleDonationCTA(array $options = []): void {
    $slug = $options['cause_slug'] ?? '';
    $label = $options['label'] ?? 'Donate Now';
    $icon = $options['icon'] ?? 'fa-heart';
    $btnStyle = $options['button'] ?? true;
    
    if (empty($slug)) return;
    
    $donateUrl = BASE_URL . 'donate/' . urlencode($slug);
    ?>
    <a href="<?php echo htmlspecialchars($donateUrl); ?>" 
       style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-weight: 600; color: var(--primary); transition: all var(--transition-fast);"
       onmouseover="this.style.color='var(--primary-dark)';" 
       onmouseout="this.style.color='var(--primary)';">
        <i class="fas <?php echo htmlspecialchars($icon); ?>"></i>
        <?php echo htmlspecialchars($label); ?> <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
    </a>
    <?php
}

/**
 * Renders a card in the listing pages with a donation link
 * Used in festival listing grids
 * 
 * @param array $options
 *   - cause_slug: string (required)
 *   - cause_title: string (required)
 *   - cause_desc: string (required)
 *   - cause_image: string (optional)
 *   - button_label: string (default: "Offer Seva")
 */
function renderDonationCard(array $options = []): void {
    $slug = $options['cause_slug'] ?? '';
    $title = $options['cause_title'] ?? '';
    $desc = $options['cause_desc'] ?? '';
    $image = $options['cause_image'] ?? 'assets/images/banners/calendar.jpg';
    $buttonLabel = $options['button_label'] ?? 'Offer Seva';
    
    if (empty($slug) || empty($title)) return;
    
    $donateUrl = BASE_URL . 'donate/' . urlencode($slug);
    $festivalLink = BASE_URL . $slug;
    ?>
    <div style="background: var(--white); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid var(--border); display: flex; flex-direction: column; transition: all var(--transition-base); height: 100%;"
         onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-4px)'; this.style.borderColor='var(--primary-light)';"
         onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';">
        <div style="height: 180px; overflow: hidden; background: var(--dark);">
            <img src="<?php echo BASE_URL . htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy" 
                 style="width: 100%; height: 100%; object-fit: cover; transition: transform var(--transition-base);"
                 onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';"
                 onerror="this.src='<?php echo BASE_URL; ?>assets/images/banners/calendar.jpg';">
        </div>
        <div style="padding: var(--space-lg); display: flex; flex-direction: column; gap: var(--space-sm); flex: 1;">
            <h3 style="margin: 0; font-family: var(--font-heading); color: var(--dark); font-size: var(--font-size-lg); font-weight: 600;">
                <?php echo htmlspecialchars($title); ?>
            </h3>
            <p style="margin: 0; color: var(--text); font-size: var(--font-size-sm); line-height: 1.6; flex: 1;">
                <?php echo htmlspecialchars($desc); ?>
            </p>
            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-sm); flex-wrap: wrap;">
                <a href="<?php echo htmlspecialchars($festivalLink); ?>" class="btn btn-outline-dark btn-sm" 
                   style="text-decoration: none; flex: 1; text-align: center; justify-content: center;">
                    <i class="fas fa-info-circle"></i> Details
                </a>
                <a href="<?php echo htmlspecialchars($donateUrl); ?>" class="btn btn-primary btn-sm" 
                   style="text-decoration: none; flex: 1; text-align: center; justify-content: center;">
                    <i class="fas fa-heart"></i> <?php echo htmlspecialchars($buttonLabel); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
}
