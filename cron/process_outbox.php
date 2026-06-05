<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

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

$outbox = ROOT . '/storage/outbox';
$sent = ROOT . '/storage/outbox/sent';
if (!is_dir($outbox)) {
    mkdir($outbox, 0755, true);
}
if (!is_dir($sent)) {
    mkdir($sent, 0755, true);
}

$files = glob($outbox . '/*.json') ?: [];
foreach ($files as $file) {
    $payload = json_decode((string) file_get_contents($file), true);
    if (!is_array($payload)) {
        continue;
    }

    $payload['processed_at'] = now();
    $payload['status'] = $payload['meta']['sent_at'] ?? null ? 'sent' : 'queued_for_smtp';
    file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT));
    rename($file, $sent . '/' . basename($file));
    echo basename($file) . " processed\n";
}
