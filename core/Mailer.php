<?php
declare(strict_types=1);

class Mailer
{
    public static function queue(string $type, string $to, string $subject, string $body, array $meta = []): string
    {
        $dir = ROOT . '/storage/outbox';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $payload = [
            'type' => $type,
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'meta' => $meta,
            'status' => 'queued',
            'created_at' => now(),
        ];

        $file = $dir . '/' . uniqid($type . '_', true) . '.json';
        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT));
        self::log($type, $to, $subject, $payload['status'], $meta + ['outbox_file' => basename($file)]);
        return $file;
    }

    public static function sendOrQueue(string $type, string $to, string $subject, string $body, array $meta = []): string
    {
        $config = require ROOT . '/config/config.php';
        $overrides = class_exists('AppSettings') ? (AppSettings::configOverrides()['mail'] ?? []) : [];
        $config['mail'] = array_merge($config['mail'] ?? [], array_filter($overrides, fn (mixed $value): bool => $value !== ''));
        if (($config['mail']['host'] ?? '') === '') {
            return self::queue($type, $to, $subject, $body, $meta);
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . ($config['mail']['from_name'] ?? 'FreedomOS') . ' <' . $config['mail']['from'] . '>',
        ];

        $sent = @mail($to, $subject, $body, implode("\r\n", $headers));
        if (!$sent) {
            return self::queue($type, $to, $subject, $body, $meta + ['send_error' => 'mail() returned false']);
        }

        return self::queue($type, $to, $subject, $body, $meta + ['sent_at' => now(), 'status' => 'sent']);
    }

    private static function log(string $type, string $to, string $subject, string $status, array $meta): void
    {
        try {
            Database::insert('notification_logs', [
                'type' => $type,
                'recipient' => $to,
                'subject' => $subject,
                'status' => $status,
                'meta' => json_encode($meta),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            error_log('Notification log failed: ' . $e->getMessage());
        }
    }
}
