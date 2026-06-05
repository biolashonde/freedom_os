<?php
declare(strict_types=1);

class PartnerController
{
    public function dashboard(): void
    {
        $pairs = Database::rows(
            'SELECT ap.*, u.name AS user_name, u.email AS user_email,
                    s.current_days, s.longest_days, s.total_relapses, s.last_clean_date,
                    sp.top_trigger, sp.escape_action, sp.truth_statement
             FROM accountability_pairs ap
             INNER JOIN users u ON u.id = ap.user_id
             LEFT JOIN streaks s ON s.user_id = ap.user_id
             LEFT JOIN safety_plans sp ON sp.user_id = ap.user_id
             WHERE ap.partner_id = ? AND ap.status = "active"
             ORDER BY ap.paired_at DESC',
            [Auth::id()]
        );

        $summaries = [];
        foreach ($pairs as $pair) {
            $summaries[(int) $pair['id']] = [
                'recent_checkins' => Database::rows(
                    'SELECT mood, urge_level, relapsed, checked_in_at
                     FROM check_ins
                     WHERE user_id = ?
                     ORDER BY checked_in_at DESC
                     LIMIT 7',
                    [$pair['user_id']]
                ),
                'open_sos' => Database::row(
                    'SELECT * FROM sos_events
                     WHERE user_id = ? AND resolved = 0
                     ORDER BY created_at DESC
                     LIMIT 1',
                    [$pair['user_id']]
                ),
                'recent_sos' => Database::rows(
                    'SELECT trigger_note, resolved, resolved_at, created_at
                     FROM sos_events
                     WHERE user_id = ?
                     ORDER BY created_at DESC
                     LIMIT 5',
                    [$pair['user_id']]
                ),
                'recent_blocked' => Database::rows(
                    'SELECT blocked_url, reason, partner_notified, attempted_at
                     FROM blocker_logs
                     WHERE user_id = ?
                     ORDER BY attempted_at DESC
                     LIMIT 5',
                    [$pair['user_id']]
                ),
                'pending_overrides' => Database::rows(
                    'SELECT * FROM blocker_overrides
                     WHERE user_id = ? AND status = "pending"
                     ORDER BY requested_at DESC
                     LIMIT 5',
                    [$pair['user_id']]
                ),
            ];
        }

        view('partner/dashboard', [
            'title' => 'Partner dashboard',
            'pairs' => $pairs,
            'summaries' => $summaries,
        ]);
    }

    public function encourage(string $pairId): void
    {
        $pair = Database::row(
            'SELECT ap.*, u.name AS user_name, u.email AS user_email
             FROM accountability_pairs ap
             INNER JOIN users u ON u.id = ap.user_id
             WHERE ap.id = ? AND ap.partner_id = ? AND ap.status = "active"',
            [(int) $pairId, Auth::id()]
        );

        if (!$pair) {
            flash('error', 'That accountability connection was not found.');
            redirect('/partner');
        }

        $message = sanitize((string) ($_POST['message'] ?? ''));
        if ($message === '') {
            $message = 'I am with you today. Take the next clean step.';
        }

        $partner = Auth::user();
        $body = "Hi {$pair['user_name']},\n\n"
            . ($partner['name'] ?? 'Your accountability partner') . " sent encouragement:\n\n"
            . $message . "\n\n"
            . "Keep going. One faithful decision at a time.";

        Mailer::sendOrQueue('partner_encouragement', $pair['user_email'], 'Encouragement from your accountability partner', $body, [
            'pair_id' => (int) $pair['id'],
            'partner_id' => Auth::id(),
        ]);

        flash('success', 'Encouragement queued.');
        redirect('/partner');
    }

    public function reviewOverride(string $overrideId): void
    {
        $decision = (string) ($_POST['decision'] ?? '');
        if (!in_array($decision, ['approved', 'denied'], true)) {
            flash('error', 'Choose approve or deny.');
            redirect('/partner');
        }

        $override = Database::row(
            'SELECT bo.*, ap.id AS pair_id
             FROM blocker_overrides bo
             INNER JOIN accountability_pairs ap ON ap.user_id = bo.user_id
             WHERE bo.id = ? AND ap.partner_id = ? AND ap.status = "active" AND bo.status = "pending"',
            [(int) $overrideId, Auth::id()]
        );

        if (!$override) {
            flash('error', 'Override request was not found or is no longer pending.');
            redirect('/partner');
        }

        Database::query(
            'UPDATE blocker_overrides SET status = ?, reviewed_at = ? WHERE id = ?',
            [$decision, now(), $override['id']]
        );

        if ($decision === 'approved') {
            $host = mb_strtolower((string) parse_url((string) $override['url'], PHP_URL_HOST));
            if ($host !== '') {
                Database::insert('blocker_rules', [
                    'user_id' => $override['user_id'],
                    'rule_type' => 'domain',
                    'pattern' => $host,
                    'action' => 'allow',
                    'reason' => 'partner approved override',
                    'active' => 1,
                    'created_at' => now(),
                ]);
            }
        }

        $user = Database::row('SELECT email, name FROM users WHERE id = ?', [$override['user_id']]);
        if ($user) {
            Mailer::sendOrQueue(
                'override_' . $decision,
                $user['email'],
                'FreedomGuard override ' . $decision,
                "Hi {$user['name']},\n\nYour override request for {$override['url']} was {$decision}.",
                ['override_id' => (int) $override['id'], 'partner_id' => Auth::id()]
            );
        }

        flash('success', 'Override request ' . $decision . '.');
        redirect('/partner');
    }
}
