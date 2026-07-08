<?php
$pageTitle = 'Diwali – The Festival of Lights';
$metaDescription = 'Celebrate Diwali at the official ISKCON temple in Seshadripuram, Bangalore. Learn about the festival of lights, Lakshmi-Ganesha puja, and Damodara pastimes.';
include '../../partials/header.php';
require_once '../../config.php';
?>

<!-- Custom Page Header with Hero Banner -->
<section class="page-header" style="position:relative; overflow:hidden; padding: var(--space-4xl) 0; text-align:center;">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/diwali.jpg'); background-size: cover; background-position: center; position: absolute; inset:0; z-index:-1; filter: brightness(0.4) contrast(1.1);"></div>
  <div class="container" style="position:relative; z-index:1;">
    <span style="display:inline-block; background:rgba(200, 107, 31, 0.2); border:1px solid var(--primary); color:var(--accent-light); padding:6px 16px; border-radius:var(--radius-xl); font-size:var(--font-size-xs); font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-md); backdrop-filter:blur(4px);">Grand Festival</span>
    <h1 class="reveal" style="font-family:var(--font-heading); color:var(--white); font-size:calc(var(--font-size-3xl) + 1vw); line-height:1.2; text-shadow:0 2px 10px rgba(0,0,0,0.6); max-width:900px; margin:0 auto var(--space-md) auto;">Diwali</h1>
    <div class="breadcrumb reveal" style="display:flex; justify-content:center; gap:8px; color:rgba(255,255,255,0.8); font-size:var(--font-size-sm);">
      <a href="<?php echo BASE_URL; ?>" style="color:var(--accent-light);">Home</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/" style="color:var(--accent-light);">Festivals</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/grand-festivals/" style="color:var(--accent-light);">Grand Festivals</a><span>›</span><span style="color:var(--white);">Diwali</span>
    </div>
  </div>
</section>

