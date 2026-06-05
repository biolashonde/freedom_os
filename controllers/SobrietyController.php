<?php
declare(strict_types=1);

class SobrietyController
{
    public function dashboard(): void
    {
        $userId = Auth::id();
        $streak = Database::row('SELECT * FROM streaks WHERE user_id = ?', [$userId]);
        $recent = Database::rows(
            'SELECT * FROM check_ins WHERE user_id = ? ORDER BY checked_in_at DESC LIMIT 14',
            [$userId]
        );
        $today = Database::row(
            'SELECT * FROM check_ins WHERE user_id = ? AND checked_in_at = ?',
            [$userId, date('Y-m-d')]
        );
        $safetyPlan = Database::row('SELECT * FROM safety_plans WHERE user_id = ?', [$userId]);
        $risk = RiskEngine::forUser($userId);

        view('sobriety/dashboard', [
            'title' => 'Dashboard',
            'user' => Auth::user(),
            'streak' => $streak,
            'recent' => $recent,
            'today' => $today,
            'safetyPlan' => $safetyPlan,
            'risk' => $risk,
        ]);
    }

    public function checkin(): void
    {
        $userId = Auth::id();
        $todayDate = date('Y-m-d');
        $mood = max(1, min(5, (int) ($_POST['mood'] ?? 3)));
        $urge = max(1, min(5, (int) ($_POST['urge_level'] ?? 1)));
        $relapsed = isset($_POST['relapsed']) ? 1 : 0;
        $note = sanitize((string) ($_POST['note'] ?? ''));

        $existing = Database::row(
            'SELECT id FROM check_ins WHERE user_id = ? AND checked_in_at = ?',
            [$userId, $todayDate]
        );

        if ($existing) {
            Database::query(
                'UPDATE check_ins SET mood = ?, urge_level = ?, relapsed = ?, note = ?, prayer_done = ?, scripture_read = ? WHERE id = ?',
                [$mood, $urge, $relapsed, $note, isset($_POST['prayer_done']) ? 1 : 0, isset($_POST['scripture_read']) ? 1 : 0, $existing['id']]
            );
        } else {
            Database::insert('check_ins', [
                'user_id' => $userId,
                'mood' => $mood,
                'urge_level' => $urge,
                'relapsed' => $relapsed,
                'note' => $note,
                'prayer_done' => isset($_POST['prayer_done']) ? 1 : 0,
                'scripture_read' => isset($_POST['scripture_read']) ? 1 : 0,
                'checked_in_at' => $todayDate,
                'created_at' => now(),
            ]);
        }

        $this->updateStreak($userId, (bool) $relapsed);
        flash('success', $relapsed ? 'Check-in saved. Reset is not defeat; start clean now.' : 'Check-in saved. One faithful day at a time.');
        redirect('/dashboard');
    }

    public function progress(): void
    {
        $analytics = Analytics::progressForUser(Auth::id(), 30);
        view('sobriety/progress', [
            'title' => 'Progress',
            'analytics' => $analytics,
            'streak' => Database::row('SELECT * FROM streaks WHERE user_id = ?', [Auth::id()]),
        ]);
    }

    private function updateStreak(int $userId, bool $relapsed): void
    {
        $streak = Database::row('SELECT * FROM streaks WHERE user_id = ?', [$userId]);
        if (!$streak) {
            Database::insert('streaks', ['user_id' => $userId, 'current_days' => 0, 'longest_days' => 0]);
            $streak = Database::row('SELECT * FROM streaks WHERE user_id = ?', [$userId]);
        }

        if ($relapsed) {
            Database::query(
                'UPDATE streaks SET current_days = 0, total_relapses = total_relapses + 1, last_clean_date = NULL WHERE user_id = ?',
                [$userId]
            );
            return;
        }

        $last = $streak['last_clean_date'] ?? null;
        $current = (int) ($streak['current_days'] ?? 0);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        if ($last === $today) {
            $newCurrent = max(1, $current);
        } elseif ($last === $yesterday) {
            $newCurrent = $current + 1;
        } else {
            $newCurrent = 1;
        }

        Database::query(
            'UPDATE streaks SET current_days = ?, longest_days = GREATEST(longest_days, ?), last_clean_date = ? WHERE user_id = ?',
            [$newCurrent, $newCurrent, $today, $userId]
        );
    }
}
