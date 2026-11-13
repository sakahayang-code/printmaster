<?php
namespace App\Modules\Marketing;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class MarketingController extends Controller {

    public function index() {
        Auth::requirePermission('marketing.manage');

        $promotions = Database::fetchAll("SELECT * FROM promotions ORDER BY id DESC");

        $this->view('marketing/promotions', [
            'title'      => 'Voucher Promo & Diskon Pemasaran',
            'promotions' => $promotions
        ]);
    }

    public function store() {
        Auth::requirePermission('marketing.manage');

        $code = strtoupper(trim(Request::input('code')));
        $title = Request::input('title');
        $discountType = Request::input('discount_type', 'percentage');
        $discountValue = (float) Request::input('discount_value');
        $minPurchase = (float) Request::input('min_purchase', 0);
        $maxDiscount = (float) Request::input('max_discount', 0);
        $quota = (int) Request::input('quota', 100);
        $startDate = Request::input('start_date', date('Y-m-d'));
        $endDate = Request::input('end_date', date('Y-m-d', strtotime('+30 days')));

        if (empty($code) || empty($title) || $discountValue <= 0) {
            Session::flash('error', 'Kode voucher, nama promo, dan nilai diskon wajib diisi.');
            Response::redirect('/promotions');
        }

        // Check duplicate code
        $exists = Database::fetch("SELECT id FROM promotions WHERE code = :code", ['code' => $code]);
        if ($exists) {
            Session::flash('error', "Kode voucher {$code} sudah pernah dibuat. Gunakan kode lain.");
            Response::redirect('/promotions');
        }

        Database::insert('promotions', [
            'company_id'     => 1,
            'code'           => $code,
            'title'          => $title,
            'discount_type'  => $discountType,
            'discount_value' => $discountValue,
            'min_purchase'   => $minPurchase,
            'max_discount'   => $maxDiscount,
            'quota'          => $quota,
            'used_count'     => 0,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'is_active'      => 1
        ]);

        Session::flash('success', "Voucher promosi {$code} berhasil diterbitkan.");
        Response::redirect('/promotions');
    }

    public function toggle(int $id) {
        Auth::requirePermission('marketing.manage');
        $promo = Database::fetch("SELECT * FROM promotions WHERE id = :id", ['id' => $id]);
        if ($promo) {
            $newStatus = $promo['is_active'] ? 0 : 1;
            Database::update('promotions', ['is_active' => $newStatus], 'id = :id', ['id' => $id]);
            Session::flash('success', "Status voucher {$promo['code']} berhasil diubah.");
        }
        Response::redirect('/promotions');
    }
}
