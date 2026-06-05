<?php
declare(strict_types=1);

class DonationSettings
{
    public static function get(): array
    {
        try {
            $row = Database::row('SELECT * FROM donation_settings WHERE id = 1');
        } catch (Throwable $e) {
            error_log('Donation settings unavailable: ' . $e->getMessage());
            return self::defaults();
        }

        if (!$row) {
            return self::defaults();
        }

        $platforms = json_decode((string) ($row['platform_links'] ?? '[]'), true);
        return array_merge(self::defaults(), $row, [
            'manual_enabled' => (int) ($row['manual_enabled'] ?? 0),
            'platform_enabled' => (int) ($row['platform_enabled'] ?? 0),
            'platform_links' => is_array($platforms) ? $platforms : [],
        ]);
    }

    public static function save(array $input): void
    {
        $platforms = [];
        $names = $input['platform_name'] ?? [];
        $urls = $input['platform_url'] ?? [];
        foreach ($names as $index => $name) {
            $name = sanitize((string) $name);
            $url = trim((string) ($urls[$index] ?? ''));
            if ($name === '' || $url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            $platforms[] = ['name' => $name, 'url' => $url];
        }

        $data = [
            'manual_enabled' => isset($input['manual_enabled']) ? 1 : 0,
            'platform_enabled' => isset($input['platform_enabled']) ? 1 : 0,
            'headline' => sanitize((string) ($input['headline'] ?? '')),
            'body' => sanitize((string) ($input['body'] ?? '')),
            'bank_name' => sanitize((string) ($input['bank_name'] ?? '')),
            'account_name' => sanitize((string) ($input['account_name'] ?? '')),
            'account_number' => sanitize((string) ($input['account_number'] ?? '')),
            'routing_code' => sanitize((string) ($input['routing_code'] ?? '')),
            'iban' => sanitize((string) ($input['iban'] ?? '')),
            'swift' => sanitize((string) ($input['swift'] ?? '')),
            'reference_note' => sanitize((string) ($input['reference_note'] ?? '')),
            'platform_links' => json_encode($platforms),
            'updated_at' => now(),
        ];

        if (Database::row('SELECT id FROM donation_settings WHERE id = 1')) {
            Database::query(
                'UPDATE donation_settings
                 SET manual_enabled = ?, platform_enabled = ?, headline = ?, body = ?, bank_name = ?,
                     account_name = ?, account_number = ?, routing_code = ?, iban = ?, swift = ?,
                     reference_note = ?, platform_links = ?, updated_at = ?
                 WHERE id = 1',
                array_values($data)
            );
            return;
        }

        Database::insert('donation_settings', ['id' => 1] + $data + ['created_at' => now()]);
    }

    private static function defaults(): array
    {
        return [
            'manual_enabled' => 0,
            'platform_enabled' => 0,
            'headline' => 'Help keep FreedomOS available for people rebuilding in private.',
            'body' => 'Your gift supports development, hosting, safer accountability tools, and recovery resources for people who need a practical way back into the light.',
            'bank_name' => '',
            'account_name' => '',
            'account_number' => '',
            'routing_code' => '',
            'iban' => '',
            'swift' => '',
            'reference_note' => '',
            'platform_links' => [],
        ];
    }
}
