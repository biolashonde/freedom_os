<section class="space-y-6">
    <div>
        <p class="text-sm text-teal-300">FreedomGuard</p>
        <h1 class="mt-1 text-3xl font-bold">Blocker foundation</h1>
        <p class="mt-2 max-w-2xl text-slate-300">Create device tokens, manage local block rules, and review blocked attempts. The browser extension and sidecar will use this API.</p>
    </div>

    <?php if (!empty($newToken)): ?>
        <div class="rounded-lg border border-amber-300/30 bg-amber-500/10 p-4">
            <h2 class="font-semibold text-amber-100">Copy this device token now</h2>
            <input class="mt-2 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2 text-amber-50" readonly value="<?= e($newToken) ?>">
        </div>
    <?php endif; ?>

    <div class="rounded-lg border border-teal-300/20 bg-teal-500/10 p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">Smart install</h2>
                <p class="mt-2 max-w-2xl text-sm text-slate-300">Browsers do not allow private extensions to install silently from a website. Download the package here, then load it in Developer Mode. For a true one-click install later, publish it to Chrome Web Store, Edge Add-ons, Firefox Add-ons, or the Apple App Store.</p>
            </div>
            <form method="post" action="<?= e(base_url('/guard/extension/download')) ?>">
                <?= csrf_field() ?>
                <button class="rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" type="submit">Download extension ZIP</button>
            </form>
        </div>
        <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm text-slate-300">
            <li>Create a device token below.</li>
            <li>Download and unzip the extension package.</li>
            <li>Open `chrome://extensions` or `edge://extensions` and enable Developer Mode.</li>
            <li>Choose Load unpacked and select the unzipped folder.</li>
            <li>Paste your app URL and device token in the extension options.</li>
        </ol>
        <a class="mt-4 inline-block text-sm font-semibold text-teal-200 hover:text-teal-100" href="<?= e(base_url('/guard/mobile')) ?>">Phone setup options</a>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <form class="rounded-lg border border-white/10 bg-white/5 p-5" method="post" action="<?= e(base_url('/guard/devices')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold">Device token</h2>
            <label class="mt-4 block">
                <span class="mb-1 block text-sm text-slate-300">Device name</span>
                <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="name" placeholder="Chrome on laptop">
            </label>
            <button class="mt-4 rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" type="submit">Create token</button>
            <div class="mt-4 space-y-2">
                <?php foreach ($devices as $device): ?>
                    <div class="rounded bg-slate-950 px-3 py-2 text-sm">
                        <strong><?= e($device['name']) ?></strong>
                        <span class="text-slate-400"> · <?= e($device['token_prefix']) ?>... · last seen <?= e($device['last_seen_at'] ?? 'never') ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$devices): ?>
                    <p class="text-sm text-slate-400">No devices yet.</p>
                <?php endif; ?>
            </div>
        </form>

        <form class="rounded-lg border border-white/10 bg-white/5 p-5" method="post" action="<?= e(base_url('/guard/rules')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold">Add block rule</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Type</span>
                    <select class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="rule_type">
                        <option value="domain">Domain</option>
                        <option value="keyword">Keyword</option>
                    </select>
                </label>
                <label>
                    <span class="mb-1 block text-sm text-slate-300">Reason</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="reason" value="custom">
                </label>
            </div>
            <label class="mt-3 block">
                <span class="mb-1 block text-sm text-slate-300">Pattern</span>
                <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="pattern" placeholder="example.com or keyword" required>
            </label>
            <button class="mt-4 rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" type="submit">Add rule</button>
        </form>
    </div>

    <div class="rounded-lg border border-white/10 bg-white/5 p-5">
        <h2 class="text-xl font-semibold">Active rules</h2>
        <div class="mt-4 grid gap-2 md:grid-cols-2">
            <?php foreach ($rules as $rule): ?>
                <div class="rounded bg-slate-950 px-3 py-2 text-sm">
                    <span class="text-teal-300"><?= e($rule['rule_type']) ?></span>
                    <strong><?= e($rule['pattern']) ?></strong>
                    <span class="text-slate-400"> · <?= e($rule['reason']) ?><?= $rule['user_id'] ? '' : ' · global' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <form class="rounded-lg border border-white/10 bg-white/5 p-5" method="post" action="<?= e(base_url('/guard/override')) ?>">
            <?= csrf_field() ?>
            <h2 class="text-xl font-semibold">Override request</h2>
            <label class="mt-4 block">
                <span class="mb-1 block text-sm text-slate-300">URL</span>
                <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="url" type="url" required>
            </label>
            <label class="mt-3 block">
                <span class="mb-1 block text-sm text-slate-300">Reason</span>
                <textarea class="min-h-20 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="reason" required></textarea>
            </label>
            <button class="mt-4 rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" type="submit">Request override</button>
        </form>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Recent overrides</h2>
            <div class="mt-4 space-y-2">
                <?php foreach ($overrides as $override): ?>
                    <div class="rounded bg-slate-950 px-3 py-2 text-sm">
                        <p><?= e($override['url']) ?></p>
                        <p class="text-slate-400"><?= e($override['status']) ?> · <?= e($override['requested_at']) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (!$overrides): ?>
                    <p class="text-sm text-slate-400">No override requests yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-white/10 bg-white/5 p-5">
        <h2 class="text-xl font-semibold">Recent blocked attempts</h2>
        <div class="mt-4 space-y-2">
            <?php foreach ($logs as $log): ?>
                <div class="rounded bg-slate-950 px-3 py-2 text-sm">
                    <p class="break-all"><?= e($log['blocked_url']) ?></p>
                    <p class="text-slate-400"><?= e($log['reason']) ?> · <?= e($log['attempted_at']) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (!$logs): ?>
                <p class="text-sm text-slate-400">No blocked attempts yet.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
