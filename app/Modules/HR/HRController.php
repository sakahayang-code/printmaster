<?php
namespace App\Modules\HR;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AccountingService;
use Exception;

class HRController extends Controller {

    // ==========================================
    // 1. EMPLOYEES (DATA KARYAWAN)
    // ==========================================
    public function employees() {
        Auth::requirePermission('hr.employees');
        $branchId = Auth::activeBranchId();

        $where = "1=1";
        $params = [];
        if ($branchId) {
            $where .= " AND e.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $search = Request::input('search');
        if ($search) {
            $where .= " AND (e.name LIKE :q OR e.employee_code LIKE :q OR e.position LIKE :q)";
            $params['q'] = "%{$search}%";
        }

        $sql = "SELECT e.*, b.name as branch_name
                FROM employees e
                JOIN branches b ON e.branch_id = b.id
                WHERE {$where}
                ORDER BY e.id ASC";
        $employees = Database::fetchAll($sql, $params);

        $branches = Database::fetchAll("SELECT * FROM branches WHERE is_active = 1 ORDER BY id ASC");

        $this->view('hr/employees', [
            'title'     => 'Karyawan & SDM',
            'employees' => $employees,
            'branches'  => $branches,
            'search'    => $search
        ]);
    }

    public function storeEmployee() {
        Auth::requirePermission('hr.employees');
        $branchId = (int) (Request::input('branch_id') ?: (Auth::activeBranchId() ?: 1));
        $name = Request::input('name');
        $phone = Request::input('phone');
        $position = Request::input('position');
        $department = Request::input('department', 'Operasional');
        $basicSalary = (float) Request::input('basic_salary', 0);
        $allowanceFixed = (float) Request::input('allowance_fixed', 0);
        $joinDate = Request::input('join_date', date('Y-m-d'));

        if (empty($name) || empty($phone) || empty($position)) {
            Session::flash('error', 'Nama, telepon, dan jabatan wajib diisi.');
            Response::redirect('/employees');
        }

        // Generate Employee Code EMP-XXX-000
        $bCode = Database::fetchColumn("SELECT code FROM branches WHERE id = :id", ['id' => $branchId]) ?: 'PM';
        $count = (int) Database::fetchColumn("SELECT COUNT(*) FROM employees WHERE branch_id = :bid", ['bid' => $branchId]);
        $empCode = "EMP-{$bCode}-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        Database::insert('employees', [
            'company_id'      => 1,
            'branch_id'       => $branchId,
            'employee_code'   => $empCode,
            'name'            => $name,
            'phone'           => $phone,
            'email'           => Request::input('email'),
            'address'         => Request::input('address'),
            'position'        => $position,
            'department'      => $department,
            'join_date'       => $joinDate,
            'basic_salary'    => $basicSalary,
            'allowance_fixed' => $allowanceFixed,
            'status'          => Request::input('status', 'active')
        ]);

