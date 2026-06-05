<?php
$manualReady  = $donation['manual_enabled'] && ($donation['bank_name'] || $donation['account_name'] || $donation['account_number'] || $donation['iban']);
$platformReady = $donation['platform_enabled'] && !empty($donation['platform_links']);
?>
<div style="max-width:860px;margin:0 auto">
    <div class="page-header" style="text-align:center">
        <p class="page-header__eyebrow">Support the mission</p>
        <h1><?= e($donation['headline']) ?></h1>
        <p><?= e($donation['body']) ?></p>
    </div>

    <?php if (!$manualReady && !$platformReady): ?>
        <div class="alert alert-warning" style="margin-bottom:1.5rem">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Donation details are being set up — check back soon.
        </div>
    <?php endif; ?>

    <div style="display:grid;gap:1.25rem;margin-bottom:1.5rem" class="lg-two-col">
        <?php if ($manualReady): ?>
            <div class="card" style="animation:fade-up 0.4s var(--ease) 0.1s both">
                <h2 style="font-size:1.1rem;margin-bottom:0.4rem">Bank transfer</h2>
                <p class="text-sm text-muted" style="margin-bottom:1rem">Use these details from your banking app. Add the reference if supported.</p>
                <div class="space-y-2">
                    <?php foreach ([
                        'Bank name'        => $donation['bank_name'],
                        'Account name'     => $donation['account_name'],
                        'Account number'   => $donation['account_number'],
                        'Routing / sort'   => $donation['routing_code'],
                        'IBAN'             => $donation['iban'],
                        'SWIFT / BIC'      => $donation['swift'],
                        'Reference'        => $donation['reference_note'],
                    ] as $label => $value): ?>
                        <?php if ($value): ?>
                            <div class="data-row">
                                <span class="text-sm text-muted"><?= e($label) ?></span>
                                <span style="font-weight:600;font-size:0.9rem"><?= e($value) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($platformReady): ?>
            <div class="card" style="animation:fade-up 0.4s var(--ease) 0.15s both">
                <h2 style="font-size:1.1rem;margin-bottom:0.4rem">Donation platforms</h2>
                <p class="text-sm text-muted" style="margin-bottom:1rem">Prefer a card or wallet? Choose a platform below.</p>
                <div class="space-y-3">
                    <?php foreach ($donation['platform_links'] as $platform): ?>
                        <a class="btn btn-primary btn-full" href="<?= e($platform['url']) ?>" target="_blank" rel="noopener noreferrer">
                            Donate with <?= e($platform['name']) ?> →
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- What it supports -->
    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.2s both">
        <h2 style="font-size:1.1rem;margin-bottom:1rem">What your gift supports</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0.85rem">
            <div class="card card--surface" style="padding:0.9rem">
                <div class="feature-icon feature-icon--red" style="width:32px;height:32px;font-size:0.95rem;margin-bottom:0.6rem">🆘</div>
                <p class="text-sm text-muted">SOS, Guard, and accountability flows — safer recovery tooling.</p>
            </div>
            <div class="card card--surface" style="padding:0.9rem">
                <div class="feature-icon feature-icon--sky" style="width:32px;height:32px;font-size:0.95rem;margin-bottom:0.6rem">🔒</div>
                <p class="text-sm text-muted">Hosting, security, privacy controls, and reliable notifications.</p>
            </div>
            <div class="card card--surface" style="padding:0.9rem">
                <div class="feature-icon feature-icon--green" style="width:32px;height:32px;font-size:0.95rem;margin-bottom:0.6rem">🌍</div>
                <p class="text-sm text-muted">Content, devotionals, mobile packaging, and support for people who can't pay.</p>
            </div>
        </div>
    </div>
</div>

<style>
@media (min-width: 800px) {
    .lg-two-col { grid-template-columns: 1fr 1fr !important; }
}
</style>
