<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-teal-300">Operations</p>
            <h1 class="mt-1 text-3xl font-bold">Admin console</h1>
            <p class="mt-2 max-w-2xl text-slate-300">A lightweight beta-support view for mentors and admins.</p>
        </div>
        <?php if ((Auth::user()['role'] ?? 'user') === 'superadmin'): ?>
            <a class="rounded-lg border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/5" href="<?= e(base_url('/admin/analytics')) ?>">Open app analytics</a>
        <?php endif; ?>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($stats as $label => $value): ?>
            <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                <p class="text-sm capitalize text-slate-400"><?= e(str_replace('_', ' ', $label)) ?></p>
                <p class="mt-1 text-3xl font-bold"><?= e($value) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="rounded-lg border border-white/10 bg-white/5 p-5">
        <h2 class="text-xl font-semibold">Recent users</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="text-slate-400">
                    <tr>
                        <th class="py-2">User</th>
                        <th>Role</th>
                        <th>Streak</th>
                        <th>Check-ins</th>
                        <th>SOS</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-t border-white/10">
                            <td class="py-2">
                                <p class="font-semibold"><?= e($user['name']) ?></p>
                                <p class="text-slate-400"><?= e($user['email']) ?></p>
                            </td>
                            <td><?= e($user['role']) ?></td>
                            <td><?= e($user['current_days'] ?? 0) ?> / <?= e($user['longest_days'] ?? 0) ?></td>
                            <td><?= e($user['checkin_count']) ?></td>
                            <td><?= e($user['sos_count']) ?></td>
                            <td><?= e($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Pending overrides</h2>
            <div class="mt-4 space-y-3">
                <?php foreach ($overrides as $override): ?>
                    <div class="rounded-lg bg-slate-950 p-4 text-sm">
                        <p class="font-semibold"><?= e($override['name']) ?> <span class="text-slate-400"><?= e($override['email']) ?></span></p>
                        <p class="mt-2 break-all"><?= e($override['url']) ?></p>
                        <p class="mt-1 text-slate-400"><?= e($override['reason']) ?></p>
                        <form class="mt-3 flex gap-2" method="post" action="<?= e(base_url('/admin/overrides/' . $override['id'] . '/review')) ?>">
                            <?= csrf_field() ?>
                            <button class="rounded bg-teal-600 px-3 py-1 font-semibold text-white hover:bg-teal-500" name="decision" value="approved" type="submit">Approve</button>
                            <button class="rounded bg-red-500 px-3 py-1 font-semibold text-white hover:bg-red-400" name="decision" value="denied" type="submit">Deny</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (!$overrides): ?>
                    <p class="text-sm text-slate-400">No pending overrides.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Testimonies</h2>
            <div class="mt-4 space-y-3">
                <?php foreach ($testimonies as $testimony): ?>
                    <div class="rounded-lg bg-slate-950 p-4 text-sm">
                        <p class="font-semibold"><?= e($testimony['title']) ?></p>
                        <p class="text-slate-400"><?= e($testimony['name']) ?> · <?= e($testimony['email']) ?></p>
                        <p class="mt-2 line-clamp-3 text-slate-300"><?= e($testimony['body']) ?></p>
                        <form class="mt-3" method="post" action="<?= e(base_url('/admin/testimonies/' . $testimony['id'] . '/visibility')) ?>">
                            <?= csrf_field() ?>
                            <label class="text-slate-300">
                                <input name="is_public" type="checkbox" <?= $testimony['is_public'] ? 'checked' : '' ?>> Public/shareable
                            </label>
                            <button class="ml-3 rounded border border-white/10 px-3 py-1 hover:bg-white/5" type="submit">Save</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (!$testimonies): ?>
                    <p class="text-sm text-slate-400">No testimonies yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-white/10 bg-white/5 p-5">
        <h2 class="text-xl font-semibold">Recent blocker logs</h2>
        <div class="mt-4 space-y-2">
            <?php foreach ($blockerLogs as $log): ?>
                <div class="rounded bg-slate-950 px-3 py-2 text-sm">
                    <p class="break-all"><?= e($log['blocked_url']) ?></p>
                    <p class="text-slate-400"><?= e($log['name'] ?? 'Unknown') ?> · <?= e($log['reason']) ?> · <?= e($log['attempted_at']) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (!$blockerLogs): ?>
                <p class="text-sm text-slate-400">No blocker logs yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
