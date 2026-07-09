<?php
/**
 * Sudamaseva Module — Receipts List (Admin)
 *
 * Paginated list with date range filters showing 80G receipts.
 * Allows manual generation of pending receipts.
 */
require_once __DIR__ . '/../../../admin/auth-check.php';
requirePermission('sudamaseva.view');

use Isjm\Modules\Sudamaseva\SudamasevaService;
use Isjm\Modules\Sudamaseva\SudamasevaRepository;

$service = new SudamasevaService();
$repo = new SudamasevaRepository();
$error = '';
$success = '';

// Initialize Session CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// POST Handler for Manual Receipt Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_receipt') {
    requirePermission('sudamaseva.edit');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $paymentId = isset($_POST['payment_id']) ? (int) $_POST['payment_id'] : 0;
        
        $db = getDB();
        $stmtPayment = $db->prepare("SELECT * FROM sudamaseva_payments WHERE id = ? LIMIT 1");
        $stmtPayment->execute([$paymentId]);
        $payment = $stmtPayment->fetch();
        
        if (!$payment) {
            $error = 'Payment record not found.';
        } elseif ($payment['payment_status'] !== 'paid') {
            $error = 'Receipt can only be generated for paid transactions.';
        } else {
            // Check if receipt already exists
            $stmtReceiptCheck = $db->prepare("SELECT COUNT(*) FROM sudamaseva_receipts WHERE payment_id = ?");
            $stmtReceiptCheck->execute([$paymentId]);
            $exists = (int) $stmtReceiptCheck->fetchColumn();
            
            if ($exists > 0) {
                $error = 'A receipt has already been generated for this payment.';
            } else {
                try {
                    $db->beginTransaction();
                    
                    // Generate receipt number
                    $receiptNo = $payment['receipt_number'] ?: $service->generateReceiptNo();
                    
                    // Fetch donor
                    $donor = $repo->getDonorById((int) $payment['donor_id']);
                    if (!$donor) {
                        throw new RuntimeException('Donor record not found for payment #' . $paymentId);
                    }
                    
                    $is80g = $service->isEligibleFor80G((int) $payment['amount'], $donor) ? 1 : 0;
                    
                    // Create receipt record
                    $repo->createReceipt([
                        'payment_id' => $paymentId,
                        'receipt_no' => $receiptNo,
                        'receipt_date' => date('Y-m-d H:i:s'),
                        'is_80g_eligible' => $is80g,
                        'receipt_data' => [
                            'donor_name' => $donor['donor_name'],
                            'donor_pan' => $donor['pan'],
                            'amount' => (int) $payment['amount'],
                            'payment_date' => $payment['payment_date'],
                            'payment_method' => 'online',
                            'reference_no' => $payment['razorpay_payment_id'],
                            'fy' => $service->getFinancialYearLabel(),
                        ],
                    ]);
                    
                    // Update payment receipt number if empty
                    if (empty($payment['receipt_number'])) {
                        $stmtUpdatePayment = $db->prepare("UPDATE sudamaseva_payments SET receipt_number = ? WHERE id = ?");
                        $stmtUpdatePayment->execute([$receiptNo, $paymentId]);
                    }
                    
                    $db->commit();
                    $success = 'Receipt ' . $receiptNo . ' generated successfully!';
                } catch (Exception $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    $error = 'Failed to generate receipt: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Sudamaseva Receipts';
$activePage = 'sudamaseva-receipts';
include 'partials/header.php';

$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;

try {
    $result = $service->getReceipts($from ?: null, $to ?: null, $page, $perPage);
    $receipts = $result['receipts'];
    $total = $result['total'];
    $pages = $result['pages'];
    $receiptStats = $service->getReceiptStats();
} catch (Exception $e) {
    $error = 'Failed to load receipts: ' . $e->getMessage();
    $receipts = [];
    $total = 0;
    $pages = 1;
    $receiptStats = [];
}

// Also show payments needing receipts
try {
    $pendingReceipts = $service->getPaymentsWithoutReceipts();
} catch (Exception $e) {
    $pendingReceipts = [];
}

$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Receipts</h1>
    <p>80G donation receipts generated for Sudamaseva subscription payments.</p>
  </div>
  <div class="admin-page-actions">
    <span style="font-size:12px; color:var(--text-light);">
      <i class="fas fa-database"></i> <?php echo $total; ?> total receipts
    </span>
  </div>
</div>

<?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($success); ?>
  </div>
<?php endif; ?>

<!-- Summary Cards -->
<div style="display:flex; gap: var(--space-md); margin-bottom: var(--space-lg); flex-wrap:wrap;">
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-receipt" style="font-size:24px; color:var(--primary);"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Total Receipts</div>
      <strong style="font-size:18px;"><?php echo $receiptStats['total'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-check-circle" style="font-size:24px; color:#2e7d32;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">80G Eligible</div>
      <strong style="font-size:18px; color:#2e7d32;"><?php echo $receiptStats['is_80g_eligible'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:160px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-calendar-day" style="font-size:24px; color:#0b5ed7;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Generated Today</div>
      <strong style="font-size:18px; color:#0b5ed7;"><?php echo $receiptStats['generated_today'] ?? 0; ?></strong>
    </div>
  </div>
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-md); border-radius:var(--radius-md); display:flex; align-items:center; gap:var(--space-md); min-width:180px; box-shadow:var(--shadow-sm);">
    <i class="fas fa-exclamation-triangle" style="font-size:24px; color:<?php echo count($pendingReceipts) > 0 ? 'var(--primary-dark)' : 'green'; ?>;"></i>
    <div>
      <div style="font-size:11px; text-transform:uppercase; font-weight:600; color:var(--text-light);">Pending Receipts</div>
      <strong style="font-size:18px; color:<?php echo count($pendingReceipts) > 0 ? 'var(--primary-dark)' : 'green'; ?>;"><?php echo count($pendingReceipts); ?></strong>
    </div>
  </div>
</div>

<!-- Filter Card -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header" style="padding:var(--space-md) var(--space-lg); background:var(--cream);">
    <h2 style="font-size:14px;"><i class="fas fa-filter" style="margin-right:6px;"></i> Filter by Date</h2>
  </div>
  <div class="admin-card-body" style="padding:var(--space-lg);">
    <form action="admin/sudamaseva-receipts" method="GET" style="display:flex; gap:var(--space-md); align-items:flex-end;">
      <div class="form-group" style="margin-bottom:0; min-width:180px;">
        <label for="from">From Date</label>
        <input type="date" id="from" name="from" class="form-control" value="<?php echo htmlspecialchars($from); ?>">
      </div>
      <div class="form-group" style="margin-bottom:0; min-width:180px;">
        <label for="to">To Date</label>
        <input type="date" id="to" name="to" class="form-control" value="<?php echo htmlspecialchars($to); ?>">
      </div>
      <div style="display:flex; gap:8px;">
        <a href="admin/sudamaseva-receipts" class="btn btn-outline-dark" style="text-decoration:none; padding:8px 16px; border:1px solid var(--border); border-radius:var(--radius-md); color:var(--text); font-weight:600; font-size:13px;">Clear</a>
        <button type="submit" class="btn btn-primary" style="background-color:var(--primary); color:white; border:none; padding:8px 24px; border-radius:var(--radius-md); font-weight:600; font-size:13px; cursor:pointer;">Filter</button>
      </div>
    </form>
  </div>
</div>

<!-- Receipts Table -->
<div class="admin-card" style="margin-bottom: var(--space-xl);">
  <div class="admin-card-header">
    <h2>Receipt Records (Showing <?php echo count($receipts); ?> of <?php echo $total; ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 800px;">
        <thead>
          <tr>
            <th>Receipt No</th>
            <th>Donor</th>
            <th>Amount</th>
            <th>Payment Date</th>
            <th>Receipt Date</th>
            <th>80G Eligible</th>
            <th>Payment ID</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($receipts)): ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:var(--space-3xl); color:var(--text-light);">No receipts found matching the filters.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($receipts as $r): ?>
              <tr>
                <td style="font-family:monospace; font-weight:600; white-space:nowrap;">
                  <a href="admin/sudamaseva-receipt-print?id=<?php echo $r['id']; ?>" target="_blank" style="color:var(--maroon); text-decoration:none;" title="Print Receipt">
                    <i class="fas fa-print"></i> <?php echo htmlspecialchars($r['receipt_no']); ?>
                  </a>
                </td>
                <td>
                  <strong style="color:var(--dark);"><?php echo htmlspecialchars($r['donor_name'] ?? '—'); ?></strong>
                  <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($r['phone'] ?? ''); ?></div>
                </td>
                <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($r['amount'] ?? 0)); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo $service->formatDate($r['payment_date'] ?? null, 'd M Y'); ?></td>
                <td style="font-size:12px; color:var(--text-light);"><?php echo $service->formatDate($r['receipt_date'] ?? null, 'd M Y'); ?></td>
                <td>
                  <?php if (!empty($r['is_80g_eligible'])): ?>
                    <span class="badge badge-success"><i class="fas fa-check"></i> Yes</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">No</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:10px; font-family:monospace; color:var(--text-light); max-width:100px; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($r['razorpay_payment_id'] ?? ''); ?>">
                  <?php echo htmlspecialchars($r['razorpay_payment_id'] ?: '—'); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Pending Receipts Alert -->
<?php if (!empty($pendingReceipts)): ?>
<div class="admin-card">
  <div class="admin-card-header" style="background:#fff8f0;">
    <h2 style="color:var(--primary-dark);"><i class="fas fa-clock" style="margin-right:6px;"></i> Payments Awaiting Receipts (<?php echo count($pendingReceipts); ?>)</h2>
  </div>
  <div class="admin-card-body" style="padding:0;">
    <div class="admin-table-container" style="border:none; margin:0; border-radius:0;">
      <table class="admin-table" style="min-width: 700px;">
        <thead>
          <tr>
            <th>Payment ID</th>
            <th>Donor</th>
            <th>Amount</th>
            <th>Date</th>
            <th>PAN</th>
            <th style="text-align:center;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $shownPending = array_slice($pendingReceipts, 0, 20); ?>
          <?php foreach ($shownPending as $pp): ?>
            <tr>
              <td style="font-family:monospace; font-size:11px;">#<?php echo $pp['id']; ?></td>
              <td>
                <strong><?php echo htmlspecialchars($pp['donor_name'] ?? '—'); ?></strong>
              </td>
              <td style="font-weight:600; color:var(--maroon);"><?php echo $service->formatAmount((float) ($pp['amount'] ?? 0)); ?></td>
              <td style="font-size:12px; color:var(--text-light);"><?php echo $service->formatDate($pp['payment_date'] ?? null, 'd M Y'); ?></td>
              <td style="text-transform:uppercase; font-family:monospace;"><?php echo htmlspecialchars($pp['pan'] ?: '—'); ?></td>
              <td style="text-align:center;">
                <?php if (hasPermission('sudamaseva.edit')): ?>
                  <form action="admin/sudamaseva-receipts" method="POST" style="margin:0; display:inline-block;" onsubmit="return confirm('Generate receipt for payment #<?php echo $pp['id']; ?>?');">
                    <input type="hidden" name="action" value="generate_receipt">
                    <input type="hidden" name="payment_id" value="<?php echo $pp['id']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" class="btn btn-sm" style="background-color:var(--maroon); color:white; border:none; padding:4px 10px; border-radius:3px; font-weight:600; font-size:11px; cursor:pointer;">
                      <i class="fas fa-receipt"></i> Generate
                    </button>
                  </form>
                <?php else: ?>
                  <span style="font-size:11px; color:var(--text-light); font-style:italic;">No Perms</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (count($pendingReceipts) > 20): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:var(--space-md); color:var(--text-light); font-size:12px;">
                <i class="fas fa-ellipsis-h"></i> and <?php echo count($pendingReceipts) - 20; ?> more payments awaiting receipts
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Pagination -->
<?php if ($pages > 1): ?>
  <div style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:var(--space-xl); margin-bottom:var(--space-2xl);">
    <?php if ($page > 1): ?>
      <a href="admin/sudamaseva-receipts?page=<?php echo ($page - 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link"><i class="fas fa-chevron-left"></i> Prev</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a href="admin/sudamaseva-receipts?page=<?php echo $i; ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link<?php echo $i === $page ? ' active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
    <?php if ($page < $pages): ?>
      <a href="admin/sudamaseva-receipts?page=<?php echo ($page + 1); ?><?php echo !empty($queryString) ? '&' . $queryString : ''; ?>" class="page-link">Next <i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include 'partials/footer.php'; ?>
