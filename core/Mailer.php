<?php
declare(strict_types=1);

class Mailer
{
    public static function queue(string $type, string $to, string $subject, string $body, array $meta = []): string
    {
        return self::queueFile($type, $to, $subject, $body, $meta);
    }

    public static function queueFile(string $type, string $to, string $subject, string $body, array $meta = []): string
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
        self::log($type, $to, $subject, $payload['status'], $meta + ['outbox_file' => basename($file), 'fallback' => 'file']);
        return $file;
    }

    public static function sendOrQueue(string $type, string $to, string $subject, string $body, array $meta = []): string
    {
        if (class_exists('EmailWorkflow')) {
            return EmailWorkflow::enqueue($type, $to, $subject, $body, $meta);
        }
        return self::queueFile($type, $to, $subject, $body, $meta);
    }

    public static function processQueue(int $limit = 50): array
    {
        if (class_exists('EmailWorkflow')) {
            return EmailWorkflow::process($limit);
        }
        return ['processed' => 0, 'sent' => 0, 'failed' => 0];
    }

    public static function log(string $type, string $to, string $subject, string $status, array $meta): void
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
