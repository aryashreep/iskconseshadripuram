<?php
$pageTitle = 'ISKCON Vaishnavi Forum';
$metaDescription = 'ISKCON Vaishnavi Forum (IVF) at ISKCON Seshadripuram — empowering young women through Vedic wisdom, scripture study, music, craft, and personality development. Online and offline programs.';
$pageType = 'service';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/vaishnavi-forum.jpg');"></div>
  <div class="container">
    <h1 class="reveal">ISKCON Vaishnavi Forum (IVF)</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Communities</span><span>›</span><span>Vaishnavi Forum</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/vaishnavi-forum.jpg" alt="ISKCON Vaishnavi Forum" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🌸</span></div>
        <h2>Empowering Young Women Through Devotion</h2>
        <p style="color:var(--text-light);line-height:1.9;">ISKCON Vaishnavi Forum (IVF) in ISKCON Seshadripuram caters to young girls to help them understand and adapt a Krishna conscious lifestyle. We organize special sessions for young girls which will help them understand our scriptures and appreciate our culture. The IVF is an enjoyable and dynamic spiritual forum.</p>
        <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">In IVF, girls get to learn Vedic scriptures, including the holy Bhagavad Gita &amp; Srimad Bhagavatam. We help them with stories from the Puranas and practical tips which teach them the best practices so that they can live without stress and can feel the real joy of their lives. Every problem in our life has a solution in our scriptures and this forum is aimed to teach the girls to look at life from a spiritual perspective.</p>
        <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">We have both <strong>online</strong> and <strong>offline (temple premise)</strong> programs to enable girls to choose between attending in temple or at the comfort of their home when they live far from the temple.</p>
        <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">We invite all girls to join us and start your Krishna conscious life in a fun filled atmosphere.</p>
      </div>
    </div>

    <!-- Group Image -->
    <div class="reveal" style="margin-top:var(--space-xl);margin-bottom:var(--space-xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);">
      <img src="assets/images/banners/vaishnavi-forum-group.jpg" alt="ISKCON Vaishnavi Forum Group" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-xl);">Activities</h3>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-lg);">
        <?php
        $activities = [
          ['📖', 'Scripture Study', 'Courses in Bhagavad Gita, Srimad Bhagavatam and Prabhupada&rsquo;s books'],
          ['📖', 'Book Reading &amp; Japa', 'Guided book reading sessions and japa meditation'],
          ['🏞️', 'Spiritual Picnics', 'Outings in a Krishna conscious atmosphere'],
          ['💍', 'Jewellery &amp; Craft', 'Jewellery and dress making for the Lord, garland making, art and craft'],
          ['🎵', 'Music &amp; Dance', 'Bhajans, musical instruments, and dance programs'],
          ['💬', 'Counselling', 'Sessions for a balanced lifestyle and spiritual perspective'],
          ['🌟', 'Personality Development', 'Vedic culture-based personality development programs'],
          ['🖼️', 'Slide Shows', 'PowerPoint presentations on spiritual topics'],
          ['🎭', 'Festival Participation', 'Involvement in temple services, kirtan, and festival celebrations'],
        ];
        foreach ($activities as $a):
        ?>
        <div style="background:var(--cream);padding:var(--space-md) var(--space-lg);border-radius:var(--radius-lg);display:flex;align-items:center;gap:var(--space-md);">
          <div style="font-size:28px;flex-shrink:0;"><?php echo $a[0]; ?></div>
          <div>
            <h4 style="font-size:var(--font-size-sm);margin-bottom:2px;"><?php echo $a[1]; ?></h4>
            <p style="color:var(--text-light);font-size:var(--font-size-xs);margin:0;line-height:1.4;"><?php echo $a[2]; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);text-align:center;">
      <h3 style="margin-bottom:var(--space-lg);">Join Us</h3>
      <p style="color:var(--text-light);margin-bottom:var(--space-sm);">We have both online and offline programs!</p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg);max-width:500px;margin:var(--space-lg) auto 0;">
        <div style="background:var(--white);padding:var(--space-lg);border-radius:var(--radius-lg);">
          <h4>Offline (Temple)</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);">Sudhamayi Haripriya Devi Dasi</p>
          <p style="font-weight:600;color:var(--primary);">+91 9886014964</p>
        </div>
        <div style="background:var(--white);padding:var(--space-lg);border-radius:var(--radius-lg);">
          <h4>Online (Zoom)</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);">Jyotsana Madhavi Devi Dasi</p>
          <p style="font-weight:600;color:var(--primary);">+91 9845037920</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
