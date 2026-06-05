<div class="page-header">
    <p class="page-header__eyebrow">Partner view</p>
    <h1>Supporting someone</h1>
    <p>You see only what they consented to share. Respond with calm, practical encouragement.</p>
</div>

<?php foreach ($pairs as $pair): ?>
    <?php $summary = $summaries[(int) $pair['id']] ?? ['recent_checkins' => [], 'recent_sos' => [], 'recent_blocked' => [], 'pending_overrides' => [], 'open_sos' => null]; ?>

    <article class="card" style="margin-bottom:1.5rem;animation:fade-up 0.4s var(--ease) both">
        <!-- Person header -->
        <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem">
            <div>
                <h2 style="font-size:1.3rem"><?= e($pair['user_name']) ?></h2>
                <p class="text-sm text-muted"><?= e($pair['user_email']) ?></p>
            </div>
            <?php if ($summary['open_sos']): ?>
                <span class="badge badge-red" style="font-size:0.85rem;padding:0.35rem 0.85rem;animation:pulse-ring 2s infinite">⚡ SOS open</span>
            <?php else: ?>
                <span class="badge badge-green">Stable</span>
            <?php endif; ?>
        </div>

        <!-- Streak stats -->
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:0.75rem;margin-bottom:1.25rem">
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Current streak</div>
                <div class="stat-tile__value" style="font-size:2rem"><?= e($pair['current_days'] ?? 0) ?></div>
                <div class="stat-tile__unit">days</div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Longest streak</div>
                <div class="stat-tile__value" style="font-size:2rem"><?= e($pair['longest_days'] ?? 0) ?></div>
                <div class="stat-tile__unit">days</div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Resets visible</div>
                <div class="stat-tile__value" style="font-size:1.4rem;margin-top:0.35rem">
                    <?= $pair['relapse_visibility'] ? e($pair['total_relapses'] ?? 0) : 'Hidden' ?>
                </div>
            </div>
        </div>

        <!-- Safety cues + Check-ins -->
        <div style="display:grid;gap:1rem;margin-bottom:1rem" class="lg-two-col">
            <div class="card card--surface" style="padding:0.9rem">
                <h3 style="margin-bottom:0.75rem">Safety cues</h3>
                <p class="form-label" style="margin-bottom:0.2rem">Their trigger</p>
                <p class="text-sm text-muted" style="margin-bottom:0.65rem"><?= e($pair['top_trigger'] ?: 'Not shared yet') ?></p>
                <p class="form-label" style="margin-bottom:0.2rem">Escape action</p>
                <p class="text-sm text-muted"><?= e($pair['escape_action'] ?: 'Not shared yet') ?></p>
                <?php if (!empty($pair['truth_statement'])): ?>
                    <blockquote style="margin-top:0.75rem;padding:0.65rem 0.85rem;background:var(--bg-glass);border-left:3px solid var(--green);border-radius:var(--radius-sm);font-size:0.85rem;color:var(--text-secondary)">
                        <?= e($pair['truth_statement']) ?>
                    </blockquote>
                <?php endif; ?>
            </div>

            <div class="card card--surface" style="padding:0.9rem">
                <h3 style="margin-bottom:0.75rem">Recent check-ins</h3>
                <div class="space-y-2">
                    <?php foreach ($summary['recent_checkins'] as $checkin): ?>
                        <div class="data-row" style="font-size:0.8rem">
                            <span class="text-muted truncate"><?= e($checkin['checked_in_at']) ?></span>
                            <span>mood <?= e($checkin['mood']) ?> · urge <?= e($checkin['urge_level']) ?><?= $pair['relapse_visibility'] && $checkin['relapsed'] ? ' · <span class="badge badge-red">reset</span>' : '' ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$summary['recent_checkins']): ?>
                        <p class="text-sm text-muted">No check-ins yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SOS + Blocked + Overrides -->
        <div class="card card--surface" style="padding:0.9rem;margin-bottom:1rem">
            <h3 style="margin-bottom:0.75rem">Recent SOS activity</h3>
            <div class="space-y-2">
                <?php foreach ($summary['recent_sos'] as $event): ?>
                    <div class="data-row" style="flex-direction:column;align-items:flex-start;gap:0.2rem">
                        <div style="display:flex;justify-content:space-between;width:100%">
                            <span class="text-sm text-muted"><?= e($event['created_at']) ?></span>
                            <span class="badge <?= $event['resolved'] ? 'badge-green' : 'badge-red' ?>"><?= $event['resolved'] ? 'resolved' : 'open' ?></span>
                        </div>
                        <?php if (!empty($event['trigger_note'])): ?>
                            <p class="text-sm text-muted"><?= e($event['trigger_note']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$summary['recent_sos']): ?><p class="text-sm text-muted">No SOS events yet.</p><?php endif; ?>
            </div>
        </div>

        <!-- Overrides -->
        <?php if ($summary['pending_overrides']): ?>
        <div class="card card--amber" style="padding:0.9rem;margin-bottom:1rem">
            <h3 style="margin-bottom:0.75rem">⚠ Pending override requests</h3>
            <div class="space-y-3">
                <?php foreach ($summary['pending_overrides'] as $override): ?>
                    <div style="background:var(--bg-surface);border-radius:var(--radius);padding:0.75rem">
                        <p class="text-sm break-all"><?= e($override['url']) ?></p>
                        <p class="text-xs text-muted" style="margin-top:0.25rem"><?= e($override['reason']) ?> · <?= e($override['requested_at']) ?></p>
                        <form style="display:flex;gap:0.5rem;margin-top:0.65rem" method="post" action="<?= e(base_url('/partner/overrides/' . $override['id'] . '/review')) ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-primary btn-sm" name="decision" value="approved" type="submit">Approve</button>
                            <button class="btn btn-danger btn-sm" name="decision" value="denied" type="submit">Deny</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Encouragement -->
        <form class="space-y-3" method="post" action="<?= e(base_url('/partner/encourage/' . $pair['id'])) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="encourage-<?= e($pair['id']) ?>">Send encouragement</label>
                <textarea id="encourage-<?= e($pair['id']) ?>" class="form-textarea" name="message" placeholder="I'm here. Take the next clean step." style="min-height:80px"></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Send message</button>
        </form>
    </article>
<?php endforeach; ?>

<?php if (!$pairs): ?>
    <div class="card" style="text-align:center;padding:2.5rem 1.5rem;animation:fade-up 0.4s var(--ease) both">
        <p style="font-size:2rem;margin-bottom:0.75rem">👥</p>
        <h2 style="font-size:1.2rem;margin-bottom:0.4rem">No active connections</h2>
        <p class="text-sm text-muted">When someone invites you and you accept, their consented support view will appear here.</p>
    </div>
<?php endif; ?>

<style>
@media (min-width: 800px) {
    .lg-two-col { grid-template-columns: 1fr 1fr !important; }
}
</style>
