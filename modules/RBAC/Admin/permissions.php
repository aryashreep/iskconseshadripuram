<?php
/**
 * Permissions Reference — Read-Only Catalog
 *
 * Displays all permissions grouped by module, showing slug, description, and
 * which roles currently have each permission assigned.
 * Super Admin only.
 */

require_once __DIR__ . '/../../Kernel/Admin/auth-check.php';
requireRole(['super_admin']);

use Isjm\Modules\RBAC\PermissionRegistry;

$pageTitle = 'Permissions Reference';
$activePage = 'permissions';
include __DIR__ . '/../../Kernel/Admin/partials/header.php';

$db = getDB();
$error = '';

try {
    // Fetch all roles
    $roles = $db->query("SELECT id, slug, name FROM rbac_roles ORDER BY sort_order ASC, name ASC")->fetchAll();

    // Fetch all role-permission assignments as [role_id => [permission_id, ...]]
    $rpStmt = $db->query("SELECT role_id, permission_id FROM rbac_role_permissions");
    $rolePerms = [];
    foreach ($rpStmt->fetchAll() as $rp) {
        $rolePerms[(int)$rp['role_id']][] = (int)$rp['permission_id'];
    }

    // Fetch all permissions from DB
    $allPerms = $db->query("SELECT * FROM rbac_permissions ORDER BY sort_order ASC")->fetchAll();

    // Group by module
    $grouped = [];
    foreach ($allPerms as $p) {
        $grouped[$p['module']][] = $p;
    }

    // Module icons from registry
    $modulesMeta = PermissionRegistry::getModules();

    // Count totals
    $totalPerms = count($allPerms);
    $totalModules = count($grouped);

    // Calculate unique role-permission connections
    $totalAssignments = 0;
    foreach ($rolePerms as $rp) {
        $totalAssignments += count($rp);
    }

} catch (\PDOException $e) {
    $error = 'Failed to load permissions. Ensure RBAC migrations have been run.';
    $grouped = [];
    $roles = [];
    $rolePerms = [];
    $modulesMeta = [];
    $totalPerms = 0;
    $totalModules = 0;
    $totalAssignments = 0;
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-key" style="color:var(--primary); margin-right:8px;"></i> Permissions Reference</h1>
    <p>Complete catalog of all <?php echo $totalPerms; ?> permissions across <?php echo $totalModules; ?> modules — read-only reference.</p>
  </div>
  <div class="admin-page-actions">
    <a href="admin/roles" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 20px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; color:var(--text); font-size:13px; display:inline-flex; align-items:center; gap:6px;">
      <i class="fas fa-shield-alt"></i> Back to Roles
    </a>
  </div>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<!-- Summary Stats -->
<div class="admin-stats-grid" style="margin-bottom: var(--space-xl);">
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Permissions</h3>
      <div class="admin-stat-value" style="font-size:28px;"><?php echo $totalPerms; ?></div>
    </div>
    <div class="admin-stat-icon"><i class="fas fa-key"></i></div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Modules</h3>
      <div class="admin-stat-value" style="font-size:28px;"><?php echo $totalModules; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:rgba(200,107,31,0.15); color:var(--primary-dark);"><i class="fas fa-cubes"></i></div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Roles</h3>
      <div class="admin-stat-value" style="font-size:28px;"><?php echo count($roles); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:#d4edda; color:#2e7d32;"><i class="fas fa-user-tag"></i></div>
  </div>
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Role-Perm Assignments</h3>
      <div class="admin-stat-value" style="font-size:28px; color:var(--maroon);"><?php echo $totalAssignments; ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color:#f3e5f5; color:#6a1b9a;"><i class="fas fa-link"></i></div>
  </div>
</div>

<!-- Legend -->
<div class="admin-card" style="margin-bottom: var(--space-xl); padding: var(--space-md) var(--space-lg); font-size: 12px; color: var(--text-light); display:flex; align-items:center; gap: var(--space-lg); flex-wrap:wrap;">
  <span><strong>Legend:</strong></span>
  <span><span class="badge" style="background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; font-size:10px;"><i class="fas fa-check"></i> Assigned</span> — Permission is assigned to this role</span>
  <span><span class="badge" style="background:#fff3e0; color:#e65100; border:1px solid #ffe0b2; font-size:10px;"><i class="fas fa-bolt"></i> Implicit</span> — Super Admin has implicit access (no assignment needed)</span>
  <span><span style="color:#ccc;"><i class="far fa-circle"></i></span> — Permission is not assigned to this role</span>
</div>

<!-- Permissions by Module -->
<?php if (empty($grouped)): ?>
  <div style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">
    <i class="fas fa-database" style="font-size:40px; margin-bottom:var(--space-lg); display:block;"></i>
    <h3 style="color:var(--text); margin-bottom:var(--space-sm);">No Permissions Found</h3>
    <p>Run the RBAC seed migration to populate the permissions table.</p>
  </div>
<?php else: ?>
  <?php foreach ($modulesMeta as $module => $meta):
    $modulePerms = $grouped[$module] ?? [];
    if (empty($modulePerms)) continue;
    $icon = $meta['icon'] ?? 'fa-cube';
    $desc = $meta['description'] ?? '';
  ?>
  <div class="admin-card" style="margin-bottom: var(--space-lg);">
    <div class="admin-card-header" style="display:flex; align-items:center; gap:10px;">
      <i class="fas <?php echo $icon; ?>" style="color:var(--primary); font-size:18px;"></i>
      <div>
        <h2 style="margin:0; font-size:15px;"><?php echo htmlspecialchars($meta['label']); ?>
          <span style="font-weight:normal; font-size:12px; color:var(--text-light); margin-left:8px;">
            <?php echo htmlspecialchars($desc); ?>
          </span>
        </h2>
      </div>
      <span class="badge" style="margin-left:auto; background:var(--cream); color:var(--text); border:1px solid var(--border); font-size:11px;">
        <?php echo count($modulePerms); ?> permission<?php echo count($modulePerms) !== 1 ? 's' : ''; ?>
      </span>
    </div>
    <div class="admin-card-body" style="padding:0; overflow-x:auto;">
      <table class="admin-table" style="min-width: 500px;">
        <thead>
          <tr>
            <th style="min-width:180px;">Permission</th>
            <th style="min-width:120px;">Slug</th>
            <?php foreach ($roles as $r):
              $isSuper = $r['slug'] === 'super_admin';
            ?>
              <th style="text-align:center; font-size:10px; white-space:nowrap; min-width:60px;">
                <?php echo htmlspecialchars(str_replace('_', ' ', $r['name'])); ?>
                <?php if ($isSuper): ?>
                  <br><span style="font-size:9px; color:var(--primary); font-weight:600;">(bypass)</span>
                <?php endif; ?>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($modulePerms as $perm):
            $slug = $perm['slug'];
            $label = $perm['label'];
            $permId = (int) $perm['id'];
          ?>
            <tr>
              <td>
                <strong style="font-size:13px; color:var(--dark);"><?php echo htmlspecialchars($label); ?></strong>
                <?php if (!empty($perm['description'])): ?>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($perm['description']); ?></div>
                <?php endif; ?>
              </td>
              <td style="font-family:monospace; font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($slug); ?></td>
              <?php foreach ($roles as $r):
                $isSuper = $r['slug'] === 'super_admin';
                $isAssigned = isset($rolePerms[(int)$r['id']]) && in_array($permId, $rolePerms[(int)$r['id']]);
              ?>
                <td style="text-align:center;">
                  <?php if ($isSuper): ?>
                    <span class="badge" style="background:#fff3e0; color:#e65100; border:1px solid #ffe0b2; font-size:10px; padding:2px 6px;">
                      <i class="fas fa-bolt" style="font-size:9px;"></i>
                    </span>
                  <?php elseif ($isAssigned): ?>
                    <span class="badge" style="background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9; font-size:10px; padding:2px 6px;">
                      <i class="fas fa-check" style="font-size:9px;"></i>
                    </span>
                  <?php else: ?>
                    <span style="color:#ddd; font-size:14px;"><i class="far fa-circle"></i></span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Super Admin Info Card -->
<div class="admin-card" style="margin-top: var(--space-lg); background:var(--cream); border:1px solid var(--border);">
  <div class="admin-card-body" style="padding: var(--space-lg);">
    <div style="display:flex; align-items:flex-start; gap:var(--space-md);">
      <div style="font-size:32px; color:var(--primary);"><i class="fas fa-info-circle"></i></div>
      <div>
        <h3 style="margin:0 0 var(--space-xs) 0; font-size:15px;">Understanding Permissions</h3>
        <ul style="margin:0; padding-left:16px; font-size:13px; line-height:1.6; color:var(--text);">
          <li><strong>Super Administrator</strong> has an <strong>implicit bypass</strong> — all permissions are automatically granted regardless of explicit assignments (marked with <i class="fas fa-bolt" style="color:#e65100;font-size:10px;"></i> above).</li>
          <li>When a user has <strong>multiple roles</strong>, they receive the <strong>union</strong> (OR) of all permissions across all assigned roles.</li>
          <li>Permissions use the format <code>module.action</code> (e.g., <code>donations.view</code>, <code>festivals.edit</code>).</li>
          <li>Each module supports up to 5 actions: <code>view</code>, <code>create</code>, <code>edit</code>, <code>delete</code>, <code>export</code>.</li>
          <li>Reports and audit logs modules have 2 actions each (<code>view</code>, <code>export</code>) since delete/create/edit don't apply.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../Kernel/Admin/partials/footer.php'; ?>
