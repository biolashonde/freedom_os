<?php
declare(strict_types=1);

class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $entry = self::entry($key, $windowSeconds);
        return count($entry['hits']) >= $maxAttempts;
    }

    public static function hit(string $key, int $windowSeconds): void
    {
        $entry = self::entry($key, $windowSeconds);
        $entry['hits'][] = time();
        self::write($key, $entry);
    }

    public static function clear(string $key): void
    {
        $file = self::file($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public static function remainingSeconds(string $key, int $windowSeconds): int
    {
        $entry = self::entry($key, $windowSeconds);
        if (!$entry['hits']) {
            return 0;
        }

        return max(0, $windowSeconds - (time() - min($entry['hits'])));
    }

    private static function entry(string $key, int $windowSeconds): array
    {
        $file = self::file($key);
        $data = file_exists($file) ? json_decode((string) file_get_contents($file), true) : null;
        $hits = is_array($data['hits'] ?? null) ? $data['hits'] : [];
        $cutoff = time() - $windowSeconds;

        return [
            'hits' => array_values(array_filter($hits, fn (int $hit): bool => $hit >= $cutoff)),
        ];
    }

    private static function write(string $key, array $entry): void
    {
        $dir = ROOT . '/storage/cache/rate-limit';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(self::file($key), json_encode($entry, JSON_PRETTY_PRINT));
    }

    private static function file(string $key): string
    {
        $dir = ROOT . '/storage/cache/rate-limit';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . hash('sha256', $key) . '.json';
    }
}
