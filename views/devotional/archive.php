<section>
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm text-teal-300">Devotional companion</p>
            <h1 class="mt-1 text-3xl font-bold">Archive</h1>
        </div>
        <a class="rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" href="<?= e(base_url('/devotional')) ?>">Today</a>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
        <?php foreach ($devotionals as $item): ?>
            <a class="rounded-lg border border-white/10 bg-white/5 p-4 hover:bg-white/10" href="<?= e(base_url('/devotional/' . $item['day_number'])) ?>">
                <p class="text-sm text-teal-300">Day <?= e($item['day_number']) ?> · <?= e($item['theme']) ?></p>
                <h2 class="mt-1 text-xl font-semibold"><?= e($item['title']) ?></h2>
                <p class="mt-2 text-sm text-slate-400"><?= e($item['scripture_ref']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>
