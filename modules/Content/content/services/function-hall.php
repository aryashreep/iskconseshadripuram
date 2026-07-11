<?php
$pageTitle = 'Function Hall';
$metaDescription = 'Host weddings, receptions, ceremonies, and family events at ISKCON The Palace Temple of Lord Jagannath in Bangalore. Multipurpose halls with catering, rooms, and sacred ambiance.';
$pageType = 'service';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/function-hall.png');"></div>
  <div class="container">
    <h1 class="reveal">Function Hall</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Function Hall</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">

    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/function-hall.png" alt="ISKCON Function Hall" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🏛️</span></div>
        <h2>Celebrate Your Special Occasions at the Temple</h2>
        <p style="color:var(--text-light);line-height:1.9;">ISKCON The Palace Temple of Lord Jagannath offers beautiful, sacred spaces for hosting all types of family events and celebrations. Our multipurpose halls provide a spiritually uplifting environment for your special occasions within the temple campus.</p>
      </div>
    </div>

    <!-- Facilities Grid -->
    <div class="reveal" style="margin-top:var(--space-3xl);display:grid;grid-template-columns:1fr 1fr;gap:var(--space-xl);">
      <div style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-lg);margin-bottom:var(--space-md);"><i class="fas fa-temple"></i></div>
        <h4>Temple Hall</h4>
        <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">The most sacred and special space within the temple complex is the main temple hall, aesthetically clad in white marble, which houses the presiding Deities Sri Sri Jagannath Baladev Subhadra Maharani, Sri Sri Radha Madhav, and Sri Sri Gaura Nitai.</p>
      </div>
      <div style="background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-lg);margin-bottom:var(--space-md);"><i class="fas fa-book-open"></i></div>
        <h4>Book &amp; Gift Shop</h4>
        <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">The Book Shop houses several books which provide deep spiritual insights and answers to life's questions and topics related to self-realization. The timeless Vedic scriptures Bhagavad-gita and Srimad Bhagavatam are also available for purchase and gifting.</p>
      </div>
    </div>

    <!-- Multipurpose Halls Section -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-2xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Multipurpose Halls</h3>
      <p style="color:var(--text-light);text-align:center;max-width:650px;margin:0 auto var(--space-xl);line-height:1.8;">
        Two multipurpose halls are available within the campus of ISKCON The Palace Temple of Lord Jagannath for hosting all types of family events.
      </p>

      <h4 style="text-align:center;margin-bottom:var(--space-lg);">Events We Host</h4>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);max-width:600px;margin:0 auto;">
        <?php
        $events = [
          ['fa-ring', 'Vedic Marriage'],
          ['fa-gem', 'Ring Ceremony'],
          ['fa-heart', 'Marriage Anniversary with Special Aarti'],
          ['fa-birthday-cake', 'Birthday Parties with Special Facilities'],
          ['fa-child', 'Anna Prashan Sanskar'],
          ['fa-fire', 'Homa, Pujaris &amp; Kirtan'],
        ];
        foreach ($events as $e):
        ?>
        <div style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-sm) var(--space-md);background:var(--white);border-radius:var(--radius-md);">
          <i class="fas <?php echo $e[0]; ?>" style="color:var(--primary);width:18px;text-align:center;"></i>
          <span style="font-size:var(--font-size-sm);"><?php echo $e[1]; ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Facilities Included -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--white);border-radius:var(--radius-lg);padding:var(--space-2xl);box-shadow:var(--shadow-md);">
      <h3 style="text-align:center;margin-bottom:var(--space-xl);">Facilities Included</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg);">
        <div style="display:flex;gap:var(--space-md);padding:var(--space-lg);background:var(--cream);border-radius:var(--radius-lg);">
          <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas fa-utensils"></i></div>
          <div><h4 style="font-size:var(--font-size-sm);margin-bottom:4px;">Breakfast &amp; Lunch</h4><p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;margin:0;">Catering for up to 40 people</p></div>
        </div>
        <div style="display:flex;gap:var(--space-md);padding:var(--space-lg);background:var(--cream);border-radius:var(--radius-lg);">
          <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas fa-bed"></i></div>
          <div><h4 style="font-size:var(--font-size-sm);margin-bottom:4px;">3 Rooms</h4><p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;margin:0;">With attached bathroom facility for half-day</p></div>
        </div>
        <div style="display:flex;gap:var(--space-md);padding:var(--space-lg);background:var(--cream);border-radius:var(--radius-lg);">
          <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas fa-gift"></i></div>
          <div><h4 style="font-size:var(--font-size-sm);margin-bottom:4px;">Tambulam Packets</h4><p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;margin:0;">For guests — up to 50 packs</p></div>
        </div>
        <div style="display:flex;gap:var(--space-md);padding:var(--space-lg);background:var(--cream);border-radius:var(--radius-lg);">
          <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas fa-book"></i></div>
          <div><h4 style="font-size:var(--font-size-sm);margin-bottom:4px;">Return Gift</h4><p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;margin:0;">Jagannath photo or Bhagavad Gita book in your language</p></div>
        </div>
      </div>
    </div>

    <!-- Govinda's Prasadam Section -->
    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);text-align:center;">
      <div style="width:56px;height:56px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-md);color:var(--white);font-size:var(--font-size-lg);"><i class="fas fa-leaf"></i></div>
      <h3 style="margin-bottom:var(--space-sm);">Govinda's Prasadam &amp; Snack Counters</h3>
      <p style="color:var(--text-light);max-width:650px;margin:0 auto;line-height:1.8;">
        One can enjoy karma-free food, both physically and spiritually nourishing, at Govinda's restaurant. Prasadam at Govinda's is prepared from carefully chosen ingredients and offered to Lord Krishna, which makes you healthy and peaceful.
      </p>
    </div>

    <!-- Contact CTA -->
    <div class="reveal" style="margin-top:var(--space-3xl);text-align:center;background:var(--gradient-cta);padding:var(--space-2xl);border-radius:var(--radius-lg);">
      <h3 style="color:var(--white);margin-bottom:var(--space-lg);">Book Your Event Today</h3>
      <p style="color:rgba(255,255,255,0.85);max-width:550px;margin:0 auto var(--space-xl);line-height:1.8;">
        For more details about our facilities, pricing, and booking availability, please get in touch with us.
      </p>
      <a href="contact" class="btn btn-accent btn-lg"><i class="fas fa-calendar-check"></i> Contact Us</a>
    </div>

  </div>
</section>

<?php include '../partials/footer.php'; ?>
