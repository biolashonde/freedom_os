<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
ini_set('expose_php', '0');

if (file_exists(ROOT . '/.env')) {
    foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

spl_autoload_register(function (string $class): void {
    foreach ([ROOT . '/core/', ROOT . '/controllers/', ROOT . '/models/'] as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once ROOT . '/core/helpers.php';

Security::sendHeaders();
Security::guardPostFlood();

set_exception_handler(function (Throwable $e): void {
    $config = require ROOT . '/config/config.php';
    http_response_code(500);
    error_log($e->getMessage());
    if ($config['app']['debug']) {
        echo '<pre>' . e($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
        return;
    }
    view('errors/500', ['title' => 'Something went wrong']);
});

session_name('freedomos_session');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
session_start();

$router = new Router();
require ROOT . '/routes.php';
$router->dispatch();
