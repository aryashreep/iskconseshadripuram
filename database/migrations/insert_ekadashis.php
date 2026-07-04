<?php
/**
 * Insert Ekadashi records into donation_causes
 * Run BEFORE the migration script.
 * Usage: php database/insert_ekadashis.php
 */

require_once __DIR__ . '/../config.php';

$ekadashis = [
    // Slug             => [Title,                                        Short Title,              Desc]
    'saphala'     => ['Saphala Ekadashi',                                'Saphala Ekadashi',       'Makes all activities successful and helps the fallen soul achieve pure love of God.'],
    'putrada'     => ['Pausha-putrada Ekadashi',                        'Putrada Ekadashi',       'Bestows a qualified, pious son and delivers ancestors from hellish realms.'],
    'sattila'     => ['Sat-tila Ekadashi',                              'Sat-tila Ekadashi',      'Purifies through six uses of sesame seeds and provides liberation from poverty and sin.'],
    'bhaimi'      => ['Bhaimi Ekadashi (Jaya Ekadashi)',                'Bhaimi Ekadashi',        'Eradicates the heaviest sins, grants spiritual strength, and frees one from ghostly existences.'],
    'vijaya'      => ['Vijaya Ekadashi',                                'Vijaya Ekadashi',        'Bestows victory over all obstacles and adversaries.'],
    'amalaki'     => ['Amalaki Ekadashi',                               'Amalaki Ekadashi',       'Worship of the sacred Amalaki tree and Lord Parashurama, yielding immediate liberation.'],
    'papamocani'  => ['Papamochani Ekadashi',                           'Papamochani Ekadashi',   'The liberator from all sinful reactions, bad habits, and planetary curses.'],
    'kamada'      => ['Kamada Ekadashi',                                'Kamada Ekadashi',        'Fulfills all righteous desires of the heart and counteracts curses.'],
    'varuthini'   => ['Varuthini Ekadashi',                             'Varuthini Ekadashi',     'Protects the devotee, removes all sins, and turns charity into eternal spiritual assets.'],
    'mohini'      => ['Mohini Ekadashi',                                'Mohini Ekadashi',        'Dedicated to Lord Vishnu\'s Mohini form to defeat illusion and cross the material ocean.'],
    'apara'       => ['Apara Ekadashi',                                 'Apara Ekadashi',         'Grants unlimited merit and acts as an axe cutting down the forest of sinful deeds.'],
    'nirjala'     => ['Pandava Nirjala Ekadashi',                       'Nirjala Ekadashi',       'The most powerful and austere fast without taking water, yielding merit of all 24 Ekadashis.'],
    'yogini'      => ['Yogini Ekadashi',                                'Yogini Ekadashi',        'Equivalent to feeding 88,000 brahmanas, purging diseases and nullifying curses.'],
    'sayana'      => ['Sayana / Devshayani Ekadashi',                   'Sayana Ekadashi',        'Marks the start of Chaturmasya, when Lord Vishnu enters sleep on Shesha Naga.'],
    'kamika'      => ['Kamika Ekadashi',                                'Kamika Ekadashi',        'Highly pleasing to Lord Hari. Awards merit equal to performing a horse sacrifice.'],
    'pavitropana' => ['Pavitropana / Pavitra Ekadashi',                  'Pavitropana Ekadashi',   'Grants a noble heir and removes past offenses against cows.'],
    'annada'      => ['Annada / Aja Ekadashi',                          'Annada Ekadashi',        'Removes all sins, restores lost kingdoms, and relieves suffering.'],
    'parivartini' => ['Parshva / Parivartini Ekadashi',                  'Parivartini Ekadashi',   'Commemorates when Lord Vishnu turns over on His side while sleeping in the milk ocean.'],
    'indira'      => ['Indira Ekadashi',                                'Indira Ekadashi',        'Delivers deceased ancestors from hellish planets to Vaikuntha.'],
    'papankusa'   => ['Papa-ankusha Ekadashi',                          'Papankusha Ekadashi',    'Acts as a goad to control sins, fulfills desires, and awards liberation.'],
    'rama'        => ['Rama Ekadashi',                                  'Rama Ekadashi',          'Beloved of Srimati Radharani. Frees even the most fallen from sinful reactions.'],
    'utthana'     => ['Utthana / Devotthan Ekadashi',                   'Utthana Ekadashi',       'Marks the end of Chaturmasya, when Lord Vishnu awakens from cosmic slumber.'],
    'utpanna'     => ['Utpanna Ekadashi',                               'Utpanna Ekadashi',       'Marks the birth of Ekadashi Devi from Lord Vishnu to defeat the demon Mura.'],
    'moksada'     => ['Mokshada Ekadashi (Gita Jayanti)',               'Mokshada Ekadashi',      'Observed on the day Lord Krishna spoke the Bhagavad-gita, granting liberation.'],
];

$db = getDB();
$inserted = 0;
$skipped = 0;

foreach ($ekadashis as $slug => $data) {
    // Check if slug already exists
    $check = $db->prepare("SELECT id FROM donation_causes WHERE slug = ?");
    $check->execute([$slug]);
    if ($check->fetch()) {
        echo "SKIP: $slug already exists\n";
        $skipped++;
        continue;
    }

    $stmt = $db->prepare(
        "INSERT INTO donation_causes (slug, title, short_title, description, category, is_active, is_time_bound, image_url)
         VALUES (?, ?, ?, ?, 'ekadashi', 1, 1, ?)"
    );
    $imageUrl = 'assets/images/banners/' . $slug . '-ekadashi.jpg';
    $stmt->execute([$slug, $data[0], $data[1], $data[2], $imageUrl]);
    echo "INSERTED: $slug ({$data[0]})\n";
    $inserted++;
}

echo "\nDone: $inserted inserted, $skipped skipped\n";
