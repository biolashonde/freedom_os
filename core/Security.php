<?php
declare(strict_types=1);

class Security
{
    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://unpkg.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; font-src 'self' data:; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    }

    public static function guardPostFlood(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        $key = 'post:' . self::clientIp();
        if (RateLimiter::tooManyAttempts($key, 90, 60)) {
            http_response_code(429);
            exit('Too many requests. Please slow down and try again shortly.');
        }

        RateLimiter::hit($key, 60);
    }

    public static function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return preg_replace('/[^a-fA-F0-9:\.]/', '', $ip) ?: 'unknown';
    }

    public static function loginKey(string $email): string
    {
        return 'login:' . self::clientIp() . ':' . mb_strtolower(trim($email));
    }
}
