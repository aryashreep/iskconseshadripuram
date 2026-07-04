-- ============================================
-- ISKCON Donation System - Complete Seed Data
-- Includes rich history, significance & benefits
-- for all donation causes
-- ============================================

-- -------------------------------------------------------
-- 1. SEVA CATEGORIES (13 reusable categories)
-- -------------------------------------------------------
INSERT INTO `donation_seva_categories` (`slug`, `name`, `sanskrit_name`, `icon`, `description`, `sort_order`) VALUES
('flower-decoration',   'Flower Decoration',        'Pushpa Seva / Shringar',   'fa-seedling',      'Sponsor the elaborate floral decorations for the deity altar and temple hall.', 1),
('deity-dress',         'Deity Dress / Alankar',    'Vastra Seva / Alankar',    'fa-tshirt',        'Sponsor new outfits and jewelry for the deities on special occasions.', 2),
('rajbhog-prasad',      'Rajbhog / Prasadam',       'Rajbhog Seva',             'fa-utensils',      'Sponsor the main offering of sanctified food to the deities.', 3),
('abhishekam',          'Abhishekam',               'Panchamrta Abhishekam',    'fa-tint',          'Sponsor the sacred bathing ceremony of the deities with auspicious liquids.', 4),
('homa-yajna',          'Homa / Yajna',             'Homa Seva',                'fa-fire',          'Sponsor the sacred fire sacrifice ceremony.', 5),
('annadanam',           'Annadanam / Food for Life','Annadhanam Seva',          'fa-hand-holding-heart', 'Sponsor free meal distribution to devotees and the needy.', 6),
('temple-decoration',   'Temple/Hall Decoration',   'Mandira Shringar',         'fa-building',      'Sponsor the decoration of the temple hall and premises.', 7),
('garland-seva',        'Garland Seva',             'Mala Seva',                'fa-flower',        'Sponsor fresh flower garlands for the deities.', 8),
('tulasi-archana',      'Tulasi / Archana',         'Tulasi Ashtottara',        'fa-leaf',          'Sponsor Tulasi worship and Archana ceremonies.', 9),
('pushpa-archana',      'Pushpa Archana',           'Pushpa Archana',           'fa-pray',          'Sponsor flower offering worship to the deities.', 10),
('kalash-abhishekam',   'Kalash Abhishekam',        'Gold/Silver Kalash',       'fa-trophy',        'Sponsor Kalash (pot) abhishekam with gold or silver vessels.', 11),
('general-festival',    'General Festival Seva',    'Utsava Seva',              'fa-gift',          'General festival donation to support overall celebrations.', 12),
('maha-prasad',         'Maha Prasad Seva',         'Maha Prasad',              'fa-drumstick-bite','Sponsor the grand feast and special prasadam distribution.', 13);

-- -------------------------------------------------------
-- 2. DONATION CAUSES - Umbrella Funds (parent causes with is_active=0)
-- -------------------------------------------------------
INSERT INTO `donation_causes` (`slug`, `title`, `short_title`, `category`, `subcategory`, `description`, `history`, `significance`, `benefits`, `allow_one_time`, `allow_monthly`, `default_mode`, `form_type`, `sort_order`, `featured`, `is_active`) VALUES
('festival-seva-fund',   'Festival Seva Fund',       'Festival Fund',   'festival',    NULL,
 'Support all major festival celebrations throughout the year.',
 'Throughout the year, ISKCON The Palace Temple of Lord Jagannath celebrates numerous festivals that bring the community together in devotion. From grand chariot processions to intimate temple ceremonies, each festival requires extensive planning, materials, and resources.',
 'Festivals are the lifeblood of temple culture. They bring the timeless pastimes of the Lord to life, allowing devotees to immerse themselves in divine consciousness and celebrate the glories of the Supreme Lord.',
 'By contributing to the Festival Seva Fund, you earn the collective blessings of all festivals celebrated throughout the year. Your support ensures that these sacred traditions continue to inspire and uplift countless souls.',
 1, 1, 'monthly', 'tiers', 1, 1, 0),

('ekadashi-seva-fund',   'Ekadashi Seva Fund',       'Ekadashi Fund',   'ekadashi',    NULL,
 'Support Ekadashi observances and special offerings.',
 'Ekadashi, the eleventh day of the lunar fortnight, is a day especially dear to Lord Vishnu. Observed since time immemorial, this sacred day involves fasting and heightened devotional practices as described in the Padma Purana.',
 'Lord Krishna Himself states in the Bhagavad-gita, "Among days I am Ekadashi." Observing Ekadashi purifies the mind and senses, helping devotees advance in spiritual life.',
 'Supporting Ekadashi observances helps maintain the tradition of fasting, special deity offerings, and prasadam distribution. Donors receive the spiritual benefits of these sacred observances throughout the year.',
 1, 1, 'monthly', 'tiers', 2, 1, 0),

('appearance-seva-fund', 'Appearance Seva Fund',     'Appearance Fund','appearance',  NULL,
 'Support appearance day celebrations of our exalted acharyas.',
 'The appearance days of our beloved acharyas mark the advent of great souls who dedicated their lives to spreading Krishna consciousness. Srila Prabhupada, Bhaktivinoda Thakura, Bhaktisiddhanta Sarasvati Thakura, and Advaita Acharya all appeared in this world to fulfill specific missions.',
 'These mahatmas appear out of their causeless mercy to deliver the fallen souls. Their appearance days are transcendental occasions, more significant than ordinary birthdays, as they commemorate the descent of pure representatives of the Lord.',
 'Celebrating their appearance days invokes their special blessings. Supporting these celebrations helps continue their mission of spreading the holy name and Vedic wisdom worldwide.',
 1, 1, 'monthly', 'tiers', 3, 1, 0),

('disappearance-seva-fund','Disappearance Seva Fund','Disappearance Fund','disappearance', NULL,
 'Support disappearance day observances of our exalted acharyas.',
 'The disappearance days of our exalted acharyas are observed with reverence and gratitude. These great souls left behind a legacy of love for Krishna, having dedicated every moment of their lives to the mission of Sri Chaitanya Mahaprabhu.',
 'Their departure is not an end but a transition to their eternal pastimes. Observing these days reminds us of their teachings, sacrifices, and the urgency of making progress in spiritual life.',
 'Honoring the disappearance of great devotees brings their mercy and blessings. Supporting these observances preserves their legacy and teachings for future generations.',
 1, 1, 'monthly', 'tiers', 4, 1, 0),

('events-seva-fund',     'Events Seva Fund',         'Events Fund',     'event',       NULL,
 'Support special temple events and programs throughout the year.',
 'Throughout the year, the temple organizes various special events including Caturmasya observances, Shiksha ceremonies, and programs that enrich the spiritual lives of devotees.',
 'These events provide structured opportunities for devotees to deepen their spiritual practices, learn from scriptures, and participate in community-building activities.',
 'Supporting temple events allows more devotees to benefit from these spiritual programs. Your contribution helps cover costs of materials, food, and facilities needed for successful events.',
 1, 1, 'monthly', 'tiers', 5, 1, 0);

-- -------------------------------------------------------
-- 3. DONATION CAUSES - Services (evergreen)
-- -------------------------------------------------------
INSERT INTO `donation_causes` (`slug`, `title`, `short_title`, `category`, `subcategory`, `description`, `history`, `significance`, `benefits`, `allow_one_time`, `allow_monthly`, `default_mode`, `form_type`, `sort_order`, `featured`, `is_active`, `page_type`, `page_slug`, `image_url`) VALUES

-- Daily Seva
('daily-seva', 'Daily Seva', 'Daily Seva', 'service', 'deity_worship',
 'Sponsor the daily worship, offerings, and maintenance of the deities at the temple. Your contribution supports the sacred daily rituals.',
 'The daily worship of the Deities in the temple follows the ancient Pancaratra system, a tradition that has been meticulously preserved in ISKCON temples worldwide. Srila Prabhupada established rigorous standards for deity worship, emphasizing that the Lord accepts our offerings through the pure medium of devotional service.',
 'Daily Seva is the foundation of temple worship. Each day, the deities are bathed, dressed, offered food, and worshipped with arati five times. Participating in this daily service connects you directly to the Lord\'s eternal pastimes.',
 'Sponsoring daily seva brings immense spiritual merit. You become a partner in the daily worship of the Lord, receiving blessings that purify your consciousness and bring peace to your home and family.',
 1, 1, 'one_time', 'tiers', 10, 1, 1, 'service', 'daily-seva', 'assets/images/banners/tulasi-maharani.jpg'),

-- Nitya Seva
('nitya-seva', 'Nitya Seva', 'Nitya Seva', 'service', 'deity_worship',
 'Become a regular sponsor of the daily seva of the deities. Choose from various levels of eternal service.',
 'The concept of Nitya (eternal) Seva has been practiced in Indian temples for millennia. Regular devotees take up the responsibility of sponsoring specific aspects of temple worship on an ongoing basis, ensuring the continuity of sacred rituals.',
 'Nitya Seva represents an unbroken chain of devotion. By committing to regular sponsorship, you establish an eternal connection with the temple and the deities.',
 'Regular seva brings sustained spiritual benefits. It creates a continuous flow of blessings, purifies your daily life, and ensures your family\'s spiritual well-being. Monthly sponsors receive regular updates and special acknowledgments.',
 1, 1, 'monthly', 'tiers', 11, 1, 1, 'service', 'nitya-seva', 'assets/images/banners/shaligram.jpg'),

