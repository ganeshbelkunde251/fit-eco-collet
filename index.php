<?php
// index.php - EcoCollect Landing Page
$pageTitle = "EcoCollect — Dispose Responsibly. Collect Smartly.";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch waste categories from database if available
$categories = [];
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT * FROM waste_categories ORDER BY id ASC");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback default categories if DB connection fails before seeding
    $categories = [
        ['name' => 'Dry Waste', 'description' => 'Recyclable packaging, clean cardboard, and dry textiles.', 'icon' => 'bi-box-seam'],
        ['name' => 'Wet Waste', 'description' => 'Organic kitchen waste, food remnants, and compostables.', 'icon' => 'bi-trash'],
        ['name' => 'Plastic', 'description' => 'Bottles, containers, synthetic packaging, and clean single-use plastics.', 'icon' => 'bi-cup-straw'],
        ['name' => 'Paper', 'description' => 'Newspapers, office papers, books, magazines, and cartons.', 'icon' => 'bi-file-earmark-text'],
        ['name' => 'Glass', 'description' => 'Clear & colored glass bottles, jars, and glass containers.', 'icon' => 'bi-gem'],
        ['name' => 'Metal', 'description' => 'Aluminum cans, tin foil, metal scrap, wire cables, and hardware.', 'icon' => 'bi-hammer'],
        ['name' => 'E-Waste', 'description' => 'Obsolete electronics, phones, chargers, batteries, and appliances.', 'icon' => 'bi-cpu'],
        ['name' => 'Hazardous Waste', 'description' => 'Chemical containers, paint tins, batteries, and medical items.', 'icon' => 'bi-exclamation-triangle'],
        ['name' => 'Other', 'description' => 'Bulky furniture, mattresses, and non-standard household items.', 'icon' => 'bi-grid']
    ];
}
?>

<!-- Hero Section -->
<section id="home" class="hero-section text-dark">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-pill mb-3">
                    <i class="bi bi-shield-check"></i> Sustainable Community Platform
                </div>
                <h1 class="display-4 fw-extrabold mb-3 lh-sm text-dark">
                    Dispose Responsibly.<br>
                    <span class="text-success">Collect Smartly.</span>
                </h1>
                <p class="lead text-secondary mb-4 pe-lg-4 fs-5">
                    Request waste collection from your location, schedule a convenient pickup time, and track your request from submission to completion with full transparency.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= base_url('user/create-request.php') ?>" class="btn btn-success btn-lg px-4 py-3 rounded-pill fw-bold shadow">
                            <i class="bi bi-plus-circle-fill me-2"></i> Request Pickup
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('signup.php') ?>" class="btn btn-success btn-lg px-4 py-3 rounded-pill fw-bold shadow">
                            <i class="bi bi-plus-circle-fill me-2"></i> Request Pickup
                        </a>
                    <?php endif; ?>
                    <a href="#how-it-works" class="btn btn-outline-dark btn-lg px-4 py-3 rounded-pill fw-semibold">
                        <i class="bi bi-play-circle me-1"></i> How It Works
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 mt-5 pt-3 border-top">
                    <div>
                        <h4 class="fw-bold mb-0 text-success">100%</h4>
                        <small class="text-muted">Accountable Pickups</small>
                    </div>
                    <div class="border-start ps-4">
                        <h4 class="fw-bold mb-0 text-dark">Real-Time</h4>
                        <small class="text-muted">Status Tracking</small>
                    </div>
                    <div class="border-start ps-4">
                        <h4 class="fw-bold mb-0 text-success">Gemini AI</h4>
                        <small class="text-muted">Waste Assistant</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-lg border relative position-relative">
                    <div class="position-absolute top-0 end-0 m-3 badge bg-success-subtle text-success fw-bold px-3 py-2 rounded-pill">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Quick Request MVP
                    </div>
                    <h4 class="fw-bold mb-3 text-dark">Schedule Waste Pickup</h4>
                    <p class="text-muted small mb-4">Easily categorize household or electronic waste for proper recycling and disposal.</p>
                    
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-box-seam fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">1. Select Waste Type</h6>
                                <small class="text-muted">Dry, E-Waste, Plastic, Hazardous</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-geo-alt fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">2. Provide Address</h6>
                                <small class="text-muted">Set pickup location & date</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">3. Live Status Tracking</h6>
                                <small class="text-muted">Assigned -> Picked Up -> Completed</small>
                            </div>
                        </div>
                    </div>

                    <a href="<?= is_logged_in() ? base_url('user/create-request.php') : base_url('signup.php') ?>" class="btn btn-success w-100 py-3 rounded-3 fw-bold">
                        Start Collection Request <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Waste Categories Section -->
