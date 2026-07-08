<?php
$pageTitle = 'Hare Krishna Movement';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/hare-krishna-movement.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Hare Krishna Movement</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="about">About Us</a><span>›</span><span>Hare Krishna Movement</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/hare-krishna-movement.jpg" alt="Lord Chaitanya and Lord Nityananda" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="reveal">
      <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon"><img src="<?php echo BASE_URL; ?>assets/images/iskcon_logo.svg" alt="ISKCON" style="height:24px;width:auto;"></span></div>
      <h2>The Global Spiritual Movement</h2>
      <p style="color:var(--text-light);line-height:1.9;">The Hare Krishna Movement, officially known as the International Society for Krishna Consciousness (ISKCON), is a global spiritual and cultural movement rooted in the teachings of the Bhagavad Gita and the Srimad Bhagavatam, ancient Hindu scriptures.</p>
      <p style="color:var(--text-light);line-height:1.9;">The movement was founded in 1966 by His Divine Grace A.C. Bhaktivedanta Swami Prabhupada, also known as Srila Prabhupada. Srila Prabhupada&rsquo;s mission was to spread the teachings of Krishna consciousness and promote spiritual awareness, love, and devotion to Lord Krishna.</p>
      <p style="color:var(--text-light);line-height:1.9;">The core philosophy of the Hare Krishna Movement is centered around the practice of bhakti-yoga, the yoga of devotion and loving service. Followers of the movement, known as devotees, strive to cultivate a deep and personal relationship with Lord Krishna through devotional practices, such as mantra meditation, singing of bhajans (devotional songs), and the study of sacred scriptures.</p>
    </div>

    <div class="reveal" style="margin-top:var(--space-2xl);text-align:center;background:var(--gradient-cta);padding:var(--space-2xl);border-radius:var(--radius-lg);">
      <p style="font-family:var(--font-subheading);font-size:var(--font-size-2xl);color:var(--accent);font-weight:600;line-height:1.8;">Hare Krishna Hare Krishna<br>Krishna Krishna Hare Hare<br>Hare Rama Hare Rama<br>Rama Rama Hare Hare</p>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);">
      <p style="color:var(--text-light);line-height:1.9;">Chanting the Hare Krishna mantra holds great significance in the Hare Krishna Movement. Devotees believe that through the repetitive chanting of this mantra, one can cleanse the heart and attain a state of spiritual awakening and connection with Krishna.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">The Hare Krishna Movement promotes a lifestyle based on principles of compassion, simplicity, and selfless service. Devotees aim to apply spiritual values in all aspects of life, including vegetarianism (with a preference for lacto-vegetarian food offered to Krishna), non-violence, and ethical conduct.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">ISKCON has established numerous temples, ashrams, and centers worldwide, where devotees come together for congregational worship, spiritual gatherings, and community service. These centers serve as places of worship, education, and cultural activities, fostering a sense of community and promoting spiritual growth.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">The movement also engages in various philanthropic activities, including food distribution programs, educational initiatives, and humanitarian relief efforts. The Hare Krishna Movement places great emphasis on sharing Krishna consciousness with others, aiming to spread love, peace, and spiritual knowledge throughout society.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">Throughout the years, the Hare Krishna Movement has gained recognition for its distinctive spiritual practices, vibrant festivals, and its contributions to art, music, and literature. The movement&rsquo;s teachings have touched the lives of millions of individuals worldwide, inspiring them to lead a spiritually conscious and purposeful life.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">Overall, the Hare Krishna Movement encourages individuals to embrace devotion, love, and service to Lord Krishna, fostering a deep connection with the divine and promoting a harmonious and spiritually enriched existence.</p>
    </div>

    <div style="margin-top:var(--space-3xl);display:grid;grid-template-columns:1fr 1fr;gap:var(--space-xl);">
      <?php
      $aspects = [
        ['fa-music', 'Sacred Chanting', 'Chanting the Hare Krishna mantra — "Hare Krishna, Hare Krishna, Krishna Krishna, Hare Hare, Hare Rama, Hare Rama, Rama Rama, Hare Hare" — cleanses the heart and awakens our spiritual consciousness.'],
        ['fa-seedling', 'Compassionate Living', 'The movement promotes a lifestyle based on compassion, simplicity, and selfless service, including vegetarianism, non-violence, and ethical conduct.'],
        ['fa-globe', 'Global Community', 'ISKCON has established numerous temples, ashrams, and centers worldwide where devotees gather for worship, education, and community service.'],
        ['fa-hand-holding-heart', 'Philanthropy', 'The movement engages in food distribution programs, educational initiatives, and humanitarian relief efforts worldwide.'],
      ];
      foreach ($aspects as $a):
      ?>
      <div class="reveal" style="background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-lg);margin-bottom:var(--space-md);"><i class="fas <?php echo $a[0]; ?>"></i></div>
        <h4 style="margin-bottom:var(--space-sm);"><?php echo $a[1]; ?></h4>
        <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;margin:0;"><?php echo $a[2]; ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
