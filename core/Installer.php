<?php
declare(strict_types=1);

class Installer
{
    public static function lockPath(): string
    {
        return ROOT . '/storage/install.lock';
    }

    public static function isLocked(): bool
    {
        return file_exists(self::lockPath()) && !filter_var($_ENV['APP_INSTALL_UNLOCK'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public static function requirements(): array
    {
        return [
            ['label' => 'PHP 8.1+', 'ok' => version_compare(PHP_VERSION, '8.1.0', '>=')],
            ['label' => 'PDO MySQL extension', 'ok' => extension_loaded('pdo_mysql')],
            ['label' => 'OpenSSL extension', 'ok' => extension_loaded('openssl')],
            ['label' => 'Zip extension', 'ok' => extension_loaded('zip')],
            ['label' => 'Root writable for .env', 'ok' => file_exists(ROOT . '/.env') ? is_writable(ROOT . '/.env') : is_writable(ROOT)],
            ['label' => 'Storage writable', 'ok' => is_writable(ROOT . '/storage')],
            ['label' => 'Cache writable', 'ok' => is_writable(ROOT . '/storage/cache')],
            ['label' => 'Outbox writable', 'ok' => is_writable(ROOT . '/storage/outbox')],
        ];
    }

    public static function canInstall(): bool
    {
        foreach (self::requirements() as $requirement) {
            if (!$requirement['ok']) {
                return false;
            }
        }
        return true;
    }

    public static function run(array $input): array
    {
        if (self::isLocked()) {
            throw new RuntimeException('Installer is locked. Set APP_INSTALL_UNLOCK=true in .env to run it again.');
        }

        if (!self::canInstall()) {
            throw new RuntimeException('Server requirements are not ready.');
        }

        $dbHost = trim((string) ($input['db_host'] ?? 'localhost'));
        $dbName = self::safeIdentifier((string) ($input['db_name'] ?? 'freedomos'));
        $dbUser = trim((string) ($input['db_user'] ?? 'root'));
        $dbPass = (string) ($input['db_pass'] ?? '');
        $appUrl = rtrim(trim((string) ($input['app_url'] ?? absolute_url('/'))), '/');
        $adminName = sanitize((string) ($input['admin_name'] ?? 'Super Admin'));
        $adminEmail = mb_strtolower(trim((string) ($input['admin_email'] ?? '')));
        $adminPassword = (string) ($input['admin_password'] ?? '');

        if ($dbHost === '' || $dbName === '' || $dbUser === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminPassword) < 12) {
            throw new InvalidArgumentException('Add database details, a valid superadmin email, and a password of at least 12 characters.');
        }

        $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");

        $schema = (string) file_get_contents(ROOT . '/database/schema.sql');
        $pdo->exec(str_replace('USE freedomos;', "USE `{$dbName}`;", $schema));

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) UNIQUE NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        foreach (self::migrationFiles() as $file) {
            $name = basename($file);
            $sql = str_replace('USE freedomos;', "USE `{$dbName}`;", (string) file_get_contents($file));
            $pdo->exec($sql);
            $stmt = $pdo->prepare('INSERT IGNORE INTO schema_migrations (migration, applied_at) VALUES (?, ?)');
            $stmt->execute([$name, date('Y-m-d H:i:s')]);
        }

        $existing = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $existing->execute([$adminEmail]);
        $userId = $existing->fetchColumn();
        if ($userId) {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, password = ?, role = "superadmin", deleted_at = NULL WHERE id = ?');
            $stmt->execute([$adminName, password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]), $userId]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, "superadmin", ?)');
            $stmt->execute([$adminName, $adminEmail, password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]), now()]);
        }

        self::writeEnv($appUrl, $dbHost, $dbName, $dbUser, $dbPass);
        self::lock($adminEmail);

        return [
            'database' => $dbName,
            'superadmin' => $adminEmail,
            'app_url' => $appUrl,
        ];
    }

    private static function migrationFiles(): array
    {
        $files = glob(ROOT . '/database/*.sql') ?: [];
        sort($files);
        return array_values(array_filter($files, fn (string $file): bool => basename($file) !== 'schema.sql'));
    }

    private static function safeIdentifier(string $value): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', $value) ?: 'freedomos';
    }

    private static function writeEnv(string $appUrl, string $dbHost, string $dbName, string $dbUser, string $dbPass): void
    {
        $existing = [];
        if (file_exists(ROOT . '/.env')) {
            foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES) ?: [] as $line) {
                if (str_contains($line, '=')) {
                    [$key] = explode('=', $line, 2);
                    $existing[trim($key)] = $line;
                }
            }
        }

        $values = [
            'APP_URL' => $appUrl,
            'APP_DEBUG' => 'false',
            'APP_KEY' => bin2hex(random_bytes(24)),
            'DB_HOST' => $dbHost,
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASS' => $dbPass,
        ];

        foreach ($values as $key => $value) {
            $existing[$key] = $key . '=' . self::envValue($value);
        }

        $defaults = file_exists(ROOT . '/.env.example') ? (file(ROOT . '/.env.example', FILE_IGNORE_NEW_LINES) ?: []) : [];
        foreach ($defaults as $line) {
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key] = explode('=', $line, 2);
            $key = trim($key);
            $existing[$key] ??= $line;
        }

        file_put_contents(ROOT . '/.env', implode(PHP_EOL, array_values($existing)) . PHP_EOL);
    }

    private static function lock(string $adminEmail): void
    {
        file_put_contents(self::lockPath(), 'Installed ' . now() . ' by ' . $adminEmail . PHP_EOL);
    }

    private static function envValue(string $value): string
    {
        return preg_match('/\s|#|=|"|\'/', $value) ? '"' . addcslashes($value, "\\\"") . '"' : $value;
    }
}
