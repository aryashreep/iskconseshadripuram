<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Legacy role-based redirect check
if (isset($_SESSION['admin_role'])) {
  $roles = array_map('trim', explode(',', $_SESSION['admin_role']));
  if (!in_array('super_admin', $roles)) {
    if (in_array('travel_agent', $roles) && count($roles) === 1) {
      require_once __DIR__ . '/../config.php';
      header('Location: ' . BASE_URL . 'admin/panihati-yatra');
      exit;
    }
    if (in_array('sudamaseva', $roles) && count($roles) === 1) {
      require_once __DIR__ . '/../config.php';
      header('Location: ' . BASE_URL . 'admin/sudamaseva-dashboard');
      exit;
    }
    if (in_array('pujari', $roles) && count($roles) === 1) {
      require_once __DIR__ . '/../config.php';
      header('Location: ' . BASE_URL . 'admin/pujari-sevalist');
      exit;
    }
  }
}

// RBAC permission-based redirect (for users assigned roles via RBAC, not legacy)
if (isset($_SESSION['admin_permissions']) && is_array($_SESSION['admin_permissions']) && !empty($_SESSION['admin_permissions'])) {
    $roleString = $_SESSION['admin_role'] ?? '';
    $roleList = array_map('trim', explode(',', $roleString));
    if (!in_array('super_admin', $roleList)) {
        // Extract unique non-dashboard modules from the user's permissions
        $modules = [];
        foreach ($_SESSION['admin_permissions'] as $p) {
            $parts = explode('.', $p);
            if (count($parts) >= 2 && $parts[0] !== 'dashboard') {
                $modules[$parts[0]] = true;
            }
        }
        $moduleNames = array_keys($modules);

        // If user only has access to a single module, redirect to that module's landing page
        if (count($moduleNames) === 1) {
            $module = $moduleNames[0];
            $redirects = [
                'sudamaseva' => 'admin/sudamaseva-dashboard',
                'panihati' => 'admin/panihati-yatra',
                'pujari_sevalist' => 'admin/pujari-sevalist',
            ];
            if (isset($redirects[$module])) {
                require_once __DIR__ . '/../config.php';
                header('Location: ' . BASE_URL . $redirects[$module]);
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../config.php';

// Initialize CSRF token for all users (needed before links are rendered)
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

$db = getDB();
$dbError = null;

// Consolidated dashboard service (aggregates revenue across all modules)
use Isjm\Services\AdminDashboardService;
$adminDashService = new AdminDashboardService();

// ==========================================
// Toggle Booking Status Handler (Pujari only)
// ==========================================
if (isset($_GET['toggle_status_id'])) {
  // Role check: only pujari and super_admin can toggle booking status
  $currentRole = $_SESSION['admin_role'] ?? '';
  if (!in_array($currentRole, ['super_admin', 'pujari'])) {
    $_SESSION['flash_error'] = 'You do not have permission to perform this action.';
    header('Location: ' . BASE_URL . 'admin/dashboard');
    exit;
  }

  $toggleId = intval($_GET['toggle_status_id']);
  if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
    $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
  } else {
    try {
      // Fetch current status
      $stmt = $db->prepare("SELECT status, id FROM booking_pujas WHERE id = ?");
      $stmt->execute([$toggleId]);
      $booking = $stmt->fetch();

      if ($booking) {
        $newStatus = ($booking['status'] === 'Completed') ? 'Pending' : 'Completed';
        $update = $db->prepare("UPDATE booking_pujas SET status = ? WHERE id = ?");
        $update->execute([$newStatus, $toggleId]);
        $_SESSION['flash_success'] = "Booking status changed to " . $newStatus . ".";
      } else {
        $_SESSION['flash_error'] = 'Booking not found.';
      }
    } catch (PDOException $e) {
      $_SESSION['flash_error'] = 'Failed to update status. Please try again.';
    }
  }
  require_once __DIR__ . '/../config.php';
  header('Location: ' . BASE_URL . 'admin/dashboard');
  exit;
}

// Date range filter
$filterFrom = trim($_GET['from'] ?? '');
$filterTo = trim($_GET['to'] ?? '');
$hasDateFilter = !empty($filterFrom) || !empty($filterTo);

include 'partials/header.php';

$roleString = $_SESSION['admin_role'] ?? 'editor';
$userRoles = array_map('trim', explode(',', $roleString));
$isSuperAdmin = in_array('super_admin', $userRoles);
$isTreasurer = in_array('treasurer', $userRoles);
$isEditor = in_array('editor', $userRoles);
$isPujari = in_array('pujari', $userRoles);

// Financial year calculation
$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
$currentFY = ($currentMonth < 4) ? $currentYear - 1 : $currentYear;

try {
  if ($isSuperAdmin || $isTreasurer) {
    // CONSOLIDATED DASHBOARD — aggregate revenue across all modules
    // Apply date filter if provided
    $filterOpts = [
        'from' => $filterFrom ?: null,
        'to' => $filterTo ?: null,
    ];
    $overview = $adminDashService->getOverview([], $filterOpts['from'], $filterOpts['to']);
    $moduleBreakdown = $adminDashService->getModuleBreakdown([], $filterOpts['from'], $filterOpts['to']);
    $trendChart = $adminDashService->getMonthlyTrendChart(12, [], $filterOpts['from'], $filterOpts['to']);
    $recentCollections = $adminDashService->getRecentCollections(10, [], $filterOpts['from'], $filterOpts['to']);
    $recurringPipeline = $adminDashService->getRecurringPipeline();
    $donationCategorySplit = $adminDashService->getDonationCategorySplit();

    // Donation category chart data
    $catLabels = [];
    $catData = [];
    $categoryLabels = [
        'festival' => 'Grand Festivals', 'ekadashi' => 'Ekadashi', 'appearance' => 'Appearance Days',
        'disappearance' => 'Disappearance Days', 'event' => 'Events & Programs', 'service' => 'Seva & Services',
        'construction' => 'Temple Construction', 'general' => 'General Donations',
    ];
    foreach ($donationCategorySplit as $row) {
        $catLabels[] = $categoryLabels[$row['category']] ?? ucfirst($row['category']);
        $catData[] = (float) ($row['total'] ?? 0);
    }

    // FY Comparison: Current FY vs Previous FY
    $prevFY = $currentFY - 1;

    $currFYFrom = "{$currentFY}-04-01";
    $currFYTo = "" . ($currentFY + 1) . "-03-31";
    $prevFYFrom = "{$prevFY}-04-01";
    $prevFYTo = "" . ($prevFY + 1) . "-03-31";

    $currFYData = $adminDashService->getOverview([], $currFYFrom, $currFYTo);
    $prevFYData = $adminDashService->getOverview([], $prevFYFrom, $prevFYTo);
    $currFYModuleBreakdown = $adminDashService->getModuleBreakdown([], $currFYFrom, $currFYTo);
    $prevFYModuleBreakdown = $adminDashService->getModuleBreakdown([], $prevFYFrom, $prevFYTo);

    // Calculate totals and % change
    $currFYTotal = $currFYData['total_collections'];
    $prevFYTotal = $prevFYData['total_collections'];
    $fyChange = null;
    $fyChangeLabel = '—';
    $fyChangeClass = 'var(--text-light)';
    $fyChangeIcon = '';
    if ($prevFYTotal > 0) {
        $fyPct = round((($currFYTotal - $prevFYTotal) / $prevFYTotal) * 100, 1);
        if ($fyPct > 0) {
            $fyChangeLabel = '+' . $fyPct . '%';
            $fyChangeClass = '#2e7d32';
            $fyChangeIcon = '<i class="fas fa-arrow-up" style="font-size:12px;"></i> ';
        } elseif ($fyPct < 0) {
            $fyChangeLabel = $fyPct . '%';
            $fyChangeClass = '#c62828';
            $fyChangeIcon = '<i class="fas fa-arrow-down" style="font-size:12px;"></i> ';
        } else {
            $fyChangeLabel = '0%';
            $fyChangeIcon = '<i class="fas fa-minus" style="font-size:12px;"></i> ';
        }
        $fyChange = $fyPct;
    }

    // Build module-by-module comparison
    $fyComparisonModules = [];
    $allModules = ['donations', 'puja', 'yagya', 'panihati', 'sudamaseva'];
    $moduleMeta = $adminDashService->getAllModuleMeta();

    foreach ($allModules as $mod) {
        $currVal = 0;
        $prevVal = 0;
        foreach ($currFYModuleBreakdown as $row) {
            if ($row['module'] === $mod) {
                $currVal = (float) ($row['total_amount'] ?? 0);
                break;
            }
        }
        foreach ($prevFYModuleBreakdown as $row) {
            if ($row['module'] === $mod) {
                $prevVal = (float) ($row['total_amount'] ?? 0);
                break;
            }
        }

        $modChange = null;
        $modChangeLabel = '—';
        $modChangeClass = 'var(--text-light)';
        if ($prevVal > 0) {
            $pct = round((($currVal - $prevVal) / $prevVal) * 100, 1);
            $modChangeLabel = ($pct > 0 ? '+' : '') . $pct . '%';
            $modChangeClass = $pct >= 0 ? '#2e7d32' : '#c62828';
            $modChange = $pct;
        }

        $meta = $moduleMeta[$mod] ?? [];
        $moduleLinks = [
            'donations'  => 'admin/donations',
            'puja'       => 'admin/bookings',
            'yagya'      => 'admin/bookings',
            'panihati'   => 'admin/panihati-yatra',
            'sudamaseva' => 'admin/sudamaseva-dashboard',
        ];

        $fyComparisonModules[] = [
            'module' => $mod,
            'label' => $meta['label'] ?? ucfirst($mod),
            'icon' => $meta['icon'] ?? 'fa-circle',
            'color' => $meta['color'] ?? '#757575',
            'link' => $moduleLinks[$mod] ?? null,
            'curr_amount' => $currVal,
            'curr_formatted' => $adminDashService->formatAmount($currVal),
            'prev_amount' => $prevVal,
            'prev_formatted' => $adminDashService->formatAmount($prevVal),
            'change_label' => $modChangeLabel,
            'change_class' => $modChangeClass,
            'change' => $modChange,
        ];
    }

    // Monthly comparison chart data: sum across all modules per month
    $currFYMonthly = $adminDashService->getMonthlyTrendChart(12, [], $currFYFrom, $currFYTo);
    $prevFYMonthly = $adminDashService->getMonthlyTrendChart(12, [], $prevFYFrom, $prevFYTo);

    // Build FY monthly comparison datasets: sum all module data per month position
    $fyChartLabels = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
    $fyChartCurrData = [];
    $fyChartPrevData = [];

    // Current FY: sum all datasets per label index
    if (!empty($currFYMonthly['labels'])) {
        foreach ($currFYMonthly['labels'] as $li => $lb) {
            $monthTotal = 0;
            foreach ($currFYMonthly['datasets'] as $ds) {
                $monthTotal += $ds['data'][$li] ?? 0;
            }
            $shortMonth = substr($lb, 0, 3);
            $idx = array_search($shortMonth, $fyChartLabels);
            if ($idx !== false) {
                $fyChartCurrData[$idx] = ($fyChartCurrData[$idx] ?? 0) + $monthTotal;
            }
        }
    }
    for ($i = 0; $i < 12; $i++) {
        $fyChartCurrData[$i] = $fyChartCurrData[$i] ?? 0;
    }
    ksort($fyChartCurrData);
    $fyChartCurrData = array_values($fyChartCurrData);

    // Previous FY: same approach
    if (!empty($prevFYMonthly['labels'])) {
        foreach ($prevFYMonthly['labels'] as $li => $lb) {
            $monthTotal = 0;
            foreach ($prevFYMonthly['datasets'] as $ds) {
                $monthTotal += $ds['data'][$li] ?? 0;
            }
            $shortMonth = substr($lb, 0, 3);
            $idx = array_search($shortMonth, $fyChartLabels);
            if ($idx !== false) {
                $fyChartPrevData[$idx] = ($fyChartPrevData[$idx] ?? 0) + $monthTotal;
            }
        }
    }
    for ($i = 0; $i < 12; $i++) {
        $fyChartPrevData[$i] = $fyChartPrevData[$i] ?? 0;
    }
    ksort($fyChartPrevData);
    $fyChartPrevData = array_values($fyChartPrevData);

    $hasFYChartData = array_sum($fyChartCurrData) + array_sum($fyChartPrevData) > 0;

    // Module colors for JS
    $moduleColorsJson = json_encode([
        'donations' => '#c86b1f',
        'puja' => '#0b5ed7',
        'yagya' => '#c62828',
        'panihati' => '#2e7d32',
        'sudamaseva' => '#d4af37',
    ]);
  }
  if ($isEditor) {
    // Total blogs count
    $stmt = $db->query("SELECT COUNT(*) FROM blogs");
    $totalBlogs = (int)$stmt->fetchColumn();

    // Published blogs
    $stmt = $db->query("SELECT COUNT(*) FROM blogs WHERE is_published = 1");
    $publishedBlogs = (int)$stmt->fetchColumn();

    // Draft blogs
    $stmt = $db->query("SELECT COUNT(*) FROM blogs WHERE is_published = 0");
    $draftBlogs = (int)$stmt->fetchColumn();

    // Active Festivals & Causes
    $stmt = $db->query("SELECT COUNT(*) FROM donation_causes WHERE is_active = 1");
    $activeCauses = (int)$stmt->fetchColumn();

    // Fetch 5 recent blogs for quick view
    $stmt = $db->query("SELECT id, title, published_date, is_published, icon FROM blogs ORDER BY id DESC LIMIT 5");
    $recentBlogs = $stmt->fetchAll();
  }
  if ($isPujari) {
    // Total Paid Bookings
    $stmt = $db->query("
            SELECT COUNT(*) 
            FROM booking_pujas b 
            JOIN donation_transactions t ON b.transaction_id = t.id 
            WHERE t.payment_status = 'paid'
        ");
    $totalBookings = (int)$stmt->fetchColumn();

    // Pending bookings
    $stmt = $db->query("
            SELECT COUNT(*) 
            FROM booking_pujas b 
            JOIN donation_transactions t ON b.transaction_id = t.id 
            WHERE b.status = 'Pending' AND t.payment_status = 'paid'
        ");
    $pendingBookings = (int)$stmt->fetchColumn();

    // Completed bookings
    $stmt = $db->query("
            SELECT COUNT(*) 
            FROM booking_pujas b 
            JOIN donation_transactions t ON b.transaction_id = t.id 
            WHERE b.status = 'Completed' AND t.payment_status = 'paid'
        ");
    $completedBookings = (int)$stmt->fetchColumn();

    // Upcoming Bookings in next 7 days
    $stmt = $db->query("
            SELECT COUNT(*) 
            FROM booking_pujas b 
            JOIN donation_transactions t ON b.transaction_id = t.id 
            WHERE b.puja_date >= CURDATE() AND b.puja_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND t.payment_status = 'paid'
        ");
    $upcomingBookings = (int)$stmt->fetchColumn();

    // Puja vs Yagya split
    $stmt = $db->query("
            SELECT 
                CASE WHEN LOWER(b.puja_type) LIKE '%yagya%' THEN 'Yagya' ELSE 'Puja' END as type_label,
                COUNT(*) as count
            FROM booking_pujas b
            JOIN donation_transactions t ON b.transaction_id = t.id
            WHERE t.payment_status = 'paid'
            GROUP BY type_label
            ORDER BY count DESC
        ");
    $pujaYagyaSplit = $stmt->fetchAll();
    $pujaYagyaLabels = [];
    $pujaYagyaData = [];
    foreach ($pujaYagyaSplit as $row) {
      $pujaYagyaLabels[] = $row['type_label'];
      $pujaYagyaData[] = (int)$row['count'];
    }

    // Fetch 5 upcoming bookings
    $stmt = $db->query("
            SELECT b.*, t.payment_status 
            FROM booking_pujas b 
            JOIN donation_transactions t ON b.transaction_id = t.id 
            WHERE t.payment_status = 'paid' AND b.puja_date >= CURDATE()
            ORDER BY b.puja_date ASC, b.id DESC 
            LIMIT 5
        ");
    $recentBookings = $stmt->fetchAll();
  }
} catch (PDOException $e) {
  $dbError = 'A database error occurred. Please try again.';
  // Default placeholders
  $overview = ['total_collections' => 0, 'this_month' => 0, 'today' => 0, 'total_entries' => 0];
  $moduleBreakdown = [];
  $trendChart = ['labels' => [], 'datasets' => []];
  $recentCollections = [];
  $recurringPipeline = ['total_subs_count' => 0, 'total_monthly' => 0];
  $catLabels = $catData = [];
  $moduleColorsJson = json_encode([]);
  $hasFYChartData = false;
  $currFYTotal = $prevFYTotal = 0;
  $fyChange = null;
  $fyChangeLabel = '—';
  $fyChangeClass = 'var(--text-light)';
  $fyChangeIcon = '';
  $totalBlogs = 0;
  $publishedBlogs = 0;
  $draftBlogs = 0;
  $activeCauses = 0;
  $totalBookings = 0;
  $pendingBookings = 0;
  $completedBookings = 0;
  $upcomingBookings = 0;
  $recentBlogs = $recentBookings = [];
}
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Dashboard Overview</h1>
    <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong> (Roles: <?php echo htmlspecialchars(implode(', ', array_map(function($r) { return ucwords(str_replace('_', ' ', $r)); }, $userRoles))); ?>)</p>
  </div>
  <div class="admin-page-actions" style="display:flex; gap:6px;">
    <?php if ($isSuperAdmin || $isTreasurer): ?>
      <a href="admin/donations" class="btn btn-primary btn-sm"><i class="fas fa-list-ul"></i> View All Donations</a>
    <?php endif; ?>
    <?php if ($isEditor): ?>
      <a href="admin/blog-edit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Write Blog Post</a>
    <?php endif; ?>
    <?php if ($isPujari): ?>
      <a href="admin/bookings" class="btn btn-primary btn-sm"><i class="fas fa-calendar-check"></i> View Pujari Sheet</a>
    <?php endif; ?>
  </div>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($_SESSION['flash_success']); ?>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($_SESSION['flash_error']); ?>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (isset($dbError)): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i> Database query error: <?php echo htmlspecialchars($dbError); ?>
  </div>
<?php endif; ?>

<style>
  .module-card-link { text-decoration: none; color: inherit; display: block; }
  .module-card-link:hover .module-card-inner {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  .module-card-link:focus-visible .module-card-inner {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
  }
</style>

<?php
// Financial year quick-select options (currentFY already defined above)
$fyOptions = [];
for ($i = 0; $i <= 3; $i++) {
    $fyStart = $currentFY - $i;
    $fyEnd = $fyStart + 1;
    $label = "FY {$fyStart}-{$fyEnd}";
    $from = "{$fyStart}-04-01";
    $to = "{$fyEnd}-03-31";
    $fyOptions[] = [
        'label' => $label,
        'from' => $from,
        'to' => $to,
        'active' => ($filterFrom === $from && $filterTo === $to),
    ];
}
?>

<!-- Date Range Filter (shown only to finance users) -->
<?php if ($isSuperAdmin || $isTreasurer): ?>
  <div class="admin-card" style="margin-bottom:var(--space-xl);">
    <div class="admin-card-body" style="padding:var(--space-md) var(--space-lg);">
      <form action="admin/dashboard" method="GET" style="display:flex; flex-wrap:wrap; gap:var(--space-md); align-items:flex-end;">
        <div class="form-group" style="margin-bottom:0;">
          <label for="from" style="font-size:12px;">From</label>
          <input type="date" id="from" name="from" class="form-control" value="<?php echo htmlspecialchars($filterFrom); ?>">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label for="to" style="font-size:12px;">To</label>
          <input type="date" id="to" name="to" class="form-control" value="<?php echo htmlspecialchars($filterTo); ?>">
        </div>
        <div style="display:flex; gap:6px;">
          <button type="submit" class="btn btn-primary" style="background:var(--primary); color:white; border:none; padding:6px 20px; border-radius:var(--radius-md); font-size:12px; font-weight:600; cursor:pointer;">
            <i class="fas fa-filter"></i> Apply
          </button>
          <?php if ($hasDateFilter): ?>
            <a href="admin/dashboard" class="btn btn-outline-dark" style="text-decoration:none; padding:6px 14px; border:1px solid var(--border); border-radius:var(--radius-md); font-size:12px; font-weight:600;">
              <i class="fas fa-times"></i> Clear
            </a>
          <?php endif; ?>
        </div>
        <?php if ($hasDateFilter): ?>
          <div style="font-size:12px; color:var(--text-light); display:flex; align-items:center; gap:4px;">
            <i class="fas fa-calendar-alt"></i>
            Showing data from <strong><?php echo htmlspecialchars($filterFrom ?: 'earliest'); ?></strong> to <strong><?php echo htmlspecialchars($filterTo ?: 'latest'); ?></strong>
          </div>
        <?php endif; ?>
      </form>

      <!-- Financial Year quick-select buttons -->
      <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:var(--space-sm); padding-top:var(--space-sm); border-top:1px solid var(--border);">
        <span style="font-size:11px; color:var(--text-light); font-weight:600; display:flex; align-items:center; gap:4px; margin-right:4px;">
          <i class="fas fa-calendar"></i> Quick Select:
        </span>
        <?php foreach ($fyOptions as $fy): ?>
          <a href="admin/dashboard?from=<?php echo $fy['from']; ?>&to=<?php echo $fy['to']; ?>"
             style="text-decoration:none; padding:4px 12px; border-radius:var(--radius-md); font-size:12px; font-weight:600; <?php echo $fy['active'] ? 'background:var(--primary); color:white;' : 'background:var(--light); color:var(--text); border:1px solid var(--border);'; ?>">
            <?php echo $fy['label']; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- ========================================== -->
<!-- 1. DASHBOARD FOR SUPER_ADMIN & TREASURER -->
<!-- ========================================== -->
<?php if ($isSuperAdmin || $isTreasurer): ?>
  <!-- YoY Growth KPI Card -->
  <div class="admin-card" style="margin-bottom: var(--space-lg);">
    <div class="admin-card-body" style="padding:var(--space-md) var(--space-lg); display:flex; align-items:center; gap:var(--space-lg); flex-wrap:wrap;">
      <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:48px; height:48px; border-radius:var(--radius-md); background:<?php echo $fyChange > 0 ? '#d4edda' : ($fyChange < 0 ? '#fce4e4' : 'var(--light)'); ?>; display:flex; align-items:center; justify-content:center;">
          <i class="fas <?php echo $fyChange > 0 ? 'fa-arrow-up' : ($fyChange < 0 ? 'fa-arrow-down' : 'fa-minus'); ?>" style="font-size:20px; color:<?php echo $fyChangeClass; ?>;"></i>
        </div>
        <div>
          <div style="font-size:11px; color:var(--text-light); font-weight:600; text-transform:uppercase; letter-spacing:0.3px;">Year-over-Year Growth</div>
          <div style="display:flex; align-items:baseline; gap:10px;">
            <span style="font-size:28px; font-weight:800; color:<?php echo $fyChangeClass; ?>;"><?php echo $fyChangeIcon; ?><?php echo $fyChangeLabel; ?></span>
            <span style="font-size:13px; color:var(--text-light);">
              <?php echo $adminDashService->formatAmount($prevFYTotal); ?> → <?php echo $adminDashService->formatAmount($currFYTotal); ?>
            </span>
          </div>
        </div>
      </div>
      <div style="flex:1; min-width:200px; height:2px; background:var(--border); border-radius:2px; position:relative;">
        <div style="width:<?php echo $prevFYTotal > 0 ? round(($currFYTotal / $prevFYTotal) * 100) : 0; ?>%; max-width:100%; height:100%; background:<?php echo $fyChangeClass; ?>; border-radius:2px; transition:width 0.5s;"></div>
      </div>
      <div style="display:flex; gap:var(--space-lg); font-size:12px; color:var(--text-light);">
        <div>
          <div style="font-weight:600; color:var(--text);"><?php echo $adminDashService->formatAmount($prevFYTotal); ?></div>
          <div><?php echo $prevFY; ?>-<?php echo $prevFY + 1; ?></div>
        </div>
        <div style="font-size:18px; color:var(--text-light);">→</div>
        <div>
          <div style="font-weight:600; color:var(--maroon);"><?php echo $adminDashService->formatAmount($currFYTotal); ?></div>
          <div><?php echo $currentFY; ?>-<?php echo $currentFY + 1; ?> <span style="color:var(--primary); font-weight:600;">(Current)</span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Consolidated Stats Grid -->
  <div class="admin-stats-grid">
    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Total Collections</h3>
        <div class="admin-stat-value"><?php echo $adminDashService->formatAmount($overview['total_collections']); ?></div>
        <div style="font-size:11px; color:var(--text-light); margin-top:2px;">Across all modules</div>
      </div>
      <div class="admin-stat-icon"><i class="fas fa-indian-rupee-sign"></i></div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>This Month</h3>
        <div class="admin-stat-value"><?php echo $adminDashService->formatAmount($overview['this_month']); ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: rgba(200,107,31,0.15); color: var(--primary-dark);"><i class="fas fa-calendar-alt"></i></div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Today</h3>
        <div class="admin-stat-value"><?php echo $adminDashService->formatAmount($overview['today']); ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: #d4edda; color: green;"><i class="fas fa-sun"></i></div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Total Entries</h3>
        <div class="admin-stat-value"><?php echo number_format($overview['total_entries']); ?></div>
        <div style="font-size:11px; color:var(--text-light); margin-top:2px;">Paid records across all modules</div>
      </div>
      <div class="admin-stat-icon"><i class="fas fa-receipt"></i></div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Active Subscriptions</h3>
        <div class="admin-stat-value"><?php echo $recurringPipeline['total_subs_count']; ?></div>
        <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
          Monthly: <?php echo $adminDashService->formatAmount($recurringPipeline['total_monthly']); ?>
        </div>
      </div>
      <div class="admin-stat-icon" style="background-color: #f0f7ff; color: #0b5ed7;"><i class="fas fa-sync"></i></div>
    </div>
  </div>

  <!-- FY Comparison Card: Current FY vs Previous FY -->
  <div class="admin-card" style="margin-bottom: var(--space-xl);">
    <div class="admin-card-header" style="display:flex; justify-content:space-between; align-items:center;">
      <h2><i class="fas fa-exchange-alt"></i> FY Comparison: <?php echo $currentFY; ?>-<?php echo $currentFY + 1; ?> vs <?php echo $prevFY; ?>-<?php echo $prevFY + 1; ?></h2>
      <span style="font-size:11px; color:var(--text-light);"><i class="fas fa-info-circle"></i> Financial year (Apr–Mar)</span>
    </div>
    <div class="admin-card-body" style="padding:var(--space-lg);">
      <div style="display:grid; grid-template-columns: 1fr auto 1fr; gap:var(--space-md); align-items:center; margin-bottom:var(--space-lg);">
        <!-- Previous FY -->
        <div style="text-align:right; padding:var(--space-lg); background:var(--light); border-radius:var(--radius-md);">
          <div style="font-size:12px; color:var(--text-light); font-weight:600; text-transform:uppercase;"><?php echo $prevFY; ?>-<?php echo $prevFY + 1; ?></div>
          <div style="font-size:28px; font-weight:700; color:var(--maroon); margin:8px 0;"><?php echo $adminDashService->formatAmount($prevFYTotal); ?></div>
          <div style="font-size:11px; color:var(--text-light);"><?php echo number_format($prevFYData['total_entries']); ?> entries</div>
        </div>

        <!-- VS + Change indicator -->
        <div style="text-align:center;">
          <div style="font-size:24px; font-weight:700; color:var(--text-light);">VS</div>
          <div style="font-size:20px; font-weight:700; margin-top:8px; color:<?php echo $fyChangeClass; ?>;">
            <?php echo $fyChangeIcon; ?><?php echo $fyChangeLabel; ?>
          </div>
        </div>

        <!-- Current FY -->
        <div style="text-align:left; padding:var(--space-lg); background:var(--light); border-radius:var(--radius-md); border-left:4px solid var(--primary);">
          <div style="font-size:12px; color:var(--primary-dark); font-weight:700; text-transform:uppercase;"><?php echo $currentFY; ?>-<?php echo $currentFY + 1; ?> <span style="color:var(--text-light); font-weight:400;">(Current)</span></div>
          <div style="font-size:28px; font-weight:700; color:var(--maroon); margin:8px 0;"><?php echo $adminDashService->formatAmount($currFYTotal); ?></div>
          <div style="font-size:11px; color:var(--text-light);"><?php echo number_format($currFYData['total_entries']); ?> entries</div>
        </div>
      </div>

      <!-- Module-by-module comparison table -->
      <div style="overflow-x:auto;">
        <table class="admin-table" style="margin:0; border:none;">
          <thead>
            <tr>
              <th>Module</th>
              <th style="text-align:right;"><?php echo $prevFY; ?>-<?php echo $prevFY + 1; ?></th>
              <th style="text-align:right;"><?php echo $currentFY; ?>-<?php echo $currentFY + 1; ?></th>
              <th style="text-align:right;">Change</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fyComparisonModules as $fc): ?>
              <tr>
                <td>
                  <i class="fas <?php echo $fc['icon']; ?>" style="color:<?php echo $fc['color']; ?>; width:18px;"></i>
                  <?php if (!empty($fc['link'])): ?>
                    <a href="<?php echo $fc['link']; ?>" style="text-decoration:none; color:var(--text); font-weight:600;">
                      <?php echo htmlspecialchars($fc['label']); ?>
                    </a>
                  <?php else: ?>
                    <strong><?php echo htmlspecialchars($fc['label']); ?></strong>
                  <?php endif; ?>
                </td>
                <td style="text-align:right; color:var(--text-light);"><?php echo $fc['prev_formatted']; ?></td>
                <td style="text-align:right; font-weight:600; color:var(--maroon);"><?php echo $fc['curr_formatted']; ?></td>
                <td style="text-align:right; font-weight:600; color:<?php echo $fc['change_class']; ?>;">
                  <?php echo $fc['change_label']; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Monthly comparison chart -->
      <?php if ($hasFYChartData): ?>
      <div style="margin-top:var(--space-lg); padding-top:var(--space-lg); border-top:1px solid var(--border);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-md);">
          <h3 style="margin:0; font-size:14px; font-weight:600;"><i class="fas fa-chart-line"></i> Monthly Comparison: <?php echo $prevFY; ?> vs <?php echo $currentFY; ?></h3>
        </div>
        <div class="chart-container" style="height:220px;">
          <canvas id="fyMonthlyChart"></canvas>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Module Breakup Cards -->
  <div class="admin-card" style="margin-bottom: var(--space-xl);">
    <div class="admin-card-header">
      <h2><i class="fas fa-chart-pie"></i> Revenue by Module</h2>
      <span style="font-size:11px; color:var(--text-light);"><i class="fas fa-info-circle"></i> Financial year-to-date</span>
    </div>
    <div class="admin-card-body" style="padding:var(--space-lg);">
      <?php if (empty($moduleBreakdown)): ?>
        <div style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No revenue data available yet.</div>
      <?php else: ?>
        <!-- Module cards grid -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--space-md); margin-bottom: var(--space-lg);">
          <?php foreach ($moduleBreakdown as $mod):
            $cardLink = $moduleLinks[$mod['module']] ?? null;
          ?>
            <?php if ($cardLink): ?>
              <a href="<?php echo $cardLink; ?>" class="module-card-link" style="text-decoration:none; color:inherit; display:block;">
            <?php endif; ?>
            <div class="module-card-inner" style="background:var(--white); border:1px solid var(--border); border-radius:var(--radius-md); padding:var(--space-md); border-left:4px solid <?php echo $mod['color']; ?>; cursor:<?php echo $cardLink ? 'pointer' : 'default'; ?>; transition:transform 0.15s, box-shadow 0.15s;">
              <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                <i class="fas <?php echo $mod['icon']; ?>" style="color:<?php echo $mod['color']; ?>; font-size:16px;"></i>
                <strong style="font-size:13px;"><?php echo htmlspecialchars($mod['label']); ?></strong>
                <span style="font-size:11px; color:var(--text-light); margin-left:auto;"><?php echo $mod['share_pct']; ?>%</span>
              </div>
              <div style="font-size:20px; font-weight:700; color:var(--maroon);"><?php echo $mod['total_formatted']; ?></div>
              <div style="font-size:11px; color:var(--text-light); margin-top:4px;">
                <?php echo number_format($mod['payment_count']); ?> entries &middot; <?php echo $mod['this_month_formatted']; ?> this month
              </div>
              <!-- Progress bar for share -->
              <div style="width:100%; height:4px; background:var(--light); border-radius:3px; margin-top:8px; overflow:hidden;">
                <div style="width:<?php echo $mod['share_pct']; ?>%; height:100%; background:<?php echo $mod['color']; ?>; border-radius:3px;"></div>
              </div>
            </div>
            <?php if ($cardLink): ?>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <!-- Module breakdown table (compact) -->
        <div style="overflow-x:auto;">
          <table class="admin-table" style="margin:0; border:none;">
            <thead>
              <tr>
                <th>Module</th>
                <th style="text-align:right;">Total Revenue</th>
                <th style="text-align:center;">Entries</th>
                <th style="text-align:right;">This Month</th>
                <th style="text-align:right;">Share</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($moduleBreakdown as $mod): ?>
                <tr>
                  <td>
                    <i class="fas <?php echo $mod['icon']; ?>" style="color:<?php echo $mod['color']; ?>; width:18px;"></i>
                    <strong><?php echo htmlspecialchars($mod['label']); ?></strong>
                  </td>
                  <td style="text-align:right; font-weight:600; color:var(--maroon);"><?php echo $mod['total_formatted']; ?></td>
                  <td style="text-align:center; color:var(--text-light);"><?php echo number_format($mod['payment_count']); ?></td>
                  <td style="text-align:right; color:var(--text);"><?php echo $mod['this_month_formatted']; ?></td>
                  <td style="text-align:right;">
                    <span style="font-size:12px; font-weight:600; color:var(--text);"><?php echo $mod['share_pct']; ?>%</span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Charts Grid -->
  <div class="admin-charts-grid">
    <!-- Chart 1: Stacked Monthly Collections Trend -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Monthly Collections Trend</h2>
        <span style="font-size:11px; color:var(--text-light);"><i class="fas fa-info-circle"></i> All modules, last 12 months</span>
      </div>
      <div class="admin-card-body">
        <div class="chart-container">
          <?php if (empty($trendChart['datasets'])): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data available yet</div>
          <?php else: ?>
            <canvas id="stackedTrendChart"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Chart 2: Revenue Share by Module -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Revenue Share by Module</h2>
      </div>
      <div class="admin-card-body">
        <div class="chart-container">
          <?php if (empty($moduleBreakdown)): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data available yet</div>
          <?php else: ?>
            <canvas id="moduleShareChart"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Donation Category Split (existing chart, kept for detail) -->
  <div class="admin-card" style="margin-bottom: var(--space-xl);">
    <div class="admin-card-header">
      <h2><i class="fas fa-tag"></i> Donations by Category</h2>
      <span style="font-size:11px; color:var(--text-light);">General donations only (excludes bookings)</span>
    </div>
    <div class="admin-card-body" style="padding:var(--space-lg);">
      <div class="chart-container" style="height:200px; max-width:400px; margin:0 auto;">
        <?php if (empty($catData)): ?>
          <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No donation data yet</div>
        <?php else: ?>
          <canvas id="categoryChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Unified Recent Collections Table -->
  <div class="admin-card" style="margin-bottom: var(--space-xl);">
    <div class="admin-card-header">
      <h2><i class="fas fa-clock"></i> Recent Collections</h2>
      <span style="font-size:11px; color:var(--text-light);">Across all modules</span>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Module</th>
              <th>Source</th>
              <th>Donor</th>
              <th style="text-align:right;">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentCollections)): ?>
              <tr>
                <td colspan="5" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No collections yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentCollections as $c): ?>
                <tr>
                  <td style="font-size:12px; color:var(--text-light); white-space:nowrap;"><?php echo $c['date_formatted']; ?></td>
                  <td>
                    <span style="display:inline-flex;align-items:center;gap:4px; font-size:12px; font-weight:600; color:<?php echo $c['module_color']; ?>;">
                      <i class="fas <?php echo $c['module_icon']; ?>"></i>
                      <?php echo htmlspecialchars($c['module_label']); ?>
                    </span>
                  </td>
                  <td style="font-size:12px; color:var(--text-light);"><?php echo htmlspecialchars($c['source_label'] ?? ''); ?></td>
                  <td>
                    <strong style="font-size:13px;"><?php echo htmlspecialchars($c['donor_name'] ?? '—'); ?></strong>
                    <?php if (!empty($c['donor_phone'])): ?>
                      <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($c['donor_phone']); ?></div>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:right; font-weight:700; color:var(--maroon); font-size:15px;">
                    <?php echo $c['amount_formatted']; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Load Chart.js from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const moduleColors = <?php echo $moduleColorsJson; ?>;

      // 1. Stacked Monthly Trend Chart
      <?php if (!empty($trendChart['datasets'])): ?>
        new Chart(document.getElementById('stackedTrendChart'), {
          type: 'bar',
          data: {
            labels: <?php echo json_encode($trendChart['labels']); ?>,
            datasets: <?php echo json_encode(array_map(function($ds) {
              return [
                'label' => $ds['label'],
                'data' => $ds['data'],
                'backgroundColor' => $ds['backgroundColor'],
                'borderColor' => $ds['color'],
                'borderWidth' => 1,
                'borderRadius' => 2,
              ];
            }, $trendChart['datasets'])); ?>
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'top', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
              tooltip: {
                mode: 'index',
                callbacks: {
                  label: function(ctx) {
                    return ctx.dataset.label + ': ' + '₹' + ctx.parsed.y.toLocaleString('en-IN');
                  }
                }
              }
            },
            scales: {
              x: { stacked: true },
              y: {
                stacked: true,
                beginAtZero: true,
                ticks: { callback: function(v) { return '₹' + v.toLocaleString('en-IN'); } }
              }
            }
          }
        });
      <?php endif; ?>

      // 2. Revenue Share by Module Doughnut
      <?php if (!empty($moduleBreakdown)): ?>
        new Chart(document.getElementById('moduleShareChart'), {
          type: 'doughnut',
          data: {
            labels: <?php echo json_encode(array_column($moduleBreakdown, 'label')); ?>,
            datasets: [{
              data: <?php echo json_encode(array_column($moduleBreakdown, 'total_amount')); ?>,
              backgroundColor: <?php echo json_encode(array_column($moduleBreakdown, 'color')); ?>,
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'right', labels: { boxWidth: 12, padding: 14, font: { size: 11 } } },
              tooltip: {
                callbacks: {
                  label: function(ctx) {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                    return ctx.label + ': ' + '₹' + ctx.parsed.toLocaleString('en-IN') + ' (' + pct + '%)';
                  }
                }
              }
            },
            cutout: '65%'
          }
        });
      <?php endif; ?>

      // 3. FY Monthly Comparison Line Chart
      <?php if (!empty($fyChartLabels) && array_sum($fyChartCurrData) + array_sum($fyChartPrevData) > 0): ?>
        new Chart(document.getElementById('fyMonthlyChart'), {
          type: 'line',
          data: {
            labels: <?php echo json_encode($fyChartLabels); ?>,
            datasets: [{
              label: '<?php echo $prevFY; ?>-<?php echo $prevFY + 1; ?>',
              data: <?php echo json_encode($fyChartPrevData); ?>,
              borderColor: '#8a7a6a',
              backgroundColor: 'rgba(138,122,106,0.08)',
              borderWidth: 2,
              borderDash: [5, 4],
              fill: true,
              tension: 0.3,
              pointRadius: 4,
              pointBackgroundColor: '#8a7a6a',
              pointBorderColor: '#fff',
              pointBorderWidth: 2
            }, {
              label: '<?php echo $currentFY; ?>-<?php echo $currentFY + 1; ?>',
              data: <?php echo json_encode($fyChartCurrData); ?>,
              borderColor: '#c86b1f',
              backgroundColor: 'rgba(200,107,31,0.12)',
              borderWidth: 3,
              fill: true,
              tension: 0.3,
              pointRadius: 5,
              pointBackgroundColor: '#c86b1f',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
              pointHoverRadius: 8
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'top', labels: { boxWidth: 14, padding: 14, font: { size: 11 } } },
              tooltip: {
                callbacks: {
                  label: function(ctx) {
                    return ctx.dataset.label + ': ₹' + ctx.parsed.y.toLocaleString('en-IN');
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: { callback: function(v) { return '₹' + v.toLocaleString('en-IN'); } }
              }
            }
          }
        });
      <?php endif; ?>

      // 4. Donations by Category Doughnut (kept for detail)
      <?php if (!empty($catData)): ?>
        const catColors = ['#c86b1f','#d4af37','#7b1e1e','#2c1b12','#8a7a6a','#e8ddd0','#4a7c59','#1565c0'];
        new Chart(document.getElementById('categoryChart'), {
          type: 'doughnut',
          data: {
            labels: <?php echo json_encode($catLabels); ?>,
            datasets: [{
              data: <?php echo json_encode($catData); ?>,
              backgroundColor: catColors.slice(0, <?php echo count($catData); ?>),
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 }, padding: 6 } }
            },
            cutout: '60%'
          }
        });
      <?php endif; ?>
    });
  </script>

<?php endif; ?>

<!-- ========================================== -->
<!-- 2. DASHBOARD FOR EDITORS -->
<!-- ========================================== -->
<?php if ($isEditor): ?>
  <!-- Stats Grid -->
  <div class="admin-stats-grid">
    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Total Articles</h3>
        <div class="admin-stat-value"><?php echo $totalBlogs; ?></div>
      </div>
      <div class="admin-stat-icon">
        <i class="fas fa-newspaper"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Published Posts</h3>
        <div class="admin-stat-value" style="color: green;"><?php echo $publishedBlogs; ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: #d4edda; color: green;">
        <i class="fas fa-check-circle"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Draft Posts</h3>
        <div class="admin-stat-value" style="color: var(--text-light);"><?php echo $draftBlogs; ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: var(--cream); color: var(--text-light);">
        <i class="fas fa-eye-slash"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Active Causes/Festivals</h3>
        <div class="admin-stat-value" style="color: var(--primary-dark);"><?php echo $activeCauses; ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: rgba(200, 107, 31, 0.15); color: var(--primary-dark);">
        <i class="fas fa-calendar-alt"></i>
      </div>
    </div>
  </div>

  <div class="admin-charts-grid" style="grid-template-columns: 2fr 1fr;">
    <!-- Recent Blogs table -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Recently Modified Articles</h2>
        <a href="admin/blogs" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight:600;">View All Blogs</a>
      </div>
      <div class="admin-card-body" style="padding:0;">
        <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentBlogs)): ?>
                <tr>
                  <td colspan="4" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No articles found.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($recentBlogs as $rb): ?>
                  <tr>
                    <td>
                      <i class="fas <?php echo htmlspecialchars($rb['icon'] ?: 'fa-newspaper'); ?>" style="color:var(--primary); margin-right:6px;"></i>
                      <strong><?php echo htmlspecialchars($rb['title']); ?></strong>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($rb['published_date'])); ?></td>
                    <td>
                      <span class="badge <?php echo $rb['is_published'] ? 'badge-published' : 'badge-draft'; ?>">
                        <?php echo $rb['is_published'] ? 'Published' : 'Draft'; ?>
                      </span>
                    </td>
                    <td>
                      <a href="admin/blog-edit?id=<?php echo $rb['id']; ?>" class="btn-sm-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Quick Shortcuts Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Quick Shortcuts</h2>
      </div>
      <div class="admin-card-body" style="display:flex; flex-direction:column; gap:12px;">
        <a href="admin/blog-edit" class="btn btn-primary" style="text-decoration:none; text-align:center; padding:12px; border-radius:var(--radius-md); font-weight:600; display:block; background-color: var(--primary); color:white;">
          <i class="fas fa-plus" style="margin-right:6px;"></i> Write New Article
        </a>
        <a href="admin/festival-edit" class="btn btn-outline-dark" style="text-decoration:none; text-align:center; padding:12px; border-radius:var(--radius-md); font-weight:600; display:block; border:1px solid var(--border); color:var(--text);">
          <i class="fas fa-calendar-plus" style="margin-right:6px;"></i> Add New Festival
        </a>
        <div style="background:var(--light); padding: var(--space-md); border-radius: var(--radius-md); border-left:3px solid var(--accent); font-size:12px; line-height:1.5; color:var(--text);">
          <strong>Content Guidelines:</strong><br>
          * Load images locally via files upload.<br>
          * Always configure SEO Meta Title & Descriptions.
        </div>
      </div>
    </div>
  </div>

