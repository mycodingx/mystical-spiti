<?php
/**
 * Render test - simulates a web request to home page
 */
declare(strict_types=1);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

require __DIR__ . '/../public/_app/vendor/autoload.php';

foreach ([
    'APP_ENV' => 'test',
    'APP_DEBUG' => 'false',
    'APP_URL' => '',
    'BUSINESS_NAME' => 'Mystical Expedition',
    'BUSINESS_EMAIL' => 'info@test.local',
    'BUSINESS_PHONE' => '+91-8219000937',
    'BUSINESS_ADDRESS' => 'NH-22, opposite SBI Bank, Shoghi, Himachal Pradesh 171219',
    'BUSINESS_WHATSAPP' => '918219000937',
    'LEADS_NOTIFY_EMAIL' => 'info@test.local',
    'DB_PATH' => 'data/leads.sqlite',
] as $k => $v) {
    $_ENV[$k] = $v;
}

ob_start();
try {
    require __DIR__ . '/../public/index.php';
    $html = ob_get_clean();
    echo "[render-test] Page rendered: " . strlen($html) . " bytes\n";
    echo "[render-test] Hero: " . (str_contains($html, 'me-hero') ? 'YES' : 'NO') . "\n";
    echo "[render-test] Packages: " . substr_count($html, 'me-package-card') . "\n";
    echo "[render-test] Reviews: " . substr_count($html, 'me-review-card') . "\n";
    echo "[render-test] AJAX form: " . (str_contains($html, 'data-ajax-form') ? 'YES' : 'NO') . "\n";
    echo "[render-test] Modal: " . (str_contains($html, 'enquiryModal') ? 'YES' : 'NO') . "\n";
    echo "[render-test] JSON-LD: " . (str_contains($html, 'TravelAgency') ? 'YES' : 'NO') . "\n";
    echo "[render-test] CSRF: " . (str_contains($html, '_csrf') ? 'YES' : 'NO') . "\n";
    echo "[render-test] Honeypot: " . (str_contains($html, 'me-honeypot') ? 'YES' : 'NO') . "\n";
    echo "[render-test] Sticky bar: " . (str_contains($html, 'me-sticky-bar') ? 'YES' : 'NO') . "\n";
    echo "[render-test] Exit-intent: " . (str_contains($html, 'exitIntentModal') ? 'YES' : 'NO') . "\n";
    echo "[render-test] All packages show: " . substr_count($html, 'data-package-slug') . " (expected 6)\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "FAIL: " . $e->getMessage() . "\n";
    echo "At: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}