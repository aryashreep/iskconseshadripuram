<?php
/**
 * Admin Panel Root Redirector
 * Redirects requests to /admin/ to the /admin/dashboard
 */
require_once __DIR__ . '/../config.php';

header('Location: ' . BASE_URL . 'admin/dashboard');
exit;
