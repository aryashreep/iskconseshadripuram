<?php
$pageTitle = 'Vaishnava Calendar 2026';
include '../../partials/header.php';

// Slug mapping: event name fragment → detail page path
$slug_map = [
  'Pushya Abhisheka'      => 'festivals/grand-festivals/pushya-abhisheka',
  'Vasanta Panchami'      => '',
  'Advaita Acharya'       => 'festivals/appearance/sri-advaita-acharya',
  'Varaha Dwadasi'        => 'festivals/grand-festivals/varaha-dwadashi',
  'Nityananda Trayodasi'  => 'festivals/grand-festivals/nityananda-trayodashi',
  'Bhaktisiddhanta Saraswati Thakura -- Appearance' => 'festivals/appearance/srila-bhaktisiddhanta-sarasvati-thakura',
  'Gaura Purnima'         => 'festivals/grand-festivals/gaura-purnima',
  'Srivasa Pandita'       => '',
  'Rama Navami'           => 'festivals/grand-festivals/rama-navami',
  'Gadadhara Pandita'     => '',
  'Akshaya Trtiya'        => 'festivals/grand-festivals/akshaya-tritiya',
  'Narasimha Caturdasi'   => 'festivals/grand-festivals/narasimha-chaturdashi',
  'Panihati'              => 'festivals/grand-festivals/panihati',
  'Snana Yatra'           => 'festivals/grand-festivals/snana-yatra',
  'Bhaktivinoda Thakura -- Disappearance' => 'festivals/disappearance/srila-bhaktivinoda-thakura',
  'Ratha Yatra'           => 'festivals/grand-festivals/ratha-yatra',
  'Balarama Appearance'   => 'festivals/grand-festivals/balarama-purnima',
  'Janmastami'            => 'festivals/grand-festivals/janmashtami',
  'Nandotsava'            => 'festivals/grand-festivals/nandotsava',
  'Srila Prabhupada -- Appearance' => 'festivals/appearance/srila-prabhupada',
  'Radhashtami'           => 'festivals/grand-festivals/radhashtami',
  'Vamana Dwadasi'        => 'festivals/grand-festivals/lord-vamanadeva',
  'Bhaktivinoda Thakura -- Appearance' => 'festivals/appearance/srila-bhaktivinoda-thakura',
  'Deepavali'             => 'festivals/grand-festivals/diwali',
  'Govardhana Puja'       => 'festivals/grand-festivals/govardhan-puja',
  'Govardhan Puja'        => 'festivals/grand-festivals/govardhan-puja',
  'Srila Prabhupada -- Disappearance' => 'festivals/disappearance/srila-prabhupada',
  'Gopastami'             => 'festivals/grand-festivals/gopastami',
  'Tulasi-Saligrama Vivaha' => 'festivals/grand-festivals/tulasi-shaligram-vivaha',
  'Bhaktisiddhanta Saraswati Thakura -- Disappearance' => 'festivals/disappearance/srila-bhaktisiddhanta-sarasvati-thakura',
  'Bahulashtami'          => 'festivals/grand-festivals/bahulastami',
];

// Ekadashi date → slug mapping (2026 dates)
$ekadashi_map = [
  '2026-01-14' => 'saphala',     '2026-01-29' => 'putrada',
  '2026-02-13' => 'sattila',     '2026-02-27' => 'bhaimi',
  '2026-03-15' => 'vijaya',      '2026-03-29' => 'amalaki',
  '2026-04-13' => 'papamocani',  '2026-04-27' => 'kamada',
  '2026-05-13' => 'varuthini',   '2026-05-27' => 'mohini',
  '2026-06-11' => 'apara',       '2026-06-25' => 'nirjala',
  '2026-07-11' => 'yogini',      '2026-07-25' => 'sayana',
  '2026-08-09' => 'kamika',      '2026-08-24' => 'pavitropana',
  '2026-09-07' => 'annada',      '2026-09-22' => 'parivartini',
  '2026-10-06' => 'indira',      '2026-10-22' => 'papankusa',
  '2026-11-05' => 'rama',        '2026-11-21' => 'utthana',
  '2026-12-04' => 'utpanna',     '2026-12-20' => 'moksada',
];

