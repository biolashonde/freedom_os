<?php
declare(strict_types=1);

class AdminMiddleware
{
    public function handle(): void
    {
        $role = Auth::user()['role'] ?? 'user';
        if (!in_array($role, ['superadmin', 'admin', 'mentor'], true)) {
            http_response_code(403);
            view('errors/403', ['title' => 'Forbidden']);
            exit;
        }
    }
}
