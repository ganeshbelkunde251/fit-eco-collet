<?php
// user/create-request.php - Create Waste Pickup Request
$pageTitle = "Create Pickup Request — EcoCollect";
require_once __DIR__ . '/../config/auth.php';

require_login();
$user = current_user();
$userId = $user['id'];

$errors = [];
$waste_category = $_GET['category'] ?? '';
$description = $_GET['desc'] ?? '';
$quantity = 'Medium';
$house_building = '';
$street_area = '';
$city = '';
$state = '';
$pincode = '';
$location_notes = '';
$pickup_date = date('Y-m-d', strtotime('+1 day'));
$pickup_time = '10:00 AM';

// Fetch waste categories from DB
$categories = [];
try {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT name FROM waste_categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = ['Dry Waste', 'Wet Waste', 'Plastic', 'Paper', 'Glass', 'Metal', 'E-Waste', 'Hazardous Waste', 'Other'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $waste_category = sanitize_input($_POST['waste_category'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $quantity = sanitize_input($_POST['quantity'] ?? 'Medium');
    $house_building = sanitize_input($_POST['house_building'] ?? '');
    $street_area = sanitize_input($_POST['street_area'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $pincode = sanitize_input($_POST['pincode'] ?? '');
    $location_notes = sanitize_input($_POST['location_notes'] ?? '');
    $pickup_date = sanitize_input($_POST['pickup_date'] ?? '');
    $pickup_time = sanitize_input($_POST['pickup_time'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validations
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = "Security check failed. Please refresh and try again.";
    }
    if (empty($waste_category)) {
        $errors[] = "Please select a waste category.";
    }
    if (empty($description)) {
        $errors[] = "Please enter a description of the waste items.";
    }
    if (empty($house_building) || empty($street_area) || empty($city) || empty($pincode)) {
        $errors[] = "Please provide complete pickup address details.";
    }
    if (empty($pickup_date)) {
        $errors[] = "Please select a pickup date.";
    } elseif (strtotime($pickup_date) < strtotime(date('Y-m-d'))) {
        $errors[] = "Pickup date cannot be in the past.";
    }

    if (empty($errors)) {
        try {
            $requestId = generate_request_id($pdo);

            $stmt = $pdo->prepare("INSERT INTO pickup_requests 
                (request_id, user_id, waste_category, description, quantity, house_building, street_area, city, state, pincode, location_notes, pickup_date, pickup_time, status)
                VALUES 
                (:req_id, :uid, :category, :desc, :qty, :house, :street, :city, :state, :pincode, :notes, :pdate, :ptime, 'Pending')");

            $stmt->execute([
                ':req_id'    => $requestId,
                ':uid'       => $userId,
                ':category'  => $waste_category,
                ':desc'      => $description,
                ':qty'       => $quantity,
                ':house'     => $house_building,
                ':street'    => $street_area,
                ':city'      => $city,
                ':state'     => $state,
                ':pincode'   => $pincode,
                ':notes'     => $location_notes,
                ':pdate'     => $pickup_date,
                ':ptime'     => $pickup_time
            ]);

            set_flash('success', "Pickup request submitted successfully! Generated Request ID: {$requestId}");
            header("Location: request-details.php?id=" . urlencode($requestId));
            exit;
        } catch (Exception $e) {
            $errors[] = "Failed to submit pickup request: " . $e->getMessage();
        }
    }
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
                <h3 class="fw-bold text-dark mb-1">Create Waste Pickup Request</h3>
                <p class="text-muted small mb-0">Fill in the waste details, pickup schedule, and location below.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
        </div>

        <?php require_once __DIR__ . '/../includes/alerts.php'; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger rounded-3 mb-4">
                <ul class="mb-0 ps-3 small">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="create-request.php" class="max-w-900">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <!-- Section 1: Waste Information -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-box-seam fs-6"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">1. Waste Details</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="waste_category" class="form-label fw-semibold small">Waste Category <span class="text-danger">*</span></label>
                            <select class="form-select bg-light" id="waste_category" name="waste_category" required>
                                <option value="">-- Select Waste Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" <?= $waste_category === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="quantity" class="form-label fw-semibold small">Approximate Quantity <span class="text-danger">*</span></label>
                            <select class="form-select bg-light" id="quantity" name="quantity" required>
                                <option value="Small" <?= $quantity === 'Small' ? 'selected' : '' ?>>Small (1-2 Bags / Single Item)</option>
                                <option value="Medium" <?= $quantity === 'Medium' ? 'selected' : '' ?>>Medium (3-5 Bags / Bulk Box)</option>
                                <option value="Large" <?= $quantity === 'Large' ? 'selected' : '' ?>>Large (Full Pickup Truck / Heavy Scrap)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold small">Waste Description <span class="text-danger">*</span></label>
                            <textarea class="form-control bg-light" id="description" name="description" rows="3" placeholder="Describe the waste items (e.g., Old mobile phone, laptop chargers, cardboard packaging boxes)" required><?= htmlspecialchars($description) ?></textarea>
                            <small class="text-muted fs-8">Not sure about the waste category? Use our <a href="ai-assistant.php" target="_blank" class="text-success fw-semibold">AI Waste Assistant</a> to identify your items.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Pickup Location -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-geo-alt fs-6"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">2. Pickup Location</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="house_building" class="form-label fw-semibold small">House / Apartment / Building <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="house_building" name="house_building" value="<?= htmlspecialchars($house_building) ?>" placeholder="Apt 4B, Greenview Heights" required>
                        </div>

                        <div class="col-md-6">
                            <label for="street_area" class="form-label fw-semibold small">Street / Area / Landmark <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="street_area" name="street_area" value="<?= htmlspecialchars($street_area) ?>" placeholder="Oakwood Street, Sector 5" required>
                        </div>

                        <div class="col-md-4">
                            <label for="city" class="form-label fw-semibold small">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="city" name="city" value="<?= htmlspecialchars($city) ?>" placeholder="Metropolis" required>
                        </div>

                        <div class="col-md-4">
                            <label for="state" class="form-label fw-semibold small">State</label>
                            <input type="text" class="form-control bg-light" id="state" name="state" value="<?= htmlspecialchars($state) ?>" placeholder="California">
                        </div>

                        <div class="col-md-4">
                            <label for="pincode" class="form-label fw-semibold small">Pincode / Zip Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-light" id="pincode" name="pincode" value="<?= htmlspecialchars($pincode) ?>" placeholder="90210" required>
                        </div>

                        <div class="col-12">
                            <label for="location_notes" class="form-label fw-semibold small">Location Notes (Optional)</label>
                            <input type="text" class="form-control bg-light" id="location_notes" name="location_notes" value="<?= htmlspecialchars($location_notes) ?>" placeholder="e.g., Leave items near garage door / Ring doorbell #4B">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Schedule -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-calendar-event fs-6"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">3. Pickup Schedule</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pickup_date" class="form-label fw-semibold small">Pickup Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control bg-light" id="pickup_date" name="pickup_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($pickup_date) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="pickup_time" class="form-label fw-semibold small">Preferred Time Window <span class="text-danger">*</span></label>
                            <select class="form-select bg-light" id="pickup_time" name="pickup_time" required>
                                <option value="09:00 AM" <?= $pickup_time === '09:00 AM' ? 'selected' : '' ?>>Morning (09:00 AM - 12:00 PM)</option>
                                <option value="12:00 PM" <?= $pickup_time === '12:00 PM' ? 'selected' : '' ?>>Afternoon (12:00 PM - 03:00 PM)</option>
                                <option value="03:00 PM" <?= $pickup_time === '03:00 PM' ? 'selected' : '' ?>>Evening (03:00 PM - 06:00 PM)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="dashboard.php" class="btn btn-light rounded-pill px-4 fw-semibold">Cancel</a>
                <button type="submit" class="btn btn-success rounded-pill px-5 py-3 fw-bold shadow">
                    <i class="bi bi-send-fill me-2"></i> Submit Pickup Request
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
