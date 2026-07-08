<?php
$pageTitle = 'Life Membership';
$metaDescription = 'ISKCON Life Membership — become a life patron with benefits including temple stays worldwide, book sets, japa mala, and tax-exempt donation under Section 80G. Join 1 million+ members.';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/life-membership.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Life Membership</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Life Membership</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/life-membership.jpg" alt="ISKCON Life Membership" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🎫</span></div>
        <h2>Become a Life Patron of ISKCON</h2>
        <p style="color:var(--text-light);line-height:1.9;">The ISKCON Life Membership program was introduced in the early 1970s as an opportunity to become an integral part of the ISKCON family by His Divine Grace A.C. Bhaktivedanta Swami Prabhupada. He invited everyone across the world to take advantage of residing in ISKCON temples all over the world for a couple of days, attend spiritual programs and receive Krishna conscious association.</p>
        <p style="color:var(--text-light);line-height:1.9;">Presently, more than <strong>1 million life patrons</strong> are part of ISKCON&rsquo;s family worldwide.</p>
        <p style="color:var(--text-light);line-height:1.9;margin-top:var(--space-lg);">Your donation will help propagate Krishna consciousness all over the globe and will be used for the construction of Karnataka&rsquo;s grand Jagannath Mandir.</p>
      </div>
    </div>

    <h3 style="text-align:center;margin:var(--space-3xl) 0 var(--space-xl);">Benefits of Life Membership</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg);">
      <?php
      $benefits = [
        ['fa-temple', 'Temple Stay', 'Wherever guest house facilities are available, with prior confirmation, you will get 3 days of free stay and prasadam once a year.'],
        ['fa-id-card', 'Hologram Card', 'A centrally issued hologram card with your photo and patron number along with an updated list of ISKCON centers worldwide.'],
        ['fa-book', 'Book Set', 'A set of books by His Divine Grace A.C. Bhaktivedanta Swami Prabhupada, available in English or Hindi.'],
        ['fa-envelope', 'Invitations', 'Free invitations to weekly programs in homes around the world.'],
        ['fa-prayer-beads', 'Japa Mala', 'Receive a japa mala and bead bag to chant the Hare Krishna maha-mantra.'],
        ['fa-file-invoice', 'Tax Exemption', 'Your donation is tax exempt under Section 80-G of the Income Tax Act, 1961.'],
      ];
      foreach ($benefits as $b):
      ?>
      <div class="reveal" style="display:flex;gap:var(--space-md);padding:var(--space-lg);background:var(--cream);border-radius:var(--radius-lg);">
        <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas <?php echo $b[0]; ?>"></i></div>
        <div><h4 style="font-size:var(--font-size-sm);margin-bottom:4px;"><?php echo $b[1]; ?></h4><p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;margin:0;"><?php echo $b[2]; ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="reveal" style="text-align:center;margin-top:var(--space-3xl);background:var(--gradient-cta);padding:var(--space-2xl);border-radius:var(--radius-lg);">
      <h3 style="color:var(--white);margin-bottom:var(--space-md);">Once in a Lifetime Offer</h3>
      <p style="font-family:var(--font-heading);font-size:var(--font-size-4xl);color:var(--accent);font-weight:700;">₹55,555</p>
      <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--space-xl);">ISKCON Life Membership — A unit of ISKCON Bureau</p>
      <p style="color:rgba(255,255,255,0.7);font-size:var(--font-size-sm);margin-bottom:var(--space-md);">For online registration, please contact the temple office.</p>
      <a href="donate?type=general" class="btn btn-accent btn-lg"><i class="fas fa-hand-holding-heart"></i> Register Now</a>
    </div>

    <div class="reveal" style="margin-top:var(--space-2xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);">
      <h4 style="margin-bottom:var(--space-md);">Where is the Life Patron Office?</h4>
      <p style="color:var(--text-light);">The Life Patron program office is situated inside the temple courtyard. This is the main temple donation office. Besides Life Patron program donations, we also accept all other types of donations.</p>
      <h4 style="margin:var(--space-md) 0 var(--space-sm);">Grievances &amp; Support</h4>
      <p style="color:var(--text-light);font-size:var(--font-size-sm);">Level 1: Uddharini Radhe DD &mdash; <a href="mailto:donorcare@iskconbangalore.co.in" style="color:var(--primary);">donorcare@iskconbangalore.co.in</a></p>
      <p style="color:var(--text-light);font-size:var(--font-size-sm);">Level 2: Rakhal Krishna Das &mdash; <a href="mailto:rakhale@gmail.com" style="color:var(--primary);">rakhale@gmail.com</a> &nbsp;|&nbsp; Ph: <strong>+91 99860 77269</strong></p>
      <p style="color:var(--text-light);font-size:var(--font-size-sm);">Level 3: Madhusudan Hari Das &mdash; Ph: <strong>+91 98450 16108</strong> / <strong>+91 99450 00444</strong></p>
      <p style="margin-top:var(--space-md);font-size:var(--font-size-sm);">For the ISKCON Life Membership policy, please contact the temple office.</p>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