// Resolve link for an event
function resolveLink($event, $slug_map, $ekadashi_map) {
  $name = $event['name'];
  $date = $event['date'];
  $type = $event['type'];

  // Ekadashi: look up by date
  if ($type === 'ekadashi') {
    if (isset($ekadashi_map[$date])) {
      return 'festivals/ekadashi/' . $ekadashi_map[$date];
    }
    return 'festivals/ekadashi';
  }

  // Other types: match by name fragment
  foreach ($slug_map as $fragment => $link) {
    if (stripos($name, $fragment) !== false && $link !== '') {
      return $link;
    }
  }
  return '';
}

// Complete list of Vaishnava Calendar Events for 2026 (Festivals & Ekadashis)
$calendar_events = [
    // January
    ['date' => '2026-01-04', 'day' => '04 Jan (Sun)', 'name' => 'Sri Krishna Pushya Abhisheka', 'type' => 'festival', 'month' => 'January'],
    ['date' => '2026-01-14', 'day' => '14 Jan (Wed)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'January'],
    ['date' => '2026-01-23', 'day' => '23 Jan (Fri)', 'name' => 'Vasanta Panchami', 'type' => 'festival', 'month' => 'January'],
    ['date' => '2026-01-25', 'day' => '25 Jan (Sun)', 'name' => 'Sri Advaita Acharya -- Appearance', 'type' => 'appearance', 'month' => 'January'],
    ['date' => '2026-01-29', 'day' => '29 Jan (Thu)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'January'],
    ['date' => '2026-01-30', 'day' => '30 Jan (Fri)', 'name' => 'Varaha Dwadasi: Appearance of Lord Varahadeva', 'type' => 'appearance', 'month' => 'January'],
    ['date' => '2026-01-31', 'day' => '31 Jan (Sat)', 'name' => 'Nityananda Trayodasi -- Appearance of Nityananda Prabhu', 'type' => 'appearance', 'month' => 'January'],

    // February
    ['date' => '2026-02-06', 'day' => '06 Feb (Fri)', 'name' => 'Srila Bhaktisiddhanta Saraswati Thakura -- Appearance', 'type' => 'appearance', 'month' => 'February'],
    ['date' => '2026-02-13', 'day' => '13 Feb (Fri)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'February'],
    ['date' => '2026-02-27', 'day' => '27 Feb (Fri)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'February'],

    // March
    ['date' => '2026-03-03', 'day' => '03 Mar (Tue)', 'name' => 'Gaura Purnima: Appearance of Sri Caitanya Mahaprabhu', 'type' => 'festival', 'month' => 'March'],
    ['date' => '2026-03-11', 'day' => '11 Mar (Wed)', 'name' => 'Sri Srivasa Pandita -- Appearance', 'type' => 'appearance', 'month' => 'March'],
    ['date' => '2026-03-15', 'day' => '15 Mar (Sun)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'March'],
    ['date' => '2026-03-27', 'day' => '27 Mar (Fri)', 'name' => 'Rama Navami: Appearance of Lord Sri Ramachandra', 'type' => 'appearance', 'month' => 'March'],
    ['date' => '2026-03-29', 'day' => '29 Mar (Sun)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'March'],

    // April
    ['date' => '2026-04-13', 'day' => '13 Apr (Mon)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'April'],
    ['date' => '2026-04-17', 'day' => '17 Apr (Fri)', 'name' => 'Sri Gadadhara Pandita -- Appearance', 'type' => 'appearance', 'month' => 'April'],
    ['date' => '2026-04-20', 'day' => '20 Apr (Mon)', 'name' => 'Akshaya Trtiya (Chandan Yatra starts)', 'type' => 'festival', 'month' => 'April'],
    ['date' => '2026-04-27', 'day' => '27 Apr (Mon)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'April'],
    ['date' => '2026-04-30', 'day' => '30 Apr (Thu)', 'name' => 'Narasimha Caturdasi: Appearance of Lord Narasimhadev', 'type' => 'appearance', 'month' => 'April'],

    // May
    ['date' => '2026-05-13', 'day' => '13 May (Wed)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'May'],
    ['date' => '2026-05-27', 'day' => '27 May (Wed)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'May'],

    // June
    ['date' => '2026-06-11', 'day' => '11 Jun (Thu)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'June'],
    ['date' => '2026-06-25', 'day' => '25 Jun (Thu)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'June'],
    ['date' => '2026-06-28', 'day' => '28 Jun (Sun)', 'name' => 'Panihati Chida Dahi Utsava', 'type' => 'festival', 'month' => 'June'],

    // July
    ['date' => '2026-07-04', 'day' => '04 Jul (Sat)', 'name' => 'Snana Yatra', 'type' => 'festival', 'month' => 'July'],
    ['date' => '2026-07-11', 'day' => '11 Jul (Sat)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'July'],
    ['date' => '2026-07-14', 'day' => '14 Jul (Tue)', 'name' => 'Srila Bhaktivinoda Thakura -- Disappearance', 'type' => 'disappearance', 'month' => 'July'],
    ['date' => '2026-07-18', 'day' => '18 Jul (Sat)', 'name' => 'Ratha Yatra', 'type' => 'festival', 'month' => 'July'],
    ['date' => '2026-07-25', 'day' => '25 Jul (Sat)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'July'],
    ['date' => '2026-07-29', 'day' => '29 Jul (Wed)', 'name' => 'First month of Caturmasya begins', 'type' => 'festival', 'month' => 'July'],

    // August
    ['date' => '2026-08-09', 'day' => '09 Aug (Sun)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'August'],
    ['date' => '2026-08-23', 'day' => '23 Aug (Sun)', 'name' => 'Jhulana Yatra begins', 'type' => 'festival', 'month' => 'August'],
    ['date' => '2026-08-24', 'day' => '24 Aug (Mon)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'August'],
    ['date' => '2026-08-27', 'day' => '27 Aug (Thu)', 'name' => 'Last day of the first Caturmasya month', 'type' => 'festival', 'month' => 'August'],
    ['date' => '2026-08-28', 'day' => '28 Aug (Fri)', 'name' => 'Lord Balarama Appearance Day - Second month of Caturmasya begins', 'type' => 'appearance', 'month' => 'August'],

    // September
    ['date' => '2026-09-04', 'day' => '04 Sep (Fri)', 'name' => 'Sri Krishna Janmastami: Appearance of Lord Sri Krishna', 'type' => 'festival', 'month' => 'September'],
    ['date' => '2026-09-05', 'day' => '05 Sep (Sat)', 'name' => 'Nandotsava & Srila Prabhupada -- Appearance', 'type' => 'appearance', 'month' => 'September'],
    ['date' => '2026-09-07', 'day' => '07 Sep (Mon)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'September'],
    ['date' => '2026-09-19', 'day' => '19 Sep (Sat)', 'name' => 'Radhashtami: Appearance of Srimati Radharani', 'type' => 'appearance', 'month' => 'September'],
    ['date' => '2026-09-22', 'day' => '22 Sep (Tue)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'September'],
    ['date' => '2026-09-23', 'day' => '23 Sep (Wed)', 'name' => 'Sri Vamana Dwadasi: Appearance of Lord Vamanadeva', 'type' => 'appearance', 'month' => 'September'],
    ['date' => '2026-09-24', 'day' => '24 Sep (Thu)', 'name' => 'Srila Bhaktivinoda Thakura -- Appearance', 'type' => 'appearance', 'month' => 'September'],
    ['date' => '2026-09-26', 'day' => '26 Sep (Sat)', 'name' => 'Bhadra Purnima - Third month of Caturmasya begins', 'type' => 'festival', 'month' => 'September'],

    // October
    ['date' => '2026-10-06', 'day' => '06 Oct (Tue)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'October'],
    ['date' => '2026-10-21', 'day' => '21 Oct (Wed)', 'name' => 'Ramachandra Vijayotsava', 'type' => 'festival', 'month' => 'October'],
    ['date' => '2026-10-22', 'day' => '22 Oct (Thu)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'October'],
    ['date' => '2026-10-25', 'day' => '25 Oct (Sun)', 'name' => 'Last day of the third Caturmasya month', 'type' => 'festival', 'month' => 'October'],
    ['date' => '2026-10-26', 'day' => '26 Oct (Mon)', 'name' => 'Fourth month of Caturmasya begins', 'type' => 'festival', 'month' => 'October'],

    // November
    ['date' => '2026-11-02', 'day' => '02 Nov (Mon)', 'name' => 'Bahulashtami', 'type' => 'festival', 'month' => 'November'],
    ['date' => '2026-11-05', 'day' => '05 Nov (Thu)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'November'],
    ['date' => '2026-11-09', 'day' => '09 Nov (Mon)', 'name' => 'Deepavali', 'type' => 'festival', 'month' => 'November'],
    ['date' => '2026-11-10', 'day' => '10 Nov (Tue)', 'name' => 'Go Puja, Govardhana Puja', 'type' => 'festival', 'month' => 'November'],
    ['date' => '2026-11-13', 'day' => '13 Nov (Fri)', 'name' => 'Srila Prabhupada -- Disappearance', 'type' => 'disappearance', 'month' => 'November'],
    ['date' => '2026-11-17', 'day' => '17 Nov (Tue)', 'name' => 'Gopastami, Sri Srinivasa Acarya -- Disappearance', 'type' => 'disappearance', 'month' => 'November'],
    ['date' => '2026-11-21', 'day' => '21 Nov (Sat)', 'name' => 'Fasting for Utthana Ekadasi - First day of Bhisma Panchaka', 'type' => 'ekadashi', 'month' => 'November'],
    ['date' => '2026-11-23', 'day' => '23 Nov (Mon)', 'name' => 'Last day of the fourth Caturmasya month', 'type' => 'festival', 'month' => 'November'],
    ['date' => '2026-11-24', 'day' => '24 Nov (Tue)', 'name' => 'Tulasi-Saligrama Vivaha, Last day of Bhisma Pancaka', 'type' => 'festival', 'month' => 'November'],

    // December
    ['date' => '2026-12-04', 'day' => '04 Dec (Fri)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'December'],
    ['date' => '2026-12-20', 'day' => '20 Dec (Sun)', 'name' => 'Ekadashi (Fasting)', 'type' => 'ekadashi', 'month' => 'December'],
    ['date' => '2026-12-27', 'day' => '27 Dec (Sun)', 'name' => 'Srila Bhaktisiddhanta Saraswati Thakura -- Disappearance', 'type' => 'disappearance', 'month' => 'December'],
];

// Group by month
$months_grouped = [];
foreach ($calendar_events as $event) {
  $months_grouped[$event['month']][] = $event;
}
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('../../assets/images/banners/caitanya-appearance-banner.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Vaishnava Calendar 2026</h1>
    <div class="breadcrumb reveal">
      <a href="/isjm/">Home</a><span>›</span><a href="festivals/">Festivals</a><span>›</span><span>Calendar</span>
    </div>
  </div>
</section>

<!-- Content Area -->
<section class="page-content">
  <div class="container">
    
    <!-- Title & Intro -->
    <div class="reveal" style="text-align:center;margin-bottom:var(--space-xl);">
      <div class="section-divider"><span class="divider-icon">📅</span></div>
      <h2 style="margin-bottom:var(--space-md);">Vedic Lunar Calendar & Festivals</h2>
      <p style="color:var(--text-light);max-width:700px;margin:0 auto;line-height:1.8;">
        Here is the consolidated Vaishnava Calendar for the year 2026 listing all festivals, appearance/disappearance days of acharyas, and fasting days for Ekadashis.
      </p>
    </div>

    <!-- Filter Buttons & Quick Jump -->
    <div class="reveal" style="display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:12px;margin-bottom:var(--space-2xl);">
      <button class="btn btn-outline-dark btn-sm active" onclick="filterCalendar('all', this)">Show All</button>
      <button class="btn btn-outline-dark btn-sm" onclick="filterCalendar('ekadashi', this)"><i class="fas fa-circle" style="color:#d4af37;margin-right:6px;"></i>Ekadashi</button>
      <button class="btn btn-outline-dark btn-sm" onclick="filterCalendar('festival', this)"><i class="fas fa-circle" style="color:var(--accent);margin-right:6px;"></i>Festivals</button>
      <button class="btn btn-outline-dark btn-sm" onclick="filterCalendar('appearance', this)"><i class="fas fa-circle" style="color:var(--primary);margin-right:6px;"></i>Appearance Days</button>
      <button class="btn btn-outline-dark btn-sm" onclick="filterCalendar('disappearance', this)"><i class="fas fa-circle" style="color:#4a5568;margin-right:6px;"></i>Disappearance Days</button>
    </div>

    <!-- Month Navigation Quick Jump -->
    <div class="reveal" style="background:var(--cream);border-radius:var(--radius-md);padding:12px;margin-bottom:var(--space-2xl);display:flex;justify-content:center;gap:8px;flex-wrap:wrap;box-shadow:var(--shadow-xs);">
      <?php foreach (array_keys($months_grouped) as $m_name): ?>
        <a href="festivals/vaishnava-calendar#month-<?php echo strtolower($m_name); ?>" style="text-decoration:none;color:var(--text-dark);font-size:var(--font-size-xs);font-weight:600;padding:6px 12px;border-radius:var(--radius-sm);transition:all var(--transition-fast);" onmouseover="this.style.background='var(--primary)';this.style.color='var(--white)'" onmouseout="this.style.background='transparent';this.style.color='var(--text-dark)'">
          <?php echo substr($m_name, 0, 3); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Monthly Listings -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(310px, 1fr));gap:var(--space-xl);margin-bottom:var(--space-3xl);">
      <?php foreach ($months_grouped as $m_name => $events): ?>
        <div id="month-<?php echo strtolower($m_name); ?>" class="reveal month-card" style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--border);overflow:hidden;display:flex;flex-direction:column;transition:all var(--transition-base);">
          <div style="background:var(--gradient-primary);color:var(--white);padding:var(--space-md);font-family:var(--font-heading);font-size:var(--font-size-lg);text-align:center;font-weight:600;">
            <?php echo $m_name; ?> 2026
          </div>
          <div style="padding:var(--space-md);display:flex;flex-direction:column;gap:12px;flex-grow:1;">
            <?php foreach ($events as $e):
              $badge_bg = '';
              $badge_color = '';
              if ($e['type'] === 'ekadashi') {
                $badge_bg = '#fef08a';
                $badge_color = '#854d0e';
              } else if ($e['type'] === 'festival') {
                $badge_bg = '#ffedd5';
                $badge_color = '#c2410c';
              } else if ($e['type'] === 'appearance') {
                $badge_bg = '#e0f2fe';
                $badge_color = '#0369a1';
              } else if ($e['type'] === 'disappearance') {
                $badge_bg = '#f1f5f9';
                $badge_color = '#475569';
              }
              $eventLink = resolveLink($e, $slug_map, $ekadashi_map);
            ?>
              <div class="calendar-item" data-type="<?php echo $e['type']; ?>" style="border-bottom:1px solid var(--border-light);padding-bottom:10px;display:flex;flex-direction:column;gap:4px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                  <span style="font-weight:600;font-size:var(--font-size-sm);color:var(--primary);"><?php echo $e['day']; ?></span>
                  <span style="background:<?php echo $badge_bg; ?>;color:<?php echo $badge_color; ?>;padding:2px 8px;border-radius:var(--radius-sm);font-size:10px;font-weight:600;text-transform:uppercase;">
                    <?php echo $e['type']; ?>
                  </span>
                </div>
                <div style="font-size:var(--font-size-sm);color:var(--text-dark);line-height:1.4;font-weight:500;">
                  <?php if ($eventLink): ?>
                    <a href="<?php echo BASE_URL . $eventLink; ?>" style="color:var(--text-dark);text-decoration:none;border-bottom:1px dashed var(--primary-light);transition:all var(--transition-fast);" onmouseover="this.style.color='var(--primary)';this.style.borderBottomColor='var(--primary)'" onmouseout="this.style.color='var(--text-dark)';this.style.borderBottomColor='var(--primary-light)'"><?php echo $e['name']; ?></a>
                  <?php else: ?>
                    <?php echo $e['name']; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Official Calendar Posters Lightbox / Download Section -->
    <div class="reveal" style="background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);box-shadow:var(--shadow-sm);margin-bottom:var(--space-2xl);border:1px solid var(--border);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);color:var(--text-dark);display:flex;align-items:center;justify-content:center;gap:10px;">
        <i class="fas fa-image" style="color:var(--primary);"></i> Official Calendar Posters
      </h3>
      <p style="text-align:center;color:var(--text-light);max-width:600px;margin:0 auto var(--space-xl) auto;line-height:1.7;">
        You can view and save the official ISKCON Seshadripuram 2026 calendar posters containing the full monthly grid and contacts.
      </p>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-xl);max-width:800px;margin:0 auto;">
        
        <div style="background:var(--white);padding:var(--space-md);border-radius:var(--radius-md);box-shadow:var(--shadow-xs);text-align:center;">
          <h4 style="margin-top:0;margin-bottom:var(--space-md);">Semester 1 (Jan – Jun)</h4>
          <a href="../../assets/images/banners/2026-1.jpeg" target="_blank" style="display:block;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;margin-bottom:var(--space-md);">
            <img src="../../assets/images/banners/2026-1.jpeg" alt="Vaishnava Calendar Jan - Jun 2026" style="width:100%;height:auto;display:block;">
          </a>
          <a href="../../assets/images/banners/2026-1.jpeg" download="Vaishnava_Calendar_2026_Part1.jpeg" class="btn btn-accent btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
            <i class="fas fa-download"></i> Save Image
          </a>
        </div>

        <div style="background:var(--white);padding:var(--space-md);border-radius:var(--radius-md);box-shadow:var(--shadow-xs);text-align:center;">
          <h4 style="margin-top:0;margin-bottom:var(--space-md);">Semester 2 (Jul – Dec)</h4>
          <a href="../../assets/images/banners/2026-2.jpeg" target="_blank" style="display:block;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;margin-bottom:var(--space-md);">
            <img src="../../assets/images/banners/2026-2.jpeg" alt="Vaishnava Calendar Jul - Dec 2026" style="width:100%;height:auto;display:block;">
          </a>
          <a href="../../assets/images/banners/2026-2.jpeg" download="Vaishnava_Calendar_2026_Part2.jpeg" class="btn btn-accent btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
            <i class="fas fa-download"></i> Save Image
          </a>
        </div>

      </div>
    </div>

  </div>
</section>

<!-- Filter Script -->
<script>
function filterCalendar(type, btn) {
  // Update button active state
  var buttons = btn.parentNode.getElementsByTagName('button');
  for (var i = 0; i < buttons.length; i++) {
    buttons[i].classList.remove('active');
  }
  btn.classList.add('active');

  // Filter items
  var items = document.querySelectorAll('.calendar-item');
  items.forEach(function(item) {
    if (type === 'all' || item.getAttribute('data-type') === type) {
      item.style.display = 'flex';
    } else {
      item.style.display = 'none';
    }
  });

  // Hide or show empty month cards
  var cards = document.querySelectorAll('.month-card');
  cards.forEach(function(card) {
    var visibleItems = card.querySelectorAll('.calendar-item[style="display: flex;"]');
    if (type !== 'all' && visibleItems.length === 0) {
      card.style.opacity = '0.4';
    } else {
      card.style.opacity = '1';
    }
  });
}
</script>

<style>
/* Smooth filter buttons */
.btn-outline-dark.active {
  background: var(--primary) !important;
  color: var(--white) !important;
  border-color: var(--primary) !important;
}
.calendar-item:last-child {
  border-bottom: none;
}
</style>

<?php include '../../partials/footer.php'; ?>
