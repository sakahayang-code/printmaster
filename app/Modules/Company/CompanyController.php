<?php
namespace App\Modules\Company;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CompanyController extends Controller {

    // ==========================================
    // 1. BRANCH MANAGEMENT (MANAJEMEN CABANG)
    // ==========================================
    public function branches() {
        Auth::requirePermission('company.manage');

        $branches = Database::fetchAll("
            SELECT b.*, 
                   (SELECT COUNT(*) FROM users u WHERE u.branch_id = b.id AND u.is_active = 1) as total_users,
                   (SELECT COUNT(*) FROM machines m WHERE m.branch_id = b.id AND m.status != 'broken') as total_machines,
                   (SELECT COUNT(*) FROM warehouses w WHERE w.branch_id = b.id) as total_warehouses
            FROM branches b
            ORDER BY b.is_headquarters DESC, b.id ASC
        ");

        $this->view('company/branches', [
            'title'    => 'Manajemen Perusahaan & Multi-Cabang',
            'branches' => $branches
        ]);
    }

    public function storeBranch() {
        Auth::requirePermission('company.manage');

        $code = strtoupper(trim(Request::input('code')));
        $name = Request::input('name');
        $address = Request::input('address');
        $phone = Request::input('phone');
        $operatingHours = Request::input('operating_hours', '08:00 - 21:00');
        $monthlyTarget = (float) Request::input('monthly_target', 0);

        if (empty($code) || empty($name)) {
            Session::flash('error', 'Kode cabang dan nama cabang wajib diisi.');
            Response::redirect('/branches');
        }

        Database::insert('branches', [
            'company_id'      => 1,
            'code'            => $code,
            'name'            => $name,
            'address'         => $address,
            'phone'           => $phone,
            'operating_hours' => $operatingHours,
            'monthly_target'  => $monthlyTarget,
            'is_headquarters' => 0,
            'is_active'       => 1
        ]);

        // Also create a default warehouse for this branch
        $newBranchId = Database::lastInsertId();
        Database::insert('warehouses', [
            'branch_id' => $newBranchId,
            'code'      => 'GD-' . $code,
            'name'      => 'Gudang Utama ' . $name,
            'location'  => $address,
            'is_active' => 1
        ]);

        // Also create a default cash register
        Database::insert('cash_registers', [
            'branch_id' => $newBranchId,
            'code'      => 'POS-' . $code,
            'name'      => 'Kasir Utama ' . $name,
            'is_active' => 1
        ]);

        Session::flash('success', "Cabang baru [{$code}] {$name} beserta Gudang dan Kasir Utama berhasil didaftarkan.");
        Response::redirect('/branches');
    }

    public function toggleBranch(int $id) {
        Auth::requirePermission('company.manage');
        $branch = Database::fetch("SELECT * FROM branches WHERE id = :id", ['id' => $id]);
        if ($branch && !$branch['is_headquarters']) {
            $newStatus = $branch['is_active'] ? 0 : 1;
            Database::update('branches', ['is_active' => $newStatus], 'id = :id', ['id' => $id]);
            Session::flash('success', "Status cabang {$branch['name']} berhasil diperbarui.");
        }
        Response::redirect('/branches');
    }

    // ==========================================
    // 2. USERS & ROLES (PENGGUNA & HAK AKSES)
    // ==========================================
    public function users() {
        Auth::requirePermission('users.manage');

        $users = Database::fetchAll("
            SELECT u.*, r.display_name as role_name, b.name as branch_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN branches b ON u.branch_id = b.id
            ORDER BY u.id ASC
        ");

        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY id ASC");
        $branches = Database::fetchAll("SELECT * FROM branches WHERE is_active = 1 ORDER BY id ASC");

        $this->view('company/users', [
            'title'    => 'Pengguna, Jabatan & Hak Akses',
            'users'    => $users,
            'roles'    => $roles,
            'branches' => $branches
        ]);
    }

    public function storeUser() {
        Auth::requirePermission('users.manage');

        $name = Request::input('name');
        $username = strtolower(trim(Request::input('username')));
        $email = strtolower(trim(Request::input('email')));
        $phone = Request::input('phone');
        $password = Request::input('password', 'password123');
        $roleId = (int) Request::input('role_id');
        $branchId = (int) Request::input('branch_id') ?: null;

        if (empty($name) || empty($username) || empty($email) || empty($roleId)) {
            Session::flash('error', 'Nama, username, email, dan jabatan wajib diisi.');
            Response::redirect('/users');
        }

        // Check duplicate username or email
        $existing = Database::fetch("SELECT id FROM users WHERE username = :u OR email = :e", ['u' => $username, 'e' => $email]);
        if ($existing) {
            Session::flash('error', "Username atau email sudah digunakan.");
            Response::redirect('/users');
        }

        $userId = Database::insert('users', [
            'company_id'    => 1,
            'branch_id'     => $branchId,
            'role_id'       => $roleId,
            'name'          => $name,
            'username'      => $username,
            'email'         => $email,
            'phone'         => $phone,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'is_active'     => 1
        ]);

        // Assign branch in user_branches
        if ($branchId) {
            Database::insert('user_branches', [
                'user_id'    => $userId,
                'branch_id'  => $branchId,
                'is_primary' => 1
            ]);
        }

        Session::flash('success', "Pengguna {$name} ({$username}) berhasil didaftarkan dengan password default.");
        Response::redirect('/users');
    }

    public function toggleUser(int $id) {
        Auth::requirePermission('users.manage');
        if ($id === 1) {
            Session::flash('error', 'Super Admin utama tidak dapat dinonaktifkan.');
            Response::redirect('/users');
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);
        if ($user) {
            $newStatus = $user['is_active'] ? 0 : 1;
            Database::update('users', ['is_active' => $newStatus], 'id = :id', ['id' => $id]);
            Session::flash('success', "Status akun pengguna {$user['name']} berhasil diperbarui.");
        }
        Response::redirect('/users');
    }

    public function resetPassword(int $id) {
        Auth::requirePermission('users.manage');
        $newPass = 'password123';
        Database::update('users', [
            'password_hash' => password_hash($newPass, PASSWORD_BCRYPT)
        ], 'id = :id', ['id' => $id]);

        Session::flash('success', "Password pengguna berhasil direset menjadi '{$newPass}'.");
        Response::redirect('/users');
    }

    // ==========================================
    // 3. AUDIT TRAIL & SYSTEM BACKUP
    // ==========================================
    public function auditLogs() {
        Auth::requirePermission('audit.view');

        $action = Request::input('action');
        $where = "1=1";
        $params = [];

        if ($action) {
            $where .= " AND al.action = :act";
            $params['act'] = $action;
        }

        $logs = Database::fetchAll("
            SELECT al.*, u.name as user_name, u.username, b.name as branch_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN branches b ON al.branch_id = b.id
            WHERE {$where}
            ORDER BY al.id DESC LIMIT 100
        ", $params);

        $this->view('company/audit_logs', [
            'title' => 'Audit Trail & Rekam Jejak Sistem',
            'logs'  => $logs
        ]);
    }

    public function downloadBackup() {
        Auth::requirePermission('audit.view');

        // Simple database backup dump for shared hosting
        $tables = Database::fetchAll("SHOW TABLES");
        $tableKey = 'Tables_in_printmaster';
        
        $sqlDump = "-- PRINTMASTER DATABASE BACKUP\n-- Generated: " . date('Y-m-d H:i:s') . "\n-- Host: Shared Hosting Compatible\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tRow) {
            $table = $tRow[$tableKey];
            $createTable = Database::fetch("SHOW CREATE TABLE `{$table}`");
            $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlDump .= $createTable['Create Table'] . ";\n\n";

            $rows = Database::fetchAll("SELECT * FROM `{$table}`");
            if (!empty($rows)) {
                $sqlDump .= "INSERT INTO `{$table}` VALUES\n";
                $rowStrings = [];
                foreach ($rows as $row) {
                    $vals = array_map(function($v) {
                        return $v === null ? "NULL" : "'" . addslashes($v) . "'";
                    }, array_values($row));
                    $rowStrings[] = "(" . implode(", ", $vals) . ")";
                }
                $sqlDump .= implode(",\n", $rowStrings) . ";\n\n";
            }
        }
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="printmaster_backup_' . date('Ymd_His') . '.sql"');
        echo $sqlDump;
        exit;
    }
}
