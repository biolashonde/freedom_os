/* ── Service Worker ─────────────────────────────── */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const manifest = document.querySelector('link[rel="manifest"]');
        const swUrl = manifest ? manifest.href.replace(/manifest\.json$/, 'sw.js') : './sw.js';
        let refreshing = false;

        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (refreshing) return;
            refreshing = true;
            window.location.reload();
        });

        navigator.serviceWorker.register(swUrl, { updateViaCache: 'none' }).then((reg) => {
            reg.addEventListener('updatefound', () => {
                const worker = reg.installing;
                if (!worker) return;
                worker.addEventListener('statechange', () => {
                    if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                        worker.postMessage({ type: 'SKIP_WAITING' });
                    }
                });
            });
        }).catch(() => {});
    });
}

/* ── Online / Offline ───────────────────────────── */
window.addEventListener('online',  () => showToast('Back online.'));
window.addEventListener('offline', () => showToast('Offline mode. SOS fallback still available.'));

/* ── Toast notification ─────────────────────────── */
function showToast(message, duration = 4500) {
    let toast = document.querySelector('[data-toast]');
    if (!toast) {
        toast = document.createElement('div');
        toast.dataset.toast = 'true';
        toast.className = 'app-toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    window.clearTimeout(toast._timer);
    toast._timer = window.setTimeout(() => toast.remove(), duration);
}

/* ── Sidebar toggle (mobile) ────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.add('hidden');
        document.body.style.overflow = '';
    }

    toggle?.addEventListener('click', () => {
        sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    overlay?.addEventListener('click', closeSidebar);

    // Close on nav link tap (mobile)
    sidebar?.querySelectorAll('.sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 769) closeSidebar();
        });
    });

    // Close on resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 769) closeSidebar();
    });

    /* ── Active sidebar link highlight ─────────── */
    const path = window.location.pathname;
    sidebar?.querySelectorAll('.sidebar-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && path.startsWith(href) && href !== '/') {
            link.classList.add('active');
        }
    });

    /* ── Range slider live values ───────────────── */
    document.querySelectorAll('input[type="range"]').forEach(range => {
        const targetId = range.getAttribute('data-value-target');
        if (targetId) {
            const target = document.getElementById(targetId);
            if (target) {
                range.addEventListener('input', () => {
                    target.textContent = range.value;
                });
            }
        }
    });

    /* ── Auto-dismiss alerts ────────────────────── */
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s, transform 0.4s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-4px)';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    /* ── Dismiss button for alerts ──────────────── */
    document.querySelectorAll('[data-dismiss]').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.alert')?.remove();
        });
    });
});
