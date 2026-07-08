<?php
/**
 * Application Bootstrap
 * 
 * Single entry point for common setup: config, session, CSRF token.
 * Include this instead of manually requiring config.php and calling session_start().
 * 
 * Usage:
 *   require_once __DIR__ . '/../includes/bootstrap.php';
 * 
 * This file is idempotent — safe to include multiple times (uses require_once internally).
 */

// Load site configuration (includes Composer autoloader, .env, Razorpay keys, etc.)
require_once __DIR__ . '/../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