-- Food for Life / Annadana
('food-for-life', 'Food for Life (Annadana)', 'Food for Life', 'service', 'food',
 'Sponsor sanctified meals for devotees, visitors, and the needy through our Food for Life program.',
 'The Food for Life program is ISKCON\'s international relief initiative, inspired by Srila Prabhupada\'s directive that "No one within ten miles of a temple should go hungry." Since 1974, ISKCON has served billions of plant-based meals worldwide.',
 'Annadana (donation of food) is considered the highest form of charity in Vedic culture. When the food is first offered to Lord Krishna, it becomes sanctified prasadam that purifies the heart and elevates consciousness.',
 'By sponsoring meals, you not only feed the hungry but also give them the opportunity to receive Krishna prasadam. The Bhagavad-gita states that offering food to the Lord and sharing it with others brings immense spiritual merit.',
 1, 1, 'one_time', 'tiers', 12, 1, 1, 'service', 'food-for-life', 'assets/images/banners/food-for-life-service.jpeg'),

-- Shastra Daan
('shastra-daan', 'Shastra Daan (Book Donation)', 'Shastra Daan', 'service', 'education',
 'Sponsor the distribution of Srila Prabhupada\'s Bhagavad-gita As It Is to spread Krishna consciousness.',
 'Srila Prabhupada\'s most significant contribution is his Bhagavad-gita As It Is, translated and commented upon to present the timeless wisdom in its pure form. He emphasized book distribution as the most effective method of preaching, saying "Print books, distribute profusely, and that will be the best preaching work."',
 'The Bhagavad-gita is the essence of all Vedic knowledge. Distributing this sacred text gives others the opportunity to understand the purpose of life, the nature of the soul, and the path to eternal happiness.',
 'Sponsoring Gita distribution makes you part of Srila Prabhupada\'s mission to spread Krishna consciousness worldwide. Every book distributed has the potential to transform someone\'s life forever.',
 1, 1, 'one_time', 'quantity', 13, 1, 1, 'service', 'shastra-daan', 'assets/images/banners/bhagavad-gita-as-it-is-banner.jpg'),

-- Tula Daan Utsav
('tula-daan-utsav', 'Tula Daan Utsav', 'Tula Daan', 'service', 'donation',
 'Donate items by weight — rice, wheat flour, edible oil, or dry fruits — in this sacred ritual of proportionate giving.',
 'Tula Daan (donation by weight) is an ancient Vedic practice where devotees donate items equal to their body weight. This temple offers a simplified version where you can donate essential food items by weight for distribution as prasadam.',
 'This practice symbolizes offering one\'s entire being to the Lord. By donating in proportion, you acknowledge that everything belongs to the Supreme and that we are merely caretakers of His resources.',
 'Tula Daan helps provide nutritious meals for thousands. The act of giving by weight symbolizes complete surrender and brings proportionally greater spiritual blessings.',
 1, 1, 'one_time', 'multi_item', 14, 1, 1, 'service', 'tula-daan', 'assets/images/banners/govindas-prasadam.jpg'),

-- Donate a Brick
('donate-a-brick', 'Donate a Brick (Temple Construction)', 'Donate a Brick', 'construction', NULL,
 'Contribute to temple construction and development. Each brick helps build the divine abode.',
 'Temple construction has been a cornerstone of Vedic culture for thousands of years. Kings, sages, and common people alike have contributed to building magnificent temples that stand as spiritual beacons through the ages.',
 'Building a temple creates a permanent center for spiritual activities. It provides a place where the Lord can reside and bless countless visitors for generations to come.',
 'The Srimad-Bhagavatam glorifies those who build temples for the Lord. Every brick contributed becomes part of the Lord\'s divine abode, bringing lasting spiritual merit to the donor and their family.',
 1, 0, 'one_time', 'tiers', 15, 1, 1, 'service', 'donate-a-brick', 'assets/images/banners/golden-temple.jpg'),

-- General Donation
('general-donation', 'General Donation', 'General', 'general', NULL,
 'Support the overall mission of the temple. Your donation helps us direct funds where they are most needed.',
 'The temple functions as a spiritual oasis, offering daily worship, educational programs, community services, and outreach activities. General donations provide the flexibility to allocate resources where they create the most impact.',
 'Your general donation becomes a lifeline for the temple\'s operations, supporting everything from electricity bills to spiritual programs, ensuring the temple remains a vibrant center of Krishna consciousness.',
 'General donors become essential partners in the temple\'s mission. Your support enables us to respond to urgent needs, seize opportunities for service, and maintain the highest standards of worship and hospitality.',
 1, 1, 'one_time', 'tiers', 20, 1, 1, 'service', 'general', 'assets/images/banners/founder-acharya.jpg');

-- -------------------------------------------------------
-- 4. DONATION CAUSES - Grand Festivals (time-bound)
-- -------------------------------------------------------
INSERT INTO `donation_causes` (`slug`, `title`, `short_title`, `category`, `subcategory`, `description`, `history`, `significance`, `benefits`, `allow_one_time`, `allow_monthly`, `default_mode`, `form_type`, `sort_order`, `is_time_bound`, `is_active`, `image_url`) VALUES

-- Janmashtami
('janmashtami', 'Sri Krishna Janmashtami', 'Janmashtami', 'festival', NULL,
 'The appearance day of Lord Sri Krishna — celebrate with grand abhishekam, flower decorations, and midnight arati.',
 'Janmashtami commemorates the birth of Lord Krishna, the Supreme Personality of Godhead, who appeared in the prison of Mathura at midnight on Bhadrapada Krishna Ashtami. Born to Devaki and Vasudeva, Krishna\'s advent was prophesied to end the tyrannical rule of King Kansa. The Srimad-Bhagavatam (10th Canto) vividly describes how the Lord appeared in His four-armed form before assuming the form of a small baby, and how Vasudeva carried Him across the Yamuna River to safety in Gokula.',
 'Lord Krishna\'s appearance marks the descent of the Absolute Truth into this material world. As He states in the Bhagavad-gita (4.7-8), "Whenever and wherever there is a decline in religious practice, I descend Myself." Janmashtami is the most significant day for devotees, as it celebrates the Lord\'s causeless mercy.',
 'Sponsoring Janmashtami celebrations brings immense spiritual blessings. Fasting and worshipping on this day removes all sins and bestows liberation. Donors who support the grand abhishekam, deity outfits, and prasadam distribution receive the Lord\'s special blessings and protection.',
 1, 0, 'one_time', 'tiers', 30, 1, 1, 'assets/images/banners/janmashtami.jpg'),

-- Gaura Purnima
('gaura-purnima', 'Gaura Purnima', 'Gaura Purnima', 'festival', NULL,
 'The appearance day of Sri Chaitanya Mahaprabhu — celebrate with sankirtan, abhishekam, and feast.',
 'Gaura Purnima celebrates the appearance of Sri Chaitanya Mahaprabhu, who is Lord Krishna Himself appearing in the golden complexion of Srimati Radharani. He took birth in Sridham Mayapur in 1486 AD as the son of Jagannath Mishra and Sachi Devi. His mission was to establish the sankirtana movement — congregational chanting of the Hare Krishna maha-mantra — as the yuga-dharma for the age of Kali.',
 'Lord Chaitanya is the most merciful incarnation, freely distributing the highest spiritual nectar of Krishna-prema (love of God) to all without distinction. He is known as Vishwambhara, "one who maintains the universe," and appeared for the liberation of all fallen souls.',
 'Participating in Gaura Purnima celebrations brings the special mercy of Lord Chaitanya. Sponsoring the abhishekam, deities\' outfits, or the grand feast invokes His blessings and helps you advance rapidly in Krishna consciousness.',
 1, 0, 'one_time', 'tiers', 31, 1, 1, 'assets/images/banners/gaura-purnima.jpg'),

-- Ratha Yatra
('ratha-yatra', 'Ratha Yatra', 'Ratha Yatra', 'festival', NULL,
 'The Festival of Chariots — Lord Jagannath\'s annual procession through the streets.',
 'Ratha-yatra has been celebrated for thousands of years in Jagannatha Puri, Odisha. The festival commemorates Lord Jagannath\'s (Krishna\'s ecstatic form) annual ride to the Gundicha temple. The British coined the term "juggernaut" from the massive chariots used. Srila Prabhupada was especially fond of this festival, organizing the first Ratha-yatra in San Francisco in 1967 and later in London, Paris, and other major cities worldwide.',
 'Ratha-yatra represents Lord Jagannatha\'s intense longing to reunite with His devotees in Vrindavan. The deities ride on grand chariots, allowing everyone — regardless of caste or creed — to see Them and receive Their blessings. Simply seeing the Lord on His chariot brings immense spiritual benefit.',
 'Pulling the chariot ropes is considered highly auspicious and removes all sins. Sponsoring the chariot, decorations, or prasadam distribution during Ratha-yatra brings the Lord\'s special blessings and ensures your name is remembered in His service.',
 1, 0, 'one_time', 'tiers', 32, 1, 1, 'assets/images/banners/ratha-yatra.jpg'),

-- Diwali
('diwali', 'Diwali - Festival of Lights', 'Diwali', 'festival', NULL,
 'The festival of lights celebrating the victory of good over evil.',
 'Diwali (Deepawali) has multiple historical significances: the return of Lord Rama to Ayodhya after 14 years of exile, Lord Krishna killing the demon Narakasura, the Damodara-lila of Mother Yashoda binding Krishna, the appearance of Goddess Lakshmi during the churning of the ocean, and Lord Vamana\'s rescue of Lakshmi from King Bali.',
 'The festival represents the victory of light over darkness, knowledge over ignorance, and good over evil. It coincides with the auspicious Damodara month (Kartika), which is especially dear to Lord Krishna.',
 'Lighting diyas in the temple and sponsoring special offerings during Diwali brings prosperity and spiritual advancement. Supporting the grand arati and prasadam distribution invokes the blessings of Lord Rama, Goddess Lakshmi, and Lord Krishna.',
 1, 0, 'one_time', 'tiers', 33, 1, 1, 'assets/images/banners/diwali.jpg'),

