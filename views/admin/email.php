<section class="space-y-6">
    <div>
        <p class="text-sm text-teal-300">Superadmin</p>
        <h1 class="mt-1 text-3xl font-bold">Email workflow</h1>
        <p class="mt-2 max-w-3xl text-slate-300">Connect multiple SMTP senders, send password recovery and system emails through a resilient queue, and fall back automatically when one sender fails.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <?php foreach ($stats as $label => $value): ?>
            <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400"><?= e($label) ?></p>
                <p class="mt-2 text-2xl font-bold"><?= e($value) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">SMTP accounts</h2>
            <p class="mt-1 text-sm text-slate-400">Lower priority numbers are tried first. If one sender fails or hits its daily limit, FreedomOS tries the next active account.</p>

            <div class="mt-5 space-y-3">
                <?php foreach ($accounts as $account): ?>
                    <div class="rounded-lg border border-white/10 bg-slate-950 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold"><?= e($account['label']) ?> <?= (int) $account['active'] === 1 ? '' : '<span class="text-xs text-amber-300">(inactive)</span>' ?></p>
                                <p class="mt-1 text-sm text-slate-400"><?= e($account['from_name']) ?> &lt;<?= e($account['from_email']) ?>&gt; via <?= e($account['host']) ?>:<?= e($account['port']) ?> <?= e($account['encryption']) ?></p>
                                <p class="mt-1 text-xs text-slate-500">Priority <?= e($account['priority']) ?> · today <?= e($account['sent_today']) ?>/<?= e($account['daily_limit']) ?></p>
                                <?php if (!empty($account['last_error'])): ?>
                                    <p class="mt-2 text-xs text-red-300"><?= e($account['last_error']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <form method="post" action="<?= e(base_url('/admin/email/accounts/' . $account['id'] . '/test')) ?>" class="flex gap-2">
                                    <?= csrf_field() ?>
                                    <input class="w-48 rounded border border-white/10 bg-black px-2 py-1 text-sm" name="test_email" type="email" placeholder="test@example.com" required>
                                    <button class="rounded bg-slate-700 px-3 py-1 text-sm hover:bg-slate-600" type="submit">Test</button>
                                </form>
                                <form method="post" action="<?= e(base_url('/admin/email/accounts/' . $account['id'] . '/delete')) ?>">
                                    <?= csrf_field() ?>
                                    <button class="rounded bg-red-900/50 px-3 py-1 text-sm text-red-100 hover:bg-red-800" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (!$accounts): ?>
                    <div class="rounded-lg border border-amber-300/20 bg-amber-500/10 p-4 text-sm text-amber-100">No SMTP accounts yet. Add at least one for live delivery; file outbox remains available as a final fallback.</div>
                <?php endif; ?>
            </div>
        </div>

        <form class="rounded-lg border border-white/10 bg-white/5 p-5" method="post" action="<?= e(base_url('/admin/email/accounts')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold">Add sender</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <label><span class="mb-1 block text-sm text-slate-300">Label</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="label" required></label>
                <label><span class="mb-1 block text-sm text-slate-300">Host</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="host" required></label>
                <label><span class="mb-1 block text-sm text-slate-300">Port</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="port" value="587" required></label>
                <label><span class="mb-1 block text-sm text-slate-300">Encryption</span><select class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="encryption"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="none">None</option></select></label>
                <label><span class="mb-1 block text-sm text-slate-300">Username</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="username"></label>
                <label><span class="mb-1 block text-sm text-slate-300">Password</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="password" type="password"></label>
                <label><span class="mb-1 block text-sm text-slate-300">From email</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="from_email" type="email" required></label>
                <label><span class="mb-1 block text-sm text-slate-300">From name</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="from_name" value="FreedomOS"></label>
                <label><span class="mb-1 block text-sm text-slate-300">Priority</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="priority" value="100"></label>
                <label><span class="mb-1 block text-sm text-slate-300">Daily limit</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="daily_limit" value="500"></label>
                <label class="sm:col-span-2 flex items-center gap-2 text-sm text-slate-300"><input name="active" type="checkbox" checked> Active</label>
            </div>
            <button class="mt-4 rounded-lg bg-teal-600 px-4 py-2 font-semibold text-white hover:bg-teal-500" type="submit">Save sender</button>
        </form>
    </div>

    <div class="grid gap-6 lg:grid-cols-[.9fr_1.1fr]">
        <form class="rounded-lg border border-white/10 bg-white/5 p-5" method="post" action="<?= e(base_url('/admin/email/bulk')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold">Bulk email</h2>
            <p class="mt-1 text-sm text-slate-400">Use for product updates, recovery resources, and operational messages. Supports <code>{{name}}</code> and <code>{{email}}</code>.</p>
            <div class="mt-4 space-y-3">
                <label><span class="mb-1 block text-sm text-slate-300">Audience</span><select class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="audience"><option value="all">All active users</option><option value="user">Users</option><option value="mentor">Mentors</option><option value="admin">Admins</option><option value="superadmin">Superadmins</option></select></label>
                <label><span class="mb-1 block text-sm text-slate-300">Subject</span><input class="w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="subject" required></label>
                <label><span class="mb-1 block text-sm text-slate-300">Message</span><textarea class="min-h-48 w-full rounded border border-white/10 bg-slate-950 px-3 py-2" name="body" required></textarea></label>
            </div>
            <button class="mt-4 rounded-lg bg-teal-600 px-4 py-2 font-semibold text-white hover:bg-teal-500" type="submit">Queue bulk email</button>
        </form>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">Recent queue</h2>
                <form method="post" action="<?= e(base_url('/admin/email/process')) ?>">
                    <?= csrf_field() ?>
                    <button class="rounded bg-slate-700 px-3 py-2 text-sm hover:bg-slate-600" type="submit">Process now</button>
                </form>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-400"><tr><th class="py-2">Status</th><th>Type</th><th>Recipient</th><th>Subject</th><th>Attempts</th></tr></thead>
                    <tbody>
                    <?php foreach ($queue as $email): ?>
                        <tr class="border-t border-white/10">
                            <td class="py-2"><?= e($email['status']) ?></td>
                            <td><?= e($email['type']) ?></td>
                            <td><?= e($email['recipient_email']) ?></td>
                            <td><?= e($email['subject']) ?></td>
                            <td><?= e($email['attempts']) ?>/<?= e($email['max_attempts']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
