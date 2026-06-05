<?php
$maxDaily = max(1, ...array_map(fn (array $row): int => max((int) $row['signups'], (int) $row['checkins'], (int) $row['sos'], (int) $row['guard_blocks']), $daily ?: [['signups' => 0, 'checkins' => 0, 'sos' => 0, 'guard_blocks' => 0]]));
$funnelMax = max(1, (int) $funnel['users']);
?>
<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-teal-300">Superadmin</p>
            <h1 class="mt-1 text-3xl font-bold">App analytics</h1>
            <p class="mt-2 max-w-2xl text-slate-300">Platform health, recovery engagement, Guard pressure, and launch readiness metrics without exposing private journal notes.</p>
        </div>
        <a class="rounded-lg border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/5" href="<?= e(base_url('/admin')) ?>">Admin console</a>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <?php foreach ($overview as $label => $value): ?>
            <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                <p class="text-sm capitalize text-slate-400"><?= e(str_replace('_', ' ', $label)) ?></p>
                <p class="mt-1 text-3xl font-bold"><?= e($value) ?><?= str_ends_with($label, 'rate') ? '%' : '' ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1.2fr_.8fr]">
        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">30-day activity</h2>
            <div class="mt-4 grid gap-2 overflow-x-auto pb-2" style="grid-template-columns: repeat(30, minmax(24px, 1fr));">
                <?php foreach ($daily as $day): ?>
                    <?php $height = max(8, (int) round(((int) $day['checkins'] / $maxDaily) * 96)); ?>
                    <div class="flex min-h-36 flex-col items-center justify-end gap-1" title="<?= e($day['day']) ?>">
                        <?php if ((int) $day['signups'] > 0): ?><span class="h-1.5 w-1.5 rounded-full bg-sky-300"></span><?php endif; ?>
                        <?php if ((int) $day['sos'] > 0): ?><span class="h-1.5 w-1.5 rounded-full bg-red-300"></span><?php endif; ?>
                        <?php if ((int) $day['guard_blocks'] > 0): ?><span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span><?php endif; ?>
                        <div class="w-full rounded-t bg-teal-400" style="height: <?= e($height) ?>px"></div>
                        <span class="text-[10px] text-slate-500"><?= e(substr((string) $day['day'], -2)) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="mt-3 text-xs text-slate-400">Bar height shows check-ins. Blue dot means signup, red dot means SOS, amber dot means Guard block.</p>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Activation funnel</h2>
            <div class="mt-4 space-y-3">
                <?php foreach ($funnel as $label => $value): ?>
                    <?php $width = (int) round(((int) $value / $funnelMax) * 100); ?>
                    <div>
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="capitalize text-slate-300"><?= e(str_replace('_', ' ', $label)) ?></span>
                            <span class="text-slate-400"><?= e($value) ?></span>
                        </div>
                        <div class="h-2 rounded bg-slate-950">
                            <div class="h-2 rounded bg-teal-400" style="width: <?= e($width) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_.8fr]">
        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">At-risk accounts</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="text-slate-400">
                        <tr>
                            <th class="py-2">User</th>
                            <th>Streak</th>
                            <th>Last check-in</th>
                            <th>Resets</th>
                            <th>SOS</th>
                            <th>Guard</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($riskUsers as $user): ?>
                            <tr class="border-t border-white/10">
                                <td class="py-2">
                                    <p class="font-semibold"><?= e($user['name']) ?></p>
                                    <p class="text-slate-400"><?= e($user['email']) ?></p>
                                </td>
                                <td><?= e($user['current_days']) ?></td>
                                <td><?= e($user['last_checkin'] ?? 'never') ?></td>
                                <td><?= e($user['resets_30d']) ?></td>
                                <td><?= e($user['sos_30d']) ?></td>
                                <td><?= e($user['guard_30d']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$riskUsers): ?>
                            <tr><td class="py-3 text-slate-400" colspan="6">No at-risk accounts surfaced right now.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">System health</h2>
            <div class="mt-4 grid gap-3">
                <?php foreach ($system as $label => $value): ?>
                    <div class="flex items-center justify-between rounded bg-slate-950 px-3 py-2 text-sm">
                        <span class="capitalize text-slate-300"><?= e(str_replace('_', ' ', $label)) ?></span>
                        <span class="font-semibold"><?= e($value) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="mt-4 text-sm text-slate-400">Use this panel before launches: queued mail, override backlog, and Guard device adoption are the fastest operational signals.</p>
        </div>
    </div>
</section>
