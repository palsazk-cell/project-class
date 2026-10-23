<?php
declare(strict_types=1);

// تنظیمات نشست؛ در اجرای محلی HTTP نیز کار می‌کند.
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$dbDirectory = dirname(__DIR__) . '/database';
if (!is_dir($dbDirectory)) {
    mkdir($dbDirectory, 0775, true);
}
$dbPath = $dbDirectory . '/users.sqlite';

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
} catch (Throwable $error) {
    http_response_code(500);
    exit(
        '<!doctype html><html lang="fa" dir="rtl"><meta charset="utf-8">' .
        '<title>خطای راه‌اندازی</title><body style="font-family:Tahoma,Arial;padding:2rem">' .
        '<h2>اتصال به دیتابیس برقرار نشد.</h2>' .
        '<p>در اوبونتو این دستور را اجرا کن و سپس سرور PHP را دوباره راه‌اندازی کن:</p>' .
        '<pre>sudo apt update\nsudo apt install php-sqlite3</pre>' .
        '<p>برای بررسی نصب بودن افزونه: <code>php -m | grep -i sqlite</code></p>' .
        '</body></html>'
    );
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['username']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    $provided = $_POST['csrf_token'] ?? '';
    return is_string($provided)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $provided);
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
