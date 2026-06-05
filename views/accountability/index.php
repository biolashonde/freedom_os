<div class="page-header">
    <p class="page-header__eyebrow">Accountability</p>
    <h1>Who's got your back?</h1>
    <p>Invite one trusted person. Choose exactly what they can see. You stay in control.</p>
</div>

<div style="display:grid;gap:1.25rem" class="lg-two-col">

    <!-- Invite form -->
    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.1s both">
        <h2 style="font-size:1.1rem;margin-bottom:1rem">Add a partner</h2>
        <form class="space-y-4" method="post" action="<?= e(base_url('/accountability/invite')) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="acc-name">Partner name</label>
                <input id="acc-name" class="form-input" name="name" placeholder="Optional">
            </div>
            <div class="form-group">
                <label class="form-label" for="acc-email">Partner email</label>
                <input id="acc-email" class="form-input" name="email" type="email" required>
            </div>
            <div>
                <p class="form-label" style="margin-bottom:0.65rem">What they can see</p>
                <div class="space-y-3">
                    <label class="form-check"><input name="sos_alerts" type="checkbox" checked> Send SOS alerts</label>
                    <label class="form-check"><input name="weekly_digest" type="checkbox" checked> Share weekly digest</label>
                    <label class="form-check"><input name="relapse_visibility" type="checkbox"> Share relapse resets</label>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Send invite</button>
        </form>
    </div>

    <!-- Partners list -->
    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.15s both">
        <h2 style="font-size:1.1rem;margin-bottom:1rem">Active partners</h2>
        <div class="space-y-3">
            <?php foreach ($partners as $partner): ?>
                <div class="card card--surface" style="padding:0.9rem">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem">
                        <div>
                            <p style="font-weight:600;font-size:0.9rem"><?= e($partner['partner_name'] ?? 'Partner') ?></p>
                            <p class="text-sm text-muted"><?= e($partner['partner_email'] ?? '') ?></p>
                        </div>
                        <span class="badge <?= $partner['status'] === 'active' ? 'badge-green' : 'badge-amber' ?>">
                            <?= e($partner['status']) ?>
                        </span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-top:0.75rem">
                        <span class="badge <?= $partner['sos_alerts'] ? 'badge-green' : 'badge-muted' ?>">SOS <?= $partner['sos_alerts'] ? 'on' : 'off' ?></span>
                        <span class="badge <?= $partner['weekly_digest'] ? 'badge-green' : 'badge-muted' ?>">Digest <?= $partner['weekly_digest'] ? 'on' : 'off' ?></span>
                        <span class="badge <?= $partner['relapse_visibility'] ? 'badge-green' : 'badge-muted' ?>">Resets <?= $partner['relapse_visibility'] ? 'visible' : 'hidden' ?></span>
                    </div>
                    <?php if ($partner['status'] === 'pending' && !empty($partner['invite_token'])): ?>
                        <div class="form-group" style="margin-top:0.85rem">
                            <label class="form-label">Invite link</label>
                            <input class="form-input text-sm" readonly value="<?= e(absolute_url('/accountability/accept/' . $partner['invite_token'])) ?>" onclick="this.select()">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$partners): ?>
                <p class="text-sm text-muted">No partners yet. Start with one trusted person.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (min-width: 800px) {
    .lg-two-col { grid-template-columns: 1fr 1fr !important; }
}
</style>
