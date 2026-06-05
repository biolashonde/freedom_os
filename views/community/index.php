<section class="space-y-6">
    <div class="page-header">
        <p class="page-header__eyebrow">Community</p>
        <h1>Talk, pray, and meet without hiding.</h1>
        <p>Use this room to rub minds, share clean next steps, encourage one another, and join online audio meetings.</p>
    </div>

    <div class="grid gap-5 lg:grid-cols-[.9fr_1.1fr]">
        <div class="card">
            <h2>Online audio meetings</h2>
            <p class="text-sm text-muted mt-2">Join active or upcoming rooms. Audio is hosted through the meeting link configured by the team.</p>
            <div class="space-y-3 mt-4">
                <?php foreach ($meetings as $meeting): ?>
                    <div class="card card--deep">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="badge badge-green"><?= e($meeting['platform'] ?: 'Online') ?></p>
                                <h3 class="mt-2"><?= e($meeting['title']) ?></h3>
                            </div>
                            <?php if ($meeting['starts_at']): ?>
                                <span class="text-xs text-muted"><?= e($meeting['starts_at']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-muted mt-2"><?= e($meeting['description']) ?></p>
                        <a class="btn btn-primary btn-sm mt-3" href="<?= e($meeting['meeting_url']) ?>" target="_blank" rel="noopener noreferrer">Join meeting</a>
                    </div>
                <?php endforeach; ?>
                <?php if (!$meetings): ?>
                    <p class="text-sm text-muted">No active meetings yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>Chatroom</h2>
            <p class="text-sm text-muted mt-2">Keep it clean, calm, and useful. No explicit details; share signals, support, and next actions.</p>
            <form class="mt-4" method="post" action="<?= e(base_url('/community/messages')) ?>">
                <?= csrf_field() ?>
                <textarea class="form-textarea" name="body" maxlength="1000" placeholder="What is one honest, helpful thing you can share right now?"></textarea>
                <button class="btn btn-primary mt-3" type="submit">Post message</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h2>Recent messages</h2>
        <div class="space-y-3 mt-4">
            <?php foreach ($messages as $message): ?>
                <div class="card card--deep">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold"><?= e($message['name']) ?></p>
                        <span class="text-xs text-muted"><?= e($message['created_at']) ?></span>
                    </div>
                    <p class="mt-2 text-sm"><?= nl2br(e($message['body'])) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (!$messages): ?>
                <p class="text-sm text-muted">No messages yet. Start with encouragement, honesty, or a clean next step.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
