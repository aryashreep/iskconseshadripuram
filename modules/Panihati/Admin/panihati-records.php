<?php
/**
 * Panihati Yatra Registration Records List
 * 
 * Allows super_admin and travel_agent to view, sort, search, and filter devotee registration records.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config.php';

$pageTitle = 'Panihati Yatra Registration Records';
$activePage = 'panihati-records';
include 'partials/header.php';
requirePermission('panihati.view');

$db = getDB();

try {
    // Fetch Bhakti Sadans for filtering dropdown
    $stmt = $db->query("SELECT DISTINCT name FROM panihati_bhakti_sadans ORDER BY name ASC");
    $dbSadans = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch all registrations
    $stmt = $db->query("SELECT * FROM panihati_yatra_registrations ORDER BY id DESC");
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = 'A database error occurred. Please try again.';
}
?>

<div class="admin-content-header" style="margin-bottom:var(--space-xl);">
  <div>
    <h1 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">Registration Records</h1>
    <p style="color:var(--text-light); margin:5px 0 0 0; font-size:var(--font-size-sm);">Search, filter, and paginate devotee registrations.</p>
  </div>
</div>

<?php if (!empty($errorMsg)): ?>
  <div style="background:#ffebee; border:1px solid #ffcdd2; padding:var(--space-md); border-radius:var(--radius-md); color:#c62828; margin-bottom:var(--space-lg); font-size:var(--font-size-sm); display:flex; align-items:center; gap:8px;">
    <i class="fas fa-exclamation-circle"></i>
    <div><?php echo htmlspecialchars($errorMsg); ?></div>
  </div>
<?php endif; ?>

<!-- Registrations List Table -->
<div style="background:var(--white); border-radius:var(--radius-lg); border:1px solid var(--border); box-shadow:var(--shadow-sm); overflow:hidden; padding:var(--space-xl); margin-bottom:var(--space-2xl);">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--space-lg); flex-wrap:wrap; gap:15px;">
    <h3 style="font-family:var(--font-heading); color:var(--text-dark); margin:0;">Registration List</h3>
    
    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <!-- Search Input -->
      <input type="text" id="regSearch" onkeyup="applyFilters()" placeholder="Search Name, Phone, Sadan..." style="padding:6px 12px; border:1px solid var(--border); border-radius:4px; font-size:12px; width:200px; outline:none;">
      
      <!-- Travel Mode Filter -->
      <select id="filterMode" onchange="applyFilters()" style="padding:6px 12px; border:1px solid var(--border); border-radius:4px; font-size:12px; outline:none; background:var(--white);">
        <option value="all">All Travel Modes</option>
        <option value="bus">Bus</option>
        <option value="own_vehicle">Own Vehicle</option>
      </select>
      
      <!-- Bhakti Sadan Filter -->
      <select id="filterSadan" onchange="applyFilters()" style="padding:6px 12px; border:1px solid var(--border); border-radius:4px; font-size:12px; outline:none; background:var(--white); max-width:160px;">
        <option value="all">All Sadans</option>
        <?php foreach ($dbSadans as $s): ?>
          <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
        <?php endforeach; ?>
      </select>
      
      <!-- Payment Status Filter -->
      <select id="filterStatus" onchange="applyFilters()" style="padding:6px 12px; border:1px solid var(--border); border-radius:4px; font-size:12px; outline:none; background:var(--white);">
        <option value="all">All Statuses</option>
        <option value="paid">Paid</option>
        <option value="offline">Offline</option>
        <option value="failed">Failed</option>
      </select>

      <!-- Rows Per Page -->
      <select id="rowsPerPage" onchange="changeRowsPerPage(this.value)" style="padding:6px 12px; border:1px solid var(--border); border-radius:4px; font-size:12px; outline:none; background:var(--white);">
        <option value="10">10 per page</option>
        <option value="25" selected>25 per page</option>
        <option value="50">50 per page</option>
        <option value="100">100 per page</option>
        <option value="-1">Show All</option>
      </select>
    </div>
  </div>
  
  <div style="overflow-x:auto;">
    <table id="regTable" style="width:100%; border-collapse:collapse; font-size:var(--font-size-sm); text-align:left; min-width:800px;">
      <thead>
        <tr style="border-bottom:2px solid var(--border); color:var(--text-dark); font-weight:600; font-size:12px; user-select:none;">
          <th onclick="handleSort('id')" style="padding:12px 10px; cursor:pointer;"><span style="display:flex; align-items:center; gap:4px;">ID <i class="fas fa-sort" id="sort-icon-id"></i></span></th>
          <th onclick="handleSort('name')" style="padding:12px 10px; cursor:pointer;"><span style="display:flex; align-items:center; gap:4px;">Devotee <i class="fas fa-sort" id="sort-icon-name"></i></span></th>
          <th style="padding:12px 10px;">Contact Details</th>
          <th onclick="handleSort('travel_mode')" style="padding:12px 10px; cursor:pointer;"><span style="display:flex; align-items:center; gap:4px;">Travel Mode <i class="fas fa-sort" id="sort-icon-travel_mode"></i></span></th>
          <th onclick="handleSort('adults')" style="padding:12px 10px; cursor:pointer; text-align:center;"><span style="display:flex; align-items:center; justify-content:center; gap:4px;">Adults <i class="fas fa-sort" id="sort-icon-adults"></i></span></th>
          <th onclick="handleSort('kids')" style="padding:12px 10px; cursor:pointer; text-align:center;"><span style="display:flex; align-items:center; justify-content:center; gap:4px;">Kids <i class="fas fa-sort" id="sort-icon-kids"></i></span></th>
          <th onclick="handleSort('sadan')" style="padding:12px 10px; cursor:pointer;"><span style="display:flex; align-items:center; gap:4px;">Bhakti Sadan <i class="fas fa-sort" id="sort-icon-sadan"></i></span></th>
          <th onclick="handleSort('pickup')" style="padding:12px 10px; cursor:pointer;"><span style="display:flex; align-items:center; gap:4px;">Pickup Location <i class="fas fa-sort" id="sort-icon-pickup"></i></span></th>
          <th onclick="handleSort('amount')" style="padding:12px 10px; cursor:pointer; text-align:right;"><span style="display:flex; align-items:center; justify-content:flex-end; gap:4px;">Amount Paid <i class="fas fa-sort" id="sort-icon-amount"></i></span></th>
          <th onclick="handleSort('status')" style="padding:12px 10px; cursor:pointer; text-align:center;"><span style="display:flex; align-items:center; justify-content:center; gap:4px;">Status <i class="fas fa-sort" id="sort-icon-status"></i></span></th>
          <th onclick="handleSort('created_at')" style="padding:12px 10px; cursor:pointer;"><span style="display:flex; align-items:center; gap:4px;">Registered At <i class="fas fa-sort" id="sort-icon-created_at"></i></span></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($registrations)): ?>
          <?php foreach ($registrations as $r): ?>
            <tr style="border-bottom:1px solid var(--border); transition:background 0.2s;" onmouseover="this.style.background='var(--cream-light)'" onmouseout="this.style.background='none'">
              <td style="padding:12px 10px;">#<?php echo $r['id']; ?></td>
              <td style="padding:12px 10px; font-weight:600; color:var(--text-dark);"><?php echo htmlspecialchars($r['name']); ?></td>
              <td style="padding:12px 10px;">
                <div style="font-size:12px;"><?php echo htmlspecialchars($r['phone']); ?></div>
                <div style="font-size:11px; color:var(--text-light);"><?php echo htmlspecialchars($r['email']); ?></div>
              </td>
              <td style="padding:12px 10px; text-transform:capitalize;">
                <?php echo str_replace('_', ' ', $r['travel_mode']); ?>
              </td>
              <td style="padding:12px 10px; text-align:center;"><?php echo $r['adults_count']; ?></td>
              <td style="padding:12px 10px; text-align:center;"><?php echo $r['kids_count']; ?></td>
              <td style="padding:12px 10px;"><?php echo htmlspecialchars($r['bhakti_sadan']); ?></td>
              <td style="padding:12px 10px;"><?php echo htmlspecialchars($r['pickup_location'] ?: '-'); ?></td>
              <td style="padding:12px 10px; text-align:right; font-weight:600; color:var(--primary);">₹<?php echo number_format($r['amount'], 2); ?></td>
              <td style="padding:12px 10px; text-align:center;">
                <?php if ($r['payment_status'] === 'paid'): ?>
                  <span style="background:#e8f5e9; color:#2e7d32; padding:3px 8px; border-radius:4px; font-size:10px; font-weight:600; text-transform:uppercase;">Paid</span>
                <?php elseif ($r['payment_status'] === 'offline'): ?>
                  <span style="background:#fff3e0; color:#e65100; padding:3px 8px; border-radius:4px; font-size:10px; font-weight:600; text-transform:uppercase;">Offline</span>
                <?php else: ?>
                  <span style="background:#ffebee; color:#c62828; padding:3px 8px; border-radius:4px; font-size:10px; font-weight:600; text-transform:uppercase;"><?php echo $r['payment_status']; ?></span>
                <?php endif; ?>
              </td>
              <td style="padding:12px 10px; font-size:11px; color:var(--text-light);"><?php echo date('d-M-Y H:i', strtotime($r['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="11" style="text-align:center; padding:var(--space-2xl); color:var(--text-light);">No registration records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <!-- Pagination Controls Footer -->
  <div id="tableFooter" style="display:flex; justify-content:space-between; align-items:center; margin-top:var(--space-md); padding-top:var(--space-md); border-top:1px solid var(--border); flex-wrap:wrap; gap:10px;">
    <div id="paginationInfo" style="font-size:12px; color:var(--text-light);">
      Showing 0 to 0 of 0 entries
    </div>
    <div id="paginationButtons" style="display:flex; gap:5px; align-items:center;">
      <!-- Dynamic pagination buttons -->
    </div>
  </div>
</div>

<script>
let allRows = [];
let sortCol = 'id';
let sortAsc = false; // default sort descending by ID
let currentPage = 1;
let rowsPerPage = 25;

document.addEventListener('DOMContentLoaded', function() {
  const table = document.getElementById('regTable');
  if (!table) return;
  const tbody = table.querySelector('tbody');
  const trs = Array.from(tbody.querySelectorAll('tr'));
  
  if (trs.length === 1 && trs[0].cells.length === 1) {
    const footer = document.getElementById('tableFooter');
    if (footer) footer.style.display = 'none';
    return;
  }
  
  allRows = trs.map(tr => {
    const idVal = parseInt(tr.cells[0].textContent.replace('#', '')) || 0;
    const nameVal = tr.cells[1].textContent.trim();
    const phoneVal = tr.cells[2].querySelector('div:first-child') ? tr.cells[2].querySelector('div:first-child').textContent.trim() : '';
    const emailVal = tr.cells[2].querySelector('div:last-child') ? tr.cells[2].querySelector('div:last-child').textContent.trim() : '';
    const modeVal = tr.cells[3].textContent.trim().toLowerCase();
    const adultsVal = parseInt(tr.cells[4].textContent) || 0;
    const kidsVal = parseInt(tr.cells[5].textContent) || 0;
    const sadanVal = tr.cells[6].textContent.trim();
    const pickupVal = tr.cells[7].textContent.trim();
    const amtVal = parseFloat(tr.cells[8].textContent.replace('₹', '').replace(/,/g, '')) || 0;
    const statusVal = tr.cells[9].textContent.trim().toLowerCase();
    
    // Parse Date
    const dateStr = tr.cells[10].textContent.trim();
    let timeMs = 0;
    if (dateStr) {
      timeMs = Date.parse(dateStr) || 0;
    }
    
    return {
      element: tr,
      id: idVal,
      name: nameVal,
      phone: phoneVal,
      email: emailVal,
      travel_mode: modeVal,
      adults: adultsVal,
      kids: kidsVal,
      sadan: sadanVal,
      pickup: pickupVal,
      amount: amtVal,
      status: statusVal,
      created_at: timeMs,
      visible: true
    };
  });
  
  // Set initial sort indicator and load
  updateSortIcons('id', false);
  applyFilters();
});

function applyFilters() {
  const searchText = document.getElementById('regSearch').value.toLowerCase();
  const filterMode = document.getElementById('filterMode').value;
  const filterSadan = document.getElementById('filterSadan').value;
  const filterStatus = document.getElementById('filterStatus').value;
  
  allRows.forEach(row => {
    const matchesSearch = row.name.toLowerCase().includes(searchText) ||
                          row.phone.toLowerCase().includes(searchText) ||
                          row.email.toLowerCase().includes(searchText) ||
                          row.sadan.toLowerCase().includes(searchText) ||
                          row.pickup.toLowerCase().includes(searchText) ||
                          ('#' + row.id).includes(searchText);
                          
    const matchesMode = (filterMode === 'all') || (row.travel_mode === filterMode);
    const matchesSadan = (filterSadan === 'all') || (row.sadan === filterSadan);
    
    let matchesStatus = true;
    if (filterStatus !== 'all') {
      matchesStatus = row.status.includes(filterStatus);
    }
    
    row.visible = matchesSearch && matchesMode && matchesSadan && matchesStatus;
  });
  
  currentPage = 1;
  renderTable();
}

function handleSort(key) {
  if (sortCol === key) {
    sortAsc = !sortAsc;
  } else {
    sortCol = key;
    sortAsc = true;
  }
  
  updateSortIcons(sortCol, sortAsc);
  
  allRows.sort((a, b) => {
    let valA = a[key];
    let valB = b[key];
    
    if (typeof valA === 'string') {
      return sortAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
    } else {
      return sortAsc ? (valA - valB) : (valB - valA);
    }
  });
  
  const tbody = document.getElementById('regTable').querySelector('tbody');
  allRows.forEach(row => {
    tbody.appendChild(row.element);
  });
  
  renderTable();
}

function updateSortIcons(activeKey, isAsc) {
  const cols = ['id', 'name', 'travel_mode', 'adults', 'kids', 'sadan', 'pickup', 'amount', 'status', 'created_at'];
  cols.forEach(col => {
    const icon = document.getElementById('sort-icon-' + col);
    if (!icon) return;
    if (col === activeKey) {
      icon.className = isAsc ? 'fas fa-sort-up' : 'fas fa-sort-down';
      icon.style.color = 'var(--primary)';
    } else {
      icon.className = 'fas fa-sort';
      icon.style.color = '#ccc';
    }
  });
}

function changeRowsPerPage(val) {
  rowsPerPage = parseInt(val, 10);
  currentPage = 1;
  renderTable();
}

function renderTable() {
  const visibleRows = allRows.filter(r => r.visible);
  const totalRows = visibleRows.length;
  
  const startIdx = (currentPage - 1) * rowsPerPage;
  const endIdx = rowsPerPage === -1 ? totalRows : Math.min(startIdx + rowsPerPage, totalRows);
  
  allRows.forEach(row => {
    row.element.style.display = 'none';
  });
  
  visibleRows.slice(startIdx, endIdx).forEach(row => {
    row.element.style.display = '';
  });
  
  const info = document.getElementById('paginationInfo');
  if (info) {
    if (totalRows === 0) {
      info.textContent = 'Showing 0 to 0 of 0 entries';
    } else {
      info.textContent = 'Showing ' + (startIdx + 1) + ' to ' + endIdx + ' of ' + totalRows + ' entries' + (allRows.length !== totalRows ? ' (filtered from ' + allRows.length + ' total entries)' : '');
    }
  }
  
  const buttonsWrap = document.getElementById('paginationButtons');
  if (!buttonsWrap) return;
  buttonsWrap.innerHTML = '';
  
  if (rowsPerPage === -1 || totalRows <= rowsPerPage) {
    return;
  }
  
  const totalPages = Math.ceil(totalRows / rowsPerPage);
  
  // Previous
  const prevBtn = document.createElement('button');
  prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
  prevBtn.disabled = currentPage === 1;
  prevBtn.style.padding = '5px 10px';
  prevBtn.style.border = '1px solid var(--border)';
  prevBtn.style.background = 'var(--white)';
  prevBtn.style.color = currentPage === 1 ? '#ccc' : 'var(--text)';
  prevBtn.style.cursor = currentPage === 1 ? 'not-allowed' : 'pointer';
  prevBtn.style.borderRadius = '4px';
  prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; renderTable(); } };
  buttonsWrap.appendChild(prevBtn);
  
  const maxButtons = 5;
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + maxButtons - 1);
  if (endPage - startPage < maxButtons - 1) {
    startPage = Math.max(1, endPage - maxButtons + 1);
  }
  
  if (startPage > 1) {
    const firstBtn = document.createElement('button');
    firstBtn.textContent = '1';
    firstBtn.style.padding = '5px 10px';
    firstBtn.style.border = '1px solid var(--border)';
    firstBtn.style.background = 'var(--white)';
    firstBtn.style.cursor = 'pointer';
    firstBtn.style.borderRadius = '4px';
    firstBtn.onclick = () => { currentPage = 1; renderTable(); };
    buttonsWrap.appendChild(firstBtn);
    
    if (startPage > 2) {
      const dots = document.createElement('span');
      dots.textContent = '...';
      dots.style.padding = '0 5px';
      dots.style.color = 'var(--text-light)';
      buttonsWrap.appendChild(dots);
    }
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    btn.style.padding = '5px 10px';
    btn.style.border = i === currentPage ? '1px solid var(--primary)' : '1px solid var(--border)';
    btn.style.background = i === currentPage ? 'var(--primary)' : 'var(--white)';
    btn.style.color = i === currentPage ? '#fff' : 'var(--text)';
    btn.style.fontWeight = i === currentPage ? '600' : 'normal';
    btn.style.cursor = 'pointer';
    btn.style.borderRadius = '4px';
    btn.onclick = () => { currentPage = i; renderTable(); };
    buttonsWrap.appendChild(btn);
  }
  
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      const dots = document.createElement('span');
      dots.textContent = '...';
      dots.style.padding = '0 5px';
      dots.style.color = 'var(--text-light)';
      buttonsWrap.appendChild(dots);
    }
    
    const lastBtn = document.createElement('button');
    lastBtn.textContent = totalPages;
    lastBtn.style.padding = '5px 10px';
    lastBtn.style.border = '1px solid var(--border)';
    lastBtn.style.background = 'var(--white)';
    lastBtn.style.cursor = 'pointer';
    lastBtn.style.borderRadius = '4px';
    lastBtn.onclick = () => { currentPage = totalPages; renderTable(); };
    buttonsWrap.appendChild(lastBtn);
  }
  
  // Next
  const nextBtn = document.createElement('button');
  nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
  nextBtn.disabled = currentPage === totalPages;
  nextBtn.style.padding = '5px 10px';
  nextBtn.style.border = '1px solid var(--border)';
  nextBtn.style.background = 'var(--white)';
  nextBtn.style.color = currentPage === totalPages ? '#ccc' : 'var(--text)';
  nextBtn.style.cursor = currentPage === totalPages ? 'not-allowed' : 'pointer';
  nextBtn.style.borderRadius = '4px';
  nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; renderTable(); } };
  buttonsWrap.appendChild(nextBtn);
}
</script>

<?php include 'partials/footer.php'; ?>
