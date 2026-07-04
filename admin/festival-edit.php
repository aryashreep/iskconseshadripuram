<?php
require_once __DIR__ . '/auth-check.php';
requireRole(['super_admin', 'editor']);

$pageTitle = 'Edit Festival / Cause';
$activePage = 'festivals';
include 'partials/header.php';

$db = getDB();
$error = '';
$success = '';

// Determine Mode
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit = $id > 0;

// Default Cause structure
$cause = [
    'title' => '',
    'slug' => '',
    'short_title' => '',
    'description' => '',
    'history' => '',
    'significance' => '',
    'benefits' => '',
    'category' => 'festival',
    'subcategory' => '',
    'image_url' => '',

    'allow_one_time' => 1,
    'allow_monthly' => 0,
    'default_mode' => 'one_time',

    'sort_order' => 10,
    'featured' => 0,
    'is_active' => 1,
    'content_body' => '',

    'meta_title' => '',
    'meta_description' => ''
];

// Master Catalog data
$linkedMasterSevasDetailed = [];
$linkedMasterSevas = [];
$oldSevas = [];
$masterCategories = [];

// Fetch master categories for the picker dropdown
try {
    $masterCategories = getMasterSevaCategoriesForSelect();
} catch (Exception $e) {
    $error .= 'Failed to load Master Categories. Please try again.';
}

