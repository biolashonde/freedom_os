<section class="space-y-6">
    <div>
        <p class="text-sm text-teal-300">Superadmin</p>
        <h1 class="mt-1 text-3xl font-bold">App settings</h1>
        <p class="mt-2 max-w-2xl text-slate-300">Manage app URL and email delivery settings. AI provider keys belong to individual users and are managed from their AI Keys page.</p>
    </div>

    <form class="space-y-6" method="post" action="<?= e(base_url('/admin/settings')) ?>">
        <?= csrf_field() ?>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">App</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label>
                    <span class="mb-1 block text-sm text-slate-300">App URL</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="app_url" value="<?= e($settings['app']['app_url']) ?>">
                </label>
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Debug mode</span>
                    <select class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="app_debug">
                        <option value="false" <?= $settings['app']['app_debug'] === 'false' ? 'selected' : '' ?>>false</option>
                        <option value="true" <?= $settings['app']['app_debug'] === 'true' ? 'selected' : '' ?>>true</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Email</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    'mail_host' => 'SMTP host',
                    'mail_port' => 'SMTP port',
                    'mail_user' => 'SMTP username',
                    'mail_from' => 'From email',
                    'mail_from_name' => 'From name',
                ] as $name => $label): ?>
                    <label>
                        <span class="mb-1 block text-sm text-slate-300"><?= e($label) ?></span>
                        <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="<?= e($name) ?>" value="<?= e($settings['mail'][$name]) ?>">
                    </label>
                <?php endforeach; ?>
                <label>
                    <span class="mb-1 block text-sm text-slate-300">SMTP password</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="mail_pass" type="password" placeholder="<?= e(AppSettings::mask($settings['mail']['mail_pass'])) ?>">
                </label>
            </div>
        </div>

        <button class="rounded-lg bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-500" type="submit">Save settings</button>
    </form>
</section>
