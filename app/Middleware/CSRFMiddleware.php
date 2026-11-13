<?php
namespace App\Middleware;

use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CSRFMiddleware {
    public function handle(): void {
        if (Request::isPost()) {
            $token = Request::input('_csrf_token');
            if (!CSRF::validate($token)) {
                if (Request::isAjax()) {
                    Response::json(['error' => 'Token CSRF tidak valid atau kedaluwarsa.'], 419);
                }
                Session::flash('error', 'Token keamanan kedaluwarsa. Silakan ulangi kembali.');
                Response::back();
            }
        }
    }
}
