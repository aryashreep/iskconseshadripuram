<?php
/**
 * Pujari Sevalist — Unified Seva Queue for Pujari Role
 * 
 * Shows all paid donations and booking records in one view:
 * 1. Seva Donations (all paid donations)
 * 2. Puja & Yagya Queue (paid bookings with ritual details)
 * 
 * Includes WhatsApp phone links and status toggles for bookings.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('pujari_sevalist.view');

$pageTitle = 'Pujari Sevalist';
$activePage = 'pujari-sevalist';
include 'partials/header.php';

$db = getDB();
$loadError = '';

/**
 * Build a redirect URL preserving active filters but excluding toggle/csrf params.
 */
function buildToggleRedirectUrl(): string
{
    $preserveParams = [];
    foreach (['search', 'section', 'start_date', 'end_date'] as $key) {
        if (!empty($_GET[$key])) {
            $preserveParams[$key] = $_GET[$key];
        }
    }
    $base = 'admin/pujari-sevalist';
    if (!empty($preserveParams)) {
        $base .= '?' . http_build_query($preserveParams);
    }
    return $base;
}

// Handle toggle status action for puja/yagya bookings — PRG pattern
if (isset($_GET['toggle_status_id']) && hasPermission('pujari_sevalist.edit')) {
    $redirectUrl = buildToggleRedirectUrl();
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])) {
        $_SESSION['flash_error'] = 'CSRF validation failed. Unauthorized request.';
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
                $_SESSION['flash_success'] = 'Performance status updated to ' . $newStatus . '.';
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Failed to update status.';
        }
    }
    header('Location: ' . BASE_URL . $redirectUrl);
    exit;
}

