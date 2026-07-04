<?php
$pageTitle = 'Events & Observances';
include '../../partials/header.php';

$listItems = [
    [
        'title'        => 'Caturmasya Vrata',
        'desc'         => 'A four-month spiritual vow observed during the rainy season. Devotees practice advanced austerity by restricting certain food items in each of the four months (spinach, yogurt, milk, and urad dal respectively) while increasing their hearing, chanting, and scriptural study.',
        'date'         => 'July – November',
        'cardIcon'     => 'fa-moon',
        'link'         => 'festivals/detail.php?slug=caturmasya',
        'donationSlug' => 'events-seva-fund',
    ],
    [
        'title'        => 'Shiksha Ceremony',
        'desc'         => 'A formal initiation ceremony through which devotees commit themselves to the progressive spiritual path. Devotees accept guidelines and vows across 5 distinct levels (Sraddhavan, Krishna Sevak, Krishna Sadhaka, Srila Prabhupada Ashraya, and Sri Guru Charana Asraya).',
        'date'         => 'Periodic Initiation Vows Ceremony',
        'cardIcon'     => 'fa-graduation-cap',
        'link'         => 'festivals/detail.php?slug=shiksha-ceremony',
        'donationSlug' => 'events-seva-fund',
    ],
];

$listConfig = [
    'category'        => 'event',
    'title'           => 'Events',
    'icon'            => '🌟',
    'description'     => 'Overview of major ongoing spiritual events, vows, and seasonal scriptural ceremonies held at the temple.',
    'cardLayout'      => 'icon_card',
    'showSearch'      => false,
    'emptyMessage'    => 'No events found.',
    'detailBtnLabel'  => 'Learn More',
    'donationBtnLabel'=> 'Offer Seva',
];

include __DIR__ . '/../listing.php';
include '../../partials/footer.php';
