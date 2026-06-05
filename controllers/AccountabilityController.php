<?php
declare(strict_types=1);

class AccountabilityController
{
    public function index(): void
    {
        $partners = Database::rows(
            'SELECT ap.*, u.name AS partner_name, u.email AS partner_email
             FROM accountability_pairs ap
             LEFT JOIN users u ON u.id = ap.partner_id
             WHERE ap.user_id = ?
             ORDER BY ap.created_at DESC',
            [Auth::id()]
        );

        view('accountability/index', [
            'title' => 'Accountability',
            'partners' => $partners,
        ]);
    }

    public function invite(): void
    {
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Add a valid partner email.');
            redirect('/accountability');
        }

        $partner = Database::row('SELECT id FROM users WHERE email = ?', [$email]);
        if (!$partner) {
            $temporaryPassword = bin2hex(random_bytes(8));
            $partnerId = Auth::register([
                'name' => $name !== '' ? $name : 'Accountability Partner',
                'email' => $email,
                'password' => $temporaryPassword,
                'timezone' => Auth::user()['timezone'] ?? 'Europe/London',
            ]);
        } else {
            $partnerId = (int) $partner['id'];
        }

        if ($partnerId === Auth::id()) {
            flash('error', 'Choose someone other than yourself as a partner.');
            redirect('/accountability');
        }

        $existing = Database::row(
            'SELECT id FROM accountability_pairs WHERE user_id = ? AND partner_id = ?',
            [Auth::id(), $partnerId]
        );

        if (!$existing) {
            $token = bin2hex(random_bytes(32));
            Database::insert('accountability_pairs', [
                'user_id' => Auth::id(),
                'partner_id' => $partnerId,
                'status' => 'pending',
                'invite_token' => $token,
                'sos_alerts' => isset($_POST['sos_alerts']) ? 1 : 0,
                'weekly_digest' => isset($_POST['weekly_digest']) ? 1 : 0,
                'relapse_visibility' => isset($_POST['relapse_visibility']) ? 1 : 0,
                'created_at' => now(),
            ]);

            $this->writeInviteOutbox($email, $token);
        }

        flash('success', 'Partner invite created. Copy the invite link for now; SMTP delivery is the next wiring step.');
        redirect('/accountability');
    }

    public function showAccept(string $token): void
    {
        $invite = $this->findInvite($token);
        if (!$invite) {
            http_response_code(404);
            view('errors/404', ['title' => 'Invite not found']);
            return;
        }

        view('accountability/accept', [
            'title' => 'Accept invite',
            'invite' => $invite,
            'token' => $token,
        ]);
    }

    public function accept(string $token): void
    {
        $invite = $this->findInvite($token);
        if (!$invite) {
            http_response_code(404);
            view('errors/404', ['title' => 'Invite not found']);
            return;
        }

        if ($invite['status'] !== 'pending') {
            flash('success', 'This accountability invite is already active.');
            Auth::attempt($invite['partner_email'], (string) ($_POST['password'] ?? ''));
            redirect('/dashboard');
        }

        $name = trim((string) ($_POST['name'] ?? $invite['partner_name']));
        $password = (string) ($_POST['password'] ?? '');
        if ($name === '' || strlen($password) < 8) {
            flash('error', 'Add your name and a password of at least 8 characters.');
            redirect('/accountability/accept/' . $token);
        }

        Database::query(
            'UPDATE users SET name = ?, password = ? WHERE id = ?',
            [$name, password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $invite['partner_id']]
        );
        Database::query(
            'UPDATE accountability_pairs SET status = "active", paired_at = ?, invite_token = NULL WHERE id = ?',
            [now(), $invite['id']]
        );

        Auth::attempt($invite['partner_email'], $password);
        flash('success', 'Invite accepted. You are now connected as an accountability partner.');
        redirect('/dashboard');
    }

    private function findInvite(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        return Database::row(
            'SELECT ap.*, inviter.name AS inviter_name, inviter.email AS inviter_email,
                    partner.name AS partner_name, partner.email AS partner_email
             FROM accountability_pairs ap
             INNER JOIN users inviter ON inviter.id = ap.user_id
             INNER JOIN users partner ON partner.id = ap.partner_id
             WHERE ap.invite_token = ?
             LIMIT 1',
            [$token]
        );
    }

    private function writeInviteOutbox(string $email, string $token): void
    {
        $user = Auth::user();
        $acceptUrl = absolute_url('/accountability/accept/' . $token);
        $body = "Hi,\n\n"
            . ($user['name'] ?? 'Someone') . " invited you to be their FreedomOS accountability partner.\n\n"
            . "Accept the invite here:\n{$acceptUrl}\n\n"
            . "You will only see the alerts and summaries they have consented to share.";

        Mailer::sendOrQueue('accountability_invite', $email, 'FreedomOS accountability invite', $body, [
            'accept_url' => $acceptUrl,
            'inviter_id' => Auth::id(),
        ]);
    }
}
