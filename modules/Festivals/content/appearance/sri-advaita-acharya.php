<?php
$pageTitle = 'Appearance Day of Sri Advaita Acharya';
$pageType = 'festival';
include '../../partials/header.php';
require_once '../../config.php';
?>

<!-- Custom Page Header with Hero Banner -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/sri-advaita-acharya-appearance-banner.jpg');"></div>
  <div class="container" style="position:relative; z-index:1;">
    <span style="display:inline-block; background:rgba(200, 107, 31, 0.2); border:1px solid var(--primary); color:var(--accent-light); padding:6px 16px; border-radius:var(--radius-xl); font-size:var(--font-size-xs); font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-bottom:var(--space-md); backdrop-filter:blur(4px);">Bhaktavatara</span>
    <h1 class="reveal" style="font-family:var(--font-heading); color:var(--white); font-size:calc(var(--font-size-3xl) + 1vw); line-height:1.2; text-shadow:0 2px 10px rgba(0,0,0,0.6); max-width:900px; margin:0 auto var(--space-md) auto;">Sri Advaita Acharya Appearance Day</h1>
    <div class="breadcrumb reveal" style="display:flex; justify-content:center; gap:8px; color:rgba(255,255,255,0.8); font-size:var(--font-size-sm);">
      <a href="<?php echo BASE_URL; ?>" style="color:var(--accent-light);">Home</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/" style="color:var(--accent-light);">Festivals</a><span>›</span><a href="<?php echo BASE_URL; ?>festivals/appearance/" style="color:var(--accent-light);">Appearance Days</a><span>›</span><span style="color:var(--white);">Sri Advaita Acharya</span>
    </div>
  </div>
</section>

