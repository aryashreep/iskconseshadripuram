<?php
$pageTitle = 'Bhakti Sadan';
include '../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/banner6.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Bhakti Sadan</h1>
    <div class="breadcrumb reveal"><a href="/isjm/">Home</a><span>›</span><a href="services">Services</a><span>›</span><a href="services/our-centers">Our Centers</a><span>›</span><span>Bhakti Sadan</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="max-width:900px;">
    <!-- Content Image -->
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="assets/images/banners/banner6.jpg" alt="Bhakti Sadan - Community Temple Program" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="about-intro" style="grid-template-columns:1fr;">
      <div class="reveal">
        <div class="section-divider" style="justify-content:flex-start;"><span class="divider-icon">🏡</span></div>
        <h2>Bringing the Temple to Your Neighbourhood</h2>
        <p style="color:var(--text-light);line-height:1.9;">Bhakti Sadan is a beautiful concept of taking the temple to the local communities. In this modern world, commuting to distant places is a big challenge. Factors like traffic, pollution, busy schedules and lack of time prevent people from experiencing self-realisation and Krishna Consciousness.</p>
        <p style="color:var(--text-light);line-height:1.9;">This visionary concept was planted in 2004 by <strong>HH Jayapataka Swami Maharaj</strong> during a conversation with our temple President <strong>HG Anukul Keshav Das</strong>. HG Anukul Prabhu was lamenting that many devotees were not coming to the temple despite wonderful arrangements. HH Jayapataka Swami Maharaj asked him why he was not thinking of taking the temple to them instead of expecting them to come to the temple.</p>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">How Bhakti Sadan Works</h3>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-xl);">
        <div style="text-align:center;background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);">📍</div>
          <h4>Local Centres</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Small replicas of temple programs set up in various parts of Bangalore city.</p>
        </div>
        <div style="text-align:center;background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);">🗓️</div>
          <h4>Every Sunday</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">Each Sadan functions every Sunday replicating the Sunday Feast program from the main temple.</p>
        </div>
        <div style="text-align:center;background:var(--white);padding:var(--space-xl);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);">
          <div style="width:64px;height:64px;background:var(--gradient-primary);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto var(--space-lg);color:var(--white);font-size:var(--font-size-2xl);">🌍</div>
          <h4>Community Building</h4>
          <p style="color:var(--text-light);font-size:var(--font-size-sm);line-height:1.7;">People from each locality participate without having to travel far, building local spiritual communities.</p>
        </div>
      </div>
    </div>

    <div class="reveal" style="margin-top:var(--space-3xl);background:var(--white);border-radius:var(--radius-lg);padding:var(--space-2xl);box-shadow:var(--shadow-md);">
      <h3 style="text-align:center;margin-bottom:var(--space-lg);">Bhakti Sadan & Extension Centres</h3>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:var(--font-size-sm);">
          <thead><tr style="background:var(--primary);color:var(--white);"><th style="padding:12px 16px;text-align:left;">Location</th><th style="padding:12px 16px;text-align:left;">Contact Person</th><th style="padding:12px 16px;text-align:left;">Phone</th></tr></thead>
          <tbody>
            <?php
            $centers = [
              ['Overall', 'Anukul Keshav Das / Rakhal Krishna Das', '+91 9986077269'],
              ['Anekal', 'Vidura Madhav Das', '+91 6360105788'],
              ['Annapurnishwara', 'Madan Mohan Das', '+91 8095821290'],
              ['Anjanapura', 'Vidura Madhav Das', '+91 6360105788'],
              ['HAL', 'Rohini Dulal Das', '+91 9945544866'],
              ['HSR Layout', 'Sankarshan Nitai Das', '+91 9945055009'],
              ['HSR Layout - 2', 'Muralipriya Govinda Das', '+91 9916614159'],
              ['JP Nagar', 'Narayana Padmanabha Das', '+91 9986074691'],
              ['Kudlu (Electronics City)', 'Kripamaya Gauranga Das', '+91 9886564641'],
              ['Nelamangala', 'Aseem Shyam Das / Srivas Krishna Das', '+91 9880855905'],
              ['Raja Rajeswari Nagar', 'Vidura Madhav Das', '+91 6360105788'],
              ['RT Nagar / Hindi group', 'Radhapati Gopinath Das / Suresvar Das', '+91 9341244364'],
              ['Sahakara Nagar', 'Vrinda Maharani DD', '+91 9739106589'],
              ['Sarjapur', 'Vraj Krishna Das / Kripamaya Gauranga Das', '+91 9513456108'],
              ['Seshadripuram', 'Tirtha Chaitanya Das / Devadutta Partha Das', '+91 8217467102'],
              ['Seshadripuram (Bengali)', 'Gunarnava Krishna Das', '+91 9886126108'],
              ['Nagarbhave', 'Yajneshwar Kesav Das', '+91 9845915664'],
              ['Byrathi Bande', 'Latika Devi Dasi / Raghuveerya Das', '+91 9663376946'],
              ['Yelahanka', 'Raghuveerya Prabhu / Kesava Krpasindhu Das', '+91 7893533570'],
              ['Hulimavu', 'Narayana Padmanabh Prabhu', '+91 9986074691'],
              ['Vijaya Nagar', 'Haranath Gaur Prabhu', '+91 9880792442'],
              ['Hoskote', 'Shivanand Prabhu', '+91 9663044999'],
            ];
            foreach ($centers as $i => $c):
            ?>
            <tr style="<?php echo $i % 2 === 0 ? 'background:var(--cream);' : ''; ?>">
              <td style="padding:10px 16px;border-bottom:1px solid var(--border);font-weight:500;"><?php echo $c[0]; ?></td>
              <td style="padding:10px 16px;border-bottom:1px solid var(--border);"><?php echo $c[1]; ?></td>
              <td style="padding:10px 16px;border-bottom:1px solid var(--border);"><?php echo $c[2]; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php include '../partials/footer.php'; ?>
