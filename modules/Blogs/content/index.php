<?php
$pageTitle = 'Blogs & Articles';
$metaDescription = 'Read spiritual articles on Bhagavad Gita, Vaishnava philosophy, temple activities and Srila Prabhupada teachings from ISKCON The Palace Temple of Lord Jagannath, Bangalore.';
$pageType = 'blog';
include '../partials/header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 8;

// Fetch blogs from database
$db = getDB();

// Get selected tag filter
$selected_tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

// Count total blogs (accounting for tag filter)
$countSql = "SELECT COUNT(*) as total FROM blogs WHERE is_published = 1";
$countParams = [];

if (!empty($selected_tag)) {
  $countSql .= " AND tags LIKE ?";
  $countParams[] = '%' . $selected_tag . '%';
}

$stmt = $db->prepare($countSql);
$stmt->execute($countParams);
$total_blogs = (int)$stmt->fetchColumn();

$total_pages = max(1, ceil($total_blogs / $per_page));
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $per_page;

// Fetch page of blogs
$blogSql = "SELECT slug, title, description, icon, banner_image, published_date, tags
            FROM blogs 
            WHERE is_published = 1";
$blogParams = [];

if (!empty($selected_tag)) {
  $blogSql .= " AND tags LIKE ?";
  $blogParams[] = '%' . $selected_tag . '%';
}

$blogSql .= " ORDER BY published_date DESC, id DESC LIMIT ? OFFSET ?";
$blogParams[] = $per_page;
$blogParams[] = $offset;

$stmt = $db->prepare($blogSql);
$stmt->execute($blogParams);
$blogs = $stmt->fetchAll();

// Build tag counts from all published blogs
$tagSql = "SELECT tags FROM blogs WHERE is_published = 1";
$stmt = $db->query($tagSql);
$tag_counts = [];
while ($row = $stmt->fetch()) {
  if (!empty($row['tags'])) {
    $blogTags = json_decode($row['tags'], true);
    if (is_array($blogTags)) {
      foreach ($blogTags as $t) {
        if (!isset($tag_counts[$t])) {
          $tag_counts[$t] = 0;
        }
        $tag_counts[$t]++;
      }
    }
  }
}
ksort($tag_counts);
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/images/banners/rasa-lila.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Blogs & Articles</h1>
    <div class="breadcrumb reveal">
      <a href="<?php echo BASE_URL; ?>">Home</a><span>›</span><span>Blogs</span>
    </div>
  </div>
</section>

