<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Login', ENT_QUOTES, 'UTF-8') ?> - PrintMaster ERP</title>
    <link rel="manifest" href="/manifest.json">
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
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 1.5rem;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .auth-header {
            background: #0f172a;
            color: #ffffff;
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
        }
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle mb-3" style="width: 54px; height: 54px;">
                <i class="bi bi-printer-fill fs-3 text-white"></i>
            </div>
            <h4 class="fw-bold mb-1">PRINTMASTER</h4>
            <p class="text-secondary small mb-0">ERP Jasa Percetakan Multi-Cabang</p>
        </div>

        <div class="p-4 p-sm-5">
            <?php \App\Core\View::component('alerts', [
                'success' => $flashSuccess,
                'error'   => $flashError,
                'warning' => $flashWarning,
                'info'    => $flashInfo
            ]); ?>

            <?= $content ?>
        </div>
    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
