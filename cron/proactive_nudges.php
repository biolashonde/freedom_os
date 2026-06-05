<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

if (file_exists(ROOT . '/.env')) {
    foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

spl_autoload_register(function (string $class): void {
    foreach ([ROOT . '/core/', ROOT . '/controllers/', ROOT . '/models/'] as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once ROOT . '/core/helpers.php';

$today = date('Y-m-d');
$queued = 0;
$force = in_array('--force', $argv ?? [], true);

$users = Database::rows(
    'SELECT u.id, u.name, u.email, u.created_at,
            MAX(ci.checked_in_at) AS last_checkin,
            ROUND(AVG(CASE WHEN ci.checked_in_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN ci.urge_level END), 1) AS avg_urge_7d,
            SUM(CASE WHEN ci.relapsed = 1 AND ci.checked_in_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) THEN 1 ELSE 0 END) AS resets_7d,
            (SELECT COUNT(*) FROM sos_events se WHERE se.user_id = u.id AND se.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS sos_7d,
            (SELECT COUNT(*) FROM blocker_logs bl WHERE bl.user_id = u.id AND bl.attempted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS guard_7d
     FROM users u
     LEFT JOIN check_ins ci ON ci.user_id = u.id
     WHERE u.deleted_at IS NULL AND u.role = "user"
     GROUP BY u.id, u.name, u.email, u.created_at
     ORDER BY u.created_at ASC'
);

foreach ($users as $user) {
    $lastCheckin = $user['last_checkin'] ?? null;
    $missedCheckin = $lastCheckin !== $today;
    $highRisk = ((float) ($user['avg_urge_7d'] ?? 0) >= 4.0)
        || ((int) ($user['resets_7d'] ?? 0) > 0)
        || ((int) ($user['sos_7d'] ?? 0) > 0)
        || ((int) ($user['guard_7d'] ?? 0) >= 3);

    if ($missedCheckin && should_queue((int) $user['id'], 'missed_checkin', $today, $force)) {
        queue_user_nudge($user, 'missed_checkin', $today);
        $queued++;
    }

    if ($highRisk && should_queue((int) $user['id'], 'high_risk_support', $today, $force)) {
        queue_user_nudge($user, 'high_risk_support', $today);
        queue_partner_prompts($user, $today);
        $queued++;
    }
}

echo "Proactive nudges queued: {$queued}\n";

function should_queue(int $userId, string $type, string $today, bool $force): bool
{
    if ($force) {
        Database::query(
            'DELETE FROM nudge_runs WHERE user_id = ? AND nudge_type = ? AND run_date = ?',
            [$userId, $type, $today]
        );
        return true;
    }
    return !Database::row(
        'SELECT id FROM nudge_runs WHERE user_id = ? AND nudge_type = ? AND run_date = ?',
        [$userId, $type, $today]
    );
}

function queue_user_nudge(array $user, string $type, string $today): void
{
    $name = $user['name'] ?: 'friend';
    if ($type === 'missed_checkin') {
        $subject = 'A tiny FreedomOS check-in for today';
        $body = "Hi {$name},\n\nNo pressure and no shame. Take 30 seconds to check in today so your recovery signal stays alive.\n\n"
            . "Open your dashboard: " . absolute_url('/dashboard') . "\n\n"
            . "One honest check-in is a win.";
    } else {
        $subject = 'FreedomOS support nudge';
        $body = "Hi {$name},\n\nYour recent signals suggest this is a support day, not a willpower day.\n\n"
            . "Try one move now: open SOS, change location, message your partner, or add a stricter Guard rule.\n\n"
            . "Open your smart plan: " . absolute_url('/progress') . "\n";
    }

    Mailer::sendOrQueue($type, $user['email'], $subject, $body, [
        'user_id' => (int) $user['id'],
        'run_date' => $today,
    ]);

    Database::insert('nudge_runs', [
        'user_id' => (int) $user['id'],
        'nudge_type' => $type,
        'run_date' => $today,
        'recipient' => $user['email'],
        'created_at' => now(),
    ]);
}

function queue_partner_prompts(array $user, string $today): void
{
    $partners = Database::rows(
        'SELECT ap.id AS pair_id, partner.name, partner.email
         FROM accountability_pairs ap
         INNER JOIN users partner ON partner.id = ap.partner_id
         WHERE ap.user_id = ? AND ap.status = "active" AND ap.sos_alerts = 1',
        [(int) $user['id']]
    );

    foreach ($partners as $partner) {
        if (!should_queue((int) $user['id'], 'partner_support_prompt_' . $partner['pair_id'], $today, false)) {
            continue;
        }

        $body = "Hi {$partner['name']},\n\n"
            . "{$user['name']} may benefit from a simple support message today. Keep it specific, calm, and shame-free.\n\n"
            . "Suggested message: \"Thinking of you today. What is one clean next step I can encourage?\"\n\n"
            . "Partner dashboard: " . absolute_url('/partner') . "\n";

        Mailer::sendOrQueue(
            'partner_support_prompt',
            $partner['email'],
            'FreedomOS support prompt for ' . $user['name'],
            $body,
            [
                'user_id' => (int) $user['id'],
                'pair_id' => (int) $partner['pair_id'],
                'run_date' => $today,
            ]
        );

        Database::insert('nudge_runs', [
            'user_id' => (int) $user['id'],
            'nudge_type' => 'partner_support_prompt_' . $partner['pair_id'],
            'run_date' => $today,
            'recipient' => $partner['email'],
            'created_at' => now(),
        ]);
    }
}
