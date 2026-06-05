<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $config = require ROOT . '/config/config.php';
        $db = $config['db'];
        $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";

        self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function row(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function rows(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): string
    {
        $columns = array_keys($data);
        $safeColumns = array_map(fn (string $col): string => '`' . str_replace('`', '', $col) . '`', $columns);
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        self::query(
            'INSERT INTO `' . str_replace('`', '', $table) . '` (' . implode(',', $safeColumns) . ") VALUES ({$placeholders})",
            array_values($data)
        );
        return self::get()->lastInsertId();
    }
}
