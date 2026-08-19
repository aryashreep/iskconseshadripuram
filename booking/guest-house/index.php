<?php
/**
 * Guest house booking has been disabled.
 * Permanently redirect to the booking index page.
 */

// Build the booking index URL from the current request
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
         . '://' . $_SERVER['HTTP_HOST'];

// Use BASE_URL if available, otherwise build from request
if (defined('BASE_URL')) {
    $redirectUrl = BASE_URL . 'booking/';
} else {
    $redirectUrl = $baseUrl . '/booking/';
}

header('HTTP/1.1 301 Moved Permanently');
header('Location: ' . $redirectUrl);
exit;
