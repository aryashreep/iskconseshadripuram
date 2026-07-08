<?php
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('bookings.view');

$pageTitle = 'Puja & Yagya Bookings';
$activePage = 'bookings';
include 'partials/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle quick actions: Toggle Performance Status (Pending <-> Completed)
if (isset($_GET['toggle_status_id'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed. Unauthorized request.';
    } elseif (!hasPermission('bookings.edit')) {
        $error = 'You do not have permission to modify booking performance status.';
    } else {
        $toggleId = intval($_GET['toggle_status_id']);
        try {
            $stmt = $db->prepare("SELECT status FROM booking_pujas WHERE id = ?");
            $stmt->execute([$toggleId]);
            $current = $stmt->fetchColumn();
            if ($current !== false) {
                $newStatus = ($current === 'Completed') ? 'Pending' : 'Completed';
                $upStmt = $db->prepare("UPDATE booking_pujas SET status = ? WHERE id = ?");
                $upStmt->execute([$newStatus, $toggleId]);
                $message = 'Booking performance status updated successfully.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to update status. Please try again.';
        }
    }
}

// Read parameters
$search = trim($_GET['search'] ?? '');
$bookingType = trim($_GET['booking_type'] ?? '');
$perfStatus = trim($_GET['perf_status'] ?? '');
$paymentStatus = trim($_GET['payment_status'] ?? 'paid'); // default paid for pujari team
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(b.person_name LIKE ? OR b.gotra LIKE ? OR b.rashi LIKE ? OR b.nakshatra LIKE ? OR b.occasion LIKE ? OR t.donor_name LIKE ? OR t.donor_email LIKE ? OR t.donor_phone LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if ($bookingType === 'yagya') {
    $where[] = "b.puja_type LIKE '%yagya%'";
} elseif ($bookingType === 'puja') {
    $where[] = "b.puja_type NOT LIKE '%yagya%'";
}

if ($perfStatus !== '') {
    $where[] = "b.status = ?";
    $params[] = $perfStatus;
}

if ($paymentStatus !== '') {
    $where[] = "t.payment_status = ?";
    $params[] = $paymentStatus;
}

if ($startDate !== '') {
    $where[] = "b.puja_date >= ?";
    $params[] = $startDate;
}

if ($endDate !== '') {
    $where[] = "b.puja_date <= ?";
    $params[] = $endDate;
}

$whereClause = implode(" AND ", $where);

try {
    // 1. Calculate general stats counters for PAID bookings (Overall metrics)
    $statsStmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN b.status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN b.status = 'Completed' THEN 1 ELSE 0 END) as completed
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE t.payment_status = 'paid'
    ");
    $overallStats = $statsStmt->fetch();
    
    // 2. Count total rows in active filtered set
    $countSql = "
        SELECT COUNT(*) 
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE {$whereClause}
    ";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalRows = (int)$countStmt->fetchColumn();

    // 3. Pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $perPage = 25;
    $totalPages = max(1, ceil($totalRows / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    // 4. Fetch the records
    $sql = "
        SELECT b.*, t.donor_name, t.donor_email, t.donor_phone, t.amount, t.payment_status, t.razorpay_payment_id
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE {$whereClause}
        ORDER BY b.puja_date ASC, b.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($sql);
    
    // Bind parameters
    $paramIdx = 1;
    foreach ($params as $pVal) {
        $stmt->bindValue($paramIdx++, $pVal);
    }
    $stmt->bindValue($paramIdx++, $perPage, PDO::PARAM_INT);
    $stmt->bindValue($paramIdx++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $bookings = $stmt->fetchAll();

} catch (PDOException $e) {
    $bookings = [];
    $totalRows = 0;
    $totalPages = 1;
    $overallStats = ['total' => 0, 'pending' => 0, 'completed' => 0];
    $error = 'Query execution failed. Please try again.';
}

// Build URL query string for paging links
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Puja & Yagya Bookings</h1>
    <p>View upcoming schedule, print gotra/nakshatra sheets, and check off completed sevas.</p>
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

<!-- Overall Paid Booking Stats -->
<div class="admin-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: var(--space-xl);">
  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Total Paid Bookings</h3>
      <div class="admin-stat-value"><?php echo (int)($overallStats['total'] ?? 0); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: var(--cream); color: var(--primary);">
      <i class="fas fa-scroll"></i>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Pending Puja/Yagyas</h3>
      <div class="admin-stat-value" style="color:var(--primary-dark);"><?php echo (int)($overallStats['pending'] ?? 0); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: rgba(200, 107, 31, 0.15); color: var(--primary-dark);">
      <i class="fas fa-hourglass-half"></i>
    </div>
  </div>

  <div class="admin-stat-card">
    <div class="admin-stat-info">
      <h3>Completed Sevas</h3>
      <div class="admin-stat-value" style="color:green;"><?php echo (int)($overallStats['completed'] ?? 0); ?></div>
    </div>
    <div class="admin-stat-icon" style="background-color: #d4edda; color: green;">
      <i class="fas fa-check-double"></i>
    </div>
  </div>
</div>

<!-- Filters Panel -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filter Bookings</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/bookings" method="GET" style="display:flex; flex-direction:column; gap:var(--space-md);">
      
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: var(--space-md);">
        
        <div class="form-group" style="margin-bottom:0;">
          <label for="search">Text Search</label>
          <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Gotra, Rashi, Phone...">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="booking_type">Booking Type</label>
          <select id="booking_type" name="booking_type" class="form-control">
            <option value="">-- All Types --</option>
            <option value="puja" <?php echo $bookingType === 'puja' ? 'selected' : ''; ?>>Puja Booking</option>
            <option value="yagya" <?php echo $bookingType === 'yagya' ? 'selected' : ''; ?>>Yagya Booking</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="perf_status">Performance Status</label>
          <select id="perf_status" name="perf_status" class="form-control">
            <option value="">-- All Actions --</option>
            <option value="Pending" <?php echo $perfStatus === 'Pending' ? 'selected' : ''; ?>>Pending (To Be Performed)</option>
            <option value="Completed" <?php echo $perfStatus === 'Completed' ? 'selected' : ''; ?>>Completed (Performed)</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="payment_status">Payment Status</label>
          <select id="payment_status" name="payment_status" class="form-control">
            <option value="">-- All Payments --</option>
            <option value="paid" <?php echo $paymentStatus === 'paid' ? 'selected' : ''; ?>>Paid (Successful)</option>
            <option value="attempted" <?php echo $paymentStatus === 'attempted' ? 'selected' : ''; ?>>Attempted</option>
            <option value="created" <?php echo $paymentStatus === 'created' ? 'selected' : ''; ?>>Created</option>
            <option value="failed" <?php echo $paymentStatus === 'failed' ? 'selected' : ''; ?>>Failed</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="start_date">Start Booking Date</label>
          <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="end_date">End Booking Date</label>
          <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
        </div>

      </div>

      <div style="display:flex; justify-content: flex-end; gap: 8px;">
        <a href="admin/bookings" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px; text-align:center;">Clear Filters</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Apply Filters</button>
      </div>

    </form>
  </div>
</div>

<!-- Bookings List Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2>Bookings List (Showing <?php echo count($bookings); ?> of <?php echo $totalRows; ?> filtered items)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width:1100px;">
        <thead>
          <tr>
            <th style="width:100px;">Puja Date</th>
            <th>Type / Deity</th>
            <th>Yajaman / Devotee Details</th>
            <th>Occasion</th>
            <th>Offerings / Instructions</th>
            <th>Payment Details</th>
            <th style="width:150px; text-align:center;">Action (Pujari)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No bookings found matching selected filters.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($bookings as $b): 
              $isYagya = (strpos(strtolower($b['puja_type']), 'yagya') !== false);
              $typeBadge = $isYagya ? '<span class="badge" style="background-color:#fff0f0; color:#c92a2a; border: 1px solid #ffc9c9; margin-bottom:4px;">Yagya</span>' : '<span class="badge" style="background-color:#f0f7ff; color:#0b5ed7; border: 1px solid #cff4fc; margin-bottom:4px;">Puja</span>';
              
              $paymentBadge = 'badge-secondary';
              if ($b['payment_status'] === 'paid') $paymentBadge = 'badge-success';
              elseif ($b['payment_status'] === 'failed') $paymentBadge = 'badge-danger';
              elseif ($b['payment_status'] === 'attempted') $paymentBadge = 'badge-warning';
              
              $perfBadge = ($b['status'] === 'Completed') ? 'badge-success' : 'badge-warning';
            ?>
              <tr>
                <td style="font-weight:600; color:var(--dark); white-space:nowrap; vertical-align:top; font-size:13px;">
                  <i class="far fa-calendar-alt" style="margin-right:4px; color:var(--primary);"></i>
                  <?php echo date('M d, Y', strtotime($b['puja_date'])); ?>
                </td>
                <td style="vertical-align:top;">
                  <?php echo $typeBadge; ?>
                  <div style="font-weight:600; color:var(--dark); line-height:1.3;"><?php echo htmlspecialchars($b['puja_type']); ?></div>
                </td>
                <td style="vertical-align:top; line-height:1.5;">
                  <strong style="color:var(--primary-dark); font-size:14px;"><?php echo htmlspecialchars($b['person_name']); ?></strong>
                  <div style="font-size:11px; margin-top:2px;">
                    <strong>Gotra:</strong> <?php echo htmlspecialchars($b['gotra'] ?: 'N/A'); ?> | 
                    <strong>Rashi:</strong> <?php echo htmlspecialchars($b['rashi'] ?: 'N/A'); ?>
                  </div>
                  <div style="font-size:11px;">
                    <strong>Nakshatra:</strong> <?php echo htmlspecialchars($b['nakshatra'] ?: 'N/A'); ?>
                  </div>
                </td>
                <td style="vertical-align:top; font-size:13px;"><?php echo htmlspecialchars($b['occasion'] ?: '-'); ?></td>
                <td style="vertical-align:top; font-size:12px; max-width: 250px; line-height: 1.4;">
                  <?php if (!empty($b['special_instructions'])): ?>
                    <span style="display:block; background:var(--light); padding:6px; border-left:3px solid var(--accent); border-radius:4px;"><?php echo nl2br(htmlspecialchars($b['special_instructions'])); ?></span>
                  <?php else: ?>
                    <span style="color:var(--text-light); font-style:italic;">None</span>
                  <?php endif; ?>
                </td>
                <td style="vertical-align:top; line-height:1.4;">
                  <strong style="color:var(--maroon);">₹<?php echo number_format($b['amount'], 2); ?></strong>
                  <div style="font-size:10px; color:var(--text-light); margin-top:2px;">
                    Donor: <?php echo htmlspecialchars($b['donor_name']); ?> (<?php echo htmlspecialchars($b['donor_phone']); ?>)
                  </div>
                  <div style="margin-top:4px;">
                    <span class="badge <?php echo $paymentBadge; ?>"><?php echo $b['payment_status']; ?></span>
                  </div>
                </td>
                <td style="text-align:center; vertical-align:middle;">
                  <?php if (hasPermission('bookings.edit')): ?>
                    <a href="admin/bookings?toggle_status_id=<?php echo $b['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>&<?php echo $queryString; ?>" 
                       class="badge <?php echo $perfBadge; ?>" 
                       style="text-decoration:none; cursor:pointer; font-size:11px; padding:6px 12px; display:inline-flex; align-items:center; gap:4px; box-shadow:var(--shadow-sm); transition:all var(--transition-fast);"
                       onmouseover="this.style.transform='scale(1.05)';"
                       onmouseout="this.style.transform='scale(1)';"
                       title="Click to toggle performance status"
                    >
                      <?php if ($b['status'] === 'Completed'): ?>
                        <i class="fas fa-check-circle"></i> Completed
                      <?php else: ?>
                        <i class="fas fa-hourglass-half"></i> Pending
                      <?php endif; ?>
                    </a>
                  <?php else: ?>
                    <span class="badge <?php echo $perfBadge; ?>" style="font-size:11px; padding:6px 12px; display:inline-flex; align-items:center; gap:4px;">
                      <?php if ($b['status'] === 'Completed'): ?>
                        <i class="fas fa-check-circle"></i> Completed
                      <?php else: ?>
                        <i class="fas fa-hourglass-half"></i> Pending
                      <?php endif; ?>
                    </span>
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

<!-- Pagination Footer -->
<?php if ($totalPages > 1): ?>
  <div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:var(--space-xl); margin-bottom:var(--space-2xl);">
    
    <?php if ($page > 1): ?>
      <a href="admin/bookings?page=<?php echo ($page - 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" style="padding:6px 12px; background:var(--white); color:var(--text-dark); border:1px solid var(--border); border-radius:var(--radius-md); font-size:12px; text-decoration:none; font-weight:600; transition:all var(--transition-fast);"
         onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';"
         onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-dark)';"
      >
        <i class="fas fa-chevron-left" style="font-size:10px; margin-right:4px;"></i> Prev
      </a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): 
      $isActive = ($i === $page);
      $bg = $isActive ? 'var(--primary)' : 'var(--white)';
      $color = $isActive ? 'var(--white)' : 'var(--text-dark)';
      $borderColor = $isActive ? 'var(--primary)' : 'var(--border)';
    ?>
      <a href="admin/bookings?page=<?php echo $i; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" style="padding:6px 12px; background:<?php echo $bg; ?>; color:<?php echo $color; ?>; border:1px solid <?php echo $borderColor; ?>; border-radius:var(--radius-md); font-size:12px; text-decoration:none; font-weight:600; transition:all var(--transition-fast);"
         onmouseover="<?php echo $isActive ? '' : 'this.style.borderColor=\\\"var(--primary)\\\"; this.style.color=\\\"var(--primary)\\\"'; ?>"
         onmouseout="<?php echo $isActive ? '' : 'this.style.borderColor=\\\"var(--border)\\\"; this.style.color=\\\"var(--text-dark)\\\"'; ?>"
      >
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <a href="admin/bookings?page=<?php echo ($page + 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" style="padding:6px 12px; background:var(--white); color:var(--text-dark); border:1px solid var(--border); border-radius:var(--radius-md); font-size:12px; text-decoration:none; font-weight:600; transition:all var(--transition-fast);"
         onmouseover="this.style.borderColor='var(--primary)'; this.style.color='var(--primary)';"
         onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-dark)';"
      >
        Next <i class="fas fa-chevron-right" style="font-size:10px; margin-left:4px;"></i>
      </a>
    <?php endif; ?>

  </div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
