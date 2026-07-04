<?php
$pageTitle = 'Bhishma Panchaka – The Last Five Days of Kartika';
include '../../partials/header.php';
require_once '../../config.php';
?>

<!-- Custom Page Header with Hero Banner -->
<section class="page-header" style="position:relative; overflow:hidden; padding: var(--space-4xl) 0; text-align:center;">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/bhishma-panchaka.jpg'); background-size: cover; background-position: center; position: absolute; inset:0; z-index:-1; filter: brightness(0.4) contrast(1.1);"></div>
  <div class="container" style="position:relative; z-index:1;">
    <span style="display:inline-block; background:rgba(200, 107, 31, 0.2); border:1px solid var(--primary); color:var(--accent-light); padding:6px 16px; border-radius:var(--radius-xl); font-size:var(--font-size-xs); font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-md); backdrop-filter:blur(4px);">Grand Festival</span>
    <h1 class="reveal" style="font-family:var(--font-heading); color:var(--white); font-size:calc(var(--font-size-3xl) + 1vw); line-height:1.2; text-shadow:0 2px 10px rgba(0,0,0,0.6); max-width:900px; margin:0 auto var(--space-md) auto;">Bhishma Panchaka</h1>
    <div class="breadcrumb reveal" style="display:flex; justify-content:center; gap:8px; color:rgba(255,255,255,0.8); font-size:var(--font-size-sm);">
      <a href="<?php echo BASE_URL; ?>" style="color:var(--accent-light);">Home</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/" style="color:var(--accent-light);">Festivals</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/grand-festivals/" style="color:var(--accent-light);">Grand Festivals</a><span>›</span><span style="color:var(--white);">Bhishma Panchaka</span>
    </div>
  </div>
</section>

