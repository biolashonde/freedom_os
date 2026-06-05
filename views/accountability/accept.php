<section class="mx-auto max-w-xl">
    <div class="rounded-lg border border-white/10 bg-white/5 p-6">
        <p class="text-sm text-teal-300">Accountability invite</p>
        <h1 class="mt-1 text-3xl font-bold"><?= e($invite['inviter_name']) ?> invited you to walk with them.</h1>
        <p class="mt-3 text-slate-300">You will only receive what they have consented to share. You can start by setting your name and password.</p>

        <div class="mt-5 rounded-lg bg-slate-950 p-4 text-sm text-slate-300">
            <p>SOS alerts: <?= $invite['sos_alerts'] ? 'enabled' : 'disabled' ?></p>
            <p>Weekly digest: <?= $invite['weekly_digest'] ? 'enabled' : 'disabled' ?></p>
            <p>Relapse visibility: <?= $invite['relapse_visibility'] ? 'enabled' : 'disabled' ?></p>
        </div>

        <?php if ($invite['status'] !== 'pending'): ?>
            <p class="mt-5 rounded-lg bg-teal-500/10 px-4 py-3 text-teal-100">This invite is already active.</p>
        <?php else: ?>
            <form class="mt-5 space-y-4" method="post" action="<?= e(base_url('/accountability/accept/' . $token)) ?>">
                <?= csrf_field() ?>
                <label class="block">
                    <span class="mb-1 block text-sm text-slate-300">Your name</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="name" value="<?= e($invite['partner_name']) ?>" required>
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm text-slate-300">Set password</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="password" type="password" minlength="8" required>
                </label>
                <button class="rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" type="submit">Accept invite</button>
            </form>
        <?php endif; ?>
    </div>
</section>