// ============================================================
// WHATSAPP EXPORT DATA: Two datasets (today-only + all pending)
// ============================================================
// ── Today-only dataset (for "Copy Today's List" button) ──
$waTodaysPuja = [];
$waTodaysDonations = [];
try {
    $stmt = $db->prepare("
        SELECT b.*, t.donor_name, t.donor_phone
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE t.payment_status = 'paid'
          AND b.status = 'Pending'
          AND b.puja_date = CURDATE()
        ORDER BY b.puja_type ASC
    ");
    $stmt->execute();
    $waTodaysPuja = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT t.id, t.created_at, t.donor_name, t.donor_phone, t.amount, t.notes,
               COALESCE(ms.name, s.name, c.title) as seva_name,
               COALESCE(c.title, 'General') as cause_title
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
        LEFT JOIN donation_cause_sevas s ON t.seva_id = s.id
        WHERE t.payment_status = 'paid'
          AND DATE(t.created_at) = CURDATE()
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $waTodaysDonations = $stmt->fetchAll();
    foreach ($waTodaysDonations as &$d) {
        $d['purpose'] = extractPurposeFromNotes($d['notes'] ?? '');
    }
    unset($d);
} catch (PDOException $e) {
    $waTodaysPuja = [];
    $waTodaysDonations = [];
}

// ── All-pending dataset (for "Copy All Pending" button) ──
$waPending = [];
$waRecentDonations = [];
try {
    $stmt = $db->prepare("
        SELECT b.*, t.donor_name, t.donor_phone
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE t.payment_status = 'paid'
          AND b.status = 'Pending'
        ORDER BY b.puja_date ASC, b.puja_type ASC
    ");
    $stmt->execute();
    $waPending = $stmt->fetchAll();

    $stmt = $db->prepare("
        SELECT t.id, t.created_at, t.donor_name, t.donor_phone, t.amount, t.notes,
               COALESCE(ms.name, s.name, c.title) as seva_name,
               COALESCE(c.title, 'General') as cause_title
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
        LEFT JOIN donation_cause_sevas s ON t.seva_id = s.id
        WHERE t.payment_status = 'paid'
          AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    $waRecentDonations = $stmt->fetchAll();
    foreach ($waRecentDonations as &$d) {
        $d['purpose'] = extractPurposeFromNotes($d['notes'] ?? '');
    }
    unset($d);
} catch (PDOException $e) {
    $waPending = [];
    $waRecentDonations = [];
}

// Read filters
$search = trim($_GET['search'] ?? '');
$section = trim($_GET['section'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

/**
 * Normalize phone number for WhatsApp link
 */
/**
 * Extract the actual purpose/intention from the notes/metadata string.
 * Notes often contain form metadata like:
 *   "Cause: daily-seva, Form: tiers | Selected: 1x Archana Seva... | Purpose: sainath c b"
 * This extracts just the meaningful part:
 *   - "Purpose:" text if present
 *   - "Selected:" items if no Purpose (e.g., "General Temple Donation")
 *   - Truncated original as fallback
 */
function extractPurposeFromNotes(?string $notes): string
{
    if (empty($notes)) return '';

    // 1) Look for "Purpose:" (case-insensitive) — prefer this
    //    Use 's' (PCRE_DOTALL) so '.' matches newlines in multi-line purpose text
    if (preg_match('/Purpose:\s*(.+?)\s*$/si', $notes, $m)) {
        $purpose = trim($m[1]);
        if ($purpose !== '') return $purpose;
    }

    // 2) No Purpose found — try to extract from "Selected:" (last occurrence)
    //    e.g. "Selected: 1x General Temple Donation" → "General Temple Donation"
    $parts = preg_split('/\s*\|\s*/', $notes);
    foreach (array_reverse($parts) as $part) {
        $part = trim($part);
        // Check for Selected: field — show the item names (strip quantity prefixes)
        if (preg_match('/^Selected:\s*(.*)$/i', $part, $m)) {
            $selected = trim($m[1]);
            // Clean up: "1x Archana Seva, 1x Tulsi Ashtothara..." → show as-is (informative)
            // But if it's just one item like "1x General Temple Donation", clean it
            if ($selected !== '') return $selected;
        }
    }

    // 3) Fallback: return truncated original notes (first 120 chars)
    $maxLen = 120;
    $truncated = mb_substr($notes, 0, $maxLen);
    if (mb_strlen($notes) > $maxLen) $truncated .= '…';
    return $truncated;
}

function normalizeWhatsAppPhone(?string $phone): ?string
{
    if (empty($phone)) return null;
    $digits = preg_replace('/\D+/', '', $phone);
    if ($digits === '') return null;

    // 10-digit Indian mobile → add 91 prefix
    if (strlen($digits) === 10) {
        $digits = '91' . $digits;
    }
    // 11-digit starting with 0 → replace 0 with 91
    if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
        $digits = '91' . substr($digits, 1);
    }
    // Must start with 91 and be at least 12 digits
    if (strlen($digits) < 12 && !str_starts_with($digits, '91')) {
        return null;
    }

    return 'https://wa.me/' . $digits;
}

/**
 * Render WhatsApp link from phone number
 */
function renderWhatsAppLink(?string $phone): string
{
    if (empty($phone)) {
        return '<span style="color:var(--text-light); font-style:italic; font-size:11px;"><i class="fas fa-times-circle"></i> No phone</span>';
    }
    $waLink = normalizeWhatsAppPhone($phone);
    if (!$waLink) {
        return '<span style="font-size:12px;">' . htmlspecialchars($phone) . '</span>';
    }
    return '<a href="' . $waLink . '" target="_blank" rel="noopener" style="color:#25D366; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:4px; font-size:12px;">'
         . '<i class="fab fa-whatsapp" style="font-size:14px;"></i> ' . htmlspecialchars($phone) . '</a>';
}

try {
    // ============================================================
    // STATS: Aggregated counts
    // ============================================================
    // Seva donations count (all paid donations)
    $stmt = $db->query("
        SELECT COUNT(*) as cnt, COALESCE(SUM(t.amount), 0) as total
        FROM donation_transactions t
        WHERE t.payment_status = 'paid'
    ");
    $donationStats = $stmt->fetch();
    $sevaDonationCount = (int) $donationStats['cnt'];
    $sevaDonationTotal = (float) $donationStats['total'];

    // Pending bookings count
    $stmt = $db->query("
        SELECT COUNT(*) FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE t.payment_status = 'paid' AND b.status = 'Pending'
    ");
    $pendingBookings = (int) $stmt->fetchColumn();

    // Completed bookings count
    $stmt = $db->query("
        SELECT COUNT(*) FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE t.payment_status = 'paid' AND b.status = 'Completed'
    ");
    $completedBookings = (int) $stmt->fetchColumn();

    // Upcoming (next 7 days) count
    $stmt = $db->query("
        SELECT COUNT(*) FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE t.payment_status = 'paid'
          AND b.puja_date >= CURDATE() AND b.puja_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ");
    $upcomingBookings = (int) $stmt->fetchColumn();

    // ============================================================
    // SECTION A: Seva Donations
    // ============================================================
    $sevaWhere = ["t.payment_status = 'paid'"];
    $sevaParams = [];

    if ($search !== '' && ($section === '' || $section === 'donations')) {
        $sevaWhere[] = "(t.donor_name LIKE ? OR t.donor_phone LIKE ? OR t.donor_email LIKE ? OR t.notes LIKE ?)";
        $sevaParams = array_merge($sevaParams, ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
    }
    if ($startDate !== '') {
        $sevaWhere[] = "t.created_at >= ?";
        $sevaParams[] = $startDate . ' 00:00:00';
    }
    if ($endDate !== '') {
        $sevaWhere[] = "t.created_at <= ?";
        $sevaParams[] = $endDate . ' 23:59:59';
    }

    $sevaSql = "
        SELECT t.id, t.created_at, t.donor_name, t.donor_phone, t.donor_email,
               t.amount, t.notes,
               COALESCE(c.title, 'General') as cause_title,
               c.category as cause_category,
               COALESCE(ms.name, s.name, c.title) as seva_name
        FROM donation_transactions t
        LEFT JOIN donation_causes c ON t.cause_id = c.id
        LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
        LEFT JOIN donation_cause_sevas s ON t.seva_id = s.id
        WHERE " . implode(" AND ", $sevaWhere) . "
        ORDER BY t.created_at DESC
        LIMIT 100
    ";
    $sevaStmt = $db->prepare($sevaSql);
    $sevaStmt->execute($sevaParams);
    $sevaDonations = $sevaStmt->fetchAll();

    // ============================================================
    // SECTION B: Puja & Yagya Bookings
    // ============================================================
    $bookWhere = ["t.payment_status = 'paid'"];
    $bookParams = [];

    if ($search !== '' && ($section === '' || $section === 'bookings')) {
        $bookWhere[] = "(b.person_name LIKE ? OR b.gotra LIKE ? OR b.rashi LIKE ? OR b.nakshatra LIKE ? OR b.occasion LIKE ? OR t.donor_name LIKE ? OR t.donor_phone LIKE ?)";
        $bookParams = array_merge($bookParams, ['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
    }
    if ($startDate !== '') {
        $bookWhere[] = "b.puja_date >= ?";
        $bookParams[] = $startDate;
    }
    if ($endDate !== '') {
        $bookWhere[] = "b.puja_date <= ?";
        $bookParams[] = $endDate;
    }

    $bookSql = "
        SELECT b.*, t.donor_name, t.donor_phone, t.donor_email, t.amount, t.payment_status
        FROM booking_pujas b
        JOIN donation_transactions t ON b.transaction_id = t.id
        WHERE " . implode(" AND ", $bookWhere) . "
        ORDER BY
            CASE WHEN b.status = 'Pending' THEN 0 ELSE 1 END,
            b.puja_date ASC,
            b.created_at DESC
        LIMIT 100
    ";
    $bookStmt = $db->prepare($bookSql);
    $bookStmt->execute($bookParams);
    $bookings = $bookStmt->fetchAll();

} catch (PDOException $e) {
    $loadError = 'Failed to load data. Please try again later.';
    error_log('Pujari Sevalist DB error: ' . $e->getMessage());
    $sevaDonations = [];
    $bookings = [];
    $sevaDonationCount = $sevaDonationTotal = $pendingBookings = $completedBookings = $upcomingBookings = 0;
    $waTodaysPuja = [];
    $waTodaysDonations = [];
    $waPending = [];
    $waRecentDonations = [];
}
?>
<style>
  .ps-table { width: 100%; border-collapse: collapse; font-size: 12px; }
  .ps-table thead th {
    background: var(--cream); padding: 9px 12px; text-align: left;
    font-size: 10px; text-transform: uppercase; font-weight: 700;
    color: var(--text-light); letter-spacing: 0.3px;
    border-bottom: 1px solid var(--border); white-space: nowrap;
  }
  .ps-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f0ebe4; vertical-align: middle; }
  .ps-table tbody tr:hover { background: #fffde6; }
  .ps-table tbody tr:last-child td { border-bottom: none; }
  .ps-section { border-top: 4px solid var(--primary); }
  .ps-section.booking-section { border-top-color: var(--accent); }
  .ps-stat-box {
    background: var(--white); border: 1px solid var(--border);
    border-radius: var(--radius-md); padding: var(--space-md) var(--space-lg);
    display: flex; align-items: center; gap: var(--space-md);
    box-shadow: var(--shadow-sm); border-left: 4px solid var(--primary);
  }
  .ps-toggle {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px; border-radius: 20px; font-size: 10px; font-weight: 700;
    text-decoration: none; border: 1px solid; transition: all 0.15s;
  }
  .ps-toggle.completed { background: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; }
  .ps-toggle.pending { background: #fff8e1; color: #b78103; border-color: #ffd54f; }
  .ps-toggle:hover { transform: scale(1.05); filter: brightness(0.95); }
  .ps-filter-bar {
    display: flex; flex-wrap: wrap; gap: var(--space-md); align-items: flex-end;
  }
</style>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1><i class="fas fa-bell-concierge" style="color:var(--maroon);"></i> Pujari Sevalist</h1>
    <p>All paid seva donations, puja &amp; yagya bookings</p>
  </div>
  <div class="admin-page-actions" style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
    <div style="text-align:right; font-size:10px; color:var(--text-light); line-height:1.4;">
      <span style="display:block;"><i class="fas fa-info-circle"></i> <strong>Today's List</strong> (items due today) or <strong>All Pending</strong> (all pending + 7 days donations) — WhatsApp-friendly format.</span>
      <span>Share with the pujari team for seva coordination.</span>
    </div>
    <div style="display:flex; gap:6px;">
      <a href="admin/export-pujari-sevalist<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>"
         style="background:var(--cream); color:var(--text); border:1px solid var(--border); padding:8px 18px; border-radius:var(--radius-md); font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none; transition:all 0.15s;"
         title="Download all visible data as CSV"
         onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Downloading…'; this.style.pointerEvents='none';">
        <i class="fas fa-file-csv"></i> Export CSV
      </a>
      <button type="button" id="waTodaysBtn" onclick="copyTodaysList()"
        style="background:#25D366; color:white; border:none; padding:8px 18px; border-radius:var(--radius-md); font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.15s;"
        title="Today's pending puja/yagya + today's donations only">
        <i class="fab fa-whatsapp" style="font-size:14px;"></i> Copy Today's List
      </button>
      <button type="button" id="waAllBtn" onclick="copyAllPending()"
        style="background:white; color:#1ebe5a; border:1.5px solid #25D366; padding:8px 18px; border-radius:var(--radius-md); font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.15s;"
        title="All pending puja/yagya (any date) + last 7 days donations">
        <i class="fab fa-whatsapp" style="font-size:14px;"></i> Copy All Pending
      </button>
    </div>
  </div>
</div>

<!-- Hidden toast for copy confirmation -->
<div id="waToast" style="position:fixed; bottom:24px; right:24px; background:#2e7d32; color:white; padding:12px 24px; border-radius:var(--radius-md); font-size:13px; font-weight:600; box-shadow:0 4px 16px rgba(0,0,0,0.2); z-index:9999; display:none; align-items:center; gap:8px; transition:opacity 0.3s;">
  <i class="fas fa-check-circle"></i> <span id="waToastMsg">Copied to clipboard!</span>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($_SESSION['flash_success']); ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($_SESSION['flash_error']); ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<?php if (!empty($loadError)): ?>
  <div class="alert alert-danger"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> <?php echo htmlspecialchars($loadError); ?></div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- STATS ROW -->
<!-- ============================================================ -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px,1fr)); gap:var(--space-md); margin-bottom:var(--space-lg);">
  <div class="ps-stat-box" style="border-left-color:var(--primary);">
    <div style="width:38px; height:38px; border-radius:var(--radius-md); background:rgba(200,168,124,0.12); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;"><i class="fas fa-ribbon"></i></div>
    <div>
      <div style="font-size:10px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Seva Donations</div>
      <div style="font-size:20px; font-weight:700;"><?php echo $sevaDonationCount; ?></div>
      <div style="font-size:10px; color:var(--text-light);">₹<?php echo number_format($sevaDonationTotal, 0); ?> total</div>
    </div>
  </div>
  <div class="ps-stat-box" style="border-left-color:var(--accent);">
    <div style="width:38px; height:38px; border-radius:var(--radius-md); background:rgba(232,148,74,0.12); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;"><i class="fas fa-hourglass-half"></i></div>
    <div>
      <div style="font-size:10px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Pending Puja/Yagya</div>
      <div style="font-size:20px; font-weight:700; color:var(--accent);"><?php echo $pendingBookings; ?></div>
      <div style="font-size:10px; color:var(--text-light);">Awaiting performance</div>
    </div>
  </div>
  <div class="ps-stat-box" style="border-left-color:#2e7d32;">
    <div style="width:38px; height:38px; border-radius:var(--radius-md); background:rgba(46,125,50,0.1); color:#2e7d32; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;"><i class="fas fa-check-double"></i></div>
    <div>
      <div style="font-size:10px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Completed Sevas</div>
      <div style="font-size:20px; font-weight:700; color:#2e7d32;"><?php echo $completedBookings; ?></div>
      <div style="font-size:10px; color:var(--text-light);">Marked done</div>
    </div>
  </div>
  <div class="ps-stat-box" style="border-left-color:#1565c0;">
    <div style="width:38px; height:38px; border-radius:var(--radius-md); background:rgba(21,101,192,0.1); color:#1565c0; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;"><i class="fas fa-calendar-day"></i></div>
    <div>
      <div style="font-size:10px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Upcoming (7 Days)</div>
      <div style="font-size:20px; font-weight:700; color:#1565c0;"><?php echo $upcomingBookings; ?></div>
      <div style="font-size:10px; color:var(--text-light);">Scheduled soon</div>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- FILTERS -->
<!-- ============================================================ -->
<div class="admin-card" style="margin-bottom:var(--space-lg);">
  <div class="admin-card-header" style="background:var(--cream); padding:var(--space-sm) var(--space-lg);">
    <h2 style="font-size:13px;"><i class="fas fa-filter"></i> Filters</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-md) var(--space-lg);">
    <form action="admin/pujari-sevalist" method="GET" style="display:flex; flex-direction:column; gap:var(--space-md);">
      <div class="ps-filter-bar">
        <div style="display:flex; flex-direction:column; gap:4px;">
          <label style="font-size:10px; font-weight:600; color:var(--text-light); text-transform:uppercase;">Search</label>
          <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, phone, gotra..." style="min-width:180px;">
        </div>
        <div style="display:flex; flex-direction:column; gap:4px;">
          <label style="font-size:10px; font-weight:600; color:var(--text-light); text-transform:uppercase;">Section</label>
          <select name="section" class="form-control">
            <option value="">All Sections</option>
            <option value="donations" <?php echo $section === 'donations' ? 'selected' : ''; ?>>Seva Donations</option>
            <option value="bookings" <?php echo $section === 'bookings' ? 'selected' : ''; ?>>Puja / Yagya</option>
          </select>
        </div>
        <div style="display:flex; flex-direction:column; gap:4px;">
          <label style="font-size:10px; font-weight:600; color:var(--text-light); text-transform:uppercase;">From</label>
          <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
        </div>
        <div style="display:flex; flex-direction:column; gap:4px;">
          <label style="font-size:10px; font-weight:600; color:var(--text-light); text-transform:uppercase;">To</label>
          <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
        </div>
        <div style="display:flex; gap:6px; align-self:flex-end;">
          <button type="submit" class="btn btn-primary" style="background:var(--primary); color:white; border:none; padding:7px 18px; border-radius:var(--radius-sm); font-size:12px; font-weight:600; cursor:pointer;">Apply</button>
          <a href="admin/pujari-sevalist" class="btn btn-outline-dark" style="text-decoration:none; padding:7px 14px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:12px; font-weight:600; color:var(--text);">Clear</a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================ -->
<!-- SECTION A: SEVA DONATIONS -->
<!-- ============================================================ -->
<div class="admin-card ps-section" style="margin-bottom:var(--space-lg);">
  <div class="admin-card-header">
    <h2><i class="fas fa-ribbon" style="color:var(--primary);"></i> Seva Donations <span style="font-weight:400; font-size:12px; color:var(--text-light);">(all paid donations)</span></h2>
    <span style="font-size:11px; color:var(--text-light);"><strong><?php echo count($sevaDonations); ?></strong> of <?php echo $sevaDonationCount; ?> donations shown</span>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div style="overflow-x:auto;">
      <table class="ps-table" style="min-width:1000px;">
        <thead>
          <tr>
            <th>Date</th>
            <th>Cause</th>
            <th>Seva</th>
            <th>Donor</th>
            <th>Phone / WhatsApp</th>
            <th>Amount</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($sevaDonations)): ?>
            <tr><td colspan="7" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No seva donations yet.</td></tr>
          <?php else: ?>
            <?php foreach ($sevaDonations as $d): ?>
              <tr>
                <td style="font-size:11px; color:var(--text-light); white-space:nowrap;"><?php echo date('M d, Y', strtotime($d['created_at'])); ?></td>
                <td>
                  <?php if (!empty($d['cause_category'])): ?>
                    <span class="badge badge-info" style="font-size:10px;"><?php echo htmlspecialchars(ucfirst($d['cause_category'])); ?></span>
                  <?php endif; ?>
                </td>
                <td><strong style="font-size:13px;"><?php echo htmlspecialchars($d['seva_name']); ?></strong></td>
                <td><strong><?php echo htmlspecialchars($d['donor_name']); ?></strong></td>
                <td><?php echo renderWhatsAppLink($d['donor_phone']); ?></td>
                <td style="font-weight:600; color:var(--maroon);">₹<?php echo number_format($d['amount'], 0); ?></td>
                <td style="font-size:11px; max-width:200px; color:var(--text-light);">
                  <?php if (!empty($d['notes'])): ?>
                    <?php $cleanPurpose = extractPurposeFromNotes($d['notes']); ?>
                    <span style="display:block; background:var(--light); padding:3px 6px; border-left:2px solid var(--accent); border-radius:3px; font-size:11px;"
                          title="<?php echo htmlspecialchars($d['notes']); ?>">
                      <?php echo htmlspecialchars($cleanPurpose ?: $d['notes']); ?>
                    </span>
                  <?php else: ?>
                    <span style="font-style:italic;">—</span>
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

<!-- ============================================================ -->
<!-- SECTION B: PUJA & YAGYA QUEUE -->
<!-- ============================================================ -->
<div class="admin-card ps-section booking-section" style="margin-bottom:var(--space-xl);">
  <div class="admin-card-header">
    <h2><i class="fas fa-hands-praying" style="color:var(--accent);"></i> Puja &amp; Yagya Queue <span style="font-weight:400; font-size:12px; color:var(--text-light);">(paid, pending first)</span></h2>
    <div style="display:flex; gap:8px; align-items:center;">
      <span style="font-size:11px; color:var(--text-light);"><strong><?php echo count($bookings); ?></strong> total</span>
      <span class="ps-toggle pending" style="cursor:default;"><i class="fas fa-hourglass-half"></i> Pending</span>
      <span class="ps-toggle completed" style="cursor:default;"><i class="fas fa-check-circle"></i> Completed</span>
    </div>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div style="overflow-x:auto;">
      <table class="ps-table" style="min-width:1200px;">
        <thead>
          <tr>
            <th style="width:88px;">Date</th>
            <th>Type</th>
            <th>Seva</th>
            <th>Beneficiary</th>
            <th>Gotra / Rashi / Nakshatra</th>
            <th>Occasion</th>
            <th>Donor / WhatsApp</th>
            <th>Instructions</th>
            <th style="width:130px; text-align:center;">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?>
            <tr><td colspan="9" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No puja or yagya bookings found.</td></tr>
          <?php else: ?>
            <?php foreach ($bookings as $b):
              $isYagya = (strpos(strtolower($b['puja_type']), 'yagya') !== false);
              $typeBadge = $isYagya
                ? '<span class="badge" style="background:#fff0f0; color:#c92a2a; border:1px solid #ffc9c9; margin-bottom:4px;">Yagya</span>'
                : '<span class="badge" style="background:#f0f7ff; color:#0b5ed7; border:1px solid #cff4fc; margin-bottom:4px;">Puja</span>';
              $isPending = ($b['status'] !== 'Completed');
            ?>
              <tr <?php echo $isPending ? 'style="background:#fffde6;"' : ''; ?>>
                <td style="font-weight:600; white-space:nowrap; font-size:12px;">
                  <i class="far fa-calendar-alt" style="color:<?php echo $isPending ? 'var(--accent)' : '#2e7d32'; ?>; margin-right:3px;"></i>
                  <?php if ($isPending): ?>
                    <span style="color:var(--maroon);"><?php echo date('M d', strtotime($b['puja_date'])); ?></span>
                  <?php else: ?>
                    <span style="color:#2e7d32;"><?php echo date('M d', strtotime($b['puja_date'])); ?></span>
                  <?php endif; ?>
                  <div style="font-size:10px; color:var(--text-light);"><?php echo date('Y', strtotime($b['puja_date'])); ?></div>
                </td>
                <td><?php echo $typeBadge; ?></td>
                <td><strong style="font-size:13px;"><?php echo htmlspecialchars($b['puja_type']); ?></strong></td>
                <td><strong><?php echo htmlspecialchars($b['person_name']); ?></strong></td>
                <td style="font-size:11px;">
                  Gotra: <strong><?php echo htmlspecialchars($b['gotra'] ?: '—'); ?></strong><br>
                  Rāśi: <?php echo htmlspecialchars($b['rashi'] ?: '—'); ?> &middot; Nakṣatra: <?php echo htmlspecialchars($b['nakshatra'] ?: '—'); ?>
                </td>
                <td style="font-size:12px;"><?php echo htmlspecialchars($b['occasion'] ?: '—'); ?></td>
                <td>
                  <strong style="font-size:12px;"><?php echo htmlspecialchars($b['donor_name']); ?></strong><br>
                  <?php echo renderWhatsAppLink($b['donor_phone']); ?>
                </td>
                <td style="font-size:11px; max-width:160px; line-height:1.4;">
                  <?php if (!empty($b['special_instructions'])): ?>
                    <span style="background:var(--light); padding:4px 6px; border-left:2px solid var(--accent); border-radius:3px; display:block;">
                      <?php echo nl2br(htmlspecialchars($b['special_instructions'])); ?>
                    </span>
                  <?php else: ?>
                    <span style="color:var(--text-light); font-style:italic;">—</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;">
                  <?php if (hasPermission('pujari_sevalist.edit')): ?>
                    <a href="admin/pujari-sevalist?toggle_status_id=<?php echo $b['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>"
                       class="ps-toggle <?php echo $isPending ? 'pending' : 'completed'; ?>"
                       title="Click to toggle performance status">
                      <?php if ($isPending): ?>
                        <i class="fas fa-hourglass-half"></i> Pending
                      <?php else: ?>
                        <i class="fas fa-check-circle"></i> Completed
                      <?php endif; ?>
                    </a>
                  <?php else: ?>
                    <span class="ps-toggle <?php echo $isPending ? 'pending' : 'completed'; ?>" style="cursor:default;">
                      <?php if ($isPending): ?>
                        <i class="fas fa-hourglass-half"></i> Pending
                      <?php else: ?>
                        <i class="fas fa-check-circle"></i> Completed
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

<!-- ============================================================ -->
<!-- WHATSAPP EXPORT: Embedded data + copy-to-clipboard JS -->
<!-- ============================================================ -->
<script>
// WhatsApp data from server: two datasets
var waData = {
  date: '<?php echo date('d M Y'); ?>',
  // Today-only
  todaysPuja: <?php echo json_encode($waTodaysPuja); ?>,
  todaysDonations: <?php echo json_encode($waTodaysDonations); ?>,
  // All pending
  pendingBookings: <?php echo json_encode($waPending); ?>,
  recentDonations: <?php echo json_encode($waRecentDonations); ?>,
};

/**
 * Format phone number for WhatsApp text display
 */
function waFormatPhone(phone) {
  if (!phone) return '';
  var d = phone.replace(/\D/g, '');
  if (d.length === 10) return d;
  if (d.length === 11 && d[0] === '0') return d.substring(1);
  if (d.length === 12 && d.substring(0, 2) === '91') return d.substring(2);
  return d;
}

/**
 * Build a message section for puja/yagya bookings
 */
function buildPujaBlock(bookings, lines) {
  if (bookings.length === 0) return;
  lines.push('🔸 *PUJA / YAGYA*');
  lines.push('');
  bookings.forEach(function(b, i) {
    var isYagya = b.puja_type.toLowerCase().indexOf('yagya') !== -1 || b.puja_type.toLowerCase().indexOf('homa') !== -1;
    var icon = isYagya ? '🔥' : '🪔';
    lines.push((i + 1) + ') ' + icon + ' ' + b.puja_type);
    if (b.puja_date && b.puja_date !== '1970-01-01') {
      var parts = b.puja_date.split('-');
      var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      var mon = months[parseInt(parts[1]) - 1] || parts[1];
      lines.push('   📅 ' + mon + ' ' + parseInt(parts[2]));
    }
    lines.push('   👤 ' + (b.person_name || '—'));
    if (b.gotra) lines.push('   🏛 Gotra: ' + b.gotra);
    if (b.occasion) lines.push('   🎯 ' + b.occasion);
    if (b.donor_phone) lines.push('   📞 ' + waFormatPhone(b.donor_phone) + ' (' + (b.donor_name || '') + ')');
    if (b.special_instructions) {
      var instr = b.special_instructions.substring(0, 120);
      if (b.special_instructions.length > 120) instr += '…';
      lines.push('   📝 ' + instr);
    }
    lines.push('');
  });
}

/**
 * Build a message section for donations
 */
function buildDonationBlock(donations, label, lines) {
  if (donations.length === 0) return;
  lines.push('🔸 *' + label + '*');
  lines.push('');
  donations.forEach(function(t, i) {
    lines.push((i + 1) + ') 💰 ₹' + Number(t.amount).toLocaleString('en-IN') + ' — ' + (t.donor_name || '—'));
    lines.push('   ' + (t.seva_name || t.cause_title || ''));
    if (t.donor_phone) lines.push('   📞 ' + waFormatPhone(t.donor_phone));
    if (t.purpose) {
      var note = t.purpose.substring(0, 150);
      if (t.purpose.length > 150) note += '…';
      lines.push('   💬 ' + note);
    }
    lines.push('');
  });
}

/**
 * Build message for TODAY'S LIST (only today's items)
 */
function buildTodaysMessage() {
  var lines = [];
  var d = waData;
  lines.push('📅 *PUJARI SEVALIST — ' + d.date + '*');
  lines.push('');
  buildPujaBlock(d.todaysPuja, lines);
  buildDonationBlock(d.todaysDonations, 'TODAY\'S DONATIONS', lines);
  if (d.todaysPuja.length === 0 && d.todaysDonations.length === 0) {
    lines.push('_No items scheduled for today._');
    lines.push('');
  }
  lines.push('🙏 Hare Krishna');
  return lines.join('\n');
}

/**
 * Build message for ALL PENDING (all pending puja + recent 7 days donations)
 */
function buildAllPendingMessage() {
  var lines = [];
  var d = waData;
  lines.push('📅 *PUJARI SEVALIST — ' + d.date + '*');
  lines.push('');
  buildPujaBlock(d.pendingBookings, lines);
  buildDonationBlock(d.recentDonations, 'RECENT DONATIONS (Last 7 Days)', lines);
  if (d.pendingBookings.length === 0 && d.recentDonations.length === 0) {
    lines.push('_No pending items or recent donations._');
    lines.push('');
  }
  lines.push('🙏 Hare Krishna');
  return lines.join('\n');
}

/**
 * Generic clipboard copy
 */
function copyToClipboard(message, btn) {
  var toast = document.getElementById('waToast');
  var toastMsg = document.getElementById('waToastMsg');

  btn.disabled = true;
  btn.style.opacity = '0.6';
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Copying…';

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(message).then(function() {
      showToast(toast, toastMsg, '📋 Copied! Paste in WhatsApp group 🙏');
      resetButton(btn);
    }).catch(function() {
      fallbackCopy(message, toast, toastMsg, btn);
    });
  } else {
    fallbackCopy(message, toast, toastMsg, btn);
  }
}

function copyTodaysList() {
  copyToClipboard(buildTodaysMessage(), document.getElementById('waTodaysBtn'));
}

function copyAllPending() {
  copyToClipboard(buildAllPendingMessage(), document.getElementById('waAllBtn'));
}

function fallbackCopy(text, toast, toastMsg, btn) {
  var ta = document.createElement('textarea');
  ta.value = text;
  ta.style.position = 'fixed';
  ta.style.left = '-9999px';
  ta.style.top = '-9999px';
  document.body.appendChild(ta);
  ta.select();
  try {
    document.execCommand('copy');
    showToast(toast, toastMsg, '📋 Copied! Paste in WhatsApp group 🙏');
  } catch (e) {
    showToast(toast, toastMsg, '❌ Could not copy. Please select and copy manually.');
  }
  document.body.removeChild(ta);
  resetButton(btn);
}

function showToast(toast, toastMsg, msg) {
  toastMsg.textContent = msg;
  toast.style.display = 'flex';
  toast.style.opacity = '0';
  setTimeout(function() { toast.style.opacity = '1'; }, 10);
  setTimeout(function() {
    toast.style.opacity = '0';
    setTimeout(function() { toast.style.display = 'none'; }, 300);
  }, 3500);
}

function resetButton(btn) {
  btn.disabled = false;
  btn.style.opacity = '1';
  if (btn.id === 'waTodaysBtn') {
    btn.innerHTML = '<i class="fab fa-whatsapp" style="font-size:14px;"></i> Copy Today\'s List';
  } else {
    btn.innerHTML = '<i class="fab fa-whatsapp" style="font-size:14px;"></i> Copy All Pending';
  }
}
</script>

<?php include 'partials/footer.php'; ?>
