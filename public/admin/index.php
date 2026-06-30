<?php
/**
 * Mystical Expedition â€“ Admin Panel
 *
 * Multi-user, role-based SQLite admin.
 * Roles: admin (full access + user management) | viewer (query only)
 *
 * First visit: setup wizard creates the master admin account.
 */
declare(strict_types=1);

require_once __DIR__ . '/../_app/vendor/autoload.php';
require_once __DIR__ . '/../_app/src/Bootstrap.php';

use Mystical\Bootstrap;
use Mystical\Database;

Bootstrap::init();   // starts session, loads .env, runs migrations

$pdo = Database::getInstance();

// â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_verify(): bool {
    return isset($_POST['_csrf']) && hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf']);
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function current_url(array $params = []): string {
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    return $params ? $base . '?' . http_build_query($params) : $base;
}

// â”€â”€ Auth helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function auth_user(): ?array { return $_SESSION['admin_user'] ?? null; }
function is_admin(): bool    { return (auth_user()['role'] ?? '') === 'admin'; }
function require_auth(): void {
    if (!auth_user()) redirect(current_url(['page' => 'login']));
}
function require_admin(): void {
    require_auth();
    if (!is_admin()) redirect(current_url(['page' => 'leads']));
}

// â”€â”€ DB user helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function count_admin_users(PDO $pdo): int {
    return (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
}

function find_user_by_username(PDO $pdo, string $username): ?array {
    $st = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? COLLATE NOCASE');
    $st->execute([$username]);
    $row = $st->fetch();
    return $row ?: null;
}

function get_all_users(PDO $pdo): array {
    return $pdo->query(
        'SELECT u.*, c.username AS created_by_name
         FROM admin_users u
         LEFT JOIN admin_users c ON c.id = u.created_by
         ORDER BY u.created_at ASC'
    )->fetchAll();
}

function touch_last_login(PDO $pdo, int $id): void {
    $pdo->prepare("UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?")
        ->execute([$id]);
}

// â”€â”€ Page routing â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$page = $_GET['page'] ?? (auth_user() ? (is_admin() ? 'query' : 'leads') : 'query');

// Viewers cannot access query runner or users page
if (auth_user() && !is_admin() && in_array($page, ['query', 'users'])) {
    redirect(current_url(['page' => 'leads']));
}

// Force setup if no users yet
if (count_admin_users($pdo) === 0 && $page !== 'setup') {
    redirect(current_url(['page' => 'setup']));
}

