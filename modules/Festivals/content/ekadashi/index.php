<?php
$pageTitle = 'Ekadashi';
$metaDescription = 'Explore all 24 Ekadashi fasting days of the year at ISKCON Seshadripuram, Bangalore. Learn glories, fasting rules, Vedic month names, and spiritual benefits of each Ekadashi.';
include '../../partials/header.php';

$ekadashis = [
  ['title' => 'Pausha-putrada Ekadashi',       'slug' => 'putrada',     'month' => 'Pausha',     'paksha' => 'Shukla (Waxing)',  'desc' => 'Bestows a qualified, pious son and delivers ancestors from hellish realms.'],
  ['title' => 'Sat-tila Ekadashi',             'slug' => 'sattila',     'month' => 'Magha',      'paksha' => 'Krishna (Waning)', 'desc' => 'Purifies through six uses of sesame seeds and provides liberation from poverty and sin.'],
  ['title' => 'Bhaimi Ekadashi (Jaya Ekadashi)','slug' => 'bhaimi',     'month' => 'Magha',      'paksha' => 'Shukla (Waxing)',  'desc' => 'Eradicates the heaviest sins, grants spiritual strength, and frees one from ghostly existences.'],
  ['title' => 'Vijaya Ekadashi',               'slug' => 'vijaya',     'month' => 'Phalguna',   'paksha' => 'Krishna (Waning)', 'desc' => 'Bestows victory over all obstacles and adversaries, as observed by Lord Ramachandra to cross the ocean.'],
  ['title' => 'Amalaki Ekadashi',              'slug' => 'amalaki',    'month' => 'Phalguna',   'paksha' => 'Shukla (Waxing)',  'desc' => 'Worship of the sacred Amalaki tree and Lord Parashurama, yielding immediate liberation.'],
  ['title' => 'Papamochani Ekadashi',          'slug' => 'papamocani', 'month' => 'Chaitra',    'paksha' => 'Krishna (Waning)', 'desc' => 'The liberator from all sinful reactions, bad habits, and planetary curses.'],
  ['title' => 'Kamada Ekadashi',               'slug' => 'kamada',     'month' => 'Chaitra',    'paksha' => 'Shukla (Waxing)',  'desc' => 'Fulfills all righteous desires of the heart and counteracts curses of demoniac nature.'],
  ['title' => 'Varuthini Ekadashi',            'slug' => 'varuthini',  'month' => 'Vaisakha',   'paksha' => 'Krishna (Waning)', 'desc' => 'Protects the devotee, removes all sins, and turns simple acts of charity into eternal spiritual assets.'],
  ['title' => 'Mohini Ekadashi',               'slug' => 'mohini',     'month' => 'Vaisakha',   'paksha' => 'Shukla (Waxing)',  'desc' => 'Dedicated to Lord Vishnu\'s beautiful Mohini Murti incarnation to defeat illusion and cross the material ocean.'],
  ['title' => 'Apara Ekadashi',                'slug' => 'apara',      'month' => 'Jyeshtha',   'paksha' => 'Krishna (Waning)', 'desc' => 'Grants unlimited merit and acts as an axe cutting down the forest of sinful deeds.'],
  ['title' => 'Pandava Nirjala Ekadashi',      'slug' => 'nirjala',    'month' => 'Jyeshtha',   'paksha' => 'Shukla (Waxing)',  'desc' => 'The most powerful and austere fast without taking water, yielding the merit of all 24 Ekadashis.'],
  ['title' => 'Yogini Ekadashi',               'slug' => 'yogini',     'month' => 'Ashadha',    'paksha' => 'Krishna (Waning)', 'desc' => 'Equivalent to feeding 88,000 brahmanas, purging diseases and nullifying severe curses.'],
  ['title' => 'Sayana / Devshayani Ekadashi',  'slug' => 'sayana',     'month' => 'Ashadha',    'paksha' => 'Shukla (Waxing)',  'desc' => 'Marks the start of Chaturmasya, when Lord Ksirodakasayi Vishnu enters sleep on Shesha Naga.'],
  ['title' => 'Kamika Ekadashi',               'slug' => 'kamika',     'month' => 'Shravana',   'paksha' => 'Krishna (Waning)', 'desc' => 'Highly pleasing to Lord Hari. Observing this fast awards merit equal to performing a horse sacrifice.'],
  ['title' => 'Pavitropana / Pavitra Ekadashi','slug' => 'pavitropana','month' => 'Shravana',   'paksha' => 'Shukla (Waxing)',  'desc' => 'Also known as Putrada Ekadashi. Grants a noble heir and removes past offenses against cows.'],
  ['title' => 'Annada / Aja Ekadashi',         'slug' => 'annada',     'month' => 'Bhadrapada', 'paksha' => 'Krishna (Waning)', 'desc' => 'Removes all sins, restores lost kingdoms, and relieves suffering, as observed by King Harishchandra.'],
  ['title' => 'Parshva / Parivartini Ekadashi','slug' => 'parivartini','month' => 'Bhadrapada', 'paksha' => 'Shukla (Waxing)',  'desc' => 'Commemorates the day when Lord Vishnu turns over on His side while sleeping in the milk ocean.'],
  ['title' => 'Indira Ekadashi',               'slug' => 'indira',     'month' => 'Ashvina',    'paksha' => 'Krishna (Waning)', 'desc' => 'Fasting on this day delivers deceased ancestors from hellish planets and promotes them to Vaikuntha.'],
  ['title' => 'Papa-ankusha Ekadashi',         'slug' => 'papankusa',  'month' => 'Ashvina',    'paksha' => 'Shukla (Waxing)',  'desc' => 'Acts as a goad to control sins, fulfills all desires, and awards ultimate liberation.'],
  ['title' => 'Rama Ekadashi',                 'slug' => 'rama',       'month' => 'Kartika',    'paksha' => 'Krishna (Waning)', 'desc' => 'Beloved of Srimati Radharani (Rama). Frees even the most fallen from all sinful reactions.'],
  ['title' => 'Utthana / Devotthan Ekadashi',  'slug' => 'utthana',    'month' => 'Kartika',    'paksha' => 'Shukla (Waxing)',  'desc' => 'Marks the end of Chaturmasya, when Lord Vishnu awakens from His four-month cosmic slumber.'],
  ['title' => 'Utpanna Ekadashi',              'slug' => 'utpanna',    'month' => 'Margashirsha','paksha' => 'Krishna (Waning)', 'desc' => 'Marks the birth of Ekadashi Devi from the body of Lord Vishnu to defeat the demon Mura.'],
  ['title' => 'Mokshada Ekadashi (Gita Jayanti)','slug' => 'moksada',  'month' => 'Margashirsha','paksha' => 'Shukla (Waxing)',  'desc' => 'Observed on the day Lord Krishna spoke the Bhagavad-gita, granting liberation and delivering ancestors.'],
  ['title' => 'Saphala Ekadashi',              'slug' => 'saphala',    'month' => 'Pausha',     'paksha' => 'Krishna (Waning)', 'desc' => 'Makes all activities successful and helps the fallen soul achieve pure love of God.'],
];

