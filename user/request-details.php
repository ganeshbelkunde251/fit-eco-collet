<?php
// user/request-details.php - Request Details & Status Tracker
$pageTitle = "Request Details — EcoCollect";
require_once __DIR__ . '/../config/auth.php';

require_login();
$user = current_user();
$userId = $user['id'];

$reqId = sanitize_input($_GET['id'] ?? '');

if (empty($reqId)) {
    set_flash('danger', 'Invalid Request ID.');
    header('Location: my-requests.php');
    exit;
}

$request = null;
try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM pickup_requests WHERE request_id = :req_id AND user_id = :uid LIMIT 1");
    $stmt->execute([':req_id' => $reqId, ':uid' => $userId]);
    $request = $stmt->fetch();

    if (!$request) {
        set_flash('danger', 'Pickup request not found or access denied.');
        header('Location: my-requests.php');
        exit;
    }
} catch (Exception $e) {
    set_flash('danger', 'Error retrieving request details.');
    header('Location: my-requests.php');
    exit;
}

// Compute timeline stage index
// Stages: 1=Pending (Submitted), 2=Assigned, 3=Scheduled, 4=Picked Up, 5=Completed
$currentStatus = $request['status'];
$stages = ['Pending', 'Assigned', 'Scheduled', 'Picked Up', 'Completed'];
$currentStageIndex = array_search($currentStatus, $stages);
if ($currentStageIndex === false) $currentStageIndex = 0; // default to first

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-layout">
    <div class="sidebar-wrapper">
        <?php require_once __DIR__ . '/../includes/user-sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="fw-bold text-dark mb-0">Request #<?= htmlspecialchars($request['request_id']) ?></h3>
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
                <p class="text-muted small mb-0">Submitted on <?= date('F j, Y \a\t g:i A', strtotime($request['created_at'])) ?></p>
            </div>

            <div class="d-flex gap-2">
                <a href="my-requests.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back to Requests
                </a>
            </div>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <!-- Visual Status Stepper Timeline -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h5 class="fw-bold text-dark mb-3">Live Status Progression</h5>

            <?php if ($currentStatus === 'Cancelled'): ?>
                <div class="alert alert-danger d-flex align-items-center rounded-3 mb-0" role="alert">
                    <i class="bi bi-x-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Request Cancelled</strong>
                        <p class="small mb-0">This waste pickup request has been cancelled by you or the administrator.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="timeline-stepper">
                    <div class="timeline-step <?= $currentStageIndex >= 0 ? ($currentStageIndex === 0 ? 'active' : 'completed') : '' ?>">
                        <div class="timeline-icon">
                            <?= $currentStageIndex > 0 ? '<i class="bi bi-check-lg"></i>' : '1' ?>
                        </div>
                        <div class="timeline-label">Submitted</div>
                    </div>

                    <div class="timeline-step <?= $currentStageIndex >= 1 ? ($currentStageIndex === 1 ? 'active' : 'completed') : '' ?>">
                        <div class="timeline-icon">
                            <?= $currentStageIndex > 1 ? '<i class="bi bi-check-lg"></i>' : '2' ?>
                        </div>
                        <div class="timeline-label">Assigned</div>
                    </div>

                    <div class="timeline-step <?= $currentStageIndex >= 2 ? ($currentStageIndex === 2 ? 'active' : 'completed') : '' ?>">
                        <div class="timeline-icon">
                            <?= $currentStageIndex > 2 ? '<i class="bi bi-check-lg"></i>' : '3' ?>
                        </div>
                        <div class="timeline-label">Pickup Scheduled</div>
                    </div>

                    <div class="timeline-step <?= $currentStageIndex >= 3 ? ($currentStageIndex === 3 ? 'active' : 'completed') : '' ?>">
                        <div class="timeline-icon">
                            <?= $currentStageIndex > 3 ? '<i class="bi bi-check-lg"></i>' : '4' ?>
                        </div>
                        <div class="timeline-label">Picked Up</div>
                    </div>

                    <div class="timeline-step <?= $currentStageIndex >= 4 ? 'completed active' : '' ?>">
                        <div class="timeline-icon">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="timeline-label">Completed</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <!-- Left Column: Waste Information -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-box-seam text-success me-2"></i> Waste Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <span class="text-muted fs-7 d-block">Waste Category</span>
                            <span class="fw-bold text-dark fs-5"><?= htmlspecialchars($request['waste_category']) ?></span>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted fs-7 d-block">Approximate Quantity</span>
                            <span class="badge bg-light text-dark border fs-7 mt-1"><?= htmlspecialchars($request['quantity']) ?></span>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted fs-7 d-block">Item Description</span>
                            <div class="p-3 bg-light rounded-3 mt-1 text-dark">
                                <?= nl2br(htmlspecialchars($request['description'])) ?>
                            </div>
                        </div>

                        <div>
                            <span class="text-muted fs-7 d-block">Requested Pickup Schedule</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <i class="bi bi-calendar-check text-success fs-5"></i>
                                <span class="fw-semibold text-dark">
                                    <?= date('l, F j, Y', strtotime($request['pickup_date'])) ?> 
                                    (<?= htmlspecialchars($request['pickup_time']) ?>)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Location & Contact -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                    <div class="card-header bg-white p-4 border-bottom">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-geo-alt text-primary me-2"></i> Pickup Address</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="fw-semibold text-dark mb-1">
                            <?= htmlspecialchars($request['house_building']) ?>
                        </p>
                        <p class="text-secondary small mb-1">
                            <?= htmlspecialchars($request['street_area']) ?>
                        </p>
                        <p class="text-secondary small mb-3">
                            <?= htmlspecialchars($request['city']) ?>, <?= htmlspecialchars($request['state']) ?> - <?= htmlspecialchars($request['pincode']) ?>
                        </p>

                        <?php if (!empty($request['location_notes'])): ?>
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <small class="fw-bold text-dark d-block mb-1"><i class="bi bi-info-circle me-1"></i> Location Notes:</small>
                                <small class="text-muted"><?= htmlspecialchars($request['location_notes']) ?></small>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <div class="d-flex align-items-center gap-3 mt-3">
                            <div class="bg-success-subtle text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Contact Phone</small>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($user['phone']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