-- Govardhan Puja
('govardhan-puja', 'Govardhan Puja', 'Govardhan Puja', 'festival', NULL,
 'Commemorating Lord Krishna lifting Govardhan Hill to protect the Vrajavasis.',
 'Govardhan Puja commemorates the pastime when seven-year-old Lord Krishna lifted the Govardhana Hill with His little finger to protect the residents of Vrindavana from the devastating rains sent by King Indra. This pastime, described in the Srimad-Bhagavatam (10th Canto), established that a devotee of Krishna need not worship demigods for material benefits.',
 'This festival teaches the importance of surrendering to Krishna and the futility of worshipping demigods. The Annakut (hill of food) prepared on this day represents the Vrajavasis\' love for Krishna and their gratitude for His protection.',
 'Sponsoring Govardhan Puja, especially the Annakut offering and cow worship, brings Krishna\'s special protection and blessings. Donors who support this festival receive the same mercy that Krishna showed to the residents of Vrindavana.',
 1, 0, 'one_time', 'tiers', 34, 1, 1, 'assets/images/banners/govardhan-puja-banner.jpg'),

-- Narasimha Chaturdashi
('narasimha-chaturdashi', 'Narasimha Chaturdashi', 'Narasimha', 'festival', NULL,
 'The appearance day of Lord Nrsimhadeva, the half-man, half-lion incarnation.',
 'Lord Narasimhadeva appeared from a palace pillar to save His five-year-old devotee Prahlada Maharaja from his demoniac father, Hiranyakashipu. The Lord\'s form as half-man, half-lion was uniquely designed to fulfill Hiranyakashipu\'s benedictions while still ending his reign of terror. This pastime illustrates the Lord\'s unlimited creative power to protect His devotees.',
 'Lord Narasimha is known as the great protector of devotees and remover of obstacles. He appeared at twilight (neither day nor night), on a threshold (neither inside nor outside), using His fingernails (no weapons), as neither man nor animal.',
 'Worshipping Lord Narasimha removes all obstacles on the spiritual path. Sponsoring the abhishekam, special lamps, and offerings on this day invokes His fierce protection and destroys all negativity and obstacles in one\'s life.',
 1, 0, 'one_time', 'tiers', 35, 1, 1, 'assets/images/banners/narasimha-chaturdashi.jpg'),

-- Rama Navami
('rama-navami', 'Sri Rama Navami', 'Rama Navami', 'festival', NULL,
 'The appearance day of Lord Sri Ramachandra, the ideal king and seventh avatar of Vishnu.',
 'Sri Rama Navami celebrates the birth of Lord Ramachandra, the seventh incarnation of Lord Vishnu, born to King Dasharatha and Queen Kaushalya of Ayodhya. Lord Rama\'s life story is told in the epic Ramayana by Sage Valmiki. He is known as Maryada-purushottama — the perfect embodiment of righteousness, duty, and virtue.',
 'Lord Rama\'s life exemplifies how to live as an ideal son, husband, king, and devotee. His victory over the demon Ravana represents the triumph of dharma over adharma. His reign (Ram Rajya) is remembered as a golden age of peace and prosperity.',
 'Observing Rama Navami and sponsoring the celebrations invokes Lord Rama\'s blessings for a life of righteousness and integrity. Donors receive the protection of Lord Rama, Mother Sita, Lakshmana, and Hanuman.',
 1, 0, 'one_time', 'tiers', 36, 1, 1, 'assets/images/banners/rama-navami.jpg'),

-- Radhashtami
('radhashtami', 'Sri Radhashtami', 'Radhashtami', 'festival', NULL,
 'The appearance day of Srimati Radharani, the pleasure potency of Lord Krishna.',
 'Radhashtami celebrates the appearance of Srimati Radharani, Krishna\'s eternal consort and the feminine aspect of the Absolute Truth. She is the Hladini-shakti (pleasure potency) of Lord Krishna, appearing in Vrindavan as the daughter of King Vrishabhanu and Queen Kirtida. Her name comes before Krishna\'s in the Hare Krishna maha-mantra.',
 'Without Radharani\'s mercy, one cannot approach Krishna. She is the most compassionate and merciful aspect of the Divine, recommending sincere souls to Krishna. Her love for Krishna is the ultimate expression of devotion.',
 'Sponsoring Radhashtami celebrations, especially the floral decorations and abhishekam, invokes Radharani\'s special mercy. Donors receive Her compassionate blessings, which help them develop love for Krishna and progress in devotional life.',
 1, 0, 'one_time', 'tiers', 37, 1, 1, 'assets/images/banners/radhashtami-banner.jpg'),

-- Gopastami
('gopastami', 'Gopastami', 'Gopastami', 'festival', NULL,
 'Marks the day Krishna and Balarama began herding cows in the forests of Vrindavan.',
 'Gopastami commemorates the day when five-year-old Krishna and Balarama were allowed by Their father Nanda Maharaja to go to the forest to herd cows for the first time. This marked Their transition from infancy to cowherd boys and is described in the Srimad-Bhagavatam\'s 10th Canto.',
 'Lord Krishna is known as Gopala (protector of cows) and Govinda (one who gives pleasure to the cows). This festival highlights the importance of cow protection (go-raksha) in Vedic culture and Krishna\'s intimate relationship with the cows of Vrindavan.',
 'Participating in Gopastami celebrations and cow worship brings the special blessings of Lord Krishna and Lord Balarama. Sponsoring gau-seva (cow service) on this day is considered extremely meritorious and purifies one\'s existence.',
 1, 0, 'one_time', 'tiers', 38, 1, 1, 'assets/images/banners/gopastami.jpg'),

-- Jhulan Yatra
('jhulan-yatra', 'Jhulan Yatra', 'Jhulan Yatra', 'festival', NULL,
 'The swing festival of Sri Sri Radha-Krishna celebrated during the rainy season.',
 'Jhulan Yatra is celebrated during the month of Sravana (July-August) when devotees place the deities of Radha and Krishna on a beautifully decorated swing (jhulan). This festival is especially associated with the pastimes of Radha-Krishna in Vrindavan during the monsoon season.',
 'The swinging pastimes of Radha and Krishna represent the intimate, loving exchanges between the Supreme Lord and His consort. Participating in this festival helps devotees develop a personal relationship with the divine couple.',
 'Sponsoring the decoration of the swing, offerings of cool drinks, and special prasadam during Jhulan Yatra brings the loving blessings of Radha and Krishna. It is an opportunity to participate in their intimate pastimes.',
 1, 0, 'one_time', 'tiers', 39, 1, 1, 'assets/images/banners/jhulan-yatra.jpg'),

-- Nandotsava
('nandotsava', 'Nandotsava', 'Nandotsava', 'festival', NULL,
 'The grand celebration by Nanda Maharaja after Krishna\'s birth in Gokula.',
 'After Vasudeva brought baby Krishna to Gokula, Nanda Maharaja celebrated the birth of his son with great joy, not knowing the child was the Supreme Lord Himself. He distributed gifts, fed the brahmanas, and performed grand celebrations. This festival is celebrated the day after Janmashtami.',
 'Nandotsava demonstrates the pure love of the Vrajavasis for Krishna. Although Nanda Maharaja thought Krishna was his son, his love was actually transcendental. The festival reminds us of the joyful mood of Krishna\'s appearance.',
 'Sponsoring Nandotsava celebrations brings the blessings of Nanda Maharaja and Mother Yashoda. It is particularly beneficial for those seeking to develop parental affection for Krishna.',
 1, 0, 'one_time', 'tiers', 40, 1, 1, 'assets/images/banners/nandotsava.jpg'),

-- Balarama Purnima
('balarama-purnima', 'Sri Balarama Purnima', 'Balarama', 'festival', NULL,
 'The appearance day of Lord Balarama, the elder brother of Lord Krishna.',
 'Sri Balarama appeared as the seventh son of Devaki and was transferred to the womb of Rohini to protect Him from Kansa. He is the first expansion of Lord Krishna (Sankarshana) and the reservoir of spiritual strength. His appearance day is celebrated on the full moon day of Sravana month.',
 'Lord Balarama represents the original spiritual master and the source of all spiritual power. He is the embodiment of service to Krishna, always protecting and serving His younger brother.',
 'Sponsoring Balarama Purnima celebrations invokes spiritual strength and purification. Balarama\'s blessings help devotees overcome material attachments and develop firm faith in Krishna consciousness.',
 1, 0, 'one_time', 'tiers', 41, 1, 1, 'assets/images/banners/balarama-purnima.jpg'),

-- Snana Yatra
('snana-yatra', 'Snana Yatra', 'Snana Yatra', 'festival', NULL,
 'The sacred bathing festival of Lord Jagannath on the full moon of Jyestha.',
 'Snana Yatra, also known as Devasnan Purnima, is an ancient festival originating from the Skanda Purana. King Indradyumna established this grand bathing ceremony for Lord Jagannath. The deities are bathed with 108 pots of sanctified water from the Golden Well (Suna Kua) amidst Vedic mantras.',
 'Witnessing the deities during Snana Yatra is believed to absolve all sins. The festival marks the appearance of Lord Jagannath and precedes the 15-day Anasara period when the deities recuperate before Ratha Yatra.',
 'Sponsoring the Jalabhisheka ingredients (water, sandalwood, turmeric, perfumes), the Hati Vesha (elephant attire), or the medical offerings during Anasara brings immense spiritual merit. Donors receive the Lord\'s special blessings for purification.',
 1, 0, 'one_time', 'tiers', 42, 1, 1, 'assets/images/banners/snana-yatra.jpg'),

