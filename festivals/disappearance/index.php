<?php
$pageTitle = 'Disappearance Days';
include '../../partials/header.php';

$disappearances = [
    ['name' => 'Srila Prabhupada',                    'subtitle' => 'Founder-Acharya of ISKCON',                'desc' => 'The divine messenger who carried the teachings of Lord Caitanya to the West, translating essential Vedic scriptures and establishing a worldwide family of temples, farm communities, and schools.', 'legacy' => 'Established the global Sankirtana movement, published over 80 volumes of translation and commentary, and opened 108 temples.', 'cardIcon' => 'fa-globe',           'cardColor' => '#c86b1f', 'slug' => 'srila-prabhupada-disappearance'],
    ['name' => 'Srila Jagannatha dasa Babaji',        'subtitle' => 'Vaishnava Sarvabhauma',                    'desc' => 'The spiritual master of Srila Bhaktivinoda Thakura. Although blind and crippled, he confirmed the birthplace of Lord Caitanya (Yogapitha) by leaping in ecstasy upon arriving at the site.', 'legacy' => 'Discovered and confirmed the birthplace of Lord Sri Caitanya Mahaprabhu in Mayapur Navadvipa Dhama.', 'cardIcon' => 'fa-leaf',            'cardColor' => '#d4af37', 'slug' => 'srila-jagannatha-dasa-babaji-disappearance'],
    ['name' => 'Srila Bhaktivinoda Thakura',           'subtitle' => 'Pioneer of Modern Gaudiya Vaishnavism',    'desc' => 'A great magistrate, visionary leader, and prolific author who revived pure Vaishnava teachings in the 19th century. He discovered the birthplace of Lord Caitanya and predicted that holy names would spread globally.', 'legacy' => 'Authored over a hundred books, re-established the birthplace of Sri Caitanya, and predicted the coming of a global preacher.', 'cardIcon' => 'fa-book-open',       'cardColor' => '#7b1e1e', 'slug' => 'srila-bhaktivinoda-thakura-disappearance'],
    ['name' => 'Sri Srinivasa Acarya',                 'subtitle' => 'Post-Caitanya Era Luminary',               'desc' => 'One of the most prominent acharyas of the generation following Lord Caitanya. He recovered lost holy spots in Vrindavan and led the historic mission to distribute the writings of the Six Goswamis.', 'legacy' => 'Led the historic first book distribution party and composed the famous "Sad-gosvamy-astaka" prayers to the Six Goswamis.', 'cardIcon' => 'fa-scroll',          'cardColor' => '#d4af37', 'slug' => ''],
    ['name' => 'Srila Gaura Kisora dasa Babaji',       'subtitle' => 'Exalted Paramahamsa Babaji',               'desc' => 'The spiritual master of Srila Bhaktisiddhanta Sarasvati Thakura and close associate of Srila Bhaktivinoda Thakura. He was the crest jewel of avadhutas, living a life of absolute renunciation and devotion.', 'legacy' => 'Exemplified utter humility and complete indifference to the material world (nirapeksa), chanting and meditating constantly in Navadvipa Dhama.', 'cardIcon' => 'fa-pray',            'cardColor' => '#5a1414', 'slug' => 'gaura-kisora-dasa-babaji-disappearance'],
    ['name' => 'Srila Bhaktisiddhanta Saraswati Thakura', 'subtitle' => 'The Lion Guru & Scholar-Preacher',      'desc' => 'The spiritual master of Srila Prabhupada and founder of the Gaudiya Math. He was a brilliant astronomer and uncompromising preacher who modernized Vaishnava outreach using printing presses.', 'legacy' => 'Established 64 Gaudiya Math branches, popularized the printing of spiritual literatures, and instructed Srila Prabhupada to preach in English.', 'cardIcon' => 'fa-feather-alt',    'cardColor' => '#a85614', 'slug' => 'srila-bhaktisiddhanta-disappearance'],
];

$listItems = [];
foreach ($disappearances as $d) {
    $item = [
        'name'     => $d['name'],
        'subtitle' => $d['subtitle'],
        'desc'     => $d['desc'],
        'legacy'   => $d['legacy'],
        'cardIcon' => $d['cardIcon'],
        'cardColor'=> $d['cardColor'],
    ];
    if (!empty($d['slug'])) {
        $item['link'] = 'festivals/detail.php?slug=' . $d['slug'];
        $item['donationSlug'] = $d['slug'];
    }
    $listItems[] = $item;
}

$listConfig = [
    'category'         => 'disappearance',
    'title'            => 'Disappearance Days',
    'icon'             => '🌟',
    'description'      => 'Disappearance days honor the departure of our great spiritual masters and acharyas from this material world. We celebrate their divine legacy, guidelines (vani), and lifetimes of pure devotion.',
    'cardLayout'       => 'vertical_card',
    'showSearch'       => false,
    'emptyMessage'     => 'No disappearance days found.',
    'legacyLabel'      => 'Divine Vani & Legacy',
    'detailBtnLabel'   => 'Divine Pastimes',
    'donationBtnLabel' => 'Offer Seva',
];

include __DIR__ . '/../listing.php';
include '../../partials/footer.php';
