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

        if ($layout) {
            $layoutFile = dirname(__DIR__, 2) . "/resources/views/layouts/{$layout}.php";
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }

    public static function component(string $componentName, array $data = []): void {
        extract($data);
        $componentFile = dirname(__DIR__, 2) . "/resources/views/components/{$componentName}.php";
        if (file_exists($componentFile)) {
            require $componentFile;
        }
    }
}
