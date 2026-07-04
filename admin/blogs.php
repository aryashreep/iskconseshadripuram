<?php
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin', 'editor']);

$pageTitle = 'Manage Blogs';
$activePage = 'blogs';
include 'partials/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle quick actions: Toggle Publish Status
if (isset($_GET['toggle_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } else {
        $toggleId = intval($_GET['toggle_id']);
        try {
            // Get current status
            $stmt = $db->prepare("SELECT is_published FROM blogs WHERE id = ?");
            $stmt->execute([$toggleId]);
            $status = $stmt->fetchColumn();
            
            if ($status !== false) {
                $newStatus = $status ? 0 : 1;
                $update = $db->prepare("UPDATE blogs SET is_published = ? WHERE id = ?");
                $update->execute([$newStatus, $toggleId]);
                $message = 'Blog status updated successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update status. Please try again.';
        }
    }
}

// Handle quick actions: Delete Blog
if (isset($_GET['delete_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } else {
        if ($_SESSION['admin_role'] !== 'super_admin') {
            $error = 'Only Super Administrators are permitted to delete articles.';
        } else {
            $deleteId = intval($_GET['delete_id']);
            try {
                $stmt = $db->prepare("DELETE FROM blogs WHERE id = ?");
                $stmt->execute([$deleteId]);
                $message = 'Blog deleted successfully.';
            } catch (PDOException $e) {
                $error = 'Failed to delete blog. Please try again.';
            }
        }
    }
}

// Fetch all blogs
try {
    $stmt = $db->query("SELECT id, slug, title, published_date, tags, is_published, icon FROM blogs ORDER BY published_date DESC, id DESC");
    $blogsList = $stmt->fetchAll();
} catch (PDOException $e) {
    $blogsList = [];
    $error = 'Failed to fetch blogs. Please try again.';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Manage Blogs & Articles</h1>
    <p>Create, edit, delete, or toggle draft status for devotee articles.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/blog-edit" class="btn btn-primary" style="background-color: var(--primary); text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-plus"></i> Create New Blog
    </a>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>All Articles (<?php echo count($blogsList); ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 50px;">Icon</th>
            <th>Title</th>
            <th>Slug</th>
            <th>Publish Date</th>
            <th>Tags</th>
            <th>Status</th>
            <th style="width: 180px; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($blogsList)): ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No articles found. Click "Create New Blog" to write your first post.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($blogsList as $b): 
              $blogTags = !empty($b['tags']) ? json_decode($b['tags'], true) : [];
              $formattedDate = !empty($b['published_date']) ? date('M d, Y', strtotime($b['published_date'])) : 'No Date';
            ?>
              <tr>
                <td style="text-align:center;">
                  <i class="fas <?php echo htmlspecialchars($b['icon'] ?: 'fa-newspaper'); ?>" style="color: var(--primary); font-size: 16px;"></i>
                </td>
                <td>
                  <strong style="color: var(--dark); font-size:14px;"><?php echo htmlspecialchars($b['title']); ?></strong>
                </td>
                <td style="font-family: monospace; font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($b['slug']); ?></td>
                <td><?php echo $formattedDate; ?></td>
                <td>
                  <?php if (is_array($blogTags)): ?>
                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                      <?php foreach ($blogTags as $t): ?>
                        <span style="font-size:10px; background:var(--cream); color:var(--primary-dark); padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($t); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($b['is_published']): ?>
                    <span class="badge badge-published">Published</span>
                  <?php else: ?>
                    <span class="badge badge-draft">Draft</span>
                  <?php endif; ?>
                </td>
                <td style="text-align: center;">
                  <div style="display:flex; justify-content:center; gap:6px;">
                    <a href="admin/blogs?toggle_id=<?php echo $b['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-edit" title="Toggle Publish Status" style="padding: 4px 6px;">
                      <i class="fas <?php echo $b['is_published'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                    </a>
                    <a href="admin/blog-edit?id=<?php echo $b['id']; ?>" class="btn-sm-action btn-edit" title="Edit Article">
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
                      <a href="admin/blogs?delete_id=<?php echo $b['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-delete" onclick="return confirm('Are you sure you want to delete this article? This action cannot be undone.');" title="Delete Article">
                        <i class="fas fa-trash"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
