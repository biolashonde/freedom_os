<?php
declare(strict_types=1);

class EmailWorkflow
{
    public static function enqueue(string $type, string $to, string $subject, string $body, array $meta = []): string
    {
        try {
            $id = Database::insert('email_queue', [
                'type' => $type,
                'recipient_email' => mb_strtolower(trim($to)),
                'recipient_name' => sanitize((string) ($meta['recipient_name'] ?? '')),
                'subject' => $subject,
                'body' => $body,
                'status' => 'queued',
                'priority' => (int) ($meta['priority'] ?? self::defaultPriority($type)),
                'max_attempts' => (int) ($meta['max_attempts'] ?? 5),
                'meta' => json_encode($meta),
                'scheduled_at' => $meta['scheduled_at'] ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Mailer::log($type, $to, $subject, 'queued', $meta + ['email_queue_id' => $id]);
            return (string) $id;
        } catch (Throwable $e) {
            error_log('Email queue unavailable: ' . $e->getMessage());
            return Mailer::queueFile($type, $to, $subject, $body, $meta + ['queue_error' => $e->getMessage()]);
        }
    }

    public static function process(int $limit = 50): array
    {
        $rows = Database::rows(
            'SELECT * FROM email_queue
             WHERE status IN ("queued", "failed")
               AND attempts < max_attempts
               AND scheduled_at <= NOW()
             ORDER BY priority ASC, scheduled_at ASC, id ASC
             LIMIT ' . max(1, min(500, $limit))
        );

        $result = ['processed' => 0, 'sent' => 0, 'failed' => 0];
        foreach ($rows as $email) {
            $result['processed']++;
            self::markSending((int) $email['id']);
            $sendResult = self::sendWithFallbacks($email);
            if ($sendResult['sent']) {
                self::markSent((int) $email['id'], $sendResult);
                $result['sent']++;
                continue;
            }

            self::markFailed((int) $email['id'], (string) $sendResult['error']);
            $result['failed']++;
        }

        return $result;
    }

    public static function accounts(): array
    {
        try {
            return Database::rows('SELECT * FROM smtp_accounts ORDER BY active DESC, priority ASC, label ASC');
        } catch (Throwable) {
            return [];
        }
    }

    public static function activeAccounts(): array
    {
        return Database::rows(
            'SELECT * FROM smtp_accounts
             WHERE active = 1
               AND (last_sent_date IS NULL OR last_sent_date < CURDATE() OR sent_today < daily_limit)
             ORDER BY priority ASC, id ASC'
        );
    }

    public static function saveAccount(array $input): void
    {
        $id = (int) ($input['id'] ?? 0);
        $password = trim((string) ($input['password'] ?? ''));
        $data = [
            'label' => sanitize((string) ($input['label'] ?? '')),
            'host' => trim((string) ($input['host'] ?? '')),
            'port' => max(1, (int) ($input['port'] ?? 587)),
            'encryption' => in_array(($input['encryption'] ?? 'tls'), ['tls', 'ssl', 'none'], true) ? $input['encryption'] : 'tls',
            'username' => trim((string) ($input['username'] ?? '')),
            'from_email' => mb_strtolower(trim((string) ($input['from_email'] ?? ''))),
            'from_name' => sanitize((string) ($input['from_name'] ?? 'FreedomOS')),
            'reply_to' => mb_strtolower(trim((string) ($input['reply_to'] ?? ''))),
            'priority' => (int) ($input['priority'] ?? 100),
            'daily_limit' => max(1, (int) ($input['daily_limit'] ?? 500)),
            'active' => isset($input['active']) ? 1 : 0,
            'updated_at' => now(),
        ];

        if ($data['label'] === '' || $data['host'] === '' || !filter_var($data['from_email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Add a label, SMTP host, and valid from email.');
        }

        if ($id > 0 && Database::row('SELECT id FROM smtp_accounts WHERE id = ?', [$id])) {
            $sql = 'UPDATE smtp_accounts
                    SET label = ?, host = ?, port = ?, encryption = ?, username = ?, from_email = ?,
                        from_name = ?, reply_to = ?, priority = ?, daily_limit = ?, active = ?, updated_at = ?';
            $values = array_values($data);
            if ($password !== '') {
                $sql .= ', password = ?';
                $values[] = $password;
            }
            $sql .= ' WHERE id = ?';
            $values[] = $id;
            Database::query($sql, $values);
            return;
        }

        Database::insert('smtp_accounts', $data + [
            'password' => $password,
            'created_at' => now(),
        ]);
    }

    public static function deleteAccount(int $id): void
    {
        Database::query('DELETE FROM smtp_accounts WHERE id = ?', [$id]);
    }

    public static function queueBulk(array $input): int
    {
        $audience = (string) ($input['audience'] ?? 'all');
        $subject = trim((string) ($input['subject'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));
        if ($subject === '' || $body === '') {
            throw new InvalidArgumentException('Add a subject and message body.');
        }

        $where = 'deleted_at IS NULL';
        $params = [];
        if (in_array($audience, ['user', 'mentor', 'admin', 'superadmin'], true)) {
            $where .= ' AND role = ?';
            $params[] = $audience;
        }

        $users = Database::rows("SELECT name, email FROM users WHERE {$where} ORDER BY created_at DESC", $params);
        foreach ($users as $user) {
            $personalBody = str_replace(
                ['{{name}}', '{{email}}'],
                [(string) $user['name'], (string) $user['email']],
                $body
            );
            self::enqueue('bulk_' . $audience, (string) $user['email'], $subject, $personalBody, [
                'recipient_name' => $user['name'],
                'audience' => $audience,
                'priority' => 200,
            ]);
        }

        return count($users);
    }

    public static function stats(): array
    {
        $rows = Database::rows('SELECT status, COUNT(*) total FROM email_queue GROUP BY status');
        $stats = ['queued' => 0, 'sending' => 0, 'sent' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $stats[(string) $row['status']] = (int) $row['total'];
        }
        return $stats;
    }

    public static function recentQueue(): array
    {
        return Database::rows('SELECT * FROM email_queue ORDER BY created_at DESC LIMIT 40');
    }

    private static function sendWithFallbacks(array $email): array
    {
        $errors = [];
        foreach (self::deliveryAccounts($email) as $account) {
            try {
                self::sendSmtp($account, $email);
                self::incrementAccount((int) $account['id']);
                return ['sent' => true, 'driver' => 'smtp', 'smtp_account_id' => (int) $account['id']];
            } catch (Throwable $e) {
                $errors[] = $account['label'] . ': ' . $e->getMessage();
                self::recordAccountError((int) $account['id'], $e->getMessage());
            }
        }

        if (self::sendPhpMail($email)) {
            return ['sent' => true, 'driver' => 'mail'];
        }

        Mailer::queueFile(
            (string) $email['type'],
            (string) $email['recipient_email'],
            (string) $email['subject'],
            (string) $email['body'],
            ['email_queue_id' => $email['id'], 'smtp_errors' => $errors]
        );

        return ['sent' => false, 'error' => implode(' | ', $errors) ?: 'No active SMTP account and mail() failed'];
    }

    private static function sendSmtp(array $account, array $email): void
    {
        $remote = ($account['encryption'] === 'ssl' ? 'ssl://' : '') . $account['host'];
        $socket = @stream_socket_client($remote . ':' . (int) $account['port'], $errno, $errstr, 20);
        if (!$socket) {
            throw new RuntimeException("Connection failed: {$errstr}");
        }
        stream_set_timeout($socket, 20);

        self::expect($socket, [220]);
        self::command($socket, 'EHLO freedomos.local', [250]);
        if ($account['encryption'] === 'tls') {
            self::command($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS failed');
            }
            self::command($socket, 'EHLO freedomos.local', [250]);
        }

        if ((string) ($account['username'] ?? '') !== '') {
            self::command($socket, 'AUTH LOGIN', [334]);
            self::command($socket, base64_encode((string) $account['username']), [334]);
            self::command($socket, base64_encode((string) ($account['password'] ?? '')), [235]);
        }

        $from = (string) $account['from_email'];
        $to = (string) $email['recipient_email'];
        self::command($socket, 'MAIL FROM:<' . $from . '>', [250]);
        self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        self::command($socket, 'DATA', [354]);

        $headers = [
            'From: ' . self::formatAddress((string) $account['from_name'], $from),
            'To: ' . self::formatAddress((string) ($email['recipient_name'] ?? ''), $to),
            'Subject: ' . (string) $email['subject'],
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        if (!empty($account['reply_to']) && filter_var($account['reply_to'], FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $account['reply_to'];
        }

        $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", (string) $email['body']);
        fwrite($socket, str_replace("\r\n.", "\r\n..", $message) . "\r\n.\r\n");
        self::expect($socket, [250]);
        self::command($socket, 'QUIT', [221, 250]);
        fclose($socket);
    }

    private static function deliveryAccounts(array $email): array
    {
        $accounts = self::activeAccounts();
        $meta = json_decode((string) ($email['meta'] ?? '{}'), true);
        $preferred = is_array($meta) ? (int) ($meta['preferred_smtp_account_id'] ?? 0) : 0;
        if ($preferred <= 0) {
            return $accounts;
        }

        usort($accounts, function (array $a, array $b) use ($preferred): int {
            if ((int) $a['id'] === $preferred) {
                return -1;
            }
            if ((int) $b['id'] === $preferred) {
                return 1;
            }
            return ((int) $a['priority']) <=> ((int) $b['priority']);
        });

        return $accounts;
    }

    private static function sendPhpMail(array $email): bool
    {
        $config = require ROOT . '/config/config.php';
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . ($config['mail']['from_name'] ?? 'FreedomOS') . ' <' . ($config['mail']['from'] ?? 'hello@example.com') . '>',
        ];
        return @mail((string) $email['recipient_email'], (string) $email['subject'], (string) $email['body'], implode("\r\n", $headers));
    }

    private static function command($socket, string $command, array $codes): string
    {
        fwrite($socket, $command . "\r\n");
        return self::expect($socket, $codes);
    }

    private static function expect($socket, array $codes): string
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException(trim($response) ?: 'SMTP response missing');
        }
        return $response;
    }

    private static function markSending(int $id): void
    {
        Database::query('UPDATE email_queue SET status = "sending", attempts = attempts + 1, updated_at = ? WHERE id = ?', [now(), $id]);
    }

    private static function markSent(int $id, array $result): void
    {
        Database::query(
            'UPDATE email_queue SET status = "sent", smtp_account_id = ?, last_error = NULL, sent_at = ?, updated_at = ? WHERE id = ?',
            [$result['smtp_account_id'] ?? null, now(), now(), $id]
        );
        $email = Database::row('SELECT * FROM email_queue WHERE id = ?', [$id]);
        if ($email) {
            Mailer::log((string) $email['type'], (string) $email['recipient_email'], (string) $email['subject'], 'sent', ['driver' => $result['driver'] ?? 'unknown', 'email_queue_id' => $id]);
        }
    }

    private static function markFailed(int $id, string $error): void
    {
        Database::query('UPDATE email_queue SET status = "failed", last_error = ?, updated_at = ? WHERE id = ?', [$error, now(), $id]);
    }

    private static function incrementAccount(int $id): void
    {
        Database::query(
            'UPDATE smtp_accounts
             SET sent_today = CASE WHEN last_sent_date = CURDATE() THEN sent_today + 1 ELSE 1 END,
                 last_sent_date = CURDATE(), last_error = NULL, updated_at = ?
             WHERE id = ?',
            [now(), $id]
        );
    }

    private static function recordAccountError(int $id, string $error): void
    {
        Database::query('UPDATE smtp_accounts SET last_error = ?, updated_at = ? WHERE id = ?', [$error, now(), $id]);
    }

    private static function formatAddress(string $name, string $email): string
    {
        return $name !== '' ? '"' . str_replace('"', '', $name) . '" <' . $email . '>' : $email;
    }

    private static function defaultPriority(string $type): int
    {
        return match ($type) {
            'password_reset' => 10,
            'sos_alert' => 20,
            'accountability_invite' => 30,
            default => 100,
        };
    }
}
