<?php
namespace App\Modules\Customers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use Exception;

class CustomerController extends Controller {
    public function index(): void {
        $this->requirePermission('customers.view');

        $tier = Request::get('tier');
        $search = trim(Request::get('search', ''));

        $where = ["is_active = 1"];
        $params = [];

        if (!empty($tier)) {
            $where[] = "customer_tier = :tier";
            $params['tier'] = $tier;
        }

        if (!empty($search)) {
            $where[] = "(name LIKE :s1 OR phone LIKE :s2 OR company_name LIKE :s3 OR customer_code LIKE :s4)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
            $params['s3'] = "%{$search}%";
            $params['s4'] = "%{$search}%";
        }

        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as total_orders,
                       (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE customer_id = c.id AND status != 'cancelled') as total_spend
                FROM customers c 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY c.id DESC";

        $customers = Database::fetchAll($sql, $params);

        $this->render('customers/index', [
            'title'     => 'Pelanggan & CRM',
            'customers' => $customers,
            'tier'      => $tier,
            'search'    => $search
        ]);
    }

    public function store(): void {
        $this->requirePermission('customers.create');
        $this->validateCSRF();

        $name = trim(Request::post('name'));
        $phone = trim(Request::post('phone'));
        $email = trim(Request::post('email')) ?: null;
        $type = Request::post('type', 'individual');
        $companyName = trim(Request::post('company_name')) ?: null;
        $tier = Request::post('customer_tier', 'regular');
        $discountPct = (float) Request::post('discount_percent', 0);
        $creditLimit = (float) Request::post('credit_limit', 0);
        $address = trim(Request::post('address'));
        $notes = trim(Request::post('notes'));

        if (empty($name) || empty($phone)) {
            $this->flash('error', 'Nama dan nomor telepon pelanggan wajib diisi.');
            $this->back();
        }

        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM customers");
        $code = 'CUST-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        try {
            $id = Database::insert('customers', [
                'company_id'       => 1,
                'branch_id'        => Auth::activeBranchId(),
                'customer_code'    => $code,
                'type'             => $type,
                'name'             => $name,
                'company_name'     => $companyName,
                'email'            => $email,
                'phone'            => $phone,
                'address'          => $address,
                'customer_tier'    => $tier,
                'discount_percent' => $discountPct,
                'credit_limit'     => $creditLimit,
                'notes'            => $notes,
                'is_active'        => 1
            ]);

            $this->flash('success', "Pelanggan '{$name}' ({$code}) berhasil ditambahkan!");
            $this->redirect('/customers/' . $id);
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menambahkan pelanggan: ' . $e->getMessage());
            $this->back();
        }
    }

    public function show(string $id): void {
        $this->requirePermission('customers.view');
        $customerId = (int) $id;

        $customer = Database::fetchOne("SELECT * FROM customers WHERE id = :id", ['id' => $customerId]);
        if (!$customer) {
            $this->flash('error', 'Pelanggan tidak ditemukan.');
            $this->redirect('/customers');
        }

        $orders = Database::fetchAll("
            SELECT o.*, b.name as branch_name 
            FROM orders o
            JOIN branches b ON o.branch_id = b.id
            WHERE o.customer_id = :id
            ORDER BY o.id DESC
        ", ['id' => $customerId]);

        $this->render('customers/show', [
            'title'    => "Profil CRM: {$customer['name']}",
            'customer' => $customer,
            'orders'   => $orders
        ]);
    }

    public function update(string $id): void {
        $this->requirePermission('customers.edit');
        $this->validateCSRF();
        $customerId = (int) $id;

        $name = trim(Request::post('name'));
        $phone = trim(Request::post('phone'));
        $email = trim(Request::post('email')) ?: null;
        $type = Request::post('type', 'individual');
        $companyName = trim(Request::post('company_name')) ?: null;
        $tier = Request::post('customer_tier', 'regular');
        $discountPct = (float) Request::post('discount_percent', 0);
        $creditLimit = (float) Request::post('credit_limit', 0);
        $address = trim(Request::post('address'));
        $notes = trim(Request::post('notes'));

        try {
            Database::update('customers', [
                'name'             => $name,
                'type'             => $type,
                'company_name'     => $companyName,
                'phone'            => $phone,
                'email'            => $email,
                'customer_tier'    => $tier,
                'discount_percent' => $discountPct,
                'credit_limit'     => $creditLimit,
                'address'          => $address,
                'notes'            => $notes
            ], 'id = :id', ['id' => $customerId]);

            $this->flash('success', "Data pelanggan berhasil diperbarui!");
            $this->back();
        } catch (Exception $e) {
            $this->flash('error', 'Gagal memperbarui data: ' . $e->getMessage());
            $this->back();
        }
    }

    public function delete(string $id): void {
        $this->requirePermission('customers.delete');
        $this->validateCSRF();
        $customerId = (int) $id;

        // Check if customer has orders
        $hasOrders = Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE customer_id = :id", ['id' => $customerId]);
        if ($hasOrders > 0) {
            // Soft delete by setting is_active = 0
            Database::update('customers', ['is_active' => 0], 'id = :id', ['id' => $customerId]);
            $this->flash('success', 'Pelanggan berhasil dinonaktifkan (memiliki riwayat transaksi).');
        } else {
            Database::delete('customers', 'id = :id', ['id' => $customerId]);
            $this->flash('success', 'Pelanggan berhasil dihapus.');
        }

        $this->redirect('/customers');
    }
}
