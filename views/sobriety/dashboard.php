<?php
$riskColors = [
    'steady'  => ['badge' => 'badge-green',  'card' => 'card--green',  'label' => 'Steady'],
    'guarded' => ['badge' => 'badge-amber',  'card' => 'card--amber',  'label' => 'Guarded'],
    'high'    => ['badge' => 'badge-red',    'card' => 'card--red',    'label' => 'High risk'],
];
$rc = $riskColors[$risk['level']] ?? $riskColors['steady'];
?>
<!-- Page header -->
<div class="page-header">
    <p class="page-header__eyebrow"><?= e(date('l, F j')) ?></p>
    <h1>Steady, <?= e($user['name'] ?? 'friend') ?>.</h1>
</div>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:0.85rem;margin-bottom:1.5rem" class="stagger">
    <div class="stat-tile animate-fade-up">
        <div class="stat-tile__label">Current streak</div>
        <div class="stat-tile__value"><?= e($streak['current_days'] ?? 0) ?></div>
        <div class="stat-tile__unit">days</div>
    </div>
    <div class="stat-tile animate-fade-up">
        <div class="stat-tile__label">Longest streak</div>
        <div class="stat-tile__value"><?= e($streak['longest_days'] ?? 0) ?></div>
        <div class="stat-tile__unit">days</div>
    </div>
    <a href="<?= e(base_url('/progress')) ?>" class="stat-tile card--link animate-fade-up" style="border-color:rgba(42,122,92,0.25);background:var(--green-dim);text-decoration:none;">
        <div class="stat-tile__label" style="color:var(--green-text)">Review patterns</div>
        <div class="stat-tile__value" style="font-size:1.4rem;margin-top:0.5rem">Progress →</div>
    </a>
    <a href="<?= e(base_url('/sos')) ?>" class="stat-tile card--link animate-fade-up" style="border-color:rgba(192,57,43,0.3);background:var(--red-dim);text-decoration:none;">
        <div class="stat-tile__label" style="color:var(--red-text)">Under pressure?</div>
        <div class="stat-tile__value" style="font-size:1.4rem;margin-top:0.5rem">Open SOS →</div>
    </a>
</div>

