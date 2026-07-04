<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ' . BASE_URL . 'admin/dashboard');
    exit;
}

// ============================================
// Rate Limiting Configuration (OWASP A07)
// ============================================
define('MAX_LOGIN_ATTEMPTS', 5);         // Max allowed attempts
define('RATE_LIMIT_WINDOW', 15);          // Time window in minutes
define('LOCKOUT_DURATION', 15);           // Lockout duration in minutes

/**
 * Get client IP address safely
 */
function getClientIP(): string {
    // Check X-Forwarded-For first (behind proxies), fall back to REMOTE_ADDR
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Check if the current IP is rate-limited.
 * Returns remaining lockout minutes if locked, or 0 if allowed.
 */
function checkRateLimit(): int {
    $ip = getClientIP();
    $db = getDB();
    
    // Count failed attempts within the window
    $cutoff = date('Y-m-d H:i:s', time() - RATE_LIMIT_WINDOW * 60);
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM login_attempts 
        WHERE ip_address = ? AND attempted_at >= ? AND successful = 0
    ");
    $stmt->execute([$ip, $cutoff]);
    $recentAttempts = (int)$stmt->fetchColumn();
    
    if ($recentAttempts >= MAX_LOGIN_ATTEMPTS) {
        // Find the earliest attempt in the window to calculate remaining lockout
        $stmt = $db->prepare("
            SELECT attempted_at FROM login_attempts 
            WHERE ip_address = ? AND attempted_at >= ? AND successful = 0
            ORDER BY attempted_at ASC LIMIT 1
        ");
        $stmt->execute([$ip, $cutoff]);
        $earliest = $stmt->fetchColumn();
        
        if ($earliest) {
            $lockoutUntil = strtotime($earliest) + LOCKOUT_DURATION * 60;
            $remaining = max(0, ceil(($lockoutUntil - time()) / 60));
            return (int)$remaining;
        }
        return LOCKOUT_DURATION;
    }
    return 0;
}

/**
 * Record a login attempt in the database
 */
function recordLoginAttempt(string $username, bool $successful): void {
    $ip = getClientIP();
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO login_attempts (ip_address, username_attempted, successful) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$ip, $username, $successful ? 1 : 0]);
}

/**
 * Clear failed login attempts for the current IP (on successful login)
 */
function clearLoginAttempts(): void {
    $ip = getClientIP();
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
}

$error = '';
$rateLimited = false;

// Check rate limit before processing form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lockoutMinutes = checkRateLimit();
    if ($lockoutMinutes > 0) {
        $rateLimited = true;
        $error = "Too many failed login attempts. Please try again in <strong>{$lockoutMinutes}</strong> minute(s).";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$rateLimited) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Clear failed attempts before recording success so we only have 1 success record
                clearLoginAttempts();
                recordLoginAttempt($username, true);
                
                // Regenerate session ID to prevent session fixation (OWASP A07)
                session_regenerate_id(true);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                
                header('Location: ' . BASE_URL . 'admin/dashboard');
                exit;
            } else {
                recordLoginAttempt($username, false);
                $error = 'Invalid username/email or password.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - <?php echo SITE_NAME; ?></title>
  <base href="<?php echo BASE_URL; ?>">
  <!-- Google Fonts & FontAwesome -->
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
  <!-- CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
  <div class="login-wrapper">
    <div class="login-card">
      <img src="assets/images/iskcon_logo.svg" alt="ISKCON Logo" class="login-logo">
      <h1>Admin Portal</h1>
      <p>ISKCON The Palace Temple of Lord Jagannath</p>
      
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      
      <form action="admin/login" method="POST">
        <div class="form-group">
          <label for="username"><i class="fas fa-user" style="margin-right: 6px;"></i> Username or Email</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter username or email" required autofocus>
        </div>
        
        <div class="form-group">
          <label for="password"><i class="fas fa-lock" style="margin-right: 6px;"></i> Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        
        <button type="submit" class="btn-admin-submit">
          <i class="fas fa-sign-in-alt" style="margin-right: 6px;"></i> Login to Dashboard
        </button>
      </form>
      
      <div style="margin-top: var(--space-lg);">
        <a href="<?php echo BASE_URL; ?>" style="color: rgba(255,255,255,0.5); font-size: var(--font-size-xs); text-decoration: none; transition: color var(--transition-fast);" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
          <i class="fas fa-arrow-left"></i> Back to Main Website
        </a>
      </div>
    </div>
  </div>
</body>
</html>
