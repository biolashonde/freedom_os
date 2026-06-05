<?php
declare(strict_types=1);

class EmailAdminController
{
    public function index(): void
    {
        view('admin/email', [
            'title' => 'Email Workflow',
            'accounts' => EmailWorkflow::accounts(),
            'stats' => EmailWorkflow::stats(),
            'queue' => EmailWorkflow::recentQueue(),
        ]);
    }

    public function saveAccount(): void
    {
        try {
            EmailWorkflow::saveAccount($_POST);
            flash('success', 'SMTP account saved.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('/admin/email');
    }

    public function deleteAccount(string $id): void
    {
        EmailWorkflow::deleteAccount((int) $id);
        flash('success', 'SMTP account deleted.');
        redirect('/admin/email');
    }

    public function testAccount(string $id): void
    {
        $to = trim((string) ($_POST['test_email'] ?? ''));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Add a valid test recipient.');
            redirect('/admin/email');
        }

        Mailer::sendOrQueue(
            'smtp_test',
            $to,
            'FreedomOS email test',
            "This is a FreedomOS email workflow test.\n\nIf this arrived, the queue and fallback system are working.",
            ['preferred_smtp_account_id' => (int) $id, 'priority' => 5]
        );
        Mailer::processQueue(10);
        flash('success', 'Test email queued and processor attempted delivery.');
        redirect('/admin/email');
    }

    public function bulk(): void
    {
        try {
            $count = EmailWorkflow::queueBulk($_POST);
            flash('success', "Bulk email queued for {$count} recipient(s).");
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('/admin/email');
    }

    public function process(): void
    {
        $result = Mailer::processQueue(100);
        flash('success', "Processed {$result['processed']} email(s): {$result['sent']} sent, {$result['failed']} failed.");
        redirect('/admin/email');
    }
}
