<section class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-sm text-teal-300">FreedomGuard mobile</p>
        <h1 class="mt-1 text-3xl font-bold">Phone setup options</h1>
        <p class="mt-2 max-w-2xl text-slate-300">FreedomOS already works well on phones as a PWA. Browser-level Guard on phones depends on the platform, because mobile browsers restrict extensions heavily.</p>
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">FreedomOS on phone</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-300">
                <li>Open FreedomOS in Safari or Chrome.</li>
                <li>Log in.</li>
                <li>Use Add to Home Screen.</li>
                <li>Open SOS once while online so the PWA can cache its offline fallback.</li>
            </ol>
            <p class="mt-4 text-sm text-slate-400">This gives you dashboard, SOS, devotionals, purpose, accountability, and privacy controls on mobile.</p>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Android Guard</h2>
            <p class="mt-3 text-sm text-slate-300">Best path: Firefox for Android extension support, or a future native Android VPN/DNS app. Chrome on Android does not support normal extension installs like desktop Chrome.</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-300">
                <li class="rounded bg-slate-950 px-3 py-2">Near term: package a Firefox-compatible extension variant.</li>
                <li class="rounded bg-slate-950 px-3 py-2">Stronger path: Android local VPN/DNS blocker app using the same `/blocker/check` API.</li>
                <li class="rounded bg-slate-950 px-3 py-2">Simple path: DNS provider such as NextDNS/AdGuard with FreedomOS accountability logs added later.</li>
            </ul>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">iPhone Guard</h2>
            <p class="mt-3 text-sm text-slate-300">Best path: Safari Web Extension or Content Blocker distributed through an iOS app in the App Store. Apple requires the extension to be packaged/signed as an app.</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-300">
                <li class="rounded bg-slate-950 px-3 py-2">Near term: PWA plus iOS Screen Time content restrictions.</li>
                <li class="rounded bg-slate-950 px-3 py-2">Production path: Safari content blocker app generated from FreedomGuard rules.</li>
                <li class="rounded bg-slate-950 px-3 py-2">Network path: DNS profile or managed device profile.</li>
            </ul>
        </div>

        <div class="rounded-lg border border-white/10 bg-white/5 p-5">
            <h2 class="text-xl font-semibold">Recommendation</h2>
            <p class="mt-3 text-sm text-slate-300">Use FreedomOS as a mobile PWA now. For Guard, ship desktop extension first, then build phone protection as either DNS/VPN or Safari/Firefox mobile packages. That keeps the product reliable without fighting each phone browser's security model.</p>
            <a class="mt-4 inline-block rounded-lg bg-teal-600 px-4 py-2 font-semibold hover:bg-teal-500" href="<?= e(base_url('/guard')) ?>">Back to Guard</a>
        </div>
    </div>
</section>
