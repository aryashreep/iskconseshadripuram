<?php
$pageTitle = 'Resources';
$metaDescription = 'Curated spiritual resources from ISKCON Seshadripuram, Bangalore. Access Srila Prabhupada\'s books, audio kirtans, Bhagavad Gita study materials, Vaishnava calendar, and ISKCON links.';
$pageType = 'default';
include 'partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('https://picsum.photos/seed/resources/1920/600');"></div>
  <div class="container">
    <h1 class="reveal">Resources</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="about">About Us</a><span>›</span><span>Resources</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <div style="text-align:center;margin-bottom:var(--space-3xl);" class="reveal">
      <div class="section-divider"><span class="divider-icon">📚</span></div>
      <h2>Spiritual Resources</h2>
      <p style="color:var(--text-light);">A curated collection of audio, video, books, and links to help you deepen your Krishna consciousness.</p>
    </div>

    <!-- Audio & Video -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-lg);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-lg);"><i class="fas fa-headphones"></i></div>
        <h3 style="margin:0;">Audio & Video</h3>
      </div>
      <div style="display:grid;gap:var(--space-sm);">
        <?php
        $audioLinks = [
          ['KK Songs - Vaishnava songs and kirtans', 'https://www.kksongs.org/'],
          ['Prabhupadavani.org', 'https://prabhupadavani.org/'],
          ['ISKCON Desire Tree Audio', 'https://audio.iskcondesiretree.com/'],
          ['ISKCON Desire Tree YouTube', 'https://www.youtube.com/user/iskcondesiretree'],
          ['Srimad Bhagavatam Class', 'http://www.srimadbhagavatamclass.com/'],
        ];
        foreach ($audioLinks as $l):
        ?>
        <a href="<?php echo $l[1]; ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);transition:all var(--transition-fast);color:var(--text);text-decoration:none;">
          <i class="fas fa-external-link-alt" style="color:var(--primary);font-size:var(--font-size-xs);"></i>
          <span style="font-size:var(--font-size-sm);"><?php echo $l[0]; ?></span>
          <i class="fas fa-arrow-right" style="margin-left:auto;color:var(--text-light);font-size:var(--font-size-xs);"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Srila Prabhupada Books -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-lg);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-lg);"><i class="fas fa-book"></i></div>
        <h3 style="margin:0;">Srila Prabhupada Books</h3>
      </div>
      <div style="display:grid;gap:var(--space-sm);">
        <?php
        $bookLinks = [
          ['BBT Media', 'https://www.bbtmedia.com/'],
          ['Vedabase.io', 'https://www.vedabase.io/'],
          ['Gita Base', 'https://www.gitabase.com/'],
          ['Prabhupada.io', 'https://www.prabhupada.io/'],
          ['Prabhupada.net', 'https://www.prabhupada.net/'],
          ['Founder Acharya', 'https://www.founderacharya.com/'],
        ];
        foreach ($bookLinks as $l):
        ?>
        <a href="<?php echo $l[1]; ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);transition:all var(--transition-fast);color:var(--text);text-decoration:none;">
          <i class="fas fa-external-link-alt" style="color:var(--primary);font-size:var(--font-size-xs);"></i>
          <span style="font-size:var(--font-size-sm);"><?php echo $l[0]; ?></span>
          <i class="fas fa-arrow-right" style="margin-left:auto;color:var(--text-light);font-size:var(--font-size-xs);"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Vaishnava Calendar -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-lg);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-lg);"><i class="fas fa-calendar-alt"></i></div>
        <h3 style="margin:0;">Vaishnava Calendar</h3>
      </div>
      <div style="display:grid;gap:var(--space-sm);">
        <?php
        $calLinks = [
          ['Vaisnava Calendar', 'https://www.vaisnavacalendar.com/'],
          ['Hare Krishna Calendar', 'https://www.harekrishnacalendar.com/'],
          ['TOVP Calendar', 'https://www.tovp.org/'],
        ];
        foreach ($calLinks as $l):
        ?>
        <a href="<?php echo $l[1]; ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);transition:all var(--transition-fast);color:var(--text);text-decoration:none;">
          <i class="fas fa-external-link-alt" style="color:var(--primary);font-size:var(--font-size-xs);"></i>
          <span style="font-size:var(--font-size-sm);"><?php echo $l[0]; ?></span>
          <i class="fas fa-arrow-right" style="margin-left:auto;color:var(--text-light);font-size:var(--font-size-xs);"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Important Websites -->
    <div class="reveal">
      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-lg);">
        <div style="width:48px;height:48px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;color:var(--white);font-size:var(--font-size-lg);"><i class="fas fa-globe"></i></div>
        <h3 style="margin:0;">Important Websites</h3>
      </div>
      <div style="display:grid;gap:var(--space-sm);">
        <?php
        $webLinks = [
          ['ISKCON Official', 'https://www.iskcon.org/'],
          ['ISKCON Communications', 'https://www.iskconcommunications.org/'],
          ['Krishna.com', 'https://www.krishna.com/'],
          ['ISKCON News', 'https://www.iskconnews.org/'],
          ['ISKCON Online', 'https://www.iskcononline.com/'],
          ['Dandavats', 'https://www.dandavats.com/'],
          ['The Spiritual Scientist', 'https://www.thespiritualscientist.com/'],
          ['Gita Daily', 'https://www.gitadaily.com/'],
          ['Hare Krishna TV', 'https://www.harekrsnatv.com/'],
          ['ISKCON Desire Tree', 'https://www.iskcondesiretree.com/'],
          ['Bhakti Course', 'https://bhakticourse.com/'],
          ['Back to Godhead India', 'https://backtogodhead.in/'],
        ];
        foreach ($webLinks as $l):
        ?>
        <a href="<?php echo $l[1]; ?>" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md);background:var(--cream);border-radius:var(--radius-md);transition:all var(--transition-fast);color:var(--text);text-decoration:none;">
          <i class="fas fa-external-link-alt" style="color:var(--primary);font-size:var(--font-size-xs);"></i>
          <span style="font-size:var(--font-size-sm);"><?php echo $l[0]; ?></span>
          <i class="fas fa-arrow-right" style="margin-left:auto;color:var(--text-light);font-size:var(--font-size-xs);"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-2xl);text-align:center;background:var(--cream);border-radius:var(--radius-lg);padding:var(--space-lg);">
      <p style="color:var(--text-light);font-size:var(--font-size-sm);">
        <i class="fas fa-info-circle" style="color:var(--primary);"></i>
        For more resources and spiritual guidance, please <a href="contact">contact us</a> or visit the temple.
      </p>
    </div>
  </div>
</section>

<?php include 'partials/footer.php'; ?>
