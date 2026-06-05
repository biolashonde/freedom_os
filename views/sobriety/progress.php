<?php
$summary = $analytics['summary'];
$series  = $analytics['series'];
$plan    = $analytics['plan'];
$victory = $analytics['victory'];
?>
<!-- Page header -->
<div class="page-header">
    <p class="page-header__eyebrow">Weekly review</p>
    <h1>Progress patterns</h1>
    <p>A 30-day view of check-ins, urges, mood, SOS use, and Guard events. Private notes stay private.</p>
</div>

<!-- Summary stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:0.85rem;margin-bottom:1.5rem" class="stagger">
    <div class="stat-tile animate-fade-up">
        <div class="stat-tile__label">Check-in rate</div>
        <div class="stat-tile__value"><?= e($summary['checkin_rate']) ?><span style="font-size:1.2rem">%</span></div>
    </div>
    <div class="stat-tile animate-fade-up">
        <div class="stat-tile__label">Avg mood</div>
        <div class="stat-tile__value"><?= e($summary['avg_mood']) ?><span style="font-size:1rem;color:var(--text-muted)">/5</span></div>
    </div>
    <div class="stat-tile animate-fade-up">
        <div class="stat-tile__label">Avg urge</div>
        <div class="stat-tile__value"><?= e($summary['avg_urge']) ?><span style="font-size:1rem;color:var(--text-muted)">/5</span></div>
    </div>
    <div class="stat-tile animate-fade-up">
        <div class="stat-tile__label">Current streak</div>
        <div class="stat-tile__value"><?= e($streak['current_days'] ?? 0) ?></div>
        <div class="stat-tile__unit">days</div>
    </div>
</div>

<!-- Weekly plan -->
<div class="card card--green" style="margin-bottom:1.25rem;animation:fade-up 0.4s var(--ease) 0.1s both">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:1rem">
        <div>
            <p class="text-label" style="margin-bottom:0.4rem">Smart weekly plan</p>
            <h2 style="font-size:1.2rem"><?= e($plan['focus']) ?></h2>
            <p class="text-sm text-muted" style="margin-top:0.4rem;max-width:500px"><?= e($plan['why']) ?></p>
        </div>
        <a class="btn btn-outline-green btn-sm" href="<?= e(base_url('/accountability')) ?>">Loop in partner</a>
    </div>
    <div style="display:grid;gap:1rem;margin-top:1.25rem" class="lg-two-col">
        <div>
            <p class="form-label" style="margin-bottom:0.5rem">This week's moves</p>
            <div class="space-y-2">
                <?php foreach ($plan['moves'] as $move): ?>
                    <div class="data-row"><?= e($move) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="background:var(--bg-surface);border-radius:var(--radius);padding:0.85rem">
            <p class="form-label" style="margin-bottom:0.5rem">Partner prompt</p>
            <p class="text-sm text-muted"><?= e($plan['partner_prompt']) ?></p>
        </div>
    </div>
</div>

<!-- Resilience + Victory timeline -->
<div style="display:grid;gap:1.25rem;margin-bottom:1.25rem" class="lg-two-col">

    <!-- Resilience metrics -->
    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.15s both">
        <p class="text-label" style="margin-bottom:0.4rem">Beyond streaks</p>
        <h2 style="font-size:1.1rem;margin-bottom:1rem">Resilience metrics</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Clean decisions</div>
                <div class="stat-tile__value" style="font-size:2rem"><?= e($victory['metrics']['clean_decisions']) ?></div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Near-miss wins</div>
                <div class="stat-tile__value" style="font-size:2rem"><?= e($victory['metrics']['near_miss_wins']) ?></div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Rebound days</div>
                <div class="stat-tile__value" style="font-size:2rem"><?= e($victory['metrics']['rebound_days']) ?></div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Purpose wins</div>
                <div class="stat-tile__value" style="font-size:2rem"><?= e($victory['metrics']['purpose_wins']) ?></div>
            </div>
        </div>
        <p class="text-xs text-muted" style="margin-top:0.85rem">Clean decisions = clean check-ins + SOS/Guard interruptions.</p>
    </div>

    <!-- Victory timeline -->
    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.2s both">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
            <div>
                <p class="text-label" style="margin-bottom:0.25rem">Victory timeline</p>
                <h2 style="font-size:1.1rem">Wins worth remembering</h2>
            </div>
            <span class="badge badge-muted">Last 30 days</span>
        </div>
        <div class="space-y-3" style="max-height:320px;overflow-y:auto;padding-right:2px">
            <?php
            $typeMap = [
                'Clean'     => ['timeline-item--clean',     'badge-green'],
                'Protected' => ['timeline-item--protected',  'badge-amber'],
                'Rebound'   => ['timeline-item--rebound',    'badge-sky'],
                'Purpose'   => ['timeline-item--purpose',    'badge-muted'],
                'Reset'     => ['timeline-item--reset',      'badge-red'],
            ];
            foreach ($victory['timeline'] as $item):
                [$tlClass, $bdClass] = $typeMap[$item['type']] ?? ['', 'badge-muted'];
            ?>
                <div class="timeline-item <?= $tlClass ?>">
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;margin-bottom:0.3rem">
                            <span class="badge <?= $bdClass ?>"><?= e($item['type']) ?></span>
                            <span class="text-xs text-muted"><?= e($item['date']) ?></span>
                        </div>
                        <p style="font-weight:600;font-size:0.9rem"><?= e($item['title']) ?></p>
                        <p class="text-sm text-muted" style="margin-top:0.15rem"><?= e($item['body']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$victory['timeline']): ?>
                <p class="text-sm text-muted">Complete a check-in, use SOS, or log a purpose goal to start your timeline.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 30-day rhythm chart -->