<!-- Main Page Content -->
<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:850px; background:var(--white); padding:var(--space-2xl) var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border:1px solid var(--border);">
    
    <!-- Banner Image at the top of the content area -->
    <div style="margin-bottom:var(--space-xl); text-align:center; overflow:hidden; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid var(--border);">
      <img src="<?php echo BASE_URL; ?>assets/images/banners/bhishma-panchaka.jpg" alt="Bhishma Panchaka Banner" style="width:100%; height:auto; display:block;">
    </div>

    <!-- Quick Stats/Info Box -->
    <div style="background:var(--cream); border-left:4px solid var(--primary); padding:var(--space-lg); border-radius:var(--radius-sm); margin-bottom:var(--space-2xl); display:flex; flex-direction:column; gap:10px;">
      <h4 style="margin:0; color:var(--primary); font-family:var(--font-heading); font-weight:600; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-info-circle"></i> Vrata Overview
      </h4>
      <p style="margin:0; font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.6;">
        <strong>Calendar:</strong> The last 5 days of Kartika (Damodara) month, beginning on Devutthana Ekadashi and ending on Rasa Purnima (November)<br>
        <strong>Alternative Names:</strong> Vishnu Panchaka<br>
        <strong>Significance:</strong> Grandfather Bhishma fasted during these five days on his bed of arrows, preparing to depart this world. Lord Krishna agreed to give His pure love to those who observe it.<br>
        <strong>Benefits:</strong> Frees observers from great sins and awards the spiritual benefit of all four Chaturmasya fasts combined.<br>
        <strong>Observance:</strong> Held on the scheduled event date. Please check the <a href="<?php echo BASE_URL; ?>festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a> for the exact day this year.
      </p>
    </div>

    <!-- Article Body -->
    <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8; display:flex; flex-direction:column; gap:var(--space-lg);">
      
      <p>
        The last five days of the month of Kartika (Damodara month) are traditionally known as the Bhishma Panchaka or the Vishnu Panchaka. Grandfather Bhishma fasted for these five days, preparing to give up his life while lying on a bed of arrows, meditating on the universal form of Lord Sri Krishna. 
      </p>
      
      <p>
        In the Hari Bhakti-Vilasa, it is stated that if one is capable, one should observe fasting from certain foodstuffs on the Bhishma-panchaka for the pleasure of the Lord. The Padma Purana states that one pleases the Lord and makes rapid spiritual advancement by such optional austerities. Lord Krishna, highly pleased by Grandfather Bhishma's devotion, agreed to grant His pure love to those fortunate souls who chant, sing, hear, read, worship, and fast during these five days.
      </p>

      <blockquote style="border-left:4px solid var(--maroon); padding-left:var(--space-md); font-style:italic; color:var(--dark); margin:var(--space-md) 0;">
        "If anyone fasts and observes the Kartika-vrata according to the rules and regulations, the Yamadutas, the messengers of Yamaraja, run away from him, just as an elephant runs away by seeing a lion." &mdash; Padma Purana
      </blockquote>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Levels of Fasting (Bhishma Panchaka Vrata)</h3>
      <p>
        Depending on physical capacity, devotees can choose between three progressive types of fasting from Ekadashi to Purnima (broken at moonrise on Purnima):
      </p>
      
      <p>
        <strong>Type 1 (Strict Pancha-gavya Vrata):</strong><br>
        The devotee takes only the five products of the cow, one on each of the five days:
      </p>
      <ul style="margin-left:var(--space-lg); line-height:1.8; display:flex; flex-direction:column; gap:6px;">
        <li><strong>Day 1 (Ekadashi):</strong> Cow dung (Gomaya)</li>
        <li><strong>Day 2 (Dvadashi):</strong> Cow urine (Go-mutra)</li>
        <li><strong>Day 3 (Trayodashi):</strong> Cow milk (Kshiira)</li>
        <li><strong>Day 4 (Chaturdashi):</strong> Cow yogurt (Dahi)</li>
        <li><strong>Day 5 (Purnima):</strong> All five cow products mixed together (Pancha-gavya)</li>
      </ul>

      <p>
        <strong>Type 2 (Ekadashi-style Fruits & Roots Vrata):</strong><br>
        If one cannot follow the first type, one can eat only fruits and roots. Fruits with many seeds (like guava, pomegranate, cucumber) should be avoided. Plain cashew nuts, raisins, and dates are allowed. Potatoes, sweet potatoes, and raw bananas can be boiled and taken with sea salt. No dairy or milk products are taken.
      </p>

      <p>
        <strong>Type 3 (Havishya Khichari Vrata):</strong><br>
        One can eat only "Havishya" (a sacred, unboiled form of rice and specific ingredients cooked without oil). 
        <em>Allowed ingredients:</em> Aatap rice, pure cow ghee, sea salt (Saindhava), cow milk, ripe bananas, wheat, mango, jackfruit, roots, pippali, haritakii, and sugarcane derivatives (other than gur). Mung dal, sesame oil, radishes, tamarind, and jeera must be avoided.
      </p>

      <!-- Sub-image block 1 -->
      <div style="margin:var(--space-xl) 0; text-align:center; overflow:hidden; border-radius:var(--radius-md); border:1px solid var(--border); box-shadow:var(--shadow-sm);">
        <img src="<?php echo BASE_URL; ?>assets/images/banners/bhishma-panchaka-offerings.jpg" alt="Bhishma Panchaka flower offerings chart" style="width:100%; height:auto; display:block;">
        <span style="display:block; background:var(--light); padding:8px 12px; font-size:12px; color:var(--text-light); border-top:1px solid var(--border);">Special flower offerings to the Lord's body parts</span>
      </div>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Daily Flower Offerings (Garuda Purana)</h3>
      <p>
        During the five days, devotees offer specific leaves, scents, and flowers to different parts of the Lord's deity form:
      </p>
      <ul style="margin-left:var(--space-lg); line-height:1.8; display:flex; flex-direction:column; gap:6px;">
        <li><strong>Day 1 (Feet):</strong> Offer Padma (lotus) flowers to the lotus feet of the Lord.</li>
        <li><strong>Day 2 (Thighs):</strong> Offer Bilva (wood-apple) leaves to the thighs of the Lord.</li>
        <li><strong>Day 3 (Navel):</strong> Offer Gandha (scents/sandalwood paste) to the navel of the Lord.</li>
        <li><strong>Day 4 (Shoulders):</strong> Offer Java (hibiscus) flowers to the shoulders of the Lord.</li>
        <li><strong>Day 5 (Head):</strong> Offer Malati (jasmine) flowers to the head of the Lord.</li>
      </ul>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Daily Bhishma Tarpana & Prayers</h3>
      <p>
        It is highly recommended to take a bath in the Ganges (or other holy rivers) early in the morning and offer water tarpana three times for Grandfather Bhishma with the following mantras:
      </p>
      
      <p>
        <strong>1. Tarpana (Water offering):</strong><br>
        <span style="font-family:monospace; display:block; background:var(--light); padding:var(--space-sm); border-radius:var(--radius-sm);">
          om vaiyaghra padya gotraya samkrti pravaraya ca<br>
          aputraya dadamyetat salilam bhismavarmane
        </span>
      </p>
      
      <p>
        <strong>2. Arghya (Auspicious offering):</strong><br>
        <span style="font-family:monospace; display:block; background:var(--light); padding:var(--space-sm); border-radius:var(--radius-sm);">
          vasunamavataraya santanoratmajaya ca<br>
          arghyam dadami bhismaya ajanma brahmacarine
        </span>
      </p>
      
      <p>
        <strong>3. Pranam (Obeisances):</strong><br>
        <span style="font-family:monospace; display:block; background:var(--light); padding:var(--space-sm); border-radius:var(--radius-sm);">
          om bhismah santanavo birah satyavadi jitendriyah<br>
          abhiradbhiravapnatu putrapautrocitam kriyam
        </span>
      </p>

    </article>

    <?php 
    include_once __DIR__ . '/../../partials/donation-cta.php';
    renderDonationSection([
      'cause_slug' => 'bhishma-panchaka',
      'button_label' => 'Offer Bhishma Panchaka Seva',
      'background' => 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)'
    ]); 
    ?>

  </div>
</section>

<?php include '../../partials/footer.php'; ?>