-- Akshaya Tritiya
('akshaya-tritiya', 'Akshaya Tritiya', 'Akshaya', 'festival', NULL,
 'The most auspicious day of the year — start of Chandana Yatra and eternal merit.',
 'Akshaya Tritiya is considered the most auspicious day in the Vedic calendar, requiring no astrological muhurta. On this day, the Treta Yuga began, the Ganges descended to earth, and Parasurama appeared. It also marks the beginning of Chandana Yatra (sandalwood paste festival) for the deities.',
 '"Akshaya" means "imperishable" — any charity or spiritual activity performed on this day yields eternal, never-diminishing results. The day falls on the third day of the bright fortnight of Vaishakha.',
 'Donations made on Akshaya Tritiya multiply exponentially and never diminish. Sponsoring the Chandana Yatra offerings and prasadam distribution on this day ensures eternal spiritual merit and material prosperity.',
 1, 0, 'one_time', 'tiers', 43, 1, 1, 'assets/images/banners/akshaya-tritiya.jpg'),

-- Gita Jayanti
('gita-jayanti', 'Gita Jayanti', 'Gita Jayanti', 'festival', NULL,
 'The advent day of Srimad Bhagavad-gita, spoken by Lord Krishna to Arjuna.',
 'Gita Jayanti marks the day when Lord Krishna spoke the Bhagavad-gita to Arjuna on the battlefield of Kurukshetra over 5,000 years ago. This sacred dialogue, recorded in the Bhishma Parva of the Mahabharata, contains the essence of all Vedic wisdom. Srila Prabhupada translated and commented on the Gita as "Bhagavad-Gita As It Is," which has been distributed in millions worldwide.',
 'The Bhagavad-gita is the only book that contains the actual words of the Supreme Personality of Godhead. It provides complete knowledge of the self, the universe, and the ultimate goal of life — surrender to Krishna.',
 'Sponsoring Gita distribution on this day multiplies the benefit millions of times. Supporting the recitation of all 18 chapters, the fire yajna, and book distribution invokes Lord Krishna\'s special blessings for knowledge and enlightenment.',
 1, 0, 'one_time', 'tiers', 44, 1, 1, 'assets/images/banners/gita-jayanti.jpg'),

-- Nityananda Trayodashi
('nityananda-trayodashi', 'Nityananda Trayodashi', 'Nityananda', 'festival', NULL,
 'The appearance day of Lord Nityananda Prabhu, the most merciful incarnation.',
 'Nityananda Trayodashi celebrates the appearance of Lord Nityananda, the elder brother of Lord Chaitanya and the combined incarnation of Lord Balarama. Born in Ekachakra, Nityananda Prabhu traveled extensively as a young sannyasi before meeting Chaitanya Mahaprabhu and joining His sankirtana movement.',
 'Lord Nityananda is known as the most merciful incarnation, who delivers even the most fallen souls. His mercy is so powerful that whoever receives it is immediately purified and becomes eligible for Krishna-prema.',
 'Sponsoring Nityananda Trayodashi celebrations invokes His unlimited mercy. Supporting the festival helps deliver fallen souls and brings the associate-like blessings of Lord Nityananda.',
 1, 0, 'one_time', 'tiers', 45, 1, 1, 'assets/images/banners/nityananda-trayodashi.jpg'),

-- Bahulastami
('bahulastami', 'Bahulastami', 'Bahulastami', 'festival', NULL,
 'The appearance of Radha-kunda and Syama-kunda, the most sacred ponds in Vrindavan.',
 'Bahulastami celebrates the appearance of Radha-kunda and Syama-kunda in Vrindavan. These sacred ponds were created by Lord Krishna and Srimati Radharani during their pastimes. A bath in these kundas on this day is considered equal to performing all austerities and pilgrimages.',
 'These ponds are non-different from Radha and Krishna themselves. Devotees consider taking bath in these sacred waters as the most purifying act, cleansing all sins and bestowing love of God.',
 'Sponsoring the special arati and offerings at the temple\'s sacred water source on Bahulastami invokes the purifying blessings of Radha-kunda and Syama-kunda.',
 1, 0, 'one_time', 'tiers', 46, 1, 1, 'assets/images/banners/bahulastami.jpg'),

-- Odana Sasthi
('odana-sasthi', 'Odana Sasthi', 'Odana Sasthi', 'festival', NULL,
 'Lord Jagannath is offered winter garments as the seasons change.',
 'Odana Sasthi marks the day when Lord Jagannath and His siblings are dressed in thick, warm winter garments. This festival signals the arrival of the winter season and the deities\' preparation for the cold months ahead. The ritual has been observed in Jagannatha Puri for centuries.',
 'This festival highlights the intimate relationship between the Lord and His devotees, who lovingly care for His comfort. It demonstrates that the Lord accepts our loving service in whatever form we offer it.',
 'Sponsoring Odana Sasthi offerings brings the Lord\'s blessings for protection and warmth. Donors who offer winter garments to the deities receive spiritual warmth and the Lord\'s special care.',
 1, 0, 'one_time', 'tiers', 47, 1, 1, 'assets/images/banners/odana-sasthi.jpg'),

-- Tulasi-Shaligram Vivaha
('tulasi-shaligram-vivaha', 'Tulasi-Shaligram Vivaha', 'Tulasi Vivaha', 'festival', NULL,
 'The sacred wedding ceremony of Tulasi Devi and Lord Shaligram.',
 'Tulasi Vivaha marks the ceremonial marriage of the Tulasi plant (an expansion of Srimati Radharani) to the Shaligram shila (a form of Lord Vishnu). This festival begins the wedding season in India and is considered highly auspicious. Tulasi Devi is supremely dear to Lord Krishna.',
 'Tulasi is the most sacred plant in Vedic tradition. She is worshipped daily in Vaishnava homes and temples. Her marriage to Lord Vishnu/Shaligram represents the eternal union of the devotee with the Lord.',
 'Participating in Tulasi-Shaligram Vivaha brings enormous spiritual merit. It is considered equal to performing a Vedic wedding ceremony. Donors who sponsor this festival receive Tulasi Devi\'s blessings and are freed from all sins.',
 1, 0, 'one_time', 'tiers', 48, 1, 1, 'assets/images/banners/tulasi-shaligram-vivaha.jpg'),

-- Panihati
('panihati', 'Panihati Chida-Dahi Utsav', 'Panihati', 'festival', NULL,
 'Commemorating Lord Nityananda\'s pastime of distributing chida-dahi to Raghunatha dasa Gosvami.',
 'The Panihati Chida-Dahi Utsav commemorates the famous pastime where Lord Nityananda Prabhu, along with His associates, sat under a tree in Panihati (near Kolkata) and distributed chida (flattened rice) and dahi (yogurt) to everyone. Ragunatha dasa Gosvami, who was then a young boy from a wealthy family, was initiated into devotional service on this day.',
 'This festival demonstrates Nityananda Prabhu\'s unlimited mercy and His ability to attract anyone to devotional service, regardless of their background. It teaches the importance of humble association with pure devotees.',
 'Sponsoring the distribution of chida-dahi prasadam on this day brings the special mercy of Lord Nityananda and Raghunatha dasa Gosvami. It is an opportunity to participate in a pastime that has been celebrated for over 500 years.',
 1, 0, 'one_time', 'tiers', 49, 1, 1, 'assets/images/banners/panihati.jpg'),

-- Varaha Dwadashi
('varaha-dwadashi', 'Varaha Dwadashi', 'Varaha', 'festival', NULL,
 'The appearance day of Lord Varahadeva, the boar incarnation of Lord Vishnu.',
 'Varaha Dwadashi celebrates Lord Varahadeva, the third incarnation of Lord Vishnu, who appeared as a gigantic boar to rescue the Earth (personified as Bhumi Devi) from the demon Hiranyaksha. Lord Varaha lifted the Earth on His tusks from the depths of the Garbhodaka Ocean and killed the demon.',
 'This incarnation demonstrates the Lord\'s willingness to assume any form to protect His devotees and the cosmic order. Lord Varaha is also intimately connected with the creation and preservation of the universe.',
 'Sponsoring Varaha Dwadashi celebrations invokes the Lord\'s protective blessings. Donors receive protection from all dangers and the assurance that the Lord will always come to their rescue.',
 1, 0, 'one_time', 'tiers', 50, 1, 1, 'assets/images/banners/varaha-dwadashi.jpg'),

-- Pushya Abhisheka
('pushya-abhisheka', 'Pushya Abhisheka', 'Pushya', 'festival', NULL,
 'Lord Krishna is decorated with flower outfits and bathed in petals.',
 'Pushya Abhisheka is a special festival where the deity of Lord Krishna is decorated with flower garments and bathed with flower petals. This unique abhishekam takes place when the moon is in the Pushya nakshatra, an especially auspicious constellation.',
 'The flower decorations and petal bath represent the beauty and sweetness of the Lord\'s pastimes. This festival celebrates the intimate mood of loving service to Krishna in Vrindavan.',
 'Sponsoring the Pushya Abhisheka, especially the flower garments and petal offerings, brings the most beautiful and sweet blessings of Lord Krishna. Donors receive the Lord\'s special affection and grace.',
 1, 0, 'one_time', 'tiers', 51, 1, 1, 'assets/images/banners/pushya-abhisheka.jpg'),

-- Bhishma Panchaka
('bhishma-panchaka', 'Bhishma Panchaka', 'Bhishma', 'festival', NULL,
 'Five days of special austerities during the Kartika month.',
 'Bhishma Panchaka refers to the five days during the Kartika month dedicated to special spiritual practices. Grandfather Bhishma, one of the greatest maharathis in the Mahabharata, observed these five days with great austerity. It is said that Bhishma chose to leave his body on this auspicious period.',
 'These five days are considered the most sacred part of the already-auspicious Kartika month. Observing special vows during this period destroys all sins and bestows the highest spiritual benefits.',
 'Sponsoring the special offerings, lamps, and prasadam during Bhishma Panchaka brings the combined blessings of the entire Kartika month. Donors receive the grace of Grandfather Bhishma and Lord Krishna.',
 1, 0, 'one_time', 'tiers', 52, 1, 1, 'assets/images/banners/bhishma-panchaka.jpg'),