<div class="card" style="margin-bottom:1.25rem;animation:fade-up 0.4s var(--ease) 0.25s both">
    <h2 style="font-size:1.1rem;margin-bottom:1rem">30-day rhythm</h2>
    <div style="display:flex;align-items:flex-end;gap:3px;height:100px;overflow-x:auto;padding-bottom:0.5rem">
        <?php foreach ($series as $day): ?>
            <?php
                $h = $day['urge'] ? max(14, $day['urge'] * 18) : 10;
                $bg = $day['relapsed'] ? '#e57373' : ($day['checked_in'] ? '#4caf83' : '#2a3630');
            ?>
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:2px;flex:1;min-width:14px;height:100%"
                 title="<?= e($day['date']) ?>">
                <?php if ($day['sos'] > 0): ?><span style="width:5px;height:5px;border-radius:50%;background:#e57373;flex-shrink:0"></span><?php endif; ?>
                <?php if ($day['blocked'] > 0): ?><span style="width:5px;height:5px;border-radius:50%;background:#fbbf24;flex-shrink:0"></span><?php endif; ?>
                <div style="width:100%;height:<?= $h ?>px;background:<?= $bg ?>;border-radius:3px 3px 0 0;transition:filter 0.15s;cursor:default"
                     onmouseover="this.style.filter='brightness(1.3)'" onmouseout="this.style.filter=''"></div>
                <span style="font-size:9px;color:var(--text-muted);line-height:1"><?= e(substr($day['date'], -2)) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-top:0.85rem;font-size:0.75rem;color:var(--text-muted)">
        <span><span style="display:inline-block;width:10px;height:10px;background:#4caf83;border-radius:2px;margin-right:4px;vertical-align:middle"></span>Clean</span>
        <span><span style="display:inline-block;width:10px;height:10px;background:#e57373;border-radius:2px;margin-right:4px;vertical-align:middle"></span>Reset</span>
        <span><span style="display:inline-block;width:10px;height:10px;background:#2a3630;border-radius:2px;margin-right:4px;vertical-align:middle"></span>No check-in</span>
        <span><span style="display:inline-block;width:5px;height:5px;background:#e57373;border-radius:50%;margin-right:4px;vertical-align:middle"></span>SOS</span>
        <span><span style="display:inline-block;width:5px;height:5px;background:#fbbf24;border-radius:50%;margin-right:4px;vertical-align:middle"></span>Blocker event</span>
    </div>
</div>

<!-- Insights + Pressure summary -->
<div style="display:grid;gap:1.25rem" class="lg-two-col">

    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.3s both">
        <h2 style="font-size:1.1rem;margin-bottom:0.85rem">Review insights</h2>
        <div class="space-y-2">
            <?php foreach ($analytics['insights'] as $insight): ?>
                <div class="data-row"><?= e($insight) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card" style="animation:fade-up 0.4s var(--ease) 0.35s both">
        <h2 style="font-size:1.1rem;margin-bottom:0.85rem">Pressure summary</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem">
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Clean days</div>
                <div class="stat-tile__value" style="font-size:1.9rem"><?= e($summary['clean_days']) ?></div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Resets</div>
                <div class="stat-tile__value" style="font-size:1.9rem"><?= e($summary['relapses']) ?></div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">SOS events</div>
                <div class="stat-tile__value" style="font-size:1.9rem"><?= e($summary['sos_total']) ?></div>
            </div>
            <div class="stat-tile" style="padding:0.85rem">
                <div class="stat-tile__label">Blocked</div>
                <div class="stat-tile__value" style="font-size:1.9rem"><?= e($summary['blocked_total']) ?></div>
            </div>
        </div>
    </div>
</div>

<style>
@media (min-width: 800px) {
    .lg-two-col { grid-template-columns: 1fr 1fr !important; }
}
</style>
