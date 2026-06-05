<?php
declare(strict_types=1);

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = Database::row('SELECT * FROM users WHERE email = ?', [mb_strtolower(trim($email))]);
        if (!$user || !empty($user['deleted_at']) || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user'] = self::publicUser($user);
        return true;
    }

    public static function register(array $data): string
    {
        $id = Database::insert('users', [
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'timezone' => $data['timezone'] ?? 'Europe/London',
            'created_at' => now(),
        ]);

        Database::insert('streaks', [
            'user_id' => $id,
            'current_days' => 0,
            'longest_days' => 0,
        ]);

        return $id;
    }

    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        if (!isset($_SESSION['user'])) {
            try {
                $user = Database::row('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
            } catch (Throwable $e) {
                error_log('Auth user unavailable: ' . $e->getMessage());
                unset($_SESSION['user_id'], $_SESSION['user']);
                return null;
            }
            $_SESSION['user'] = $user ? self::publicUser($user) : null;
        }
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function logout(): never
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('/login');
    }

    private static function publicUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }
}
