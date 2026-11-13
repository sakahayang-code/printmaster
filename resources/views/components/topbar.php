<?php
use App\Core\Auth;
use App\Core\CSRF;
?>
<header class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline-secondary btn-sm d-lg-none" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Active Branch Dropdown / Switcher -->
        <div class="dropdown">
            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2 fw-semibold px-3 py-2 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <span><?= htmlspecialchars($activeBranch['name'] ?? 'Pusat') ?></span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1"><?= htmlspecialchars($activeBranch['code'] ?? 'JKT01') ?></span>
            </button>
            <?php if (count($userBranches) > 1 || Auth::isSuperAdmin() || Auth::isOwner()): ?>
            <ul class="dropdown-menu shadow">
                <li class="dropdown-header text-uppercase small fw-bold">Pilih Cabang Aktif</li>
                <?php foreach ($userBranches as $b): ?>
                <li>
                    <form action="/switch-branch" method="POST" class="m-0">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                        <button type="submit" class="dropdown-item d-flex justify-content-between align-items-center <?= ($b['id'] == ($activeBranch['id'] ?? 0)) ? 'active fw-bold' : '' ?>">
                            <span><i class="bi bi-building me-2"></i><?= htmlspecialchars($b['name']) ?></span>
                            <span class="badge bg-secondary"><?= htmlspecialchars($b['code']) ?></span>
                        </button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Side User Menu -->
    <div class="d-flex align-items-center gap-3">
        <div class="text-end d-none d-sm-block">
            <div class="fw-semibold text-dark lh-sm"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div>
            <div class="text-muted small"><?= htmlspecialchars($currentUser['role_display'] ?? 'Staff') ?></div>
        </div>

        <div class="dropdown">
            <button class="btn btn-light rounded-circle p-1 border shadow-sm dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                    <span class="badge bg-primary mt-1"><?= htmlspecialchars($currentUser['role_display'] ?? '') ?></span>
                </li>
                <li><a class="dropdown-item py-2" href="/profile"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                <?php if (Auth::hasPermission('company.manage')): ?>
                <li><a class="dropdown-item py-2" href="/branches"><i class="bi bi-gear me-2"></i>Pengaturan Cabang</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="/logout" method="POST" class="m-0">
                        <?= CSRF::field() ?>
                        <button type="submit" class="dropdown-item text-danger py-2">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
