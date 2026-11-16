<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="/manifest.json">
    <title><?= htmlspecialchars($title ?? 'Portal Pelanggan - PRINTMASTER') ?></title>
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
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/printmaster.css">
    
    <style>
        :root {
            --pm-primary: #2563eb;
            --pm-primary-dark: #1d4ed8;
            --pm-accent: #f59e0b;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .customer-nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .hero-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 48px 0;
            border-radius: 0 0 24px 24px;
        }
        .footer-pm {
            margin-top: auto;
            background: #0f172a;
            color: #94a3b8;
            padding: 24px 0;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg customer-nav sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="/customer-portal">
                <i class="bi bi-printer-fill fs-3"></i>
                <span>PRINTMASTER</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="/customer-portal"><i class="bi bi-house me-1"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="/customer-portal/order"><i class="bi bi-cart-plus me-1"></i> Order Online</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="/customer-portal#tracking-section"><i class="bi bi-search me-1"></i> Lacak Pesanan</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-primary btn-sm px-3" href="/login">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login Staf ERP
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="flex-grow-1">
        <?php if (!empty($flashSuccess = \App\Core\Session::flash('success'))): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($flashError = \App\Core\Session::flash('error'))): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="footer-pm text-center">
        <div class="container">
            <p class="mb-1 small">© <?= date('Y') ?> <strong>PT Master Grafika Indonesia</strong>. PRINTMASTER Percetakan Terintegrasi.</p>
            <p class="small text-muted mb-0">Layanan cetak profesional banner, brosur, kartu nama, packaging, dan merchandise.</p>
        </div>
    </footer>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js').catch(err => console.log('SW fail:', err));
            });
        }
    </script>
</body>
</html>
