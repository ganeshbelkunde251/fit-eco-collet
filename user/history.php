<?php
// user/history.php - User Pickup History
$pageTitle = "Pickup History — EcoCollect";
require_once __DIR__ . '/../config/auth.php';

require_login();
$user = current_user();
$userId = $user['id'];

$historyRequests = [];

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM pickup_requests WHERE user_id = :uid AND status IN ('Completed', 'Cancelled') ORDER BY updated_at DESC");
    $stmt->execute([':uid' => $userId]);
    $historyRequests = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("History Fetch Error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/user-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h3 class="fw-bold text-dark mb-1">Pickup History</h3>
                <p class="text-muted small mb-0">Archived list of your completed and cancelled waste pickups.</p>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light fs-7 text-uppercase">
                        <tr>
                            <th class="ps-4">Request ID</th>
                            <th>Waste Category</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                            <th>Completed / Closed Date</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historyRequests)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-clock-history fs-1 d-block mb-2 text-secondary"></i>
                                        <h6 class="fw-bold text-dark">No historical pickups found</h6>
                                        <p class="small mb-0">Completed or cancelled pickups will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historyRequests as $req): 
                                $statusClass = $req['status'] === 'Completed' ? 'badge-completed' : 'badge-cancelled';
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <?= htmlspecialchars($req['request_id']) ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($req['waste_category']) ?></span>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($req['pickup_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $statusClass ?>">
                                            <i class="bi bi-circle-fill fs-8"></i> <?= htmlspecialchars($req['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y, g:i A', strtotime($req['updated_at'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="request-details.php?id=<?= urlencode($req['request_id']) ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold">
                                            Details
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
