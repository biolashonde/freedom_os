<section class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-sm text-teal-300">Trust center</p>
        <h1 class="mt-1 text-3xl font-bold">Privacy and data control</h1>
        <p class="mt-2 max-w-2xl text-slate-300">FreedomOS stores sensitive recovery data. You can export your data or anonymize your account from here.</p>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <?php foreach ($counts as $label => $count): ?>
            <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                <p class="text-sm capitalize text-slate-400"><?= e(str_replace('_', ' ', $label)) ?></p>
                <p class="mt-1 text-3xl font-bold"><?= e($count) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <form class="rounded-lg border border-white/10 bg-white/5 p-5" method="post" action="<?= e(base_url('/privacy/export')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold">Export data</h2>
            <p class="mt-2 text-sm text-slate-300">Download a JSON copy of your profile, check-ins, SOS events, safety plan, goals, testimony, accountability links, and notifications.</p>
            <button class="mt-4 rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" type="submit">Download export</button>
        </form>

        <form class="rounded-lg border border-red-300/30 bg-red-500/10 p-5" method="post" action="<?= e(base_url('/privacy/delete')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold text-red-100">Delete account</h2>
            <p class="mt-2 text-sm text-red-50">This anonymizes your profile, pauses accountability links, and prevents future login. Export first if you want a copy.</p>
            <label class="mt-4 block">
                <span class="mb-1 block text-sm text-red-100">Password</span>
                <input class="w-full rounded-lg border border-red-200/20 bg-slate-950 px-3 py-2" name="password" type="password" required>
            </label>
            <label class="mt-3 block">
                <span class="mb-1 block text-sm text-red-100">Type DELETE</span>
                <input class="w-full rounded-lg border border-red-200/20 bg-slate-950 px-3 py-2" name="confirm" required>
            </label>
            <button class="mt-4 rounded-lg bg-red-500 px-4 py-2 font-semibold text-white hover:bg-red-400" type="submit">Anonymize account</button>
        </form>
    </div>
</section>
