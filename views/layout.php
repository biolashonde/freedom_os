<?php
$config = require ROOT . '/config/config.php';
$title = $title ?? $config['app']['name'];
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$basePath = rtrim(parse_url(base_url('/'), PHP_URL_PATH), '/');
$isActive = function(string $path) use ($currentPath, $basePath): bool {
    $check = $basePath . $path;
    return $path === '/'
        ? $currentPath === $check
        : str_starts_with($currentPath, $check);
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0c0f0e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= e($title) ?> | <?= e($config['app']['name']) ?></title>
    <link rel="manifest" href="<?= e(base_url('/manifest.json')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
</head>
<body>

<?php if (Auth::check()): ?>
<!-- ════════════════════ AUTHENTICATED SHELL ════════════════════ -->

<!-- Header -->
<header class="app-header">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>
    <a href="<?= e(base_url('/dashboard')) ?>" class="app-header__logo">
        <div class="logo-mark">🕊</div>
        <span>FreedomOS</span>
    </a>
    <div class="header-spacer"></div>
    <div class="header-nav">
        <a href="<?= e(base_url('/sos')) ?>" style="background:var(--red-dim);color:var(--red-text);border:1px solid rgba(192,57,43,0.3);border-radius:var(--radius-sm);padding:0.3rem 0.8rem;font-size:0.8rem;font-weight:700;letter-spacing:0.04em;">
            ⚡ SOS
        </a>
        <form method="post" action="<?= e(base_url('/logout')) ?>" style="display:contents">
            <?= csrf_field() ?>
            <button type="submit" style="background:none;border:none;cursor:pointer;padding:0.35rem 0.75rem;border-radius:var(--radius-sm);font-size:0.875rem;font-weight:500;color:var(--text-secondary);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'">Logout</button>
        </form>
    </div>
</header>

<!-- Sidebar overlay -->
<div class="sidebar-overlay hidden" id="sidebarOverlay"></div>

<!-- Sidebar -->
<nav class="app-sidebar" id="appSidebar" aria-label="Main navigation">

    <div class="sidebar-label">Recovery</div>
    <a href="<?= e(base_url('/dashboard')) ?>" class="sidebar-link <?= $isActive('/dashboard') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>
    <a href="<?= e(base_url('/progress')) ?>" class="sidebar-link <?= $isActive('/progress') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Progress
    </a>
    <a href="<?= e(base_url('/devotional')) ?>" class="sidebar-link <?= $isActive('/devotional') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        Devotional
    </a>
    <a href="<?= e(base_url('/purpose')) ?>" class="sidebar-link <?= $isActive('/purpose') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Purpose
    </a>

    <div class="sidebar-label" style="margin-top:0.5rem">Support</div>
    <a href="<?= e(base_url('/accountability')) ?>" class="sidebar-link <?= $isActive('/accountability') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Accountability
    </a>
    <a href="<?= e(base_url('/partner')) ?>" class="sidebar-link <?= $isActive('/partner') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
        Partner View
    </a>
    <a href="<?= e(base_url('/guard')) ?>" class="sidebar-link <?= $isActive('/guard') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Guard
    </a>
    <a href="<?= e(base_url('/community')) ?>" class="sidebar-link <?= $isActive('/community') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a4 4 0 01-4 4H7l-4 4V7a4 4 0 014-4h10a4 4 0 014 4z"/></svg>
        Community
    </a>

    <div class="sidebar-label" style="margin-top:0.5rem">Other</div>
    <a href="<?= e(base_url('/donate')) ?>" class="sidebar-link <?= $isActive('/donate') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        Donate
    </a>
    <a href="<?= e(base_url('/privacy')) ?>" class="sidebar-link <?= $isActive('/privacy') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Privacy
    </a>
    <a href="<?= e(base_url('/settings/ai')) ?>" class="sidebar-link <?= $isActive('/settings/ai') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/><circle cx="12" cy="12" r="3"/></svg>
        AI Keys
    </a>
    <a href="<?= e(base_url('/health')) ?>" class="sidebar-link <?= $isActive('/health') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Health
    </a>

    <?php if (in_array(Auth::user()['role'] ?? 'user', ['superadmin', 'admin', 'mentor'], true)): ?>
    <div class="sidebar-label" style="margin-top:0.5rem">Admin</div>
    <a href="<?= e(base_url('/admin')) ?>" class="sidebar-link <?= $isActive('/admin') && !$isActive('/admin/analytics') && !$isActive('/admin/donations') && !$isActive('/admin/settings') && !$isActive('/admin/email') && !$isActive('/admin/content') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M5.34 18.66l-1.41 1.41M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M5.34 5.34 3.93 3.93M12 20v2M12 2v2"/></svg>
        Admin
    </a>
    <?php endif; ?>
    <?php if ((Auth::user()['role'] ?? 'user') === 'superadmin'): ?>
    <a href="<?= e(base_url('/admin/analytics')) ?>" class="sidebar-link <?= $isActive('/admin/analytics') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Analytics
    </a>
    <a href="<?= e(base_url('/admin/donations')) ?>" class="sidebar-link <?= $isActive('/admin/donations') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        Donations
    </a>
    <a href="<?= e(base_url('/admin/settings')) ?>" class="sidebar-link <?= $isActive('/admin/settings') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M5.34 18.66l-1.41 1.41M20 12h2M2 12h2M19.07 19.07l-1.41-1.41M5.34 5.34 3.93 3.93M12 20v2M12 2v2"/></svg>
        Settings
    </a>
    <a href="<?= e(base_url('/admin/email')) ?>" class="sidebar-link <?= $isActive('/admin/email') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        Email
    </a>
    <a href="<?= e(base_url('/admin/content')) ?>" class="sidebar-link <?= $isActive('/admin/content') ? 'active' : '' ?>">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        Content Studio
    </a>
    <?php endif; ?>

    <!-- SOS at bottom -->
    <div style="flex:1"></div>
    <a href="<?= e(base_url('/sos')) ?>" class="sidebar-link sos-link">
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        SOS — I need help now
    </a>
</nav>

<!-- Main content -->
<main class="app-main">

<?php else: ?>
<!-- ════════════════════ PUBLIC SHELL ════════════════════ -->
<header class="app-header">
    <a href="<?= e(base_url('/')) ?>" class="app-header__logo">
        <div class="logo-mark">🕊</div>
        <span>FreedomOS</span>
    </a>
    <div class="header-spacer"></div>
    <nav class="header-nav">
        <a href="<?= e(base_url('/donate')) ?>">Donate</a>
        <a href="<?= e(base_url('/login')) ?>">Log in</a>
        <a href="<?= e(base_url('/register')) ?>" class="btn btn-primary btn-sm">Get started</a>
    </nav>
</header>

<main class="app-main app-main--public" style="padding-top:0">

<?php endif; ?>

    <?php if ($message = flash('success')): ?>
        <div class="alert alert-success animate-fade-up" style="margin-bottom:1.25rem" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><polyline points="20 6 9 17 4 12"/></svg>
            <?= e($message) ?>
        </div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert alert-error animate-fade-up" style="margin-bottom:1.25rem" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <?php require $templatePath; ?>

</main>

<?php if (!Auth::check()): ?>
<footer style="border-top:1px solid var(--border);padding:1.5rem;text-align:center;font-size:0.8rem;color:var(--text-muted);">
    <div style="display:flex;align-items:center;justify-content:center;gap:1.5rem;flex-wrap:wrap;">
        <span>© <?= date('Y') ?> FreedomOS</span>
        <a href="<?= e(base_url('/privacy')) ?>" style="color:var(--text-muted)" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-muted)'">Privacy</a>
        <a href="<?= e(base_url('/donate')) ?>" style="color:var(--text-muted)" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-muted)'">Support the mission</a>
    </div>
</footer>
<?php endif; ?>

<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
