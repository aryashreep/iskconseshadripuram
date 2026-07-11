<?php
/**
 * Sudamaseva Module — Donors List (Admin)
 *
 * Paginated list with search, status filter, and quick actions.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

use Isjm\Modules\Sudamaseva\SudamasevaService;

$service = new SudamasevaService();
$error = '';
$message = '';

// Handle delete action (must be executed before any HTML output for redirect to work)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('sudamaseva.delete')) {
        $error = 'You do not have permission to delete donors.';
    } else {
        $deleteId = intval($_GET['id']);
        try {
            if ($service->hasPaidPayments($deleteId)) {
                $error = 'Cannot delete donor because they have successful donation records. Donors with successful payments cannot be deleted to preserve financial audit integrity.';
            } else {
                if ($service->deleteDonor($deleteId)) {
                    $message = 'Donor deleted successfully.';
                    header('Location: ' . BASE_URL . 'admin/sudamaseva-donors?message=' . urlencode($message));
                    exit;
                } else {
                    $error = 'Failed to delete donor. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'Error deleting donor: ' . $e->getMessage();
        }
    }
}

$message = trim($_GET['message'] ?? '');

$pageTitle = 'Sudamaseva Donors';
$activePage = 'sudamaseva-donors';
include 'partials/header.php';

// Read filters
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$hideOrphans = isset($_GET['hide_orphans']) ? $_GET['hide_orphans'] !== '0' : true;

try {
    $result = $service->getDonors($status ?: null, $search ?: null, $page, $perPage, $hideOrphans);
    $donors = $result['donors'];
    $total = $result['total'];
    $pages = $result['pages'];
    $stats = $service->getDonorStats();
} catch (Exception $e) {
    $error = 'Failed to load donors: ' . $e->getMessage();
    $donors = [];
    $total = 0;
    $pages = 1;
    $stats = [];
}

$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Sudamaseva Donors</h1>
    <p>Manage all registered donors from the subscription donation system.</p>
  </div>
  <div class="admin-page-actions">
    <?php if (hasPermission('sudamaseva.edit')): ?>
      <a href="admin/sudamaseva-donor-add" class="btn btn-primary btn-sm" style="text-decoration:none;"><i class="fas fa-user-plus"></i> Add New Donor</a>
    <?php endif; ?>
    <span style="font-size:12px; color:var(--text-light); margin-left:12px;">
      <i class="fas fa-database"></i> <?php echo $total; ?> total donors
    </span>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<?php if ($message): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<!-- Summary Cards -->
<div style="display:flex; gap: var(--space-md); margin-bottom: var(--space-lg); flex-wrap:wrap;">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-users" style="font-size:24px; color:var(--primary);"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Total</div>
      <strong style="font-size:18px;"><?php echo $stats['total'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-check-circle" style="font-size:24px; color:green;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Active</div>
      <strong style="font-size:18px; color:green;"><?php echo $stats['active'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-history" style="font-size:24px; color:var(--maroon);"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Migrated</div>
      <strong style="font-size:18px; color:var(--maroon);"><?php echo $stats['migrated'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-user-plus" style="font-size:24px; color:#0b5ed7;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">New Today</div>
      <strong style="font-size:18px; color:#0b5ed7;"><?php echo $stats['new_signups_today'] ?? 0; ?></strong>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Search Donors</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/sudamaseva-donors" method="GET" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-md); align-items:end;">
        <div class="form-group" style="margin-bottom:0;">
          <label for="search">Search</label>
          <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Phone, or Email...">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label for="status">Status</label>
          <select id="status" name="status" class="form-control">
            <option value="">-- All Statuses --</option>
            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            <option value="paused" <?php echo $status === 'paused' ? 'selected' : ''; ?>>Paused</option>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label>&nbsp;</label>
          <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px; font-weight:500; color:var(--text);">
            <input type="checkbox" name="hide_orphans" value="1" <?php echo $hideOrphans ? 'checked' : ''; ?> style="accent-color:var(--primary);">
            Hide orphan donors
          </label>
        </div>
      </div>
      <div style="display:flex; justify-content: flex-end; gap:8px;">
        <a href="admin/sudamaseva-donors" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Search</button>
      </div>
    </form>
  </div>
</div>

<!-- Donors Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Donor Records (Showing <?php echo count($donors); ?> of <?php echo $total; ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 800px;">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email / PAN</th>
            <th>Source</th>
            <th>Status</th>
            <th>Created</th>
            <th style="text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($donors)): ?>
            <tr>
              <td colspan="8" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No donors found matching the filters.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($donors as $d): ?>
              <tr>
                <td style="font-family:monospace; font-size:12px;">#<?php echo $d['id']; ?></td>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($d['donor_name']); ?></strong>
                  <?php if (!empty($d['email'])): ?>
                    <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($d['email']); ?></div>
                  <?php endif; ?>
                </td>
                <td style="font-family:monospace; font-size:12px;"><?php echo htmlspecialchars($d['phone'] ?: '—'); ?></td>
                <td>
                  <div style="font-size:11px;"><?php echo htmlspecialchars($d['email'] ?: '—'); ?></div>
                  <div style="font-size:10px; color:var(--text-light); text-transform:uppercase;">PAN: <?php echo htmlspecialchars($d['pan'] ?: '—'); ?></div>
                </td>
                <td>
                  <span class="badge badge-info"><?php echo $service->getDonorSourceLabel($d['source'] ?? 'sudamaseva'); ?></span>
                </td>
                <td>
                  <?php
                    $statusClass = 'badge-secondary';
                    if (($d['status'] ?? '') === 'active') $statusClass = 'badge-success';
                    elseif (($d['status'] ?? '') === 'inactive') $statusClass = 'badge-danger';
                    elseif (($d['status'] ?? '') === 'paused') $statusClass = 'badge-warning';
                  ?>
                  <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($d['status'] ?? 'unknown')); ?></span>
                </td>
                <td style="font-size:11px; color:var(--text-light);"><?php echo $service->formatDate($d['created_at'] ?? null, 'd M Y'); ?></td>
                <td style="text-align:center; white-space:nowrap; display:flex; gap:4px; justify-content:center; align-items:center;">
                  <a href="admin/sudamaseva-donor-detail?id=<?php echo $d['id']; ?>" class="btn-sm-action btn-edit" title="View Donor Profile" style="padding: 6px 8px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; text-decoration:none;"><i class="fas fa-user"></i></a>
                  <?php if (!empty($d['active_sub_id'])): ?>
                    <a href="admin/sudamaseva-record-payment?subscription_id=<?php echo $d['active_sub_id']; ?>" class="btn-sm-action" title="Record Offline Payment" style="padding: 6px 8px; border-radius: 4px; background:#2e7d32; color:white; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; text-decoration:none;"><i class="fas fa-hand-holding-usd"></i></a>
                  <?php else: 
                    $nextCycle = max(1, (int)($d['max_cycle'] ?? 0) + 1);
                    $enrollUrl = "admin/sudamaseva-donor-add?phone=" . urlencode($d['phone'] ?? '') 
                      . "&donor_name=" . urlencode($d['donor_name'] ?? '')
                      . "&email=" . urlencode($d['email'] ?? '')
                      . "&pan=" . urlencode($d['pan'] ?? '')
                      . "&area=" . urlencode($d['area'] ?? '')
                      . "&city=" . urlencode($d['city'] ?? '')
                      . "&state=" . urlencode($d['state'] ?? '')
                      . "&cycle=" . $nextCycle;
                  ?>
                    <?php if (hasPermission('sudamaseva.edit')): ?>
                      <a href="<?php echo $enrollUrl; ?>" class="btn-sm-action" title="Enroll / Renew Subscription (New Cycle)" style="padding: 6px 8px; border-radius: 4px; background:#0b5ed7; color:white; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; text-decoration:none;"><i class="fas fa-user-plus"></i></a>
                    <?php endif; ?>
                  <?php endif; ?>
                  <?php if (hasPermission('sudamaseva.delete')): ?>
                    <a href="admin/sudamaseva-donors?action=delete&id=<?php echo $d['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" 
                       class="btn-sm-action btn-delete" 
                       title="Delete Donor" 
                       onclick="return confirm('Are you sure you want to delete this donor? This will remove the donor and associated incomplete subscriptions/payments.');" 
                       style="padding: 6px 8px; border-radius: 4px; background:#dc3545; color:white; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; text-decoration:none;">
                      <i class="fas fa-trash"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pagination -->
<link rel="stylesheet" href="<?= asset('assets/css/pages/admin/donations.css') ?>">
<?php if ($pages > 1): ?>
  <div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:var(--space-xl); margin-bottom:var(--space-2xl);">
    <?php if ($page > 1): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-donors?page=<?php echo ($page - 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link"><i class="fas fa-chevron-left" style="font-size:10px; margin-right:4px;"></i> Prev</a>
    <?php endif; ?>

    <?php
      $maxVisible = 10;
      $startPage = max(1, $page - 5);
      $endPage = min($pages, $startPage + $maxVisible - 1);
      if ($endPage - $startPage + 1 < $maxVisible) {
          $startPage = max(1, $endPage - $maxVisible + 1);
      }
      
      if ($startPage > 1):
    ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-donors?page=1<?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">1</a>
      <?php if ($startPage > 2): ?>
        <span style="color:var(--text-light); padding: 0 4px;">...</span>
      <?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-donors?page=<?php echo $i; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link<?php echo $i === $page ? ' active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($endPage < $pages): ?>
      <?php if ($endPage < $pages - 1): ?>
        <span style="color:var(--text-light); padding: 0 4px;">...</span>
      <?php endif; ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-donors?page=<?php echo $pages; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link"><?php echo $pages; ?></a>
    <?php endif; ?>

    <?php if ($page < $pages): ?>
      <a href="<?php echo BASE_URL; ?>admin/sudamaseva-donors?page=<?php echo ($page + 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">Next <i class="fas fa-chevron-right" style="font-size:10px; margin-left:4px;"></i></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
