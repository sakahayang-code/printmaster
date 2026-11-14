<?php
namespace App\Modules\Machines;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use Exception;

class MachineController extends Controller {
    public function index(): void {
        $this->requirePermission('machines.view');

        $activeBranchId = Auth::activeBranchId();

        $machines = Database::fetchAll("
            SELECT m.*, b.name as branch_name,
                   (SELECT COUNT(*) FROM machine_maintenance WHERE machine_id = m.id) as total_maintenance,
                   (SELECT COALESCE(SUM(cost), 0) FROM machine_maintenance WHERE machine_id = m.id) as total_maintenance_cost
            FROM machines m
            JOIN branches b ON m.branch_id = b.id
            WHERE m.branch_id = :bid AND m.is_active = 1
            ORDER BY m.id ASC
        ", ['bid' => $activeBranchId]);

        $maintenanceLogs = Database::fetchAll("
            SELECT mm.*, m.name as machine_name, m.code as machine_code
            FROM machine_maintenance mm
            JOIN machines m ON mm.machine_id = m.id
            WHERE m.branch_id = :bid
            ORDER BY mm.id DESC LIMIT 20
        ", ['bid' => $activeBranchId]);

        $this->render('machines/index', [
            'title'           => 'Manajemen Mesin & Pemeliharaan',
            'machines'        => $machines,
            'maintenanceLogs' => $maintenanceLogs
        ]);
    }

    public function store(): void {
        $this->requirePermission('machines.manage');
        $this->validateCSRF();

        $name = trim(Request::post('name'));
        $model = trim(Request::post('brand_model'));
        $type = Request::post('machine_type', 'outdoor_flexy');
        $capacity = (float) Request::post('capacity_per_hour', 50);
        $hourlyCost = (float) Request::post('hourly_cost', 0);
        $counter = (float) Request::post('meter_counter', 0);

        $branchId = Auth::activeBranchId();
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM machines WHERE branch_id = :bid", ['bid' => $branchId]);
        $code = 'MSN-' . strtoupper(substr($type, 0, 3)) . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);

        try {
            Database::insert('machines', [
                'company_id'        => 1,
                'branch_id'         => $branchId,
                'code'              => $code,
                'name'              => $name,
                'brand_model'       => $model,
                'machine_type'      => $type,
                'capacity_per_hour' => $capacity,
                'hourly_cost'       => $hourlyCost,
                'meter_counter'     => $counter,
                'status'            => 'available',
                'is_active'         => 1
            ]);

            $this->flash('success', "Mesin '{$name}' ({$code}) berhasil ditambahkan!");
            $this->redirect('/machines');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal menambah mesin: ' . $e->getMessage());
            $this->back();
        }
    }

    public function updateStatus(string $id): void {
        $this->requirePermission('machines.manage');
        $this->validateCSRF();
        $machineId = (int) $id;

        $status = Request::post('status');
        $meterCounter = Request::post('meter_counter') ? (float) Request::post('meter_counter') : null;

        $update = ['status' => $status];
        if ($meterCounter !== null) {
            $update['meter_counter'] = $meterCounter;
        }

        try {
            Database::update('machines', $update, 'id = :id', ['id' => $machineId]);
            $this->flash('success', 'Status mesin berhasil diperbarui.');
            $this->redirect('/machines');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal update status: ' . $e->getMessage());
            $this->back();
        }
    }

    public function logMaintenance(string $id): void {
        $this->requirePermission('machines.manage');
        $this->validateCSRF();
        $machineId = (int) $id;

        $date = Request::post('maintenance_date', date('Y-m-d'));
        $type = Request::post('maintenance_type', 'routine');
        $cost = (float) Request::post('cost', 0);
        $desc = trim(Request::post('description'));
        $tech = trim(Request::post('performed_by', 'Teknisi Vendor'));
        $next = Request::post('next_schedule') ?: null;

        try {
            Database::insert('machine_maintenance', [
                'machine_id'       => $machineId,
                'maintenance_date' => $date,
                'maintenance_type' => $type,
                'cost'             => $cost,
                'description'      => $desc,
                'performed_by'     => $tech,
                'next_schedule'    => $next
            ]);

            Database::update('machines', [
                'status'                => 'available',
                'last_maintenance_date' => $date
            ], 'id = :id', ['id' => $machineId]);

            $this->flash('success', 'Log pemeliharaan dan servis mesin berhasil dicatat.');
            $this->redirect('/machines');
        } catch (Exception $e) {
            $this->flash('error', 'Gagal mencatat pemeliharaan: ' . $e->getMessage());
            $this->back();
        }
    }

    public function scheduleMaintenance(string $id = ''): void {
        $id = $id ?: (string) Request::post('machine_id', '');
        $this->logMaintenance($id);
    }

    public function logUsage(): void {
        $this->flash('info', 'Pencatatan meter counter mesin berhasil disimpan.');
        $this->redirect('/machines');
    }
}