        Session::flash('success', "Karyawan baru {$name} ({$empCode}) berhasil didaftarkan.");
        Response::redirect('/employees');
    }

    public function updateEmployee() {
        Auth::requirePermission('hr.employees');
        $id = (int) Request::input('id');
        $name = Request::input('name');
        $position = Request::input('position');
        $basicSalary = (float) Request::input('basic_salary');
        $allowanceFixed = (float) Request::input('allowance_fixed');
        $status = Request::input('status');

        Database::update('employees', [
            'name'            => $name,
            'position'        => $position,
            'department'      => Request::input('department'),
            'phone'           => Request::input('phone'),
            'basic_salary'    => $basicSalary,
            'allowance_fixed' => $allowanceFixed,
            'status'          => $status
        ], 'id = :id', ['id' => $id]);

        Session::flash('success', "Data karyawan {$name} berhasil diperbarui.");
        Response::redirect('/employees');
    }

    // ==========================================
    // 2. ATTENDANCE (ABSENSI & KEHADIRAN)
    // ==========================================
    public function attendance() {
        Auth::requirePermission('hr.employees');
        $branchId = Auth::activeBranchId();
        $date = Request::input('date', date('Y-m-d'));

        $where = "ea.date = :dt";
        $params = ['dt' => $date];
        if ($branchId) {
            $where .= " AND ea.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $sql = "SELECT ea.*, e.name as employee_name, e.employee_code, e.position, b.name as branch_name
                FROM employee_attendance ea
                JOIN employees e ON ea.employee_id = e.id
                JOIN branches b ON ea.branch_id = b.id
                WHERE {$where}
                ORDER BY ea.id DESC";
        $attendances = Database::fetchAll($sql, $params);

        // Active employees for check-in dropdown
        $empWhere = "status = 'active'";
        $empParams = [];
        if ($branchId) {
            $empWhere .= " AND branch_id = :bid";
            $empParams['bid'] = $branchId;
        }
        $employees = Database::fetchAll("SELECT id, name, employee_code, position FROM employees WHERE {$empWhere} ORDER BY name ASC", $empParams);

        $this->view('hr/attendance', [
            'title'       => 'Absensi & Kehadiran Karyawan',
            'attendances' => $attendances,
            'employees'   => $employees,
            'date'        => $date
        ]);
    }

    public function checkIn() {
        Auth::requirePermission('hr.employees');
        $employeeId = (int) Request::input('employee_id');
        $date = Request::input('date', date('Y-m-d'));
        $time = Request::input('time', date('H:i:s'));
        $status = Request::input('status', 'present');
        $notes = Request::input('notes');

        $emp = Database::fetch("SELECT branch_id, name FROM employees WHERE id = :id", ['id' => $employeeId]);
        if (!$emp) {
            Session::flash('error', 'Karyawan tidak valid.');
            Response::redirect('/attendance');
        }

        // Check if already checked in
        $existing = Database::fetch("SELECT id FROM employee_attendance WHERE employee_id = :eid AND date = :dt", [
            'eid' => $employeeId,
            'dt'  => $date
        ]);

        if ($existing) {
            Session::flash('error', "Karyawan {$emp['name']} sudah memiliki data absensi pada tanggal {$date}.");
            Response::redirect('/attendance?date=' . $date);
        }

        // Determine if late (e.g. after 08:30)
        if ($status === 'present' && $time > '08:30:00') {
            $status = 'late';
        }

        Database::insert('employee_attendance', [
            'employee_id' => $employeeId,
            'branch_id'   => $emp['branch_id'],
            'date'        => $date,
            'check_in'    => $time,
            'status'      => $status,
            'notes'       => $notes
        ]);

        Session::flash('success', "Absensi Masuk {$emp['name']} berhasil dicatat ({$time}).");
        Response::redirect('/attendance?date=' . $date);
    }

    public function checkOut() {
        Auth::requirePermission('hr.employees');
        $attendanceId = (int) Request::input('attendance_id');
        $time = Request::input('time', date('H:i:s'));

        Database::update('employee_attendance', [
            'check_out' => $time
        ], 'id = :id', ['id' => $attendanceId]);

        Session::flash('success', "Absensi Pulang berhasil dicatat ({$time}).");
        Response::redirect('/attendance');
    }

    // ==========================================
    // 3. PAYROLL (PENGGAJIAN)
    // ==========================================
    public function payroll() {
        Auth::requirePermission('hr.employees');
        $branchId = Auth::activeBranchId();

        $month = (int) Request::input('month', date('n'));
        $year = (int) Request::input('year', date('Y'));

        $where = "p.period_month = :m AND p.period_year = :y";
        $params = ['m' => $month, 'y' => $year];
        if ($branchId) {
            $where .= " AND p.branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }

        $sql = "SELECT p.*, e.name as employee_name, e.employee_code, e.position, b.name as branch_name
                FROM payrolls p
                JOIN employees e ON p.employee_id = e.id
                JOIN branches b ON p.branch_id = b.id
                WHERE {$where}
                ORDER BY e.name ASC";
        $payrolls = Database::fetchAll($sql, $params);

        $totalNet = array_sum(array_column($payrolls, 'net_salary'));

        $this->view('hr/payroll', [
            'title'    => 'Penggajian (Payroll)',
            'payrolls' => $payrolls,
            'totalNet' => $totalNet,
            'month'    => $month,
            'year'     => $year
        ]);
    }

    public function generatePayroll() {
        Auth::requirePermission('hr.employees');
        $branchId = Auth::activeBranchId();
        $month = (int) Request::input('month', date('n'));
        $year = (int) Request::input('year', date('Y'));

        $where = "status = 'active'";
        $params = [];
        if ($branchId) {
            $where .= " AND branch_id = :bid";
            $params['bid'] = $branchId;
        }
        $employees = Database::fetchAll("SELECT * FROM employees WHERE {$where}", $params);

        $count = 0;
        foreach ($employees as $emp) {
            // Check if already generated
            $existing = Database::fetch(
                "SELECT id FROM payrolls WHERE employee_id = :eid AND period_month = :m AND period_year = :y",
                ['eid' => $emp['id'], 'm' => $month, 'y' => $year]
            );
            if ($existing) continue;

            $basic = (float) $emp['basic_salary'];
            $allowance = (float) $emp['allowance_fixed'];

            // Count late and absent days
            $lateDays = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM employee_attendance 
                 WHERE employee_id = :eid AND MONTH(date) = :m AND YEAR(date) = :y AND status = 'late'",
                ['eid' => $emp['id'], 'm' => $month, 'y' => $year]
            );
            $absentDays = (int) Database::fetchColumn(
                "SELECT COUNT(*) FROM employee_attendance 
                 WHERE employee_id = :eid AND MONTH(date) = :m AND YEAR(date) = :y AND status = 'absent'",
                ['eid' => $emp['id'], 'm' => $month, 'y' => $year]
            );

            // Deductions: 25k per late, 100k per absent
            $deductions = ($lateDays * 25000) + ($absentDays * 100000);
            $netSalary = max(0, $basic + $allowance - $deductions);

            Database::insert('payrolls', [
                'company_id'     => 1,
                'branch_id'      => $emp['branch_id'],
                'period_month'   => $month,
                'period_year'    => $year,
                'employee_id'    => $emp['id'],
                'basic_salary'   => $basic,
                'allowances'     => $allowance,
                'overtime_pay'   => 0,
                'bonus'          => 0,
                'deductions'     => $deductions,
                'net_salary'     => $netSalary,
                'payment_status' => 'draft',
                'created_by'     => Auth::id()
            ]);
            $count++;
        }

        Session::flash('success', "Berhasil mengenerate payroll untuk {$count} karyawan (Periode {$month}/{$year}).");
        Response::redirect("/payroll?month={$month}&year={$year}");
    }

    public function payPayroll() {
        Auth::requirePermission('hr.employees');
        $payrollId = (int) Request::input('payroll_id');

        $p = Database::fetch("SELECT * FROM payrolls WHERE id = :id AND payment_status = 'draft'", ['id' => $payrollId]);
        if (!$p) {
            Session::flash('error', 'Payroll tidak valid atau sudah dibayarkan.');
            Response::redirect('/payroll');
        }

        Database::beginTransaction();
        try {
            Database::update('payrolls', [
                'payment_status' => 'paid',
                'paid_at'        => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $payrollId]);

            // Auto-post journal for salary expense
            // Debit: Beban Gaji (6001)
            // Credit: Kas/Bank (1001)
            $salaryAcc = Database::fetch("SELECT id FROM accounts WHERE code = '6001' LIMIT 1");
            $cashAcc = Database::fetch("SELECT id FROM accounts WHERE code = '1001' LIMIT 1");

            if ($salaryAcc && $cashAcc) {
                AccountingService::postJournal(
                    $p['branch_id'],
                    date('Y-m-d'),
                    'payroll',
                    $p['id'],
                    "Pembayaran Gaji Karyawan ID #{$p['employee_id']} Periode {$p['period_month']}/{$p['period_year']}",
                    [
                        ['account_id' => $salaryAcc['id'], 'debit' => $p['net_salary'], 'credit' => 0, 'memo' => 'Beban Gaji Karyawan'],
                        ['account_id' => $cashAcc['id'], 'debit' => 0, 'credit' => $p['net_salary'], 'memo' => 'Kas Keluar Penggajian']
                    ],
                    Auth::id()
                );
            }

            Database::commit();
            Session::flash('success', "Gaji berhasil dibayarkan dan jurnal beban gaji otomatis tercatat.");
        } catch (Exception $e) {
            Database::rollBack();
            Session::flash('error', 'Gagal memproses pembayaran gaji: ' . $e->getMessage());
        }

        Response::redirect("/payroll?month={$p['period_month']}&year={$p['period_year']}");
    }
}
