<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

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

$force = in_array('--force', $argv ?? [], true);
$weekStart = date('Y-m-d', strtotime('monday this week'));
$weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

$pairs = Database::rows(
    'SELECT ap.*, user.name AS user_name, user.email AS user_email,
            partner.name AS partner_name, partner.email AS partner_email,
            s.current_days, s.longest_days
     FROM accountability_pairs ap
     INNER JOIN users user ON user.id = ap.user_id
     INNER JOIN users partner ON partner.id = ap.partner_id
     LEFT JOIN streaks s ON s.user_id = ap.user_id
     WHERE ap.status = "active" AND ap.weekly_digest = 1'
);

$queued = 0;
foreach ($pairs as $pair) {
    if (!$force && Database::row('SELECT id FROM digest_runs WHERE pair_id = ? AND week_start = ?', [$pair['id'], $weekStart])) {
        continue;
    }

    $stats = Database::row(
        'SELECT COUNT(*) AS checkin_count,
                ROUND(AVG(mood), 1) AS avg_mood,
                ROUND(AVG(urge_level), 1) AS avg_urge,
                SUM(CASE WHEN relapsed = 0 THEN 1 ELSE 0 END) AS clean_checkins,
                SUM(CASE WHEN relapsed = 1 THEN 1 ELSE 0 END) AS relapse_count
         FROM check_ins
         WHERE user_id = ? AND checked_in_at BETWEEN ? AND ?',
        [$pair['user_id'], $weekStart, $weekEnd]
    ) ?? [];

    $sosStats = Database::row(
        'SELECT COUNT(*) AS sos_count,
                SUM(CASE WHEN resolved = 0 THEN 1 ELSE 0 END) AS open_sos
         FROM sos_events
         WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?',
        [$pair['user_id'], $weekStart, $weekEnd]
    ) ?? [];

    $body = build_digest_body($pair, $stats, $sosStats, $weekStart, $weekEnd);
    Mailer::sendOrQueue(
        'weekly_digest',
        $pair['partner_email'],
        'FreedomOS weekly digest for ' . $pair['user_name'],
        $body,
        [
            'pair_id' => (int) $pair['id'],
            'user_id' => (int) $pair['user_id'],
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
        ]
    );

    Database::insert('digest_runs', [
        'pair_id' => $pair['id'],
        'week_start' => $weekStart,
        'week_end' => $weekEnd,
        'recipient' => $pair['partner_email'],
        'created_at' => now(),
    ]);

    $queued++;
}

echo "Weekly digests queued: {$queued}\n";

function build_digest_body(array $pair, array $stats, array $sosStats, string $weekStart, string $weekEnd): string
{
    $checkins = (int) ($stats['checkin_count'] ?? 0);
    $clean = (int) ($stats['clean_checkins'] ?? 0);
    $avgMood = $stats['avg_mood'] ?? 'n/a';
    $avgUrge = $stats['avg_urge'] ?? 'n/a';
    $sosCount = (int) ($sosStats['sos_count'] ?? 0);
    $openSos = (int) ($sosStats['open_sos'] ?? 0);

    $body = "Hi {$pair['partner_name']},\n\n"
        . "Here is {$pair['user_name']}'s FreedomOS weekly digest for {$weekStart} to {$weekEnd}.\n\n"
        . "Current streak: " . (int) ($pair['current_days'] ?? 0) . " days\n"
        . "Longest streak: " . (int) ($pair['longest_days'] ?? 0) . " days\n"
        . "Check-ins this week: {$checkins}\n"
        . "Clean check-ins: {$clean}\n"
        . "Average mood: {$avgMood}/5\n"
        . "Average urge level: {$avgUrge}/5\n"
        . "SOS events: {$sosCount}\n"
        . "Open SOS events: {$openSos}\n";

    if ((int) $pair['relapse_visibility'] === 1) {
        $body .= "Relapse resets this week: " . (int) ($stats['relapse_count'] ?? 0) . "\n";
    }

    $body .= "\nSupport prompt: send one specific encouragement, ask one simple question, and avoid shame.\n"
        . "Open your partner dashboard: " . absolute_url('/partner') . "\n";

    return $body;
}
