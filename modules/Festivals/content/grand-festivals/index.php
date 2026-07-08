<?php
$pageTitle = 'Grand Festivals';
$metaDescription = 'Explore grand Vaishnava festivals at the official ISKCON temple in Seshadripuram, Bangalore. Janmashtami, Rath Yatra, Gaura Purnima, Diwali, and more celebrations with kirtan and prasadam.';
include '../../partials/header.php';

// Fetch festivals from DB
$festivals = getFestivalsByCategory('festival', true);
if (empty($festivals)) {
    $festivals = getDonationCauses('festival', false);
}

// Build items array for listing template
$listItems = [];
foreach ($festivals as $f) {
    $listItems[] = [
        'slug'         => $f['slug'],
        'title'        => $f['short_title'] ?: $f['title'],
        'desc'         => $f['description'] ?? '',
        'image'        => $f['image_url'] ?: 'assets/images/banners/calendar.jpg',
        'link'         => 'festivals/detail.php?slug=' . urlencode($f['slug']),
        'donationSlug' => $f['slug'],
    ];
}

$listConfig = [
    'category'        => 'festival',
    'title'           => 'Grand Festivals',
    'icon'            => '🚩',
    'description'     => 'Join us in celebrating the main festivals of the year with devotion, kirtan, and prasadam at the temple.',
    'cardLayout'      => 'image_card',
    'showSearch'      => false,
    'emptyMessage'    => 'No festivals found. Check back soon for upcoming celebrations.',
    'detailBtnLabel'  => 'Details',
    'donationBtnLabel'=> 'Offer Seva',
    'donationCta'     => [
        'cause_slug' => 'festival-seva-fund',
        'button_label' => 'Support All Festivals',
        'background' => 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)',
    ],
];

include __DIR__ . '/../listing.php';
include '../../partials/footer.php';
