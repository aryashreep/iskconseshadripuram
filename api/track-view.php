<?php
/**
 * Page View Tracker
 *
 * Lightweight endpoint to track donation page visits.
 * Called via 1x1 pixel image or navigator.sendBeacon from donate pages.
 * Enables conversion funnel analytics on the admin dashboard.
 */

header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/../config.php';

$pageUrl = $_GET['url'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$causeSlug = $_GET['cause'] ?? '';

// Extract cause slug from URL if not provided directly
if (empty($causeSlug) && !empty($pageUrl)) {
    if (preg_match('#/donate/([a-z0-9\-]+)#', $pageUrl, $m)) {
        $causeSlug = $m[1];
    }
}

// Skip tracking for non-donation pages
if (empty($causeSlug)) {
    // Return 1x1 transparent GIF
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

$sessionId = $_COOKIE['pv_sid'] ?? bin2hex(random_bytes(16));
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

try {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO page_views (session_id, page_url, cause_slug, referrer, user_agent, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$sessionId, $pageUrl, $causeSlug, $referrer, $userAgent, $ip]);
} catch (PDOException $e) {
    // Silently fail — tracking should never break the user experience
    error_log("Page view tracking error: " . $e->getMessage());
}

// Set session cookie (30 min expiry)
if (empty($_COOKIE['pv_sid'])) {
    setcookie('pv_sid', $sessionId, [
        'expires' => time() + 1800,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Return 1x1 transparent GIF
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