// Build a slug => image_url lookup from DB (so listing banners match detail page banners)
$dbImages = [];
$dbEkadashis = getDonationCauses('ekadashi', false);
foreach ($dbEkadashis as $db) {
    if (!empty($db['slug']) && !empty($db['image_url'])) {
        $dbImages[$db['slug']] = $db['image_url'];
    }
}

$listItems = [];
foreach ($ekadashis as $e) {
    $listItems[] = [
        'title'        => $e['title'],
        'desc'         => $e['desc'] . ' Observed in ' . $e['month'] . ' month during ' . $e['paksha'] . '.',
        'slug'         => $e['slug'],
        'image'        => $dbImages[$e['slug']] ?? 'assets/images/banners/ekadashi/' . $e['slug'] . '.jpg',
        'link'         => 'festivals/ekadashi/' . $e['slug'] . '/',
        'donationSlug' => 'ekadashi-general',
    ];
}

$listConfig = [
    'category'         => 'ekadashi',
    'title'            => 'Ekadashi',
    'icon'             => '🌙',
    'description'      => 'Ekadashi is the eleventh day of the lunar fortnight, dedicated to Lord Vishnu. Fasting on this day helps cleanse the body and elevate spiritual consciousness.',
    'cardLayout'       => 'image_card',
    'showSearch'       => true,
    'searchFields'     => ['title', 'desc'],
    'searchPlaceholder'=> 'Search Ekadashis by name or benefits...',
    'emptyMessage'     => 'No matching Ekadashis found.',
    'detailBtnLabel'   => 'Divine Glories',
    'donationBtnLabel' => 'Offer Ekadashi Seva',
    'infoBox'          => '<h4 style="margin-top:0;margin-bottom:var(--space-sm);color:var(--primary);font-family:var(--font-heading);font-weight:600;"><i class="fas fa-info-circle"></i> Fasting Guidelines</h4>Fasting starts at sunrise and ends the next morning during the Parana time. In general, grain, beans, and pulses must be avoided on Ekadashi. Devotees chant more rounds of the Hare Krishna maha-mantra and spend time in devotional reading and hearing. To see the scheduled Gregorian dates for the current year, please consult the active <a href="' . BASE_URL . 'festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a>.',
];

include __DIR__ . '/../listing.php';
include '../../partials/footer.php';
