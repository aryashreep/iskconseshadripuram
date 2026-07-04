<?php
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin', 'editor']);

$pageTitle = 'Edit Blog';
$activePage = 'blogs';
include 'partials/header.php';

$db = getDB();
$error = '';
$success = '';

// Determine Mode
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit = $id > 0;

$blog = [
    'title' => '',
    'slug' => '',
    'description' => '',
    'icon' => 'fa-newspaper',
    'banner_image' => '',
    'published_date' => date('Y-m-d'),
    'tags' => '',
    'content_body' => '',
    'meta_title' => '',
    'meta_description' => '',
    'is_published' => 1
];

// Fetch existing blog if in edit mode
if ($isEdit) {
    try {
        $stmt = $db->prepare("SELECT * FROM blogs WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $fetched = $stmt->fetch();
        if ($fetched) {
            $blog = $fetched;
            // Decode tags from JSON array to comma-separated string for display
            $tagsArray = json_decode($blog['tags'], true);
            $blog['tags'] = is_array($tagsArray) ? implode(', ', $tagsArray) : '';
        } else {
            $isEdit = false; // Fallback to create mode if not found
            $error = 'Blog article not found. Creating a new one instead.';
        }
    } catch (PDOException $e) {
        $error = 'Failed to load article details. Please try again.';
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } else {
        $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-newspaper');
    $publishedDate = $_POST['published_date'] ?? date('Y-m-d');
    $tagsRaw = trim($_POST['tags'] ?? '');
    $contentBody = $_POST['content_body'] ?? '';
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDescription = trim($_POST['meta_description'] ?? '');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    
    // Process tags into JSON
    $tagsArray = array_filter(array_map('trim', explode(',', $tagsRaw)));
    $tagsJson = json_encode(array_values($tagsArray));

    // File Upload handling
    $bannerImage = $_POST['existing_banner'] ?? '';
    
    if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['banner_file']['tmp_name'];
        $fileName = $_FILES['banner_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // Generate slug-based filename to satisfy local storage rule
            $cleanSlug = preg_replace('/[^a-z0-9\-]/', '', $slug);
            $newFileName = 'blog-' . $cleanSlug . '-' . time() . '.' . $fileExtension;
            
            $uploadFileDir = __DIR__ . '/../assets/images/banners/';
            
            // Create directory if not exists
            if (!is_dir($uploadFileDir)) {
                @mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $bannerImage = 'assets/images/banners/' . $newFileName;
            } else {
                $error .= 'Error moving the uploaded file. ';
            }
        } else {
            $error .= 'Invalid upload file type. Allowed: ' . implode(', ', $allowedExtensions) . '. ';
        }
    }

    if (empty($title) || empty($slug)) {
        $error .= 'Title and Slug are required fields.';
    } else {
        try {
            // Check for slug uniqueness (excluding current article in edit mode)
            $checkSql = "SELECT COUNT(*) FROM blogs WHERE slug = ?" . ($isEdit ? " AND id != ?" : "");
            $checkParams = $isEdit ? [$slug, $id] : [$slug];
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute($checkParams);
            $slugExists = (int)$checkStmt->fetchColumn() > 0;
            
            if ($slugExists) {
                $error .= 'The slug is already in use by another article. Please modify the slug.';
            } else {
                if ($isEdit) {
                    $updateStmt = $db->prepare("
                        UPDATE blogs 
                        SET title = ?, slug = ?, description = ?, icon = ?, banner_image = ?, 
                            published_date = ?, tags = ?, content_body = ?, meta_title = ?, 
                            meta_description = ?, is_published = ?
                        WHERE id = ?
                    ");
                    $updateStmt->execute([
                        $title, $slug, $description, $icon, $bannerImage,
                        $publishedDate, $tagsJson, $contentBody, $metaTitle,
                        $metaDescription, $isPublished, $id
                    ]);
                    $success = 'Article updated successfully!';
                } else {
                    $insertStmt = $db->prepare("
                        INSERT INTO blogs (title, slug, description, icon, banner_image, published_date, tags, content_body, meta_title, meta_description, is_published)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insertStmt->execute([
                        $title, $slug, $description, $icon, $bannerImage,
                        $publishedDate, $tagsJson, $contentBody, $metaTitle,
                        $metaDescription, $isPublished
                    ]);
                    $newId = $db->lastInsertId();
                    // Redirect to edit page
                    header('Location: ' . BASE_URL . 'admin/blog-edit?id=' . $newId . '&success=1');
                    exit;
                }
                
                // Refresh local model array
                $blog = [
                    'title' => $title,
                    'slug' => $slug,
                    'description' => $description,
                    'icon' => $icon,
                    'banner_image' => $bannerImage,
                    'published_date' => $publishedDate,
                    'tags' => $tagsRaw,
                    'content_body' => $contentBody,
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription,
                    'is_published' => $isPublished
                ];
            }
        } catch (PDOException $e) {
            $error .= 'Database save failed. Please try again.';
        }
    }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Article created successfully!';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><?php echo $isEdit ? 'Edit Blog Post' : 'Create Blog Post'; ?></h1>
    <p><?php echo $isEdit ? 'Modify details, body content, and SEO metadata.' : 'Write a new blog post and publish it to the temple website.'; ?></p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/blogs" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 15px; border: 1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:var(--font-size-sm); display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-arrow-left"></i> Back to Blogs
    </a>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($success); ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header">
    <h2>Blog Content Details</h2>
  </div>
  <div class="admin-card-body">
    <form action="admin/blog-edit<?php echo $isEdit ? '?id=' . $id : ''; ?>" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <input type="hidden" name="existing_banner" value="<?php echo htmlspecialchars($blog['banner_image']); ?>">
      
      <div class="form-row">
        <div class="form-group">
          <label for="title">Title *</label>
          <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($blog['title']); ?>" placeholder="Enter blog title" required>
        </div>
        
        <div class="form-group">
          <label for="slug">URL Slug *</label>
          <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($blog['slug']); ?>" placeholder="e.g. janmashtami-celebration-2026" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="published_date">Publish Date</label>
          <input type="date" id="published_date" name="published_date" class="form-control" value="<?php echo htmlspecialchars($blog['published_date']); ?>">
        </div>
        
        <div class="form-group">
          <label for="icon">FontAwesome Icon Class</label>
          <input type="text" id="icon" name="icon" class="form-control" value="<?php echo htmlspecialchars($blog['icon']); ?>" placeholder="e.g. fa-newspaper, fa-pray, fa-heart">
        </div>
      </div>

      <div class="form-group">
        <label for="tags">Tags (Comma-separated)</label>
        <input type="text" id="tags" name="tags" class="form-control" value="<?php echo htmlspecialchars($blog['tags']); ?>" placeholder="e.g. Festivals, Chanting, Preaching">
      </div>

      <div class="form-group">
        <label for="description">Short Description (for Listing Cards) *</label>
        <textarea id="description" name="description" class="form-control" rows="3" placeholder="Enter a brief summary of the article..." required><?php echo htmlspecialchars($blog['description']); ?></textarea>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="banner_file">Upload Local Banner Image (Recommended: 1200x600 px)</label>
          <input type="file" id="banner_file" name="banner_file" class="form-control">
        </div>
        
        <div class="form-group">
          <label>Current Banner Path</label>
          <div style="display:flex; align-items:center; gap:var(--space-sm); height: 42px;">
            <?php if (!empty($blog['banner_image'])): ?>
              <div style="width:50px; height:35px; border-radius:4px; border:1px solid var(--border); background-image:url(<?php echo BASE_URL . $blog['banner_image']; ?>); background-size:cover; background-position:center;"></div>
              <span style="font-family:monospace; font-size:11px; color:var(--text-light); text-overflow:ellipsis; overflow:hidden; white-space:nowrap; max-width:200px;"><?php echo htmlspecialchars($blog['banner_image']); ?></span>
            <?php else: ?>
              <span style="color:var(--text-light); font-size:var(--font-size-sm); font-style:italic;">No banner image set.</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="content_body">Article Content (HTML Allowed) *</label>
        <div style="font-size:11px; color:var(--text-light); margin-bottom:6px;">Use standard HTML tags for formatting (e.g. &lt;p&gt;, &lt;h3&gt;, &lt;blockquote&gt;). Use <strong>{{BASE_URL}}</strong> placeholder for any relative image or asset links.</div>
        <textarea id="content_body" name="content_body" class="form-control" rows="12" placeholder="Write full article here..." required><?php echo htmlspecialchars($blog['content_body'] ?? ''); ?></textarea>
      </div>

      <h3 style="font-family:var(--font-heading); color:var(--dark); font-size:var(--font-size-md); border-bottom:1px solid var(--border); padding-bottom:6px; margin-top:var(--space-md); margin-bottom:4px;">SEO Metadata (Optional)</h3>
      
      <div class="form-row">
        <div class="form-group">
          <label for="meta_title">Meta Title</label>
          <input type="text" id="meta_title" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($blog['meta_title'] ?? ''); ?>" placeholder="Enter custom SEO title tag">
        </div>
        
        <div class="form-group">
          <label for="meta_description">Meta Description</label>
          <input type="text" id="meta_description" name="meta_description" class="form-control" value="<?php echo htmlspecialchars($blog['meta_description'] ?? ''); ?>" placeholder="Enter search engine snippet description">
        </div>
      </div>
      
      <div class="form-group" style="margin-top:var(--space-sm);">
        <label class="form-checkbox">
          <input type="checkbox" name="is_published" <?php echo $blog['is_published'] ? 'checked' : ''; ?>>
          Publish Immediately (Visible to the public)
        </label>
      </div>

      <div style="margin-top:var(--space-md); padding-top:var(--space-md); border-top:1px solid var(--border); display:flex; gap:10px;">
        <button type="submit" class="btn btn-primary" style="background-color: var(--primary); color:white; border:none; padding:12px 24px; border-radius:var(--radius-md); font-weight:600; cursor:pointer;">
          <i class="fas fa-save" style="margin-right:6px;"></i> <?php echo $isEdit ? 'Save Changes' : 'Publish Article'; ?>
        </button>
        <a href="admin/blogs" class="btn btn-outline-dark" style="text-decoration:none; padding:12px 24px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; text-align:center;">Cancel</a>
      </div>

    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const titleInput = document.getElementById('title');
  const slugInput = document.getElementById('slug');
  let userEditedSlug = <?php echo $isEdit ? 'true' : 'false'; ?>;

  function slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }

  slugInput.addEventListener('input', () => {
    userEditedSlug = true;
  });

  titleInput.addEventListener('input', () => {
    if (!userEditedSlug) {
      slugInput.value = slugify(titleInput.value);
    }
  });
});
</script>

<?php include 'partials/footer.php'; ?>