<!-- Main Page Content -->
<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:850px; background:var(--white); padding:var(--space-2xl) var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border:1px solid var(--border);">
    
    <!-- Banner Image at the top of the content area -->
    <div style="margin-bottom:var(--space-xl); text-align:center; overflow:hidden; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid var(--border);">
      <img src="<?php echo BASE_URL; ?>assets/images/banners/diwali.jpg" alt="Diwali Banner" style="width:100%; height:auto; display:block;">
    </div>

    <!-- Quick Stats/Info Box -->
    <div style="background:var(--cream); border-left:4px solid var(--primary); padding:var(--space-lg); border-radius:var(--radius-sm); margin-bottom:var(--space-2xl); display:flex; flex-direction:column; gap:10px;">
      <h4 style="margin:0; color:var(--primary); font-family:var(--font-heading); font-weight:600; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-info-circle"></i> Festival Overview
      </h4>
      <p style="margin:0; font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.6;">
        <strong>Calendar:</strong> Celebrated on Kartik Amavasya (new moon day of the month of Kartika) (October/November)<br>
        <strong>Alternative Names:</strong> Deepawali, Festival of Lights<br>
        <strong>Deities Worshipped:</strong> Lord Ramachandra, Goddess Lakshmi, Lord Ganesha, and Lord Damodara<br>
        <strong>Significance:</strong> Represents the return of Lord Rama to Ayodhya, the killing of demon Narakasura by Krishna, the birth of Goddess Lakshmi, and the Damodara-lila.<br>
        <strong>Observance:</strong> Held on the scheduled event date. Please check the <a href="<?php echo BASE_URL; ?>festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a> for the exact day this year.
      </p>
    </div>

    <!-- Article Body -->
    <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8; display:flex; flex-direction:column; gap:var(--space-lg);">
      
      <p>
        Diwali or Deepawali is the festival of lights. This festival is celebrated throughout the world with extreme splendor, representing the ultimate victory of good over evil, knowledge over ignorance, and light over darkness.
      </p>
      
      <p>
        It is an auspicious start for the rest of the year, and we encourage devotees reading this to kindly consider donating on this day.
      </p>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Why Do We Celebrate Diwali?</h3>
      <p>
        Scriptures record several historical occurrences on this day:
      </p>
      <ul style="margin-left:var(--space-lg); line-height:1.8; display:flex; flex-direction:column; gap:6px;">
        <li><strong>Return of Lord Rama:</strong> Lord Ramachandra returned to Ayodhya after 14 years of exile and defeating the demon king Ravana. The citizens illuminated the entire kingdom with rows of clay lamps (*diya*).</li>
        <li><strong>Krishna Kills Narakasura:</strong> In Dvapara Yuga, Lord Krishna killed the demon Narakasura and liberated 16,000 captive princesses. This is celebrated as Narakachaturdashi.</li>
        <li><strong>Damodara Lila:</strong> Mother Yashoda tied little Krishna to a grinding mortar on this day. Krishna crawled and dragged the mortar, liberating the two sons of Kuvera (Nalakuvara and Manigriva) who had been cursed to stand as Yamalarjuna trees.</li>
        <li><strong>Birth of Goddess Lakshmi:</strong> Goddess Lakshmi appeared during the churning of the cosmic ocean of milk (*Samudra Manthan*) and married Lord Vishnu on this day.</li>
        <li><strong>Vishnu Saves Lakshmi from King Bali:</strong> Lord Vishnu, in His fifth incarnation as Vamanadeva, rescued Goddess Lakshmi from the prison of King Bali and sent Bali to rule the sub-terranean world.</li>
        <li><strong>Return of the Pandavas:</strong> The five Pandava brothers returned to Hastinapur on Kartik Amavasya after 12 years of exile.</li>
      </ul>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">The Tale of Ramayana</h3>
      <p>
        Lord Rama was the eldest son of King Dasharatha and the seventh incarnation of Lord Vishnu, born with the specific purpose of defeating Ravana. After winning His wife Sita in an archery competition, they returned to Ayodhya. However, His stepmother Kaikeyi forced King Dasharatha to exile Rama to the forest for 14 years. His wife Sita and brother Lakshmana accompanied Him.
      </p>
      
      <p>
        During their forest exile, the demon king Ravana kidnapped Sita and carried her to his palace in Lanka. With the help of the monkey king Sugriva and His devoted general Hanuman, Lord Rama built a bridge to Lanka, defeated Ravana, and rescued Sita. The return of Sita and Rama to Ayodhya marked the beginning of *Ram Rajya* &mdash; the glorious reign of righteous order.
      </p>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Why Do We Do Lakshmi-Ganesha Puja?</h3>
      <p>
        While Diwali marks the return of Lord Rama, Goddess Lakshmi and Lord Ganesha are traditionally worshipped in the evening. In Kali Yuga, which is heavily influenced by *rajoguna* (the mode of passion), people seek wealth above all else. Devotees worship Goddess Lakshmi for prosperity.
      </p>
      
      <p>
        Lord Ganesha, the elephant-headed god of wisdom, is worshipped alongside Lakshmi. Ganesha was adopted by Lakshmi out of love, and she declared that those who do not worship Ganesha with her will never see prosperity. What is wealth without wisdom to use it properly? Wisdom (Ganesha) and wealth (Lakshmi) must remain balanced to bring true prosperity.
      </p>
      
      <p>
        As we decorate our houses with lights and rangolis, let us also cleanse our hearts by bringing in spiritual knowledge, making our hearts pure temples to serve Radha-Krishna and progress in Bhakti.
      </p>

    </article>

    <?php 
    include_once __DIR__ . '/../../../../partials/donation-cta.php';
    renderDonationSection([
      'cause_slug' => 'diwali',
      'button_label' => 'Offer Diwali Seva',
      'background' => 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)'
    ]); 
    ?>

  </div>
</section>

<?php include '../../partials/footer.php'; ?>
