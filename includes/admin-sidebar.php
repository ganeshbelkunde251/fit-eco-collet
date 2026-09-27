<?php
// includes/admin-sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = current_user();
?>
<div class="admin-sidebar bg-dark text-white h-100 p-3 d-flex flex-column">
    <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom border-secondary">
        <div class="bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
            <i class="bi bi-recycle fs-4"></i>
        </div>
        <div>
            <h6 class="fw-bold text-white mb-0">EcoCollect</h6>
            <span class="badge bg-danger text-uppercase fs-8">Admin Control</span>
        </div>
    </div>

    <ul class="nav nav-pills flex-column gap-1 mb-auto">
        <li class="nav-item">
            <a href="<?= base_url('admin/dashboard.php') ?>" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active bg-success' : 'text-light-emphasis' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-speedometer2"></i> Overview
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/requests.php') ?>" class="nav-link <?= ($currentPage === 'requests.php' && !isset($_GET['status'])) ? 'active bg-success' : 'text-light-emphasis' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-inbox-fill"></i> All Requests
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/requests.php?status=Pending') ?>" class="nav-link <?= (isset($_GET['status']) && $_GET['status'] === 'Pending') ? 'active bg-success' : 'text-light-emphasis' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-clock-fill text-warning"></i> Pending Requests
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/requests.php?status=Completed') ?>" class="nav-link <?= (isset($_GET['status']) && $_GET['status'] === 'Completed') ? 'active bg-success' : 'text-light-emphasis' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-check-circle-fill text-success"></i> Completed Pickups
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/statistics.php') ?>" class="nav-link <?= $currentPage === 'statistics.php' ? 'active bg-success' : 'text-light-emphasis' ?> d-flex align-items-center gap-2 rounded-3">
                <i class="bi bi-pie-chart-fill text-info"></i> Collection Analytics
            </a>
        </li>
    </ul>

    <div class="pt-3 border-top border-secondary mt-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="avatar bg-danger text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px;">
                    A
                </div>
                <div class="text-truncate">
                    <p class="fw-semibold text-white mb-0 small text-truncate"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></p>
                    <small class="text-light-emphasis fs-8 text-truncate d-block">Administrator</small>
                </div>
            </div>
            <a href="<?= base_url('logout.php') ?>" class="btn btn-outline-light btn-sm border-0 rounded-circle" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
