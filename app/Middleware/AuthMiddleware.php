<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;
use App\Core\Response;
use App\Core\Request;

class AuthMiddleware {
    public function handle(): void {
        if (!Auth::check()) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Sesi Anda telah berakhir. Silakan login kembali.'], 401);
            }
            Session::flash('warning', 'Silakan login terlebih dahulu untuk mengakses sistem.');
            Response::redirect('/login');
        }
    }
}
