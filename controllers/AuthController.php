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
}
