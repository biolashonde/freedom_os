<div class="page-header">
    <p class="page-header__eyebrow">System</p>
    <h1>App health</h1>
    <p>Quick checks for local or beta installs. Red items need attention before real users.</p>
</div>

<!-- Health checks grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:0.85rem;margin-bottom:1.5rem" class="stagger">
    <?php foreach ($checks as $check): ?>
        <div class="card <?= $check['ok'] ? 'card--green' : 'card--red' ?> animate-fade-up">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem;margin-bottom:0.5rem">
                <h2 style="font-size:0.95rem;font-weight:600"><?= e($check['label']) ?></h2>
                <span class="badge <?= $check['ok'] ? 'badge-green' : 'badge-red' ?>" style="flex-shrink:0">
                    <?= $check['ok'] ? '✓ OK' : '⚠ Check' ?>
                </span>
            </div>
            <p class="text-sm text-muted break-all"><?= e($check['detail']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Runtime stats -->
<div class="card" style="margin-bottom:1.25rem;animation:fade-up 0.4s var(--ease) 0.15s both">
    <h2 style="font-size:1.05rem;margin-bottom:0.85rem">Runtime stats</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:0.75rem">
        <?php foreach ($stats as $label => $value): ?>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label"><?= e(str_replace('_', ' ', $label)) ?></div>
                <div class="stat-tile__value" style="font-size:1.8rem"><?= e($value) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Pre-launch checklist -->
<div class="card" style="animation:fade-up 0.4s var(--ease) 0.2s both">
    <h2 style="font-size:1.05rem;margin-bottom:0.85rem">Before real beta users</h2>
    <div class="space-y-2">
        <div class="data-row"><span class="text-sm">Set <code style="background:var(--bg-elevated);padding:0.1rem 0.4rem;border-radius:4px;font-size:0.8rem">APP_DEBUG=false</code></span></div>
        <div class="data-row"><span class="text-sm">Replace the default <code style="background:var(--bg-elevated);padding:0.1rem 0.4rem;border-radius:4px;font-size:0.8rem">APP_KEY</code></span></div>
        <div class="data-row"><span class="text-sm">Configure SMTP or monitor <code style="background:var(--bg-elevated);padding:0.1rem 0.4rem;border-radius:4px;font-size:0.8rem">storage/outbox</code> manually</span></div>
        <div class="data-row"><span class="text-sm">Run the smoke test in <code style="background:var(--bg-elevated);padding:0.1rem 0.4rem;border-radius:4px;font-size:0.8rem">docs/SMOKE_TEST.md</code></span></div>
    </div>
</div>
