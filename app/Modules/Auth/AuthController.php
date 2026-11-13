<?php
namespace App\Modules\Auth;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;

class AuthController extends Controller {
    public function showLogin(): void {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->render('auth/login', ['title' => 'Login Masuk'], 'auth');
    }

    public function login(): void {
        $this->validateCSRF();
        $username = trim(Request::post('username', ''));
        $password = Request::post('password', '');

        if (empty($username) || empty($password)) {
            $this->flash('error', 'Username dan password wajib diisi.');
            $this->back();
        }

        if (Auth::login($username, $password)) {
            $this->flash('success', 'Selamat datang kembali, ' . Auth::user()['name'] . '!');
            $this->redirect('/dashboard');
        } else {
            $this->flash('error', 'Username atau password tidak sesuai, atau akun Anda nonaktif.');
            $this->back();
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->flash('info', 'Anda telah berhasil keluar.');
        $this->redirect('/login');
    }

    public function switchBranch(): void {
        $this->requireAuth();
        $this->validateCSRF();
        $branchId = (int) Request::post('branch_id');

        if (Auth::switchBranch($branchId)) {
            $branch = Auth::activeBranch();
            $this->flash('success', 'Berhasil beralih ke ' . $branch['name'] . '.');
        } else {
            $this->flash('error', 'Anda tidak memiliki hak akses untuk cabang tersebut.');
        }
        $this->back();
    }
}
