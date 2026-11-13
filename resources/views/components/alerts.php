<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($warning)): ?>
<div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
    <i class="bi bi-exclamation-circle-fill fs-5"></i>
    <div><?= htmlspecialchars($warning, ENT_QUOTES, 'UTF-8') ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (!empty($info)): ?>
<div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
    <i class="bi bi-info-circle-fill fs-5"></i>
    <div><?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
