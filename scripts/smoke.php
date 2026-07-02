<?php
/**
 * Quick smoke test - run from CLI:
 *   php scripts/smoke.php
 */

declare(strict_types=1);

require __DIR__ . '/../public/_app/vendor/autoload.php';

// Mock .env values for CLI testing
foreach ([
    'APP_ENV' => 'test',
    'APP_DEBUG' => 'true',
    'BUSINESS_NAME' => 'Mystical Expedition',
    'BUSINESS_EMAIL' => 'mysticalexpedition@gmail.com',
    'BUSINESS_PHONE' => '+91-8894042702',
    'LEADS_NOTIFY_EMAIL' => 'info@test.local',
    'LEADS_RATE_LIMIT_PER_HOUR' => '5',
    'DB_PATH' => 'data/leads.sqlite',
] as $k => $v) {
    $_ENV[$k] = $v;
}

use Mystical\Bootstrap;
use Mystical\Csrf;
use Mystical\Database;
use Mystical\LeadService;

echo "=== Mystical Expedition - Smoke Test ===\n\n";

// 1. Bootstrap
echo "[1] Bootstrap...\n";
try {
    Bootstrap::init();
    echo "    OK\n";
} catch (Throwable $e) {
    echo "    FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. CSRF
echo "[2] CSRF token + field...\n";
$t1 = Csrf::token();
$t2 = Csrf::token();
if ($t1 === $t2 && strlen($t1) === 64) {
    echo "    OK (token stable, 64 chars)\n";
} else {
    echo "    FAIL: tokens differ or wrong length\n";
    exit(1);
}

// 3. Database
echo "[3] Database migration + insert + count...\n";
try {
    Database::getInstance(); // triggers migration
    $count = Database::countRecentByIp('127.0.0.1', 60);
    echo "    OK (recent count: $count)\n";
} catch (Throwable $e) {
    echo "    FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. LeadService - happy path
echo "[4] LeadService happy path...\n";
$svc = new LeadService();
$result = $svc->process([
    'name'        => 'Test User',
    'city'        => 'Delhi',
    'email'       => 'test@example.com',
    'phone'       => '9876543210',
    'destination' => 'Spiti Valley Circuit - 10 Days / 9 Nights',
    'website'     => '', // honeypot
], '127.0.0.1', 'PHP CLI', 'http://localhost');

if ($result['ok']) {
    echo "    OK (lead #" . ($result['lead']['id'] ?? '?') . ")\n";
} else {
    echo "    FAIL: " . json_encode($result) . "\n";
    exit(1);
}

// 5. LeadService - validation failure
echo "[5] LeadService validation failure...\n";
$result = $svc->process([
    'name'        => 'A',
    'city'        => '',
    'email'       => 'not-an-email',
    'phone'       => '12',
    'destination' => 'Fake Place',
    'website'     => '',
], '127.0.0.2', 'PHP CLI', '');

if (!$result['ok'] && !empty($result['errors'])) {
    echo "    OK (rejected with " . count($result['errors']) . " errors)\n";
} else {
    echo "    FAIL: should have been rejected\n";
    exit(1);
}

// 6. Honeypot
echo "[6] Honeypot detection...\n";
$result = $svc->process([
    'name'        => 'Bot',
    'city'        => 'BotCity',
    'email'       => 'bot@example.com',
    'phone'       => '9876543211',
    'destination' => 'Spiti Valley Circuit - 10 Days / 9 Nights',
    'website'     => 'http://spam.com', // BOT FILLED IT
], '127.0.0.3', 'bot/1.0', '');

if ($result['ok'] && !isset($result['lead']['id'])) {
    echo "    OK (bot given fake success, not persisted)\n";
} else {
    echo "    FAIL: bot was persisted or rejected\n";
    exit(1);
}

echo "\n=== All checks passed ===\n";