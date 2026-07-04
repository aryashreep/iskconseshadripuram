<?php
$pageTitle = 'Nilachal School of Music';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/music-school.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Nilachal School of Music</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Communities</span><span>›</span><span>Music School</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/music-school.jpg" alt="Nilachal School of Music" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🎵</span></div>
        <h2>Learn Bhajan &amp; Kirtan from Expert Teachers</h2>
        <p style="color:var(--text-light);line-height:1.9;">If you are looking forward to learning a musical instrument or singing, then Nilachal School of Music Classes are the best option. If you wish to turn your passion for music into your profession, then getting professionally trained in the field is necessary. Such classes are for the students who wish to do so. From learning a variety of musical instruments to training their voices professionally, there is nothing you cannot do at our music school.</p>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-xl);">Instruments & Classes We Offer</h3>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-xl);">
        <?php
        $instruments = [
          ['🎹', 'Harmonium', 'Learn the classic devotional instrument'],
          ['🥁', 'Kartal', 'Traditional hand cymbals for kirtan'],
          ['🥁', 'Mridangam (Sri Khol)', 'Bengal-style devotional drum'],
          ['🥁', 'Djembe', 'West African drum for kirtan'],
          ['🎤', 'Bhajan Vocal', 'Learn to sing bhajans properly'],
          ['🎵', 'Kirtan Leadership', 'Lead congregational chanting'],
        ];
        foreach ($instruments as $i):
        ?>
        <div style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);text-align:center;">
          <div style="font-size:48px;margin-bottom:var(--space-md);"><?php echo $i[0]; ?></div>
          <h4><?php echo $i[1]; ?></h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);"><?php echo $i[2]; ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-2xl);text-align:center;background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);">
      <p style="color:var(--text-light);margin-bottom:var(--space-md);">For more details about class timings, fees, and registration, please contact us.</p>
      <a href="contact" class="btn btn-primary"><i class="fas fa-guitar"></i> Enquire Now</a>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
