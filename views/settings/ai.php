<section class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-sm text-teal-300">Personal AI</p>
        <h1 class="mt-1 text-3xl font-bold">Your AI keys</h1>
        <p class="mt-2 max-w-2xl text-slate-300">Bring your own provider key for personalized devotionals. Keys are tied to your account and are not managed by the superadmin.</p>
    </div>

    <form class="space-y-6" method="post" action="<?= e(base_url('/settings/ai')) ?>">
        <?= csrf_field() ?>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Provider</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Preferred provider</span>
                    <select class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="provider">
                        <?php foreach (['auto', 'anthropic', 'openai', 'gemini', 'openrouter'] as $provider): ?>
                            <option value="<?= e($provider) ?>" <?= $settings['provider'] === $provider ? 'selected' : '' ?>><?= e($provider) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <?php foreach ([
                'anthropic' => ['label' => 'Anthropic', 'key' => 'anthropic_key', 'model' => 'anthropic_model'],
                'openai' => ['label' => 'OpenAI', 'key' => 'openai_key', 'model' => 'openai_model'],
                'gemini' => ['label' => 'Gemini', 'key' => 'gemini_key', 'model' => 'gemini_model'],
                'openrouter' => ['label' => 'OpenRouter', 'key' => 'openrouter_key', 'model' => 'openrouter_model'],
            ] as $provider => $meta): ?>
                <div class="rounded-lg border border-white/10 bg-white/5 p-5">
                    <h2 class="text-xl font-semibold"><?= e($meta['label']) ?></h2>
                    <div class="mt-4 space-y-4">
                        <label class="block">
                            <span class="mb-1 block text-sm text-slate-300">API key</span>
                            <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="<?= e($meta['key']) ?>" type="password" placeholder="<?= e(UserAISettings::mask($settings[$meta['key']])) ?>">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-sm text-slate-300">Model</span>
                            <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="<?= e($meta['model']) ?>" value="<?= e($settings[$meta['model']]) ?>">
                        </label>
                        <?php if ($provider === 'openrouter'): ?>
                            <label class="block">
                                <span class="mb-1 block text-sm text-slate-300">Site URL</span>
                                <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="openrouter_site_url" value="<?= e($settings['openrouter_site_url']) ?>">
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-sm text-slate-300">App name</span>
                                <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="openrouter_app_name" value="<?= e($settings['openrouter_app_name']) ?>">
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="rounded-lg border border-amber-300/20 bg-amber-500/10 p-4 text-sm text-amber-50">
            Blank key fields keep the existing stored key unchanged. To replace a key, paste the new one and save.
        </div>

        <button class="rounded-lg bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-500" type="submit">Save my AI keys</button>
    </form>
</section>
