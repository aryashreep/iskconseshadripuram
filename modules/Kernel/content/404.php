<?php

/**
 * Advanced 404 Error Page
 *
 * Features:
 * - Proper HTTP 404 status + noindex header
 * - Smart URL-based suggestions (inspects broken path for keywords)
 * - Client-side quick finder to filter destination cards
 * - Curated destination grid with 8 key pages
 * - Spiritual blessing with temple-appropriate tone
 * - Contact help strip
 */

require_once __DIR__ . '/../config.php';

http_response_code(404);
header('X-Robots-Tag: noindex, follow', true);

$pageTitle = 'Page Not Found';
$metaDescription = 'The page you requested could not be found. Explore our darshan, seva, festivals, and temple resources.';
$canonicalUrl = BASE_URL;

// ==========================================
// Smart URL-based suggestions
// Inspect the broken URL to suggest relevant pages
// ==========================================
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPathLower = strtolower($requestPath);

$suggestionMap = [
    'blog'        => ['label' => 'Latest Blogs',          'url' => 'blogs',               'icon' => 'fa-blog'],
    'donate'      => ['label' => 'Make a Donation',       'url' => 'donate',              'icon' => 'fa-hand-holding-heart'],
    'booking'     => ['label' => 'Book Puja / Yagya',     'url' => 'booking',             'icon' => 'fa-hands-praying'],
    'puja'        => ['label' => 'Book Puja',             'url' => 'booking/puja',        'icon' => 'fa-hands-praying'],
    'yagya'       => ['label' => 'Book Yagya',            'url' => 'booking/yagya',       'icon' => 'fa-fire'],
    'festival'    => ['label' => 'Festivals & Events',    'url' => 'festivals',           'icon' => 'fa-calendar-alt'],
    'ekadashi'    => ['label' => 'Ekadashi',              'url' => 'festivals/ekadashi',  'icon' => 'fa-moon'],
    'yatra'       => ['label' => 'Yatra Packages',        'url' => 'yatra',               'icon' => 'fa-route'],
    'panihati'    => ['label' => 'Panihati Yatra',        'url' => 'yatra/panihati',      'icon' => 'fa-route'],
    'seva'        => ['label' => 'Seva Offerings',        'url' => 'seva',                'icon' => 'fa-hands'],
    'sudamaseva'  => ['label' => 'Monthly Seva',          'url' => 'sudamaseva',          'icon' => 'fa-sync-alt'],
    'service'     => ['label' => 'Our Services',          'url' => 'services',            'icon' => 'fa-concierge-bell'],
    'course'      => ['label' => 'Spiritual Courses',     'url' => 'courses/bhakti-shastri', 'icon' => 'fa-graduation-cap'],
    'darshan'     => ['label' => 'Gallery / Darshan',     'url' => 'darshan',             'icon' => 'fa-images'],
    'gallery'     => ['label' => 'Gallery / Darshan',     'url' => 'darshan',             'icon' => 'fa-images'],
    'contact'     => ['label' => 'Contact Us',            'url' => 'contact',             'icon' => 'fa-envelope'],
    'about'       => ['label' => 'About Us',              'url' => 'about',               'icon' => 'fa-info-circle'],
    'guest'       => ['label' => 'Guest House',           'url' => 'booking/guest-house', 'icon' => 'fa-bed'],
    'sitemap'     => ['label' => 'Sitemap',               'url' => 'sitemap',             'icon' => 'fa-sitemap'],
];

$suggestions = [];
foreach ($suggestionMap as $keyword => $suggestion) {
    if (strpos($requestPathLower, $keyword) !== false) {
        $suggestions[] = $suggestion;
    }
}

// Deduplicate by URL and limit to 4
$seen = [];
$suggestions = array_filter($suggestions, function ($s) use (&$seen) {
    $key = $s['url'];
    if (in_array($key, $seen)) return false;
    $seen[] = $key;
    return true;
});
$suggestions = array_slice($suggestions, 0, 4);

include __DIR__ . '/../partials/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="reveal">This Path Seems to Have Wandered</h1>
        <div class="breadcrumb reveal">
            <a href="<?= BASE_URL ?>">Home</a><span>›</span><span>404</span>
        </div>
    </div>
</section>

<!-- ============================================
     404 MAIN HERO
     ============================================ -->
