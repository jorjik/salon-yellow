<?php
session_start();

function load_env(string $path): void
{
    if (!file_exists($path))
        return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}
load_env(__DIR__ . '/../.env');

define('ADMIN_USER', $_ENV['ADMIN_USER'] ?? 'admin');
define('ADMIN_PASS_HASH', $_ENV['ADMIN_PASS_HASH'] ?? '');

function is_logged_in(): bool
{
    return isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true;
}

function require_auth()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

function save_json(string $filename, array $data): bool
{
    $path = __DIR__ . '/../data/' . $filename;
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)) !== false;
}

function load_json(string $filename): array
{
    $path = __DIR__ . '/../data/' . $filename;
    if (!file_exists($path))
        return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}
