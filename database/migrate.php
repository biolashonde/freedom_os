<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

if (file_exists(ROOT . '/.env')) {
    foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$config = require ROOT . '/config/config.php';
$db = $config['db'];
$serverDsn = "mysql:host={$db['host']};charset={$db['charset']}";
$pdo = new PDO($serverDsn, $db['user'], $db['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$databaseName = str_replace('`', '', $db['name']);
$charset = str_replace('`', '', $db['charset']);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
$pdo->exec("USE `{$databaseName}`");
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) UNIQUE NOT NULL,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )'
);

$files = glob(ROOT . '/database/*.sql') ?: [];
sort($files);

foreach ($files as $file) {
    if (basename($file) === 'schema.sql') {
        continue;
    }

    $name = basename($file);
    $already = $pdo->prepare('SELECT id FROM schema_migrations WHERE migration = ?');
    $already->execute([$name]);
    if ($already->fetch()) {
        echo "Skipped {$name}\n";
        continue;
    }

    $sql = (string) file_get_contents($file);
    $pdo->exec($sql);

    $stmt = $pdo->prepare('INSERT INTO schema_migrations (migration, applied_at) VALUES (?, ?)');
    $stmt->execute([$name, date('Y-m-d H:i:s')]);
    echo "Applied {$name}\n";
}

echo "Migrations complete.\n";