-- Sri Sri Radha-Ramana
('sri-sri-radha-ramana', 'Sri Sri Radha-Ramana Appearance', 'Radha-Ramana', 'festival', NULL,
 'The self-manifestation of Sri Radha-Ramana deity of Vrindavan.',
 'Sri Sri Radha-Ramana is one of the seven main deities of Vrindavan, self-manifested through the devotional service of Srila Gopala Bhatta Goswami. The deity appeared from a Shalagram shila and has been worshipped in Vrindavan for over 500 years. This festival celebrates the appearance of this famous deity.',
 'The Radha-Ramana temple is one of the most significant Gaudiya Vaishnava temples in Vrindavan. The deity\'s self-manifestation demonstrates the Lord\'s reciprocation with the sincere service of His devotees.',
 'Sponsoring this festival connects you with the rich heritage of Vrindavan\'s worship. Donors receive the blessings of the Radha-Ramana deity and Srila Gopala Bhatta Goswami.',
 1, 0, 'one_time', 'tiers', 53, 1, 1, 'assets/images/banners/sri-sri-radha-ramana-appearance-banner.jpg');

-- -------------------------------------------------------
-- 5. DONATION CAUSES - Appearance Days
-- -------------------------------------------------------
INSERT INTO `donation_causes` (`slug`, `title`, `short_title`, `category`, `subcategory`, `description`, `history`, `significance`, `benefits`, `allow_one_time`, `allow_monthly`, `default_mode`, `form_type`, `sort_order`, `is_active`, `page_type`, `image_url`) VALUES
('srila-prabhupada-appearance', 'Srila Prabhupada Appearance Day', 'Srila Prabhupada', 'appearance', NULL,
 'The appearance day of His Divine Grace A.C. Bhaktivedanta Swami Prabhupada, founder-acharya of ISKCON.',
 'Srila Prabhupada appeared in Calcutta on September 1, 1896. He met his spiritual master, Srila Bhaktisiddhanta Sarasvati Thakura, in 1922 and received the order to spread Krishna consciousness in the English-speaking world. At the age of 69, he sailed to America with only seven dollars and a trunk of books, establishing the International Society for Krishna Consciousness in 1966.',
 'Srila Prabhupada is the foremost exponent of Krishna consciousness in the modern age. He translated and commented on over 70 volumes of Vedic literature and established 108 temples worldwide. His appearance day is celebrated as a major festival across all ISKCON centers.',
 'Celebrating Srila Prabhupada\'s appearance day invokes his special blessings for advancing in Krishna consciousness. Sponsoring the celebration helps continue his mission of spreading the holy name worldwide.',
 1, 0, 'one_time', 'tiers', 60, 1, 'appearance', 'assets/images/banners/srila-prabhupada-appearance-banner.jpg'),

('srila-bhaktivinoda-thakura-appearance', 'Srila Bhaktivinoda Thakura Appearance', 'Bhaktivinoda Thakura', 'appearance', NULL,
 'The appearance day of Srila Bhaktivinoda Thakura, the pioneer of the Gaudiya Vaishnava revival.',
 'Srila Bhaktivinoda Thakura (1838-1914) was a great scholar, magistrate, and the pioneer of the Gaudiya Vaishnava revival in the 19th century. He rediscovered the birthplace of Sri Chaitanya Mahaprabhu in Mayapur, wrote extensively on Vaishnava philosophy, and predicted the coming of a "broad-minded preacher" who would spread Krishna consciousness worldwide — Srila Prabhupada.',
 'Bhaktivinoda Thakura revived the teachings of Lord Chaitanya and the Six Goswamis at a time when they were almost lost. His books and compositions continue to inspire millions.',
 'Sponsoring his appearance day celebrations invokes the blessings of a great acharya who dedicated his life to spreading the glories of the holy name.',
 1, 0, 'one_time', 'tiers', 61, 1, 'appearance', 'assets/images/banners/srila-bhaktivinoda-thakura-appearance-banner.jpg'),

('srila-bhaktisiddhanta-sarasvati-appearance', 'Srila Bhaktisiddhanta Sarasvati Appearance', 'Sarasvati Thakura', 'appearance', NULL,
 'The appearance day of Srila Bhaktisiddhanta Sarasvati Thakura, the spiritual master of Srila Prabhupada.',
 'Srila Bhaktisiddhanta Sarasvati Thakura (1874-1937) was the son of Srila Bhaktivinoda Thakura and the spiritual master of Srila Prabhupada. He established 64 Gaudiya Mathas worldwide and vigorously preached against sahajiyaism and impersonalism. His powerful preaching and establishment of the Gaudiya Matha set the stage for ISKCON.',
 'He was a powerful preacher who fearlessly established the supremacy of Krishna consciousness. His emphasis on combining book distribution with preaching became the model for ISKCON\'s missionary work.',
 'Celebrating his appearance day brings the blessings of a pure Vaishnava who dedicated his life to establishing the true teachings of Lord Chaitanya.',
 1, 0, 'one_time', 'tiers', 62, 1, 'appearance', 'assets/images/banners/srila-bhaktisiddhanta-sarasvati-thakura-appearance-banner.jpg'),

('sri-advaita-acharya-appearance', 'Sri Advaita Acharya Appearance', 'Advaita Acharya', 'appearance', NULL,
 'The appearance day of Sri Advaita Acharya, the incarnation of Mahavishnu and Lord Brahma combined.',
 'Sri Advaita Acharya was a great devotee and incarnation of Mahavishnu who lived in Shantipur, West Bengal. He was instrumental in bringing about the descent of Sri Chaitanya Mahaprabhu. When he saw the suffering condition of the fallen souls of Kali-yuga, he fervently worshipped Lord Krishna with Tulasi leaves and water, crying out for His appearance.',
 'Advaita Acharya\'s intense prayers were the immediate cause of Lord Chaitanya\'s descent. He is considered the combined incarnation of Mahavishnu and Lord Brahma, and his role in the Gaudiya Vaishnava tradition is unparalleled.',
 'Sponsoring Advaita Acharya\'s appearance day invokes the blessings of the great devotee whose prayers brought Lord Chaitanya to this world. Donors receive his mercy for developing intense devotion.',
 1, 0, 'one_time', 'tiers', 63, 1, 'appearance', 'assets/images/banners/sri-advaita-acharya-appearance-banner.jpg');

-- -------------------------------------------------------
-- 6. DONATION CAUSES - Disappearance Days
-- -------------------------------------------------------
INSERT INTO `donation_causes` (`slug`, `title`, `short_title`, `category`, `subcategory`, `description`, `history`, `significance`, `benefits`, `allow_one_time`, `allow_monthly`, `default_mode`, `form_type`, `sort_order`, `is_active`, `page_type`, `image_url`) VALUES
('srila-prabhupada-disappearance', 'Srila Prabhupada Disappearance Day', 'Srila Prabhupada', 'disappearance', NULL,
 'The disappearance day of His Divine Grace A.C. Bhaktivedanta Swami Prabhupada.',
 'Srila Prabhupada left this world on November 14, 1977, in Vrindavan, surrounded by his loving disciples. He had established ISKCON in just 11 years, spreading Krishna consciousness to every continent. His passing was a transcendental event, with devotees around the world remembering his extraordinary contributions.',
 'The disappearance of a great Vaishnava is not a cause for mourning but for reflection on his teachings and legacy. Srila Prabhupada continues to guide millions through his books, which are his eternal presence.',
 'Observing Srila Prabhupada\'s disappearance day reminds us to follow his instructions. Sponsoring the celebrations helps preserve his legacy and brings his blessings for advancing in devotional service.',
 1, 0, 'one_time', 'tiers', 70, 1, 'disappearance', 'assets/images/banners/srila-prabhupada-disappearance-banner.jpg'),

('srila-bhaktivinoda-thakura-disappearance', 'Bhaktivinoda Thakura Disappearance', 'Bhaktivinoda Thakura', 'disappearance', NULL,
 'The disappearance day of Srila Bhaktivinoda Thakura.',
 'Srila Bhaktivinoda Thakura left this world on June 23, 1914, in Calcutta. He spent his final years in complete dedication to writing and publishing Vaishnava literature. Even his government service as a magistrate was used as an opportunity to spread Krishna consciousness.',
 'His departure marked the end of an era of Vaishnava revival. His legacy continues through his numerous books, songs, and the institution he established.',
 'Honoring his disappearance brings the blessings of a great acharya who revived Lord Chaitanya\'s movement in the modern age.',
 1, 0, 'one_time', 'tiers', 71, 1, 'disappearance', 'assets/images/banners/srila-bhaktivinoda-thakura-disappearance-banner.jpg'),

('srila-bhaktisiddhanta-disappearance', 'Bhaktisiddhanta Sarasvati Disappearance', 'Sarasvati Thakura', 'disappearance', NULL,
 'The disappearance day of Srila Bhaktisiddhanta Sarasvati Thakura.',
 'Srila Bhaktisiddhanta Sarasvati Thakura departed from this world on January 1, 1937, in Calcutta. Despite facing many obstacles, he established 64 mathas and tirelessly preached the philosophy of acintya-bhedabheda-tattva (inconceivable oneness and difference).',
 'His powerful preaching left an indelible mark on the spiritual landscape of India. His disciple, Srila Prabhupada, would go on to fulfill his vision of spreading Krishna consciousness worldwide.',
 'Honoring his disappearance invokes the blessings of a powerful spiritual warrior who fearlessly established the truth of devotional service.',
 1, 0, 'one_time', 'tiers', 72, 1, 'disappearance', 'assets/images/banners/srila-bhaktisiddhanta-sarasvati-thakura-banner.jpg'),

