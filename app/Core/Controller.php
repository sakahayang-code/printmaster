<?php
namespace App\Core;

class Controller {
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void {
        View::render($view, $data, $layout);
    }

    protected function view(string $view, array $data = [], ?string $layout = 'main'): void {
        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $status = 200): void {
        Response::json($data, $status);
    }

    protected function redirect(string $url): void {
        Response::redirect($url);
    }

    protected function back(): void {
        Response::back();
    }

    protected function flash(string $type, string $message): void {
        Session::flash($type, $message);
    }

    protected function requireAuth(): void {
        if (!Auth::check()) {
            $this->flash('warning', 'Silakan login terlebih dahulu.');
            $this->redirect('/login');
        }
    }

    protected function requirePermission(string $permissionCode): void {
        $this->requireAuth();
        if (!Auth::hasPermission($permissionCode)) {
            if (Request::isAjax()) {
                $this->json(['error' => 'Akses ditolak: Anda tidak memiliki izin untuk tindakan ini.'], 403);
            }
            $this->flash('error', 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman tersebut.');
            $this->redirect('/dashboard');
        }
    }

    protected function requireBranchAccess(int $branchId): void {
        $this->requireAuth();
        if (!Auth::canAccessBranch($branchId)) {
            if (Request::isAjax()) {
                $this->json(['error' => 'Akses ditolak: Anda tidak memiliki akses ke cabang ini.'], 403);
            }
            $this->flash('error', 'Akses ditolak: Data berada di luar cabang kewenangan Anda.');
            $this->redirect('/dashboard');
        }
    }

    protected function validateCSRF(): void {
        $token = Request::input('_csrf_token');
        if (!CSRF::validate($token)) {
            if (Request::isAjax()) {
                $this->json(['error' => 'Sesi kedaluwarsa atau token CSRF tidak valid. Silakan muat ulang halaman.'], 419);
            }
            $this->flash('error', 'Sesi kedaluwarsa atau token keamanan tidak valid.');
            $this->back();
        }
    }

    protected function validate(array $data, array $rules): array {
        $errors = [];
        foreach ($rules as $field => $ruleStr) {
            $ruleList = explode('|', $ruleStr);
            $val = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                if ($rule === 'required' && (is_null($val) || $val === '')) {
                    $errors[$field] = "Field {$field} wajib diisi.";
                } elseif ($rule === 'numeric' && !empty($val) && !is_numeric($val)) {
                    $errors[$field] = "Field {$field} harus berupa angka.";
                } elseif (strpos($rule, 'min:') === 0 && !empty($val)) {
                    $min = (int) substr($rule, 4);
                    if (is_numeric($val) && (float)$val < $min) {
                        $errors[$field] = "Field {$field} minimal bernilai {$min}.";
                    } elseif (is_string($val) && strlen($val) < $min) {
                        $errors[$field] = "Field {$field} minimal {$min} karakter.";
                    }
                }
            }
        }
        return $errors;
    }
}
