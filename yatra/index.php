<?php
$pageTitle = 'Pilgrimage Tours - Yatra';
include '../partials/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;

$all_yatras = [
  ['Panihati Chips & Dahi Utsav Yatra', 'panihati', 'June', '1 Day', 'fa-water', 'Celebrate the auspicious Panihati Cida-Dahi festival on the banks of Kaveri River at Dodda Gosai Ghat, Srirangapatna.', '../assets/images/banners/panihati-banner1.jpg'],
  ['TriRanga Darshan', 'triranga-darshan', 'January, December', '3 Days', 'fa-om', 'Visit the sacred Trilinga desham — a spiritually enriching journey through ancient temples and holy rivers.', '../assets/images/banners/triranga-darshan.jpg'],
  ['Bhadrachalam Yatra', 'bhadrachalam', 'January', '4 Days', 'fa-temple', 'Visit the divine abode of Lord Rama at Bhadrachalam, situated on the banks of the Godavari River.', '../assets/images/banners/bhadrachalam.jpg'],
  ['Jharkhand Yatra', 'jharkhand', 'January', '8 Days', 'fa-mountain', 'Explore the sacred hills and forests of Jharkhand, visiting the 17th-century Jagannath Temple at Ranchi, Chaitanya Mahaprabhu\'s lotus feet, and stunning waterfalls.', '../assets/images/banners/jharkhand.jpg'],
  ['Shri Pandharpur Yatra', 'pandharpur', 'January', '5 Days', 'fa-pray', 'Journey to Pandharpur, the abode of Lord Vitthala — a deeply devotional experience on the banks of the Chandrabhaga River.', '../assets/images/banners/pandharpur.jpg'],
  ['Chardham Yatra', 'chardham', 'June, October', '11 Days', 'fa-hiking', 'The ultimate pilgrimage to the four sacred abodes of Uttarakhand — Yamunotri, Gangotri, Kedarnath, and Badrinath.', '../assets/images/banners/chardham.png'],
  ['Mathura-Vrindavan Kartik Yatra', 'mathura-vrindavan', 'November', '10 Days', 'fa-tree', 'Experience the divine land of Lord Krishna\'s pastimes during the auspicious Kartik month in Braj.', '../assets/images/banners/mathura-vrindavan.jpg'],
  ['Angkor Wat Yatra', 'angkor-wat', 'February, December', '8 Days', 'fa-globe-asia', 'Journey to the magnificent Angkor Wat temple complex in Cambodia — a sacred heritage pilgrimage.', '../assets/images/banners/angkor-wat.jpg'],
  ['Sri Jagannath Puri Dham Yatra', 'jagannath-puri', 'February, December', '9/11 Days', 'fa-dharmachakra', 'Visit the abode of Lord Jagannath at Puri, one of the four sacred Chardham destinations.', '../assets/images/banners/jagannath-puri.png'],
  ['Ahobilam Yatra', 'ahobilam', '2nd Week Every Month', '3 Days', 'fa-mountain', 'Visit the sacred Nava Narasimha temples of Ahobilam, nestled in the hills of Andhra Pradesh.', '../assets/images/banners/ahobilam.jpg'],
  ['Sri Lanka Ramayana Yatra', 'sri-lanka-ramayana', 'February', '8 Days', 'fa-map-marked-alt', 'Trace the footsteps of Lord Rama through the sacred Ramayana sites of Sri Lanka.', '../assets/images/banners/sri-lanka-ramayana.jpeg'],
  ['Kumbh Mela', 'kumbh-mela', 'February', '3 Days', 'fa-water', 'Participate in the world\'s largest spiritual gathering — the holy Kumbh Mela.', '../assets/images/banners/kumbh-mela.jpg'],
  ['Karavali (Coastal) Tirtha Yatra', 'karavali-coastal', 'February', '4 Days', 'fa-umbrella-beach', 'Explore the sacred coastal pilgrimage sites of Karnataka\'s Karavali region.', '../assets/images/banners/karavali-coastal.jpg'],
  ['Jagannath Puri, Mayapur & Gangasagar Yatra', 'puri-mayapur-gangasagar', 'February', '8/10 Days', 'fa-map-signs', 'A combined pilgrimage to Jagannath Puri, ISKCON Mayapur, and the holy Gangasagar.', '../assets/images/banners/puri-mayapur-gangasagar.jpg'],
  ['Ayodhya Kashi Yatra', 'ayodhya-kashi', 'March, September', '11 Days', 'fa-city', 'Visit the birthplace of Lord Rama in Ayodhya and the sacred city of Kashi (Varanasi).', '../assets/images/banners/ayodhya-kashi.jpg'],
  ['Vaishno Devi Kurukshetra Yatra', 'vaishno-devi-kurukshetra', 'March', '10/14 Days', 'fa-mountain', 'Journey to the holy cave shrine of Mata Vaishno Devi and the sacred battlefield of Kurukshetra.', '../assets/images/banners/vaishno-devi-kurukshetra.jpg'],
  ['Rameswaram & Madurai Divya Desham Yatra', 'rameswaram-madurai', 'March', '3 Days/ 2 Nights', 'fa-temple', 'Visit the sacred Ramanathaswamy Temple in Rameswaram and the divine Meenakshi Temple in Madurai.', '../assets/images/banners/rameswaram-madurai.jpg'],
  ['Narmada Pushkaralu Snana Yatra', 'narmada-pushkaralu', 'May', '9 Days', 'fa-water', 'Take a holy dip in the sacred Narmada River during the auspicious Pushkaralu festival.', '../assets/images/banners/narmada-pushkaralu.jpg'],
  ['Rameswaram Yatra', 'rameswaram', 'July', '3 Days', 'fa-water', 'Visit the sacred island town of Rameswaram, where Lord Rama built the bridge to Lanka.', '../assets/images/banners/rameswaram.png'],
  ['Temples Tour of Dwarka and Rajasthan', 'dwarka-rajasthan', 'August', '13 Days', 'fa-landmark', 'Explore the kingdom of Lord Krishna in Dwarka and the majestic temples of Rajasthan.', '../assets/images/banners/dwarka-rajasthan.jpg'],
  ['Divine Nepal Yatra', 'divine-nepal', 'September', '11 Days', 'fa-mountain', 'Visit the sacred Hindu and Buddhist pilgrimage sites of Nepal, including Pashupatinath and Lumbini.', '../assets/images/banners/divine-nepal.jpg'],
];

