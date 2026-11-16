<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?> - PrintMaster ERP</title>
    <!-- Favicon & PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0b1120">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <script>
        window.APP_BASE_URL = '<?= \App\Core\Request::basePath() ?>';
        if (window.APP_BASE_URL) {
            const _origFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === 'string' && url.startsWith('/') && !url.startsWith('//') && !url.startsWith(window.APP_BASE_URL + '/')) {
                    url = window.APP_BASE_URL + url;
                }
                return _origFetch.call(this, url, options);
            };
        }
    </script>
    <!-- Local Bootstrap 5.3 & Icons -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <!-- PrintMaster Modern Responsive Design System -->
    <link rel="stylesheet" href="/assets/css/printmaster.css">
</head>
<body>
    <div id="offline-indicator" class="offline-bar">
        <i class="bi bi-wifi-off me-1"></i> Anda sedang berada dalam mode offline. Data operasional tersimpan lokal dan akan disinkronkan saat online kembali.
    </div>

    <!-- Mobile Offcanvas Backdrop Overlay -->
    <div id="sidebar-overlay" aria-hidden="true"></div>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar Component -->
        <?php \App\Core\View::component('sidebar'); ?>

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper">
            <!-- Topbar Component -->
            <?php \App\Core\View::component('topbar', ['activeBranch' => $activeBranch, 'userBranches' => $userBranches, 'currentUser' => $currentUser]); ?>

            <!-- Main Body Container -->
            <main class="container-fluid px-3 px-md-4 py-4 flex-grow-1">
                <!-- Alerts / Flash Messages -->
                <?php \App\Core\View::component('alerts', [
                    'success' => $flashSuccess,
                    'error'   => $flashError,
                    'warning' => $flashWarning,
                    'info'    => $flashInfo
                ]); ?>

                <!-- Rendered View Content -->
                <?= $content ?>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-3 px-md-4 text-muted small d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div>
                    <strong>PRINTMASTER ERP</strong> v2.0 &copy; <?= date('Y') ?> PT Master Grafika Indonesia.
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= htmlspecialchars($activeBranch['name'] ?? 'Pusat') ?>
                    </span>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-clock me-1"></i> <?= date('d M Y') ?>
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check-circle-fill me-1"></i> Sistem Aktif
                    </span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (PWA App Experience for Mobile Users) -->
    <nav class="mobile-bottom-nav d-lg-none" aria-label="Navigasi Pintas">
        <?php $currentUri = \App\Core\Request::uri(); ?>
        <a href="/dashboard" class="mobile-nav-item <?= ($currentUri === '/dashboard' || $currentUri === '/') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        <a href="/pos" class="mobile-nav-item <?= ($currentUri === '/pos') ? 'active' : '' ?>">
            <i class="bi bi-calculator"></i>
            <span>Kasir</span>
        </a>
        <a href="/orders" class="mobile-nav-item <?= (strpos($currentUri, '/orders') === 0 && strpos($currentUri, '/orders/create') === false) ? 'active' : '' ?>">
            <i class="bi bi-bag-check"></i>
            <span>Pesanan</span>
        </a>
        <a href="/pricing" class="mobile-nav-item <?= (strpos($currentUri, '/pricing') === 0) ? 'active' : '' ?>">
            <i class="bi bi-calculator-fill"></i>
            <span>Hitung</span>
        </a>
        <button type="button" class="mobile-nav-item border-0 bg-transparent" id="mobileMenuBtn" aria-label="Menu Lengkap">
            <i class="bi bi-grid-fill"></i>
            <span>Menu</span>
        </button>
    </nav>

    <!-- Scripts -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/chart.umd.min.js"></script>
    <script>
        // Sidebar Mobile Offcanvas Controller
        const sidebarWrapper = document.getElementById('sidebar-wrapper');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');

        function openSidebar() {
            if (sidebarWrapper) sidebarWrapper.classList.add('show');
            if (sidebarOverlay) sidebarOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebarWrapper) sidebarWrapper.classList.remove('show');
            if (sidebarOverlay) sidebarOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            if (sidebarWrapper && sidebarWrapper.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebarWrapper && sidebarWrapper.classList.contains('show')) {
                closeSidebar();
            }
        });

        // Auto close when nav item clicked on mobile
        document.querySelectorAll('.nav-link-pm').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });
        });

        // Offline / Online Status Detection
        function updateOnlineStatus() {
            const bar = document.getElementById('offline-indicator');
            if (bar) {
                bar.style.display = navigator.onLine ? 'none' : 'block';
            }
        }
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('PrintMaster PWA Service Worker Registered', reg))
                    .catch(err => console.log('Service Worker failed', err));
            });
        }
    </script>
</body>
</html>
