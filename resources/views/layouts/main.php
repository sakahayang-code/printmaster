<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?> - PrintMaster ERP</title>
    <!-- Favicon & PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
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
    <style>
        :root {
            --pm-sidebar-bg: #0f172a;
            --pm-sidebar-hover: #1e293b;
            --pm-sidebar-active: #2563eb;
            --pm-sidebar-text: #94a3b8;
            --pm-sidebar-text-active: #ffffff;
            --pm-topbar-bg: #ffffff;
            --pm-body-bg: #f8fafc;
        }
        body {
            background-color: var(--pm-body-bg);
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            min-height: 100vh;
        }
        #sidebar-wrapper {
            width: 270px;
            background: var(--pm-sidebar-bg);
            color: var(--pm-sidebar-text);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1040;
            transition: all 0.3s ease;
        }
        #page-content-wrapper {
            margin-left: 270px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        .sidebar-heading {
            padding: 1rem 1.5rem 0.4rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }
        .nav-link-pm {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.65rem 1.5rem;
            color: var(--pm-sidebar-text);
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .nav-link-pm:hover {
            color: var(--pm-sidebar-text-active);
            background: var(--pm-sidebar-hover);
        }
        .nav-link-pm.active {
            color: var(--pm-sidebar-text-active);
            background: var(--pm-sidebar-active);
            border-radius: 0 8px 8px 0;
            margin-right: 12px;
        }
        .topbar {
            height: 68px;
            background: var(--pm-topbar-bg);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }
        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 600;
            border-top: none;
        }
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: -270px;
            }
            #sidebar-wrapper.show {
                margin-left: 0;
            }
            #page-content-wrapper {
                margin-left: 0;
            }
        }
        .offline-bar {
            background: #ef4444;
            color: white;
            text-align: center;
            padding: 4px;
            font-size: 0.85rem;
            display: none;
        }
    </style>
</head>
<body>
    <div id="offline-indicator" class="offline-bar">
        <i class="bi bi-wifi-off"></i> Anda sedang berada dalam mode offline. Data transaksi tersimpan lokal dan akan disinkronkan saat online.
    </div>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar Component -->
        <?php \App\Core\View::component('sidebar'); ?>

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper">
            <!-- Topbar Component -->
            <?php \App\Core\View::component('topbar', ['activeBranch' => $activeBranch, 'userBranches' => $userBranches, 'currentUser' => $currentUser]); ?>

            <!-- Main Body Container -->
            <main class="container-fluid px-4 py-4 flex-grow-1">
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
            <footer class="bg-white border-top py-3 px-4 text-muted small d-flex justify-content-between align-items-center">
                <div>
                    <strong>PRINTMASTER ERP</strong> v1.0 &copy; <?= date('Y') ?> PT Master Grafika Indonesia.
                </div>
                <div>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-geo-alt-fill text-primary"></i> <?= htmlspecialchars($activeBranch['name'] ?? 'Pusat') ?>
                    </span>
                    <span class="badge bg-light text-dark border ms-2">
                        <i class="bi bi-clock"></i> <?= date('d M Y') ?>
                    </span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/chart.umd.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                document.getElementById('sidebar-wrapper').classList.toggle('show');
            });
        }

        // Offline / Online Status Detection
        function updateOnlineStatus() {
            const bar = document.getElementById('offline-indicator');
            if (navigator.onLine) {
                bar.style.display = 'none';
            } else {
                bar.style.display = 'block';
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
