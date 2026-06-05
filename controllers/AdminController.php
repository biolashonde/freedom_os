<?php
declare(strict_types=1);

class AdminController
{
    public function dashboard(): void
    {
        $stats = [
            'users' => $this->count('users', 'deleted_at IS NULL'),
            'check_ins' => $this->count('check_ins'),
            'sos_events' => $this->count('sos_events'),
            'active_pairs' => $this->count('accountability_pairs', 'status = "active"'),
            'blocker_logs' => $this->count('blocker_logs'),
            'pending_overrides' => $this->count('blocker_overrides', 'status = "pending"'),
            'public_testimonies' => $this->count('testimonies', 'is_public = 1'),
        ];

        view('admin/dashboard', [
            'title' => 'Admin',
            'stats' => $stats,
            'users' => Database::rows(
                'SELECT u.id, u.name, u.email, u.role, u.created_at,
                        s.current_days, s.longest_days,
                        (SELECT COUNT(*) FROM check_ins ci WHERE ci.user_id = u.id) AS checkin_count,
                        (SELECT COUNT(*) FROM sos_events se WHERE se.user_id = u.id) AS sos_count
                 FROM users u
                 LEFT JOIN streaks s ON s.user_id = u.id
                 WHERE u.deleted_at IS NULL
                 ORDER BY u.created_at DESC
                 LIMIT 25'
            ),
            'testimonies' => Database::rows(
                'SELECT t.*, u.name, u.email
                 FROM testimonies t
                 INNER JOIN users u ON u.id = t.user_id
                 ORDER BY t.updated_at DESC
                 LIMIT 20'
            ),
            'overrides' => Database::rows(
                'SELECT bo.*, u.name, u.email
                 FROM blocker_overrides bo
                 INNER JOIN users u ON u.id = bo.user_id
                 WHERE bo.status = "pending"
                 ORDER BY bo.requested_at ASC
                 LIMIT 25'
            ),
            'blockerLogs' => Database::rows(
                'SELECT bl.*, u.name, u.email
                 FROM blocker_logs bl
                 LEFT JOIN users u ON u.id = bl.user_id
                 ORDER BY bl.attempted_at DESC
                 LIMIT 25'
            ),
        ]);
    }

