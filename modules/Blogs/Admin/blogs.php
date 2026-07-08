<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('blogs.view');

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
        if (!hasPermission('blogs.delete')) {
            $error = 'You do not have permission to delete articles.';
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
    <?php if (hasPermission('blogs.create')): ?>
      <a href="admin/blog-edit" class="btn-admin btn-admin-primary">
        <i class="fas fa-plus"></i> Create New Blog
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<div class="admin-card">
  <div class="admin-card-header">
    <h2>All Articles (<?php echo count($blogsList); ?>)</h2>
  </div>
  <div class="admin-card-body admin-card-body-table">
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th class="w-50px">Icon</th>
            <th>Title</th>
            <th>Slug</th>
            <th>Publish Date</th>
            <th>Tags</th>
            <th>Status</th>
            <th class="w-180px text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($blogsList)): ?>
            <tr>
              <td colspan="7" class="admin-table-empty">No articles found. Click "Create New Blog" to write your first post.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($blogsList as $b): 
              $blogTags = !empty($b['tags']) ? json_decode($b['tags'], true) : [];
              $formattedDate = !empty($b['published_date']) ? date('M d, Y', strtotime($b['published_date'])) : 'No Date';
            ?>
              <tr>
                <td class="text-center">
                  <i class="fas <?php echo htmlspecialchars($b['icon'] ?: 'fa-newspaper'); ?> admin-icon-primary"></i>
                </td>
                <td>
                  <strong class="admin-title-strong"><?php echo htmlspecialchars($b['title']); ?></strong>
                </td>
                <td class="font-mono text-light fs-xs"><?php echo htmlspecialchars($b['slug']); ?></td>
                <td><?php echo $formattedDate; ?></td>
                <td>
                  <?php if (is_array($blogTags)): ?>
                    <div class="admin-tags-list">
                      <?php foreach ($blogTags as $t): ?>
                        <span class="admin-tag-badge"><?php echo htmlspecialchars($t); ?></span>
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
                <td class="text-center">
                  <div class="flex-center-gap-sm">
                    <?php if (hasPermission('blogs.edit')): ?>
                      <a href="admin/blogs?toggle_id=<?php echo $b['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-edit btn-action-icon-only" title="Toggle Publish Status">
                        <i class="fas <?php echo $b['is_published'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                      </a>
                      <a href="admin/blog-edit?id=<?php echo $b['id']; ?>" class="btn-sm-action btn-edit" title="Edit Article">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                    <?php endif; ?>
                    <?php if (hasPermission('blogs.delete')): ?>
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