('srila-jagannatha-dasa-babaji-disappearance', 'Srila Jagannatha dasa Babaji Disappearance', 'Jagannatha dasa Babaji', 'disappearance', NULL,
 'The disappearance day of Srila Jagannatha dasa Babaji, the great paramahamsa of Vrindavan.',
 'Srila Jagannatha dasa Babaji was one of the most exalted Vaishnava paramahamsas of the 19th century. He lived in Vrindavan for many years and was intimately connected with Srila Bhaktivinoda Thakura. He personally identified the actual birthplace of Sri Chaitanya Mahaprabhu in Mayapur.',
 'His pure love for Krishna and humble disposition made him one of the most respected Vaishnavas of his time. His confirmation of Chaitanya Mahaprabhu\'s birthplace was crucial for the Gaudiya Vaishnava revival.',
 'Honoring his disappearance brings the blessings of a pure devotee who had the highest realization of Krishna consciousness.',
 1, 0, 'one_time', 'tiers', 73, 1, 'disappearance', 'assets/images/banners/srila-jagannatha-dasa-babaji-disappearance-banner.jpg'),

('gaura-kisora-dasa-babaji-disappearance', 'Gaura Kisora dasa Babaji Disappearance', 'Gaura Kisora dasa Babaji', 'disappearance', NULL,
 'The disappearance day of Srila Gaura Kisora dasa Babaji, the spiritual master of Srila Bhaktisiddhanta Sarasvati Thakura.',
 'Srila Gaura Kisora dasa Babaji was a paramahamsa Vaishnava who lived as a wandering mendicant, always absorbed in chanting the holy names. Despite being illiterate, he was the spiritual master of the great scholar Srila Bhaktisiddhanta Sarasvati Thakura, demonstrating that pure devotion transcends all material qualifications.',
 'His life exemplified that love for Krishna can be attained through simple, sincere chanting, without any material qualifications. His position as the spiritual master of a great scholar shows Krishna\'s special mercy on the humble.',
 'Honoring his disappearance brings the blessings of a pure paramahamsa who demonstrates that sincere devotion is more important than any material accomplishment.',
 1, 0, 'one_time', 'tiers', 74, 1, 'disappearance', 'assets/images/banners/gaura-kisora-dasa-babaji-banner.jpg');

-- -------------------------------------------------------
-- 7. DONATION CAUSES - Ekadashi
-- -------------------------------------------------------
INSERT INTO `donation_causes` (`slug`, `title`, `short_title`, `category`, `subcategory`, `description`, `history`, `significance`, `benefits`, `allow_one_time`, `allow_monthly`, `default_mode`, `form_type`, `sort_order`, `is_active`, `image_url`) VALUES
('ekadashi-general', 'Ekadashi Seva', 'Ekadashi', 'ekadashi', NULL,
 'Sponsor special offerings and prasadam distribution on Ekadashi.',
 'Ekadashi, the eleventh day of both the waxing and waning moon, has been observed as a day of fasting and increased devotion since time immemorial. The Padma Purana and other scriptures glorify this day as "Hari-vasara" — the day of Lord Hari. Lord Krishna declares in the Bhagavad-gita, "Among days I am Ekadashi."',
 'Observing Ekadashi helps control the senses and mind, making it easier to focus on Krishna. Fasting on this day is said to destroy all sins and bring one closer to the Lord. Even broken observance gives great benefit.',
 'Sponsoring Ekadashi offerings and prasadam helps others observe this sacred day. Donors receive the combined spiritual benefits of all Ekadashi observances throughout the year.',
 1, 0, 'one_time', 'tiers', 80, 1, 'assets/images/banners/bhaimi-ekadashi.jpg');

-- -------------------------------------------------------
-- 8. DONATION CAUSES - Events
-- -------------------------------------------------------
INSERT INTO `donation_causes` (`slug`, `title`, `short_title`, `category`, `subcategory`, `description`, `history`, `significance`, `benefits`, `allow_one_time`, `allow_monthly`, `default_mode`, `form_type`, `sort_order`, `is_active`, `image_url`) VALUES
('caturmasya', 'Caturmasya Seva', 'Caturmasya', 'event', NULL,
 'Sponsor the four-month period of special austerities and offerings.',
 'Caturmasya is the four-month period (July to October) during the rainy season when Lord Vishnu is said to sleep (Yoga-nidra). Devotees observe various vows during this period — such as avoiding certain foods — as an austerity to please the Lord. This tradition is followed by all Vaishnavas.',
 'Observing Caturmasya vows helps control the senses and deepen one\'s spiritual practice. Each month has specific observances that purify different aspects of one\'s life and bring one closer to Krishna.',
 'Sponsoring Caturmasya observances supports devotees in their vows. Donors receive the spiritual benefits of the austerities performed and the Lord\'s special blessings during this sacred period.',
 1, 0, 'one_time', 'tiers', 90, 1, 'assets/images/banners/caturmasya.jpg'),

('shiksha-ceremony', 'Shiksha Ceremony', 'Shiksha', 'event', NULL,
 'Sponsor the sacred thread ceremony and educational rites for young devotees.',
 'The Shiksha ceremony (sacred thread initiation) is a Vedic samskara that marks the beginning of a child\'s formal education in Vedic knowledge. This ceremony has been performed for thousands of years and is an important tradition for preserving Vedic culture.',
 'This ceremony marks a child\'s second birth (dvija) into spiritual life. It is a crucial step in preserving Vedic culture and ensuring the next generation grows up with spiritual values.',
 'Sponsoring a Shiksha ceremony helps a child begin their spiritual education. Donors receive the blessings of the child\'s family and the satisfaction of helping preserve Vedic traditions for future generations.',
 1, 0, 'one_time', 'tiers', 91, 1, 'assets/images/banners/shiksha-ceremony.jpg');

-- ================================================================
-- 9. CAUSE-SEVA PRICING
-- ================================================================

-- Helper: Map category IDs to names
-- 1=flower-decoration, 2=deity-dress, 3=rajbhog-prasad, 4=abhishekam, 5=homa-yajna,
-- 6=annadanam, 7=temple-decoration, 8=garland-seva, 9=tulasi-archana, 10=pushpa-archana,
-- 11=kalash-abhishekam, 12=general-festival, 13=maha-prasad

-- -------------------------------------------------------
-- A. DAILY SEVA sub-options (cause slug = 'daily-seva')
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 9, 'Archana', 108, 'Daily Archana worship ceremony', 1),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 9, 'Tulsi Astothara', 251, 'Tulsi worship with 108 names', 2),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 9, 'Krishna Astothara', 1008, 'Krishna worship with 108 names', 3),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 5, 'Narasimha Puja', 2508, 'Lord Narasimha special worship', 4),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 4, 'Shaligram Puja', 1008, 'Shaligram shila worship', 5),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 1, 'Pushpa Seva / Daily Garland (Partial)', 508, 'Contribute to daily flower garlands for deities', 6),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 3, 'Rajbog Seva', 1008, 'Sponsor the midday Rajbog offering', 7),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 2, 'Vigraha Seva (Arati / Paraphernalia)', 5008, 'Support daily arati items and deity paraphernalia', 8),
((SELECT id FROM `donation_causes` WHERE slug='daily-seva'), 12, 'Full Day Seva (For You / Your Family)', 11008, 'Sponsor all sevas for one full day', 9);

-- -------------------------------------------------------
-- B. NITYA SEVA sub-options
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='nitya-seva'), 12, 'Govardhan Seva', 4000, 'Nitya seva at Govardhan level', 1),
((SELECT id FROM `donation_causes` WHERE slug='nitya-seva'), 12, 'Sudarshan Seva', 7000, 'Nitya seva at Sudarshan level', 2),
((SELECT id FROM `donation_causes` WHERE slug='nitya-seva'), 12, 'Subhadra Seva', 10000, 'Nitya seva at Subhadra level', 3),
((SELECT id FROM `donation_causes` WHERE slug='nitya-seva'), 12, 'Baladev Seva', 15000, 'Nitya seva at Baladev level', 4),
((SELECT id FROM `donation_causes` WHERE slug='nitya-seva'), 12, 'Jagannath Seva', 20000, 'Nitya seva at Jagannath level', 5),
((SELECT id FROM `donation_causes` WHERE slug='nitya-seva'), 12, 'Gaurangi Bangaloreswar Seva', 50000, 'Nitya seva at the highest level', 6);

-- -------------------------------------------------------
-- C. FOOD FOR LIFE sub-options
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 25 Meals', 1000, 'Sponsor 25 nutritious meals', 1),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 50 Meals', 2000, 'Sponsor 50 nutritious meals', 2),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 100 Meals', 4000, 'Sponsor 100 nutritious meals', 3),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 125 Meals', 5000, 'Sponsor 125 nutritious meals', 4),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 250 Meals', 10000, 'Sponsor 250 nutritious meals', 5),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 500 Meals', 20000, 'Sponsor 500 nutritious meals', 6),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 1000 Meals', 40000, 'Sponsor 1000 nutritious meals', 7),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 1250 Meals', 50000, 'Sponsor 1250 nutritious meals', 8),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 2500 Meals', 100000, 'Sponsor 2500 nutritious meals', 9),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 5000 Meals', 200000, 'Sponsor 5000 nutritious meals', 10),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 7500 Meals', 300000, 'Sponsor 7500 nutritious meals', 11),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 10000 Meals', 400000, 'Sponsor 10000 nutritious meals', 12),
((SELECT id FROM `donation_causes` WHERE slug='food-for-life'), 6, 'Donate 12500 Meals', 500000, 'Sponsor 12500 nutritious meals', 13);

