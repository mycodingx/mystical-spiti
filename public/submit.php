<?php
/**
 * Mystical Expedition - Submit Endpoint
 * Handles POST from hero form and modal form.
 */

declare(strict_types=1);

// Load Composer autoloader (needed for Dotenv, PHPMailer, etc.)
require_once __DIR__ . '/_app/vendor/autoload.php';
require_once __DIR__ . '/_app/src/Bootstrap.php';

use Mystical\Bootstrap;
use Mystical\Csrf;
use Mystical\LeadService;

Bootstrap::init();

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Method Not Allowed';
    exit;
}

// CSRF check
if (!Csrf::verify()) {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'message' => 'Invalid security token. Please reload the page and try again.']);
    exit;
}

// Detect AJAX vs classic form submission
$wantsJson = (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) || (
    isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
);

// Gather context
$ip        = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referrer  = $_SERVER['HTTP_REFERER'] ?? '';

// Process
$service = new LeadService();
$result  = $service->process($_POST, $ip, $userAgent, $referrer);

// AJAX response
if ($wantsJson) {
    header('Content-Type: application/json; charset=UTF-8');
    if ($result['ok']) {
        http_response_code(200);
        echo json_encode([
            'ok'      => true,
            'message' => $result['message'] ?? 'Thank you!',
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'ok'      => false,
            'message' => $result['message'] ?? 'Validation failed.',
            'errors'  => $result['errors'] ?? [],
        ]);
    }
    exit;
}

// Classic form: redirect to thanks.php on success, back to index on error.
// Use a relative path so it resolves correctly whether the app sits at
// host root (https://shimla.mysticalexpedition.com/) or under a
// subdirectory (http://localhost:8080/mystical/).
$successUrl = 'thanks.php';
if ($result['ok']) {
    header('Location: ' . $successUrl);
    exit;
}

// Failure - encode errors into the redirect so the page can display them
$ref = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$qs = http_build_query([
    'err' => $result['message'] ?? 'Validation failed.',
]);
$sep = (strpos($ref, '?') === false) ? '?' : '&';
header('Location: ' . $ref . $sep . $qs);
exit;