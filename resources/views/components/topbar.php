<?php
use App\Core\Auth;
use App\Core\CSRF;
?>
<header class="topbar">
    <div class="d-flex align-items-center gap-2 gap-sm-3">
        <button class="btn btn-light border btn-sm d-lg-none px-2 py-1 shadow-xs" id="sidebarToggle" type="button" aria-label="Toggle Sidebar">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Mobile Brand Logo -->
        <a href="/dashboard" class="d-flex d-lg-none align-items-center gap-2 text-decoration-none text-dark">
            <div class="sidebar-brand-icon" style="width: 32px; height: 32px; font-size: 1.1rem; border-radius: 8px;">
                <i class="bi bi-printer-fill"></i>
            </div>
            <span class="fw-bold tracking-tight d-none d-sm-inline" style="font-size: 1rem;">PRINTMASTER</span>
        </a>

        <!-- Active Branch Dropdown / Switcher -->
        <div class="dropdown">
            <button class="btn btn-sm branch-badge-btn dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <span class="d-inline-block text-truncate"><?= htmlspecialchars($activeBranch['name'] ?? 'Pusat') ?></span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1 d-none d-sm-inline"><?= htmlspecialchars($activeBranch['code'] ?? 'JKT01') ?></span>
            </button>
            <?php if (count($userBranches) > 1 || Auth::isSuperAdmin() || Auth::isOwner()): ?>
            <ul class="dropdown-menu shadow-lg border-0 rounded-3 mt-1 py-2">
                <li class="dropdown-header text-uppercase small fw-bold text-muted px-3">Ganti Cabang Operasional</li>
                <?php foreach ($userBranches as $b): ?>
                <li>
                    <form action="/switch-branch" method="POST" class="m-0">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                        <button type="submit" class="dropdown-item px-3 py-2 d-flex justify-content-between align-items-center <?= ($b['id'] == ($activeBranch['id'] ?? 0)) ? 'active fw-bold' : '' ?>">
                            <span><i class="bi bi-building me-2 opacity-75"></i><?= htmlspecialchars($b['name']) ?></span>
                            <span class="badge bg-secondary-subtle text-secondary ms-2"><?= htmlspecialchars($b['code']) ?></span>
                        </button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Side User Menu -->
    <div class="d-flex align-items-center gap-2 gap-sm-3">
        <div class="text-end d-none d-md-block">
            <div class="fw-bold text-dark lh-sm" style="font-size: 0.9rem;"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div>
            <div class="text-muted small" style="font-size: 0.75rem;"><?= htmlspecialchars($currentUser['role_display'] ?? 'Staff') ?></div>
        </div>

        <div class="dropdown">
            <div class="user-avatar-btn" data-bs-toggle="dropdown" aria-expanded="false" role="button" title="<?= htmlspecialchars($currentUser['name'] ?? 'User') ?>">
                <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2 py-2" style="min-width: 230px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div>
                    <div class="text-muted small text-truncate"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1"><?= htmlspecialchars($currentUser['role_display'] ?? '') ?></span>
                </li>
                <li><a class="dropdown-item py-2 px-3" href="/pos"><i class="bi bi-calculator me-2 text-primary"></i>Kasir Cepat POS</a></li>
                <?php if (Auth::hasPermission('company.manage')): ?>
                <li><a class="dropdown-item py-2 px-3" href="/branches"><i class="bi bi-gear me-2 text-secondary"></i>Pengaturan Cabang</a></li>
                <?php endif; ?>
                <li><a class="dropdown-item py-2 px-3" href="/backup"><i class="bi bi-database me-2 text-warning"></i>Backup & Data</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form action="/logout" method="POST" class="m-0">
                        <?= CSRF::field() ?>
                        <button type="submit" class="dropdown-item text-danger py-2 px-3 fw-semibold">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
