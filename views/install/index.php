<section class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-sm text-teal-300">FreedomOS setup</p>
        <h1 class="mt-1 text-3xl font-bold">Robust installer</h1>
        <p class="mt-2 max-w-2xl text-slate-300">Create the database, write the environment file, seed core content, and provision the first superadmin account.</p>
    </div>

    <?php if ($result): ?>
        <div class="rounded-lg border border-teal-300/30 bg-teal-500/10 p-5">
            <h2 class="text-xl font-semibold text-teal-100">Install complete</h2>
            <div class="mt-3 grid gap-3 text-sm text-slate-300 sm:grid-cols-3">
                <p><span class="block text-slate-500">Database</span><?= e($result['database']) ?></p>
                <p><span class="block text-slate-500">Superadmin</span><?= e($result['superadmin']) ?></p>
                <p><span class="block text-slate-500">App URL</span><?= e($result['app_url']) ?></p>
            </div>
            <a class="mt-4 inline-block rounded-lg bg-teal-600 px-4 py-2 font-semibold text-white hover:bg-teal-500" href="<?= e(base_url('/login')) ?>">Go to login</a>
        </div>
    <?php endif; ?>

    <?php if ($locked && !$result): ?>
        <div class="rounded-lg border border-amber-300/30 bg-amber-500/10 p-5 text-amber-50">
            <h2 class="text-xl font-semibold">Installer locked</h2>
            <p class="mt-2 text-sm">The install lock exists at <span class="font-mono">storage/install.lock</span>. To intentionally rerun setup, set <span class="font-mono">APP_INSTALL_UNLOCK=true</span> in <span class="font-mono">.env</span>.</p>
        </div>
    <?php endif; ?>

    <div class="grid gap-5 lg:grid-cols-[.8fr_1.2fr]">
        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Preflight</h2>
            <div class="mt-4 space-y-2">
                <?php foreach ($requirements as $requirement): ?>
                    <div class="flex items-center justify-between rounded bg-slate-950 px-3 py-2 text-sm">
                        <span><?= e($requirement['label']) ?></span>
                        <span class="<?= $requirement['ok'] ? 'text-teal-300' : 'text-red-300' ?>"><?= $requirement['ok'] ? 'Ready' : 'Fix' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="rounded-lg border border-white/10 bg-white/5 p-5" method="post" action="<?= e(base_url('/install')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold">Setup details</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label>
                    <span class="mb-1 block text-sm text-slate-300">App URL</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="app_url" value="<?= e(absolute_url('/')) ?>" required>
                </label>
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Database host</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="db_host" value="localhost" required>
                </label>
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Database name</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="db_name" value="freedomos" required>
                </label>
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Database user</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="db_user" value="root" required>
                </label>
                <label class="sm:col-span-2">
                    <span class="mb-1 block text-sm text-slate-300">Database password</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="db_pass" type="password">
                </label>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Superadmin name</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="admin_name" required>
                </label>
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Superadmin email</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="admin_email" type="email" required>
                </label>
                <label class="sm:col-span-2">
                    <span class="mb-1 block text-sm text-slate-300">Superadmin password</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="admin_password" type="password" minlength="12" required>
                </label>
            </div>

            <button class="mt-6 rounded-lg bg-teal-600 px-4 py-2 font-semibold text-white hover:bg-teal-500 disabled:cursor-not-allowed disabled:opacity-50" type="submit" <?= (!$canInstall || $locked) ? 'disabled' : '' ?>>Install FreedomOS</button>
        </form>
    </div>
</section>
