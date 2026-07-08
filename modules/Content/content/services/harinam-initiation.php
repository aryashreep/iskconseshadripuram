<?php
$pageTitle = 'Harinam Initiation';
$metaDescription = 'Harinam initiation at ISKCON Seshadripuram — formal entrance into disciplined Krishna consciousness. Learn about eligibility, required books, and the ISKCON Disciples Course (IDC).';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/harinam-init/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">Harinam Initiation</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="../siksha">Siksha</a><span>›</span><span>Harinam Initiation</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🔱</span></div>
        <h2>Formal Initiation into the Holy Name</h2>
        <p style="color:var(--text-light);line-height:1.9;">Harinam initiation (Harinam Diksha) is the formal entrance into the disciplined practice of Krishna consciousness. It marks a devotee's solemn commitment to chant the Hare Krishna Mahamantra under the guidance of a qualified spiritual master, following the regulative principles set forth by Srila Prabhupada and ISKCON's GBC.</p>
      </div>
    </div>

    <!-- Eligibility Requirements -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;"><i class="fas fa-clipboard-check"></i></div>
        <h3 style="margin:0;">Eligibility Requirements</h3>
      </div>
      <p style="color:var(--text-light);margin-bottom:var(--space-lg);font-weight:500;">To be eligible to take Harinam initiation, the candidate has to ensure the following:</p>
      <div style="display:flex;flex-direction:column;gap:var(--space-md);">
        <?php
        $requirements = [
          [
            'icon' => 'fa-utensils',
            'title' => 'Regulative Principles & Japa',
            'desc' => 'Following the 4 regulative principles (No meat-fish-egg-onion-garlic eating, no gambling, no intoxication and no illicit sex) and chanting 16 rounds of Japa daily for a minimum of the last 12 months without any fail.'
          ],
          [
            'icon' => 'fa-home',
            'title' => 'Taking Shelter',
            'desc' => 'Have taken shelter minimum 6 months before applying for initiation.'
          ],
          [
            'icon' => 'fa-hand-holding-heart',
            'title' => 'Continuous Service',
            'desc' => 'Serving continuously under or favorably connected to an ISKCON approved temple / Bhakti Vriksha / Namahatta / Gurukul etc. for a minimum of the last 12 months.'
          ],
          [
            'icon' => 'fa-graduation-cap',
            'title' => 'ISKCON Disciples Course',
            'desc' => 'Completion of the ISKCON Disciples Course (IDC).'
          ],
          [
            'icon' => 'fa-envelope',
            'title' => 'Recommendation Letter',
            'desc' => 'Official recommendation letter from the temple president / authorized official authority.'
          ],
          [
            'icon' => 'fa-file-alt',
            'title' => 'Document Submission',
            'desc' => 'Filled documents (recommendation letter, IDC certificate, Essay, Oath and bio-data) must be submitted to the local ISKCON authorized center prior to the initiation exam and interview.'
          ],
          [
            'icon' => 'fa-pencil-alt',
            'title' => 'Philosophical Questions',
            'desc' => 'Candidates have to write the answers to the GBC Harinam initiation philosophical questions. This can be done at your local ISKCON authorized center.'
          ],
          [
            'icon' => 'fa-microphone-alt',
            'title' => 'Oral Interview',
            'desc' => 'Appear for the oral interview test at the Guru\'s office before the deadline provided by the office.'
          ],
        ];
        foreach ($requirements as $r):
        ?>
        <div style="display:flex;gap:var(--space-md);align-items:flex-start;background:var(--white);padding:var(--space-md) var(--space-lg);border-radius:var(--radius-md);border-left:4px solid var(--primary);">
          <div style="width:40px;height:40px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:14px;flex-shrink:0;margin-top:2px;"><i class="fas <?php echo $r['icon']; ?>"></i></div>
          <div>
            <h4 style="font-size:var(--font-size-base);margin-bottom:4px;"><?php echo $r['title']; ?></h4>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;margin:0;"><?php echo $r['desc']; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Recommended Books -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--white);border-radius:var(--radius-lg);padding:var(--space-2xl);box-shadow:var(--shadow-md);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;"><i class="fas fa-book-open"></i></div>
        <h3 style="margin:0;">Recommended Books</h3>
      </div>
      <p style="color:var(--text-light);margin-bottom:var(--space-lg);">Candidates are encouraged to deeply study the following scriptures:</p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
        <?php
        $books = [
          ['Bhagavad Gita As It Is', '3 times', 'https://vedabase.io/en/library/bg/'],
          ['Srimad Bhagavatam (1st Canto)', '', 'https://vedabase.io/en/library/sb/'],
          ['Nectar of Instruction', '', 'https://vedabase.io/en/library/noi/'],
          ['Nectar of Devotion (Chapters 10–19)', '', 'https://vedabase.io/en/library/nod/'],
          ['Krishna Book (Chapter 46 to end)', '', 'https://vedabase.io/en/library/kb/'],
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
    </div>

    <!-- Recommended Courses -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-xl);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:20px;flex-shrink:0;"><i class="fas fa-chalkboard-teacher"></i></div>
        <h3 style="margin:0;">Recommended Course</h3>
      </div>
      <div style="display:flex;align-items:center;gap:var(--space-lg);background:var(--white);padding:var(--space-lg);border-radius:var(--radius-lg);border-left:4px solid var(--primary);">
        <div style="width:56px;height:56px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:24px;flex-shrink:0;"><i class="fas fa-graduation-cap"></i></div>
        <div>
          <h4 style="font-size:var(--font-size-base);margin-bottom:4px;">ISKCON Disciples Course (IDC)</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;margin:0;">A comprehensive training program that deepens understanding of guru tattva and prepares devotees for initiation. Completion of this course is a mandatory requirement for Harinam initiation.</p>
        </div>
        <a href="../courses/idc" style="text-decoration:none;flex-shrink:0;">
          <div style="display:inline-flex;align-items:center;gap:var(--space-sm);background:var(--gradient-primary);color:var(--white);padding:var(--space-sm) var(--space-md);border-radius:var(--radius-md);font-size:var(--font-size-sm);font-weight:500;transition:all var(--transition-fast);">
            Learn More <i class="fas fa-arrow-right" style="font-size:12px;"></i>
          </div>
        </a>
      </div>
    </div>

    <!-- CTA -->
    <div class="reveal" style="margin-top:var(--space-3xl);text-align:center;background:var(--gradient-cta);padding:var(--space-2xl);border-radius:var(--radius-lg);">
      <h3 style="color:var(--white);margin-bottom:var(--space-md);">Begin Your Journey</h3>
      <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--space-lg);max-width:600px;margin-left:auto;margin-right:auto;">If you are ready to commit to the disciplined practice of Krishna consciousness and take Harinam initiation, reach out to your local ISKCON center to begin the process.</p>
      <div style="display:flex;gap:var(--space-md);justify-content:center;flex-wrap:wrap;">
        <a href="../contact" class="btn btn-accent"><i class="fas fa-envelope"></i> Contact Us</a>
        <a href="../courses/idc" class="btn btn-primary" style="background:var(--white);color:var(--primary);"><i class="fas fa-graduation-cap"></i> IDC Course Details</a>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
