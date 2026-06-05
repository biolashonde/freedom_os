<?php
declare(strict_types=1);

class HealthController
{
    public function index(): void
    {
        $config = require ROOT . '/config/config.php';
        $checks = [
            'php_version' => [
                'label' => 'PHP version',
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'detail' => PHP_VERSION,
            ],
            'database' => $this->databaseCheck(),
            'storage_cache' => $this->writableCheck('storage/cache'),
            'storage_outbox' => $this->writableCheck('storage/outbox'),
            'storage_logs' => $this->writableCheck('storage/logs'),
            'app_key' => [
                'label' => 'App key changed',
                'ok' => ($config['app']['key'] ?? '') !== 'change-this-32char-secret-key!!',
                'detail' => ($config['app']['key'] ?? '') === 'change-this-32char-secret-key!!' ? 'Default key still in use' : 'Configured',
            ],
            'debug_mode' => [
                'label' => 'Debug mode',
                'ok' => !$config['app']['debug'],
                'detail' => $config['app']['debug'] ? 'On' : 'Off',
            ],
            'mail' => [
                'label' => 'Mail transport',
                'ok' => ($config['mail']['host'] ?? '') !== '',
                'detail' => ($config['mail']['host'] ?? '') !== '' ? 'SMTP configured' : 'Local outbox mode',
            ],
            'ai_provider' => $this->aiCheck(),
        ];

        $stats = [
            'users' => $this->safeCount('users', 'deleted_at IS NULL'),
            'deleted_users' => $this->safeCount('users', 'deleted_at IS NOT NULL'),
            'check_ins' => $this->safeCount('check_ins'),
            'sos_events' => $this->safeCount('sos_events'),
            'active_pairs' => $this->safeCount('accountability_pairs', 'status = "active"'),
            'notifications' => $this->safeCount('notification_logs'),
            'migrations' => $this->safeCount('schema_migrations'),
            'outbox_files' => count(glob(ROOT . '/storage/outbox/*.json') ?: []),
        ];

        view('health/index', [
            'title' => 'Health',
            'checks' => $checks,
            'stats' => $stats,
        ]);
    }

    private function databaseCheck(): array
    {
        try {
            Database::row('SELECT 1 AS ok');
            return ['label' => 'Database', 'ok' => true, 'detail' => 'Connected'];
        } catch (Throwable $e) {
            return ['label' => 'Database', 'ok' => false, 'detail' => $e->getMessage()];
        }
    }

    private function writableCheck(string $path): array
    {
        $fullPath = ROOT . '/' . $path;
        if (!is_dir($fullPath)) {
            @mkdir($fullPath, 0755, true);
        }

        return [
            'label' => $path . ' writable',
            'ok' => is_dir($fullPath) && is_writable($fullPath),
            'detail' => is_dir($fullPath) ? $fullPath : 'Missing',
        ];
    }

    private function aiCheck(): array
    {
        $ai = new AI();
        $available = array_keys(array_filter($ai->availableProviders()));

        return [
            'label' => 'AI connector',
            'ok' => $ai->configured(),
            'detail' => $ai->configured()
                ? 'Active: ' . $ai->provider() . ' | Available: ' . implode(', ', $available)
                : 'No AI provider key configured',
        ];
    }

    private function safeCount(string $table, string $where = '1=1'): int
    {
        try {
            $table = str_replace('`', '', $table);
            $row = Database::row("SELECT COUNT(*) AS total FROM `{$table}` WHERE {$where}");
            return (int) ($row['total'] ?? 0);
        } catch (Throwable) {
            return 0;
        }
    }
}