<!-- Main Page Content -->
<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:850px; background:var(--white); padding:var(--space-2xl) var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border:1px solid var(--border);">
    
    <!-- Banner Image at the top of the content area -->
    <div style="margin-bottom:var(--space-xl); text-align:center; overflow:hidden; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid var(--border);">
      <img src="<?php echo BASE_URL; ?>assets/images/banners/sri-advaita-acharya-appearance-banner.jpg" alt="Sri Advaita Acharya Appearance Banner" style="width:100%; height:auto; display:block;">
    </div>

    <!-- Quick Stats/Info Box -->
    <div style="background:var(--cream); border-left:4px solid var(--maroon); padding:var(--space-lg); border-radius:var(--radius-sm); margin-bottom:var(--space-2xl); display:flex; flex-direction:column; gap:10px;">
      <h4 style="margin:0; color:var(--maroon); font-family:var(--font-heading); font-weight:600; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-info-circle"></i> Holy Observance Overview
      </h4>
      <p style="margin:0; font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.6;">
        <strong>Appearance Date:</strong> Seventh day of the waxing moon in the month of Magh (1434 A.D.)<br>
        <strong>Appearance Location:</strong> Navagrama (near Sylhet, Bangladesh)<br>
        <strong>Disappearance:</strong> 1559 A.D. (Age 125 years)<br>
        <strong>Parents:</strong> Kubera Pandit & Nabha Devi<br>
        <strong>Divine Identity:</strong> Incarnation of Maha-Vishnu and Sadashiva<br>
        <strong>Observance:</strong> Held on the scheduled event date. Please check the <a href="<?php echo BASE_URL; ?>festivals/vaishnava-calendar" style="color:var(--primary); font-weight:600; text-decoration:underline;">Vaishnava Calendar</a> for the exact day this year.
      </p>
    </div>

    <!-- Article Body -->
    <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8; display:flex; flex-direction:column; gap:var(--space-lg);">
      
      <p>
        Lord Advaita Ācārya is the incarnation of Mahā-Viṣṇu, whose main function is to create the cosmic world through the actions of <em>māyā</em>. He appeared in the village of Navagrama near the city of Sylhet as the son of the Varendra Brahmin Kubera Pandit and his wife Nabha Devi, on the seventh day of the waxing moon in the month of Magh (January–February). 
      </p>
      
      <p>
        Advaita Acharya also maintained a home in Nabadwip. His full name was Sri Kamalaksha (or Kamala Kanta Vedapanchanana). His birth took place in 1434 A.D., and he disappeared in the year 1559, at the exalted age of 125 years.
      </p>

      <!-- Pranama Verse Box -->
      <div style="background:var(--light); padding:var(--space-xl); border-radius:var(--radius-sm); border-left:4px solid var(--primary); text-align:center; margin:var(--space-md) 0;">
        <p style="font-family:var(--font-subheading); font-size:var(--font-size-base); font-weight:600; color:var(--primary-dark); line-height:1.6; margin-bottom:12px;">
          maha-vishnur jagat-karta mayaya yah srijaty adah<br>
          tasyavatara evayam advaitacarya ishvarah<br><br>
          advaitam harinadvaitad acaryam bhakti-shasanat<br>
          bhaktavataram isham tam advaitacaryam ashraye
        </p>
        <p style="font-size:var(--font-size-sm); color:var(--text-dark); line-height:1.5; font-style:italic; margin:0;">
          "Because He is nondifferent from Hari, the Supreme Lord, He is called Advaita, and because He propagates the cult of devotion, He is called Acarya. He is the Lord and the incarnation of the Lord's devotee. Therefore I take shelter of Him."<br>
          <span style="font-size:11px; color:var(--text-light); font-weight:600; font-style:normal; display:block; margin-top:6px;">— Śrī Caitanya-caritāmṛta Ādi-līlā 1.12-13</span>
        </p>
      </div>

      <h3 style="color:var(--primary); font-family:var(--font-heading); font-size:var(--font-size-xl); margin-top:var(--space-lg);">Calling Down the Supreme Lord</h3>
      <p>
        It is written that Lord Caitanya Mahaprabhu is the main trunk of the wish-fulfilling tree of devotion. Sri Advaita Acharya and Sri Nityananda Prabhu were the two secondary trunks into which the main trunk divided.
      </p>
      <p>
        When Advaita Acharya appeared along with the other elderly members of Lord Chaitanya's retinue, he observed that the sinfulness of the age of Kali had reached a disturbing limit. The world was entirely devoid of devotion to Krishna, with people absorbed in materialistic rituals. He realized that a partial incarnation of the Lord would not be sufficient to bring about the welfare of the earth in such a fallen state. 
      </p>
      <p>
        He determined that only the Supreme Personality of Godhead Himself could deliver the fallen souls. Therefore, he began to worship Krishna's lotus feet with Ganges water and Tulasi leaves, shouting and pleading to the Lord to manifest. With roars of love (<em>prema-hunkara</em>), Advaita Acharya showed his intense desire for the Lord of Goloka to descend to the earth.
      </p>

      <blockquote style="border-left:4px solid var(--accent); padding-left:var(--space-md); font-style:italic; color:var(--dark); margin:var(--space-md) 0;">
        "He constantly offered water from the Ganges and tulasi manjaris while meditating on Krishna's lotus feet, calling to Him with loud roars. This is how he caused Lord Caitanya to descend."
      </blockquote>
      
      <p>
        This worship and loud calling was the chief cause for the descent of Sri Krishna Chaitanya Mahaprabhu. Out of affection for His devotee, the Supreme Lord descended to inaugurate the Sankirtana movement and deliver all living entities.
      </p>

    </article>

    <?php 
    include_once __DIR__ . '/../../../../partials/donation-cta.php';
    renderDonationSection([
      'cause_slug' => 'sri-advaita-acharya-appearance',
      'button_label' => 'Offer Advaita Acharya Seva',
      'background' => 'linear-gradient(135deg, var(--primary) 0%, var(--maroon) 100%)'
    ]); 
    ?>

  </div>
</section>

<?php include '../../partials/footer.php'; ?>