<!-- Main grid -->
<div style="display:grid;gap:1.25rem;grid-template-columns:1fr" class="lg-grid-sidebar">

    <!-- LEFT column -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;min-width:0">

        <!-- Risk signal -->
        <div class="card <?= $rc['card'] ?>" style="animation:fade-up 0.4s var(--ease) 0.1s both">
            <div class="flex items-start justify-between" style="gap:0.75rem">
                <div>
                    <p class="text-label" style="margin-bottom:0.25rem">Today's risk signal</p>
                    <h2 style="font-size:1.4rem"><?= e($rc['label']) ?></h2>
                </div>
                <span class="badge <?= $rc['badge'] ?>" style="flex-shrink:0">Score <?= e($risk['score']) ?>/100</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1rem">
                <div>
                    <p class="form-label" style="margin-bottom:0.5rem">Signals</p>
                    <div class="space-y-2">
                        <?php foreach ($risk['signals'] as $signal): ?>
                            <div class="data-row"><?= e($signal) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <p class="form-label" style="margin-bottom:0.5rem">Next actions</p>
                    <div class="space-y-2">
                        <?php foreach ($risk['next_actions'] as $action): ?>
                            <div class="data-row"><?= e($action) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily check-in -->
        <div class="card" style="animation:fade-up 0.4s var(--ease) 0.2s both">
            <h2 style="font-size:1.15rem;margin-bottom:1rem">Daily check-in</h2>
            <?php if ($today): ?>
                <div class="alert alert-success" style="margin-bottom:1rem">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><polyline points="20 6 9 17 4 12"/></svg>
                    Checked in today — update below any time.
                </div>
            <?php endif; ?>
            <form class="space-y-4" method="post" action="<?= e(base_url('/checkin')) ?>">
                <?= csrf_field() ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div class="range-group">
                        <div class="flex justify-between items-center">
                            <label class="form-label">Mood</label>
                            <span class="range-value" id="moodVal"><?= e($today['mood'] ?? 3) ?>/5</span>
                        </div>
                        <input name="mood" type="range" min="1" max="5" value="<?= e($today['mood'] ?? 3) ?>" style="width:100%"
                               oninput="document.getElementById('moodVal').textContent=this.value+'/5'">
                        <div class="range-labels"><span>Low</span><span>High</span></div>
                    </div>
                    <div class="range-group">
                        <div class="flex justify-between items-center">
                            <label class="form-label">Urge level</label>
                            <span class="range-value" id="urgeVal"><?= e($today['urge_level'] ?? 1) ?>/5</span>
                        </div>
                        <input name="urge_level" type="range" min="1" max="5" value="<?= e($today['urge_level'] ?? 1) ?>" style="width:100%"
                               oninput="document.getElementById('urgeVal').textContent=this.value+'/5'">
                        <div class="range-labels"><span>None</span><span>Strong</span></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="checkin-note">What do you notice today?</label>
                    <textarea id="checkin-note" class="form-textarea" name="note"><?= e($today['note'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:1rem">
                    <label class="form-check">
                        <input name="prayer_done" type="checkbox" <?= !empty($today['prayer_done']) ? 'checked' : '' ?>>
                        Prayer done
                    </label>
                    <label class="form-check">
                        <input name="scripture_read" type="checkbox" <?= !empty($today['scripture_read']) ? 'checked' : '' ?>>
                        Scripture read
                    </label>
                    <label class="form-check">
                        <input name="relapsed" type="checkbox" <?= !empty($today['relapsed']) ? 'checked' : '' ?>>
                        I relapsed
                    </label>
                </div>
                <button class="btn btn-primary" type="submit">Save check-in</button>
            </form>
        </div>
    </div>

    <!-- RIGHT sidebar -->
    <aside style="display:flex;flex-direction:column;gap:1.25rem;min-width:0">

        <!-- Freedom signal -->
        <div class="card" style="animation:fade-up 0.4s var(--ease) 0.15s both">
            <h2 style="font-size:1.05rem;margin-bottom:0.5rem">Freedom signal</h2>
            <p class="text-sm text-muted">Your next breakthrough is boring on purpose: check in, tell the truth, take one clean action.</p>
            <?php if (!$safetyPlan): ?>
                <a class="btn btn-outline-green btn-sm" style="margin-top:0.85rem;display:inline-flex" href="<?= e(base_url('/purpose')) ?>">Create safety plan</a>
            <?php else: ?>
                <blockquote style="margin-top:0.85rem;padding:0.75rem;background:var(--bg-surface);border-radius:var(--radius);border-left:3px solid var(--green);font-size:0.875rem;color:var(--text-secondary)">
                    <?= e($safetyPlan['truth_statement']) ?>
                </blockquote>
            <?php endif; ?>
        </div>

        <!-- Devotional card -->
        <a href="<?= e(base_url('/devotional')) ?>" class="card card--green card--link" style="animation:fade-up 0.4s var(--ease) 0.2s both;display:block;text-decoration:none">
            <p class="text-label" style="margin-bottom:0.35rem">Daily devotional</p>
            <h2 style="font-size:1.05rem">Anchor today in truth</h2>
            <p class="text-sm text-muted" style="margin-top:0.35rem">A short recovery reading, scripture, and prayer.</p>
        </a>

        <!-- Recent check-ins -->
        <div class="card" style="animation:fade-up 0.4s var(--ease) 0.25s both">
            <h2 style="font-size:1.05rem;margin-bottom:0.85rem">Last 14 check-ins</h2>
            <div class="space-y-2">
                <?php foreach ($recent as $row): ?>
                    <div class="data-row">
                        <span class="text-sm text-muted truncate"><?= e($row['checked_in_at']) ?></span>
                        <span class="badge <?= $row['relapsed'] ? 'badge-red' : 'badge-green' ?>" style="flex-shrink:0">
                            <?= $row['relapsed'] ? 'Reset' : 'Clean' ?> · <?= e($row['urge_level']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$recent): ?>
                    <p class="text-sm text-muted">No check-ins yet. Start today.</p>
                <?php endif; ?>
            </div>
        </div>
    </aside>
</div>

<style>
@media (min-width: 900px) {
    .lg-grid-sidebar { grid-template-columns: 1.15fr 0.85fr !important; }
}
</style>