<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:1000px;">
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="<?php echo BASE_URL; ?>assets/images/banners/rasa-lila.jpg" alt="Blogs Banner" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div class="reveal" style="text-align:center;margin-bottom:var(--space-3xl);">
      <div class="section-divider"><span class="divider-icon">📝</span></div>
      <h2 style="font-family:var(--font-heading); color:var(--dark); font-weight:600;">Spiritual Insights & Temple Updates</h2>
      <p style="color:var(--text-light);max-width:700px;margin:var(--space-sm) auto 0 auto;line-height:1.8;">
        Explore articles on Vedic philosophy, temple activities, and the teachings of Srila Prabhupada.
      </p>
    </div>

    <!-- Tag Filter Pills -->
    <div class="reveal tag-filter-bar">
      <div class="tag-filter-inner">
        <a href="<?php echo BASE_URL; ?>blogs" class="tag-pill tag-pill-all <?php echo empty($selected_tag) ? 'active' : ''; ?>">
          All
        </a>
        <?php foreach ($tag_counts as $tag => $count):
          $active = $selected_tag === $tag ? 'active' : '';
        ?>
          <a href="<?php echo BASE_URL; ?>blogs?tag=<?php echo urlencode($tag); ?>"
            class="tag-pill <?php echo $active; ?>">
            <?php echo htmlspecialchars($tag); ?>
            <span class="tag-pill-count"><?php echo $count; ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Blogs Cards Grid -->
    <?php if (empty($blogs)): ?>
      <div style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">
        <i class="fas fa-newspaper" style="font-size:48px; opacity:0.3; margin-bottom:var(--space-md);"></i>
        <p style="font-size:var(--font-size-lg);">No articles found<?php echo !empty($selected_tag) ? ' with this tag' : ''; ?>.</p>
        <a href="<?php echo BASE_URL; ?>blogs" class="btn btn-outline-dark btn-sm" style="margin-top:var(--space-md);">View All Articles</a>
      </div>
    <?php else: ?>
      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(310px, 1fr)); gap:var(--space-xl); margin-bottom:var(--space-3xl);">
        <?php foreach ($blogs as $b):
          $blogTags = !empty($b['tags']) ? json_decode($b['tags'], true) : [];
          $cardBanner = !empty($b['banner_image']) ? $b['banner_image'] : 'assets/images/banners/pain-motivation-banner.jpg';
          $formattedDate = !empty($b['published_date']) ? date('F d, Y', strtotime($b['published_date'])) : '';
        ?>
          <a href="<?php echo BASE_URL; ?>blogs/<?php echo urlencode($b['slug']); ?>" class="reveal" style="text-decoration:none; color:inherit; display:block;">
            <div style="background:var(--white); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm); border:1px solid var(--border); display:flex; flex-direction:column; height:100%; transition:all var(--transition-base);"
              onmouseover="this.style.boxShadow='var(--shadow-md)'; this.style.transform='translateY(-4px)'; this.style.borderColor='var(--primary-light)';"
              onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';">
              <div style="height:170px; background-size:cover; background-position:center; background-image:url(<?php echo BASE_URL . $cardBanner; ?>); position:relative; display:flex; align-items:center; justify-content:center; font-size:52px; color:rgba(255,255,255,0.35);">
                <div style="position:absolute; inset:0; background:linear-gradient(135deg, rgba(44,27,18,0.4) 0%, rgba(123,30,30,0.2) 100%);"></div>
                <i class="fas <?php echo $b['icon']; ?>" style="position:relative; z-index:1; color: rgba(255, 255, 255, 0.9); text-shadow:0 2px 4px rgba(0,0,0,0.5);"></i>
              </div>
              <div style="padding:var(--space-lg); display:flex; flex-direction:column; gap:var(--space-xs); flex-grow:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; margin-bottom:2px;">
                  <span style="font-size:11px; color:var(--text-light); font-weight:600; text-transform:uppercase; letter-spacing:0.5px;"><?php echo $formattedDate; ?></span>
                  <?php if (!empty($blogTags) && is_array($blogTags)): ?>
                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                      <?php foreach (array_slice($blogTags, 0, 3) as $t): ?>
                        <span style="font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; background:var(--cream); color:var(--primary-dark); padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($t); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
                <h3 style="font-family:var(--font-heading); font-size:var(--font-size-md); font-weight:600; color:var(--dark); margin:0; line-height:1.4;"><?php echo htmlspecialchars($b['title']); ?></h3>
                <p style="color:var(--text); font-size:var(--font-size-sm); line-height:1.6; margin:var(--space-xs) 0 0 0; flex-grow:1;"><?php echo htmlspecialchars($b['description'] ?? ''); ?></p>
                <div style="margin-top:var(--space-md); display:flex; align-items:center; gap:6px; color:var(--primary); font-weight:600; font-size:var(--font-size-sm);">
                  Read More <i class="fas fa-arrow-right" style="font-size:11px;"></i>
                </div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1):
      $tag_param = !empty($selected_tag) ? '&tag=' . urlencode($selected_tag) : '';
    ?>
      <div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:var(--space-2xl);" class="reveal">
        <?php if ($page > 1): ?>
          <a href="<?php echo BASE_URL . 'blogs?page=' . ($page - 1) . $tag_param; ?>" style="padding:8px 16px; background:var(--white); color:var(--text-dark); border:1px solid var(--border); border-radius:var(--radius-md); font-size:var(--font-size-sm); text-decoration:none; font-weight:600; transition:all var(--transition-fast);"
            onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';"
            onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-dark)';">
            <i class="fas fa-chevron-left" style="font-size:11px; margin-right:4px;"></i> Prev
          </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++):
          $isActive = ($i === $page);
          $href = BASE_URL . 'blogs?page=' . $i . $tag_param;
          $bg = $isActive ? 'var(--primary)' : 'var(--white)';
          $color = $isActive ? 'var(--white)' : 'var(--text-dark)';
          $borderColor = $isActive ? 'var(--primary)' : 'var(--border)';
        ?>
          <a href="<?php echo $href; ?>" style="padding:8px 16px; background:<?php echo $bg; ?>; color:<?php echo $color; ?>; border:1px solid <?php echo $borderColor; ?>; border-radius:var(--radius-md); font-size:var(--font-size-sm); text-decoration:none; font-weight:600; transition:all var(--transition-fast);"
            onmouseover="<?php echo $isActive ? '' : 'this.style.borderColor=\\\"var(--primary)\\\"; this.style.color=\\\"var(--primary)\\\"'; ?>"
            onmouseout="<?php echo $isActive ? '' : 'this.style.borderColor=\\\"var(--border)\\\"; this.style.color=\\\"var(--text-dark)\\\"'; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
          <a href="<?php echo BASE_URL . 'blogs?page=' . ($page + 1) . $tag_param; ?>" style="padding:8px 16px; background:var(--white); color:var(--text-dark); border:1px solid var(--border); border-radius:var(--radius-md); font-size:var(--font-size-sm); text-decoration:none; font-weight:600; transition:all var(--transition-fast);"
            onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';"
            onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-dark)';">
            Next <i class="fas fa-chevron-right" style="font-size:11px; margin-left:4px;"></i>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include '../partials/footer.php'; ?>