<section class="not-found-hero">
    <div class="container">

        <!-- Giant 404 Visual -->
        <div class="not-found-visual reveal">
            <div class="not-found-404">
                <span class="not-found-digit">4</span>
                <span class="not-found-zero-wrapper">
                    <span class="not-found-digit zero">0</span>
                    <div class="not-found-halo"></div>
                    <img src="<?= BASE_URL ?>assets/images/iskcon_logo.svg"
                         alt=""
                         class="not-found-logo-in-404"
                         aria-hidden="true"
                         loading="lazy">
                </span>
                <span class="not-found-digit">4</span>
            </div>
        </div>

        <p class="not-found-message reveal">
            The link may be outdated, moved, or mistyped — but you are always welcome
            to continue your journey here.
        </p>

        <!-- Requested Path Display -->
        <div class="not-found-path reveal">
            <span class="not-found-path-label">Requested path</span>
            <code class="not-found-path-value"><?= htmlspecialchars($requestPath) ?></code>
        </div>

        <!-- Primary Actions -->
        <div class="not-found-actions reveal">
            <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-home"></i> Return Home
            </a>
            <button type="button" class="btn btn-outline-dark btn-lg" onclick="window.goBack()">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
            <a href="<?= BASE_URL ?>contact" class="btn btn-outline-dark btn-lg">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
        </div>

    </div>
</section>

<!-- ============================================
     SMART SUGGESTIONS (URL-based)
     ============================================ -->
<?php if (!empty($suggestions)): ?>
<section class="section section-alt not-found-suggestions">
    <div class="container">
        <div class="section-divider reveal">
            <span class="divider-icon"><i class="fas fa-compass"></i></span>
        </div>
        <p class="section-subtitle reveal" style="text-align:center;">From your path</p>
        <h2 class="section-title reveal" style="text-align:center;">You Might Be Looking For</h2>

        <div class="not-found-suggestion-grid reveal">
            <?php foreach ($suggestions as $s): ?>
            <a href="<?= BASE_URL . htmlspecialchars($s['url']) ?>" class="not-found-suggestion-card">
                <div class="not-found-suggestion-icon">
                    <i class="fas <?= htmlspecialchars($s['icon']) ?>"></i>
                </div>
                <span><?= htmlspecialchars($s['label']) ?></span>
                <i class="fas fa-arrow-right not-found-suggestion-arrow"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================
     QUICK FINDER + DESTINATION GRID
     ============================================ -->
<section class="section not-found-explore">
    <div class="container">
        <div class="section-divider reveal">
            <span class="divider-icon"><i class="fas fa-search"></i></span>
        </div>
        <p class="section-subtitle reveal" style="text-align:center;">Explore the temple</p>
        <h2 class="section-title reveal" style="text-align:center;">Where Would You Like to Go?</h2>

        <!-- Quick Finder Input -->
        <div class="not-found-finder reveal">
            <span class="not-found-finder-icon"><i class="fas fa-filter"></i></span>
            <input
                type="text"
                class="not-found-finder-input"
                id="notFoundFinder"
                placeholder="Filter destinations: donate, darshan, booking, festivals, blogs…"
                aria-label="Filter destinations"
                autocomplete="off">
            <button type="button" class="not-found-finder-clear" id="notFoundClear" aria-label="Clear filter">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Destination Cards Grid -->
        <div class="not-found-links-grid" id="notFoundGrid">
            <?php
            $destinations = [
                ['icon' => 'fa-home',            'label' => 'Home',              'desc' => 'Welcome to ISKCON Seshadripuram',       'url' => ''],
                ['icon' => 'fa-hands-praying',   'label' => 'Book Puja',         'desc' => 'Offer prayers and book puja services',   'url' => 'booking/puja'],
                ['icon' => 'fa-fire',            'label' => 'Book Yagya',        'desc' => 'Sacred fire ceremonies and homas',       'url' => 'booking/yagya'],
                ['icon' => 'fa-hand-holding-heart','label' => 'Donate',          'desc' => 'Support the temple through seva',         'url' => 'donate'],
                ['icon' => 'fa-sync-alt',        'label' => 'Sudamaseva',        'desc' => 'Monthly seva subscription program',       'url' => 'sudamaseva'],
                ['icon' => 'fa-calendar-alt',    'label' => 'Festivals',         'desc' => 'Upcoming festivals and celebrations',     'url' => 'festivals'],
                ['icon' => 'fa-images',          'label' => 'Darshan / Gallery', 'desc' => 'Photo gallery of temple and deities',      'url' => 'darshan'],
                ['icon' => 'fa-blog',            'label' => 'Blogs',             'desc' => 'Spiritual articles and updates',           'url' => 'blogs'],
                ['icon' => 'fa-route',           'label' => 'Yatras',            'desc' => 'Pilgrimage tours and spiritual journeys',  'url' => 'yatra'],
                ['icon' => 'fa-concierge-bell',  'label' => 'Services',          'desc' => 'All temple services and programs',         'url' => 'services'],
                ['icon' => 'fa-graduation-cap',  'label' => 'Courses',           'desc' => 'Bhakti Shastri and spiritual education',   'url' => 'courses/bhakti-shastri'],
                ['icon' => 'fa-envelope',        'label' => 'Contact Us',        'desc' => 'Get in touch with the temple office',      'url' => 'contact'],
            ];
            foreach ($destinations as $d):
                $destUrl = BASE_URL . htmlspecialchars($d['url']);
            ?>
            <a href="<?= $destUrl ?>" class="not-found-link-card card-lift" data-filter-target>
                <div class="not-found-link-icon">
                    <i class="fas <?= htmlspecialchars($d['icon']) ?>"></i>
                </div>
                <div class="not-found-link-content">
                    <h3><?= htmlspecialchars($d['label']) ?></h3>
                    <p><?= htmlspecialchars($d['desc']) ?></p>
                </div>
                <i class="fas fa-chevron-right not-found-link-arrow"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
     SPIRITUAL BLESSING
     ============================================ -->