-- -------------------------------------------------------
-- D. SHASTRA DAAN - quantity-based (books)
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='shastra-daan'), 12, 'Bhagavad-gita (Big) - All Languages', 300, 'Full-size deluxe edition Bhagavad-gita As It Is', 1),
((SELECT id FROM `donation_causes` WHERE slug='shastra-daan'), 12, 'Bhagavad-gita (Pocket Size) - All Languages', 250, 'Compact pocket edition Bhagavad-gita As It Is', 2);

-- -------------------------------------------------------
-- E. TULA DAAN - multi-item cart (per kg pricing)
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='tula-daan-utsav'), 6, 'Rice', 50, 'Per kg - Donate rice by weight for Tula Daan', 1),
((SELECT id FROM `donation_causes` WHERE slug='tula-daan-utsav'), 6, 'Wheat Flour', 40, 'Per kg - Donate wheat flour by weight for Tula Daan', 2),
((SELECT id FROM `donation_causes` WHERE slug='tula-daan-utsav'), 6, 'Edible Oil', 140, 'Per litre - Donate edible oil for Tula Daan', 3),
((SELECT id FROM `donation_causes` WHERE slug='tula-daan-utsav'), 6, 'Dry Fruits', 800, 'Per kg - Donate dry fruits for Tula Daan', 4);

-- -------------------------------------------------------
-- F. DONATE A BRICK
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='donate-a-brick'), 12, '1 Brick', 1100, 'Sponsor one brick for temple construction', 1),
((SELECT id FROM `donation_causes` WHERE slug='donate-a-brick'), 12, '5 Bricks', 5001, 'Sponsor five bricks for temple construction', 2),
((SELECT id FROM `donation_causes` WHERE slug='donate-a-brick'), 12, '11 Bricks', 10001, 'Sponsor eleven bricks for temple construction', 3),
((SELECT id FROM `donation_causes` WHERE slug='donate-a-brick'), 12, '51 Bricks', 51000, 'Sponsor fifty-one bricks for temple construction', 4),
((SELECT id FROM `donation_causes` WHERE slug='donate-a-brick'), 12, '108 Bricks', 108000, 'Sponsor 108 bricks — highly auspicious', 5);

-- -------------------------------------------------------
-- G. GENERAL DONATION
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='general-donation'), 12, 'Any Contribution', 101, 'Any amount you wish to contribute', 1),
((SELECT id FROM `donation_causes` WHERE slug='general-donation'), 12, 'Modest Support', 501, 'Support the temple mission', 2),
((SELECT id FROM `donation_causes` WHERE slug='general-donation'), 12, 'Generous Gift', 1001, 'A generous contribution', 3),
((SELECT id FROM `donation_causes` WHERE slug='general-donation'), 12, 'Major Donation', 5001, 'A major donation for the temple', 4);

-- -------------------------------------------------------
-- H. GRAND FESTIVAL PRICING - Janmashtami (flagship)
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `is_featured`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 12, 'Sri Vigraha Seva Mukhya Yajaman Seva', 200008, 'Be the main sponsor of the Janmashtami celebrations', 1, 1),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 13, 'Maha Prasad Seva', 175008, 'Sponsor the grand prasadam feast', 0, 2),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 2, 'Sri Vigraha Seva Deity Dress', 151008, 'Sponsor new deity outfits for Janmashtami', 0, 3),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 1, 'Flower Decoration', 100008, 'Sponsor elaborate flower decorations', 0, 4),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 7, 'Temple Hall Decoration', 100008, 'Sponsor temple hall decorations', 0, 5),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 6, 'Annadhanam Seva', 50008, 'Sponsor food distribution', 0, 6),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 3, 'Rajbhog Seva', 25008, 'Sponsor the Rajbhog offering', 0, 7),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 8, 'Deity Garland Seva', 15008, 'Sponsor fresh flower garlands', 0, 8),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 12, 'Festival Donation', 10008, 'General festival contribution', 0, 9),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 11, 'Gold Kalash Abhishekam', 6008, 'Sponsor gold pot abhishekam', 0, 10),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 11, 'Silver Kalash Abhishekam', 3508, 'Sponsor silver pot abhishekam', 0, 11),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 5, 'Homa', 5008, 'Sponsor fire sacrifice ceremony', 0, 12),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 4, 'Panchamrta Abhishekam', 1008, 'Sponsor the five-nectar bathing ceremony', 0, 13),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 6, 'Food for Life', 1008, 'Sponsor meal distribution', 0, 14),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 9, 'Tulasi Ashtottara Seva', 508, 'Sponsor Tulasi worship', 0, 15),
((SELECT id FROM `donation_causes` WHERE slug='janmashtami'), 10, 'Pushpa Archana Seva', 258, 'Sponsor flower archana', 0, 16);

-- -------------------------------------------------------
-- I. OTHER GRAND FESTIVALS - tiered pricing
-- -------------------------------------------------------
-- Gaura Purnima (same pricing as Janmashtami)
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `is_featured`, `sort_order`)
SELECT (SELECT id FROM `donation_causes` WHERE slug='gaura-purnima'), `category_id`, `name`, `amount`, `description`, `is_featured`, `sort_order`
FROM `donation_cause_sevas` WHERE `cause_id` = (SELECT id FROM `donation_causes` WHERE slug='janmashtami');

-- Ratha Yatra
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `is_featured`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 12, 'Mukhya Yajaman Seva', 150008, 'Be the main sponsor of Ratha Yatra', 1, 1),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 13, 'Maha Prasad Seva', 125008, 'Sponsor grand prasadam', 0, 2),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 2, 'Deity Dress', 100008, 'Sponsor deity outfits', 0, 3),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 1, 'Flower Decoration', 75008, 'Sponsor flower decorations', 0, 4),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 7, 'Chariot Decoration', 75008, 'Sponsor chariot decorations', 0, 5),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 6, 'Annadhanam Seva', 35008, 'Sponsor food distribution', 0, 6),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 3, 'Rajbhog Seva', 15008, 'Sponsor Rajbhog offering', 0, 7),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 8, 'Deity Garland Seva', 10008, 'Sponsor garlands', 0, 8),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 12, 'Festival Donation', 7508, 'General festival donation', 0, 9),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 11, 'Gold Kalash', 5008, 'Gold Kalash abhishekam', 0, 10),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 11, 'Silver Kalash', 2508, 'Silver Kalash abhishekam', 0, 11),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 5, 'Homa', 3508, 'Fire sacrifice ceremony', 0, 12),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 4, 'Panchamrta Abhishekam', 751, 'Five-nectar bathing ceremony', 0, 13),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 6, 'Food for Life', 751, 'Meal distribution', 0, 14),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 9, 'Tulasi Ashtottara', 351, 'Tulasi worship', 0, 15),
((SELECT id FROM `donation_causes` WHERE slug='ratha-yatra'), 10, 'Pushpa Archana', 151, 'Flower archana', 0, 16);

-- Govardhan Puja
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `is_featured`, `sort_order`) VALUES
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 12, 'Mukhya Yajaman Seva', 125008, 'Be the main sponsor', 1, 1),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 13, 'Maha Prasad / Annakut Seva', 100008, 'Sponsor the Annakut offering', 0, 2),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 2, 'Deity Dress', 75008, 'Sponsor deity outfits', 0, 3),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 1, 'Flower Decoration', 50008, 'Sponsor flower decorations', 0, 4),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 7, 'Temple Decoration', 50008, 'Sponsor temple decorations', 0, 5),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 6, 'Annadhanam Seva', 25008, 'Sponsor food distribution', 0, 6),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 3, 'Rajbhog Seva', 10008, 'Rajbhog offering', 0, 7),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 8, 'Garland Seva', 7508, 'Flower garlands', 0, 8),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 12, 'Festival Donation', 5008, 'General donation', 0, 9),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 11, 'Gold Kalash', 3508, 'Gold Kalash', 0, 10),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 11, 'Silver Kalash', 1508, 'Silver Kalash', 0, 11),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 5, 'Homa', 2508, 'Fire sacrifice', 0, 12),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 4, 'Panchamrta Abhishekam', 501, 'Bathing ceremony', 0, 13),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 6, 'Food for Life', 501, 'Meals', 0, 14),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 9, 'Tulasi Ashtottara', 251, 'Tulasi worship', 0, 15),
((SELECT id FROM `donation_causes` WHERE slug='govardhan-puja'), 10, 'Pushpa Archana', 101, 'Flower archana', 0, 16);

-- Diwali (medium pricing, similar to Govardhan)
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `is_featured`, `sort_order`)
SELECT (SELECT id FROM `donation_causes` WHERE slug='diwali'), `category_id`, `name`, `amount`, `description`, `is_featured`, `sort_order`
FROM `donation_cause_sevas` WHERE `cause_id` = (SELECT id FROM `donation_causes` WHERE slug='govardhan-puja');

