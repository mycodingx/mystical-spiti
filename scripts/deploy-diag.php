<?php
/**
 * Mystical Expedition — Deployment Diagnostic Script
 * ===================================================
 * Upload this to public_html/shimla/diag.php on the server,
 * then visit https://shimla.mysticalexpedition.com/diag.php
 * and paste the entire output back for analysis.
 *
 * The script does NOT modify anything on the server. It only READS.
 * Safe to delete after use.
 */

declare(strict_types=1);

// Disable any output buffering, send plain text
header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

/**
 * Prints a section header.
 */
function section(string $title): void
{
    echo "\n";
    echo str_repeat('=', 70) . "\n";
    echo $title . "\n";
    echo str_repeat('=', 70) . "\n";
}

/**
 * Prints a labelled line; values are right-padded for readability.
 */
function row(string $label, string $value): void
{
    echo str_pad($label, 32) . $value . "\n";
}

/**
 * Prints PASS / FAIL / WARN for a single check.
 */
function check(string $name, bool $pass, string $detail = ''): void
{
    $tag = $pass ? '[PASS]' : '[FAIL]';
    echo str_pad($tag, 8) . $name;
    if ($detail !== '') {
        echo ' — ' . $detail;
    }
    echo "\n";
}

function warn(string $name, string $detail = ''): void
{
    echo str_pad('[WARN]', 8) . $name;
    if ($detail !== '') {
        echo ' — ' . $detail;
    }
    echo "\n";
}

function info(string $name, string $detail = ''): void
{
    echo str_pad('[INFO]', 8) . $name;
    if ($detail !== '') {
        echo ' — ' . $detail;
    }
    echo "\n";
}

// Resolve our real path so we can find files relative to the docroot.
$docroot = $_SERVER['DOCUMENT_ROOT'] ?? getcwd();
$docroot = rtrim($docroot, '/\\');

echo "Mystical Expedition — Server Diagnostic Report\n";
echo "Generated: " . date('Y-m-d H:i:s e') . "\n";
echo "DOCUMENT_ROOT: {$docroot}\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'n/a') . "\n";
echo "PHP_SAPI: " . PHP_SAPI . "\n";
echo "Hostname: " . gethostname() . "\n";

// -----------------------------------------------------------------------
section('1. PHP runtime');
// -----------------------------------------------------------------------
row('PHP version', PHP_VERSION);
row('Required (composer.json min)', '8.1');
$phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
check('PHP version >= 8.1', $phpOk);

row('display_errors', ini_get('display_errors'));
row('log_errors', ini_get('log_errors'));
row('error_reporting', (string) error_reporting());
row('memory_limit', ini_get('memory_limit'));
row('max_execution_time', ini_get('max_execution_time') . 's');
row('open_basedir', ini_get('open_basedir') ?: '(empty)');
row('upload_max_filesize', ini_get('upload_max_filesize'));
row('post_max_size', ini_get('post_max_size'));
row('allow_url_fopen', ini_get('allow_url_fopen'));

// Required PHP extensions for this app
$exts = ['pdo', 'pdo_sqlite', 'mbstring', 'openssl', 'json', 'curl'];
foreach ($exts as $ext) {
    check("Extension: {$ext}", extension_loaded($ext));
}

// -----------------------------------------------------------------------
section('2. DocumentRoot layout');
// -----------------------------------------------------------------------

// On Hostinger the docroot is public_html/shimla, so .htaccess sits inside it.
$htaccess = $docroot . '/.htaccess';
check('.htaccess exists at docroot', file_exists($htaccess),
    file_exists($htaccess) ? filesize($htaccess) . ' bytes, mode ' . substr(sprintf('%o', fileperms($htaccess)), -4) : 'NOT FOUND');

$indexPhp = $docroot . '/index.php';
check('index.php exists', file_exists($indexPhp));

$submitPhp = $docroot . '/submit.php';
check('submit.php exists', file_exists($submitPhp));

$thxPhp = $docroot . '/thanks.php';
check('thanks.php exists', file_exists($thxPhp));

$errPhp = $docroot . '/404.php';
check('404.php exists', file_exists($errPhp));

