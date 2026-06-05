<?php
declare(strict_types=1);

class SOSController
{
    public function show(): void
    {
        $scripture = Cache::remember('sos_scripture', 300, function (): array {
            return Database::row('SELECT * FROM scriptures WHERE category = "sos" ORDER BY RAND() LIMIT 1')
                ?? ['reference' => '1 Corinthians 10:13', 'text' => 'God is faithful, and he will not let you be tempted beyond your ability.'];
        });
        $safetyPlan = Database::row('SELECT * FROM safety_plans WHERE user_id = ?', [Auth::id()]);

        view('sos/index', [
            'title' => 'SOS',
            'scripture' => $scripture,
            'safetyPlan' => $safetyPlan,
            'music' => $this->resources('music', 20),
            'videos' => $this->resources('video', 10),
            'games' => $this->resources('game', 10),
            'calmResources' => $this->resources('resource', 10),
        ]);
    }

    public function trigger(): void
    {
        $note = sanitize((string) ($_POST['note'] ?? ''));
        Database::insert('sos_events', [
            'user_id' => Auth::id(),
            'trigger_note' => $note,
            'created_at' => now(),
        ]);

        $alerted = $this->notifyPartners($note);
        if ($alerted > 0) {
            Database::query(
                'UPDATE sos_events SET partner_alerted = 1 WHERE user_id = ? AND resolved = 0 ORDER BY id DESC LIMIT 1',
                [Auth::id()]
            );
        }

        $suffix = $alerted > 0 ? " {$alerted} partner alert queued." : '';
        flash('success', 'SOS started. Move your body, change location, and use the next right action.' . $suffix);
        redirect('/sos');
    }

    public function resolve(): void
    {
        Database::query(
            'UPDATE sos_events SET resolved = 1, resolved_at = ? WHERE user_id = ? AND resolved = 0',
            [now(), Auth::id()]
        );
        flash('success', 'Resolved. That resistance counts.');
        redirect('/dashboard');
    }

    private function notifyPartners(string $note): int
    {
        $partners = Database::rows(
            'SELECT u.email, u.name
             FROM accountability_pairs ap
             INNER JOIN users u ON u.id = ap.partner_id
             WHERE ap.user_id = ? AND ap.status = "active" AND ap.sos_alerts = 1',
            [Auth::id()]
        );

        $user = Auth::user();
        foreach ($partners as $partner) {
            $body = "Hi {$partner['name']},\n\n"
                . ($user['name'] ?? 'Your accountability partner') . " started SOS mode in FreedomOS.\n\n"
                . "Pressure note: " . ($note !== '' ? $note : 'No note added') . "\n\n"
                . "Reach out with calm support. A simple message like \"I'm here. Take the next clean step.\" is enough.";

            Mailer::sendOrQueue('sos_alert', $partner['email'], 'FreedomOS SOS alert', $body, [
                'user_id' => Auth::id(),
            ]);
        }

        return count($partners);
    }

    private function resources(string $type, int $limit): array
    {
        return Database::rows(
            'SELECT * FROM sos_resources WHERE type = ? AND active = 1 ORDER BY sort_order ASC, title ASC LIMIT ' . (int) $limit,
            [$type]
        );
    }
}
