<?php
// user/my-requests.php - User's Pickup Requests List
$pageTitle = "My Pickup Requests — EcoCollect";
require_once __DIR__ . '/../config/auth.php';

require_login();
$user = current_user();
$userId = $user['id'];

$search = sanitize_input($_GET['search'] ?? '');
$status_filter = sanitize_input($_GET['status'] ?? '');
$category_filter = sanitize_input($_GET['category'] ?? '');

$requests = [];
$categories = [];

try {
    $pdo = get_db_connection();

    // Fetch categories for filter dropdown
    $stmt = $pdo->query("SELECT DISTINCT waste_category FROM pickup_requests ORDER BY waste_category ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Build SQL query dynamically
    $sql = "SELECT * FROM pickup_requests WHERE user_id = :uid";
    $params = [':uid' => $userId];

    if (!empty($search)) {
        $sql .= " AND (request_id LIKE :search OR description LIKE :search OR city LIKE :search)";
        $params[':search'] = "%{$search}%";
    }

    if (!empty($status_filter)) {
        $sql .= " AND status = :status";
        $params[':status'] = $status_filter;
    }

    if (!empty($category_filter)) {
        $sql .= " AND waste_category = :category";
        $params[':category'] = $category_filter;
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("My Requests Fetch Error: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/user-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">My Pickup Requests</h3>
                <p class="text-muted small mb-0">View, search, and track all your waste collection submissions.</p>
            </div>
            <a href="create-request.php" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-plus-circle-fill me-1"></i> New Request
            </a>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <!-- Search & Filters Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
            <form method="GET" action="my-requests.php" class="row g-2 align-items-center">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Request ID, description, city...">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-6">
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

                <div class="col-lg-2 col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold rounded-3">
                        Filter
                    </button>
                    <?php if ($search || $status_filter || $category_filter): ?>
                        <a href="my-requests.php" class="btn btn-light btn-sm text-muted rounded-3" title="Clear Filters">
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
                            <th>Waste Category</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Pickup Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-search fs-1 d-block mb-2 text-secondary"></i>
                                        <h6 class="fw-bold text-dark">No pickup requests found</h6>
                                        <p class="small mb-3">Try adjusting your search filters or submit a new pickup request.</p>
                                        <a href="create-request.php" class="btn btn-success btn-sm rounded-pill fw-bold px-4">
                                            + Create Request
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): 
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
                                        <span class="small text-muted d-inline-block text-truncate" style="max-width: 200px;">
                                            <?= htmlspecialchars($req['description']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($req['quantity']) ?></span>
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
                                            Track <i class="bi bi-chevron-right fs-8 ms-1"></i>
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
