<?php
// user/profile.php - User Profile & Password Update
$pageTitle = "My Profile — EcoCollect";
require_once __DIR__ . '/../config/auth.php';

require_login();
$user = current_user();
$userId = $user['id'];

$errors = [];
$name = $user['name'];
$email = $user['email'];
$phone = '';

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = :uid LIMIT 1");
    $stmt->execute([':uid' => $userId]);
    $dbUser = $stmt->fetch();
    if ($dbUser) {
        $name = $dbUser['name'];
        $email = $dbUser['email'];
        $phone = $dbUser['phone'];
    }
} catch (Exception $e) {
    error_log("Profile Fetch Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        $errors[] = "Security check failed. Please refresh and try again.";
    } elseif ($action === 'update_profile') {
        $newName = sanitize_input($_POST['name'] ?? '');
        $newPhone = sanitize_input($_POST['phone'] ?? '');

        if (empty($newName)) $errors[] = "Full Name cannot be empty.";
        if (empty($newPhone)) $errors[] = "Phone number cannot be empty.";

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :uid");
                $stmt->execute([':name' => $newName, ':phone' => $newPhone, ':uid' => $userId]);
                $_SESSION['user_name'] = $newName;
                set_flash('success', 'Profile details updated successfully!');
                header('Location: profile.php');
                exit;
            } catch (Exception $e) {
                $errors[] = "Failed to update profile: " . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (strlen($new_password) < 8) $errors[] = "New password must be at least 8 characters long.";
        if ($new_password !== $confirm_password) $errors[] = "New passwords do not match.";

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :uid LIMIT 1");
                $stmt->execute([':uid' => $userId]);
                $passHash = $stmt->fetchColumn();

                if (!password_verify($current_password, $passHash)) {
                    $errors[] = "Current password is incorrect.";
                } else {
                    $newHash = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE id = :uid");
                    $stmt->execute([':pass' => $newHash, ':uid' => $userId]);
                    set_flash('success', 'Password changed successfully!');
                    header('Location: profile.php');
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = "Failed to change password: " . $e->getMessage();
            }
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
                <h3 class="fw-bold text-dark mb-1">Account Profile</h3>
                <p class="text-muted small mb-0">View and update your personal contact details and security credentials.</p>
            </div>
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

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-gear text-success me-2"></i> Profile Information</h5>
                    
                    <form method="POST" action="profile.php">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email Address (Read-only)</label>
                            <input type="email" class="form-control bg-light border-0" value="<?= htmlspecialchars($email) ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold small">Full Name</label>
                            <input type="text" class="form-control bg-light" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="phone" class="form-label fw-semibold small">Phone Number</label>
                            <input type="text" class="form-control bg-light" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 rounded-3 fw-bold shadow-sm">
                            Save Profile Changes
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock text-primary me-2"></i> Change Password</h5>
                    
                    <form method="POST" action="profile.php">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold small">Current Password</label>
                            <input type="password" class="form-control bg-light" id="current_password" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold small">New Password (Min 8 chars)</label>
                            <input type="password" class="form-control bg-light" id="new_password" name="new_password" minlength="8" required>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold small">Confirm New Password</label>
                            <input type="password" class="form-control bg-light" id="confirm_password" name="confirm_password" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
