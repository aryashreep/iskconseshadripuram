<?php
$pageTitle = 'Jhulan Yatra – The Swing Festival';
$metaDescription = 'Learn about Jhulan Yatra at ISKCON Seshadripuram, Bangalore. The swing festival celebrating Radha and Krishna\'s loving pastimes during the rainy season in Vrindavan.';
include '../../partials/header.php';
require_once '../../config.php';
?>

<!-- Custom Page Header with Hero Banner -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/jhulan-yatra.jpg');"></div>
  <div class="container" style="position:relative; z-index:1;">
    <span style="display:inline-block; background:rgba(200, 107, 31, 0.2); border:1px solid var(--primary); color:var(--accent-light); padding:6px 16px; border-radius:var(--radius-xl); font-size:var(--font-size-xs); font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-md); backdrop-filter:blur(4px);">Grand Festival</span>
    <h1 class="reveal" style="font-family:var(--font-heading); color:var(--white); font-size:calc(var(--font-size-3xl) + 1vw); line-height:1.2; text-shadow:0 2px 10px rgba(0,0,0,0.6); max-width:900px; margin:0 auto var(--space-md) auto;">Jhulan Yatra</h1>
    <div class="breadcrumb reveal" style="display:flex; justify-content:center; gap:8px; color:rgba(255,255,255,0.8); font-size:var(--font-size-sm);">
      <a href="<?php echo BASE_URL; ?>" style="color:var(--accent-light);">Home</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/" style="color:var(--accent-light);">Festivals</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/grand-festivals/" style="color:var(--accent-light);">Grand Festivals</a><span>›</span><span style="color:var(--white);">Jhulan Yatra</span>
    </div>
  </div>
</section>

<!-- Main Page Content -->
<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:850px; background:var(--white); padding:var(--space-2xl) var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border:1px solid var(--border);">
    
    <!-- Banner Image at the top of the content area -->
    <div style="margin-bottom:var(--space-xl); text-align:center; overflow:hidden; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid var(--border);">
      <img src="<?php echo BASE_URL; ?>assets/images/banners/jhulan-yatra.jpg" alt="Jhulan Yatra Banner" style="width:100%; height:auto; display:block;">
    </div>

    <!-- Quick Stats/Info Box -->
    <div style="background:var(--cream); border-left:4px solid var(--primary); padding:var(--space-lg); border-radius:var(--radius-sm); margin-bottom:var(--space-2xl); display:flex; flex-direction:column; gap:10px;">
      <h4 style="margin:0; color:var(--primary); font-family:var(--font-heading); font-weight:600; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-info-circle"></i> Festival Overview
      </h4>
      <p style="margin:0; font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.6;">
        <strong>Calendar:</strong> Celebrated for five days in the waxing phase of Shravana month (July/August), starting on Pavitropana Ekadashi and ending on Balarama Purnima.<br>
        <strong>Significance:</strong> Celebrates the sweet pastime of Srimati Radharani and Lord Krishna swinging on a flower-bedecked swing.<br>
        <strong>Observance:</strong> Deities are placed on a beautifully decorated swing. Devotees offer prayers and pull the swing during ecstatic kirtan.<br>
        <strong>Observance:</strong> Held on the scheduled event date. Please check the <a href="<?php echo BASE_URL; ?>festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a> for the exact day this year.
      </p>
    </div>

    <!-- Article Body -->
    <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8; display:flex; flex-direction:column; gap:var(--space-lg);">
      
      <p>
        Jhulan Yatra is among the most auspicious festivals celebrated in Vrindavana. Also known as the Swing Festival, this beautiful pastime lasts for thirteen days in the sacred town of Vrindavana, celebrating Radha-Krishna's pastimes of swinging on a golden swing every day until Balarama-purnima.
      </p>
      
      <p>
        In accordance with Srila Prabhupada's instructions, ISKCON temples worldwide observe this festival for 5 days, starting on the Pavitropana Ekadashi in the waxing phase of the moon and continuing until Sawan Purnima (the Full Moon day).
      </p>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">The Swing Festival Celebrations</h3>
      <p>
        During these five days, the small deities of Radha and Krishna are placed on a beautifully decorated swing in the temple hall. After receiving traditional arati worship, the deities are gently pushed on their swing. The swing is decorated with all types of colorful flowers, creepers, and leaves, giving the feel as if the swing has appeared directly from the groves of Vrindavana. Devotees work hard and put their heart and soul into making this festival a blissful sight for visitors.
      </p>
      
      <blockquote style="border-left:4px solid var(--maroon); padding-left:var(--space-md); font-style:italic; color:var(--dark); margin:var(--space-md) 0;">
        "In the evening, members of the congregation come to the temple to participate. Each person offers personal prayers and then pushes the swing several times while ecstatic kirtan is going on simultaneously. The atmosphere is exciting and jubilant, offering an intimate, practical service to Radha and Krishna."
      </blockquote>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Cooling the Lordships in the Monsoon Heat</h3>
      <p>
        Like every festival, Jhulan Yatra is a meaningful way to offer practical service to the Lord. In Vrindavan, the monsoon season is very sticky and the temperature remains hot despite the cooling rains. Heavy humidity makes finding a cool breeze a true luxury. Devotees, therefore, place Radha and Krishna on a swing (Jhulan) to create a pleasant breeze from its own motion, providing satisfaction and comfort to Their Lordships.
      </p>
      
      <p>
        The final day of Jhulan Yatra falls on the full moon, which also marks Balarama Purnima, the appearance day of Lord Balarama. Be a part of this Jhulan Yatra with us this year and offer your loving spiritual services to Lord Krishna and Srimati Radharani.
      </p>

    </article>

    <?php 
    include_once __DIR__ . '/../../../../partials/donation-cta.php';
    renderDonationSection([
      'cause_slug' => 'jhulan-yatra',
      'button_label' => 'Offer Jhulan Yatra Seva',
      'background' => 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)'
    ]); 
    ?>

  </div>
</section>

<?php include '../../partials/footer.php'; ?>
