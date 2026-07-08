<?php

/**
 * Blog Detail Page
 * Reads blog content from the `blogs` database table.
 * 
 * Usage: blogs/detail.php?slug=article-slug
 */

require_once __DIR__ . '/../../../config.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
  header('Location: ' . BASE_URL . 'blogs');
  exit;
}

try {
  $db = getDB();
  $stmt = $db->prepare("SELECT slug, title, description, icon, banner_image, published_date, updated_at, tags, content_body 
                          FROM blogs WHERE slug = ? AND is_published = 1 LIMIT 1");
  $stmt->execute([$slug]);
  $blog = $stmt->fetch();
} catch (PDOException $e) {
  $blog = null;
}

if (!$blog) {
  header('HTTP/1.0 404 Not Found');
  $pageTitle = 'Article Not Found';
  include __DIR__ . '/../../../partials/header.php';
?>
  <section class="page-header">
    <div class="container">
      <h1>Article Not Found</h1>
      <p style="color:rgba(255,255,255,0.8);">The article you're looking for could not be found.</p>
      <a href="<?php echo BASE_URL; ?>blogs" class="btn btn-accent" style="margin-top:var(--space-lg);">Browse All Articles</a>
    </div>
  </section>
<?php
  include __DIR__ . '/../../../partials/footer.php';
  exit;
}

$metaDescription = htmlspecialchars(mb_substr($blog['description'] ?? $blog['title'], 0, 160));
$ogImage = !empty($blog['banner_image']) ? BASE_URL . $blog['banner_image'] : (BASE_URL . 'assets/images/iskcon_logo.svg');
$pageType = 'blog';
$pageTitle = htmlspecialchars($blog['title']) . ' - ISKCON The Palace Temple of Lord Jagannath';

$blogTags = !empty($blog['tags']) ? json_decode($blog['tags'], true) : [];

// Prepare Article data for schema.org structured data
$articleData = [
  'headline' => $blog['title'],
  'description' => $blog['description'] ?? $blog['title'],
  'image' => !empty($blog['banner_image']) ? BASE_URL . $blog['banner_image'] : (BASE_URL . 'assets/images/iskcon_logo.svg'),
  'datePublished' => $blog['published_date'] ? date('c', strtotime($blog['published_date'])) : '',
  'dateModified' => $blog['updated_at'] ?? $blog['published_date'] ?? '',
  'author' => SITE_NAME,
  'url' => BASE_URL . 'blogs/' . urlencode($slug),
  'tags' => $blogTags,
];
$formattedDate = !empty($blog['published_date']) ? date('F d, Y', strtotime($blog['published_date'])) : '';
$bannerImage = !empty($blog['banner_image']) ? $blog['banner_image'] : 'assets/images/banners/rasa-lila.jpg';

include __DIR__ . '/../../../partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('<?php echo BASE_URL . $bannerImage; ?>');"></div>
  <div class="container">
    <h1 class="reveal" style="font-family:var(--font-heading); color:var(--white); font-size:calc(var(--font-size-2xl) + 0.5vw); line-height:1.3; text-shadow:0 2px 10px rgba(0,0,0,0.6); max-width:900px; margin:0 auto var(--space-md) auto;">
      <?php echo htmlspecialchars($blog['title']); ?>
    </h1>
    <div class="breadcrumb reveal" style="display:flex; justify-content:center; gap:8px; color:rgba(255,255,255,0.8); font-size:var(--font-size-sm);">
      <a href="<?php echo BASE_URL; ?>" style="color:var(--accent-light);">Home</a><span>›</span>
      <a href="<?php echo BASE_URL; ?>blogs" style="color:var(--accent-light);">Blogs</a><span>›</span>
      <span style="color:var(--white);"><?php echo htmlspecialchars($blog['title']); ?></span>
    </div>
  </div>
</section>

<section class="page-content" style="background:var(--cream-light); padding:var(--space-3xl) 0;">
  <div class="container" style="max-width:850px;">
    <div class="reveal" style="margin-bottom:var(--space-2xl);border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);">
      <img src="<?php echo BASE_URL . $bannerImage; ?>" alt="<?php echo htmlspecialchars($blog['title']); ?> Banner" loading="lazy" style="width:100%;height:auto;display:block;">
    </div>

    <div style="background:var(--white); padding:var(--space-2xl) var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); border:1px solid var(--border);">

      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--space-sm); margin-bottom:var(--space-xl); padding-bottom:var(--space-lg); border-bottom:1px solid var(--border);">
        <div style="display:flex; align-items:center; gap:var(--space-sm);">
          <i class="fas <?php echo htmlspecialchars($blog['icon'] ?? 'fa-newspaper'); ?>" style="color:var(--primary); font-size:var(--font-size-xl);"></i>
          <div>
            <span style="font-size:var(--font-size-sm); font-weight:600; color:var(--text-light); text-transform:uppercase; letter-spacing:0.5px;"><?php echo $formattedDate; ?></span>
          </div>
        </div>
        <?php if (!empty($blogTags) && is_array($blogTags)): ?>
          <div style="display:flex; gap:4px; flex-wrap:wrap;">
            <?php foreach ($blogTags as $t): ?>
              <a href="<?php echo BASE_URL; ?>blogs?tag=<?php echo urlencode($t); ?>"
                style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; background:var(--cream); color:var(--primary-dark); padding:3px 8px; border-radius:4px; text-decoration:none; transition:all var(--transition-fast);"
                onmouseover="this.style.background='var(--primary)'; this.style.color='var(--white)';"
                onmouseout="this.style.background='var(--cream)'; this.style.color='var(--primary-dark)';"><?php echo htmlspecialchars($t); ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($blog['content_body'])): ?>
        <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8;">
          <?php echo $blog['content_body']; ?>
        </article>
      <?php else: ?>
        <article class="reveal" style="font-family:var(--font-body); font-size:var(--font-size-base); color:var(--text); line-height:1.8;">
          <?php if (!empty($blog['description'])): ?>
            <p><?php echo nl2br(htmlspecialchars($blog['description'])); ?></p>
          <?php else: ?>
            <p style="color:var(--text-light); font-style:italic;">Full article content coming soon.</p>
          <?php endif; ?>
        </article>
      <?php endif; ?>

      <div style="margin-top:var(--space-2xl); padding-top:var(--space-lg); border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:var(--space-md);">
        <a href="<?php echo BASE_URL; ?>blogs" style="display:inline-flex; align-items:center; gap:6px; color:var(--primary); font-weight:600; font-size:var(--font-size-sm); text-decoration:none; transition:all var(--transition-fast);"
          onmouseover="this.style.color='var(--primary-dark)';" onmouseout="this.style.color='var(--primary)';">
          <i class="fas fa-arrow-left"></i> Back to All Articles
        </a>

        <?php
        $shareUrl = urlencode(BASE_URL . 'blogs/' . $slug);
        $shareTitle = urlencode($blog['title']);
        ?>
        <div style="display:flex; align-items:center; gap:var(--space-sm);">
          <span style="font-size:var(--font-size-xs); color:var(--text-light); font-weight:600; text-transform:uppercase;">Share:</span>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $shareUrl; ?>" target="_blank" rel="noopener"
            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; background:var(--cream); color:var(--primary); border-radius:50%; text-decoration:none; transition:all var(--transition-fast); font-size:14px;"
            onmouseover="this.style.background='#4267B2'; this.style.color='white';" onmouseout="this.style.background='var(--cream)'; this.style.color='var(--primary)';">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?text=<?php echo $shareTitle; ?>&url=<?php echo $shareUrl; ?>" target="_blank" rel="noopener"
            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; background:var(--cream); color:var(--primary); border-radius:50%; text-decoration:none; transition:all var(--transition-fast); font-size:14px;"
            onmouseover="this.style.background='#1DA1F2'; this.style.color='white';" onmouseout="this.style.background='var(--cream)'; this.style.color='var(--primary)';">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="https://wa.me/?text=<?php echo $shareTitle . '%20' . $shareUrl; ?>" target="_blank" rel="noopener"
            style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; background:var(--cream); color:var(--primary); border-radius:50%; text-decoration:none; transition:all var(--transition-fast); font-size:14px;"
            onmouseover="this.style.background='#25D366'; this.style.color='white';" onmouseout="this.style.background='var(--cream)'; this.style.color='var(--primary)';">
            <i class="fab fa-whatsapp"></i>
          </a>
        </div>
      </div>

    </div>
</section>

<?php include __DIR__ . '/../../../partials/footer.php'; ?>