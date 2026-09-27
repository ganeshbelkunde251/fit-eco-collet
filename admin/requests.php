<?php
// admin/requests.php - All Pickup Requests Management
$pageTitle = "All Requests — Admin Panel";
$extraJs = 'requests.js';
require_once __DIR__ . '/../config/auth.php';

require_admin();

$search = sanitize_input($_GET['search'] ?? '');
$status_filter = sanitize_input($_GET['status'] ?? '');
$category_filter = sanitize_input($_GET['category'] ?? '');
$date_filter = sanitize_input($_GET['date'] ?? '');

$requests = [];
$categories = [];

try {
    $pdo = get_db_connection();

    // Fetch categories for filter dropdown
    $stmt = $pdo->query("SELECT DISTINCT waste_category FROM pickup_requests ORDER BY waste_category ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Build dynamic SQL query
    $sql = "SELECT r.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
            FROM pickup_requests r 
            JOIN users u ON r.user_id = u.id 
            WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (r.request_id LIKE :search OR u.name LIKE :search OR u.email LIKE :search OR r.city LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($status_filter)) {
        $sql .= " AND r.status = :status";
        $params[':status'] = $status_filter;
    }

    if (!empty($category_filter)) {
        $sql .= " AND r.waste_category = :category";
        $params[':category'] = $category_filter;
    }

    if (!empty($date_filter)) {
        $sql .= " AND r.pickup_date = :pdate";
        $params[':pdate'] = $date_filter;
    }

    $sql .= " ORDER BY r.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Admin Requests Fetch Error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/admin-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div>
                <h3 class="fw-bold text-dark mb-1">Pickup Requests Management</h3>
                <p class="text-muted small mb-0">Search, filter, and update status for all waste collection submissions.</p>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <!-- Search & Filter Controls -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
            <form method="GET" action="requests.php" class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ID, User name, Email, City...">
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <select class="form-select form-select-sm bg-light" name="status">
                        <option value="">-- All Statuses --</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Assigned" <?= $status_filter === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                        <option value="Scheduled" <?= $status_filter === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="Picked Up" <?= $status_filter === 'Picked Up' ? 'selected' : '' ?>>Picked Up</option>
                        <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-3 col-6">
                    <select class="form-select form-select-sm bg-light" name="category">
                        <option value="">-- All Categories --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <input type="date" class="form-control form-control-sm bg-light" name="date" value="<?= htmlspecialchars($date_filter) ?>" placeholder="Filter by Date">
                </div>

                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold rounded-3">
                        Filter
                    </button>
                    <?php if ($search || $status_filter || $category_filter || $date_filter): ?>
                        <a href="requests.php" class="btn btn-light btn-sm text-muted rounded-3" title="Clear Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Requests Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead class="table-light fs-7 text-uppercase">
                        <tr>
                            <th class="ps-4">Request ID</th>
                            <th>User Details</th>
                            <th>Waste Category</th>
                            <th>Pickup Date</th>
                            <th>Location Address</th>
                            <th>Update Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    No pickup requests found matching your filter criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): ?>
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
                                        <span class="small text-dark d-block"><?= htmlspecialchars($req['house_building']) ?>, <?= htmlspecialchars($req['street_area']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($req['city']) ?> - <?= htmlspecialchars($req['pincode']) ?></small>
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
                                            Manage <i class="bi bi-chevron-right fs-8 ms-1"></i>
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
