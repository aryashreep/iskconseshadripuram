<?php
/**
 * Admin Panel Header Partial
 */
require_once __DIR__ . '/../auth-check.php';

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
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/admin.css">
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
        <li class="admin-nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
          <a href="admin/dashboard">
            <i class="fas fa-chart-line"></i> Dashboard
          </a>
        </li>
        <?php if (hasRole(['super_admin', 'editor'])): ?>
          <li class="admin-nav-item <?php echo in_array($activePage, ['blogs', 'blog-edit']) ? 'active' : ''; ?>">
            <a href="admin/blogs">
              <i class="fas fa-newspaper"></i> Manage Blogs
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasRole(['super_admin', 'editor'])): ?>
          <li class="admin-nav-item <?php echo in_array($activePage, ['seva-catalogue', 'seva-catalogue-edit']) ? 'active' : ''; ?>">
            <a href="admin/seva-catalogue">
              <i class="fas fa-list-check"></i> Seva Catalogue
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasRole(['super_admin', 'editor', 'treasurer'])): ?>
          <li class="admin-nav-item <?php echo in_array($activePage, ['festivals', 'festival-edit']) ? 'active' : ''; ?>">
            <a href="admin/festivals">
              <i class="fas fa-calendar-alt"></i> Manage Festivals
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasRole(['super_admin', 'treasurer'])): ?>
          <li class="admin-nav-item <?php echo $activePage === 'donations' ? 'active' : ''; ?>">
            <a href="admin/donations">
              <i class="fas fa-hand-holding-heart"></i> Donation Logs
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasRole(['super_admin', 'pujari', 'treasurer'])): ?>
          <li class="admin-nav-item <?php echo $activePage === 'bookings' ? 'active' : ''; ?>">
            <a href="admin/bookings">
              <i class="fas fa-hands-praying"></i> Puja & Yagya Bookings
            </a>
          </li>
        <?php endif; ?>
        <?php if (hasRole(['super_admin', 'travel_agent'])): ?>
          <?php 
            $panihatiSubPages = ['panihati-yatra', 'panihati-records', 'panihati-reports', 'panihati-sadans', 'panihati-pickups', 'panihati-add-offline', 'panihati-bulk-summary', 'panihati-pricing'];
            $panihatiActive = in_array($activePage, $panihatiSubPages);
          ?>
          <li class="admin-nav-group <?php echo $panihatiActive ? 'active' : ''; ?>">
            <a href="admin/panihati-yatra">
              <span><i class="fas fa-route"></i> Panihati Yatra</span>
              <i class="fas fa-chevron-down nav-chevron"></i>
            </a>
            <ul class="admin-subnav">
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
              <li class="admin-subnav-item <?php echo $activePage === 'panihati-add-offline' ? 'active' : ''; ?>">
                <a href="admin/panihati-add-offline">
                  <i class="fas fa-plus-circle"></i> Add Offline Entry
                </a>
              </li>
              <li class="admin-subnav-item <?php echo $activePage === 'panihati-bulk-summary' ? 'active' : ''; ?>">
                <a href="admin/panihati-bulk-summary">
                  <i class="fas fa-layer-group"></i> Add Bulk Summary
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
            </ul>
          </li>
        <?php endif; ?>
        <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
          <li class="admin-nav-item <?php echo in_array($activePage, ['admins', 'admin-edit']) ? 'active' : ''; ?>">
            <a href="admin/admins">
              <i class="fas fa-user-shield"></i> Manage Admins
            </a>
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

  <!-- Main Content Wrapper -->
  <main class="admin-main">