$errPhp5 = $docroot . '/500.php';
check('500.php exists', file_exists($errPhp5));

$appDir = $docroot . '/_app';
check('_app/ directory exists', is_dir($appDir));

if (is_dir($appDir)) {
    $appHt = $appDir . '/.htaccess';
    check('_app/.htaccess exists (deny rule)', file_exists($appHt),
        file_exists($appHt) ? substr(sprintf('%o', fileperms($appHt)), -4) : 'NOT FOUND — _app/ will be PUBLIC!');

    $vendor = $appDir . '/vendor/autoload.php';
    check('_app/vendor/autoload.php exists (composer install)', file_exists($vendor));
    check('_app/src/Bootstrap.php exists', file_exists($appDir . '/src/Bootstrap.php'));
    check('_app/views/pages/home.php exists', file_exists($appDir . '/views/pages/home.php'));
}

$assets = $docroot . '/assets';
check('assets/ directory exists', is_dir($assets),
    is_dir($assets) ? 'contents: ' . implode(', ', array_slice(scandir($assets), 2)) : 'MISSING');

// -----------------------------------------------------------------------
section('3. Permissions — files and directories');
// -----------------------------------------------------------------------

/**
 * Format perms as rwxrwxrwx + octal.
 */
function fmtPerms(string $path): string
{
    $oct = substr(sprintf('%o', fileperms($path)), -4);
    $p = fileperms($path);
    $s = '';
    $s .= ($p & 0x0100) ? 'r' : '-';
    $s .= ($p & 0x0080) ? 'w' : '-';
    $s .= ($p & 0x0040) ? (($p & 0x0800) ? 's' : 'x') : (($p & 0x0800) ? 'S' : '-');
    $s .= ($p & 0x0020) ? 'r' : '-';
    $s .= ($p & 0x0010) ? 'w' : '-';
    $s .= ($p & 0x0008) ? (($p & 0x0400) ? 's' : 'x') : (($p & 0x0400) ? 'S' : '-');
    $s .= ($p & 0x0004) ? 'r' : '-';
    $s .= ($p & 0x0002) ? 'w' : '-';
    $s .= ($p & 0x0001) ? 'x' : '-';
    return "{$s} (octal {$oct})";
}

function permsOwner(string $path): string
{
    $owner = @posix_getpwuid(fileowner($path));
    $group = @posix_getgrgid(filegroup($path));
    $ownerName = $owner['name'] ?? (string) fileowner($path);
    $groupName = $group['name'] ?? (string) filegroup($path);
    return "owner={$ownerName}:{$groupName}";
}

foreach ([
    $docroot . '/.htaccess',
    $docroot . '/index.php',
    $docroot . '/submit.php',
    $docroot,
] as $p) {
    if (file_exists($p)) {
        row($p, fmtPerms($p) . '  ' . permsOwner($p));
    }
}

// Check writable folders we need for runtime (data, logs)
$dataDir = $docroot . '/_app/data';
if (is_dir($dataDir)) {
    check('_app/data is writable (needed for leads.sqlite)', is_writable($dataDir),
        fmtPerms($dataDir) . '  ' . permsOwner($dataDir));
}

$logsDir = $docroot . '/_app/logs';
if (is_dir($logsDir)) {
    check('_app/logs is writable (needed for error.log)', is_writable($logsDir),
        fmtPerms($logsDir) . '  ' . permsOwner($logsDir));
}

// -----------------------------------------------------------------------
section('4. Sensitive file access from the web');
// -----------------------------------------------------------------------

// We can't fetch HTTP from inside a request to ourselves easily,
// so check file readability from CLI mode instead.
$sensitivePaths = [
    $docroot . '/.env',
    $docroot . '/_app/.env',
    $docroot . '/_app/.env.example',
    $docroot . '/_app/vendor/',
    $docroot . '/_app/src/',
    $docroot . '/_app/data/',
    $docroot . '/_app/composer.json',
    $docroot . '/_app/composer.lock',
    $docroot . '/_app/schema.sql',
];
foreach ($sensitivePaths as $p) {
    if (file_exists($p)) {
        check('Should NOT be web-readable: ' . basename(dirname($p)) . '/' . basename($p),
            true, 'EXISTS — confirm .htaccess deny is working');
    }
}

