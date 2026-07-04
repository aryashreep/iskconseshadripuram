<?php
$pageTitle = 'Siksha - The Bhakti Steps';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/siksha.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Siksha – The Bhakti Steps</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><span>Siksha</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">

<style>
/* ===== Bhakti Steps Timeline ===== */
.bhakti-timeline {
  position: relative;
  padding: 0 0 0 70px;
  max-width: 650px;
  margin: 0 auto;
}

.bhakti-timeline::before {
  content: '';
  position: absolute;
  left: 28px;
  top: 10px;
  bottom: 10px;
  width: 3px;
  background: linear-gradient(to bottom, var(--primary-light) 0%, var(--primary) 40%, var(--primary-dark) 70%, var(--maroon) 100%);
  border-radius: 2px;
  opacity: 0.6;
}

.timeline-step {
  position: relative;
  margin-bottom: var(--space-lg);
}

.timeline-step:last-child {
  margin-bottom: 0;
}

.timeline-node {
  position: absolute;
  left: -70px;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 56px;
  background: var(--gradient-primary);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-weight: 700;
  font-size: var(--font-size-lg);
  font-family: var(--font-heading);
  z-index: 2;
  border: 3px solid var(--cream-light);
  box-shadow: 0 0 0 3px var(--primary), 0 4px 15px rgba(200, 107, 31, 0.25);
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.timeline-step:hover .timeline-node {
  transform: translateY(-50%) scale(1.15);
  box-shadow: 0 0 0 3px var(--accent), 0 0 30px rgba(212, 175, 55, 0.35);
  background: var(--gradient-accent);
}

.timeline-step.active .timeline-node {
  background: var(--gradient-accent);
  box-shadow: 0 0 0 3px var(--accent), 0 0 30px rgba(212, 175, 55, 0.3);
}

.timeline-card {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  background: var(--cream);
  padding: var(--space-lg) var(--space-xl);
  border-radius: var(--radius-lg);
  border-left: 4px solid var(--primary);
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
  cursor: pointer;
  position: relative;
}

.timeline-card::after {
  content: '';
  position: absolute;
  left: -10px;
  top: 50%;
  transform: translateY(-50%);
  border: 10px solid transparent;
  border-right-color: var(--cream);
  border-left: none;
  transition: all 0.35s ease;
}

.timeline-step:hover .timeline-card {
  background: var(--white);
  box-shadow: var(--shadow-md);
  transform: translateX(6px);
  border-left-color: var(--accent);
}

.timeline-step:hover .timeline-card::after {
  border-right-color: var(--white);
}

.timeline-step.active .timeline-card {
  background: var(--white);
  border-left-color: var(--accent);
  box-shadow: var(--shadow-sm);
}

.timeline-step.active .timeline-card::after {
  border-right-color: var(--white);
}

.timeline-icon {
  width: 44px;
  height: 44px;
  background: var(--gradient-primary);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-size: var(--font-size-base);
  flex-shrink: 0;
  transition: all 0.35s ease;
}

.timeline-step:hover .timeline-icon {
  background: var(--gradient-accent);
  transform: rotate(360deg);
}

.timeline-content {
  flex: 1;
  min-width: 0;
}

.timeline-content h4 {
  font-size: var(--font-size-base);
  margin-bottom: 2px;
  transition: color var(--transition-fast);
}

.timeline-step:hover .timeline-content h4 {
  color: var(--primary);
}

.timeline-content p {
  color: var(--text-light);
  font-size: var(--font-size-sm);
  margin: 0;
  line-height: 1.5;
}

.timeline-arrow {
  color: var(--primary);
  font-size: 14px;
  flex-shrink: 0;
  opacity: 0.5;
  transition: all 0.35s ease;
}

.timeline-step:hover .timeline-arrow {
  opacity: 1;
  transform: translateX(4px);
  color: var(--accent);
}

/* Pulse glow on the current step node */
.timeline-step.active .timeline-node::before {
  content: '';
  position: absolute;
  inset: -6px;
  border-radius: var(--radius-full);
  border: 2px solid var(--accent);
  animation: pulse-ring 2s ease infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.1); opacity: 0.2; }
  100% { transform: scale(1); opacity: 0.6; }
}

