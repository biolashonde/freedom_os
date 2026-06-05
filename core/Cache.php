<?php
declare(strict_types=1);

class Cache
{
    private static function dir(): string
    {
        return ROOT . '/storage/cache';
    }

    public static function get(string $key): mixed
    {
        $file = self::dir() . '/' . md5($key) . '.json';
        if (!file_exists($file)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data) || (($data['expires'] ?? 0) !== 0 && time() > $data['expires'])) {
            @unlink($file);
            return null;
        }
        return $data['value'] ?? null;
    }

    public static function set(string $key, mixed $value, int $ttlSeconds = 3600): void
    {
        if (!is_dir(self::dir())) {
            mkdir(self::dir(), 0755, true);
        }
        file_put_contents(self::dir() . '/' . md5($key) . '.json', json_encode([
            'value' => $value,
            'expires' => $ttlSeconds > 0 ? time() + $ttlSeconds : 0,
        ], JSON_THROW_ON_ERROR));
    }

    public static function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }
        $value = $callback();
        self::set($key, $value, $ttlSeconds);
        return $value;
    }
}
