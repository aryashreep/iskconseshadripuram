<?php
/**
 * Panihati Yatra — Expenses & Finance Management
 *
 * CRUD for managing manual expenses and other income, and displays a
 * side-by-side Income & Expenditure Account report.
 * Accessible by: super_admin, travel_agent
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../Kernel/Admin/auth-check.php';
require_once __DIR__ . '/../panihati-helpers.php';

// Enforce permission before doing database operations
requirePermission('panihati.edit');

$db = getDB();
$successMsg = '';
$errorMsg = '';

// Get selected year filter
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : 2026;

// Handle CSV Export (must run before headers are outputted)
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="panihati_financial_statement_' . $selectedYear . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Panihati Yatra Income & Expenditure Account (' . $selectedYear . ')']);
    fputcsv($output, []);
    fputcsv($output, ['Type', 'Particulars / Description', 'Category', 'Quantity', 'Rate / Price', 'Amount (INR)', 'Date']);
    
    // 1. Write Expenses
    try {
        $stmt = $db->prepare("SELECT * FROM `panihati_expenses` WHERE `type` = 'expense' AND YEAR(`expense_date`) = ? ORDER BY `expense_date` ASC, `id` ASC");
        $stmt->execute([$selectedYear]);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($expenses as $exp) {
            fputcsv($output, [
                'Expense',
                $exp['particulars'],
                $exp['category'],
                '-',
                '-',
                $exp['amount'],
                $exp['expense_date']
            ]);
        }
    } catch (PDOException $e) {}
    
    // 2. Write Incomes (Calculated Online)
    $pricing = null;
    try {
        $stmt = $db->prepare("SELECT * FROM `panihati_pricing` WHERE `year` = ?");
        $stmt->execute([$selectedYear]);
        $pricing = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
    $busAdultPrice = floatval($pricing['bus_adult_price'] ?? 1000.00);
    $busKidPrice = floatval($pricing['bus_kid_price'] ?? 600.00);
    $vehicleAdultPrice = floatval($pricing['vehicle_adult_price'] ?? 600.00);

    try {
        $stmt = $db->prepare("SELECT SUM(adults_count) FROM `panihati_yatra_registrations` WHERE YEAR(created_at) = ? AND travel_mode = 'bus' AND payment_status IN ('paid', 'offline')");
        $stmt->execute([$selectedYear]);
        $regAdultsBus = intval($stmt->fetchColumn() ?: 0);

        $stmt = $db->prepare("SELECT SUM(kids_count) FROM `panihati_yatra_registrations` WHERE YEAR(created_at) = ? AND payment_status IN ('paid', 'offline')");
        $stmt->execute([$selectedYear]);
        $regKids = intval($stmt->fetchColumn() ?: 0);

        $stmt = $db->prepare("SELECT SUM(adults_count) FROM `panihati_yatra_registrations` WHERE YEAR(created_at) = ? AND travel_mode = 'own_vehicle' AND payment_status IN ('paid', 'offline')");
        $stmt->execute([$selectedYear]);
        $regAdultsVehicle = intval($stmt->fetchColumn() ?: 0);

        fputcsv($output, ['Income', 'Adults (Bus) - Online', 'Online Registration', $regAdultsBus, $busAdultPrice, $regAdultsBus * $busAdultPrice, '-']);
        fputcsv($output, ['Income', 'Children (Bus & Vehicle) - Online', 'Online Registration', $regKids, $busKidPrice, $regKids * $busKidPrice, '-']);
        fputcsv($output, ['Income', 'Own Vehicle - Online', 'Online Registration', $regAdultsVehicle, $vehicleAdultPrice, $regAdultsVehicle * $vehicleAdultPrice, '-']);
        
        // Write Offline registrations
        $stmt = $db->prepare("SELECT * FROM `panihati_yatra_offline_aggregates` WHERE `reported_year` = ? ORDER BY bhakti_sadan ASC");
        $stmt->execute([$selectedYear]);
        $offline = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($offline as $off) {
            $modeLabel = $off['travel_mode'] === 'bus' ? 'Bus' : 'Own Vehicle';
            $details = $off['adults_count'] . ' ad, ' . $off['kids_count'] . ' kd';
            fputcsv($output, [
                'Income',
                $off['bhakti_sadan'] . ' (' . $modeLabel . ') - Offline',
                'Offline Aggregate',
                $details,
                '-',
                $off['amount'],
                $off['created_at']
            ]);
        }

        // Write Manual Incomes
        $stmt = $db->prepare("SELECT * FROM `panihati_expenses` WHERE `type` = 'income' AND YEAR(`expense_date`) = ? ORDER BY `expense_date` ASC, `id` ASC");
        $stmt->execute([$selectedYear]);
        $manualIncomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($manualIncomes as $inc) {
            fputcsv($output, [
                'Income',
                $inc['particulars'],
                'Other Collections',
                '-',
                '-',
                $inc['amount'],
                $inc['expense_date']
            ]);
        }
    } catch (PDOException $e) {}
    
    fclose($output);
    exit();
}

$pageTitle = 'Panihati Finance & Expenses';
$activePage = 'panihati-expenses';
include 'partials/header.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$categories = [
    'Transport',
    'Prasadam & Kitchen',
    'Venue Bookings',
    'Deity Worship',
    'Labour & Seva',
    'Miscellaneous'
];

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMsg = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'];

        if ($action === 'add') {
            $type = $_POST['type'] ?? 'expense';
            $particulars = trim($_POST['particulars'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $expense_date = $_POST['expense_date'] ?? '';
            $category = trim($_POST['category'] ?? 'Miscellaneous');

            if (empty($particulars) || $amount <= 0 || empty($expense_date)) {
                $errorMsg = 'All fields are required and amount must be positive.';
            } else {
                try {
                    $stmt = $db->prepare("INSERT INTO `panihati_expenses` (`type`, `particulars`, `category`, `amount`, `expense_date`) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$type, $particulars, $category, $amount, $expense_date]);
                    $successMsg = 'Entry added successfully.';
                } catch (PDOException $e) {
                    $errorMsg = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'edit') {
            $id = intval($_POST['id'] ?? 0);
            $type = $_POST['type'] ?? 'expense';
            $particulars = trim($_POST['particulars'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $expense_date = $_POST['expense_date'] ?? '';
            $category = trim($_POST['category'] ?? 'Miscellaneous');

            if ($id <= 0 || empty($particulars) || $amount <= 0 || empty($expense_date)) {
                $errorMsg = 'All fields are required and amount must be positive.';
            } else {
                try {
                    $stmt = $db->prepare("UPDATE `panihati_expenses` SET `type` = ?, `particulars` = ?, `category` = ?, `amount` = ?, `expense_date` = ? WHERE `id` = ?");
                    $stmt->execute([$type, $particulars, $category, $amount, $expense_date, $id]);
                    $successMsg = 'Entry updated successfully.';
                } catch (PDOException $e) {
                    $errorMsg = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    $stmt = $db->prepare("DELETE FROM `panihati_expenses` WHERE `id` = ?");
                    $stmt->execute([$id]);
                    $successMsg = 'Entry deleted successfully.';
                } catch (PDOException $e) {
                    $errorMsg = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

// Fetch Pricing details for calculation
$pricing = null;
try {
    $stmt = $db->prepare("SELECT * FROM `panihati_pricing` WHERE `year` = ?");
    $stmt->execute([$selectedYear]);
    $pricing = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$busAdultPrice = floatval($pricing['bus_adult_price'] ?? 1000.00);
$busKidPrice = floatval($pricing['bus_kid_price'] ?? 600.00);
$vehicleAdultPrice = floatval($pricing['vehicle_adult_price'] ?? 600.00);

// Fetch registration totals from DB for the selected year
$regAdultsBus = 0;
$regKids = 0;
$regAdultsVehicle = 0;

try {
    // 1. Adults Bus
    $stmt = $db->prepare("SELECT SUM(adults_count) FROM `panihati_yatra_registrations` WHERE YEAR(created_at) = ? AND travel_mode = 'bus' AND payment_status IN ('paid', 'offline')");
    $stmt->execute([$selectedYear]);
    $regAdultsBus = intval($stmt->fetchColumn() ?: 0);

    // 2. Kids Total (both modes)
    $stmt = $db->prepare("SELECT SUM(kids_count) FROM `panihati_yatra_registrations` WHERE YEAR(created_at) = ? AND payment_status IN ('paid', 'offline')");
    $stmt->execute([$selectedYear]);
    $regKids = intval($stmt->fetchColumn() ?: 0);

    // 3. Adults Vehicle
    $stmt = $db->prepare("SELECT SUM(adults_count) FROM `panihati_yatra_registrations` WHERE YEAR(created_at) = ? AND travel_mode = 'own_vehicle' AND payment_status IN ('paid', 'offline')");
    $stmt->execute([$selectedYear]);
    $regAdultsVehicle = intval($stmt->fetchColumn() ?: 0);
} catch (PDOException $e) {}

// Compile dynamic income entries
$calculatedIncome = [
    [
        'id' => null,
        'particulars' => 'Adults (Bus)',
        'no' => $regAdultsBus,
        'rate' => $busAdultPrice,
        'amount' => $regAdultsBus * $busAdultPrice,
        'is_auto' => true
    ],
    [
        'id' => null,
        'particulars' => 'Children (Bus & Vehicle)',
        'no' => $regKids,
        'rate' => $busKidPrice,
        'amount' => $regKids * $busKidPrice,
        'is_auto' => true
    ],
    [
        'id' => null,
        'particulars' => 'Own Vehicle',
        'no' => $regAdultsVehicle,
        'rate' => $vehicleAdultPrice,
        'amount' => $regAdultsVehicle * $vehicleAdultPrice,
        'is_auto' => true
    ]
];

// Fetch manual expenses, manual incomes, and offline bulk summaries from database
$manualExpenses = [];
$manualIncomes = [];
$offlineRegistrations = [];

try {
    // Fetch manual expenses
    $stmt = $db->prepare("SELECT * FROM `panihati_expenses` WHERE `type` = 'expense' AND YEAR(`expense_date`) = ? ORDER BY `expense_date` ASC, `id` ASC");
    $stmt->execute([$selectedYear]);
    $manualExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch manual incomes
    $stmt = $db->prepare("SELECT * FROM `panihati_expenses` WHERE `type` = 'income' AND YEAR(`expense_date`) = ? ORDER BY `expense_date` ASC, `id` ASC");
    $stmt->execute([$selectedYear]);
    $manualIncomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch offline bulk summary registrations
    $stmt = $db->prepare("SELECT bhakti_sadan, travel_mode, SUM(adults_count) as adults, SUM(kids_count) as kids, SUM(amount) as total_amount FROM `panihati_yatra_offline_aggregates` WHERE `reported_year` = ? GROUP BY bhakti_sadan, travel_mode ORDER BY bhakti_sadan ASC");
    $stmt->execute([$selectedYear]);
    $offlineRegistrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Calculate totals
$totalExpenses = 0;
foreach ($manualExpenses as $exp) {
    $totalExpenses += floatval($exp['amount']);
}

$totalIncome = 0;
foreach ($calculatedIncome as $inc) {
    $totalIncome += floatval($inc['amount']);
}
foreach ($manualIncomes as $inc) {
    $totalIncome += floatval($inc['amount']);
}
foreach ($offlineRegistrations as $off) {
    $totalIncome += floatval($off['total_amount']);
}

$surplusDeficit = $totalIncome - $totalExpenses;

// Calculate category totals for charts
$categoryTotals = [];
foreach ($categories as $cat) {
    $categoryTotals[$cat] = 0;
}
foreach ($manualExpenses as $exp) {
    $cat = $exp['category'] ?: 'Miscellaneous';
    if (!isset($categoryTotals[$cat])) {
        $categoryTotals[$cat] = 0;
    }
    $categoryTotals[$cat] += floatval($exp['amount']);
}

function getCategoryStyle($cat) {
    switch ($cat) {
        case 'Transport': return 'background:#e3f2fd; color:#0d47a1; border:1px solid #bbdefb;';
        case 'Prasadam & Kitchen': return 'background:#e8f5e9; color:#1b5e20; border:1px solid #c8e6c9;';
        case 'Venue Bookings': return 'background:#f3e5f5; color:#4a148c; border:1px solid #e1bee7;';
        case 'Deity Worship': return 'background:#fffde7; color:#f57f17; border:1px solid #fff9c4;';
        case 'Labour & Seva': return 'background:#fff3e0; color:#e65100; border:1px solid #ffe0b2;';
        default: return 'background:#eceff1; color:#37474f; border:1px solid #cfd8dc;';
    }
}
?>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/admin/panihati-expenses.css">

<div class="admin-content-header" style="margin-bottom:var(--space-xl); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
  <div>
    <h1 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">Yatra Expenses & Finance</h1>
    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:var(--font-size-sm);">Track manual expenditures and display the Income & Expenditure account statement.</p>
  </div>
  
  <!-- Year Filter Form -->
  <form method="GET" action="" style="display:flex; align-items:center; gap:8px;">
    <label for="year" style="font-size:var(--font-size-sm); font-weight:600; color:var(--text-dark);">Select Year:</label>
    <select name="year" id="year" onchange="this.form.submit()" style="padding:6px 12px; border:1px solid var(--border); border-radius:4px; font-size:13px; font-weight:600; outline:none; background:var(--white);">
      <option value="2025" <?php echo $selectedYear === 2025 ? 'selected' : ''; ?>>2025</option>
      <option value="2026" <?php echo $selectedYear === 2026 ? 'selected' : ''; ?>>2026</option>
    </select>
  </form>
</div>

<?php if (!empty($successMsg)): ?>
  <div style="background:#e8f5e9; border:1px solid #c8e6c9; padding:var(--space-md); border-radius:var(--radius-md); color:#2e7d32; margin-bottom:var(--space-lg); font-size:var(--font-size-sm); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-check-circle"></i>
    <div><?php echo htmlspecialchars($successMsg); ?></div>
  </div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
  <div style="background:#ffebee; border:1px solid #ffcdd2; padding:var(--space-md); border-radius:var(--radius-md); color:#c62828; margin-bottom:var(--space-lg); font-size:var(--font-size-sm); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-exclamation-circle"></i>
    <div><?php echo htmlspecialchars($errorMsg); ?></div>
  </div>
<?php endif; ?>

<!-- Finance KPI cards -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:var(--space-lg); margin-bottom:var(--space-2xl);">
  <!-- Total Income Card -->
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); display:flex; align-items:center; gap:16px;">
    <div style="width:48px; height:48px; border-radius:50%; background:#e8f5e9; color:#2e7d32; display:flex; align-items:center; justify-content:center; font-size:20px;">
      <i class="fas fa-hand-holding-usd"></i>
    </div>
    <div>
      <div style="font-size:var(--font-size-xs); color:var(--text-light); text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Total Income (<?php echo $selectedYear; ?>)</div>
      <div style="font-size:24px; font-weight:700; color:var(--text-dark); margin-top:4px;">₹<?php echo number_format($totalIncome, 2); ?></div>
    </div>
  </div>

  <!-- Total Expenses Card -->
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); display:flex; align-items:center; gap:16px;">
    <div style="width:48px; height:48px; border-radius:50%; background:#ffebee; color:#c62828; display:flex; align-items:center; justify-content:center; font-size:20px;">
      <i class="fas fa-file-invoice-dollar"></i>
    </div>
    <div>
      <div style="font-size:var(--font-size-xs); color:var(--text-light); text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Total Expenses (<?php echo $selectedYear; ?>)</div>
      <div style="font-size:24px; font-weight:700; color:var(--text-dark); margin-top:4px;">₹<?php echo number_format($totalExpenses, 2); ?></div>
    </div>
  </div>

  <!-- Surplus/Deficit Card -->
  <div style="background:var(--white); border:1px solid var(--border); padding:var(--space-xl); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); display:flex; align-items:center; gap:16px;">
    <div style="width:48px; height:48px; border-radius:50%; background:<?php echo $surplusDeficit >= 0 ? '#e3f2fd' : '#fff3e0'; ?>; color:<?php echo $surplusDeficit >= 0 ? '#1565c0' : '#e65100'; ?>; display:flex; align-items:center; justify-content:center; font-size:20px;">
      <i class="fas <?php echo $surplusDeficit >= 0 ? 'fa-chart-line' : 'fa-chart-line-down'; ?>"></i>
    </div>
    <div>
      <div style="font-size:var(--font-size-xs); color:var(--text-light); text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Surplus / Deficit</div>
      <div style="font-size:24px; font-weight:700; color:<?php echo $surplusDeficit >= 0 ? '#2e7d32' : '#c62828'; ?>; margin-top:4px;">
        ₹<?php echo number_format($surplusDeficit, 2); ?>
      </div>
    </div>
  </div>
</div>

<!-- Header Control Buttons (CSV, PDF, Charts Toggle) -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); flex-wrap:wrap; gap:10px;">
  <button type="button" id="analyticsToggle" onclick="toggleAnalytics()" class="btn" style="background:var(--cream); color:var(--text-dark); border:1px solid var(--border); font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:6px; cursor:pointer; padding:6px 12px; border-radius:var(--radius-md);">
    <i class="fas fa-chart-pie" style="color:var(--primary);"></i> <span id="toggleText">Hide Analytics Dashboard</span>
  </button>
  
  <div style="display:flex; gap:8px;">
    <a href="admin/panihati-expenses?year=<?php echo $selectedYear; ?>&action=export_csv" class="btn" style="background:#2e7d32; color:var(--white); border:none; font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:var(--radius-md); text-decoration:none;">
      <i class="fas fa-file-csv"></i> Export Statement
    </a>
    <button type="button" onclick="window.print()" class="btn" style="background:#1e88e5; color:var(--white); border:none; font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:6px; cursor:pointer; padding:6px 12px; border-radius:var(--radius-md);">
      <i class="fas fa-print"></i> Print / Save PDF
    </button>
  </div>
</div>

<!-- Analytics Container -->
<div id="analyticsContainer" style="display:block; background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:var(--space-xl); margin-bottom:var(--space-2xl); box-shadow:var(--shadow-sm);">
  <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:var(--space-xl); min-height:280px; align-items:center; flex-wrap:wrap;">
    <div>
      <h4 style="margin:0 0 15px 0; font-family:var(--font-heading); color:var(--text-dark); font-size:13px; text-transform:uppercase; text-align:center; letter-spacing:0.5px;">Income vs. Expenses</h4>
      <div style="height:220px; position:relative;">
        <canvas id="financeBarChart"></canvas>
      </div>
    </div>
    <div>
      <h4 style="margin:0 0 15px 0; font-family:var(--font-heading); color:var(--text-dark); font-size:13px; text-transform:uppercase; text-align:center; letter-spacing:0.5px;">Expense Allocation Share</h4>
      <div style="height:220px; position:relative;">
        <canvas id="categoryDoughnutChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Side-by-Side Account Table -->
<div style="background:var(--white); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); overflow:hidden; padding:var(--space-xl); margin-bottom:var(--space-2xl);">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); flex-wrap:wrap; gap:15px; border-bottom: 2px solid var(--primary); padding-bottom: 10px;">
    <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0; text-transform: uppercase; font-size: 16px; letter-spacing: 0.5px;">
      <i class="fas fa-balance-scale"></i> Panihati Income & Expenditure Account (<?php echo $selectedYear; ?>)
    </h3>
    <div style="display:flex; gap:10px;">
      <button onclick="openModal('add', 'expense')" class="btn btn-sm btn-primary" style="font-size:11px; padding:6px 12px; display:flex; align-items:center; gap:5px; cursor:pointer;">
        <i class="fas fa-plus"></i> Add Expense
      </button>
      <button onclick="openModal('add', 'income')" class="btn btn-sm" style="font-size:11px; padding:6px 12px; border: 1px solid var(--primary); color: var(--primary); background: transparent; display:flex; align-items:center; gap:5px; cursor:pointer;" onmouseover="this.style.background='var(--cream-light)'" onmouseout="this.style.background='transparent'">
        <i class="fas fa-plus"></i> Add Other Income
      </button>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-2xl); min-width: 800px; overflow-x: auto;">
    <!-- LEFT COLUMN: EXPENSES -->
    <div>
      <div class="filter-bar-container" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-md); flex-wrap:wrap; gap:10px;">
        <h4 style="font-family:var(--font-heading); color:var(--maroon); margin:0; font-size:14px; text-transform:uppercase;">EXPENSES</h4>
        
        <!-- Live JS Search & Filter -->
        <div style="display:flex; gap:6px; align-items:center;">
          <input type="text" id="expenseSearch" onkeyup="filterExpenses()" placeholder="Search..." style="padding:4px 8px; font-size:11px; border:1px solid var(--border); border-radius:4px; width:110px; outline:none;">
          <select id="expenseCategoryFilter" onchange="filterExpenses()" style="padding:4px 8px; font-size:11px; border:1px solid var(--border); border-radius:4px; outline:none; background:var(--white);">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
        <thead>
          <tr style="border-bottom:2px solid var(--border); color:var(--text-dark); font-weight:600;">
            <th style="padding:10px 8px; width:45%;">Particulars / Description</th>
            <th style="padding:10px 8px; text-align:right; width:30%;">Amount</th>
            <th style="padding:10px 8px; text-align:center; width:25%;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($manualExpenses)): ?>
            <?php foreach ($manualExpenses as $exp): ?>
              <tr class="expense-row-item" data-particulars="<?php echo htmlspecialchars($exp['particulars']); ?>" data-category="<?php echo htmlspecialchars($exp['category']); ?>" style="border-bottom:1px solid var(--border);" onmouseover="this.style.background='var(--cream-light)'" onmouseout="this.style.background='none'">
                <td style="padding:10px 8px; vertical-align:middle;">
                  <div style="font-weight:600; color:var(--text-dark); display:flex; align-items:center; flex-wrap:wrap; gap:6px;">
                    <span><?php echo htmlspecialchars($exp['particulars']); ?></span>
                    <span class="badge" style="<?php echo getCategoryStyle($exp['category']); ?> font-size:8px; padding:1px 4px; border-radius:3px; font-weight:normal; text-transform:uppercase;"><?php echo htmlspecialchars($exp['category']); ?></span>
                  </div>
                  <div style="font-size:10px; color:var(--text-light); margin-top:2px;">
                    <i class="far fa-calendar-alt"></i> <?php echo date('d-M-Y', strtotime($exp['expense_date'])); ?>
                  </div>
                </td>
                <td style="padding:10px 8px; text-align:right; font-weight:600; color:var(--text-dark); vertical-align:middle;">
                  ₹<?php echo number_format($exp['amount'], 2); ?>
                </td>
                <td style="padding:10px 8px; text-align:center; vertical-align:middle;">
                  <button onclick="openModal('edit', 'expense', <?php echo $exp['id']; ?>, '<?php echo htmlspecialchars(addslashes($exp['particulars'])); ?>', <?php echo $exp['amount']; ?>, '<?php echo $exp['expense_date']; ?>', '<?php echo $exp['category']; ?>')" class="btn btn-sm" style="padding:2px 6px; font-size:10px; border:1px solid #1e88e5; color:#1e88e5; background:transparent; cursor:pointer;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background='transparent'">Edit</button>
                  <button onclick="confirmDelete(<?php echo $exp['id']; ?>)" class="btn btn-sm" style="padding:2px 6px; font-size:10px; border:1px solid #e53935; color:#e53935; background:transparent; margin-left:4px; cursor:pointer;" onmouseover="this.style.background='#ffebee'" onmouseout="this.style.background='transparent'">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr class="no-expenses-row">
              <td colspan="3" style="text-align:center; padding:var(--space-xl); color:var(--text-light);">No manual expenses recorded.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--border); font-weight:700; background:var(--cream-light);">
            <td style="padding:12px 8px;">Total Expenses</td>
            <td style="padding:12px 8px; text-align:right; color:var(--maroon);">₹<?php echo number_format($totalExpenses, 2); ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- RIGHT COLUMN: INCOME -->
    <div>
      <h4 style="font-family:var(--font-heading); color:#2e7d32; border-bottom:1px solid var(--border); padding-bottom:8px; margin:0 0 var(--space-md) 0; display:flex; justify-content:space-between;">
        <span>INCOME</span>
        <span style="font-size:12px; color:var(--text-light); font-weight:normal;">Registrations & Counter</span>
      </h4>
      <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
        <thead>
          <tr style="border-bottom:2px solid var(--border); color:var(--text-dark); font-weight:600;">
            <th style="padding:10px 8px; width:40%;">Particulars / Description</th>
            <th style="padding:10px 8px; text-align:center; width:12%;">No</th>
            <th style="padding:10px 8px; text-align:right; width:18%;">Rate</th>
            <th style="padding:10px 8px; text-align:right; width:20%;">Amount</th>
            <th style="padding:10px 8px; text-align:center; width:10%;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Automated Registration Income -->
          <?php foreach ($calculatedIncome as $inc): ?>
            <tr style="border-bottom:1px solid var(--border); background:#f9f9f9;" title="Auto-calculated from registration records">
              <td style="padding:10px 8px; font-weight:600; color:var(--text-dark); vertical-align:middle;">
                <?php echo $inc['particulars']; ?>
                <span style="display:inline-block; background:#e8f5e9; color:#2e7d32; font-size:9px; padding:1px 4px; border-radius:3px; font-weight:normal; margin-left:4px; text-transform:uppercase;">Auto</span>
              </td>
              <td style="padding:10px 8px; text-align:center; color:var(--text-dark); vertical-align:middle;">
                <?php echo $inc['no']; ?>
              </td>
              <td style="padding:10px 8px; text-align:right; color:var(--text-light); vertical-align:middle;">
                ₹<?php echo number_format($inc['rate'], 2); ?>
              </td>
              <td style="padding:10px 8px; text-align:right; font-weight:600; color:var(--text-dark); vertical-align:middle;">
                ₹<?php echo number_format($inc['amount'], 2); ?>
              </td>
              <td style="padding:10px 8px; text-align:center; color:var(--text-light); vertical-align:middle;">
                <i class="fas fa-lock" style="font-size:10px;" title="Locked (Computed dynamically)"></i>
              </td>
            </tr>
          <?php endforeach; ?>

          <!-- Divider for Offline Summary Data -->
          <?php if (!empty($offlineRegistrations)): ?>
            <tr style="background:var(--cream-light);">
              <td colspan="5" style="padding:4px 8px; font-size:10px; font-weight:600; color:var(--text-light); letter-spacing:0.5px; text-transform:uppercase;">Offline Registrations (via Add Offline Entry)</td>
            </tr>
            <?php foreach ($offlineRegistrations as $off): 
              $modeLabel = $off['travel_mode'] === 'bus' ? 'Bus' : 'Own Vehicle';
              $details = [];
              if ($off['adults'] > 0) $details[] = $off['adults'] . ' ad';
              if ($off['kids'] > 0) $details[] = $off['kids'] . ' kd';
              $detailsStr = implode(', ', $details);
            ?>
              <tr style="border-bottom:1px solid var(--border);" title="Entered in Add Offline Entry">
                <td style="padding:10px 8px; vertical-align:middle;">
                  <div style="font-weight:600; color:var(--text-dark);"><?php echo htmlspecialchars($off['bhakti_sadan']); ?></div>
                  <div style="font-size:10px; color:var(--text-light); margin-top:2px;">
                    <span class="badge <?php echo $off['travel_mode'] === 'bus' ? 'badge-info' : 'badge-secondary'; ?>"><?php echo $modeLabel; ?></span>
                  </div>
                </td>
                <td style="padding:10px 8px; text-align:center; color:var(--text-dark); vertical-align:middle;">
                  <?php echo $detailsStr; ?>
                </td>
                <td style="padding:10px 8px; text-align:right; color:var(--text-light); vertical-align:middle;">-</td>
                <td style="padding:10px 8px; text-align:right; font-weight:600; color:var(--text-dark); vertical-align:middle;">
                  ₹<?php echo number_format($off['total_amount'], 2); ?>
                </td>
                <td style="padding:10px 8px; text-align:center; color:var(--text-light); vertical-align:middle;">
                  <a href="admin/panihati-bulk-summary" title="Manage Offline Entries"><i class="fas fa-external-link-alt" style="font-size:10px; color:var(--primary);"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Divider for Manual Income -->
          <?php if (!empty($manualIncomes)): ?>
            <tr style="background:var(--cream-light);">
              <td colspan="5" style="padding:4px 8px; font-size:10px; font-weight:600; color:var(--text-light); letter-spacing:0.5px; text-transform:uppercase;">Other Collections / Counter Cash</td>
            </tr>
            <?php foreach ($manualIncomes as $inc): ?>
              <tr style="border-bottom:1px solid var(--border);" onmouseover="this.style.background='var(--cream-light)'" onmouseout="this.style.background='none'">
                <td style="padding:10px 8px; vertical-align:middle;">
                  <div style="font-weight:600; color:var(--text-dark);"><?php echo htmlspecialchars($inc['particulars']); ?></div>
                  <div style="font-size:10px; color:var(--text-light); margin-top:2px;">
                    <i class="far fa-calendar-alt"></i> <?php echo date('d-M-Y', strtotime($inc['expense_date'])); ?>
                  </div>
                </td>
                <td style="padding:10px 8px; text-align:center; color:var(--text-light); vertical-align:middle;">-</td>
                <td style="padding:10px 8px; text-align:right; color:var(--text-light); vertical-align:middle;">-</td>
                <td style="padding:10px 8px; text-align:right; font-weight:600; color:var(--text-dark); vertical-align:middle;">
                  ₹<?php echo number_format($inc['amount'], 2); ?>
                </td>
                <td style="padding:10px 8px; text-align:center; vertical-align:middle;">
                  <button onclick="openModal('edit', 'income', <?php echo $inc['id']; ?>, '<?php echo htmlspecialchars(addslashes($inc['particulars'])); ?>', <?php echo $inc['amount']; ?>, '<?php echo $inc['expense_date']; ?>')" class="btn btn-sm" style="padding:2px 6px; font-size:10px; border:1px solid #1e88e5; color:#1e88e5; background:transparent;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background='transparent'"><i class="fas fa-edit"></i></button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--border); font-weight:700; background:var(--cream-light);">
            <td colspan="3" style="padding:12px 8px;">Total Income</td>
            <td style="padding:12px 8px; text-align:right; color:#2e7d32;">₹<?php echo number_format($totalIncome, 2); ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- Surplus / Deficit Bottom Row -->
  <div style="margin-top:var(--space-2xl); border-top:2px double var(--border); padding-top:var(--space-lg); display:flex; justify-content:space-between; align-items:center; font-weight:700; font-size:15px;">
    <span style="color:var(--text-dark);">Surplus / Deficit (Income - Expenses)</span>
    <span style="color:<?php echo $surplusDeficit >= 0 ? '#2e7d32' : '#c62828'; ?>; background:<?php echo $surplusDeficit >= 0 ? '#e8f5e9' : '#ffebee'; ?>; padding:6px 16px; border-radius:4px; border:1px solid <?php echo $surplusDeficit >= 0 ? '#c8e6c9' : '#ffcdd2'; ?>;">
      ₹<?php echo number_format($surplusDeficit, 2); ?>
    </span>
  </div>
</div>

<!-- Modal Form Dialog (Handles Add and Edit) -->
<div id="financeModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4); justify-content:center; align-items:center;">
  <div style="background-color:var(--white); border-radius:var(--radius-lg); width:90%; max-width:480px; padding:var(--space-xl); box-shadow:var(--shadow-lg); border:1px solid var(--border);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); border-bottom:1px solid var(--border); padding-bottom:10px;">
      <h3 id="modalTitle" style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">Add Entry</h3>
      <span onclick="closeModal()" style="font-size:24px; font-weight:bold; color:var(--text-light); cursor:pointer;" onmouseover="this.style.color='var(--text-dark)'" onmouseout="this.style.color='var(--text-light)'">&times;</span>
    </div>

    <form method="POST" action="">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <input type="hidden" name="action" id="formAction" value="add">
      <input type="hidden" name="id" id="entryId" value="">
      <input type="hidden" name="type" id="entryType" value="expense">

      <!-- Particulars -->
      <div style="margin-bottom:var(--space-md);">
        <label for="particulars" style="display:block; font-size:var(--font-size-sm); font-weight:600; color:var(--text-dark); margin-bottom:6px;">Particulars / Description *</label>
        <input type="text" name="particulars" id="particulars" required placeholder="e.g. Bus payment, Deity materials, etc." style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:4px; outline:none; font-size:13px;">
      </div>

      <!-- Category (Only for Expense) -->
      <div id="categoryGroup" style="margin-bottom:var(--space-md);">
        <label for="category" style="display:block; font-size:var(--font-size-sm); font-weight:600; color:var(--text-dark); margin-bottom:6px;">Category *</label>
        <select name="category" id="category" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:4px; outline:none; font-size:13px; background:var(--white);">
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Amount -->
      <div style="margin-bottom:var(--space-md);">
        <label for="amount" style="display:block; font-size:var(--font-size-sm); font-weight:600; color:var(--text-dark); margin-bottom:6px;">Amount (₹) *</label>
        <input type="number" step="0.01" name="amount" id="amount" required min="0.01" placeholder="0.00" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:4px; outline:none; font-size:13px;">
      </div>

      <!-- Expense Date -->
      <div style="margin-bottom:var(--space-lg);">
        <label for="expense_date" style="display:block; font-size:var(--font-size-sm); font-weight:600; color:var(--text-dark); margin-bottom:6px;">Date *</label>
        <input type="date" name="expense_date" id="expense_date" required value="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:8px 12px; border:1px solid var(--border); border-radius:4px; outline:none; font-size:13px;">
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" onclick="closeModal()" class="btn" style="background:#f5f5f5; border:1px solid var(--border); color:var(--text-dark);">Cancel</button>
        <button type="submit" class="btn btn-primary" id="submitBtn">Save Entry</button>
      </div>
    </form>
  </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" action="" style="display:none;">
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="id" id="deleteEntryId" value="">
</form>

<script>
// JSON encoded data for charts
var financeData = {
    income: <?php echo $totalIncome; ?>,
    expenses: <?php echo $totalExpenses; ?>,
    categories: <?php echo json_encode(array_keys($categoryTotals)); ?>,
    categoryAmounts: <?php echo json_encode(array_values($categoryTotals)); ?>
};

var barChartInst = null;
var doughnutChartInst = null;

function toggleAnalytics() {
    const container = document.getElementById('analyticsContainer');
    const toggleText = document.getElementById('toggleText');
    const isHidden = container.style.display === 'none';
    
    if (isHidden) {
        container.style.display = 'block';
        toggleText.textContent = 'Hide Analytics Dashboard';
        initCharts();
    } else {
        container.style.display = 'none';
        toggleText.textContent = 'Show Analytics Dashboard';
    }
}

function initCharts() {
    if (barChartInst && doughnutChartInst) return;
    
    // Bar Chart
    const ctxBar = document.getElementById('financeBarChart').getContext('2d');
    barChartInst = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Total Income', 'Total Expenses'],
            datasets: [{
                data: [financeData.income, financeData.expenses],
                backgroundColor: ['#2e7d32', '#c62828'],
                borderRadius: 4,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return '₹' + value.toLocaleString('en-IN'); }
                    }
                }
            }
        }
    });

    // Filter categories that have 0 expenditure
    var filteredCategories = [];
    var filteredAmounts = [];
    for (var i = 0; i < financeData.categories.length; i++) {
        if (financeData.categoryAmounts[i] > 0) {
            filteredCategories.push(financeData.categories[i]);
            filteredAmounts.push(financeData.categoryAmounts[i]);
        }
    }

    // Doughnut Chart
    const ctxDoughnut = document.getElementById('categoryDoughnutChart').getContext('2d');
    doughnutChartInst = new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: filteredCategories,
            datasets: [{
                data: filteredAmounts,
                backgroundColor: [
                    '#1e88e5', // Transport
                    '#4caf50', // Prasadam
                    '#9c27b0', // Venue
                    '#fbc02d', // Deity
                    '#ff9800', // Labour
                    '#78909c'  // Misc
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 10, font: { size: 9 } }
                }
            }
        }
    });
}

function filterExpenses() {
    var searchVal = document.getElementById('expenseSearch').value.toLowerCase();
    var catVal = document.getElementById('expenseCategoryFilter').value;
    var rows = document.querySelectorAll('.expense-row-item');
    
    rows.forEach(function(row) {
        var particulars = row.getAttribute('data-particulars').toLowerCase();
        var category = row.getAttribute('data-category');
        
        var matchesSearch = particulars.indexOf(searchVal) > -1;
        var matchesCat = catVal === '' || category === catVal;
        
        if (matchesSearch && matchesCat) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function openModal(action, type, id = '', particulars = '', amount = '', date = '', category = 'Miscellaneous') {
    const modal = document.getElementById('financeModal');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const entryId = document.getElementById('entryId');
    const entryType = document.getElementById('entryType');
    const inputParticulars = document.getElementById('particulars');
    const inputAmount = document.getElementById('amount');
    const inputDate = document.getElementById('expense_date');
    const inputCategory = document.getElementById('category');
    const categoryGroup = document.getElementById('categoryGroup');
    const submitBtn = document.getElementById('submitBtn');

    formAction.value = action;
    entryType.value = type;
    entryId.value = id;
    inputParticulars.value = particulars;
    inputAmount.value = amount;
    inputCategory.value = category;
    
    if (date) {
        inputDate.value = date;
    } else {
        inputDate.value = "<?php echo $selectedYear; ?>-06-18";
    }

    if (type === 'expense') {
        categoryGroup.style.display = 'block';
    } else {
        categoryGroup.style.display = 'none';
    }

    if (action === 'add') {
        modalTitle.textContent = type === 'expense' ? 'Add Expense' : 'Add Other Income';
        submitBtn.textContent = 'Add Entry';
    } else {
        modalTitle.textContent = type === 'expense' ? 'Edit Expense' : 'Edit Other Income';
        submitBtn.textContent = 'Save Changes';
    }

    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('financeModal').style.display = 'none';
}

function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this expense record?')) {
        document.getElementById('deleteEntryId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('financeModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Auto-initialize charts on DOMContentLoaded
window.addEventListener('DOMContentLoaded', function() {
    initCharts();
});
</script>

<?php include 'partials/footer.php'; ?>
