<?php
$pageTitle = 'Temple Schedule';
include '../partials/header.php';
require_once '../config.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/schedule/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">Temple Schedule</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="about">About Us</a><span>›</span><span>Temple Schedule</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:800px;">
    <div style="text-align:center;margin-bottom:var(--space-3xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon">⏰</span></div>
      <h2>Daily Temple Timings</h2>
      <p style="color:var(--text-light);">Timings are subject to change during festivals and special occasions. Please check for updates.</p>
    </div>

    <div class="reveal" style="background:var(--white);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);">
      <div style="background:var(--gradient-primary);padding:var(--space-lg);text-align:center;">
        <h3 style="color:var(--white);font-size:var(--font-size-lg);margin:0;">Daily Schedule</h3>
        <p style="color:rgba(255,255,255,0.8);font-size:var(--font-size-sm);margin-top:4px;">Temple open from 5:00 AM to 8:30 PM</p>
      </div>
      <div style="padding:var(--space-xl);">
        <?php foreach ($TEMPLE_SCHEDULE as $i => $item): ?>
        <div class="reveal" style="display:flex;align-items:center;gap:var(--space-lg);padding:var(--space-md) 0;border-bottom:<?php echo $i < count($TEMPLE_SCHEDULE) - 1 ? '1px solid var(--cream)' : 'none'; ?>;">
          <div style="min-width:120px;text-align:center;">
            <span style="font-family:var(--font-heading);font-size:var(--font-size-lg);font-weight:600;color:var(--primary);"><?php echo $item['time']; ?></span>
          </div>
          <div style="flex:1;">
            <strong style="color:var(--dark);"><?php echo $item['activity']; ?></strong>
            <p style="color:var(--text-light);font-size:var(--font-size-sm);margin:2px 0 0;"><?php echo $item['desc']; ?></p>
          </div>
          <div style="width:8px;height:8px;background:var(--gradient-primary);border-radius:50%;flex-shrink:0;"></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-2xl);background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);text-align:center;">
      <p style="color:var(--text-light);">For special darshan timings during festivals, please contact the temple office.</p>
      <a href="contact" class="btn btn-primary" style="margin-top:var(--space-md);"><i class="fas fa-envelope"></i> Contact Us</a>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
