<?php
namespace App\Core;

class View {
    public static function render(string $viewPath, array $data = [], ?string $layout = 'main'): void {
        extract($data);

        // Standard helpers made available in views
        $currentUser = Auth::user();
        $activeBranch = Auth::activeBranch();
        $userBranches = Auth::userBranches();
        $flashSuccess = Session::flash('success');
        $flashError   = Session::flash('error');
        $flashWarning = Session::flash('warning');
        $flashInfo    = Session::flash('info');

        $viewFile = dirname(__DIR__, 2) . "/resources/views/{$viewPath}.php";

        if (!file_exists($viewFile)) {
            die("View file not found: {$viewPath}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        ob_start();
        if ($layout) {
            $layoutFile = dirname(__DIR__, 2) . "/resources/views/layouts/{$layout}.php";
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
        $html = ob_get_clean();

        // Automatically prefix root-relative links & assets for subfolder deployments (e.g. /percetakan)
        $base = Request::basePath();
        if ($base !== '') {
            $html = self::prefixUrls($html, $base);
        }

        echo $html;
    }

    public static function prefixUrls(string $html, string $base): string {
        if (empty($base)) {
            return $html;
        }
        $trimmed = trim($base, '/');
        $escaped = preg_quote($trimmed, '#');

        // Replace href="/...", src="/...", action="/..."
        // Exclude protocol-relative //, and exclude paths already starting with $trimmed/
        $pattern = '#\b(href|src|action)=(["\'])/(?!/|' . $escaped . '(/|["\']|$))#i';
        return preg_replace($pattern, '$1=$2/' . $trimmed . '/', $html);
    }

    public static function component(string $componentName, array $data = []): void {
        extract($data);
        $componentFile = dirname(__DIR__, 2) . "/resources/views/components/{$componentName}.php";
        if (file_exists($componentFile)) {
            require $componentFile;
        }
    }
}