<section class="not-found-blessing">
    <div class="container">
        <div class="not-found-blessing-inner reveal">
            <div class="not-found-blessing-icon">
                <img src="<?= BASE_URL ?>assets/images/iskcon_logo.svg" alt="" width="48" height="48" loading="lazy">
            </div>
            <p class="not-found-blessing-text">
                "May your next step lead you to darshan, seva, and spiritual inspiration."
            </p>
            <span class="not-found-blessing-sig">— Hare Krishna</span>
        </div>
    </div>
</section>

<!-- ============================================
     HELP / CONTACT STRIP
     ============================================ -->
<section class="section not-found-help">
    <div class="container" style="text-align:center;">
        <div class="section-divider reveal">
            <span class="divider-icon"><i class="fas fa-headset"></i></span>
        </div>
        <h2 class="section-title reveal" style="text-align:center;">Still Can't Find What You're Looking For?</h2>
        <p class="section-description reveal" style="text-align:center;">
            Our temple office is here to help. Give us a call or send a message — we would be happy to assist you.
        </p>
        <div class="not-found-help-actions reveal">
            <a href="tel:<?= htmlspecialchars(SITE_PHONE) ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-phone-alt"></i> <?= htmlspecialchars(SITE_PHONE) ?>
            </a>
            <a href="<?= BASE_URL ?>contact" class="btn btn-outline-dark btn-lg">
                <i class="fas fa-envelope"></i> Send a Message
            </a>
        </div>
    </div>
</section>

<!-- ============================================
     Inline JS: Quick Finder Filter + Go Back
     ============================================ -->
<script>
(function () {
    'use strict';

    // --- Go Back fallback ---
    window.goBack = function () {
        if (document.referrer && document.referrer.indexOf(window.location.hostname) !== -1) {
            window.history.back();
        } else {
            window.location.href = '<?= BASE_URL ?>';
        }
    };

    // --- Quick Finder ---
    var input = document.getElementById('notFoundFinder');
    var clearBtn = document.getElementById('notFoundClear');
    var grid = document.getElementById('notFoundGrid');
    if (!input || !grid) return;

    var cards = grid.querySelectorAll('[data-filter-target]');

    function filterCards(query) {
        var q = query.toLowerCase().trim();
        var hasQuery = q.length > 0;

        cards.forEach(function (card) {
            var text = card.textContent.toLowerCase();
            var match = !hasQuery || text.indexOf(q) !== -1;

            if (match) {
                card.style.display = '';
                card.classList.add('reveal', 'visible');
            } else {
                card.style.display = 'none';
                card.classList.remove('reveal', 'visible');
            }
        });

        // Show/hide clear button
        if (clearBtn) {
            clearBtn.style.display = hasQuery ? 'flex' : 'none';
        }

        // Show "no results" state if needed
        var visibleCards = grid.querySelectorAll('[data-filter-target]:not([style*="display: none"])');
        var noResults = grid.querySelector('.not-found-no-results');
        if (visibleCards.length === 0) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.className = 'not-found-no-results';
                noResults.innerHTML = '<i class="fas fa-search" style="font-size:2rem;opacity:0.3;margin-bottom:1rem;display:block;"></i>'
                    + '<p>No destinations match your filter. Try: <strong>donate</strong>, <strong>darshan</strong>, <strong>booking</strong>, <strong>festivals</strong>, <strong>blogs</strong></p>';
                grid.appendChild(noResults);
            }
            noResults.style.display = '';
        } else if (noResults) {
            noResults.style.display = 'none';
        }
    }

    input.addEventListener('input', function () {
        filterCards(this.value);
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            filterCards('');
            input.focus();
        });
    }

    // Handle escape to clear
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            input.value = '';
            filterCards('');
            input.blur();
        }
    });
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
