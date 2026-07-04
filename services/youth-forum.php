<?php
$pageTitle = 'ISKCON Youth Forum';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/youth-forum.jpg');"></div>
  <div class="container">
    <h1 class="reveal">ISKCON Youth Forum (IYF)</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Communities</span><span>›</span><span>Youth Forum</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/youth-forum.jpg" alt="ISKCON Youth Forum" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <!-- Introduction -->
    <div class="reveal">
      <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🔥</span></div>
      <h2>Empowering Youth Through Vedic Wisdom</h2>
      <p style="color:var(--text-light);line-height:1.9;">ISKCON Youth Forum (IYF) envisions a society of dynamic young men, ready to live a life of values, culture and devotion to Krishna. Our mission is to empower everyone with knowledge of the Bhagavad Gita that helps to differentiate between truth and illusion; revive faith that is based on scientific principles, logic and self-realization.</p>
      <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">Spiritual life is often misunderstood as dry, rigid, blind submission, devoid of rational thought, denying enjoyment and a paper-theory that never works in practice. We offer a pleasant surprise to the skeptics and suspicious; new light and direction to the confused and frustrated; a meaningful lifestyle to the hip and cool; a genuine experience for the curious and contemporary spiritual seekers. The ever-fresh world of Krishna consciousness challenges one to draw the best of one&rsquo;s creativity and talent as an offering to Sri Krishna.</p>
    </div>

    <!-- Activities -->
    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Activities We Organize &amp; Train In</h3>
      <p style="text-align:center;color:var(--text-light);max-width:650px;margin:0 auto var(--space-xl);line-height:1.7;">We organize and train in a wide range of activities designed to engage the mind, body, and spirit.</p>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-lg);">
        <?php
        $activities = [
          ['📝', 'Workshops & Seminars', 'Philosophy, art and culture presentations'],
          ['🧘', 'Yoga Retreats', 'Getaway towards the inner self'],
          ['🏞️', 'Picnics & Outings', 'Trips to charming places seemingly lost in time'],
          ['<img src="assets/images/iskcon_logo.svg" alt="ISKCON" style="height:28px;width:auto;">', 'Mantra Meditation', 'Tips and techniques to keep the spirit active'],
          ['🎵', 'Music, Dance & Drama', 'For those unforgettable moments of cultural expression'],
          ['💬', 'Lifestyle Counseling', 'Balance pressures from all corners of life'],
          ['🎭', 'Culture & Etiquette', 'Do the right thing at the right time in the right place'],
          ['📊', 'Leadership & Management', 'Confidently and smartly go ahead in life'],
          ['🎤', 'Public Speaking', 'Debates and quizzes for words to make impact'],
          ['🌟', 'Personality Development', 'Character development to stand out of the crowd'],
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

    <!-- Now Is The Time -->
    <div class="reveal" style="margin-top:var(--space-3xl);text-align:center;padding:var(--space-2xl);background:var(--gradient-primary);border-radius:var(--radius-lg);">
      <h3 style="color:var(--white);margin-bottom:var(--space-lg);">Now Is The Time</h3>
      <p style="color:rgba(255,255,255,0.9);max-width:650px;margin:0 auto;line-height:1.8;font-size:var(--font-size-base);">The first aphorism of the Vedanta Sutra issues a clarion call: <em>athato brahma-jignyasa</em>. &ldquo;Therefore, now that you have got the human form of life, inquire into the Absolute Truth.&rdquo; To embark on this spiritual journey in the quest of undying happiness &mdash; what better time can there be other than youth, when we are full with energy, enthusiasm and determination? Now is the time to begin this internal revolution of uplifting our consciousness.</p>
    </div>

    <!-- Membership -->
    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Membership</h3>
      <p style="color:var(--text-light);text-align:center;max-width:650px;margin:0 auto;line-height:1.9;">Our members come from a wide spectrum of society &mdash; software engineers, doctors, project managers, scientists, academicians, collegians, businessmen, etc. We welcome all genuine seekers of Truth irrespective of background.</p>

      <div style="margin-top:var(--space-xl);display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);max-width:600px;margin-left:auto;margin-right:auto;">
        <?php
        $questions = [
          'How can I raise the quality of my life?',
          'What aspirations to aim for?',
          'What is life for? What is the purpose of my existence?',
          'Where did I come from? Where do I go from here?',
          'Where can I find everlasting happiness?',
          'Who is God? Is He controlling my life?',
          'Can the Vedas be true?',
          'Why good/bad things happen to bad/good people?',
          'How to combat recession?'
        ];
        foreach ($questions as $q):
        ?>
        <div style="background:var(--cream);padding:var(--space-sm) var(--space-md);border-radius:var(--radius-md);font-size:var(--font-size-xs);color:var(--text-light);display:flex;align-items:center;gap:var(--space-sm);">
          <span style="color:var(--primary);font-size:var(--font-size-base);">❓</span>
          <span><?php echo $q; ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="text-align:center;margin-top:var(--space-xl);padding:var(--space-lg);background:var(--cream);border-radius:var(--radius-lg);">
        <p style="font-size:var(--font-size-lg);font-weight:600;color:var(--primary);">If yes, you are already a member at heart!</p>
        <p style="color:var(--text-light);">Our fees: a sincere inquiry and an open mind. Meet us at any of our branches to enroll yourself.</p>
      </div>
    </div>

    <!-- Thought-provoking Seminars -->
    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Thought-provoking Seminars</h3>
      <p style="color:var(--text-light);text-align:center;max-width:650px;margin:0 auto var(--space-xl);line-height:1.9;">Contact us for monthly seminars on a wide range of topics based on the authentic teachings of Srimad Bhagavad Gita, Srimad Bhagavatam and other Vedic literature. Well-researched, prepared and presented by practitioners of the philosophy.</p>

      <h4 style="text-align:center;color:var(--primary);margin-bottom:var(--space-lg);">Discover Your Self &mdash; A 6-Session Course</h4>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-md);">
        <?php
        $seminars = [
          'Does God Exist',
          '3 Ways of Gaining Knowledge',
          'Are the Vedas Relevant in 21st Century',
          'Science of the Soul',
          'Substance and Shadow',
          'The Art of Re-Living',
          'Moulding Your Future',
          'Power of Habits and Prayers',
          'Curing the Cancer of the Mind',
          'Managing Your Emotions and Stress',
          'Developing the Right Personality',
          'Vedic Culture &ndash; The Glory of India',
          'The Yoga of Perfection',
        ];
        foreach ($seminars as $s):
        ?>
        <div style="background:var(--white);padding:var(--space-md);border-radius:var(--radius-md);box-shadow:var(--shadow-sm);text-align:center;border-left:3px solid var(--primary);">
          <span style="font-size:var(--font-size-sm);font-weight:500;color:var(--dark);"><?php echo $s; ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Hostel Facility -->
    <div class="reveal" style="margin-top:var(--space-3xl);padding:var(--space-2xl);background:var(--cream);border-radius:var(--radius-lg);">
      <div style="font-size:36px;text-align:center;margin-bottom:var(--space-md);">🏠</div>
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Hostel Facility</h3>
      <p style="color:var(--text-light);text-align:center;max-width:650px;margin:0 auto;line-height:1.9;"><strong>Bhaktivedanta Academy for Culture and Education (BACE)</strong> is a residential facility for boys where they intensify their spiritual activities, develop a cooperative spirit, and practice and inculcate noble values and discipline.</p>
    </div>

    <!-- Contact -->
    <div class="reveal" style="margin-top:var(--space-2xl);text-align:center;background:var(--gradient-cta);padding:var(--space-2xl);border-radius:var(--radius-lg);">
      <h3 style="color:var(--white);margin-bottom:var(--space-lg);">Get in Touch</h3>
      <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--space-lg);">For Youth Forum related queries, please contact us:</p>
      <p style="font-size:var(--font-size-lg);color:var(--accent);margin-bottom:var(--space-md);"><i class="fas fa-envelope"></i> iyfsjmbangalore@gmail.com</p>
      <p style="font-size:var(--font-size-lg);color:var(--accent);"><i class="fab fa-whatsapp"></i> +91 80886 32663</p>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
