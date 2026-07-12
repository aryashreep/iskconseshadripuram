<?php
/**
 * Sudamaseva Module — Donor Lookup AJAX Endpoint
 *
 * Finds a donor by phone number OR legacy ID without authentication.
 * Returns minimal donor info + subscription summaries for the dashboard redirect.
 *
 * POST /api/sudamaseva/lookup
 *   { query: "phone_or_legacy_id" }
 *
 * Response:
 *   { found: true, donor_id: N, donor_name: "...", redirect_url: "..." }
 *   OR
 *   { found: false, error: "No donor found..." }
 */

header('Content-Type: application/json');
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestHost = parse_url($requestOrigin, PHP_URL_HOST) ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($requestHost && $requestHost === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
}

require_once __DIR__ . '/../../../config.php';

use Isjm\Modules\Sudamaseva\SudamasevaRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
\Isjm\Helpers\Security::checkHoneypot($input);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body']);
    exit;
}

$query = trim($input['query'] ?? '');
if (empty($query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Please enter a phone number or ID']);
    exit;
}

try {
    $repo = new SudamasevaRepository();
    $donor = $repo->findDonorByPhoneOrLegacyId($query);

    if (!$donor) {
        echo json_encode([
            'found' => false,
            'error' => 'No donor found with that phone number or ID.',
        ]);
        exit;
    }

    // Build redirect URL to the dashboard
    $redirectUrl = BASE_URL . 'sudamaseva/dashboard?donor_id=' . $donor['id'];

    echo json_encode([
        'found' => true,
        'donor_id' => (int) $donor['id'],
        'donor_name' => $donor['donor_name'],
        'phone' => $donor['phone'],
        'redirect_url' => $redirectUrl,
    ]);

} catch (Throwable $e) {
    error_log('Sudamaseva lookup error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred. Please try again.']);
}
