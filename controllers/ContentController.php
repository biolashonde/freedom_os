<?php
declare(strict_types=1);

class ContentController
{
    public function index(): void
    {
        view('admin/content', [
            'title' => 'Content Studio',
            'devotionals' => Database::rows('SELECT * FROM devotionals WHERE user_id IS NULL ORDER BY COALESCE(day_number, 9999), created_at DESC LIMIT 40'),
            'resources' => Database::rows('SELECT * FROM sos_resources ORDER BY active DESC, sort_order ASC, title ASC LIMIT 80'),
            'meetings' => Database::rows('SELECT * FROM online_meetings ORDER BY active DESC, starts_at IS NULL, starts_at ASC, created_at DESC LIMIT 40'),
            'messages' => Database::rows(
                'SELECT cm.*, u.name
                 FROM community_messages cm
                 INNER JOIN users u ON u.id = cm.user_id
                 ORDER BY cm.created_at DESC
                 LIMIT 30'
            ),
        ]);
    }

    public function storeDevotional(): void
    {
        $title = sanitize((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            flash('error', 'Devotional title is required.');
            redirect('/admin/content');
        }

        Database::insert('devotionals', [
            'user_id' => null,
            'day_number' => ($_POST['day_number'] ?? '') !== '' ? max(1, (int) $_POST['day_number']) : null,
            'title' => $title,
            'theme' => sanitize((string) ($_POST['theme'] ?? 'Identity')),
            'scripture_ref' => sanitize((string) ($_POST['scripture_ref'] ?? '')),
            'scripture_text' => trim(strip_tags((string) ($_POST['scripture_text'] ?? ''))),
            'body' => trim(strip_tags((string) ($_POST['body'] ?? ''))),
            'prayer' => trim(strip_tags((string) ($_POST['prayer'] ?? ''))),
            'ai_generated' => 0,
            'published_date' => ($_POST['published_date'] ?? '') !== '' ? sanitize((string) $_POST['published_date']) : null,
            'created_at' => now(),
        ]);

        flash('success', 'Custom devotional added.');
        redirect('/admin/content');
    }

    public function storeResource(): void
    {
        $type = (string) ($_POST['type'] ?? 'resource');
        if (!in_array($type, ['music', 'game', 'video', 'resource'], true)) {
            $type = 'resource';
        }

        $title = sanitize((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            flash('error', 'Resource title is required.');
            redirect('/admin/content');
        }

        $url = trim((string) ($_POST['url'] ?? ''));
        Database::insert('sos_resources', [
            'type' => $type,
            'title' => $title,
            'description' => trim(strip_tags((string) ($_POST['description'] ?? ''))),
            'url' => filter_var($url, FILTER_VALIDATE_URL) ? $url : null,
            'duration_label' => sanitize((string) ($_POST['duration_label'] ?? '')),
            'active' => isset($_POST['active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'created_at' => now(),
        ]);

        flash('success', 'SOS resource added.');
        redirect('/admin/content');
    }

    public function storeMeeting(): void
    {
        $title = sanitize((string) ($_POST['title'] ?? ''));
        $url = trim((string) ($_POST['meeting_url'] ?? ''));
        if ($title === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            flash('error', 'Meeting title and a valid meeting URL are required.');
            redirect('/admin/content');
        }

        Database::insert('online_meetings', [
            'title' => $title,
            'description' => trim(strip_tags((string) ($_POST['description'] ?? ''))),
            'meeting_url' => $url,
            'platform' => sanitize((string) ($_POST['platform'] ?? '')),
            'starts_at' => ($_POST['starts_at'] ?? '') !== '' ? sanitize((string) $_POST['starts_at']) : null,
            'active' => isset($_POST['active']) ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        flash('success', 'Online meeting added.');
        redirect('/admin/content');
    }

    public function toggleMessage(string $messageId): void
    {
        $message = Database::row('SELECT * FROM community_messages WHERE id = ?', [(int) $messageId]);
        if (!$message) {
            flash('error', 'Message not found.');
            redirect('/admin/content');
        }

        Database::query(
            'UPDATE community_messages SET status = ? WHERE id = ?',
            [$message['status'] === 'visible' ? 'hidden' : 'visible', (int) $messageId]
        );

        flash('success', 'Community message visibility updated.');
        redirect('/admin/content');
    }
}
