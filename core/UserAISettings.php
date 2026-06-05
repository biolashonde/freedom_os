<?php
declare(strict_types=1);

class UserAISettings
{
    private const SECRET_KEYS = [
        'anthropic_key',
        'openai_key',
        'gemini_key',
        'openrouter_key',
    ];

    public static function get(int $userId): array
    {
        $row = Database::row('SELECT * FROM user_ai_settings WHERE user_id = ?', [$userId]);
        if (!$row) {
            return self::defaults();
        }

        return array_merge(self::defaults(), $row);
    }

    public static function save(int $userId, array $input): void
    {
        $current = self::get($userId);
        $data = [
            'provider' => self::provider((string) ($input['provider'] ?? $current['provider'])),
            'anthropic_key' => self::secretValue('anthropic_key', $input, $current),
            'anthropic_model' => sanitize((string) ($input['anthropic_model'] ?? $current['anthropic_model'])),
            'openai_key' => self::secretValue('openai_key', $input, $current),
            'openai_model' => sanitize((string) ($input['openai_model'] ?? $current['openai_model'])),
            'gemini_key' => self::secretValue('gemini_key', $input, $current),
            'gemini_model' => sanitize((string) ($input['gemini_model'] ?? $current['gemini_model'])),
            'openrouter_key' => self::secretValue('openrouter_key', $input, $current),
            'openrouter_model' => sanitize((string) ($input['openrouter_model'] ?? $current['openrouter_model'])),
            'openrouter_site_url' => trim((string) ($input['openrouter_site_url'] ?? $current['openrouter_site_url'])),
            'openrouter_app_name' => sanitize((string) ($input['openrouter_app_name'] ?? $current['openrouter_app_name'])),
            'updated_at' => now(),
        ];

        if (Database::row('SELECT id FROM user_ai_settings WHERE user_id = ?', [$userId])) {
            Database::query(
                'UPDATE user_ai_settings
                 SET provider = ?, anthropic_key = ?, anthropic_model = ?, openai_key = ?, openai_model = ?,
                     gemini_key = ?, gemini_model = ?, openrouter_key = ?, openrouter_model = ?,
                     openrouter_site_url = ?, openrouter_app_name = ?, updated_at = ?
                 WHERE user_id = ?',
                array_merge(array_values($data), [$userId])
            );
            return;
        }

        Database::insert('user_ai_settings', ['user_id' => $userId] + $data + ['created_at' => now()]);
    }

    public static function configFor(int $userId): array
    {
        $settings = self::get($userId);
        return [
            'provider' => $settings['provider'],
            'key' => $settings['anthropic_key'],
            'model' => $settings['anthropic_model'],
            'openai_key' => $settings['openai_key'],
            'openai_model' => $settings['openai_model'],
            'gemini_key' => $settings['gemini_key'],
            'gemini_model' => $settings['gemini_model'],
            'openrouter_key' => $settings['openrouter_key'],
            'openrouter_model' => $settings['openrouter_model'],
            'openrouter_site_url' => $settings['openrouter_site_url'],
            'openrouter_app_name' => $settings['openrouter_app_name'],
        ];
    }

    public static function mask(string $value): string
    {
        if ($value === '') {
            return 'Not set';
        }
        return strlen($value) <= 8 ? 'Stored' : substr($value, 0, 4) . '...' . substr($value, -4);
    }

    private static function defaults(): array
    {
        return [
            'provider' => 'auto',
            'anthropic_key' => '',
            'anthropic_model' => 'claude-sonnet-4-20250514',
            'openai_key' => '',
            'openai_model' => 'gpt-4o-mini',
            'gemini_key' => '',
            'gemini_model' => 'gemini-2.5-flash',
            'openrouter_key' => '',
            'openrouter_model' => 'openai/gpt-4o-mini',
            'openrouter_site_url' => '',
            'openrouter_app_name' => 'FreedomOS',
        ];
    }

    private static function provider(string $provider): string
    {
        $provider = strtolower(trim($provider));
        return in_array($provider, ['auto', 'anthropic', 'openai', 'gemini', 'openrouter'], true) ? $provider : 'auto';
    }

    private static function secretValue(string $key, array $input, array $current): string
    {
        if (!array_key_exists($key, $input) || trim((string) $input[$key]) === '') {
            return (string) ($current[$key] ?? '');
        }
        return trim((string) $input[$key]);
    }
}