$flash   = $_SESSION['flash'] ?? null;
$error   = null;
$success = null;
unset($_SESSION['flash']);

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ACTION: Setup (first run)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if ($page === 'setup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (count_admin_users($pdo) > 0) redirect(current_url(['page' => 'login']));
    if (!csrf_verify()) { $error = 'Invalid request.'; }
    else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm']  ?? '';

        if (strlen($username) < 3)       $error = 'Username must be at least 3 characters.';
        elseif (strlen($password) < 8)   $error = 'Password must be at least 8 characters.';
        elseif ($password !== $confirm)  $error = 'Passwords do not match.';
        else {
            $pdo->prepare('INSERT INTO admin_users (username, password_hash, role) VALUES (?, ?, ?)')
                ->execute([$username, password_hash($password, PASSWORD_BCRYPT), 'admin']);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Master admin Â«$usernameÂ» created. Please log in."];
            redirect(current_url(['page' => 'login']));
        }
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ACTION: Login
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { $error = 'Invalid request.'; }
    else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $user     = find_user_by_username($pdo, $username);
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_user'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ];
            touch_last_login($pdo, (int)$user['id']);
            $dest = $user['role'] === 'admin' ? 'query' : 'leads';
            redirect(current_url(['page' => $dest]));
        }
        $error = 'Invalid username or password.';
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ACTION: Logout
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if ($page === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_verify()) {
        session_destroy();
        redirect(current_url(['page' => 'login']));
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ACTION: Add user
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if ($page === 'users' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    require_admin();
    if (!csrf_verify()) { $error = 'Invalid request.'; }
    else {
        $username = trim($_POST['new_username'] ?? '');
        $password = $_POST['new_password'] ?? '';
        $role     = in_array($_POST['new_role'] ?? '', ['admin', 'viewer']) ? $_POST['new_role'] : 'viewer';

        if (strlen($username) < 3)     $error = 'Username must be at least 3 characters.';
        elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
        elseif (find_user_by_username($pdo, $username)) $error = "Username Â«$usernameÂ» is already taken.";
        else {
            $pdo->prepare('INSERT INTO admin_users (username, password_hash, role, created_by) VALUES (?, ?, ?, ?)')
                ->execute([$username, password_hash($password, PASSWORD_BCRYPT), $role, auth_user()['id']]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "User Â«$usernameÂ» added."];
            redirect(current_url(['page' => 'users']));
        }
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ACTION: Delete user
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if ($page === 'users' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    require_admin();
    if (!csrf_verify()) { $error = 'Invalid request.'; }
    else {
        $del_id = (int)($_POST['del_id'] ?? 0);
        if ($del_id === (int)(auth_user()['id'] ?? 0)) {
            $error = 'You cannot delete your own account.';
        } else {
            $pdo->prepare('DELETE FROM admin_users WHERE id = ?')->execute([$del_id]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'User removed.'];
            redirect(current_url(['page' => 'users']));
        }
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// ACTION: Change password (own account)
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if ($page === 'users' && isset($_POST['action']) && $_POST['action'] === 'change_pw') {
    require_auth();
    if (!csrf_verify()) { $error = 'Invalid request.'; }
    else {
        $new_pw  = $_POST['new_pw']  ?? '';
        $confirm = $_POST['confirm_pw'] ?? '';
        if (strlen($new_pw) < 8)    $error = 'Password must be at least 8 characters.';
        elseif ($new_pw !== $confirm) $error = 'Passwords do not match.';
        else {
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new_pw, PASSWORD_BCRYPT), auth_user()['id']]);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password updated successfully.'];
            redirect(current_url(['page' => 'users']));
        }
    }
}

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// Guard remaining pages
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if (!in_array($page, ['login', 'setup'])) {
    require_auth();
}

// â”€â”€ Query runner data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$queryResult  = null;
$queryError   = null;
$queryTime    = null;
$affectedRows = null;
$sql          = '';

$quickQueries = [
    'All Leads'      => 'SELECT * FROM leads ORDER BY created_at DESC LIMIT 50',
    'New Leads'      => "SELECT * FROM leads WHERE status = 'new' ORDER BY created_at DESC",
    'Leads Today'    => "SELECT * FROM leads WHERE date(created_at) = date('now') ORDER BY created_at DESC",
    'By Destination' => 'SELECT destination, COUNT(*) AS total FROM leads GROUP BY destination ORDER BY total DESC',
    'By Status'      => 'SELECT status, COUNT(*) AS total FROM leads GROUP BY status',
];

// ── Leads dashboard data (viewer page) ───────────────────────────────────────
$leadsFilter = $_GET['filter'] ?? 'all';
$leadsSearch = trim($_GET['search'] ?? '');

$leadsFilters = [
    'all'     => ['label' => 'All Leads',    'icon' => 'list',           'sql' => '1=1'],
    'new'     => ['label' => 'New',          'icon' => 'bell',           'sql' => "status = 'new'"],
    'today'   => ['label' => 'Today',        'icon' => 'calendar-day',   'sql' => "date(created_at) = date('now')"],
    'week'    => ['label' => 'This Week',    'icon' => 'calendar-week',  'sql' => "created_at >= datetime('now', '-7 days')"],
    'called'  => ['label' => 'Called',       'icon' => 'phone-volume',   'sql' => "status = 'called'"],
    'booked'  => ['label' => 'Booked',       'icon' => 'circle-check',   'sql' => "status = 'booked'"],
];

$leadsData = [];
$leadsCounts = [];
if ($page === 'leads') {
    // Counts for filter tabs
    foreach ($leadsFilters as $key => $f) {
        $leadsCounts[$key] = (int)$pdo->query("SELECT COUNT(*) FROM leads WHERE {$f['sql']}")->fetchColumn();
    }
    // Main query
    $where = $leadsFilters[$leadsFilter]['sql'] ?? '1=1';
    $params = [];
    if ($leadsSearch !== '') {
        $where .= " AND (name LIKE :s OR phone LIKE :s OR email LIKE :s OR destination LIKE :s OR city LIKE :s)";
        $params[':s'] = "%$leadsSearch%";
    }
    $st = $pdo->prepare("SELECT id, name, city, phone, email, destination, status, created_at FROM leads WHERE $where ORDER BY created_at DESC LIMIT 200");
    $st->execute($params);
    $leadsData = $st->fetchAll();
}

if ($page === 'query' && isset($_POST['run_query'])) {
    if (!csrf_verify()) { $queryError = 'Invalid CSRF token.'; }
    else {
        $sql = trim($_POST['sql'] ?? '');
        if ($sql !== '') {
            try {
                $start       = microtime(true);
                $stmt        = $pdo->query($sql);
                $queryTime   = round((microtime(true) - $start) * 1000, 2);
                $queryResult = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                $affectedRows = $stmt ? $stmt->rowCount() : 0;
            } catch (Throwable $e) {
                $queryError = $e->getMessage();
            }
        }
    }
}

// â”€â”€ Tables list for sidebar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$tables = [];
try {
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")
                  ->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable) {}

if ($flash) {
    [$flashType, $flashMsg] = [$flash['type'], $flash['msg']];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin â€“ Mystical Expedition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:        #0a1422;
            --surface:   #121e30;
            --surface2:  #0d1828;
            --border:    rgba(255,255,255,0.07);
            --accent:    #c9531a;
            --accent-lt: rgba(201,83,26,0.13);
            --blue:      #1e4d8c;
            --text:      rgba(255,255,255,0.85);
            --muted:     rgba(255,255,255,0.4);
            --success:   #22c55e;
            --danger:    #ef4444;
            --radius:    10px;
            --radius-lg: 16px;
        }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; font-size: 14px; line-height: 1.6; }

        /* â”€â”€ Shared form elements â”€â”€ */
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 11px; font-weight: 600; color: var(--muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control {
            width: 100%; padding: 9px 13px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: #fff; font-size: 13.5px; font-family: 'Poppins', sans-serif;
            outline: none; transition: border-color .2s;
        }
        .form-control:focus { border-color: var(--accent); }
        select.form-control option { background: #121e30; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border: none; border-radius: var(--radius); font-size: 13px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; transition: opacity .2s, transform .15s; text-decoration: none; white-space: nowrap; }
        .btn:hover { opacity: 0.88; }
        .btn-accent { background: linear-gradient(135deg, var(--accent), #e8a020); color: #fff; box-shadow: 0 3px 10px rgba(201,83,26,0.35); }
        .btn-muted  { background: rgba(255,255,255,0.06); border: 1px solid var(--border); color: var(--muted); }
        .btn-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: #fca5a5; }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }
        .badge { display: inline-block; padding: 2px 9px; border-radius: 100px; font-size: 11px; font-weight: 600; }
        .badge-admin  { background: rgba(201,83,26,0.15); border: 1px solid rgba(201,83,26,0.3); color: #f4a87a; }
        .badge-viewer { background: rgba(30,77,140,0.15); border: 1px solid rgba(30,77,140,0.3); color: #7ab4f5; }
        .alert { padding: 10px 14px; border-radius: var(--radius); font-size: 13px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 8px; }
        .alert-danger  { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); color: #fca5a5; }
        .alert-success { background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.25); color: #86efac; }

        /* â”€â”€ Login / Setup â”€â”€ */
        .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .auth-card {
            background: var(--surface); border: 1px solid var(--border);
            border-top: 3px solid var(--accent); border-radius: var(--radius-lg);
            padding: 44px 40px; width: 100%; max-width: 420px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.5);
        }
        .auth-logo { font-family: 'Cinzel', serif; font-size: 1rem; font-weight: 700; color: #fff; letter-spacing: 1px; }
        .auth-sub  { font-size: 11.5px; color: var(--muted); margin-top: 2px; margin-bottom: 28px; }
        .auth-card h2 { font-family: 'Cinzel', serif; font-size: 1.25rem; color: #fff; margin-bottom: 20px; }

        /* â”€â”€ Layout â”€â”€ */
        .layout { display: flex; height: 100vh; overflow: hidden; }
        .sidebar {
            width: 230px; flex-shrink: 0;
            background: var(--surface2); border-right: 1px solid var(--border);
            display: flex; flex-direction: column; overflow-y: auto;
        }
        .sidebar-header { padding: 18px 16px 14px; border-bottom: 1px solid var(--border); }
        .sidebar-logo { font-family: 'Cinzel', serif; font-size: 0.9rem; font-weight: 700; color: #fff; }
        .sidebar-user { font-size: 11px; color: var(--muted); margin-top: 4px; display: flex; align-items: center; gap: 5px; }
        .sidebar-section { padding: 14px 12px 6px; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; }
        .nav-link {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 12px; margin: 2px 6px;
            border-radius: var(--radius); font-size: 13px;
            color: rgba(255,255,255,0.6); text-decoration: none;
            transition: background .15s, color .15s;
        }
        .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-link.active { background: var(--accent-lt); color: #fff; }
        .nav-link i { width: 16px; color: var(--accent); font-size: 12px; }
        .sidebar-divider { height: 1px; background: var(--border); margin: 8px 12px; }
        .sidebar-tbl-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 12px; margin: 1px 6px;
            border-radius: var(--radius); font-size: 12px;
            font-family: 'JetBrains Mono', monospace;
            color: rgba(255,255,255,0.5);
            background: none; border: none; cursor: pointer; text-align: left; width: calc(100% - 12px);
            transition: background .15s, color .15s;
        }
        .sidebar-tbl-btn:hover { background: var(--accent-lt); color: #fff; }
        .sidebar-tbl-btn i { color: var(--accent); font-size: 10px; width: 12px; }
        .sidebar-footer { margin-top: auto; padding: 12px; border-top: 1px solid var(--border); }

        /* â”€â”€ Main â”€â”€ */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .topbar {
            padding: 13px 20px; border-bottom: 1px solid var(--border);
            background: var(--surface2); display: flex; align-items: center; gap: 10px; flex-shrink: 0;
        }
        .topbar h1 { font-family: 'Cinzel', serif; font-size: 0.95rem; color: #fff; }
        .topbar-badge { background: var(--accent-lt); border: 1px solid rgba(201,83,26,0.3); color: #f4a87a; font-size: 11px; padding: 2px 10px; border-radius: 100px; }
        .content { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 16px; }

        /* â”€â”€ Query panel â”€â”€ */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
        .card-header { padding: 11px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.65); }
        .card-header i { color: var(--accent); }
        .quick-bar { display: flex; flex-wrap: wrap; gap: 7px; padding: 11px 16px; border-bottom: 1px solid var(--border); }
        .quick-q { padding: 3px 11px; background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 100px; color: rgba(255,255,255,0.55); font-size: 11.5px; font-family: 'Poppins', sans-serif; cursor: pointer; transition: all .15s; white-space: nowrap; }
        .quick-q:hover { background: var(--accent-lt); border-color: rgba(201,83,26,0.3); color: #fff; }
        textarea.sql-input {
            width: 100%; min-height: 110px; padding: 14px 16px;
            background: #080f1c; border: none; color: #e2e8f0;
            font-family: 'JetBrains Mono', monospace; font-size: 13px; line-height: 1.7;
            resize: vertical; outline: none; display: block;
        }
        .query-actions { padding: 10px 16px; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
        .query-hint { margin-left: auto; font-size: 11px; color: var(--muted); }
        kbd { background: rgba(255,255,255,0.07); border: 1px solid var(--border); border-radius: 4px; padding: 1px 5px; font-size: 11px; font-family: 'JetBrains Mono', monospace; }

        /* Results */
        .results-header { padding: 11px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 9px; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.65); }
        .results-header i { color: var(--accent); }
        .results-meta { margin-left: auto; display: flex; gap: 10px; }
        .results-meta span { font-size: 11px; color: var(--muted); }
        .error-box { padding: 14px 16px; margin: 0; background: rgba(239,68,68,0.07); color: #fca5a5; font-family: 'JetBrains Mono', monospace; font-size: 12.5px; line-height: 1.6; }
        .table-wrap { overflow-x: auto; }
        table.results { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        table.results th { padding: 9px 13px; background: #080f1c; color: var(--muted); font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
        table.results td { padding: 8px 13px; border-bottom: 1px solid rgba(255,255,255,0.04); color: var(--text); max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: 'JetBrains Mono', monospace; cursor: pointer; }
        table.results tr:last-child td { border-bottom: none; }
        table.results tr:hover td { background: rgba(255,255,255,0.025); }
        table.results td:first-child { color: rgba(201,83,26,0.9); font-weight: 600; }
        .empty-state { padding: 28px; text-align: center; color: var(--muted); font-size: 13px; }

        /* â”€â”€ Users table â”€â”€ */
        table.users-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.users-tbl th { padding: 10px 14px; background: #080f1c; color: var(--muted); font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border-bottom: 1px solid var(--border); }
        table.users-tbl td { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        table.users-tbl tr:last-child td { border-bottom: none; }
        table.users-tbl tr:hover td { background: rgba(255,255,255,0.02); }
        .you-tag { font-size: 10px; color: var(--muted); margin-left: 6px; }

        /* Two-column grid for users page forms */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 700px) { .form-grid { grid-template-columns: 1fr; } }
        .card-body { padding: 18px 20px; }

        /* ── Leads dashboard ── */
        .filter-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 4px; }
        .filter-tab {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 100px;
            font-size: 12.5px; font-weight: 600; font-family: 'Poppins', sans-serif;
            text-decoration: none; border: 1px solid var(--border);
            color: rgba(255,255,255,0.55); background: rgba(255,255,255,0.04);
            transition: all .15s; white-space: nowrap;
        }
        .filter-tab:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .filter-tab.active { background: var(--accent-lt); border-color: rgba(201,83,26,0.35); color: #fff; }
        .filter-tab .tab-count { background: rgba(255,255,255,0.1); border-radius: 100px; padding: 1px 7px; font-size: 11px; margin-left: 2px; }
        .filter-tab.active .tab-count { background: rgba(201,83,26,0.25); }

        .search-bar { display: flex; gap: 8px; }
        .search-input {
            flex: 1; padding: 9px 13px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: var(--radius); color: #fff; font-size: 13px;
            font-family: 'Poppins', sans-serif; outline: none; transition: border-color .2s;
        }
        .search-input:focus { border-color: var(--accent); }
        .search-input::placeholder { color: var(--muted); }

        table.leads-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.leads-tbl th { padding: 10px 14px; background: #080f1c; color: var(--muted); font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
        table.leads-tbl td { padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        table.leads-tbl tr:last-child td { border-bottom: none; }
        table.leads-tbl tr:hover td { background: rgba(255,255,255,0.025); }
        table.leads-tbl td.id-col { color: var(--muted); font-size: 11px; width: 40px; }
        table.leads-tbl td.name-col { font-weight: 600; color: #fff; }
        table.leads-tbl td.dest-col { color: #9ec4f5; }
        table.leads-tbl td.date-col { color: var(--muted); font-size: 11.5px; font-family: 'JetBrains Mono', monospace; }
        .status-pill { display: inline-block; padding: 2px 9px; border-radius: 100px; font-size: 11px; font-weight: 600; }
        .status-new    { background: rgba(201,83,26,0.15); border: 1px solid rgba(201,83,26,0.3);  color: #f4a87a; }
        .status-called { background: rgba(30,77,140,0.15);  border: 1px solid rgba(30,77,140,0.3); color: #7ab4f5; }
        .status-booked { background: rgba(34,197,94,0.12);  border: 1px solid rgba(34,197,94,0.3); color: #86efac; }
        .status-other  { background: rgba(255,255,255,0.05); border: 1px solid var(--border);       color: var(--muted); }
        .action-links { display: flex; gap: 8px; }
        .action-links a { color: var(--muted); font-size: 13px; transition: color .15s; }
        .action-links a:hover { color: #fff; }
        .action-links a.wa:hover  { color: #4ade80; }
        .action-links a.tel:hover { color: #7ab4f5; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    </style>
</head>
<body>

<?php // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// SETUP PAGE
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
if ($page === 'setup'): ?>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">â›° Mystical Expedition</div>
        <div class="auth-sub">Admin Panel Â· First-Time Setup</div>
        <h2>Create Master Admin</h2>
        <?php if ($error): ?><div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i><?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= e($_POST['username'] ?? '') ?>" autofocus required minlength="3" placeholder="e.g. admin">
            </div>
            <div class="form-group">
                <label>Password <span style="color:var(--muted);font-weight:400">(min 8 chars)</span></label>
                <input type="password" name="password" class="form-control" required minlength="8" placeholder="Strong password">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" class="form-control" required placeholder="Repeat password">
            </div>
            <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center;padding:11px">
                <i class="fa-solid fa-user-shield"></i> Create Master Admin
            </button>
        </form>
    </div>
</div>

<?php // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// LOGIN PAGE
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
elseif ($page === 'login'): ?>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">â›° Mystical Expedition</div>
        <div class="auth-sub">Admin Panel Â· Sign In</div>
        <h2>Welcome Back</h2>
        <?php if (!empty($flashMsg)): ?><div class="alert alert-<?= e($flashType) ?>"><i class="fa-solid fa-circle-check"></i> <?= e($flashMsg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" autofocus required autocomplete="username" placeholder="Your username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Your password">
            </div>
            <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center;padding:11px">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>
    </div>
</div>

<?php // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// MAIN ADMIN LAYOUT
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
else:
    $user = auth_user();
?>
<div class="layout">

    <!-- â”€â”€ Sidebar â”€â”€ -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">â›° ME Admin</div>
            <div class="sidebar-user">
                <i class="fa-solid fa-circle-user"></i>
                <?= e($user['username']) ?>
                <span class="badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-viewer' ?>"><?= e($user['role']) ?></span>
            </div>
        </div>

        <div class="sidebar-section">Navigation</div>
        <a href="?page=leads" class="nav-link <?= $page === 'leads' ? 'active' : '' ?>">
            <i class="fa-solid fa-inbox"></i> Leads
        </a>
        <?php if (is_admin()): ?>
        <a href="?page=query" class="nav-link <?= $page === 'query' ? 'active' : '' ?>">
            <i class="fa-solid fa-terminal"></i> Query Runner
        </a>
        <a href="?page=users" class="nav-link <?= $page === 'users' ? 'active' : '' ?>">
            <i class="fa-solid fa-users-gear"></i> Manage Users
        </a>
        <?php endif; ?>
        <a href="/" target="_blank" class="nav-link">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> View Website
        </a>

        <?php if (is_admin()): ?>
        <div class="sidebar-divider"></div>
        <div class="sidebar-section">Quick Queries</div>
        <?php foreach ($quickQueries as $label => $q): ?>
            <button type="button" class="sidebar-tbl-btn" onclick="setQuery(<?= htmlspecialchars(json_encode($q), ENT_QUOTES) ?>)">
                <i class="fa-solid fa-bolt"></i> <?= e($label) ?>
            </button>
        <?php endforeach; ?>

        <?php if (!empty($tables)): ?>
        <div class="sidebar-divider"></div>
        <div class="sidebar-section">Tables</div>
        <?php foreach ($tables as $tbl): ?>
            <button type="button" class="sidebar-tbl-btn" onclick="setQuery('SELECT * FROM <?= e($tbl) ?> LIMIT 50')">
                <i class="fa-solid fa-table"></i> <?= e($tbl) ?>
            </button>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>

        <div class="sidebar-footer">
            <form method="POST" action="?page=logout">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- â”€â”€ Main â”€â”€ -->
    <div class="main">
        <div class="topbar">
            <?php if ($page === 'leads'): ?>
                <h1><i class="fa-solid fa-inbox" style="color:var(--accent);margin-right:8px"></i>Leads</h1>
                <span class="topbar-badge"><?= $leadsCounts[$leadsFilter] ?? 0 ?> records</span>
            <?php elseif ($page === 'query'): ?>
                <h1><i class="fa-solid fa-terminal" style="color:var(--accent);margin-right:8px"></i>Query Runner</h1>
                <span class="topbar-badge">SQLite</span>
            <?php elseif ($page === 'users'): ?>
                <h1><i class="fa-solid fa-users-gear" style="color:var(--accent);margin-right:8px"></i>Manage Users</h1>
                <span class="topbar-badge">Admin Only</span>
            <?php endif; ?>
        </div>

        <div class="content">

        <?php if (!empty($flashMsg)): ?>
            <div class="alert alert-<?= e($flashType) ?>">
                <i class="fa-solid fa-<?= $flashType === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
                <?= e($flashMsg) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div>
        <?php endif; ?>

        <!-- LEADS PAGE -->
        <?php if ($page === 'leads'): ?>

            <div class="filter-tabs">
                <?php foreach ($leadsFilters as $key => $f): ?>
                <a href="?page=leads&filter=<?= e($key) ?><?= $leadsSearch ? '&search='.urlencode($leadsSearch) : '' ?>"
                   class="filter-tab <?= $leadsFilter === $key ? 'active' : '' ?>">
                    <i class="fa-solid fa-<?= e($f['icon']) ?>"></i>
                    <?= e($f['label']) ?>
                    <span class="tab-count"><?= $leadsCounts[$key] ?? 0 ?></span>
                </a>
                <?php endforeach; ?>
            </div>

            <form method="GET" action="" class="search-bar">
                <input type="hidden" name="page" value="leads">
                <input type="hidden" name="filter" value="<?= e($leadsFilter) ?>">
                <input type="text" name="search" class="search-input"
                       placeholder="Search name, phone, email, destination, city..."
                       value="<?= e($leadsSearch) ?>">
                <button type="submit" class="btn btn-accent"><i class="fa-solid fa-search"></i> Search</button>
                <?php if ($leadsSearch): ?>
                <a href="?page=leads&filter=<?= e($leadsFilter) ?>" class="btn btn-muted">Clear</a>
                <?php endif; ?>
            </form>

            <div class="card">
                <div class="table-wrap">
                    <?php if (empty($leadsData)): ?>
                        <div class="empty-state"><i class="fa-solid fa-inbox" style="margin-right:6px;color:var(--accent)"></i>No leads found.</div>
                    <?php else: ?>
                    <table class="leads-tbl">
                        <thead><tr><th>#</th><th>Name</th><th>City</th><th>Phone</th><th>Email</th><th>Destination</th><th>Status</th><th>Date</th><th>Contact</th></tr></thead>
                        <tbody>
                        <?php foreach ($leadsData as $lead):
                            $statusClass = match($lead['status']) { 'new'=>'status-new','called'=>'status-called','booked'=>'status-booked',default=>'status-other' };
                            $wa = Bootstrap::config('BUSINESS_WHATSAPP','918219000937');
                            $waMsg = urlencode("Hi {$lead['name']}, this is Mystical Expedition regarding your enquiry for {$lead['destination']}.");
                        ?>
                        <tr>
                            <td class="id-col"><?= (int)$lead['id'] ?></td>
                            <td class="name-col" title="<?= e($lead['name']) ?>"><?= e($lead['name']) ?></td>
                            <td><?= e($lead['city']) ?></td>
                            <td style="font-family:'JetBrains Mono',monospace;font-size:12px"><?= e($lead['phone']) ?></td>
                            <td title="<?= e($lead['email']) ?>" style="color:var(--muted);font-size:12px;max-width:150px;overflow:hidden;text-overflow:ellipsis"><?= e($lead['email']) ?></td>
                            <td class="dest-col" title="<?= e($lead['destination']) ?>"><?= e($lead['destination']) ?></td>
                            <td><span class="status-pill <?= $statusClass ?>"><?= e($lead['status']) ?></span></td>
                            <td class="date-col"><?= e(substr($lead['created_at'],0,16)) ?></td>
                            <td>
                                <div class="action-links">
                                    <a href="tel:<?= e($lead['phone']) ?>" class="tel" title="Call"><i class="fa-solid fa-phone"></i></a>
                                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= $waMsg ?>" target="_blank" rel="noopener" class="wa" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                                    <a href="mailto:<?= e($lead['email']) ?>" title="Email"><i class="fa-solid fa-envelope"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>



        <!-- â”€â”€ QUERY PAGE â”€â”€ -->
        <?php elseif ($page === 'query'): ?>

            <div class="card">
                <div class="card-header"><i class="fa-solid fa-code"></i> SQL Query</div>
                <div class="quick-bar">
                    <?php foreach ($quickQueries as $label => $q): ?>
                        <button type="button" class="quick-q" onclick="setQuery(<?= htmlspecialchars(json_encode($q), ENT_QUOTES) ?>"><?= e($label) ?></button>
                    <?php endforeach; ?>
                </div>
                <form method="POST" action="?page=query" id="queryForm">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                    <textarea name="sql" class="sql-input" id="sqlInput" spellcheck="false"><?= e($sql) ?></textarea>
                    <div class="query-actions">
                        <button type="submit" name="run_query" class="btn btn-accent">
                            <i class="fa-solid fa-play"></i> Run Query
                        </button>
                        <button type="button" class="btn btn-muted" onclick="document.getElementById('sqlInput').value=''">Clear</button>
                        <span class="query-hint"><kbd>Ctrl</kbd>+<kbd>Enter</kbd> to run</span>
                    </div>
                </form>
            </div>

            <?php if ($queryResult !== null || $queryError !== null): ?>
            <div class="card">
                <div class="results-header">
                    <?php if ($queryError): ?>
                        <i class="fa-solid fa-circle-exclamation" style="color:var(--danger)"></i> Error
                    <?php else: ?>
                        <i class="fa-solid fa-table-list"></i> Results
                        <div class="results-meta">
                            <span><?= $queryTime ?>ms</span>
                            <span><?= count($queryResult ?? []) ?> row<?= count($queryResult ?? []) !== 1 ? 's' : '' ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($queryError): ?>
                    <div class="error-box"><?= e($queryError) ?></div>
                <?php elseif (empty($queryResult)): ?>
                    <div class="empty-state"><i class="fa-solid fa-circle-check" style="color:var(--success);margin-right:6px"></i>
                        Query completed â€” no rows returned<?= $affectedRows ? " Â· $affectedRows row(s) affected" : '' ?>.</div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="results">
                            <thead><tr><?php foreach (array_keys($queryResult[0]) as $col): ?><th><?= e($col) ?></th><?php endforeach; ?></tr></thead>
                            <tbody>
                                <?php foreach ($queryResult as $row): ?>
                                <tr><?php foreach ($row as $val): ?><td title="<?= e((string)$val) ?>"><?= e((string)$val) ?></td><?php endforeach; ?></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <!-- â”€â”€ USERS PAGE â”€â”€ -->
        <?php elseif ($page === 'users'): ?>

            <!-- Users list -->
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-users"></i> All Users</div>
                <div class="table-wrap">
                    <table class="users-tbl">
                        <thead><tr><th>#</th><th>Username</th><th>Role</th><th>Created By</th><th>Last Login</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach (get_all_users($pdo) as $u): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:12px"><?= (int)$u['id'] ?></td>
                            <td>
                                <?= e($u['username']) ?>
                                <?php if ((int)$u['id'] === $user['id']): ?><span class="you-tag">(you)</span><?php endif; ?>
                            </td>
                            <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-viewer' ?>"><?= e($u['role']) ?></span></td>
                            <td style="color:var(--muted);font-size:12px"><?= $u['created_by_name'] ? e($u['created_by_name']) : 'â€”' ?></td>
                            <td style="color:var(--muted);font-size:12px"><?= $u['last_login_at'] ? e($u['last_login_at']) : 'Never' ?></td>
                            <td>
                                <?php if ((int)$u['id'] !== $user['id']): ?>
                                <form method="POST" action="?page=users" style="display:inline" onsubmit="return confirm('Delete user Â«<?= e($u['username']) ?>Â»?')">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="del_id" value="<?= (int)$u['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Remove</button>
                                </form>
                                <?php else: ?><span style="color:var(--muted);font-size:12px">â€”</span><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add user + Change password -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

                <!-- Add new user -->
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-user-plus"></i> Add User</div>
                    <div class="card-body">
                        <form method="POST" action="?page=users">
                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="add_user">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="new_username" class="form-control" required minlength="3" placeholder="e.g. atul">
                            </div>
                            <div class="form-group">
                                <label>Password <span style="color:var(--muted);font-weight:400">(min 8)</span></label>
                                <input type="password" name="new_password" class="form-control" required minlength="8" placeholder="Strong password">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="new_role" class="form-control">
                                    <option value="viewer">Viewer â€” query only</option>
                                    <option value="admin">Admin â€” full access</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center">
                                <i class="fa-solid fa-user-plus"></i> Add User
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change own password -->
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-key"></i> Change My Password</div>
                    <div class="card-body">
                        <form method="POST" action="?page=users">
                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="change_pw">
                            <div class="form-group">
                                <label>New Password <span style="color:var(--muted);font-weight:400">(min 8)</span></label>
                                <input type="password" name="new_pw" class="form-control" required minlength="8" placeholder="New password">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_pw" class="form-control" required placeholder="Repeat new password">
                            </div>
                            <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center;margin-top:14px">
                                <i class="fa-solid fa-lock"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        <?php endif; ?>

        </div><!-- /content -->
    </div><!-- /main -->
</div><!-- /layout -->

<script>
function setQuery(q) {
    document.getElementById('sqlInput').value = q;
    document.getElementById('sqlInput').focus();
}
document.getElementById('sqlInput')?.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') document.getElementById('queryForm').submit();
});
document.addEventListener('click', function(e) {
    const td = e.target.closest('table.results td[title]');
    if (td) td.style.whiteSpace = td.style.whiteSpace === 'normal' ? 'nowrap' : 'normal';
});
</script>
<?php endif; ?>
</body>
</html>