// -----------------------------------------------------------------------
section('5. .env configuration');
// -----------------------------------------------------------------------

$envPath = $docroot . '/_app/.env';
check('_app/.env exists', file_exists($envPath));
if (file_exists($envPath)) {
    row('Permissions', fmtPerms($envPath));
    // Parse simple KEY=VALUE lines, ignore comments and quotes
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $parsed = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $v = trim($v);
        if (
            (strlen($v) >= 2) &&
            ((($v[0] === '"') && (substr($v, -1) === '"')) ||
             (($v[0] === "'") && (substr($v, -1) === "'")))
        ) {
            $v = substr($v, 1, -1);
        }
        $parsed[trim($k)] = $v;
    }
    foreach (['APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_PATH', 'MAIL_HOST', 'MAIL_PORT',
              'MAIL_USERNAME', 'MAIL_FROM_ADDRESS', 'LEADS_NOTIFY_EMAIL'] as $key) {
        $val = $parsed[$key] ?? '(not set)';
        if ($key === 'MAIL_PASSWORD') continue; // never print secrets
        row($key, $val);
    }
    $passwordSet = !empty($parsed['MAIL_PASSWORD']);
    check('MAIL_PASSWORD is set', $passwordSet, $passwordSet ? '(length: ' . strlen($parsed['MAIL_PASSWORD']) . ', not shown)' : 'EMPTY!');

    $dbPath = $parsed['DB_PATH'] ?? 'data/leads.sqlite';
    if (!str_starts_with($dbPath, '/')) {
        $dbPath = $docroot . '/_app/' . $dbPath;
    }
    row('Resolved DB absolute path', $dbPath);
    row('DB dir exists', is_dir(dirname($dbPath)) ? 'yes' : 'NO');
    row('DB dir writable', is_dir(dirname($dbPath)) && is_writable(dirname($dbPath)) ? 'yes' : 'NO');
    row('DB file present', file_exists($dbPath) ? 'yes' : 'NO (will be auto-created)');
}

// -----------------------------------------------------------------------
section('6. .htaccess content (first 60 lines)');
// -----------------------------------------------------------------------
if (file_exists($htaccess)) {
    $lines = file($htaccess);
    $max = min(60, count($lines));
    for ($i = 0; $i < $max; $i++) {
        echo sprintf('%3d  %s', $i + 1, rtrim($lines[$i], "\r\n")) . "\n";
    }
    if (count($lines) > $max) {
        echo "... (" . (count($lines) - $max) . " more lines)\n";
    }
    echo "\n";
    // Search for ErrorDocument lines specifically
    foreach ($lines as $n => $line) {
        if (stripos($line, 'ErrorDocument') !== false) {
            row("Line " . ($n + 1) . ":", trim($line));
        }
    }
} else {
    warn('.htaccess is missing');
}

// -----------------------------------------------------------------------
section('7. Composer / vendor state');
// -----------------------------------------------------------------------
$composerLock = $docroot . '/_app/composer.lock';
check('_app/composer.lock exists', file_exists($composerLock));

if (file_exists($composerLock)) {
    $lock = json_decode(file_get_contents($composerLock), true);
    $phpReq = $lock['platform-overrides']['php'] ?? ($lock['platform']['php'] ?? 'n/a');
    row('Lock file PHP requirement', $phpReq);
    row('Total packages', isset($lock['packages']) ? (string) count($lock['packages']) : 'n/a');
}

$vendorDir = $docroot . '/_app/vendor';
check('_app/vendor/ exists', is_dir($vendorDir));
if (is_dir($vendorDir)) {
    $vendorHtaccess = $vendorDir . '/.htaccess';
    check('_app/vendor/.htaccess exists (deny from web)', file_exists($vendorHtaccess));
    $autoload = $vendorDir . '/autoload.php';
    check('vendor/autoload.php readable', is_readable($autoload),
        is_readable($autoload) ? (string) filesize($autoload) . ' bytes' : 'NOT READABLE');
}

