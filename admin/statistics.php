<?php
// admin/statistics.php - Collection Analytics & Charting
$pageTitle = "Collection Statistics — Admin Analytics";
$extraJs = 'dashboard.js';
require_once __DIR__ . '/../config/auth.php';

require_admin();

$catLabels = [];
$catCounts = [];
$statusLabels = ['Pending', 'Assigned', 'Scheduled', 'Picked Up', 'Completed', 'Cancelled'];
$statusCountsMap = array_fill_keys($statusLabels, 0);
$cityBreakdown = [];
$totalAll = 0;

try {
    $pdo = get_db_connection();

    // 1. Total count
    $totalAll = $pdo->query("SELECT COUNT(*) FROM pickup_requests")->fetchColumn();

    // 2. Category Distribution
    $catStmt = $pdo->query("SELECT waste_category, COUNT(*) as count FROM pickup_requests GROUP BY waste_category ORDER BY count DESC");
    $catRows = $catStmt->fetchAll();
    foreach ($catRows as $row) {
        $catLabels[] = $row['waste_category'];
        $catCounts[] = (int)$row['count'];
    }

    // 3. Status Distribution
    $statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM pickup_requests GROUP BY status");
    $statusRows = $statusStmt->fetchAll();
    foreach ($statusRows as $row) {
        if (isset($statusCountsMap[$row['status']])) {
            $statusCountsMap[$row['status']] = (int)$row['count'];
        }
    }

    // 4. City Breakdown
    $cityStmt = $pdo->query("SELECT city, COUNT(*) as count FROM pickup_requests GROUP BY city ORDER BY count DESC LIMIT 5");
    $cityBreakdown = $cityStmt->fetchAll();

} catch (Exception $e) {
    error_log("Statistics Page SQL Error: " . $e->getMessage());
}

$statusCounts = array_values($statusCountsMap);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/admin-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h3 class="fw-bold text-dark mb-1">Collection Analytics & Metrics</h3>
                <p class="text-muted small mb-0">Visual breakdown of waste categories, request fulfillment rates, and regional activity.</p>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <!-- Visual Analytics Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart text-success me-2"></i> Category Distribution</h5>
                    <div style="height: 300px; position: relative;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart text-info me-2"></i> Request Status Volume</h5>
                    <div style="height: 300px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row g-4">
            <!-- Category Breakdown Table -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h5 class="fw-bold text-dark mb-0">Category Breakdown Table</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light fs-7 text-uppercase">
                                <tr>
                                    <th class="ps-4">Waste Category</th>
                                    <th>Total Requests</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($catRows)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No data available</td></tr>
                                <?php else: ?>
                                    <?php foreach ($catRows as $c): 
                                        $pct = $totalAll > 0 ? round(($c['count'] / $totalAll) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($c['waste_category']) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= $c['count'] ?></span></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct ?>%"></div>
                                                    </div>
                                                    <small class="fw-semibold text-muted" style="min-width: 45px;"><?= $pct ?>%</small>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Cities Breakdown -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-geo-alt text-primary me-2"></i> Top Pickup Cities</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light fs-7 text-uppercase">
                                <tr>
                                    <th class="ps-4">City</th>
                                    <th class="text-end pe-4">Request Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cityBreakdown)): ?>
                                    <tr><td colspan="2" class="text-center py-4 text-muted">No regional data available</td></tr>
                                <?php else: ?>
                                    <?php foreach ($cityBreakdown as $city): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($city['city']) ?></td>
                                            <td class="text-end pe-4">
                                                <span class="badge bg-success text-white px-3 py-1 rounded-pill"><?= $city['count'] ?> pickups</span>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
