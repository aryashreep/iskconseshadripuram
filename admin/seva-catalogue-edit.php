<?php
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin', 'editor']);

$pageTitle = 'Edit Seva';
$activePage = 'seva-catalogue-edit';
include 'partials/header.php';

$db = getDB();
$error = '';
$success = '';

// Determine Mode
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$presetCatId = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
$isEdit = $id > 0;

// Default Seva structure
$seva = [
    'id' => 0,
    'slug' => '',
    'name' => '',
    'sanskrit_name' => '',
    'category_id' => 0,
    'default_amount' => 100.00,
    'image_url' => '',
    'icon' => 'fa-hand-holding-heart',
    'is_featured' => 0,
    'is_active' => 1,

];

// Fetch categories for dropdown
$categories = getMasterSevaCategoriesForSelect();

// Fetch existing seva if in Edit Mode
if ($isEdit) {
    $fetched = getMasterSevaById($id);
    if ($fetched) {
        $seva = $fetched;
    } else {
        $isEdit = false;
        $error .= 'Seva not found. Switching to Create Mode. ';
    }
} elseif ($presetCatId > 0) {
    // Pre-select category if cat_id is provided in URL
    $seva['category_id'] = $presetCatId;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error .= 'CSRF validation failed. Unauthorized request. ';
    } else {
        // Collect fields
        $slug = trim($_POST['slug'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $sanskritName = trim($_POST['sanskrit_name'] ?? '');

        $categoryId = intval($_POST['category_id'] ?? 0);
        $defaultAmount = floatval($_POST['default_amount'] ?? 0);

        $imageUrl = trim($_POST['image_url'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-hand-holding-heart');

        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;


        // Validation
        if (empty($name)) {
            $error .= 'Seva name is required. ';
        }
        if (empty($slug)) {
            $error .= 'URL slug is required. ';
        }
        if ($categoryId <= 0) {
            $error .= 'Category is required. ';
        }
        if ($defaultAmount < 0) {
            $error .= 'Default amount cannot be negative. ';
        }


        if (empty($error)) {
            try {
                // Check slug uniqueness
                $checkSql = "SELECT COUNT(*) FROM master_sevas WHERE slug = ?" . ($isEdit ? " AND id != ?" : "");
                $checkParams = $isEdit ? [$slug, $id] : [$slug];
                $checkStmt = $db->prepare($checkSql);
                $checkStmt->execute($checkParams);
                $slugExists = (int)$checkStmt->fetchColumn() > 0;

                if ($slugExists) {
                    $error .= 'The slug is already in use by another seva. ';
                } else {
                    $data = [
                        'slug' => $slug,
                        'name' => $name,
                        'sanskrit_name' => $sanskritName ?: null,

                        'category_id' => $categoryId,
                        'default_amount' => $defaultAmount,
                        'image_url' => $imageUrl ?: null,
                        'icon' => $icon,
                        'is_featured' => $isFeatured,
                        'is_active' => $isActive,
                    ];

                    if ($isEdit) {
                        if (updateMasterSeva($id, $data)) {
                            $success = 'Seva updated successfully!';
                            // Reload seva data
                            $seva = getMasterSevaById($id);
                        } else {
                            $error .= 'Failed to update seva. Please try again. ';
                        }
                    } else {
                        $newId = createMasterSeva($data);
                        if ($newId) {
                            header('Location: ' . BASE_URL . 'admin/seva-catalogue-edit?id=' . $newId . '&success=1');
                            exit;
                        } else {
                            $error .= 'Failed to create seva. Please try again. ';
                        }
                    }
                }
            } catch (Exception $e) {
                $error .= 'An error occurred. Please try again.';
            }
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Seva created successfully!';
    // Reload if we have an id
    if ($id > 0) {
        $seva = getMasterSevaById($id);
    }
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><?php echo $isEdit ? 'Edit Seva' : 'Add New Seva'; ?></h1>
    <p><?php echo $isEdit ? 'Edit this master seva offering in the catalog.' : 'Create a new reusable seva offering in the Master Seva Catalog.'; ?></p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/seva-catalogue" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 15px; border: 1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:var(--font-size-sm); display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-arrow-left"></i> Back to Catalogue
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

<form action="admin/seva-catalogue-edit<?php echo $isEdit ? '?id=' . $id : ($presetCatId > 0 ? '?cat_id=' . $presetCatId : ''); ?>" method="POST" style="display:flex; flex-direction:column; gap:var(--space-xl); margin-bottom: var(--space-3xl);">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

  <!-- Core Details Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Seva Details</h2>
    </div>
    <div class="admin-card-body" style="display:flex; flex-direction:column; gap:var(--space-md);">

      <div class="form-row">
        <div class="form-group" style="flex:1;">
          <label for="name">Seva Name *</label>
          <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($seva['name']); ?>" placeholder="e.g. Rose Garland Offering" required>
        </div>
        <div class="form-group" style="flex:1;">
          <label for="sanskrit_name">Sanskrit Name (optional)</label>
          <input type="text" id="sanskrit_name" name="sanskrit_name" class="form-control" value="<?php echo htmlspecialchars($seva['sanskrit_name'] ?? ''); ?>" placeholder="e.g. Puṣpa Mālā">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group" style="flex:1;">
          <label for="slug">URL Slug *</label>
          <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($seva['slug']); ?>" placeholder="e.g. rose-garland-offering" required>
        </div>
        <div class="form-group" style="flex:1;">
          <label for="category_id">Category *</label>
          <select id="category_id" name="category_id" class="form-control" required>
            <option value="">— Select Category —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>" <?php echo (int)$seva['category_id'] === (int)$cat['id'] ? 'selected' : ''; ?>>
                <i class="fas <?php echo htmlspecialchars($cat['icon'] ?? 'fa-hand-holding-heart'); ?>"></i>
                <?php echo htmlspecialchars($cat['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group" style="flex:1;">
          <label for="default_amount">Default Amount (₹) *</label>
          <input type="number" id="default_amount" name="default_amount" class="form-control" value="<?php echo floatval($seva['default_amount']); ?>" step="1" min="0" required>
        </div>
        <div class="form-group" style="flex:1;">
          <label for="icon">FontAwesome Icon</label>
          <input type="text" id="icon" name="icon" class="form-control" value="<?php echo htmlspecialchars($seva['icon']); ?>" placeholder="e.g. fa-om">
        </div>
      </div>

    </div>
  </div>

  <!-- Status & Featured Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Status & Visibility</h2>
    </div>
    <div class="admin-card-body">

      <div class="form-row">
        <div class="form-group">
          <label class="form-checkbox">
            <input type="checkbox" name="is_active" <?php echo $seva['is_active'] ? 'checked' : ''; ?>>
            Active (Visible to users)
          </label>
        </div>
        <div class="form-group">
          <label class="form-checkbox">
            <input type="checkbox" name="is_featured" <?php echo $seva['is_featured'] ? 'checked' : ''; ?>>
            Featured (Highlighted in category)
          </label>
        </div>
        <div class="form-group" style="flex:1;">
          <label for="image_url">Image URL</label>
          <input type="text" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($seva['image_url'] ?? ''); ?>" placeholder="Optional image URL for this seva">
        </div>
      </div>

      <?php if ($isEdit): ?>
      <div class="form-row" style="margin-top: var(--space-md); padding: var(--space-md); background: #f9f9f9; border-radius: var(--radius-md);">
        <div style="font-size: 12px; color: var(--text-light); line-height: 1.6;">
          <strong><i class="fas fa-info-circle"></i> Important Note:</strong>
          Changes made here affect the <strong>default</strong> values for this seva across all linked causes.
          Individual festivals/causes may have override values (set in the festival editor) that take precedence.
          Currently linked to <strong><?php echo getMasterSevaUsageCount($id); ?> cause(s)</strong>.
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Form Actions -->
  <div style="display:flex; gap:12px; padding: var(--space-md) 0;">
    <button type="submit" class="btn btn-primary" style="background-color: var(--primary); color:white; border:none; padding:14px 28px; border-radius:var(--radius-md); font-weight:600; cursor:pointer;">
      <i class="fas fa-save" style="margin-right:6px;"></i> <?php echo $isEdit ? 'Save Changes' : 'Create Seva'; ?>
    </button>
    <a href="admin/seva-catalogue" class="btn btn-outline-dark" style="text-decoration:none; padding:14px 28px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600;">Cancel</a>
  </div>

</form>

<script>
// Slug auto-generate from name
document.addEventListener('DOMContentLoaded', function() {
  const nameInput = document.getElementById('name');
  const slugInput = document.getElementById('slug');
  let userEditedSlug = <?php echo $isEdit ? 'true' : 'false'; ?>;

  function slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+/, '')
      .replace(/-+$/, '');
  }

  if (slugInput) {
    slugInput.addEventListener('input', () => {
      userEditedSlug = true;
    });
  }

  if (nameInput && slugInput) {
    nameInput.addEventListener('input', () => {
      if (!userEditedSlug) {
        slugInput.value = slugify(nameInput.value);
      }
    });
  }
});
</script>

<?php include 'partials/footer.php'; ?>
