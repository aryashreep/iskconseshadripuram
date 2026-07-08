<?php
$pageTitle = 'Our Philosophy';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/our-philosophy.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Our Philosophy</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="about">About Us</a><span>›</span><span>Our Philosophy</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/our-philosophy.jpg" alt="ISKCON Philosophy" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div style="text-align:center;margin-bottom:var(--space-3xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon">📖</span></div>
      <p style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-style:italic;color:var(--primary);line-height:1.8;">
        harer nāma harer nāma<br>
        harer nāmaiva kevalam<br>
        kalau nāsty eva nāsty eva<br>
        nāsty eva gatir anyathā
      </p>
      <p style="color:var(--text-light);margin-top:var(--space-md);">"Harinama, Harinama, Harinama is the only and foremost means to achieve emancipation. There is no other way, no other way, no other way in this age of Kali."</p>
    </div>

    <div class="reveal" style="line-height:1.9;color:var(--text-light);">
      <h2>The Doctrines of ISKCON</h2>
      <p>The doctrines of the International Society for Krishna Consciousness (ISKCON) are derived from ancient Vaishnava scriptures such as Srimad-Bhagavatam (commentary on the Vedas), Srimad Bhagavad-gita (Lord Krishna&rsquo;s teachings), and Sri Caitanya-Charitamrta (Lord Chaitanya&rsquo;s teachings).</p>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);line-height:1.9;color:var(--text-light);">
      <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🌱</span></div>
      <h2>Origin</h2>
      <p>The basic tenet of these teachings is that each living entity is an eternal spirit soul and has a distinct relationship with God, Krishna. Lord Chaitanya distinguished Gaudiya Vaishnavism from other sampradayas by understanding that the spirit soul is &ldquo;inconceivably one and different&rdquo; from the Supreme Lord: <em>acintya bheda tattva</em>. Both are of the same spiritual nature, but the Lord is unlimited in quantity, and the spirit soul is His tiny servant.</p>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);">
      <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">📖</span></div>
      <h2>As Explained by Srila Prabhupada</h2>
      <p style="color:var(--text-light);line-height:1.8;">The basic Hare Krishna beliefs can be summarized as follows:</p>
    </div>

    <div style="margin-top:var(--space-3xl);display:grid;gap:var(--space-xl);">
      <?php
      $principles = [
        [
          'Beyond Body &mdash; Soul Exists',
          'The very first step in self-realization is realizing one&rsquo;s identity as separate from the body. &ldquo;I am not this body but am spirit soul&rdquo; is an essential realization for anyone who wants to transcend death and enter into the spiritual world beyond. It is not simply a matter of saying &ldquo;I am not this body,&rdquo; but of actually realizing it.'
        ],
        [
          'Reincarnation &mdash; One Life Is Not Everything',
          'Remembrances of past lives can be fascinating, but the real goal of understanding reincarnation is to become free from the painful cycle of birth and death. Srila Prabhupada warns: &ldquo;This is not a very good business &ndash; to die and take birth again. We know that when we die we&rsquo;ll have to enter again into the womb of a mother &ndash; and nowadays mothers are killing the children within the womb.&rdquo;'
        ],
        [
          'The Spiritual World &mdash; Beyond the Material',
          'In the material world there are so many inconveniences. In the spiritual world, there is no inconvenience, no inebriety. The material world is a reflection of the spiritual world, but there are so many difficulties here. Therefore it is called material world.'
        ],
        [
          'The Supreme Controller &mdash; God Exists',
          'Īśvaraḥ paramaḥ kṛṣṇaḥ: &ldquo;The supreme controller is Kṛṣṇa.&rdquo; Sac-cid-ānanda-vigrahaḥ. This is our definition of God. God means supreme controller &ndash; who is not controlled by anyone, but He is controller of everyone. That is God.'
        ],
        [
          'How to Connect with God',
          'As soon as one understands his identity and his relationship with God, then immediately he becomes happy. We are so full of miseries because we have identified ourselves with the material world. Therefore we are unhappy. Anxieties and fearfulness are due to our misidentifying with the material world. The sankirtana movement is the easiest process for self-realization (bhakti yoga) because it cleanses the heart. &ldquo;Harinama, Harinama, Harinama is the only and foremost means to achieve emancipation. There is no other way, no other way, no other way in this age of Kali.&rdquo;'
        ],
        [
          'Process of Bhakti Yoga',
          'A pure devotee has no plans other than those for the Lord&rsquo;s service. One can execute the process of bhakti-yoga successfully with full-hearted enthusiasm, perseverance, and determination, by following the prescribed duties in the association of devotees and by engaging completely in activities of goodness.'
        ],
      ];
      foreach ($principles as $p):
      ?>
      <div class="reveal" style="background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);">
        <h4 style="margin-bottom:var(--space-sm);"><?php echo $p[0]; ?></h4>
        <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.8;"><?php echo $p[1]; ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Founder's Statement -->
    <div class="reveal" style="margin-top:var(--space-3xl);">
      <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">📜</span></div>
      <h2>Founder&rsquo;s Statement</h2>
      <p style="color:var(--text-light);line-height:1.8;">Kolkata-born A.C. Bhaktivedanta Swami (1896-1977), also known as Srila Prabhupada, who founded The Hare Krishna Movement in New York in 1966, wrote a statement that was used in the religion&rsquo;s initial incorporation. This statement is still relevant for ISKCON, and serves as a Mission Statement.</p>
    </div>

    <div style="display:grid;gap:var(--space-lg);margin-top:var(--space-lg);">
      <?php
      $founderPurposes = [
        'To systematically propagate spiritual knowledge to society at large and to educate all people in the techniques of spiritual life in order to check the imbalance of values in life and to achieve real unity and peace in the world.',
        'To propagate a consciousness of Krishna (God), as it is revealed in the great scriptures of India, especially Bhagavad-gita and Srimad-Bhagavatam. We accept the principle of transmigration of the soul (reincarnation).',
        'To bring the members of the Society together with each other and nearer to Krishna, the prime entity, thus developing the idea within the members, and humanity at large, that each soul is part and parcel of the quality of Godhead (Krishna).',
        'To teach and encourage the sankirtana movement, congregational chanting of the holy name of God, as revealed in the teachings of Lord Sri Caitanya Mahaprabhu.',
        'To erect for the members and for society at large a holy place of transcendental pastimes dedicated to the personality of Krishna.',
        'To bring the members closer together for the purpose of teaching a simpler, more natural way of life.',
        'With a view towards achieving the aforementioned purposes, to publish and distribute periodicals, magazines, books and other writings and to create websites that help realize these purposes.',
      ];
      foreach ($founderPurposes as $i => $purpose):
      ?>
      <div class="reveal" style="background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-lg);border-left:4px solid var(--primary);">
        <div style="display:flex;align-items:flex-start;gap:var(--space-md);">
          <div style="width:32px;height:32px;min-width:32px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-weight:700;font-size:var(--font-size-sm);font-family:var(--font-heading);"><?php echo $i + 1; ?></div>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.8;"><?php echo htmlspecialchars($purpose); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--gradient-cta);border-radius:var(--radius-lg);padding:var(--space-2xl);text-align:center;">
      <p style="font-family:var(--font-subheading);font-style:italic;font-size:var(--font-size-lg);color:var(--white);line-height:1.8;">Hare Krishna Hare Krishna<br>Krishna Krishna Hare Hare<br>Hare Rama Hare Rama<br>Rama Rama Hare Hare</p>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
