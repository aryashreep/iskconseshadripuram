<?php

/**
 * ISKCON Bangalore - Site Configuration
 */

// ============================================
// Error Logging (for production debugging)
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Never show errors to visitors
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__, 2) . '/logs/php_errors.log');
ini_set('log_errors_max_len', '1024');

// Catch fatal errors that would otherwise show a blank/500 page
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = sprintf(
            "[%s] %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $error['message'],
            $error['file'],
            $error['line']
        );
        file_put_contents(dirname(__DIR__, 2) . '/logs/php_errors.log', $msg, FILE_APPEND | LOCK_EX);
    }
});

// Load Composer autoloader (vendor dependencies)
// __DIR__ = modules/Kernel/ -> need to reach root
$projectRoot = realpath(__DIR__ . '/../..');
$autoloadPath = $projectRoot . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require $autoloadPath;
    // Load .env file from project root (not from this module directory)
    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

// Razorpay API Keys — loaded from environment variables for security (OWASP A02)
// Never hardcode live credentials in this file.
//
// For local development, copy .env.example to .env and set your test keys.
// For production, set the environment variables on the server (e.g., via .htaccess or hosting panel).
//
// Required env vars:
//   RAZORPAY_KEY_ID       — Your Razorpay Key ID (rzp_test_* for test, rzp_live_* for production)
//   RAZORPAY_KEY_SECRET   — Your Razorpay Key Secret
//   RAZORPAY_TEST_MODE    — Set to "true" on dev/test, omit or "false" on production

// Determine if we're in test mode (defaults to false for production safety)
$razorpayTestMode = filter_var(
    $_ENV['RAZORPAY_TEST_MODE'] ?? $_SERVER['RAZORPAY_TEST_MODE'] ?? getenv('RAZORPAY_TEST_MODE') ?? false,
    FILTER_VALIDATE_BOOLEAN
);
if (!defined('RAZORPAY_TEST_MODE')) define('RAZORPAY_TEST_MODE', $razorpayTestMode);

// Load Razorpay credentials from environment only (no hardcoded fallback)
$razorpayKeyId = $_ENV['RAZORPAY_KEY_ID'] ?? $_SERVER['RAZORPAY_KEY_ID'] ?? getenv('RAZORPAY_KEY_ID');
$razorpayKeySecret = $_ENV['RAZORPAY_KEY_SECRET'] ?? $_SERVER['RAZORPAY_KEY_SECRET'] ?? getenv('RAZORPAY_KEY_SECRET');

if (empty($razorpayKeyId) || empty($razorpayKeySecret)) {
    // Fail gracefully — log the error and define empty constants
    // The frontend will show an appropriate message if payment is attempted
    error_log('CRITICAL: Razorpay API keys are not configured. ' .
        'Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in your environment or .env file.');
    if (!defined('RAZORPAY_KEY_ID')) define('RAZORPAY_KEY_ID', '');
    if (!defined('RAZORPAY_KEY_SECRET')) define('RAZORPAY_KEY_SECRET', '');
} else {
    if (!defined('RAZORPAY_KEY_ID')) define('RAZORPAY_KEY_ID', $razorpayKeyId);
    if (!defined('RAZORPAY_KEY_SECRET')) define('RAZORPAY_KEY_SECRET', $razorpayKeySecret);
}

if (!defined('CURRENCY')) define('CURRENCY', 'INR');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', html_entity_decode('&#8377;', ENT_QUOTES, 'UTF-8'));

// Auto-detect base path by comparing project directory with document root
// This works reliably regardless of which subdirectory page is being served
// Use $projectRoot (calculated above) because __DIR__ is now modules/Kernel/
$project_dir = str_replace('\\', '/', $projectRoot);
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
// Use case-insensitive comparison for Windows paths
$base_path = str_ireplace($doc_root, '', $project_dir);
if (!defined('BASE_PATH')) define('BASE_PATH', rtrim($base_path, '/'));
if (!defined('BASE_URL')) define('BASE_URL', BASE_PATH ? BASE_PATH . '/' : '/');

// Site settings
if (!defined('SITE_NAME')) define('SITE_NAME', 'ISKCON The Palace Temple of Lord Jagannath');
if (!defined('SITE_TAGLINE')) define('SITE_TAGLINE', 'ISKCON Seshadripuram, Bangalore');
if (!defined('SITE_URL')) define('SITE_URL', BASE_URL);
if (!defined('SITE_EMAIL')) define('SITE_EMAIL', 'info@iskconseshadripuram.org');
if (!defined('SITE_PHONE')) define('SITE_PHONE', '+91 99860 77269');

