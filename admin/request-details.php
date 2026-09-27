<?php
// admin/request-details.php - Detailed View & Status Controller
$pageTitle = "Request Details — Admin Panel";
$extraJs = 'requests.js';
require_once __DIR__ . '/../config/auth.php';

require_admin();

$reqId = sanitize_input($_GET['id'] ?? '');

if (empty($reqId)) {
    set_flash('danger', 'Invalid Request ID.');
    header('Location: requests.php');
    exit;
}

$request = null;
try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT r.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                           FROM pickup_requests r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.request_id = :req_id LIMIT 1");
    $stmt->execute([':req_id' => $reqId]);
    $request = $stmt->fetch();

    if (!$request) {
        set_flash('danger', 'Pickup request not found.');
        header('Location: requests.php');
        exit;
    }
} catch (Exception $e) {
    set_flash('danger', 'Database error retrieving request details.');
    header('Location: requests.php');
    exit;
}

// Handle Form POST Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = sanitize_input($_POST['status'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        set_flash('danger', 'Security token invalid.');
    } elseif (!empty($newStatus)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE pickup_requests SET status = :status WHERE request_id = :req_id");
            $updateStmt->execute([':status' => $newStatus, ':req_id' => $reqId]);

            set_flash('success', "Status for Request {$reqId} updated to {$newStatus} successfully!");
            header("Location: request-details.php?id=" . urlencode($reqId));
            exit;
        } catch (Exception $e) {
            set_flash('danger', 'Failed to update status: ' . $e->getMessage());
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/admin-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="fw-bold text-dark mb-0">Admin Review: Request #<?= htmlspecialchars($request['request_id']) ?></h3>
                    <?php 
                        $statusClass = match($request['status']) {
                            'Pending'   => 'badge-pending',
                            'Assigned'  => 'badge-assigned',
                            'Scheduled' => 'badge-scheduled',
                            'Picked Up' => 'badge-pickedup',
                            'Completed' => 'badge-completed',
                            'Cancelled' => 'badge-cancelled',
                            default     => 'badge-pending'
                        };
                    ?>
                    <span class="badge-status <?= $statusClass ?>">
                        <i class="bi bi-circle-fill fs-8"></i> <?= htmlspecialchars($request['status']) ?>
                    </span>
                </div>
                <p class="text-muted small mb-0">Created on <?= date('F j, Y \a\t g:i A', strtotime($request['created_at'])) ?></p>
            </div>

            <a href="requests.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Back to Requests
            </a>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <div class="row g-4">
            <!-- Left Column: Update Status Form & Details -->
            <div class="col-lg-7">
                <!-- Status Controller Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-sliders text-success me-2"></i> Update Collection Status</h5>
                    
                    <form method="POST" action="request-details.php?id=<?= urlencode($reqId) ?>" onsubmit="return confirmStatusChange(this)">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold small">Current Workflow Status</label>
                            <select class="form-select bg-light fw-bold" id="status" name="status" required>
                                <option value="Pending" <?= $request['status'] === 'Pending' ? 'selected' : '' ?>>Pending Review</option>
                                <option value="Assigned" <?= $request['status'] === 'Assigned' ? 'selected' : '' ?>>Assigned to Collector</option>
                                <option value="Scheduled" <?= $request['status'] === 'Scheduled' ? 'selected' : '' ?>>Pickup Scheduled</option>
                                <option value="Picked Up" <?= $request['status'] === 'Picked Up' ? 'selected' : '' ?>>Picked Up from Site</option>
                                <option value="Completed" <?= $request['status'] === 'Completed' ? 'selected' : '' ?>>Completed & Disposed</option>
                                <option value="Cancelled" <?= $request['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled Request</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Save Updated Status
                        </button>
                    </form>
                </div>

                <!-- Waste Details Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-box-seam text-dark me-2"></i> Waste Specifications</h5>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block fs-8 text-uppercase">Waste Category</small>
                        <span class="fw-bold text-dark fs-5"><?= htmlspecialchars($request['waste_category']) ?></span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block fs-8 text-uppercase">Quantity</small>
                        <span class="badge bg-light text-dark border mt-1 fs-7"><?= htmlspecialchars($request['quantity']) ?></span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block fs-8 text-uppercase">Item Description</small>
                        <div class="p-3 bg-light rounded-3 mt-1 text-dark">
                            <?= nl2br(htmlspecialchars($request['description'])) ?>
                        </div>
                    </div>

                    <div>
                        <small class="text-muted d-block fs-8 text-uppercase">Scheduled Pickup Date & Time</small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <i class="bi bi-calendar-event text-success fs-5"></i>
                            <span class="fw-bold text-dark">
                                <?= date('l, F j, Y', strtotime($request['pickup_date'])) ?> 
                                (<?= htmlspecialchars($request['pickup_time']) ?>)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: User & Location Details -->
            <div class="col-lg-5">
                <!-- User Contact Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person text-success me-2"></i> Resident Contact Info</h5>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block fs-8 text-uppercase">Full Name</small>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($request['user_name']) ?></span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block fs-8 text-uppercase">Email Address</small>
                        <a href="mailto:<?= htmlspecialchars($request['user_email']) ?>" class="text-decoration-none text-dark fw-semibold">
                            <?= htmlspecialchars($request['user_email']) ?>
                        </a>
                    </div>

                    <div>
                        <small class="text-muted d-block fs-8 text-uppercase">Phone Number</small>
                        <a href="tel:<?= htmlspecialchars($request['user_phone']) ?>" class="text-decoration-none text-success fw-bold">
                            <i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($request['user_phone']) ?>
                        </a>
                    </div>
                </div>

                <!-- Pickup Address Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt text-primary me-2"></i> Pickup Address</h5>

                    <p class="fw-bold text-dark mb-1">
                        <?= htmlspecialchars($request['house_building']) ?>
                    </p>
                    <p class="text-secondary small mb-1">
                        <?= htmlspecialchars($request['street_area']) ?>
                    </p>
                    <p class="text-secondary small mb-3">
                        <?= htmlspecialchars($request['city']) ?>, <?= htmlspecialchars($request['state']) ?> - <?= htmlspecialchars($request['pincode']) ?>
                    </p>

                    <?php if (!empty($request['location_notes'])): ?>
                        <div class="p-3 bg-light rounded-3 border">
                            <small class="fw-bold text-dark d-block mb-1"><i class="bi bi-info-circle me-1"></i> Location Notes:</small>
                            <small class="text-muted"><?= htmlspecialchars($request['location_notes']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmStatusChange(form) {
    const statusSelect = form.querySelector('#status');
    if (statusSelect.value === 'Cancelled') {
        return confirm('Are you sure you want to cancel this waste pickup request?');
    }
    return true;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
