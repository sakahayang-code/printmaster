<?php
namespace App\Core;

class Auth {
    private static ?array $currentUser = null;
    private static ?array $userPermissions = null;

    public static function check(): bool {
        return Session::has('user_id');
    }

    public static function id(): ?int {
        return Session::get('user_id');
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }

        if (self::$currentUser === null) {
            $userId = self::id();
            $sql = "SELECT u.*, r.name as role_name, r.display_name as role_display, 
                           b.name as default_branch_name, b.code as default_branch_code
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    LEFT JOIN branches b ON u.branch_id = b.id
                    WHERE u.id = :id AND u.is_active = 1";
            self::$currentUser = Database::fetchOne($sql, ['id' => $userId]);
            if (!self::$currentUser) {
                self::logout();
                return null;
            }
        }
        return self::$currentUser;
    }

    public static function role(): string {
        $u = self::user();
        return $u['role_name'] ?? '';
    }

    public static function isSuperAdmin(): bool {
        return self::role() === 'superadmin';
    }

    public static function isOwner(): bool {
        return self::role() === 'owner';
    }

    public static function hasRole($roles): bool {
        $userRole = self::role();
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        return $userRole === $roles;
    }

    public static function hasPermission(string $permissionCode): bool {
        if (self::isSuperAdmin()) {
            return true;
        }

        if (self::$userPermissions === null) {
            $user = self::user();
            if (!$user) {
                return false;
            }
            $sql = "SELECT p.code 
                    FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    WHERE rp.role_id = :role_id";
            $perms = Database::fetchAll($sql, ['role_id' => $user['role_id']]);
            self::$userPermissions = array_column($perms, 'code');
        }

        return in_array($permissionCode, self::$userPermissions);
    }

    public static function requireAuth(): void {
        if (!self::check()) {
            Session::flash('warning', 'Silakan login terlebih dahulu.');
            Response::redirect('/login');
        }
    }

    public static function requirePermission(string $permissionCode): void {
        self::requireAuth();
        if (!self::hasPermission($permissionCode)) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Akses ditolak: Anda tidak memiliki izin untuk tindakan ini.'], 403);
            }
            Session::flash('error', 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman tersebut.');
            Response::redirect('/dashboard');
        }
    }

    public static function requireBranchAccess(int $branchId): void {
        self::requireAuth();
        if (!self::canAccessBranch($branchId)) {
            if (Request::isAjax()) {
                Response::json(['error' => 'Akses ditolak: Anda tidak memiliki akses ke cabang ini.'], 403);
            }
            Session::flash('error', 'Akses ditolak: Data berada di luar cabang kewenangan Anda.');
            Response::redirect('/dashboard');
        }
    }

    public static function userBranches(): array {
        $userId = self::id();
        if (!$userId) {
            return [];
        }

        if (self::isSuperAdmin() || self::isOwner()) {
            // Super Admin and Owner have access to all active branches
            return Database::fetchAll("SELECT * FROM branches WHERE is_active = 1 ORDER BY is_headquarters DESC, name ASC");
        }

        $sql = "SELECT b.* 
                FROM branches b
                JOIN user_branches ub ON b.id = ub.branch_id
                WHERE ub.user_id = :user_id AND b.is_active = 1
                ORDER BY b.name ASC";
        return Database::fetchAll($sql, ['user_id' => $userId]);
    }

    public static function canAccessBranch(int $branchId): bool {
        if (self::isSuperAdmin() || self::isOwner()) {
            return true;
        }
        $branches = self::userBranches();
        $allowedIds = array_column($branches, 'id');
        return in_array($branchId, $allowedIds);
    }

    public static function activeBranchId(): int {
        $activeId = Session::get('active_branch_id');
        if ($activeId && self::canAccessBranch($activeId)) {
            return (int) $activeId;
        }

        $user = self::user();
        if ($user && !empty($user['branch_id']) && self::canAccessBranch($user['branch_id'])) {
            Session::set('active_branch_id', $user['branch_id']);
            return (int) $user['branch_id'];
        }

        $branches = self::userBranches();
        if (!empty($branches)) {
            $firstId = (int) $branches[0]['id'];
            Session::set('active_branch_id', $firstId);
            return $firstId;
        }

        return 1; // Default fallback to headquarters
    }

    public static function activeBranch(): array {
        $branchId = self::activeBranchId();
        $branch = Database::fetchOne("SELECT * FROM branches WHERE id = :id", ['id' => $branchId]);
        return $branch ?: ['id' => 1, 'name' => 'Cabang Utama', 'code' => 'JKT01'];
    }

    public static function switchBranch(int $branchId): bool {
        if (!self::canAccessBranch($branchId)) {
            return false;
        }
        Session::set('active_branch_id', $branchId);
        
        // Log branch switch
        Database::insert('audit_logs', [
            'company_id' => 1,
            'branch_id'  => $branchId,
            'user_id'    => self::id(),
            'action'     => 'switch_branch',
            'entity'     => 'branches',
            'entity_id'  => $branchId,
            'new_values' => json_encode(['branch_id' => $branchId]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        return true;
    }

    public static function login(string $username, string $password): bool {
        $sql = "SELECT * FROM users WHERE (username = :u1 OR email = :u2) AND is_active = 1";
        $user = Database::fetchOne($sql, ['u1' => $username, 'u2' => $username]);

        if ($user && password_verify($password, $user['password_hash'])) {
            Session::regenerate();
            Session::set('user_id', $user['id']);
            Session::set('company_id', $user['company_id']);
            
            // Set initial active branch
            if (!empty($user['branch_id'])) {
                Session::set('active_branch_id', $user['branch_id']);
            } else {
                $userBranches = Database::fetchAll("SELECT branch_id FROM user_branches WHERE user_id = :uid", ['uid' => $user['id']]);
                if (!empty($userBranches)) {
                    Session::set('active_branch_id', $userBranches[0]['branch_id']);
                } else {
                    Session::set('active_branch_id', 1);
                }
            }

            Database::update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);
            
            // Record login attempt
            Database::insert('login_attempts', [
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'username'   => $username,
                'is_success' => 1
            ]);

            // Audit log
            Database::insert('audit_logs', [
                'company_id' => $user['company_id'],
                'branch_id'  => Session::get('active_branch_id'),
                'user_id'    => $user['id'],
                'action'     => 'login',
                'entity'     => 'users',
                'entity_id'  => $user['id'],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            return true;
        }

        // Record failed attempt
        Database::insert('login_attempts', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'username'   => $username,
            'is_success' => 0
        ]);

        return false;
    }

    public static function logout(): void {
        $userId = self::id();
        if ($userId) {
            Database::insert('audit_logs', [
                'company_id' => Session::get('company_id') ?: 1,
                'branch_id'  => Session::get('active_branch_id') ?: 1,
                'user_id'    => $userId,
                'action'     => 'logout',
                'entity'     => 'users',
                'entity_id'  => $userId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
        self::$currentUser = null;
        self::$userPermissions = null;
        Session::destroy();
    }
}
