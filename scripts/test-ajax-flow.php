<?php
/**
 * Test the AJAX form submission path that the JS uses.
 * Usage: php scripts/test-ajax-flow.php
 */

$cookieFile = sys_get_temp_dir() . '/cookies_test_' . getmypid() . '.txt';

function req(string $method, string $url, array $post = null, bool $isAjax = false) {
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
        $opts[CURLOPT_POSTFIELDS] = http_build_query($post ?? []);
    }
    if ($isAjax) {
        $opts[CURLOPT_HTTPHEADER] = [
            'Content-Type: application/x-www-form-urlencoded',
            'X-Requested-With: XMLHttpRequest',
            'Accept: application/json',
        ];
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
echo "  CSRF: " . ($csrf ? substr($csrf, 0, 16) . "..." : "(missing)") . "\n";

if (!$csrf) {
    @unlink($cookieFile);
    exit(1);
}

echo "\n=== 2. POST /submit.php WITH AJAX HEADERS ===\n";
$ajax = req('POST', 'http://localhost:8080/mystical/submit.php', [
    '_csrf'          => $csrf,
    'name'           => 'AJAX Test',
    'city'           => 'Shimla',
    'email'          => 'ajax@example.com',
    'phone'          => '9876543210',
    'destination'    => 'Shimla Tour Package',
    'website'        => '',
    'contact_submit' => '1',
], true);

echo "  HTTP " . $ajax['status'] . "\n";
preg_match('/^Location:\s*(.+)$/im', $ajax['headers'], $lm);
echo "  Location: " . trim($lm[1] ?? '(none)') . "\n";
echo "  Body: " . substr($ajax['body'], 0, 300) . "\n";

echo "\n=== 3. What the JS will do with this response ===\n";
echo "  Server returns JSON (no Location redirect) for AJAX requests.\n";
echo "  The JS reads data.ok from JSON, then sets window.location.href\n";
echo "  to thanksUrl = new URL('thanks.php', window.location.href).href\n";
echo "  Which on http://localhost:8080/mystical/ resolves to:\n";
echo "    -> http://localhost:8080/mystical/thanks.php\n";

@unlink($cookieFile);
