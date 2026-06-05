<?php
declare(strict_types=1);

class AppSettings
{
    private const SECRET_KEYS = ['mail_pass'];

    public static function all(): array
    {
        try {
            $rows = Database::rows('SELECT setting_key, setting_value FROM app_settings');
        } catch (Throwable $e) {
            return [];
        }

        $settings = [];
        foreach ($rows as $row) {
            $settings[(string) $row['setting_key']] = (string) $row['setting_value'];
        }
        return $settings;
    }

    public static function groups(): array
    {
        $config = require ROOT . '/config/config.php';
        $stored = self::all();

        return [
            'app' => [
                'app_url' => self::value('app_url', $stored, $config['app']['url'] ?? ''),
                'app_debug' => self::value('app_debug', $stored, ($config['app']['debug'] ?? false) ? 'true' : 'false'),
            ],
            'mail' => [
                'mail_host' => self::value('mail_host', $stored, $config['mail']['host'] ?? ''),
                'mail_port' => self::value('mail_port', $stored, (string) ($config['mail']['port'] ?? '587')),
                'mail_user' => self::value('mail_user', $stored, $config['mail']['user'] ?? ''),
                'mail_pass' => self::value('mail_pass', $stored, $config['mail']['pass'] ?? ''),
                'mail_from' => self::value('mail_from', $stored, $config['mail']['from'] ?? ''),
                'mail_from_name' => self::value('mail_from_name', $stored, $config['mail']['from_name'] ?? ''),
            ],
        ];
    }

    public static function configOverrides(): array
    {
        $groups = self::groups();
        return [
            'app' => [
                'url' => $groups['app']['app_url'],
                'debug' => filter_var($groups['app']['app_debug'], FILTER_VALIDATE_BOOLEAN),
            ],
            'mail' => [
                'host' => $groups['mail']['mail_host'],
                'port' => (int) $groups['mail']['mail_port'],
                'user' => $groups['mail']['mail_user'],
                'pass' => $groups['mail']['mail_pass'],
                'from' => $groups['mail']['mail_from'],
                'from_name' => $groups['mail']['mail_from_name'],
            ],
        ];
    }

    public static function save(array $input): void
    {
        foreach (self::allowedKeys() as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }

            $value = trim((string) $input[$key]);
            if (in_array($key, self::SECRET_KEYS, true) && $value === '') {
                continue;
            }

            self::upsert($key, $value);
        }
    }

    public static function mask(string $value): string
    {
        if ($value === '') {
            return 'Not set';
        }
        return strlen($value) <= 8 ? 'Stored' : substr($value, 0, 4) . '...' . substr($value, -4);
    }

    private static function value(string $key, array $stored, mixed $fallback): string
    {
        return array_key_exists($key, $stored) ? $stored[$key] : (string) $fallback;
    }

    private static function allowedKeys(): array
    {
        return [
            'app_url',
            'app_debug',
            'mail_host',
            'mail_port',
            'mail_user',
            'mail_pass',
            'mail_from',
            'mail_from_name',
        ];
    }

    private static function upsert(string $key, string $value): void
    {
        if (Database::row('SELECT id FROM app_settings WHERE setting_key = ?', [$key])) {
            Database::query('UPDATE app_settings SET setting_value = ?, updated_at = ? WHERE setting_key = ?', [$value, now(), $key]);
            return;
        }

        Database::insert('app_settings', [
            'setting_key' => $key,
            'setting_value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