<?php endif; ?>

<!-- ========================================== -->
<!-- 3. DASHBOARD FOR PUJARIS -->
<!-- ========================================== -->
<?php if ($isPujari): ?>
  <!-- Stats Grid -->
  <div class="admin-stats-grid">
    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Total Puja/Yagya Bookings</h3>
        <div class="admin-stat-value"><?php echo $totalBookings; ?></div>
      </div>
      <div class="admin-stat-icon">
        <i class="fas fa-scroll"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Pending Performance</h3>
        <div class="admin-stat-value" style="color: var(--primary-dark);"><?php echo $pendingBookings; ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: rgba(200, 107, 31, 0.15); color: var(--primary-dark);">
        <i class="fas fa-hourglass-half"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Completed Sevas</h3>
        <div class="admin-stat-value" style="color: green;"><?php echo $completedBookings; ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: #d4edda; color: green;">
        <i class="fas fa-check-double"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Next 7 Days Schedule</h3>
        <div class="admin-stat-value" style="color: #0b5ed7;"><?php echo $upcomingBookings; ?></div>
      </div>
      <div class="admin-stat-icon" style="background-color: #f0f7ff; color: #0b5ed7;">
        <i class="fas fa-calendar-day"></i>
      </div>
    </div>
  </div>

  <!-- Puja vs Yagya Chart -->
  <div class="admin-charts-grid" style="grid-template-columns: 1fr 2fr; margin-bottom: var(--space-xl);">
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Puja vs Yagya Split</h2>
      </div>
      <div class="admin-card-body">
        <div class="chart-container">
          <?php if (empty($pujaYagyaData)): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No booking data yet</div>
          <?php else: ?>
            <canvas id="pujaYagyaChart"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Upcoming Schedule Table -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Upcoming Puja & Yagya Schedule (Next 5 Items)</h2>
        <a href="admin/bookings" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight:600;">View All Bookings</a>
      </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type & Details</th>
              <th>Yajaman / Devotee</th>
              <th>Gotra / Nakshatra</th>
              <th>Status</th>
              <th style="text-align:center;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentBookings)): ?>
              <tr>
                <td colspan="6" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No upcoming bookings scheduled for today or later.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentBookings as $rb):
                $isYagya = (strpos(strtolower($rb['puja_type']), 'yagya') !== false);
                $badge = $isYagya ? '<span class="badge" style="background-color:#fff0f0; color:#c92a2a;">Yagya</span>' : '<span class="badge" style="background-color:#f0f7ff; color:#0b5ed7;">Puja</span>';
              ?>
                <tr>
                  <td style="font-weight:600;"><?php echo date('M d, Y', strtotime($rb['puja_date'])); ?></td>
                  <td>
                    <?php echo $badge; ?>
                    <strong><?php echo htmlspecialchars($rb['puja_type']); ?></strong>
                  </td>
                  <td><?php echo htmlspecialchars($rb['person_name']); ?></td>
                  <td>
                    Gotra: <?php echo htmlspecialchars($rb['gotra'] ?: '-'); ?><br>
                    Nakshatra: <?php echo htmlspecialchars($rb['nakshatra'] ?: '-'); ?>
                  </td>
                  <td>
                    <span class="badge <?php echo $rb['status'] === 'Completed' ? 'badge-success' : 'badge-warning'; ?>">
                      <?php echo htmlspecialchars($rb['status']); ?>
                    </span>
                  </td>
                  <td style="text-align:center;">
                    <a href="admin/dashboard?toggle_status_id=<?php echo $rb['id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-sm-action btn-edit" title="Toggle completion status">
                      <i class="fas fa-sync"></i> Toggle Status
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </div>

  <!-- Load Chart.js from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (!empty($pujaYagyaData)): ?>
        const ctxPujaYagya = document.getElementById('pujaYagyaChart').getContext('2d');
        new Chart(ctxPujaYagya, {
          type: 'doughnut',
          data: {
            labels: <?php echo json_encode($pujaYagyaLabels); ?>,
            datasets: [{
              data: <?php echo json_encode($pujaYagyaData); ?>,
              backgroundColor: ['#0b5ed7', '#c92a2a'],
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  boxWidth: 12,
                  padding: 16
                }
              }
            },
            cutout: '60%'
          }
        });
      <?php endif; ?>
    });
  </script>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>