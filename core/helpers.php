<?php
declare(strict_types=1);

function view(string $template, array $data = []): void
{
    extract($data);
    $templatePath = ROOT . '/views/' . $template . '.php';
    require ROOT . '/views/layout.php';
}

function partial(string $template, array $data = []): void
{
    extract($data);
    require ROOT . '/views/' . $template . '.php';
}

function redirect(string $url): never
{
    header('Location: ' . base_url($url));
    exit;
}

function app_base_path(): string
{
    if (PHP_SAPI === 'cli') {
        $configured = class_exists('AppSettings')
            ? (AppSettings::configOverrides()['app']['url'] ?? '')
            : ((require ROOT . '/config/config.php')['app']['url'] ?? '');
        $path = is_string($configured) ? (parse_url($configured, PHP_URL_PATH) ?: '') : '';
        return rtrim($path, '/');
    }

    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if (str_ends_with($scriptDir, '/public')) {
        $scriptDir = substr($scriptDir, 0, -7);
    }
    return ($scriptDir && $scriptDir !== '/') ? $scriptDir : '';
}

function base_url(string $path = ''): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return app_base_path() . '/' . ltrim($path, '/');
}

function absolute_url(string $path = ''): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $configured = class_exists('AppSettings')
        ? (AppSettings::configOverrides()['app']['url'] ?? '')
        : ((require ROOT . '/config/config.php')['app']['url'] ?? '');
    $host = $_SERVER['HTTP_HOST'] ?? parse_url((string) $configured, PHP_URL_HOST) ?? 'localhost';
    return $scheme . '://' . $host . base_url($path);
}

function asset(string $path): string
{
    return base_url('/assets/' . ltrim($path, '/'));
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('CSRF token mismatch.');
    }
}

function json(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function remember_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

function now(): string
{
    return date('Y-m-d H:i:s');
}
