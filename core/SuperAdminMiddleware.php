<?php
declare(strict_types=1);

class SuperAdminMiddleware
{
    public function handle(): void
    {
        if ((Auth::user()['role'] ?? 'user') !== 'superadmin') {
            http_response_code(403);
            view('errors/403', ['title' => 'Forbidden']);
            exit;
        }
    }
}
