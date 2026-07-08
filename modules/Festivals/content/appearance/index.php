<?php
$pageTitle = 'Appearance Days';
$metaDescription = 'View Vaishnava appearance days at the official ISKCON temple in Seshadripuram, Bangalore. Honoring the descents of Lord Krishna, His expansions, and the appearances of great acharyas.';
include '../../partials/header.php';

$typeColors = ['avatar' => 'var(--maroon)', 'expansion' => 'var(--primary)', 'acharya' => 'var(--accent)', 'deity' => 'var(--primary-dark)', 'devotee' => 'var(--maroon-dark)'];
$typeIcons = ['avatar' => 'fa-crown', 'expansion' => 'fa-dharmachakra', 'acharya' => 'fa-scroll', 'deity' => 'fa-place-of-worship', 'devotee' => 'fa-pray'];
$typeLabels = ['avatar' => 'Divine Avatar', 'expansion' => 'Divine Expansion', 'acharya' => 'Spiritual Master (Acharya)', 'deity' => 'Divine Deity', 'devotee' => 'Pure Devotee'];

$appearances = [
  ['title' => 'Sri Advaita Acharya -- Appearance',           'slug' => 'sri-advaita-acharya-appearance',           'tithi' => 'Magha Shukla Saptami',           'type' => 'acharya',  'desc' => 'The incarnation of Maha-Vishnu and Sadashiva who descended to call down Lord Caitanya Mahaprabhu through heartfelt prayers and offerings of Ganges water and Tulasi leaves.'],
  ['title' => 'Srila Bhaktisiddhanta Saraswati Thakura -- Appearance', 'slug' => 'srila-bhaktisiddhanta-sarasvati-appearance', 'tithi' => 'Govinda Krishna Panchami', 'type' => 'acharya',  'desc' => 'The founder of the Gaudiya Math, brilliant astronomer, and spiritual master of Srila Prabhupada.'],
  ['title' => 'Sri Srivasa Pandita -- Appearance',           'slug' => '', 'tithi' => 'Chaitra Krishna Dashami',      'type' => 'devotee',  'desc' => 'One of the Pancha-tattva, representing the pure devotee. His courtyard (Srivas Angan) in Mayapur was the birthplace of the congregational chanting of the holy names.'],
  ['title' => 'Sri Gadadhara Pandita -- Appearance',          'slug' => '', 'tithi' => 'Chaitra Krishna Amavasya',     'type' => 'expansion', 'desc' => 'One of the members of the Pancha-tattva, representing the internal pleasure potency (Hladini-shakti) of Lord Sri Caitanya Mahaprabhu.'],
  ['title' => 'Nandotsava & Srila Prabhupada -- Appearance',  'slug' => 'srila-prabhupada-appearance',              'tithi' => 'Bhadrapada Krishna Navami',     'type' => 'acharya',  'desc' => 'The appearance anniversary of His Divine Grace A.C. Bhaktivedanta Swami Prabhupada, the Founder-Acharya of ISKCON.'],
  ['title' => 'Srila Bhaktivinoda Thakura -- Appearance',     'slug' => 'srila-bhaktivinoda-thakura-appearance',    'tithi' => 'Bhadrapada Shukla Dashami',    'type' => 'acharya',  'desc' => 'The pioneer of modern Gaudiya Vaishnavism who revitalized the preaching of Lord Caitanya\'s teachings and rediscovered His birthplace in Sridham Mayapur.'],
];

$listItems = [];
foreach ($appearances as $a) {
    $color = $typeColors[$a['type']] ?? 'var(--primary)';
    $item = [
        'title'    => $a['title'],
        'subtitle' => $typeLabels[$a['type']] ?? 'Festival',
        'desc'     => $a['desc'],
        'tithi'    => $a['tithi'],
        'cardIcon' => 'fas ' . ($typeIcons[$a['type']] ?? 'fa-star'),
        'cardColor'=> $color,
    ];
    if (!empty($a['slug'])) {
        $item['link'] = 'festivals/detail.php?slug=' . $a['slug'];
        $item['donationSlug'] = $a['slug'];
    }
    $listItems[] = $item;
}

$listConfig = [
    'category'         => 'appearance',
    'title'            => 'Appearance Days',
    'icon'             => '🪷',
    'description'      => 'Appearance days mark the descents of Lord Sri Krishna, His expansions, divine avatars, and the appearances of preeminent acharyas who guide us in the path of devotion.',
    'cardLayout'       => 'vertical_card',
    'showSearch'       => true,
    'searchFields'     => ['title', 'tithi', 'desc'],
    'searchPlaceholder'=> 'Search appearance days or tithis...',
    'emptyMessage'     => 'No matching appearance days or tithis found.',
    'metadataKeys'     => ['subtitle'],
    'metadataLabels'   => ['tithi' => 'Lunar Tithi: '],
    'detailBtnLabel'   => 'Divine Pastimes',
    'donationBtnLabel' => 'Offer Seva',
    'infoBox'          => '<p style="margin:0;"><strong>Note on Dates:</strong> Vaishnava festivals and appearance days are observed according to the Vedic lunar calendar (tithis). Because these tithis correspond to different dates on the solar Gregorian calendar each year, specific solar dates are not listed here. To see the scheduled dates for the current year, please consult the active <a href="' . BASE_URL . 'festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a>.</p>',
];

include __DIR__ . '/../listing.php';
include '../../partials/footer.php';
