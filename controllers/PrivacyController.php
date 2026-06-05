<?php
declare(strict_types=1);

class PrivacyController
{
    public function index(): void
    {
        $counts = [
            'check_ins' => $this->countRows('check_ins'),
            'sos_events' => $this->countRows('sos_events'),
            'goals' => $this->countRows('goals'),
            'testimonies' => $this->countRows('testimonies'),
            'accountability_pairs' => Database::row(
                'SELECT COUNT(*) AS total FROM accountability_pairs WHERE user_id = ? OR partner_id = ?',
                [Auth::id(), Auth::id()]
            )['total'] ?? 0,
        ];

        view('privacy/index', [
            'title' => 'Privacy',
            'counts' => $counts,
            'user' => Auth::user(),
        ]);
    }

    public function export(): never
    {
        $userId = Auth::id();
        $export = [
            'exported_at' => now(),
            'user' => Auth::user(),
            'streak' => Database::row('SELECT * FROM streaks WHERE user_id = ?', [$userId]),
            'check_ins' => Database::rows('SELECT * FROM check_ins WHERE user_id = ? ORDER BY checked_in_at ASC', [$userId]),
            'sos_events' => Database::rows('SELECT * FROM sos_events WHERE user_id = ? ORDER BY created_at ASC', [$userId]),
            'safety_plan' => Database::row('SELECT * FROM safety_plans WHERE user_id = ?', [$userId]),
            'goals' => Database::rows('SELECT * FROM goals WHERE user_id = ? ORDER BY created_at ASC', [$userId]),
            'testimonies' => Database::rows('SELECT * FROM testimonies WHERE user_id = ? ORDER BY created_at ASC', [$userId]),
            'accountability_as_user' => Database::rows('SELECT * FROM accountability_pairs WHERE user_id = ?', [$userId]),
            'accountability_as_partner' => Database::rows('SELECT * FROM accountability_pairs WHERE partner_id = ?', [$userId]),
            'notifications' => Database::rows(
                'SELECT * FROM notification_logs WHERE recipient = ? ORDER BY created_at ASC',
                [Auth::user()['email'] ?? '']
            ),
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="freedomos-export-' . date('Y-m-d') . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT);
        exit;
    }

    public function deleteAccount(): never
    {
        $user = Auth::user();
        $password = (string) ($_POST['password'] ?? '');
        $confirm = trim((string) ($_POST['confirm'] ?? ''));
        $dbUser = Database::row('SELECT * FROM users WHERE id = ?', [Auth::id()]);

        if (!$dbUser || !password_verify($password, $dbUser['password']) || $confirm !== 'DELETE') {
            flash('error', 'Enter your password and type DELETE to confirm.');
            redirect('/privacy');
        }

        $anonymousEmail = 'deleted+' . Auth::id() . '+' . bin2hex(random_bytes(4)) . '@freedomos.local';
        Database::query(
            'UPDATE users SET name = "Deleted user", email = ?, password = ?, phone = NULL, deleted_at = ? WHERE id = ?',
            [$anonymousEmail, password_hash(bin2hex(random_bytes(24)), PASSWORD_BCRYPT), now(), Auth::id()]
        );
        Database::query('UPDATE accountability_pairs SET status = "paused" WHERE user_id = ? OR partner_id = ?', [Auth::id(), Auth::id()]);

        Mailer::sendOrQueue('account_deleted', $user['email'], 'FreedomOS account deleted', "Your FreedomOS account was anonymized on " . now() . ".\n\nIf this was not you, contact support immediately.", [
            'user_id' => Auth::id(),
        ]);

        Auth::logout();
    }

    private function countRows(string $table): int
    {
        $row = Database::row("SELECT COUNT(*) AS total FROM {$table} WHERE user_id = ?", [Auth::id()]);
        return (int) ($row['total'] ?? 0);
    }
}
