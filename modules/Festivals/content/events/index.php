<?php
$pageTitle = 'Events & Observances';
$metaDescription = 'Explore special Vaishnava events and spiritual observances at ISKCON Seshadripuram, Bangalore. Caturmasya Vrata, Shiksha Ceremony, and other sacred vows at the temple.';
include '../../partials/header.php';

$listItems = [
    [
        'title'        => 'Caturmasya Vrata',
        'desc'         => 'A four-month spiritual vow observed during the rainy season. Devotees practice advanced austerity by restricting certain food items in each of the four months (spinach, yogurt, milk, and urad dal respectively) while increasing their hearing, chanting, and scriptural study.',
        'image'        => 'assets/images/banners/caturmasya.jpg',
        'link'         => 'festivals/events/caturmasya/',
        'donationSlug' => 'events-seva-fund',
    ],
    [
        'title'        => 'Shiksha Ceremony',
        'desc'         => 'A formal initiation ceremony through which devotees commit themselves to the progressive spiritual path. Devotees accept guidelines and vows across 5 distinct levels (Sraddhavan, Krishna Sevak, Krishna Sadhaka, Srila Prabhupada Ashraya, and Sri Guru Charana Asraya).',
        'image'        => 'assets/images/banners/shiksha-ceremony.jpg',
        'link'         => 'festivals/events/shiksha-ceremony/',
        'donationSlug' => 'events-seva-fund',
    ],
];

$listConfig = [
    'category'        => 'event',
    'title'           => 'Events',
    'icon'            => '🌟',
    'description'     => 'Overview of major ongoing spiritual events, vows, and seasonal scriptural ceremonies held at the temple.',
    'cardLayout'      => 'image_card',
    'showSearch'      => false,
    'emptyMessage'    => 'No events found.',
    'detailBtnLabel'  => 'Learn More',
    'donationBtnLabel'=> 'Offer Seva',
];

include __DIR__ . '/../listing.php';
include '../../partials/footer.php';