/* Responsive timeline */
@media (max-width: 600px) {
  .bhakti-timeline {
    padding-left: 50px;
  }
  .bhakti-timeline::before {
    left: 18px;
  }
  .timeline-node {
    left: -54px;
    width: 44px;
    height: 44px;
    font-size: var(--font-size-base);
  }
  .timeline-card {
    padding: var(--space-md);
  }
  .timeline-card::after {
    left: -8px;
    border-width: 8px;
  }
  .timeline-icon {
    width: 36px;
    height: 36px;
    font-size: var(--font-size-sm);
  }
  .timeline-content h4 {
    font-size: var(--font-size-sm);
  }
  .timeline-content p {
    font-size: var(--font-size-xs);
  }
}
</style>
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/siksha.jpg" alt="Siksha - The Bhakti Steps" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🪜</span></div>
        <h2>Systematic Spiritual Progression</h2>
        <p style="color:var(--text-light);line-height:1.9;">The Siksha (Bhakti Steps) program is a system for encouraging devotees by recognizing their chanting and spiritual standards. It is a way to help devotees consolidate and increase their spiritual practices, and offers a system to identify those who are serious about advancing in Krishna consciousness. Supported by an ISKCON GBC resolution since 1997.</p>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-xl);">The Bhakti Steps</h3>
      <div class="bhakti-timeline">
        <?php
        $steps = [
          ['Sraddhavan', 'services/sraddhavan', 'One who has faith — the first step of spiritual life.', 'fa-star'],
          ['Krishna Sevaka', 'services/krishna-sevaka', 'One who renders service to Krishna — actively engaging in devotional practices.', 'fa-hands-helping'],
          ['Krishna Sadhaka', 'services/krishna-sadhaka', 'One who practices sadhana — committed to daily spiritual disciplines.', 'fa-fire'],
          ['Krishna Upasaka', 'services/krishna-upasaka', 'One who worships Krishna — deeper commitment to deity worship and rituals.', 'fa-hands-praying'],
          ['Srila Prabhupada Ashraya', 'services/srila-prabhupada-ashraya', 'Taking shelter of Srila Prabhupada — committing to 16 rounds and 4 regulative principles.', 'fa-om'],
          ['Sri Guru Carana Ashraya', 'services/sri-guru-carana-ashraya', 'Taking shelter of the spiritual master — preparing for initiation.', 'fa-user-graduate'],
          ['Harinam Initiation', 'services/harinam-initiation', 'Formal initiation into the Hare Krishna mantra from a spiritual master.', 'fa-bell'],
        ];
        foreach ($steps as $i => $s):
        ?>
        <a href="<?php echo $s[1]; ?>" class="timeline-step" style="text-decoration:none;color:inherit;display:block;">
          <div class="timeline-node"><?php echo $i + 1; ?></div>
          <div class="timeline-card">
            <div class="timeline-icon"><i class="fas <?php echo $s[3]; ?>"></i></div>
            <div class="timeline-content">
              <h4><?php echo $s[0]; ?></h4>
              <p><?php echo $s[2]; ?></p>
            </div>
            <div class="timeline-arrow"><i class="fas fa-chevron-right"></i></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--white);border-radius:var(--radius-lg);padding:var(--space-2xl);box-shadow:var(--shadow-md);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Benefits of the Bhakti Steps Program</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-lg);">
        <?php
        $benefits = [
          ['fa-check-circle', 'Recognition', 'Congregational members feel accepted as practicing devotees and that they are making progress.'],
          ['fa-chart-line', 'Gradual Progress', 'Offers a step-by-step approach to sadhana-bhakti, making advancement achievable for everyone.'],
          ['fa-hand-holding-heart', 'Not Based on Finance', 'The program is not linked with donations and is open to everyone, independently of monetary conditions.'],
          ['fa-shield-alt', 'Prevents Defections', 'Develops a strong feeling of belonging to Srila Prabhupada\'s movement within ISKCON.'],
        ];
        foreach ($benefits as $b):
        ?>
        <div style="display:flex;gap:var(--space-md);padding:var(--space-lg);background:var(--cream);border-radius:var(--radius-lg);">
          <div style="width:44px;height:44px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-base);flex-shrink:0;"><i class="fas <?php echo $b[0]; ?>"></i></div>
          <div><h4 style="font-size:var(--font-size-sm);margin-bottom:4px;"><?php echo $b[1]; ?></h4><p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.6;margin:0;"><?php echo $b[2]; ?></p></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-2xl);text-align:center;background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-xl);">
      <p style="color:var(--text-light);">Standard certificates are issued worldwide for each level achieved. The recognition shall be granted in any of the following categories (giving these is optional, as also the bestowal ceremony according to local time, place and circumstance).</p>
      <p style="font-weight:600;color:var(--primary);"><i class="fas fa-phone-alt"></i> +91 99860 77269</p>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--white);border-radius:var(--radius-lg);padding:var(--space-2xl);box-shadow:var(--shadow-md);border-left:4px solid var(--primary);">
      <h3 style="margin-bottom:var(--space-lg);">ISKCON Law Book Chapter 15.2.1 Guidelines</h3>
      <div style="color:var(--text-light);line-height:1.9;">
        <p style="margin-bottom:var(--space-md);"><strong>ISKCON Leaders and GBC Members</strong> (with the exception of ISKCON initiating spiritual masters who cannot perform this ceremony except when it is for an approved guru-asraya (sheltered) or approved aspirant disciple) shall, within their area of authority, be permitted to publicly bestow acceptance and recognition to members of its congregation for devotional achievements and progress in devotional service.</p>
        <p style="margin-bottom:var(--space-md);">Local temples and congregational preaching units should implement programs for aiding the congregational members to enhance their standing, and for training them to qualify for the higher levels. This should include following a recommended study course for the different levels (Adult Education and Congregational Preaching Monitors shall recommend.)</p>
        <p style="margin-bottom:var(--space-md);">Standard certificates shall be issued worldwide. (The proforma certificates shall be created and circulated by the Corresponding Secretary in consultation with the Congregational Preaching Monitor.)</p>
        <p>The recognition shall be granted in any of the following categories (giving these is optional, as also the bestowal ceremony according to local time, place and circumstance).</p>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
