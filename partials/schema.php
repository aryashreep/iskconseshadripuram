<?php
/**
 * Schema.org Structured Data Partial
 *
 * Outputs appropriate JSON-LD based on $pageType.
 * Include from footer.php after the closing </footer> but before </body>.
 *
 * Expected variables (set before including header.php):
 *   $pageType  - 'home', 'about', 'festival', 'blog', 'contact', 'donate', 'gallery', 'default'
 *   $pageTitle - Page title (already set for <title>)
 *   $canonicalUrl - Canonical URL (already set in header)
 */

if (!isset($pageType)) $pageType = 'default';
$schema = [];

// ========================================
// 1. Organization + HinduTemple (every page)
// ========================================
$organization = [
    '@context' => 'https://schema.org',
    '@type' => 'HinduTemple',
    '@id' => BASE_URL . '#organization',
    'name' => SITE_NAME,
    'alternateName' => 'ISKCON Seshadripuram',
    'description' => 'ISKCON The Palace Temple of Lord Jagannath in Seshadripuram, Bangalore, established in 1998 by HH Jayapataka Swami Maharaj.',
    'url' => BASE_URL,
    'telephone' => SITE_PHONE,
    'email' => SITE_EMAIL,
    'foundingDate' => '1998-01-31',
    'founder' => [
        '@type' => 'Person',
        'name' => 'A.C. Bhaktivedanta Swami Prabhupada'
    ],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => '159, 1st Main road, Beside TRUGAS, Seshadripuram',
        'addressLocality' => 'Bengaluru',
        'addressRegion' => 'Karnataka',
        'postalCode' => '560020',
        'addressCountry' => 'IN'
    ],
    'openingHoursSpecification' => [
        [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'opens' => '05:00',
            'closes' => '20:30'
        ]
    ],
    'image' => BASE_URL . 'assets/images/iskcon_logo.svg',
    'sameAs' => [
        'https://www.facebook.com/sjmblr',
        'https://www.instagram.com/iskcon_seshadripuram',
        'https://www.youtube.com/@ISKCONSeshadripuramBengaluru'
    ]
];
$schema[] = $organization;

// ========================================
// 2. BreadcrumbList (every inner page)
// ========================================
if ($pageType !== 'home') {
    $breadcrumbs = [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => BASE_URL
    ];

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = array_values(array_filter(explode('/', $path)));
    $items = [$breadcrumbs];
    $currentPath = '';

    foreach ($segments as $pos => $seg) {
        $currentPath .= $seg . '/';
        // Try to get a readable name from the segment
        $name = ucwords(str_replace(['-', '_'], ' ', $seg));
        // Remove .php extension if present
        $name = str_replace('.php', '', $name);
        $items[] = [
            '@type' => 'ListItem',
            'position' => $pos + 2,
            'name' => $name,
            'item' => ($pos < count($segments) - 1) ? BASE_URL . $currentPath : null
        ];
    }

    $schema[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items
    ];
}

// ========================================
// 3. Event (festival detail page only)
// ========================================
if ($pageType === 'festival' && !empty($eventData)) {
    $eventSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $eventData['name'],
        'description' => $eventData['description'],
        'image' => $eventData['image'],
        'url' => $eventData['url'],
        'startDate' => $eventData['startDate'],
        'endDate' => $eventData['endDate'],
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'organizer' => $eventData['organizer'],
        'location' => $eventData['location'],
        'offers' => $eventData['offers'],
    ];

    // Add per-event type for the festival
    $eventCategoryLabels = [
        'festival' => 'ReligiousEvent',
        'ekadashi' => 'ReligiousEvent',
        'appearance' => 'ReligiousEvent',
        'disappearance' => 'ReligiousEvent',
        'event' => 'SocialEvent',
    ];
    $eventSubType = $eventCategoryLabels[$eventData['category']] ?? 'Event';
    if ($eventSubType !== 'Event') {
        $eventSchema['@type'] = [$eventSubType, 'Event'];
    }

    // Link back to organization
    $eventSchema['organizer']['@id'] = BASE_URL . '#organization';

    $schema[] = $eventSchema;
}

// ========================================
// 5. FAQ (donate/seva pages with FAQ content)
// ========================================
if ($pageType === 'donate' && !empty($faqItems)) {
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [],
    ];
    foreach ($faqItems as $item) {
        $faqSchema['mainEntity'][] = [
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['answer'],
            ],
        ];
    }
    $schema[] = $faqSchema;
}

// ========================================
// 6. Article (blog detail page only)
// ========================================
if ($pageType === 'blog' && !empty($articleData)) {
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $articleData['headline'],
        'description' => $articleData['description'],
        'image' => $articleData['image'],
        'datePublished' => $articleData['datePublished'],
        'dateModified' => $articleData['dateModified'] ? date('c', strtotime($articleData['dateModified'])) : $articleData['datePublished'],
        'author' => [
            '@type' => 'Organization',
            '@id' => BASE_URL . '#organization',
            'name' => $articleData['author'],
        ],
        'publisher' => [
            '@type' => 'Organization',
            '@id' => BASE_URL . '#organization',
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $articleData['url'],
        ],
    ];

    // Add keywords from tags if available
    if (!empty($articleData['tags']) && is_array($articleData['tags'])) {
        $articleSchema['keywords'] = implode(', ', $articleData['tags']);
    }

    $schema[] = $articleSchema;
}

// ========================================
// 7. ImageGallery / ImageObject (gallery page only)
// ========================================
if ($pageType === 'gallery' && !empty($galleryImages)) {
    // ImageGallery schema for the collection
    $gallerySchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ImageGallery',
        'name' => 'Temple Gallery — ' . SITE_NAME,
        'description' => 'Photo gallery of ISKCON The Palace Temple of Lord Jagannath in Seshadripuram, Bangalore. Includes deity darshan, festival celebrations, temple events, and spiritual moments.',
        'url' => BASE_URL . 'darshan',
        'author' => [
            '@type' => 'Organization',
            '@id' => BASE_URL . '#organization',
        ],
        'associatedMedia' => [],
    ];

    // Include up to 10 images as associatedMedia (keep schema compact)
    $maxImages = min(count($galleryImages), 10);
    for ($i = 0; $i < $maxImages; $i++) {
        $img = $galleryImages[$i];
        $gallerySchema['associatedMedia'][] = [
            '@type' => 'ImageObject',
            'contentUrl' => $img['url'],
            'caption' => $img['caption'],
            'name' => $img['name'],
        ];
    }

    $schema[] = $gallerySchema;
}

// ========================================
// 8. WebSite (home page only — search action)
// ========================================
if ($pageType === 'home') {
    $schema[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => BASE_URL . '#website',
        'url' => BASE_URL,
        'name' => SITE_NAME,
        'description' => SITE_NAME . ', ' . SITE_TAGLINE . ' — Official Website.',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => BASE_URL . 'search?q={search_term_string}'
            ],
            'query-input' => 'required name=search_term_string'
        ]
    ];
}

// ========================================
// Output all schemas
// ========================================
foreach ($schema as $s): ?>
<script type="application/ld+json">
<?php echo json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
</script>
<?php endforeach; ?>
