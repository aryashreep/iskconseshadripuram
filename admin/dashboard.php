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

include 'partials/header.php';

$roleString = $_SESSION['admin_role'] ?? 'editor';
$userRoles = array_map('trim', explode(',', $roleString));
$isSuperAdmin = in_array('super_admin', $userRoles);
$isTreasurer = in_array('treasurer', $userRoles);
$isEditor = in_array('editor', $userRoles);
$isPujari = in_array('pujari', $userRoles);

try {
  if ($isSuperAdmin || $isTreasurer) {
    // 1. Stats Queries
    // Total Revenue (Paid)
    $stmt = $db->query("SELECT SUM(amount) as total FROM donation_transactions WHERE payment_status = 'paid'");
    $totalRevenue = (float)$stmt->fetchColumn();

    // Total Paid Count
    $stmt = $db->query("SELECT COUNT(*) as total_count FROM donation_transactions WHERE payment_status = 'paid'");
    $totalPaidCount = (int)$stmt->fetchColumn();

    // Unique Donors Count
    $stmt = $db->query("SELECT COUNT(DISTINCT donor_email) as donors FROM donation_transactions WHERE payment_status = 'paid'");
    $uniqueDonors = (int)$stmt->fetchColumn();

    // Active Monthly Subscriptions
    $stmt = $db->query("SELECT COUNT(*) as active_subs FROM donation_subscriptions WHERE subscription_status = 'active'");
    $activeSubs = (int)$stmt->fetchColumn();

    // Donor Repeat Rate
    $stmt = $db->query("
            SELECT 
              COUNT(*) as total_donors,
              SUM(CASE WHEN donation_count > 1 THEN 1 ELSE 0 END) as repeat_donors
            FROM (
              SELECT donor_email, COUNT(*) as donation_count
              FROM donation_transactions
              WHERE payment_status = 'paid' AND donor_email IS NOT NULL AND donor_email != ''
              GROUP BY donor_email
            ) donor_counts
        ");
    $donorStats = $stmt->fetch();
    $totalDonorEmails = (int)$donorStats['total_donors'];
    $repeatDonors = (int)$donorStats['repeat_donors'];
    $repeatRate = $totalDonorEmails > 0 ? round(($repeatDonors / $totalDonorEmails) * 100, 1) : 0;

    // 2. Chart Queries
    // Chart 1: Monthly Revenue Trend (Last 12 months)
    $stmt = $db->query("
            SELECT 
                DATE_FORMAT(created_at, '%b %Y') as month_label, 
                DATE_FORMAT(created_at, '%Y-%m') as month_key, 
                SUM(amount) as total 
            FROM donation_transactions 
            WHERE payment_status = 'paid' 
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
            ORDER BY month_key ASC 
            LIMIT 12
        ");
    $monthlyTrend = $stmt->fetchAll();

    $trendLabels = [];
    $trendData = [];
    foreach ($monthlyTrend as $row) {
      $trendLabels[] = $row['month_label'];
      $trendData[] = (float)$row['total'];
    }

    // Chart 2: Revenue by Cause Category
    $stmt = $db->query("
            SELECT c.category, SUM(t.amount) as total 
            FROM donation_transactions t
            JOIN donation_causes c ON t.cause_id = c.id
            WHERE t.payment_status = 'paid'
            GROUP BY c.category
            ORDER BY total DESC
        ");
    $categorySplit = $stmt->fetchAll();

    $catLabels = [];
    $catData = [];
    foreach ($categorySplit as $row) {
      $catLabels[] = ucfirst($row['category']);
      $catData[] = (float)$row['total'];
    }

    // Chart 3: Top 5 Performing Festivals/Causes
    $stmt = $db->query("
            SELECT c.title, SUM(t.amount) as total 
            FROM donation_transactions t
            JOIN donation_causes c ON t.cause_id = c.id
            WHERE t.payment_status = 'paid'
            GROUP BY t.cause_id, c.title
            ORDER BY total DESC
            LIMIT 5
        ");
    $topCauses = $stmt->fetchAll();

    $causeLabels = [];
    $causeData = [];
    foreach ($topCauses as $row) {
      $causeLabels[] = strlen($row['title']) > 20 ? substr($row['title'], 0, 17) . '...' : $row['title'];
      $causeData[] = (float)$row['total'];
    }

    // 3. Payment Status Breakdown
    $stmt = $db->query("
            SELECT payment_status, COUNT(*) as count, SUM(amount) as total
            FROM donation_transactions
            GROUP BY payment_status
            ORDER BY count DESC
        ");
    $statusBreakdown = $stmt->fetchAll();
    $statusLabels = [];
    $statusData = [];
    $statusColors = [];
    $colorMap = [
      'paid' => '#2e7d32',
      'failed' => '#c62828',
      'created' => '#f9a825',
      'attempted' => '#ef6c00',
      'cancelled' => '#757575',
      'refunded' => '#1565c0',
    ];
    foreach ($statusBreakdown as $row) {
      $label = ucfirst($row['payment_status']);
      $statusLabels[] = $label . ' (' . $row['count'] . ')';
      $statusData[] = (float)$row['total'];
      $statusColors[] = $colorMap[$row['payment_status']] ?? '#9e9e9e';
    }

    // 4. Recent Transactions Query
    $stmt = $db->query("
            SELECT t.*, c.title as cause_title
            FROM donation_transactions t
            LEFT JOIN donation_causes c ON t.cause_id = c.id
            ORDER BY t.created_at DESC
            LIMIT 5
        ");
    $recentDonations = $stmt->fetchAll();
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
  $totalRevenue = 0;
  $totalPaidCount = 0;
  $uniqueDonors = 0;
  $activeSubs = 0;
  $totalDonorEmails = 0;
  $repeatDonors = 0;
  $repeatRate = 0;
  $totalBlogs = 0;
  $publishedBlogs = 0;
  $draftBlogs = 0;
  $activeCauses = 0;
  $totalBookings = 0;
  $pendingBookings = 0;
  $completedBookings = 0;
  $upcomingBookings = 0;
  $trendLabels = $trendData = $catLabels = $catData = $causeLabels = $causeData = $statusLabels = $statusData = $statusColors = [];
  $recentDonations = $recentBlogs = $recentBookings = [];
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

<!-- ========================================== -->
<!-- 1. DASHBOARD FOR SUPER_ADMIN & TREASURER -->
<!-- ========================================== -->
<?php if ($isSuperAdmin || $isTreasurer): ?>
  <!-- Stats Grid -->
  <div class="admin-stats-grid">
    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Total Revenue</h3>
        <div class="admin-stat-value">₹<?php echo number_format($totalRevenue, 2); ?></div>
      </div>
      <div class="admin-stat-icon">
        <i class="fas fa-indian-rupee-sign"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Donation Count</h3>
        <div class="admin-stat-value"><?php echo $totalPaidCount; ?></div>
      </div>
      <div class="admin-stat-icon">
        <i class="fas fa-hand-holding-heart"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Unique Donors</h3>
        <div class="admin-stat-value"><?php echo $uniqueDonors; ?></div>
      </div>
      <div class="admin-stat-icon">
        <i class="fas fa-users"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Active Monthly Subs</h3>
        <div class="admin-stat-value"><?php echo $activeSubs; ?></div>
      </div>
      <div class="admin-stat-icon">
        <i class="fas fa-calendar-check"></i>
      </div>
    </div>

    <div class="admin-stat-card">
      <div class="admin-stat-info">
        <h3>Donor Repeat Rate</h3>
        <div class="admin-stat-value" style="color: <?php echo $repeatRate >= 30 ? 'green' : ($repeatRate >= 15 ? 'var(--primary-dark)' : 'var(--text-light)'); ?>;">
          <?php echo $repeatRate; ?>%
        </div>
        <div style="font-size:11px; color:var(--text-light); margin-top:2px;">
          <?php echo $repeatDonors; ?> of <?php echo $totalDonorEmails; ?> donors returned
        </div>
      </div>
      <div class="admin-stat-icon" style="background-color: <?php echo $repeatRate >= 30 ? '#d4edda' : 'rgba(200,107,31,0.15)'; ?>; color: <?php echo $repeatRate >= 30 ? 'green' : 'var(--primary-dark)'; ?>;">
        <i class="fas fa-user-check"></i>
      </div>
    </div>
  </div>

  <!-- Charts Grid -->
  <div class="admin-charts-grid">
    <!-- Chart 1: Revenue Trend -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Revenue Trend</h2>
        <span style="font-size:11px; color:var(--text-light);"><i class="fas fa-info-circle"></i> Month-over-Month</span>
      </div>
      <div class="admin-card-body">
        <div class="chart-container">
          <?php if (empty($trendData)): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data available yet</div>
          <?php else: ?>
            <canvas id="revenueTrendChart"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Chart 2: Category distribution -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Revenue by Category</h2>
      </div>
      <div class="admin-card-body">
        <div class="chart-container">
          <?php if (empty($catData)): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data available yet</div>
          <?php else: ?>
            <canvas id="categoryChart"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Extra row with top causes & recent table -->
  <div class="admin-charts-grid" style="grid-template-columns: 1fr 1fr;">
    <!-- Chart 3: Top Causes Comparison -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Top 5 Festivals & Causes by Revenue</h2>
      </div>
      <div class="admin-card-body">
        <div class="chart-container">
          <?php if (empty($causeData)): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No data available yet</div>
          <?php else: ?>
            <canvas id="topCausesChart"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Chart 4: Payment Status Breakdown -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h2>Payment Status Breakdown</h2>
      </div>
      <div class="admin-card-body">
        <div class="chart-container">
          <?php if (empty($statusData)): ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-light);">No transactions yet</div>
          <?php else: ?>
            <canvas id="paymentStatusChart"></canvas>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Transactions Table (full width, below charts) -->
  <div class="admin-card" style="margin-bottom: var(--space-xl);">
    <div class="admin-card-header">
      <h2>Recent Successful Donations</h2>
      <a href="admin/donations" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight:600;">View All</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
      <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Donor</th>
              <th>Cause / Festival</th>
              <th>Amount</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentDonations)): ?>
              <tr>
                <td colspan="4" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No donations received yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentDonations as $d): ?>
                <tr>
                  <td>
                    <strong style="color:var(--dark);"><?php echo htmlspecialchars($d['donor_name']); ?></strong>
                    <div style="font-size:11px;color:var(--text-light);"><?php echo htmlspecialchars($d['donor_email']); ?></div>
                  </td>
                  <td><?php echo htmlspecialchars($d['cause_title'] ?: 'General Donation'); ?></td>
                  <td style="font-weight:600; color:var(--maroon);">₹<?php echo number_format($d['amount'], 2); ?></td>
                  <td style="font-size:12px; color:var(--text-light);"><?php echo date('M d, Y H:i', strtotime($d['created_at'])); ?></td>
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
      const primaryColor = '#c86b1f';
      const primaryLight = 'rgba(200, 107, 31, 0.2)';
      const accentColor = '#d4af37';
      const maroonColor = '#7b1e1e';
      const darkColor = '#2c1b12';

      // 1. Monthly Revenue Trend Line Chart
      <?php if (!empty($trendData)): ?>
        const ctxTrend = document.getElementById('revenueTrendChart').getContext('2d');
        new Chart(ctxTrend, {
          type: 'line',
          data: {
            labels: <?php echo json_encode($trendLabels); ?>,
            datasets: [{
              label: 'Donation Volume (₹)',
              data: <?php echo json_encode($trendData); ?>,
              borderColor: primaryColor,
              backgroundColor: primaryLight,
              borderWidth: 3,
              fill: true,
              tension: 0.3,
              pointBackgroundColor: primaryColor,
              pointBorderColor: '#fff',
              pointHoverRadius: 7
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return '₹' + value.toLocaleString();
                  }
                }
              }
            }
          }
        });
      <?php endif; ?>

      // 2. Revenue by Category Doughnut Chart
      <?php if (!empty($catData)): ?>
        const ctxCategory = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCategory, {
          type: 'doughnut',
          data: {
            labels: <?php echo json_encode($catLabels); ?>,
            datasets: [{
              data: <?php echo json_encode($catData); ?>,
              backgroundColor: [primaryColor, accentColor, maroonColor, darkColor, '#8a7a6a', '#e8ddd0'],
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right',
                labels: {
                  boxWidth: 12
                }
              }
            },
            cutout: '65%'
          }
        });
      <?php endif; ?>

      // 3. Top 5 Performing Causes Bar Chart
      <?php if (!empty($causeData)): ?>
        const ctxCauses = document.getElementById('topCausesChart').getContext('2d');
        new Chart(ctxCauses, {
          type: 'bar',
          data: {
            labels: <?php echo json_encode($causeLabels); ?>,
            datasets: [{
              label: 'Revenue (₹)',
              data: <?php echo json_encode($causeData); ?>,
              backgroundColor: [primaryColor, accentColor, maroonColor, darkColor, '#8a7a6a'],
              borderRadius: 6,
              maxBarThickness: 35
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return '₹' + value.toLocaleString();
                  }
                }
              }
            }
          }
        });
      <?php endif; ?>

      // 4. Payment Status Breakdown Doughnut Chart
      <?php if (!empty($statusData)): ?>
        const ctxStatus = document.getElementById('paymentStatusChart').getContext('2d');
        new Chart(ctxStatus, {
          type: 'doughnut',
          data: {
            labels: <?php echo json_encode($statusLabels); ?>,
            datasets: [{
              data: <?php echo json_encode($statusData); ?>,
              backgroundColor: <?php echo json_encode($statusColors); ?>,
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right',
                labels: {
                  boxWidth: 12,
                  padding: 14
                }
              },
              tooltip: {
                callbacks: {
                  label: function(ctx) {
                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const value = ctx.parsed;
                    const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return ctx.label.split(' (')[0] + ': ₹' + value.toLocaleString('en-IN') + ' (' + pct + '%)';
                  }
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