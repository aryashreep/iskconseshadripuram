<?php
/**
 * Debug: Show PHP errors and DB status
 *
 * REMOVE THIS FILE AFTER DEBUGGING — it exposes server paths.
 */

header('Content-Type: text/plain; charset=utf-8');

// Load config first (this loads .env)
require_once dirname(__DIR__) . '/config.php';

$logFile = dirname(__DIR__) . '/logs/php_errors.log';

echo "=== Server Info ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n\n";

echo "=== .env Check ===\n";
$envFile = dirname(__DIR__) . '/.env';
echo ".env exists: " . (file_exists($envFile) ? 'YES' : 'NO') . "\n\n";

echo "=== DB Credentials (from _ENV) ===\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? '(not set)') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? '(not set)') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? '(not set)') . "\n";
echo "DB_PASS: " . (isset($_ENV['DB_PASS']) ? '***set***' : '(not set)') . "\n\n";

echo "=== DB Connection Test ===\n";
print_r(testDBConnection());

echo "\n=== Recent Errors (last 30 lines) ===\n\n";
if (file_exists($logFile) && filesize($logFile) > 0) {
    $lines = file($logFile);
    $last = array_slice($lines, -30);
    echo implode('', $last);
} else {
    echo "No errors logged.\n";
}
