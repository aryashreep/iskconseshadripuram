<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Gallery';
$metaDescription = 'Browse the gallery of ISKCON The Palace Temple of Lord Jagannath in Seshadripuram, Bangalore. Photos of deities, festivals, temple events and spiritual moments.';
$pageType = 'gallery';

// Dynamically scan the media folder for images
$mediaDir = __DIR__ . '/media';
$allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
$galleryImages = [];

if (is_dir($mediaDir)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($mediaDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExts)) {
                // Get path relative to media/ directory
                $relativePath = 'media/' . $iterator->getSubPathName();
                $relativePath = str_replace('\\', '/', $relativePath);

                // Use filename (without extension) as display name
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                // Clean up the name: replace underscores/hyphens with spaces, title case
                $name = ucwords(str_replace(['_', '-'], ' ', $name));

                $galleryImages[] = [
                    'url'    => BASE_URL . $relativePath,
                    'name'   => $name,
                    'caption'=> $name,
                ];
            }
        }
    }
}

// Sort alphabetically for consistent ordering
usort($galleryImages, function ($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Encode URL path: spaces and special chars, but preserve slashes and scheme
function encode_gallery_url($url) {
    $encoded = rawurlencode($url);
    $encoded = str_replace(['%2F', '%3A', '%3F', '%3D', '%26'], ['/', ':', '?', '=', '&'], $encoded);
    return $encoded;
}

// Pagination
$perPage = 12;
$totalImages = count($galleryImages);
$totalPages = max(1, ceil($totalImages / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min(intval($_GET['page']), $totalPages)) : 1;
$offset = ($currentPage - 1) * $perPage;
$pageImages = array_slice($galleryImages, $offset, $perPage);

include 'partials/header.php';
?>

<section class="page-header">
  <div class="page-header-bg" style="background-image: url('assets/images/banners/banner1.jpg');"></div>
  <div class="container">
    <h1 class="reveal">Gallery</h1>
    <div class="breadcrumb reveal"><a href="/">Home</a><span>›</span><span>Gallery</span></div>
  </div>
</section>

<section class="page-content">
  <div class="container" style="text-align:center;">
    <div class="section-divider"><span class="divider-icon"><img src="/assets/images/iskcon_logo.svg" alt="ISKCON" style="height:24px;width:auto;"></span></div>
    <span class="section-subtitle reveal">Darshan</span>
    <h2 class="section-title reveal">Glimpses of The Palace Temple of Lord Jagannath</h2>
    <p class="section-description reveal">Browse through our collection of sacred moments captured at ISKCON Seshadripuram, Bangalore.</p>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <?php if (empty($pageImages)): ?>
      <p style="text-align:center; color:var(--text-light); padding:var(--space-3xl) 0;">No images found in the media folder.</p>
    <?php else: ?>
    <div class="masonry-grid">
      <?php foreach ($pageImages as $idx => $img): ?>
      <div class="masonry-item masonry-span-<?php echo ($idx % 5 === 0) ? '2' : '1'; ?> reveal">
        <img src="<?php echo htmlspecialchars(encode_gallery_url($img['url'])); ?>" alt="<?php echo htmlspecialchars($img['name']); ?>" loading="lazy">
        <div class="masonry-overlay">
          <h4><?php echo htmlspecialchars($img['name']); ?></h4>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:var(--space-2xl); flex-wrap:wrap;">
      <?php if ($currentPage > 1): ?>
        <a href="<?php echo BASE_URL; ?>darshan?page=<?php echo $currentPage - 1; ?>" style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); background:var(--white); color:var(--text-dark); font-size:13px; font-weight:600; text-decoration:none;"><i class="fas fa-chevron-left" style="font-size:10px;"></i> Prev</a>
      <?php endif; ?>

      <?php
      // Show max 7 page buttons with ellipsis
      $range = 2;
      $start = max(1, $currentPage - $range);
      $end = min($totalPages, $currentPage + $range);
      if ($start > 1) {
          echo '<a href="' . BASE_URL . 'darshan?page=1" style="display:inline-flex; align-items:center; padding:8px 14px; border:1px solid var(--border); border-radius:var(--radius-md); background:var(--white); color:var(--text-dark); font-size:13px; font-weight:600; text-decoration:none;">1</a>';
          if ($start > 2) echo '<span style="color:var(--text-light); padding:0 4px;">...</span>';
      }
      for ($i = $start; $i <= $end; $i++): ?>
        <a href="<?php echo BASE_URL; ?>darshan?page=<?php echo $i; ?>" style="display:inline-flex; align-items:center; padding:8px 14px; border:1px solid <?php echo $i === $currentPage ? 'var(--primary)' : 'var(--border)'; ?>; border-radius:var(--radius-md); background:<?php echo $i === $currentPage ? 'var(--primary)' : 'var(--white)'; ?>; color:<?php echo $i === $currentPage ? 'var(--white)' : 'var(--text-dark)'; ?>; font-size:13px; font-weight:600; text-decoration:none;"><?php echo $i; ?></a>
      <?php endfor;
      if ($end < $totalPages) {
          if ($end < $totalPages - 1) echo '<span style="color:var(--text-light); padding:0 4px;">...</span>';
          echo '<a href="' . BASE_URL . 'darshan?page=' . $totalPages . '" style="display:inline-flex; align-items:center; padding:8px 14px; border:1px solid var(--border); border-radius:var(--radius-md); background:var(--white); color:var(--text-dark); font-size:13px; font-weight:600; text-decoration:none;">' . $totalPages . '</a>';
      }
      ?>

      <?php if ($currentPage < $totalPages): ?>
        <a href="<?php echo BASE_URL; ?>darshan?page=<?php echo $currentPage + 1; ?>" style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); background:var(--white); color:var(--text-dark); font-size:13px; font-weight:600; text-decoration:none;">Next <i class="fas fa-chevron-right" style="font-size:10px;"></i></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div style="text-align:center; margin-top:var(--space-md); font-size:13px; color:var(--text-light);">
      Showing <?php echo $totalImages > 0 ? $offset + 1 : 0; ?>–<?php echo min($offset + $perPage, $totalImages); ?> of <?php echo $totalImages; ?> photos
    </div>
  </div>
</section>

<style>
.masonry-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-auto-rows: 120px;
  gap: 12px;
}
.masonry-item {
  position: relative;
  border-radius: var(--radius-md);
  overflow: hidden;
  cursor: pointer;
}
.masonry-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.4s ease;
}
.masonry-item:hover img {
  transform: scale(1.05);
}
.masonry-span-1 { grid-row: span 2; }
.masonry-span-2 { grid-row: span 3; }
.masonry-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(44,27,18,0.85), transparent 60%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: var(--space-lg);
  opacity: 0;
  transition: opacity 0.3s ease;
}
.masonry-item:hover .masonry-overlay { opacity: 1; }
.masonry-overlay h4 {
  color: var(--white);
  font-size: var(--font-size-base);
  margin: 0;
  font-family: var(--font-heading);
}
@media (max-width: 768px) {
  .masonry-grid { grid-template-columns: repeat(2, 1fr); grid-auto-rows: 100px; }
}
@media (max-width: 480px) {
  .masonry-grid { grid-template-columns: 1fr; grid-auto-rows: 200px; }
  .masonry-span-2 { grid-row: span 1; }
}
</style>

<?php include 'partials/footer.php'; ?>
