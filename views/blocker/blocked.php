<section class="mx-auto max-w-2xl">
    <div class="rounded-lg border border-red-300/30 bg-red-500/10 p-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-red-200">FreedomGuard</p>
        <h1 class="mt-2 text-4xl font-bold">This page was blocked.</h1>
        <p class="mt-3 text-red-50">This is a protection moment, not a punishment. Change location, breathe, and take the next clean step.</p>
    </div>
    <div class="mt-5 rounded-lg border border-white/10 bg-white/5 p-5">
        <h2 class="text-xl font-semibold">Details</h2>
        <p class="mt-3 break-all text-slate-300"><?= e($url) ?></p>
        <p class="mt-2 text-sm text-slate-400">Reason: <?= e($reason) ?></p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a class="rounded-lg bg-red-500 px-4 py-2 font-semibold text-white hover:bg-red-400" href="<?= e(base_url('/sos')) ?>">Open SOS</a>
            <a class="rounded-lg border border-white/10 px-4 py-2 text-slate-200 hover:bg-white/5" href="<?= e(base_url('/dashboard')) ?>">Back to dashboard</a>
        </div>
    </div>
</section>
