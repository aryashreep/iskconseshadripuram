<?php
$pageTitle = 'Krishna Fun School';
$metaDescription = 'Krishna Fun School at ISKCON Bangalore — weekend spiritual classes for children ages 3-16. Learn Bhagavad Gita, shloka recitation, art, and Vedic values in a fun atmosphere.';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/krishna-fun-school.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Krishna Fun School</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Communities</span><span>›</span><span>Krishna Fun School</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/krishna-fun-school.jpg" alt="Krishna Fun School" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <!-- Introduction -->
    <div class="reveal">
      <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🎨</span></div>
      <h2>Weekend Fun with Spiritual Values</h2>
      <p style="color:var(--text-light);line-height:1.9;">Krishna Fun School (KFS) is a weekend program for children. Every parent has an earnest desire to give their child the best by fulfilling their physical, emotional, social and spiritual needs. Would you like to have your children learn about Vedic culture and life-long skills in a fun-filled, hands-on atmosphere? If yes, then KFS is just the right place for them.</p>
    </div>

    <!-- Parents' Concerns -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Worries of Parents</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);max-width:600px;margin:0 auto;">
        <?php
        $concerns = [
          'How do I provide the best things to my kid?',
          'How do I save my kid from bad habits and influences?',
          'How can I help my kid grow as a balanced person?',
          'How can my kid understand the value of our Vedic culture?',
          'How can I spend quality time with my kid?',
        ];
        foreach ($concerns as $c):
        ?>
        <div style="display:flex;align-items:center;gap:var(--space-sm);background:var(--white);padding:var(--space-sm) var(--space-md);border-radius:var(--radius-md);font-size:var(--font-size-sm);">
          <span style="color:var(--primary);">❓</span>
          <span><?php echo $c; ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <p style="text-align:center;margin-top:var(--space-xl);color:var(--primary);font-weight:600;font-size:var(--font-size-base);">If you are facing such concerns, then Krishna Fun School is the best answer &mdash; not just for you, but for your child as well!</p>
    </div>

    <!-- Need in Present Scenario -->
    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="margin-bottom:var(--space-lg);">Need of Krishna Fun School in Present Scenario</h3>
      <p style="color:var(--text-light);line-height:1.9;">Presently, we are living in a modern society. Depression, stress, violence, addictions and offending behaviour are some of the major problems. Now, children have less value and respect for their elders. So we, at ISKCON Seshadripuram, have taken the onus to train the children in character and values what the modern education system seriously lacks. Our special study programs are designed to bring that ethical value back. We believe that a spiritually guided kid is well-equipped to face all the difficulties and challenges of life with ease.</p>
    </div>

    <!-- What We Offer -->
    <div class="reveal" style="margin-top:var(--space-3xl);padding:var(--space-2xl);background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">What We Offer</h3>
      <p style="color:var(--text-light);line-height:1.9;">We at ISKCON Seshadripuram have been offering Sunday classes for children ages 3&ndash;16 for several years. Lessons are based on the Bhagavad Gita, Srimad Bhagavatam and other Vedic literatures. The Sunday School program is designed to give the children a rich experience of the Vedic culture &amp; knowledge based on the teachings of Srila Prabhupada and the previous acharyas.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-md);">The children are exposed to the Vedic knowledge and get to interact with like-minded children in a nourishing atmosphere. The children recite shlokas, learn Vaishnava songs, stories and activities based on Srimad Bhagavatam, poems for the little ones, art &amp; craft, games and much more. We have special programs on festivals where the little Vaishnavas perform skits, dance, put up stalls, engage in book distribution and much more.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-md);">The program equips children with lifelong skills &amp; values, which will enable them to make positive and outstanding contributions in all aspects of life as well as meaningful contributions to society.</p>
    </div>

    <!-- Krishna Playing Image -->
    <div class="reveal" style="margin-top:var(--space-xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);">
      <img src="assets/images/banners/krishna-playing.jpg" alt="Krishna - The Supreme Personality of Godhead" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <!-- What Children Do -->
    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-xl);">What Will Children Do?</h3>
      <p style="color:var(--text-light);text-align:center;max-width:650px;margin:0 auto var(--space-xl);line-height:1.7;">We have created a bunch of interesting activities for learning. Our activities include:</p>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:var(--space-md);">
        <?php
        $activities = [
          'Krishna Conscious Games',
          'Drawing &amp; Art',
          'Dance',
          'Drama',
          'Quiz',
          'Movies',
          'Shloka Recitation',
          'Krishna Conscious Lifestyle',
          'Seminars',
          'Vedic Knowledge',
          'Moral Education',
        ];
        foreach ($activities as $a):
        ?>
        <div style="text-align:center;background:var(--cream);padding:var(--space-md);border-radius:var(--radius-lg);">
          <div style="font-size:28px;margin-bottom:var(--space-sm);">⭐</div>
          <p style="font-size:var(--font-size-sm);font-weight:500;margin:0;"><?php echo $a; ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <p style="color:var(--text-light);text-align:center;max-width:650px;margin:var(--space-xl) auto 0;line-height:1.7;">In simple words, you can say that KFS is the much-needed solution for holistic education. With this, your child can grow with a strong character.</p>
      <p style="color:var(--text-light);text-align:center;max-width:650px;margin:var(--space-md) auto 0;line-height:1.7;">We also organise workshops time-to-time. Our workshops are meant to develop and encourage the basic values and innate talent of a child. We also arrange a gesture to cultivate the value and the habit of giving. We promote the value of sharing, kindness, and selfless concerns for others.</p>
    </div>

    <!-- Courses & Registration -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--gradient-cta);border-radius:var(--radius-lg);padding:var(--space-2xl);text-align:center;">
      <h3 style="color:var(--white);margin-bottom:var(--space-lg);">Courses Offered</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg);max-width:500px;margin:0 auto;">
        <div style="background:rgba(255,255,255,0.12);padding:var(--space-lg);border-radius:var(--radius-lg);">
          <div style="font-size:36px;margin-bottom:var(--space-sm);">🧒</div>
          <h4 style="color:var(--white);">Junior Group</h4>
          <p style="color:rgba(255,255,255,0.85);font-size:var(--font-size-sm);">Ages 3&ndash;9 years</p>
        </div>
        <div style="background:rgba(255,255,255,0.12);padding:var(--space-lg);border-radius:var(--radius-lg);">
          <div style="font-size:36px;margin-bottom:var(--space-sm);">👦</div>
          <h4 style="color:var(--white);">Senior Group</h4>
          <p style="color:rgba(255,255,255,0.85);font-size:var(--font-size-sm);">Ages 10&ndash;16 years</p>
        </div>
      </div>
      <p style="color:rgba(255,255,255,0.85);margin-top:var(--space-xl);">Mentor: <strong style="color:var(--accent);">HG Rasika Sakhi Devi Dasi</strong></p>
    </div>

    <div class="reveal" style="margin-top:var(--space-xl);text-align:center;background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);">
      <h4 style="margin-bottom:var(--space-md);">Registration</h4>
      <p style="color:var(--text-light);max-width:600px;margin:0 auto;line-height:1.7;">All registrations are on a first-come first-served basis as we have limited spaces. By registering, you commit to your child/children attending Krishna Fun School on most weeks so that they fully benefit from the sessions.</p>
      <p style="margin-top:var(--space-md);font-weight:600;color:var(--primary);"><i class="fas fa-phone-alt"></i> +91 99860 77269</p>
      <p style="color:var(--text-light);font-size:var(--font-size-sm);margin-top:var(--space-sm);">For registration, please contact the temple office.</p>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
