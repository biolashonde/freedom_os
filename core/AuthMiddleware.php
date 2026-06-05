<?php
declare(strict_types=1);

class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            redirect('/login');
        }
    }
}
