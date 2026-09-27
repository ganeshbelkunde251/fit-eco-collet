<?php
// includes/footer.php
?>
<footer class="bg-dark text-white pt-5 pb-4 mt-auto">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-recycle fs-4"></i>
                    </div>
                    <span class="fs-4 fw-bold text-white">Eco<span class="text-success">Collect</span></span>
                </div>
                <p class="text-light-emphasis small pe-lg-4 mb-3">
                    Connecting communities with smart, accountable waste collection. Request pickups, track status in real-time, and manage recycling effortlessly.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="btn btn-outline-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-instagram"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold text-success mb-3 text-uppercase fs-7">Quick Links</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small">
                    <li><a href="<?= base_url('index.php#home') ?>" class="text-light-emphasis text-decoration-none hover-white">Home</a></li>
                    <li><a href="<?= base_url('index.php#how-it-works') ?>" class="text-light-emphasis text-decoration-none hover-white">How It Works</a></li>
                    <li><a href="<?= base_url('index.php#categories') ?>" class="text-light-emphasis text-decoration-none hover-white">Waste Categories</a></li>
                    <li><a href="<?= base_url('login.php') ?>" class="text-light-emphasis text-decoration-none hover-white">User Portal</a></li>
                    <li><a href="<?= base_url('seed_admin.php') ?>" class="text-light-emphasis text-decoration-none hover-white">Database Setup</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold text-success mb-3 text-uppercase fs-7">Waste Services</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small text-light-emphasis">
                    <li><i class="bi bi-check2 me-1 text-success"></i> Household Dry & Wet Waste</li>
                    <li><i class="bi bi-check2 me-1 text-success"></i> Responsible E-Waste Collection</li>
                    <li><i class="bi bi-check2 me-1 text-success"></i> Hazardous Material Disposal</li>
                    <li><i class="bi bi-check2 me-1 text-success"></i> Bulk Packaging & Metal Scrap</li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold text-success mb-3 text-uppercase fs-7">Contact Support</h6>
                <p class="text-light-emphasis small mb-2"><i class="bi bi-geo-alt-fill me-2 text-success"></i> 100 Eco Street, Green Zone, CA 90210</p>
                <p class="text-light-emphasis small mb-2"><i class="bi bi-envelope-fill me-2 text-success"></i> support@ecocollect.local</p>
                <p class="text-light-emphasis small mb-0"><i class="bi bi-telephone-fill me-2 text-success"></i> +1 (800) 555-ECOS</p>
            </div>
        </div>

        <hr class="border-secondary opacity-25">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-2 small text-light-emphasis">
            <p class="mb-2 mb-md-0">&copy; <?= date('Y') ?> EcoCollect Platform. All rights reserved. Hackathon MVP Build.</p>
            <div class="d-flex gap-3">
                <a href="#" class="text-light-emphasis text-decoration-none">Privacy Policy</a>
                <a href="#" class="text-light-emphasis text-decoration-none">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Custom JS -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>
<?php if (isset($extraJs)): ?>
    <script src="<?= base_url('assets/js/' . $extraJs) ?>"></script>
<?php endif; ?>

</body>
</html>