$total_yatras = count($all_yatras);
$total_pages = ceil($total_yatras / $per_page);
$page = max(1, min($page, max(1, $total_pages)));
$offset = ($page - 1) * $per_page;
$yatras = array_slice($all_yatras, $offset, $per_page);
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/banner7.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Pilgrimage Tours</h1>
    <div class="breadcrumb reveal"><a href="<?php echo BASE_URL; ?>">Home</a><span>›</span><span>Yatra</span></div>
  </div>
</section>

<section class="page-content" style="background:var(--cream-light);">
  <div class="container" style="max-width:1000px;">

    <!-- Banner Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);">
      <img src="<?php echo BASE_URL; ?>assets/images/banners/banner7.jpg" alt="Pilgrimage Tours" style="width:100%;display:block;max-height:500px;object-fit:cover;">
    </div>

    <div class="reveal" style="text-align:center;margin-bottom:var(--space-3xl);">
      <div class="section-divider"><span class="divider-icon">🚩</span></div>
      <h2 style="margin-bottom:var(--space-md);">Sacred Pilgrimage Journeys</h2>
      <p style="color:var(--text-light);max-width:700px;margin:0 auto;line-height:1.8;">Experience divine destinations through our curated yatra packages. Each pilgrimage includes holy baths, sadhu sanga, kirtan, katha, prasadam, travel, and comfortable stay.</p>
    </div>

    <!-- Yatra Cards Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:var(--space-xl);">
      <?php foreach ($yatras as $y): ?>
      <a href="yatra/<?php echo $y[1]; ?>" class="reveal" style="text-decoration:none;color:inherit;display:block;">
        <div style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);transition:all var(--transition-base);" onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-4px)'" onmouseout="this.style.boxShadow='var(--shadow-sm)';this.style.transform='translateY(0)'">
          <div style="height:180px;background-size:cover;background-position:center;<?php echo $y[6] ? 'background-image:url('.$y[6].');position:relative;' : 'background:var(--gradient-primary);'; ?>display:flex;align-items:center;justify-content:center;font-size:56px;color:rgba(255,255,255,0.25);">
            <?php if ($y[6]): ?><div style="position:absolute;inset:0;background:linear-gradient(135deg, rgba(44,27,18,0.5) 0%, rgba(123,30,30,0.3) 100%);"></div><?php endif; ?>
            <i class="fas <?php echo $y[4]; ?>" style="position:relative;z-index:1;<?php echo $y[6] ? 'opacity:0.8;' : ''; ?>"></i>
          </div>
          <div style="padding:var(--space-lg);">
            <h3 style="font-size:var(--font-size-lg);margin-bottom:var(--space-sm);color:var(--text-dark);"><?php echo $y[0]; ?></h3>
            <div style="display:flex;gap:var(--space-md);margin-bottom:var(--space-md);flex-wrap:wrap;">
              <span style="background:var(--cream);padding:4px 10px;border-radius:var(--radius-md);font-size:var(--font-size-xs);"><i class="far fa-clock" style="color:var(--primary);margin-right:4px;"></i><?php echo $y[3]; ?></span>
            </div>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;margin:0;"><?php echo $y[5]; ?></p>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Pagination: First Prev 1 2 3 4 Next Last -->
    <?php if ($total_pages > 1): ?>
    <?php
    $pager_link = function($label, $pg, $is_active = false, $is_disabled = false) use ($page, $total_pages) {
      $href = $is_disabled ? '#' : 'yatra/?page=' . $pg;
      $tag = $is_disabled ? 'span' : 'a';
      $extra = $is_disabled ? '' : 'href="' . $href . '"';
      $bg = $is_active ? 'var(--gradient-primary)' : ($is_disabled ? 'var(--cream)' : 'var(--white)');
      $color = $is_active ? 'var(--white)' : ($is_disabled ? 'var(--text-light)' : 'var(--text-dark)');
      $border = $is_active ? 'transparent' : 'var(--border)';
      $cursor = $is_disabled ? 'default' : 'pointer';
      $hover = $is_disabled ? '' : 'this.style.borderColor="var(--primary)";this.style.color="var(--primary)"';
      $hout = $is_disabled ? '' : 'this.style.borderColor="' . $border . '";this.style.color="' . $color . '"';
      echo '<' . $tag . ' ' . $extra . ' style="padding:8px 14px;background:' . $bg . ';color:' . $color . ';border:1px solid ' . $border . ';border-radius:var(--radius-md);font-size:var(--font-size-sm);text-decoration:none;font-weight:' . ($is_active ? '600' : '400') . ';cursor:' . $cursor . ';transition:all var(--transition-fast);"';
      if (!$is_disabled && !$is_active) {
        echo ' onmouseover="' . $hover . '" onmouseout="' . $hout . '"';
      }
      echo '>' . $label . '</' . $tag . '>';
    };
    ?>
    <div class="reveal" style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:var(--space-2xl);flex-wrap:wrap;">
      <?php
      $pager_link('First', 1, false, $page === 1);
      $pager_link('<i class="fas fa-chevron-left" style="font-size:12px;"></i> Prev', $page - 1, false, $page === 1);
      for ($i = 1; $i <= $total_pages; $i++) {
        $pager_link($i, $i, $i === $page);
      }
      $pager_link('Next <i class="fas fa-chevron-right" style="font-size:12px;"></i>', $page + 1, false, $page === $total_pages);
      $pager_link('Last', $total_pages, false, $page === $total_pages);
      ?>
    </div>
    <?php endif; ?>

    <!-- Yatra Includes -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);text-align:center;">
      <h3 style="margin-bottom:var(--space-lg);">Every Yatra Includes</h3>
      <div style="display:flex;justify-content:center;gap:var(--space-xl);flex-wrap:wrap;">
        <?php
        $includes = [
          ['fa-bath', 'Holy Bath'],
          ['fa-users', 'Sadhu Sanga'],
          ['fa-music', 'Kirtan'],
          ['fa-book-open', 'Katha'],
          ['fa-utensils', 'Prasadam'],
          ['fa-bus', 'Travel'],
          ['fa-hotel', 'Stay'],
        ];
        foreach ($includes as $i):
        ?>
        <div style="display:flex;align-items:center;gap:var(--space-sm);background:var(--white);padding:var(--space-sm) var(--space-md);border-radius:var(--radius-md);">
          <i class="fas <?php echo $i[0]; ?>" style="color:var(--primary);"></i>
          <span style="font-size:var(--font-size-sm);font-weight:500;"><?php echo $i[1]; ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Contact CTA -->
    <div class="reveal" style="margin-top:var(--space-3xl);text-align:center;background:var(--gradient-cta);padding:var(--space-2xl);border-radius:var(--radius-lg);">
      <h3 style="color:var(--white);margin-bottom:var(--space-lg);">Interested in a Pilgrimage?</h3>
      <p style="color:rgba(255,255,255,0.85);max-width:550px;margin:0 auto var(--space-xl);line-height:1.8;">
        For bookings, schedules, and inquiries about upcoming yatras, please get in touch with us.
      </p>
      <a href="../contact" class="btn btn-accent btn-lg"><i class="fas fa-route"></i> Enquire About Yatra</a>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
