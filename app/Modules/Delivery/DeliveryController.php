<?php
namespace App\Modules\Delivery;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use Exception;

class DeliveryController extends Controller {

    public function index() {
        Auth::requirePermission('delivery.view');
        $branchId = Auth::activeBranchId();

        $where = "1=1";
        $params = [];
        if ($branchId) {
            $where .= " AND do.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $status = Request::input('status');
        if ($status) {
            $where .= " AND do.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT do.*, o.order_number, c.name as customer_name, b.name as branch_name
                FROM delivery_orders do
                JOIN orders o ON do.order_id = o.id
                JOIN customers c ON o.customer_id = c.id
                JOIN branches b ON do.branch_id = b.id
                WHERE {$where}
                ORDER BY do.id DESC LIMIT 100";
        $deliveries = Database::fetchAll($sql, $params);

        // Orders that are completed or ready for delivery in active branch
        $readyOrdersSql = "SELECT o.id, o.order_number, c.name as customer_name, c.phone as customer_phone, c.address as customer_address, o.total_amount
                           FROM orders o
                           JOIN customers c ON o.customer_id = c.id
                           WHERE o.status IN ('completed', 'in_production') 
                           AND o.branch_id = :bid
                           AND o.id NOT IN (SELECT order_id FROM delivery_orders WHERE status != 'failed')
                           ORDER BY o.id DESC LIMIT 30";
        $readyOrders = Database::fetchAll($readyOrdersSql, ['bid' => $branchId ?: 1]);

        $this->view('delivery/index', [
            'title'       => 'Pengiriman & Surat Jalan (Logistik)',
            'deliveries'  => $deliveries,
            'readyOrders' => $readyOrders,
            'currentStatus' => $status
        ]);
    }

    public function store() {
        Auth::requirePermission('delivery.view');
        $branchId = Auth::activeBranchId() ?: 1;
        $orderId = (int) Request::input('order_id');
        $courierType = Request::input('courier_type', 'internal');
        $courierName = Request::input('courier_name');
        $trackingNumber = Request::input('tracking_number');
        $recipientName = Request::input('recipient_name');
        $recipientPhone = Request::input('recipient_phone');
        $deliveryAddress = Request::input('delivery_address');
        $notes = Request::input('notes');

        if (empty($orderId) || empty($courierName) || empty($recipientName) || empty($deliveryAddress)) {
            Session::flash('error', 'Semua data pengiriman wajib diisi lengkap.');
            Response::redirect('/delivery');
        }

        $ym = date('Ym');
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM delivery_orders WHERE delivery_number LIKE 'DO-{$ym}-%'");
        $deliveryNumber = "DO-{$ym}-" . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        Database::insert('delivery_orders', [
            'company_id'       => 1,
            'branch_id'        => $branchId,
            'order_id'         => $orderId,
            'delivery_number'  => $deliveryNumber,
            'delivery_type'    => 'customer_delivery',
            'courier_type'     => $courierType,
            'courier_name'     => $courierName,
            'tracking_number'  => $trackingNumber,
            'recipient_name'   => $recipientName,
            'recipient_phone'  => $recipientPhone,
            'delivery_address' => $deliveryAddress,
            'status'           => 'in_transit',
            'estimated_delivery_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'notes'            => $notes
        ]);

        Session::flash('success', "Surat Jalan & Pengiriman {$deliveryNumber} berhasil dibuat dengan status Sedang Dikirim.");
        Response::redirect('/delivery');
    }

    public function updateStatus() {
        Auth::requirePermission('delivery.view');
        $deliveryId = (int) Request::input('delivery_id');
        $status = Request::input('status');
        $notes = Request::input('notes');

        $update = [
            'status' => $status,
            'notes'  => $notes
        ];

        if ($status === 'delivered') {
            $update['actual_delivery_time'] = date('Y-m-d H:i:s');
            // Also update order status to delivered/completed
            $do = Database::fetch("SELECT order_id FROM delivery_orders WHERE id = :id", ['id' => $deliveryId]);
            if ($do) {
                Database::update('orders', ['status' => 'completed'], 'id = :id', ['id' => $do['order_id']]);
            }
        }

        Database::update('delivery_orders', $update, 'id = :id', ['id' => $deliveryId]);

        Session::flash('success', "Status pengiriman berhasil diperbarui menjadi {$status}.");
        Response::redirect('/delivery');
    }

    public function printSuratJalan(int $id) {
        Auth::requirePermission('delivery.view');
        $do = Database::fetch(
            "SELECT do.*, o.order_number, c.name as customer_name, c.company_name as customer_company, b.name as branch_name, b.address as branch_address, b.phone as branch_phone
             FROM delivery_orders do
             JOIN orders o ON do.order_id = o.id
             JOIN customers c ON o.customer_id = c.id
             JOIN branches b ON do.branch_id = b.id
             WHERE do.id = :id",
            ['id' => $id]
        );

        if (!$do) {
            Session::flash('error', 'Surat jalan tidak ditemukan.');
            Response::redirect('/delivery');
        }

        $items = Database::fetchAll(
            "SELECT oi.*, p.name as product_name 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = :oid",
            ['oid' => $do['order_id']]
        );

        $this->renderDirectView('delivery/print', [
            'title' => 'Surat Jalan - ' . $do['delivery_number'],
            'do'    => $do,
            'items' => $items
        ]);
    }
}
