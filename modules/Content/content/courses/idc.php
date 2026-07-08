<?php
$pageTitle = 'ISKCON Disciples Course (IDC)';
$metaDescription = 'ISKCON Disciples Course (IDC) at ISKCON Seshadripuram — mandatory training for initiation covering guru tattva, guru selection, and discipleship. 6 sessions in English, Hindi, Kannada.';
include '../partials/header.php';
?>

<!-- Page Header -->
<section class="page-header">
  <div class="page-header-bg" style="background-image: url('../assets/images/banners/idc.jpg');"></div>
  <div class="container">
    <h1 class="reveal">ISKCON Disciples Course (IDC)</h1>
    <div class="breadcrumb reveal">
      <a href="/isjm/">Home</a><span>›</span><a href="courses/bhaktivedanta-education">Courses</a><span>›</span><span>IDC</span>
    </div>
  </div>
</section>

<!-- Course Details Section -->
<section class="page-content">
  <div class="container" style="max-width: 900px;">
    
    <!-- Intro description -->
    <div class="reveal" style="text-align: center; margin-bottom: var(--space-3xl);">
      <div class="section-divider"><span class="divider-icon">📜</span></div>
      <h2 style="margin-bottom: var(--space-md);">Deepening the Understanding of Guru Tattva</h2>
      <p style="color: var(--text-light); max-width: 800px; margin: 0 auto; line-height: 1.8; font-size: var(--font-size-base);">
        The ISKCON Disciples Course (IDC) is a training program that deepens devotees' understanding of guru tattva and guru padasraya within the multiple guru environment of ISKCON. Designed for new devotees preparing to take initiation in ISKCON, the course is also recommended for leaders, preachers, councilors, and educators in ISKCON.
      </p>
    </div>

    <!-- Origin & Design -->
    <div class="reveal" style="background: var(--cream-light); border-left: 5px solid var(--primary); padding: var(--space-xl); border-radius: var(--radius-md); margin-bottom: var(--space-3xl); box-shadow: var(--shadow-sm); font-size: var(--font-size-sm); line-height: 1.7; color: var(--text-dark);">
      The course was developed under the direction of the Guru Services Committee, with the combined efforts of leading educators in ISKCON. It is based on the teachings of Srila Prabhupada and current ISKCON Law, and references writings from the broader Gaudiya Vaishnava tradition.
    </div>

    <!-- Topics Covered -->
    <div class="reveal" style="background: var(--white); border-radius: var(--radius-lg); padding: var(--space-xl); box-shadow: var(--shadow-sm); margin-bottom: var(--space-3xl); border: 1px solid var(--border);">
      <h3 style="margin-bottom: var(--space-lg); color: var(--text-dark); text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
        <i class="fas fa-list-ul" style="color: var(--primary);"></i> Topics Covered in this Course
      </h3>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
        <?php
        $topics = [
          'Guru-tattva and the Guru-parampara System',
          'Srila Prabhupada: ISKCON Founder Acarya and Preeminent Guru',
          'Types of Gurus',
          'Relationship between ISKCON Guru and ISKCON Authorities',
          'Gurus outside of ISKCON',
          'Guru-padasraya (taking shelter)',
          'Selecting a Guru',
          'Following Initiation Vows',
          'Guru-puja and Vyasa-puja',
          'Worship of ISKCON Gurus',
          'Guru-vapu and vani seva',
          'Guru-tyaga (rejection of guru)',
          'Developing Cooperative Relationships in a multi Guru environment'
        ];
        foreach ($topics as $t):
        ?>
        <div style="display: flex; gap: var(--space-sm); align-items: flex-start; background: var(--cream); padding: var(--space-sm) var(--space-md); border-radius: var(--radius-md);">
          <i class="fas fa-check" style="color: var(--primary); margin-top: 4px; font-size: var(--font-size-xs);"></i>
          <span style="color: var(--text-dark); font-size: var(--font-size-sm); line-height: 1.5; font-weight: 500;"><?php echo $t; ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Quick Facts Grid -->
    <div class="reveal" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-3xl);">
      <div style="background: var(--white); border-radius: var(--radius-lg); padding: var(--space-lg); text-align: center; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
        <i class="far fa-clock" style="font-size: 28px; color: var(--primary); margin-bottom: 8px;"></i>
        <h4 style="margin: 0 0 4px 0; color: var(--text-dark);">Class Duration</h4>
        <p style="color: var(--text-light); font-size: var(--font-size-sm); margin: 0; line-height: 1.6;">
          6 sessions (2 hours per session, mostly conducted on both Saturday and Sunday)
        </p>
      </div>
      <div style="background: var(--white); border-radius: var(--radius-lg); padding: var(--space-lg); text-align: center; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
        <i class="fas fa-language" style="font-size: 28px; color: var(--primary); margin-bottom: 8px;"></i>
        <h4 style="margin: 0 0 4px 0; color: var(--text-dark);">Languages Offered</h4>
        <p style="color: var(--text-light); font-size: var(--font-size-sm); margin: 0; line-height: 1.6;">
          English, Hindi, Kannada
        </p>
      </div>
    </div>

    <!-- Contact & Bottom CTA -->
    <div class="reveal" style="text-align: center; background: var(--gradient-cta); padding: var(--space-2xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); color: var(--white);">
      <h3 style="color: var(--white); margin-bottom: var(--space-md); font-size: var(--font-size-xl);">Register or Ask Questions</h3>
      <p style="color: rgba(255,255,255,0.9); margin-bottom: var(--space-xl); max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.7;">
        Completion of the IDC is a mandatory requirement for initiation. Contact us to find out about upcoming batches.
      </p>
      
      <div style="display: flex; gap: var(--space-lg); justify-content: center; flex-wrap: wrap; margin-bottom: var(--space-xl);">
        <a href="mailto:bihecourses@gmail.com" class="btn btn-primary" style="background: var(--white); color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
          <i class="fas fa-envelope"></i> bihecourses@gmail.com
        </a>
        <a href="tel:+919538382299" class="btn btn-primary" style="background: var(--white); color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
          <i class="fas fa-phone-alt"></i> +91 9538382299
        </a>
      </div>

      <div style="border-top: 1px solid rgba(255, 255, 255, 0.15); padding-top: var(--space-lg); display: flex; gap: var(--space-md); justify-content: center; flex-wrap: wrap;">
        <span style="font-size: var(--font-size-sm); color: rgba(255,255,255,0.85); align-self: center;">Other Sastric Courses:</span>
        <a href="courses/bhakti-shastri" class="btn btn-accent btn-sm">Bhakti Shastri</a>
        <a href="courses/bhakti-vaibhava" class="btn btn-accent btn-sm">Bhakti Vaibhava</a>
        <a href="courses/bhaktivedanta-education" class="btn btn-accent btn-sm">BIHE Main Page</a>
      </div>
    </div>

  </div>
</section>

<?php include '../partials/footer.php'; ?>
