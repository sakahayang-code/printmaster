<?php
namespace App\Modules\WhatsApp;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\WhatsAppService;

class WhatsAppController extends Controller {

    public function index() {
        Auth::requirePermission('whatsapp.manage');

        $templates = Database::fetchAll("SELECT * FROM notification_templates WHERE channel = 'whatsapp' ORDER BY id ASC");

        $logs = Database::fetchAll(
            "SELECT nl.*, b.name as branch_name 
             FROM notification_logs nl 
             LEFT JOIN branches b ON nl.branch_id = b.id 
             ORDER BY nl.id DESC LIMIT 50"
        );

        $this->view('whatsapp/index', [
            'title'     => 'Notifikasi & WhatsApp Gateway Simulator',
            'templates' => $templates,
            'logs'      => $logs
        ]);
    }

    public function sendTest() {
        Auth::requirePermission('whatsapp.manage');

        $phone = Request::input('phone');
        $eventCode = Request::input('event_code');
        $customMessage = Request::input('custom_message');

        if (empty($phone)) {
            Session::flash('error', 'Nomor WhatsApp tujuan wajib diisi.');
            Response::redirect('/whatsapp');
        }

        if ($eventCode === 'custom') {
            Database::insert('notification_logs', [
                'company_id'      => 1,
                'branch_id'       => Auth::activeBranchId() ?: 1,
                'recipient'       => $phone,
                'channel'         => 'whatsapp',
                'subject'         => 'Pesan Kustom WhatsApp',
                'message_content' => $customMessage,
                'status'          => 'sent',
                'response_log'    => json_encode(['provider' => 'Simulator', 'status' => 'success', 'time' => date('Y-m-d H:i:s')]),
                'sent_at'         => date('Y-m-d H:i:s')
            ]);
            Session::flash('success', "Pesan WhatsApp simulasi berhasil dikirim ke {$phone}.");
        } else {
            $variables = [
                'customer_name'  => 'Pelanggan Uji Coba',
                'order_number'   => 'ORD-' . date('Ymd') . '-001',
                'total_amount'   => '150.000',
                'payment_amount' => '150.000',
                'branch_name'    => 'Cabang Percetakan',
                'payment_ref'    => 'PAY-TEST-001'
            ];

            WhatsAppService::send($phone, $eventCode, $variables, Auth::activeBranchId());
            Session::flash('success', "Simulasi notifikasi WhatsApp ({$eventCode}) berhasil diproses dan dikirim ke {$phone}.");
        }

        Response::redirect('/whatsapp');
    }

    public function updateTemplate() {
        Auth::requirePermission('whatsapp.manage');

        $templateId = (int) Request::input('template_id');
        $templateBody = Request::input('message_template');

        Database::update('notification_templates', [
            'message_template' => $templateBody
        ], 'id = :id', ['id' => $templateId]);

        Session::flash('success', 'Template pesan WhatsApp berhasil diperbarui.');
        Response::redirect('/whatsapp');
    }
}