-- Other grand festivals (standard pricing)
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`) VALUES
-- Narasimha Chaturdashi
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 12, 'Mukhya Yajaman Seva', 51008, 'Main sponsor', 1),
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 1, 'Flower Decoration', 25008, 'Flower decorations', 2),
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 2, 'Deity Dress', 21008, 'Deity outfits', 3),
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 6, 'Annadhanam Seva', 15008, 'Food distribution', 4),
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 3, 'Rajbhog Seva', 5008, 'Rajbhog offering', 5),
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 4, 'Abhishekam', 1008, 'Bathing ceremony', 6),
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 10, 'Pushpa Archana', 508, 'Flower archana', 7),
((SELECT id FROM `donation_causes` WHERE slug='narasimha-chaturdashi'), 12, 'Festival Donation', 2508, 'General donation', 8),

-- Rama Navami
((SELECT id FROM `donation_causes` WHERE slug='rama-navami'), 12, 'Mukhya Yajaman Seva', 51008, 'Main sponsor', 1),
((SELECT id FROM `donation_causes` WHERE slug='rama-navami'), 1, 'Flower Decoration', 25008, 'Flower decorations', 2),
((SELECT id FROM `donation_causes` WHERE slug='rama-navami'), 2, 'Deity Dress', 21008, 'Deity outfits', 3),
((SELECT id FROM `donation_causes` WHERE slug='rama-navami'), 6, 'Annadhanam Seva', 15008, 'Food distribution', 4),
((SELECT id FROM `donation_causes` WHERE slug='rama-navami'), 3, 'Rajbhog Seva', 5008, 'Rajbhog offering', 5),
((SELECT id FROM `donation_causes` WHERE slug='rama-navami'), 4, 'Abhishekam', 1008, 'Bathing ceremony', 6),
((SELECT id FROM `donation_causes` WHERE slug='rama-navami'), 12, 'Festival Donation', 2508, 'General donation', 7),

-- Radhashtami
((SELECT id FROM `donation_causes` WHERE slug='radhashtami'), 1, 'Flower Decoration', 35008, 'Special flower decorations for Radharani', 1),
((SELECT id FROM `donation_causes` WHERE slug='radhashtami'), 2, 'Deity Dress', 25008, 'New outfit for Srimati Radharani', 2),
((SELECT id FROM `donation_causes` WHERE slug='radhashtami'), 8, 'Special Garland Seva', 15008, 'Special garlands for Radharani', 3),
((SELECT id FROM `donation_causes` WHERE slug='radhashtami'), 6, 'Annadhanam Seva', 15008, 'Food distribution', 4),
((SELECT id FROM `donation_causes` WHERE slug='radhashtami'), 3, 'Rajbhog Seva', 5008, 'Rajbhog offering', 5),
((SELECT id FROM `donation_causes` WHERE slug='radhashtami'), 12, 'Festival Donation', 2508, 'General donation', 6),

-- Gopastami
((SELECT id FROM `donation_causes` WHERE slug='gopastami'), 12, 'Gau Seva', 10008, 'Cow protection donation', 1),
((SELECT id FROM `donation_causes` WHERE slug='gopastami'), 1, 'Flower Decoration', 15008, 'Flower decorations', 2),
((SELECT id FROM `donation_causes` WHERE slug='gopastami'), 6, 'Annadhanam Seva', 10008, 'Food distribution', 3),
((SELECT id FROM `donation_causes` WHERE slug='gopastami'), 3, 'Rajbhog Seva', 5008, 'Rajbhog offering', 4),
((SELECT id FROM `donation_causes` WHERE slug='gopastami'), 12, 'Festival Donation', 2100, 'General donation', 5),

-- Gita Jayanti
((SELECT id FROM `donation_causes` WHERE slug='gita-jayanti'), 12, 'Shastra Daan Seva', 10008, 'Sponsor Gita distribution', 1),
((SELECT id FROM `donation_causes` WHERE slug='gita-jayanti'), 1, 'Flower Decoration', 15008, 'Flower decorations', 2),
((SELECT id FROM `donation_causes` WHERE slug='gita-jayanti'), 6, 'Annadhanam Seva', 10008, 'Food distribution', 3),
((SELECT id FROM `donation_causes` WHERE slug='gita-jayanti'), 12, 'Festival Donation', 2100, 'General donation', 4),

-- Akshaya Tritiya
((SELECT id FROM `donation_causes` WHERE slug='akshaya-tritiya'), 12, 'Akshaya Seva', 10008, 'Auspicious day donation', 1),
((SELECT id FROM `donation_causes` WHERE slug='akshaya-tritiya'), 2, 'Chandana Yatra Seva', 5008, 'Sandalwood paste offering', 2),
((SELECT id FROM `donation_causes` WHERE slug='akshaya-tritiya'), 6, 'Annadhanam Seva', 10008, 'Food distribution', 3),
((SELECT id FROM `donation_causes` WHERE slug='akshaya-tritiya'), 12, 'Festival Donation', 2100, 'General donation', 4);

-- Other festivals get standard minimal pricing
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 12, 'Festival Seva', 5008, 'Support this festival celebration', 1
FROM `donation_causes` c WHERE c.slug IN ('nandotsava','balarama-purnima','snana-yatra','jhulan-yatra',
  'nityananda-trayodashi','bahulastami','odana-sasthi','tulasi-shaligram-vivaha','panihati',
  'varaha-dwadashi','pushya-abhisheka','bhishma-panchaka','sri-sri-radha-ramana');

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 1, 'Flower Decoration', 5008, 'Sponsor festival flower decorations', 2
FROM `donation_causes` c WHERE c.slug IN ('nandotsava','balarama-purnima','snana-yatra','jhulan-yatra',
  'nityananda-trayodashi','bahulastami','odana-sasthi','tulasi-shaligram-vivaha','panihati',
  'varaha-dwadashi','pushya-abhisheka','bhishma-panchaka','sri-sri-radha-ramana');

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 6, 'Annadhanam Seva', 5008, 'Sponsor prasadam distribution', 3
FROM `donation_causes` c WHERE c.slug IN ('nandotsava','balarama-purnima','snana-yatra','jhulan-yatra',
  'nityananda-trayodashi','bahulastami','odana-sasthi','tulasi-shaligram-vivaha','panihati',
  'varaha-dwadashi','pushya-abhisheka','bhishma-panchaka','sri-sri-radha-ramana');

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 3, 'Rajbhog Seva', 2100, 'Sponsor Rajbhog offering', 4
FROM `donation_causes` c WHERE c.slug IN ('nandotsava','balarama-purnima','snana-yatra','jhulan-yatra',
  'nityananda-trayodashi','bahulastami','odana-sasthi','tulasi-shaligram-vivaha','panihati',
  'varaha-dwadashi','pushya-abhisheka','bhishma-panchaka','sri-sri-radha-ramana');

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 10, 'Pushpa Archana', 508, 'Sponsor flower archana', 5
FROM `donation_causes` c WHERE c.slug IN ('nandotsava','balarama-purnima','snana-yatra','jhulan-yatra',
  'nityananda-trayodashi','bahulastami','odana-sasthi','tulasi-shaligram-vivaha','panihati',
  'varaha-dwadashi','pushya-abhisheka','bhishma-panchaka','sri-sri-radha-ramana');

-- -------------------------------------------------------
-- J. APPEARANCE DAYS
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 10, 'Pushpa Archana Seva', 258, 'Flower archana offering', 1
FROM `donation_causes` c WHERE c.category = 'appearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 9, 'Tulasi Ashtottara Seva', 508, 'Tulasi worship', 2
FROM `donation_causes` c WHERE c.category = 'appearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 4, 'Abhishekam Seva', 1008, 'Bathing ceremony', 3
FROM `donation_causes` c WHERE c.category = 'appearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 3, 'Rajbhog Seva', 5008, 'Rajbhog offering', 4
FROM `donation_causes` c WHERE c.category = 'appearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 6, 'Annadhanam Seva', 10008, 'Food distribution', 5
FROM `donation_causes` c WHERE c.category = 'appearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 2, 'Deity Dress / Alankar', 25008, 'New deity outfit', 6
FROM `donation_causes` c WHERE c.category = 'appearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 1, 'Flower Decoration', 15008, 'Flower decorations', 7
FROM `donation_causes` c WHERE c.category = 'appearance';

-- -------------------------------------------------------
-- K. DISAPPEARANCE DAYS
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 10, 'Pushpa Archana Seva', 151, 'Flower archana offering', 1
FROM `donation_causes` c WHERE c.category = 'disappearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 9, 'Tulasi Ashtottara Seva', 351, 'Tulasi worship', 2
FROM `donation_causes` c WHERE c.category = 'disappearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 3, 'Rajbhog Seva', 2100, 'Rajbhog offering', 3
FROM `donation_causes` c WHERE c.category = 'disappearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 6, 'Annadhanam Seva', 5008, 'Food distribution', 4
FROM `donation_causes` c WHERE c.category = 'disappearance';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 12, 'Shraddha Seva', 2100, ' homage offering', 5
FROM `donation_causes` c WHERE c.category = 'disappearance';

-- -------------------------------------------------------
-- L. EKADASHI
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 3, 'Rajbhog Seva', 2008, 'Special Ekadashi Rajbhog', 1
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 6, 'Annadhanam Seva', 5008, 'Ekadashi food distribution', 2
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 1, 'Flower Decoration', 10008, 'Ekadashi flower decorations', 3
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 4, 'Panchamrta Abhishekam', 501, 'Bathing ceremony', 4
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 10, 'Pushpa Archana', 151, 'Flower archana', 5
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 9, 'Tulasi Ashtottara', 251, 'Tulasi worship', 6
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 12, 'Festival Donation', 1008, 'General Ekadashi donation', 7
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 8, 'Deity Garland', 1508, 'Fresh garlands', 8
FROM `donation_causes` c WHERE c.slug = 'ekadashi-general';

-- -------------------------------------------------------
-- M. EVENTS
-- -------------------------------------------------------
INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 12, 'Event Sponsorship', 10008, 'Support this event', 1
FROM `donation_causes` c WHERE c.category = 'event';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 1, 'Flower Decoration', 5008, 'Event flower decorations', 2
FROM `donation_causes` c WHERE c.category = 'event';

INSERT INTO `donation_cause_sevas` (`cause_id`, `category_id`, `name`, `amount`, `description`, `sort_order`)
SELECT c.id, 6, 'Annadhanam Seva', 5008, 'Food distribution', 3
FROM `donation_causes` c WHERE c.category = 'event';
