<?php
declare(strict_types=1);

class Analytics
{
    public static function progressForUser(int $userId, int $days = 30): array
    {
        $start = date('Y-m-d', strtotime('-' . max(0, $days - 1) . ' days'));
        $rows = Database::rows(
            'SELECT checked_in_at, mood, urge_level, relapsed, prayer_done, scripture_read
             FROM check_ins
             WHERE user_id = ? AND checked_in_at >= ?
             ORDER BY checked_in_at ASC',
            [$userId, $start]
        );

        $sos = Database::rows(
            'SELECT DATE(created_at) AS day, COUNT(*) AS total
             FROM sos_events
             WHERE user_id = ? AND created_at >= ?
             GROUP BY DATE(created_at)
             ORDER BY day ASC',
            [$userId, $start . ' 00:00:00']
        );

        $goals = Database::rows(
            'SELECT title, category, DATE(completed_at) AS day
             FROM goals
             WHERE user_id = ? AND completed_at IS NOT NULL AND completed_at >= ?
             ORDER BY completed_at DESC',
            [$userId, $start . ' 00:00:00']
        );

        $blocked = Database::rows(
            'SELECT DATE(attempted_at) AS day, COUNT(*) AS total
             FROM blocker_logs
             WHERE user_id = ? AND attempted_at >= ?
             GROUP BY DATE(attempted_at)
             ORDER BY day ASC',
            [$userId, $start . ' 00:00:00']
        );

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row['checked_in_at']] = $row;
        }

        $sosByDate = array_column($sos, 'total', 'day');
        $blockedByDate = array_column($blocked, 'total', 'day');
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $row = $byDate[$date] ?? null;
            $series[] = [
                'date' => $date,
                'mood' => $row ? (int) $row['mood'] : null,
                'urge' => $row ? (int) $row['urge_level'] : null,
                'relapsed' => $row ? (int) $row['relapsed'] : 0,
                'checked_in' => $row !== null,
                'sos' => (int) ($sosByDate[$date] ?? 0),
                'blocked' => (int) ($blockedByDate[$date] ?? 0),
            ];
        }

        $checkins = count($rows);
        $relapses = array_sum(array_map(fn (array $row): int => (int) $row['relapsed'], $rows));
        $avgMood = self::avg($rows, 'mood');
        $avgUrge = self::avg($rows, 'urge_level');
        $cleanDays = $checkins - $relapses;
        $sosTotal = array_sum(array_map(fn (array $row): int => (int) $row['total'], $sos));
        $blockedTotal = array_sum(array_map(fn (array $row): int => (int) $row['total'], $blocked));

        return [
            'series' => $series,
            'summary' => [
                'days' => $days,
                'checkins' => $checkins,
                'clean_days' => $cleanDays,
                'relapses' => $relapses,
                'avg_mood' => $avgMood,
                'avg_urge' => $avgUrge,
                'sos_total' => $sosTotal,
                'blocked_total' => $blockedTotal,
                'checkin_rate' => $days > 0 ? round(($checkins / $days) * 100) : 0,
            ],
            'insights' => self::insights($checkins, $days, $avgMood, $avgUrge, $relapses, $sosTotal, $blockedTotal),
            'plan' => self::plan($checkins, $days, $avgMood, $avgUrge, $relapses, $sosTotal, $blockedTotal),
            'victory' => self::victory($series, $goals, $cleanDays, $sosTotal, $blockedTotal),
        ];
    }

    private static function avg(array $rows, string $key): float
    {
        if (!$rows) {
            return 0.0;
        }
        return round(array_sum(array_map(fn (array $row): int => (int) $row[$key], $rows)) / count($rows), 1);
    }

    private static function insights(int $checkins, int $days, float $avgMood, float $avgUrge, int $relapses, int $sos, int $blocked): array
    {
        $insights = [];
        if ($checkins < max(3, (int) floor($days * 0.4))) {
            $insights[] = 'Your signal is thin. A steady check-in rhythm will make insights more useful.';
        }
        if ($avgUrge >= 4) {
            $insights[] = 'Average urge is high. Treat this as a support week, not a willpower week.';
        }
        if ($avgMood > 0 && $avgMood <= 2) {
            $insights[] = 'Mood is low. Add recovery actions that support sleep, movement, and connection.';
        }
        if ($relapses > 0) {
            $insights[] = 'Recent resets are data. Review the hour before each reset and strengthen the escape plan.';
        }
        if ($sos > 0 || $blocked > 0) {
            $insights[] = 'Pressure showed up, and you used protection systems. That is a win to build from.';
        }

        return $insights ?: ['Your recent pattern looks steady. Keep the rhythm simple and repeatable.'];
    }

    private static function plan(int $checkins, int $days, float $avgMood, float $avgUrge, int $relapses, int $sos, int $blocked): array
    {
        if ($relapses > 0 || $avgUrge >= 4 || $blocked >= 5) {
            return [
                'focus' => 'Protection week',
                'why' => 'Your signals show elevated pressure, so the goal is to reduce exposure and increase fast support.',
                'moves' => [
                    'Add one stricter Guard rule for the highest-risk domain or keyword.',
                    'Choose a no-phone window for the hour you are most vulnerable.',
                    'Send your accountability partner one honest check-in before the urge peaks.',
                ],
                'partner_prompt' => 'Ask your partner to check in once today and once near your riskiest hour.',
            ];
        }

        if ($checkins < max(3, (int) floor($days * 0.4))) {
            return [
                'focus' => 'Signal building',
                'why' => 'The system needs more daily data before it can spot reliable patterns.',
                'moves' => [
                    'Set a fixed check-in time for the next seven days.',
                    'Keep the note short: trigger, feeling, next right action.',
                    'Use SOS at the first warning sign instead of waiting for a crisis.',
                ],
                'partner_prompt' => 'Ask your partner to celebrate consistency, not perfection, this week.',
            ];
        }

        if ($avgMood > 0 && $avgMood <= 2.5) {
            return [
                'focus' => 'Stability week',
                'why' => 'Low mood can weaken resistance, even when urges are not extreme.',
                'moves' => [
                    'Protect sleep and add a short walk before screen-heavy time.',
                    'Write one truth statement in Purpose and reread it during check-in.',
                    'Schedule one connection point with a safe person.',
                ],
                'partner_prompt' => 'Ask your partner for encouragement rather than pressure this week.',
            ];
        }

        return [
            'focus' => 'Momentum week',
            'why' => 'Your recent pattern looks steady enough to build confidence and reinforce what works.',
            'moves' => [
                'Keep the daily check-in streak alive.',
                'Review one goal in Purpose and complete a small next step.',
                'Keep Guard active even when you feel strong.',
            ],
            'partner_prompt' => 'Ask your partner to notice one specific win from this week.',
        ];
    }

    private static function victory(array $series, array $goals, int $cleanDays, int $sosTotal, int $blockedTotal): array
    {
        $timeline = [];
        $nearMissWins = 0;
        $reboundDays = 0;
        $protectedDays = 0;
        $previousRelapsed = false;

        foreach ($series as $day) {
            $usedProtection = $day['sos'] > 0 || $day['blocked'] > 0;
            $clean = $day['checked_in'] && !$day['relapsed'];

            if ($clean || $usedProtection) {
                $protectedDays++;
            }

            if ($usedProtection && !$day['relapsed']) {
                $nearMissWins++;
                $timeline[] = [
                    'date' => $day['date'],
                    'type' => 'Protected',
                    'title' => 'Pressure interrupted',
                    'body' => 'SOS or Guard showed up before a reset. That counts as a real recovery rep.',
                ];
            } elseif ($clean) {
                $timeline[] = [
                    'date' => $day['date'],
                    'type' => 'Clean',
                    'title' => 'Clean check-in',
                    'body' => 'You told the truth and stayed clean for the day.',
                ];
            } elseif ($day['relapsed']) {
                $timeline[] = [
                    'date' => $day['date'],
                    'type' => 'Reset',
                    'title' => 'Reset recorded',
                    'body' => 'A reset is data, not identity. The next clean response matters.',
                ];
            }

            if ($previousRelapsed && $clean) {
                $reboundDays++;
                $timeline[] = [
                    'date' => $day['date'],
                    'type' => 'Rebound',
                    'title' => 'Returned after a reset',
                    'body' => 'You came back to the rhythm instead of disappearing.',
                ];
            }

            $previousRelapsed = (bool) $day['relapsed'];
        }

        foreach ($goals as $goal) {
            $timeline[] = [
                'date' => $goal['day'],
                'type' => 'Purpose',
                'title' => 'Goal completed',
                'body' => ucfirst((string) $goal['category']) . ': ' . $goal['title'],
            ];
        }

        usort($timeline, fn (array $a, array $b): int => strcmp($b['date'], $a['date']));

        return [
            'metrics' => [
                'clean_decisions' => $cleanDays + $sosTotal + $blockedTotal,
                'near_miss_wins' => $nearMissWins,
                'rebound_days' => $reboundDays,
                'protected_days' => $protectedDays,
                'purpose_wins' => count($goals),
            ],
            'timeline' => array_slice($timeline, 0, 12),
        ];
    }
}