<section id="categories" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold mb-2">Categorized Disposal</span>
            <h2 class="fw-bold text-dark display-6">Supported Waste Categories</h2>
            <p class="text-muted">EcoCollect handles a wide variety of waste streams safely and in compliance with environmental standards.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="category-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success-subtle text-success rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="bi <?= htmlspecialchars($cat['icon']) ?> fs-3"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($cat['name']) ?></h5>
                            </div>
                        </div>
                        <p class="text-muted small flex-grow-1 mb-3">
                            <?= htmlspecialchars($cat['description']) ?>
                        </p>
                        <a href="<?= is_logged_in() ? base_url('user/create-request.php?category=' . urlencode($cat['name'])) : base_url('login.php') ?>" class="text-success fw-semibold text-decoration-none small align-self-start">
                            Request Collection <i class="bi bi-chevron-right fs-8"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-5" style="background-color: var(--ec-bg);">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold mb-2">Step-by-Step</span>
            <h2 class="fw-bold text-dark display-6">How EcoCollect Works</h2>
            <p class="text-muted">A seamless 4-step digital workflow connecting residents with dedicated waste management collectors.</p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-lg-3 col-md-6">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                    <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                        1
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Select Waste</h5>
                    <p class="text-muted small mb-0">Choose your waste category manually or use our built-in <strong>Gemini AI Assistant</strong> for automated waste identification.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                    <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                        2
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Schedule Pickup</h5>
                    <p class="text-muted small mb-0">Specify house/building address, pin code, preferred pickup date, and convenient time window.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                    <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                        3
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Collection</h5>
                    <p class="text-muted small mb-0">Our collection team receives the assignment, reviews location notes, and arrives on schedule for safe pickup.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border">
                    <div class="bg-success text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 60px; height: 60px;">
                        4
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Track Status</h5>
                    <p class="text-muted small mb-0">Monitor request progression live via your dashboard timeline: Pending &rarr; Picked Up &rarr; Completed.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why EcoCollect Section -->
<section id="why-us" class="py-5 bg-white">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold mb-2">Why EcoCollect</span>
                <h2 class="fw-bold text-dark display-6 mb-4">Empowering Clean Communities through Technology</h2>
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex gap-3">
                        <div class="bg-success-subtle text-success rounded-3 p-3 h-auto d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-sliders fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Easy Request Management</h5>
                            <p class="text-muted small mb-0">Submit waste pickup details in less than 60 seconds with clear forms and instant unique request tracking IDs.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="bg-success-subtle text-success rounded-3 p-3 h-auto d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Scheduled Pickup</h5>
                            <p class="text-muted small mb-0">No more waiting endlessly for uncoordinated waste trucks. Choose pickup dates that fit your daily schedule.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="bg-success-subtle text-success rounded-3 p-3 h-auto d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-eye fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Transparent Tracking</h5>
                            <p class="text-muted small mb-0">Real-time status updates guarantee that submitted waste requests are processed, collected, and properly disposed of.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="bg-success-subtle text-success rounded-3 p-3 h-auto d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                            <i class="bi bi-tree fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Responsible Disposal</h5>
                            <p class="text-muted small mb-0">Harmful e-waste and chemical materials are routed directly to certified recycling partners.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="p-4 p-md-5 bg-dark text-white rounded-4 shadow-xl">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-robot text-success fs-2"></i>
                        <h4 class="fw-bold mb-0 text-white">Gemini AI Integration</h4>
                    </div>
                    <p class="text-light-emphasis mb-4">
                        Not sure how to dispose of an old laptop, chemical container, or mixed items? Ask our integrated Gemini AI assistant to classify your items instantly and provide handling guidance.
                    </p>
                    <div class="bg-secondary bg-opacity-25 p-3 rounded-3 mb-4 border border-secondary">
                        <small class="text-success fw-bold d-block mb-1">PROMPT EXAMPLE:</small>
                        <p class="small text-white mb-0 italic">"Old mobile phones, rechargeable batteries, and broken headphones"</p>
                        <hr class="my-2 border-secondary">
                        <small class="text-info fw-bold d-block mb-1">AI RESULT:</small>
                        <p class="small text-light-emphasis mb-0">Category: <strong>E-Waste</strong> | Action: Schedule pickup, do not mix with food waste.</p>
                    </div>
                    <a href="<?= is_logged_in() ? base_url('user/ai-assistant.php') : base_url('login.php') ?>" class="btn btn-success w-100 py-3 rounded-3 fw-bold">
                        Try AI Waste Assistant <i class="bi bi-magic ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call To Action Section -->
<section class="py-5 bg-success text-white text-center">
    <div class="container py-4">
        <h2 class="display-6 fw-bold mb-3">Ready to schedule your waste pickup?</h2>
        <p class="lead mb-4 opacity-90">Join thousands of households taking action for cleaner, smarter cities.</p>
        <a href="<?= is_logged_in() ? base_url('user/create-request.php') : base_url('signup.php') ?>" class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold text-success shadow">
            Request Pickup Now <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