// Temple info
$TEMPLE_INFO = [
    'name' => 'ISKCON The Palace Temple of Lord Jagannath',
    'location' => 'Seshadripuram, Bangalore',
    'address' => '159, 1st Main road, Beside TRUGAS, Seshadripuram, Bengaluru - 560020',
    'phone' => '+91 99860 77269',
    'email' => 'info@iskconseshadripuram.org',
    'guest_email' => 'isjmadmin@gmail.com',
    'established' => 'January 31, 1998',
    'inaugurated_by' => 'His Holiness Jayapataka Swami Maharaj',
    'affiliation' => 'ISKCON Juhu, Mumbai',
    'deities' => 'Lord Sri Jagannath',
    'founder_acharya' => 'His Divine Grace A.C. Bhaktivedanta Swami Prabhupada',
];

// Bank details for direct transfer
$BANK_DETAILS = [
    'bank_name' => 'State Bank of India',
    'account_name' => 'ISKCON Seshadripuram Seva Account',
    'account_number' => '1234567890123456',
    'branch' => 'Seshadripuram Branch',
    'ifsc_code' => 'SBIN0012345',
    'swift_code' => 'SBININBB104',
    'upi_id' => 'iskconbangalore@upi',
];

// Temple Schedule
$TEMPLE_SCHEDULE = [
    ['time' => '05:00 AM', 'activity' => 'Mangal Arati', 'desc' => 'First ceremony of the day'],
    ['time' => '07:30 AM', 'activity' => 'Shringar Arati', 'desc' => 'Deity decoration ceremony'],
    ['time' => '08:00 AM', 'activity' => 'Srimad Bhagavatam Discourses', 'desc' => 'Spiritual discourse'],
    ['time' => '08:30 AM', 'activity' => 'Darshan Opens', 'desc' => 'Temple open for devotees'],
    ['time' => '12:30 PM', 'activity' => 'Raj Bhoga Arati', 'desc' => 'Midday offering ceremony'],
    ['time' => '01:00 PM', 'activity' => 'Darshan Closes', 'desc' => 'Afternoon closure'],
    ['time' => '04:30 PM', 'activity' => 'Darshan Opens', 'desc' => 'Evening darshan begins'],
    ['time' => '06:15 PM', 'activity' => 'Tulasi Arati', 'desc' => 'Tulasi worship ceremony'],
    ['time' => '06:30 PM', 'activity' => 'Sandhya Arati', 'desc' => 'Evening arati'],
    ['time' => '07:00 PM', 'activity' => 'Bhagavad Gita Class', 'desc' => 'Spiritual class'],
    ['time' => '08:15 PM', 'activity' => 'Shayan Arati', 'desc' => 'Bedtime ceremony'],
    ['time' => '08:30 PM', 'activity' => 'Darshan Closes', 'desc' => 'Temple closes'],
];

// Seva types (adapted for ISKCON Bangalore)
// Seva types (legacy — kept for backward compatibility)
// New causes are loaded from the donation_causes DB table via donation-helpers.php
$SEVA_TYPES = [
    'general' => [
        'name' => 'General Donation',
        'subtitle' => 'Support the Temple',
        'icon' => 'fa-hand-holding-heart',
        'image' => 'https://picsum.photos/seed/general/600/400',
        'short_desc' => 'Support the overall mission of ISKCON Seshadripuram.',
        'description' => 'Your general donation helps us direct funds where they are most needed.',
        'benefits' => ['Flexible support for urgent needs', 'Funds community programs', 'Supports temple mission'],
        'preset_amounts' => [101, 501, 1001, 5001],
        'default_amount' => 501,
        'options' => [
            ['name' => 'Any Contribution', 'amount' => 101, 'label' => '₹101'],
            ['name' => 'Modest Support', 'amount' => 501, 'label' => '₹501'],
            ['name' => 'Generous Gift', 'amount' => 1001, 'label' => '₹1,001'],
            ['name' => 'Major Donation', 'amount' => 5001, 'label' => '₹5,001'],
        ],
        'faq' => [
            ['q' => 'How is my donation used?', 'a' => 'Donations are allocated to our most pressing needs, ensuring maximum impact.'],
            ['q' => 'Can I specify usage?', 'a' => 'Yes! Please mention your preference in the notes section during checkout.'],
        ],
    ],
];

// Load donation helper functions (DB-backed cause system)
// Load from the Kernel module's includes/ which has the wrappers
// that delegate to modules/Donation/ via the facade
require_once __DIR__ . '/includes/donation-helpers.php';

// Blog helpers (get_blog_date, get_blog_tags) moved to database/migrations/migrate_blogs.php
// after the blog system was migrated to DB-driven in June 2026.