$composerBin = trim(shell_exec('command -v composer 2>/dev/null') ?? '');
if ($composerBin === '') {
    $composerBin = trim(shell_exec('command -v composer.phar 2>/dev/null') ?? '');
}
info('composer binary', $composerBin === '' ? 'NOT IN PATH' : $composerBin);
$phpBin = trim(shell_exec('command -v php 2>/dev/null') ?? 'php');
info('php binary in SSH', $phpBin);

// -----------------------------------------------------------------------
section('8. PHP Composer autoload sanity check');
// -----------------------------------------------------------------------
$autoloadPath = $docroot . '/_app/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    try {
        require $autoloadPath;
        check('Composer autoload loaded', true);
        check('PHPMailer class available',
            class_exists(\PHPMailer\PHPMailer\PHPMailer::class),
            class_exists(\PHPMailer\PHPMailer\PHPMailer::class) ? 'yes' : 'NO');
        check('Dotenv class available',
            class_exists(\Dotenv\Dotenv::class),
            class_exists(\Dotenv\Dotenv::class) ? 'yes' : 'NO');
    } catch (\Throwable $e) {
        check('Composer autoload loaded', false, $e->getMessage());
    }
} else {
    warn('Skipped — vendor/autoload.php missing. Run composer install on the server.');
}

// -----------------------------------------------------------------------
section('9. Database reachability');
// -----------------------------------------------------------------------
$dbRel = 'data/leads.sqlite';
if (file_exists($docroot . '/_app/.env')) {
    $envLines = file($docroot . '/_app/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (str_starts_with(trim($line), 'DB_PATH=')) {
            $dbRel = trim(explode('=', $line, 2)[1]);
            $dbRel = trim($dbRel, "\"' \t\n\r\0\x0B");
            break;
        }
    }
}
$dbAbs = str_starts_with($dbRel, '/') ? $dbRel : $docroot . '/_app/' . $dbRel;
row('DB_PATH (relative)', $dbRel);
row('DB_PATH (absolute)', $dbAbs);

try {
    $pdo = new PDO('sqlite:' . $dbAbs);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $count = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
    check('SQLite open + leads table queryable', true, 'leads row count: ' . $count);
} catch (\Throwable $e) {
    check('SQLite open + leads table queryable', false, $e->getMessage());
}

// -----------------------------------------------------------------------
section('10. Quick PHP execution test');
// -----------------------------------------------------------------------
try {
    ob_start();
    $marker = 'OK_' . bin2hex(random_bytes(4));
    echo $marker;
    $out = ob_get_clean();
    check('PHP can write output to response', $out === $marker, 'marker roundtrip: ' . ($out === $marker ? 'match' : "expected={$marker} got={$out}"));
} catch (\Throwable $e) {
    check('PHP can write output to response', false, $e->getMessage());
}

// Test session_start works
try {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    check('session_start() works', session_status() === PHP_SESSION_ACTIVE);
} catch (\Throwable $e) {
    check('session_start() works', false, $e->getMessage());
}

// Test sqlite write
try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE t (a INTEGER)');
    $pdo->exec('INSERT INTO t VALUES (1)');
    $v = (int) $pdo->query('SELECT a FROM t')->fetchColumn();
    check('In-memory SQLite write works', $v === 1);
} catch (\Throwable $e) {
    check('In-memory SQLite write works', false, $e->getMessage());
}

// Test mail() function exists
check('mail() function available (fallback)', function_exists('mail'));

// -----------------------------------------------------------------------
section('11. Apache modules (best-effort)');
// -----------------------------------------------------------------------
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $want = ['mod_rewrite', 'mod_headers', 'mod_deflate', 'mod_expires',
             'mod_authz_core', 'mod_php', 'mod_ssl'];
    foreach ($want as $m) {
        check($m, in_array($m, $modules, true));
    }
} else {
    info('apache_get_modules() not available', 'probably running under CGI/FPM');
}

echo "\n";
echo str_repeat('=', 70) . "\n";
echo "End of report. Copy everything above and paste it back.\n";
echo "DELETE THIS FILE FROM THE SERVER when done!\n";
echo str_repeat('=', 70) . "\n";
