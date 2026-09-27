<?php
// includes/user-sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = current_user();
?>
<div class="user-sidebar bg-white border-end h-100 p-3 d-flex flex-column">
    <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
        <div class="bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-recycle fs-4"></i>
        </div>
        <div>
            <h6 class="fw-bold text-dark mb-0">EcoCollect</h6>
            <small class="text-muted fs-8">User Portal</small>
        </div>
    </div>

    <ul class="nav nav-pills flex-column gap-1 mb-auto">
        <li class="nav-item">
            <a href="<?= base_url('user/dashboard.php') ?>" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : 'text-dark' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('user/create-request.php') ?>" class="nav-link <?= $currentPage === 'create-request.php' ? 'active' : 'text-dark' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-plus-circle-fill"></i> Create Pickup
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('user/my-requests.php') ?>" class="nav-link <?= $currentPage === 'my-requests.php' ? 'active' : 'text-dark' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-list-task"></i> My Requests
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('user/history.php') ?>" class="nav-link <?= $currentPage === 'history.php' ? 'active' : 'text-dark' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-clock-history"></i> Pickup History
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('user/ai-assistant.php') ?>" class="nav-link <?= $currentPage === 'ai-assistant.php' ? 'active' : 'text-dark' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-robot text-success"></i> AI Waste Assistant
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('user/dashboard.php#ai-camera-scanner') ?>" class="nav-link text-dark d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-camera-fill text-success"></i> AI Camera Scanner
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('user/profile.php') ?>" class="nav-link <?= $currentPage === 'profile.php' ? 'active' : 'text-dark' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-person-circle"></i> Profile
            </a>
        </li>
    </ul>

    <div class="pt-3 border-top mt-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="avatar bg-success-subtle text-success fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px;">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="text-truncate">
                    <p class="fw-semibold text-dark mb-0 small text-truncate"><?= htmlspecialchars($user['name'] ?? 'User') ?></p>
                    <small class="text-muted fs-8 text-truncate d-block"><?= htmlspecialchars($user['email'] ?? '') ?></small>
                </div>
            </div>
            <a href="<?= base_url('logout.php') ?>" class="btn btn-light btn-sm text-danger border-0 rounded-circle" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
