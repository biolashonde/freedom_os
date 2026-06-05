<?php
declare(strict_types=1);

class BlockerController
{
    public function index(): void
    {
        $userId = Auth::id();
        $devices = Database::rows(
            'SELECT id, name, token_prefix, last_seen_at, created_at FROM blocker_devices WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
        $rules = Database::rows(
            'SELECT * FROM blocker_rules WHERE user_id = ? OR user_id IS NULL ORDER BY user_id IS NULL DESC, rule_type, pattern',
            [$userId]
        );
        $logs = Database::rows(
            'SELECT * FROM blocker_logs WHERE user_id = ? ORDER BY attempted_at DESC LIMIT 20',
            [$userId]
        );
        $overrides = Database::rows(
            'SELECT * FROM blocker_overrides WHERE user_id = ? ORDER BY requested_at DESC LIMIT 10',
            [$userId]
        );

        view('blocker/index', [
            'title' => 'FreedomGuard',
            'devices' => $devices,
            'rules' => $rules,
            'logs' => $logs,
            'overrides' => $overrides,
            'newToken' => flash('device_token'),
        ]);
    }

    public function createDevice(): void
    {
        $name = sanitize((string) ($_POST['name'] ?? 'My device'));
        $token = 'fg_' . bin2hex(random_bytes(32));
        Database::insert('blocker_devices', [
            'user_id' => Auth::id(),
            'name' => $name !== '' ? $name : 'My device',
            'token_hash' => hash('sha256', $token),
            'token_prefix' => substr($token, 0, 10),
            'created_at' => now(),
        ]);

        flash('device_token', $token);
        flash('success', 'Device token created. Copy it now; it will not be shown again.');
        redirect('/guard');
    }

    public function mobile(): void
    {
        view('blocker/mobile', ['title' => 'FreedomGuard mobile']);
    }

    public function downloadExtension(): never
    {
        if (!class_exists('ZipArchive')) {
            flash('error', 'PHP ZipArchive is not enabled. Install/enable zip support or load the unpacked folder manually.');
            redirect('/guard');
        }

        $source = ROOT . '/freedomguard/extension';
        $zipPath = ROOT . '/storage/cache/freedomguard-extension.zip';
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            flash('error', 'Could not create extension ZIP.');
            redirect('/guard');
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $zip->addFile($file->getPathname(), str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1)));
            }
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="freedomguard-extension.zip"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        exit;
    }

    public function storeRule(): void
    {
        $pattern = mb_strtolower(trim((string) ($_POST['pattern'] ?? '')));
        $type = (string) ($_POST['rule_type'] ?? 'domain');
        if ($pattern === '' || !in_array($type, ['domain', 'keyword'], true)) {
            flash('error', 'Add a domain or keyword rule.');
            redirect('/guard');
        }

        Database::insert('blocker_rules', [
            'user_id' => Auth::id(),
            'rule_type' => $type,
            'pattern' => $pattern,
            'reason' => sanitize((string) ($_POST['reason'] ?? 'custom')),
            'active' => 1,
            'created_at' => now(),
        ]);

        flash('success', 'FreedomGuard rule added.');
        redirect('/guard');
    }

    public function requestOverride(): void
    {
        $url = trim((string) ($_POST['url'] ?? ''));
        $reason = sanitize((string) ($_POST['reason'] ?? ''));
        if ($url === '' || $reason === '') {
            flash('error', 'Add the URL and a reason for the override request.');
            redirect('/guard');
        }

        Database::insert('blocker_overrides', [
            'user_id' => Auth::id(),
            'url' => $url,
            'reason' => $reason,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        flash('success', 'Override request saved as pending.');
        redirect('/guard');
    }

    public function blockedPage(): void
    {
        view('blocker/blocked', [
            'title' => 'Blocked',
            'url' => $_GET['url'] ?? '',
            'reason' => $_GET['reason'] ?? 'FreedomGuard blocked this page.',
        ]);
    }

    public function check(): never
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            json(['error' => 'Invalid JSON'], 400);
        }

        $token = (string) ($payload['token'] ?? '');
        $url = trim((string) ($payload['url'] ?? ''));
        if ($token === '' || $url === '') {
            json(['error' => 'Token and URL are required'], 422);
        }

        $device = Database::row('SELECT * FROM blocker_devices WHERE token_hash = ?', [hash('sha256', $token)]);
        if (!$device) {
            json(['error' => 'Invalid device token'], 401);
        }

        Database::query('UPDATE blocker_devices SET last_seen_at = ? WHERE id = ?', [now(), $device['id']]);
        $decision = $this->decision((int) $device['user_id'], $url);

        if ($decision['blocked']) {
            $logId = Database::insert('blocker_logs', [
                'user_id' => $device['user_id'],
                'device_id' => (string) $device['id'],
                'blocked_url' => $url,
                'reason' => $decision['reason'],
                'partner_notified' => 0,
                'attempted_at' => now(),
            ]);
            if ($this->notifyPartners((int) $device['user_id'], $url, $decision['reason'])) {
                Database::query('UPDATE blocker_logs SET partner_notified = 1 WHERE id = ?', [$logId]);
            }
        }

        json([
            'blocked' => $decision['blocked'],
            'reason' => $decision['reason'],
            'redirect' => $decision['blocked'] ? absolute_url('/blocked?url=' . rawurlencode($url) . '&reason=' . rawurlencode($decision['reason'])) : null,
        ]);
    }

    private function decision(int $userId, string $url): array
    {
        $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));
        $needle = mb_strtolower($url . ' ' . $host);
        $rules = Database::rows(
            'SELECT * FROM blocker_rules WHERE active = 1 AND (user_id = ? OR user_id IS NULL) ORDER BY action = "allow" DESC',
            [$userId]
        );

        foreach ($rules as $rule) {
            $pattern = mb_strtolower((string) $rule['pattern']);
            if ($rule['rule_type'] === 'domain' && $host !== '' && ($host === $pattern || str_ends_with($host, '.' . $pattern))) {
                return [
                    'blocked' => ($rule['action'] ?? 'block') !== 'allow',
                    'reason' => $rule['reason'] ?: (($rule['action'] ?? 'block') === 'allow' ? 'approved override' : 'domain'),
                ];
            }
            if ($rule['rule_type'] === 'keyword' && str_contains($needle, $pattern)) {
                return [
                    'blocked' => ($rule['action'] ?? 'block') !== 'allow',
                    'reason' => $rule['reason'] ?: (($rule['action'] ?? 'block') === 'allow' ? 'approved override' : 'keyword'),
                ];
            }
        }

        return ['blocked' => false, 'reason' => 'allowed'];
    }

    private function notifyPartners(int $userId, string $url, string $reason): bool
    {
        $user = Database::row('SELECT name FROM users WHERE id = ?', [$userId]) ?? ['name' => 'Your partner'];
        $partners = Database::rows(
            'SELECT partner.email, partner.name
             FROM accountability_pairs ap
             INNER JOIN users partner ON partner.id = ap.partner_id
             WHERE ap.user_id = ? AND ap.status = "active" AND ap.sos_alerts = 1',
            [$userId]
        );

        foreach ($partners as $partner) {
            $body = "Hi {$partner['name']},\n\n"
                . "{$user['name']} had a FreedomGuard blocked attempt.\n\n"
                . "URL: {$url}\n"
                . "Reason: {$reason}\n\n"
                . "Respond calmly. Encouragement and a simple check-in are usually better than interrogation.\n"
                . "Partner dashboard: " . absolute_url('/partner');

            Mailer::sendOrQueue('blocker_alert', $partner['email'], 'FreedomGuard blocked attempt', $body, [
                'user_id' => $userId,
                'url' => $url,
                'reason' => $reason,
            ]);
        }

        return count($partners) > 0;
    }
}
