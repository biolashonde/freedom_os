<?php
declare(strict_types=1);

class AuthController
{
    public function showLogin(): void
    {
        view('auth/login', ['title' => 'Login']);
        clear_old();
    }

    public function login(): void
    {
        $email = (string) ($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $limitKey = Security::loginKey($email);

        if (RateLimiter::tooManyAttempts($limitKey, 5, 300)) {
            $wait = RateLimiter::remainingSeconds($limitKey, 300);
            flash('error', 'Too many login attempts. Try again in about ' . max(1, (int) ceil($wait / 60)) . ' minute(s).');
            redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            RateLimiter::clear($limitKey);
            clear_old();
            redirect('/dashboard');
        }

        RateLimiter::hit($limitKey, 300);
        remember_old(['email' => $email]);
        flash('error', 'Those login details did not match.');
        redirect('/login');
    }

    public function showRegister(): void
    {
        view('auth/register', ['title' => 'Create account']);
        clear_old();
    }

    public function showForgotPassword(): void
    {
        view('auth/forgot_password', ['title' => 'Forgot password']);
        clear_old();
    }

    public function forgotPassword(): void
    {
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        remember_old(['email' => $email]);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Add a valid email address.');
            redirect('/forgot-password');
        }

        $user = Database::row('SELECT id, name, email FROM users WHERE email = ? AND deleted_at IS NULL', [$email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::insert('password_resets', [
                'user_id' => $user['id'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
                'created_at' => now(),
            ]);

            $url = absolute_url('/reset-password/' . $token);
            Mailer::sendOrQueue(
                'password_reset',
                (string) $user['email'],
                'Reset your FreedomOS password',
                "Hello {$user['name']},\n\nUse this secure link to reset your FreedomOS password:\n\n{$url}\n\nThis link expires in 1 hour. If you did not request it, you can ignore this email.",
                ['recipient_name' => $user['name'], 'priority' => 10]
            );
        }

        clear_old();
        flash('success', 'If that email exists, a password reset link has been queued.');
        redirect('/login');
    }

    public function showResetPassword(string $token): void
    {
        if (!$this->validReset($token)) {
            flash('error', 'That reset link is invalid or expired.');
            redirect('/forgot-password');
        }

        view('auth/reset_password', ['title' => 'Reset password', 'token' => $token]);
    }

    public function resetPassword(string $token): void
    {
        $reset = $this->validReset($token);
        if (!$reset) {
            flash('error', 'That reset link is invalid or expired.');
            redirect('/forgot-password');
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');
        if (strlen($password) < 8 || $password !== $confirm) {
            flash('error', 'Use at least 8 characters and make sure both password fields match.');
            redirect('/reset-password/' . $token);
        }

        Database::query('UPDATE users SET password = ? WHERE id = ?', [password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $reset['user_id']]);
        Database::query('UPDATE password_resets SET used_at = ? WHERE id = ?', [now(), $reset['id']]);

        flash('success', 'Password updated. You can log in now.');
        redirect('/login');
    }

    public function register(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        remember_old(['name' => $name, 'email' => $email]);

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            flash('error', 'Add your name, a valid email, and a password of at least 8 characters.');
            redirect('/register');
        }

        if (Database::row('SELECT id FROM users WHERE email = ?', [mb_strtolower($email)])) {
            flash('error', 'That email is already registered.');
            redirect('/register');
        }

        Auth::register(['name' => $name, 'email' => $email, 'password' => $password]);
        Auth::attempt($email, $password);
        clear_old();
        redirect('/dashboard');
    }

    public function logout(): never
    {
        Auth::logout();
    }

    private function validReset(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        return Database::row(
            'SELECT * FROM password_resets
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
             ORDER BY created_at DESC
             LIMIT 1',
            [hash('sha256', $token)]
        );
    }
}
