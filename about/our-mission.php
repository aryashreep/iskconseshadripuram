<?php
$pageTitle = 'Our Mission';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/our-mission.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Our Mission</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="about">About Us</a><span>›</span><span>Our Mission</span></div>
  </div>
</section>

<!-- Intro -->
<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/our-mission.jpg" alt="Seven Purposes of ISKCON" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="reveal" style="text-align:center;margin-bottom:var(--space-2xl);">
      <div class="section-divider" style="justify-content:center;"><span class="divider-icon">🎯</span></div>
      <h2>Our Mission</h2>
      <p style="color:var(--text-light);line-height:1.9;font-size:var(--font-size-lg);">Our mission is to bring the members of the Society together with each other and nearer to Krishna, the prime entity, thus developing the idea within the members, and humanity at large, that each soul is part and parcel of the quality of Godhead (Krishna). Working for Krishna is the ultimate perfection of all our activities.</p>
      <p style="color:var(--text-light);line-height:1.9;font-size:var(--font-size-lg);margin-top:var(--space-lg);">ISKCON Seshadripuram stands as a beacon of spiritual enlightenment, nurturing the souls of its followers and inspiring them to cultivate a deeper connection with Krishna. Through its various activities and programs, it aims to foster personal growth, promote values of love and compassion, and encourage individuals to lead a spiritually fulfilling life.</p>
    </div>

    <div class="reveal" style="margin-bottom:var(--space-3xl);">
      <div class="section-divider" style="justify-content:center;"><span class="divider-icon">📜</span></div>
      <h2 style="text-align:center;">Seven Purposes of ISKCON</h2>
      <p style="color:var(--text-light);line-height:1.8;font-size:var(--font-size-lg);text-align:center;">When Prabhupada began the International Society for Krishna Consciousness (in New York City in 1966), he formulated a clear mission statement. Thus the 7 Purposes of ISKCON are as follows:</p>
    </div>

    <div style="display:grid;gap:var(--space-xl);">
      <?php
      $purposes = [
        [
          'To systematically propagate spiritual knowledge to society at large and to educate all people in the techniques of spiritual life in order to check the imbalance of values in life and to achieve real unity and peace in the world.',
          'The purpose is to educate people from all backgrounds, races, and ethnicities on the importance of spiritual life and how to practice spiritual life. The aim is to give people genuine spiritual knowledge that we are all spirit souls and that there is a spiritual world beyond this material world. Examples of how this can be achieved are by distributing Srila Prabhupada&rsquo;s books which present the most scientific and the most authentic understanding of spiritual reality in a very simple way, and by inviting people to talks/seminars by spiritual teachers to understand the purpose of spiritual life. Hearing about the purpose and the goal of spiritual life will enable people to become more peaceful, content, and self-satisfied from within.'
        ],
        [
          'To propagate a consciousness of Krishna (God), as it is revealed in the great scriptures of India, Bhagavad-gita and Srimad-Bhagavatam.',
          'The purpose is to present the specifics of who God is as revealed in the scriptures. Srila Prabhupada has presented knowledge about Krishna, the Supreme Being, by translating great scriptures such as the Bhagavad-gita and Srimad Bhagavatam. Krishna (God) knows everything. He is the Supreme controller; He controls everything. He is smaller than the smallest and greater than the greatest, and He is maintaining everyone. Thus, learning about the Supreme and our relationship with Him will allow us to move forward and make progress with our spiritual lives.'
        ],
        [
          'To bring the members of the Society together with each other and nearer to Krishna, the prime entity, thus developing the idea within the members, and humanity at large, that each soul is part and parcel of the quality of Godhead (Krishna).',
          'The purpose is for people to come close together to become part of an institution whose focus is centered on Krishna. It is the nature of the soul to want knowledge of the Supreme Being and to learn about our relationship with Him. Feeling a sense of belonging in a community and making friendships with like-minded people further fosters and solidifies our connection with Krishna. The focus is to get on the spiritual path and to help others onto the path back to Godhead too. This movement is not a static movement. The word &lsquo;movement&rsquo; means that we are moving along the path of bhakti, and giving knowledge of Krishna consciousness to others.'
        ],
        [
          'To teach and encourage the sankirtana movement, congregational chanting of the holy name of God, as revealed in the teachings of Lord Sri Caitanya Mahaprabhu.',
          'The purpose is for everyone to come together to glorify Krishna and to chant the holy name, the Hare Krishna Maha-mantra &mdash; Hare Krishna Hare Krishna Krishna Krishna Hare Hare Hare Rama Hare Rama Rama Rama Hare Hare. The aim is to distribute the holy name to everyone, everywhere; and to help spirit souls reawaken love for God through congregational chanting of the holy name.'
        ],
        [
          'To erect for the members and for society at large a holy place of transcendental pastimes dedicated to the personality of Krishna.',
          'The purpose is to provide for the community a place where Krishna&rsquo;s glorifications manifest. This can be achieved by providing spiritual educational institutions to hear about Krishna and His pastimes. For example, establishing ISKCON temples/centers around the world, and at places of historical spiritual significance such as Sri Vrindavana and Sri Mayapur.'
        ],
        [
          'To bring the members closer together for the purpose of teaching a simpler, natural way of life.',
          'The purpose is to ensure that our day-to-day living does not take up too much of our time and energy, so that we have enough time to practice Krishna consciousness. Srila Prabhupada stressed simple living, and high thinking. The point is to simplify our life and make it less complicated in order to have more time to think about Krishna. This has inspired the development of self-sufficient farm communities.'
        ],
        [
          'With a view towards achieving the aforementioned purposes, to publish and distribute periodicals, magazines, books and other writings.',
          'The purpose is to provide educational material that can be easily accessed by people from all walks of life. Srila Prabhupada published volumes of books, several of which have been translated into more than 70 languages. The distribution of Srila Prabhupada&rsquo;s books has become one of the biggest activities for the Krishna consciousness movement. Srila Prabhupada encouraged us to write articles and essays for newsletters, write books, and so on, as this very process of meditating on Krishna and His qualities enables one to become purified. Distributing spiritual wisdom to society at large will enable people to realize the importance of Krishna consciousness.'
        ],
      ];
      $i = 0;
      foreach ($purposes as $p):
        $i++;
      ?>
      <div class="reveal" style="background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);border-left:4px solid var(--primary);">
        <div style="display:flex;align-items:flex-start;gap:var(--space-lg);">
          <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-weight:700;font-family:var(--font-heading);flex-shrink:0;"><?php echo $i; ?></div>
          <div>
            <p style="font-weight:600;color:var(--dark);margin-bottom:var(--space-sm);"><?php echo htmlspecialchars($p[0]); ?></p>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);"><?php echo htmlspecialchars($p[1]); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      
      <div class="reveal" style="background:var(--gradient-cta);border-radius:var(--radius-lg);padding:var(--space-xl);text-align:center;margin-top:var(--space-xl);">
        <p style="font-family:var(--font-subheading);font-style:italic;font-size:var(--font-size-lg);color:var(--white);line-height:1.8;">Ultimately, the &ldquo;7 Purposes of ISKCON&rdquo; are to help each one of us to become Krishna conscious and to be able to share this message of Krishna consciousness with others.</p>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