// Fetch existing cause and sevas if in Edit Mode
if ($isEdit) {
    try {
        $stmt = $db->prepare("SELECT * FROM donation_causes WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $fetched = $stmt->fetch();
        if ($fetched) {
            $cause = $fetched;
            
            // Fetch linked master sevas (Phase 3)
            $linkedMasterSevasDetailed = getCauseLinkedMasterSevasDetailed($id);
            // Build the indexed set for quick JS bootstrap
            $linkedMasterSevas = getCauseLinkedMasterSevas($id);
            // Fetch old sevas for informational display
            $oldSevas = getCauseOldSevas($id);
        } else {
            $isEdit = false;
            $error .= 'Cause not found. Switching to Creation Mode. ';
        }
    } catch (PDOException $e) {
        $error .= 'Failed to load festival details. Please try again. ';
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error .= 'CSRF validation failed. Unauthorized request. ';
    } else {
        // Collect cause fields
        $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $shortTitle = trim($_POST['short_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $history = trim($_POST['history'] ?? '');
    $significance = trim($_POST['significance'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');
    $category = $_POST['category'] ?? 'festival';
    $subcategory = trim($_POST['subcategory'] ?? '');

    $allowOneTime = isset($_POST['allow_one_time']) ? 1 : 0;
    $allowMonthly = isset($_POST['allow_monthly']) ? 1 : 0;
    $defaultMode = $_POST['default_mode'] ?? 'one_time';

    $sortOrder = intval($_POST['sort_order'] ?? 10);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $contentBody = $_POST['content_body'] ?? '';

    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDescription = trim($_POST['meta_description'] ?? '');
    
    // File Upload handling
    $imageUrl = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_file']['tmp_name'];
        $fileName = $_FILES['image_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $cleanSlug = preg_replace('/[^a-z0-9\-]/', '', $slug);
            $newFileName = 'festival-' . $cleanSlug . '-' . time() . '.' . $fileExtension;
            
            $uploadFileDir = __DIR__ . '/../assets/images/banners/';
            if (!is_dir($uploadFileDir)) {
                @mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageUrl = 'assets/images/banners/' . $newFileName;
            } else {
                $error .= 'Error moving the uploaded file. ';
            }
        } else {
            $error .= 'Invalid upload file type. Allowed: ' . implode(', ', $allowedExtensions) . '. ';
        }
    }

    if (empty($title) || empty($slug)) {
        $error .= 'Title and Slug are required. ';
    } else {
        try {
            // Check for slug uniqueness
            $checkSql = "SELECT COUNT(*) FROM donation_causes WHERE slug = ?" . ($isEdit ? " AND id != ?" : "");
            $checkParams = $isEdit ? [$slug, $id] : [$slug];
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute($checkParams);
            $slugExists = (int)$checkStmt->fetchColumn() > 0;
            
            if ($slugExists) {
                $error .= 'The slug is already in use by another cause. ';
            } else {
                // Begin Transaction to save cause and sevas atomically
                $db->beginTransaction();
                
                if ($isEdit) {
                    $updateStmt = $db->prepare("
                        UPDATE donation_causes 
                        SET title = ?, slug = ?, short_title = ?, description = ?, history = ?, 
                            significance = ?, benefits = ?, category = ?, subcategory = ?, image_url = ?, 
                            allow_one_time = ?, 
                            allow_monthly = ?, default_mode = ?, 
                            sort_order = ?, featured = ?, is_active = ?, content_body = ?, 
                            meta_title = ?, meta_description = ?
                        WHERE id = ?
                    ");
                    $updateStmt->execute([
                        $title, $slug, $shortTitle, $description, $history,
                        $significance, $benefits, $category, $subcategory, $imageUrl,
                        $allowOneTime,
                        $allowMonthly, $defaultMode,
                        $sortOrder, $featured, $isActive, $contentBody, $metaTitle, $metaDescription, $id
                    ]);
                    $causeId = $id;
                } else {
                    $insertStmt = $db->prepare("
                        INSERT INTO donation_causes (
                            title, slug, short_title, description, history, significance, benefits, 
                            category, subcategory, image_url,                            allow_one_time, allow_monthly, default_mode, 
                            sort_order, featured, is_active, content_body, meta_title, meta_description
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insertStmt->execute([
                        $title, $slug, $shortTitle, $description, $history, $significance, $benefits,
                        $category, $subcategory, $imageUrl,                        $allowOneTime, $allowMonthly, $defaultMode,
                        $sortOrder, $featured, $isActive, $contentBody, $metaTitle, $metaDescription
                    ]);
                    $causeId = (int)$db->lastInsertId();
                }
                
                // Sync Master Catalog Sevas (Phase 3)
                $submittedMasterSevaIds = [];
                if (isset($_POST['master_sevas']) && is_array($_POST['master_sevas'])) {
                    foreach ($_POST['master_sevas'] as $masterSevaId => $sData) {
                        $masterSevaId = intval($masterSevaId);
                        if ($masterSevaId <= 0) continue;
                        
                        $isLinked = isset($sData['linked']) ? 1 : 0;
                        $overrideAmount = !empty($sData['override_amount']) ? floatval($sData['override_amount']) : null;
                
                        $overrideMaxQty = !empty($sData['override_max_quantity']) ? intval($sData['override_max_quantity']) : null;
                        $sortOrder = intval($sData['sort_order'] ?? 0);
                        $isFeatured = isset($sData['is_featured']) ? 1 : 0;
                        
                        if (!$isLinked) {
                            // Unlink: deactivate the link
                            $delStmt = $db->prepare("
                                UPDATE donation_cause_master_sevas 
                                SET is_active = 0 
                                WHERE cause_id = ? AND master_seva_id = ?
                            ");
                            $delStmt->execute([$causeId, $masterSevaId]);
                            continue;
                        }
                        
                        // Check if link already exists
                        $checkStmt = $db->prepare("
                            SELECT id, is_active FROM donation_cause_master_sevas 
                            WHERE cause_id = ? AND master_seva_id = ?
                        ");
                        $checkStmt->execute([$causeId, $masterSevaId]);
                        $existingLink = $checkStmt->fetch();
                        
                        if ($existingLink) {
                            // Update existing link
                            $upStmt = $db->prepare("
                                UPDATE donation_cause_master_sevas 
                                SET override_amount = ?, override_description = ?, override_max_quantity = ?,
                                    sort_order = ?, is_featured = ?, is_active = 1
                                WHERE id = ?
                            ");
                            $upStmt->execute([
                                $overrideAmount, 
                                null, 
                                $overrideMaxQty,
                                $sortOrder, $isFeatured, 
                                $existingLink['id']
                            ]);
                            $submittedMasterSevaIds[] = $masterSevaId;
                        } else {
                            // Create new link
                            $inStmt = $db->prepare("
                                INSERT INTO donation_cause_master_sevas 
                                (cause_id, master_seva_id, override_amount, override_description, override_max_quantity, sort_order, is_featured, is_active)
                                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                            ");
                            $inStmt->execute([
                                $causeId, $masterSevaId, $overrideAmount,
                                null,
                                $overrideMaxQty, $sortOrder, $isFeatured
                            ]);
                            $submittedMasterSevaIds[] = $masterSevaId;
                        }
                    }
                }
                
                // Also deactivate master sevas that were not submitted (uncheck all then submit)
                if ($isEdit && isset($_POST['master_sevas_submitted'])) {
                    $deactivateStmt = $db->prepare("
                        UPDATE donation_cause_master_sevas 
                        SET is_active = 0 
                        WHERE cause_id = ? AND is_active = 1" . 
                        (!empty($submittedMasterSevaIds) ? " AND master_seva_id NOT IN (" . implode(',', array_map('intval', $submittedMasterSevaIds)) . ")" : "")
                    );
                    $deactivateStmt->execute([$causeId]);
                }
                
                $db->commit();
                
                if ($isEdit) {
                    $success = 'Festival / Cause and Seva tiers updated successfully!';
                    
                    // Reload cause details and linked sevas
                    $stmt = $db->prepare("SELECT * FROM donation_causes WHERE id = ?");
                    $stmt->execute([$causeId]);
                    $cause = $stmt->fetch();
                    
                    $linkedMasterSevasDetailed = getCauseLinkedMasterSevasDetailed($causeId);
                    $linkedMasterSevas = getCauseLinkedMasterSevas($causeId);
                    $oldSevas = getCauseOldSevas($causeId);
                } else {
                    header('Location: ' . BASE_URL . 'admin/festival-edit?id=' . $causeId . '&success=1');
                    exit;
                }
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error .= 'Transaction failed. Please try again.';
        }
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = 'Festival / Cause created successfully!';
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><?php echo $isEdit ? 'Edit Festival / Cause' : 'Add Festival / Cause'; ?></h1>
    <p>Configure historical details, categories, schedules, and link active sponsorships.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/festivals" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 15px; border: 1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:var(--font-size-sm); display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-arrow-left"></i> Back to Causes
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
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo $error; ?>
  </div>
<?php endif; ?>

<form action="admin/festival-edit<?php echo $isEdit ? '?id=' . $id : ''; ?>" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:var(--space-xl); margin-bottom: var(--space-3xl);">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
  <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($cause['image_url']); ?>">

  <!-- Cause Core Details Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>General Information</h2>
    </div>
    <div class="admin-card-body" style="display:flex; flex-direction:column; gap:var(--space-md);">
      
      <div class="form-row">
        <div class="form-group">
          <label for="title">Title *</label>
          <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($cause['title']); ?>" placeholder="e.g. Sri Krishna Janmashtami" required>
        </div>
        
        <div class="form-group">
          <label for="slug">URL Slug *</label>
          <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($cause['slug']); ?>" placeholder="e.g. janmashtami" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="short_title">Short Title / Menu Label</label>
          <input type="text" id="short_title" name="short_title" class="form-control" value="<?php echo htmlspecialchars($cause['short_title']); ?>" placeholder="e.g. Janmashtami">
        </div>
        
        <div class="form-group">
          <label for="category">Category *</label>
          <select id="category" name="category" class="form-control" required>
            <option value="festival" <?php echo $cause['category'] === 'festival' ? 'selected' : ''; ?>>Grand Festival (festival)</option>
            <option value="ekadashi" <?php echo $cause['category'] === 'ekadashi' ? 'selected' : ''; ?>>Ekadashi (ekadashi)</option>
            <option value="appearance" <?php echo $cause['category'] === 'appearance' ? 'selected' : ''; ?>>Appearance Day (appearance)</option>
            <option value="disappearance" <?php echo $cause['category'] === 'disappearance' ? 'selected' : ''; ?>>Disappearance Day (disappearance)</option>
            <option value="event" <?php echo $cause['category'] === 'event' ? 'selected' : ''; ?>>Special Event (event)</option>
            <option value="service" <?php echo $cause['category'] === 'service' ? 'selected' : ''; ?>>Evergreen Service (service)</option>
            <option value="construction" <?php echo $cause['category'] === 'construction' ? 'selected' : ''; ?>>Construction Fund (construction)</option>
            <option value="general" <?php echo $cause['category'] === 'general' ? 'selected' : ''; ?>>General Donation (general)</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="subcategory">Subcategory (e.g. deity_worship, food)</label>
          <input type="text" id="subcategory" name="subcategory" class="form-control" value="<?php echo htmlspecialchars($cause['subcategory'] ?? ''); ?>" placeholder="e.g. deity_worship">
        </div>
        
        <div class="form-group">
          <label for="sort_order">Display Sort Order</label>
          <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo intval($cause['sort_order']); ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="description">Short Description *</label>
        <textarea id="description" name="description" class="form-control" rows="3" placeholder="A short card overview for listings..." required><?php echo htmlspecialchars($cause['description']); ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="image_file">Upload Local Banner Image (Recommended: 1200x600 px)</label>
          <input type="file" id="image_file" name="image_file" class="form-control">
        </div>
        
        <div class="form-group">
          <label>Current Image Path</label>
          <div style="display:flex; align-items:center; gap:var(--space-sm); height: 42px;">
            <?php if (!empty($cause['image_url'])): ?>
              <div style="width:50px; height:35px; border-radius:4px; border:1px solid var(--border); background-image:url(<?php echo BASE_URL . $cause['image_url']; ?>); background-size:cover; background-position:center;"></div>
              <span style="font-family:monospace; font-size:11px; color:var(--text-light); text-overflow:ellipsis; overflow:hidden; white-space:nowrap; max-width:200px;"><?php echo htmlspecialchars($cause['image_url']); ?></span>
            <?php else: ?>
              <span style="color:var(--text-light); font-size:var(--font-size-sm); font-style:italic;">No image set.</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-checkbox">
            <input type="checkbox" name="allow_one_time" <?php echo $cause['allow_one_time'] ? 'checked' : ''; ?>>
            Allow One-time Payments
          </label>
        </div>
        <div class="form-group">
          <label class="form-checkbox">
            <input type="checkbox" name="allow_monthly" <?php echo $cause['allow_monthly'] ? 'checked' : ''; ?>>
            Allow Recurring Subscriptions
          </label>
        </div>
        <div class="form-group">
          <label for="default_mode">Default Mode</label>
          <select id="default_mode" name="default_mode" class="form-control">
            <option value="one_time" <?php echo $cause['default_mode'] === 'one_time' ? 'selected' : ''; ?>>One-time</option>
            <option value="monthly" <?php echo $cause['default_mode'] === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
          </select>
        </div>
      </div>

      <div class="form-row" style="margin-top:var(--space-xs);">
        <div class="form-group">
          <label class="form-checkbox">
            <input type="checkbox" name="featured" <?php echo $cause['featured'] ? 'checked' : ''; ?>>
            Feature on Home Page / Donation portal
          </label>
        </div>
        <div class="form-group">
          <label class="form-checkbox">
            <input type="checkbox" name="is_active" <?php echo $cause['is_active'] ? 'checked' : ''; ?>>
            Is Active (Visible to the public)
          </label>
        </div>
      </div>

    </div>
  </div>

  <!-- Cause Rich Narrative Card -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Rich Content & Historical Narrative</h2>
    </div>
    <div class="admin-card-body" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <div class="form-group">
        <label for="history">Historical Background</label>
        <textarea id="history" name="history" class="form-control" rows="4" placeholder="Vedic history and background of the festival..."><?php echo htmlspecialchars($cause['history'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label for="significance">Spiritual Significance</label>
        <textarea id="significance" name="significance" class="form-control" rows="4" placeholder="Scriptural instructions and significance..."><?php echo htmlspecialchars($cause['significance'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label for="benefits">Blessings & Benefits</label>
        <textarea id="benefits" name="benefits" class="form-control" rows="4" placeholder="Devotional blessings of participating/donating..."><?php echo htmlspecialchars($cause['benefits'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label for="content_body">Full HTML Content Body (Optional custom override)</label>
        <div style="font-size:11px; color:var(--text-light); margin-bottom:6px;">If provided, this HTML block overrides the history/significance/benefits blocks. Use <strong>{{BASE_URL}}</strong> for paths.</div>
        <textarea id="content_body" name="content_body" class="form-control" rows="8" placeholder="Enter custom HTML details..."><?php echo htmlspecialchars($cause['content_body'] ?? ''); ?></textarea>
      </div>
    </div>
  </div>

  <!-- Master Seva Catalog Picker -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Master Seva Catalog</h2>
      <span style="font-size: 11px; color: var(--text-light);">Select sevas from the Master Catalog to link to this festival.</span>
    </div>
    <div class="admin-card-body">
      <input type="hidden" name="master_sevas_submitted" value="1">
      
      <!-- Add Seva Button -->
      <div class="master-catalog-toolbar">
        <button type="button" id="btnShowSevaPicker" class="btn-add-seva" style="padding:10px 20px; width:auto; display:inline-flex; align-items:center; gap:8px; border:2px dashed var(--primary); background:white; color:var(--primary); border-radius:var(--radius-md); font-weight:600; cursor:pointer; font-size:14px; transition:all 0.2s;" onclick="showMasterSevaPicker()">
          <i class="fas fa-plus"></i> Add Seva from Catalog
        </button>
      </div>
      
      <!-- Picker Panel (hidden by default) -->
      <div id="masterSevaPickerPanel" class="master-seva-picker-panel" style="display:none;">
        <div class="picker-header">
          <strong>Select a Seva Category</strong>
          <button type="button" class="picker-close-btn" onclick="hideMasterSevaPicker()" title="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="picker-category-select">
          <select id="masterSevaCategorySelect" class="form-control" onchange="onCategorySelected(this.value)">
            <option value="">— Choose a Category —</option>
            <?php foreach ($masterCategories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="masterSevaCategoryResults" class="picker-results">
          <div class="picker-placeholder">Select a category above to browse available sevas.</div>
        </div>
      </div>
      
      <!-- Linked Sevas List -->
      <div id="linkedMasterSevasContainer" class="linked-sevas-container">
        <?php if (!empty($linkedMasterSevasDetailed)): ?>
          <?php foreach ($linkedMasterSevasDetailed as $link): 
            $msId = $link['master_seva_id'];
            $prefix = 'master_sevas[' . $msId . ']';
            $defaultAmount = (float)$link['default_amount'];
            $overrideAmount = $link['override_amount'] ? (float)$link['override_amount'] : null;
          ?>
          <div class="linked-seva-row" data-master-id="<?php echo $msId; ?>">
            <div class="linked-seva-header">
              <div class="linked-seva-info">
                <i class="fas <?php echo htmlspecialchars($link['cat_icon'] ?? 'fa-hand-holding-heart'); ?>" style="color:var(--primary); width:18px;"></i>
                <strong><?php echo htmlspecialchars($link['seva_name']); ?></strong>
                <span class="linked-seva-category"><?php echo htmlspecialchars($link['cat_name']); ?></span>
                <span class="linked-seva-amount-badge">₹<?php echo number_format($defaultAmount); ?></span>
              </div>
              <button type="button" class="linked-seva-remove" onclick="removeLinkedSeva(<?php echo $msId; ?>)" title="Remove this seva">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
            <div class="linked-seva-overrides">
              <input type="hidden" name="<?php echo $prefix; ?>[linked]" value="1">
              <div class="form-row override-row">
                <div class="form-group">
                  <label>Override Amount (₹)</label>
                  <input type="number" name="<?php echo $prefix; ?>[override_amount]" class="form-control" 
                         value="<?php echo $overrideAmount ?: ''; ?>" 
                         placeholder="Default: ₹<?php echo number_format($defaultAmount); ?>" step="1">
                </div>
                <div class="form-group">
                  <label>Sort Order</label>
                  <input type="number" name="<?php echo $prefix; ?>[sort_order]" class="form-control" 
                         value="<?php echo (int)$link['sort_order']; ?>">
                </div>
                <div class="form-group" style="max-width:100px;">
                  <label>Max Qty</label>
                  <input type="number" name="<?php echo $prefix; ?>[override_max_quantity]" class="form-control" 
                         value="<?php echo $link['override_max_quantity'] ? (int)$link['override_max_quantity'] : ''; ?>" 
                         placeholder="<?php echo (int)$link['max_quantity'] ? 'Default: ' . (int)$link['max_quantity'] : 'Unlimited'; ?>">
                </div>
                <div class="form-group checkbox-group">
                  <label class="form-checkbox">
                    <input type="checkbox" name="<?php echo $prefix; ?>[is_featured]" value="1" <?php echo $link['is_featured'] ? 'checked' : ''; ?>>
                    Featured
                  </label>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="linked-sevas-empty" id="linkedSevasEmpty">
            <i class="fas fa-inbox"></i>
            <span>No sevas linked yet. Click "Add Seva from Catalog" to get started.</span>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- Legacy Sevas Info -->
      <?php if (!empty($oldSevas)): ?>
      <div class="legacy-sevas-info">
        <h4><i class="fas fa-history"></i> Legacy Sevas (from old system — <?php echo count($oldSevas); ?> items)</h4>
        <p style="font-size:12px; color:var(--text-light); margin:0 0 var(--space-sm) 0;">
          These sevas exist in the old <code>donation_cause_sevas</code> table for historical reference.
          New edits should use the Master Catalog above. Old sevas are preserved for transaction integrity.
        </p>
        <div class="legacy-sevas-list">
          <?php foreach ($oldSevas as $os): ?>
            <span class="legacy-seva-tag"><?php echo htmlspecialchars($os['name']); ?> (₹<?php echo number_format((float)$os['amount']); ?>)</span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Template for linked seva row (used by JS when adding new sevas) -->
  <template id="linkedSevaRowTemplate">
    <div class="linked-seva-row" data-master-id="">
      <div class="linked-seva-header">
        <div class="linked-seva-info">
          <i class="fas fa-hand-holding-heart" style="color:var(--primary); width:18px;"></i>
          <strong class="linked-seva-name"></strong>
          <span class="linked-seva-category"></span>
          <span class="linked-seva-amount-badge"></span>
        </div>
        <button type="button" class="linked-seva-remove" onclick="removeLinkedSeva(this.closest('.linked-seva-row').dataset.masterId)" title="Remove this seva">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <div class="linked-seva-overrides">
        <input type="hidden" name="" class="input-linked" value="1">
        <div class="form-row override-row">
          <div class="form-group">
            <label>Override Amount (₹)</label>
            <input type="number" name="" class="form-control input-override-amount" placeholder="" step="1">
          </div>
          <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="" class="form-control input-sort-order" value="10">
          </div>
          <div class="form-group" style="max-width:100px;">
            <label>Max Qty</label>
            <input type="number" name="" class="form-control input-max-qty" placeholder="">
          </div>
          <div class="form-group checkbox-group">
            <label class="form-checkbox">
              <input type="checkbox" name="" class="input-featured" value="1">
              Featured
            </label>
          </div>
        </div>
      </div>
    </div>
  </template>

  <!-- SEO Metadata -->
  <div class="admin-card">
    <div class="admin-card-header">
      <h2>Search Engine Optimization (SEO)</h2>
    </div>
    <div class="admin-card-body" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <div class="form-row">
        <div class="form-group">
          <label for="meta_title">Meta Title</label>
          <input type="text" id="meta_title" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($cause['meta_title'] ?? ''); ?>" placeholder="Meta Title tag">
        </div>
        <div class="form-group">
          <label for="meta_description">Meta Description</label>
          <input type="text" id="meta_description" name="meta_description" class="form-control" value="<?php echo htmlspecialchars($cause['meta_description'] ?? ''); ?>" placeholder="Meta description tag (search snippet)">
        </div>
      </div>
    </div>
  </div>

  <!-- Form Actions -->
  <div style="display:flex; gap:12px; padding: var(--space-md) 0;">
    <button type="submit" class="btn btn-primary" style="background-color: var(--primary); color:white; border:none; padding:14px 28px; border-radius:var(--radius-md); font-weight:600; cursor:pointer;">
      <i class="fas fa-save" style="margin-right:6px;"></i> <?php echo $isEdit ? 'Save Changes' : 'Create Festival / Cause'; ?>
    </button>
    <a href="admin/festivals" class="btn btn-outline-dark" style="text-decoration:none; padding:14px 28px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600;">Cancel</a>
  </div>

</form>

<!-- Master Seva Catalog JS -->
<script>
// Track linked master seva IDs
const linkedMasterSevaIds = new Set(<?php echo json_encode(array_map('intval', array_keys($linkedMasterSevas))); ?>);

// Show the picker panel
function showMasterSevaPicker() {
  const panel = document.getElementById('masterSevaPickerPanel');
  panel.style.display = 'block';
  panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Hide the picker panel
function hideMasterSevaPicker() {
  document.getElementById('masterSevaPickerPanel').style.display = 'none';
  document.getElementById('masterSevaCategorySelect').value = '';
  document.getElementById('masterSevaCategoryResults').innerHTML = '<div class="picker-placeholder">Select a category above to browse available sevas.</div>';
}

// When a category is selected, load its sevas via AJAX
function onCategorySelected(categoryId) {
  const resultsEl = document.getElementById('masterSevaCategoryResults');
  
  if (!categoryId) {
    resultsEl.innerHTML = '<div class="picker-placeholder">Select a category above to browse available sevas.</div>';
    return;
  }
  
  resultsEl.innerHTML = '<div class="picker-loading"><i class="fas fa-spinner fa-spin"></i> Loading sevas...</div>';
  
  fetch('admin/ajax/master-sevas-by-category.php?category_id=' + encodeURIComponent(categoryId))
    .then(function(resp) { return resp.json(); })
    .then(function(data) {
      if (!data.success || !data.items || data.items.length === 0) {
        resultsEl.innerHTML = '<div class="picker-empty"><i class="fas fa-inbox"></i> No sevas found in this category.<br><small>Add sevas via the <a href="admin/seva-catalogue-edit?cat_id=' + categoryId + '">Seva Catalogue</a> first.</small></div>';
        return;
      }
      
      var html = '<div class="picker-items">';
      data.items.forEach(function(s) {
        var isLinked = linkedMasterSevaIds.has(s.id);
        html += '<div class="picker-item">' +
          '<div class="picker-item-info">' +
            '<strong>' + escHtml(s.name) + '</strong>' +
            (s.description ? '<div class="picker-item-desc">' + escHtml(s.description) + '</div>' : '') +
          '</div>' +
          '<div class="picker-item-actions">' +
            '<span class="picker-item-amount">₹' + numberFormat(s.default_amount) + '</span>';
        if (isLinked) {
          html += '<span class="picker-item-added"><i class="fas fa-check"></i> Added</span>';
        } else {
          html += '<button type="button" class="btn-sm-action btn-edit" onclick="addSevaToFestival(' + s.id + ', ' + JSON.stringify(s).replace(/"/g, "&quot;") + ')"><i class="fas fa-plus"></i> Add</button>';
        }
        html += '</div></div>';
      });
      html += '</div>';
      resultsEl.innerHTML = html;
    })
    .catch(function(err) {
      resultsEl.innerHTML = '<div class="picker-error"><i class="fas fa-exclamation-triangle"></i> Failed to load sevas. Please try again.</div>';
      console.error('Master Seva load error:', err);
    });
}

// Add a selected seva to the festival's linked list
function addSevaToFestival(masterSevaId, sevaData) {
  if (linkedMasterSevaIds.has(masterSevaId)) return;
  
  linkedMasterSevaIds.add(masterSevaId);
  
  // Clone template
  var template = document.getElementById('linkedSevaRowTemplate');
  var clone = template.content.cloneNode(true);
  var row = clone.querySelector('.linked-seva-row');
  
  row.dataset.masterId = masterSevaId;
  row.querySelector('.linked-seva-name').textContent = sevaData.name;
  row.querySelector('.linked-seva-category').textContent = document.getElementById('masterSevaCategorySelect').options[document.getElementById('masterSevaCategorySelect').selectedIndex].text;
  row.querySelector('.linked-seva-amount-badge').textContent = '₹' + numberFormat(sevaData.default_amount);
  
  // Set icon if available
  if (sevaData.icon) {
    row.querySelector('.linked-seva-info i').className = 'fas ' + sevaData.icon;
  }
  
  // Set input names
  var prefix = 'master_sevas[' + masterSevaId + ']';
  row.querySelector('.input-linked').name = prefix + '[linked]';
  row.querySelector('.input-override-amount').name = prefix + '[override_amount]';
  row.querySelector('.input-override-amount').placeholder = 'Default: ₹' + numberFormat(sevaData.default_amount);
  row.querySelector('.input-sort-order').name = prefix + '[sort_order]';
  var maxQtyPlaceholder = sevaData.max_quantity ? 'Default: ' + sevaData.max_quantity : 'Unlimited';
  row.querySelector('.input-max-qty').name = prefix + '[override_max_quantity]';
  row.querySelector('.input-max-qty').placeholder = maxQtyPlaceholder;
  row.querySelector('.input-featured').name = prefix + '[is_featured]';

  
  // Add to DOM
  var container = document.getElementById('linkedMasterSevasContainer');
  var emptyEl = document.getElementById('linkedSevasEmpty');
  if (emptyEl) emptyEl.style.display = 'none';
  container.appendChild(row);
  
  // Refresh picker results to show "Added" state
  var select = document.getElementById('masterSevaCategorySelect');
  if (select.value) {
    onCategorySelected(select.value);
  }
}

// Remove a linked seva from the festival
function removeLinkedSeva(masterSevaId) {
  if (!confirm('Remove this seva from the festival?')) return;
  
  linkedMasterSevaIds.delete(parseInt(masterSevaId));
  
  var row = document.querySelector('.linked-seva-row[data-master-id="' + masterSevaId + '"]');
  if (row) row.remove();
  
  // Show empty state if no more linked sevas
  var container = document.getElementById('linkedMasterSevasContainer');
  if (container.querySelectorAll('.linked-seva-row').length === 0) {
    var emptyEl = document.getElementById('linkedSevasEmpty');
    if (emptyEl) emptyEl.style.display = 'flex';
  }
  
  // Refresh picker results to re-enable Add button
  var select = document.getElementById('masterSevaCategorySelect');
  if (select.value && document.getElementById('masterSevaPickerPanel').style.display !== 'none') {
    onCategorySelected(select.value);
  }
}

// Helper: escape HTML
function escHtml(str) {
  var div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// Helper: format number with commas (Indian style)
function numberFormat(num) {
  num = parseFloat(num);
  if (isNaN(num)) return num;
  return num.toLocaleString('en-IN');
}

// Before form submit, ensure linked=1 inputs are present for all rows
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function() {
      // Nothing extra needed — all linked rows already have correct inputs
    });
  }
});

// Slug auto-generate from title
document.addEventListener('DOMContentLoaded', function() {
  const titleInput = document.getElementById('title');
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
