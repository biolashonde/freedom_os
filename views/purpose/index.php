<div class="page-header">
    <p class="page-header__eyebrow">Purpose map</p>
    <h1>Build the life freedom is for</h1>
    <p>Your safety plan, goals, and testimony — all in one place.</p>
</div>

<div style="display:grid;gap:1.25rem;margin-bottom:1.25rem" class="lg-two-col">

    <!-- Safety plan -->
    <form class="card space-y-4" method="post" action="<?= e(base_url('/purpose/safety-plan')) ?>" style="animation:fade-up 0.4s var(--ease) 0.1s both">
        <?= csrf_field() ?>
        <div>
            <h2 style="font-size:1.1rem;margin-bottom:0.25rem">Safety plan</h2>
            <p class="text-sm text-muted">What to do when pressure peaks.</p>
        </div>
        <div class="form-group">
            <label class="form-label" for="pp-trigger">My highest-risk trigger</label>
            <input id="pp-trigger" class="form-input" name="top_trigger" value="<?= e($plan['top_trigger'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label" for="pp-escape">My 60-second escape action</label>
            <input id="pp-escape" class="form-input" name="escape_action" value="<?= e($plan['escape_action'] ?? '') ?>" placeholder="Walk outside, cold shower, call mentor">
        </div>
        <div class="form-group">
            <label class="form-label" for="pp-contact">Support contact</label>
            <input id="pp-contact" class="form-input" name="support_contact" value="<?= e($plan['support_contact'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label" for="pp-truth">Truth statement</label>
            <textarea id="pp-truth" class="form-textarea" name="truth_statement" placeholder="I am not my urge. I am free to choose the next right action."><?= e($plan['truth_statement'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Save plan</button>
    </form>

    <!-- Goals -->
    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.15s both">
        <h2 style="font-size:1.1rem;margin-bottom:1rem">Purpose goals</h2>
        <form style="display:grid;gap:0.75rem;margin-bottom:1.25rem" method="post" action="<?= e(base_url('/purpose/goals')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="pg-title">Goal</label>
                <input id="pg-title" class="form-input" name="title" placeholder="Exercise 3× this week" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
                <div class="form-group">
                    <label class="form-label" for="pg-cat">Category</label>
                    <select id="pg-cat" class="form-select" name="category">
                        <option value="spiritual">Spiritual</option>
                        <option value="relational">Relational</option>
                        <option value="physical">Physical</option>
                        <option value="vocational">Vocational</option>
                        <option value="ministry">Ministry</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="pg-date">Target date</label>
                    <input id="pg-date" class="form-input" name="target_date" type="date">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="pg-desc">Description</label>
                <textarea id="pg-desc" class="form-textarea" name="description" style="min-height:70px"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Add goal</button>
        </form>

        <div class="divider"></div>

        <div class="space-y-3">
            <?php foreach ($goals as $goal): ?>
                <div class="card card--surface" style="padding:0.85rem">
                    <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:0.5rem">
                        <div style="flex:1;min-width:0">
                            <span class="badge badge-green" style="margin-bottom:0.4rem"><?= e($goal['category']) ?></span>
                            <h3 style="font-size:0.9rem;margin-top:0.3rem"><?= e($goal['title']) ?></h3>
                            <?php if (!empty($goal['description'])): ?>
                                <p class="text-sm text-muted" style="margin-top:0.2rem"><?= e($goal['description']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($goal['target_date'])): ?>
                                <p class="text-xs text-muted" style="margin-top:0.4rem">Target: <?= e($goal['target_date']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($goal['completed_at']): ?>
                            <span class="badge badge-green">✓ Done</span>
                        <?php else: ?>
                            <form method="post" action="<?= e(base_url('/purpose/goals/' . $goal['id'] . '/complete')) ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-ghost btn-sm" type="submit">Complete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$goals): ?>
                <p class="text-sm text-muted">No goals yet. Add one small constructive action.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Testimony -->
<form class="card space-y-4" method="post" action="<?= e(base_url('/purpose/testimony')) ?>" style="animation:fade-up 0.4s var(--ease) 0.2s both">
    <?= csrf_field() ?>
    <div>
        <h2 style="font-size:1.1rem;margin-bottom:0.25rem">Testimony draft</h2>
        <p class="text-sm text-muted">Private by default. Share only when you're ready.</p>
    </div>
    <div class="form-group">
        <label class="form-label" for="test-title">Title</label>
        <input id="test-title" class="form-input" name="title" value="<?= e($testimony['title'] ?? 'My testimony') ?>">
    </div>
    <div class="form-group">
        <label class="form-label" for="test-body">What is God changing in your story?</label>
        <textarea id="test-body" class="form-textarea" name="body" style="min-height:160px" placeholder="Before, I… Now I'm learning… I still need grace for…"><?= e($testimony['body'] ?? '') ?></textarea>
    </div>
    <label class="form-check">
        <input name="is_public" type="checkbox" <?= !empty($testimony['is_public']) ? 'checked' : '' ?>>
        Allow this testimony to be shared later
    </label>
    <button class="btn btn-primary" type="submit">Save testimony</button>
</form>

<style>
@media (min-width: 800px) {
    .lg-two-col { grid-template-columns: 1fr 1fr !important; }
}
</style>
