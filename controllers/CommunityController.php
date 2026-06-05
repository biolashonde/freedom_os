<?php
declare(strict_types=1);

class CommunityController
{
    public function index(): void
    {
        view('community/index', [
            'title' => 'Community',
            'messages' => Database::rows(
                'SELECT cm.*, u.name
                 FROM community_messages cm
                 INNER JOIN users u ON u.id = cm.user_id
                 WHERE cm.status = "visible"
                 ORDER BY cm.created_at DESC
                 LIMIT 80'
            ),
            'meetings' => Database::rows(
                'SELECT * FROM online_meetings
                 WHERE active = 1 AND (starts_at IS NULL OR starts_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR))
                 ORDER BY starts_at IS NULL, starts_at ASC, created_at DESC
                 LIMIT 12'
            ),
        ]);
    }

    public function post(): void
    {
        $body = trim(strip_tags((string) ($_POST['body'] ?? '')));
        if ($body === '' || mb_strlen($body) > 1000) {
            flash('error', 'Write a message between 1 and 1000 characters.');
            redirect('/community');
        }

        Database::insert('community_messages', [
            'user_id' => Auth::id(),
            'body' => $body,
            'status' => 'visible',
            'created_at' => now(),
        ]);

        flash('success', 'Message posted.');
        redirect('/community');
    }
}
