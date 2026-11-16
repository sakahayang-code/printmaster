<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($title ?? 'Login Masuk', ENT_QUOTES, 'UTF-8') ?> - PrintMaster ERP</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0b1120">
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
        body {
            background: radial-gradient(circle at 50% 20%, #1e1b4b 0%, #0f172a 60%, #020617 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .auth-card {
            background: #ffffff;
            border-radius: var(--pm-radius-lg);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .auth-header {
            background: linear-gradient(180deg, #0b1120 0%, #0f172a 100%);
            color: #ffffff;
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }
        .auth-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--pm-primary) 0%, #7c3aed 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.45);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="bi bi-printer-fill fs-2 text-white"></i>
            </div>
            <h4 class="fw-bold mb-1 tracking-tight">PRINTMASTER ERP</h4>
            <p class="text-slate-400 small mb-0" style="color: #94a3b8; font-size: 0.82rem;">Sistem Manajemen Percetakan Multi-Cabang</p>
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
