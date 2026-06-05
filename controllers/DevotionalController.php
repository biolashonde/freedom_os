<?php
declare(strict_types=1);

class DevotionalController
{
    public function today(): void
    {
        $day = $this->currentDay();
        $devotional = $this->findUserDevotional() ?? $this->findByDay($day) ?? $this->firstDevotional();
        $ai = new AI(Auth::id());
        view('devotional/today', [
            'title' => 'Devotional',
            'devotional' => $devotional,
            'day' => $day,
            'aiConfigured' => $ai->configured(),
            'aiProvider' => $ai->provider(),
        ]);
    }

    public function archive(): void
    {
        $devotionals = Database::rows(
            'SELECT day_number, title, scripture_ref, theme
             FROM devotionals
             WHERE user_id IS NULL
             ORDER BY day_number ASC'
        );

        view('devotional/archive', [
            'title' => 'Devotional archive',
            'devotionals' => $devotionals,
        ]);
    }

    public function show(string $day): void
    {
        $devotional = $this->findByDay(max(1, (int) $day));
        if (!$devotional) {
            http_response_code(404);
            view('errors/404', ['title' => 'Devotional not found']);
            return;
        }

        view('devotional/today', [
            'title' => $devotional['title'],
            'devotional' => $devotional,
            'day' => (int) $devotional['day_number'],
            'aiConfigured' => (new AI(Auth::id()))->configured(),
            'aiProvider' => (new AI(Auth::id()))->provider(),
        ]);
    }

    public function generate(): void
    {
        $ai = new AI(Auth::id());
        if (!$ai->configured()) {
            flash('error', 'Add your AI provider key in AI Settings to enable personalized devotionals.');
            redirect('/devotional');
        }

        try {
            $context = $this->devotionalContext(Auth::id());
            $generated = $ai->generateDevotional($context);
            Database::insert('devotionals', [
                'user_id' => Auth::id(),
                'day_number' => null,
                'title' => $generated['title'],
                'theme' => $generated['theme'],
                'scripture_ref' => $generated['scripture_ref'],
                'scripture_text' => $generated['scripture_text'],
                'body' => $generated['body'],
                'prayer' => $generated['prayer'],
                'ai_generated' => 1,
                'published_date' => date('Y-m-d'),
                'created_at' => now(),
            ]);
            flash('success', 'Personal devotional generated.');
        } catch (Throwable $e) {
            error_log('AI devotional failed: ' . $e->getMessage());
            flash('error', 'Could not generate a devotional right now. The seeded devotional is still available.');
        }

        redirect('/devotional');
    }

    private function currentDay(): int
    {
        $user = Auth::user();
        $created = new DateTimeImmutable(substr((string) ($user['created_at'] ?? date('Y-m-d')), 0, 10));
        $today = new DateTimeImmutable(date('Y-m-d'));
        return ((int) $created->diff($today)->format('%a') % 100) + 1;
    }

    private function findByDay(int $day): ?array
    {
        return Database::row(
            'SELECT * FROM devotionals WHERE user_id IS NULL AND day_number = ? LIMIT 1',
            [$day]
        );
    }

    private function firstDevotional(): ?array
    {
        return Database::row('SELECT * FROM devotionals WHERE user_id IS NULL ORDER BY day_number ASC LIMIT 1');
    }

    private function findUserDevotional(): ?array
    {
        return Database::row(
            'SELECT * FROM devotionals
             WHERE user_id = ? AND published_date = ?
             ORDER BY created_at DESC
             LIMIT 1',
            [Auth::id(), date('Y-m-d')]
        );
    }

    private function devotionalContext(int $userId): array
    {
        $streak = Database::row('SELECT * FROM streaks WHERE user_id = ?', [$userId]) ?? [];
        $recent = Database::row(
            'SELECT ROUND(AVG(mood), 1) AS avg_mood, ROUND(AVG(urge_level), 1) AS avg_urge
             FROM check_ins
             WHERE user_id = ? AND checked_in_at >= ?',
            [$userId, date('Y-m-d', strtotime('-7 days'))]
        ) ?? [];
        $sos = Database::row(
            'SELECT COUNT(*) AS total FROM sos_events WHERE user_id = ? AND created_at >= ?',
            [$userId, date('Y-m-d H:i:s', strtotime('-7 days'))]
        ) ?? [];
        $risk = RiskEngine::forUser($userId);

        return [
            'current_days' => (int) ($streak['current_days'] ?? 0),
            'avg_mood' => $recent['avg_mood'] ?? 'n/a',
            'avg_urge' => $recent['avg_urge'] ?? 'n/a',
            'recent_sos_count' => (int) ($sos['total'] ?? 0),
            'risk_level' => $risk['level'] ?? 'steady',
        ];
    }
}
