<div style="max-width:680px;margin:0 auto;animation:fade-up 0.4s var(--ease) both">

    <div class="card" style="padding:2rem">
        <!-- Eyebrow -->
        <p class="text-label" style="margin-bottom:0.5rem">
            <?= !empty($devotional['ai_generated']) ? '✦ Personalized' : 'Day ' . e($devotional['day_number'] ?? $day) ?> · <?= e($devotional['theme'] ?? 'Identity') ?>
        </p>

        <!-- Title -->
        <h1 style="font-size:clamp(1.5rem,4vw,2.2rem);margin-bottom:1.5rem">
            <?= e($devotional['title'] ?? "Today's devotional") ?>
        </h1>

        <!-- Scripture block -->
        <div style="background:var(--bg-surface);border-left:4px solid var(--green);border-radius:0 var(--radius) var(--radius) 0;padding:1rem 1.25rem;margin-bottom:1.5rem">
            <p style="font-weight:700;font-size:0.8rem;letter-spacing:0.05em;color:var(--green-text);margin-bottom:0.5rem">
                <?= e($devotional['scripture_ref'] ?? '') ?>
            </p>
            <p style="color:var(--text-primary);line-height:1.8;font-size:1rem;font-style:italic">
                <?= e($devotional['scripture_text'] ?? '') ?>
            </p>
        </div>

        <!-- Body -->
        <div style="color:var(--text-secondary);line-height:1.85;font-size:0.95rem" class="space-y-4">
            <?php foreach (preg_split('/\n+/', (string) ($devotional['body'] ?? '')) as $paragraph): ?>
                <?php if (trim($paragraph) !== ''): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Prayer -->
        <div class="card card--green" style="margin-top:1.75rem;padding:1.25rem">
            <p style="font-size:0.72rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--green-text);margin-bottom:0.5rem">Prayer</p>
            <p style="color:var(--text-primary);line-height:1.75;font-style:italic"><?= e($devotional['prayer'] ?? '') ?></p>
        </div>

        <!-- Actions -->
        <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:1.75rem;align-items:center">
            <a class="btn btn-primary" href="<?= e(base_url('/dashboard')) ?>">Back to dashboard</a>
            <a class="btn btn-ghost" href="<?= e(base_url('/devotional/archive')) ?>">Archive</a>
            <form method="post" action="<?= e(base_url('/devotional/ai')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-outline-green" type="submit">
                    <?= !empty($aiConfigured) ? '✦ Personalize with ' . e($aiProvider) : 'AI not configured' ?>
                </button>
            </form>
        </div>
        <p class="text-xs text-muted" style="margin-top:0.75rem">Personalized generation uses streak, mood averages, urge levels, SOS count, and risk level — nothing private.</p>
    </div>
</div>
