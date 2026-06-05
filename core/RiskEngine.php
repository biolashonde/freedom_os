<?php
declare(strict_types=1);

class RiskEngine
{
    public static function forUser(int $userId): array
    {
        $recent = Database::rows(
            'SELECT mood, urge_level, relapsed, checked_in_at
             FROM check_ins
             WHERE user_id = ?
             ORDER BY checked_in_at DESC
             LIMIT 14',
            [$userId]
        );

        $openSos = Database::row(
            'SELECT id, trigger_note, created_at
             FROM sos_events
             WHERE user_id = ? AND resolved = 0
             ORDER BY created_at DESC
             LIMIT 1',
            [$userId]
        );

        $lastCheckin = $recent[0] ?? null;
        $signals = [];
        $score = 0;

        if ($openSos) {
            $score += 35;
            $signals[] = 'SOS mode is currently open.';
        }

        if (!$lastCheckin) {
            $score += 20;
            $signals[] = 'No check-ins yet, so the app has little signal.';
        } else {
            $daysSince = self::daysSince((string) $lastCheckin['checked_in_at']);
            if ($daysSince >= 2) {
                $score += min(30, $daysSince * 8);
                $signals[] = "No check-in for {$daysSince} days.";
            }
        }

        $lastThree = array_slice($recent, 0, 3);
        $avgUrge = self::average($lastThree, 'urge_level');
        $avgMood = self::average($lastThree, 'mood');

        if ($avgUrge >= 4) {
            $score += 25;
            $signals[] = 'Urges have been high in recent check-ins.';
        } elseif ($avgUrge >= 3) {
            $score += 12;
            $signals[] = 'Urges are elevated.';
        }

        if ($avgMood > 0 && $avgMood <= 2) {
            $score += 18;
            $signals[] = 'Mood has been low recently.';
        }

        $recentRelapses = array_sum(array_map(fn (array $row): int => (int) $row['relapsed'], array_slice($recent, 0, 7)));
        if ($recentRelapses > 0) {
            $score += min(25, $recentRelapses * 12);
            $signals[] = 'A reset happened recently; plan for extra support.';
        }

        $score = min(100, $score);
        $level = self::level($score);

        return [
            'score' => $score,
            'level' => $level,
            'signals' => $signals ?: ['No elevated risk signals from recent check-ins.'],
            'next_actions' => self::nextActions($level, $openSos !== null),
            'avg_urge' => $avgUrge,
            'avg_mood' => $avgMood,
        ];
    }

    private static function average(array $rows, string $key): float
    {
        if (!$rows) {
            return 0.0;
        }
        $values = array_map(fn (array $row): int => (int) ($row[$key] ?? 0), $rows);
        return round(array_sum($values) / count($values), 1);
    }

    private static function daysSince(string $date): int
    {
        $today = new DateTimeImmutable(date('Y-m-d'));
        $then = new DateTimeImmutable($date);
        return (int) $then->diff($today)->format('%a');
    }

    private static function level(int $score): string
    {
        if ($score >= 65) {
            return 'high';
        }
        if ($score >= 35) {
            return 'guarded';
        }
        return 'steady';
    }

    private static function nextActions(string $level, bool $openSos): array
    {
        if ($openSos || $level === 'high') {
            return [
                'Open SOS and change location now.',
                'Message or call an accountability partner.',
                'Do a 10-minute no-screen reset.',
            ];
        }

        if ($level === 'guarded') {
            return [
                'Check in honestly before the evening.',
                'Review your safety plan.',
                'Remove one easy access point before it becomes pressure.',
            ];
        }

        return [
            'Keep the daily check-in rhythm.',
            'Do one purpose-building action today.',
            'Encourage someone else if you have capacity.',
        ];
    }
}
