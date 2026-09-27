<?php
// admin/dashboard.php - Admin Overview & Control Center
$pageTitle = "Admin Overview — EcoCollect Control";
$extraJs = 'dashboard.js';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$admin = current_user();

$totalRequests = 0;
$pendingRequests = 0;
$assignedRequests = 0;
$scheduledRequests = 0;
$pickedUpRequests = 0;
$completedRequests = 0;
$cancelledRequests = 0;
$recentRequests = [];

$catLabels = [];
$catCounts = [];
$statusLabels = ['Pending', 'Assigned', 'Scheduled', 'Picked Up', 'Completed', 'Cancelled'];
$statusCountsMap = array_fill_keys($statusLabels, 0);

try {
    $pdo = get_db_connection();

    // 1. Stat Counts
    $totalRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests")->fetchColumn();
    $pendingRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'Pending'")->fetchColumn();
    $assignedRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'Assigned'")->fetchColumn();
    $scheduledRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'Scheduled'")->fetchColumn();
    $pickedUpRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'Picked Up'")->fetchColumn();
    $completedRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'Completed'")->fetchColumn();
    $cancelledRequests = $pdo->query("SELECT COUNT(*) FROM pickup_requests WHERE status = 'Cancelled'")->fetchColumn();

    // 2. Recent 10 Requests with User Information
    $stmt = $pdo->query("SELECT r.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                        FROM pickup_requests r 
                        JOIN users u ON r.user_id = u.id 
                        ORDER BY r.id DESC LIMIT 10");
    $recentRequests = $stmt->fetchAll();

    // 3. Category Distribution SQL Query
    $catStmt = $pdo->query("SELECT waste_category, COUNT(*) as count FROM pickup_requests GROUP BY waste_category ORDER BY count DESC");
    $catRows = $catStmt->fetchAll();
    foreach ($catRows as $row) {
        $catLabels[] = $row['waste_category'];
        $catCounts[] = (int)$row['count'];
    }

    // 4. Status Distribution SQL Query
    $statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM pickup_requests GROUP BY status");
    $statusRows = $statusStmt->fetchAll();
    foreach ($statusRows as $row) {
        if (isset($statusCountsMap[$row['status']])) {
            $statusCountsMap[$row['status']] = (int)$row['count'];
        }
    }
} catch (Exception $e) {
    error_log("Admin Dashboard SQL Error: " . $e->getMessage());
}

$statusCounts = array_values($statusCountsMap);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/admin-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Admin Operations Center</h3>
                <p class="text-muted small mb-0">Monitor citywide pickup requests, assign collection teams, and analyze trends.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="requests.php" class="btn btn-dark btn-sm rounded-pill px-4 fw-semibold">
                    <i class="bi bi-list-task me-1"></i> Manage Requests
                </a>
                <a href="statistics.php" class="btn btn-outline-success btn-sm rounded-pill px-4 fw-semibold">
                    <i class="bi bi-bar-chart-line me-1"></i> Analytics
                </a>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <!-- Detailed Stat Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Total Pickups</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1"><?= $totalRequests ?></h3>
                        </div>
                        <div class="bg-primary-subtle text-primary p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
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

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Assigned</span>
                            <h3 class="fw-bold text-info mb-0 mt-1"><?= $assignedRequests ?></h3>
                        </div>
                        <div class="bg-info-subtle text-info p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-person-check-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Scheduled</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1"><?= $scheduledRequests ?></h3>
                        </div>
                        <div class="bg-primary-subtle text-primary p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-calendar-check-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-8 fw-semibold text-uppercase">Completed</span>
                            <h3 class="fw-bold text-success mb-0 mt-1"><?= $completedRequests ?></h3>
                        </div>
                        <div class="bg-success-subtle text-success p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
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

        <!-- Real-Time Analytics Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3">Waste Category Breakdown</h5>
                    <div style="height: 260px; position: relative;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3">Request Status Overview</h5>
                    <div style="height: 260px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Pickup Requests Management Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Recent Collection Requests</h5>
                    <small class="text-muted">Showing latest 10 submissions</small>
                </div>
                <a href="requests.php" class="btn btn-outline-dark btn-sm fw-semibold rounded-pill px-3">
                    All Requests <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light fs-7 text-uppercase">
                        <tr>
                            <th class="ps-4">Request ID</th>
                            <th>Resident User</th>
                            <th>Waste Category</th>
                            <th>Pickup Date</th>
                            <th>Location</th>
                            <th>Status Action</th>
                            <th class="text-end pe-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentRequests)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    No pickup requests found in system database.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentRequests as $req): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <a href="request-details.php?id=<?= urlencode($req['request_id']) ?>" class="text-decoration-none text-dark hover-success">
                                            <?= htmlspecialchars($req['request_id']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($req['user_name']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($req['user_email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($req['waste_category']) ?></span>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($req['pickup_date'])) ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($req['pickup_time']) ?></small>
                                    </td>
                                    <td>
                                        <span class="small text-dark d-block"><?= htmlspecialchars($req['house_building']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($req['city']) ?></small>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm border-0 bg-light fw-bold admin-status-select" 
                                                data-request-id="<?= htmlspecialchars($req['request_id']) ?>"
                                                data-current-status="<?= htmlspecialchars($req['status']) ?>"
                                                style="width: 140px;">
                                            <option value="Pending" <?= $req['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Assigned" <?= $req['status'] === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                                            <option value="Scheduled" <?= $req['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                            <option value="Picked Up" <?= $req['status'] === 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                                            <option value="Completed" <?= $req['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" <?= $req['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="request-details.php?id=<?= urlencode($req['request_id']) ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                                            Manage
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const catLabels = <?= json_encode($catLabels) ?>;
    const catCounts = <?= json_encode($catCounts) ?>;
    const statusLabels = <?= json_encode($statusLabels) ?>;
    const statusCounts = <?= json_encode($statusCounts) ?>;

    initCategoryChart('categoryChart', catLabels, catCounts);
    initStatusChart('statusChart', statusLabels, statusCounts);
});
</script>

<script src="<?= base_url('assets/js/requests.js') ?>"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
