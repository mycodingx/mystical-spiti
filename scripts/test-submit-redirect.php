<?php
/**
 * Simulates a browser form submission to verify the redirect URL.
 * Usage: php scripts/test-submit-redirect.php
 */

$cookieFile = __DIR__ . '/cookies.txt';

function req(string $method, string $url, array $post = []): array {
    global $cookieFile;
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($post);
        $opts[CURLOPT_HTTPHEADER] = ['Content-Type: application/x-www-form-urlencoded'];
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [
        'status'  => $info['http_code'],
        'headers' => substr((string) $body, 0, $info['header_size']),
        'body'    => substr((string) $body, $info['header_size']),
    ];
}

echo "=== 1. GET home page (capture session + CSRF) ===\n";
$home = req('GET', 'http://localhost:8080/mystical/');
preg_match('/name="_csrf"\s+value="([^"]+)"/', $home['body'], $m);
$csrf = $m[1] ?? '';
echo "  HTTP " . $home['status'] . "\n";
echo "  CSRF token: " . ($csrf ? substr($csrf, 0, 16) . "..." : "(missing)") . "\n";

if (!$csrf) {
    echo "\nFAIL: no CSRF token in HTML\n";
    @unlink($cookieFile);
    exit(1);
}

echo "\n=== 2. POST /submit.php (form submit) ===\n";
$r = req('POST', 'http://localhost:8080/mystical/submit.php', [
    '_csrf'          => $csrf,
    'name'           => 'Test User',
    'city'           => 'Shimla',
    'email'          => 'tester@example.com',
    'phone'          => '9876543210',
    'destination'    => 'Shimla Tour Package',
    'website'        => '',
    'contact_submit' => '1',
]);
echo "  HTTP " . $r['status'] . "\n";
preg_match('/^Location:\s*(.+)$/im', $r['headers'], $lm);
$loc = trim($lm[1] ?? '(none)');
echo "  Location: " . $loc . "\n";
echo "  Body (first 250): " . substr($r['body'], 0, 250) . "\n";

if ($loc === 'thanks.php' || str_ends_with($loc, '/thanks.php')) {
    echo "\nPASS: redirect target is valid\n";
} else {
    echo "\nFAIL: redirect target is '" . $loc . "', expected 'thanks.php'\n";
}

@unlink($cookieFile);
exit(0);
