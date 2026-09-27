<?php
// user/dashboard.php - User Dashboard Overview
$pageTitle = "User Dashboard — EcoCollect";
$extraJs = 'dashboard.js';
require_once __DIR__ . '/../config/auth.php';

require_login();
$user = current_user();
$userId = $user['id'];

$totalRequests = 0;
$pendingRequests = 0;
$scheduledRequests = 0;
$completedRequests = 0;
$cancelledRequests = 0;
$recentRequests = [];

$userCatLabels = [];
$userCatCounts = [];

try {
    $pdo = get_db_connection();

    // 1. Total Requests Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE user_id = :uid");
    $stmt->execute([':uid' => $userId]);
    $totalRequests = $stmt->fetchColumn();

    // 2. Pending Requests Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE user_id = :uid AND status = 'Pending'");
    $stmt->execute([':uid' => $userId]);
    $pendingRequests = $stmt->fetchColumn();

    // 3. Scheduled / In Progress Requests Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE user_id = :uid AND status IN ('Assigned', 'Scheduled', 'Picked Up')");
    $stmt->execute([':uid' => $userId]);
    $scheduledRequests = $stmt->fetchColumn();

    // 4. Completed Requests Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE user_id = :uid AND status = 'Completed'");
    $stmt->execute([':uid' => $userId]);
    $completedRequests = $stmt->fetchColumn();

    // 5. Cancelled Requests Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pickup_requests WHERE user_id = :uid AND status = 'Cancelled'");
    $stmt->execute([':uid' => $userId]);
    $cancelledRequests = $stmt->fetchColumn();

    // 6. Recent 5 Requests
    $stmt = $pdo->prepare("SELECT * FROM pickup_requests WHERE user_id = :uid ORDER BY id DESC LIMIT 5");
    $stmt->execute([':uid' => $userId]);
    $recentRequests = $stmt->fetchAll();

    // 7. Category Breakdown for Chart.js
    $stmt = $pdo->prepare("SELECT waste_category, COUNT(*) as count FROM pickup_requests WHERE user_id = :uid GROUP BY waste_category ORDER BY count DESC");
    $stmt->execute([':uid' => $userId]);
    $catRows = $stmt->fetchAll();
    foreach ($catRows as $row) {
        $userCatLabels[] = $row['waste_category'];
        $userCatCounts[] = (int)$row['count'];
    }

} catch (Exception $e) {
    error_log("Dashboard Data Error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/user-sidebar.php'; ?>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navbar Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Welcome back, <?= htmlspecialchars($user['name']) ?>! 👋</h3>
                <p class="text-muted small mb-0">Manage your waste collection requests and track pickup status in real-time.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="create-request.php" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> Request Waste Pickup
                </a>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <!-- Statistics Cards Grid (5 Stat Cards) -->
        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Total Pickups</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $totalRequests ?></h3>
                        </div>
                        <div class="bg-success-subtle text-success p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-inbox-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Pending</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1"><?= $pendingRequests ?></h3>
                        </div>
                        <div class="bg-warning-subtle text-warning p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Scheduled / In Progress</span>
                            <h3 class="fw-bold text-info mb-0 mt-1"><?= $scheduledRequests ?></h3>
                        </div>
                        <div class="bg-info-subtle text-info p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-truck fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Completed Pickups</span>
                            <h3 class="fw-bold text-success mb-0 mt-1"><?= $completedRequests ?></h3>
                        </div>
                        <div class="bg-success-subtle text-success p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-6 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Cancelled</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1"><?= $cancelledRequests ?></h3>
                        </div>
                        <div class="bg-danger-subtle text-danger p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Camera Waste Scanner Section -->
        <div id="ai-camera-scanner" class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 pb-3 border-bottom gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-camera-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">AI Waste Scanner</h5>
                        <small class="text-muted">Identify your waste instantly using AI camera scanning.</small>
                    </div>
                </div>
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold border border-success-subtle align-self-start align-self-md-center">
                    <i class="bi bi-eye-fill me-1"></i> Gemini Vision AI
                </span>
            </div>

            <!-- Scanner Error Alert -->
            <div id="scanner-error-alert" class="alert alert-warning rounded-3 d-none mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <span id="scanner-error-message">Error message</span>
            </div>

            <!-- Initial Action & Upload Area -->
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border text-center">
                        <i class="bi bi-camera-reels text-success fs-1 mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">Instant Visual Waste Scanner</h6>
                        <p class="text-muted small mb-3">Scan any product, electronic device, or household waste item using your camera.</p>
                        
                        <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                            <button type="button" id="btn-start-camera" class="btn btn-success fw-bold px-4 py-3 rounded-pill shadow-sm">
                                <i class="bi bi-camera-fill me-2"></i> Open Camera
                            </button>
                        </div>

                        <!-- Camera Stream & Controls Container -->
                        <div id="scanner-camera-section" class="d-none">
                            <div class="position-relative overflow-hidden rounded-4 bg-dark mb-3 border border-2 border-success" style="max-height: 380px;">
                                <video id="scanner-video" autoplay playsinline class="w-100 h-100 object-fit-cover"></video>
                                <canvas id="scanner-canvas" class="d-none"></canvas>
                                <div class="position-absolute top-50 start-50 translate-middle text-white opacity-50 pointer-events-none">
                                    <i class="bi bi-bounding-box-circles display-1"></i>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" id="btn-capture-scan" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-camera me-1"></i> Scan Waste
                                </button>
                                <button type="button" id="btn-stop-camera" class="btn btn-outline-secondary fw-semibold px-3 py-2 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> Stop Camera
                                </button>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <label for="scanner-file-input" class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-upload me-1"></i> Upload Image (JPG, JPEG, PNG)</label>
                            <input type="file" id="scanner-file-input" accept="image/jpeg,image/png,image/jpg" class="form-control form-control-sm bg-white">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <!-- Loading State -->
                    <div id="scanner-loading-state" class="text-center py-5 d-none">
                        <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Analyzing...</span>
                        </div>
                        <h6 class="fw-bold text-dark">Analyzing Waste Image with Gemini Vision AI...</h6>
                        <p class="text-muted small mb-0">Identifying object type, waste category, recyclability, and safety rules.</p>
                    </div>

                    <!-- Default Placeholder -->
                    <div id="scanner-placeholder" class="p-4 bg-light rounded-4 border text-center text-muted">
                        <i class="bi bi-qr-code-scan fs-1 text-secondary mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark">No Active Scan</h6>
                        <p class="small mb-0">Click "Open Camera" or upload a photo of your waste item to generate an AI visual analysis.</p>
                    </div>

                    <!-- AI Result Card -->
                    <div id="scanner-result-card" class="p-4 bg-white rounded-4 border shadow-sm d-none">
                        <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom">
                            <div>
                                <small class="text-muted fs-8 text-uppercase fw-bold">Scanned Object</small>
                                <h4 id="scan-res-object" class="fw-bold text-dark mb-0">--</h4>
                            </div>
                            <span id="scan-res-recyclable" class="badge bg-success text-white px-3 py-1 rounded-pill">Recyclable: Yes</span>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded-3">
                                    <small class="text-muted fs-8 d-block">Category</small>
                                    <span id="scan-res-category" class="fw-bold text-success">--</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded-3">
                                    <small class="text-muted fs-8 d-block">Waste Type</small>
                                    <span id="scan-res-type" class="fw-semibold text-dark">--</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Recommended Disposal</small>
                            <p id="scan-res-disposal" class="small text-dark mb-0 bg-light p-2 rounded-3 border">--</p>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted fs-8 fw-bold text-uppercase d-block mb-1">Safety Warning</small>
                            <div class="p-2 bg-warning-subtle text-dark rounded-3 border border-warning">
                                <i class="bi bi-shield-exclamation text-warning me-1"></i>
                                <span id="scan-res-safety" class="small fw-medium">--</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small id="scan-res-confidence" class="text-muted fs-8 italic d-block">--</small>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <button type="button" id="btn-scan-again" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold px-3">
                                <i class="bi bi-arrow-repeat me-1"></i> Scan Again
                            </button>
                            <a id="scan-quick-request-btn" href="create-request.php" class="btn btn-success btn-sm rounded-pill fw-bold px-3">
                                <i class="bi bi-plus-circle me-1"></i> Schedule Pickup
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Analytics & Recent Requests Row -->
        <div class="row g-4 mb-4">
            <!-- Left: Personal Waste Category Chart -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart text-success me-2"></i> My Waste Stream Breakdown</h5>
                    <div style="height: 240px; position: relative;">
                        <canvas id="userCategoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right: Recent Pickup Requests Table -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                    <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Recent Pickup Requests</h5>
                            <small class="text-muted">Showing your latest 5 collection submissions</small>
                        </div>
                        <a href="my-requests.php" class="btn btn-outline-success btn-sm fw-semibold rounded-pill px-3">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light fs-7 text-uppercase">
                                <tr>
                                    <th class="ps-4">Request ID</th>
                                    <th>Waste Category</th>
                                    <th>Pickup Date</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentRequests)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                                <h6 class="fw-bold text-dark">No pickup requests yet</h6>
                                                <p class="small mb-3">Ready to schedule your first eco-friendly waste pickup?</p>
                                                <a href="create-request.php" class="btn btn-success btn-sm rounded-pill fw-bold px-4">
                                                    + Request Pickup
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentRequests as $req): 
                                        $statusClass = match($req['status']) {
                                            'Pending'   => 'badge-pending',
                                            'Assigned'  => 'badge-assigned',
                                            'Scheduled' => 'badge-scheduled',
                                            'Picked Up' => 'badge-pickedup',
                                            'Completed' => 'badge-completed',
                                            'Cancelled' => 'badge-cancelled',
                                            default     => 'badge-pending'
                                        };
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">
                                                <a href="request-details.php?id=<?= urlencode($req['request_id']) ?>" class="text-decoration-none text-dark hover-success">
                                                    <?= htmlspecialchars($req['request_id']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($req['waste_category']) ?></span>
                                            </td>
                                            <td>
                                                <i class="bi bi-calendar-event me-1 text-muted"></i>
                                                <?= date('M d, Y', strtotime($req['pickup_date'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge-status <?= $statusClass ?>">
                                                    <i class="bi bi-circle-fill fs-8"></i> <?= htmlspecialchars($req['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="request-details.php?id=<?= urlencode($req['request_id']) ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                                                    Details <i class="bi bi-chevron-right fs-8 ms-1"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Educational Video & Disposal Tips Row -->
        <div class="row g-4 mb-4">
            <!-- Educational Video Section -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-play-btn-fill text-danger fs-4"></i>
                        <h5 class="fw-bold text-dark mb-0">How to Dispose Waste Responsibly</h5>
                    </div>
                    <p class="text-muted small mb-3">Watch this short eco guide on segregating dry, wet, and hazardous household materials.</p>
                    
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm border bg-dark">
                        <iframe src="https://www.youtube-nocookie.com/embed/OasbYWF4_S8" title="How to Dispose Waste Responsibly" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <small class="text-muted fs-8 mt-2 d-block"><i class="bi bi-info-circle me-1"></i> Video source: Sustainable Waste Segregation Guidelines.</small>
                </div>
            </div>

            <!-- Recycling & Disposal Tips Card -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-lightbulb-fill text-warning fs-4"></i>
                        <h5 class="fw-bold text-dark mb-0">Segregation & Safety Tips</h5>
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 bg-light rounded-3 d-flex align-items-start gap-3">
                            <div class="bg-success text-white p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px;">
                                <i class="bi bi-cpu fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">E-Waste Safety</h6>
                                <small class="text-muted">Never throw rechargeable batteries or electronic gadgets into regular trash bins. Keep lithium batteries stored dry.</small>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 d-flex align-items-start gap-3">
                            <div class="bg-primary text-white p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px;">
                                <i class="bi bi-cup-straw fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Clean Plastics Before Pickup</h6>
                                <small class="text-muted">Rinse milk pouches, soda bottles, and plastic containers with water before bundling for recycling.</small>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 d-flex align-items-start gap-3">
                            <div class="bg-warning text-dark p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; min-width: 36px;">
                                <i class="bi bi-exclamation-triangle fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Hazardous Materials</h6>
                                <small class="text-muted">Store paint tins, chemicals, and medical waste separately. Clearly label bags for collector safety.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const catLabels = <?= json_encode($userCatLabels) ?>;
    const catCounts = <?= json_encode($userCatCounts) ?>;
    
    if (catLabels.length > 0) {
        initCategoryChart('userCategoryChart', catLabels, catCounts);
    } else {
        const canvas = document.getElementById('userCategoryChart');
        if (canvas) {
            canvas.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="bi bi-pie-chart fs-1 d-block mb-2 text-secondary"></i><small>Submit your first pickup to see your personal waste stream chart.</small></div>';
        }
    }
});
</script>

<script src="<?= base_url('assets/js/scanner.js') ?>"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
