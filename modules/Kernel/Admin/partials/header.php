<?php
/**
 * Admin Panel Header Partial
 */
require_once __DIR__ . '/../auth-check.php';
require_once __DIR__ . '/../../includes/asset-helper.php';

// Determine active page if not explicitly set
if (!isset($activePage)) {
    $activePage = basename($_SERVER['PHP_SELF'], '.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin Panel' : 'ISKCON The Palace Temple of Lord Jagannath Admin Portal'; ?></title>
  <base href="<?php echo BASE_URL; ?>">
  <!-- Google Fonts & FontAwesome -->
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="admin-body">

  <!-- Sidebar Navigation -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
      <img src="assets/images/iskcon_logo.svg" alt="ISKCON Logo">
      <div>
        <h2>Palace Temple</h2>
        <span>Admin Portal</span>
      </div>
    </div>
    
    <nav class="admin-nav">
      <ul class="admin-nav-list">
        <?php if (hasPermission('dashboard.view')): ?>
          <li class="admin-nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <a href="admin/dashboard">
              <i class="fas fa-chart-line"></i> Dashboard
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasPermission('pujari_sevalist.view')): ?>
          <li class="admin-nav-item <?php echo $activePage === 'pujari-sevalist' ? 'active' : ''; ?>">
            <a href="admin/pujari-sevalist">
              <i class="fas fa-bell-concierge"></i> Pujari Sevalist
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasPermission('blogs.view')): ?>
          <li class="admin-nav-item <?php echo in_array($activePage, ['blogs', 'blog-edit']) ? 'active' : ''; ?>">
            <a href="admin/blogs">
              <i class="fas fa-newspaper"></i> Manage Blogs
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasPermission('seva_catalog.view')): ?>
          <li class="admin-nav-item <?php echo in_array($activePage, ['seva-catalogue', 'seva-catalogue-edit']) ? 'active' : ''; ?>">
            <a href="admin/seva-catalogue">
              <i class="fas fa-list-check"></i> Seva Catalogue
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasPermission('festivals.view')): ?>
          <?php
            $festivalSubPages = ['festivals', 'festival-edit', 'janmashtami-contest-registrations'];
            $festivalActive = in_array($activePage, $festivalSubPages);
          ?>
          <li class="admin-nav-group <?php echo $festivalActive ? 'active' : ''; ?>">
            <a href="admin/festivals">
              <span><i class="fas fa-calendar-alt"></i> Manage Festivals</span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </a>
            <ul class="admin-subnav">
              <li class="admin-subnav-item <?php echo in_array($activePage, ['festivals', 'festival-edit']) ? 'active' : ''; ?>">
                <a href="admin/festivals">
                  <i class="fas fa-list"></i> Festivals / Causes
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'janmashtami-contest-registrations' ? 'active' : ''; ?>">
                <a href="admin/janmashtami-contest-registrations">
                  <i class="fas fa-trophy"></i> Contest Registrations
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
        <?php if (hasAnyPermission(['donations.view', 'reports.view'])): ?>
          <?php
            $donationSubPages = ['report-dashboard', 'donations', 'report-category', 'report-activity', 'report-seva'];
            $donationActive = in_array($activePage, $donationSubPages);
          ?>
          <li class="admin-nav-group <?php echo $donationActive ? 'active' : ''; ?>">
            <a href="admin/donations">
              <span><i class="fas fa-hand-holding-heart"></i> Donations</span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </a>
            <ul class="admin-subnav">
              <li class="admin-subnav-item <?php echo $activePage === 'report-dashboard' ? 'active' : ''; ?>">
                <a href="admin/report-dashboard">
                  <i class="fas fa-chart-pie"></i> Dashboard
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'donations' ? 'active' : ''; ?>">
                <a href="admin/donations">
                  <i class="fas fa-list"></i> Transaction Logs
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'report-category' ? 'active' : ''; ?>">
                <a href="admin/report-category">
                  <i class="fas fa-layer-group"></i> Category Report
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'report-activity' ? 'active' : ''; ?>">
                <a href="admin/report-activity">
                  <i class="fas fa-calendar-alt"></i> Activity Report
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'report-seva' ? 'active' : ''; ?>">
                <a href="admin/report-seva">
                  <i class="fas fa-ribbon"></i> Seva Report
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
        <?php if (hasPermission('bookings.view')): ?>
          <li class="admin-nav-item <?php echo $activePage === 'bookings' ? 'active' : ''; ?>">
            <a href="admin/bookings">
              <i class="fas fa-hands-praying"></i> Puja & Yagya Bookings
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasAnyPermission(['panihati.view', 'panihati.create', 'panihati.edit'])): ?>
          <?php 
            $panihatiSubPages = ['panihati-yatra', 'panihati-records', 'panihati-reports', 'panihati-sadans', 'panihati-pickups', 'panihati-bulk-summary', 'panihati-pricing', 'panihati-expenses'];
            $paniActive = in_array($activePage, $panihatiSubPages);
            $parentLink = 'admin/panihati-yatra';
            if (hasPermission('panihati.edit') && !hasAnyPermission(['panihati.view', 'panihati.create'])) {
                $parentLink = 'admin/panihati-expenses';
            }
          ?>
          <li class="admin-nav-group <?php echo $paniActive ? 'active' : ''; ?>">
            <a href="<?php echo $parentLink; ?>">
              <span><i class="fas fa-route"></i> Panihati Yatra</span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </a>
            <ul class="admin-subnav">
              <?php if (hasPermission('panihati.view')): ?>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-yatra' ? 'active' : ''; ?>">
                  <a href="admin/panihati-yatra">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                  </a>
                </li>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-records' ? 'active' : ''; ?>">
                  <a href="admin/panihati-records">
                    <i class="fas fa-table"></i> Registration Records
                  </a>
                </li>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-reports' ? 'active' : ''; ?>">
                  <a href="admin/panihati-reports">
                    <i class="fas fa-file-download"></i> Download Reports
                  </a>
                </li>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-bulk-summary' ? 'active' : ''; ?>">
                  <a href="admin/panihati-bulk-summary">
                    <i class="fas fa-file-upload"></i> Add Offline Entry
                  </a>
                </li>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-sadans' ? 'active' : ''; ?>">
                  <a href="admin/panihati-sadans">
                    <i class="fas fa-place-of-worship"></i> Bhakti Sadans
                  </a>
                </li>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-pickups' ? 'active' : ''; ?>">
                  <a href="admin/panihati-pickups">
                    <i class="fas fa-map-pin"></i> Pickup Locations
                  </a>
                </li>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-pricing' ? 'active' : ''; ?>">
                  <a href="admin/panihati-pricing">
                    <i class="fas fa-tags"></i> Pricing
                  </a>
                </li>
              <?php endif; ?>
              <?php if (hasPermission('panihati.edit')): ?>
                <li class="admin-subnav-item <?php echo $activePage === 'panihati-expenses' ? 'active' : ''; ?>">
                  <a href="admin/panihati-expenses">
                    <i class="fas fa-file-invoice-dollar"></i> Finance & Expenses
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </li>
        <?php endif; ?>
        <?php if (hasPermission('sudamaseva.view')): ?>
          <?php
            $sudamasevaSubPages = ['sudamaseva-dashboard', 'sudamaseva-donors', 'sudamaseva-donor-add', 'sudamaseva-subscriptions', 'sudamaseva-payments', 'sudamaseva-receipts'];
            $sudamasevaActive = in_array($activePage, $sudamasevaSubPages);
          ?>
          <li class="admin-nav-group <?php echo $sudamasevaActive ? 'active' : ''; ?>">
            <a href="admin/sudamaseva-dashboard">
              <span><i class="fas fa-sync"></i> Sudamaseva</span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </a>
            <ul class="admin-subnav">
              <li class="admin-subnav-item <?php echo $activePage === 'sudamaseva-dashboard' ? 'active' : ''; ?>">
                <a href="admin/sudamaseva-dashboard">
                  <i class="fas fa-chart-pie"></i> Dashboard
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'sudamaseva-donors' ? 'active' : ''; ?>">
                <a href="admin/sudamaseva-donors">
                  <i class="fas fa-users"></i> Donors
                </a>
              </li>
              <?php if (hasPermission('sudamaseva.edit')): ?>
                <li class="admin-subnav-item <?php echo $activePage === 'sudamaseva-donor-add' ? 'active' : ''; ?>">
                  <a href="admin/sudamaseva-donor-add">
                    <i class="fas fa-user-plus"></i> Enroll New Donor
                  </a>
                </li>
              <?php endif; ?>
              <li class="admin-subnav-item <?php echo $activePage === 'sudamaseva-subscriptions' ? 'active' : ''; ?>">
                <a href="admin/sudamaseva-subscriptions">
                  <i class="fas fa-sync"></i> Subscriptions
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'sudamaseva-payments' ? 'active' : ''; ?>">
                <a href="admin/sudamaseva-payments">
                  <i class="fas fa-list"></i> Payments
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'sudamaseva-receipts' ? 'active' : ''; ?>">
                <a href="admin/sudamaseva-receipts">
                  <i class="fas fa-receipt"></i> Receipts
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
        <?php if (hasRole(['super_admin'])): ?>
          <?php
            $roleMgmtPages = ['admins', 'admin-edit', 'roles', 'role-edit', 'permissions'];
            $roleMgmtActive = in_array($activePage, $roleMgmtPages);
          ?>
          <li class="admin-nav-group <?php echo $roleMgmtActive ? 'active' : ''; ?>">
            <a href="admin/roles">
              <span><i class="fas fa-shield-alt"></i> Role Management</span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </a>
            <ul class="admin-subnav">
              <li class="admin-subnav-item <?php echo $activePage === 'admins' ? 'active' : ''; ?>">
                <a href="admin/admins">
                  <i class="fas fa-user-shield"></i> Manage Admins
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'admin-edit' ? 'active' : ''; ?>">
                <a href="admin/admin-edit">
                  <i class="fas fa-user-plus"></i> Assign Roles
                </a>
              </li>
              <li class="admin-subnav-item <?php echo in_array($activePage, ['roles', 'role-edit']) ? 'active' : ''; ?>">
                <a href="admin/roles">
                  <i class="fas fa-list"></i> Roles
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'permissions' ? 'active' : ''; ?>">
                <a href="admin/permissions">
                  <i class="fas fa-key"></i> Permissions
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
        <li class="admin-nav-item" style="margin-top: var(--space-xl); border-top: 1px solid rgba(255,255,255,0.05); padding-top: var(--space-sm);">
          <a href="<?php echo BASE_URL; ?>" target="_blank">
            <i class="fas fa-external-link-alt"></i> View Website
          </a>
        </li>
        <li class="admin-nav-item">
          <a href="admin/logout" style="color: #ffc9c9;">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </li>
      </ul>
    </nav>
    
    <div class="admin-sidebar-footer">
      <div>Logged in as:</div>
      <strong style="color: var(--accent);"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
    </div>
  </aside>

  <script>
  // Admin sidebar accordion
  document.addEventListener('DOMContentLoaded', function() {
    var groups = document.querySelectorAll('.admin-nav-group');

    // Open the active group; otherwise open the first group by default
    var activeFound = false;
    groups.forEach(function(g) {
      if (g.classList.contains('active')) {
        g.classList.add('open');
        activeFound = true;
      }
    });
    if (!activeFound && groups.length > 0) {
      groups[0].classList.add('open');
    }

    // Toggle accordion on parent link click (prevent navigation on all clicks)
    groups.forEach(function(group) {
      var link = group.querySelector(':scope > a');
      if (link) {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          var isOpen = group.classList.contains('open');
          // Close all other groups
          groups.forEach(function(other) {
            other.classList.remove('open');
          });
          // Toggle this group
          if (!isOpen) {
            group.classList.add('open');
          }
        });
      }
    });
  });
  </script>

  <!-- Main Content Wrapper -->
  <main class="admin-main">