    public function analytics(): void
    {
        $overview = [
            'total_users' => $this->count('users', 'deleted_at IS NULL'),
            'new_users_30d' => $this->count('users', 'deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'),
            'active_users_7d' => $this->activeUsers(7),
            'active_users_30d' => $this->activeUsers(30),
            'checkins_30d' => $this->count('check_ins', 'checked_in_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)'),
            'resets_30d' => $this->count('check_ins', 'relapsed = 1 AND checked_in_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)'),
            'sos_30d' => $this->count('sos_events', 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'),
            'guard_blocks_30d' => $this->count('blocker_logs', 'attempted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'),
            'active_pairs' => $this->count('accountability_pairs', 'status = "active"'),
            'ai_devotionals' => $this->count('devotionals', 'ai_generated = 1'),
        ];
        $overview['activation_rate'] = $overview['total_users'] > 0
            ? round(($this->usersWithCheckins() / $overview['total_users']) * 100)
            : 0;
        $overview['reset_rate_30d'] = $overview['checkins_30d'] > 0
            ? round(($overview['resets_30d'] / $overview['checkins_30d']) * 100)
            : 0;

        view('admin/analytics', [
            'title' => 'Superadmin Analytics',
            'overview' => $overview,
            'daily' => $this->dailyAnalytics(30),
            'riskUsers' => $this->riskUsers(),
            'funnel' => [
                'users' => $overview['total_users'],
                'checked_in' => $this->usersWithCheckins(),
                'with_partner' => $this->usersWithPartners(),
                'with_guard' => $this->usersWithGuard(),
                'with_purpose' => $this->usersWithPurpose(),
            ],
            'system' => [
                'nudges_today' => $this->count('nudge_runs', 'run_date = CURDATE()'),
                'queued_mail' => $this->count('notification_logs', 'status = "queued"'),
                'sent_mail' => $this->count('notification_logs', 'status = "sent"'),
                'pending_overrides' => $this->count('blocker_overrides', 'status = "pending"'),
                'devices' => $this->count('blocker_devices'),
            ],
        ]);
    }

    public function donations(): void
    {
        view('admin/donations', [
            'title' => 'Donation Settings',
            'donation' => DonationSettings::get(),
        ]);
    }

    public function saveDonations(): void
    {
        DonationSettings::save($_POST);
        flash('success', 'Donation settings saved.');
        redirect('/admin/donations');
    }

    public function settings(): void
    {
        view('admin/settings', [
            'title' => 'App Settings',
            'settings' => AppSettings::groups(),
        ]);
    }

    public function saveSettings(): void
    {
        AppSettings::save($_POST);
        flash('success', 'App settings saved. Secret fields left blank were kept unchanged.');
        redirect('/admin/settings');
    }

    public function updateTestimonyVisibility(string $testimonyId): void
    {
        Database::query(
            'UPDATE testimonies SET is_public = ?, updated_at = ? WHERE id = ?',
            [isset($_POST['is_public']) ? 1 : 0, now(), (int) $testimonyId]
        );
        flash('success', 'Testimony visibility updated.');
        redirect('/admin');
    }

    public function reviewOverride(string $overrideId): void
    {
        $decision = (string) ($_POST['decision'] ?? '');
        if (!in_array($decision, ['approved', 'denied'], true)) {
            flash('error', 'Choose approve or deny.');
            redirect('/admin');
        }

        $override = Database::row('SELECT * FROM blocker_overrides WHERE id = ? AND status = "pending"', [(int) $overrideId]);
        if (!$override) {
            flash('error', 'Override request is not pending.');
            redirect('/admin');
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
                    'reason' => 'admin approved override',
                    'active' => 1,
                    'created_at' => now(),
                ]);
            }
        }

        flash('success', 'Override ' . $decision . '.');
        redirect('/admin');
    }

    private function count(string $table, string $where = '1=1'): int
    {
        $table = str_replace('`', '', $table);
        $row = Database::row("SELECT COUNT(*) AS total FROM `{$table}` WHERE {$where}");
        return (int) ($row['total'] ?? 0);
    }

    private function activeUsers(int $days): int
    {
        $row = Database::row(
            'SELECT COUNT(DISTINCT user_id) AS total FROM (
                SELECT user_id FROM check_ins WHERE checked_in_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                UNION
                SELECT user_id FROM sos_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                UNION
                SELECT user_id FROM blocker_logs WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ) active',
            [$days - 1, $days, $days]
        );
        return (int) ($row['total'] ?? 0);
    }

    private function usersWithCheckins(): int
    {
        $row = Database::row('SELECT COUNT(DISTINCT user_id) AS total FROM check_ins');
        return (int) ($row['total'] ?? 0);
    }

    private function usersWithPartners(): int
    {
        $row = Database::row('SELECT COUNT(DISTINCT user_id) AS total FROM accountability_pairs WHERE status = "active"');
        return (int) ($row['total'] ?? 0);
    }

    private function usersWithGuard(): int
    {
        $row = Database::row('SELECT COUNT(DISTINCT user_id) AS total FROM blocker_devices');
        return (int) ($row['total'] ?? 0);
    }

    private function usersWithPurpose(): int
    {
        $row = Database::row(
            'SELECT COUNT(DISTINCT user_id) AS total FROM (
                SELECT user_id FROM safety_plans
                UNION
                SELECT user_id FROM goals
                UNION
                SELECT user_id FROM testimonies
            ) purpose'
        );
        return (int) ($row['total'] ?? 0);
    }

    private function dailyAnalytics(int $days): array
    {
        $rows = Database::rows(
            'SELECT d.day,
                    COALESCE(u.total, 0) AS signups,
                    COALESCE(c.total, 0) AS checkins,
                    COALESCE(r.total, 0) AS resets,
                    COALESCE(s.total, 0) AS sos,
                    COALESCE(b.total, 0) AS guard_blocks
             FROM (
                SELECT CURDATE() - INTERVAL seq DAY AS day
                FROM (
                    SELECT 0 seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                    UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14
                    UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19
                    UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
                    UNION ALL SELECT 25 UNION ALL SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29
                ) seqs
                WHERE seq < ?
             ) d
             LEFT JOIN (SELECT DATE(created_at) day, COUNT(*) total FROM users WHERE deleted_at IS NULL GROUP BY DATE(created_at)) u ON u.day = d.day
             LEFT JOIN (SELECT checked_in_at day, COUNT(*) total FROM check_ins GROUP BY checked_in_at) c ON c.day = d.day
             LEFT JOIN (SELECT checked_in_at day, COUNT(*) total FROM check_ins WHERE relapsed = 1 GROUP BY checked_in_at) r ON r.day = d.day
             LEFT JOIN (SELECT DATE(created_at) day, COUNT(*) total FROM sos_events GROUP BY DATE(created_at)) s ON s.day = d.day
             LEFT JOIN (SELECT DATE(attempted_at) day, COUNT(*) total FROM blocker_logs GROUP BY DATE(attempted_at)) b ON b.day = d.day
             ORDER BY d.day ASC',
            [$days]
        );
        return $rows;
    }

    private function riskUsers(): array
    {
        return Database::rows(
            'SELECT *
             FROM (
                SELECT u.id, u.name, u.email, u.created_at,
                        COALESCE(st.current_days, 0) AS current_days,
                        MAX(ci.checked_in_at) AS last_checkin,
                        SUM(CASE WHEN ci.relapsed = 1 AND ci.checked_in_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) THEN 1 ELSE 0 END) AS resets_30d,
                        (SELECT COUNT(*) FROM sos_events se WHERE se.user_id = u.id AND se.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS sos_30d,
                        (SELECT COUNT(*) FROM blocker_logs bl WHERE bl.user_id = u.id AND bl.attempted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS guard_30d
                 FROM users u
                 LEFT JOIN streaks st ON st.user_id = u.id
                 LEFT JOIN check_ins ci ON ci.user_id = u.id
                 WHERE u.deleted_at IS NULL AND u.role = "user"
                 GROUP BY u.id, u.name, u.email, u.created_at, st.current_days
             ) risk
             WHERE resets_30d > 0 OR sos_30d > 0 OR guard_30d > 0 OR last_checkin IS NULL OR last_checkin < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             ORDER BY (resets_30d * 5 + sos_30d * 3 + guard_30d) DESC, last_checkin ASC
             LIMIT 25'
        );
    }
}
