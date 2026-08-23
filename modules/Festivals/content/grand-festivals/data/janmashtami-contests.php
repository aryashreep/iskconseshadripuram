<?php
/**
 * Janmashtami Contest & Seva Data
 *
 * Structured data for all 6 contests and seva offerings.
 * Edit this file annually to update contest details.
 *
 * @return array
 */

return [

    // ================================================================
    // CONTESTS
    // ================================================================
    'contests' => [

        // ----------------------------------------------------------------
        // 1. DRESS TO BE BLESSED
        // ----------------------------------------------------------------
        [
            'slug'        => 'dress-to-be-blessed',
            'title'       => 'Dress to be Blessed',
            'summary'     => 'Dress your child in the most attractive Krishna attire and win prizes for the most impressive attires!',
            'icon'        => 'fa-child',
            'color'       => '#e91e63',
            'status'      => 'active',
            'mode'        => 'offline',
            'venue'       => 'ISKCON Seshadripuram',
            'schedule'    => 'Schedule will be announced',
            'age_groups'  => [
                [
                    'name'         => 'Group 1',
                    'age_range'    => '3 – 6 years',
                    'instructions' => 'Dress and dance in your favourite character from Lord Krishna\'s Vrindavan leela. Time: 1 min per participant.',
                ],
                [
                    'name'         => 'Group 2',
                    'age_range'    => '7 – 10 years',
                    'instructions' => 'Dress and dance / act out your favourite character from Vedic pastimes and tell us about your character. Time: 1 min per participant.',
                ],
                [
                    'name'         => 'Group 3',
                    'age_range'    => '11 – 15 years',
                    'instructions' => 'Dress and hone your acting skills by depicting inspiring and breath-taking events from Krishna Lila, Ram Leela, Chaitanya Leela. Time: 2 mins per participant.',
                ],
            ],
            'evaluation'  => 'Authenticity, adherence to time limit',
            'prizes'      => 'Attractive prizes for the winners. Blessings of Lord Krishna to every participant.',
        ],

        // ----------------------------------------------------------------
        // 2. SHLOKANJALI
        // ----------------------------------------------------------------
        [
            'slug'        => 'shlokanjali',
            'title'       => 'Shlokanjali',
            'summary'     => 'Sloka Memorisation Challenge! Recite slokas from Bhagavad Gita and Srimad Bhagavatam for different age groups.',
            'icon'        => 'fa-book-open',
            'color'       => '#1565c0',
            'status'      => 'active',
            'mode'        => 'online_offline',
            'venue'       => 'ISKCON Seshadripuram (Offline) / Zoom (Online)',
            'schedule'    => 'Schedule will be announced',
            'age_groups'  => [
                [
                    'name'         => 'Group 1',
                    'age_range'    => 'Up to 6 years',
                    'instructions' => 'Syllabus: Slokas from Bhagavad Gita Chapter 2. Recite any 5 slokas from Chapter 2.',
                ],
                [
                    'name'         => 'Group 2',
                    'age_range'    => '7 – 10 years',
                    'instructions' => 'Syllabus: Slokas from Bhagavad Gita Chapter 15. Recite 8 slokas from Chapter 15 with the sloka number and translation of any one. Translation from Bhagavad Gita As It Is by His Divine Grace Srila A.C. Bhakti Vedanta Swami Prabhupada.',
                ],
                [
                    'name'         => 'Group 3',
                    'age_range'    => '11 – 15 years',
                    'instructions' => 'Syllabus: Bhagavad Gita Chapter 13, Slokas 1–8. Recite B.G. 10.8–10.15 and translation of Catur-sloki Bhagavad Gita. Sloka translation from Bhagavad Gita As It Is by His Divine Grace Srila A.C. Bhakti Vedanta Swami Prabhupada.',
                ],
            ],
            'evaluation'  => 'Completeness, correctness of words, pronunciation, and meter',
            'prizes'      => 'Attractive prizes for the top three winners. Blessings of Lord Krishna to every participant.',
        ],

        // ----------------------------------------------------------------
        // 3. KAUN BANEGA KRISHNA MATI
        // ----------------------------------------------------------------
        [
            'slug'        => 'kaun-banega-krishna-mati',
            'title'       => 'Kaun Banega Krishna Mati',
            'summary'     => 'How much do you know about Krishna? Our Quiz Master will test your knowledge of Lord Krishna\'s pastimes!',
            'icon'        => 'fa-question-circle',
            'color'       => '#f57c00',
            'status'      => 'active',
            'hidden'      => true,
            'mode'        => 'online_offline',
            'venue'       => 'ISKCON Seshadripuram (Offline) / Zoom (Online)',
            'schedule'    => 'Schedule will be announced. Finals held offline at ISKCON Seshadripuram.',
            'age_groups'  => [
                [
                    'name'         => 'Group 2',
                    'age_range'    => '7 – 10 years',
                    'instructions' => 'Prerequisites: Knowledge of Lord Krishna\'s pastimes, Ramayana and Mahabharat. Basic Knowledge about Srila Prabhupada & ISKCON. Format: Multiple Choice Questions quiz.',
                ],
                [
                    'name'         => 'Group 3',
                    'age_range'    => '11 – 16 years',
                    'instructions' => 'Prerequisites: Knowledge of Lord Krishna\'s pastimes, Ramayana and Mahabharat. Knowledge about Srila Prabhupada & ISKCON. Format: Multiple Choice Questions quiz.',
                ],
                [
                    'name'         => 'Group 4',
                    'age_range'    => '16 years & above',
                    'instructions' => 'Prerequisites: Knowledge of Lord Krishna\'s pastimes, Ramayana and Mahabharat. Knowledge about Srila Prabhupada & ISKCON. Format: Multiple Choice Questions quiz.',
                ],
            ],
            'evaluation'  => 'Accuracy of answers in MCQ quiz. Top scorers move to finals.',
            'prizes'      => 'Attractive prizes for the winners. Blessings of Lord Krishna to every participant.',
        ],

        // ----------------------------------------------------------------
        // 4. KALANJALI — Colouring Competition
        // ----------------------------------------------------------------
        [
            'slug'        => 'kalanjali',
            'title'       => 'Kalanjali',
            'summary'     => 'Bring out the artist in you! A colouring and drawing competition for kids to express their devotion through art.',
            'icon'        => 'fa-palette',
            'color'       => '#7b1fa2',
            'status'      => 'active',
            'mode'        => 'offline',
            'venue'       => 'ISKCON Seshadripuram',
            'schedule'    => 'Schedule will be announced',
            'age_groups'  => [
                [
                    'name'         => 'Group 1',
                    'age_range'    => 'Up to 6 years',
                    'instructions' => 'Kids will be given colouring sheets. They will have to bring their own colour pencils, sketch pens, paints etc.',
                ],
                [
                    'name'         => 'Group 2',
                    'age_range'    => '7 – 10 years',
                    'instructions' => 'Kids will be given colouring sheets. They will have to bring their own colour pencils, sketch pens, paints etc.',
                ],
                [
                    'name'         => 'Group 3',
                    'age_range'    => '11 – 16 years',
                    'instructions' => 'Draw or sketch Lord\'s picture, Acharya\'s picture and colour the same. Picture should be authentic and adhering to Vedic version.',
                ],
                [
                    'name'         => 'Group 4',
                    'age_range'    => '16 years & above',
                    'instructions' => 'Draw or sketch Lord\'s picture, Acharya\'s picture and colour the same. Picture should be authentic and adhering to Vedic version.',
                ],
            ],
            'evaluation'  => 'Completeness, Creativity/Innovation, Neatness, Choice of colours',
            'prizes'      => 'Attractive prizes for the top three winners. Blessings of Lord Krishna to every participant.',
        ],

        // ----------------------------------------------------------------
        // 5. KIRTANAM — Bhajan Singing Competition
        // ----------------------------------------------------------------
        [
            'slug'        => 'kirtanam',
            'title'       => 'Kirtanam',
            'summary'     => 'Sing and express your devotion with a melodious bhajan! Show your love to Krishna by singing His glories.',
            'icon'        => 'fa-music',
            'color'       => '#00838f',
            'status'      => 'active',
            'mode'        => 'online_offline',
            'venue'       => 'ISKCON Seshadripuram (Offline) / Zoom (Online)',
            'schedule'    => 'Schedule will be announced',
            'age_groups'  => [
                [
                    'name'         => 'Group 1',
                    'age_range'    => 'Up to 6 years',
                    'instructions' => 'You can sing any Krishna bhajan / Vaishnav song. Time: 3 min per participant.',
                ],
                [
                    'name'         => 'Group 2',
                    'age_range'    => '7 – 10 years',
                    'instructions' => 'You can sing any Krishna bhajan / Vaishnav song. Song can be accompanied by a musical instrument. **You need not repeat the lines as that would take more time. Time: 3 min per participant.',
                ],
                [
                    'name'         => 'Group 3',
                    'age_range'    => '11 – 15 years',
                    'instructions' => 'You can sing any one Krishna bhajan / Vaishnav song. Song can be accompanied by a musical instrument. **You need not repeat the lines as that would take more time. Time: 3 min per participant.',
                ],
            ],
            'evaluation'  => 'Completeness, Raga & bhava (melody and emotion)',
            'prizes'      => 'Attractive prizes for the top three winners. Blessings of Lord Krishna to every participant.',
        ],

        // ----------------------------------------------------------------
        // 6. KATHAMRTA — Storytelling Competition
        // ----------------------------------------------------------------
        [
            'slug'        => 'kathamrta',
            'title'       => 'Kathamrta',
            'summary'     => 'Let\'s meditate on the Lord\'s appearance day by reciting His wonderful pastimes through storytelling.',
            'icon'        => 'fa-microphone-alt',
            'color'       => '#2e7d32',
            'status'      => 'active',
            'mode'        => 'online_offline',
            'venue'       => 'ISKCON Seshadripuram (Offline) / Zoom (Online)',
            'schedule'    => 'Schedule will be announced',
            'age_groups'  => [
                [
                    'name'         => 'Group 1',
                    'age_range'    => 'Up to 6 years',
                    'instructions' => 'Narrate any one pastime of Krishna and the demons (demons in Krishna leela). Props can be used. Time: 3 min per participant.',
                ],
                [
                    'name'         => 'Group 2',
                    'age_range'    => '7 – 10 years',
                    'instructions' => 'Narrate any pastime from Ramayana / Mahabharata. Explain the lesson learnt. Props can be used. Time: 3 min per participant.',
                ],
                [
                    'name'         => 'Group 3',
                    'age_range'    => '11 – 15 years',
                    'instructions' => 'Narrate any pastime from Ramayana, Mahabharata, Krishna Leela, Gaura Leela. Props and mimicry can be used. Time: 3 min per participant.',
                ],
            ],
            'evaluation'  => 'Authenticity, message and storytelling skills',
            'prizes'      => 'Attractive prizes for the top three winners. Blessings of Lord Krishna to every participant.',
        ],
    ],

    // ================================================================
    // SEVA OFFERINGS
    // ================================================================
    'sevas' => [
        [
            'slug'        => 'panchamrita-abhisheka',
            'title'       => 'Panchamrita Abhisheka',
            'summary'     => 'Participate in the sacred bathing ceremony of the Lord with five nectarean substances — milk, yogurt, ghee, honey, and sugar.',
            'icon'        => 'fa-tint',
            'color'       => '#c86b1f',
        ],
        [
            'slug'        => 'janmashtami-homam',
            'title'       => 'Janmashtami Homam',
            'summary'     => 'Sponsor the sacred fire ceremony invoking the Lord\'s presence and blessings for the well-being of all beings.',
            'icon'        => 'fa-fire',
            'color'       => '#d32f2f',
        ],
        [
            'slug'        => 'janmashtami-archana',
            'title'       => 'Janmashtami Archana',
            'summary'     => 'Offer special flower and mantra worship to the Lord on His auspicious appearance day.',
            'icon'        => 'fa-leaf',
            'color'       => '#388e3c',
        ],
    ],

    // ================================================================
    // REGISTRATION CONFIG
    // ================================================================
    'registration' => [
        'fee'          => 108, // ₹108 per participant per contest
        'currency'     => 'INR',
        'success_page' => BASE_URL . 'festivals/contest-registration-success',
    ],
];
