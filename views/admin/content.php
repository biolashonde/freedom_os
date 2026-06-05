<section class="space-y-6">
    <div class="page-header">
        <p class="page-header__eyebrow">Superadmin</p>
        <h1>Content Studio</h1>
        <p>Add custom devotionals, calm music, Bible games, videos, SOS resources, and online audio meeting links.</p>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <form class="card space-y-4" method="post" action="<?= e(base_url('/admin/content/devotionals')) ?>">
            <?= csrf_field() ?>
            <h2>Add devotional</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="form-group"><span class="form-label">Day number</span><input class="form-input" name="day_number" type="number" min="1"></label>
                <label class="form-group"><span class="form-label">Published date</span><input class="form-input" name="published_date" type="date"></label>
            </div>
            <label class="form-group"><span class="form-label">Title</span><input class="form-input" name="title" required></label>
            <label class="form-group"><span class="form-label">Theme</span><input class="form-input" name="theme" value="Grace"></label>
            <label class="form-group"><span class="form-label">Scripture reference</span><input class="form-input" name="scripture_ref"></label>
            <label class="form-group"><span class="form-label">Scripture text</span><textarea class="form-textarea" name="scripture_text"></textarea></label>
            <label class="form-group"><span class="form-label">Body</span><textarea class="form-textarea" name="body" required></textarea></label>
            <label class="form-group"><span class="form-label">Prayer</span><textarea class="form-textarea" name="prayer"></textarea></label>
            <button class="btn btn-primary" type="submit">Add devotional</button>
        </form>

        <form class="card space-y-4" method="post" action="<?= e(base_url('/admin/content/resources')) ?>">
            <?= csrf_field() ?>
            <h2>Add SOS resource</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="form-group">
                    <span class="form-label">Type</span>
                    <select class="form-select" name="type">
                        <option value="music">Music</option>
                        <option value="game">Game</option>
                        <option value="video">Video</option>
                        <option value="resource">Resource</option>
                    </select>
                </label>
                <label class="form-group"><span class="form-label">Sort order</span><input class="form-input" name="sort_order" type="number" value="0"></label>
            </div>
            <label class="form-group"><span class="form-label">Title</span><input class="form-input" name="title" required></label>
            <label class="form-group"><span class="form-label">URL</span><input class="form-input" name="url" type="url" placeholder="https://..."></label>
            <label class="form-group"><span class="form-label">Duration label</span><input class="form-input" name="duration_label" placeholder="5 min"></label>
            <label class="form-group"><span class="form-label">Description</span><textarea class="form-textarea" name="description"></textarea></label>
            <label class="form-check"><input name="active" type="checkbox" checked> Active</label>
            <button class="btn btn-primary" type="submit">Add resource</button>
        </form>
    </div>

    <form class="card space-y-4" method="post" action="<?= e(base_url('/admin/content/meetings')) ?>">
        <?= csrf_field() ?>
        <h2>Add online audio meeting</h2>
        <div class="grid gap-3 md:grid-cols-3">
            <label class="form-group"><span class="form-label">Title</span><input class="form-input" name="title" required></label>
            <label class="form-group"><span class="form-label">Platform</span><input class="form-input" name="platform" placeholder="Zoom, Google Meet, Telegram..."></label>
            <label class="form-group"><span class="form-label">Starts at</span><input class="form-input" name="starts_at" type="datetime-local"></label>
        </div>
        <label class="form-group"><span class="form-label">Meeting URL</span><input class="form-input" name="meeting_url" type="url" required></label>
        <label class="form-group"><span class="form-label">Description</span><textarea class="form-textarea" name="description"></textarea></label>
        <label class="form-check"><input name="active" type="checkbox" checked> Active</label>
        <button class="btn btn-primary" type="submit">Add meeting</button>
    </form>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="card">
            <h2>Recent devotionals</h2>
            <div class="space-y-2 mt-4">
                <?php foreach ($devotionals as $item): ?>
                    <div class="data-row"><span><?= e($item['title']) ?></span><span class="text-xs text-muted"><?= e($item['theme']) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card">
            <h2>SOS resources</h2>
            <div class="space-y-2 mt-4">
                <?php foreach ($resources as $item): ?>
                    <div class="data-row"><span><?= e($item['title']) ?></span><span class="badge badge-muted"><?= e($item['type']) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card">
            <h2>Meetings</h2>
            <div class="space-y-2 mt-4">
                <?php foreach ($meetings as $item): ?>
                    <div class="data-row"><span><?= e($item['title']) ?></span><span class="text-xs text-muted"><?= e($item['platform']) ?></span></div>
                <?php endforeach; ?>
                <?php if (!$meetings): ?><p class="text-sm text-muted">No meetings yet.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Community moderation</h2>
        <div class="space-y-3 mt-4">
            <?php foreach ($messages as $message): ?>
                <div class="card card--deep">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold"><?= e($message['name']) ?></p>
                            <p class="text-xs text-muted"><?= e($message['created_at']) ?> · <?= e($message['status']) ?></p>
                        </div>
                        <form method="post" action="<?= e(base_url('/admin/content/messages/' . $message['id'] . '/toggle')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost btn-sm" type="submit"><?= $message['status'] === 'visible' ? 'Hide' : 'Show' ?></button>
                        </form>
                    </div>
                    <p class="text-sm mt-2"><?= nl2br(e($message['body'])) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (!$messages): ?>
                <p class="text-sm text-muted">No community messages yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
