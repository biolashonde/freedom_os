<?php
$platforms = $donation['platform_links'] ?: [
    ['name' => 'Stripe Payment Link', 'url' => ''],
    ['name' => 'PayPal', 'url' => ''],
    ['name' => 'Donorbox', 'url' => ''],
    ['name' => 'Givebutter', 'url' => ''],
];
?>
<section class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm text-teal-300">Superadmin</p>
            <h1 class="mt-1 text-3xl font-bold">Donation settings</h1>
            <p class="mt-2 max-w-2xl text-slate-300">Control the donation story, manual transfer details, and external donation platform connectors shown on the homepage and Donate page.</p>
        </div>
        <a class="rounded-lg border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/5" href="<?= e(base_url('/donate')) ?>">Preview donate page</a>
    </div>

    <form class="space-y-6" method="post" action="<?= e(base_url('/admin/donations')) ?>">
        <?= csrf_field() ?>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Story copy</h2>
            <div class="mt-4 space-y-4">
                <label class="block">
                    <span class="mb-1 block text-sm text-slate-300">Donation headline</span>
                    <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="headline" value="<?= e($donation['headline']) ?>" maxlength="255">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm text-slate-300">Donation body</span>
                    <textarea class="min-h-28 w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="body"><?= e($donation['body']) ?></textarea>
                </label>
            </div>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <label class="flex items-center gap-2 font-semibold">
                <input name="manual_enabled" type="checkbox" <?= $donation['manual_enabled'] ? 'checked' : '' ?>>
                Enable manual bank transfer
            </label>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <?php foreach ([
                    'bank_name' => 'Bank name',
                    'account_name' => 'Account name',
                    'account_number' => 'Account number',
                    'routing_code' => 'Routing / sort code',
                    'iban' => 'IBAN',
                    'swift' => 'SWIFT / BIC',
                    'reference_note' => 'Reference note',
                ] as $name => $label): ?>
                    <label class="<?= $name === 'reference_note' ? 'sm:col-span-2' : '' ?>">
                        <span class="mb-1 block text-sm text-slate-300"><?= e($label) ?></span>
                        <input class="w-full rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="<?= e($name) ?>" value="<?= e($donation[$name]) ?>">
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <label class="flex items-center gap-2 font-semibold">
                <input name="platform_enabled" type="checkbox" <?= $donation['platform_enabled'] ? 'checked' : '' ?>>
                Enable donation platform connectors
            </label>
            <p class="mt-2 text-sm text-slate-400">Paste hosted checkout or donation URLs from Stripe Payment Links, PayPal, Donorbox, Givebutter, GoFundMe, Ko-fi, or another trusted platform.</p>
            <div class="mt-4 space-y-3">
                <?php foreach ($platforms as $platform): ?>
                    <div class="grid gap-3 sm:grid-cols-[.45fr_1fr]">
                        <input class="rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="platform_name[]" value="<?= e($platform['name'] ?? '') ?>" placeholder="Platform name">
                        <input class="rounded-lg border border-white/10 bg-slate-950 px-3 py-2" name="platform_url[]" value="<?= e($platform['url'] ?? '') ?>" placeholder="https://...">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button class="rounded-lg bg-teal-600 px-5 py-3 font-semibold text-white hover:bg-teal-500" type="submit">Save donation settings</button>
    </form>
</section>
