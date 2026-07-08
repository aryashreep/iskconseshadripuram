<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('seva_catalog.view');

$pageTitle = 'Seva Catalogue';
$activePage = 'seva-catalogue';
include 'partials/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle quick actions: Toggle Active Status
if (isset($_GET['toggle_active_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('seva_catalog.edit')) {
        $error = 'You do not have permission to modify seva status.';
    } else {
        $toggleId = intval($_GET['toggle_active_id']);
        try {
            $stmt = $db->prepare("SELECT is_active FROM master_sevas WHERE id = ?");
            $stmt->execute([$toggleId]);
            $status = $stmt->fetchColumn();

            if ($status !== false) {
                $newStatus = $status ? 0 : 1;
                $update = $db->prepare("UPDATE master_sevas SET is_active = ? WHERE id = ?");
                $update->execute([$newStatus, $toggleId]);
                $message = 'Seva active status updated successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update status. Please try again.';
        }
    }
}

// Handle quick actions: Toggle Featured Status
if (isset($_GET['toggle_featured_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('seva_catalog.edit')) {
        $error = 'You do not have permission to modify featured status.';
    } else {
        $toggleId = intval($_GET['toggle_featured_id']);
        try {
            $stmt = $db->prepare("SELECT is_featured FROM master_sevas WHERE id = ?");
            $stmt->execute([$toggleId]);
            $status = $stmt->fetchColumn();

            if ($status !== false) {
                $newStatus = $status ? 0 : 1;
                $update = $db->prepare("UPDATE master_sevas SET is_featured = ? WHERE id = ?");
                $update->execute([$newStatus, $toggleId]);
                $message = 'Seva featured status updated successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update status. Please try again.';
        }
    }
}

// Handle quick actions: Archive (soft-delete) a seva
if (isset($_GET['archive_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('seva_catalog.delete')) {
        $error = 'You do not have permission to archive sevas.';
    } else {
        $archiveId = intval($_GET['archive_id']);
        $result = archiveMasterSeva($archiveId, false);
        if ($result === true) {
            $message = 'Seva archived successfully.';
        } elseif (is_string($result)) {
            $message = htmlspecialchars($result);
        } else {
            $error = htmlspecialchars($result);
        }
    }
}

// Fetch the full catalog with usage counts
$catalog = getMasterSevasWithUsageByCategory(false); // active only
$catalogAll = getMasterSevasWithUsageByCategory(true); // include inactive for counts

// Calculate summary stats
$totalActiveSevas = 0;
$totalFeaturedSevas = 0;
$totalCategories = count($catalog);
foreach ($catalog as $g) {
    $totalActiveSevas += count($g['items']);
    foreach ($g['items'] as $item) {
        if ($item['is_featured']) $totalFeaturedSevas++;
    }
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Seva Catalogue</h1>
    <p>Manage the Master Seva Catalog — the reusable library of seva offerings available across all festivals & causes.</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('seva_catalog.create')): ?>
      <a href="admin/seva-catalogue-edit" class="btn btn-primary" style="background-color: var(--primary); text-decoration:none; padding:10px 20px; border-radius:var(--radius-md); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
        <i class="fas fa-plus"></i> Add New Seva
      </a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($message)): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo $message; ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- Summary Stats -->
<div class="admin-stats-grid" style="margin-bottom: var(--space-xl);">
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Categories</h3>
      <div class="admin-stat-value" style="font-size: 28px;"><?php echo $totalCategories; ?></div>
    </div>
    <div class="admin-stat-icon">
      <i class="fas fa-folder-tree"></i>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Active Sevas</h3>
      <div class="admin-stat-value" style="font-size: 28px; color: var(--primary-dark);"><?php echo $totalActiveSevas; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: rgba(200, 107, 31, 0.15); color: var(--primary-dark);">
      <i class="fas fa-hand-holding-heart"></i>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Featured Sevas</h3>
      <div class="admin-stat-value" style="font-size: 28px; color: var(--accent);"><?php echo $totalFeaturedSevas; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: rgba(212, 175, 55, 0.15); color: var(--accent);">
      <i class="fas fa-star"></i>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Inactive / Archived</h3>
      <div class="admin-stat-value" style="font-size: 28px; color: var(--text-light);">
        <?php 
        $inactiveCount = 0;
        foreach ($catalogAll as $g) {
          foreach ($g['items'] as $item) {
            if (!$item['is_active']) $inactiveCount++;
          }
        }
        echo $inactiveCount;
        ?>
      </div>
    </div>
    <div class="admin-stat-icon" style="background-color: #f5f5f5; color: var(--text-light);">
      <i class="fas fa-archive"></i>
    </div>
  </div>
</div>

<!-- Catalog Sections -->
<div class="master-catalogue-admin">
  <?php foreach ($catalog as $catGroup): 
    $cat = $catGroup['category'];
    $items = $catGroup['items'];
    $activeCount = count($items);
    $totalForCat = 0;
    foreach ($catalogAll as $g) {
      if ($g['category']['id'] === $cat['id']) {
        $totalForCat = count($g['items']);
        break;
      }
    }
    $archivedCount = $totalForCat - $activeCount;
  ?>
  <div class="catalogue-category-section">
    <div class="catalogue-category-header" onclick="toggleCatalogueCategory(this)">
      <div class="catalogue-category-title">
        <i class="fas <?php echo htmlspecialchars($cat['icon'] ?? 'fa-hand-holding-heart'); ?>"></i>
        <span class="catalogue-category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
        <span class="catalogue-count-badge"><?php echo $activeCount; ?> active</span>
        <?php if ($archivedCount > 0): ?>
          <span class="catalogue-count-badge badge-archived"><?php echo $archivedCount; ?> archived</span>
        <?php endif; ?>
      </div>
      <div class="catalogue-header-actions">
        <?php if (hasPermission('seva_catalog.create')): ?>
          <a href="admin/seva-catalogue-edit?cat_id=<?php echo $cat['id']; ?>" class="btn-sm-action btn-edit" title="Add seva in this category">
            <i class="fas fa-plus"></i> Add
          </a>
        <?php endif; ?>
        <i class="fas fa-chevron-down catalogue-chevron"></i>
      </div>
    </div>
    <div class="catalogue-category-body">
      <?php if (empty($items)): ?>
        <div class="catalogue-empty">
          <i class="fas fa-inbox"></i>
          <span>No sevas in this category yet.</span>
          <?php if (hasPermission('seva_catalog.create')): ?>
          <a href="admin/seva-catalogue-edit?cat_id=<?php echo $cat['id']; ?>" class="btn-sm-action btn-edit">Add first seva</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
      <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
        <table class="admin-table catalogue-seva-table">
          <thead>
            <tr>
              <th style="width: 30px;">#</th>
              <th>Name</th>
              <th style="width: 100px;">Default Amount</th>
              <th style="width: 60px; text-align:center;">Multi</th>
              <th style="width: 60px; text-align:center;">Max Qty</th>
              <th style="width: 70px; text-align:center;">Sort</th>
              <th style="width: 70px; text-align:center;">Featured</th>
              <th style="width: 60px; text-align:center;">Active</th>
              <th style="width: 80px; text-align:center;">Linked To</th>
              <th style="width: 180px; text-align:center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $rowNum = 0; ?>
            <?php foreach ($items as $item): 
              $rowNum++;
            ?>
            <tr class="<?php echo !$item['is_active'] ? 'row-inactive' : ''; ?>">
              <td style="text-align:center; color:var(--text-light); font-size:11px;"><?php echo $rowNum; ?></td>
              <td>
                <div class="catalogue-seva-name">
                  <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                  <?php if ($item['sanskrit_name']): ?>
                    <span class="catalogue-sanskrit"><?php echo htmlspecialchars($item['sanskrit_name']); ?></span>
                  <?php endif; ?>
                </div>
                <?php if ($item['short_description']): ?>
                  <div class="catalogue-short-desc"><?php echo htmlspecialchars($item['short_description']); ?></div>
                <?php endif; ?>
                <div class="catalogue-slug"><code><?php echo htmlspecialchars($item['slug']); ?></code></div>
              </td>
              <td style="font-weight:600; color:var(--maroon);">₹<?php echo number_format((float)$item['default_amount']); ?></td>
              <td style="text-align:center;">
                <?php if ($item['allow_multiple']): ?>
                  <span class="badge badge-multi">Yes</span>
                <?php else: ?>
                  <span class="badge badge-single">No</span>
                <?php endif; ?>
              </td>
              <td style="text-align:center;"><?php echo (int)$item['max_quantity']; ?></td>
              <td style="text-align:center; color:var(--text-light);"><?php echo (int)$item['sort_order']; ?></td>
              <td style="text-align:center;">
                <?php if (hasPermission('seva_catalog.edit')): ?>
                <a href="admin/seva-catalogue?toggle_featured_id=<?php echo $item['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                   class="btn-sm-action" 
                   style="background:none; border:none; text-decoration:none; color:<?php echo $item['is_featured'] ? 'var(--accent)' : '#ccc'; ?>; font-size:14px; cursor:pointer;" 
                   title="Toggle Featured">
                  <i class="<?php echo $item['is_featured'] ? 'fas fa-star' : 'far fa-star'; ?>"></i>
                </a>
                <?php else: ?>
                <i class="<?php echo $item['is_featured'] ? 'fas fa-star' : 'far fa-star'; ?>" style="color:<?php echo $item['is_featured'] ? 'var(--accent)' : '#ccc'; ?>; font-size:14px;"></i>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <?php if (hasPermission('seva_catalog.edit')): ?>
                <a href="admin/seva-catalogue?toggle_active_id=<?php echo $item['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                   class="btn-sm-action" 
                   style="background:none; border:none; text-decoration:none; font-size:16px; cursor:pointer;" 
                   title="Toggle Active">
                  <i class="fas <?php echo $item['is_active'] ? 'fa-check-circle' : 'fa-times-circle'; ?>" 
                     style="color: <?php echo $item['is_active'] ? '#22c55e' : '#ccc'; ?>;"></i>
                </a>
                <?php else: ?>
                <i class="fas <?php echo $item['is_active'] ? 'fa-check-circle' : 'fa-times-circle'; ?>" style="color:<?php echo $item['is_active'] ? '#22c55e' : '#ccc'; ?>; font-size:16px;"></i>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <span class="catalogue-usage-count" title="Causes linked to this seva">
                  <?php echo (int)$item['usage_count']; ?>
                </span>
              </td>
              <td style="text-align:center;">
                <div style="display:flex; justify-content:center; gap:4px;">
                  <?php if (hasPermission('seva_catalog.edit')): ?>
                  <a href="admin/seva-catalogue-edit?id=<?php echo $item['id']; ?>" class="btn-sm-action btn-edit" title="Edit Seva">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <?php endif; ?>
                  <?php if (hasPermission('seva_catalog.delete')): ?>
                    <a href="admin/seva-catalogue?archive_id=<?php echo $item['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                       class="btn-sm-action btn-delete" 
                       onclick="return confirm('Are you sure you want to archive this seva?\n\nIt is linked to <?php echo (int)$item['usage_count']; ?> cause(s). Archiving hides it from public pages while preserving existing links.');" 
                       title="Archive Seva">
                      <i class="fas fa-archive"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (empty($catalog)): ?>
    <div style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">
      <i class="fas fa-database" style="font-size:40px; margin-bottom:var(--space-lg); display:block;"></i>
      <h3 style="color:var(--text); margin-bottom:var(--space-sm);">No Sevas Found</h3>
      <p>The Master Seva Catalog is empty or has no active categories.</p>
    </div>
  <?php endif; ?>
</div>

<!-- JS for category accordion -->
<script>
function toggleCatalogueCategory(header) {
  const section = header.closest('.catalogue-category-section');
  const body = section.querySelector('.catalogue-category-body');
  const chevron = header.querySelector('.catalogue-chevron');

  if (section.classList.contains('open')) {
    section.classList.remove('open');
    body.style.maxHeight = '0';
    body.style.opacity = '0';
    chevron.style.transform = 'rotate(0deg)';
  } else {
    section.classList.add('open');
    body.style.maxHeight = 'none';
    body.style.opacity = '1';
    chevron.style.transform = 'rotate(180deg)';
  }
}

// Auto-open all accordions on page load (optional — remove if too crowded)
document.addEventListener('DOMContentLoaded', function() {
  // Open the first category by default
  const first = document.querySelector('.catalogue-category-section');
  if (first) {
    const header = first.querySelector('.catalogue-category-header');
    if (header) toggleCatalogueCategory(header);
  }
});
</script>

<?php include 'partials/footer.php'; ?>
