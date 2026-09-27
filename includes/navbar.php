<?php
// includes/navbar.php
$currentUser = current_user();
?>
<nav class="navbar navbar-expand-lg sticky-top bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-4 text-success" href="<?= base_url('index.php') ?>">
            <div class="brand-icon bg-success-subtle text-success rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-recycle fs-4"></i>
            </div>
            <span>Eco<span class="text-dark">Collect</span></span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium" href="<?= base_url('index.php#home') ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium" href="<?= base_url('index.php#how-it-works') ?>">How It Works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium" href="<?= base_url('index.php#categories') ?>">Waste Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-medium" href="<?= base_url('index.php#why-us') ?>">Why EcoCollect</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-dark fw-semibold rounded-pill px-4">
                            <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('user/dashboard.php') ?>" class="btn btn-outline-success fw-semibold rounded-pill px-4">
                            <i class="bi bi-grid-fill me-1"></i> Dashboard
                        </a>
                        <a href="<?= base_url('user/create-request.php') ?>" class="btn btn-success fw-semibold rounded-pill px-4 shadow-sm">
                            <i class="bi bi-plus-circle-fill me-1"></i> Request Pickup
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('logout.php') ?>" class="btn btn-light text-muted border-0 rounded-circle" title="Sign Out">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('login.php') ?>" class="btn btn-outline-success fw-semibold rounded-pill px-4">
                        Sign In
                    </a>
                    <a href="<?= base_url('signup.php') ?>" class="btn btn-success fw-semibold rounded-pill px-4 shadow-sm">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
