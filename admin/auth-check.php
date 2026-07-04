<?php
/**
 * Admin Authentication Gate & Role-Based Access Control (RBAC)
 * Include this at the top of every admin page to ensure only logged-in admins can access.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load site configuration if not already loaded (for BASE_URL and DB helpers)
require_once __DIR__ . '/../config.php';

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if admin session variable is set
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to admin login page
    header('Location: ' . BASE_URL . 'admin/login');
    exit;
}

// Self-healing session: load role from database if not set in session (for users logged in before migration)
if (!isset($_SESSION['admin_role'])) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT role FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $_SESSION['admin_role'] = $stmt->fetchColumn() ?: 'editor';
    } catch (PDOException $e) {
        $_SESSION['admin_role'] = 'editor';
    }
}

/**
 * Check if the logged-in admin has any of the allowed roles.
 * Super Admin always has full access (returns true).
 * 
 * @param array $allowedRoles Array of role names, e.g. ['editor', 'pujari']
 * @return bool
 */
function hasRole(array $allowedRoles): bool {
    $role = $_SESSION['admin_role'] ?? 'editor';
    if ($role === 'super_admin') {
        return true; // Bypass all checks for super_admin
    }
    return in_array($role, $allowedRoles);
}

/**
 * Enforce role-based access control. If the current admin does not have any of the
 * allowed roles, blocks the request and shows an Access Denied message.
 * 
 * @param array $allowedRoles Array of role names
 */
function requireRole(array $allowedRoles) {
    if (!hasRole($allowedRoles)) {
        http_response_code(403);
        $pageTitle = 'Access Denied';
        $activePage = 'access-denied';
        include __DIR__ . '/partials/header.php';
        ?>
        <div style="text-align:center; padding: var(--space-4xl) 0;">
          <div style="font-size: 64px; color: var(--maroon); margin-bottom: var(--space-md);">
            <i class="fas fa-shield-halved"></i>
          </div>
          <h1 style="font-family:var(--font-heading); color:var(--dark); margin-bottom:var(--space-sm);">Access Denied</h1>
          <p style="color:var(--text-light); max-width:500px; margin: 0 auto var(--space-lg) auto; line-height:1.6; font-size:var(--font-size-sm);">
            Your account role (<strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $_SESSION['admin_role']))); ?></strong>) does not have permission to access this page. Please contact a Super Administrator if you require elevated privileges.
          </p>
          <a href="admin/dashboard" class="btn btn-outline-dark" style="text-decoration:none; padding:10px 20px; border:1px solid var(--border); border-radius:var(--radius-md); font-weight:600; color:var(--text); font-size:13px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Return to Dashboard
          </a>
        </div>
        <?php
        include __DIR__ . '/partials/footer.php';
        exit;
    }
}
