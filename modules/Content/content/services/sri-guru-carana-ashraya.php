<?php
$pageTitle = 'Sri Guru Carana Ashraya';
$metaDescription = 'Sri Guru Carana Ashraya — Siksha Level 6 at ISKCON Seshadripuram. Preparing for diksha initiation by selecting an authorized spiritual master. Study Bhagavad Gita, Srimad Bhagavatam and more.';
$pageType = 'service';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/guru-ashraya/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">Sri Guru Carana Ashraya</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="../siksha">Siksha</a><span>›</span><span>Sri Guru Carana Ashraya</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🪷</span></div>
        <h2>Siksha Level 6 — Taking Shelter of the Spiritual Master</h2>
        <p style="color:var(--text-light);line-height:1.9;">Sri Guru Carana Ashraya is the sixth step in the Bhakti Steps program, where a devotee formally prepares to take shelter of a qualified spiritual master (diksha guru). After establishing a steady practice at the Srila Prabhupada Ashraya level, the candidate begins the process of selecting an ISKCON authorized diksha guru and preparing for Harinam initiation.</p>
      </div>
    </div>

    <!-- Recommended Practices -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;"><i class="fas fa-tasks"></i></div>
        <h3 style="margin:0;">Recommended Practices</h3>
      </div>
      <div style="display:flex;flex-direction:column;gap:var(--space-md);">
        <?php
        $practices = [
          [
            'icon' => 'fa-user-check',
            'title' => 'Selecting a Diksha Guru',
            'desc' => 'After a minimum of six months of following Srila Prabhupada Ashraya standards, one may select an ISKCON authorized diksha guru from amongst one\'s established siksa gurus. One may also take as much time as required to make this selection.'
          ],
          [
            'icon' => 'fa-brain',
            'title' => 'Intelligent Choice',
            'desc' => 'It is the responsibility of candidates to choose a guru by the exercise of their own intelligence.'
          ],
          [
            'icon' => 'fa-bell',
            'title' => 'Notify Authority',
            'desc' => 'Notify the local Temple President or relevant authority of your intention.'
          ],
          [
            'icon' => 'fa-file-signature',
            'title' => 'Written Examination',
            'desc' => 'Take a written examination issued by ISKCON authority.'
          ],
          [
            'icon' => 'fa-envelope-open-text',
            'title' => 'Recommendation Letter',
            'desc' => 'Receive a formal letter of recommendation from an ISKCON authority.'
          ],
          [
            'icon' => 'fa-hand-paper',
            'title' => 'Guru\'s Permission',
            'desc' => 'Receive permission from the selected guru.'
          ],
        ];
        foreach ($practices as $p):
        ?>
        <div style="display:flex;gap:var(--space-md);align-items:flex-start;background:var(--white);padding:var(--space-md) var(--space-lg);border-radius:var(--radius-md);border-left:4px solid var(--primary);">
          <div style="width:40px;height:40px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:14px;flex-shrink:0;margin-top:2px;"><i class="fas <?php echo $p['icon']; ?>"></i></div>
          <div>
            <h4 style="font-size:var(--font-size-base);margin-bottom:4px;"><?php echo $p['title']; ?></h4>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;margin:0;"><?php echo $p['desc']; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Additional Recommendations -->
    <div class="reveal" style="margin-top:var(--space-xl);display:grid;grid-template-columns:1fr 1fr 1fr;gap:var(--space-md);">
      <?php
      $recs = [
        ['fa-question-circle', '15 Philosophical Q & A', 'Recommended to read the 15 Philosophical Questions & Answers.'],
        ['fa-ban', '10 Offences to the Holy Name', 'Recommended to study and avoid the 10 offences to the Holy Name.'],
        ['fa-handshake', 'Vaishnava Etiquette', 'Recommended to follow proper Vaishnava etiquette.'],
      ];
      foreach ($recs as $r):
      ?>
      <div style="background:var(--white);padding:var(--space-lg);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);text-align:center;border-top:3px solid var(--primary);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:18px;margin:0 auto var(--space-md);"><i class="fas <?php echo $r[0]; ?>"></i></div>
        <h4 style="font-size:var(--font-size-sm);margin-bottom:6px;"><?php echo $r[1]; ?></h4>
        <p style="color:var(--text-light);font-size:var(--font-size-xs);line-height:1.6;margin:0;"><?php echo $r[2]; ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Recommended Books -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--white);border-radius:var(--radius-lg);padding:var(--space-2xl);box-shadow:var(--shadow-md);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;"><i class="fas fa-book-open"></i></div>
        <h3 style="margin:0;">Recommended Books</h3>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
        <?php
        $books = [
          ['Bhagavad Gita As It Is', 'Two times', 'https://vedabase.io/en/library/bg/'],
          ['Srimad Bhagavatam (1st Canto)', '', 'https://vedabase.io/en/library/sb/'],
          ['Teachings of Lord Caitanya', '', 'https://vedabase.io/en/library/tlc/'],
          ['Path of Perfection', '', 'https://vedabase.io/en/library/pop/'],
          ['Nectar of Devotion (Chapters 1–9)', '', 'https://vedabase.io/en/library/nod/'],
          ['Krishna Book (Chapters 1–45)', '', 'https://vedabase.io/en/library/kb/'],
        ];
        foreach ($books as $b):
        ?>
        <a href="<?php echo $b[2]; ?>" target="_blank" rel="noopener" style="text-decoration:none;color:inherit;">
          <div style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);transition:all var(--transition-fast);border:2px solid transparent;">
            <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas fa-book"></i></div>
            <div style="flex:1;">
              <h4 style="font-size:var(--font-size-sm);margin-bottom:2px;"><?php echo $b[0]; ?></h4>
              <?php if ($b[1]): ?>
              <p style="color:var(--primary);font-size:var(--font-size-xs);margin:0;font-weight:500;">Read <?php echo $b[1]; ?></p>
              <?php endif; ?>
            </div>
            <div style="color:var(--primary);font-size:12px;flex-shrink:0;"><i class="fas fa-external-link-alt"></i></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Additional Resources -->
      <div style="margin-top:var(--space-xl);display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
        <a href="https://www.vaishnavaetiquette.com/" target="_blank" rel="noopener" style="text-decoration:none;color:inherit;">
          <div style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);border:2px solid transparent;transition:all var(--transition-fast);">
            <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas fa-globe"></i></div>
            <div style="flex:1;">
              <h4 style="font-size:var(--font-size-sm);margin-bottom:2px;">Advanced Vaishnava Etiquette</h4>
              <p style="color:var(--text-light);font-size:var(--font-size-xs);margin:0;">Online Seminar</p>
            </div>
            <div style="color:var(--primary);font-size:12px;flex-shrink:0;"><i class="fas fa-external-link-alt"></i></div>
          </div>
        </a>
        <a href="https://www.radha.name/digital-books/courses-seminars-study/holy-name-seminar" target="_blank" rel="noopener" style="text-decoration:none;color:inherit;">
          <div style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);border:2px solid transparent;transition:all var(--transition-fast);">
            <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas fa-globe"></i></div>
            <div style="flex:1;">
              <h4 style="font-size:var(--font-size-sm);margin-bottom:2px;">Holy Name Seminar</h4>
              <p style="color:var(--text-light);font-size:var(--font-size-xs);margin:0;">Online Resource</p>
            </div>
            <div style="color:var(--primary);font-size:12px;flex-shrink:0;"><i class="fas fa-external-link-alt"></i></div>
          </div>
        </a>
      </div>
    </div>

    <!-- Recommended Songs -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;"><i class="fas fa-music"></i></div>
        <h3 style="margin:0;">Recommended Songs</h3>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);max-width:600px;margin:0 auto;">
        <?php
        $songs = [
          'Guru Pranama Mantra',
          'Gurudev Kripa Bindu Diya',
          'Sri Siksastakam',
          'Damodarastakam',
        ];
        foreach ($songs as $s):
        ?>
        <div style="display:flex;align-items:center;gap:var(--space-md);background:var(--white);padding:var(--space-md) var(--space-lg);border-radius:var(--radius-md);">
          <div style="width:36px;height:36px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:14px;flex-shrink:0;"><i class="fas fa-play"></i></div>
          <span style="font-weight:500;font-size:var(--font-size-sm);"><?php echo $s; ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Audio Lectures -->
    <div class="reveal" style="margin-top:var(--space-xl);background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border-left:4px solid var(--primary);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-md);">
        <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:18px;flex-shrink:0;"><i class="fas fa-podcast"></i></div>
        <div>
          <h4 style="margin-bottom:2px;">Recommended Audio Lectures</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);margin:0;">Selected audio recordings of Srila Prabhupada and senior leaders of ISKCON for daily hearing.</p>
        </div>
      </div>
    </div>

    <!-- Recommended Courses -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;"><i class="fas fa-graduation-cap"></i></div>
        <h3 style="margin:0;">Recommended Courses</h3>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
        <?php
        $courses = [
          ['fa-book', 'Bhakti Shastri', '2 years'],
          ['fa-book', 'Bhakti Vaibhav', '2–3 years'],
          ['fa-stream', 'Overview of CC', '20 episodes'],
          ['fa-layer-group', 'Bhakti Vedanta', '2–3 years'],
        ];
        foreach ($courses as $c):
        ?>
        <div style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--white);border-radius:var(--radius-md);border-left:4px solid var(--primary);">
          <div style="width:40px;height:40px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:14px;flex-shrink:0;"><i class="fas <?php echo $c[0]; ?>"></i></div>
          <div style="flex:1;">
            <h4 style="font-size:var(--font-size-sm);margin-bottom:2px;"><?php echo $c[1]; ?></h4>
            <p style="color:var(--primary);font-size:var(--font-size-xs);margin:0;font-weight:500;"><?php echo $c[2]; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- CTA -->
    <div class="reveal" style="margin-top:var(--space-3xl);text-align:center;background:var(--gradient-cta);padding:var(--space-2xl);border-radius:var(--radius-lg);">
      <h3 style="color:var(--white);margin-bottom:var(--space-md);">Continue Your Spiritual Journey</h3>
      <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--space-lg);max-width:600px;margin-left:auto;margin-right:auto;">Having reached Sri Guru Carana Ashraya, you are now preparing for one of the most important commitments in your spiritual life. Contact your local ISKCON center for guidance on the next steps.</p>
      <div style="display:flex;gap:var(--space-md);justify-content:center;flex-wrap:wrap;">
        <a href="../contact" class="btn btn-accent"><i class="fas fa-envelope"></i> Contact Us</a>
        <a href="../harinam-initiation" class="btn btn-primary" style="background:var(--white);color:var(--primary);"><i class="fas fa-om"></i> Harinam Initiation</a>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
